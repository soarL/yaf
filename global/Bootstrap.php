<?php
use Yaf\Loader;
use Yaf\Application;
use Yaf\Registry;
use Yaf\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use tools\Log;
/**
 * 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends Yaf\Bootstrap_Abstract{
	
	public function _initWeixin(){
        Log::writeFileLog('weixin',"asdasd");
        if(isset($_GET['echostr'])){
            $echoStr = $_GET["echostr"];
            if($this->checkSignature()){
                echo $echoStr;
                exit;
            }
        }
	}

	public function checkSignature(){
		$signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = _wxini()->token;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
	}

	public function _initLoader() {
        $data = Loader::import(APP_PATH . "/../vendor/autoload.php");
    }
	
	public function _initConfig() {
		header("Content-type: text/html; charset=utf-8");
		$config = Application::app()->getConfig();
		Registry::set("config", $config);
		Dispatcher::getInstance()->autoRender(FALSE);
	}

	public function _initDefine(){
		define('WEB_MAIN',$_SERVER['SERVER_NAME']);
	}
	
	public function _initDefaultName(Dispatcher $dispatcher) {
		$dispatcher->setDefaultModule("Index")->setDefaultController("Index")->setDefaultAction("index");
	}
	
	//数据库初始化操作
	public function _initDatabaseEloquent() {
        $config = Application::app()->getConfig()->database->toArray();
        $capsule = new Capsule();

        // 创建链接
        $capsule->addConnection($config);

        // 设置全局静态可访问
        $capsule->setAsGlobal();

        // 启动Eloquent
        $capsule->bootEloquent();
    }
}