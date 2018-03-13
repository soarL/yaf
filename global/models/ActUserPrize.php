<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * ActUserPrize|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class ActUserPrize extends Model {

	protected $table = 'act_user_prize';

	public $timestamps = false;

	public function prize() {
        return $this->belongsTo('models\ActPrize', 'prizeId');
    }

    public function user() {
        return $this->belongsTo('models\User', 'userId');
    }

}