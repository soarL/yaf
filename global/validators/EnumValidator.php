<?php
namespace validators;
use tools\Captcha;
class EnumValidator extends \Validator {

	public function validate() {
		if(!in_array($this->value, $this->params['values'])) {
			$this->addError($this->label.'的值不正确！');
            return false;
		} else {
            return true;
        }
	}

}