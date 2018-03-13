<?php
namespace models;

use Yaf\Registry;
use helpers\DateHelper;
use tools\Redis;
use tools\Counter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Odd|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Odd extends Model {

  /**
   * 生成合同状态（二进制判断）
   */
  const CER_STA_PR = 1;

  /**
   * 发送安存状态（二进制判断）
   */
  const CER_STA_AC = 2;

	protected $table = 'work_odd';

  protected $primaryKey = 'oddNumber';
  
  protected $guarded = ['remain'];

  /*protected $casts = [
    'oddNumber' => 'string'
  ];*/

  public $incrementing = false;

  public $timestamps = false;

  // 消费金融期数对应年利率
  public static $csfnRates = [
    30 => 0.16,
    35 => 0.165,
    40 => 0.17,
    45 => 0.175,
    50 => 0.18
  ];

  public static $prgVals = [
    'prep' => 0,
    'start' => 1,
    'review' => 2,
    'run' => 3,
    'end' => 4,
  ];

  public static $recUsers = [
    '18760419185','18850400209'
  ];

  public static $oddTypes = [
    'house-mor' => ['key'=>1, 'long'=>'房优贷', 'short'=>'房优贷', 'color'=>'#2EA6E3'],
    'auto-ins' => ['key'=>2, 'long'=>'车险贷', 'short'=>'车险贷', 'color'=>'#BFDB39'],
  ];

  public static $finishTypes = [
    'normal' => 1,
    'advance' => 2,
    'delay' => 3,
  ];

  /**
   * 进程类型
   * success： 成功的借款
   * finished：完结的借款
   * fail：失败的借款
   * repaying：还款中的借款
   * biding：筹款中的借款
   * prepare：准备筹款中
   */
  public static $progressTypes = [
    'success' => ['run', 'end', 'review'],
    'finished' => ['end'],
    'fail' => ['fail'],
    'repaying' => ['run'],
    'biding' => ['start','review'],
    'review' => ['review'],
    'prepare' => ['prep'],
  ];
  
  public static $investTypes = ['自动标', '手动标'];

  public function debts() {
    return $this->hasMany('models\OddMoney', 'oddNumber', 'oddNumber');
  }

  public function interests() {
    return $this->hasMany('models\Interest', 'oddNumber', 'oddNumber');
  }

  public function user() {
    return $this->belongsTo('models\User', 'userId');
  }

  public function username() {
    return $this->belongsTo('models\User', 'userId')->select('name','userId');
  }

  public function gps() {
    return $this->hasOne('models\Gps', 'oddNumber', 'oddNumber');
  }

  public function loan() {
    return $this->hasOne('models\OddMoney', 'oddNumber', 'oddNumber')->where('type', 'loan');
  }

  public function tenders() {
    return $this->hasMany('models\OddMoney', 'oddNumber', 'oddNumber')->where('type', '<>' , 'loan');
  }
  
  public function invest() {
  	return $this->hasMany('models\OddMoney', 'oddNumber', 'oddNumber')->where('type', 'invest');
  }

  /**
   * 获取搜索类型
   * @param  integer $type 类型ID
   * @return string        类型标识符
   */
  public static function getSearchType($type) {
    $where = false;
    switch ($type) {
      case 1:
        $where = '(oddType=\'house-mor\')';
        break;
      case 2:
        $where = '(oddType=\'auto-ins\')';
        break;
    }
    return $where;
  }

  /**
   * 获取搜索时间
   * @param  integer $time 时间ID
   * @return string        时间where语句
   */
  public static function getSearchTime($time) {
    $where = false;
    switch ($time) {
      case 1:
        $where = '(oddBorrowStyle=\'month\' and oddBorrowPeriod<=3)';
        break;
      case 2:
        $where = '(oddBorrowStyle=\'month\' and oddBorrowPeriod>3 and oddBorrowPeriod<=6)';
        break;
      case 3:
        $where = '(oddBorrowStyle=\'month\' and oddBorrowPeriod>6 and oddBorrowPeriod<=9)';
        break;
      case 4:
        $where = '(oddBorrowStyle=\'month\' and oddBorrowPeriod>9 and oddBorrowPeriod<=12)';
        break;
      case 5:
        $where = '(oddBorrowStyle=\'month\' and oddBorrowPeriod>12)';
        break;
      default:
        break;
    }
    return $where;
  }

  /**
   * 获取搜索利率
   * @param  integer $rate  利率ID
   * @return string         利率where语句
   */
  public static function getSearchRate($rate) {
    $where = false;
    switch ($rate) {
      case 1:
        $where = 'oddYearRate<0.1';
        break;
      case 2:
        $where = 'oddYearRate>=0.1 and oddYearRate<0.11';
        break;
      case 3:
        $where = 'oddYearRate>=0.11 and oddYearRate<0.12';
        break;
      case 4:
        $where = 'oddYearRate>=0.12 and oddYearRate<0.13';
        break;
      case 5:
        $where = 'oddYearRate>=0.13 and oddYearRate<0.15';
        break;
      case 6:
        $where = 'oddYearRate>=0.15';
        break;
      default:
        break;
    }
    return $where;
  }

  /**
   * 获取搜索金额
   * @param  integer $money 金额ID
   * @return string         金额where语句
   */
  public static function getSearchMoney($money) {
    $where = false;
    switch ($money) {
      case 1:
        $where = 'oddMoney<50000';
        break;
      case 2:
        $where = 'oddMoney>=50000 and oddMoney<100000';
        break;
      case 3:
        $where = 'oddMoney>=100000 and oddMoney<200000';
        break;
      case 4:
        $where = 'oddMoney>=200000 and oddMoney<300000';
        break;
      case 5:
        $where = 'oddMoney>=300000 and oddMoney<500000';
        break;
      case 6:
        $where = 'oddMoney>=500000';
        break;
      default:
        break;
    }
    return $where;
  }

  /**
   * 获取可投标的数量
   * @param  array $conditions 其他条件
   * @return int
   */
  public static function getCanBidNum($userId=null, $conditions=[]) {
    $builder = self::where('progress', 'start')->where('lookstatus', 1)->where($conditions);
    return self::withAppointUser($builder, $userId)->count();
  }

  /**
   * 获取标的
   * @param  string  $num     标的号
   * @param  string  $userId     登录用户
   * @return database           查询类
   */
  public static function getBuilder($num, $userId=null) {
    $builder = self::where('oddNumber', $num);
    return self::withAppointUser($builder, $userId);
  }

	/**
	 * 获取标的列表
   * @param  string  $userId     登录用户 
	 * @return Builder            查询类
	 */
	public static function getListBuilder($userId=null) {
		$builder = self::whereIn('progress', ['start', 'review', 'run'])->where('lookstatus', '1');
		return self::withAppointUser($builder, $userId);
	}

  /**
   * 获取新手标的列表
   * @param  string  $userId     登录用户 
   * @return Builder            查询类
   */
  public static function getNewBuilder($userId=null) {
    $builder = self::whereIn('progress', ['start', 'review', 'run'])->where('oddStyle','newhand')->where('lookstatus', '1');
    return self::withAppointUser($builder, $userId);
  }

  /**
   * 给builder加上预约标筛选条件
   * @param  Builder $builder  Builder类
   * @param  string $userId   当前用户ID
   * @return Builder          返回builder
   */
  public static function withAppointUser($builder, $userId=null) {
    if($userId) {
      $builder->whereRaw('(appointUserId=? or appointUserId=?)', [$userId, '']);
    } else {
      $builder->whereRaw('appointUserId=?', ['']);
    }
    return $builder;
  }

  /**
   * 标的列表排序
   * @return Builder            查询类
   */
  public static function sortList($builder,$sort = 'normal',$order = 'desc') {
    if($sort != 'normal' && $sort != ''){
        $builder->orderBy($sort, $order)->orderByRaw('field(progress, ?, ?, ?, ?, ?)', ['prep', 'start', 'review', 'run', 'end']);
    }else{
        $builder->orderByRaw('field(progress, ?, ?, ?, ?, ?)', ['prep', 'start', 'review', 'run', 'end'])->orderBy('oddTrialTime', 'desc');
    }
    
    return $builder;
  }

  /**
   * 全部成交量
   * @return double 成交量
   */
  public static function getTotalVolume() {
    $volume = Odd::where('oddType', '<>' , 'special')->whereIn('progress', self::$progressTypes['success'])->sum('oddMoney');
    //旧系统成交量
    $oldVolume = OldData::sum('investmoney');

    return $volume+$oldVolume;
  }

  /**
   * 昨日成交量
   * @return double 成交量
   */
  public static function getYestodayVolume() {
    $today = date('Y-m-d');
    $yestoday = date('Y-m-d', strtotime("$today -1 day"));
    $yestodayBegin = $yestoday . ' 00:00:00';
    $yestodayEnd = $yestoday . ' 23:59:59';
    $volume = Odd::where('oddType','<>','special')
      ->whereIn('progress', self::$progressTypes['success'])
      ->whereBetween('oddRehearTime', [$yestodayBegin,$yestodayEnd])
      ->sum('oddMoney');
    return $volume;
  }

  /**
   * 上月成交量
   * @return double 成交量
   */
  public static function getLastMonthVolume() {
    $dayArray = DateHelper::getLastMonth();
    $volume = Odd::where('oddType', '<>', 'special')
      ->whereIn('progress', self::$progressTypes['success'])
      ->whereBetween('oddRehearTime', [$dayArray[0],$dayArray[1]])
      ->sum('oddMoney');
    return $volume;
  }

  /**
   * 获取流水号
   * @return string 
   */
  public static function generateNumber() {
    $bnq = Counter::next('oddNumber', 'd');
    $time = Counter::getTime();
    return  date('Ymd', $time) . str_repeat('0', 6-strlen($bnq)).$bnq;
  }

  /**
   * 解析图片字符串成为数组
   * @param  string  $column  要解析的字段
   * @return array            图片数组max为大图,min为小图,normal为原图
   */
  public function oldDecodeImage($column) {
    $string = $this->$column;
    $imageStrArray = explode('[[', $string);
    $imageList = [];
    foreach ($imageStrArray as $key => $imgStr) {
      $image = json_decode(json_decode(htmlspecialchars_decode($imgStr),true),true);
      if($image) {
        $pos = strripos($image['max'], '.');
        $suffix = substr($image['max'], $pos);
        $imageName = substr($image['max'], 0, $pos);
        $image['normal'] = $imageName.$suffix;
        $imageList[] = $image;
      }

    }
    return $imageList;
  }

  /**
   * 解析图片字符串成为数组
   * @param  string  $column  要解析的字段
   * @return array            图片数组max为大图,min为小图,normal为原图
   */
  public function decodeImage($column) {
    $string = $this->$column;
    $imageStrArray = explode('[[', $string);
    $imageList = [];
    foreach ($imageStrArray as $key => $imgStr) {
      $image = json_decode(json_decode(htmlspecialchars_decode($imgStr),true),true);
      if($image) {
        $pos = strripos($image['max'], '.');
        $suffix = substr($image['max'], $pos);
        $imageName = substr($image['max'], 0, $pos);
        $image['normal'] = $imageName.$suffix;
        $imageList[] = $image;
      }

    }
    return $imageList;
  }

  /**
   * 获取查标投票数
   * @return integer    投票数
   */
  public function getVoteCount() {
    return LookVote::where('oddNumber', $this->oddNumber)->count('userId');
  }

  /**
   * 标的是否可投
   * @param string  $userId   用户ID
   * @return boolean           是否可投
   */
  public function isBidable($userId) {
    if($this->userId==$userId) {
      $rdata['status'] = 0;
      $rdata['info'] = '您不能投自己的标！';
      return $rdata;
    }
    if($this->progress!='start') {
      $rdata['status'] = 0;
      $rdata['info'] = '此标不处于可投标状态！';
      return $rdata;
    }
    if(time()<strtotime($this->openTime)) {
      $rdata['status'] = 0;
      $rdata['info'] = '此标不处于可投标状态！';
      return $rdata;
    }
    if($this->appointUserId&&$this->appointUserId!=$userId) {
      $rdata['status'] = 0;
      $rdata['info'] = '您不可投该标！';
      return $rdata;
    }
    if($this->isATBiding==1) {
      $rdata['status'] = 0;
      $rdata['info'] = '自动投标中，请稍后！';
      return $rdata;
    }
    $limitTime = strtotime($this->oddTrialTime)+$this->oddBorrowValidTime*24*60*60;
    if(time()>$limitTime) {
      $rdata['status'] = 0;
      $rdata['info'] = '此标已过期！';
      return $rdata;
    }
    $rdata['status'] = 1;
    return $rdata;
  }

  /**
   * 标的已还金额
   * @return double            已还金额
   */
  public function getHadRepayMoney() {
    $total = 0;
    foreach ($this->interests as $interest) {
      $total += $interest->realAmount;
    }
    return $total;
  }

  /**
   * 获取该标的最终收益
   * @return double
   */
  public function getInterest($type='all') {
    $total = 0;
    if($type=='over') {
      $total = $this->getOverInterest();
    } else if($type=='stay') {
      $total = $this->getStayInterest();
    } else {
      foreach ($this->interests as $invest) {
        if($invest->status==0||$invest->status==1||$invest->status==3) {
          $total += $invest->interest;
        } else if($invest->status==2) {
          $total += $invest->realinterest;
        }
      } 
    }
    return $total;
  }

  /**
   * 标的下次还款时间
   * @param  stinrg $oddNumber 标的ID
   * @return stinrg            时间
   */
  public function getNextRepayTime() {
    $last = null;
    $nextTime = false;
    foreach ($this->interests as $interest) {
      if($interest->status==0) {
        if($nextTime===false) {
          $nextTime = strtotime($interest->endtime);
          continue;
        }
        if(strtotime($interest->endtime) < $nextTime) {
          $nextTime = strtotime($interest->endtime);
        }
      }
    }
    if($nextTime!==false) {
      $nextTime = date('Y-m-d', $nextTime);
    }
    return $nextTime;
  }

    /**
   * 标的下次还款ID
   * @param  stinrg $oddNumber 标的ID
   * @return stinrg            时间
   */
  public function getNextRepayID() {
    $last = null;
    $nextTime = false;
    $id = null;
    foreach ($this->interests as $interest) {
      if($interest->realAmount==0) {
        if($nextTime===false) {
          $nextTime = strtotime($interest->endtime);
          $id = $interest->id;
          continue;
        }
        if(strtotime($interest->endtime) < $nextTime) {
          $id = $interest->id;
          $nextTime = strtotime($interest->endtime);
        }
      }
    }
    return $id;
  }

  /**
   * 获取该笔投资的最后回款日期
   * @param  boolean $isReal 是否返回实际还款时间
   * @return string
   */
  public function getEndTime($isReal=true) {
    if($isReal && $this->progress=='end') {
      return $this->finishTime;
    }
    $endTime = strtotime($this->oddRehearTime);
    if($this->oddBorrowStyle=='month') {
      $endTime += $this->oddBorrowPeriod * 30 * 24 * 60 * 60;
    } else if($this->oddBorrowStyle=='week') {
      $endTime += $this->oddBorrowPeriod * 7 * 24 * 60 * 60;
    } else if($this->oddBorrowStyle=='day') {
      $endTime += $this->oddBorrowPeriod * 1 * 24 * 60 * 60;
    }
    return date('Y-m-d H:i:s', $endTime);
  }

  /**
   * 获取该笔投资的剩余期数
   * @param  boolean $isReal 是否返回实际还款时间
   * @return string
   */
  public function getLastPeriod() {
    $endTime = strtotime($this->oddRehearTime);
    if($this->oddBorrowStyle=='month') {
      $endTime += $this->oddBorrowPeriod * 30 * 24 * 60 * 60;
    }
    $res = ceil(($endTime - time()) / 30 / 24 / 60 / 60);
    return $res;
  }

  /**
   * 获取投资笔数
   * @param  integer $mode 标的ID
   * @return string
   */
  public function getTenderTime($mode=1) {
    $total = 0;
    if($mode==1) {
      foreach ($this->tenders as $tender) {
        if($tender->type=='invest') {
          $total++;
        }
      }
    }
    if($mode==2) {
      foreach ($this->tenders as $tender) {
        if($tender->status=='1') {
          $total++;
        }
      }
    }
    return $total;
  }

  /**
   * 获取投标比例
   * @return integer 进度(如100,表示100%)
   */
  public function getPercent($remain=false) {
    if(!$this->oddMoney) {
      return 100;
    }
    if($remain===false) {
      $remain = $this->getRemain();
    }
    $per = ($this->oddMoney-$remain)/$this->oddMoney*100;
    return floor($per);
  }

  /**
   * 获取标的期限
   * @return string 标的期限
   */
  public function getPeriod($preWraper='', $aftWraper='') {
    $type = '';
    if($this->oddBorrowStyle=='month') {
      $type = '个月';
    } else if($this->oddBorrowStyle=='day') {
      $type = '天';
    } else if($this->oddBorrowStyle=='week') {
      $type = '周';
    }
    $type = $aftWraper==''?$type:'<'.$aftWraper.'>'.$type.'</'.$aftWraper.'>';
    $period = $preWraper==''?$this->oddBorrowPeriod:'<'.$preWraper.'>'.$this->oddBorrowPeriod.'</'.$preWraper.'>';
    return $period . $type;
  }
   
  /**
   * 获取标的还款方式
   * @return string 标的还款方式
   */
  public function getRepayType() {
    if($this->oddRepaymentStyle=='monthpay') {
      return '<span title="每月利息=借款金额×年化利率/12
项目总利息=每月利息×借款期限" style="cursor: help;">按月付息<span>';
    } else if($this->oddRepaymentStyle=='matchpay') {
      return '<span title="每月还本息=〔借款本金×月利率×(1＋月利率)＾借款期限〕÷〔(1＋月利率)＾借款期限-1〕
月利率=年化利率/12
项目总利息=项目期限×每月还本息-借款本金" style="cursor: help;">等额本息<span>';
    } else {
      return '其他';
    }
  }

  /**
   * 获取标的还款方式
   * @return string 标的还款方式
   */
  public function getRepayTypeName() {
    if($this->oddRepaymentStyle=='monthpay') {
      return '先息后本';
    } else if($this->oddRepaymentStyle=='matchpay') {
      return '等额本息';
    } else {
      return '其他';
    }
  }

  /**
   * 获取最大可投金额
   * @param  models\User $user 用户
   * @return mixed
   */
  public function getMaxInvest($user) {
    $userMoney = 0;
    if($this->oddType=='special') {
        $userMoney = $user->imiMoney;
    } else {
      $userMoney = $user->fundMoney;
      if($this->oddStyle=='newhand') {
        $tenderMoney = OddMoney::where('userId', $user->userId)
            ->whereIn('type', ['invest', 'credit'])
            ->whereHas('odd', function($q) {
              $q->where('oddStyle', 'newhand');
            })->sum('money');
        if((20000-$tenderMoney)>0) {
          $userMoney = 20000-$tenderMoney;
        } else {
          $userMoney = 0;
        }
      } else if($this->investType==1&&$this->oddBorrowPeriod<12) {
        $tenderMoney = OddMoney::where('oddNumber', $this->oddNumber)->where('userId', $user->userId)->whereIn('status', [0, 1])->sum('money');
        if((99999999-$tenderMoney)>0) {
          $userMoney = 99999999-$tenderMoney;
        } else {
          $userMoney = 0;
        }
      }
      if($userMoney>$user->fundMoney) {
        $userMoney = $user->fundMoney;
      }
    }
    $remain = $this->getRemain();
    if($userMoney>=$remain) {
      return $remain;
    } else {
      return intval($userMoney/50)*50;
    }
  }

  /**
   * 获取倒计时秒数
   * @return integer
   */
  public function getOpenSecond() {
    $limitTime = 1800;
    $openTime = strtotime($this->openTime);
    $second = $openTime - time();
    // 已开始
    if($second<0) {
      $second = -1;
    }
    // 未开始
    if($second>$limitTime) {
      $second = 0;
    }
    return $second;
  }

  public function getBadges() {
    $badges = '';
    if($this->oddType=='diya') {
      $badges .= '<span class="badge blue">抵</span>';
    } else if($this->oddType=='xingyong') {
      $badges .= '<span class="badge blue">质</span>';
    } else if($this->oddType=='danbao') {
      $badges .= '<span class="badge blue">融</span>';
    } else if($this->oddType=='special') {
      $badges .= '<span class="badge blue">专</span>';
    } else if($this->oddType=='xiaojin') {
      $badges .= '<span class="badge blue">信</span>';
    }

    if($this->oddStyle=='newhand') {
      $badges .= '<span class="badge yellow">新</span>';
    }

    if($this->investType==1) {
      $badges .= '<span class="badge green">手</span>';
    } else {
      $badges .= '<span class="badge green">自</span>';
    }

    return $badges;
  }

  public function getButton() {
    $btnType = 'btn-primary';
    $word = '';
    $openTime = strtotime($this->openTime);
    $remain = $this->getRemain();
    $btnColor = 'oran-button';
    if($this->progress=='start') {
      if($remain>0) {
        if(time()<$openTime) {
          $btnType = 'btn-warning';
          $word = date('H:i', $openTime).'开始';
        } else {
          if($this->isATBiding==1) {
            $btnType = 'btn-handle';
            $word = '自动投标中';
          } else {
            $word = '立即抢购';
          }
        }
      } else {
        $btnType = 'btn-handle';
        $word = '已投满';
      }
      $btnColor = 'blue-button';
    } else if($this->progress=='review') {
      $btnType = 'btn-handle';
      $word = '复审中';
    } else if($this->progress=='run') {
      $btnType = 'btn-unable';
      $word = '还款中';
    } else {
      $btnType = 'btn-unable';
      $word = '已结束';
    }
    
    $second = $this->getOpenSecond();
    
    return '<a target="_blank" data-second="'.$second.'" href="'
      .WEB_MAIN.'/odd/'.$this->oddNumber.'" class="start-time btn btn-block '.$btnType.' '.$btnColor.'">'.$word.'</a>';
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
          $word = date('H:i', $openTime).'开始';
        } else {
          if($this->isATBiding==1) {
            $word = '自动投标中';
          } else {
            $disabled = 'id="bid-btn"';
            $word = '立即抢购';
          }
        }
      } else {
        $word = '已投满';
      }
    } else if($this->progress=='review') {
      $word = '复审中';
    } else if($this->progress=='run') {
      $word = '还款中';
    } else {
      $word = '已结束';
    }

    if(($this->riskLevel - $user->getEstimateLevel()) > 1 ){
      $risk = 0;
    }elseif(($this->riskLevel - $user->getEstimateLevel()) == 1){
      $risk = -1;
    }else{
      $risk = 1;
    }
    
    $second = $this->getOpenSecond();
    
    return '<button '.$disabled.' class="'.$class.'" '.$data.' data-estimate="'.$score.'" data-paypass="'.($paypass?'1':'-1').'" data-custody="'.($custody?'1':'-1').'" data-second="'.$second.'"'. 'data-isseal="'.$isseal.'"'. 'data-risk="'.$risk.'"' .'>'.$word.'</button>';
  }

  /**
   * 获取类型名称
   * @return string 类型名称
   */
  public function getTypeName($type='long') {
    if(isset(self::$oddTypes[$this->oddType])) {
      $oddType = self::$oddTypes[$this->oddType];
      return $type=='all'?$oddType:$oddType[$type];
    } else {
      return false;
    }
  }

  /**
   * 获取风险等级
   */
  public function getRisk(){
    if($this->riskLevel=='1') {
      $name = '保守型';
    } else if($this->riskLevel=='2') {
      $name = '谨慎型';
    } else if($this->riskLevel=='3') {
      $name = '稳健型';
    } else if($this->riskLevel=='4') {
      $name = '进取型';
    } else{
      $name = '激进型';
    }
    return $name;
  }
  /**
   * 获取进程名称
   * @return string 进程名称
   */
  public function getPRGName($type='word') {
    $name = '';
    $label = '';
    if($this->progress=='start' && $this->successMoney!=$this->oddMoney) {
      $name = '筹款中';
      $label = 'primary';
    } else if($this->progress=='start' && $this->successMoney==$this->oddMoney) {
      $name = '待复审';
      $label = 'info';
    } else if($this->progress=='review') {
      $name = '复审中';
      $label = 'default';
    } else if($this->progress=='run') {
      $name = '还款中';
      $label = 'default';
    } else if($this->progress=='fail') {
      $name = '已失效';
      $label = 'danger';
    } else if($this->progress=='prep') {
      $name = '待发布';
      $label = 'warning';
    } else if($this->progress=='end') {
      $name = '已完结';
      $label = 'default';
    } else if($this->progress=='published') {
      $name = '待初审';
      $label = 'success';
    }
    if($type='html') {
      return '<label class="label label-'.$label.'">'.$name.'</label>';
    }
    return $name;
  }

   /**
   * 获取优先级名称
   * @return string 进程名称
   */
  public function getFron() {
    $name = $this->fronStatus;

    if($this->fronStatus=='1') {
      $name = '普通';
    } else if($this->fronStatus=='2') {
      $name = '优先';
    }
    return $name;
  }

  /**
   * 是否可以复审
   * @return boolean
   */
  public function canRehear() {
    if($this->successMoney==$this->oddMoney && $this->progress=='start') {
      return true;
    } else {
      return false;
    }
  }

  /**
   * 是否可以自动投标
   * @return boolean
   */
  public function canAutoBid() {
    $remain = $this->getRemain();
    if($this->progress=='start' && $this->investType==0 && $remain>0) {
      return true;
    } else {
      return false;
    }
  }

  public static function mergeImages($old, $new) {
    $oldList = [];
    $newList = [];
    if(is_array($old)) {
      $oldList = $old;
    } else {
      $oldList = explode('|', $old);
    }
    if(is_array($new)) {
      $newList = $new;
    } else {
      $newList = explode('|', $new);
    }
    $list = [];
    foreach ($oldList as $img) {
      $normal = '';
      $small = '';
      if(strpos($img, '/data/')>=0) {
        $normal = $img;
        $small = str_replace('_max', '_min', $img);
      } else {
        $normal = $img;
        $small = _thumbnail($img);
      }
      if($normal&&$small) {
        $list[] = $normal . ',' . $small;
      }
    }

    $newSrc = '/uploads/images/';
    foreach ($newList as $img) {
      $normal = $img;
      $small = _thumbnail($img);
      if($normal&&$small) {
        $list[] = $newSrc . $normal . ',' . $newSrc . $small;
      }
    }
    return implode('|', $list);
  }

  public function getImages($column, $return='array', $type='all', $prev=WEB_ASSET) {
    $string = $this->$column;
    $list = explode('|', $string);
    $result = [];
    foreach ($list as $item) {
      $img = explode(',', $item);
      $min = isset($img[1])?$img[1]:'';
      $max = isset($img[0])?$img[0]:'';

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
  
  public static function getTypeValueByName($oddTypeName) {
      $oddTypes = self::$oddTypes;
	    foreach ($oddTypes as $key => $value) {
          if ($value['long'] == $oddTypeName) {
  			return $key;
  		}
		}
		return false;
  }

  public static function periodBack($period) {
  	if (strpos($period, '个月')) {
  		$oddPeriod['oddBorrowStyle'] = 'month';
  		$oddPeriod['oddBorrowPeriod'] = substr($period, 0, -2);
  	} elseif (strpos($period, '周')) {
  		$oddPeriod['oddBorrowStyle'] = 'week';
  		$oddPeriod['oddBorrowPeriod'] = substr($period, 0, -1);
  	} elseif (strpos($period, '天')) {
  		$oddPeriod['oddBorrowStyle'] = 'day';
  		$oddPeriod['oddBorrowPeriod'] = substr($period, 0, -1);
  	} else {
  		return false;
  	}
		return $oddPeriod;
  }

  /**
   * 获取剩余可投金额
   * @return double
   */
  public function getRemain() {
    if($this->progress=='prep') {
      return $this->oddMoney;
    } else if($this->progress=='start') {
      $key = Redis::getKey('oddRemain', ['oddNumber'=>$this->oddNumber]);
      return floatval(Redis::get($key))/100;
    } else {
      return 0;
    }
  }

  /**
   * 投标
   * @param  string $oddNumber  标的编号
   * @param  double $money      投资的金额
   * @return array              消息数组
   */
  public static function bid($oddNumber, $money) {
    $key = Redis::getKey('oddRemain', ['oddNumber'=>$oddNumber]);
    $hanMoney = intval(bcmul($money, 100));
    $remain = Redis::decr($key, $hanMoney);
    $rdata = [];
    if($remain<0) {
      $rdata['status'] = 0;
      $rdata['msg'] = '投资金额超过剩余可投金额！';
      $rdata['remain'] = $remain/100;
      Redis::incr($key, $hanMoney);
    } else if($remain>0&&$remain<5000) {
      $rdata['status'] = 0;
      $rdata['msg'] = '不能使可投金额小于50元！';
      $rdata['remain'] = $remain/100;
      Redis::incr($key, $hanMoney);
    } else {
      $rdata['status'] = 1;
      $rdata['msg'] = '投资成功！';
      $rdata['remain'] = $remain/100;
    }
    return $rdata;
  }

  /**
   * 投资的反操作(用于错误处理)
   * @param  string $oddNumber  标的编号
   * @param  double $money      投资的金额
   * @return double             剩余可投金额
   */
  public static function disBid($oddNumber, $money) {
    $key = Redis::getKey('oddRemain', ['oddNumber'=>$oddNumber]);
    $remain = Redis::incr($key, intval(bcmul($money, 100)));
    return $remain/100;
  }

  /**
   * 获取存管productId
   * @return string productId
   */
  public function getPID() {
    return _ntop($this->oddNumber);
  }
}
