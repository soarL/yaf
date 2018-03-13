<?php
namespace models;

use custody\Code;
use Illuminate\Database\Eloquent\Model;

/**
 * BailRepay|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class BailRepay extends Model {

    protected $table = 'work_bail_repay';

    public $timestamps = false;

    public function odd() {
        return $this->belongsTo('models\Odd', 'oddNumber');
    }
    
    public function getResult() {
        if($this->result) {
            return Code::getMsg($this->result);
        }
        return '';
    }
}
