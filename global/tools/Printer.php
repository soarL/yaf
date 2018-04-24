<?php
/**
 * Printer
 * 工具类，打印类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
namespace tools;

use Yaf\Registry;

class Printer {

	const CONSOLE_NONE_SUF = 0;
    const CONSOLE_FULL_SUF = 1;
    const CONSOLE_LINE_SUF = 2;

    public static function pretty($content, $exit=false) {
        echo '<pre>';
        print_r($content);
        echo '</pre>';
        $exit && exit(0);
    }

    public static function json($data, $exit=true) {
    	echo json_encode($data);
    	$exit && exit(0);
    }

    public static function jsonp($data, $callback, $exit=true) {
        echo $callback.'('.json_encode($data).')';
        $exit && exit(0);
    }

    public static function export($msg, $exit=false) {
        $siteinfo = Registry::get($siteinfo);
        if($siteinfo['console.charset']!='utf8') {
        	$msg = iconv('UTF-8',$config->console->charset.'//IGNORE', $msg);
        }
        echo $msg;
        $exit && exit(0);
    }
}