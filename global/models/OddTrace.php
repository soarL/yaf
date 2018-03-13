<?php
namespace models;

use Illuminate\Database\Eloquent\Model;
use Yaf\Registry;
use helpers\DateHelper;

/**
 * Odd|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class OddTrace extends Model {
	protected $table = 'work_odd_trace';

    public function odd() {
    	return $this->belongsTo('models\Odd', 'oddNumber');
    }

}