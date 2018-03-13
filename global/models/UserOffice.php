<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * UserOffice|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class UserOffice extends Model {

	protected $table = 'user_office';

	public $timestamps = false;

	public static function noneInfo() {
		$word = '未填写';
		$info = [];
		$info['officename'] = $word;
		$info['officephone'] = $word;
		$info['officecity'] = $word;
		$info['officeadder'] = $word;
		$info['officeyear'] = $word;
		$info['officeproof'] = $word;
		$info['officeprooftel'] = $word;
		return $info;
	}

	public function user() {
		return $this->belongsTo('models\User', 'userId');
	}

	public function getOfficeyearName() {
		$officeyear = $this->officeyear;
		if($officeyear==5) {
			return '10年以上';
		} else if($officeyear==4) {
			return '5-10年';
		} else if($officeyear==3) {
			return '3-5年';
		} else if($officeyear==2) {
			return '1-3年';
		} else if($officeyear==1) {
			return '1年以下';
		} else {
			return '未填写';
		}
	}

	public function prepareInfo() {
		$info = [];
		$word = '未填写';
		$info['officename'] = $this->officename==''?$word:$this->officename;
		$info['officephone'] = $this->officephone==''?$word:$this->officephone;
		$info['officecity'] = $this->officecity==''?$word:$this->officecity;
		$info['officeadder'] = $this->officeadder==''?$word:$this->officeadder;
		$info['officeyear'] = $this->getOfficeyearName();
		$info['officeproof'] = $this->officeproof==''?$word:$this->officeproof;
		$info['officeprooftel'] = $this->officeprooftel==''?$word:$this->officeprooftel;
		return $info;
	}
}