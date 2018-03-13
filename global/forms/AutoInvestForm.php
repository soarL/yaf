<?php
namespace forms;
use models\AutoInvest;
use Yaf\Registry;
use tools\Redis;
use models\Lottery;
class AutoInvestForm extends \Form {
	public $lottery;

	public function defaults() {
		return [
			'periods' => [],
			'staystatus' => 0,
			'investEgisMoney' => 0,
			'moneyType' => 1,
			'fixedMoney' => 0,
			'rangeBegin' => 0,
			'rangeEnd' => 0,
			'types' => [],
			'mode' => 0,
			'lotteryID' => 0,
		];
	}

	public function init() {
		if($this->lotteryID!=0) {
			$this->lottery = Lottery::where('id', $this->lotteryID)->first();
		}
	}

	public function rules() {
		return [
			[['autostatus', 'staystatus', 'investEgisMoney', 'moneyType', 'rangeBegin', 'rangeEnd', 'fixedMoney', 'mode'], 'required'],
			['autostatus', 'enum', ['values'=>['0', '1']]],
			['autostatus', 'validateStatus'],
			['moneyType', 'enum', ['values'=>['0', '1']]],
			['autostatus', 'enum', ['values'=>['0', '1']]],
			['investEgisMoney', 'type', ['type'=>'number']],
			['fixedMoney', 'type', ['type'=>'int']],
			['rangeBegin', 'type', ['type'=>'int']],
			['rangeEnd', 'type', ['type'=>'int']],
			['rangeEnd', 'validateMoney'],
		];
	}

	public function labels() {
		return [
        	'autostatus' => '是否开启',
        	'staystatus' => '是否站队',
        	'investEgisMoney' => '账户保留金额',
        	'moneyType' => '投资金额',
        	'rangeBegin' => '最低投资金额',
        	'rangeEnd' => '最高投资金额',
        	'fixedMoney' => '指定投资金额',
        	'types' => '投资类型',
        	'lotteryID' => '可用奖券',
        ];
	}

	public function validateStatus() {
		$user = $this->getUser();
		// if($user->auto_bid_auth=='') {
		// 	$this->addError('autostatus', '您还未进行自动投标签约'); return;
  //       }
        
		$key = Redis::getKey('autoInvesting');
        $ing = Redis::get($key);
        if($ing) {
            $this->addError('autostatus', '目前正在进行自动投标，请稍后再设置！');
        }
	}

	public function validateMoney() {
		if($this->moneyType==1) {
			if($this->rangeBegin>$this->rangeEnd) {
				$this->addError('rangeEnd', '最低投资金额不能大于最高投资金额'); return;
			}
			if($this->rangeBegin<50) {
				$this->addError('rangeBegin', '最低投资金额不能小于50'); return;
			}
			if($this->rangeEnd<50) {
				$this->addError('rangeEnd', '最高投资金额不能不能小于50'); return;
			}
			if($this->rangeBegin>=1000000) {
				$this->addError('rangeEnd', '最低投资金额需要小于100万'); return;
			}
			if($this->rangeEnd>=1000000) {
				$this->addError('rangeEnd', '最高投资金额需要小于100万'); return;
			}
			if($this->rangeBegin%50!=0) {
				$this->addError('rangeBegin', '最低投资金额需要为50的倍数'); return;
			}
			if($this->rangeEnd%50!=0) {
				$this->addError('rangeBegin', '最高投资金额需要为50的倍数'); return;
			}
		} else {
			if($this->fixedMoney<50) {
				$this->addError('fixedMoney', '指定投资金额不能不能小于50'); return;
			}
			if($this->fixedMoney>=1000000) {
				$this->addError('fixedMoney', '指定投资金额需要小于100万'); return;
			}
			if($this->fixedMoney%50!=0) {
				$this->addError('fixedMoney', '指定投资金额需要为50的倍数'); return;
			}
		}
		if($this->investEgisMoney<0) {
			$this->addError('investEgisMoney', '账户保留金额不能不能小于0'); return;
		}
	}

	public function update() {
		if($this->check()) {
			$user = $this->getUser();

			$autoInvest = AutoInvest::where('userId', $user->userId)->first();
			$beforeStatus = 0;
			if(!$autoInvest) {
				$autoInvest = new AutoInvest();
				$autoInvest->addtime = date('Y-m-d H:i:s');
				$autoInvest->userId = $user->userId;
			} else {
				$beforeStatus = $autoInvest->autostatus;
			}
			if($beforeStatus != $this->autostatus){
				$autoInvest->status = $this->autostatus;
			}

			if($this->autostatus == 0){
				$autoInvest->total = 0;
				$autoInvest->successMoney = 0;
			}

			$autoInvest->autostatus = $this->autostatus;
			$autoInvest->staystatus = $this->staystatus;
			// 投资金额范围
			if($this->moneyType==1) {
				$autoInvest->investMoneyUper = $this->rangeEnd;
				$autoInvest->investMoneyLower = $this->rangeBegin;
			} else {
				$autoInvest->investMoneyUper = $this->fixedMoney;
				$autoInvest->investMoneyLower = $this->fixedMoney;
			}

			if($autoInvest->lottery_id>0 && $this->lottery_id!=$autoInvest->lottery_id) {
                // 若上次的奖券还没使用
                $useLottery = Lottery::where('id', $autoInvest->lottery_id)->first();
                if($useLottery->status==Lottery::STATUS_FROZEN) {
                    Lottery::where('id', $useLottery->id)->update(['status'=> Lottery::STATUS_NOUSE]);
                }
            }

            if($this->lottery_id>0 && $autoInvest->lottery_id!=$this->lottery_id) {
                Lottery::where('id', $this->lotteryID)->update(['status'=>Lottery::STATUS_FROZEN]);
            }
            $autoInvest->lottery_id = $this->lottery_id;

			$autoInvest->investEgisMoney = $this->investEgisMoney;
			$autoInvest->moneyType = $this->moneyType;
			$autoInvest->types = implode('#', $this->types);
			if($autoInvest->types!='') {
				$autoInvest->types = '#'.$autoInvest->types .'#';
			}

			$key = Redis::getKey('autoInvestQueue');
			if($beforeStatus==0&&$this->autostatus==1) {
				Redis::lRem($key, $user->userId, 0);
				Redis::lPush($key, $user->userId);
			} else if($this->autostatus==0) {
				Redis::lRem($key, $user->userId, 0);
			}

			$autoInvest->mode = $this->mode;

			if($autoInvest->save()) {
				return true;
			} else {
				$this->addError('form', '设置失败！');
				return false;
			}

		} else {
			return false;
		}
	}
}