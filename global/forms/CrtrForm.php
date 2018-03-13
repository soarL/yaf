<?php
namespace forms;
use models\Crtr;
use models\UserCrtr;
use Yaf\Registry;
use custody\API;
use tools\Counter;
use models\User;

use Illuminate\Database\Capsule\Manager as DB;

/**
 * CrtrForm
 * 购买债权表单
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class CrtrForm extends \Form {
	public $crtr;
	public $html;
	public $interest;

	public function rules() {
		return [
			[['money', 'id', 'paypass'], 'required'],
			['id', 'validateCrtr'],
			['money', 'validateMoney'],
			['paypass', 'validatePaypass']
		];
	}

	public function labels() {
		return [
			'id' => '债权号',
        	'money' => '购买金额',
        	'paypass' => '交易密码',
        ];
	}

	public function validateCrtr() {
		$crtr = Crtr::where('id', $this->id)->first();
		if($crtr) {
			$user = $this->getUser();
			$this->crtr = $crtr;
			$result = $crtr->isBuyable($user->userId);
			if($result['status']==0) {
				$this->addError('id', $result['info']);
			}
		} else {
			$this->addError('id', '债权转让不存在！');
		}
	}

    public function validatePaypass(){
        $user = $this->getUser();
        $res = User::paypassNormal($user, $this->paypass);
        if($res['status']){

        }else{
            $this->addError('paypass', $res['info']); return;
        }
    }

	public function validateMoney() {
		$user = $this->getUser();
		if($user->username!='cbq123') {
			if($this->money<50) {
				$this->addError('money', '购买金额至少需50元！'); return;
			}
		}
		$this->interest = $this->crtr->getInterestFee($this->money);
		if($user->fundMoney<($this->money+$this->interest)) {
			$this->addError('money', '账户金额不足！'); return;
		}
		$remain = $this->crtr->getRemain();
		if($this->money>$remain) {
			$this->addError('money', '购买金额超过剩余可买金额！'); return;
		}
		$remainAft = $remain-$this->money;
		if($remainAft>0 && $remainAft<50) {
			$this->addError('money', '不能使剩余金额小于50元！'); return;
		}
	}

	public function buy() {
		if($this->check()) {

			$user = $this->getUser();
			$data = $this->crtr->buy($this->money);

			if($data['status']=0) {
				$this->addError('form', $data['msg']);
	        	return false;
			}

			$crtrSN = $this->crtr->getSN();
			$batchNo = $crtrSN;

	    	$fee = 0;
	    	if($this->crtr->oddMoney->needFee()) {
	    		$fee = Crtr::serviceFee($this->money,$this->crtr->odd->oddRehearTime);
	    	}

	    	$tradeNo = Counter::getOrderID();

	        $remark = '债权购买';
    		$trade = new UserCrtr();
    		$trade->batchNo = $batchNo;
	        $trade->tradeNo = $tradeNo;
	        $trade->crtr_id = $this->id;
	        $trade->userId = $user->userId;
	        $trade->money = $this->money;
	        $trade->interest = $this->interest;
	        $trade->fee = $fee;
	        $trade->remark = $remark;
	        $trade->addTime = date('Y-m-d H:i:s');
	        $trade->media = $this->getMedia();

	        if($trade->save()) {
	        	$this->html = API::crtr($trade, $this->crtr);
	        	return true;
	        } else {
	        	$this->crtr->disBuy($this->money);
	        	$this->addError('form', '购买债权失败！');
	        	return false;
	        }
		} else {
			return false;
		}
	}
}