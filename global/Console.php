<?php
/**
 * Console
 * 控制台类，解析命令行参数，获取路由
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Console {
    /* 参数名前缀，用于识别 */
    const ARGPRE = '--';

    /* 路由参数名 */
    const ARG_URL = 'url';

    private $config;
    private $arguments;
    private $bootstrapFile;
    private $url;

    private $module = 'Console';
    private $controller = 'Index';
    private $action = 'Index';

    function __construct($config = []) {
        $this->config = $config;
        $this->initArguments();
        $this->init();
    }

    public function init() {

    }

    public function export($info) {
        echo $info;exit(0);
    }

    public function getUrl() {
        return $this->url;
    }

    public function getArguments() {
        return $this->arguments;
    }

    public function getArgument($arg, $default=null) {
        if(isset($this->arguments[$arg])) {
            return $this->arguments[$arg];
        } else {
            return $default;
        }
    }

    public function getModule() {
        return $this->module;
    }

    public function getController() {
        return $this->controller;
    }

    public function getAction() {
        return $this->action;
    }

    /**
     * 初始化参数
     * @return void
     */
    private function initArguments() {
        $arguments = $this->config['argv'];
        $argumentCount = $this->config['argc'];
        if($argumentCount==1) {
            $this->export('Argument \'' . self::ARGPRE . self::ARG_URL . '\' is required!');
        }
        $this->bootstrapFile = $arguments[0];

        foreach ($arguments as $key => $argument) {
            if($key>=1) {
                $this->explodeArgument($argument, $key);
            }
        }

        if(isset($this->arguments[self::ARG_URL])) {
            $this->url = $this->arguments[self::ARG_URL];
            unset($this->arguments[self::ARG_URL]);
        } else {
            if(isset($this->arguments[1])) {
                $this->url = $this->arguments[1];
                unset($this->arguments[1]);
            } else {
                $this->export('Argument \'' . self::ARGPRE . self::ARG_URL . '\' is required!');
            }
        }

        $explodeUrl = explode('/', $this->url);
        $urlCount = count($explodeUrl);
        if($urlCount>=3) {
            $this->module = $explodeUrl[0];
            $this->controller = $explodeUrl[1];
            $this->action = $explodeUrl[2];
        } else if($urlCount==2) {
            $this->controller = $explodeUrl[0];
            $this->action = $explodeUrl[1];
        } else if($urlCount==1) {
            $this->controller = $explodeUrl[0];
        }
    }

    /**
     * 解析命令参数
     * @param  string $argString 参数字符串
     * @param  string $key       参数字符串的key
     * @return void
     */
    private function explodeArgument($argString, $key) {
        if(substr($argString, 0, 2)==self::ARGPRE) {
            $explodeArg = explode('=', ltrim($argString, self::ARGPRE));
            $this->arguments[$explodeArg[0]] = isset($explodeArg[1])?$explodeArg[1]:'';
        } else {
            $this->arguments[$key] = $argString;
        }
    }

}
