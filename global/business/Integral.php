<?php
namespace business;

use models\OddMoney;
use models\WorkInfo;
use models\User;
use models\Invest;
use models\Integration;
use helpers\DateHelper;

class Integral {

    /**
     * 获取积分,还款时获取
     * @global type $db
     * @param type $moneyId 投资ID
     * @param type $claunsId 债权转让ID
     * @param type $qishu 当前还款的期数   还款时要用到
     * @return string
     */
    public static function addIntegral($moneyId, $qishu, $time, &$user, $type = 'repayment') {
        //判断标的类型，秒标，天标，月标
        $oddMoney = OddMoney::with(['invests'=>function($q) use($qishu){ $q->select('id', 'oddMoneyId', 'addtime')->where('qishu',$qishu);},'odd'=>function($q){
            $q->select('oddNumber', 'oddBorrowStyle', 'oddBorrowPeriod');
        }])
        ->where('id', $moneyId)->first(['userId','oddNumber','id']);
        //获取积分比例
        $integralRate = self::getIntegralRate($oddMoney->odd->oddBorrowPeriod);
        $userId = $oddMoney->userId;
        $oldintegral = $user->integral;
        //投资本金
        $principal = Invest::where('oddMoneyId',$moneyId)->whereIn('status',['0','3'])->sum('benJin');
        //计算投资天数
        $rate = DateHelper::getIntervalDay($oddMoney->invests[0]->addtime,date('Y-m-d'))/30;

        $integral = ceil($rate*$principal*$integralRate/100)*100;

        $total = $integral + $oldintegral;
        $remark = "投资借款标@oddNumber{" . $oddMoney['oddNumber'] . "},投资金额为" . $principal . ",获取的积分为" . (intval($integral/100));

        $data = ['ref_id'=>$oddMoney->invests[0]->id,'userId'=>$userId,'type'=>$type,'money'=>$principal,'integral'=>$integral,'total'=>$total,'remark'=>$remark,'created_at'=>$time,'updated_at'=>$time];
        $user->integral = $user->integral + $integral;
        if (Integration::insert($data)) {
            $msg['status'] = 'success';
            $msg['msg'] = "用户" . $userId . "获取积分成功";
        } else {
            $msg['status'] = 'error';
            $msg['msg'] = "用户" . $userId . "获取积分失败";
        }
        return $msg;
    }

    //获取积分比率
    public static function getIntegralRate($oddBrrowPeriod){
    	$integralRate = WorkInfo::where('oddkey','integralRate')->first(['oddvalue'])->oddvalue;
        return json_decode($integralRate)->$oddBrrowPeriod;
    }

    /**
     * 计算利息服务费
     * @param  [type] $lixi   [description]
     * @param  [type] $userId [description]
     * @return [type]         [description]
     */
    public static function getOddInterestServer($lixi, $integral) {
        $rad = self::getServiceLv($integral);
        $money = floatval($rad) * floatval($lixi);
        $money = round($money, 2);
        return $money;
    }

    /**
     * 计算利息服务费利率
     * @param type $money 投资金额
     * @return type 利息服务费利率
     */
    public static function getServiceLv($money) {
        if ($money >= 0 AND $money <= 30000) {
            $lv = 0.1;
        } else if ($money > 30000 AND $money <= 150000) {
            $lv = 0.09;
        } else if ($money > 150000 AND $money <= 300000) {
            $lv = 0.08;
        } else if ($money > 300000 AND $money <= 750000) {
            $lv = 0.07;
        } else if ($money > 750000 AND $money <= 1500000) {
            $lv = 0.06;
        } else if ($money > 1500000 AND $money <= 2400000) {
            $lv = 0.05;
        } else if ($money > 2400000 AND $money <= 3500000) {
            $lv = 0.04;
        } else if ($money > 3500000 AND $money <= 6000000) {
            $lv = 0.03;
        } else if ($money > 6000000) {
            $lv = 0.01;
        } else {
            $lv = 0.1;
        }
        return $lv;
    }
}
