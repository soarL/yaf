<?php
namespace validators;
use tools\Captcha;
class CaptchaValidator extends \Validator {

	public function validate() {
		if(!Captcha::check($this->value)) {
			$this->addError($this->label.'错误！');
            return false;
		} else {
            return true;
        }
	}
}