<?php
/**
 * Config
 * 工具类，打印类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
namespace tools;

use Yaf\Config\Ini;
use Yaf\Config\Simple;

class Config {
	const BASE = '../../conf/'; 
	const APP = '../conf/';

	public static function get($file, $node='', $path=self::BASE, $type='ini') {
		$config = null;
		if($type=='ini') {
			$fullFile = $path.$file.'.ini';
			if(file_exists($fullFile)) {
				$config = self::getIni($fullFile, $node);
			}
		} else if($type=='array') {
			$config = [];
			$fullFile = $path.$file.'.php';
			if(file_exists($fullFile)) {
				$config = self::getArray($fullFile);
			}
		} else if($type=='json') {
			$fullFile = $path.$file.'.json';
			if(file_exists($fullFile)) {
				$config = self::getJson($fullFile);
			}
		} else if($type=='yaml') {
			$fullFile = $path.$file.'.yml';
			if(file_exists($fullFile)) {
				$config = self::getYaml($fullFile);
			}
		}
		return $config;
	}

	public static function app($file, $node='', $type='ini') {
		return self::get($file, $node, self::APP, $type);
	}

	public static function base($file, $node='', $type='ini') {
		return self::get($file, $node, self::BASE, $type);
	}

	public static function ini($file, $node='', $path=self::BASE) {
		return self::get($file, $node, $path, 'ini');
	}

	public static function arr($file, $path=self::BASE) {
		return self::get($file, '', $path, 'array');
	}

	public static function json($file, $path=self::BASE) {
		return self::get($file, '', $path, 'json');
	}

	public static function yaml($file, $path=self::BASE) {
		return self::get($file, '', $path, 'yaml');
	}

	private static function getIni($file, $node='') {
		$config = null;
		if($node=='') {
			$config = new Ini($file);
		} else {
			$config = new Ini($file, $node);
		}
		
		return $config;
	}

	private static function getArray($file) {
		$array = require($file);
		return $array;
	}

	private static function getJson($file, $isArray=true) {
		$json = file_get_contents($file);
		$data = json_decode($json, $isArray);
		return $data;
	}

	private static function getYaml($file) {
		$array = spyc_load_file($file);
		return $array;
	}
}