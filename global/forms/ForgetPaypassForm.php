<?php
namespace forms;
use Yaf\Registry;
use models\User;
use models\Sms;
class ForgetPaypassForm extends \Form {

	public function rules() {
		return [
			[['phone', 'paypass', 'paypassSure', 'phoneCode'], 'required'],
			['paypass', 'validatePaypass'],
			['paypassSure', 'validatePaypassSure'],
			['phone', 'validatePhone'],
			['phoneCode', 'validatePhoneCode'],
		];
	}

	public function labels() {
		return [
			'phone' => '手机号',
        	'paypass' => '新支付密码',
        	'paypassSure' => '重复密码',
        	'phoneCode' => '短信验证码',
        ];
	}

	public function validatePhone() {
		$user = User::where('phone', $this->phone)->first();
		if(!$user) {
			$this->addError('phone', '手机号不存在！');
		} else {
			$this->setUser($user);
		}
	}

	public function validatePhoneCode() {
		$result = Sms::checkCode($this->phone, $this->phoneCode, 'forget');
		if($result['status']==0) {
			$this->addError('phoneCode', $result['info']);
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
				$this->addError('paypass', '找回失败！');
				return false;
			}
		} else {
			return false;
		}
	}
}