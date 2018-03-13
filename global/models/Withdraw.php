<?php
namespace models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;
use models\Sms;
use plugins\ancun\ACTool;

class Withdraw extends Model {

	const FEE_BASE = 2;

	const FEE_PER = 0.005;

    public static $unknowCodes = ['CT9903', 'CT990300', 'CE999999', '510000'];
    
    protected $table = 'user_withdraw';

    public $timestamps = false;
	
	public function user() {
  		return $this->belongsTo('models\User', 'userId');
  	}

  	public static function after($return) {
  		DB::beginTransaction();
        $result = false;
        try {
            $trade = self::where('tradeNo', $return['tradeNo'])->lock()->first();
            if(!$trade) {
                DB::rollback();
                return $result;
            }
            if($return['status']==1) {
                $result = $trade->afterSuccess($return);
            } else {
                $result = $trade->afterFail($return);
            }
        } catch(\Exception $e) {
            \Log::write('提现通知：'.$e->getMessage(), 'sqlError');
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
			$rdata['info'] = '提现成功！';
			return $rdata;	
		}
		if($this->status==2) {
			$rdata['status'] = 1;
			$rdata['info'] = '提现失败！';
			return $rdata;	
		}
        
        $this->status = 1;
        $this->result = $return['result'];
        $this->validTime = date('Y-m-d H:i:s');
        if($this->save() && $this->user->updateAfterWithdraw($this->outMoney)) {
            // 用户资金日志
            $time = date('Y-m-d H:i:s');
            $getMoney = $this->outMoney - $this->fee;
            $remark = '提现'.$getMoney.'元。';
            $logs = [];
            $logs[] = [
                'type' => 'nor-withdraw',
                'mode' => 'out',
                'mvalue' => $getMoney,
                'userId' => $this->userId,
                'remark' => $remark,
                'remain' => $this->user->fundMoney + $this->fee,
                'frozen' => $this->user->frozenMoney,
                'time' => $time,
            ];
            if($this->fee>0) {
                User::where('userId', User::ACCT_FEE)->update([
                    'fundMoney'=>DB::raw('fundMoney+'.$this->fee)
                ]);
                $feeRemark = '提现手续费'.$this->fee.'元。';
                $logs[] = [
                    'type' => 'fee-withdraw',
                    'mode' => 'out',
                    'mvalue' => $this->fee,
                    'userId' => $this->userId,
                    'remark' => $feeRemark,
                    'remain' => $this->user->fundMoney,
                    'frozen' => $this->user->frozenMoney,
                    'time' => $time,
                ];

                $acctfee = User::where('userId', User::ACCT_FEE)->first();
                $feeRemark = $this->userId.'提现手续费'.$this->fee.'元。';
                $logs[] = [
                    'type' => 'fee-withdraw',
                    'mode' => 'in',
                    'mvalue' => $this->fee,
                    'userId' => User::ACCT_FEE,
                    'remark' => $feeRemark,
                    'remain' => $acctfee->fundMoney,
                    'frozen' => $acctfee->frozenMoney,
                    'time' => $time,
                ];

                $acctfee = User::where('userId', User::ACCT_RP)->first();
                $bffee = 1;
                User::where('userId', User::ACCT_RP)->update(['fundMoney'=>DB::raw('fundMoney-'.$bffee)]);
                $feeRemark = $this->userId.'宝付提现费'.$bffee.'元。';
                $logs[] = [
                    'type' => 'fee-withdraw',
                    'mode' => 'out',
                    'mvalue' => $bffee,
                    'userId' => User::ACCT_RP,
                    'remark' => $feeRemark,
                    'remain' => $acctfee->fundMoney,
                    'frozen' => $acctfee->frozenMoney,
                    'time' => $time,
                ];

            }
            MoneyLog::insert($logs);

			if($this->lotteryId) {
				Lottery::where('id', $this->lotteryId)->update(['status'=>Lottery::STATUS_USED, 'used_at'=>$time]);
			}

			$acTool = new ACTool($this, 'withdraw');
		    $acTool->send();

            $user = $this->user;
            $msg['phone'] = $user->phone;
            $msg['msgType'] = 'withdraw';
            $msg['userId'] = $user->userId;
            $msg['params'] = [
                                $user->getPName(),
                                date('Y-m-d H:i:s'),
                                $this->outMoney,
                                $this->fee,
                                $getMoney,
                            ];
            Sms::send($msg);

			$rdata['status'] = 1;
			$rdata['info'] = '提现成功！';
		} else {
			$rdata['status'] = 0;
			$rdata['info'] = '系统异常！';
		}
		return $rdata;
	}

	public function afterFail($return) {
		$rdata = [];
		if($this->status==2) {
			$rdata['status'] = 0;
			$rdata['info'] = '提现失败！';
			return $rdata;	
		}

        //$this->user->updateAfterWithdrawE($this->outMoney);

		$this->status = 2;
		$this->result = $return['result'];
		$this->validTime = date('Y-m-d H:i:s');
		if($this->save()) {
			$rdata['status'] = 1;
			$rdata['info'] = '提现失败！';
		} else {
			$rdata['status'] = 0;
			$rdata['info'] = '系统异常！';
		}
		return $rdata;
	}
}
