<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * NewMoneyLog|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class NewMoneyLog extends Model {
    
    protected $table = 'user_moneylog_new';

    public $timestamps = false;
}