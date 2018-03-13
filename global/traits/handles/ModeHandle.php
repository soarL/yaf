<?php
namespace traits\handles;

use Yaf\Registry;
use exceptions\HttpException;

/**
 * ModeHandle
 * 实现action的再分离
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
trait ModeHandle {

    public function execute() {
        $m = $this->getQuery('m', 'index');
        $m .= 'Mode';
        if(method_exists($this, $m)) {
            $this->$m();
        } else {
            throw new HttpException(404);
        }
    }
}