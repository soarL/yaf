<?php
namespace forms;
use Yaf\Registry;
use models\User;
use models\UserBank;
use models\Withdraw;
use models\MoneyLog;
use models\OldData;
use models\Invest;
use models\OddMoney;
use models\Redpack;
use models\Lottery;
use helpers\NetworkHelper;
use tools\Banks;
use tools\Areas;
use tools\Log;
use tools\Redis;
use tools\Counter;
use custody\API;
class WithdrawForm extends \Form {
	public $fee = 0;
	public $html;
	public $lottery;

	public function defaults() {
		return [
			'isLottery' => 0,
		];
	}

	public function rules() {
		return [
			[['money'], 'required'],
			['isLottery', 'validateLottery'],
			['money', 'validateMoney'],
			['paypass', 'validatePaypass']
		];
	}

	public function labels() {
		return [
        	'money' => '金额',
        	'paypass' => '交易密码',
        ];
	}

	public function validateLottery() {
		$user = $this->getUser();
		if($this->isLottery==1) {
			$lottery = Lottery::where('userId', $user->userId)
				->where('status', Lottery::STATUS_NOUSE)
				->where('type', 'withdraw')
				->where('endtime', '>', time())
				->orderBy('endtime', 'asc')
				->first();
			if($lottery) {
				$this->lottery = $lottery;
			} else {
				$this->addError('isLottery', '提现券不存在！');
			}
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
		$key = Redis::getKey('autoInvesting');
        $ing = Redis::get($key);
        if($ing) {
            $this->addError('money', '目前正在进行自动投标，请稍后再提现！'); return;
        };

		$user = $this->getUser();
		$fundMoney = $user->fundMoney;
		if($fundMoney < $this->money) {
			$this->addError('money', '该卡最多可转出'.$fundMoney.'元！'); return;
		}
		if($this->lottery) {
			$this->fee = $user->getWithdrawFee($this->money, true);
		} else {
			$this->fee = $user->getWithdrawFee($this->money, false);
		}
		
		if($fundMoney <= $this->fee) {
			$this->addError('money', '该卡最多可转出'.$fundMoney.'元！'); return;
		}
		if($this->money-$this->fee<1) {
			$this->addError('money', '实得金额不得小于1元！'); return;
		}
		if($this->money > 3000000) {
			$this->addError('money', '单笔提现不得超过300万！'); return;
		}
	}

	public function withdraw() {
		if($this->check()) {

			$user = $this->getUser();
			$bank = UserBank::where('userId', $user->userId)->where('status', '1')->first();
			if(!$bank) {
				$this->addError('bank', '您还未绑定银行卡！');
				return false;
			}

			$remark = '用户提现';
			$realMoney = $this->money - $this->fee;
			$tradeNo = Counter::getOrderID();
			$withdraw = new Withdraw();
			$withdraw->tradeNo = $tradeNo;
			$withdraw->userId = $user->userId;
			$withdraw->bank = 0;
			$withdraw->province = 0;
			$withdraw->city = 0;
			$withdraw->subbranch = '';
			$withdraw->bankNum = $bank->bankNum;
			$withdraw->bankUsername = $bank->bankUsername;
			$withdraw->remark = $remark;
			$withdraw->outMoney = $this->money;
			$withdraw->fee = $this->fee;
			$withdraw->status = 0;
			$withdraw->addTime = date('Y-m-d H:i:s');
			$withdraw->media = $this->getMedia();
			if($this->lottery) {
				$withdraw->lotteryId = $this->lottery->id;
			}
			if($withdraw->save()) {
				$this->html = API::withdraw($withdraw);
				return true;
			} else {
				$this->addError('form', '系统异常！');
				return false;
			}
		} else {
			return false;
		}
	}
}