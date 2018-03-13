<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * AccessLog|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class AccessLog extends Model {

    protected $table = 'system_access_logs';
    
    public $timestamps = false;

}