<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * Video|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Video extends Model {

	protected $table = 'system_video';

	public $timestamps = false;


	public function area() {
        return $this->belongsTo('models\VideoArea', 'area_id');
    }
}