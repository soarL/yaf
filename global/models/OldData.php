<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * OldData|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class OldData extends Model {

	protected $table = 'user_oldinvest';

	public $timestamps = false;

  /**
   * 查询用户旧系统投资金额
   * @param  string $userId   用户userId
   * @return double           用户投资金额
   */
  public static function getTenderMoneyByUser($userId) {
    $user = self::where(['userId'=>$userId])->first();
    if($user) {
      return $user['investmoney'];
    } else {
      return 0;
    }
  }
}