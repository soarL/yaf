<?php
namespace tools;
use Yaf\Registry;
/**
 * 富友支付辅助类
 * @author elf <360197197@qq.com>
 */
class FYBank {
	/**
	 * 银行信息
	 * 
	 * key 为银行编码
	 * id 为银行ID，对应/data/banks.php中的key
	 * name 银行名称
	 * ichar 银行缩写
	 */
	public static $banks = [
		'0801020000' => ['id'=>2, 'name'=>'中国工商银行', 'ichar'=>'icbc'],
		'0801030000' => ['id'=>3, 'name'=>'中国农业银行', 'ichar'=>'abc'],
		'0801050000' => ['id'=>7, 'name'=>'中国建设银行', 'ichar'=>'ccb'],
		'0801040000' => ['id'=>1, 'name'=>'中国银行', 'ichar'=>'boc'],
		'0803010000' => ['id'=>4, 'name'=>'中国交通银行', 'ichar'=>'bcom'],
		'0803090000' => ['id'=>12, 'name'=>'兴业银行', 'ichar'=>'cib'],
		'0803020000' => ['id'=>13, 'name'=>'中信银行', 'ichar'=>'citic'],
		'0803030000' => ['id'=>15, 'name'=>'中国光大银行', 'ichar'=>'ceb'],
		'0804100000' => ['id'=>24, 'name'=>'平安银行', 'ichar'=>'sdb'],
		'0801000000' => ['id'=>28, 'name'=>'中国邮政储蓄银行', 'ichar'=>'psbc'],
		'0803040000' => ['id'=>14, 'name'=>'华夏银行', 'ichar'=>'hxb'],
		'0803060000' => ['id'=>5, 'name'=>'广发银行', 'ichar'=>'gdb'],
		'0803100000' => ['id'=>8, 'name'=>'浦发银行', 'ichar'=>'spdb'],
		'0803050000' => ['id'=>11, 'name'=>'中国民生银行', 'ichar'=>'cmbc'],
		'0803080000' => ['id'=>10, 'name'=>'招商银行', 'ichar'=>'cmb'],
	];
}