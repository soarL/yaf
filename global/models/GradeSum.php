<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * GradeSum|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class GradeSum extends Model {

	protected $table = 'work_gradeSum';

	public $timestamps = false;

	/**
	 * 推荐人
	 * @return Builder
	 */
	public function reference() {
		return $this->belongsTo('models\User', 'friend');
	}

	/**
	 * 客户-指被推荐人
	 * @return Builder
	 */
	public function client() {
		return $this->belongsTo('models\User', 'userId');
	}

	/**
	 * 被推荐人设置
	 * @return Builder
	 */
	public function clientSetting() {
		return $this->belongsTo('models\UserSetting', 'userId', 'userId');
	}

}