<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * Reward|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Reward extends Model {

	protected $table = 'user_reward';

	public $timestamps = false;

	public static function getTenderReard($userId, $oddmoneyId) {
                $row = self::where('oddmoneyId', $oddmoneyId)->where('userId', $userId)->orderBy('addtime', 'desc')->first(['money']);
                if($row) {
                	return $row->money;
                } else {
                	return 0;
                }
	}
}