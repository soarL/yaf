<?php
namespace helpers;
use \Tag;

/**
 * HtmlHelper
 * Html帮助类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class HtmlHelper {
	public static function tableRecords($data, $template) {
		$records = '';
		foreach ($data as $v => $item) {
			$record = $template;
			foreach ($item as $key => $value) {
				// var_dump($record);
				$record = str_replace('#'.$key.'#', $value, $record);
			}
			$records .= $record;
		}
		return $records;
	}
}