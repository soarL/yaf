<?php
namespace tools;
use Yaf\Registry;
/**
 * 民生支付辅助类
 * @author elf <360197197@qq.com>
 */
class BFBank {
	/**
	 * 银行信息
	 * 
	 * key 为银行编码
	 * id 为银行ID，对应/data/banks.php中的key
	 * name 银行名称
	 * ichar 银行缩写
	 */
	public static $banks = [
		'ICBC' => ['id'=>2, 'name'=>'中国工商银行', 'ichar'=>'icbc'],
		'ABC' => ['id'=>3, 'name'=>'中国农业银行', 'ichar'=>'abc'],
		'CCB' => ['id'=>7, 'name'=>'中国建设银行', 'ichar'=>'ccb'],
		'BOC' => ['id'=>1, 'name'=>'中国银行', 'ichar'=>'boc'],
		'BOCOM' => ['id'=>4, 'name'=>'交通银行', 'ichar'=>'bcom'],
		'CIB' => ['id'=>12, 'name'=>'兴业银行', 'ichar'=>'cib'],
		'CITIC' => ['id'=>13, 'name'=>'中信银行', 'ichar'=>'citic'],
		'CEB' => ['id'=>15, 'name'=>'中国光大银行', 'ichar'=>'ceb'],
		'PAB' => ['id'=>24, 'name'=>'平安银行', 'ichar'=>'sdb'],
		'PSBC' => ['id'=>28, 'name'=>'中国邮政储蓄银行', 'ichar'=>'psbc'],
		'SHB' => ['id'=>101, 'name'=>'上海银行', 'ichar'=>'bosh'],
		'SPDB' => ['id'=>8, 'name'=>'浦发银行', 'ichar'=>'spdb'],
		'CMBC' => ['id'=>11, 'name'=>'中国民生银行', 'ichar'=>'cmbc'],
		// 'CMB' => ['id'=>10, 'name'=>'招商银行', 'ichar'=>'cmb'],
	];

	public static $cxBanks = [
		'3001' => ['id'=>10, 'name'=>'招商银行', 'ichar'=>'cmb'],
		'3002' => ['id'=>2, 'name'=>'中国工商银行', 'ichar'=>'icbc'],
		'3003' => ['id'=>7, 'name'=>'中国建设银行', 'ichar'=>'ccb'],
		'3004' => ['id'=>8, 'name'=>'浦发银行', 'ichar'=>'spdb'],
		'3005' => ['id'=>3, 'name'=>'中国农业银行', 'ichar'=>'abc'],
		'3006' => ['id'=>11, 'name'=>'中国民生银行', 'ichar'=>'cmbc'],
		'3009' => ['id'=>12, 'name'=>'兴业银行', 'ichar'=>'cib'],
		'3020' => ['id'=>4, 'name'=>'交通银行', 'ichar'=>'bcom'],
		'3022' => ['id'=>15, 'name'=>'中国光大银行', 'ichar'=>'ceb'],
		'3026' => ['id'=>1, 'name'=>'中国银行', 'ichar'=>'boc'],
		'3032' => ['id'=>16, 'name'=>'北京银行', 'ichar'=>'bobj'],
		'3035' => ['id'=>24, 'name'=>'平安银行', 'ichar'=>'sdb'],
		'3036' => ['id'=>5, 'name'=>'广发银行', 'ichar'=>'gdb'],
		'3037' => ['id'=>26, 'name'=>'上海农商行', 'ichar'=>'srcb'],
		'3038' => ['id'=>28, 'name'=>'中国邮政储蓄银行', 'ichar'=>'psbc'],
		'3039' => ['id'=>13, 'name'=>'中信银行', 'ichar'=>'citic'],
		'3050' => ['id'=>14, 'name'=>'华夏银行', 'ichar'=>'hxb'],
		'3059' => ['id'=>101, 'name'=>'上海银行', 'ichar'=>'bosh'],
	];

	public static $xyBanks = [
		'4001' => ['id'=>10, 'name'=>'招商银行', 'ichar'=>'cmb'],
		'4002' => ['id'=>2, 'name'=>'中国工商银行', 'ichar'=>'icbc'],
		'4003' => ['id'=>7, 'name'=>'中国建设银行', 'ichar'=>'ccb'],
		'4004' => ['id'=>8, 'name'=>'浦发银行', 'ichar'=>'spdb'],
		'4005' => ['id'=>3, 'name'=>'中国农业银行', 'ichar'=>'abc'],
		'4006' => ['id'=>11, 'name'=>'中国民生银行', 'ichar'=>'cmbc'],
		'4009' => ['id'=>12, 'name'=>'兴业银行', 'ichar'=>'cib'],
		'4020' => ['id'=>4, 'name'=>'交通银行', 'ichar'=>'bcom'],
		'4022' => ['id'=>15, 'name'=>'中国光大银行', 'ichar'=>'ceb'],
		'4026' => ['id'=>1, 'name'=>'中国银行', 'ichar'=>'boc'],
		'4032' => ['id'=>16, 'name'=>'北京银行', 'ichar'=>'bobj'],
		'4035' => ['id'=>24, 'name'=>'平安银行', 'ichar'=>'sdb'],
		'4036' => ['id'=>5, 'name'=>'广发银行', 'ichar'=>'gdb'],
		'4037' => ['id'=>26, 'name'=>'上海农商行', 'ichar'=>'srcb'],
		'4038' => ['id'=>28, 'name'=>'中国邮政储蓄银行', 'ichar'=>'psbc'],
		'4039' => ['id'=>13, 'name'=>'中信银行', 'ichar'=>'citic'],
		'4050' => ['id'=>14, 'name'=>'华夏银行', 'ichar'=>'hxb'],
		'4059' => ['id'=>101, 'name'=>'上海银行', 'ichar'=>'bosh'],
	];

	/**
	 * 获取通讯公私钥
	 * @param  string $type    public|private
	 * @param  string $company xwsd|ms
	 * @return string          key
	 */
	public static function getKey($type='public', $company='hc') {
		if($company=='hc') {
			if($type=='public') {
				$keyFile = Registry::get('config')->get('baofoo')->get('hc_pub');
				return \Data::getFileContent('bfKey/'.$keyFile);
			} else if($type=='private') {
				$keyFile = Registry::get('config')->get('baofoo')->get('hc_key');
				return \Data::getFileContent('bfKey/'.$keyFile);
			} else {
				return '';
			}
		} else if($company=='pay_hc') {
			if($type=='public') {
				$keyFile = Registry::get('config')->get('baofoo')->get('pay_hc_pub');
				return \Data::getFileContent('bfKey/'.$keyFile);
			} else if($type=='private') {
				$keyFile = Registry::get('config')->get('baofoo')->get('pay_hc_key');
				return \Data::getFileContent('bfKey/'.$keyFile);
			} else {
				return '';
			}
		} else if($company=='wap_hc') {
			if($type=='public') {
				$keyFile = Registry::get('config')->get('baofoo')->get('wap_hc_pub');
				return \Data::getFileContent('bfKey/'.$keyFile);
			} else if($type=='private') {
				$keyFile = Registry::get('config')->get('baofoo')->get('wap_hc_key');
				return \Data::getFileContent('bfKey/'.$keyFile);
			} else {
				return '';
			}
		}
	}
}