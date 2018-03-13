<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * LookVote|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class LookVote extends Model {
	
	protected $table = 'system_look_votes';

	public $timestamps = true;

	public function odd() {
		return $this->belongsTo('models\Odd', 'oddNumber');
	}

	public function user() {
		return $this->belongsTo('models\User', 'userId');
	}

}