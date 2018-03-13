<?php
namespace models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * QuestionAnswer|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class QuestionAnswer extends Model {

	protected $table = 'user_question_answer';

	public $timestamps = false;

	public function question() {
		return $this->belongsTo('models\Question', 'questionId');
	}

	public function replies() {
		return $this->hasMany('models\QuestionAnswer', 'parentId');
	}

	public function user() {
		return $this->belongsTo('models\User', 'username', 'username');
	}

	/**
	 * 获取问题回答排行榜
	 * @return  array  排行榜数据
	 */
	public static function getRanking() {
		$rankingList = self::with('question')
			->where('parentId', 0)
			->where('status', 1)
			->where('username', '<>', '平台客服')
			->where('username', '<>', '汇诚普惠平台')
			->whereHas('question', function($q){
				$q->where('type', 'normal');
			})
			->groupBy('username')
			->orderBy('userAnswerCount', 'desc')
			->limit(10)
			->get(['username', DB::raw('count(*) userAnswerCount')]);
		return $rankingList;
	}
}