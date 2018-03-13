<?php
namespace models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

class WorkInfo extends Model {

	protected $table = 'work_info';

	public $timestamps = false;
	
	public static function getWorkInfo($type){
		return self::where('oddkey',$type)->first(['oddvalue'])->oddvalue;
	}
}
