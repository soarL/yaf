<?php
namespace forms;
use models\User;
use models\Sms;
class ForgetTwoForm extends \Form {
	public function rules() {
		return [
			[['phone', 'phoneCode', 'password', 'passwordSure'], 'required'],
			['phone', 'validatePhone'],
			['password','validatePassword'],
			['passwordSure','validatePasswordSure'],
			['phoneCode', 'validatePhoneCode'],
		];
	}

	public function labels() {
		return [
        	'phone' => '手机号',
        	'captcha' => '验证码',
        	'password' => '交易密码'
        ];
	}

	public function validatePhone() {
		if(!User::isPhoneExist($this->phone)) {
			$this->addError('phone', '手机号不存在！');
		}
	}

	public function validatePhoneCode() {
		$result = Sms::checkCode($this->phone, $this->phoneCode, 'forget');
		if($result['status']==0) {
			$this->addError('password', $result['info']);
		}
	}

	public function validatePassword() {
		if(strlen($this->password)<6) {
			$this->addError('password', '登录密码长度不能小于6位！');
		}
	}

	public function validatePasswordSure() {
		if($this->passwordSure!==$this->password) {
			$this->addError('password', '两次输入密码不一致！');
		}
	}

	public function update() {
		if($this->check()) {
			$user = User::where('phone', $this->phone)->first();
			$loginpass = $user->password($this->password);
			$user->loginpass = $loginpass;
			if($user->save()) {

				/** update bbs password begin **/
				/*$oldpass = substr($user->loginpass, 8, 16);
				$newpass = substr($loginpass, 8, 16);
				$email = $user->email;
		        if(!$email) {
		        	$email = 'random'.$user->userId.'@hcjrfw.com';
		        }
				$result = BBSUnion::resetPassword($user->username, $oldpass, $newpass, $email, 1);*/
				/** update bbs password end **/

				return true;
			} else {
				$this->addError('form', '找回失败！');
				return false;
			}
		} else {
			return false;
		}
	}
}