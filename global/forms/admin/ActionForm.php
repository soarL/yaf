<?php
namespace forms\admin;

use models\AuthAction;
use models\Permission;

/**
 * ActionForm|form类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class ActionForm extends \Form {
	public $action;
	public $perm;
	public $mode = 'add';
	public $permChange = 'save';


	public function init() {
		if($this->id) {
			$this->action = AuthAction::find($this->id);
			$this->perm = $this->action->perm;
			$this->mode = 'update';
		}
	}

	public function defaults() {
		return ['id'=>0, 'rank'=>0];
	}

	public function rules() {
		return [
			[['name', 'is_menu', 'parent_id', 'domain', 'module', 'identifier'], 'required'],
			['identifier', 'validatePerm'],
		];
	}

	public function labels() {
		return [
        	'name' => '名称',
        	'identifier' => '标识符',
        	'is_menu' => '是否菜单',
        	'parent_id' => '上级行为',
        	'domain' => '域',
        	'module' => '模块',
        	'link' => '链接',
        	'rank' => '排序',
        	'description' => '描述',
        ];
	}

	public function validatePerm() {
		if($this->mode=='update') {
			if($this->perm) {
				if($this->identifier) {
					$count = Permission::where('name', $this->identifier)
						->where('id', '<>', $this->perm->id)->count();
					if($count>0) {
						$this->addError('identifier', '标识符已存在！'); return;
					} else {
						$this->perm->display_name = $this->name;
						$this->perm->description = $this->description;
						$this->perm->name = $this->identifier;
					}
				} else {
					$this->permChange = 'delete';
				}
			} else {
				if($this->identifier) {
					$count = Permission::where('name', $this->identifier)->count();
					if($count>0) {
						$this->addError('identifier', '标识符已存在！'); return;
					} else {
						$this->perm = new Permission();
						$this->perm->display_name = $this->name;
						$this->perm->description = $this->description;
						$this->perm->name = $this->identifier;
					}
				}
			}
		} else {
			if($this->identifier) {
				$count = Permission::where('name', $this->identifier)->count();
				if($count>0) {
					$this->addError('identifier', '标识符已存在！'); return;
				} else {
					$this->perm = new Permission();
					$this->perm->display_name = $this->name;
					$this->perm->description = $this->description;
					$this->perm->name = $this->identifier;
				}
			}
		}
	}

	public function save() {
		if($this->check()) {
			$action = $this->action;
			if(!$action) {
				$action = new AuthAction();
			}
			$action->identifier = $this->identifier;
			$action->name = $this->name;
			$action->is_menu = $this->is_menu;
			$action->parent_id = $this->parent_id;
			$action->description = $this->description;
			$action->link = $this->link;
			$action->rank = $this->rank;
			$action->domain = $this->domain;
			$action->module = $this->module;
			$action->icon = $this->icon;

			if($action->save()) {
				if($this->perm) {
					if($this->permChange=='delete') {
						$this->perm->delete();
					} else {
						$this->perm->act_id = $action->id;
						$this->perm->save();
					}
				}
				return true;
			} else {
				$this->addError('form', '操作失败！');
				return false;
			}
		} else {
			return false;
		}
	}
}