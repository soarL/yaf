<?php
namespace tools;
use \Data;
use plugins\lianlian\lib\LLapi;
class Banks {
	public static function getBanks() {
		$banks = Data::get('banks');
		$i = 0;
		$newBanks = [];
		foreach ($banks as $key => $bank) {
			$newBanks[$i]['key'] = $key;
			$newBanks[$i]['name'] = $bank;
			$i++;
		}
		return $newBanks;
	}

	public static function getBankName($bankId) {
		$banks = Data::get('banks');
		return isset($banks[$bankId])?$banks[$bankId]:'无法识别的银行卡';
	}

	public static function getBankIDByCode($code) {
		$bank = false;
		if(isset(LLapi::$banks[$code])) {
			$bank = LLapi::$banks[$code];
		} else if(isset(FYBank::$banks[$code])) {
			$bank = FYBank::$banks[$code];
		} else if(isset(BFBank::$banks[$code])) {
			$bank = BFBank::$banks[$code];
		}
		if($bank) {
			return $bank['id'];
		} else {
			return 0;
		}
	}

	public static function getBankNameByCode($code) {
		$bank = false;
		if(isset(LLapi::$banks[$code])) {
			$bank = LLapi::$banks[$code];
		} else if(isset(FYBank::$banks[$code])) {
			$bank = FYBank::$banks[$code];
		} else if(isset(BFBank::$banks[$code])) {
			$bank = BFBank::$banks[$code];
		}
		if($bank) {
			return $bank['name'];
		} else {
			return '';
		}
	}

	public static function getBankCodeByID($id, $type) {
		$banks = [];
		if($type=='lianlian') {
			$banks = LLapi::$banks;
		} else if($type=='fuiou') {
			$banks = FYBank::$banks;
		} else if($type=='baofoo') {
			$banks = BFBank::$banks;
		}
		$code = '';
		foreach ($banks as $key => $bank) {
			if($id==$bank['id']) {
				$code = $key;
				break;
			}
		}
		return $code;
	}

	public static function getBankCodeByCode($code, $type) {
		$bankID = self::getBankIDByCode($code);
		return self::getBankCodeByID($bankID, $type);
	}
}