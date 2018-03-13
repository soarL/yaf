<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * OperationLog|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class OperationLog extends Model {
	
	protected $table = 'system_operation_logs';

	public $timestamps = false;

	public static function addOne($manager, $content) {
		$data = [];
		$data['userId'] = $manager->userId;
		$data['content'] = $content;
		$data['action_time'] = date('Y-m-d H:i:s');
		return self::insert($data);
	}

}