<?php
use Yaf\Registry;
use tools\Queries;
use traits\handles\RequestHandle;
use traits\handles\UserHandle;
use traits\handles\ExportHandle;
use traits\handles\DisplayHandle;

/**
 * Controller
 * 控制器基类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Controller extends \Yaf\Controller_Abstract {
    use RequestHandle, ExportHandle, UserHandle, DisplayHandle;

    const CONSOLE_NONE_SUF = 0;
    const CONSOLE_FULL_SUF = 1;
    const CONSOLE_LINE_SUF = 2;

    protected $behaviors = [];

    /**
     * 创建控制器后执行
     */
    public function init() {
        $this->queries = new Queries($this->getRequest());
        $this->auto();
    }

    /**
     * 在init后执行
     */
    public function auto() {
        
    }

    /**
     * 页面跳转
     * @param  string $url 跳转地址
     */
    public function redirect($url) {
        parent::redirect($url);
        exit();
    }
}
