<?php
/**
 * Siteinfo
 * 工具类，获取网站信息
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
namespace tools;

use \ArrayAccess;
use base\Factory;
use Yaf\Session;
use Yaf\Registry;

class Siteinfo implements ArrayAccess {
	public $base;
	public $info;
    public $mode;

	function __construct($mode='normal') {
		$this->base = $_SERVER;
        $this->mode = $mode;
        $this->initInfo();
	}

	private function initInfo() {
		$info = [];
		$config = Registry::get('config');
        $scheme = isset($this->base['REQUEST_SCHEME'])?$this->base['REQUEST_SCHEME']:'';
        $info['env'] = $config->application->env?$config->application->env:'product';
        if($config->application->console&&$config->application->console->charset) {
            $info['console.charset'] = $config->application->console->charset;
        } else {
            $info['console.charset'] = 'utf8';
        }
        if($this->mode=='normal') {
            $info['scheme'] = $scheme;
            $info['host'] = $this->base['HTTP_HOST'];
            $info['port'] = $this->base['SERVER_PORT'];
            $info['serverIp'] = $this->base['SERVER_ADDR'];
            $info['clientIp'] = $this->getClientIP();
            $info['requestTime'] = $this->base['REQUEST_TIME'];
            $info['method'] = $this->base['REQUEST_METHOD'];
            $info['webPath'] = $this->base['DOCUMENT_ROOT'];
            $info['baseUrl'] = $scheme.'://'.$this->base['HTTP_HOST'];
            $info['fullUrl'] = $info['baseUrl'].$this->base['REQUEST_URI'];
            $info['referer'] = isset($this->base['HTTP_REFERER'])?$this->base['HTTP_REFERER']:'';
            $info['userAgent'] = isset($this->base['HTTP_USER_AGENT'])?$this->base['HTTP_USER_AGENT']:'';
            $info['domain'] = str_replace($config->website->domain, '', $info['host']);
        } else if($this->mode=='console') {
            
        }
        $this->info = $info;
	}

	private function getClientIP() {
        if(!empty($this->base["HTTP_CLIENT_IP"])) {
            $ips = $this->base["HTTP_CLIENT_IP"];
        }else if(!empty($this->base["HTTP_X_FORWARDED_FOR"])){
            $array = explode(',', $this->base['HTTP_X_FORWARDED_FOR']);
            $ip = array_pop($array);
        }else if(!empty($this->base["REMOTE_ADDR"])){
            $ip = $this->base["REMOTE_ADDR"];
        }else{
            $ip = '';
        }
        return $ip;
    }

	//检查一个偏移位置是否存在
    public function offsetExists($key) {
        return isset($this->info[$key]);
    }

    //获取一个偏移位置的值
    public function offsetGet($key) {
        if(isset($this->info[$key])){
            return $this->info[$key];
        }else{
            return '';
        }
    }

    //设置一个偏移位置的值
    public function offsetSet($key, $value) {
        $this->info[$key] = $value;
    }

    //复位一个偏移位置的值
    public function offsetUnset($key) {
        unset($this->info[$key]);
    }
}