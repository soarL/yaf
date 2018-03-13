<?php
namespace models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;
use traits\BatchInsert;

/**
 * Lottery|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Lottery extends Model {
    use BatchInsert;
    
	const STATUS_NOGET = 0;
	const STATUS_NOUSE = 1;
	const STATUS_USED = 2;
    const STATUS_FROZEN = 3;

	const USERFUL_DAY = 360;
	
	public static $types = [
		'withdraw' => ['mr'=>2, 'key'=>'withdraw8899', 'name'=>'提现券'],
		'interest' => ['mr'=>0.02, 'key'=>'interest0321', 'name'=>'加息券'],
		'money' => ['mr'=>50, 'key'=>'money6678', 'name'=>'现金券'],
        'invest_money' => ['mr'=>10, 'key'=>'invest_money1238', 'name'=>'抵扣红包'],
	];

    public static $moneyList = [
        10 => 10000,
        20 => 20000,
        50 => 50000,
        100 => 100000,
        200 => 200000,
        500 => 500000,
    ];

	protected $table = 'system_lotteries';

	public function user() {
        return $this->belongsTo('models\User', 'userId');
    }

    /**
     * 获取状态名称
     * @param  string $type all:标识符和名称组成的数组 key:标识符 name:名称
     * @return mixed
     */
    public function getStatusName($type='name') {
    	$name = '';
        $key = '';
    	if($this->status==self::STATUS_NOGET) {
    		$name = '未被获取';
            $key = 'noget';
    	} else if($this->status==self::STATUS_NOUSE) {
            if(strtotime($this->endtime)<time()) {
                $name = '已过期';
                $key = 'overtime';
            } else {
                if($this->type=='money') {
                    $name = '冻结';
                } else {
                    $name = '未使用';
                }
                $key = 'nouse';
            }
    	} else if($this->status==self::STATUS_USED) {
    		$name = '已使用';
            $key = 'used';
    	} else if($this->status==self::STATUS_FROZEN) {
            $name = '使用中';
            $key = 'used';
        }

    	if($type=='name') {
            return $name;
        } else if($type=='key') {
            return $key;
        } else {
            return [$key, $name];
        }
    }

    public function getTypeName() {
    	$name = '';
    	if($this->type=='withdraw') {
    		$name = '提现券';
    	} else if($this->type=='interest') {
    		$name = '加息券';
    	} else if($this->type=='money') {
    		$name = '现金券';
    	} else if($this->type=='invest_money') {
            $name = '抵扣红包';
        }
    	return $name;
    }

    public function getName() {
        $name = '';
        if($this->type=='withdraw') {
            $name = '提现券';
        } else if($this->type=='interest') {
            $name = ($this->money_rate*100).'%加息券';
        } else if($this->type=='money') {
            $name = $this->money_rate.'元现金券';
        } else if($this->type=='invest_money') {
            $name = $this->money_rate.'元抵扣红包';
        }
        return $name;
    }

    public function getValue() {
        if($this->type=='withdraw') {
            return $this->money_rate . '元';
        } else if($this->type=='interest') {
            return $this->money_rate*100 . '%';
        } else if($this->type=='money') {
            return $this->money_rate.'元';
        } else if($this->type=='invest_money') {
            return $this->money_rate.'元';
        }
        return $this->money_rate;
    }

    public function investCanUse($oddMoney) {
        if($oddMoney->lotteryId) {
            return ['status'=>0, 'msg'=>'该笔投资已经使用过加息券！'];
        }
        if($oddMoney->isEndDay()) {
            return ['status'=>0, 'msg'=>'结息日不可使用加息券！'];
        }
        if($this->type!='interest') {
            return ['status'=>0, 'msg'=>'该券不是加息券！'];
        }
        if($this->userId!=$oddMoney->userId) {
            return ['status'=>0, 'msg'=>'此加息券不属于您！'];
        }
        if($this->status!=self::STATUS_NOUSE) {
            return ['status'=>0, 'msg'=>'该券不可用！'];
        }
        if(strtotime($this->endtime)<time()) {
            return ['status'=>0, 'msg'=>'此加息券已过期！'];
        }
        if($this->money_lower!=null&&$this->money_lower>$oddMoney->money) {
            return ['status'=>0, 'msg'=>'未达限制金额！'];
        }
        if($this->money_uper!=null&&$this->money_uper<$oddMoney->money) {
            return ['status'=>0, 'msg'=>'超出限制金额！'];
        }
        if($this->period_lower!=null&&$this->period_lower>$oddMoney->odd->oddBorrowPeriod) {
            return ['status'=>0, 'msg'=>'投资类型不符！'];
        }
        if($this->period_uper!=null&&$this->period_uper<$oddMoney->odd->oddBorrowPeriod) {
            return ['status'=>0, 'msg'=>'投资类型不符！'];
        }
        return ['status'=>1, 'msg'=>'可以使用！'];
    }

    public function getMoneyType() {
        if($this->type=='withdraw') {
            return '--';
        }
        if($this->type=='moneys') {
            return self::$moneyList[$this->money_rate] . '元';
        }
        $name = '';
        if($this->money_lower==null&&$this->money_uper==null) {
            $name = '无限制';
        } else {
            if($this->money_lower==null) {
                $name = $this->money_uper.'元以下的投资';
            } else if($this->money_uper==null) {
                $name = $this->money_lower.'元以上的投资';
            } else {
                if($this->money_uper==$this->money_lower) {
                    $name = '仅限'.$this->money_lower.'元的投资';
                } else {
                    $name = $this->money_lower.'-'.$this->money_uper.'元的投资';
                }
            }
        }
        return $name;
    }

    public function getPeriodType() {
        if(!in_array($this->type, ['interest', 'invest_money', 'money'])) {
            return '--';
        }
        $name = '';
        if($this->period_lower==null&&$this->period_uper==null) {
            $name = '无限制';
        } else {
            if($this->period_lower==null) {
                $name = $this->period_uper.'个月以下的标';
            } else if($this->period_uper==null) {
                $name = $this->period_lower.'个月以上的标';
            } else {
                if($this->period_uper==$this->period_lower) {
                    $name = '仅限'.$this->period_lower.'月标';
                } else {
                    $name = $this->period_lower.'-'.$this->period_uper.'个月的标';
                }
            }
        }
        return $name;
    }

    /**
     * 生成奖券
     * @param  array   $params  参数
     * @param  boolean $insert  是否插入
     * @return mixed
     */
    public static function generate($params, $insert=true) {
        $type = isset($params['type'])?$params['type']:'';
        if($type==''||!isset(self::$types[$type])) {
            return false;
        }
        $typeRow = self::$types[$type];

        $money_lower = isset($params['money_lower'])?$params['money_lower']:null;
        $money_uper = isset($params['money_uper'])?$params['money_uper']:null;
        $period_lower = isset($params['period_lower'])?$params['period_lower']:null;
        $period_uper = isset($params['period_uper'])?$params['period_uper']:null;
        $endtime = isset($params['endtime'])?$params['endtime'].' 23:59:59':null;
        $useful_day = isset($params['useful_day'])?$params['useful_day']:self::USERFUL_DAY;
        $money_rate = isset($params['money_rate'])?$params['money_rate']:$typeRow['mr'];
        $userId = isset($params['userId'])?$params['userId']:'';
        $remark = isset($params['remark'])?$params['remark']:'';

        if($userId=='') {
            return false;
        }

        if($endtime==null) {
            $endtime = date('Y-m-d H:i:s', time()+($useful_day)*24*60*60);
        }

        $sn = strtoupper(substr(md5($userId.microtime().$typeRow['key'].rand(1000, 9999)), 8, 16));
        $common = [];
        $common['type'] = $type;
        $common['useful_day'] = $useful_day;
        $common['money_rate'] = $money_rate;
        $common['money_lower'] = $money_lower;
        $common['money_uper'] = $money_uper;
        $common['period_lower'] = $period_lower;
        $common['period_uper'] = $period_uper;
        $common['remark'] = $remark;
        $common['created_at'] = date('Y-m-d H:i:s');
        $common['updated_at'] = date('Y-m-d H:i:s');
        $common['sn'] = $sn;
        $common['endtime'] = $endtime;
        $common['get_at'] = date('Y-m-d H:i:s');
        $common['status'] = Lottery::STATUS_NOUSE;
        $common['userId'] = $userId;
        if($insert) {
            return self::insert($common);
        } else {
            return $common;
        }
    }

    /**
     * 批量生成奖券
     * @param  array   $list    奖券列表参数
     * @return boolean          是否生成成功
     */
    public static function generateBatch($list) {
        $rows = [];
        foreach ($list as $item) {
            $row = self::generate($item, false);
            if($row) {
                $rows[] = $row;
            }
        }
        return self::insert($rows);
    }

    /**
     * 将未分配奖券分配给用户
     * @param  models\User  $user   用户
     * @param  string       $remark 备注
     * @return boolean
     */
    public function assign($user, $remark='') {
        $this->userId = $user->userId;
        if($this->endtime==null) {
            $this->endtime = date('Y-m-d H:i:s', time()+($this->useful_day)*24*60*60);
        }
        if($remark!='') {
            $this->remark = $remark;
        }
        $this->get_at = date('Y-m-d H:i:s');
        $this->status = Lottery::STATUS_NOUSE;
        return $this->save();
    }

    /**
     * 红包券是否能解冻【失效】
     * @return array [status, msg]
     */
    public function isUnfreeze() {
        return [0, '奖券不可用！'];
        // 失效
        if($this->status!=self::STATUS_NOUSE) {
            return [0, '奖券不可用！'];
        }
        $allMoney = UserTender::where('userId', $this->userId)
            ->where('created_at', '>=', $this->get_at)
            ->where('created_at', '<=', $this->endtime)
            ->where('money_last', '>', 0)
            ->sum('money_last');
        if($allMoney<self::$moneyList[$this->money_rate]) {
            return [0, '投资金额不足！'];
        } else {
            return [1, '奖券可用！'];
        }
    }

    /**
     * 解冻红包券
     * @return boolean 是否解冻成功
     */
    public function unfreeze() {
        DB::beginTransaction();
        $tenders = UserTender::where('userId', $this->userId)
            ->where('created_at', '>=', $this->get_at)
            ->where('created_at', '<=', $this->endtime)
            ->where('money_last', '>', 0)
            ->orderBy('id', 'asc')
            ->lock()
            ->get();

        $needMoney = self::$moneyList[$this->money_rate];
        $money = 0;
        $list = [];
        foreach ($tenders as $tender) {
            $money += $tender->money_last;
            $list[] = $tender->id;
            if($money>=$needMoney) {
                break;
            }
        }
        
        // 所有投资金额总和不满足解冻条件
        if($money<$needMoney) {
            DB::rollback();
            return false;
        }

        $lastID = array_pop($list);
        $status1 = UserTender::whereIn('id', $list)->update(['money_last'=>0]);
        $status2 = UserTender::where('id', $lastID)->update(['money_last'=>($money-$needMoney)]);

        $this->status = self::STATUS_USED;
        $this->used_at = date('Y-m-d H:i:s');
        $status3 = $this->save();

        if($status1&&$status2&&$status3) {
            DB::commit();
            return true;
        } else {
            DB::rollback();
            return false;
        }
    }

    public function checkMoney($money) {
        if(!$this->money_uper && !$this->money_lower) {
            return true;
        }
        if($this->money_uper && !$this->money_lower) {
            return $this->money_uper>=$money?true:false;
        }
        if(!$this->money_uper && $this->money_lower) {
            return $this->money_lower<=$money?true:false;
        }
        return ($this->money_uper>=$money && $this->money_lower<=$money)?true:false;
    }

    public function checkPeriod($period) {
        if(!$this->period_uper && !$this->period_lower) {
            return true;
        }
        if($this->period_uper && !$this->period_lower) {
            return $this->period_uper>=$period?true:false;
        }
        if(!$this->period_uper && $this->period_lower) {
            return $this->period_lower<=$period?true:false;
        }
        return ($this->period_uper>=$period && $this->period_lower<=$period)?true:false;
    }
}