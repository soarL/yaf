<?php
namespace forms\app;
use models\User;
use models\Sms;
use Yaf\Registry;
class ForgetLoginpassForm extends \Form {

	public function rules() {
		return [
			[['phone', 'loginpass', 'loginpassSure', 'phoneCode'], 'required'],
			['loginpass', 'validateLoginpass'],
			['loginpassSure', 'validateLoginpassSure'],
			['phone', 'validatePhone'],
			['phoneCode', 'validatePhoneCode'],
		];
	}

	public function labels() {
		return [
			'phone' => '手机号',
        	'loginpass' => '新登录密码',
        	'loginpassSure' => '重复密码',
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
			$this->addError('loginpass', $result['info']);
		}
	}

	public function validateLoginpassSure() {
		if($this->loginpassSure!=$this->loginpass) {
			$this->addError('loginpassSure', '重复密码必须与新登录密码一致！');
		}
	}

	public function validateLoginpass() {
		if(strlen($this->loginpass)<6) {
			$this->addError('loginpass', '新登录密码长度需要大于6位！');	
		}
	}

	public function update() {
		if($this->check()) {
			$user = $this->getUser();
			$loginpass = $user->password($this->loginpass);
			$user->loginpass = $loginpass;
			if($user->save()) {
				return true;
			} else {
				$this->addError('loginpass', '找回失败！');
				return false;
			}
		} else {
			return false;
		}
	}
}