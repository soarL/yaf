<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * ActUserAddress|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class ActUserAddress extends Model {

	protected $table = 'act_user_address';

	public $timestamps = false;


	/**
	 * 用户是否设置地址
	 * @param  string  $userId userId
	 * @return boolean
	 */
	public static function isUserSet($userId) {
		$count = self::where('userId', $userId)->count();
		if($count>0) {
			return true;
		} else {
			return false;
		}
	}

}