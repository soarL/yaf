<?php
namespace helpers;

/**
 * NumberHelper
 * 数字、格式化帮助类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class NumberHelper {
	public static function format($number, $type='normal') {
        $result = $number;
        switch ($type) {
            case 'normal':
                $result = self::formatNormal($number);
                break;
            case 'special':
                $result = self::formatSpecial($number);
                break;
            default:
                $result = self::formatNormal($number);
                break;
        }
        return $result;
    }

    public static function formatNormal($number) {
        return number_format($number, 2);
    }

    public static function formatSpecial($number) {
        if($number<10000) {
            return $number;
        }
        if($number>=10000&&$number<100000000) {
            $preNumber = intval($number/10000);
            $sufNumber = $number - $preNumber*10000;
            return $preNumber.'万'.$sufNumber;
        }
        if($number>=100000000) {
            $preNumber = intval($number/100000000);
            $sufNumber1 = $number - $preNumber*100000000;
            $preNumber1 = intval($sufNumber1/10000);
            $sufNumber2 = $sufNumber1 - $preNumber1*10000;
            return $preNumber.'亿'.$preNumber1.'万'.$sufNumber2;
        }
    }

    public static function zeroPrefix($number, $length) {
        $numLength = strlen($number);
        if($numLength>=$length) {
            return $number;
        }
        return str_repeat('0', $length-$numLength).$number;
    }
}