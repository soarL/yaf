<?php
use Yaf\Plugin_Abstract;
use traits\ActionControl;

/**
 * ActionControlPlugin
 * 用户行为控制插件（可替代Authorize）
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class ActionControlPlugin extends Plugin_Abstract {
    use ActionControl;
}