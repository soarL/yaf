<?php
namespace business;

use models\Lottery as Lot;
use models\OddLog;
use models\OddLogErr;

use Illuminate\Database\Capsule\Manager as DB;

class Lottery {
	use Common;

	public static function interestLottery($lotteryId,$oddMoneyId){
		$self = new self;
		$time = date("Y-m-d H:i:s");
		$self->log = ['oddNumber'=>$lotteryId,'time'=>$time,'type'=>'lottery'];
		$rate = Lot::where('type','interest')->where('endtime',$time)->where('status','1')->where('id',$lotteryId)->first(['money_rate'])->money_rate;
		DB::beginTransaction();
		if(!empty($rate)){
			OddMoney::where('id',$oddMoneyId)->update(['lotteryId'=>$lotteryId]);
			$odd = Odd::where('oddNumber',$data[0]->oddNumber)->first(['oddYearRate','oddReward']);
			$yearRate = $odd->oddYearRate + $odd->oddReward;
			$rate = ($rate)/($yearRate);
			$res = Invest::where('oddMoneyId',$oddMoneyId)->where('status',0)->update(['extra'=>DB::raw('interest *'.$rate)]);
			$self->log['remark'] = '使用加息券'.$lotteryId;
            if ($res) {
                $self->oddLog[] = $self->log;
            } else {
                $msg['status'] = FALSE;
                $msg['msg'] = $self->log['remark'];
                OddLogErr::writeLog($self->log);
                DB::rollBack();
                return $msg;
            }
			Lot::where('id',$lotteryId)->update(['status'=>2,'used_at'=>$time]);
			OddLog::writeLogAll($self->oddLog);
			DB::commit();
	        $msg['status'] = 'success';
	        $msg['msg'] = $self->log['remark'];
	        return $msg;
		}else{
	        $msg['status'] = 'error';
	        $msg['msg'] = '位置错误';
	        $self->log['remark'] = $msg['msg'];
	        OddLogErr::writeLog($self->log);
	        return $msg;
		}
	}
}
