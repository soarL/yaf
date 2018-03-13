<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * UserBespoke|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class UserBespoke extends Model {

	protected $table = 'user_bespokes';

	public static $moneyList = [
		1 => '5万—10万',
		2 => '10万—20万',
		3 => '20万以上',
	];

	public static $monthList = [
		// 3 => '3月',
//		6 => '6月',
		12 => '12月',
		24 => '24月',
	];

	public function user() {
        return $this->belongsTo('models\User', 'userId');
    }
}