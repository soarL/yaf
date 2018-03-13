<?php
namespace models;

use tools\Banks;
use tools\Areas;
use Illuminate\Database\Eloquent\Model;

/**
 * CustomService|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class CustomService extends Model {
	
	protected $table = 'system_admin';

	public $timestamps = false;

	public static function checkIsCustomService($uid) {
		$count = self::where('uid', $uid)->where('dept_id', 11)->count();
		if($count>0) {
			return true;
		} else {
			return false;
		}
	}
}