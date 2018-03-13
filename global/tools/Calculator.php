<?php
namespace tools;

/**
 * Calculator
 * 工具类，计算器
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Calculator {

    public static $sumInterest = 0;

    private static function getRepayType($key) {
        if($key==='monthpay') { // bcai
            return 1;
        } else if($key==='matchpay') { // avg_ci
            return 2;
        } else if($key==='avg_c') {
            return 3;
        }
        return $key;
    }

    public static function getResult($data) {
        $period = isset($data['period'])?$data['period']:0;
        $account = isset($data['account'])?$data['account']:0;
        $repayType = isset($data['repayType'])?$data['repayType']:0;
        $periodType = isset($data['periodType'])?$data['periodType']:'';
        $yearRate = isset($data['yearRate'])?$data['yearRate']:0;
        
        $timeStatus = isset($data['timeStatus'])?$data['timeStatus']:0;
        $beginTime = isset($data['time'])?$data['time']:date('Y-m-d H:i:s');
        $beginTime = strtotime($beginTime);

        $monthRate = $yearRate/12;
        $weekRate = $yearRate/360*7;
        $dayRate = $yearRate/360;

        $periodRate = 0;
        if($periodType=='month') {
            $periodRate = $monthRate;
        } else if($periodType=='week') {
            $periodRate = $weekRate;
        }

        $params = [];
        $params['period'] = $period;
        $params['account'] = $account;
        $params['periodRate'] = $periodRate;

        $repayType = self::getRepayType($repayType);

        $list = [];
        if($repayType==1) {
            $list = self::biac($params); // 先息后本
        } else if($repayType==2) {
            $list = self::acpi($params); // 等额本息
        } else if($repayType==3) {
            $list = self::ac($params); // 等额本金
        }

        if($timeStatus) {
            $list = self::getTimes($list, $beginTime, $period, $periodType);
        }

        $result = [];
        $result['list'] = $list;
        $result['yearRate'] = round($yearRate*100, 2);
        $result['monthRate'] = round($monthRate*100, 2);
        $result['weekRate'] = round($weekRate*100, 2);
        $result['dayRate'] = round($dayRate*100, 2);
        $result['sumInterest'] = self::$sumInterest;
        $result['sumTotal'] = $account + self::$sumInterest;

        return $result;
    }
    
    /**
     * 等额本息计算 average capital plus interest
     * A借款金额
     * N总期数
     * R每期利率
     * X每期还款总额,每期都一样
     *
     * 第一期利息：A*R
     * 第一期还款本金: Y1 = X - A*R
     * 第一期剩余本金: Z1 = A - (X - A*R) = A*(1+R) - X
     *
     * 第二期利息：(A*(1+R) - X)*R
     * 第二期还款本金: Y2 = X - (A*(1+R) - X)*R
     * 第二期剩余本金: Z2 = Z1 - Y2 = A*(1+R) - X - (X - (A*(1+R) - X)*R)
     *     .
     *     .
     *     .
     * 第n期的剩余本金 = A*(1+R)^n - X*Sn 【Sn为 (1+R) 的等比数列前n项和】
     * 如：
     *    第3期的剩余本金 = A*(1+R)^3 - X*[1 + (1+R) + (1+R)^2]
     *
     * 最后一期剩余本金 = A*(1+R)^N - X*( (1+R)^N - 1)/ R = 0
     * 
     * 所以每期还款总额 X = A*R*(1+R)^N /((1+R)^N-1)
     * 第n月的利息 A*R*[(1+R)^N-(1+R)^(n-1)]/(1+R)^N-1)
     **/
    private static function acpi($data) {
        $period = $data['period'];
        $periodRate = $data['periodRate'];
        $account = $data['account'];

        $rateTime = pow(1 + $periodRate, $period);

        // 每期还款总额
        $total = round($account * $periodRate * $rateTime / ($rateTime - 1), 2);

        $sumCapital = 0;
        $sumInterest = 0;

        $dm = 0;

        $list = [];
        for ($i=1; $i <= $period; $i++) {
            $tmpVal = $rateTime - pow(1+$periodRate, $i-1);
            
            $interest = 0;
            if($i < $period) {
                $interest = round($account * $periodRate * $tmpVal/($rateTime-1), 2);
                $capital = $total - $interest;
            } else {
                $capital = $account - $sumCapital;
                $interest = round($total - $capital, 2);

                // 最后一期误差
                if($interest<0) {
                    $dm = abs($interest);
                    $interest = 0;
                    $total = $interest + $capital;
                }
            }

            $sumCapital +=  $capital;
            $sumInterest +=  $interest;

            $remain = $total*($period-$i);

            $list[] = ['period'=>$i, 'interest'=>$interest, 'capital'=>round($capital, 2), 'total'=>round($total, 2), 'remain'=>round($remain, 2)];
        }

        if($dm>0) {
            foreach ($list as $key => $item) {
                if($item['period']!=$period) {
                    $item['remain'] = round($item['remain'] + $dm, 2);
                    $list[$key] = $item;
                }
            }
        }

        self::$sumInterest = round($sumInterest, 2);

        return $list;
    }

    /**
     * 先息后本 before interest after capital
     **/
    private static function biac($data = array()) {
        $period = $data['period'];
        $account = $data['account'];
        $periodRate = $data['periodRate'];
        
        $interest = round($account * $periodRate, 2);
        
        $sumInterest = 0;
        $list = [];

        $totalInterest = round($account * $periodRate * $period, 2);

        for ($i=1; $i <= $period; $i++) {

            $capital = 0;

            if($period==$i) {
                $capital = $account;
                $interest = $totalInterest - ($interest * ($period - 1));
            }

            $total = $capital + $interest;

            $remain = $account - $capital + $interest * ($period - $i);

            $sumInterest += $interest;

            $list[] = ['period'=>$i, 'interest'=>$interest, 'capital'=>round($capital, 2), 'total'=>round($total, 2), 'remain'=>round($remain, 2)];
        }

        self::$sumInterest = round($sumInterest, 2);

        return $list;
    }

    /**
     * 等额本金 average capital
     * 当期本金 = 总贷款数/还款次数
     * 当期利息 = 剩余本金*每期利率 = 总贷款数*[1 - (还款期数-1) / 还款次数]*每期利率
     * 总利息 = [(总贷款额/还款期数+总贷款额*每期利率)+总贷款额/还款期数*(1+每期利率)]/2*还款期数-总贷款额
     **/
    private static function ac($data = array()) {
        $period = $data['period'];
        $account = $data['account'];
        $periodRate = $data['periodRate'];

        $capital = round($account / $period, 2);

        $sumTotal = (($account/$period+$account*$periodRate)+$account/$period*(1+$periodRate))/2*$period;

        $list = [];
        $sumCapital = 0;
        $sumInterest = 0;

        for ($i=1; $i <= $period; $i++) {

            if($period==$i) {
                $capital = $account - $sumCapital;
            }

            $interest = round(($account - $sumCapital) * $periodRate, 2);

            $total = $capital + $interest;

            $sumCapital += $capital;
            $sumInterest += $interest;

            $remain = $sumTotal - $sumCapital - $sumInterest;

            $list[] = ['period'=>$i, 'interest'=>$interest, 'capital'=>round($capital, 2), 'total'=>round($total, 2), 'remain'=>round($remain, 2)];
        }

        self::$sumInterest = round($sumInterest, 2);

        return $list;
    }

    /**
     * 生成每期开始时间和还款时间
     **/
    private static function getTimes($repayList, $beginTime, $period, $periodType) {
        $distanceTime = 0;
        if($periodType=='month') {
            $distanceTime = 30*24*60*60;
        } else if($periodType=='week') {
            $distanceTime = 7*24*60*60;
        }

        foreach ($repayList as $key => $item) {
            $item['begin'] = date('Y-m-d H:i:s', $beginTime);
            
            $endTime = $beginTime + $distanceTime;
            $item['end'] = date('Y-m-d H:i:s', $endTime);

            $beginTime = $endTime;
            $repayList[$key] = $item;
        }
        return $repayList;
    }
}