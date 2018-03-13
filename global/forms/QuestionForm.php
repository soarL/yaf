<?php
namespace forms;
use models\Question;
use Yaf\Registry;

/**
 * QuestionForm
 * 用户提问表单类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class QuestionForm extends \Form {
	const AUTO_PUBLISH = false;
	public $question;
	public function rules() {
		return [
			[['title', 'content', 'type'], 'required'],
			['title', 'validateTitle'],
			['content', 'validateContent'],
			['type', 'enum', ['values'=>['normal', 'ceo']]],
		];
	}

	public function labels() {
		return [
        	'title' => '问题标题',
        	'content' => '问题内容',
        ];
	}

	public function validateTitle() {

	}

	public function validateContent() {
		
	}

	public function validateCanAsk() {
		$beginTime = date('Y-m-d').' 00:00:00';
		$todayCount = Question::where('addTime', '>=', $beginTime)->first();
		if($todayCount>=20) {
			$this->addError('form', '每天最多只能提问20个问题！'); return;
		}
		$time = date('Y-m-d H:i:s', (time()-2*60));
		$recCount = Question::where('addTime', '>=', $time)->count();
		if($recCount>0) {
			$this->addError('form', '提问间隔少于2分钟，请稍后再提问！'); return;
		}
	}

	public function ask() {
		if($this->check()) {
			$user = $this->getUser();
			$question = new Question();
        	$question->username = $user->username;
			$question->title = $this->title;
        	$question->content = $this->content;
        	$question->type = $this->type;
        	$question->addTime = date('Y-m-d H:i:s');
        	$question->status = 0;
        	if(self::AUTO_PUBLISH) {
        		$data->status = Question::STATUS_ACTIVE;
        	}
			if($question->save()) {
				$this->question = $question;
				return true;
			} else {
				$this->addError('form', '提问失败！');
				return false;
			}
		} else {
			return false;
		}
	}
}