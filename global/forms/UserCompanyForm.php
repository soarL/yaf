<?php
namespace forms;
use models\UserOffice;
use Yaf\Registry;
class UserCompanyForm extends \Form {

	public function defaults() {
		return [
			'officeyear' => '',
		];
	}

	public function rules() {
		return [
			['officeproof', 'chineseName'],
			['officeprooftel', 'phoneNumber'],
			['officeyear', 'enum', ['values'=>[1, 3, 3, 4, 5]]],
		];
	}

	public function labels() {
		return [
        	'officeproof' => '工作年限',
        	'officeprooftel' => '证明人',
        	'officeyear' => '证明人手机',
        ];
	}

	public function update() {
		if($this->check()) {
			$user = $this->getUser();
			$office = UserOffice::where('userId', $user->userId)->first();
			if(!$office) {
				$office = new UserOffice();
				$office->userId = $user->userId;
			}
			$office->officename = $this->officename;
			$office->officephone = $this->officephone;
			$office->officecity = $this->officecity;
			$office->officeyear = $this->officeyear==''?null:$this->officeyear;
			$office->officeadder = $this->officeadder;
			$office->officeproof = $this->officeproof;
			$office->officeprooftel = $this->officeprooftel;
			if($office->save()) {
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