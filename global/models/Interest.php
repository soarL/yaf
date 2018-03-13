<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * Interest|model类
 * 借款人还款表
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Interest extends Model {
	/**
	 * 待收阶段
	 */
	const STATUS_STAY = 0;

	/**
	 * 正在还款
	 */
	const STATUS_ING = -1;
	
	/**
	 * 已还款，正常还款
	 */
	const STATUS_OVER = 1;
	
	/**
	 * 已还款，提前还款
	 */
	const STATUS_PREV = 2;

	/**
	 * 已还款（垫付），逾期还款
	 */
	const STATUS_DELAY = 3;

	protected $table = 'work_oddinterest';

	public $timestamps = false;

	/**
	 * 完成的还款
	 */
	public static $finished = [self::STATUS_OVER, self::STATUS_PREV, self::STATUS_DELAY];

	public function odd() {
		return $this->belongsTo('models\Odd', 'oddNumber');
	}

	public function user() {
		return $this->belongsTo('models\User', 'userId');
	}

	public function oddMoney() {
		return $this->belongsTo('models\OddMoney', 'oddMoneyId','id');
	}

	/**
	 * 获取网站待收总额
	 * @return double
	 */
	public static function getStayMoney() {
		return self::where('status', self::STATUS_STAY)->sum('zongEr');
	}

	/**
	 * 获取用户待还总额
	 * @param  string $userId 用户ID
	 * @return double
	 */
	public static function getStayMoneyByUser($userId) {
		return self::where('status', self::STATUS_STAY)->where('userId', $userId)->sum('zongEr');
	}

	/**
	 * 获取用户待还本金
	 * @param  string $userId 用户ID
	 * @return double
	 */
	public static function getBackPrincipalByUser($userId) {
		$total = self::where('userId', $userId)->where('status', self::STATUS_STAY)->sum('benJin');
		return $total;
	}

	/**
	 * 获取用户待还利息
	 * @param  string $userId 用户ID
	 * @return double
	 */
	public static function getBackInterestByUser($userId) {
		$total = self::where('userId', $userId)->where('status', self::STATUS_STAY)->sum('interest');
		//$total += self::where('userId', $userId)->where('status', self::STATUS_STAY)->sum('reward');
		return floatval(round($total,2));
	}

	/**
	 * 获取该笔还款以后的剩余本金
	 * @return double
	 */
	public function getRestPrincipal() {
		if($this->status==self::STATUS_PREV) {
			return 0;
		}
		$total = self::where('oddNumber', $this->oddNumber)->where('qishu', '>', $this->qishu)->sum('benJin');
		if($total) {
			return $total;
		} else {
			return 0;
		}
	}

	/*
	 *获取借款标，本金和利息
	 */
	protected function getOddInterest($oddNumber,$qishu){
        return self::where('oddNumber',$oddNumber)->where('qishu',$qishu)->select('benJin','interest','zongEr','realinterest','endtime','subsidy')->first();
	}
	
    /**
     *  还款 更新借款表 
     */
    public static function updateInterest($data,$oddNumber,$qishu){
    	return self::where('oddNumber',$oddNumber)->where('qishu',$qishu)->update($data);
    }

    public function getStatusName() {
    	if($this->status==self::STATUS_STAY) {
    		return '未还款';
    	} else if($this->status==self::STATUS_PREV) {
    		return '提前还款';
    	} else if($this->status==self::STATUS_DELAY) {
    		return '逾期还款';
    	} else if($this->status==self::STATUS_OVER) {
    		return '正常还款';
    	} else if($this->status==self::STATUS_ING) {
    		return '正在还款';
    	} 
    }
}