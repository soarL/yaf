<?php
namespace forms;
use Yaf\Registry;
class SetPaypassForm extends \Form {

	public function rules() {
		return [
			[['paypass', 'paypassSure', 'loginpass'], 'required'],
			['paypass', 'validatePaypass'],
			['paypassSure', 'validatePaypassSure'],
			['loginpass', 'validateLoginpass'],
		];
	}

	public function labels() {
		return [
        	'paypass' => '支付密码',
        	'paypassSure' => '重复密码',
        	'loginpass' => '登录密码',
        ];
	}

	public function validateLoginpass() {
		$user = $this->getUser();
		if(!$user->checkLoginpass($this->loginpass)) {
			$this->addError('loginpass', '登录密码错误！');
		}
	}

	public function validatePaypassSure() {
		if($this->paypassSure!=$this->paypass) {
			$this->addError('paypassSure', '重复密码必须与支付密码一致！');
		}
	}

	public function validatePaypass() {
		if(strlen($this->paypass)<6) {
			$this->addError('paypass', '支付密码长度需要大于6位！');	
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
				$this->addError('paypass', '设置失败！');
				return false;
			}
		} else {
			return false;
		}
	}
}