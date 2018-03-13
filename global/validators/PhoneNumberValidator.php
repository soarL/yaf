<?php
namespace validators;
class PhoneNumberValidator extends \Validator {
	const PATTERN = '/^1\d{10}$/';

	public static function isPhone($phone) {
		if(preg_match(self::PATTERN, $phone)) {
			return true;
		} else {
			return false;
		}
	}

	public function validate() {
		if(!preg_match(self::PATTERN, $this->value)){
			$this->addError('请输入正确的手机号码！');
			return false;
		}
		return true;
	}

}