<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * SyncLog|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class SyncLog extends Model {
    
    protected $table = 'user_sync_log';

    public $timestamps = false;

}