<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * Task|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Task extends Model {
	
	protected $table = 'work_task';

	public $timestamps = false;
}