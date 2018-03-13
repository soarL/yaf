<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * UserLog|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class UserEstimate extends Model {
	
	protected $table = 'user_estimate';

	public $timestamps = false;

	public function user() {
		return $this->belongsTo('models\User', 'userId');
	}
	
}