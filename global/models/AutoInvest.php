<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * AutoInvest|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class AutoInvest extends Model {
	
	protected $table = 'work_oddautomatic';

	public $timestamps = false;

    public static $oddTypes = ['house-mor'=>'房抵贷', 'auto-ins'=>'车险贷'];
	public static $types = [
        11 => ['name'=>'1月标(8.6%)', 'rate'=>0.086, 'period'=>1, 'periodType'=>'month', 'type'=>'house-mor', 'status'=>1],
        12 => ['name'=>'2月标(8.6%)', 'rate'=>0.086, 'period'=>2, 'periodType'=>'month', 'type'=>'house-mor', 'status'=>1],
        13 => ['name'=>'3月标(8.6%)', 'rate'=>0.086, 'period'=>3, 'periodType'=>'month', 'type'=>'house-mor', 'status'=>1],
        14 => ['name'=>'6月标(9.8%)', 'rate'=>0.098, 'period'=>6, 'periodType'=>'month', 'type'=>'house-mor', 'status'=>1],
        15 => ['name'=>'12月标(11%)', 'rate'=>0.11, 'period'=>12, 'periodType'=>'month', 'type'=>'house-mor', 'status'=>1],

        27 => ['name'=>'5月标(9.8%)', 'rate'=>0.098, 'period'=>2, 'periodType'=>'month', 'type'=>'auto-ins', 'status'=>1],
        24 => ['name'=>'6月标(9.8%)', 'rate'=>0.098, 'period'=>6, 'periodType'=>'month', 'type'=>'auto-ins', 'status'=>1],
        26 => ['name'=>'11月标(11%)', 'rate'=>0.11, 'period'=>12, 'periodType'=>'month', 'type'=>'auto-ins', 'status'=>1],
        25 => ['name'=>'12月标(11%)', 'rate'=>0.11, 'period'=>12, 'periodType'=>'month', 'type'=>'auto-ins', 'status'=>1],
	];

    public static $status = [
        0 => ['name'=> '未开启','color'=> '#ababab'],
        1 => ['name'=> '排队中','color'=> '#009dec'],
        2 => ['name'=> '投标中','color'=> '#ff8c00'],
    ];

	public function queue() {
		return $this->hasOne('models\Queue', 'userId', 'userId');
	}

	public function user() {
		return $this->belongsTo('models\User', 'userId');
	}

    public function lottery() {
        return $this->belongsTo('models\Lottery', 'lottery_id');
    }

    public static function getOTTypes() {
        $list = [];
        foreach (self::$types as $key => $type) {
            if($type['status']==1) {
                $type['id'] = $key;
                $list[$type['type']][] = $type;
            }
        }
        return $list;
    }

    public function getTypeIDList() {
        if($this->types=='') {
            return [];
        }
        return explode('#', trim($this->types, '#'));
    }

	public function getTypes() {
		$ids = $this->getTypeIDList();

		$types = [];
		foreach ($ids as $id) {
            if(isset(self::$types[$id])) {
                $types[] = self::$types[$id];
            }
		}
		return $types;
	}

    public static function getTypesByPeriods($periods) {
        if(!$periods) {
            return [];
        }
        $types = [];
        foreach (self::$types as $key => $type) {
            if(in_array($type['period'].$type['periodType'], $periods)) {
                $types[] = $key;
            }
        }
        return $types;
    }

    public function investable($user=null) {
        $moneyable = true;
        if($user==null) {
            $user = $this->user;
        }
        if($this->investMoneyUper!=null && $this->investMoneyUper<$this->investMoneyLower) {
            $moneyable = false;
        }
        if(($user->fundMoney - $this->investEgisMoney)<$this->investMoneyLower) {
            $moneyable = false;
        }
        if(($user->fundMoney - $this->investEgisMoney)<50) {
            $moneyable = false;
        }
        if($this->autostatus==1 && $this->staystatus==0 && 
            $moneyable && $this->types != '') {
            return true;
        } else {
            return false;
        }
    }
}