<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * Mail|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Mail extends Model {

	protected $table = 'system_webmail';

	public $timestamps = false;

	/**
	 * 接收短信
	 * @param  string $username 用户名
	 * @return void
	 */
	public static function receiveByUsername($username) {
		$mailList = UserMail::where('username', $username)->get(['webmailId']);
		$mailIdList = [];
		foreach ($mailList as $mail) {
			$mailIdList[] = $mail->webmailId;
		}
		$mails = self::whereRaw('(receiveUser=? or sendType=?)', [$username, 1])->whereNotIn('id', $mailIdList)->get(['id']);
		foreach ($mails as $key => $mail) {
			$userMail = new UserMail();
			$userMail->webmailId = $mail->id;
			$userMail->username = $username;
			$userMail->status = 0;
			$userMail->save();
		}
	}
}