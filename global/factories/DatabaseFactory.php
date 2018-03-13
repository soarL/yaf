<?php
/**
 * DatabaseFactory
 * Database工厂类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
namespace factories;

use base\Factory;
use Yaf\Registry;
use tools\Config;
use tools\Log;
use tools\Paginator;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Database\Capsule\Manager as Capsule;

class DatabaseFactory implements Factory {
	const INI_NAME = 'databases';

	public static $defaultValues = [
		'driver' => 'mysql',
		'host' => 'localhost',
		'port' => '3306',
		'database' => '',
		'username' => '',
		'password' => '',
		'charset' => 'urf8',
		'collation' => 'utf8_unicode_ci',
		'prefix' => ''
	];

	public static function create($params=[]) {
		$databases = Config::get(self::INI_NAME);
		$capsule = new Capsule();
		$capsule->setEventDispatcher(new Dispatcher(new Container));

		foreach ($databases as $name => $database) {
			$config = self::getConfig($database);
			$capsule->addConnection($config, $name);
		}
		$capsule->setAsGlobal();
		$capsule->bootEloquent();

		// 记录执行sql
		Capsule::listen(function($sql){
			if(APP_ENV=='product') {
				if(strpos($sql->sql, 'select')!==0) {
	        		Log::write($sql->sql.' ['.$sql->time.'ms]', $sql->bindings, 'sql');
	        	}
        	} else {
        		Log::write($sql->sql.' ['.$sql->time.'ms]', $sql->bindings, 'sql');
        	}
        });

		self::bindEvents();

		return $capsule;
	}

	private static function getConfig($database) {
		$config = [];
		$config['driver'] = _value($database->driver, self::$defaultValues['driver']);
		$config['database'] = _value($database->database, self::$defaultValues['database']);
		$config['username'] = _value($database->username, self::$defaultValues['username']);
		$config['password'] = _value($database->password, self::$defaultValues['password']);
		$config['charset'] = _value($database->charset, self::$defaultValues['charset']);
		$config['collation'] = _value($database->collation, self::$defaultValues['collation']);
		$config['prefix'] = _value($database->prefix, self::$defaultValues['prefix']);

		if(!$database->host) {
			if($database->read&&$database->write) {
				$config['read'] = ['host' => $database->read];
				$config['write'] = ['host' => $database->write];
			} else {
				$config['host'] = self::$defaultValues['host'];
			}
		} else {
			$config['host'] = $database->host;
		}

		return $config;
	}

	private static function bindEvents() {
		\models\AutoInvest::observe(new \observes\AutoInvestObserve());
	}
}