<?php
use Yaf\Controller_Abstract;

/**
 * Controller
 * 控制器基类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
abstract class Controller extends Controller_Abstract {

    /**
     * 创建控制器后执行
     */
    public function init() {
        
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

    public function backJson($array) {
        if(empty($array['data']['version'])){
            $array['data']['version'] = '0.1.0';
            $array['data']['time'] = time();
        }
        echo json_encode($array);exit(0);
    }
}
