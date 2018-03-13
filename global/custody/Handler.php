<?php
namespace custody;

use Yaf\Registry;
use tools\Counter;
use tools\Log;
use helpers\StringHelper;

/**
 * Handler
 * 处理存管接口的工具类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Handler {
	const VSERSION = "10";
	const SIGN_KEY = 'sign';
	const SUCCESS = '00000000';
	const SEQ_PL = '#seq-no#';
	const BNQ_PL = '#bnq-no#';
	const API_TOKEN = 'liuchenhui123';

	const M_APP = 'MOBILE';		/** 手机APP **/
	const M_WEB = 'PC';		/** 网页 **/
	const M_WECHAT = 'MOBILE';	/** 微信 **/
	const M_COUNTER = 'PC';	/** 柜面 **/

	private static $config;
	private static $apiList = [
		'PERSONAL_REGISTER_API', 		/** 开户 **/
		'PERSONAL_BIND_BANKCARD', 		/** 绑定银行卡 **/
		'UNBIND_BANKCARD_DIRECT', 		/** 解绑银行卡 **/
		'RESET_PASSWORD', 				/** 密码设置 **/
		'passwordReset', 				/** 密码重置 **/
		'MODIFY_MOBILE', 				/** 手机号码修改 **/
		'smsCodeApply', 				/** 发送短信验证码 **/
		'RECHARGE', 					/** 充值 **/
		'CREATE_PROJECT', 				/** 标的登记 **/
		'debtRegisterCancel', 			/** 标的登记撤销 **/
		'USER_PRE_FREEZE', 				/** 投标申请 **/
		'MEMBER_AUTH_API', 				/** 自动投标签约 **/
		'autoBidAuthCancel', 			/** 自动投标解约 **/
		'bidAutoApply', 				/** 自动投标申请 **/
		'bidCancel', 					/** 投标申请撤销 **/
		'MEMBER_AUTH_API', 				/** 自动债权转让签约 **/
		'autoCreditInvestAuthCancel',	/** 自动债权转让解约 **/
		'trusteePay',					/** 受托支付申请 **/

		'lendPay', 						/** 放款 **/
		'repay', 						/** 还款 **/
		'WITHDRAW', 					/** 提现 **/
		'DEBENTURE_SALE', 				/** 债转 **/
		'USER_PRE_FREEZE', 				/** 购买债权 **/
		'RED_PACKET', 					/** 红包发放 **/
		'voucherPayCancel', 			/** 红包发放撤销 **/
		'voucherPayDelayCancel',		/** 红包发放隔日撤销 **/
		'repayBail', 					/** 融资人还担保账户垫款 **/
		'creditEnd', 					/** 结束债权 **/
		'payCancel', 					/** 放款或还款撤销 **/
		'balanceFreeze', 				/** 还款申请冻结资金 **/
		'balanceUnfreeze', 				/** 还款申请撤销资金解冻 **/

		'accountOpenPlus',				/** [增强]开户 **/
		'cardBindPlus',					/** [增强]绑定银行卡 **/
		'mobileModifyPlus',				/** [增强]手机号码修改**/
		'passwordResetPlus',			/** [增强]密码重置 **/
		'directRechargePlus', 			/** [增强]充值 **/
		'autoBidAuthPlus', 				/** [增强]自动投标签约 **/
		'autoCreditInvestAuthPlus', 	/** [增强]自动债权转让签约 **/

		'CONFIRM_LOAN', 				/** [批次]放款 **/
		'CONFIRM_REPAYMENT', 			/** [批次]还款 **/
		'batchRepayBail', 				/** [批次]融资人还担保账户垫款 **/
		'batchCreditEnd', 				/** [批次]结束债权 **/
		'batchCancel', 					/** [批次]撤销 **/
		'batchCreditInvest', 			/** [批次]投资人购买债权 **/
		'batchBailRepay', 				/** [批次]担保账户代偿 **/
		'batchVoucherPay', 				/** [批次]批次发红包 **/

		'balanceQuery', 				/** [查询]电子账户余额 **/
		'accountDetailsQuery', 			/** [查询]电子账户资金交易明细 **/
		'creditDetailsQuery', 			/** [查询]投资人债权明细 **/
		'cardBindDetailsQuery', 		/** [查询]绑卡关系 **/
		'mobileMaintainace', 			/** [查询]电子账户手机号 **/
		'accountIdQuery', 				/** [查询]按证件号查电子账号 **/
		'accountQueryByMobile', 		/** [查询]按手机号查电子账号信息 **/
		'debtDetailsQuery', 			/** [查询]借款人标的信息 **/
		'transactionStatusQuery', 		/** [查询]交易状态 **/
		'batchQuery', 					/** [查询]批次状态 **/
		'batchDetailsQuery', 			/** [查询]批次交易明细状态 **/
		'creditInvestQuery', 			/** [查询]投资人购买债权 **/
		'bidApplyQuery', 				/** [查询]投资人投标申请 **/
		'creditAuthQuery', 				/** [查询]投资人签约状态 **/
		'corprationQuery', 				/** [查询]企业账户 **/
		'freezeDetailsQuery', 			/** [查询]账户资金冻结明细 **/
		'passwordSetQuery',  			/** [查询]电子账户密码是否设置 **/
		'balanceFreezeQuery', 			/** [查询]单笔还款申请冻结 **/
		'trusteePayQuery',				/** [查询]受托支付申请查询 **/
		'batchVoucherDetailsQuery',		/** [查询]批次发红包交易明细 **/
		'fundTransQuery', 				/** [查询]单笔资金类业务交易 **/

		'file-eve',                     /** [文件]交易明细流水文件 **/
		'file-aleve',                   /** [文件]交易明细全流水文件 **/
	];

	private $unCommonKey = [
		'passwordSet'=>'passwordset', 
		'mobileModify'=>'mobileModify', 
		'withdraw'=>'withdraw', 
		'bidApply'=>'bidapply', 
		'creditInvest'=>'creditInvest',
		'trusteePay'=>'trusteePay',
	];

	private $key;
	private $params = [];

	/** @var string 流水号20位由以下变量组成 YYYYMMDDhhmmssXXXXXX  XXXXXX是由计数器生成的，必须是数字 */
	private $snDate;
	private $snTime;
	private $seq;
	private $bnTime;
	private $bnq;
	private $media;
	private $isBatch = false;
	private $isLog = true;

	/**
	 * 返回存管交易渠道
	 * @param  string $media 媒体标识符
	 * @return string        交易渠道编号
	 */
	public static function getChannel($media) {
		if($media=='pc') {
			return self::M_WEB;
		} else if($media=='app'||$media=='ios'||$media=='andriod') {
			return self::M_APP;
		} else if($media=='wechat') {
			return self::M_WECHAT;
		} else if($media=='counter') {
			return self::M_COUNTER;
		}
		return '';
	}

	/**
	 * 获取指定配置参数
	 * @param  string $key 配置参数名
	 * @return string      配置参数值
	 */
	public static function getConfig($key) {
		if(!self::$config) {
			self::$config = Registry::get('config')->get('custody');
		}
		return self::$config[$key];
	}

	/**
	 * 加密
	 * @param  array  $params 请求参数
	 * @return string         加密后的字符串
	 */
	public static function sign($params) {
		ksort($params);
        reset($params);
        $string = '';
        foreach ($params as $key => $value) {
        	$string .= $value;
        }

		//$keyFile = self::getConfig('xwsd_pri');
		//$pass = self::getConfig('xwsd_pwd');
		//$privateKey = \Data::getFileContent('custody/'.$keyFile);

        //私钥加密    
		//openssl_pkcs12_read($privateKey, $certs, $pass); //读取公钥、私钥
		//$prikeyID = $certs['pkey']; //私钥
		//openssl_sign($string, $sign, $prikeyID, OPENSSL_ALGO_SHA1); //注册生成加密信息
		$sign = MD5($string.self::API_TOKEN);
		$sign = base64_encode($sign); //base64转码加密信息

		return $sign;
	}

	/**
	 * 验证
	 * @param  string   $sign  返回值
	 * @return boolean
	 */
    public static function verify($params){
    	if(!isset($params[self::SIGN_KEY])) {
    		return false;
    	}
    	$sign = $params[self::SIGN_KEY];
    	//$sign = base64_decode($sign);
    	unset($params[self::SIGN_KEY]);
    	ksort($params);
        reset($params);
        $string = '';
        foreach ($params as $key => $value) {
        	$string .= $value;
        }
        $keyFile = self::getConfig('cust_pub');

        $realsign = MD5($string.self::API_TOKEN);
        return $realsign == $sign;
        //$publicKey = \Data::getFileContent('custody/'.$keyFile);
        //$pubKey = openssl_pkey_get_public($publicKey);
		//return openssl_verify($string, $sign, $pubKey);
    }

    /**
     * 用于接收到异步通知后返回信息
     * @return mixed
     */
    public static function back() {
    	echo 'success'; 
    	exit(0);
    }

    /**
	 * 构造方法
	 * @param  string  $key     接口名称，见$apiList
	 * @param  array   $params  参数
	 * @param  boolean $isBatch 是否是批次，是批次将自动生成参数batchNo
	 */
    public function __construct($key, $params, $isBatch=false) {
    	$this->key = $key;
    	$this->isBatch = $isBatch;
		$this->params = $this->packParams($key, $params);
		$sign = self::sign($this->params);
		$this->params[self::SIGN_KEY] = $sign;
	}

	/**
	 * 组合一些固定的参数
	 * @param  string $key    接口名称，见$apiList
	 * @param  array  $params 传入的参数
	 * @return array          返回全部参数
	 */
	private function packParams($key, $params) {
		$this->generateSN();
		$params['version'] = self::VSERSION;
		$params['serviceName'] = $key;
		$params['instCode'] = self::getConfig('instCode');
		$params['bankCode']	= self::getConfig('bankCode');
		$params['txDate'] = $this->snDate;
		$params['txTime'] = $this->snTime;
		$params['seqNo'] = $this->seq;
		$params['requestNo'] = $this->snDate.$this->snTime.$this->seq;
		if(!isset($params['userDevice'])) {
			$params['userDevice'] = self::M_WEB;
		}
		if($this->isBatch) {
			$this->generateBN();
			$params['batchNo'] = $this->bnq;
		}

		foreach ($params as $k => $v) {
			$exchange = [self::SEQ_PL => $this->getSN(), self::BNQ_PL => $this->getBN()];
			if(is_string($v)) {
				$params[$k] = strtr($v, $exchange);
			} else if(is_array($v)) {
				$vals = [];
				foreach ($v as $a => $b) {
					$vals[$a] = strtr($b, $exchange);
				}
				$params[$k] = StringHelper::encodeQueryString($vals);
			} else {
				$params[$k] = (string)$v;
			}
		}

		return $params;
	}

	/**
	 * 用于直接请求的接口[接口调用]
	 * @return array 结果数组
	 */
	public function api() {
		if(!in_array($this->key, self::$apiList)) {
			return ['retCode'=>'XW000001', 'retMsg'=>'接口不存在！'];
		}

		if($this->isLog) {
			Log::write('['.$this->key.']['.$this->getSN().']', $this->getData(), 'custody-send');
		}

		$data = $this->params;
		$url = self::getConfig('url');

		$result = self::jsonPost($url, $data);
		
		if($this->isLog) {
			Log::write('['.$this->key.']接口数据返回：' . $result, [], 'custody');
		}

		$result = json_decode($result, true);

		return $result;
	}


	/**
	 * 用于需要页面跳转的接口[页面调用]
	 * @param boolean $export 是否直接输出
	 */
	public function form($export=true) {
		if(!in_array($this->key, self::$apiList)) {
			if($export) {
				echo '接口不存在！'; exit(0);
			}
			return '接口不存在！';
		}

		if($this->isLog) {
			Log::write('['.$this->key.']['.$this->getSN().']', $this->getData(), 'custody-send');
		}
		
		$url = self::getConfig('url') . '/p2p/page/mobile';
		if(isset($this->unCommonKey[$this->key])) {
			$url = self::getConfig('url') . '/p2p/page/'.$this->unCommonKey[$this->key];
		}

		$html = "<form id='custody_form' name='custody_form' action='" . $url . "' method='post'>";
		foreach ($this->params as $key => $value) {
			$html .= "<input type='hidden' name='" . $key . "' value='" . $value . "'/>";
		}
		$html = $html . "<input style='display:none' type='submit' value='提交'></form>";
		$html = $html . '<div>页面跳转中，请勿关闭网页！</div>';
		$html = $html . "<script>document.forms['custody_form'].submit();</script>";
		if($export) {
			echo $html; exit(0);
		}
		return $html;
	}

	public static function file($key, $date) {
		if(!in_array($key, self::$apiList)) {
			return ['retCode'=>'XW000001', 'retMsg'=>'接口不存在！'];
		}
		$date = date('Ymd', strtotime($date));
		$url = self::getConfig('url') . '/file/download';
		$params = [];
		$params['instCode'] = self::getConfig('instCode');
		$params['bankCode']	= self::getConfig('bankCode');
		$params['txDate'] = $date;

		$keyRows = explode('-', $key);
		$v = strtoupper($keyRows[1]);

		$fileName = self::getConfig('bankNo').'-'.$v.self::getConfig('productNo').'-'.$date;
		$params['fileName'] = $fileName;
		$params['SIGN'] = self::sign($params);
		$data = json_encode($params, JSON_UNESCAPED_UNICODE);

		$result = self::jsonPost($url, $data);
		return $result;
	}

	/**
	 * 获取流水号
	 * @return string 流水号
	 */
	public function getSN() {
		return $this->snDate . $this->snTime . $this->seq;
	}

	/**
	 * 生成流水号
	 */
	private function generateSN() {
		$seq = Counter::next('custody', 'n');
		$this->seq = str_repeat('0', 6-strlen($seq)).$seq;
		$time = Counter::getTime();
		$this->snDate = date('Ymd', $time);
		$this->snTime = date('His', $time);
	}

	/**
	 * 获取发送数据[json字符串]
	 * @return string json数据
	 */
	public function getJson() {
		return json_encode($this->params, JSON_UNESCAPED_UNICODE);
	}

	/**
	 * 获取数据[array]
	 * @return array 发送的数据
	 */
	public function getData() {
		return $this->params;
	}

	/**
	 * 生成批次号
	 */
	private function generateBN() {
		$bnq = Counter::next('batchNo', 'd');
        $this->bnq = str_repeat('0', 6-strlen($bnq)).$bnq;
        $time = Counter::getTime();
		$this->bnTime = date('Ymd', $time);
	}

	/**
	 * 获取批次号
	 * @return string 流水号
	 */
	public function getBN() {
		return $this->bnTime . $this->bnq;
	}

	/**
	 * 提交json数据
	 * @return string 返回结果
	 */
	private static function jsonPost($url, $data) {
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_POST, 1);                  
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
	    curl_setopt($curl, CURLOPT_HEADER, 0);
                $strlen = strlen(json_encode($data,JSON_UNESCAPED_UNICODE));
		curl_setopt($curl, CURLOPT_HTTPHEADER,array('Content-Type: application/json; charset=utf-8','Content-Length:' . $strlen));
		
		$result = curl_exec($curl);

		/*if(curl_errno($curl)){
			print curl_error($ch);
		}*/
		
		curl_close($curl);

		return $result;
	}

	public static function getOrg() {
		$conCode = self::getConfig('conCode');
		return $conCode . '0000';
	}
}
