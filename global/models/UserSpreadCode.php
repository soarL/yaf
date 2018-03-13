<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * UserFriend|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class UserSpreadCode extends Model {

	protected $table = 'user_spread_code';

	public $timestamps = false;


	public static function getSpreadCode($userId,$type) {
		$spreadCode = '';
		$spread = self::where('userId', $userId)->where('type', $type)->first();
		$spreadCode = $spread?$spread->spreadCode:(self::addOne($userId,$type));
		return $spreadCode;
	}

	public static function addOne($userId, $type) {
		$userSpreadCode = new self();
		$userSpreadCode->userId = $userId;
		$userSpreadCode->spreadCode = substr(md5($userId.$type), 8, 16);
		$userSpreadCode->type = $type;
		$userSpreadCode->time = date('Y-m-d H:i:s', time());
		$userSpreadCode->save();
		return $userSpreadCode->spreadCode;
	}
}