<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * UserMail|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class UserMail extends Model {

	protected $table = 'system_user_webmail';

	public $timestamps = false;

	public function user() {
		return $this->belongsTo('models\User', 'username', 'username');
	}

	public function mail() {
		return $this->belongsTo('models\Mail', 'webmailId');
	}

	public static function notReadCount($username) {
		return self::where('username', $username)->where('status','0')->count();
	}
}