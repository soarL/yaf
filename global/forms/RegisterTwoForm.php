<?php
namespace forms;
use models\User;
use models\Sms;
class RegisterTwoForm extends \Form {

	public function rules() {
		return [
			[['username','phone', 'authCode', 'phoneCode', 'password_hash', 'randomCode'], 'required'],
			['username', 'validateUsername'],
			['phone', 'validatePhone'],
			['phoneCode', 'validatePhoneCode'],
			['authCode', 'validateAuthCode'],
		];
	}

	public function labels() {
		return [
			'username' => '昵称',
        	'phone' => '手机号码',
        	'password' => '登录密码',
        	'passwordSure' => '重复密码',
        	'captcha' => '验证码',
        ];
	}

	public function validateUsername() {
		$length = strlen($this->username);
		if($length<4||$length>24) {
			$this->addError('username', '昵称长度为4-16个字符之间！');
		} else {
			if(!preg_match("/^[a-zA-Z0-9_\x{4e00}-\x{9fa5}]{2,16}$/u",$this->username)) {
				$this->addError('username', '昵称格式不正确！');
			} else {
				if(User::isUsernameExist($this->username)) {
					$this->addError('username', '该昵称已经被占用！');
				}
			}
		}
	}

	public function validatePhone() {
		if(!preg_match("/1\d{10}$/",$this->phone)) {
			$this->addError('phone', '手机号码格式不正确！');
		} else {
			if(User::isPhoneExist($this->phone)) {
				$this->addError('phone', '该手机号已经被占用！');
			}
		}
	}

	public function validatePhoneCode() {
		$result = Sms::checkCode($this->phone, $this->phoneCode, 'register');
		if($result['status']==0) {
			$this->addError('password', $result['info']);
		}
	}

	public function validateAuthCode() {
		$authCode = md5($this->username.$this->phone.$this->password_hash.$this->randomCode);
		if($authCode!=$this->authCode) {
			$this->addError('password', '操作错误！');
		}
	}
}