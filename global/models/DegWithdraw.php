<?php
namespace models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

class DegWithdraw extends Model {
    
    protected $table = 'user_degwithdraw';

    public $timestamps = false;
	
	public function user() {
  		return $this->belongsTo('models\User', 'userId');
  	}

    public function odd() {
        return $this->belongsTo('models\Odd', 'oddNumber');
    }

  	public static function after($return) {
  		DB::beginTransaction();
        $result = false;
        try {
            $trade = self::where('tradeNo', $return['tradeNo'])->lock()->first();
            if(!$trade) {
                DB::rollback();
            }
            if($return['status']==1) {
                $result = $trade->afterSuccess($return);
            } else {
                $result = $trade->afterFail($return);
            }
        } catch(\Exception $e) {
            \Log::write('受托支付提现通知：'.$e->getMessage(), 'sqlError');
            DB::rollback();
        }
        if($result['status']==1) {
            DB::commit();
        } else {
            DB::rollback();
        }
        return $result;
  	}

  	public function afterSuccess($return) {
		$rdata = [];
		if($this->status==1) {
			$rdata['status'] = 1;
			$rdata['info'] = '受托支付提现成功！';
			return $rdata;	
		}
		if($this->status==2) {
			$rdata['status'] = 1;
			$rdata['info'] = '受托支付提现失败！';
			return $rdata;	
		}
        
        $this->status = 1;
        $this->result = $return['result'];
        $this->validTime = date('Y-m-d H:i:s');
        if($this->save()) {
            $status1 = User::where('userId', $this->userId)->update(['fundMoney'=>DB::raw('fundMoney-'.$this->money)]);
            $status2 = User::where('userId', $this->odd->receiptUserId)->update(['fundMoney'=>DB::raw('fundMoney+'.$this->money)]);

            if($status1 && $status2) {
                // 用户资金日志
                $time = date('Y-m-d H:i:s');
                $remark = '受托支付提现'.$this->money.'元。';
                $logs = [];

                $user = User::where('userId', $this->odd->userId)->first(['userId', 'fundMoney', 'frozenMoney']);
                $logs[] = [
                    'type' => 'nor-degwithdraw',
                    'mode' => 'out',
                    'mvalue' => $this->money,
                    'userId' => $user->userId,
                    'remark' => '受托支付提现'.$this->money.'元。',
                    'remain' => $user->fundMoney,
                    'frozen' => $user->frozenMoney,
                    'time' => $time,
                ];

                $receiptUser = User::where('userId', $this->odd->receiptUserId)->first(['userId', 'fundMoney', 'frozenMoney']);
                $logs[] = [
                    'type' => 'nor-degincome',
                    'mode' => 'in',
                    'mvalue' => $this->money,
                    'userId' => $receiptUser->userId,
                    'remark' => '受托支付入账'.$this->money.'元。',
                    'remain' => $receiptUser->fundMoney,
                    'frozen' => $receiptUser->frozenMoney,
                    'time' => $time,
                ];
                MoneyLog::insert($logs);
                $rdata['status'] = 1;
                $rdata['info'] = '受托支付提现成功！';
            } else {
                $rdata['status'] = 0;
                $rdata['info'] = '修改用户金额异常！';
            }
		} else {
			$rdata['status'] = 0;
			$rdata['info'] = '修改订单异常！';
		}
		return $rdata;
	}

	public function afterFail($return) {
		$rdata = [];
		if($this->status==2) {
			$rdata['status'] = 0;
			$rdata['info'] = '受托支付提现失败！';
			return $rdata;	
		}

		$this->status = 2;
		$this->result = $return['result'];
		$this->validTime = date('Y-m-d H:i:s');
		if($this->save()) {
			$rdata['status'] = 1;
			$rdata['info'] = '受托支付提现失败！';
		} else {
			$rdata['status'] = 0;
			$rdata['info'] = '系统异常！';
		}
		return $rdata;
	}
}
