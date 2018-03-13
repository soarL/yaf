<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

class Redpack extends Model {

	protected $table = 'user_redpack';

	public $timestamps = false;

	public static $types = [
		'rpk-spread' => '推荐奖励',
        'rpk-newuser' => '新手红包',
        'rpk-normal' => '红包',
        'rpk-tran' => '资金迁移',
        'rpk-cancel' => '红包撤销',
        'rpk-interest' => '加息券加息',
        'rpk-investmoney' => '抵扣红包',
	];

	public function user() {
		return $this->belongsTo('models\User', 'userId');
	}

	public static function isUserGet($userId) {
		$redpack = self::where('userId', $userId)->where('status', 1)->first();
		if($redpack) {
			return true;
		} else {
			return false;
		}
	}

	public static function canUserGet($user) {
		$redpack = self::where('userId', $user->userId)->where('type', 'rpk-newuser')->where('status', 1)->count();
		if($redpack>0) {
			return 2;
		} else {
			$money = OddMoney::getTenderMoneyByUser($user->userId, '', '', 'redpack') + OldData::getTenderMoneyByUser($user->userId);
			if($money<10000) {
				return 0;
			}
			$regTime = strtotime($user->addtime);
			$twoMonth = $regTime+30*24*60*60*2;
			$now = time();
			if($now<$twoMonth) {
				return 0;
			}
			return 1;
		}
	}
}