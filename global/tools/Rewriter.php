<?php
/**
 * Rewrite
 * 工具类，重写辅助类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
namespace tools;

use Yaf\Route\Rewrite;
use Yaf\Route\Regex;

class Rewriter {

    public static function app($router) {
        $routes = Config::arr('rewrite', Config::APP);
        if($rewriteRoutes=_isset($routes, 'rewrite')) {
            foreach ($rewriteRoutes as $name => $route) {
                $rewrite = self::getRewrite($route);
                $router->addRoute($name, $rewrite);
            }
        }
        if($regexRoutes=_isset($routes, 'regex')) {
            foreach ($regexRoutes as $name => $route) {
                $regex = self::getRegex($route);
                $router->addRoute($name, $regex);
            }
        }
    }

    public static function getRegex($route) {
        $routeArray = explode('/', $route['route']);
        $config = [];
        $config[] = $route['rule'];
        if(count($routeArray)==2) {
            $config[] = ['controller' => $routeArray[0],'action' => $routeArray[1]];
        } else if(count($routeArray)==3) {
            $config[] = ['module' => $routeArray[0] , 'controller' => $routeArray[1],'action' => $routeArray[2]];
        }
        if(isset($route['params'])) {
            $newParams = [];
            foreach ($route['params'] as $key => $param) {
                $newParams[$key+1] = $param;
            }
            $config[] = $newParams;
        }
        return new Regex($config[0], $config[1], $config[2]);
    }

    public static function getRewrite($route) {
        $routeArray = explode('/', $route['route']);
        $config = [];
        $config[] = $route['rule'];
        if(count($routeArray)==2) {
            $config[] = ['controller' => $routeArray[0],'action' => $routeArray[1]];
        } else if(count($routeArray)==3) {
            $config[] = ['module' => $routeArray[0] , 'controller' => $routeArray[1],'action' => $routeArray[2]];
        }
        return new Rewrite($config[0], $config[1]);
    }
}