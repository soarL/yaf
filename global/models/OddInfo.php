<?php
namespace models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * OddInfo|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class OddInfo extends Model {
    protected $table = 'work_odd_info';

    protected $primaryKey = 'oddNumber';

    public $incrementing = false;
    public $timestamps = false;

    public function getImages($column, $return='array', $type='all', $prev=WEB_ASSET) {
        $string = $this->$column;
        $list = explode('|', $string);
        $result = [];
        foreach ($list as $item) {
            $img = explode(',', $item);
            $min = isset($img[1])?$img[1]:'';
            $max = isset($img[0])?$img[0]:'';
            if($max == '' || $min == ''){
                continue;
            }
            if($type=='small') {
                $result[] = strpos($min, 'http')===0?$min:$prev.$min;
            } else if($type=='normal') {
                $result[] = strpos($max, 'http')===0?$max:$prev.$max;
            } else {
                $result[] = [
                'max'=> strpos($max, 'http')===0?$max:$prev.$max,
                'min'=> strpos($min, 'http')===0?$min:$prev.$min, 
                'normal'=> strpos($max, 'http')===0?$max:$prev.$max,
                ];
            }
        }
        if($return=='string') {
            if($type=='all') {
                return $this->$column;
            } else {
                return implode('|', $result);
            }
        } 
        return $result;
    }
}