<?php
namespace plugins\lianlian\lib;
class Rsa {
	/**RSA签名
	 * $data签名数据(需要先排序，然后拼接)
	 * 签名用商户私钥，必须是没有经过pkcs8转换的私钥
	 * 最后的签名，需要用base64编码
	 * return Sign签名
	 */
	public static function Rsasign($data,$priKey) {
		//转换为openssl密钥，必须是没有经过pkcs8转换的私钥
	    $res = openssl_get_privatekey($priKey);
		//调用openssl内置签名方法，生成签名$sign
	    openssl_sign($data, $sign, $res, OPENSSL_ALGO_MD5);

		//释放资源
	    openssl_free_key($res);
	    
		//base64编码
		$sign = base64_encode($sign);
		//file_put_contents("log.txt","签名原串:".$data."\n", FILE_APPEND);
	    return $sign;
	}

	/**RSA验签
	 * $data待签名数据(需要先排序，然后拼接)
	 * $sign需要验签的签名,需要base64_decode解码
	 * 验签用连连支付公钥
	 * return 验签是否通过 bool值
	 */
	public static function Rsaverify($data, $sign)  {
		//读取连连支付公钥文件
		$pubKey = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCgz70LDONsdpNVPPpEyaicE3Rx
7PT6vtQU1dcC7khrkWBZEoh1uelBAV2SmT4ljOQAeib68jIA+05mKtjh7sGlbzIH
/Xg7+KdRZLcuICfUAmHidR2S3P+inAYhU52uzDmLoZkbj4dbqjU7iLM79DZYmXKS
LIz9mqvjcC2Tjuir1wIDAQAB
-----END PUBLIC KEY-----';

		//转换为openssl格式密钥
	    $res = openssl_get_publickey($pubKey);

		//调用openssl内置方法验签，返回bool值
	    $result = (bool)openssl_verify($data, base64_decode($sign), $res,OPENSSL_ALGO_MD5);
		
		//释放资源
	    openssl_free_key($res);

		//返回资源是否成功
	    return $result;
	}

}