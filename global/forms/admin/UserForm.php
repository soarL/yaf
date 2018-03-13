<?php
namespace forms\admin;
use models\Permission;
use models\User;

/**
 * UserForm|form类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class UserForm extends \Form {
	public $user;

	public function defaults() {
		return ['roles'=>[],'blackstatus'=>0];
	}

	public function rules() {
		return [
			[['userId','username'], 'required'],
			['userId', 'validateUser'],
			['username', 'validateUsername'],
			['loginpass', 'validateLoginpass'],
			['paypass', 'validatePaypass'],
			['level', 'validateLevel'],
		];
	}

	public function labels() {
		return [
        	'loginpass' => '登录密码',
        	'paypass' => '支付密码',
        	'username' => '用户名',
        ];
	}

	public function validateUser() {
		$this->user = User::find($this->userId);
		if(!$this->user) {
			$this->addError('userId', '用户不存在！');
		}
	}

	public function validateUsername() {
		if($this->username != $this->user->username) {
			$user = User::where('username', $this->username)->first();
			if($user) {
				$this->addError('username', '用户名已存在!');
			}
		}
	}

	public function validatePaypass() {
		if($this->paypass!='') {
			if(strlen($this->paypass)<6) {
				$this->addError('paypass', '支付密码长度需要大于6位！');	
			}
		}
	}

	public function validateLoginpass() {
		if($this->loginpass!='') {
			if(strlen($this->loginpass)<6) {
				$this->addError('loginpass', '登录密码长度需要大于6位！');	
			}
		}
	}

	public function validateLevel(){
		if($this->level != $this->user->level){
			$this->waiter = $this->getUser();
			if($this->user->service && $this->user->service != $this->waiter->userId){
				$this->addError('level', '您不是该用户客服人员！');	
			}
		}
	}

	public function update() {
		if($this->check()) {

			$this->user->username = $this->username;
			//$this->user->level = $this->level;
			if($this->level != $this->user->level){
				$this->user->service = $this->waiter->userId;
				$this->user->level = $this->level;
			}
			$this->user->userimg = $this->photo;
			$this->user->blackstatus = $this->blackstatus;
			$this->user->hcparter = $this->hcparter;
			$this->user->recruit = $this->recruit;

			if($this->paypass!='') {
				$this->user->paypass = $this->user->password($this->paypass);
			}

			if($this->loginpass!='') {
				$this->user->loginpass = $this->user->password($this->loginpass);
			}

			$status = $this->user->save();

			if(!$status) {
				$this->addError('form', '更新用户失败！');
			}

			if($this->scene=='user') {
				// 移除未选中角色
				$roles = $this->user->roles;
				$roleIDList = [];
				foreach ($roles as $role) {
					if(!in_array($role->id, $this->roles)) {
						$this->user->detachRole($role);
					} else {
						$roleIDList[] = $role->id;
					}
				}
				
				foreach ($this->roles as $role) {
					if(!in_array($role, $roleIDList)) {
						$this->user->attachRole($role);
					}
				}
			}

			if($this->hasErrors()) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}
}