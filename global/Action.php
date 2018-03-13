<?php
use Yaf\Action_Abstract;
use traits\handles\RequestHandle;
use traits\handles\UserHandle;
use traits\handles\ExportHandle;

/**
 * Action
 * 分离action基类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
abstract class Action extends Action_Abstract {
	use RequestHandle, UserHandle, ExportHandle;
}