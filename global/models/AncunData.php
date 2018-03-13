<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * AncunData|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class AncunData extends Model {

	protected $table = 'user_ancun_data';

	public $timestamps = false;

	public function oddMoney() {
		return $this->belongsTo('models\OddMoney', 'tradeNo', 'tradeNo');
	}
}