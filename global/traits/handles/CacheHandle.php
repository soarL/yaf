<?php
namespace traits\handles;

use Yaf\Registry;

/**
 * CacheHandle
 * 缓存处理-控制器方法分离
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
trait CacheHandle {
	public function isCache() {
        $status = Registry::get('config')->get('cache')->get('isOpen');
        if($status==0) {
            return false;
        } else {
            return true;
        }
    }

    public function cache($name, $value) {
        if($this->isCache()) {
            $this->cache[$name] = $value;
        }
    }

    public function getCache($name) {
        if(!$this->isCache()) {
            return false;
        }
        $request = $this->getRequest();
        $route = '/' . strtolower($request->controller) . '/' . strtolower($request->action);
        $data = Cache::get($name, $route);
        return $data;
    }
}