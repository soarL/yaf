<?php
namespace tools;

use helpers\StringHelper;
/**
 * WebSign
 * 通用接口参数加密、验证
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class WebSign {
	
	const AUTH_KEY = 'abcdeft12345';
	const SIGN_KEY = 'sign';
	private static $msg;
	private static $signCompare;

	/**
	 * 签名验证
	 * @param array  $params    参数
	 * @param array  $expects   期望
	 * @param string $checkSign 是否验证SIGN
	 * @return boolean 是否验证通过
	 */
	public static function check($params, $expects=array(), $checkSign=true) {
		foreach ($expects as $key => $value) {
			if(!isset($params[$key])) {
				self::setMsg('缺少参数：'.$key.'【'.$value.'】！');
				return false;
			}
		}
		if(!$checkSign) {
			return true;
		}
		$sign = isset($params[self::SIGN_KEY])?$params[self::SIGN_KEY]:'';
		$paramsFilter = StringHelper::paramsFilter($params, self::SIGN_KEY);
		$paramsSort = StringHelper::paramsSort($paramsFilter);
		$paramsLinkString = StringHelper::createLinkString($paramsSort);
		$computeSign = md5($paramsLinkString.self::AUTH_KEY);
		$rdata = [];
        if($sign!=$computeSign) {
        	self::$signCompare = 'post_to_compute:' . $sign . '--to--' . $computeSign;
        	self::setMsg('签名验证失败！');
        	return false;
        }
        return true;
	}

	/**
	 * 签名
	 * @param array $params 参数
	 * @return string sign密文
	 */
	public static function sign($params) {
		$paramsFilter = StringHelper::paramsFilter($params, self::SIGN_KEY);
		$paramsSort = StringHelper::paramsSort($paramsFilter);
		$paramsLinkString = StringHelper::createLinkString($paramsSort);
		$sign = md5($paramsLinkString.self::AUTH_KEY);
        return $sign;
	}

	public static function getMsg() {
		return self::$msg;
	}

	private static function setMsg($msg) {
		self::$msg = $msg;
	}

	public static function getSignCompare() {
		return self::$signCompare;
	}
}
