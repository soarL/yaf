<?php
namespace traits;

trait BatchInsert {

    /**
     * 批次插入分批
     * @param  array   $logs       要插入的记录
     * @param  integer $batchCount 没批最大次数
     * @return integer             失败次数
     */
    public static function batchInsert($logs, $batchCount=100) {
        $count = 0;
        $subLogs = [];
        $failCount = 0;
        foreach ($logs as $log) {
            $subLogs[] = $log;
            $count ++;
            if($count%$batchCount==0) {
                $status = self::insert($subLogs);
                if(!$status) {
                    $failCount += $batchCount;
                } 
                $subLogs = [];
            }
        }
        $count = count($subLogs);
        if($count>0) {
            $status = self::insert($subLogs);
            if(!$status) {
                $failCount += $count;
            } 
        }
        return $failCount;
    }

}