<?php
namespace plugins\lianlian\lib;
class LLpaySubmit {
	public $llpay_config;
	/**
	 *连连支付网关地址
	 */
	public $llpay_gateway = '';

	function __construct($llpay_config, $gateway='gateway') {
		$this->llpay_config = $llpay_config;
		$this->llpay_gateway = $llpay_config[$gateway];
	}

	/**
	 * 生成签名结果
	 * @param $para_sort 已排序要签名的数组
	 * return 签名结果字符串
	 */
	public function buildRequestMysign($para_sort) {
		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = Core::createLinkstring($para_sort);
		// echo $prestr;echo '<br>';
		$mysign = "";
		switch (strtoupper(trim($this->llpay_config['sign_type']))) {
			case "MD5" :
				$mysign = Md5::md5Sign($prestr, $this->llpay_config['key']);
				break;
			case "RSA" :
				$mysign = Rsa::RsaSign($prestr, $this->llpay_config['RSA_PRIVATE_KEY']);
				break;
			default :
				$mysign = "";
		}
		// echo $mysign;die();
		return $mysign;
	}

	/**
	 * 生成要请求给连连支付的参数数组
	 * @param $para_temp 请求前的参数数组
	 * @return mixed 要请求的参数数组
	 */
	public function buildRequestPara($para_temp) {
		//除去待签名参数数组中的空值和签名参数
		$para_filter = Core::paraFilter($para_temp);
		//对待签名参数数组排序
		$para_sort = Core::argSort($para_filter);
		//生成签名结果
		$mysign = $this->buildRequestMysign($para_sort);
		//签名结果与签名方式加入请求提交参数组中
		$para_sort['sign'] = $mysign;
		$para_sort['sign_type'] = strtoupper(trim($this->llpay_config['sign_type']));
		foreach ($para_sort as $key => $value) {
			$para_sort[$key] = $value;
		}
		return $para_sort;
		//return urldecode(json_encode($para_sort));
	}

	/**
	 * 生成要请求给连连支付的参数数组
	 * @param $para_temp 请求前的参数数组
	 * @return mixed 要请求的参数数组字符串
	 */
	public function buildRequestParaToString($para_temp) {
		//待请求参数数组
		$para = $this->buildRequestPara($para_temp);
		
		//把参数组中所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
		$request_data = Core::createLinkstringUrlencode($para);
		
		return $request_data;
	}

	/**
	 * 建立请求，以表单HTML形式构造（默认）
	 * @param $para_temp 请求参数数组
	 * @param $method 提交方式。两个值可选：post、get
	 * @param $button_name 确认按钮显示文字
	 * @return mixed 提交表单HTML文本
	 */
	public function buildRequestForm($para_temp, $method, $button_name) {
		//待请求参数数组
		$para = $this->buildRequestPara($para_temp);
		$sHtml = "<form id='llpaysubmit' name='llpaysubmit' action='" . $this->llpay_gateway . "' method='" . $method . "'>";
		foreach ($para as $key => $value) {
			if($key!='risk_item') {
				$sHtml .= "<input type='hidden' name='" . $key . "' value='" . $value . "'/>";
			} else {
				$sHtml .= "<input type='hidden' name='" . $key . "' value='" . stripslashes($value) . "'/>";
			}
		}
		//submit按钮控件请不要含有name属性
		$sHtml = $sHtml . "<input style='display:none' type='submit' value='" . $button_name . "'></form>";
		$sHtml = $sHtml . '<div>前往支付，请勿关闭网页！</div>';
		$sHtml = $sHtml."<script>document.forms['llpaysubmit'].submit();</script>";
		return $sHtml;
	}

	/**
	 * 建立请求，以模拟远程HTTP的POST请求方式构造并获取连连支付的处理结果
	 * @param $para_temp 请求参数数组
	 * @return mixed 连连支付处理结果
	 */
	public function buildRequestHttp($para_temp) {
		$sResult = '';

		//待请求参数数组字符串
		$request_data = $this->buildRequestPara($para_temp);
		//远程获取数据
		$sResult = Core::getHttpResponsePOST($this->llpay_gateway, dirname(dirname(__FILE__)).$this->llpay_config['cacert'], $request_data, trim(strtolower($this->llpay_config['input_charset'])));

		return $sResult;
	}

	/**
	 * 建立请求，以模拟远程HTTP的POST请求方式构造并获取连连支付的处理结果，带文件上传功能
	 * @param $para_temp 请求参数数组
	 * @param $file_para_name 文件类型的参数名
	 * @param $file_name 文件完整绝对路径
	 * @return mixed 连连支付返回处理结果
	 */
	public function buildRequestHttpInFile($para_temp, $file_para_name, $file_name) {
		//待请求参数数组
		$para = $this->buildRequestPara($para_temp);
		$para[$file_para_name] = "@" . $file_name;

		//远程获取数据
		$sResult = Core::getHttpResponsePOST($this->llpay_gateway, $this->llpay_config['cacert'], $para, trim(strtolower($this->llpay_config['input_charset'])));

		return $sResult;
	}

	/**
	 * 建立请求，以模拟远程HTTP的POST请求方式构造并获取连连支付的处理结果
	 * @param $para_temp 请求参数数组
	 * @return mixed 连连支付处理结果
	 */
	public function buildRequestJSON($para_temp) {
		$sResult = '';

		//待请求参数数组字符串
		$request_data = $this->buildRequestPara($para_temp);
		//远程获取数据
		$sResult = Core::getHttpResponseJSON($this->llpay_gateway, $request_data);

		return $sResult;
	}

	/**
	 * 用于防钓鱼，调用接口query_timestamp来获取时间戳的处理函数
	 * 注意：该功能PHP5环境及以上支持，因此必须服务器、本地电脑中装有支持DOMDocument、SSL的PHP配置环境。建议本地调试时使用PHP开发软件
	 * return 时间戳字符串
	 */
	public function query_timestamp() {
		$url = $this->llpay_gateway . "service=query_timestamp&partner=" . trim(strtolower($this->llpay_config['partner'])) . "&_input_charset=" . trim(strtolower($this->llpay_config['input_charset']));
		$encrypt_key = "";

		$doc = new DOMDocument();
		$doc->load($url);
		$itemEncrypt_key = $doc->getElementsByTagName("encrypt_key");
		$encrypt_key = $itemEncrypt_key->item(0)->nodeValue;

		return $encrypt_key;
	}
}