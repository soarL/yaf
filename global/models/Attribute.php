<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * Attribute|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Attribute extends Model {

	protected $table = 'system_attribute';

	public $timestamps = false;


	public static function getByIdentity($identity) {
		if(is_array($identity)) {
			$in = [];
			foreach ($identity as $i) {
				$in[] = '?';
			}
			$results = self::whereRaw('identity in ('.implode(',', $in).')', $identity)->get();
			$attributes = [];
			foreach ($results as $result) {
				$attributes[$result['identity']] = $result['value'];
			}
			return $attributes;
		} else if(is_string($identity)) {
			$result = self::where('identity', $identity)->first();
			if($result) {
				return $result['value'];
			}
		}
		return null;
	}

	public static function updateByIdentity($identity, $value=null) {
		if(is_array($identity)&&$value===null) {
			foreach ($identity as $i => $v) {
				self::where('identity', $i)->update(['value' => $v]);
			}
		} else if(is_string($identity)&&$value!==null) {
			self::where('identity', $identity)->update(['value' => $value]);
		}
	}
}