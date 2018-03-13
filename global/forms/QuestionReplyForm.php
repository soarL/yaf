<?php
namespace forms;
use models\Question;
use models\QuestionAnswer;
use models\Mail;
use Yaf\Registry;
use helpers\StringHelper;
use helpers\DateHelper;

/**
 * QuestionReplyForm
 * 用户回复表单类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class QuestionReplyForm extends \Form {
	const AUTO_PUBLISH = true;
	public $question;
	public $answer;
	public $reply;

	public function rules() {
		return [
			[['replyContent', 'questionId', 'answerId'], 'required'],
			['questionId', 'validateQuestionId'],
			['questionId', 'validateCanAnswer'],
			['answerId', 'validateAnswerId'],
			['replyContent', 'validateReplyContent'],
		];
	}

	public function labels() {
		return [
        	'questionId' => '问题ID',
        	'answerId' => '答案ID',
        	'replyContent' => '回复内容',
        ];
	}

	public function validateQuestionId() {
		$question = Question::where('id', $this->questionId)->where('status', Question::STATUS_ACTIVE)->first();
		if(!$question) {
			$this->addError('questionId', '问题不存在或已关闭！');
		} else {
			$this->question = $question;
		}
	}

	public function validateReplyContent() {
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

	public function validateAnswerId() {
		$answer = QuestionAnswer::whereRaw('parentId=0 and id=?', [$this->answerId])->first();
		$this->answer = $answer;
		if(!$answer) {
			$this->addError('answerId', '回答不存在！');
		}
	}

	public function reply() {
		if($this->check()) {
			$user = $this->getUser();
			$reply = new QuestionAnswer();
        	$reply->username = $user->username;
			$reply->questionId = $this->questionId;
			$reply->parentId = $this->answerId;
        	$reply->content = StringHelper::filterCensorWord($this->replyContent);
        	$reply->answerTime = date('Y-m-d H:i:s');
        	$reply->status = 0;
        	if(self::AUTO_PUBLISH) {
        		$reply->status = 1;
        	}
			if($reply->save()) {
				if(self::AUTO_PUBLISH) {
					$this->answer->replyCount = $this->answer->replyCount + 1;
					$this->answer->save();

					$data = [];
					$data['username'] = StringHelper::getHideUsername($reply->username);
					$data['content'] = $reply->content;
					$data['answerTime'] = DateHelper::getTimeDistance(strtotime($reply->answerTime));
					$this->reply = $data;

					$this->question->answerCount = $this->question->answerCount + 1;
					$this->question->lastAnswerUser = $user->username;
					$this->question->lastAnswerTime = $reply->answerTime;
					$this->question->save();

					// 发送邮件给回答问题的人
					if($this->question->type!='ceo') {
						$mail = new Mail();
						$mail->title = '您的回答有新的回复！';
						$mail->content = '您的回答【<a href="'.WEB_MAIN.'/question/view?id='.$this->question->id
							.'" target="_blank">'.StringHelper::newsTitle($this->answer->content, 18).'</a>】有新的回复！';
						$mail->status = 1;
						$mail->addTime = date('Y-m-d H:i:s');
						$mail->sendUser = 'system';
						$mail->receiveUser = $this->answer->username;
						$mail->save();
					}
					
					// 发送邮件给提出问题的人
					$mail = new Mail();
					$mail->title = '您的问题有新的回复！';
					$mail->content = '您的回答【<a href="'.WEB_MAIN.'/question/view?id='.$this->question->id
						.'" target="_blank">'.$this->question->title.'</a>】有新的回复！';
					$mail->status = 1;
					$mail->addTime = date('Y-m-d H:i:s');
					$mail->sendUser = 'system';
					$mail->receiveUser = $this->question->username;
					$mail->save();
				}
				return true;
			} else {
				$this->addError('form', '回复失败！');
				return false;
			}
		} else {
			return false;
		}
	}
}