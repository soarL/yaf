<?php
namespace models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Ranking|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Ranking extends Model {
	
	protected $table = 'system_ranking';

	public $timestamps = false;

	public static function generate() {
		$day = date('Y-m-d', time()-24*60*60);
		$dayBegin = $day.' 00:00:00';
		$dayEnd = $day.' 23:59:59';
		$count = 100;

		$sql = "select total+investmoney as totalMoney, t4.userId from (select sum(resultMoney) total,userId from"
			." (select t1.money resultMoney,t1.userId from "
			.with(new OddMoney)->getTable()." t1 left join ".with(new Odd)->getTable()
			." t2 on t1.oddNumber=t2.oddNumber where t1.`status`=:status and t1.type=:type and t1.ckclaims<>'1' and "
			."t2.oddBorrowStyle='month' and t2.oddRehearTime<=:time) t3 GROUP BY userId) t4 left JOIN "
			.with(new OldData)->getTable()." t5 on t4.userId=t5.userId order by totalMoney desc limit " . $count;
		$params = [];
		$params['type'] = 'invest';
		$params['status'] = 1;
		$params['time'] = $dayEnd;
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
			$change = $last - $rank['id'];
			$rank->rankChange = $change;
			$rank->save();
		}
		DB::commit();
	}
	
}