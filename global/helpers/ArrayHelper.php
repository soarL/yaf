<?php
namespace helpers;

/**
 * ArrayHelper
 * 数组帮助类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class ArrayHelper {
	public static function isAssoc($array) {
		return array_keys($array) !== range(0, count($array) - 1); 
	}

	public static function hasSubArray($array) {
		foreach ($array as $key => $value) {
			if(is_array($value)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * 获取数组中的指定键值，不存在返回null
	 * @param  array $array 数组
	 * @param  string $key  键名
	 * @return mixed        键值
	 */
	public static function getValue(array $array, $key) {
		if(isset($array[$key])) {
			return $array[$key];
		} else {
			return null;
		}
	}
	
	/**
	 * 根据键值获取数组中的键名，不存在返回null
	 * @param  array $array 数组
	 * @param  mixed $value 键值
	 * @return mixed        键名
	 */
	public static function getKey(array $array, $value) {
		if (in_array($value, $array)) {
			return array_search($value, $array);
		} else {
			return null;
		}
	}
	
	/**
	 * 二维数组根据某个字段排序
	 * @param array $array 数组
	 * @param string $column 排序字段 
	 * @param string $sortType 排序顺序标志 desc 降序；asc 升序  
	 * @return array || boolean
	 */
	public static function sortByColumn(array $array, $column, $sortType = 'desc') {
		$arrSort = array();  
		foreach ($array AS $uniqid => $row) {  
		    foreach ($row AS $key=>$value) {  
		        $arrSort[$key][$uniqid] = $value;  
		    }  
		}  
		if (in_array($sortType, ['desc', 'asc'])) {
			$sort = ['asc' => 'SORT_ASC', 'desc' => 'SORT_DESC'];
		    array_multisort($arrSort[$column], constant($sort[$sortType]), SORT_NUMERIC, $array);
		} else {
			return false;
		}
		return $array;
	}
}