<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * UserHouse|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class UserHouse extends Model {

	protected $table = 'user_house';

	public $timestamps = false;

	public function user() {
		return $this->belongsTo('models\User', 'userId');
	}

	public static function noneInfo() {
		$word = '未填写';
		$info = [];
		$info['houseradder'] = $word;
		$info['houserarea'] = $word;
		$info['houseryear'] = $word;
		$info['houserpay'] = $word;
		$info['houserpayShow'] = $word;
		$info['housername1'] = $word;
		$info['housername2'] = $word;
		$info['houserage'] = $word;
		$info['housermonth'] = $word;
		$info['houserbalance'] = $word;
		$info['houserbank'] = $word;
		return $info;
	}

	public function prepareInfo() {
		$info = [];
		$word = '未填写';
		$info['houseradder'] = $this->houseradder==''?$word:$this->houseradder;
		$info['houserarea'] = $this->houserarea==''?$word:$this->houserarea.'平米';
		$info['houseryear'] = $this->houseryear==''?$word:$this->houseryear.'年';
		if($this->houserpay=='y') {
			$info['houserpayShow'] = '已供完房';
		} else if($this->houserpay=='n') {
			$info['houserpayShow'] = '按揭中';
		} else {
			$info['houserpayShow'] = $word;
		}
		$info['houserpay'] = $this->houserpay;
		$info['housername1'] = $this->housername1==''?$word:$this->housername1;
		$info['housername2'] = $this->housername2==''?$word:$this->housername2;
		$info['houserage'] = $this->houserage==''?$word:$this->houserage.'年';
		$info['housermonth'] = $this->housermonth==''?$word:$this->housermonth.'元';
		$info['houserbalance'] = $this->houserbalance==''?$word:$this->houserbalance.'元';
		$info['houserbank'] = $this->houserbank==''?$word:$this->houserbank;
		return $info;
	}
}