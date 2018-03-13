<?php
namespace models;

use Illuminate\Database\Eloquent\Model;
use models\User;
use helpers\StringHelper;

/**
 * Promotion|model类
 * 
 * @version 1.0
 */
class Promotion extends Model {
	const PM_KEY = 'pm_key';

	protected $table = 'promotion_channel';

	public $timestamps = false;
	
	public static function getIDByUrl($url, $list = false) {
		if(!$list) {
			$list = self::getKVList();
		}
		$val = StringHelper::getUrlParam($url, self::PM_KEY);
		if($val) {
			return isset($list[$val])?$list[$val]:0;
		}
		return 0;
	}

	public static function getKVList($key='code') {
		$list = self::get(['id', 'channelCode']);
		$rows = [];
		if($key=='code') {
			foreach ($list as $item) {
				$rows[$item['channelCode']] = $item['id'];
			}
		} else {
			foreach ($list as $item) {
				$rows[$item['id']] = $item['channelCode'];
			}
		}
	}
}