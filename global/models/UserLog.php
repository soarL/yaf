<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * UserLog|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class UserLog extends Model {
	
	protected $table = 'user_log';

	public $timestamps = false;

	public function user() {
		return $this->belongsTo('models\User', 'userId');
	}
	
	public static function saveModel($type, $model, $columns=[]) {
		$data = [];
		if(count($columns)>0) {
			foreach ($columns as $key) {
				$data[$key] = $model->$key;
			}
			$data['host'] = $_SERVER['SERVER_NAME'];
		} else {
			$data = $model;
		}
		$data = json_encode($data);
		$log = new self();
		$log->userId = $model->userId;
		$log->data = $data;
		$log->change_time = date('Y-m-d H:i:s');
		$log->type = $type;
		return $log->save();
	}
}