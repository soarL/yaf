<?php
namespace models;

use tools\Redis;
use tools\Log;
use helpers\DateHelper;
use tools\Calculator;
use custody\API;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Crtr|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Crtr extends Model {
	const SN_PRE = 80000000;

	/**
	 * 生成合同状态（二进制判断）
	 */
	const CER_STA_PR = 1;

	/**
	 * 发送安存状态（二进制判断）
	 */
	const CER_STA_AC = 2;

	protected $table = 'work_creditass';

	public $timestamps = false;

	public function oddMoney() {
		return $this->belongsTo('models\OddMoney', 'oddmoneyId');
	}

	public function odd() {
		return $this->belongsTo('models\Odd', 'oddNumber');
	}

	public function invests() {
		return $this->hasMany('models\Invest', 'oddMoneyId', 'oddmoneyId');
	}

	public function user() {
		return $this->belongsTo('models\User', 'userId');
	}

	public static function getListBuilder() {
		return self::with('odd.interests', 'oddMoney.invests')
			->where('work_creditass.progress', '<>', 'fail');
			/*->whereDoesntHave('invests', function($q) {
				$begin = date('Y-m-d 00:00:00');
				$end = date('Y-m-d 23:59:59');
				$q->where('endtime', '>=', $begin)
					->where('endtime', '<=', $end);
			});*/
	}

	/**
	* 标的列表排序
	* @return Builder            查询类
	*/
	public static function sortList($builder,$sort = '',$order = 'desc') {
		if($sort){
			$builder->orderBy($sort, $order)->orderByRaw('field(work_creditass.progress, ?, ?)', ['start', 'run']);
		}else{
			$builder->orderByRaw('field(work_creditass.progress, ?, ?)', ['start', 'run']);	
		}
		return $builder;
	}

  public function getDetailButton($user) {
    $data = '';
    $word = '';
    $class = 'btn-blue';
    $openTime = strtotime($this->openTime);
    $disabled = 'disabled';
    $remain = $this->getRemain();
    $score = $user->estimateScore;
    if(!isset($user->estimate) || (time() - strtotime($user->estimate->addtime)) > 30*12*60*60){
      $score = -1;
      if(isset($user->estimate)){
        $user->estimate->status = 0;
        $user->estimate->save();
      }
    }
    $paypass = $user->paypass;
    $custody = $user->custody_id;
    $isseal = $user->isseal;

    if($this->progress=='start') {
      if($remain>0) {
        if(time()<$openTime) {
          $class = 'short-info-btn btn-blue open-time-down';
          $data = 'data-info="该标的还未开放，敬请期待！"';
        } else {
          if($this->isATBiding!=1) {
            $disabled = 'id="bid-btn"';
            $word = '立即抢购';
          }
        }
      }
    }

    if(($this->odd->riskLevel - $user->getEstimateLevel()) > 1 ){
      $risk = 0;
    }elseif(($this->odd->riskLevel - $user->getEstimateLevel()) == 1){
      $risk = -1;
    }else{
      $risk = 1;
    }
    
    //$second = $this->getOpenSecond();
    
    if($this->progress=='start'&&$remain>0){
    	return '<button '.$disabled.' class="'.$class.'" '.$data.' data-estimate="'.$score.'" data-paypass="'.($paypass?'1':'-1').'" data-custody="'.($custody?'1':'-1').'" data-isseal="'.$isseal.'"'. 'data-risk="'.$risk.'"' .'>'.$word.'</button>';
    }elseif($this->progress=='start'&&$remain==0){
         return '<button>复审中</button>';
    }else{
         return '<button>已出售</button>';
    }
  }

  /**
   * 获取搜索时间
   * @param  integer $time 时间ID
   * @return string        时间where语句
   */
    public static function getSearchTime($time) {
    $where = false;
    $GLOBALS['begin'] = 0;
    $GLOBALS['end'] = 9999999999;
    switch ($time) {
      case 1:
		$GLOBALS['end'] = 30*24*60*60;
        break;
      case 2:
	    $GLOBALS['begin'] = 30*24*60*60;
	    $GLOBALS['end'] = 90*24*60*60;
        break;
      case 3:
  	    $GLOBALS['begin'] = 90*24*60*60;
	    $GLOBALS['end'] = 180*24*60*60;
        break;
      case 4:
	    $GLOBALS['begin'] = 180*24*60*60;
	    $GLOBALS['end'] = 360*24*60*60;
        break;
      case 5:
  	    $GLOBALS['begin'] = 360*24*60*60;
        break;
      default:
        break;
    }
    //return $where;
  }
	public static function getCanBuyNum() {
		return self::where('progress', 'start')
			->whereDoesntHave('invests', function($q) {
				$begin = date('Y-m-d 00:00:00');
				$end = date('Y-m-d 23:59:59');
				$q->where('endtime', '>=', $begin)
					->where('endtime', '<=', $end);
			})->count();
	}

	public static function SN($id) {
		return self::SN_PRE + $id;
	}

	public static function serviceFee($money,$rehearTime) {
		$eday = DateHelper::getIntervalDay($rehearTime, date('Y-m-d'));
		$emonth = intval($eday/30);
		switch ($emonth) {
			case '0':
				$rate = '0.005';
				break;
			case '1':
				$rate = '0.005';
				break;
			case '2':
				$rate = '0.002';
			default:
				$rate = 0;
				break;
		}
		
		return round($money * $rate, 2);
	}

	public function getSN() {
		return self::SN($this->id);
	}

	public function getPer($remain=false) {
		if(!$this->money) {
	    	return 100;
	    }
	    if($remain===false) {
	    	$remain = $this->getRemain();
	    }
		$per = floor(($this->money-$remain)/$this->money*100);
		return $per;
	}

	/**
	 * 债权剩余天数
	 * @return integer
	 */
	public function getRemainDay() {
		$endTime = $this->odd->getEndTime();
		$a = strtotime(date('Y-m-d', strtotime($endTime)) . ' 00:00:00');
		$b = strtotime(date('Y-m-d') . ' 00:00:00');
		$day = intval(($a-$b)/(24*60*60));
		if($day<0) {
			$day = 0;
		}
		return $day;
	}
	
	public function isBuyable($userId) {
		$rdata = [];
		if($this->userId==$userId) {
			$rdata['status'] = 0;
			$rdata['info'] = '您不能购买自己出售的债权！';
			return $rdata;
		}
		if($this->odd->userId==$userId) {
			$rdata['status'] = 0;
			$rdata['info'] = '您不能购买自己的债权！';
			return $rdata;
		}
		if($this->oddMoney->isEndDay()) {
			$rdata['status'] = 0;
			$rdata['info'] = '该债权正在还款，暂时无法购买！';
			return $rdata;
		}
		if($this->progress!='start') {
			$rdata['status'] = 0;
			$rdata['info'] = '此债权不能购买！';
			return $rdata;
		}
		$rdata['status'] = 1;
		return $rdata;
	}

	/**
	 * 获取该笔投资的待收利息
	 * @return double
	 */
	public function getStayInterest() {
		$total = 0;
		foreach ($this->invests as $invest) {
			if($invest->status==0||$invest->status==2) {
				$total += $invest->interest;
			}
		}
		return $total;
	}

	/**
	 * 获取购买债权时购买用户需要额外支付给出售方的利息费
	 * @param  double $money [description]
	 * @return [type]        [description]
	 */
	public function getInterestFee($money) {
		$investedStayInterest = $this->oddMoney->getInvestedStayInterest();
		return round($investedStayInterest*($money/$this->money), 2);
	}

	/**
	 * 获取实际剩余可买金额
	 * @return double        可买金额
	 */
	public function getRemain() {
		if($this->progress=='start') {
			$key = Redis::getKey('crtrRemain', ['sn'=>$this->getSN()]);
			$remain = Redis::get($key);
			return round($remain/100, 2);
		} else {
			return 0;
		}
	}
	  /**
	   * 获取投标比例
	   * @return integer 进度(如100,表示100%)
	   */
	  public function getPercent($remain=false) {
	    if(!$this->money) {
	      return 100;
	    }
	    if($remain===false) {
	      $remain = $this->getRemain();
	    }
	    $per = ($this->money-$remain)/$this->money*100;
	    return floor($per);
	  }
	/**
	 * 购买债权
	 * @param  double 	$money 	购买的金额
	 * @return array         	消息数组
	 */
	public function buy($money) {
		$key = Redis::getKey('crtrRemain', ['sn'=>self::SN($this->id)]);
		$hanMoney = intval(bcmul($money, 100));
		$remain = Redis::decr($key, $hanMoney);
		$rdata = [];
		if($remain<0) {
			$rdata['status'] = 0;
			$rdata['msg'] = '购买金额超过可购买金额！';
			$rdata['remain'] = $remain/100;
			Redis::incr($key, $hanMoney);
		} else if($remain>0&&$remain<5000) {
			$rdata['status'] = 0;
			$rdata['msg'] = '不能使债权剩余金额小于50元！';
			$rdata['remain'] = $remain/100;
			Redis::incr($key, $hanMoney);
		} else {
			$rdata['status'] = 1;
			$rdata['msg'] = '购买成功！';
			$rdata['remain'] = $remain/100;
		}
		return $rdata;
	}

	/**
	 * 购买债权的反操作(用于错误处理)
	 * @param  double  	$money 	购买的金额
	 * @return double        	剩余金额
	 */
	public function disBuy($money) {
		$key = Redis::getKey('crtrRemain', ['sn'=>self::SN($this->id)]);
		$remain = Redis::incr($key, intval(bcmul($money, 100)));
		return $remain/100;
	}

	/**
	 * 完成该笔债权转让
	 * @param  $isFull 是否全部售出
	 * @return boolean 是否成功
	 */
	public function finish($isFull=false) {
		$odd = $this->odd;
		$curPeriod = $odd->oddBorrowPeriod - $this->period + 1;
		$curInvest = Invest::where('qishu', $curPeriod)->where('oddMoneyId', $this->oddmoneyId)->first();
		$list = OddMoney::with(['crtrTrade'=>function($q) {
			$q->select('tradeNo', 'fee');
		}])->where('cid', $this->id)->where('status', 0)->get();

		$status = false;

		$trades = [];

		DB::beginTransaction();
		try {
			foreach ($list as $oddMoney) {
				$data = [];
				$data['period'] = $this->period;
				$data['account'] = $oddMoney->money;
				$data['repayType'] = $odd->oddRepaymentStyle;
				$data['periodType'] = $odd->oddBorrowStyle;
				$data['yearRate'] = $odd->oddYearRate + $odd->oddReward;
				$data['timeStatus'] = 1;
				$data['time'] = $curInvest->addtime;
				$result = Calculator::getResult($data);
				$invests = [];
				foreach ($result['list'] as $key => $item) {
					$period = $curPeriod + $item['period'] -1;
					$invests[] = [
						'oddNumber' => $odd->oddNumber,
						'qishu' => $period,
						'benJin' => $item['capital'],
						'interest' => $item['interest'],
						'zongEr' => $item['capital'] + $item['interest'],
						'yuEr' => $item['remain'],
						'oddMoneyId' => $oddMoney->id,
						'addtime' => $item['begin'],
						'endtime' => $item['end'],
						'userId' => $oddMoney->userId,
						'status' => 0,
						'subsidy' => 0,
						'extra' => 0,
					];
				}
				Invest::insert($invests);

				$trades[] = $oddMoney->crtrTrade;
			}

			if($this->successMoney>0) {
				$data = [];
				$data['period'] = $this->period;
				$data['account'] = $this->money-$this->successMoney;
				$data['repayType'] = $odd->oddRepaymentStyle;
				$data['periodType'] = $odd->oddBorrowStyle;
				$data['yearRate'] = $odd->oddYearRate + $odd->oddReward;
				$result = Calculator::getResult($data);
				$remainPer = round($data['account']/$this->money, 4);
				foreach ($result['list'] as $key => $item) {
					$period = $curPeriod + $item['period'] -1;
					$invest = [
						'benJin' => $item['capital'],
						'interest' => $item['interest'],
						'zongEr' => $item['capital'] + $item['interest'],
						'yuEr' => $item['remain'],
						'extra' => DB::raw('truncate(extra*'.$remainPer.', 2)')
					];
					if($isFull) {
						$invest['operatetime'] = date('Y-m-d H:i:s');
						$invest['status'] = 2;
					} else {
						$invest['status'] = 0;
					}
					Invest::where('oddMoneyId', $this->oddmoneyId)->where('qishu', $period)->update($invest);
				}
			}

			$serviceMoney = UserCrtr::where('crtr_id', $this->id)->where('status', 1)->sum('fee');
			$this->serviceMoney = floatval($serviceMoney);
			$this->progress = 'run';
			$this->endtime = date('Y-m-d H:i:s');
			$this->save();

			OddMoney::where('cid', $this->id)->where('status', 0)->update(['status'=>1]);
			OddMoney::where('id', $this->oddmoneyId)->where('ckclaims', -1)->update(['status'=>($isFull?2:1), 'ckclaims'=>1]);

			$status = true;
		} catch(\Exception $e) {
			$status = false;
			Log::write('完成债权转让：'.$e->getMessage(), [], 'sqlError');
		}

		if($status) {
			DB::commit();

			$key = Redis::getKey('crtrRemain', ['sn'=>self::SN($this->id)]);
			Redis::delete($key);

			$result = API::finishCrtr($odd->oddNumber, $trades);
			if(!$result['status']) {
				Log::write('完成债权银行接口失败：'.$result['msg'], [], 'finish-crtr');
			}
			
		} else {
			DB::rollback();
		}

		return $status;
	}
}
