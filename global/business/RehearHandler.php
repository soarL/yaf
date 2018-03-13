<?php
namespace business;

use Illuminate\Database\Capsule\Manager as DB;
use models\WorkInfo;
use models\Interest;
use models\MoneyLog;
use models\Invest;
use models\Odd;
use models\Redpack;
use models\OddLog;
use custody\Handler;
use models\OddMoney;
use models\User;
use models\CustodyBatch;
use models\TmpEmail;
use models\Integration;
use models\Sms;
use models\Crtr;
use models\OddTrace;
use custody\API;
use tools\Calculator;
use tools\Counter;
use tools\Log;
use tools\Redis;
use task\Handler as BaseHandler;

/**
 * 用于复审的工具类
 * 
 * params:
 *     oddNumber    要复审的标的号
 *     fee          借款费率，默认为0
 *     
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class RehearHandler extends BaseHandler {

    private $oddNumber;
    private $time;
    private $odd;
    private $oddMoneys;
    private $step;
    private $siteUrl;
    private $receivedUser;

    public function init() {
        $this->siteUrl = WEB_MAIN;
        $this->oddNumber = isset($this->params['oddNumber'])?$this->params['oddNumber']:'';
        $this->step = isset($this->params['step'])?$this->params['step']:1;
        $this->time = date('Y-m-d H:i:s');

        $progress = 'start';
        if($this->step==2) {
            $progress = 'review';
        }

        $columns = ['id', 'serviceFee', 'oddMoney', 'userId', 'oddType', 'oddBorrowStyle', 'oddBorrowPeriod', 'oddRepaymentStyle', 'oddYearRate', 'oddReward', 'oddNumber', 'receiptUserId'];
        $this->odd = Odd::with(['user'=>function($q) { $q->select('userId', 'fundMoney', 'frozenMoney', 'custody_id', 'name', 'sex'); }])
            ->whereRaw('oddMoney=successMoney')
            ->where('progress', $progress)
            ->where('oddNumber', $this->oddNumber)
            ->first($columns);

        if($this->odd) {
            $this->receivedUser = $this->odd->user;
        }

        $this->loanOddMoney = OddMoney::where('oddNumber', $this->oddNumber)
            ->where('type', 'loan')
            ->where('status', 0)
            ->get(['id', 'money', 'tradeNo', 'userId', 'type', 'authCode', 'lotteryId', 'oddNumber']);

        $this->oddMoneys = OddMoney::with([
                'user'=>function($q) {$q->select('userId', 'fundMoney', 'frozenMoney', 'custody_id', 'phone', 'name', 'sex');}, 
                'lottery'=>function($q) {$q->select('id', 'money_rate', 'type');},
            ])
            ->where('oddNumber', $this->oddNumber)
            ->where('type', 'invest')
            ->where('status', 0)
            ->get(['id', 'money', 'tradeNo', 'userId', 'type', 'authCode', 'lotteryId', 'oddNumber']);
    }

    /**
     * 复审
     */
    public function handle() {
        Log::write('-----------------['.$this->oddNumber.']复审，步骤'.$this->step.'---------------------', [], 'rehear');

        $rdata = [];
        if($this->odd==null) {
            $rdata['status'] = false;
            $rdata['msg'] = '标的[' . $this->oddNumber . ']不存在！';
            return $rdata;
        }
        if($this->odd->oddMoney<=0) {
            $rdata['status'] = false;
            $rdata['msg'] = '标的[' . $this->oddNumber . ']无投标记录！';
            return $rdata;
        }

        if($this->step==1) {
            return $this->post();
        } else if($this->step==2) {
            return $this->success();
        }
    }

    /**
     * 复审批次放款接口
     */
    private function post() {
        return API::rehear($this->odd, $this->oddMoneys);
    }

    /**
     * 复审
     */
    private function success() {
        $oddNumber = $this->oddNumber;

        DB::beginTransaction();
        $status = OddMoney::where('oddNumber', $oddNumber)->where('status', '0')->update(['status'=>'1']);

        if($status) {
            Log::write('标的[' . $oddNumber . ']更新投资列表成功！', [], 'rehear');
        }else{
            $rdata['status'] = false;
            $rdata['msg'] = '标的[' . $oddNumber . ']更新投资列表错误！';
            return $rdata;
        }

        /* 更新借款单状态 */
        $oddData = [];
        $oddData['oddRehearRemark'] = '复审成功';
        $oddData['progress'] = 'run';
        $oddData['oddRehearTime'] = $this->time;

        $status = Odd::where('oddNumber', $oddNumber)->update($oddData);

        if ($status) {
            Log::write('标的[' . $oddNumber . ']更新标的状态成功！', [], 'rehear');
        } else {
            $rdata['status'] = false;
            $rdata['msg'] = '标的[' . $oddNumber . ']更新标的状态失败！';
            DB::rollBack();
            return $rdata;
        }

        $fee = 0;
        $fee = $this->odd->serviceFee;// + $this->odd->gpsFee
        $realMoney = $this->odd->oddMoney - $fee;

        $status = User::where('userId', $this->receivedUser->userId)->update([
            'fundMoney'=>DB::raw('fundMoney+'.$realMoney),
            'withdrawMoney'=>DB::raw('withdrawMoney+'.$realMoney),
            'investMoney'=>DB::raw('investMoney+'.$this->odd->oddMoney)
        ]);

        if ($status) {
            Log::write('标的[' . $oddNumber . ']修改收款人账户金额', [], 'rehear');
        } else {
            $rdata['status'] = false;
            $rdata['msg'] = '标的[' . $oddNumber . ']修改收款人账户金额失败！';
            DB::rollBack();
            return $rdata;
        }

        // 融资方资金日志
        $userMoney = $this->odd->user->fundMoney;

        $logs = [];

        $userMoney += $this->odd->oddMoney;
        $log = [];
        $log['type'] = 'nor-loan';
        $log['mode'] = 'in';
        $log['mvalue'] = $this->odd->oddMoney;
        $log['userId'] = $this->odd->user->userId;
        $log['remark'] = '借款标的@oddNumber{'.$this->oddNumber.'}复审成功，获得借款金额'.$this->odd->oddMoney.'元';
        $log['remain'] = $userMoney;
        $log['frozen'] = $this->odd->user->frozenMoney;
        $log['time'] = $this->time;
        $logs[] = $log;

        if($fee>0) {

            $count = User::where('userId', User::ACCT_FEE)->update([
                'fundMoney'=>DB::raw('fundMoney+'.$fee)
            ]);
            if($count) {
                Log::write('标的[' . $oddNumber . ']修改手续费账户金额成功！', [], 'rehear');
            } else {
                $rdata['status'] = false;
                $rdata['msg'] = '标的[' . $oddNumber . ']修改手续费账户金额失败！';
                DB::rollBack();
                return $rdata;
            }

            $log = [];
            $log['type'] = 'fee-loan';
            $log['mode'] = 'out';
            $log['mvalue'] = $fee;
            $log['userId'] = $this->receivedUser->userId;
            $log['remark'] = '扣除借款标的@oddNumber{'.$this->oddNumber.'}借款手续费'.$fee.'元';
            $log['remain'] = $userMoney - $fee;
            $log['frozen'] = $this->receivedUser->frozenMoney;
            $log['time'] = $this->time;
            $logs[] = $log;

            $acctfee = User::where('userId', User::ACCT_FEE)->first();
            $log = [];
            $log['type'] = 'fee-loan';
            $log['mode'] = 'in';
            $log['mvalue'] = $fee;
            $log['userId'] = User::ACCT_FEE;
            $log['remark'] = '获得借款标的@oddNumber{'.$this->oddNumber.'}借款手续费'.$fee.'元';
            $log['remain'] = $acctfee->fundMoney;
            $log['frozen'] = $acctfee->frozenMoney;
            $log['time'] = $this->time;
            $log['source'] = $this->receivedUser->userId;
            $logs[] = $log;
        }

        $status = MoneyLog::insert($logs);
        if ($status) {
            Log::write('标的[' . $oddNumber . ']插入融资方资金日志成功！', [], 'rehear');
        } else {
            $rdata['status'] = false;
            $rdata['msg'] = '标的[' . $oddNumber . ']插入融资方资金日志失败！';
            DB::rollBack();
            return $rdata;
        }

        /* 解冻投资者金额 */
        $status = $this->unfreezeInvestMoney();
        if($status){
            Log::write('标的[' . $oddNumber . ']解冻投资者冻结金额成功！', [], 'rehear');
        }else{
            $rdata['status'] = false;
            $rdata['msg'] = '标的[' . $oddNumber . ']解冻投资者冻结金额失败！';
            DB::rollBack();
            return $rdata;
        }

        // 添加借款人还款、投资人回款列表
        if($this->addRepayList()) {
            Log::write('标的[' . $oddNumber . ']添加回款表、还款表成功！', [], 'rehear');
        } else {
            $rdata['status'] = false;
            $rdata['msg'] = '标的[' . $oddNumber . ']添加回款表、还款表失败！';
            DB::rollBack();
            return $rdata;
        }
        DB::commit();

        Log::write('标的[' . $oddNumber . ']复审完成！', [], 'rehear');

        $key = Redis::getKey('rehearIngQueue');
        Redis::sRem($key, $oddNumber);

        $result = API::changeOddStatus($oddNumber, 'REPAYING');
        if($result['status']){
            Log::write('标的[' . $oddNumber . ']改变银行状态成功！', [], 'rehear');
        }else{
            $rdata['status'] = false;
            $rdata['msg'] = '标的[' . $oddNumber . ']改变银行状态失败！';
            return $rdata;
        }

        if($this->odd->receiptUserId != $this->odd->userId) {
            // 划款到主借款人
            $result = API::degWithdraw($this->odd);
            if($result['status']){
                Log::write('标的[' . $oddNumber . ']划款到主借款人申请成功！', [], 'rehear');
            }else{
                $rdata['status'] = false;
                $rdata['msg'] = '标的[' . $oddNumber . ']划款到主借款人申请失败！';
                return $rdata;
            }
        }

        $oddTrace[] = ['addtime'=>$this->time,'oddNumber'=>$this->odd->oddNumber,'type'=>'rehear','info'=>'借款项目复审成功，放款起息'];
        OddTrace::insert($oddTrace);

        $rdata['status'] = true;
        $rdata['msg'] = '标的[' . $oddNumber . ']复审成功';
        return $rdata;
    }

    //安存
    private function protocol(){
        $dataArray['sign'] = md5('id='.$this->oddNumber.'&type=investabcdeft12345');
        $dataArray['type'] = 'invest';
        $dataArray['id'] = $this->oddNumber;
        post(WEB_MAIN.'/api/backstage/generateProtocols', $dataArray);
    }

    //添加邮件记录
    private function addEmail(){
        TmpEmail::insert(['oddNumber'=>$this->oddNumber, 'addtime'=>$this->time, 'tab_status'=>0]);
    }

    /**
     * 记录借款人还款表以及投资人收益表[复审时操作]
     * @param  string   $oddNumber
     * @return boolean
     */
    protected function addRepayList() {
        $rate = $this->odd->oddYearRate;
        $repayList = [];
        foreach ($this->oddMoneys as $oddMoney) {
            $result = Calculator::getResult([
                'period'=>$this->odd->oddBorrowPeriod, 
                'account'=>$oddMoney->money, 
                'repayType'=>$this->odd->oddRepaymentStyle,
                'periodType'=>$this->odd->oddBorrowStyle,
                'yearRate'=>$rate,
                //'reward'=>$this->odd->oddReward,
                'timeStatus'=>1,
                'time'=>date('Y-m-d H:i:s'),
            ]);

            /** 贴息 BEGIN **/
            $extraList = [];

            if($this->odd->oddReward) {
                $rewardResult = Calculator::getResult([
                    'period'=>$this->odd->oddBorrowPeriod, 
                    'account'=>$oddMoney->money, 
                    'repayType'=>$this->odd->oddRepaymentStyle,
                    'periodType'=>$this->odd->oddBorrowStyle,
                    'yearRate'=>$this->odd->oddReward,
                ]);
                foreach ($rewardResult['list'] as $rewardItem) {
                    $rewardList[$rewardItem['period']] = $rewardItem['interest'];
                }
            }
            /** 贴息 END **/

            /** 加息券 BEGIN **/
            $extraList = [];
            if($oddMoney->lottery && $oddMoney->lottery->type == 'interest') {
                $extraResult = Calculator::getResult([
                    'period'=>$this->odd->oddBorrowPeriod, 
                    'account'=>$oddMoney->money, 
                    'repayType'=>$this->odd->oddRepaymentStyle,
                    'periodType'=>$this->odd->oddBorrowStyle,
                    'yearRate'=>$oddMoney->lottery->money_rate,
                ]);
                foreach ($extraResult['list'] as $extraItem) {
                    $extraList[$extraItem['period']] = $extraItem['interest'];
                }
            }
            /** 加息券 END **/
            Log::write(json_encode($oddMoney->lottery), [], 'rehear');
            /** 现金券 BEGIN **/
            if($oddMoney->lottery && $oddMoney->lottery->type == 'money') {
                $remark = '标的@oddNumber{' . $oddMoney->oddNumber . '},获得投资红包金额'.$oddMoney->lottery->money_rate.'元';
                Log::write($remark, [], 'rehear');
                API::redpack($oddMoney->userId,$oddMoney->lottery->money_rate,'rpk-normal',$remark);

                // $user = User::where('userId', $oddMoney->userId)->first();
                // $bonus = $oddMoney->lottery->money_rate;
                // $user->fundMoney += $bonus;
                // $user->save();
                // $remark = '投资标的@oddNumber{'.$this->oddNumber.'}获得抵扣红包'.$bonus.'元';
                // $time = date('Y-m-d H:i:s');

                // $redpack = new Redpack();
                // $redpack->userId = $user->userId;
                // $redpack->money = $bonus;
                // $redpack->remark = $remark;
                // $redpack->type = 'rpk-investmoney';
                // $redpack->status = 1;
                // $redpack->addtime = $time;
                // $redpack->orderId = '';
                // $redpack->save();

                // $log = [];
                // $log['userId'] = $user->userId;
                // $log['type'] = 'rpk-investmoney';
                // $log['mode'] = 'in';
                // $log['mvalue'] = $bonus;
                // $log['remark'] = $remark;
                // $log['remain'] = $user->fundMoney;
                // $log['frozen'] = $user->frozenMoney;
                // $log['time'] = $time;
                // $logs[] = $log;
            }
            /** 现金券 END **/
            $totalInterest = 0;
            //生成回款表
            foreach ($result['list'] as $item) {
                $rkey = $item['period'] - 1;
                if(isset($repayList[$rkey])) {
                    $repayList[$rkey]['benJin'] += $item['capital'];
                    $repayList[$rkey]['interest'] += $item['interest'];
                    $repayList[$rkey]['zongEr'] += $item['total'];
                    $repayList[$rkey]['yuEr'] += $item['remain'];
                    if(isset($rewardList[$item['period']])){
                        $repayList[$rkey]['reward'] += $rewardList[$item['period']];
                    }
                } else {
                    $repayList[$rkey]['qishu'] = $item['period'];
                    $repayList[$rkey]['addtime'] = $item['begin'];
                    $repayList[$rkey]['endtime'] = $item['end'];
                    $repayList[$rkey]['benJin'] = $item['capital'];
                    $repayList[$rkey]['interest'] = $item['interest'];
                    if(isset($rewardList[$item['period']])){
                        $repayList[$rkey]['reward'] = $rewardList[$item['period']];
                    }else{
                        $repayList[$rkey]['reward'] = 0;
                    }
                    $repayList[$rkey]['zongEr'] = $item['total'];
                    $repayList[$rkey]['yuEr'] = $item['remain'];
                    $repayList[$rkey]['userId'] = $this->odd->userId;
                    $repayList[$rkey]['oddNumber'] = $this->oddNumber;
                    $repayList[$rkey]['oddMoneyId'] = $this->loanOddMoney->id;
                }


                $investRow = [
                    'oddNumber' => $this->oddNumber,
                    'qishu' => $item['period'],
                    'benJin' => $item['capital'],
                    'interest' => $item['interest'],
                    'zongEr' => $item['total'],
                    'yuEr' => $item['remain'],
                    'oddMoneyId' => $oddMoney->id,
                    'addtime' => $item['begin'],
                    'endtime' => $item['end'],
                    'userId' => $oddMoney->userId,
                ];

                /** 贴息 BEGIN **/
                if(isset($rewardList[$item['period']])) {
                    $investRow['reward'] = $rewardList[$item['period']];
                    $investRow['zongEr'] += $rewardList[$item['period']];
                } else {
                    $investRow['reward'] = 0;
                }
                /** 贴息 END **/

                /** 加息券 BEGIN **/
                if(isset($extraList[$item['period']])) {
                    $investRow['extra'] = $extraList[$item['period']];
                } else {
                    $investRow['extra'] = 0;
                }
                /** 加息券 END **/
                
                $totalInterest += $investRow['zongEr'] - $investRow['benJin'];
                $lastendtime = $item['end'];
                
                $investData[] = $investRow;
            }

            if($oddMoney->lottery && $oddMoney->lottery->type == 'money') {
                $msg['phone'] = $oddMoney->user->phone;
                $msg['msgType'] = 'rehearRedpack';
                $msg['userId'] = $oddMoney->userId;
                $msg['params'] = [
                                    $oddMoney->user->getPName(),
                                    $oddMoney->oddNumber,
                                    $oddMoney->lottery->money_rate,
                                    $oddMoney->money,
                                    round($totalInterest,2),
                                    $lastendtime
                                ];
                Sms::send($msg);
            }else{
                $msg['phone'] = $oddMoney->user->phone;
                $msg['msgType'] = 'rehear';
                $msg['userId'] = $oddMoney->userId;
                $msg['params'] = [
                                    $oddMoney->user->getPName(),
                                    $oddMoney->oddNumber,
                                    $oddMoney->money,
                                    round($totalInterest,2),
                                    $lastendtime
                                ];
                Sms::send($msg);
            }
        }
        
        $failCount = Invest::batchInsert($investData);

        if($failCount==0 && Interest::insert($repayList)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 解冻投资者投资资金
     * @return boolean
     */
    protected function unfreezeInvestMoney() {
        $logs = [];
        foreach ($this->oddMoneys as $key => $oddMoney) {
            $status =  User::where('userId', $oddMoney->userId)->update(['frozenMoney'=>DB::raw('frozenMoney - '.$oddMoney->money)]);
            
            $oddMoney->user->frozenMoney -= $oddMoney->money;

            if($status){
                $remark = '投资标的@oddNumber{'.$this->oddNumber.'},解冻'.$oddMoney->money.'元';
                $log = [];
                $log['type'] = 'nor-tender';
                $log['mode'] = 'unfreeze';
                $log['mvalue'] = $oddMoney->money;
                $log['userId'] = $oddMoney->userId;
                $log['remark'] = $remark;
                $log['remain'] = $oddMoney->user->fundMoney;
                $log['frozen'] = $oddMoney->user->frozenMoney;
                $log['time'] = $this->time;
                $logs[] = $log;
            }else{
                return false;
            }
        }

        $failCount = MoneyLog::batchInsert($logs);

        return true;
    }
}
