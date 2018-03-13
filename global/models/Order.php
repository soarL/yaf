<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * Order|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Order extends Model {
	
	protected $table = 'system_order';
	
	public $timestamps = false;

	public static $types = [
		'商品房',
		'拆迁安置房',
		'经济适用房',
		'自建房',
		'写字楼',
		'商业店面',
		'别墅',
		'其他',
	];

	public static $yearRates = [
		'6.0%-6.4%',
		'6.5%-6.9%',
		'7.0%-7.4%',
		'7.5%-7.9%',
		'8.0%-8.4%',
		'8.5%-8.9%',
		'9.0%-9.4%',
		'9.5%-9.9%',
		'10.0%-10.4%',
		'10.5%-11.9%',
		'11.0%-11.4%',
		'11.5%-11.9%',
		'12.0%-12.4%',
	];


}