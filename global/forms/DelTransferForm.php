<?php
namespace forms;
use Yaf\Registry;
use models\OddMoney;
use models\Crtr;
use models\User;
use Illuminate\Database\Capsule\Manager as DB;
class DelTransferForm extends \Form {
	public $oddMoney;
	public $crtr;

	public function init() {
		DB::beginTransaction();
	}

	public function rules() {
		return [
			['oddMoneyId', 'required'],
			['oddMoneyId', 'validateOddMoney'],
			['paypass', 'validatePaypass']
		];
	}

	public function labels() {
		return [
        	'oddMoneyId' => '投标记录',
        	'paypass' => '交易密码',
        ];
	}

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
        	$this->addError('oddMoneyId', '债权不存在！'); return;
        } else {
        	$this->oddMoney = $oddMoney;
        }
        if($oddMoney->ckclaims!=-1) {
        	$this->addError('oddMoneyId', '该笔转让不存在！'); return;
        }

        $crtr = Crtr::where('oddmoneyId', $oddMoney->id)->lock()->orderBy('addtime', 'desc')->first();
        if(!$crtr) {
        	$this->addError('oddMoneyId', '该笔转让不存在！'); return;
        } else {
        	$this->crtr = $crtr;
        }
        if($crtr->progress!='start') {
        	$this->addError('oddMoneyId', '该笔转让不可撤销！'); return;
        }
        if($crtr->getRemain()!=$crtr->money) {
        	$this->addError('oddMoneyId', '该笔转让已有购买，不可撤销！'); return;
        }
	}

	public function delete() {
		if($this->check()) {
			$this->crtr->progress = 'fail';
	        $this->crtr->endtime = date('Y-m-d H:i:s');
	        $status1 = $this->crtr->save();

	        $this->oddMoney->ckclaims = 0;
	        $status2 = $this->oddMoney->save();

			if($status1&&$status2) {
				DB::commit();
				return true;
			} else {
				DB::rollback();
				$this->addError('form', '转让失败！');
				return false;
			}
		} else {
			DB::rollback();
			return false;
		}
	}
}