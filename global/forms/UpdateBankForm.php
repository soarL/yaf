<?php
namespace forms;
use Yaf\Registry;
use models\UserBank;
class UpdateBankForm extends \Form {

	public $bankAccount;

	public function rules() {
		return [
			[['id','bankUsername', 'bankNum', 'bank', 'province', 'city', 'subbranch', 'isDefault'], 'required'],
			['id', 'validateBankId'],
			// ['bankUsername', 'chineseName'],
			['bankNum', 'validateBankNum'],
			['bank', 'validateBank'],
			['province', 'validateProvince'],
			['city', 'validateCity'],
			['subbranch', 'validateSubbranch'],
			// ['paypass', 'validatePaypass'],
			['isDefault', 'enum', ['values'=>['y', 'n']]],
		];
	}

	public function labels() {
		return [
        	'bankUsername' => '开户人',
			'bankNum' => '银行卡号',
        	'bank' => '开户行',
        	'province' => '开户行所在省',
        	'city' => '开户行所在市',
        	'subbranch' => '支行名称',
        	// 'paypass' => '支付密码',
        	'isDefault' => '是否默认',
        ];
	}

	public function validateBankId() {
		$user = $this->getUser();
		$bankAccount = UserBank::where('userId', $user->userId)->where('id', $this->id)->first();
		if($bankAccount) {
			$this->bankAccount = $bankAccount;
		} else {
			$this->addError('id', '银行账户错误！');
		}
	}

	public function validateBankNum() {
		if(strlen($this->bankNum)>15) {
			if(!preg_match("/^\d+$/",$this->bankNum)){
				$this->addError('bankNum', '请填写正确的银行卡号！'); return;
			} else {
				$bankAccount = $this->bankAccount;
				$user = $this->getUser();
				if($bankAccount->agreeID!=0) {
					$this->isDefault = 'y';
					if($bankAccount->bankNum!=$this->bankNum) {
						$this->addError('bankNum', '您使用了认证支付，卡号无法修改！'); return;
					}
					if($bankAccount->bank!=$this->bank) {
						$this->addError('bank', '您使用了认证支付，银行无法修改！'); return;
					}
				}
				if($bankAccount->bankNum!=$this->bankNum) {
					if(UserBank::isUserBankNumExist($this->bankNum, $user->userId)) {
						$this->addError('bankNum', '您已填写过该银行卡号，无需重复填写！'); return;
					}
				}
			}
		} else {
			$this->addError('bankNum', '请填写正确的银行卡号！'); return;
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

	public function update() {
		if($this->check()) {
			$user = $this->getUser();
			$data = [];
			$data['bankUsername'] = $this->bankUsername;
			$data['bank'] = $this->bank;
			$data['bankNum'] = $this->bankNum;
			$data['city'] = $this->city;
			$data['province'] = $this->province;
			$data['subbranch'] = $this->subbranch;
			$data['isDefault'] = $this->isDefault;
			$data['updateAt'] = date('Y-m-d H:i:s');
			$data['isDefault'] = $this->isDefault;
			$status = UserBank::where('userId', $user->userId)->where('id', $this->id)->update($data);
			if($status) {
				if($this->isDefault=='y') {
					UserBank::where('userId', $user->userId)->where('id', '<>', $this->id)->update(['isDefault'=>'n']);
				}
				return true;
			} else {
				$this->addError('bank', '更新失败！');
				return false;
			}
		} else {
			return false;
		}
	}
}