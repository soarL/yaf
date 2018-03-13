<?php
namespace forms;
use models\Recharge;
use models\UserBank;
use models\User;
use models\RechargeAgree;
use helpers\StringHelper;
use helpers\NetworkHelper;
use Yaf\Registry;
use plugins\lianlian\lib\LLpaySubmit;
use plugins\lianlian\lib\Core;
use plugins\lianlian\Config as LLConfig;
use tools\MSBank;
use tools\BFBank;
use tools\Log;

class OpenBaofooForm extends \Form {
	public $formHtml = '';
	public $adviceUrl = '';
	public $returnUrl = '';
	public $result;
	public $apitype = 'sdk';

	public function rules() {
		return [
            [['cardnum', 'name', 'bankNum'], 'required'],
            ['cardnum', 'idCard'],
            ['cardnum', 'validateCardnum'],
			['bankNum', 'validateBankCard'],
            //['paypass', 'validatePassword']
		];
	}

	public function queue() {
		return [
			'required', 'validateBankCode', 'validateMoney'
		];
	}

	public function labels() {
		return [
            "name" => '真实姓名',
            "cardnum" =>   '身份证',
            "bankNum" => '银行卡号',
            "card_type" =>  '类型',
            "valid_date" => '银行卡有效期',
            "cvv" => '银行卡安全码',
            "sms_code" =>   '验证码',
            "paypass" => '交易密码'
        ];
	}

    public function validatePassword() {
        if(strlen($this->paypass)<6) {
            $this->addError('paypass', '支付密码长度不能小于6位！');
        }
    }

    public function validateCardnum() {
        $user = $this->getUser();
        if(User::isIDCardExist($this->cardnum, $user->userId)) {
            $this->addError('cardnum', '身份证号已存在！'); return;
        }
        $age = StringHelper::getAgeByBirthday(StringHelper::getBirthdayByCardnum($this->cardnum));
        if($age<18) {
            $this->addError('cardnum', '未满18周岁不能开户！'); return;
        }
        $status = true;
        // $status = API::identify(['name'=>$this->realname, 'cardnum'=>$this->cardnum]);
        if(!$status) {
            $this->addError('cardnum', '实名认证失败！'); return;
        }
    }

	public function validateBankCard() {
		if(!$this->hasErrors()) {
			if($this->bankNum==null||$this->bankNum=='') {
				$this->addError('bankNum', '请输入银行卡号！');
			}
		}
	}

    public function open1() {
        if($this->check()) {
            return $this->openBaofoo1();
        } else {
            return false;
        }
    }

    public function open2() {
        if($this->check()) {
            return $this->openBaofoo2();
        } else {
            return false;
        }
    }

	private function openBaofoo1() {
		$user = $this->getUser();
		
		$terminal_id = Registry::get('config')->get('baofoo')->get('wid');
		$member_id = Registry::get('config')->get('baofoo')->get('member_id');
		$baseUrl = Registry::get('config')->get('baofoo')->get('url');
		$password = Registry::get('config')->get('baofoo')->get('hc_key_pw');
		$url = $baseUrl .'/cutpayment/api/backTransRequest';

        $fee = 0;
        $userId = $user->userId;
        $nickname = $userId;
        $amount = $this->money;
        $baseUrl = WEB_MAIN;
        $adviceUrl = $this->adviceUrl;
        $returnUrl = $this->returnUrl;
        if($adviceUrl=='') {
        	$adviceUrl = $baseUrl . '/application/openBfNotify';
        }
        if($returnUrl=='') {
        	$returnUrl = $baseUrl . '/application/openBfReturn';
        }

        $serialNumber = date('Ymd').substr(md5(microtime().$userId), 8, 16).rand(10, 99);

        $bankData = file_get_contents('https://ccdcapi.alipay.com/validateAndCacheCardInfo.json?_input_charset=utf-8&cardNo='.$this->bankNum.'&cardBinCheck=true');
        $bankData = json_decode($bankData,true);

        if(!isset($bankData['bank'])){
            $bankData['bank'] = '';
        }

    	$contents = [
    		'txn_sub_type' => '11',
    		'biz_type' => '0000',
    		'terminal_id' => $terminal_id,
            'member_id' => $member_id,
    		'trans_serial_no' => $serialNumber,
            'trans_id' => $serialNumber,
            'acc_no' => $this->bankNum,

            // 'card_type' => $this->card_type,
            'id_card' => $this->cardnum,
            'id_card_type' => '01',
            'id_holder' => $this->name,
            'pay_code' => $bankData['bank'],
            //'id_holder' => $user->name,
            'trade_date' => date('YmdHis'),
            'mobile' => $user->phone,

            // 'valid_date' => $this->valid_date,
            // 'valid_no' => $this->cvv,
            // 'txn_amt' => _yuan2fen($amount),
            // 'user_id' => $user->userId,
            //'commodity_name' => '',
            //'commodity_amount' => '',
            //'user_name' => '',
            // 'return_url' => $adviceUrl,
            // 'additional_info' => json_encode($agreeInfo),
    	];
    	
    	Log::write('contents', $contents, 'debug', 'DEBUG');
    	$privateKey = BFBank::getKey('private', 'hc');
    	$content = StringHelper::bfSign(json_encode($contents), $privateKey, $password);
    	$params = [
			'version' => '4.0.0.0',
			// 'input_charset' => 1,
			//'language' => 1,
			'terminal_id' => $terminal_id,
			'txn_type' => '0431',
			'txn_sub_type' => '11',
			'member_id' => $member_id,
			'data_type' => 'json',
			'data_content' => $content
		];
		$this->baofooSdkPost($url, $params);

		return true;
	}

	private function openBaofoo2() {
		$user = $this->getUser();
		
		$terminal_id = Registry::get('config')->get('baofoo')->get('wid');
		$member_id = Registry::get('config')->get('baofoo')->get('member_id');
		$baseUrl = Registry::get('config')->get('baofoo')->get('url');
		$password = Registry::get('config')->get('baofoo')->get('hc_key_pw');
		$url = $baseUrl .'/cutpayment/api/backTransRequest';

        $fee = 0;
        $userId = $user->userId;
        $nickname = $userId;
        $amount = $this->money;
        $baseUrl = WEB_MAIN;
        $adviceUrl = $this->adviceUrl;
        $returnUrl = $this->returnUrl;
        if($adviceUrl=='') {
        	$adviceUrl = $baseUrl . '/application/openBfNotify';
        }
        if($returnUrl=='') {
        	$returnUrl = $baseUrl . '/application/openBfReturn';
        }

        $serialNumber = date('Ymd').substr(md5(microtime().$userId), 8, 16).rand(10, 99);

    	$contents = [
            'txn_sub_type' => '12',
            'biz_type' => '0000',
    		'terminal_id' => $terminal_id,
    		'member_id' => $member_id,
    		'trans_serial_no' => $serialNumber,
            'trans_id' => $this->unique_code,
            'sms_code' => $this->sms_code,
            'trade_date' => date('YmdHis'),
    	];
    	
    	Log::write('contents', $contents, 'debug', 'DEBUG');
    	$privateKey = BFBank::getKey('private', 'hc');
    	$content = StringHelper::bfSign(json_encode($contents), $privateKey, $password);
    	$params = [
			'version' => '4.0.0.0',
			// 'input_charset' => 1,
			//'language' => 1,
			'terminal_id' => $terminal_id,
			'txn_type' => '0431',
			'txn_sub_type' => '12',
			'member_id' => $member_id,
			'data_type' => 'json',
			'data_content' => $content
		];
		$this->baofooSdkPost($url, $params);

		return true;
	}

	private function baofooSdkPost($url, $data) {
		Log::write($url, [], 'debug', 'DEBUG');
		Log::write('data', $data, 'debug', 'DEBUG');
		$return = NetworkHelper::postTwo($url, $data);
        // var_dump($return);exit;
		// $ReturnCountent = array();
		// parse_str($return, $ReturnCountent);
        // var_dump($ReturnCountent);exit;
        $publicKey = BFBank::getKey('public', 'hc');
        $result = StringHelper::bfVerify($return, $publicKey);
		$this->result = json_decode($result, true);
		$this->result['resp_msg'] = StringHelper::bfReMsg($this->result['resp_msg']);
	}


}
