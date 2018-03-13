<?php
/**
 * RedisFactory
 * Redis工厂类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
namespace factories;

use base\Factory;
use Yaf\Registry;
use tools\Config;
use tools\Log;
use \Redis;

class RedisFactory implements Factory {
	const INI_NAME = 'redis';

	public static $redis = null;

	public static $defaultValues = [
		'host' => '127.0.0.1',
		'port' => 6379,
		'database' => 0,
		'password' => null,
		'timeout' => 5,
		'read_write_timeout' => -1,
	];

	public static function create($params=[]) {
		if(self::$redis) {
			return self::$redis;
		}
		$item = Config::get(self::INI_NAME);
		if($item&&$item->open==1) {
			$config = self::getConfig($item->default);
			$redis = new Redis();
	        $redis->connect($config['host'], $config['port'], $config['timeout']);
	        if($config['password']!==null) {
	        	$redis->auth($config['password']);
	        }
	        self::$redis = $redis;
			return $redis;
		}
		return null;
	}

	private static function getConfig($item) {
		$config = [];
		$config['host'] = _value($item->host, self::$defaultValues['host']);
		$config['port'] = _value($item->port, self::$defaultValues['port']);
		$config['database'] = _value($item->database, self::$defaultValues['database']);
		$config['password'] = _value($item->password, self::$defaultValues['password']);
		$config['timeout'] = _value($item->charset, self::$defaultValues['timeout']);
		$config['read_write_timeout'] = _value($item->prefix, self::$defaultValues['read_write_timeout']);
		return $config;
	}
}