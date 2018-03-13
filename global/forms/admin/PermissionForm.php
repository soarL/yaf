<?php
namespace forms\admin;
use models\Permission;
use models\Role;

/**
 * PermissionForm|form类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class PermissionForm extends \Form {
	public $permission;

	public function init() {
		if($this->id && $this->id!='') {
			$this->permission = Permission::find($this->id);
		}
	}

	public function defaults() {
		return ['name'=>''];
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
        	'display_name' => '权限名',
        	'description' => '描述',
        ];
	}

	public function validateName() {
		if(!$this->permission) {
			if($this->name=='') {
				$this->addError('name', '请输入权限标识符！');
			} else {
				$count = Permission::where('name', $this->name)->count();
				if($count>0) {
					$this->addError('name', '权限标识符['.$this->name.']已存在！');
				}
			}
		}
	}

	public function save() {
		if($this->check()) {
			if($this->permission) {
				$this->permission->display_name = $this->display_name;
				$this->permission->description = $this->description;
				$this->permission->updated_at = date('Y-m-d H:i:s', time());
				$status = $this->permission->save();
				if($status) {
					return true;
				} else {
					$this->addError('form', '更新失败！');
					return false;
				}
			} else {
				$permission = new Permission();
				$permission->name = $this->name;
				$permission->display_name = $this->display_name;
				$permission->description = $this->description;
				$permission->created_at = date('Y-m-d H:i:s', time());
				$permission->updated_at = date('Y-m-d H:i:s', time());
				$status = $permission->save();
				if($status) {
					
					// 自动给超级管理员分配权限
					$super = Role::where('name', Role::SUPER_ROLE)->first();
					if($super) {
						$super->attachPermission($permission);
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