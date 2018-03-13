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
use helpers\StringHelper;
use tools\Banks;
use tools\BFBank;
use tools\Areas;
use tools\Log;
class WithdrawFormOld extends \Form {
	public $fee = 0;
	public $transData = '';
	public $adviceURL = '';
	public $lottery;

	public function defaults() {
		return ['isLottery'=>0];
	}

	public function rules() {
		return [
			[['money', 'bank'], 'required'],
			['paypass', 'validatePaypass'],
			['isLottery', 'validateLottery'],
			['bank', 'validateBank'],
			['money', 'validateMoney'],
		];
	}

	public function labels() {
		return [
        	'money' => '金额',
			'paypass' => '支付密码',
        	'bank' => '银行账户',
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

	public function validateMoney() {
		$user = $this->getUser();
		$fundMoney = $user->fundMoney;
		if($fundMoney < $this->money) {
			$this->addError('money', '该卡最多可转出'.$this->money.'元！'); return;
		}
		if($this->lottery) {
			$this->fee = $user->getWithdrawFee($this->money, true);
		} else {
			$this->fee = $user->getWithdrawFee($this->money, false);
		}
		$this->useInvestMoney = $user->useInvestMoney;
		if($fundMoney <= $this->fee) {
			$this->addError('money', '该卡最多可转出'.$money.'元！'); return;
		}
		if($this->money-$this->fee<1) {
			$this->addError('money', '实得金额不得小于1元！'); return;
		}
		if($this->money > 3000000) {
			$this->addError('money', '单笔提现不得超过300万！'); return;
		}
	}

	public function validateBank() {
		$user = $this->getUser();
		if(!UserBank::isUserBankExist($this->bank, $user->userId)) {
			$this->addError('bank', '您不存在该银行账户！');
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

	public function withdraw() {
		$user = $this->getUser();
		$bank = UserBank::where('userId', $user->userId)->where('status', '1')->first();
		$this->bank = $bank->id;

		if($this->check()) {
			$bankInfo = UserBank::getBankInfo($this->bank);

			if(!$bankInfo) {
				$this->addError('bank', '银行卡不存在或者银行卡信息不全！');
				return false;
			}

			$remark = '用户提现';
			$realMoney = $this->money - $this->fee;
			
			$withdraw = new Withdraw();
			$withdraw->useInvestMoney = $this->useInvestMoney;
			$withdraw->userId = $user->userId;
			$withdraw->bank = $bankInfo['bank'];
			$withdraw->province = $bankInfo['province'];
			$withdraw->city = $bankInfo['city'];
			$withdraw->subbranch = $bankInfo['subbranch'];
			$withdraw->bankNum = $bankInfo['bankNum'];
			$withdraw->bankUsername = $bankInfo['bankUsername'];
			$withdraw->remark = $remark;
			$withdraw->outMoney = $this->money;
			$withdraw->fee = $this->fee;
			$withdraw->status = 0;
			$withdraw->addTime = date('Y-m-d H:i:s');
			$withdraw->media = $this->getMedia();
			if($this->lottery) {
				$withdraw->lotteryId = $this->lottery->id;
				$this->lottery->status = Lottery::STATUS_USED;
				$this->lottery->used_at = date('Y-m-d H:i:s');
				$this->lottery->save();
			}
			
			$tradeNo = date('Ymd').substr(md5(microtime().$this->money.$user->userId), 8, 16).rand(10,99);
			$withdraw->tradeNo = $tradeNo;

			$xmlData = [];
	    	$xmlData['tradeNo'] = $tradeNo;
	    	$xmlData['bank'] = $bankInfo['bank'];
	    	$xmlData['province'] = $bankInfo['province'];
	    	$xmlData['city'] = $bankInfo['city'];
	    	$xmlData['subbranch'] = $bankInfo['subbranch'];
	    	$xmlData['user'] = $user;
	    	$xmlData['bankNum'] = $bankInfo['bankNum'];
	    	$xmlData['fee'] = $this->fee;
	    	$xmlData['remark'] = $remark;
	    	$xmlData['realMoney'] = $realMoney;
			$transData = $this->getBaofooData($xmlData);
			$withdraw->xml = json_encode($transData);
			if($withdraw->save()&&$user->updateAfterWithdrawF($this->money)) {
				//$user->userType==1 提现审核
				if(0) {
			        $code = $this->baofooSdkPost($transData);
			        $withdraw->result = $code;
			        $withdraw->save();

			        $data['tradeNo'] = $tradeNo;
			        $data['result'] = $code;
			        if($code == '0000'){
			        	$data['status'] = 1;
			        }else{
			        	$data['status'] = 0;
			        	$this->addError('form', $this->result['trans_content']['trans_head']['return_msg']);
			    	}
			    	Withdraw::after($data);
			    	return $data['status'];
		        } else {
					$withdraw->status = 3;
					$withdraw->save();
		        	return true;
		        }
			} else {
				$this->addError('form', '提现失败！');
				return false;
			}
		} else {
			return false;
		}
	}

	public function withdrawSearch() {
			$data = [];
	    	//$data['trans_batchid'] = $_GET['trans_batchid'];
	    	$data['trans_no'] = $_GET['trans_no'];

			$terminal_id = Registry::get('config')->get('baofoo')->get('pay_wid');
			$member_id = Registry::get('config')->get('baofoo')->get('pay_member_id');
			$password = Registry::get('config')->get('baofoo')->get('pay_hc_key_pw');

	    	$trans_reqData = [
		 	 'trans_no' => $data['trans_no'], 
			 'trans_batchid' => $data['trans_batchid'], 
			];

	    	$contents = [
	    		'trans_content' => ['trans_reqDatas'=>[['trans_reqData'=>[$trans_reqData]]]]
	    	];
	    	Log::write('contents', $contents, 'debug', 'DEBUG');
	    	$privateKey = BFBank::getKey('private', 'pay_hc');
	    	$content = StringHelper::bfSign(json_encode($contents), $privateKey, $password);
	    	$transData = [
				'version' => '4.0.0',
				'terminal_id' => $terminal_id,
				'member_id' => $member_id,
				'data_type' => 'json',
				'data_content' => $content
			];

			$baseUrl = Registry::get('config')->get('baofoo')->get('pay_url');
			$url = $baseUrl . '/baofoo-fopay/pay/BF0040002.do';
			Log::write($url, [], 'debug', 'DEBUG');
			$return = NetworkHelper::postTwo($url, $transData);
			$publicKey = BFBank::getKey('public', 'pay_hc');
			$result = StringHelper::bfVerify($return, $publicKey);
			Log::write('data', [$result], 'debug', 'DEBUG');
			$this->result = json_decode($result, true);
			var_dump($this->result);exit;
	}

	private function getBaofooData($data) {
		$terminal_id = Registry::get('config')->get('baofoo')->get('pay_wid');
		$member_id = Registry::get('config')->get('baofoo')->get('pay_member_id');
		$password = Registry::get('config')->get('baofoo')->get('pay_hc_key_pw');

    	$trans_reqData = [
	 	 'trans_no' => $data['tradeNo'], 
		 'trans_money' => $data['realMoney'], 
		 'to_acc_name' => $data['user']->name,
		 'to_acc_no' => $data['bankNum'],
		 'to_bank_name' => $data['bank'],
		 'trans_card_id' => $data['user']->cardnum,
		 'trans_mobile' =>$data['user']->phone,
		];

		if($data['province']){
			$trans_reqData['to_pro_name'] = $data['province'];
			$trans_reqData['to_city_name'] = $data['city'];
			$trans_reqData['to_acc_dept'] = $data['subbranch'];
		}

    	$contents = [
    		'trans_content' => ['trans_reqDatas'=>[['trans_reqData'=>[$trans_reqData]]]]
    	];
    	Log::write('contents', $contents, 'debug', 'DEBUG');
    	$privateKey = BFBank::getKey('private', 'pay_hc');
    	$content = StringHelper::bfSign(json_encode($contents), $privateKey, $password);
    	$params = [
			'version' => '4.0.0',
			'terminal_id' => $terminal_id,
			'member_id' => $member_id,
			'data_type' => 'json',
			'data_content' => $content
		];
		return $params;
	}

	public function baofooSdkPost($data) {
		$baseUrl = Registry::get('config')->get('baofoo')->get('pay_url');
		$url = $baseUrl . '/baofoo-fopay/pay/BF0040001.do';
		Log::write('postdata', [$data], 'baofoo', 'DEBUG');
		$return = NetworkHelper::postTwo($url, $data);
		$publicKey = BFBank::getKey('public', 'pay_hc');
		Log::write('data', [$return], 'baofoo', 'DEBUG');
		$result = StringHelper::bfVerify($return, $publicKey);
		Log::write('data', [$result], 'baofoo', 'DEBUG');
		$this->result = json_decode($result, true);
		return $this->result['trans_content']['trans_head']['return_code'];
	}

	private function getTransData($data) {
		$adviceURL = $this->adviceURL;
    	if($adviceURL=='') {
    		$adviceURL = WEB_MAIN.'/application/withdrawBack';
    	}
		$numberId = Registry::get('config')->get('third')->get('number_id');
		$merchantKey = Registry::get('config')->get('third')->get('key');
    	$secureCode = strtolower(md5($numberId.$data['tradeNo'].$data['bank'].$data['province']
    		.$data['city'].$data['subbranch'].$data['userId'].$data['bankNum'].$data['realMoney']
    		.$data['fee'].$data['remark'].$merchantKey));

		$transData  =  "";
        $transData .= '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $transData .= '<yimadai>';
        $transData .=   '<accountNumber>'.$numberId.'</accountNumber>';//商户数字id
        $transData .=   '<adviceURL>'.$adviceURL.'</adviceURL>';
        $transData .=   '<transfer>';
        $transData .=   	'<outTradeNo>'.$data['tradeNo'].'</outTradeNo>';
        $transData .=   	'<bankName>'.$data['bank'].'</bankName>';
        $transData .=   	'<provice>'.$data['province'].'</provice>';
        $transData .=   	'<city>'.$data['city'].'</city>';
        $transData .=   	'<branchName>'.$data['subbranch'].'</branchName>';
        $transData .=   	'<nickName>'.$data['userId'].'</nickName>';
        $transData .=   	'<cardNo>'.$data['bankNum'].'</cardNo>';
        $transData .=   	'<amount>'.$data['realMoney'].'</amount>';
        $transData .=   	'<fee>'.$data['fee'].'</fee>';
        $transData .=   	'<remark>'.$data['remark'].'</remark>';
        $transData .=   	'<secureCode>'.$secureCode.'</secureCode>';
        $transData .=   '</transfer>';
        $transData .= '</yimadai>';
        return $transData;
	}
}