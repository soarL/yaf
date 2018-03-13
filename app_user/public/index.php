<?php
error_reporting(E_ALL | E_STRICT);

define("APP_PATH",  realpath(dirname(dirname(__FILE__))));

include '../../bootstrap/autoload.php';

$app = new Yaf\Application(APP_PATH . "/../conf/app.ini");
try {
    $app->bootstrap()->run();
} catch (Yaf\Exception $e) {
    print_r($e->getMessage());
}