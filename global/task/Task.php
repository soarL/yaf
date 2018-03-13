<?php
namespace task;

use tools\Redis;
use tools\Log;

/**
 * Task
 * 任务类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Task {
    const TASK_SLEEP = 1;
    const OT_LIMIT = 10;

    private static $OT = 0;

    public static $tasks = [
        'sms' => 'task\handlers\SmsHandler',
        'invest' => 'task\handlers\InvestHandler',
        'redpack' => 'task\handlers\RedpackHandler',
        'test' => 'task\handlers\TestHandler',
        'trial' => 'business\TrialHandler',
        'rehear' => 'business\RehearHandler',
        'repay' => 'business\RepayHandler',
        'autobid' => 'business\AutoBidHandler',
    ];

    /**
     * 获取任务对应handler
     * @param  string $name 任务名
     * @return string       对应handler类名
     */
    public static function getHanlder($name) {
        return isset(self::$tasks[$name])?self::$tasks[$name]:false;
    }

    /**
     * 添加任务
     * @param string  $name   任务名
     * @param array   $params 任务运行参数
     * @param integer $OT     时间复杂度，0表示以handler内置复杂度为准
     * @return boolean
     */
    public static function add($name, $params, $OT=0) {
        $data = ['name'=>$name, 'params'=>$params, 'OT'=>$OT];
        $str = json_encode($data);
        return Redis::lPush('global_tasks', $str);
    }

    /**
     * 运行任务
     * @param  integer $num      运行队列任务数量，0为根据时间复杂度运行
     * @param  boolean $callback 回调函数
     */
    public static function run($num=0, $callback = false) {
        if($num==0) {
            while(true) {
                $isContinue = self::runSingle($callback);
                if(!$isContinue) {
                    break;
                }
                sleep(self::TASK_SLEEP);
            }
        } else {   
            for ($i=0; $i < $num; $i++) { 
                self::runSingle($callback);
                sleep(self::TASK_SLEEP);
            }
        }
    }

    /**
     * 运行单个任务
     * @param  mixed    $callback    回调函数
     * @return boolean               是否继续执行下一个
     */
    public static function runSingle($callback = false) {
        $isContinue = true;
        $taskJson = Redis::rPop('global_tasks');
        if(!$taskJson) {
            return false;
        }
        $item = json_decode($taskJson, true);
        $handlerClass = self::getHanlder($item['name']);
        if(class_exists($handlerClass)) {
            $handler = new $handlerClass($item['params'], $item['OT']);
            $OT = $handler->getOT();
            if(self::$OT+$OT<=self::OT_LIMIT) {
                self::$OT = self::$OT + $OT;
                $result = $handler->handle();
                if(is_callable($callback)) {
                    $callback($item['name'], $result);
                }
            } else {
                Redis::rPush('global_tasks', $taskJson);
                $isContinue = false;
            }
        } else {
            Log::write('执行任务['.$item['name'].']失败，找不到对应Handler！', [], 'task', 'ERROR');
        }
        return $isContinue;
    }
}