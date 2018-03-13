<?php
namespace forms\admin;
use models\History;

/**
 * HistoryForm|form类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class HistoryForm extends \Form {
	public $history = false;

	public function init() {
		if($this->id && $this->id!='') {
			$this->history = History::find($this->id);
		} else {
			$this->history = false;
		}
	}

	public function rules() {
		return [
			[['name', 'content', 'happened_at'], 'required']
		];
	}
	
	public function labels() {
		return [
        	'name' => '名称',
        	'content' => '内容',
        	'happened_at' => '发生时间',
        ];
	}

	public function save() {
		if($this->check()) {
			$history = $this->history;
			if(!$history) {
				$history = new History();
				$history->created_at = date('Y-m-d H:i:s');
			}
			$history->name = $this->name;
			$history->content = $this->content;
			$history->happened_at = $this->happened_at;
			if($history->save()) {
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