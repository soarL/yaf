<?php
namespace models;

use Illuminate\Database\Eloquent\Model;
use Yaf\Registry;
use helpers\NetworkHelper;
use helpers\StringHelper;
use tools\Log;
class Sms extends Model {

	protected $table = 'system_smslog';

	public $timestamps = false;

	const URL = 'http://www.jianzhou.sh.cn/JianzhouSMSWSServer/http/sendBatchMessage';
	const SOAP_URL = 'http://www.jianzhou.sh.cn/JianzhouSMSWSServer/services/BusinessService?wsdl';
	const APPEND = '';
	const ACCOUNT = 'sdk_ruanyin57';
	const PASSWORD = '6400111';
	const CODE_LENGTH = 6;
	const PHONE_DAY_MAX = 20;
	const CHECK_MAX = 5;
	const IP_DAY_MAX = 1000000;
	const EXPIRE_TIME = 600;

	const APPID = '1400036997';
	const APPKEY = '1de657c88bee77e9b3be65c3f27f0fb5';

	// public static $msgTypes = [
	// 	'register' => '您正在执行注册操作，验证码是#AUTHCODE#。',
	// 	'find_loginpass' => '您正在执行修改登录密码操作，验证码是#AUTHCODE#。',
	// 	'find_paypass' => '您正在执行修改支付密码操作，验证码是#AUTHCODE#。',
	// 	'forget' => '您正在执行找回密码操作，验证码是#AUTHCODE#。',
	// 	'forgetPaypass' => '您正在执行找回支付密码操作，验证码是#AUTHCODE#。',
	// 	'buyClaim' => '您正在执行购买债权操作，验证码是#AUTHCODE#。',
	// 	'regPassword' => '您成功领取了红包！您的汇诚普惠账户登录密码为：#AUTHCODE#',
	// 	'xhztRegister' => '您已成功注册汇诚普惠！账户初始登录密码为：#AUTHCODE#，请及时登录官网www.hcjrfw.com修改密码！',//星火智投注册接口
	// 	'transfer' => '您正在转让债权，验证码是：#AUTHCODE#',
	// 	'orderLoan' => '您申请借款，验证码是：#AUTHCODE#',
	// 	'rehear' => '1',

	// ];

	public static $msg = [
		'register' => '执行注册操作',
		'login' => '执行登录操作',
		'find_loginpass' => '执行修改登录密码操作',
		'find_paypass' => '执行修改支付密码操作',
		'forget' => '执行找回密码操作',
		'forgetPaypass' => '执行找回支付密码操作',
		'buyClaim' => '执行购买债权操作',
		'regPassword' => '您成功领取了红包！您的汇诚普惠账户登录密码为：#AUTHCODE#',
		'xhztRegister' => '您已成功注册汇诚普惠！账户初始登录密码为：#AUTHCODE#，请及时登录官网www.hcjrfw.com修改密码！',//星火智投注册接口
		'transfer' => '转让债权',
		'orderLoan' => '您申请借款',
	];

	public static $tplid = [
		'register' => '30974',
		'find_loginpass' => '30974',
		'find_paypass' => '30974',
		'forget' => '30974',
		'login' => '30974',
		'forgetPaypass' => '30974',
		'buyClaim' => '30974',
		'regPassword' => '30974',
		'xhztRegister' => '30974',
		'transfer' => '30974',
		'orderLoan' => '30974',
		'rehear' => '80300',
		'userbid' => '80296',
		'withdraw' => '80293',
		'recharge' => '80292',
		'goRehear' => '80505',
		'advanceRepay' => '80492',
		'normalRepay' => '80416',
		'interestRepay' => '80980',
		'buyCrtr' => '80408',
		'sellCrtr' => '80400',
		'delayNotice' => '80496',
		'delayRepay' => '80502',
		'registerSuccess' => '80897',
		'rehearRedpack' => '81223',
		'bidRedpack' => '81225',
		'sellCrtrFee' => '81227',
		'moveMoney' => '91026',
	];

	public static $system_sms = [
		'rehear',
		'rehearRedpack',
		'userbid',
		'withdraw',
		'recharge',
		'goRehear',
		'advanceRepay',
		'normalRepay',
		'interestRepay',
		'buyCrtr',
		'sellCrtr',
		'delayNotice',
		'delayRepay',
		'registerSuccess',
		'sellCrtrFee',
		'bidRedpack',
		'moveMoney',

	];

	public static $msgContent = [
		'30974' => '手机验证码：{1}。您正在{2}。该验证码在{3}分钟内有效。如非本人操作，请忽略此短信。',
		'80300' => '{1}您好！项目{2}已复审成功。您投资本金为{3}元，预计总收益{4}元，预期结清日期：{5}。感谢您对本平台的支持！',
		'80296' => '{1}您好！您已通过{2}购买项目{3}的本金{4}元（{5}月/{6}/年化利率{7}），项目复审后计息，感谢您对本平台的支持！',
		'80293' => '{1}您好！您于{2} 提现申请成功。提现金额：{3}元，扣除手续费：{4}元，实际到帐金额：{5}元。若非本人操作请致电400-080-8885',
		'80292' => '{1}您好！您于{2}充值的{3}元已到帐，请登录平台账号查询！',
		'80505' => '项目{1}标已于{2}满标，请及时登录后台进行复审！',
		'80492' => '{1}您好！项目{2}已进行提前结清。您已收到本金{3}元，利息{4}元，利息管理费{5}元，实际到帐{6}元，请登录平台账号查询！',
		'80416' => '{1}您好！您已收到项目{2}第{3}期回款本金{4}元，回款利息{5}元，利息管理费{6}元，实际到帐{7}元，请登录平台账号查询！',
		'80980' => '{1}您好！您已收到项目{2}第{3}期回款利息{4}元，利息管理费{5}元，实际到帐{6}元。',
		'80408' => '{1}您好！您已成功承接{2}中的债权本金{3}元，并支付未结利息{4}元，您将获得该笔债权后续的回款收益，若有疑问欢迎致电400-080-8885',
		'80400' => '{1}您好！您所发布的{2}已转让债权本金{3}元，获得未结利息{4}元，实际到帐{5}元，剩余债权本金{6}元，请登录平台账号查询！',
		'80502' => '{1}您好！逾期项目{2}已结清。您已收到回款本金{3}元，逾期罚息{4}元，回款利息{5}元，支付利息管理费{6}元，实际到帐{7}元，请登录平台账号查询！',
		'80496' => '{1}您好！项目{2}未按时回款，平台将每日计实际本金的万分之一为罚息（原利息正常计），若有疑问欢迎致电400-080-8885',
		'80897' => '您好，账号已经注册成功！请登录后台查询投资红包，实名认证后即可参与投资，咨询热线400-080-8885',
		'81223' => '{1}您好！项目{2}已复审成功，获得红包{3}元。您投资本金为{4}元，预计总收益{5}元，预期结清日期：{6}。感谢您对本平台的支持！',
		'81225' => '{1}您好！您已通过{2}购买项目{3}的本金{4}元（{5}月/{6}/年化利率{7}），使用投资红包券{8}元，项目满标复审后计息并兑现红包，感谢您对本平台的支持！',
		'81227' => '{1}您好！您所发布的{2}已转让债权本金{3}元，获得未结利息{4}元，支付转让费{5}元， 实际到帐{6}元，剩余债权本金{7}元，请登录平台账号查询！',
		'91026' => '您好！汇诚普惠旧系统项目已全部结清，系统已将您账户余额 {1}元迁移至新系统，请你及时登录新系统中核对账户余额。汇诚普惠合规前行，感恩有您！'
	];

	public static function dxTwo($content, $phone) {
		return false;
		$sendData = [];
		$sendData['account'] = static::ACCOUNT;
		$sendData['password'] = static::PASSWORD;
		$sendData['destmobile'] = $phone;
		$sendData['msgText'] = $content;
		return NetworkHelper::curlRequest(self::URL,$sendData,'post');
	}

    public static function setSmsData($strMobile, $params, $tpl_id = 30974, $ext = "") {
        $app_id = static::APPID;
        $app_key = static::APPKEY;
        $data = new \stdClass();
        $tel = new \stdClass();
        $strTime = time();
        $tel->nationcode = "86";
        $tel->mobile = $strMobile;
        $data->tel = $tel;
        $data->sign = "";
        $data->tpl_id = $tpl_id; //手机验证码：{1}。您正在{2}。该验证码在{3}分钟内有效。如非本人操作，请忽略此短信。
        foreach ($params as $key => $value) {
        	$params[$key] = strval($value);
        }
        $data->params = $params;
        $strRand = rand(100000,999999);
        $data->sig = hash("sha256", "appkey=" . $app_key . "&random=" . $strRand . "&time=" . $strTime . "&mobile=" . $strMobile);
        $data->time = $strTime;
        $data->extend = "";
        $data->ext = $ext;
        $url = 'https://yun.tim.qq.com/v5/tlssmssvr/sendsms?sdkappid=' . $app_id . '&random=' . $strRand;
        $result = NetworkHelper::CurlPost($url, json_encode($data),array('Expect:'),'0');
        $arMsg = json_decode($result,true);
        Log::write('短信发送:',[$arMsg,$data], 'sms');
        return (0==$arMsg['result'])?'SUCCESS':'ERROR';
    }

	public static function dxOne($content, $phone, $way=1) {
		return false;
		$user = '300157';
		$pass = 'WOEIW144TB';
		$spcode = '54000330';
		if($way==2) {
			$user = '402276';
			$pass = 'JT71Z1SXU1';
			$spcode = '54000329';
		}
	    $cust_code = $user;
	    $password = $pass;
	    $sp_code = $spcode;
	    $destMobiles = $phone;
	    $url='http://43.243.130.33:8860/';
	    $post_data = array();
	    $post_data['cust_code'] = $cust_code;                                                                                   
	    $post_data['destMobiles'] = $destMobiles;
	    $post_data['content'] =  $content;
	    $post_data['sign'] = md5(urlencode($content.$password));
	    $post_data['sp_code'] = $sp_code;
	    $result = NetworkHelper::curlRequest($url, $post_data, 'post');
	    $resultArray = explode(':', $result);
	    return isset($resultArray[0])?$resultArray[0]:'ERROR';
	}

	public static function send($data){
		$rdata = [];
		if(!isset(self::$tplid[$data['msgType']])) {
			$rdata['status'] = 0;
			$rdata['info'] = '发送类型不存在！';
			return $rdata;
		}

		if(!in_array($data['msgType'], self::$system_sms)){
			$lastLog = self::getLastLogByPhone($data['phone'], $data['msgType']);
			if(time()-strtotime($lastLog['sendTime'])<60) {
				$rdata['status'] = 0;
				$rdata['info'] = '发送过于频繁，请稍后再发送！';
				return $rdata;
			}

			$sendCount = self::getDaySendCountByPhone($data['phone']);
			if($sendCount>=self::PHONE_DAY_MAX) {
				$rdata['status'] = 0;
				$rdata['info'] = '今天发送短信已达上限，请明日再试！';
				return $rdata;	
			}

			if($data['msgType']=='register' && User::isPhoneExist($data['phone'])) {
				$rdata['status'] = 0;
				$rdata['info'] = '手机号已存在！';
				return $rdata;	
			}
			$siteinfo = Registry::get('siteinfo');
			$ip = StringHelper::ipton($siteinfo['clientIp']);

			if(strpos($data['phone'],'179059')!==0) { 
				$sendCount = self::getDaySendCountByIP($ip);
				if($sendCount>=self::IP_DAY_MAX) {
					$rdata['status'] = 0;
					$rdata['info'] = '请勿频繁发送短信，请明日再试！';
					return $rdata;	
				}
			}
		}else{
			$ip = '007';
		}

		$tpl_id = self::$tplid[$data['msgType']];
        $result = self::setSmsData($data['phone'],$data['params'],$tpl_id);
		$msgData = [];
		$msgData['result'] = $result;
		$msgData['userId'] = isset($data['userId'])?$data['userId']:'';
		$msgData['phone'] = $data['phone'];
		$msgData['content'] = self::getContent(self::$msgContent[$tpl_id],$data['params']); // json_encode($data['params']);
		$msgData['sendCode'] = isset($data['code'])?$data['code']:'';
		$msgData['type'] = $data['msgType'];
		$msgData['sendTime'] = date('Y-m-d H:i:s', time());
		$msgData['ip'] = $ip;

        self::insert($msgData);

		if ($result>=0) {
			$rdata['status'] = 1;
			$rdata['info'] = '发送成功！';
			$rdata['code'] = isset($data['code'])?$data['code']:'';
			return $rdata;
		} else {
			$rdata['status'] = 0;
			$rdata['info'] = '发送失败！';
			return $rdata;
		}
	}

	public static function getContent($temp ,$params){
		foreach ($params as $key => $value) {
			$temp = str_replace('{'. ($key+1) .'}', $value, $temp);
		}
		return $temp;
	}

	public static function checkCode($phone, $phoneCode, $msgType) {
		$log = self::getLastLogByPhone($phone, $msgType);
		$rdata = [];
		if($log) {
			if($log->checkTime>=self::CHECK_MAX) {
				$rdata['status'] = 0;
				$rdata['info'] = '验证码已失效，请重新发送！';
				return $rdata;
			}
			if($log->sendCode==$phoneCode) {
				if((time()-strtotime($log->sendTime))>self::EXPIRE_TIME) {
					$rdata['status'] = 0;
					$rdata['info'] = '验证码已过期！';
					return $rdata;
				} else {
					$rdata['status'] = 1;
					$rdata['info'] = '验证成功！';
					return $rdata;
				}
			} else {
				$log->checkTime = $log->checkTime + 1;
				$log->save();
				$rdata['status'] = 0;
				$rdata['info'] = '验证码错误！';
				return $rdata;
			}
		} else {
			$rdata['status'] = 0;
			$rdata['info'] = '验证码错误！';
			return $rdata;
		}
	}

	public static function getLastLogByPhone($phone, $msgType) {
		return self::where('phone', $phone)->where('type', $msgType)->orderBy('sendTime', 'desc')->first();
	}

	public static function getDaySendCountByPhone($phone, $day='') {
		$dayBegin = '';
		$dayEnd = '';
		if($day=='') {
			$dayBegin = date('Y-m-d', time()) . ' 00:00:00';
			$dayEnd = date('Y-m-d', time()) . ' 23:59:59';
		} else {
			$dayBegin = $day . ' 00:00:00';
			$dayEnd = $day . ' 23:59:59';
		}
		return self::where('phone', $phone)->where('sendTime', '>=', $dayBegin)->where('sendTime', '<=',$dayEnd)->count();
	}

	public static function getDaySendCountByIP($ip, $day='') {
		$dayBegin = '';
		$dayEnd = '';
		if($day=='') {
			$dayBegin = date('Y-m-d', time()) . ' 00:00:00';
			$dayEnd = date('Y-m-d', time()) . ' 23:59:59';
		} else {
			$dayBegin = $day . ' 00:00:00';
			$dayEnd = $day . ' 23:59:59';
		}
		return self::where('ip', $ip)->where('sendTime', '>=', $dayBegin)->where('sendTime', '<=',$dayEnd)->count();
	}

	public static function generateCode($length) {
        $prepareStrArr = ['0','1','2','3','4','5','6','7','8','9'];
        $count = count($prepareStrArr);
        $randomStr = '';
        for ($i=0; $i < $length; $i++) { 
            $position = rand(0,$count-1);
            $randomStr .= $prepareStrArr[$position];
        }
        return $randomStr;
	}
}
