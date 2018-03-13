<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * UserLink|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class UserLink extends Model {

	protected $table = 'user_link';

	public $timestamps = false;

	public static function noneInfo() {
		$word = '未填写';
		$info = [];
		$info['firstLinkName'] = $word;
		$info['firstRelation'] = $word;
		$info['firstPhone'] = $word;
		$info['secondLinkName'] = $word;
		$info['secondRelation'] = $word;
		$info['secondPhone'] = $word;
		$info['thirdLinkName'] = $word;
		$info['thirdRelation'] = $word;
		$info['thirdPhone'] = $word;
		return $info;
	}

	public function user() {
		return $this->belongsTo('models\User', 'userId');
	}

	public static function getRelationName($identifier) {
		if($identifier=='house') {
			return '家庭成员';
		} else if($identifier=='friend') {
			return '朋友';
		} else if($identifier=='business') {
			return '商业伙伴';
		} else {
			return '未填写';
		}
	}

	public function prepareInfo() {
		$info = [];
		$word = '未填写';
		$info['firstLinkName'] = $this->firstLinkName==''?$word:$this->firstLinkName;
		$info['firstRelation'] = self::getRelationName($this->firstRelation);
		$info['firstPhone'] = $this->firstPhone==''?$word:$this->firstPhone;
		$info['secondLinkName'] = $this->secondLinkName==''?$word:$this->secondLinkName;
		$info['secondRelation'] = self::getRelationName($this->secondRelation);
		$info['secondPhone'] = $this->secondPhone==''?$word:$this->secondPhone;
		$info['thirdLinkName'] = $this->thirdLinkName==''?$word:$this->thirdLinkName;
		$info['thirdRelation'] = self::getRelationName($this->thirdRelation);
		$info['thirdPhone'] = $this->thirdPhone==''?$word:$this->thirdPhone;
		return $info;
	}
}