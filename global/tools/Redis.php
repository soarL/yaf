<?php
namespace tools;

use factories\RedisFactory;

/**
 * Redis
 * 工具类，Redis管理
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Redis {
    const REDIS_STRING = 1;
    const REDIS_SET = 2;
    const REDIS_LIST = 3;
    const REDIS_ZSET = 4;
    const REDIS_HASH = 5;
    const REDIS_NOT_FOUND = 0;
    const AFTER = 'after';
    const BEFORE = 'before';
    const MULTI = 1;
    const PIPELINE = 2;

    private static $redis = null;

    public static $keys = [
        'oddRemain' => 'odd_remain:{oddNumber}',        /** 标的剩余可投金额 **/
        'crtrRemain' => 'crtr_remain:{sn}',             /** 债权剩余可买金额 **/
        'counter' => 'counter:{name}_{time}',           /** 计数器数字 **/
        'trialQueue' => 'trial_queue',                  /** 标的初审列表 **/
        'trialIngQueue' => 'trial_ing_queue',           /** 正在初审中的标的 **/
        'oddAutoQueue' => 'odd_auto_queue',             /** 自动投标标的队列 **/
        'autoInvesting' => 'auto_investing',            /** 正在自动投标的标的号 **/
        'autoInvestQueue' => 'auto_invest_queue',       /** 自动投标用户队列 **/
        'rehearIngQueue' => 'rehear_ing_queue',         /** 正在复审中的标的 **/
        'repayIngQueue' => 'repay_ing_queue',           /** 正在还款中的还款记录 **/
        'userMaxNum' => 'user_max_num',                 /** 用户ID最高数 **/

        'protocolQueue' => 'protocol_queue',            /** 合同生成队列 **/
        'ancunQueue' => 'ancun_queue',                  /** 安存发送队列 **/

        'trafficHourIP' => 'traffic_hour_ip:{pm}_{hour}',           /** 小时访问流量IP **/
        'trafficHourPV' => 'traffic_hour_pv:{hour}',                /** 小时访问流量PV **/
        'trafficHourUV' => 'traffic_hour_uv:{pm}_{hour}',           /** 小时问流量UV **/
        'trafficDateIP' => 'traffic_date_ip:{pm}_{date}',           /** 日访问流量IP **/
        'trafficDateUV' => 'traffic_date_uv:{pm}_{date}',           /** 日访问流量UV **/
        'trafficWeekIP' => 'traffic_week_ip:{pm}_{week}',           /** 周访问流量IP **/
        'trafficWeekUV' => 'traffic_week_uv:{pm}_{week}',           /** 周访问流量UV **/
        'trafficMonthIP' => 'traffic_month_ip:{pm}_{month}',        /** 月访问流量IP **/
        'trafficMonthUV' => 'traffic_month_uv:{pm}_{month}',        /** 月访问流量UV **/
    ];

    public static function getKey($name, $params=[]) {
        $key = isset(self::$keys[$name])?self::$keys[$name]:'';
        foreach ($params as $k => $v) {
            $key = str_replace('{'.$k.'}', $v, $key);
        }
        return $key;
    }

    public static function getUser($userId, $column='') {
        $user = self::hGetAll('user:'.$userId);
        if($column!='') {
            return isset($user[$column])?$user[$column]:false;
        }
        return count($user)>0?$user:false;
    }

    public static function setUser(array $user) {
        $userId = isset($user['userId'])?$user['userId']:0;
        $status = self::hMset('user:'.$userId, $user);
        return $status;
    }

    public static function updateUser(array $user) {
        $userId = isset($user['userId'])?$user['userId']:0;
        unset($user['userId']);
        foreach ($user as $key => $value) {
            self::hSet('user:'.$userId, $key, $value);
        }
    }

    public static function __callStatic($func, $arguments){
        if(self::$redis==null) {
            self::$redis = RedisFactory::create();
        }
        return call_user_func_array([self::$redis, $func], $arguments);
    }
}