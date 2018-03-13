<?php
namespace forms;
use models\User;
use Yaf\Registry;
// use plugins\bbs\BBSUnion;
class UpdateLoginpassForm extends \Form {

	public function rules() {
		return [
			[['oldpass', 'loginpass', 'loginpassSure'], 'required'],
			['oldpass', 'validateOldpass'],
			['loginpass', 'validateLoginpass'],
			['loginpassSure', 'validateLoginpassSure'],
		];
	}

	public function labels() {
		return [
        	'loginpass' => '新登录密码',
        	'loginpassSure' => '重复密码',
        	'oldpass' => '原登录密码',
        ];
	}

	public function validateOldpass() {
		$user = $this->getUser();
		if(!$user->checkLoginpass($this->oldpass)) {
			$this->addError('oldpass', '原登录密码错误！');
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
			$oldpass = $user->loginpass;
			$loginpass = $user->password($this->loginpass);
			$user->loginpass = $loginpass;
			if($user->save()) {

				/** update bbs password begin **/
				/*$oldpass = substr($user->loginpass, 8, 16);
				$newpass = substr($loginpass, 8, 16);
				$email = $user->email;
		        if(!$email) {
		        	$email = 'random'.$user->userId.'@hcjrfw.com';
		        }
				$result = BBSUnion::resetPassword($user->username , $oldpass, $newpass , $email, 1);*/
				/** update bbs password end **/

				return true;
			} else {
				$this->addError('loginpass', '更新失败！');
				return false;
			}
		} else {
			return false;
		}
	}
}