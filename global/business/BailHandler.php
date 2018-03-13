<?php
namespace business;

use Illuminate\Database\Capsule\Manager as DB;
use models\MoneyLog;
use models\Odd;
use custody\Handler;
use custody\API;
use models\OddMoney;
use models\User;
use models\CustodyBatch;
use models\BailRepay;
use tools\Counter;
use tools\Log;
use tools\Redis;
use task\Handler as BaseHandler;

/**
 * 用于借款人还垫付的工具类
 * 
 * params:
 *     oddNumber    要复审的标的号
 *     fee          借款费率，默认为0
 *     
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class BailHandler extends BaseHandler {

    private $orgBatchNo;
    private $siteUrl;

    public function init() {
        $this->siteUrl = WEB_MAIN;

        $this->orgBatchNo = isset($this->params['orgBatchNo'])?$this->params['orgBatchNo']:'';
        $this->time = date('Y-m-d H:i:s');
    }

    public function handle() {
        $orgBatchNo = $this->orgBatchNo;
        $bailRepay = BailRepay::with(['odd'=>function($q){
            $q->select('oddNumber', 'userId');
        }])->where('orgBatchNo', $orgBatchNo)->first();

        $rdata = [];
        if(!$bailRepay) {
            $rdata['msg'] = '代偿不存在！';
            $rdata['status'] = false;
            return $rdata;
        }

        if($bailRepay->status!=0) {
            $rdata['msg'] = '代偿已处理或者正在处理中！';
            $rdata['status'] = false;
            return $rdata;
        }

        $data['txAmount'] = 0;
        $data['acqRes'] = ['batchNo'=>Handler::BNQ_PL, 'orgBatchNo'=>$orgBatchNo];
        $data['notifyURL'] = $this->siteUrl.'/custody/batchRepayBailAuthNotify';
        $data['retNotifyURL'] = $this->siteUrl.'/custody/batchRepayBailAuth';

        $items = json_decode($bailRepay->items, true);
        $ids = [];
        foreach ($items as $item) {
            $ids[] = $item['creditId'];
        }

        $creditors = OddMoney::whereIn('id', $ids)->get(['id', 'tradeNo']);
        $crdList = [];
        foreach ($creditors as $creditor) {
            $crdList[$creditor->id] = $creditor->tradeNo;
        }

        if(count($creditors)!=count($items)) {
            $rdata['msg'] = '记录异常，订单数量与代偿数量不一致！';
            $rdata['status'] = false;
            return $rdata;
        }

        $user = User::where('userId', $bailRepay->odd->userId)->first(['userId', 'fundMoney', 'frozenMoney', 'custody_id']);
        $dbUser = User::where('username', User::ACCT_DB)->first(['userId', 'custody_id']);

        $count = 0;
        foreach ($items as $item) {
            $data['subPacks'][] = [
                'accountId' => $user->custody_id,
                'orderId' => _order_id('bail', $item['creditId'], $item['period']),
                'txAmount' => $item['txAmount'],
                'intAmount' => $item['intAmount'],
                'txFeeOut' => 0,
                'forAccountId' => $dbUser->custody_id, // 担保帐户 待定
                'orgOrderId' => $crdList[$item['creditId']],
                'authCode' => $item['authCode'],
            ];
            $data['txAmount'] += $item['txAmount'];
            $count ++;
        }

        if($user->fundMoney<$data['txAmount']) {
            $rdata['msg'] = '用户金额不足！';
            $rdata['status'] = true;
            return $rdata;
        }

        $remark = '[还垫付款]冻结标的@oddNumber{'.$bailRepay->oddNumber.'}，第'.$bailRepay->period.'期还款'.$data['txAmount'].'元。';
        $result = API::frozen($user, $data['txAmount'], ['remark'=>$remark, 'type'=>'nor-bailrepay', 'time'=>$this->time]);
        if(!$result['status']) {
            $rdata['msg'] = $result['msg'];
            $rdata['status'] = false;
            return $rdata;
        }

        $data['txCounts'] = $count;
        $data['subPacks'] = json_encode($data['subPacks']);
        $handler = new Handler('batchRepayBail', $data, true);
        $retData = $handler->api();

        if($retData['received']=='success') {
            $batchNo = $handler->getBN();
            CustodyBatch::insert([
                'batchNo' => $batchNo,
                'type' => 'batchRepayBail',
                'sendTime' => $this->time,
                'refNum'=>$bailRepay->oddNumber,
                'sendData'=> json_encode(['count'=>$count, 'amount'=>$data['txAmount'], 'period'=>$bailRepay->period])
            ]);

            BailRepay::where('orgBatchNo', $orgBatchNo)->where('status', 0)->update(['batchNo'=>$batchNo, 'status'=>-1, 'sendTime'=>$this->time]);

            $rdata['msg'] = '提交银行存管成功！';
            $rdata['status'] = true;
            return $rdata;
        } else {
            $rdata['msg'] = '提交银行存管失败！';
            $rdata['status'] = false;
            return $rdata;
        }
    }
}