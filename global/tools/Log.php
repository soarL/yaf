<?php
namespace tools;

/**
 * Log
 * 工具类，日志、
 * 
 * @author elf <271105196@qq.com>
 * @version 1.0
 */
class Log {
	
    const BASE_PATH = '../../log';

    static function writeFileLog($fFileName, $fContent, $fTag = 'a') {
        $fFileName = self::BASE_PATH . '/' . $fFileName . '_log' . date("Ymd") . '.txt';
        ignore_user_abort(TRUE);
        if (!file_exists($fFileName)) {
            $fp = fopen($fFileName, 'w');
        } else {
            $fp = fopen($fFileName, 'a');
        }
        if (flock($fp, LOCK_EX)) {
            fwrite($fp, date("Y-m-d H:i:s") . "=>" . $fContent . "\n");
            flock($fp, LOCK_UN);
        }
        fclose($fp);
        ignore_user_abort(FALSE);
        return true;
    }

}