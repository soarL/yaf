<?php
namespace tools;
use Yaf\Registry;
/**
 * 民生支付辅助类
 * @author elf <360197197@qq.com>
 */
class MSBank {
	/**
	 * 银行信息
	 * 
	 * key 为银行编码
	 * id 为银行ID，对应/data/banks.php中的key
	 * name 银行名称
	 * ichar 银行缩写
	 */
	public static $banks = [
		'01020000' => ['id'=>2, 'name'=>'中国工商银行', 'ichar'=>'icbc', 'dcl'=>0, 'ccl'=>0, 'bcl'=>1500],
		'01050000' => ['id'=>7, 'name'=>'中国建设银行', 'ichar'=>'ccb', 'dcl'=>0, 'ccl'=>0, 'bcl'=>0],
		'01030000' => ['id'=>3, 'name'=>'中国农业银行', 'ichar'=>'abc', 'dcl'=>0, 'ccl'=>0, 'bcl'=>1500],
		'03080000' => ['id'=>10, 'name'=>'招商银行', 'ichar'=>'cmb', 'dcl'=>0, 'ccl'=>0, 'bcl'=>0],
		'03010000' => ['id'=>4, 'name'=>'交通银行', 'ichar'=>'bcom', 'dcl'=>0, 'ccl'=>0, 'bcl'=>0],
		'01040000' => ['id'=>1, 'name'=>'中国银行', 'ichar'=>'boc', 'dcl'=>0, 'ccl'=>0, 'bcl'=>0],
		'03050000' => ['id'=>11, 'name'=>'中国民生银行', 'ichar'=>'cmbc', 'dcl'=>0, 'ccl'=>0, 'bcl'=>0],
		'03030000' => ['id'=>15, 'name'=>'中国光大银行', 'ichar'=>'ceb', 'dcl'=>0, 'ccl'=>0, 'bcl'=>0],
		'03060000' => ['id'=>5, 'name'=>'广发银行', 'ichar'=>'gdb', 'dcl'=>0, 'ccl'=>1500, 'bcl'=>0],
		'03090000' => ['id'=>12, 'name'=>'兴业银行', 'ichar'=>'cib', 'dcl'=>1500, 'ccl'=>1500, 'bcl'=>0],
		'01000000' => ['id'=>28, 'name'=>'中国邮政储蓄银行', 'ichar'=>'psbc', 'dcl'=>0, 'ccl'=>0, 'bcl'=>0],
		'03100000' => ['id'=>8, 'name'=>'浦发银行', 'ichar'=>'spdb', 'dcl'=>0, 'ccl'=>0, 'bcl'=>1500],
		'03020000' => ['id'=>13, 'name'=>'中信银行', 'ichar'=>'citic', 'dcl'=>0, 'ccl'=>0, 'bcl'=>0],
		'03070000' => ['id'=>24, 'name'=>'平安银行', 'ichar'=>'sdb', 'dcl'=>0, 'ccl'=>0, 'bcl'=>0],
		'03040000' => ['id'=>14, 'name'=>'华夏银行', 'ichar'=>'hxb', 'dcl'=>0, 'ccl'=>0, 'bcl'=>0],
		'04083320' => ['id'=>21, 'name'=>'宁波银行', 'ichar'=>'bonb', 'dcl'=>0, 'ccl'=>0, 'bcl'=>1500],
		'03200000' => ['id'=>100, 'name'=>'东亚银行', 'ichar'=>'bea', 'dcl'=>0, 'ccl'=>1500, 'bcl'=>0],
		'04012900' => ['id'=>101, 'name'=>'上海银行', 'ichar'=>'bosh', 'dcl'=>0, 'ccl'=>0, 'bcl'=>0],
		'04243010' => ['id'=>102, 'name'=>'南京银行', 'ichar'=>'bonj', 'dcl'=>0, 'ccl'=>0, 'bcl'=>0],
		// '65012900' => ['id'=>26, 'name'=>'上海农商行', 'ichar'=>'srcb', 'dcl'=>0, 'ccl'=>0, 'bcl'=>0],
		'03170000' => ['id'=>103, 'name'=>'渤海银行', 'ichar'=>'cbb', 'dcl'=>0, 'ccl'=>0, 'bcl'=>0],
		'64296510' => ['id'=>104, 'name'=>'成都银行', 'ichar'=>'bocd', 'dcl'=>0, 'ccl'=>0, 'bcl'=>0],
		'04031000' => ['id'=>16, 'name'=>'北京银行', 'ichar'=>'bobj', 'dcl'=>0, 'ccl'=>0, 'bcl'=>0],
	];

	/* 借计卡 */
	public static $debitCard = ['01020000', '01030000', '01040000', '01050000', '03010000', '03080000', '03030000', '03050000', '03020000', '03060000', '03100000', '03070000', '01000000', '03040000', '04083320', '03200000', '04012900', '04243010', '03170000', '64296510', '04031000', '03090000'];

	/* 信用卡 */
	public static $creditCard = ['01020000', '03100000', '03060000', '03090000', '03200000'];

	/* 混合通道 */
	public static $blendCard = ['01050000', '03010000', '01040000', '03080000', '03050000', '03030000', '03060000', '03090000', '04012900', '03070000', '01020000', '01030000', '04083320', '03100000'];

	/**
	 * 获取通讯公私钥
	 * @param  string $type    public|private
	 * @param  string $company xwsd|ms
	 * @return string          key
	 */
	public static function getKey($type='public', $company='xwsd') {
		if($company=='xwsd') {
			if($type=='public') {
				$keyFile = Registry::get('config')->get('minsheng')->get('xwsdPublicKey');
				return \Data::getFileContent('msKey/'.$keyFile);
			} else if($type=='private') {
				$keyFile = Registry::get('config')->get('minsheng')->get('xwsdPrivateKey');
				return \Data::getFileContent('msKey/'.$keyFile);
			} else {
				return '';
			}
		} else if($company=='ms') {
			if($type=='public') {
				$keyFile = Registry::get('config')->get('minsheng')->get('msPublicKey');
				return \Data::getFileContent('msKey/'.$keyFile);
			} else {
				return '';
			}
		} else {
			return '';
		}
	}
}