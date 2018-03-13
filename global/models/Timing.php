<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * Activity|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Timing extends Model {
	
	protected $table = 'work_timing';

	public $timestamps = false;

  	public function odd() {
  		$time = date('Y-m-d H:i:s',time()+1800);
   		return $this->hasOne('models\Odd', 'oddNumber', 'oddNumber')->where('openTime','<=',$time);
  	}

}