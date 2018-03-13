<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * OldInvest|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class OldInvest extends Model {

	protected $table = 'system_oldinvest';

	public $timestamps = false;

  public function user() {
    return $this->belongsTo('models\User', 'user_id');
  }

  /**
   * 获取用户总共获得的利息
   * @param  string $userId 用户ID
   * @return double         获得的利息
   */
  public static function getTotalInterestByUser($userId) {
    return self::where('user_id', $userId)
      ->where('tender_status', 1)
      ->sum('recover_account_interest_yes');
  }
}