<?php
namespace models;

use tools\Redis;
use Illuminate\Database\Eloquent\Model;
use traits\BatchInsert;

/**
 * Protocol|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Protocol extends Model {
    use BatchInsert;
    
	protected $table = 'user_protocols';

	public $timestamps = false;

    public function task($object) {
        $key = Redis::getKey('protocolQueue');
        Redis::lPush($key, $object->type.'-'.$object->id);
    }
}