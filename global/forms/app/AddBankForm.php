<?php
namespace forms\app;
use Yaf\Registry;
use models\UserBank;
class AddBankForm extends \Form {

	public function rules() {
		return [
			[['bankNum', 'bank', 'province', 'city', 'subbranch', 'isDefault'], 'required'],
			['form', 'validateBankCount'],
			['bankNum', 'validateBankNum'],
			['bank', 'validateBank'],
			['province', 'validateProvince'],
			['city', 'validateCity'],
			['subbranch', 'validateSubbranch'],
			['isDefault', 'enum', ['values'=>['y', 'n']]],
		];
	}

	public function labels() {
		return [
			'bankNum' => '银行卡号',
        	'bank' => '开户行',
        	'province' => '开户行所在省',
        	'city' => '开户行所在市',
        	'subbranch' => '支行名称',
        	'isDefault' => '是否默认',
        ];
	}

	public function validateBankCount() {
		$user = $this->getUser();
		$count = UserBank::where('userId', $user->userId)->where('status', '1')->count();
		if($count>=9) {
			$this->addError('form', '每个用户最多只能添加9张银行卡！');
		}
	}

	public function validateBankNum() {
		if(strlen($this->bankNum)>15) {
			if(!preg_match("/^\d+$/",$this->bankNum)){
				$this->addError('bankNum', '请填写正确的银行卡号！');
			} else {
				$user = $this->getUser();
				if(UserBank::isUserBankNumExist($this->bankNum, $user->userId)) {
					$this->addError('bankNum', '您已填写过该银行卡号，无需重复填写！');		
				}
			}
		} else {
			$this->addError('bankNum', '请填写正确的银行卡号！');
		}
	}

	public function validateBank() {
		if($this->bank<=0) {
			$this->addError('bank', '请选择开户行！');
		}
	}

	public function validateProvince() {
		if($this->province<=0) {
			$this->addError('province', '请选择开户行所在省！');
		}
	}

	public function validateCity() {
		if($this->city<=0) {
			$this->addError('city', '请选择开户行所在市！');
		}
	}

	public function validateSubbranch() {
		if(!preg_match('/^[\x{4e00}-\x{9fa5}]+$/u',$this->subbranch)){
			$this->addError('subbranch', '请输入正确的支行名称！');
		}
	}

	public function insert() {
		if($this->check()) {
			$user = $this->getUser();
			$bank = new UserBank();
			$bank->userId = $user->userId;
			$bank->bankUsername = $user->name;
			$bank->bank = $this->bank;
			$bank->bankNum = $this->bankNum;
			$bank->city = $this->city;
			$bank->province = $this->province;
			$bank->subbranch = $this->subbranch;
			$bank->isDefault = $this->isDefault;
			$bank->createAt = date('Y-m-d H:i:s');
			$bank->updateAt = date('Y-m-d H:i:s');
			$bank->isDefault = $this->isDefault;

			if($bank->save()) {
				if($this->isDefault=='y') {
					UserBank::where('userId', $user->userId)->where('bankNum', '<>', $this->bankNum)->update(['isDefault'=>'n']);
				}
				return true;
			} else {
				$this->addError('bank', '添加失败！');
				return false;
			}
		} else {
			return false;
		}
	}
}