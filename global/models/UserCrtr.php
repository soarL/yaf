<?php
namespace models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;
use models\Sms;
use tools\Log;
use tools\Redis;

/**
 * UserCrtr|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class UserCrtr extends Model {
	const TIME_LIMIT = 930;

	protected $table = 'user_crtr';

	public $timestamps = false;

	public function crtr() {
		return $this->belongsTo('models\Crtr', 'crtr_id');
	}

	public function user() {
		return $this->belongsTo('models\User', 'userId');
	}

	/**
	 * 用于购买债权后回调
	 * @param  array  $return 返回的一些参数
	 * @return array          结果信息
	 */
	public static function after($return) {
		$tradeNo = $return['tradeNo'];
		$result = [];

		DB::beginTransaction();
		try {
			$trade = self::where('tradeNo', $tradeNo)->lock()->first();
			if($trade) {
				if($return['status']==1) {
					$result = $trade->afterSuccess($return);
				} else {
					$result = $trade->afterFail($return);
				}
			} else {
				$result['status'] = 0;
				$result['msg'] = '订单不存在！';
			}
		} catch(\Exception $e) {
			Log::write('购买债权回调：'.$e->getMessage(), [], 'sqlError');
			$result['status'] = 0;
			$result['msg'] = '系统异常，请联系客服！';
		}

		if($result['status']==1) {
			DB::commit();
		} else {
			DB::rollback();
		}
		
		return $result;
	}

	/**
	 * 用于购买债权成功后回调
	 * @param  array  $return 返回的一些参数
	 * @return array          结果信息
	 */
	public function afterSuccess($return) {
		$rdata = [];
		if($this->status==1) {
			$rdata['status'] = 1;
			$rdata['msg'] = '购买成功！';
			return $rdata;
		}

		$result = (isset($return['result']) && $return['result'])?$return['result']:'UNKNOWN';

		$count = self::where('tradeNo', $this->tradeNo)->where('status', '0')->update([
			'status' => 1,
			'validTime' => date('Y-m-d H:i:s'),
			'result' => $result,
		]);

		if($count && $this->handle($return['authCode'])) {
			$rdata['status'] = 1;
			$rdata['msg'] = '购买成功！';
		} else {
			$rdata['status'] = 0;
			$rdata['msg'] = '系统异常，请联系客服！';
		}
		return $rdata;
	}

	/**
	 * 用于购买债权失败后回调
	 * @param  array  $return 返回的一些参数
	 * @return array          结果信息
	 */
	public function afterFail($return) {
		$result = (isset($return['result']) && $return['result'])?$return['result']:'UNKNOWN';
		$count = self::where('tradeNo', $this->tradeNo)->where('status', '0')->update([
			'status' => 2,
			'validTime' => date('Y-m-d H:i:s'),
			'result' => $result,
		]);

		if($count) {
			$this->crtr->disBuy($this->money);
		}

		$rdata = [];
		$rdata['status'] = 1;
		$rdata['msg'] = '购买失败！';
		return $rdata;
	}

	private function handle($authCode) {
		$time = date('Y-m-d H:i:s');
		$oddMoney = new OddMoney();
		$oddMoney->oddNumber = $this->crtr->oddNumber;
		$oddMoney->type = 'credit';
		$oddMoney->money = $this->money;
		$oddMoney->remain = $this->money;
		$oddMoney->userId = $this->userId;
		$oddMoney->remark = '债权转让';
		$oddMoney->time = $time;
		$oddMoney->status = 0;
		$oddMoney->tradeNo = $this->tradeNo;
		$oddMoney->bid = $this->crtr->oddmoneyId;
		$oddMoney->cid = $this->crtr->id;
		$oddMoney->media = $this->media;
		$oddMoney->authCode = $authCode;
		if($oddMoney->save()) {
			$total = $this->money + $this->interest;
			$outTotal = $total - $this->fee; 
			$status1 = OddMoney::where('id', $oddMoney->bid)->update(['remain'=>DB::raw('remain-'.$this->money)]);
			$status2 = User::where('userId', $this->userId)->update(['fundMoney'=>DB::raw('fundMoney-'.$total)]);
			$status3 = User::where('userId', $this->crtr->userId)->update([
				'fundMoney'=>DB::raw('fundMoney+'.$outTotal), 
				'investMoney'=>DB::raw('investMoney+'.$outTotal)
			]);
			$status4 = Crtr::where('id', $this->crtr_id)->update(['successMoney'=>DB::raw('successMoney+'.$this->money)]);
			
			if($status1&&$status2&&$status3&&$status4) {
				// 用户资金日志 此处更新时间值是为了账户金额时间误差
				$time = date('Y-m-d H:i:s');
				$sellUser = User::where('userId', $this->crtr->userId)->first(['userId', 'fundMoney', 'frozenMoney', 'sex', 'phone', 'name']);
				$buyUser = User::where('userId', $this->userId)->first(['userId', 'fundMoney', 'frozenMoney', 'sex', 'phone', 'name']);
				$logs = [];
				$remark = '购买债权@crtrNumber{'.$this->crtr->getSN().'}，支出'.$total.'元。其中本金：'.$this->money.'元，已获利息：'.$this->interest.'元。';
				$logs[] = [
	                'type' => 'nor-crtr',
	                'mode' => 'out',
	                'mvalue' => $total,
	                'userId' => $buyUser->userId,
	                'remark' => $remark,
	                'remain' => $buyUser->fundMoney,
	                'frozen' => $buyUser->frozenMoney,
	                'time' => $time,
	            ];
	            $remark = '出售债权@crtrNumber{'.$this->crtr->getSN().'}，收入'.($outTotal+$this->fee).'元。其中本金：'.$this->money.'元，未结利息：'.$this->interest.'元。';
	            $logs[] = [
	                'type' => 'nor-transfer',
	                'mode' => 'in',
	                'mvalue' => $total,
	                'userId' => $sellUser->userId,
	                'remark' => $remark,
	                'remain' => $sellUser->fundMoney + $this->fee,
	                'frozen' => $sellUser->frozenMoney,
	                'time' => $time,
	            ];
	            if($this->fee>0) {
	            	User::where('userId', User::ACCT_FEE)->update([
	                    'fundMoney'=>DB::raw('fundMoney+'.$this->fee)
	                ]);
		            $remark = '出售债权@crtrNumber{'.$this->crtr->getSN().'}，支出转让服务费：'.$this->fee.'元。';
		            $logs[] = [
		                'type' => 'fee-crtr',
		                'mode' => 'out',
		                'mvalue' => $this->fee,
		                'userId' => $sellUser->userId,
		                'remark' => $remark,
		                'remain' => $sellUser->fundMoney,
		                'frozen' => $sellUser->frozenMoney,
		                'time' => $time,
		            ];

		            $acctfee = User::where('userId', User::ACCT_FEE)->first();
		            $logs[] = [
		                'type' => 'fee-crtr',
		                'mode' => 'in',
		                'mvalue' => $this->fee,
		                'userId' => User::ACCT_FEE,
		                'remark' => $remark,
		                'remain' => $acctfee->fundMoney,
		                'frozen' => $acctfee->frozenMoney,
		                'time' => $time,
		            ];
	            }
	            MoneyLog::insert($logs);


	            $msg = [];
	            $user = $buyUser;
	            $msg['phone'] = $user->phone;
	            $msg['msgType'] = 'buyCrtr';
	            $msg['userId'] = $user->userId;
	            $msg['params'] = [
	                                $user->getPName(),
	                                '债转项目'.$this->crtr->getSN(),
	                                $this->money,
	                                $this->interest,
	                            ];
	            Sms::send($msg);

	            if($this->fee==0) {
		            $msg = [];
		            $user = $sellUser;
		            $msg['phone'] = $user->phone;
		            $msg['msgType'] = 'sellCrtr';
		            $msg['userId'] = $user->userId;
		            //$remain = OddMoney::where('id', $oddMoney->bid)->first();
		            $msg['params'] = [
		                                $user->getPName(),
		                                '债转项目'.$this->crtr->getSN(),
		                                $this->money,
		                                $this->interest,
		                                $outTotal,
		                                $this->crtr->money-$this->crtr->successMoney-$this->money,
		                            ];
		            Sms::send($msg);
	            }else{
	            	$msg = [];
		            $user = $sellUser;
		            $msg['phone'] = $user->phone;
		            $msg['msgType'] = 'sellCrtrFee';
		            $msg['userId'] = $user->userId;
		            //$remain = OddMoney::where('id', $oddMoney->bid)->first();
		            $msg['params'] = [
		                                $user->getPName(),
		                                '债转项目'.$this->crtr->getSN(),
		                                $this->money,
		                                $this->interest,
		                                $this->fee,
		                                $outTotal,
		                                $this->crtr->money-$this->crtr->successMoney-$this->money,
		                            ];
		            Sms::send($msg);
	            }

				$key = Redis::getKey('ancunQueue');
                $params = [$key];
                $list[] = json_encode(['key'=>$oddMoney->id, 'type'=>'assign', 'flow'=>0]);
                $params = array_merge($params, $list);
                call_user_func_array(array('tools\Redis', 'lpush'), $params);

				return true;
			} else {
				return false;
			}
		} else {
			return false;	
		}
	}
}
