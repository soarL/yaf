<?php
namespace models;

use Yaf\Registry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;
use traits\protocols\LoanProtocol;
use traits\protocols\LeaseProtocol;
use traits\protocols\CrtrProtocol;
use traits\protocols\CrdProtocol;
use tools\Redis;
use models\Crtr;
use helpers\DateHelper;
use helpers\StringHelper;

/**
 * OddMoney|model类
 *
 * status/ckclaims说明
 * status为1 且 ckclaims为1 旧的债权转让
 * status为2 且 ckclaims为1 新的债权转让
 * status为1 且 ckclaims为-1 新的债权转让中
 * status为-1表示失效
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class OddMoney extends Model {
	use LoanProtocol,LeaseProtocol,CrtrProtocol,CrdProtocol;

	protected $table = 'work_oddmoney';

	public $timestamps = false;
	
	public static $userTypes = ['未处理', '新', '旧'];

	public function invests() {
		return $this->hasMany('models\Invest', 'oddMoneyId');
	}

	public function user() {
		return $this->belongsTo('models\User', 'userId');
	}

	public function oddinfo() {
		return $this->belongsTo('models\OddInfo', 'oddNumber');
	}

	public function odd() {
		return $this->belongsTo('models\Odd', 'oddNumber');
	}

	public function lottery() {
		return $this->belongsTo('models\Lottery', 'lotteryId');
	}

	/**
	 * 该投资的债权转让（仅在该投资正在转让或者已经转让时有此值，其他情况下为null）
	 */
	public function crtr() {
		return $this->hasOne('models\Crtr', 'oddmoneyId')->where('progress', '<>', 'fail');
	}

	/**
	 * 上级投资的债权转让
	 */
	public function pcrtr() {
		return $this->belongsTo('models\Crtr', 'cid');
	}

	/**
	 * 获取安存合同
	 */
	public function ancun(){
		return $this->hasOne('models\AncunData', 'tradeNo', 'tradeNo');//->where('display','hide')
	}

	/**
	 * 上级投资
	 */
	public function parent() {
		return $this->belongsTo('models\OddMoney', 'bid');
	}

	public function trade() {
		return $this->hasOne('models\UserBid', 'tradeNo', 'tradeNo');
	}

	public function crtrTrade() {
		return $this->hasOne('models\UserCrtr', 'tradeNo', 'tradeNo');
	}

	public function protocol() {
		return $this->hasOne('models\Protocol', 'oddMoneyId');
	}
	
	public function interest() {
		return $this->hasOne('models\Interest', 'oddNumber', 'oddNumber');
	}

	/**
	 * 查询用户投资金额
	 * @param  string $userId   用户userId
	 * @param  string $begin    开始时间
	 * @param  string $end   	结束时间
	 * @param  string $type   	类型
	 * @return double           用户投资金额
	 */
	public static function getTenderMoneyByUser($userId, $begin='', $end='', $type='normal') {
		$builder = self::where('userId', $userId)->where('status', '1');
		if($type=='redpack') {
			$time = date('Y-m-d H:i:s', time()- 30*24*60*60);
			$builder->whereRaw('(type=? or (type=? and time<?))', ['invest', 'credit', $time]);
		} else {
			$builder->where('type', 'invest');
		}
		if($begin!='') {
			$builder->where('time', '>=', $begin);
		}
		if($end!='') {
			$builder->where('time', '<=', $end);
		}
		$builder->whereHas('odd', function($q){
          $q->where('oddBorrowStyle', 'month');
        });
		return $builder->sum('money');
	}

	/**
	 * 查询用户新旧系统投资金额
	 * @param  string $userId   用户userId
	 * @return double           用户投资金额
	 */
	public static function getAllTenderMoneyByUser($userId) {
		return self::getTenderMoneyByUser($userId) + OldData::getTenderMoneyByUser($userId);
	}

	/**
	 * 查询用户是否投资过
	 * @param  string $userId     用户ID
	 * @return boolean            是否投标
	 */
	public static function isUserInvest($userId) {
		$count = self::where('type', 'invest')->where('userId', $userId)->where('status', '1')->count();
		if($count>0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 获取用户可转让的债权
	 * @param  string $userId  用户userId
	 * @return Database        查询结果
	 */
	public static function getCanTransferBuilder($userId) {
		return self::with('odd.interests', 'invests')->whereRaw('(type=? or type=?)', ['invest', 'credit'])
			->where('userId', $userId)
			->where('status', '1')
			->where('ckclaims', '<>', -1)
			->whereHas('odd', function($q){
				$q->where('progress', 'run')
					->where('oddBorrowStyle', '<>', 'day');
			});
	}

	/**
	 * 获取转让中的债权
	 * @param  string $userId  用户userId
	 * @return Database        查询结果
	 */
	public static function getIngBuilder($userId) {
		return self::with('odd.interests', 'crtr', 'invests')
			->where('userId', $userId)
			->where('ckclaims', -1);
	}

	/**
	 * 获取用户转让的债权
	 * @param  string $userId  用户userId
	 * @return Database        查询结果
	 */
	public static function getSellBuilder($userId) {
		return self::with('odd.interests', 'invests', 'crtr')
			->where('userId', $userId)
			->where('status', '2')
			->where('ckclaims', 1);
	}

	/**
	 * 获取用户购买的债权
	 * @param  string $userId  用户userId
	 * @return Database        查询结果
	 */
	public static function getBuyBuilder($userId) {
		return self::with('odd.interests', 'invests', 'parent.crtr')->where('type', 'credit')
			->where('userId', $userId)
			->where('status', '1')
			->where('ckclaims', 0);
	}

	/**
	 * 获取用户回收中的债权
	 * @param  string $userId  用户userId
	 * @return Database        查询结果
	 */
	public static function getRepayBuilder($userId) {
		return self::with('odd.interests', 'invests', 'parent.crtr')->where('type', 'credit')
			->where('userId', $userId)
			->where('status', '1')
			->where('ckclaims', 0)
			->whereHas('odd', function($q){
				$q->where('progress', 'run');
			});
	}

	/**
	 * 获取用户已回收的债权
	 * @param  string $userId  用户userId
	 * @return Database        查询结果
	 */
	public static function getOverBuilder($userId) {
		return self::with('odd.interests', 'invests', 'parent.crtr')->where('type', 'credit')
			->where('userId', $userId)
			->where('status', '1')
			->where('ckclaims', 0)
			->whereHas('odd', function($q){
				$q->whereIn('progress', Odd::$progressTypes['finished']);
			});
	}

	/**
	 * 查询所投资债权是否能够转让
	 * @return boolean            是否能转让
	 */
	public function canTransfer() {
		if($this->status==1&&$this->ckclaims!=-1) {
			$nextRepay = $this->getNextRepay();
			
			if(!$nextRepay) {
				return false;
			}

			// 还款前3天内(包含还款日)不允许转让
			$repayTime = strtotime(_date('Y-m-d 23:59:59', $nextRepay->endtime)) + 1 - 3*24*60*60;

			if(time()>$repayTime) {
				return false;
			}

			return true;
		} else {
			return false;
		}
	}

	/**
	 * 查询此债权本期回款记录
	 * @return mixed
	 */
	public function getNextRepay() {
		$next = null;
		foreach ($this->invests as $invest) {
			if($invest->status==0 || $invest->status==-1) {
				if($next==null || $next->qishu>$invest->qishu) {
					$next = $invest;
				}
			}
		}
		return $next;
	}

	/**
	 * 查询此债权下次回款日期
	 * @return string|false            下次回款日期,没有回款了返回false
	 */
	public function getNextRepayDay() {
		$next = $this->getNextRepay();
		if($next) {
			return date('Y-m-d', strtotime($next->endtime));
		} else {
			return false;
		}
	}

	/**
	 * 获取该笔投资的待收总额
	 * @return double
	 */
	public function getStayMoney() {
		$total = 0;
		foreach ($this->invests as $invest) {
			if($invest->status==0) {
				$total += $invest->zongEr;
			}
		}
		return $total;
	}

	/**
	 * 获取该笔投资的待收利息
	 * @return double
	 */
	public function getOneInterest($isTransfer=false) {
		$total = 0;
		$status = 0;
		if($isTransfer) {
			$status = 2;
		}
		foreach ($this->invests as $invest) {
			if($invest->status==$status) {
				$total = $invest->interest;
				break;
			}
		}
		return $total;
	}

	/**
	 * 获取该笔投资的待收利息
	 * @return double
	 */
	public function getStayInterest($isTransfer=false) {
		$total = 0;
		$status = 0;
		if($isTransfer) {
			$status = 2;
		}
		foreach ($this->invests as $invest) {
			if($invest->status==$status) {
				$total += $invest->interest;
			}
		}
		return $total;
	}

	/**
	 * 获取该笔投资的已获利息
	 * @return double
	 */
	public function getOverInterest() {
		$total = 0;
		foreach ($this->invests as $invest) {
			if($invest->status==1||$invest->status==4) {
				$total += $invest->interest;
			} else if($invest->status==3) {
				$total += $invest->realinterest;
			}
		}
		return $total;
	}

	/**
	 * 获取该笔投资的未结利息【即本期已投资天数的利息】
	 * @return double
	 */
	public function getInvestedStayInterest() {
		$interest = 0;
		$next = $this->getNextRepay();
		if($next) {
			$baseDay = DateHelper::getIntervalDay($next->addtime, $next->endtime);
			$day = DateHelper::getIntervalDay($next->addtime, date('Y-m-d'));

			$interest = $next->interest/$baseDay*$day;
		}
		return $interest;
	}

	/**
	 * 获取该笔投资的最终收益
	 * @return double
	 */
	public function getInterest($type='all') {
		$total = 0;
		if($type=='over') {
			$total = $this->getOverInterest();
		} else if($type=='stay') {
			$total = $this->getStayInterest();
		} else {
			foreach ($this->invests as $invest) {
				if($invest->status==0||$invest->status==1||$invest->status==4) {
					$total += $invest->interest;
				} else if($invest->status==3) {
					$total += $invest->realinterest;
				}
			} 
		}
		return $total;
	}

	/**
	 * 获取该笔投资的待收总额（投资伊始）
	 * @return double
	 */
	public function getBaseStayMoney() {
		$total = 0;
		foreach ($this->invests as $invest) {
			$total += $invest->benJin + $invest->interest;
		}
		return $total;
	}

	/**
	 * 获取该笔投资的回款时间
	 * @return string
	 */
	public function getEndTimes() {
		$endtimes = [];
		foreach ($this->invests as $invest) {
			$endtimes[] = $invest->endtime;
		}
		return $endtimes;
	}

	/**
	 * 获取该笔投资的最后回款时间
	 * @return string
	 */
	public function getLastEndTime() {
		$last = 0;
		foreach ($this->invests as $invest) {
			$endtime = strtotime($invest->endtime);
			$last = $endtime>$last?$endtime:$last;
		}
		return date('Y-m-d', $last);
	}

	/**
	 * 获取该笔投资的剩余天数
	 * @return string
	 */
	public function getRemainDay() {
		$time = $this->getLastEndTime();
		return DateHelper::getIntervalDay(date('Y-m-d'), $time);
	}

	/**
	 * 获取转让时是否需要服务费
	 * @return string
	 */
	public function needFee() {
		$begin = 0;
		$begin = strtotime(date('Y-m-d', strtotime($this->time)) . ' 00:00:00');
		$end = strtotime(date('Y-m-d') . ' 00:00:00');
		$day = intval(($end-$begin)/(24*60*60));
		if($day < 90) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 获取转让时收的服务费
	 * @return string
	 */
	public function getCrtrSM() {
		if($this->needFee()) {
			return Crtr::serviceFee($this->remain,date('Y-m-d', strtotime($this->time)));
		}
		return 0;
	}

	/**
	 * 债权转让时查询债权信息
	 * @return array               债权转让信息
	 */
	public function getTenderInfo() {
		// 二次转让
		if($this->ckclaims==1) {
			return $this->getTenderInfoTwice();
		} else {
			return $this->getTenderInfoOnce();
		}
	}

	/**
	 * 债权转让时查询债权信息(首次转让)
	 * @return array               债权转让信息
	 */
	public function getTenderInfoOnce() {
		$odd = $this->odd;
        $user = $this->user;

        $reward = floatval(Reward::getTenderReard($this->userId, $this->id));

        // 上一次还款记录
        $prevInvest = Invest::where('oddMoneyId', $this->id)
        	->where('status', 1)
        	->orderBy('endtime', 'desc')
        	->first(['endtime','realAmount']);

        // 投资未结利息天数
        $noInvestDay = 0;
        if($prevInvest) {
            $noInvestDay = intval((time()-strtotime($prevInvest->endtime))/(24*60*60));
        } else {
            $noInvestDay = intval((time()-strtotime($odd->oddRehearTime))/(24*60*60));
        }

        // 投资未结利息
        $noInvestMoney = $noInvestDay*(($odd->oddYearRate/360)*$this->money);
        $noInvestMoney = floatval($noInvestMoney);

        // 投资未结利息管理费
        $userGreade = $user->getTenderGrade();
        $noInvestFee = $noInvestMoney * $userGreade['feePer'];
        $noInvestFee = floatval($noInvestFee);

        // 参考价值
        $reference = $this->money + $noInvestMoney - $noInvestFee;

        // 已结利息
        $overInterest = Invest::where('oddMoneyId', $this->id)->where('status', 1)->sum('interest');

        $info = [];
		$info['realTenderMoney'] = intval($this->money);
		$info['tenderMoney'] = intval($this->money);
		$info['realTenderDay'] = intval((time()-strtotime($odd->oddRehearTime))/(24*60*60));
		$info['tenderDay'] = $info['realTenderDay'];
		$info['overInterest'] = floatval($overInterest);
		$info['noInvestFee'] = $noInvestFee;
		$info['noInvestDay'] = $noInvestDay;
		$info['noInvestMoney'] = floatval($noInvestMoney);
		$info['reward'] = $reward;
		$info['reference'] = $reference;
		$info['count'] = 0;

		return $info;
	}

	/**
	 * 债权转让时查询债权信息(二次转让)
	 * @return array               债权转让信息
	 */
	public function getTenderInfoTwice() {
		$odd = $this->odd;
		$user = $this->user;
		$claim = OddClaims::where('oddmoneyId', $this->id)->where('status', 1)->where('type', 'in')->orderBy('id', 'desc')->first();
		$reward = floatval(Reward::getTenderReard($this->userId, $this->id));
		$count = OddClaims::where('oddmoneyId', $this->id)->where('status', 1)->where('type', 'in')->count();

		// 投资未结利息天数
        $noInvestDay = 0;
		$prevInvest = Invest::where('oddMoneyId', $this->id)
			->where('status', 1)
        	->orderBy('endtime', 'desc')
        	->first(['endtime','realAmount']);

        if($prevInvest) {
        	$noInvestDay = intval((time()-strtotime($prevInvest->endtime))/(24*60*60));
        } else {
        	$noInvestDay = intval((time()-strtotime($claim->addtime))/(24*60*60));
        }

        // 投资未结利息
        $noInvestMoney = $noInvestDay*(($odd->oddYearRate/360)*$this->money);
        $noInvestMoney = floatval($noInvestMoney);

        // 投资未结利息管理费
        $userGreade = $user->getTenderGrade();
        $noInvestFee = $noInvestMoney * $userGreade['feePer'];
        $noInvestFee = floatval($noInvestFee);

        // 已结利息
        $overInterest = Invest::where('oddMoneyId', $this->id)
        	->where('status', 1)
        	->where('endtime', '>', $claim->addtime)
        	->sum('interest');

        // 参考价值
        $reference = $this->money + $noInvestMoney - $noInvestFee;

		$info = [];
		$info['realTenderMoney'] = floatval($claim->claimsMoney);
		$info['tenderMoney'] = intval($this->money);
		$info['realTenderDay'] = intval((time()-strtotime($claim->addtime))/(24*60*60));
		$info['tenderDay'] = intval((time()-strtotime($odd->oddRehearTime))/(24*60*60));
		$info['overInterest'] = floatval($overInterest);
		$info['noInvestFee'] = $noInvestFee;
		$info['noInvestDay'] = $noInvestDay;
		$info['noInvestMoney'] = $noInvestMoney;
		$info['reward'] = $reward;
		$info['reference'] = $reference;
		$info['count'] = intval($count);

		return $info;
	}

	/**
	 * 是否结息日
	 * @return boolean            是否结息日
	 */
	public function isEndDay() {
		foreach ($this->invests as $invest) {
			if(date('Y-m-d', strtotime($invest->endtime))==date('Y-m-d')) {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * 获取最终还款日
	 * @param boolean $checkOT   若为true则返回实际最终还款日，否则为预计最终还款日
	 * @return string            最终还款日
	 */
	public function getEndDay($checkOT=true) {
		$last = null;
		foreach ($this->invests as $invest) {
			if($last) {
				if($invest->qishu>$last->qishu) {
					$last = $invest;
				}
			} else {
				$last = $invest;
			}
		}
		if(!$last) {
			return '0000-00-00';
		}
		if($checkOT&&$last->operatetime) {
			return date('Y-m-d', strtotime($last->operatetime));
		}
		return date('Y-m-d', strtotime($last->endtime));
	}



	/**
	 * 获取合同信息
	 * @return array           合同信息
	 */
	public function getProtocolInfo($type) {
		$params = [];
		$odd = $this->odd;
	    $borrower = $odd->user;
	    $investor = $this->user;

	    $params['companySeal'] = '<div style="color:#fff"><span>~~~~~~~~~~~~汇诚普惠平台章</span><span>&nbsp&nbsp</span></div>';
	    $params['loanerSeal'] = '<div style="color:#fff"><span>~~~~~~'.StringHelper::l2uNum($borrower->userId).'</span><span>&nbsp&nbsp</span></div>';

		if(($this->type=='invest'||$this->type=='loan')  && $odd->oddType=='house-mor') {
	        $params['tradeNo'] = $this->oddNumber;
	        $params['tenderName'] = $borrower->name;
	        if($this->type == 'loan'){
		        $params['tenderUserId'] = $borrower->userId;
		        $params['tenderID'] = $borrower->cardnum;
		        $params['phone'] = _hide_phone($borrower->phone);
	        }else{
		        $params['tenderUserId'] = $borrower->userId;
		        $params['tenderID'] = $borrower->cardnum; //_hide_cardnum();
		        $params['phone'] = _hide_phone($borrower->phone);
	        }
	        $params['repayType'] = $odd->getRepayTypeName();
	        $params['rehearTime'] = $odd->oddRehearTime;
	        $params['rehearTimeYear'] = _date('Y', $odd->oddRehearTime);
	        $params['rehearTimeMonth'] = _date('n', $odd->oddRehearTime);
	        $params['rehearTimeDay'] = _date('j', $odd->oddRehearTime);
	        $params['oddPeriod'] = $odd->oddBorrowPeriod;
	        $params['borrowerName'] = $borrower->name;
	        $params['borrowerID'] = _hide_cardnum($borrower->cardnum);
	        $params['interestRatio'] = round($this->getInterestRatio()*100, 2);
	        $data = [];
	        $data[] = ['1.1','借款本金总金额：人民币（大写）'.DateHelper::toChineseNumber($odd->oddMoney).'（小写:'.$odd->oddMoney.'）；'];
	        $data[] =  ['1.2','借款期限：'.$odd->oddBorrowPeriod.'个月， 自 '.date('Y年m月d日',strtotime($odd->oddRehearTime)).'起至 '.date('Y年m月d日',strtotime( ' + '.$odd->oddBorrowPeriod*30 . 'day', strtotime($odd->oddRehearTime))).'止。'];
	        $interest = round(($odd->oddMoney * $odd->oddBorrowPeriod * $odd->oddYearRate /12),2);
	        $data[] = ['1.3','借款年化利率：'.$odd->oddYearRate * 100 .'%/年。利息总计：'.$interest.'元。借款利率不因国家利率政策变化而调整。借款利率的折算：日利率=年利率/360，月利率=年利率/12。'];
	        $style = [
	            'rowWidth' => [15,165],
	            'rowHeight' => [8,8,18]
	        ];
	        $params['borrowInfo'] = ['data'=>$data, 'style'=>$style];

	        if($borrower->userType == 3){
        		$params['tmpl'] = 'loanCompany';
        		$bank = $borrower->userbank;
        		$params['USCI'] = $bank->USCI;
        		$params['enterpriseName'] = $bank->enterpriseName;
				$params['legal'] = $bank->legal;
        	}else{
        		$params['tmpl'] = 'loan';
        	}

	        $header = ['姓名','身份证号码','平台账号','出借金额'];
	        $data = [];
	        $params['investSeal'] = '<div style="color:#fff">';
	        $sealUser = [];
	        $i = 0;
	        foreach ($odd->invest as $invest) {
	        	if(!in_array($invest->user->userId, $sealUser)){
		        	$i++;
		        	if($i == 4){
		        		$i = 0;
		        		$params['investSeal'] .= '<div>~~~</div>';
		        	}
		        	$sealUser[] = $invest->user->userId;
		        	$params['investSeal'] .= '<span>'.StringHelper::l2uNum($invest->user->userId).'</span><span>&nbsp&nbsp</span>';
	        	}
	        	if($invest->user->userId == $investor->userId || 1){
	        		$cardnum = $invest->user->cardnum;
	        		$name = $invest->user->name;
	        	}else{
	        		$cardnum = _hide_cardnum($invest->user->cardnum);
	        		$name = _hide_name($invest->user->name);
	        	}
	            $row = [$invest->user->name,$cardnum,$invest->user->userId,$invest->money];
	            $data[] = $row;
	        }
	        $params['investSeal'] .= '</div>';
	        $params['investInfo'] = ['header'=>$header, 'data'=>$data];

	        if($type == 'hide'){
	        	if($borrower->userType == 3){
		        	$params['USCI'] = _hide_USCI($params['USCI']);
		        	$params['enterpriseName'] = _hide_company($params['enterpriseName']);
					$params['legal'] = _hide_name($params['legal']);
				}
	        	$params['tenderName'] = _hide_name($params['tenderName']);
				$params['tenderID'] = _hide_cardnum($params['tenderID']);
	        }
	        return $params;
        }

        if(($this->type=='invest'||$this->type=='loan') && $odd->oddType=='auto-ins') {	        
        	$params['tradeNo'] = $this->tradeNo;
	        $params['tenderName'] = $borrower->name;
	        if($this->type == 'loan'){
		        $params['tenderUserId'] = $borrower->userId;
		        $params['tenderID'] = $borrower->cardnum;
		        $params['phone'] = _hide_phone($borrower->phone);
	        }else{
		        $params['tenderUserId'] = $borrower->userId;
		        $params['tenderID'] = $borrower->cardnum; //_hide_cardnum();
		        $params['phone'] = _hide_phone($borrower->phone);
	        }
	        $params['repayType'] = $odd->getRepayTypeName();
	        $params['rehearTime'] = $odd->oddRehearTime;
	        $params['rehearTimeYear'] = _date('Y', $odd->oddRehearTime);
	        $params['rehearTimeMonth'] = _date('n', $odd->oddRehearTime);
	        $params['rehearTimeDay'] = _date('j', $odd->oddRehearTime);
	        $params['oddPeriod'] = $odd->oddBorrowPeriod;
	        $params['borrowerName'] = $borrower->name;
	        $params['borrowerID'] = _hide_cardnum($borrower->cardnum);
	        $params['interestRatio'] = round($this->getInterestRatio()*100, 2);
	        $params['cardNum'] = $this->oddinfo->cardNum;
	        $params['cardType'] = $this->oddinfo->cardType;
	        $params['cardIDCode'] = $this->oddinfo->cardIDCode;
	        $params['cardEngine'] = $this->oddinfo->cardEngine;
	        $params['strongInsurance'] = $this->oddinfo->strongInsurance;
        	$params['vvTax'] = $this->oddinfo->vvTax;
        	$params['totalInsurance'] = round($this->oddinfo->totalInsurance,2);
        	$params['Ctotal'] = DateHelper::toChineseNumber($params['totalInsurance']);
        	$params['businessInsurance'] = $this->oddinfo->businessInsurance;
        	$params['insuranceCompany'] = $this->oddinfo->insuranceCompany;

        	if($borrower->userType == 3){
        		$params['tmpl'] = 'crdCompany';
        		$bank = $borrower->userbank;
        		$params['USCI'] = $bank->USCI;
        		$params['enterpriseName'] = $bank->enterpriseName;
				$params['legal'] = $bank->legal;
        	}else{
        		$params['tmpl'] = 'crd';
        	}

	        $data = [];
	        $data[] = ['2.1','借款本金总金额：人民币（大写）'.DateHelper::toChineseNumber($odd->oddMoney).'（小写:'.$odd->oddMoney.'）；'];
	        $data[] =  ['2.2','借款期限：'.$odd->oddBorrowPeriod.'个月， 自 '.date('Y年m月d日',strtotime($odd->oddRehearTime)).'起至 '.date('Y年m月d日',strtotime( ' + '.$odd->oddBorrowPeriod*30 . 'day', strtotime($odd->oddRehearTime))).'止。借款期限的具体起始日期以居间人授权第三方支付机构或其他付款方操作转款予借款人指定账户之日起算。'];
	        $interest = round($odd->getInterest(),2);
		$data[] = ['2.3','借款年化利率：'.$odd->oddYearRate * 100 .'%/年。利息总计：'.$interest.'元。借款利率不因国家利率政策变化而调整。借款利率的折算：日利率=年利率/360，月利率=年利率/12。'];
	        $style = [
	            'rowWidth' => [15,165],
	            'rowHeight' => [8,24,18]
	        ];
	        $params['borrowInfo'] = ['data'=>$data, 'style'=>$style];

			$header = ['序号','品牌型号','车牌号码','车辆识别代码','发动机代码','投保险种','投保金额'];
	        $data = [];

	        $info = $this->oddinfo;
	        $info->cardNum = json_decode($info->cardNum,true);
            $info->cardType = json_decode($info->cardType,true);
            $info->cardIDCode = json_decode($info->cardIDCode,true);
            $info->cardEngine = json_decode($info->cardEngine,true);
            $info->insuranceMoney = json_decode($info->insuranceMoney,true);
            $info->insuranceType = json_decode($info->insuranceType,true);

	        foreach ($info->cardNum as $k => $v) {
	        	if($info->cardType[$k]){
		            $row = [
		            		$k,
							$info->cardType[$k],
		            		$info->cardNum[$k],
							$info->cardIDCode[$k],
							$info->cardEngine[$k],
							$info->insuranceType[$k],
							$info->insuranceMoney[$k],
				        ];
				    if($type == 'hide'){
				    	$row = [
		            		$k,
							$info->cardType[$k],
		            		_hide_name($info->cardNum[$k]),
							_hide_name($info->cardIDCode[$k]),
							_hide_name($info->cardEngine[$k]),
							$info->insuranceType[$k],
							$info->insuranceMoney[$k],
				        ];
				    }
		            $data[] = $row;
	        	}
	        }
	        $params['carinfo'] = ['header'=>$header, 'data'=>$data];

	        {
		        // $data = [];
		        // $data[] = [
		        // 	'出借人：',$investor->name
		        // ];
		        // $data[] = [
		        // 	'身份证号码：',$investor->cardnum
		        // ];
		        // $data[] = [
		        // 	'平台账号：',$investor->username
		        // ];
		        // $data[] = [
		        // 	'借出金额：',$this->money
		        // ];
		        // $data[] = [
		        // 	'借款期限：',$odd->oddBorrowPeriod * 30 . '天'
		        // ];
		        // $data[] = [
		        // 	'每月应收利息：',$this->getOneInterest()
		        // ];
		        // $data[] = [
		        // 	'到期应收本金：',$this->money
		        // ];

		        // $paydays = '';
		        // $i = 0;
		        // foreach ($this->invests as $key => $value) {
		        // 	$paydays.= '第'.$value['qishu'].'期回款：'._date('Y年m月d日', $value['endtime']).'，返息：'.$value['interest'].'元，还本：'.$value['benJin'].'元。';
		        // 	$i++;
		        // }

		        // $data[] = [
		        // 	'每期回款数据：',$paydays
		        // ];
		        // $style = [
		        //     'rowWidth' => [40,140],
		        //     'rowHeight' => [8,8,8,8,8,$i?$i*8:'8']
		        // ];
		        // $params['investInfo'] = ['data'=>$data, 'style'=>$style];
			}
	        $header = ['姓名','身份证号码','平台账号','出借金额'];
	        $data = [];
	        $params['investSeal'] = '<div style="color:#fff">';
	        $sealUser = [];
	        $i = 0;
	        foreach ($odd->invest as $invest) {
	        	if(!in_array($invest->user->userId, $sealUser)){
		        	$i++;
		        	if($i == 4){
		        		$i = 0;
		        		$params['investSeal'] .= '<div>~~~</div>';
		        	}
		        	$sealUser[] = $invest->user->userId;
		        	$params['investSeal'] .= '<span>'.StringHelper::l2uNum($invest->user->userId).'</span><span>&nbsp&nbsp</span>';
	        	}
	        	if($invest->user->userId == $investor->userId || 1){
	        		$cardnum = $invest->user->cardnum;
	        		$name = $invest->user->name;
	        	}else{
	        		$cardnum = _hide_cardnum($invest->user->cardnum);
	        		$name = _hide_name($invest->user->name);
	        	}
	            $row = [$invest->user->name,$cardnum,$invest->user->userId,$invest->money];
	            $data[] = $row;
	        }
	        $params['investSeal'] .= '</div>';
	        $params['investInfo'] = ['header'=>$header, 'data'=>$data];
	        
	        if($type == 'hide'){
	        	if($borrower->userType == 3){
		        	$params['USCI'] = _hide_USCI($params['USCI']);
		        	$params['enterpriseName'] = _hide_company($params['enterpriseName']);
					$params['legal'] = _hide_name($params['legal']);
				}
	        	$params['tenderName'] = _hide_name($params['tenderName']);
				$params['tenderID'] = _hide_cardnum($params['tenderID']);
	        }

	        return $params;
        }

        if(0) {
	        $params['tradeNo'] = $this->tradeNo;
	        $params['tenderName'] = $investor->name;
	        $params['tenderID'] = $investor->cardnum;
	        $params['borrowerName'] = $borrower->name;
	        $params['borrowerID'] = _hide_cardnum($borrower->cardnum);
	        $params['borrowerUsername'] = _hide_username($borrower->username);
	        $params['rehearTime'] = $odd->oddRehearTime;
	        $header = ['出借人帐户名', '出借人姓名', '身份证号', '投标金额', '借款期限', '年利率', '借款开始日', '借款截止日', '投标本息'];
	        $data[] = [
	            $investor->username, 
	            $investor->name,
	            $investor->cardnum,
	            $this->money,
	            $odd->getPeriod(),
	            ($odd->oddYearRate*100).'%',
	            _date('Y.m.d', $odd->oddRehearTime),
	            _date('Y.m.d', $odd->getEndTime()),
	            $this->getBaseStayMoney()
	        ];
	        $style = [
	            'rowWidth' => [24, 22, 32, 16, 15, 15, 20, 20, 16],
	        ];
	        $params['borrowInfo'] = ['header'=>$header, 'data'=>$data, 'style'=>$style];

	        $header = ['还款日期', '每期还款金额'];
	        $data = [];
	        foreach ($this->invests as $invest) {
	            $row = [_date('Y年n月j日', $invest->endtime), $invest->zongEr];
	            $data[] = $row;
	        }
	        $params['repayInfo'] = ['header'=>$header, 'data'=>$data];
	        return $params;
	    }

	    if($this->type=='credit') {
	        $buyer = $this->user;
	        $traner = $this->parent->user;
	        $borrower = $this->odd->user;

	        $params['tranName'] = $traner->name;
	        $params['tranSeal'] = '<div style="color:#fff"><span>'.StringHelper::l2uNum($traner->userId).'</span><span>&nbsp&nbsp</span></div>';
	        $params['tranID'] = $traner->cardnum;
	        $params['tranUsername'] = _hide_username($traner->username);

	        $params['buyerName'] = $buyer->name;
	        $params['buyerSeal'] = '<div style="color:#fff"><span>'.StringHelper::l2uNum($buyer->userId).'</span><span>&nbsp&nbsp</span></div>';
	        $params['buyerID'] = $buyer->cardnum;
	        $params['buyerUsername'] = $buyer->username;

	        $params['borrowerName'] = $borrower->name;
	        $params['borrowerID'] = $borrower->cardnum;
	        $params['borrowerUsername'] = _hide_username($borrower->username);
	        $params['buyTime'] = _date('Y年n月j日', $this->time);

	        // if($odd->oddType=='danbao') {
	        //     $params['preName'] = '《融资租赁收益权转让协议》';
	        // } else {
	        //     $params['preName'] = '《借款协议》';
	        // }
	        $params['tradeNo'] = $this->tradeNo;
        	$params['oldtradeNo'] = $this->parent->tradeNo;
	        $params['money'] = $this->money;
	        $params['yearRate'] = $this->odd->oddYearRate * 100;
	        //$params['qishu'] = $this->odd->getLastPeriod();

	        // $header = ['借款合同编号', '剩余债权金额', '转让价格（即发标金额）'];
	        // $data[] = [$this->parent->tradeNo, $this->money, $this->money];
	        // $params['transferInfo'] = ['header'=>$header, 'data'=>$data];

	        // $header = ['债务人还款日期', '每期还款金额'];
	        // $data = [];
	        // $zongEr = 0;
	        $i = 0;
	        foreach ($this->invests as $invest) {
	        	// $zongEr += $invest->zongEr;
	        	$i ++;
	            // $row = [date('Y年n月j日', strtotime($invest->endtime)), $invest->zongEr];
	            // $data[] = $row;
	        }
	        $params['qishu'] = $i;
	        // $params['repayInfo'] = ['header'=>$header, 'data'=>$data];
	        if($type == 'hide'){
	        	$params['tenderName'] = _hide_name($params['tenderName']);
				$params['tenderID'] = _hide_cardnum($params['tenderID']);
	        }
	        return $params;
        }
	}

	/**
	 * 获取合同信息
	 * @return array           合同信息
	 */
	public function getProtocolInfoAdd($type) {
		$params = [];
		$odd = $this->odd;
		$info = $this->oddinfo;
	    $borrower = $odd->user;
	    $investor = $this->user;

	    $params['companySeal'] = '<div style="color:#fff"><span>汇诚普惠平台章</span><span>&nbsp&nbsp</span></div>';
	    $params['loanerSeal'] = '<div style="color:#fff"><span>~~~~~~'.StringHelper::l2uNum($borrower->userId).'</span><span>&nbsp&nbsp</span></div>';

		if(($this->type=='invest'||$this->type=='loan')  && $odd->oddType=='house-mor') {

	        $params['tradeNo'] = $this->tradeNo;
	        $params['tenderName'] = $borrower->name;
	        if($this->type == 'loan'){
		        $params['tenderUserId'] = $borrower->username;
		        $params['tenderID'] = $borrower->cardnum;
		        $params['phone'] = $borrower->phone;
	        }else{
		        $params['tenderUserId'] = _hide_phone($borrower->username);
		        $params['tenderID'] = $borrower->cardnum; //_hide_cardnum();
		        $params['phone'] = _hide_phone($borrower->phone);
	        }
	        
	        $params['thirdname'] = $this->oddinfo->thirdname;
	        $params['thirdcard'] = $this->oddinfo->thirdcard;
	        $params['houseaddr'] = $this->oddinfo->houseaddr;
			$params['housecard'] = $this->oddinfo->housecard;
			$params['housespace'] = $this->oddinfo->housespace;
			$params['needname'] = $this->oddinfo->needname;
			$params['needcardnum'] = $this->oddinfo->needcardnum;
			$params['accountor'] = $this->oddinfo->needname;
			$params['accountorcard'] = $this->oddinfo->needcardnum;
	        $params['rehearTime'] = $odd->oddRehearTime;
	        $params['rehearTimeYear'] = _date('Y', $odd->oddRehearTime);
	        $params['rehearTimeMonth'] = _date('n', $odd->oddRehearTime);
	        $params['rehearTimeDay'] = _date('j', $odd->oddRehearTime);
	        $params['oddPeriod'] = $odd->oddBorrowPeriod;
	        $params['borrowerName'] = $borrower->name;
	        $params['borrowerID'] = _hide_cardnum($borrower->cardnum);
	        $params['interestRatio'] = round($this->getInterestRatio()*100, 2);

	        if($borrower->userType == 3){
        		$params['tmpl'] = 'loanCompanyself';
        		$bank = $borrower->userbank;
        		$params['USCI'] = $bank->USCI;
        		$params['enterpriseName'] = $bank->enterpriseName;
				$params['legal'] = $bank->legal;
        	}else{
        		if($info->third){
        			$params['tmpl'] = 'loanthird';
        		}else{
        			$params['tmpl'] = 'loanself';
        		}
        	}

        	$params['accountSeal'] = '<div><span style="color:#fff">电子签章投资章专用</span>';

	        $header = ['姓名','身份证号码','平台账号','出借金额'];
	        $data = [];
	        $params['investSeal'] = '<div style="color:#fff">';
	        $sealUser = [];
	        $i = 0;
	        foreach ($odd->invest as $invest) {
	        	if(!in_array($invest->user->userId, $sealUser)){
		        	$i++;
		        	if($i == 4){
		        		$i = 0;
		        		$params['investSeal'] .= '<div>~~~</div>';
		        	}
		        	$sealUser[] = $invest->user->userId;
		        	$params['investSeal'] .= '<span>'.StringHelper::l2uNum($invest->user->userId).'</span><span>&nbsp&nbsp</span>';
	        	}
	        	if($invest->user->userId == $investor->userId || 1){
	        		$cardnum = $invest->user->cardnum;
	        	}else{
	        		$cardnum = _hide_cardnum($invest->user->cardnum);
	        	}
	            $row = [$invest->user->name,$cardnum,$invest->user->userId,$invest->money];
	            $data[] = $row;
	        }
	        $params['investInfo'] = ['header'=>$header, 'data'=>$data];
	        $params['investSeal'] .= '</div>';

	        if($type == 'hide'){
	        	if($borrower->userType == 3){
	        		$params['USCI'] = _hide_USCI($params['USCI']);
	        		$params['enterpriseName'] = _hide_company($params['enterpriseName']);
					$params['legal'] = _hide_name($params['legal']);
				}
	        	$params['accountorcard'] = _hide_cardnum($params['accountorcard']);
	        	$params['thirdcard'] = _hide_cardnum($params['thirdcard']);
	        	$params['thirdname'] = _hide_name($params['thirdname']);
	        	$params['houseaddr'] = _hide_num($params['houseaddr']);
	        	$params['housecard'] = '******';
	        	$params['tenderName'] = _hide_name($params['tenderName']);
				$params['tenderID'] = _hide_cardnum($params['tenderID']);
	        }
	        return $params;
        }
	}

	/**
	 * 转让债权
	 * @param string $media  操作来源  
	 * @return boolean       是否成功
	 */
	public function transfer($tradeNo, $media='pc') {
		$period = 0;
		foreach ($this->invests as $invest) {
			if($invest->status==0) {
				$period ++;
			}
		}

		$money = $this->remain;
		$this->ckclaims = -1;

		$crtr = new Crtr();
		$crtr->oddmoneyId = $this->id;
		$crtr->addtime = date('Y-m-d H:i:s');
		$crtr->money = $money;
		$crtr->userId = $this->userId;
		$crtr->oddNumber = $this->oddNumber;
		$crtr->progress = 'start';
		$crtr->period = $period;
		$crtr->media = $media;
		$crtr->outtime = date('Y-m-d H:i:s', time()+3*24*3600);
		$crtr->tradeNo = $tradeNo;

		if($this->save() && $crtr->save()) {
			
			$key = Redis::getKey('crtrRemain', ['sn'=>$crtr->getSN()]);
			Redis::set($key, bcmul($money, 100));

			return true;
		} else {
			return false;
		}
	}

	/**
	 * 生成合同PDF
	 * @param  boolean $seal  是否盖章
	 * @param  boolean $fresh 若已有合同，是否重新生成
	 * @return string  合同文件名
	 */
	public function generateProtocol($seal=true, $fresh=false) {
		$fileName = '';
		if(!$fresh&&$this->protocol) {
			$fileName = $this->protocol->protocolName;
		} else {
			$type = '';
			if($this->type=='invest' || $this->type=='loan') {
				$type = 'odd';
				if($this->odd->oddType=='house-mor' || $this->odd->oddType=='auto-ins') {
					$fileName = $this->generateLoan($this, 'F', $seal);
					//$this->generateLoan($this, 'F', $seal, 'hide');
					if($this->odd->oddType=='house-mor'){
						$this->generateLoanAdd($this, 'F', $seal);
						//$this->generateLoanAdd($this, 'F', $seal, 'hide');
					}
	            } else if($this->odd->oddType=='auto-ins') {
	            	$fileName = $this->generateCrd($this, 'F', $seal);
	            } else {
	                $fileName = $this->generateLease($this, 'F', $seal);
	            }
			} else if($this->type=='credit') {
				$type = 'crtr';
	            $fileName = $this->generateCrtr($this, 'F', $seal);
	            $this->generateCrtr($this, 'F', $seal, 'hide');
			}
			
			if(!$this->protocol) {
				$protocol = new Protocol();
				$protocol->userId = $this->userId;
	        	$protocol->oddMoneyId = $this->id;
	        	$protocol->created_at = date('Y-m-d H:i:s');
	        	$protocol->type = $type?$type:$this->type;
				$protocol->protocolName = $fileName;
	        	$protocol->save();
			}
        }
        return $fileName;
	}

	public function getProtocolLink($isFile=true) {
		if($this->ancun) {
			$url = Registry::get('config')->ancun->search;
			return $url . '/investment-detail?recordNo=' . $this->ancun->recordNo;
		} else {
			if($isFile) {
				$protocol = Protocol::where('oddMoneyId', $this->id)->first();
				if($protocol) {
					return WEB_ASSET.'/protocols/'.$protocol->protocolName;
				} else {
					$fileName = $this->generateProtocol(false);
					return WEB_ASSET.'/protocols/'.$fileName;
				}
			} else {
				return WEB_MAIN.'/protocol/show/pronum/'.$this->id;
			}
		}
	}
	
	/**
	 * 获取某日的投资和借款统计数据
	 * @param string $time 日期
	 * @return array
	 */
	public static function getDateDs($time) {
		// 新用户投资人数
		$data['newInvestUserNum'] = 0;
		// 老用户投资人数
		$data['oldInvestUserNum'] = 0;
		// 投资金额
		$data['investMoney'] = 0;
		// 新用户借款人数
		$data['newLoanUserNum'] = 0;
		// 老用户借款人数
		$data['oldLoanUserNum'] = 0;
		// 借款金额
		$data['loanMoney'] = 0;
		$investUser = [];
		$loanUser = [];
		$userOdd = self::where('time', 'like', $time.'%')->where('type', '<>', 'credit')->get();
		if ($userOdd->isEmpty()) {
			return $data;
		}
		foreach ($userOdd as $value) {
			if ($value->type == 'invest') {
				$data['investMoney'] += $value->money;
				$investUser[] = $value->userId;
			} elseif ($value->type == 'loan') {
				$data['loanMoney'] += $value->money;
				$loanUser[] = $value->userId;
			}
		}
		
		// 查询用户第一次投资的时间
		if ($investUser) {
			$userFirstInvest = self::where('type', '=', 'invest')
				->whereIn('userId', $investUser)
				->orderBy('time', 'asc')
				->groupBy('userId')
				->select('userId', 'time')
				->get()
				->toArray();
			$userFirstInvest = array_column($userFirstInvest, 'time', 'userId');
		}
		
		// 查询用户第一次借款的时间
		if ($loanUser) {
			$userFirstLoan = self::where('type', '=', 'loan')
				->whereIn('userId', $loanUser)
				->orderBy('time', 'asc')
				->groupBy('userId')
				->select('userId', 'time')
				->get()
				->toArray();
			$userFirstLoan = array_column($userFirstLoan, 'time', 'userId');
		}
		
		foreach ($userOdd as $value) {
			if ($value->type == 'invest') {
				if ($value->time <= $userFirstInvest[$value->userId]) {
					self::where('id', $value->id)->update(['userType' => 1]);
					$data['newInvestUserNum'] ++;
				} else {
					self::where('id', $value->id)->update(['userType' => 2]);
					$data['oldInvestUserNum'] ++;
				}
			} elseif ($value->type == 'loan') {
				if ($value->time <= $userFirstLoan[$value->userId]) {
					self::where('id', $value->id)->update(['userType' => 1]);
					$data['newLoanUserNum'] ++;
				} else {
					self::where('id', $value->id)->update(['userType' => 2]);
					$data['oldLoanUserNum'] ++;
				}
			}
		}
		return $data;
	}

	/**
	 * 利息所占投资金额的比例
	 * @param  models\Odd   $odd  所投标的(为null时使用关联对象odd)
	 * @return double
	 */
	public function getInterestRatio($odd=null) {
		if($odd==null) {
			$odd = $this->odd;
		}
	    $ratio = 0;
	    if($odd->oddRepaymentStyle=='monthpay') {
	    	$ratio = $odd->oddYearRate/12*$odd->oddBorrowPeriod;
	    } else if($odd->oddRepaymentStyle=='matchpay') {
	    	$stayAll = $this->getBaseStayMoney();
	    	$ratio = ($stayAll-$this->money)/$this->money;
	    }
	    return $ratio;
	}

	/**
	 * 获取存管订单号
	 * @param  string $type 类型
	 * @return string       订单号
	 */
	public function getOrderID($type='rehear') {
		$time = date('YmdHis');
		$orderID = str_repeat('0', 10-strlen($this->id)).$this->id;
		if($type=='rehear') {
			$orderID = 'R'.$time.$orderID;
		} else if($type=='end') {
			$orderID = 'E'.$time.$orderID;
		}
		return $orderID;
	}
}
