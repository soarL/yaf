<?php
use Yaf\Plugin_Abstract;
use traits\Access;

/**
 * AccessPlugin
 * 用于用户权限检查的插件
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class AccessPlugin extends Plugin_Abstract {
    use Access;

    const RETURN_URL = '__returnUrl__';
}
