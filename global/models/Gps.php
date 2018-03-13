<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * Gps|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Gps extends Model {
    protected $table = 'tmp_gps';

    public $timestamps = false;

    public $gmAccounts = [
        ['username'=>'xwsd', 'password'=>'XwsdGps321.'],
        ['username'=>'zhanweixiao', 'password'=>'XwsdGps321.'],
        ['username'=>'汇诚普惠2', 'password'=>'XwsdGps321.'],
    ];

    public $bcxAccounts = [
        ['username'=>'zhanweixiao', 'password'=>'XwsdGps321.'],
    ];

    public $ocAccounts = [
        ['username'=>'xwsd', 'password'=>'123456.'],
    ];
}