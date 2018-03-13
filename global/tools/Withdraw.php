<?php
namespace tools;
use helpers\NetworkHelper;
/**
 * 移至后台，未启用。
 */
class Withdraw {
	const KEY = '153aae6a576b88db9825c17f0f5d8d49bc2018d970f6ff6b63fc97ad';
	const API = 'http://gateway.yemadai.com/hostingWithdrawCash';
	const ADVICE = 'http://www.lvpins.com/huicao/ok.php';
	const ACCOUNT = '1508000';

	public static function execute($data=array()) {
		$postData['transData'] = self::getXml($data);
		$result = NetworkHelper::post(self::API, $postData);
		return $result;
	}

	private static function getXml($data){
		$bank = $data['bank'];

		$merchantKey = self::KEY;
		$adviceURL = self::ADVICE;
		$accountNumber = self::ACCOUNT;

		$tradeNo = date("YmdHis");
		$bankName = $bank['bankName'];
		$provice = $bank['province'];
		$city = $bank['city'];
		$branchName = $bank['subbranch'];
		$cardNo = $bank['bankNum'];
		
		$nickName = $data['account'];
		$amount = $data['money'];
		$fee = $data['fee'];
		$remark = $data['remark'];

		$transData  = "";
		$transData .= "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>";
		$transData .= "<yimadai>";
		$transData .= 	"<accountNumber>".$accountNumber."</accountNumber>";//商户数字id
		$transData .= 	"<adviceURL>".$adviceURL."</adviceURL>";//商户数字id
		$transData .= 	"<transfer>";
		$transData .= 	"<outTradeNo>".$tradeNo."</outTradeNo>";
		$transData .=	"<bankName>".$bankName."</bankName>";
		$transData .=	"<provice>".$provice."</provice>";
		$transData .= 	"<city>".$city."</city>";
		$transData .= 	"<branchName>".$branchName."</branchName>";
		$transData .=	"<nickName>".$nickName."</nickName>";
		$transData .=	"<cardNo>".$cardNo."</cardNo>";
		$transData .= 	"<amount>".$amount."</amount>";
		$transData .= 	"<fee>".$fee."</fee>";
		$transData .= 	"<remark>".$remark."</remark>";
		$transData .= 	"<secureCode>".strtolower(md5($accountNumber.$tradeNo.$bankName.$provice.$city.$branchName.$nickName.$cardNo.$amount.$fee.$remark.$merchantKey))."</secureCode>";
		$transData .= 	"</transfer>";	
		$transData .=   "</yimadai>";
		$transData = base64_encode($transData);
		return $transData;
	}
}