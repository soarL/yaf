<?php
namespace traits\handles;

/**
 * RequestHandle
 * 请求（参数）处理-控制器方法分离
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
trait RequestHandle {

    public function getQuery($param, $default=null, $isFilter=true) {
        $request = $this->getRequest();
        $value = $request->getQuery($param, $default);
        if($value==null) {
            return $value;
        }
        if($isFilter&&!is_array($value)) {
            $value = htmlspecialchars($value);
        }
        return $value;
    }

    public function getAllQuery($isFilter=true) {
        $params = $this->getRequest()->getQuery();
        if(!$isFilter) {
            return $params;
        }
        $newParams = [];
        foreach ($params as $key => $value) {
            if(is_array($value)) {
                $newParams[$key] = $value;
            } else {
                $newParams[$key] = htmlspecialchars($value);
            }
        }
        return $newParams;
    }

    public function getPost($param, $default=null, $isFilter=true) {
        $request = $this->getRequest();
        $value = $request->getPost($param, $default);
        if($value==null) {
            return $value;
        }
        if($isFilter&&!is_array($value)) {
            $value = htmlspecialchars($value);
        }
        return $value;
    }

    public function getAllPost($isFilter=true) {
        $params = $this->getRequest()->getPost();
        if(!$isFilter) {
            return $params;
        }
        $newParams = [];
        foreach ($params as $key => $value) {
            if(is_array($value)) {
                $newParams[$key] = $value;
            } else {
                $newParams[$key] = htmlspecialchars($value);
            }
        }
        return $newParams;
    }

    public function getParam($param, $default=null, $isFilter=true) {
        $request = $this->getRequest();
        $value = $request->getParam($param, $default);
        if($value==null) {
            return $value;
        }
        if($isFilter&&!is_array($value)) {
            $value = htmlspecialchars($value);
        }
        return $value;
    }

    public function getParams($isFilter=true) {
        $params = $this->getRequest()->getParams();
        if(!$isFilter) {
            return $params;
        }
        $newParams = [];
        foreach ($params as $key => $value) {
            if(is_array($value)) {
                $newParams[$key] = $value;
            } else {
                $newParams[$key] = htmlspecialchars($value);
            }
        }
        return $newParams;
    }

    public function isPost() {
        return $this->getRequest()->isPost();
    }

    public function isGet() {
        return $this->getRequest()->isGet();
    }
}
