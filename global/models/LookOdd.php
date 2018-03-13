<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * LookOdd|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class LookOdd extends Model {
	
	protected $table = 'system_look_odds';

	public $timestamps = true;

	public function odd() {
		return $this->belongsTo('models\Odd', 'oddNumber');
	}
}