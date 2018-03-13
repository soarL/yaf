<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * Article|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Article extends Model {
	
	protected $table = 'system_article';

	public $timestamps = false;

	public static function types() {
		return self::groupBy('rootType')->get(['rootType']);
	}

	public static function getTops($num=8) {
		return self::orderBy('id', 'desc')->limit($num)->get(['id','title']);
	}

	public static function getTopByType($type) {
		return self::where('rootType', $type)->orderBy('id', 'desc')->first(['id','title', 'body']);
	}

	public static function getByType($type, $num=4) {
		return self::where('rootType', $type)->orderBy('id', 'desc')->limit($num)->get(['id','title']);
	}
}