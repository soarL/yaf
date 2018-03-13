<?php
namespace forms;
use Yaf\Registry;
use models\OddMoney;
use models\Invest;
use models\Sms;
use models\User;
use custody\API;

/**
 * TransferForm
 * 债权转让表单
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class TransferForm extends \Form {
	public $oddNumber;
	public $oddMoney;

	public function rules() {
		return [
			[['oddMoneyId', 'paypass'], 'required'],
			['oddMoneyId', 'validateOddMoney'],
			//['smsCode', 'validateSmsCode'],
			['paypass', 'validatePaypass']
		];
	}

	public function labels() {
		return [
        	'oddMoneyId' => '投标记录',
        	'paypass' => '交易密码',
        ];
	}

	// public function validateSmsCode() {
	// 	$user = $this->getUser();
 //        $result = Sms::checkCode($user->phone, $this->smsCode, 'transfer');
 //        if($result['status']==0) {
 //            $this->addError('smsCode', $result['info']);
 //        }
 //    }

	public function validatePaypass(){
        $user = $this->getUser();
        $res = User::paypassNormal($user, $this->paypass);
        if($res['status']){

        }else{
            $this->addError('paypass', $res['info']); return;
        }
    }

	public function validateOddMoney() {
		$user = $this->getUser();
		$oddMoney = OddMoney::where('id', $this->oddMoneyId)->where('userId', $user->userId)->first();
		if(!$oddMoney) {
			$this->addError('oddMoneyId', '该投标不存在！');
		}
		
		$this->oddMoney = $oddMoney;

		if(!$oddMoney->canTransfer()) {
			$this->addError('oddMoneyId', '该投标不能转让！');
		}

		if($this->oddMoney->remain<50) {
			$this->addError('oddMoneyId', '转让金额小于50元，不可转让！');
		}
	}

	public function transfer() {
		if($this->check()) {
			$user = $this->getUser();

			$result = API::transfer($this->oddMoney);

			if($result['status']) {
				$data = [];
				$data['userId'] = $user->userId;
	        	$data['oddmoneyId'] = $this->oddMoneyId;
	        	$data['oddNumber'] = $this->oddNumber;
				$status = $this->oddMoney->transfer($result['requestNo'], $this->getMedia());
				if($status) {
					return true;
				} else {
					$this->addError('form', '转让失败！');
					return false;
				}
			} else {
				$this->addError('form', '存管债权转让失败！');
				return false;
			}
		} else {
			return false;
		}
	}
}