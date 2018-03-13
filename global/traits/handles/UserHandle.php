<?php
namespace traits\handles;

use Yaf\Registry;

/**
 * UserHandle
 * 用户处理-控制器方法分离
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
trait UserHandle {
	public function getUser() {
		$user = Registry::get('user');
        return $user;
    }
}