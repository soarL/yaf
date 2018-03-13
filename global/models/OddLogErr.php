<?php
namespace models;

use Illuminate\Database\Eloquent\Model;
use Yaf\Registry;
use helpers\DateHelper;

/**
 * Odd|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class OddLogErr extends Model {
	protected $table = 'work_oddlogError';

    /**
     * 写借款流程日志
     * @global type $oddNumber
     * @param type $remark
     * @param string $sqllog
     * @param type @status 成功的写入work_oddlog 失败的写入work_oddlogError
     */
    static function writeLog($data) {
    	$data['user'] = 'sysadmin';
        $data['remark'] = '{SYSTEM}:' . $data['remark'];
        if(!empty($data['sqllog'])){
            $data['sqllog'] = str_replace("'", '"', $data['sqllog']);
        }
	    return $res = self::insert($data);
    }
}