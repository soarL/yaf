<?php
namespace tools;

use tools\Redis;

/**
 * Counter
 * 计数器-依赖redis
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Counter {

    /**
     * 所有类型
     * @var array
     */
    public static $types = [
        'f', // 永久
        's', // 秒
        'd', // 天
        'n', // 数字循环
    ];

    /**
     * 计数器最后使用的时间戳，通常与计数器的值配合生成唯一的序列号[流水号]
     */
    private static $time;

    /**
     * 获取下个值
     * @param  string  $name  类型
     * @param  string  $type  计数器类型
     * @return integer        计数值
     */
    public static function next($name, $type='f') {
        $key = self::getKey($name, $type);
        $value = Redis::incr($key);

        // 天计数器设置生存时间
        if($value==1) {
            if($type=='s') {
                Redis::expire($key, 10);
            } else if($type=='d') {
                Redis::expireAt($key, strtotime(date('Y-m-d 23:59:59'))+10);
            }
        }
        if($type=='n' && $value==999999) {
            Redis::delete($key);
        }

        return $value;
    }

    /**
     * 删除某个计数器
     * @param  string  $name  类型
     * @param  string  $type  计数器类型
     * @return integer        0-删除失败 1-删除成功
     */
    public static function del($name, $type='f') {
        $key = self::getKey($name, $type);
        return Redis::del($key);
    }

    /**
     * 获取计数器的KEY
     * @param  string  $name  类型
     * @param  string  $type  计数器类型
     * @return string         计数值的KEY
     */
    public static function getKey($name, $type='f') {
        self::$time = time();
        if($type=='s') {
            return Redis::getKey('counter', ['time'=>self::$time, 'name'=>$name]);
        } else if($type=='d') {
            return Redis::getKey('counter', ['time'=>date('Ymd', self::$time), 'name'=>$name]);
        } else if($type=='n') {
            return Redis::getKey('counter', ['time'=>'number', 'name'=>$name]);
        }
        return '';
    }

    /**
     * 获取计数器最后使用的时间戳
     * @return string
     */
    public static function getTime() {
        return self::$time;
    }

    public static function getOrderID($name='common') {
        $seq = self::next($name, 'n');
        $seq = str_repeat('0', 6-strlen($seq)).$seq;
        $time = self::getTime();
        $snDate = date('Ymd', $time);
        $snTime = date('His', $time);
        return $snDate.$snTime.$seq;
    }

    public static function getBatchNo() {
        $bnq = Counter::next('batch', 'd');
        $bnq = str_repeat('0', 6-strlen($bnq)).$bnq;
        $time = Counter::getTime();
        return date('Ymd', $time) . $bnq;
    }
}
