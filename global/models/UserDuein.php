<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * Link|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class UserDuein extends Model {
	
	protected $table = 'user_duein';

	public $timestamps = false;

    /**
     * 佣金计算
     * @return [type] [description]
     */
    public static function calcCommission($total,$stay){
    	switch ($total) {
    		case ($total <= 100000) :
    			$rate = 0.005;
    			break;
    		case ($total > 100000 && $total <= 200000) :
    			$rate = 0.006;
    			break;
    		case ($total > 200000 && $total <= 500000) :
    			$rate = 0.007;
    			break;
    		case ($total > 500000 && $total <= 1000000) :
    			$rate = 0.008;
    			break;
    		case ($total > 1000000 && $total <= 3000000) :
    			$rate = 0.009;
    			break;
    		case ($total > 3000000) :
    			$rate = 0.01;
    			break;
    		default:
    			$rate = 0.005;
    			break;
    	}
    	return round($stay*$rate/360,2);
    }
}