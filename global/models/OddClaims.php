<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * OddClaims|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class OddClaims extends Model {
  const SN_PRE = 90000000;

	protected $table = 'work_oddclaims';

	public $timestamps = false;

  public function odd() {
    return $this->belongsTo('models\Odd', 'oddNumber');
  }

  public function oddMoney() {
    return $this->belongsTo('models\OddMoney', 'oddmoneyId');
  }

  public function fromClaim() {
    return $this->belongsTo('models\OddClaims', 'idFrom');
  }

  public function toClaim() {
    return $this->hasOne('models\OddClaims', 'idFrom');
  }

  public function user() {
    return $this->belongsTo('models\User', 'userId');
  }

  /**
   * 获取用户进行中的债权
   * @param  string $userId 用户ID
   * @return Database         查询对象
   */
  public static function getIngBuilder($userId) {
    $builder = OddClaims::with('odd.interests', 'oddMoney.invests')
      ->where('status', '0')
      ->where('type', 'out')
      ->where('userId', $userId)
      ->whereHas('odd', function($q) {
        $q->where('progress', 'run');
      })->orderBy('addtime', 'desc');
    return $builder;

  }

  /**
   * 获取用户已转让的债权
   * @param  string $userId 用户ID
   * @return Database         查询对象
   */
  public static function getOutBuilder($userId) {
    $builder = OddClaims::with('odd.interests', 'oddMoney.invests', 'toClaim')
      ->where('status', '-1')
      ->where('type', 'out')
      ->where('userId', $userId)
      ->orderBy('addtime', 'desc');
    return $builder;
  }

  /**
   * 获取用户已购买的债权
   * @param  string $userId 用户ID
   * @return Database         查询对象
   */
  public static function getBuyBuilder($userId) {
    $builder = OddClaims::with('odd.interests', 'oddMoney.invests')
      ->where('status', '1')
      ->where('type', 'in')
      ->where('userId', $userId)
      ->orderBy('addtime', 'desc');
    return $builder;

  }

  /**
   * 获取用户回收中的债权
   * @param  string $userId 用户ID
   * @return Database         查询对象
   */
  public static function getBackBuilder($userId) {
    $builder = OddClaims::with('odd.interests', 'oddMoney.invests')
      ->where('status', '1')
      ->where('type', 'in')
      ->where('userId', $userId)
      ->whereHas('odd', function($q) {
        $q->where('progress', 'run');
      })->orderBy('addtime', 'desc');
    return $builder;
  }

  /**
   * 获取已经撤销的债权
   * @param  string $userId 用户ID
   * @return Database         查询对象
   */
  public static function getDelBuilder($userId) {
    $builder = OddClaims::with('odd.interests', 'oddMoney.invests')
      ->where('status', '-2')
      ->where('type', 'out')
      ->where('userId', $userId)
      ->orderBy('addtime', 'desc');
    return $builder;
  }

  /**
   * 获取可购买债权数量
   * @return int
   */
  public static function getCanBuyNum() {
    return self::where('type', 'out')
      ->where('status', 0)
      ->count();
  }

  public static function getRecordBuilder() {
    $builder = OddClaims::with('odd.interests', 'oddMoney.invests')
      ->where('type', 'out')
      ->whereRaw('(status=? or status=?)', [0, -1])
      ->whereHas('odd', function($q){
          $q->where('progress', 'run');
        })
      ->orderByRaw('field(status, ?, ?)', [0, -1])
      ->orderBy('addtime', 'desc');
    return $builder;
  }

  /**
   * 获取指定债权的债权链
   * @param  integer $oddMoneyId  oddMoneyId
   * @return array   信息
   */
  public static function getClaimChain($oddMoneyId) {
    $chain = [];
    $sellClaim = self::where('oddmoneyId', $oddMoneyId)->where('status', -1)->where('type', 'out')->orderBy('id', 'asc')->first();
    $oddMoney = OddMoney::find($oddMoneyId);
    $chain[] = ['userId'=>$sellClaim->userId, 'getTime'=>$oddMoney->time];
    $claims = self::where('oddmoneyId', $oddMoneyId)->where('status', 1)->where('type', 'in')->orderBy('id', 'asc')->get(['userId', 'addtime']);
    foreach ($claims as $claim) {
      $row = [];
      $row['userId'] = $claim->userId;
      $row['getTime'] = $claim->addtime;
      $chain[] = $row;
    }
    return $chain;
  }

  /**
   * 获取指定债权的债权链组
   * @param  array $oddMoneyIds  oddMoneyId数组
   * @return array              信息
   */
  public static function getClaimChains($oddMoneyIds) {
    $chains = [];
    if(count($oddMoneyIds)>0) {
      $sellClaims = self::whereIn('oddmoneyId', $oddMoneyIds)->where('status', -1)->where('type', 'out')->orderBy('id', 'asc')->get();
      $oddMoneys = OddMoney::whereIn('id', $oddMoneyIds)->get();
      foreach ($sellClaims as $sellClaim) {
        if(!isset($chains[$sellClaim->oddmoneyId])) {
          $chains[$sellClaim->oddmoneyId][] = ['userId'=>$sellClaim->userId, 'getTime'=>$sellClaim->addtime];
        }
      }
      foreach ($oddMoneys as $oddMoney) {
        $chains[$oddMoney->id][0]['getTime'] = $oddMoney->time;
      }

      $claims = self::whereIn('oddmoneyId', $oddMoneyIds)
        ->where('status', 1)
        ->where('type', 'in')
        ->orderBy('id', 'asc')
        ->get(['userId', 'addtime', 'oddmoneyId']);
      foreach ($claims as $claim) {
        $row = [];
        $row['userId'] = $claim->userId;
        $row['getTime'] = $claim->addtime;
        $chains[$claim->oddmoneyId][] = $row;
      }
    }
    return $chains;
  }

  /**
   * 获取该笔债权的待收总额
   * @return double
   */
  public function getStayMoney() {
    $total = 0;
    foreach ($this->oddMoney->invests as $invest) {
      if($invest->status==0) {
        $total += $invest->zongEr;
      }
    }
    return $total;
  }

  /**
   * 获取该笔债权的最后回款时间
   * @return string
   */
  public function getEndTime() {
    $last = null;
    foreach ($this->odd->interests as $interest) {
      if($last) {
        if($interest->qishu > $last->qishu) {
          $last = $interest;
        }
      } else {
        $last = $interest;
      }
    }
    return $last->endtime;
  }

  /**
   * 获取合同信息
   * @return array           合同信息
   */
  public function getProtocolInfo() {
    $protocolData = false;
    $odd = $this->odd;
    if($this->status==-1) {
      $fromClaim = $this;
      $toClaim = $this->toClaim;
    } else if($oddClaim['status']==1) {
      $fromClaim = $this->fromClaim;
      $toClaim = $this;
    }
    $sellNum = 90000000 + $fromClaim->id;
    $buyNum = 90000000 + $toClaim->id;
    $proSerial = $sellNum . '_' . $buyNum;
    $protocolData['proSerial'] = $proSerial;
    $protocolData['sellUser'] = $fromClaim->user->username;
    $protocolData['buyUser'] = $toClaim->user->username;
    $protocolData['time'] = $toClaim->addtime;
    $protocolData['money'] = $toClaim->claimsMoney;
    $protocolData['oddUser'] = $odd->user->username;
    $protocolData['oddTitle'] = $odd->oddTitle;
    $protocolData['oddYearRate'] = $odd->oddYearRate;
    return $protocolData;
  }

  /**
   * 分析债权的信息
   * @return array            信息
   */
  public function analysis() {
    $toClaim = $this->toClaim;
    $oddMoney = $this->oddMoney;
    $odd = $this->odd;

    $stayMoney = 0;
    $stayInterest = 0;
    $stayPrincipal = 0;
    $repaymentDate = ['over'=>[], 'repayment'=>[]];

    $records = Invest::where('oddMoneyId', $this->oddmoneyId)->orderBy('endtime', 'asc')->get();

    if($this->status==0) {
      foreach ($records as $key => $record) {
        if($record->status==0) {
          $stayMoney += $record->zongEr;
          $stayInterest += $record->interest;
          $stayPrincipal += $record->benJin;
          $repaymentDate['repayment'][] = date('Y/m/d',strtotime($record->endtime));
        } else {
          $repaymentDate['over'][] = date('Y/m/d',strtotime($record->endtime));
        }
      }
    } else {
      foreach ($records as $key => $record) {
        if(strtotime($record->endtime)>strtotime($toClaim->addtime)) {
          $stayMoney += $record->zongEr;
          $stayInterest += $record->interest;
          $stayPrincipal += $record->benJin;
          $repaymentDate['repayment'][] = date('Y/m/d',strtotime($record->endtime));  
        } else {
          $repaymentDate['over'][] = date('Y/m/d',strtotime($record->endtime));
        }
        
      }
    }

    $endTime = $this->odd->getEndTime();

    // 折让金 = 债权本价 - 转让金额
    $zherang = $oddMoney->money - $this->claimsMoney;

    // 投标奖励
    $reward = Reward::getTenderReard($this->userId, $this->oddmoneyId);

    // 认购收益 = 折让金 + 剩余未结利息 + 项目奖励 - 未结利息管理费用
    $rengou = $zherang + $stayInterest + $reward - $stayInterest*0.1;

    // 剩余投资时间
    $remindTime = 0;
    if($this->status==0) {
      $remindTime = strtotime($endTime)-time();
    } else {
      $remindTime = strtotime($endTime) - strtotime($toClaim->addtime);
    }

    // 剩余投资天数
    $remindDay = 0;
    if($remindTime>0) {
      $remindDay = intval($remindTime/(24*60*60));
    }

    // 多赚或亏损 = 认购收益 - 本金 × 每日收益率 × 剩余投资天数
    $duozhuan = $rengou-$oddMoney->money*($odd->oddYearRate/360)*0.9*$remindDay;

    $info = [];
    $info['endTime'] = $endTime;
    $info['stayMoney'] = $stayMoney;
    $info['stayInterest'] = $stayInterest;
    $info['stayPrincipal'] = $stayPrincipal;
    $info['repaymentDate'] = $repaymentDate;
    $info['zherang'] = $zherang;
    $info['rengou'] = $rengou;
    $info['remindDay'] = $remindDay;
    $info['duozhuan'] = $duozhuan;
    $info['reward'] = $reward;
    return $info;
  }

  public function getSN() {
    return self::SN_PRE + $this->id;
  }
}