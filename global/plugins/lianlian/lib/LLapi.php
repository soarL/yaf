<?php
namespace plugins\lianlian\lib;
use plugins\lianlian\Config;

/**
 * LLapi
 * 连连支付一些接口
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class LLapi {
	/**
	 * 银行信息
	 * 
	 * key 为银行编码
	 * id 为银行ID，对应/data/banks.php中的key
	 * name 银行名称
	 * ichar 银行缩写
	 */
	public static $banks = [
		'01020000' => ['id'=>2, 'name'=>'中国工商银行', 'ichar'=>'icbc'],
		'01050000' => ['id'=>7, 'name'=>'中国建设银行', 'ichar'=>'ccb'],
		'01030000' => ['id'=>3, 'name'=>'中国农业银行', 'ichar'=>'abc'],
		'03080000' => ['id'=>10, 'name'=>'招商银行', 'ichar'=>'cmb'],
		'03010000' => ['id'=>4, 'name'=>'交通银行', 'ichar'=>'bcom'],
		'01040000' => ['id'=>1, 'name'=>'中国银行', 'ichar'=>'boc'],
		'03050000' => ['id'=>11, 'name'=>'中国民生银行', 'ichar'=>'cmbc'],
		'03030000' => ['id'=>15, 'name'=>'中国光大银行', 'ichar'=>'ceb'],
		'03060000' => ['id'=>5, 'name'=>'广发银行', 'ichar'=>'gdb'],
		'03090000' => ['id'=>12, 'name'=>'兴业银行', 'ichar'=>'cib'],
		'01000000' => ['id'=>28, 'name'=>'中国邮政储蓄银行', 'ichar'=>'psbc'],
		'03100000' => ['id'=>8, 'name'=>'浦发银行', 'ichar'=>'spdb'],
		'03020000' => ['id'=>13, 'name'=>'中信银行', 'ichar'=>'citic'],
		'03070000' => ['id'=>24, 'name'=>'平安银行', 'ichar'=>'sdb'],
		'03040000' => ['id'=>14, 'name'=>'华夏银行', 'ichar'=>'hxb'],

		'04083320' => ['id'=>21, 'name'=>'宁波银行', 'ichar'=>'bonb'],
		'03200000' => ['id'=>100, 'name'=>'东亚银行', 'ichar'=>'bea'],
		'04012900' => ['id'=>101, 'name'=>'上海银行', 'ichar'=>'bosh'],
		'04243010' => ['id'=>102, 'name'=>'南京银行', 'ichar'=>'bonj'],
		// '65012900' => ['id'=>26, 'name'=>'上海农商行', 'ichar'=>'srcb'],
		'03170000' => ['id'=>103, 'name'=>'渤海银行', 'ichar'=>'cbb'],
		'64296510' => ['id'=>104, 'name'=>'成都银行', 'ichar'=>'bocd'],
		'04031000' => ['id'=>16, 'name'=>'北京银行', 'ichar'=>'bobj'],

	];

	public static $authpayBanks = [
		'01020000', '01050000', '01030000',
		'03080000', '03010000', '01040000',
		'03050000', '03030000', '03060000',
		'03090000', '01000000', '03100000',
		'03020000', '03070000', '03040000',
	];

	public static $gatewayBanks = [
		'01020000', '01050000', '01030000',
		'03080000', '03010000', '01040000',
		'03050000', '03030000', '03060000',
		'03090000', '01000000', '03100000',
		'03020000', '03070000', '03040000',
		'04083320', '03200000', '04012900',
		'04243010', '03170000', '64296510',
		'04031000',
	];

	public static function getUserBankCard($userId) {
		$config = Config::$params;
        $params = [];
        $params['oid_partner'] = $config['oid_partner'];
        $params['user_id'] = $userId;
        $params['platform'] = '';
        $params['pay_type'] = 'D';
        $params['no_agree'] = '';
        $params['card_no'] = '';
        $params['offset'] = '0';
        $params['sign_type'] = $config['sign_type'];
        $llpaySubmit = new LLpaySubmit($config, 'userbankcard');
        $result = $llpaySubmit->buildRequestJSON($params);
        $result = json_decode($result, true);
        $agreements = [];
        if($result['ret_code']=='0000') {
            $agreements = $result['agreement_list'];
        }
        return $agreements;
	}

	public static function getBankCardInfo($cardNo) {
		$config = Config::$params;
        $params = [];
        $params['oid_partner'] = $config['oid_partner'];
        $params['card_no'] = $cardNo;
        $params['flag_amt_limit'] = '1';
        $params['pay_type'] = 'D';
        $params['sign_type'] = $config['sign_type'];
        $llpaySubmit = new LLpaySubmit($config, 'bankcardquery');
        $result = $llpaySubmit->buildRequestJSON($params);
        \Log::write($cardNo.'---'.$result, 'bankQuery');
        $result = json_decode($result, true);
        return $result;
	}
}
?>
