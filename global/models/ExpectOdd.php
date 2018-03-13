<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * ExpectOdd|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class ExpectOdd extends Model {

	protected $table = 'system_expect_odds';

	public $timestamps = true;

	public static $types = [
        1 => '手动标',
        2 => '新手标',
        3 => '自动标',
    ];
    
    public static $times = [
        9=>'9:00', 
        10=>'10:00', 
        11=>'11:00', 
        12=>'12:00', 
        13=>'13:00', 
        14=>'14:00', 
        15=>'15:00', 
        16=>'16:00', 
        17=>'17:00', 
        18=>'18:00', 
        19=>'19:00', 
        20=>'20:00',
        25=>'不定时',
    ];

    public static $periods = [
        1=>'1个月', 
        2=>'2个月', 
        3=>'3个月', 
        6=>'6个月', 
        11=>'11个月', 
        12=>'12个月', 
        24=>'24个月',
        30=>'30周',
        35=>'35周',
        40=>'40周',
        45=>'45周',
        50=>'50周',
    ];

    public function getContent() {
        return $this->title.' '.($this->money/10000.00).'万元/'
            .self::$periods[$this->period].' '.date('m-d', strtotime($this->day)).' '.self::$times[$this->time].'发标';
    }

}