<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * OldUser|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class OldUser extends Model {

    protected $table = 'system_old_users';

    public $timestamps = false;

    public function user() {
        return $this->belongsTo('models\User', 'userId');
    }
}