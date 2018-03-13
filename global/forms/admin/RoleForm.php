<?php
namespace forms\admin;
use models\Permission;
use models\Role;

/**
 * RoleForm|form类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class RoleForm extends \Form {
	public $role;

	public function init() {
		if($this->id && $this->id!='') {
			$this->role = Role::find($this->id);
		}
	}

	public function defaults() {
		return ['permissions'=>[], 'name'=>''];
	}

	public function rules() {
		return [
			[['display_name', 'description'], 'required'],
			['name', 'validateName'],
		];
	}

	public function labels() {
		return [
        	'name' => '标识符',
        	'display_name' => '角色名',
        	'description' => '描述',
        ];
	}

	public function validateName() {
		if(!$this->role) {
			if($this->name=='') {
				$this->addError('name', '请输入角色标识符！');
			} else {
				$count = Role::where('name', $this->name)->count();
				if($count>0) {
					$this->addError('name', '角色标识符['.$this->name.']已存在！');
				}
			}	
		}
	}

	public function save() {
		if($this->check()) {
			if($this->role) {
				$this->role->display_name = $this->display_name;
				$this->role->description = $this->description;
				$this->role->updated_at = date('Y-m-d H:i:s', time());
				$status = $this->role->save();
				if($status) {
					$permIDList = [];
					foreach ($this->role->perms as $perm) {
						if(!in_array($perm->id, $this->permissions)) {
							$this->role->detachPermission($perm);
						} else {
							$permIDList[] = $perm->id;
						}
					}

					foreach ($this->permissions as $permission) {
						if(!in_array($permission, $permIDList)) {
							$this->role->attachPermission($permission);
						}
					}

					return true;
				} else {
					$this->addError('form', '更新失败！');
					return false;
				}
			} else {
				$role = new Role();
				$role->name = $this->name;
				$role->display_name = $this->display_name;
				$role->description = $this->description;
				$role->created_at = date('Y-m-d H:i:s', time());
				$role->updated_at = date('Y-m-d H:i:s', time());
				$status = $role->save();
				if($status) {
					if($this->permissions&&is_array($this->permissions)) {
						foreach ($this->permissions as $permission) {
							$role->attachPermission($permission);
						}
					}
					return true;
				} else {
					$this->addError('form', '添加失败！');
					return false;
				}
			}
		} else {
			return false;
		}
	}
}