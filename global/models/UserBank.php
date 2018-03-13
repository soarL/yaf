<?php
namespace models;

use tools\Banks;
use tools\Areas;
use Illuminate\Database\Eloquent\Model;

/**
 * UserBank|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class UserBank extends Model {

	protected $table = 'user_bank_account';

	public $timestamps = false;

	public function user() {
		return $this->belongsTo('models\User', 'userId');
	}

	public function agree() {
		return $this->hasOne('models\RechargeAgree', 'noAgree', 'noAgree');
	}

	public static function isUserBankExist($bankId, $userId) {
		$count = self::where('userId', $userId)->where('id', $bankId)->count();
		if($count>0) {
			return true;
		} else {
			return false;
		}
	}

	public static function isUserBankNumExist($bankNum, $userId) {
		$count = self::where('userId', $userId)->where('bankNum', $bankNum)->where('status', '1')->count();
		if($count>0) {
			return true;
		} else {
			return false;
		}
	}

	public static function getHideBankNum($bankNum) {
		$length = strlen($bankNum);
		$hideBankNum = '';
		$bankNumBegin = substr($bankNum, 0, 3);
		$bankNumEnd = substr($bankNum, $length-4);
		$hideBankNum = $bankNumBegin . '****' . $bankNumEnd;
		return $hideBankNum;
	}

	public static function getBankInfo($bankId) {
		$bankInfo = self::where('id', $bankId)->first();
		if(!$bankInfo) {
			return false;
		} else {
			// if($bankInfo['province']==0||$bankInfo['city']==0||$bankInfo['subbranch']=='') {
			// 	return false;
			// }
		}
		$bank = [];
		$temp = explode('-', $bankInfo->binInfo);
		$bank['bank'] = $bankInfo->bankCName;//Banks::getBankName($bankInfo->bank)
		$bank['province'] = $bankInfo->province;
		$bank['city'] =$bankInfo->city;
		$bank['subbranch'] = $bankInfo->subbranch;
		$bank['bankNum'] = $bankInfo->bankNum;
		$bank['bankUsername'] = $bankInfo->bankUsername;
		$bank['data'] = $bankInfo;
		return $bank;
	}

	public function getBank() {
		return Banks::getBankName($this->bank);
	}

	public function getProvince() {
		return Areas::getProvinceName($this->province);
	}

	public function getCity() {
		return Areas::getCityName($this->province, $this->city);
	}

	public function getBin($type='') {
		$binInfoList = explode('-', $this->binInfo);
		if($type=='bank') {
			return isset($binInfoList[0])?$binInfoList[0]:'';
		} else if($type=='name') {
			return isset($binInfoList[1])?$binInfoList[1]:'';
		} else if($type=='type') {
			return isset($binInfoList[2])?$binInfoList[2]:'';
		} else {
			return $this->binInfo;
		}
	}
}