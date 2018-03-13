<?php
namespace models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * RankingWeek|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class RankingWeek extends Model {
	
	protected $table = 'system_ranking_week';

	public $timestamps = false;
	
	public static function generate($timeBegin, $timeEnd) {
		$count = 100;
        $sql = "select sum(resultMoney) totalMoney,userId from (select t1.money resultMoney,t1.userId from "
            .with(new OddMoney)->getTable()." t1 left join ".with(new Odd)->getTable()
            ." t2 on t1.oddNumber=t2.oddNumber where t1.`status`=:status and t1.type=:type and t1.ckclaims<>'1' and"
            ." t2.oddBorrowStyle=:style and t2.oddRehearTime>=:beginTime and t2.oddRehearTime<=:endTime) t3 GROUP BY userId"
            ." order by totalMoney desc limit " . $count;
        $params = [];
        $params['type'] = 'invest';
        $params['status'] = 1;
        $params['style'] = 'month';
        $params['beginTime'] = $timeBegin;
        $params['endTime'] = $timeEnd;
        $result = DB::select($sql, $params);

		$lastRanking = self::all();
		$newLastRanking = [];
		foreach ($lastRanking as $rank) {
			$newLastRanking[$rank->username] = $rank->id;
		}

		self::truncate();

		$list = [];
		foreach ($result as $row) {
			$list[] = $row->userId;
		}
		$users = User::whereIn('userId', $list)->get();
		$usernames = _package($users, 'userId', 'username');

		DB::beginTransaction();
		foreach ($result as $row) {
			$rank = new self();
			$rank->username = $usernames[$row->userId];
			$rank->tenderMoney = $row->totalMoney===null?0:$row->totalMoney;
			$rank->save();
		}
		DB::commit();
		
		$nowRanking = self::all();

		DB::beginTransaction();
		foreach ($nowRanking as $rank) {
			$last = isset($newLastRanking[$rank->username])?$newLastRanking[$rank->username]:$count+1;
			$change = $last - $rank->id;
			$rank->rankChange = $change;
			$rank->save();
		}
		DB::commit();
	}

}