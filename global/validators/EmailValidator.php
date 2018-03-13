<?php
namespace validators;
class EmailValidator extends \Validator {
	const PATTERN = '/([a-z0-9]*[-_\.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[\.][a-z]{2,3}([\.][a-z]{2})?/i';

	public static function isEmail($email) {
		if(preg_match(self::PATTERN, $email)) {
			return true;
		} else {
			return false;
		}
	}

	public function validate() {
		if(!preg_match(self::PATTERN, $this->value)){
			$this->addError('请输入正确的邮箱地址！');
			return false;
		}
		return true;
	}

}