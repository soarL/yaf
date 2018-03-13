<?php
use Yaf\Plugin_Abstract;
use traits\Authorize;

/**
 * AuthorizePlugin
 * 用于用户授权访问的插件（可用ActionControl替代）
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class AuthorizePlugin extends Plugin_Abstract {
    use Authorize;
}