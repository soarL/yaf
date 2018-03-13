<?php
namespace forms;
use Yaf\Registry;
use models\Email;
use models\User;
class SetEmailForm extends \Form {

	public function rules() {
		return [
			[['email', 'captcha'], 'required'],
			['captcha', 'captcha'],
			['email', 'email'],
			['email', 'validateEmail'],
		];
	}

	public function labels() {
		return [
        	'email' => '邮箱',
        	'captcha' => '验证码',
        ];
	}

	public function validateEmail() {
		$count = User::where('email', $this->email)->count();
		if($count>0) {
			$this->addError('email', '该邮箱已存在！');
		}
	}

	public function send() {
		if($this->check()) {
			$result = Email::send(['type'=>'setEmail', 'email'=>$this->email]);
			if($result['status']==1) {
				return true;
			} else {
				$this->addError('form', $result['info']);
				return false;
			}
		} else {
			return false;
		}
	}
}