<?php
namespace models;

use Illuminate\Database\Eloquent\Model;
use plugins\lianlian\lib\LLapi;
use tools\Banks;

class RechargeAgree extends Model {

	protected $table = 'user_llagree';

	public $timestamps = false;

	/**
	 * 插入或者更新一条记录
	 * @param  array   $data   数据项
	 * @return boolean         操作是否成功
	 */
	public static function insertOrUpdate($data) {
		$agreement = false;
		if(isset($data['noAgreeThird'])) {
			$agreement = self::where('userId', $data['userId'])->where('noAgreeThird', $data['noAgreeThird'])->first();
		} else {
			$agreement = self::where('userId', $data['userId'])->where('noAgree', $data['noAgree'])->first();	
		}
		
		if($agreement) {
			$agreement->lastUseTime = date('Y-m-d H:i:s');
			$agreement->bankCode = $data['bankCode'];
			$agreement->save();
		} else {
			$agreement = new self();
			$agreement->lastUseTime = date('Y-m-d H:i:s');
			$agreement->addTime =  date('Y-m-d H:i:s');
			foreach ($data as $key => $value) {
				$agreement->$key = $value;
			}
			$agreement->save();
		}
		return $agreement->id;
	}

	public static function getAgreement($userId, $third=false) {
		$return = false;
        $agreement = self::where('userId', $userId)->orderBy('lastUseTime', 'desc')->first();
        if(!$agreement) {
        	return $return;
        }
		if($third=='lianlian') {
			$agreements = LLapi::getUserBankCard($userId);
	        foreach ($agreements as $row) {
	            if($agreement->noAgreeThird==$row['no_agree']) {
	                $return = $row;
	                break;
	            }
	        }
		} else {
			$code = Banks::getBankCodeByCode($agreement->bankCode, $third);

			$return = [];
	        $return['bank_code'] = $code;
	        $return['no_agree'] = $agreement->noAgree;
	        $return['card_no'] = substr($agreement['bankCard'], strlen($agreement['card_no'])-4);
	        $return['card_type'] = 2;
			$return['bank_name'] = Banks::getBankNameByCode($code);
		}
        return $return;
	}
}