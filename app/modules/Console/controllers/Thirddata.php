<?php
use helpers\StringHelper;
use models\Odd;
use models\User;
use models\OddMoney;
use models\OldData;
use models\Interest;
use tools\DuoZhuan;
use helpers\NetworkHelper;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * ThirddataController
 * 第三方数据接口
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class ThirddataController extends Controller {
	
	public function rongtuAction() {
		$odds = Odd::with('tenders', 'user')->where('progress', 'start')->get();

		if(count($odds)==0) {
			$this->export('推送融途网，无数据不推送！', self::CONSOLE_FULL_SUF, true);
		}

		$data = [];
		
		$borrows = [];
		foreach ($odds as $key => $odd) {
			$borrows[$key]['borrowid'] = $odd->oddNumber;
			$borrows[$key]['name'] = $odd->oddTitle;
			$borrows[$key]['url'] = WEB_MAIN . \Url::to('/odd/view', ['num'=>$odd->oddNumber]);
			$borrows[$key]['isday'] = $odd->oddBorrowStyle=='day'?1:0;
			$borrows[$key]['timelimit'] = $odd->oddBorrowStyle=='day'?0:$odd->oddBorrowPeriod;
			$borrows[$key]['timelimitday'] = $odd->oddBorrowStyle=='day'?$odd->oddBorrowPeriod:0;
			$borrows[$key]['account'] = $odd->oddMoney;
			$borrows[$key]['owner'] = StringHelper::getHideUsername($odd->user->username);
			$borrows[$key]['apr'] = $odd['oddYearRate']*100;
			$borrows[$key]['award'] = 0;
			$borrows[$key]['partaccount'] = 0;
			$borrows[$key]['funds'] = 0;
			$borrows[$key]['repaymentType'] = $odd->oddRepaymentStyle=='monthpay'?3:0;
			$borrows[$key]['type'] = 1;
			$borrows[$key]['addtime'] = strtotime($odd->oddTrialTime);
			$borrows[$key]['sumTender'] = $odd->successMoney;
			$borrows[$key]['startmoney'] = $odd->startMoney;
			$borrows[$key]['tenderTimes'] = $odd->getTenderTime();
		}

		$list = [];

		$endTime = date('Y-m-d 00:00:00');
		$beginTime = date('Y-m-d H:i:s', strtotime($endTime)-30*24*60*60);

		$odds = Odd::where('oddTrialTime', '>=', $beginTime)
			->where('oddTrialTime', '<', $endTime)
			->where('progress', '<>', 'fail')
			->where('oddType', '<>', 'special')
			->get(['oddYearRate', 'oddTrialTime', 'oddMoney', 'oddRehearTime']);

		$oddNums = [];
		$aprAvgs = [];
		$aprTotals = [];
		$moneyTotals = [];
		$smoneyTotals = [];
		for ($i=0; $i < 30; $i++) { 
			$dayBegin = strtotime($endTime)-24*60*60*($i+1);
			$dayEnd = $dayBegin+24*60*60;
			$key = date('m-d', $dayBegin);
			$aprAvgs[$key] = 0;
			$oddNums[$key] = 0;
			$aprTotals[$key] = 0;
			$moneyTotals[$key] = 0;
			$smoneyTotals[$key] = 0;
			foreach ($odds as $odd) {
				if(strtotime($odd->oddTrialTime)>=$dayBegin&&strtotime($odd->oddTrialTime)<$dayEnd) {
					$oddNums[$key] += 1;
					$aprTotals[$key] = $aprTotals[$key]+$odd->oddYearRate;
					$aprAvgs[$key] = $aprTotals[$key]/$oddNums[$key];
					$moneyTotals[$key] = $moneyTotals[$key]+$odd->oddMoney;
					// $moneyAvgs[$key] = $moneyTotals[$key]/$oddNums[$key];
				}
				if(strtotime($odd->oddRehearTime)>=$dayBegin&&strtotime($odd->oddRehearTime)<$dayEnd) {
					$smoneyTotals[$key] = $smoneyTotals[$key]+$odd->oddMoney;
				}
			}
			$aprAvgs[$key] = round(($aprTotals[$key]/$oddNums[$key])*100, 2);
			$moneyTotals[$key] = round($moneyTotals[$key]/10000, 2);
			$smoneyTotals[$key] = round($smoneyTotals[$key]/10000, 2);
		}

		$list['apr_data'] = $aprAvgs;
		$list['count_data'] = $moneyTotals;
		$list['dcount_data'] = $smoneyTotals;

		$periods = Odd::where('oddBorrowStyle', 'month')
			->whereIn('progress', ['run', 'end'])
			->where('oddType', '<>', 'special')
			->groupBy('oddBorrowPeriod')
			->get([DB::raw('sum(oddMoney) total'), 'oddBorrowPeriod']);

		$timeData = [['1-3个月', 0],['4-6个月', 0],['7-12个月', 0],['12个月以上', 0]];

		foreach ($periods as $period) {
			if($period['oddBorrowPeriod']<=3) {
				$timeData[0][1] = $timeData[0][1] + $period['total'];
			} else if($period['oddBorrowPeriod']>=4&&$period['oddBorrowPeriod']<=6) {
				$timeData[1][1] = $timeData[1][1] + $period['total'];
			} else if($period['oddBorrowPeriod']>=7&&$period['oddBorrowPeriod']<=12) {
				$timeData[2][1] = $timeData[2][1] + $period['total'];
			} else {
				$timeData[3][1] = $timeData[3][1] + $period['total'];
			}
		}
		$oldVolume = OldData::sum('investmoney');
		$timeData[0][1] += $oldVolume;

		$otherMoney = Odd::where('oddBorrowStyle', 'day')
			->whereIn('progress', ['run', 'end'])
			->where('oddType', '<>', 'special')
			->sum('oddMoney');
		$timeData[0][1] += $otherMoney;

		$totalVolume = round(($timeData[0][1] + $timeData[1][1] + $timeData[2][1] + $timeData[2][1]) / 10000, 2);

		$timeData[0][1] = round($timeData[0][1]/10000, 2);
		$timeData[1][1] = round($timeData[1][1]/10000, 2);
		$timeData[2][1] = round($timeData[2][1]/10000, 2);
		$timeData[2][1] = round($timeData[3][1]/10000, 2);

		$stayMoney = round(Interest::getStayMoney()/10000, 2);

		$list['time_data'] = "[1-3个月,{$timeData[0][1]}],[4-6个月, {$timeData[1][1]}],[7-12个月, {$timeData[2][1]}],[12个月以上, {$timeData[3][1]}]";
		$list['cj_data'] = $totalVolume;
		$list['dh_data'] = $stayMoney;

		$yestoday = date('m-d', time()-24*60*60);
		$list['avg_apr'] = $aprAvgs[$yestoday];

		$danganId = 1117;

		$data['borrow'] = json_encode($borrows);
		$data['list'] = json_encode($list);
		$data['dangan_id'] = $danganId;

		//正式接口: http://shuju.erongtu.com/api/borrow
		//测试接口: http://shuju.erongtu.com/api/test

		$url = 'http://shuju.erongtu.com/api/borrow';
		$result = NetworkHelper::post($url, $data);

		$this->export('推送融途网数据，响应结果：' . $result);
	}

	/**
	 * 多赚用户投资监控
	 * 每天晚上10点05分运行
	 */
	public function duozhuanAction() {
		$begin = date('Y-m-d H:i:s', (strtotime(date('Y-m-d 22:00:00')) - 24*3600));
		$end = date('Y-m-d 22:00:00');
		$result = DuoZhuan::actTenders($begin, $end);
		$this->export('推送多赚用户投资数据，响应结果：' . $result);
	}

    /**
     * 多赚用户投资信息请求多赚接口
     *
     */
    public function dzUserTenderInfoAction() {
        $end = date('Y-m-d H:00:00');
        $begin = date('Y-m-d H:i:s', strtotime($end)-3600);
        $result = DuoZhuan::userTenderInfo($begin, $end);
        $this->export('推送多赚用户投资数据，响应结果：' . $result);
    }
}