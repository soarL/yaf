<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * Job|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Job extends Model {
	
	protected $table = 'system_jobs';

	public function department() {
		return $this->belongsTo('models\Department', 'dp_id');
	}
}