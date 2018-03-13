<?php
namespace plugins\bbs;

class BBSUnion {

	public static function synlogin($uid,$name,$pwd){
		$time = time();
		$str = "action=synlogin&username={$name}&uid={$uid}&password={$pwd}&time={$time}";
		$code = urlencode(uc_authcode($str,'ENCODE',UC_BBSKEY));
		return UC_BBSAPI."?code={$code}&time={$time}";
	}

	public static function synlogout(){
		$time = time();
		$str = "action=synlogout&time={$time}";
		$code = urlencode(uc_authcode($str,'ENCODE',UC_BBSKEY));
		return UC_BBSAPI."?code={$code}&time={$time}";
	}

	

	public static function login($name,$pwd,$email) {
		include_once __DIR__.'/config.inc.php';
		include_once __DIR__.'/uc_client/client.php';
		$uData = uc_get_user($name);
		if($uData == 0){
			// 如果用户名不存在，向UC创建用户
			$sta = uc_user_register($name,$pwd,$email);
			switch($sta){
				case -1:
					$result = array(
						'status' => $sta,
						'desc' => '用户名无效'
					);
					return $result;

				case -2:
					$result = array(
						'status' => $sta,
						'desc' => '用户名包含敏感词'
					);
					return $result;
				case -3:
					$result = array(
						'status' => $sta,
						'desc' => '用户名已存在'
					);
					return $result;
				case -4:
					$result = array(
						'status' => $sta,
						'desc' => 'email格式无效'
					);
					return $result;
				case -5:
					$result = array(
						'status' => $sta,
						'desc' => 'email不允许注册'
					);
					return $result;
				case -6:
					$result = array(
						'status' => $sta,
						'desc' => 'email已被注册'
					);
					return $result;
				default:
					if($sta <= 0){
						$result = array(
							'status' => $sta,
							'desc' => '其他错误'
						);
						return $result;
					}
					// 输出同步登录脚本
					$script = self::synlogin($sta,$name,$pwd);
					$result = array(
						'status' => 200,
						'desc' => '登录成功',
						'script' => $script
					);
					return $result;
			}
		}else{
			// 如果用户名存在，进行登录操作
			list($sta,$name,$pwd,$email) = uc_user_login($name,$pwd);
			switch($sta){
				case -1:
					$result = array(
						'status' => $sta,
						'desc' => '用户不存在'
					);
					return $result;

				case -2:
					$result = array(
						'status' => $sta,
						'desc' => '密码错误'
					);
					return $result;
				case -3:
					$result = array(
						'status' => $sta,
						'desc' => '安全提问错误'
					);
					return $result;
				default:
					if($sta <= 0){
						$result = array(
							'status' => $sta,
							'desc' => '其他错误'
						);
						return $result;
					}
					// 输出同步登录脚本
					$script = self::synlogin($sta,$name,$pwd);
					$result = array(
						'status' => 200,
						'desc' => '登录成功',
						'script' => $script
					);
					return $result;
			}
		}
	}

	public static function logout() {
		include_once __DIR__.'/config.inc.php';
		include_once __DIR__.'/uc_client/client.php';
		$script = self::synlogout();
		return $script;
	}

	public static function resetPassword($name , $oldpwd , $newpwd , $email , $ignoreoldpwd=0) {
		include_once __DIR__.'/config.inc.php';
		include_once __DIR__.'/uc_client/client.php';
		$result = uc_user_edit($name , $oldpwd , $newpwd , $email , $ignoreoldpwd);
		return $result;
	}
}