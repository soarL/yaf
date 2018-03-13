<?php
namespace models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;
use factories\RedisFactory;
use tools\Yemadai;
use tools\API;

/**
 * UserBid|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class UserBid extends Model {
	
	protected $table = 'user_bid';

	public $timestamps = false;

	public function user() {
		return $this->belongsTo('models\User', 'userId');
	}

	public function odd() {
		return $this->belongsTo('models\Odd', 'oddNumber');
	}

	public function oddMoney() {
		return $this->belongsTo('models\OddMoney', 'tradeNo', 'tradeNo');
	}

	public static function after($return) {
		DB::beginTransaction();
		$tradeNo = $return['tradeNo'];
		$result = false;
		try {
			$trade = self::where('tradeNo', $tradeNo)->lock()->first();
			if(!$trade) {
				DB::rollback();
			}
			if($return['status']==1) {
				$result = $trade->afterSuccess($return);
			} else {
				$result = $trade->afterFail($return);
			}
		} catch(\Exception $e) {
			\Log::write('投标通知：'.$e->getMessage(), 'sqlError');
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
		if($this->status!=0) {
			if($this->status==1) {
				$rdata['status'] = 1;
				$rdata['msg'] = '投标成功！';
			} else if($this->status==3) {
				// 超时处理，待定
				
				$rdata['status'] = 1;
				$rdata['msg'] = '该笔投资超时！';
			} else {
				$rdata['status'] = 1;
				$rdata['msg'] = '投标失败！';
			}
			return $rdata;
		}
		Odd::where('oddNumber', $this->oddNumber)->update([
			'freezeMoney'=>DB::raw('freezeMoney-'.$this->bidMoney)
		]);
		$this->status = 1;
		$this->validTime = date('Y-m-d H:i:s');
		$this->result = $return['result'];

		if($this->save() && $this->handle($return['authCode'])) {
			$rdata['status'] = 1;
			$rdata['state'] = 'success';
			$rdata['msg'] = '投标成功！';
		} else {
			$rdata['status'] = 0;
			$rdata['state'] = 'fail';
			$rdata['msg'] = '投标失败！';
		}
		return $rdata;
	}

	public function afterFail($return) {
		$rdata = [];
		if($this->status!=0) {
			if($this->status==1) {
				$rdata['status'] = 1;
				$rdata['msg'] = '投标成功！';
			} else if($this->status==3) {
				$rdata['status'] = 1;
				$rdata['msg'] = '该笔投资超时！';
			} else {
				$rdata['status'] = 1;
				$rdata['msg'] = '投标失败！';
			}
			return $rdata;
		}

		$this->status = 2;
		$this->validTime = date('Y-m-d H:i:s');
		$this->result = $return['result'];

		if($this->save()) {
			Odd::where('oddNumber', $this->oddNumber)->update([
				'oddMoneyLast'=>DB::raw('oddMoneyLast+'.$this->bidMoney),
				'freezeMoney'=>DB::raw('freezeMoney-'.$this->bidMoney)
			]);
		}

		$rdata['status'] = 1;
		$rdata['msg'] = '投标失败！';
		
		return $rdata;
	}

	private function handle($authCode) {
		$user = $this->user;
		if($user->fundMoney<$this->bidMoney) {
			$this->errorMsg = '用户余额不足！';
			return false;
		}
		$oddMoney = new OddMoney();
		$oddMoney->oddNumber = $this->oddNumber;
		$oddMoney->type = 'invest';
		$oddMoney->money = $this->bidMoney;
		$oddMoney->userId = $this->userId;
		$oddMoney->remark = '手动投标';
		$oddMoney->time = date('Y-m-d H:i:s');
		$oddMoney->status = 0;
		$oddMoney->tradeNo = $this->tradeNo;
		$oddMoney->media = $this->media;
		$oddMoney->authCode = $authCode;
		if($oddMoney->save()) {
			$status = User::where('userId', $this->userId)->update([
				'frozenMoney'=>DB::raw('frozenMoney+'.$this->bidMoney), 
				'fundMoney'=>DB::raw('fundMoney-'.$this->bidMoney)
			]);

			// 用户资金日志
			$remark = '投资标的@oddNumber{'.$this->oddNumber.'},冻结'.$this->bidMoney.'元';
			MoneyLog::log($user, 'bid', 'freeze', $this->bidMoney, $remark);

			return $status;
		}
		return false;
	}
}