<?php
namespace task\handlers;

use task\Handler;
use tools\Log;

/**
 * TestHandler
 * 测试的Handler
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class TestHandler extends Handler {

    public function handle() {
        Log::write('测试：'.$this->params['val']);
        return ['status'=>1, 'msg'=>'success'];
    }

}