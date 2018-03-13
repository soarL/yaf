<?php
namespace models;

use Illuminate\Database\Eloquent\Model;
use helpers\DateHelper;

/**
 * UserSign|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class UserSign extends Model {
	
	protected $table = 'user_sign_log';

	public $timestamps = false;

	/**
	 * 用户签到
	 * @param  User    $user     用户
	 * @return array             签到状态及信息
	 */
	public static function sign($user) {
		$lastSign = self::where('username', $user->username)->orderBy('addTime', 'desc')->first();
		$rdata = [];
		if($lastSign && DateHelper::isSameDay(strtotime($lastSign->addTime), time())) {
			$rdata['status'] = 0;
			$rdata['info'] = '您今天已经签到了！';
			return $rdata;
		}
		$continuousDay = 1;
		if($lastSign && DateHelper::isYestoday(strtotime($lastSign->addTime))) {
			$continuousDay = $lastSign->continuousDay + 1;
		}
		$sign = new self();
		$sign->username = $user->username;
		$sign->continuousDay = $continuousDay;
		$sign->addTime = date('Y-m-d H:i:s');
		if($sign->save()) {
			$rdata['status'] = 1;
			$rdata['info'] = '签到成功！';
		} else {
			$rdata['status'] = 0;
			$rdata['info'] = '签到失败！';
		}
		return $rdata;
	}

	/**
	 * 获取当天签到人数
	 * @return int 当天签到人数
	 */
	public static function getSignCount() {
		$timeBegin = date('Y-m-d', time()) . ' 00:00:00';
		$timeEnd = date('Y-m-d', time()) . ' 23:59:59';
		$params[':timeBegin'] = $timeBegin;
		$params[':timeEnd'] = $timeEnd;
		return self::where('addTime', '>=', $timeBegin)->where('addTime', '<=', $timeEnd)->count();
	}

	/**
	 * 用户是否签到
	 * @param  User   $user  用户
	 * @return boolean             是否签到
	 */
	public static function isUserSign($user) {
		if(!$user) {
			return false;
		}
		$timeBegin = date('Y-m-d', time()) . ' 00:00:00';
		$timeEnd = date('Y-m-d', time()) . ' 23:59:59';
		$params[':timeBegin'] = $timeBegin;
		$params[':timeEnd'] = $timeEnd;
		$params[':username'] = $user->username;
		$count = self::where('addTime', '>=', $timeBegin)->where('addTime', '<=', $timeEnd)->where('username', $user->username)->count();
		return $count>0?true:false;
	}
}