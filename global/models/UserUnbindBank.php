<?php
namespace models;

use tools\Banks;
use tools\Areas;
use Illuminate\Database\Eloquent\Model;

/**
 * UserUnbindBank|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class UserUnbindBank extends Model {

	protected $table = 'user_bank_unbind';

	public $timestamps = false;
	
}