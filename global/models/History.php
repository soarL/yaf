<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * History|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class History extends Model {
	
	protected $table = 'system_history';

	public $timestamps = false;

}