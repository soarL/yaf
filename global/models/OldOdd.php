<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * OldOdd|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class OldOdd extends Model {

	protected $table = 'system_oldodd';

	public $timestamps = false;

	public function user() {
	    return $this->belongsTo('models\User', 'user_id');
	}

	public function tenders() {
		return $this->hasMany('models\OldInvest', 'borrow_nid', 'borrow_nid');
	}
}