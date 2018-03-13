<?php
/**
 * SessionFactory
 * Session工厂类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
namespace factories;

use base\Factory;
use Yaf\Session;
use Yaf\Registry;
use tools\Config;
use tools\Redis;

class SessionFactory implements Factory {
	const INI_NAME = 'app';
	const INI_NODE = 'session';

	public static function create($params=[]) {
		if(Registry::has('session')) {
	        return Registry::get('session');
        } else {
        	$config = Config::get(self::INI_NAME, self::INI_NODE);
        	if($config) {
		        foreach ($config as $key => $value) {
		        	ini_set('session.' . $key, $value);
		        }
	        }

	        //单点登录
	        $session = Session::getInstance();
	        $userID = $session->get('userID');
	        if($userID) {
	        	$ssid = Redis::get('ssid:'.$userID);
	        	if($ssid == session_id()){
	        		return $session;
	        	}else{
	        		$session->del('user');
	        		$session->del('userID');
	        		return $session;
	        	}
            }
	        return Session::getInstance();
        }
	}

}