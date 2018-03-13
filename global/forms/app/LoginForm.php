<?php
namespace forms\app;
use models\User;
use Yaf\Registry;
class LoginForm extends \Form {

	public function rules() {
		return [
			[['username','password'], 'required'],
		];
	}

	public function queue() {
		return [
			'require',
		];
	}

	public function labels() {
		return [
        	'password' => '密码',
        	'username' => '用户名',
        ];
	}

	public function login() {
		if($this->check()) {
			$result = User::loginApp($this->username, $this->password);
			if($result['status']==1) {
				$user = Registry::get('user');
				$this->setUser($user);
				return true;
			} else {
				$this->addError('form', $result['info']);
				return false;
			}
		} 
		return false;
	}
}