<?php
namespace tools;
class ThirdToken {
	public static $thirdAuth = [
		'tianyan' => ['username'=>'tianyan', 'password'=>'xwsd_tianyan', 'key'=>'xiaoweishidai_tianyan_token_key'],
		'zhijia' => ['username'=>'zhijia', 'password'=>'xwsd_zhijia', 'key'=>'xiaoweishidai_zhijia_token_key'],
		'jialu' => ['username'=>'jialu', 'password'=>'xwsd_jialu', 'key'=>'xiaoweishidai_jialu_token_key'],
		'dailuopan' => ['username'=>'dailuopan', 'password'=>'xwsd_dailuopan', 'key'=>'xiaoweishidai_dailuopan_token_key'],
		'duozhuan' => ['username'=>'duozhuan', 'password'=>'xwsd_duozhuan', 'key'=>'xiaoweishidai_duozhuan_token_key'],
	];

	public static function checkThirdToken($third, $token) {
		if(!isset(self::$thirdAuth[$third])) {
    		return false;
    	}
    	$key = self::$thirdAuth[$third]['key'];
    	$username = self::$thirdAuth[$third]['username'];
    	$password = self::$thirdAuth[$third]['password'];

    	$tokenLocal = substr(md5($username.$password.$key), 8, 16);
		if($token!=$tokenLocal) {
			return false;
		} else {
			return true;
		}
    }

    public static function getThirdToken($third, $username, $password) {
    	if(!isset(self::$thirdAuth[$third])) {
    		return false;
    	}
		$key = self::$thirdAuth[$third]['key'];
		$usernameLocal = self::$thirdAuth[$third]['username'];
		$passwordLocal = self::$thirdAuth[$third]['password'];
		if($username==$usernameLocal&&$password==$passwordLocal) {
			return substr(md5($username.$password.$key), 8, 16);
		} else {
			return false;
		}
    }
}