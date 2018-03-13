<?php
namespace plugins\bbsunion;

include './config.inc.php';
include './uc_client/client.php';

// 修改密码
/*
uc_user_edit($name , $oldpwd , $newpwd , $email , $ignoreoldpwd)
$name 用户名 $oldpwd 原密码 $newpwd 新密码 $email 新邮箱 $ignoreoldpwd 修改时直接忽略旧密码判定（1）
返回值：
 1:更新成功
 0:没有做任何修改
-1:旧密码不正确
-4:Email 格式有误
-5:Email 不允许注册
-6:该 Email 已经被注册
-7:没有做任何修改
-8:该用户受保护无权限更改
*/
var_dump('sss');
function unionTest() {
	var_dump('sss');
}

// $name 用户账号 $pwd 密码 $email 用户邮箱地址

function checkUserInfo($name,$pwd,$email) {
	$uData = uc_get_user($name);
	//var_dump($uRet);
	if($uData == 0){
		// 如果用户名不存在，向UC创建用户
		$sta = uc_user_register($name,$pwd,$email);
		switch($sta){
			case -1:
				$result = array(
					'status' => $sta,
					'desc' => '用户名无效'
				);
				return json_encode($result);

			case -2:
				$result = array(
					'status' => $sta,
					'desc' => '用户名包含敏感词'
				);
				return json_encode($result);
			case -3:
				$result = array(
					'status' => $sta,
					'desc' => '用户名已存在'
				);
				return json_encode($result);
			case -4:
				$result = array(
					'status' => $sta,
					'desc' => 'email格式无效'
				);
				return json_encode($result);
			case -5:
				$result = array(
					'status' => $sta,
					'desc' => 'email不允许注册'
				);
				return json_encode($result);
			case -6:
				$result = array(
					'status' => $sta,
					'desc' => 'email已被注册'
				);
				return json_encode($result);
			default:
				if($sta <= 0){
					$result = array(
						'status' => $sta,
						'desc' => '其他错误'
					);
					return json_encode($result);
				}
				// 输出同步登录脚本
				$script = uc_user_synlogin($sta);
				$result = array(
					'status' => 200,
					'desc' => '登录成功',
					'script' => $script
				);
				return json_encode($result);
		}
		//return "No User";
	}else{
		list($uid,$name,$email) = $uData;
		// 如果用户名存在，进行登录操作
		list($sta,$name,$pwd,$email) = uc_user_login($name,$pwd);
		switch($sta){
			case -1:
				$result = array(
					'status' => $sta,
					'desc' => '用户不存在'
				);
				return json_encode($result);

			case -2:
				$result = array(
					'status' => $sta,
					'desc' => '密码错误'
				);
				return json_encode($result);
			case -3:
				$result = array(
					'status' => $sta,
					'desc' => '安全提问错误'
				);
				return json_encode($result);
			default:
				if($sta <= 0){
					$result = array(
						'status' => $sta,
						'desc' => '其他错误'
					);
					return json_encode($result);
				}
				// 输出同步登录脚本
				$script = uc_user_synlogin($sta);
				$result = array(
					'status' => 200,
					'desc' => '登录成功',
					'script' => $script
				);
				return json_encode($result);
		}
	}
}

header('Content-Type: application/x-javascript; charset=UTF-8');

$ret = checkUserInfo($_GET["user"],"12345678",$_GET["user"]."123@58303.com");
if($_GET["callback"]){
	echo $_GET["callback"]."({$ret})";
}
else echo $ret;