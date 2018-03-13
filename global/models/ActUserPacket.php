<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * ActUserPacket|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class ActUserPacket extends Model {

	protected $table = 'act_user_packetes';

	function user(){
		return $this->belongsTo('models\User', 'userId','userId');
	}
}