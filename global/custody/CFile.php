<?php
namespace custody;

/**
 * CFile
 * 存管文件生成、解析
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class CFile {
    public static function parseFile($filePath, $items) {
        $file = fopen($filePath, 'r');
        $rows = [];
        while(!feof($file)) {
            $row = [];
            $content = fgets($file);
            foreach ($items as $key => $val) {
                $res = self::popStr($content, $val);
                $row[$key] = $res[0];
                $content = $res[1];
            }
            $rows[] = $row;
        }
        fclose($file);
        return $rows;
    }
    
    public static function popStr($str, $length) {
        $sub = substr($str, 0, $length);
        $sub = iconv('gbk', 'utf-8', trim($sub));
        $less = substr($str, $length);
        return [$sub, $less];
    }

    public static function appendStr($str, $length, $type='right', $s=' ') {
        $newStr = iconv('utf-8', 'gbk', $str);
        $len = strlen($newStr);
        $repeat = str_repeat($s, $length-$len);
        if($type=='left') {
            return $repeat . $newStr;
        }
        return $newStr . $repeat;
    }

    public static function dnum($num, $f=2) {
        $result = intval($num * pow(10, $f));
        if($result==0) {
            return '0' . str_repeat('0', $f);
        } else {
            return $result;
        }
    }
}