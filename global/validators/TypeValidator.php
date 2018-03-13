<?php
namespace validators;
class TypeValidator extends \Validator {

	public function validate() {
		$type = $this->params['type'];
		if($type=='number') {
			if(!is_numeric($this->value)) {
				$this->addError($this->label.'必须为数字！');
			}
		} else if($type=='int') {
			if(!is_int($this->value)) {
				if(!preg_match('/^\d+$/', $this->value)) {
					$this->addError($this->label.'必须为整数！');
				}
			}
		} else if($type=='float') {
			if(!is_float($this->value)) {
				if(!preg_match('/^[1-9]\d*\.\d+$|^0\.\d+$|^\d+$/', $this->value)) {
					$this->addError($this->label.'必须为浮点数！');
				}
			}
		} else if($type==='boolean') {
			if($this->value===true||$this->value===false) {
				$this->addError($this->label.'必须为True或False！');
			}
		} else if($type=='string') {
			if(!is_string($this->value)) {
				$this->addError($this->label.'必须为字符串！');
			}
		} else {
			$this->addError($this->label.'值不正确！');
		}
		if($this->hasError()) {
			return false;
		} else {
			return true;
		}
	}
}