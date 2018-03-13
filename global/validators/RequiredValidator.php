<?php
namespace validators;
class RequiredValidator extends \Validator {

	public function validate() {
		if($this->value===null || $this->value==='') {
			$this->addError($this->label.'不能为空！');
			return false;
		}
		return true;
	}

}