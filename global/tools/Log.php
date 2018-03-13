<?php
namespace tools;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\RedisHandler;
use factories\RedisFactory;
use Yaf\Registry;

/**
 * Log
 * 工具类，日志、需要配合Monolog
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Log {
	
    const BASE_PATH = '../../log';

    private static $loggers = [];

    public static $levels = [
        'DEBUG' => Logger::DEBUG,
        'INFO' => Logger::INFO,
        'NOTICE' => Logger::NOTICE,
        'WARNING' => Logger::WARNING,
        'ERROR' => Logger::ERROR,
        'CRITICAL' => Logger::CRITICAL,
        'ALERT' => Logger::ALERT,
        'EMERGENCY' => Logger::EMERGENCY,
    ];

    public static function getFileLogger($name, $level) {
        if(!isset(self::$loggers[$name])) {
            $fullPath = self::BASE_PATH.'/'.$name.'/';
            if(!is_dir($fullPath)) {
                mkdir($fullPath);
            }
            $fullPath = $fullPath . date('Ym') . '/';
            if(!is_dir($fullPath)) {
                mkdir($fullPath);
            }

            $file = $fullPath.date('d').'.log';

            $logger = new Logger($name);
            $logger->pushHandler(new StreamHandler($file, $level));
            $logger->pushHandler(new FirePHPHandler());
            self::$loggers[$name] = $logger;
        }
        return self::$loggers[$name];
    }

    public static function getRedisLogger($name, $level) {
        $redis = RedisFactory::create();
        if(!isset(self::$loggers[$name])) {
            $logger = new Logger($name);
            $key = $name . '_logs_' . date('Ymd');
            $logger->pushHandler(new RedisHandler($redis, $key, $level));
            $logger->pushHandler(new FirePHPHandler());
            self::$loggers[$name] = $logger;
        }
        return self::$loggers[$name];
    }

	public static function write($message, array $context=[], $name='common', $level='INFO') {
        $handler = Registry::get('config')->get('log.handler');
        if(isset(self::$levels[$level])) {
            $levelNum = self::$levels[$level];
            $logger = null;
            if($handler=='redis') {
                $logger = self::getRedisLogger($name, $levelNum);
            } else {
                $logger = self::getFileLogger($name, $levelNum);
            }
            $logger->log($levelNum, $message, $context);
        }
	}

}