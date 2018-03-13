<?php
namespace forms;
use models\Question;
use models\QuestionAnswer;
use models\Mail;
use helpers\StringHelper;
use Yaf\Registry;

/**
 * QuestionAnswerForm
 * 用户回答表单类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class QuestionAnswerForm extends \Form {
	const AUTO_PUBLISH = true;
	public $question;
	public $answer;

	public function rules() {
		return [
			[['answerContent', 'questionId'], 'required'],
			['questionId', 'validateQuestionId'],
			['questionId', 'validateCanAnswer'],
			['answerContent', 'validateAnswerContent'],
		];
	}

	public function labels() {
		return [
        	'questionId' => '问题ID',
        	'answerContent' => '回答内容',
        ];
	}

	public function validateQuestionId() {
		$question = Question::where('id', $this->questionId)->where('status', Question::STATUS_ACTIVE)->first();
		$this->question = $question;
		if(!$question) {
			$this->addError('questionId', '问题不存在或已关闭！');
		}
	}

	public function validateAnswerContent() {

	}

	public function validateCanAnswer() {
		$time = date('Y-m-d H:i:s', (time()-30));
		$params = [];
		$params[] = $time;
		$params[] = $this->questionId;
		$recCount = QuestionAnswer::whereRaw('answerTime>=? and questionId=?', $params)->count();
		if($recCount>0) {
			$this->addError('form', '回答间隔少于30秒，请稍后再提问！');
		}
	}

	public function answer() {
		if($this->check()) {
			$user = $this->getUser();
			$answer = new QuestionAnswer();
        	$answer->username = $user->username;
			$answer->questionId = $this->questionId;
        	$answer->content = StringHelper::filterCensorWord($this->answerContent);
        	$answer->answerTime = date('Y-m-d H:i:s');
        	$answer->status = 0;
			if(self::AUTO_PUBLISH) {
        		$answer->status = 1;
        	}
			if($answer->save()) {
				$this->answer = $answer;
				if(self::AUTO_PUBLISH) {
					$this->question->answerCount = $this->question->answerCount + 1;
					$this->question->lastAnswerUser = $user->username;
					$this->question->lastAnswerTime = $answer->answerTime;
					$this->question->save();

					$mail = new Mail();
					$mail->title = '您的问题有新的回答！';
					$mail->content = '您的回答【<a href="' . WEB_MAIN . '/question/view?id='.$this->question->id
						.'" target="_blank">'.$this->question->title.'</a>】有新的回复！';
					$mail->status = 1;
					$mail->addTime = date('Y-m-d H:i:s');
					$mail->sendUser = 'system';
					$mail->receiveUser = $this->question->username;
					$mail->save();
				}
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