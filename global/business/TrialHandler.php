<?php
namespace business;

use models\Odd;
use models\User;
use models\OddMoney;
use models\OddTrace;
use tools\Log;
use tools\Counter;
use tools\Redis;
use Illuminate\Database\Capsule\Manager as DB;
use task\Handler as BaseHandler;

/**
 * 用于初审的工具类
 * params
 *     odds 标的号数组（要初审的标的号）
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class TrialHandler extends BaseHandler {
    private $time;

    public function handle(){
        $this->time = date('Y-m-d H:i:s');
        $list = isset($this->params['odds'])?$this->params['odds']:[];
        $odds = Odd::whereIn('oddNumber', $list)->get([
            'oddNumber', 
            'oddYearRate', 
            'investType',
            'oddMoney',
            'oddType',
            'userId',
            'oddBorrowValidTime',
            'oddBorrowPeriod',
            'oddBorrowStyle',
            'progress',
            'isCr',
            'receiptUserId',
            'receiptStatus',
        ]);

        $msg = '';
        foreach ($odds as $odd) {
            $result = $this->trial($odd);
            $msg .= $result['msg'];
            
            $key = Redis::getKey('trialIngQueue');
            Redis::lRem($key, $odd->oddNumber, 0);
        }

        $rdata['status'] = true;
        $rdata['msg'] = $msg;
        return $rdata;
    }

    private function trial($odd) {
        $time = date('Y-m-d H:i:s');

        if($odd->receiptUserId!='' && $odd->receiptStatus==0) {
            $rdata['status'] = false;
            $rdata['msg'] = '标的['.$odd->oddNumber.']初审失败，受托支付未签约！';
            return $rdata; 
        }

        $key = Redis::getKey('oddRemain', ['oddNumber'=>$odd->oddNumber]);
        Redis::set($key, $odd->oddMoney*100);

        $data = ['progress'=>'start', 'oddTrialTime'=>$time, 'oddTrialRemark'=>'初审成功'];
        $status = Odd::where('oddNumber', $odd->oddNumber)->update($data);

        User::where('userId',$odd->userId)->where('userType','<>','3')->update(['userType'=>'2']);

        $oddMoneys[] = [
                    'oddNumber' => $odd->oddNumber,
                    'type' => 'loan',
                    'money' => $odd->oddMoney,
                    'remain' => $odd->oddMoney,
                    'userId' => $odd->userId,
                    'remark' => '借款',
                    'time' => $time,
                    'status' => '0',
                    'tradeNo' => Counter::getOrderID(),
                ];
        $status = OddMoney::insert($oddMoneys);

        $rdata = [];
        if($status) {
            if($odd->investType==0) {
                $key = Redis::getKey('oddAutoQueue');
                Redis::lpush($key, $odd->oddNumber);
            }
            $oddTrace[] = ['info'=>'{"money":"无","management":"无","finance":"无","ability":"无","delay":"无","appeal":"无","penalty":"无"}','oddNumber'=>$odd->oddNumber,'type'=>'base'];
            $oddTrace[] = ['addtime'=>$time,'oddNumber'=>$odd->oddNumber,'type'=>'publish','info'=>'该借款项目发布'];
            OddTrace::insert($oddTrace);
            Log::write('标的['.$odd->oddNumber.']初审，改变标的状态成功！', [], 'trial');
            $rdata['status'] = true;
            $rdata['msg'] = '标的['.$odd->oddNumber.']初审，改变标的状态成功！';
            return $rdata;
        } else {
            $rdata['status'] = false;
            $rdata['msg'] = '标的['.$odd->oddNumber.']初审，改变标的状态成失败！';
            return $rdata;
        }
    }
}
