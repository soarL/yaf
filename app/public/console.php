<?php
if(strtoupper(php_sapi_name())!=='CLI') {
	header("Content-type: text/html; charset=utf-8");
	echo '访问错误！';exit(0);
}
error_reporting(E_ALL | E_STRICT);
define("APP_PATH",  realpath(dirname(dirname(__FILE__))));

include APP_PATH . '../../bootstrap/autoload.php';
require_once APP_PATH . '/../global/Console.php';

$console = new Console($_SERVER);
$module = $console->getModule();
$controller = $console->getController();
$action = $console->getAction();
$arguments = $console->getArguments();
$request = new Yaf\Request\Simple('CLI', $module, $controller, $action, $arguments);

$app = new Yaf\Application(APP_PATH . "/../conf/app.ini");
$app->bootstrap()->getDispatcher()->dispatch($request);