<?php
namespace models;

use Illuminate\Database\Eloquent\Model;
use traits\BatchInsert;

/**
 * Integration|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Integration extends Model {
    use BatchInsert;

	protected $table = 'user_integral';

	public $timestamps = false;

    public static $types = [
        // 'repayment' => '回款',
        'recvpayment' => '回款',
    ];

    public static $rates = [
        'month-1' => 1,
        'month-2' => 1.2,
        'month-3' => 1.4,
        'month-6' => 1.6,
        'month-12' => 2,
        'month-24' => 3,
        'week-30' => 1.6,
        'week-35' => 2,
        'week-40' => 2, 
        'week-45' => 2, 
        'week-50' => 2
    ];

    public function translate() {
        $linkMarks = [
            '@oddNumber' => WEB_MAIN . '/odd/',
            '@crtrNumber' => WEB_MAIN . '/crtr/view/num/',
        ];

        $remark = $this->remark;
        foreach ($linkMarks as $mark => $link) {
            $num = preg_match_all('/(?<='.$mark.'{)\d+(?=})/', $remark, $matches);
            if($num>0) {
                foreach ($matches[0] as $value) {
                    $search = $mark.'{'.$value.'}';
                    $name = $this->getNameByMark($mark, $value);
                    $replace = '<a target="_blank" href="'.$link.$value.'">'.$name.'</a>';
                    $remark = str_replace($search, $replace, $remark);
                }
            }
        }
        return $remark;
    }

    public function getNameByMark($mark, $key) {
        if($mark=='@oddNumber') {
            return $key;
        } else if($mark=='@crtrNumber') {
            return '债权转让'.$key.'号';
        }
    }

    public function user() {
        return $this->belongsTo('models\User', 'userId');
    }

    public function invest() {
        return $this->belongsTo('models\Invest', 'ref_id');
    }

    public function getTypeName() {
        return self::$types[$this->type];
    }

    public static function getRate($period, $periodType='month') {
        $key = $periodType.'-'.$period;
        return isset(self::$rates[$key])?self::$rates[$key]:0;
    }
}