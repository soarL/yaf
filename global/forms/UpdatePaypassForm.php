<?php
namespace forms;
use Yaf\Registry;
class UpdatePaypassForm extends \Form {

	public function rules() {
		return [
			[['oldpass', 'paypass', 'paypassSure'], 'required'],
			['oldpass', 'validateOldpass'],
			['paypass', 'validatePaypass'],
			['paypassSure', 'validatePaypassSure'],
		];
	}

	public function labels() {
		return [
        	'paypass' => '新支付密码',
        	'paypassSure' => '重复密码',
        	'oldpass' => '原支付密码',
        ];
	}

	public function validateOldpass() {
		$user = $this->getUser();
		if(!$user->checkPaypass($this->oldpass)) {
			$this->addError('oldpass', '原支付密码错误！');
		}
	}

	public function validatePaypassSure() {
		if($this->paypassSure!=$this->paypass) {
			$this->addError('paypassSure', '重复密码必须与新支付密码一致！');
		}
	}

	public function validatePaypass() {
		if(strlen($this->paypass)<6) {
			$this->addError('paypass', '新支付密码长度需要大于6位！');	
		}
	}

	public function update() {
		if($this->check()) {
			$user = $this->getUser();
			$paypass = $user->password($this->paypass);
			$user->paypass = $paypass;
			if($user->save()) {
				return true;
			} else {
				$this->addError('paypass', '更新失败！');
				return false;
			}
		} else {
			return false;
		}
	}
}