<?php

use models\StandMoney;
use models\Statistics;
use models\User;
use models\Recharge;
use models\Odd;
use models\OddMoney;
use models\Withdraw;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * StatisticsController
 * 数据统计控制器
 * 
 * @author chenwei <269646431@qq.com>
 * @version 1.0
 */
class StatisticsController extends Controller {
	/**
	 * 写入今日以前的统计数据
	 */
	public function dsAction() {
		set_time_limit(0);
		$this->insert();
	}
	
	/**
	 * 递归写入数据
	 */
	private function insert() {
		$statistics = Statistics::orderBy('time', 'desc')->first();
		if (empty($statistics)) {
			$firstUser = User::getFirst();
			$time = substr($firstUser->addtime , 0 , 10);
		} else {
			$time = $this->getNextDay($statistics->time);
		}
		
		if ($time < date('Y-m-d')) {
			$data = $this->getData($time);
			$res = Statistics::insert($data);
			$res = $res ? 'success' : 'fail';
			echo  $time . ' ' . $res . PHP_EOL;
			$this->insert();
		} else {
			echo 'over';
			exit;
		}
	}
	
	/**
	 * 获取给定时间的第二天日期
	 * @param string $time 日期，Y-m-d
	 * @return string
	 */
	private function getNextDay($time) {
		$time = strtotime($time);
		$time = $time + 86400;
		$time = date('Y-m-d', $time);
		return $time;
	}
	
	/**
     * 获取统计数据
     * @param string $time
     * @return array
     */
    private function getData($time) {
    	$data = [];
    	$data['time'] = $time;
    	// 当天注册人数
    	$data['registrations'] = User::where('addTime', 'like', $time.'%')->where('userType', '1')->count();
    	// 用户充值数据
    	$recharge = Recharge::getDateDs($time);
    	$data = array_merge($data, $recharge);
    	// 用户投资数据
    	$data['oddMoney'] = Odd::where('opentime', 'like', $time.'%')->where('progress', '<>' ,'fail')->sum('oddMoney');
    	$data['oddMoney'] || $data['oddMoney'] = 0;
    	$workOdd = OddMoney::getDateDs($time);
    	$data = array_merge($data, $workOdd);
    	// 用户提现数据
    	$withdraw = Withdraw::select(DB::raw('count(1) as count, sum(outMoney) as outMoney'))->where('addTime', 'like', $time.'%')->where('status', 1)->first();
    	
    	$data['withdrawNum'] = $withdraw->count;
    	$data['withdrawMoney'] = $withdraw->outMoney ? $withdraw->outMoney : 0;
    	return $data;
    }

    /**
     * 定时站岗资金插入数据库
     */
    public function getStandDataAction()
    {
        $result = DB::select("SELECT count(t1.userId) as validCount , SUM(t2.fundMoney) validMoney
        FROM work_oddautomatic t1
        LEFT JOIN system_userinfo t2 ON t1.userId = t2.userId
        WHERE autostatus = '1'
        AND staystatus = '0'
        AND types <> ''
        AND investMoneyUper > investMoneyLower
        AND (t2.fundMoney - t1.investEgisMoney) >= 50
        AND (t2.fundMoney - t1.investEgisMoney) >= investMoneyLower");
        $validCount = $result[0]->validCount;
        $validMoney = $result[0]->validMoney;
        $invalidCount = User::where('fundMoney','>',0)->count() - $validCount;
        $invalidMoney = User::where('fundMoney','>',0)->sum('fundMoney') - $validMoney;

        $standMoney = new StandMoney();
        $standMoney->validCount = $validCount;
        $standMoney->validMoney = $validMoney;
        $standMoney->invalidCount = $invalidCount;
        $standMoney->invalidMoney = $invalidMoney;
        $standMoney->sumCount = $validCount+$invalidCount;
        $standMoney->sumMoney = $validMoney+$invalidMoney;
        $standMoney->addtime = date('y-m-d',time());
        $standMoney->save();
    }
	
	
}