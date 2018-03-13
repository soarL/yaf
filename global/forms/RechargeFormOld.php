<?php
namespace forms;
use models\Recharge;
use models\UserBank;
use models\MoneyLog;
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

class RechargeFormOld extends \Form {
	public static $payTypes = ['yemadai','lianlian', 'minsheng', 'baofoo', 'fuiou'];
	public $formHtml = '';
	public $adviceUrl = '';
	public $returnUrl = '';
	public $result;
	public $apitype = 'pc';

	public function rules() {
		return [
			[['money', 'payType', 'payWay'], 'required', 'isStrict'=>true],
			//['bankCard', 'value'=>''],// 'default',
			['payType', 'validatePayType'],
			['money', 'validateMoney'],
			['payWay', 'validatePayWay'],
			['bankCard', 'validateBankCard'],
			['bankCode', 'validateBankCode'],
		];
	}

	public function queue() {
		return [
			'required', 'validateBankCode', 'validateMoney'
		];
	}

	public function labels() {
		return [
        	'money' => '充值金额',
        	'payType' => '充值方式',
        	'bankCard' => '银行卡号'
        ];
	}

	public function validateMoney() {
		if(!$this->hasErrors()) {
			if(!is_numeric($this->money)) {
				$this->addError('money', '充值金额必须为数字！');
            } else {
            	if($this->money<100) {
	            	$this->addError('money', '充值金额不能小于100元！');
	            } else {
	            	if($this->money>5000000) {
	            		$this->addError('money', '单笔充值不能超过500万！');
	            	}	
	            }
	            /*if($this->payType=='lianlian'&&$this->payWay=='D') {
	            	if($this->money<500) {
	            		$this->addError('money', '认证支付每笔充值不能少于500元！');
	            	}
	            }*/
	            if($this->payType=='minsheng') {
	            	$payLimit = 0;
	            	if($this->payWay==1) {
	            		$payLimit = MSBank::$banks[$this->bankCode]['dcl'];
	            	} else if($this->payWay==2) {
	            		$payLimit = MSBank::$banks[$this->bankCode]['ccl'];
	            	} else if($this->payWay==3) {
	            		$payLimit = MSBank::$banks[$this->bankCode]['bcl'];
	            	}
	            	if($this->money<$payLimit) {
	            		$this->addError('money', '该充值方式每笔充值不能少于' . $payLimit . '元！');
	            	}
	            }
        	}
		}
	}

	public function validatePayType() {
		if(!$this->hasErrors()) {
			if(!in_array($this->payType, self::$payTypes)) {
				$this->addError('payType', '充值方式不存在！');
			}
			// 暂时关闭宝付
			if($this->payType=='baofoo') {
				//$this->addError('payType', '该充值渠道暂时关闭，请使用其他渠道！');
			}
			
			/*if($this->apitype=='sdk') {
				$this->addError('payType', '手机APP充值暂时关闭，请使用网页端充值！');
			}*/
		}
	}

	public function validateBankCard() {
		if(!$this->hasErrors()) {
			if($this->payType=='lianlian'&&$this->payWay=='D') {
				if($this->noAgree!='') {

				} else {
					if($this->bankCard==null||$this->bankCard=='') {
						$this->addError('bankCard', '请输入银行卡号！');
					}
				}
			} else if($this->payType=='baofoo'&&$this->payWay==3) {
				if($this->noAgree!='') {

				} else {
					if($this->bankCard==null||$this->bankCard=='') {
						$this->addError('bankCard', '请输入银行卡号！');
					}
				}
			} else if($this->payType=='fuiou'&&$this->payWay=='02') {
				if($this->noAgree!='') {

				} else {
					if($this->bankCard==null||$this->bankCard=='') {
						$this->addError('bankCard', '请输入银行卡号！');
					}
				}
			}
		}
	}

	public function validateBankCode() {
		if(!$this->hasErrors()) {
			if($this->payType=='minsheng') {
				if($this->bankCode==null||$this->bankCode=='') {
					$this->addError('bankCode', '请选择银行！');
				}
			}
			if($this->payType=='baofoo'&&$this->payWay==3) {
				if($this->bankCode==null||$this->bankCode=='') {
					$this->addError('bankCode', '请选择银行！');
				}
			}
		}
	}

	public function validatePayWay() {
		if(!$this->hasErrors()) {
			if($this->payType=='lianlian') {
				if($this->payWay!='1'&&$this->payWay!='D'&&$this->payWay!='8') {
					$this->addError('payWay', '请选择支付方式！');
				}
				if($this->payWay=='1') {
					$this->bankCard = '';
					$this->noAgree = '';
				}
			}
		}
	}

	public function recharge() {
		$this->payType = 'baofoo';
		if($this->check()) {
			if($this->payType=='yemadai') {
				return $this->yemadaiRecharge();
			} else if($this->payType=='lianlian') {
				return $this->lianlianRecharge();
			} else if($this->payType=='minsheng') {
				return $this->minshengRecharge();
			} else if($this->payType=='baofoo') {
				if($this->payWay=='SWIFT') {
					return $this->baofooPreRecharge();
				} else {
					return $this->baofooWebRecharge();
				}
			} else if($this->payType=='fuiou') {
				return $this->fuiouRecharge();
			} else {
				$this->addError('payType', '充值方式不存在！');
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * 一麻袋支付
	 * @return boolean 是否成功
	 */
	private function yemadaiRecharge() {
		$numberId = Registry::get('config')->get('third')->get('number_id');
		$user = $this->getUser();
        $fee = 0;
        $userId = $user->userId;
        $nickname = $userId;
        $amount = $this->money;
        $remark='在线充值';
        $merchantKey = Registry::get('config')->get('third')->get('key');

        $recharge = new Recharge();
        $recharge->serialNumber = date('Ymd').substr(md5(microtime().$userId.$amount), 8, 16).rand(10, 99);
        $recharge->userId = $userId;
        $recharge->mode = 'in';
        $recharge->money = $amount;
        $recharge->fee = $fee;
        $recharge->status = 0;
        $recharge->time = date('Y-m-d H:i:s');
        $recharge->operator = $userId;
        $recharge->payType = $this->payType;
        $recharge->remark = $remark;
        $recharge->source = 1;
        $recharge->payWay = 1;
        $recharge->media = $this->getMedia();

        if($recharge->save()) {
        	$tradeNo = $recharge->serialNumber;
	        $baseUrl = WEB_MAIN;
	        $adviceUrl = $this->adviceUrl;
	        $returnUrl = $this->returnUrl;
	        if($adviceUrl=='') {
	        	$adviceUrl = $baseUrl . '/application/rechargeAdvice';
	        }
	        if($returnUrl=='') {
	        	$returnUrl = $baseUrl . '/application/rechargeReturn';
	        }
	        $sign = "number_id=$numberId&out_trade_no=$tradeNo&amount=$amount&fee=$fee&nick_name=$nickname&advice_url=$adviceUrl&return_url=$returnUrl&remark=$remark&merchantKey=$merchantKey";
	        $sign = strtolower(md5($sign));
	        $url = Registry::get('config')->get('third')->get('base_url').'/hostingRecharge';

        	$postData = [];
        	$postData['numberId'] = $numberId;
        	$postData['tradeNo'] = $tradeNo;
        	$postData['amount'] = $amount;
        	$postData['fee'] = $fee;
        	$postData['nickname'] = $nickname;
        	$postData['adviceUrl'] = $adviceUrl;
        	$postData['returnUrl'] = $returnUrl;
        	$postData['remark'] = urlencode($remark);
        	$postData['sign'] = $sign;
        	$this->formHtml = $this->yemadaiFormPost($url, $postData);
        	return true;
        } else {
        	$this->addError('form', '充值失败！');
        	return false;
        }
	}

	private function yemadaiFormPost($url, $data) {
		$html = '<html>'
			. '<head>'
			. '<title>Payment By CreditCard online</title>'
			. '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'
			. '</head>'
			. '<body onload="document.form1.submit()">'
			. '<div>正在跳转至支付页面，请勿关闭浏览器！</div>'
			. '<form action="' . $url . '" method="post" name="form1">'
			  . '<input type="hidden" name="number_id" value="' . $data['numberId'] . '">'
			  . '<input type="hidden" name="out_trade_no" value="' . $data['tradeNo'] . '">'
			  . '<input type="hidden" name="amount" value="' . $data['amount'] . '">'
			  . '<input type="hidden" name="fee" value="' . $data['fee'] . '">'
			  . '<input type="hidden" name="nick_name" value="' . $data['nickname'] . '">'
			  . '<input type="hidden" name="advice_url" value="' . $data['adviceUrl'] . '">'
			  . '<input type="hidden" name="return_url" value="' . $data['returnUrl'] . '">'
			  . '<input type="hidden" name="remark" value="' . $data['remark'] . '">'
			  . '<input type="hidden" name="sign_info" value="' . $data['sign'] . '">'
			. '</form>'
			. '</body>'
			. '</html>';
		return $html;
	}

	private function lianlianRecharge() {
		$user = $this->getUser();
        $fee = 0;
        $userId = $user->userId;
        $nickname = $userId;
        $amount = $this->money;
        $baseUrl = WEB_MAIN;
        $adviceUrl = $this->adviceUrl;
        $returnUrl = $this->returnUrl;
        if($adviceUrl=='') {
        	$adviceUrl = $baseUrl . '/application/llNotify';
        }
        if($returnUrl=='') {
        	$returnUrl = $baseUrl . '/application/llReturn';
        }
        $remark='在线充值';
        $merchantKey = Registry::get('config')->get('third')->get('key');

        $recharge = new Recharge();
        $recharge->serialNumber = date('Ymd').substr(md5(microtime().$userId.$amount), 8, 16).rand(10, 99);
        $recharge->userId = $userId;
        $recharge->mode = 'in';
        $recharge->money = $amount;
        $recharge->fee = $fee;
        $recharge->status = 0;
        $recharge->time = date('Y-m-d H:i:s');
        $recharge->operator = $userId;
        $recharge->payType = $this->payType;
        $recharge->remark = $remark;
        $recharge->source = 1;
        $recharge->payWay = $this->payWay;
        $recharge->media = $this->getMedia();

        if($this->payWay=='D') {
        	if($this->bankCard=='') {
        		$userBank = UserBank::where('userId', $userId)->where('noAgree', $this->noAgree)->first();
        		if($userBank) {
        			$recharge->bankCard = $userBank->bankNum;
        		} else {
        			$this->addError('form', '充值失败，您的帐号异常，请选择其他方式充值！');
        			return false;
        		}
        	} else {
        		$recharge->bankCard = $this->bankCard;
        	}
		}
        if($recharge->save()) {
        	$llConfig = LLConfig::$params;
        	$risk_item = '{"frms_ware_category":"2009","user_info_mercht_userno":"'.$userId
        		.'","user_info_bind_phone":"'.$user->phone.'","user_info_dt_register":"'
        		.date('YmdHis',strtotime($user->addtime)).'","user_info_full_name":"'
        		.$user->name.'","user_info_id_type":"0","user_info_id_no":"'
        		.$user->cardnum.'","user_info_identify_state":"0"}';
        	$parameter = [
				'version' => trim($llConfig['version']),
				'oid_partner' => trim($llConfig['oid_partner']),
				'sign_type' => trim($llConfig['sign_type']),
				'userreq_ip' => trim($llConfig['userreq_ip']),
				'id_type' => trim($llConfig['id_type']),
				'valid_order' => trim($llConfig['valid_order']),
				'user_id' => $userId,
				'timestamp' => Core::localDate('YmdHis', time()),
				'busi_partner' => '101001',
				'no_order' => $recharge->serialNumber,
				'dt_order' => Core::localDate('YmdHis', time()),
				'name_goods' => '汇诚普惠-充值',
				'info_order' => '',
				'money_order' => $amount,
				'notify_url' => $adviceUrl,
				'url_return' => $returnUrl,
				'url_order' => '',
				'bank_code' => $this->bankCode,
				'pay_type' => $this->payWay,
				'no_agree' => $this->noAgree,
				'shareing_data' => '',
				'risk_item' => $risk_item,
				'id_no' => $user->cardnum,
				'acct_name' => $user->name,
				'flag_modify' => '',
				'card_no' => $this->bankCard,
				'back_url' => '',
			];
			$gateWay = 'gateway';
			if($this->payWay=='D') {
				$gateWay = 'authpay';
			}
			//建立请求
			$llpaySubmit = new LLpaySubmit($llConfig, $gateWay);
			$this->formHtml = $llpaySubmit->buildRequestForm($parameter, "post", "确认");
			return true;
        } else {
        	$this->addError('form', '充值失败！');
        	return false;
        }
	}

	private function minshengRecharge() {
		$oidPartner = Registry::get('config')->get('minsheng')->get('name');
		$user = $this->getUser();
        $fee = 0;
        $userId = $user->userId;
        $nickname = $userId;
        // $amount = 0.15;
       	$amount = $this->money;
        $remark='在线充值';
        $merchantKey = Registry::get('config')->get('third')->get('key');

        $recharge = new Recharge();
        $recharge->serialNumber = date('Ymd').substr(md5(microtime().$userId.$amount), 8, 16).rand(10, 99);
        $recharge->userId = $userId;
        $recharge->mode = 'in';
        $recharge->money = $amount;
        $recharge->fee = $fee;
        $recharge->status = 0;
        $recharge->time = date('Y-m-d H:i:s');
        $recharge->operator = $userId;
        $recharge->payType = $this->payType;
        $recharge->remark = $remark;
        $recharge->source = 1;
        $recharge->payWay = $this->payWay;
        $recharge->media = $this->getMedia();

        if($recharge->save()) {
	        $baseUrl = WEB_MAIN;
	        $adviceUrl = $this->adviceUrl;
	        $returnUrl = $this->returnUrl;
	        if($adviceUrl=='') {
	        	$adviceUrl = $baseUrl . '/application/msNotify';
	        }
	        if($returnUrl=='') {
	        	$returnUrl = $baseUrl . '/application/msReturn';
	        }
	        
	        $url = Registry::get('config')->get('minsheng')->get('url').'/payServlet';
	        // $url = WEB_MAIN.'/test/url';

        	$postData = [];
        	$postData['version'] = '1.0';
        	$postData['oid_partner'] = $oidPartner;
        	$postData['user_id'] = $userId;
        	// $postData['busi_partner'] = '';
        	$postData['no_order'] = $recharge->serialNumber;
        	$postData['dt_order'] = date('YmdHis');
        	$postData['name_goods'] = '汇诚普惠充值';
        	// $postData['info_order'] = '汇诚普惠充值';
        	$postData['money_order'] = round($amount, 2);
        	$postData['notify_url'] = $adviceUrl;
        	$postData['url_return'] = $returnUrl;
        	// $postData['userreq_ip'] = '';
        	// $postData['url_order'] = '';
        	$postData['bank_code'] = $this->bankCode;
        	$postData['pay_type'] = $this->payWay;

			$privateKey = MSBank::getKey('private', 'xwsd');

        	$sign = StringHelper::rsaSign(StringHelper::createLinkString(StringHelper::paramsSort($postData, true)), $privateKey);

        	$postData['sign'] = $sign;

        	$this->formHtml = $this->minshengFormPost($url, $postData);
        	return true;
        } else {
        	$this->addError('form', '充值失败！');
        	return false;
        }
	}

	private function minshengFormPost($url, $data) {
		$html = '<html>'
			. '<head>'
			. '<title>民生支付</title>'
			. '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'
			. '</head>'
			// . '<body>'
			. '<body onload="document.form1.submit()">'
			. '<div>正在跳转至支付页面，请勿关闭浏览器！</div>'
			. '<form action="' . $url . '" method="post" name="form1">' 
			. '<input type="hidden" name="version" value="' . $data['version'] . '">'
			. '<input type="hidden" name="oid_partner" value="' . $data['oid_partner'] . '">'
			. '<input type="hidden" name="user_id" value="' . $data['user_id'] . '">'
			// . '<input type="hidden" name="busi_partner" value="' . $data['busi_partner'] . '">'
			. '<input type="hidden" name="no_order" value="' . $data['no_order'] . '">'
			. '<input type="hidden" name="dt_order" value="' . $data['dt_order'] . '">'
			. '<input type="hidden" name="name_goods" value="' . $data['name_goods'] . '">'
			// . '<input type="hidden" name="info_order" value="' . $data['info_order'] . '">'
			. '<input type="hidden" name="money_order" value="' . $data['money_order'] . '">'
			. '<input type="hidden" name="notify_url" value="' . $data['notify_url'] . '">'
			. '<input type="hidden" name="url_return" value="' . $data['url_return'] . '">'
			// . '<input type="hidden" name="userreq_ip" value="' . $data['userreq_ip'] . '">'
			// . '<input type="hidden" name="url_order" value="' . $data['url_order'] . '">'
			. '<input type="hidden" name="bank_code" value="' . $data['bank_code'] . '">'
			. '<input type="hidden" name="pay_type" value="' . $data['pay_type'] . '">'
			. '<input type="hidden" name="sign" value="' . $data['sign'] . '">'
			// . '<input type="submit" value="立即支付">'
		. '</form>'
		. '</body>'
		. '</html>';
		return $html;
	}

	public function helpRecharge(){
		$recharge = Recharge::where('status',0)->select();
		foreach ($recharge as $key => $value) {
			$results = $this->baofooHelpRecharge($value);
			if(!$results){
				continue;
			}
            $resp_code = $results['resp_code'];
            if($form->result['resp_code'] != '0000'){
                $data['tradeNo'] = $results['trans_id'];
                $data['money'] = $results['succ_amt'];
                $data['fee'] = 0;
                $data['status'] = -1;
                $data['result'] = $resp_code;
                $data['thirdSerialNo'] = $results['trans_no'];
                Recharge::after($data);

                $rdata['status'] = -1;
                $rdata['info'] = $form->result['resp_msg'];
                $this->backJson($rdata); 
            }else{
                $data['tradeNo'] = $results['trans_id'];
                $data['money'] = $results['succ_amt'];
                $data['fee'] = 0;
                $data['result'] = $resp_code;
                $data['status'] = 1;
                $data['thirdSerialNo'] = $results['trans_no'];
                $result = Recharge::after($data);

                $rdata['status'] = 1;
                $rdata['info'] = '充值成功';
                $this->backJson($rdata); 
            }
		}
	}

	private function baofooHelpRecharge($item){
		$user = $this->getUser();
		
		$terminal_id = Registry::get('config')->get('baofoo')->get('wid');
		$member_id = Registry::get('config')->get('baofoo')->get('member_id');
		$baseUrl = Registry::get('config')->get('baofoo')->get('url');
		$password = Registry::get('config')->get('baofoo')->get('hc_key_pw');
		$url = $baseUrl .'/quickpay/api/queryorder';

        $fee = 0;
        $userId = $user->userId;
        $nickname = $userId;
        $amount = $this->money;
        $baseUrl = WEB_MAIN;
		$serialNumber = date('ymd').substr(md5(microtime().$userId.$amount), 8, 10).rand(10, 99);
        if(1) {
        	$contents = [
        		'terminal_id' => $terminal_id,
        		'member_id' => $member_id,
				'trans_serial_no' => $serialNumber,
        		'orig_trans_id' => $item->tradeNo,
        		'trade_date' => date('YmdHis',strtotime($item->time)),
        	];
        	Log::write('contents', $contents, 'debug', 'DEBUG');
        	$privateKey = BFBank::getKey('private', 'hc');
        	$content = StringHelper::bfSign(json_encode($contents), $privateKey, $password);
        	$params = [
				'version' => '4.0.0.0',
				'input_charset' => 1,
				'terminal_id' => $terminal_id,
				'member_id' => $member_id,
				'data_type' => 'json',
				'data_content' => $content
			];
			$this->baofooSdkPost($url, $params);
			return $this->result;
        }
	}

	public function baofooConfirmRecharge(){
		ignore_user_abort();
		set_time_limit(0);
		
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
		$serialNumber = date('ymd').substr(md5(microtime().$userId.$amount), 8, 10).rand(10, 99);
        if(1) {
        	$contents = [
				'biz_type' => '0000',
				'txn_sub_type' => '16',
        		'terminal_id' => $terminal_id,
        		'member_id' => $member_id,
        		'business_no' => $this->business_no,
        		'sms_code' => $this->sms_code,
        		'trade_date' => date('YmdHis'),
				'trans_serial_no' => $serialNumber,
        	];
        	Log::write('contents', $contents, 'debug', 'DEBUG');
        	$privateKey = BFBank::getKey('private', 'hc');
        	$content = StringHelper::bfSign(json_encode($contents), $privateKey, $password);
        	$params = [
				'version' => '4.0.0.0',
				// 'input_charset' => 1,
				'terminal_id' => $terminal_id,
				'txn_type' => '0431',
				'txn_sub_type' => '16',
				'member_id' => $member_id,
				'data_type' => 'json',
				'data_content' => $content
			];
			$this->baofooSdkPost($url, $params);
			return true;
        }
	}

	public function unbindCard() {
		$user = $this->getUser();
		
		$terminal_id = Registry::get('config')->get('baofoo')->get('wid');
		$member_id = Registry::get('config')->get('baofoo')->get('member_id');
		$baseUrl = Registry::get('config')->get('baofoo')->get('url');
		$password = Registry::get('config')->get('baofoo')->get('hc_key_pw');
		$url = $baseUrl .'/cutpayment/api/backTransRequest';
        
        $bank = UserBank::where('userId', $user->userId)->where('status', '1')->first();

    	$contents = [
    		'biz_type' => '0000',
			'txn_sub_type' => '02',
    		'terminal_id' => $terminal_id,
    		'member_id' => $member_id,
    		'bind_id' => $bank->bindId,
    		'trade_date' => date('YmdHis'),
    		'trans_serial_no' => date('ymd').substr(md5(microtime().$userId.$amount), 8, 10).rand(10, 99),
    	];

    	Log::write('contents', $contents, 'debug', 'DEBUG');
    	$privateKey = BFBank::getKey('private', 'hc');
    	$content = StringHelper::bfSign(json_encode($contents), $privateKey, $password);
    	$params = [
			'version' => '4.0.0.0',
			// 'input_charset' => 1,
			'txn_type' => '0431',
			'txn_sub_type' => '02',
			'terminal_id' => $terminal_id,
			'member_id' => $member_id,
			'data_type' => 'json',
			'data_content' => $content
		];
		$this->baofooSdkPost($url, $params);
		return true;
	}

	private function baofooPreRecharge() {
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
        // $adviceUrl = $this->adviceUrl;
        // $returnUrl = $this->returnUrl;
        // if($adviceUrl=='') {
        // 	$adviceUrl = $baseUrl . '/application/bfNotify';
        // }
        // if($returnUrl=='') {
        // 	$returnUrl = $baseUrl . '/application/bfReturn';
        // }
        $remark='在线充值';

        $recharge = new Recharge();
        $recharge->serialNumber = date('ymd').substr(md5(microtime().$userId.$amount), 8, 10).rand(10, 99);
        $recharge->userId = $userId;
        $recharge->money = $amount;
        $recharge->fee = $fee;
        $recharge->status = 0;
        $recharge->time = date('Y-m-d H:i:s');
        $recharge->payType = $this->payType;
        $recharge->remark = $remark;
        $recharge->payWay = $this->payWay;
        $recharge->media = $this->getMedia();
        
        $bank = UserBank::where('userId', $user->userId)->where('status', '1')->first();
		$this->bankCard = $bank['bankNum'];

        if($recharge->save()) {

        	$latestTradeDate = MoneyLog::where('userId',$user->userId)->orderBy('id','desc')->first(['time']);
        	$latestTradeDate = $latestTradeDate?date('YmdHis',strtotime($latestTradeDate)):'0';
        	$risk_item = [
        		// 'goodsCategory' => '02',
        		// 'userLoginId' => $user->phone,
        		// 'userMobile' => $user->phone,
        		// 'registerUserName' => $user->name,
        		// 'identifyState' => 1,
        		// 'userIdNo' => $user->cardnum,
        		// 'registerTime' => date('YmdHis',strtotime($user->addtime)),
        		// 'chName' => $user->name,
        		// 'chIdNo' => $user->cardnum,
        		// 'chCardNo' => $bank->bankNum,
        		// 'chMobile' => $user->phone,
        		// 'chPayIp' => $_SERVER['REMOTE_ADDR'],
        		// 'deviceOrderNo' => $recharge->serialNumber,
        		// 'tradeType' => '1',
        		// 'customerType' => '1',
        		// 'latestTradeDate' => $latestTradeDate,
        		'client_ip' => $_SERVER['REMOTE_ADDR'],
        	];

        	$contents = [
        		'txn_sub_type' => '15',
        		'biz_type' => '0000',
        		'terminal_id' => $terminal_id,
        		'member_id' => $member_id,
        		'trans_serial_no' => $recharge->serialNumber,
        		'trans_id' => $recharge->serialNumber,
        		'bind_id' => $bank->bindId,
        		'txn_amt' => _yuan2fen($amount),
        		'trade_date' => date('YmdHis'),
        		
    			//'acc_no' => $bank->bankNum,
				// 'card_holder' => $user->name,
				// 'id_card_type' => '01',
				// 'id_card' => $user->cardnum,
				// 'mobile' => $user->phone,
				// 'valid_date' => $bank->validDate,
				// 'cvv' => $bank->cvv,
    			//'user_id' => $user->userId,
        		'risk_content' => $risk_item,
        	];
        	Log::write('contents', $contents, 'debug', 'DEBUG');
        	$privateKey = BFBank::getKey('private', 'hc');
        	$content = StringHelper::bfSign(json_encode($contents), $privateKey, $password);
        	$params = [
				'version' => '4.0.0.0',
				'terminal_id' => $terminal_id,
				'txn_type' => '0431',
				'txn_sub_type' => '15',
				'member_id' => $member_id,
				'data_type' => 'json',
				'data_content' => $content
				// 'input_charset' => 1,
			];
			$this->baofooSdkPost($url, $params);
			return true;
        } else {
        	$this->addError('form', '充值失败！');
        	return false;
        }
	}

	private function baofooFormPost($url, $data) {
		$html = '<div>正在跳转至支付页面，请勿关闭浏览器！</div>'
			. '<form id="llpaysubmit" action="' . $url . '" method="post" name="llpaysubmit">' 
			. '<input type="hidden" name="version" value="' . $data['version'] . '">'
			. '<input type="hidden" name="input_charset" value="' . $data['input_charset'] . '">'
			. '<input type="hidden" name="language" value="' . $data['language'] . '">'
			. '<input type="hidden" name="terminal_id" value="' . $data['terminal_id'] . '">'
			. '<input type="hidden" name="txn_type" value="' . $data['txn_type'] . '">'
			. '<input type="hidden" name="txn_sub_type" value="' . $data['txn_sub_type'] . '">'
			. '<input type="hidden" name="member_id" value="' . $data['member_id'] . '">'
			. '<input type="hidden" name="data_type" value="' . $data['data_type'] . '">'
			. '<input type="hidden" name="data_content" value="' . $data['data_content'] . '">'
			. '<input type="hidden" name="back_url" value="' . $data['back_url'] . '">'
		. '</form>';
		$html .= '<script>document.forms[\'llpaysubmit\'].submit();</script>';
		return $html;
	}

	private function baofooSdkPost($url, $data) {
		Log::write($url, [], 'debug', 'DEBUG');
		Log::write('data', $data, 'debug', 'DEBUG');
		$return = NetworkHelper::postTwo($url, $data);
		$ReturnCountent = array();
		parse_str($return, $ReturnCountent);
		$publicKey = BFBank::getKey('public', 'hc');
		$result = StringHelper::bfVerify($return, $publicKey);
		$this->result = json_decode($result, true);
		Log::write('data', [$this->result], 'debug', 'DEBUG');
		$this->result['resp_msg'] = StringHelper::bfReMsg($this->result['resp_msg']);
		if(!StringHelper::bfReMsg($this->result['resp_msg'])){
			$this->result['resp_code'] = -1;
			$this->result['resp_msg'] = $ReturnCountent['ret_msg'];
		}
	}

	private function baofooWebRecharge() {
		$user = $this->getUser();
		
		$terminalID = Registry::get('config')->get('baofoo')->get('web_wid');
		$memberID = Registry::get('config')->get('baofoo')->get('web_member_id');
		$baseUrl = Registry::get('config')->get('baofoo')->get('web_url');
		$bfKey = Registry::get('config')->get('baofoo')->get('web_key');
		$url = $baseUrl .'/payindex';

        $fee = 0;
        $userId = $user->userId;
        $nickname = $userId;
        $amount = $this->money;
        $baseUrl = WEB_MAIN;
        $adviceUrl = $this->adviceUrl;
        $returnUrl = $this->returnUrl;
        if($adviceUrl=='') {
        	$adviceUrl = $baseUrl . '/application/bfWebNotify';
        }
        if($returnUrl=='') {
        	$returnUrl = $baseUrl . '/application/bfWebReturn';
        }
        $remark='在线充值';

        $recharge = new Recharge();
        $recharge->serialNumber = date('Ymd').substr(md5(microtime().$userId.$amount), 8, 16).rand(10, 99);
        $recharge->userId = $userId;
        //$recharge->mode = 'in';
        $recharge->money = $amount;
        $recharge->fee = $fee;
        $recharge->status = 0;
        $recharge->time = date('Y-m-d H:i:s');
        //$recharge->operator = $userId;
        $recharge->payType = $this->payType;
        $recharge->remark = $remark;
        //$recharge->source = 1;
        $recharge->payWay = $this->payWay;
        //$recharge->bankCard = $this->bankCard;
        $recharge->media = $this->getMedia();

        $noticeType = 1;
        $keyType = 1;
        $version = '4.0';
        $money = _yuan2fen($amount);
        $date = date('YmdHis');

        $signList = [];
        $signList[] = $memberID;
        $signList[] = $this->bankCode;
        $signList[] = $date;
        $signList[] = $recharge->serialNumber;
        $signList[] = $money;
        $signList[] = $returnUrl;
        $signList[] = $adviceUrl;
        $signList[] = $noticeType;
        $signList[] = $bfKey;

        $sign = md5(implode('|', $signList));
        if($recharge->save()) {
        	$params = [
        		'InterfaceVersion' => $version,
        		'TerminalID' => $terminalID,
        		'MemberID' => $memberID,
        		'PayID' => $this->bankCode,
        		'KeyType' => $keyType,
        		'NoticeType' => $noticeType,
        		'TransID' => $recharge->serialNumber,
        		'OrderMoney' => $money,
        		'TradeDate' => $date,
        		'PageUrl' => $returnUrl,
        		'ReturnUrl' => $adviceUrl,
        		'Signature' => $sign,
        	];
			$this->html = $this->baofooWebFormPost($url, $params);
			return true;
        } else {
        	$this->addError('form', '充值失败！');
        	return false;
        }
	}

	private function baofooWebFormPost($url, $data) {
		$html = '<div>正在跳转至支付页面，请勿关闭浏览器！</div>'
			. '<form id="llpaysubmit" action="' . $url . '" method="post" name="llpaysubmit">' 
			. '<input type="hidden" name="InterfaceVersion" value="' . $data['InterfaceVersion'] . '">'
			. '<input type="hidden" name="TerminalID" value="' . $data['TerminalID'] . '">'
			. '<input type="hidden" name="MemberID" value="' . $data['MemberID'] . '">'
			. '<input type="hidden" name="PayID" value="' . $data['PayID'] . '">'
			. '<input type="hidden" name="KeyType" value="' . $data['KeyType'] . '">'
			. '<input type="hidden" name="NoticeType" value="' . $data['NoticeType'] . '">'
			. '<input type="hidden" name="TransID" value="' . $data['TransID'] . '">'
			. '<input type="hidden" name="OrderMoney" value="' . $data['OrderMoney'] . '">'
			. '<input type="hidden" name="TradeDate" value="' . $data['TradeDate'] . '">'
			. '<input type="hidden" name="PageUrl" value="' . $data['PageUrl'] . '">'
			. '<input type="hidden" name="ReturnUrl" value="' . $data['ReturnUrl'] . '">'
			. '<input type="hidden" name="Signature" value="' . $data['Signature'] . '">'
		. '</form>';
		$html .= '<script>document.forms[\'llpaysubmit\'].submit();</script>';
		return $html;
	}

	private function fuiouRecharge() {
		$user = $this->getUser();
		$mchntcd = Registry::get('config')->get('fuiou')->get('mchntcd');
		$key = Registry::get('config')->get('fuiou')->get('key');

        $fee = 0;
        $userId = $user->userId;
        $nickname = $userId;
        $adviceUrl = $this->adviceUrl;
        $returnUrl = $this->returnUrl;
        if($adviceUrl=='') {
        	$adviceUrl = WEB_MAIN . '/application/fyNotify';
        }
        if($returnUrl=='') {
        	$returnUrl = WEB_MAIN . '/application/fyReturn';
        }
        $remark='在线充值';

        $tradeNo = date('md').substr(md5(microtime().$userId.$this->money).rand(10, 99), 8, 16);

        $recharge = new Recharge();
    	$agreement = RechargeAgree::where('userId', $userId)->orderBy('lastUseTime', 'desc')->first();
        if($this->noAgree!='') {
        	if(!$agreement) {
    			$this->addError('form', '系统异常，不存在该认证银行卡！');
    			return false;
    		}
    		$this->bankCard = $agreement->bankCard;
    	} else {
    		if($agreement) {
    			$this->addError('form', '系统异常，已经有认证过的银行卡！');
    			return false;
    		}
    	}
        $recharge->serialNumber = $tradeNo;
        $recharge->userId = $userId;
        $recharge->mode = 'in';
        $recharge->money = $this->money;
        $recharge->fee = $fee;
        $recharge->status = 0;
        $recharge->time = date('Y-m-d H:i:s');
        $recharge->operator = $userId;
        $recharge->payType = $this->payType;
        $recharge->remark = $remark;
        $recharge->source = 1;
        $recharge->bankCard = $this->bankCard;
        $recharge->bankCode = $this->bankCode;
        $recharge->payWay = $this->payWay;
        $recharge->media = $this->getMedia();

        if($recharge->save()) {
        	$amount = _yuan2fen($this->money);
	        $version = '2.0';
	        $list = [$this->payWay, $version, $mchntcd, $tradeNo, $userId, $amount, $this->bankCard, $adviceUrl, $user->name, $user->cardnum, 0, $key];
	        $sign = md5(implode('|', $list));
        	$this->result = [
	        	'MCHNTCD'=>$mchntcd, 
	        	'MCHNTORDERID'=>$tradeNo, 
	        	'USERID'=>$userId,
	        	'AMT'=>$amount,
	        	'BANKCARD'=>$this->bankCard,
	        	'VERSION'=>$version, 
	        	'TYPE'=>$this->payWay,
	        	'BACKURL'=>$adviceUrl, 
	        	'NAME'=>$user->name, 
	        	'IDNO'=>$user->cardnum,
	        	'IDTYPE'=>0,
	        	'SIGN'=>$sign,
	        	'SIGNTP'=>'MD5'
	        ];
			return true;
        } else {
        	$this->addError('form', '充值失败！');
        	return false;
        }
	}
}
