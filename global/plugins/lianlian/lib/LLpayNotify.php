<?php
namespace plugins\lianlian\lib;
class LLpayNotify {
	public $llpay_config;

	function __construct($llpay_config) {
		$this->llpay_config = $llpay_config;
	}

	public function LLpayNotify($llpay_config) {
		$this->__construct($llpay_config);
	}
	/**
	 * 针对notify_url验证消息是否是连连支付发出的合法消息
	 * @return mixed 验证结果
	 */
	public function verifyNotify() {
		//生成签名结果
		$is_notify = true;
		$json = new JSON();
		$str = file_get_contents("php://input");
		\Log::write($str, 'recharge');
		$val = $json->decode($str);
		$oid_partner = trim($val->{'oid_partner'});
		$sign_type = trim($val->{'sign_type'});
		$sign = trim($val->{'sign'});
		$dt_order = trim($val->{'dt_order'});
		$no_order = trim($val->{'no_order'});
		$oid_paybill = trim($val->{'oid_paybill'});
		$money_order = trim($val->{'money_order'});
		$result_pay = trim($val->{'result_pay'});
		$settle_date = trim($val->{'settle_date'});
		// $info_order = trim($val->{'info_order'});
		$pay_type = trim($val->{'pay_type'});

		$bank_code = '';
		$no_agree = '';
		$id_type = '';
		$id_no = '';
		$acct_name = '';
		if($pay_type=='D') {
			\Log::write('type-D', 'recharge');
			$bank_code = trim($val->{'bank_code'});
			$no_agree = trim($val->{'no_agree'});
			$id_type = trim($val->{'id_type'});
			$id_no = trim($val->{'id_no'});
			$acct_name = trim($val->{'acct_name'});
		}
		
		//首先对获得的商户号进行比对
		if ($oid_partner != $this->llpay_config['oid_partner']) {
			//商户号错误
			return false;
		}
		$parameter = array (
			'oid_partner' => $oid_partner,
			'sign_type' => $sign_type,
			'dt_order' => $dt_order,
			'no_order' => $no_order,
			'oid_paybill' => $oid_paybill,
			'money_order' => $money_order,
			'result_pay' => $result_pay,
			'settle_date' => $settle_date,
			// 'info_order' => $info_order,
			'pay_type' => $pay_type,
			'bank_code' => $bank_code,
			'no_agree' => $no_agree,
			'id_type' => $id_type,
			'id_no' => $id_no,
			'acct_name' => $acct_name
		);
		if (!$this->getSignVeryfy($parameter, $sign)) {
			\Log::write('re-fail', 'recharge');
			return false;
		}
		return true;
	}

	/**
	 * 针对return_url验证消息是否是连连支付发出的合法消息
	 * @return mixed 验证结果
	 */
	public function verifyReturn() {
		if (empty ($_POST)) { //判断POST来的数组是否为空
			return false;
		} else {
			$oid_partner = isset($_POST['oid_partner'])?$_POST['oid_partner']:'';
			$sign_type = isset($_POST['sign_type'])?$_POST['sign_type']:'';
			$dt_order = isset($_POST['dt_order' ])?$_POST['dt_order']:'';
			$no_order = isset($_POST['no_order' ])?$_POST['no_order']:'';
			$oid_paybill = isset($_POST['oid_paybill'])?$_POST['oid_paybill']:'';
			$money_order = isset($_POST['money_order'])?$_POST['money_order']:'';
			$result_pay = isset($_POST['result_pay'])?$_POST['result_pay']:'';
			$settle_date = isset($_POST['settle_date'])?$_POST['settle_date']:'';
			$info_order = isset($_POST['info_order'])?$_POST['info_order']:'';
			$pay_type = isset($_POST['pay_type'])?$_POST['pay_type']:'';
			$bank_code = isset($_POST['bank_code'])?$_POST['bank_code']:'';


			//首先对获得的商户号进行比对
			if (trim($oid_partner) != $this->llpay_config['oid_partner']) {
				//商户号错误
				return false;
			}

			//生成签名结果
			$parameter = array (
				'oid_partner' => $oid_partner,
				'sign_type' => $sign_type,
				'dt_order' => $dt_order,
				'no_order' =>  $no_order,
				'oid_paybill' => $oid_paybill,
				'money_order' => $money_order,
				'result_pay' =>  $result_pay,
				'settle_date' => $settle_date,
				'info_order' =>$info_order,
				'pay_type' => $pay_type,
				'bank_code' => $bank_code,
			);

			if (!$this->getSignVeryfy($parameter, trim($_POST['sign' ]))) {
				return false;
			}
			return true;

		}
	}

	/**
	 * 获取返回时的签名验证结果
	 * @param $para_temp 通知返回来的参数数组
	 * @param $sign 返回的签名结果
	 * @return mixed 签名验证结果
	 */
	public function getSignVeryfy($para_temp, $sign) {
		//除去待签名参数数组中的空值和签名参数
		$para_filter = Core::paraFilter($para_temp);

		//对待签名参数数组排序
		$para_sort = Core::argSort($para_filter);

		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = Core::createLinkstring($para_sort);

		\Log::write($prestr,'recharge');
		\Log::write($sign,'recharge');
		$isSgin = false;
		switch (strtoupper(trim($this->llpay_config['sign_type']))) {
			case "MD5" :
				$isSgin = Md5::md5Verify($prestr, $sign, $this->llpay_config['key']);
				break;
			default :
				$isSgin = false;
		}

		return $isSgin;
	}

}
?>
