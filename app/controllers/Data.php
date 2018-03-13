
<?php
use Yaf\Registry;
use helpers\StringHelper;
use helpers\NetworkHelper;
use exceptions\HttpException;
use models\Odd;
use models\OddMoney;
use models\Interest;
use models\Invest;
use Illuminate\Database\Capsule\Manager as DB;

class DataController extends Controller {
    
    public $menu = 'Data';

    public function xiehuiAction() {
        ini_set('memory_limit','1024M');
        set_time_limit(0);
        $date = $this->getQuery('date', date('Y-m-d'));
        $time = $date . ' 23:59:59';
        $interests = Interest::with(['odd'=>function($q) { $q->select(['oddNumber', 'progress', 'oddRehearTime', 'oddBorrowPeriod', 'oddYearRate']); }])
            ->where('endtime', '>', $time)
            ->get(['oddNumber', 'zongEr']);

        $stayOdds = [];
        $stayMoney = 0;
        $stayCount = 0;
        foreach ($interests as $interest) {
            if($interest->odd->progress!='fail'&&strtotime($interest->odd->oddRehearTime)<strtotime($time)) {
                $stayMoney += $interest->zongEr;
                if(isset($stayOdds[$interest->oddNumber])) {
                    $stayOdds[$interest->oddNumber]['stay'] = $stayOdds[$interest->oddNumber]['stay'] + $interest->zongEr;
                } else {
                    $stayCount ++;
                    $stayOdds[$interest->oddNumber] = [
                        'period'=>$interest->odd->oddBorrowPeriod, 
                        'stay'=> $interest->zongEr, 
                        'rate'=>$interest->odd->oddYearRate
                    ];
                }
            }
        }
        $stayMoney = $stayMoney/1000;

        $iUserCount = Odd::where('oddRehearTime', '<', $time)->where('progress', '<>', 'fail')->whereHas('interests', function($q) use($time) {
            $q->where('endtime', '>', $time);
        })->count(DB::raw('distinct userId'));

        $invests = Invest::with(['odd'=>function($q) { $q->select(['oddNumber', 'progress', 'oddRehearTime']); }])
            ->where('endtime', '>', $time)
            ->get(['oddNumber', 'userId']);
        $oUsers = [];
        $oUserCount = 0;
        foreach ($invests as $invest) {
            if($invest->odd->progress!='fail'&&strtotime($invest->odd->oddRehearTime)<strtotime($time)) {
                if(!in_array($invest->userId, $oUsers)) {
                    $oUsers[] = $invest->userId;
                    $oUserCount ++;
                }
            }
        }
        unset($oUsers);

        $row = Odd::where('oddRehearTime', '<', $time)->where('progress', '<>', 'fail')->where('oddBorrowStyle', 'month')
        ->whereHas('interests', function($q) use($time) {
            $q->where('endtime', '>', $time);
        })->first([
            DB::raw('sum(oddBorrowPeriod*30)/count(1) as avgPeriod')
        ]);
        $avgPeriod = $row->avgPeriod;

        $avgMoney = $stayMoney/$stayCount;

        $tmp = 0;
        foreach ($stayOdds as $item) {
            $tmp += ($item['rate']*100)*($item['stay']/10000)*($item['period']*30);
        }
        $avgRate = (365/$avgPeriod)*$tmp/$stayMoney;

        echo '    借款余额：' . $stayMoney . "\n";
        echo '  总借款人数：' . $iUserCount . "\n";
        echo '    出借人数：' . $oUserCount . "\n";
        echo '平均借款期限：' . $avgPeriod . "\n";
        echo '平均借款额度：' . $avgMoney . "\n";
        echo '平均借款利率：' . $avgRate . "\n";
    }
}