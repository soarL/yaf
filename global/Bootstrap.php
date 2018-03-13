<?php

/**
 * 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */

use Yaf\Bootstrap_Abstract;
use Yaf\Application;
use Yaf\Registry;
use Yaf\Dispatcher;
use Yaf\Session;
use Yaf\Loader;
use Yaf\Route\Rewrite;
use Yaf\Route\Regex;
use factories\SessionFactory;
use factories\DatabaseFactory;
use tools\Config;
use tools\Siteinfo;
use tools\Rewriter;
use models\User;

class Bootstrap extends Bootstrap_Abstract {
    private $config;

    public function _initConfig() {
        header("Content-type: text/html; charset=utf-8");
        $this->config = Application::app()->getConfig();
        Registry::set('config', $this->config);

        define('APP_ENV', $this->config->application->env);
        if(APP_ENV!='product') {
            ini_set("display_errors", "On");
        }

        $session = SessionFactory::create();
        Registry::set('session', $session);

        Dispatcher::getInstance()->autoRender(FALSE);

        $webDomain = $this->config->website->domain;
        foreach ($this->config->website as $siteName => $siteUrl) {
            $realSiteUrl = str_replace('{domain}', $webDomain, $siteUrl);
            defined('WEB_'.strtoupper($siteName))?true:define('WEB_'.strtoupper($siteName), $realSiteUrl);
        }
    }

    public function _initSite(Dispatcher $dispatcher) {
        $method = strtolower($dispatcher->getRequest()->getMethod());
        $mode = 'normal';
        if($method=='cli') {
            $mode = 'console';
        }
        $siteinfo = new Siteinfo($mode);
        Registry::set('siteinfo', $siteinfo);
        
        if($mode=='normal') {
            $domain = '.'.$this->getUrlDomain($siteinfo['host']);
            if($this->config->website->domain!==$domain) {
                header("HTTP/1.1 301 Moved Permanently");
                header("Location: ".WEB_MAIN);
                exit(0);
            }

            if($siteinfo['baseUrl'] == WEB_MAIN) {
                $dispatcher->setDefaultModule("Index")->setDefaultController("Index")->setDefaultAction("index");
            } else if($siteinfo['baseUrl'] == WEB_USER) {
                $dispatcher->setDefaultModule("Index")->setDefaultController("Account")->setDefaultAction("index");
            } else {
                $dispatcher->setDefaultModule("Index")->setDefaultController("Index")->setDefaultAction("index");
            }
        }

        DatabaseFactory::create();
        if($mode=='normal') {
            User::loginByCookie(); 
        }

        $router = $dispatcher->getRouter();
        Rewriter::app($router);
    }

    public function _initLoader(Dispatcher $dispatcher) {
        $loader = Loader::getInstance();
        $loader->registerLocalNamespace(array('assets'));
    }

    /**
     * 调用插件,在开始路由器调用
     * @param Yaf_Dispatcher $dispatcher
     */
    public function _initPlugin(Dispatcher $dispatcher) {
        $plugins = Config::app('other', 'plugins');
        foreach ($plugins as $pluginName => $pluginStatus) {
            if($pluginStatus==1) {
                $pluginName = $pluginName.'Plugin';
                $plugin = new $pluginName();
                $dispatcher->registerPlugin($plugin);
            }
        }
    }

    private function getUrlDomain($url) {
        $domain = '';
        $domainPostfixs = ['com', 'net', 'org', 'gov', 'edu', 'com.cn', 'cn'];
        $urlArray = explode(".", $url);
        $arrayNum = count($urlArray) - 1;
        if ($urlArray[$arrayNum] == 'cn') {
            if (in_array($urlArray[$arrayNum - 1], $domainPostfixs)) {
                $domain = $urlArray[$arrayNum - 2] . "." . $urlArray[$arrayNum - 1] . "." . $urlArray[$arrayNum];
            } else {
                $domain = $urlArray[$arrayNum - 1] . "." . $urlArray[$arrayNum];
            }
        } else {
            $domain = $urlArray[$arrayNum - 1] . "." . $urlArray[$arrayNum];
        }
        return $domain;
    }
}
