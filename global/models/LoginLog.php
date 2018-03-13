<?php
namespace models;

use Yaf\Registry;
use Illuminate\Database\Eloquent\Model;

/**
 * LoginLog|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class LoginLog extends Model {

	protected $table = 'user_login_log';

	public $timestamps = false;

	public function user() {
		return $this->belongsTo('models\User', 'userId','userId');
	}

	public static function log($userId) {
		$siteinfo = Registry::get('siteinfo');
		$ip = $siteinfo['clientIp'];
		$log = new self();
		$log->loginIp = $ip;
		$log->userId = $userId;
		$log->loginTime = date('Y-m-d H:i:s', time());
		$log->save();
	}
}