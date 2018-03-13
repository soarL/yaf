<?php
namespace models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;
use traits\BatchInsert;

/**
 * Invest|model类
 * 投资人回款表
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Invest extends Model {
	use BatchInsert;

	/**
	 * 正在还款
	 */
	const STATUS_REPAYING = -1;

	/**
	 * 待收阶段
	 */
	const STATUS_STAY = 0;
	
	/**
	 * 已还款，正常还款
	 */
	const STATUS_OVER = 1;

	/**
	 * 债权转让
	 */
	const STATUS_OUT = 2;
	
	/**
	 * 已还款，提前还款
	 */
	const STATUS_PREV = 3;

	/**
	 * 已还款（垫付），逾期还款
	 */
	const STATUS_DELAY = 4;



	/**
	 * 完成的回款
	 */
	public static $finished = [self::STATUS_OVER, self::STATUS_PREV, self::STATUS_DELAY];

	protected $table = 'work_oddinterest_invest';

	public $timestamps = false;

	public function oddMoney() {
		return $this->belongsTo('models\OddMoney', 'oddMoneyId');
	}

	public function odd() {
		return $this->belongsTo('models\Odd', 'oddNumber');
	}

	public function user() {
		return $this->belongsTo('models\User', 'userId');
	}

	/**
	 * 获取指定回款记录Builder
	 * @param  string $userId 用户ID
	 * @param  string $beginTime 开始时间
	 * @param  string $endTime   结束时间
	 * @return array          用户回款记录
	 */
	public static function getRepaymentsBuilder($userId, $beginTime='', $endTime='', $type='all', $oddNumber='', $oddMoneyId='') {
		$builder = self::with('oddMoney.odd')
			->where('userId', $userId)
			->where('status', '<>', self::STATUS_OUT)
			->where('zongEr', '>', 0);

		if($type=='all') {
			$beginTime = $beginTime==''?'1970-01-01 08:00:00':$beginTime;
			$endTime = $endTime==''?date('9999-12-31 23:59:59'):$endTime;
			$builder->whereRaw('((status=0 and endtime>=? and endtime<=?) or (status<>0 and operatetime>=? and operatetime<=?))', [$beginTime, $endTime, $beginTime, $endTime]);
		} else {
			if($beginTime!='') {
				$builder->where('endtime', '>=', $beginTime);
			}
			if($endTime!='') {
				$builder->where('endtime', '<=', $endTime);
			}
		}
		if($type=='over') {
			$builder->where('status', self::STATUS_OVER);
		}
		if($type=='stay') {
			$builder->where('status', self::STATUS_STAY);
		}
		if($type=='prev') {
			$builder->where('status', self::STATUS_PREV);
		}
		if($type=='delay') {
			$builder->where('status', self::STATUS_DELAY);
		}
		if($oddNumber!='') {
			$builder->where('oddNumber', $oddNumber);
		}
		if($oddMoneyId!='') {
			$builder->where('oddMoneyId', $oddMoneyId);
		}

		return $builder;
	}

	public function getStatus(){
		switch ($this->status) {
			case (self::STATUS_REPAYING):
				return 'stay';
				break;
			case (self::STATUS_STAY):
				return 'stay';
				break;
			case (self::STATUS_OVER):
				return 'over';
				break;
			case (self::STATUS_OUT):
				return 'over';
				break;
			case (self::STATUS_PREV):
				return 'prev';
				break;
			case (self::STATUS_DELAY):
				return 'delay';
				break;
			default:
				break;
		}
	}

	/**
	 * 获取真实应回款本金
	 * @return double 真实应回款本金
	 */
	public function getPrincipal() {
		if($this->status==self::STATUS_PREV) {
			return $this->zongEr - $this->realinterest;
		} else {
			return $this->benJin;
		}
	}

	/**
	 * 获取真实应回款利息
	 * @return double 真实应回款利息
	 */
	public function getInterest() {
		if($this->status==self::STATUS_PREV) {
			return $this->realinterest;
		} else {
			return $this->interest;
		}
	}

	/**
	 * 获取真实应回款总额
	 * @return double 真实应回款总额
	 */
	public function getAmount() {
		return $this->zongEr;
	}

	/**
	 * 获取用户总共获得的利息
	 * @param  string $userId 用户ID
	 * @return double         获得的利息
	 */
	public static function getTotalInterestByUser($userId) {
		$oldInterest = OldInvest::getTotalInterestByUser($userId);
		$newInterest = self::whereIn('status', self::$finished)
			->whereIn('oddMoneyId', function($q) use($userId) {
				$q->select('id')
					->from(with(new OddMoney)->getTable())
					->where('userId', $userId);
			})->sum('realinterest');

		return $oldInterest + $newInterest;
	}

	/**
	 * 获取用户总共被扣除的利息服务费
	 * @param  string $userId 用户ID
	 * @return double         扣除的利息服务费
	 */
	public static function getTotalInterestFee($userId) {
		$oldServiceMoney = OldLog::where('user_id', $userId)->where('type', 'tender_recover_fee_service')->sum('money');
		$serviceMoney = self::where('status', self::STATUS_OVER)
			->whereIn('oddMoneyId', function($q) use($userId) {
				$q->select('id')
					->from(with(new OddMoney)->getTable())
					->where('userId', $userId);
			})->sum('serviceMoney');

		return $serviceMoney+$oldServiceMoney;
	}

	/**
	 * 获取用户待收本金
	 * @param  string $userId 用户userId
	 * @return double         用户待收本金
	 */
	public static function getStayPrincipalByUser($userId) {
		$money = self::where('status', self::STATUS_STAY)
			->whereIn('oddMoneyId', function($q) use($userId) {
				$q->select('id')
					->from(with(new OddMoney)->getTable())
					->where('userId', $userId);
			})
			->whereIn('oddNumber', function($q) use($userId) {
				$q->select('oddNumber')
					->from(with(new Odd)->getTable())
					->where('oddType', '<>', 'special');
			})
			->sum('benJin');
		return $money;
	}

	/**
	 * 获取用户待收利息
	 * @param  string $userId 用户userId
	 * @return double         用户待收利息
	 */
	public static function getStayInterestByUser($userId) {
		$money = self::where('status', self::STATUS_STAY)
			->whereIn('oddMoneyId', function($q) use($userId) {
				$q->select('id')
					->from(with(new OddMoney)->getTable())
					->where('userId', $userId);
			})
			->sum('interest');
		return $money;
	}

	/**
	 * 获取用户总共被扣除的利息服务费、获得的利息、待收本金、待收利息
	 * @param  string $userId 用户ID
	 * @return double         扣除的利息服务费
	 */
	public static function getUserTenderInfo($userId) {
		$oldInterest = OldInvest::getTotalInterestByUser($userId);
		$oldServiceMoney = OldLog::where('user_id', $userId)->where('type', 'tender_recover_fee_service')->sum('money');
		$resultOne = self::where('status', '<>', self::STATUS_STAY)
			->whereIn('oddMoneyId', function($q) use($userId) {
				$q->select('id')
					->from(with(new OddMoney)->getTable())
					->where('userId', $userId);
			})
			->first([
				DB::raw('sum(serviceMoney) as hasSC'), 
				DB::raw('sum(realinterest) as hasInterest'),
			]);
		$resultTwo = self::where('status', self::STATUS_STAY)
			->whereIn('oddMoneyId', function($q) use($userId) {
				$q->select('id')
					->from(with(new OddMoney)->getTable())
					->where('userId', $userId);
			})
			->whereIn('oddNumber', function($q) use($userId) {
				$q->select('oddNumber')
					->from(with(new Odd)->getTable())
					->where('oddType', '<>', 'special');
			})
			->first([
				DB::raw('sum(benJin) as stayPrincipal'), 
				DB::raw('sum(interest) as stayInterest'),
			]);

		return [
			'hasSC'=>$resultOne->hasSC+$oldServiceMoney,
			'hasInterest'=>$resultOne->hasInterest+$oldInterest,
			'stayPrincipal'=>$resultTwo->stayPrincipal,
			'stayInterest'=>$resultTwo->stayInterest,
			'stayAll'=>$resultTwo->stayPrincipal+$resultTwo->stayInterest,
		];
	}

	/**
	 * 获取用户提前还款的投资
	 * @param  string $userId  用户userId
	 * @return Database        查询结果
	 */
	public static function getPrepayBuilder($userId) {
		return self::with('odd', 'oddMoney')
			->where('userId', $userId)
			->where('status', self::STATUS_PREV);
	}

	/**
	 * 获取用户逾期的投资
	 * @param  string $userId  用户userId
	 * @return Database        查询结果
	 */
	public static function getOverdueBuilder($userId) {
		return self::with('odd', 'oddMoney')
			->where('userId', $userId)
			->whereRaw('((status=? and endtime<=?) or status=?)', [self::STATUS_STAY, date('Y-m-d').' 00:00:00', self::STATUS_DELAY]);
	}

	/**
	 * 获取结息天数
	 * @return integer        结息天数
	 */
	public function getTenderDay() {
		$time = strtotime($this->operatetime) - strtotime($this->addtime);
		return intval($time/(24*60*60));
	}

	/**
	 * 获取债权类型（购买，转让）
	 * @return mixed
	 */
	public function getCTType() {
		if($this->oddMoney->bid) {
			return 'buy';
		} else {
			// 原先的机制
			if($this->oddMoney->userId!=$this->userId) {
				return 'sell';
			} else {
				if($this->oddMoney->status==2) {
					return 'sell';
				}
				// 原先的机制
				if($this->oddMoney->ckclaims==1) {
					return 'buy';
				}
			}
		}
		return 'normal';
	}

	/**
	 * 获取债权类型2（提前，逾期）
	 * @return mixed
	 */
	public function getPDType() {
		$repayTime = strtotime(date('Y-m-d 23:59:59', strtotime($this->endtime)));
		if($this->status==self::STATUS_DELAY||($this->status==self::STATUS_STAY&&time()>$repayTime)) {
			return 'delay';
		} else if($this->status==self::STATUS_PREV) {
			return 'prev';
		} else {
			return 'normal';
		}
	}

	/**
	 * 获取债权类型标签
	 * @return mixed
	 */
	public function getTypeLabels() {
		$ctType = $this->getCTType();
		$pdType = $this->getPDType();

		$labels = '';
		if($ctType=='sell') {
			$labels .= '<span class="status-label status-default layer-tip" data-align-x="center" data-align-y="top" data-content="此回款的债权已出售">售出债权</span>';
		} else if($ctType=='buy') {
			$labels .= '<span class="status-label status-primary layer-tip" data-align-x="center" data-align-y="top" data-content="此回款的债权为购买所得">购买债权</span>';
		}
		if($pdType=='prev') {
			$labels .= '<span class="status-label status-warning layer-tip" data-content="提前还款">提前</span>';
		} else if($pdType=='delay') {
			$labels .= '<span class="status-label status-fail layer-tip" data-align-x="center" data-align-y="top" data-content="逾期还款">逾期</span>';
		}
		if($labels=='') {
			$labels .= '<span class="status-label status-success layer-tip" data-align-x="center" data-align-y="top" data-content="正常还款">正常</span>';
		}
		return $labels;
	}

	/**
	 * 获取回款逾期天数
	 * @return Database        查询结果
	 */
	public function getOverdueDay() {
		$day = 24*60*60;
		return (strtotime(date('Y-m-d 00:00:00')) - strtotime(date('Y-m-d 00:00:00', strtotime($this->endtime))))/$day;
	}

	/**
	 * 获取用户债权转让的信息
	 * @param string $userId 用户ID
	 * @return  array  
	 */
	public static function getTransferInfo($userId) {
		$result = self::where('status', self::STATUS_OUT)
			->where('userId', $userId)
			->first([DB::raw('sum(benJin) as principal'), DB::raw('sum(realinterest) as interest')]);
		
		// 利息
		$interest = self::whereHas('oddMoney', function($q) use ($userId){
			$q->where('status', 2)->where('userId', $userId);
		})
		->where('userId', $userId)
		->whereIn('status', self::$finished)
		->sum('realinterest');

		return ['principal'=>floatval($result->principal), 'interest'=>floatval($result->interest+$interest)];
	}

	/**
	 * 获取用户购买债权的信息
	 * @param string $userId 用户ID
	 * @return  array  
	 */
	public static function getCrtrInfo($userId) {
		$result = self::whereHas('oddMoney', function($q) use ($userId) {
			$q->where('type', 'credit')->whereIn('status', [1, 2])->where('userId', $userId);
		})
		->where('userId', $userId)
		->where('status', '<>', self::STATUS_STAY)
		->first([DB::raw('sum(benJin) as principal'), DB::raw('sum(realinterest) as interest')]);

		return ['principal'=>floatval($result->principal), 'interest'=>floatval($result->interest)];
	}

    /**
     * 获取投资者收益列表
     * @param  [type] $oddNumber [description]
     * @param  [type] $qishu     [description]
     * @return [type]            [description]
     */
    public static function getOddInvest($oddNumber, $qishu){
    	return self::with('user')->whereHas('oddMoney', function($q) use ($oddNumber){
			$q->where('status', 1)->where('oddNumber', $oddNumber);
		})->where('qishu',$qishu)->get();
    }

	/**
	 * 获取当期日利息，以四舍五入方式保留四位小数，误差(-0.00005, +0.00005)
	 * 30天误差(-0.0015, +0.0015)
	 * @return [type] [description]
	 */
	public function getDayInterest() {
		$day = DateHelper::getIntervalDay($this->addtime, $this->endtime);
		return round($this->interest/$day, 4);
	}

	/**
	 * 获取存管订单号
	 * @param  string $type 类型
	 * @return string       订单号
	 */
	public function getOrderID($type='pay') {
		$time = date('YmdHis');
		$orderID = str_repeat('0', 10-strlen($this->oddMoneyId)).$this->oddMoneyId;
		$orderID .= str_repeat('0', 3-strlen($this->qishu)).$this->qishu;
		if($type=='pay') {
			$orderID = 'P'.$time.$orderID;
		} else if($type=='bail') {
			$orderID = 'B'.$time.$orderID;
		} else if($type=='end') {
			$orderID = str_repeat('0', 10-strlen($this->oddMoneyId)).$this->oddMoneyId;
			$orderID = 'E'.$time.$orderID;
		} else if($type=='extra') {
			$orderID = 'G'.$time.$orderID;
		} else if($type=='reward') {
			$orderID = 'R'.$time.$orderID;
		}
		return $orderID;
	}
}