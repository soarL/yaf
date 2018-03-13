<?php
// 流量统计
header('Access-Control-Allow-Origin: *');
$ref = isset($_GET['referrer'])?$_GET['referrer']:'';
$uv = isset($_GET['uvKey'])?$_GET['uvKey']:'';
$config = new Yaf\Config\Ini(dirname(__FILE__).'/../../conf/redis.ini', 'default');

$redis = new Redis();
$redis->connect($config->host, $config->port, $config->timeout);
$redis->auth($config->password);
$redis->rpush('access_logs', json_encode([
    'ip'=>getClientIP(), 
    'refer'=>$ref, 
    'accessed_at'=>date('Y-m-d H:i:s'),
    'pm'=>isset($_COOKIE['pm_key'])?$_COOKIE['pm_key']:'',
    'uv'=>$uv
]));

function getClientIP() {
    if(!empty($_SERVER["HTTP_CLIENT_IP"])) {
        $ips = $_SERVER["HTTP_CLIENT_IP"];
    }else if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
        $array = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = array_pop($array);
    }else if(!empty($_SERVER["REMOTE_ADDR"])){
        $ip = $_SERVER["REMOTE_ADDR"];
    }else{
        $ip = '';
    }
    return $ip;
}