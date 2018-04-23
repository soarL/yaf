<?php
namespace base;

class Exception extends \Exception {
    /**
     * 获取错误名称
     * @return string 一个用户友好的错误名称
     */
    public function getName() {
        return 'Exception';
    }
}
