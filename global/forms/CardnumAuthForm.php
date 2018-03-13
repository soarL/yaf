<?php
namespace forms;
use models\User;
use Yaf\Registry;
use helpers\StringHelper;
use plugins\ancun\ACTool;
use tools\API;
class CardnumAuthForm extends \Form {

	public function rules() {
		return [
			[['realname', 'cardnum'], 'required'],
			['realname', 'chineseName'],
			['cardnum', 'idCard'],
			['cardnum', 'validateCardnum'],
		];
	}

	public function labels() {
		return [
        	'realname' => '真实姓名',
        	'cardnum' => '身份证号',
        ];
	}

	public function validateCardnum() {
		if(User::isIDCardExist($this->cardnum)) {
			$this->addError('cardnum', '身份证号已存在！'); return;
		}
		$status = API::identify(['name'=>$this->realname, 'cardnum'=>$this->cardnum]);
		if(!$status) {
			$this->addError('cardnum', '实名认证失败！'); return;
		}
	}

	public function update() {
		if($this->check()) {
			$user = $this->getUser();
			$birth = StringHelper::getBirthdayByCardnum($this->cardnum);
			$sex = StringHelper::getSexByCardnum($this->cardnum);
			$user->cardnum = $this->cardnum;
			$user->name = $this->realname;
			$user->cardstatus = 'y';
			$user->birth = $birth;
			$user->sex = $sex;
			$user->certificationTime = date('Y-m-d H:i:s');
			
			if($user->save()) {

				$acTool = new ACTool($user, 'user');
    			$acTool->send();

				return true;
			} else {
				$this->addError('paypass', '认证失败！');
				return false;
			}
		} else {
			return false;
		}
	}
}