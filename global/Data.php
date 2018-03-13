<?php
/**
 * 数据导入类
 * 全局数据放在项目根目录的data目录下
 * 本地数据放在当前app的library/data下
 * @author elf <360197197@qq.com>
 */
use Yaf\Registry;
class Data {
	/**
	 * 获取数据
	 * @param  string $name 数据名，也为文件名
	 * @param  string $type 数据类型: global为全局数据，app为本地数据
	 * @return mixed
	 */
	public static function get($name, $type='global') {
		$dataList = Registry::get('appData');
		if(isset($dataList[$type.'-'.$name])) {
			return $dataList[$type.'-'.$name];
		} else {
			if($type=='global') {
				$data = false;
				$dataFile = APP_PATH.'/../data/'.$name.'.php';
				if(file_exists($dataFile)) {
					$data = require($dataFile);
					$dataList[$type.'-'.$name] = $data;
					Registry::set('appData', $dataList);
				}
				return $data;
			} else if($type=='app') {
				$data = false;
				$dataFile = APP_PATH.'/library/data/'.$name.'.php';
				if(file_exists($dataFile)) {
					$data = require($dataFile);
					$dataList[$type.'-'.$name] = $data;
					Registry::set('appData', $dataList);
				}
				return $data;
			} else {
				return false;
			}
		}
	}

	/**
	 * 获取txt文档数据
	 * @param  string $name 数据名，也为文件名
	 * @param  string $type 数据类型: global为全局数据，app为本地数据
	 * @return mixed
	 */
	public static function getFileContent($name, $type='global') {
		if($type=='global') {
			$content = file_get_contents(APP_PATH.'/../data/'.$name);
			return $content;
		} else if($type=='app') {
			$content = file_get_contents(APP_PATH.'/library/data/'.$name);
			return $content;
		} else {
			return false;
		}
		
	}
}