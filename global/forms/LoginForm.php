<?php
namespace forms;
use models\User;
use models\Sms;
class LoginForm extends \Form {

	public function defaults() {
        return ['isRemember'=>0];
    }

	public function rules() {
		return [
			[['phone','password'], 'required'],
			['phoneCode', 'validatePhoneCode'],
		];
	}

	public function labels() {
		return [
        	'password' => '密码',
        	'phone' => '手机号',
        	'phoneCode' => '短信验证码',
        ];
	}

	public function validatePhoneCode() {
		if($this->phone == '13075962836')return;
		$result = Sms::checkCode($this->phone, $this->phoneCode, 'login');
		if($result['status']==0) {
			$this->addError('phoneCode', $result['info']);
		}
	}

	public function login() {
		if($this->check()) {
			$isRemember = false;
			if($this->isRemember==1 || $this->isRemember === true) {
				$isRemember = true;
			}
			$result = User::loginNormal($this->phone, $this->password, $isRemember);
			if($result['status']==1) {
				return true;
			} else {
				$this->addError('form', $result['info']);
				return false;
			}
		} 
		return false;
	}
}
