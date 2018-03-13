<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * TranLog|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class TranLog extends Model {

    protected $table = 'user_tran_logs';

    public $timestamps = false;

    public function user() {
        return $this->belongsTo('models\User', 'userId');
    }
}