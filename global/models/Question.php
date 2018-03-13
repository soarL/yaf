<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * Question|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Question extends Model {
	const STATUS_ACTIVE = 1;
	
	protected $table = 'user_question';

	public $timestamps = false;

	public function user() {
		return $this->belongsTo('models\User', 'username', 'username');
	}

	public static function getLastAnswer($id) {
		$answer = QuestionAnswer::where('questionId', $id)->first();
		return $answer;
	}
}