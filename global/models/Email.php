<?php
namespace models;

use Illuminate\Database\Eloquent\Model;
use Yaf\Registry;
use helpers\NetworkHelper;
use plugins\mail\Mail;
use tools\Log;

/**
 * Email|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Email extends Model {
	protected $table = 'system_emaillog';

	public $timestamps = false;

	public static $templates = ['<div style="background: url(#@{web_asset}@#/common/images/email_bg.png) no-repeat left bottom; font-size:14px; width: 588px; ">
			<div style="padding: 10px 0px; background: url(#@{web_asset}@#/common/images/email_button.png)  no-repeat ">
				<h1 style="padding: 0px 15px; margin: 0px; overflow: hidden; height: 48px;">
					<a title="汇诚普惠用户中心" href="#@{web_user}@#/account" target="_blank" swaped="true">
					<img style="border-width: 0px; padding: 0px; margin: 0px;" alt="汇诚普惠用户中心" src="#@{web_asset}@#/common/images/email_logo.png" height="48" width="208">		</a>
				</h1>
				<div style="padding: 0px 20px; overflow: hidden; line-height: 40px; height: 50px; text-align: right;"> </div>
				<div style="padding: 2px 20px 30px;">
					<p>亲爱的 <span style="color: rgb(196, 0, 0);">#@{username}@#</span> , 您好！</p>
					<p>请点击下面的链接#@{action}@#:</p>
					<p style="overflow: hidden; width: 100%; word-wrap: break-word;"><a href="#@{link}@#" target="_blank" swaped="true">#@{link}@#</a>
					<br><span style="color: rgb(153, 153, 153);">(如果链接无法点击，请将它拷贝到浏览器的地址栏中)</span></p>
					
					<p style="text-align: right;"><br>汇诚普惠用户中心 敬启</p>
					<p><br>此为自动发送邮件，请勿直接回复！如您有任何疑问，请点击<a title="点击联系我们" style="color: rgb(15, 136, 221);" href="#@{web_main}@#" target="_blank" >联系我们&gt;&gt;</a></p>
				</div>
			</div>
		</div>'];

	public static $types =  [
		'setEmail' => ['action'=>'/user/validateSetEmail', 'actionName'=>'设置常用邮箱', 'template'=>0, 'title'=>'设置常用邮箱'],
		'checkUpdateEmail' => ['action'=>'/user/validateUpdateEmail', 'actionName'=>'修改常用邮箱', 'template'=>0, 'title'=>'修改常用邮箱'],
		'updateEmail' => ['action'=>'/user/validateUpdateEmailTwo', 'actionName'=>'验证新邮箱', 'template'=>0, 'title'=>'验证新邮箱'],
	];

	public static function send($data){
		$rdata = [];
		if(!isset(self::$types[$data['type']])) {
			$rdata['status'] = 0;
			$rdata['info'] = '发送类型不存在！';
			return $rdata;
		}
		$lastLog = self::where('email', $data['email'])->where('sendType', $data['type'])->orderBy('sendTime', 'desc')->first();
		if($lastLog) {
			if(time()-strtotime($lastLog->sendTime)<60) {
				$rdata['status'] = 0;
				$rdata['info'] = '发送过于频繁，请稍后再发送！';
				return $rdata;
			}
		}

		$user = Registry::get('user');
		$userId = '';
		$username = '汇诚普惠用户';
		if($user) {
			$userId = $user->userId;
			$username = $user->username;
		}

		$type = self::$types[$data['type']];

		$code = self::generateCode($userId, $data['type']);
		$link = WEB_USER.$type['action'].'/email/'.$data['email'].'/code/'.$code;
		
		$text = self::$templates[$type['template']];
		$text = str_replace('#@{username}@#', $username, $text);
		$text = str_replace('#@{action}@#', $type['actionName'], $text);
		$text = str_replace('#@{link}@#', $link, $text);
		$text = str_replace('#@{web_user}@#', WEB_USER, $text);
		$text = str_replace('#@{web_main}@#', WEB_MAIN, $text);
		$text = str_replace('#@{web_asset}@#', WEB_ASSET, $text);

		$status = Mail::send($type['title'], $text , [$data['email']]);
		if($status) {
			$log = new self();
			$log->userId = $userId;
			$log->email = $data['email'];
			$log->sendCode = $code;
			$log->sendType = $data['type'];
			$log->sendTime = date('Y-m-d H:i:s', time());
			$log->save();
			$rdata['status'] = 1;
			$rdata['info'] = '发送成功！';
			return $rdata;
		} else {
			$rdata['status'] = 0;
			$rdata['info'] = '发送失败！';
			Log::write('邮箱发送错误：'.Mail::$msg, [], 'email');
			return $rdata;
		}
	}

	public static function checkCode($email, $code, $type, $userId=false) {
		$log = self::where('sendCode', $code)->orderBy('sendTime', 'desc')->first();
		$rdata = [];
		if($log) {
			if($userId&&$userId!=$log->userId) {
				$rdata['status'] = 0;
				$rdata['info'] = '该链接错误！';
				return $rdata;
			}
			if($email!=$log->email) {
				$rdata['status'] = 0;
				$rdata['info'] = '该链接错误！';
				return $rdata;
			}
			if($log->sendCode==$code) {
				if($log->status=='1') {
					$rdata['status'] = 0;
					$rdata['info'] = '该链接已失去效！';
					return $rdata;
				}
				if((time()-strtotime($log->sendTime))>3600) {
					$log->status = 1;
					$log->save();
					$rdata['status'] = 0;
					$rdata['info'] = '该链接已过期！';
					return $rdata;
				} else {
					$log->status = 1;
					$log->save();
					$rdata['status'] = 1;
					$rdata['info'] = '验证成功！';
					return $rdata;
				}
			} else {
				$rdata['status'] = 0;
				$rdata['info'] = '验证错误！';
				return $rdata;
			}
		} else {
			$rdata['status'] = 0;
			$rdata['info'] = '验证错误！';
			return $rdata;
		}
	}

	public static function generateCode($userId, $sendType) {
		$siteinfo = Registry::get('siteinfo');
      	$md5 = md5(microtime().$userId.$sendType.$siteinfo['clientIp']);
      	return $md5;
	}
}