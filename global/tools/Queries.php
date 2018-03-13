<?php
/**
 * Queries
 * 查询参数处理
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
namespace tools;

class Queries {
    private $_paramsBefore = [];
    private $_params = [];
    private $_defaults = [];
    private $_request;

    function __construct($request, $defaults=[]) {
        $this->_request = $request;
        $this->_paramsBefore = $request->getQuery();
        $this->_params = $request->getQuery();
        $this->_defaults = $defaults;
        foreach ($defaults as $key => $value) {
            if(!isset($params[$key])) {
                $this->_params[$key] = $value;
            }
        }
    }

    public function all($encode=true, $isBefore=true) {
        $params = $this->_params;
        if($isBefore) {
            $params = $this->_paramsBefore;
        }
        if($encode) {
            foreach ($params as $key => $value) {
                if(is_array($value)) {
                    $params[$key] = $value;
                } else {
                    $params[$key] = htmlspecialchars($value);
                }
            }
        }
        return $params;
    }

    public function get($param, $default=null, $encode=true) {
        $value = isset($this->_params[$param])?$this->_params[$param]:$default;
        if($encode) {
            if(!is_array($value)) {
                $value = htmlspecialchars($value);
            }
        }
        return $value;
    }

    public function set($param, $value) {
        $this->_params[$param] = $value;
        return $this;
    }

    public function defaults($defaults=array()) {
        $this->_defaults = $defaults;
        foreach ($defaults as $key => $value) {
            if(!isset($this->_params[$key])) {
                $this->_params[$key] = $value;
            }
        }
        return $this;
    }

    public function getUrl($otherParams=array()) {
        $url = $this->_request->getRequestUri();
        foreach ($otherParams as $key => $value) {
            $this->_paramsBefore[$key] = $value;
        }
        $paramsStr = '';
        foreach ($this->_paramsBefore as $key => $value) {
            $paramsStr .= $key . '=' . $value . '&';
        }
        if($paramsStr!='') {
            $url .= '?' . trim($paramsStr, '&');
        }
        return $url;
    }

    public function sort($column, $reset=array()) {
        $type = '';
        $url = '';
        $params = ['sortBy'=>$column];
        if($this->get('sortType')=='desc' && $this->get('sortBy')==$column) {
            $type = '-desc';
            $params['sortType'] = 'asc';
        } else if($this->get('sortType')=='asc' && $this->get('sortBy')==$column) {
            $type = '-asc';
            $params['sortType'] = 'desc';
        } else {
            $type = '';
            $params['sortType'] = 'desc';
        }
        $url = $this->getUrl(array_merge($params, $reset));
        echo ' <a href="'.$url.'"><i class="fa fa-sort'.$type.'"></i></a>';
    }

    public function __get($name) {
        if(isset($this->_params[$name])) {
            if(is_array($this->_params[$name])) {
                return $this->_params[$name];
            } else {
                return htmlspecialchars($this->_params[$name]);
            }
        } else {
            return null;
        }
    }

    public function __set($name, $value) {
        $this->_params[$name] = $value;
    }
}