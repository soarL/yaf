<?php
use tools\Log;
use tools\Redis;
use Illuminate\Database\Capsule\Manager as DB;
use forms\admin\LotteryForm;
use models\AccessLog;
use models\Pmvv;
use models\Promotion;
use helpers\StringHelper;
use models\TrafficDay;
use models\TrafficMonth;
use models\TrafficHour;
use models\TrafficWeek;

/**
 * LogController
 * 日志系统任务控制器
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class LogController extends Controller {

    /**
     * 访问日志备份 [* 0,7,10,13,16,20 * * *]
     * @return mixed
     */
    public function backUpAccessAction() {
        $count = Redis::lSize('access_logs');
        for ($i=0; $i < $count; $i++) { 
            $result = Redis::lpop('access_logs');
            $log = json_decode($result, true);
            if($log) {
                $hour = date('YmdH', strtotime($log['accessed_at']));
                $key = Redis::getKey('trafficHourIP', ['pm'=>'all', 'hour'=>$hour]);
                Redis::sAdd($key, $log['ip']);
                
                $key = Redis::getKey('trafficHourPV', ['hour'=>$hour]);
                Redis::hIncrBy($key, 'all', 1);

                $uv = substr(md5($log['uv'].$log['ip']), 8, 16);
                $key = Redis::getKey('trafficHourUV', ['pm'=>'all', 'hour'=>$hour]);
                Redis::sAdd($key, $uv);

                if($log['pm']=='') {
                    if($log['refer']=='') {
                        $key = Redis::getKey('trafficHourIP', ['pm'=>'direct', 'hour'=>$hour]);
                        Redis::sAdd($key, $log['ip']);

                        $key = Redis::getKey('trafficHourPV', ['hour'=>$hour]);
                        Redis::hIncrBy($key, 'direct', 1);

                        $uv = substr(md5($log['uv'].$log['ip']), 8, 16);
                        $key = Redis::getKey('trafficHourUV', ['pm'=>'direct', 'hour'=>$hour]);
                        Redis::sAdd($key, $uv);
                    }
                } else {
                    $key = Redis::getKey('trafficHourIP', ['pm'=>$log['pm'], 'hour'=>$hour]);
                    Redis::sAdd($key, $log['ip']);
                    
                    $key = Redis::getKey('trafficHourPV', ['hour'=>$hour]);
                    Redis::hIncrBy($key, $log['pm'], 1);

                    $uv = substr(md5($log['uv'].$log['ip']), 8, 16);
                    $key = Redis::getKey('trafficHourUV', ['pm'=>$log['pm'], 'hour'=>$hour]);
                    Redis::sAdd($key, $uv);
                }
            }
        }
    }

    /**
     * 访问日志备份，每天凌晨1点执行 [0 1 * * *]
     * @return mixed
     */
    public function backUpAccessTotalAction() {
        $yestoday = date('Ymd', time()-24*60*60);
        echo $yestoday;
        $keys = Redis::keys('traffic_hour_uv:*');
        $records = [];
        $dayPVList = [];
        $hourList = [];
        $sucList = [];
        foreach ($keys as $key) {
            $str = str_replace('traffic_hour_uv:', '', $key);
            $ipKey = 'traffic_hour_ip:'.$str;
            $item = explode('_', $str);
            $date = substr($item[1], 0, 8);
            if($yestoday==$date) {
                $pvKey = 'traffic_hour_pv:'.$item[1];
                $row = [];
                $row['pm_key'] = $item[0];
                $row['hour'] = $item[1];
                $row['pv'] = Redis::hGet($pvKey, $item[0]);
                $row['uv'] = Redis::sSize($key);
                $row['ip'] = Redis::sSize($ipKey);
                if(isset($dayPVList[$item[0]])) {
                    $dayPVList[$item[0]] += $row['pv'];
                } else {
                    $dayPVList[$item[0]] = $row['pv'];
                }
                $hourList[] = $row;

                $dayIPKey = Redis::getKey('trafficDateIP', ['pm'=>$item[0], 'date'=>$yestoday]);
                $dayUVKey = Redis::getKey('trafficDateUV', ['pm'=>$item[0], 'date'=>$yestoday]);
                Redis::sUnionStore($dayIPKey, $dayIPKey, $ipKey);
                Redis::sUnionStore($dayUVKey, $dayUVKey, $key);

                $sucList[] = $key;
                $sucList[] = $pvKey;
                $sucList[] = $ipKey;
            }
        }

        TrafficHour::insert($hourList);

        Redis::delete($sucList);

        // 汇总上一日流量数据
        $month = date('Ym', strtotime($yestoday));
        $yesTime = strtotime($yestoday);
        $monday = date('Ymd', ($yesTime-((date('w', $yesTime)==0?7:date('w', $yesTime))-1)*24*3600));
        $dayList = [];
        $sucList = [];
        foreach ($dayPVList as $key => $pvCount) {
            $dayIPKey = Redis::getKey('trafficDateIP', ['pm'=>$key, 'date'=>$yestoday]);
            $dayUVKey = Redis::getKey('trafficDateUV', ['pm'=>$key, 'date'=>$yestoday]);
            $row = [];
            $row['pm_key'] = $key;
            $row['date'] = $yestoday;
            $row['pv'] = $pvCount;
            $row['uv'] = Redis::sSize($dayUVKey);
            $row['ip'] = Redis::sSize($dayIPKey);
            $dayList[] = $row;

            $weekIPKey = Redis::getKey('trafficWeekIP', ['pm'=>$key, 'week'=>$monday]);
            $weekUVKey = Redis::getKey('trafficWeekUV', ['pm'=>$key, 'week'=>$monday]);
            Redis::sUnionStore($weekIPKey, $weekIPKey, $dayIPKey);
            Redis::sUnionStore($weekUVKey, $weekUVKey, $dayUVKey);

            $monthIPKey = Redis::getKey('trafficMonthIP', ['pm'=>$key, 'month'=>$month]);
            $monthUVKey = Redis::getKey('trafficMonthUV', ['pm'=>$key, 'month'=>$month]);
            Redis::sUnionStore($monthIPKey, $monthIPKey, $dayIPKey);
            Redis::sUnionStore($monthUVKey, $monthUVKey, $dayUVKey);

            $subList[] = $dayUVKey;
            $subList[] = $dayIPKey;
        }
        TrafficDay::insert($dayList);

        Redis::delete($sucList);

        // 每周一统计上周流量数据
        if(date('w')==1) {
            // 上周一日期
            $firstDay = date('Ymd', time()-7*24*60*60);
            $lastDay = $yestoday;
            $list = TrafficDay::where('date', '>=', $firstDay)
                ->where('date', '<=', $lastDay)
                ->groupBy('pm_key')
                ->get(['pm_key', DB::raw('sum(pv) as pvNum')]);
            $pvList = [];
            foreach ($list as $row) {
                $pvList[$row['pm_key']] = $row['pvNum'];
            }
            $keys = Redis::keys('traffic_week_uv:*');
            $weekList = [];
            $sucList = [];
            foreach ($keys as $key) {
                $str = str_replace('traffic_week_uv:', '', $key);
                $item = explode('_', $str);
                $monday = $item[1];
                $weekIPKey = Redis::getKey('trafficWeekIP', ['pm'=>$item[0], 'week'=>$monday]);
                $weekUVKey = Redis::getKey('trafficWeekUV', ['pm'=>$item[0], 'week'=>$monday]);

                $row = [];
                $row['pm_key'] = $item[0];
                $row['monday'] = $monday;
                $row['pv'] = $pvList[$item[0]];
                $row['uv'] = Redis::sSize($weekUVKey);
                $row['ip'] = Redis::sSize($weekIPKey);
                $weekList[] = $row;

                $sucList[] = $weekUVKey;
                $sucList[] = $weekIPKey;
            }
            TrafficWeek::insert($weekList);

            Redis::delete($sucList);
        }

        // 每月1日统计上一月流量数据
        if(date('j')==1) {
            $lastDay = date('Ymd', time()-24*3600);
            $firstDay = date('Ym01', strtotime($lastDay));
            $keys = Redis::keys('traffic_month_uv:*');
            $list = TrafficDay::where('date', '>=', $firstDay)
                ->where('date', '<=', $lastDay)
                ->groupBy('pm_key')
                ->get(['pm_key', DB::raw('sum(pv) as pvNum')]);
            $pvList = [];
            foreach ($list as $row) {
                $pvList[$row['pm_key']] = $row['pvNum'];
            }
            $monthList = [];
            $sucList = [];
            foreach ($keys as $key) {
                $str = str_replace('traffic_month_uv:', '', $key);
                $item = explode('_', $str);
                $month = $item[1];
                $monthIPKey = Redis::getKey('trafficMonthIP', ['pm'=>$item[0], 'month'=>$month]);
                $monthUVKey = Redis::getKey('trafficMonthUV', ['pm'=>$item[0], 'month'=>$month]);

                $row = [];
                $row['pm_key'] = $item[0];
                $row['month'] = $item[1];
                $row['pv'] = $pvList[$item[0]];
                $row['uv'] = Redis::sSize($monthUVKey);
                $row['ip'] = Redis::sSize($monthIPKey);
                $monthList[] = $row;

                $sucList[] = $monthUVKey;
                $sucList[] = $monthIPKey;
            }

            TrafficMonth::insert($monthList);
            Redis::delete($sucList);
        }
    }
}