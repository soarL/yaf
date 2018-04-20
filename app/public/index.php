<?php
define("APP_PATH", dirname(dirname(__FILE__)));
$app = new Yaf\Application(APP_PATH . "/../conf/app.ini"); 
include '../../global/libs/helpers.php';
try {
	$app->bootstrap()->run();
} catch (Yaf\Exception $e) {
	print_r($e->getMessage());
}
