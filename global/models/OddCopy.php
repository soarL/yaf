<?php
namespace models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;
use Yaf\Registry;
use helpers\DateHelper;

/**
 * Odd|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class OddCopy extends Model {

  /**
   * 生成合同状态（二进制判断）
   */
  const CER_STA_PR = 1;

  /**
   * 发送安存状态（二进制判断）
   */
  const CER_STA_AC = 2;

	protected $table = 'work_odd_copy';

  protected $primaryKey = 'oddNumber';
  
  /*protected $casts = [
    'oddNumber' => 'string'
  ];*/

  public $incrementing = false;

  public $timestamps = false;

  public static $oddTypes = [1=>'diya',2=>'xingyong',3=>'danbao',4=>'newhand',5=>'special'];

  public static $name = [
    'oddType' => '标的类型',
    'oddTitle' => '借款标题',
    //'oddUse' => '借款用途',
    'oddYearRate' => '年化率',
    'oddMoney' => '借款金额',
    'startMoney' => '起投金额',
    'oddMultiple' => '杠杆倍数',
    'oddBorrowStyle' => '月标/天标',
    'oddRepaymentStyle' => '还款类型',
    'oddBorrowPeriod' => '借款期限',
    'oddBorrowValidTime' => '筹标期限',
    'oddExteriorPhotos' => '外观图片',
    'oddPropertyPhotos' => '产权图片',
    'bankCreditReport' => '征信图片',
    'otherPhotos' => '借款手续',
    'oddLoanRemark' => '借款描述',
    'oddLoanServiceFees' => '借款服务费',
    'userId' => '借款人',
    'oddLoanControlList' => '风控资料列表',
    'oddLoanControl' => '风控说明',
    'oddGarageName' => '车库名称',
    //'oddGarageNum' => '车库编号',
    'investType' => '自动/手动',
    'openTime' => '发标时间',
    'controlPhotos' => '风控图片',
    'validateCarPhotos' => '验车图片',
    'contractVideoUrl' => '签约视频URL',
    'chepai' => '车牌',
    'oddReward' => '奖励利率',
    'oddStyle' => '新手/普通',
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
    'success' => ['run', 'end', 'fron', 'delay'],
    'finished' => ['end', 'fron', 'delay'],
    'fail' => ['fail'],
    'repaying' => ['run'],
    'biding' => ['start'],
    'prepare' => ['prep'],
  ];

  /**
   * 借款对比
   * @return mixed
   */
  public static function diff($a,$b){
      $b = array_diff_assoc($a,$b);
      $c['key'] = self::check(array_keys($b));
      if($c['key'] == ''){
        return null;
      }
      $c['addtime'] = $a['addtime'];
      $c['oddNumber'] = $a['oddTitle'];
      $c['operator'] = DB::table('system_userinfo')->where('userId',$a['operator'])->first()->username;
      return $c;
  }

  public static function check($array){
    foreach ($array as $value) {
      if(!empty(self::$name[$value])){
        $result[] = self::$name[$value];
      }
    }
    return implode('，', $result);
  }
}