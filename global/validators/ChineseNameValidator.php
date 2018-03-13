<?php
namespace validators;
use tools\Captcha;
class ChineseNameValidator extends \Validator {

	public function validate() {
		if(!preg_match('/^[\x{4e00}-\x{9fa5}]{2,4}$/u', $this->value)){
			$this->addError('请输入正确的中文名！');
            return false;
		}
        return true;
	}

}