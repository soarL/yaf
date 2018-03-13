<?php
namespace forms;
use models\UserLink;
use Yaf\Registry;
class UserLinkForm extends \Form {

	public function defaults() {
		return [
			'firstRelation' => '',
			'secondRelation' => '',
			'thirdRelation' => '',
		];
	}

	public function rules() {
		return [
			[['firstLinkName', 'secondLinkName', 'thirdLinkName'], 'chineseName'],
			[['firstPhone', 'secondPhone', 'thirdPhone'], 'phoneNumber'],
			[['firstRelation', 'secondRelation', 'thirdRelation'], 'enum', ['values'=>['house', 'friend', 'business']]],
		];
	}

	public function labels() {
		return [
        	'firstLinkName' => '联系人一',
        	'firstRelation' => '关系',
        	'firstPhone' => '联系电话',
        	'secondLinkName' => '联系人二',
        	'secondRelation' => '关系',
        	'secondPhone' => '联系电话',
        	'thirdLinkName' => '联系人三',
        	'thirdRelation' => '关系',
        	'thirdPhone' => '联系电话',
        ];
	}

	public function update() {
		if($this->check()) {
			$user = $this->getUser();
			$link = UserLink::where('userId', $user->userId)->first();
			if(!$link) {
				$link = new UserLink();
				$link->userId = $user->userId;
			}
			$link->firstLinkName = $this->firstLinkName;
			$link->firstRelation = $this->firstRelation==''?null:$this->firstRelation;
			$link->firstPhone = $this->firstPhone;
			$link->secondLinkName = $this->secondLinkName;
			$link->secondRelation = $this->secondRelation==''?null:$this->secondRelation;
			$link->secondPhone = $this->secondPhone;
			$link->thirdLinkName = $this->thirdLinkName;
			$link->thirdRelation = $this->thirdRelation==''?null:$this->thirdRelation;
			$link->thirdPhone = $this->thirdPhone;
			if($link->save()) {
				return true;
			} else {
				$this->addError('form', '更新失败！');
				return false;
			}
		} else {
			return false;
		}
	}
}