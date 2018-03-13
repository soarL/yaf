<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * UserSetting|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class UserSetting extends Model {

	protected $table = 'user_settings';

	public $timestamps = true;

	/**
	 * 用户
	 * @return Builder
	 */
	public function user() {
		return $this->belongsTo('models\User', 'userId');
	}
}