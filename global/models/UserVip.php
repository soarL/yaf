<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * UserVip|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class UserVip extends Model {

	protected $table = 'user_vip';

	public $timestamps = false;

	public static function getVipByUser($userId) {
		$userVip = self::where('userId', $userId)->first();
		if($userVip) {
			$endtime = strtotime($userVip->endTime);
			if($endtime>time()) {
				return $userVip;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public static function getTMByCode($code) {
		$rdata = [];
		if($code==1) {
			$rdata['time'] = 30*24*60*60;
			$rdata['money'] = 0;
		} else if($code==2) {
			$rdata['time'] = 60*24*60*60;
			$rdata['money'] = 0;
		} else if($code==3) {
			$rdata['time'] = 95*24*60*60;
			$rdata['money'] = 0;
		} else if($code==4) {
			$rdata['time'] = 190*24*60*60;
			$rdata['money'] = 0;
		} else if($code==5) {
			$rdata['time'] = 365*24*60*60;
			$rdata['money'] = 0;
		} else if($code==6) {
			$rdata['time'] = 820*24*60*60;
			$rdata['money'] = 0;
		} else if($code==7) {
			$rdata['time'] = 1400*24*60*60;
			$rdata['money'] = 0;
		} else {
			$rdata = false;
		}

		$rdata['time'] = 365*24*60*60;
		$rdata['money'] = 0;

		return $rdata;
	}
	
}