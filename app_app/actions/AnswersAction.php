<?php
use models\News;
use models\Question;
use models\QuestionAnswer;
use traits\handles\ITFAuthHandle;

/**
 * AnswersAction
 * APP问题详情接口
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class AnswersAction extends Action {
    use ITFAuthHandle;

    public function execute() {
    	$params = $this->getAllQuery();
    	$this->authenticate($params, ['id'=>'问题ID']);
        
        $this->pv('ab');

        $id = $this->getQuery('id', 0);

        $question = Question::find($id);
        $hitCount = $question->hitCount + 1;
        $question->save();

        $answers = QuestionAnswer::with('replies.user', 'user')->where('questionId', $id)->where('parentId', 0)->where('status', 1)->get();

        $info = [];
        $info['id'] = $question->id;
        $info['title'] = $question->title;
        $info['content'] = $question->content;
        $info['time'] = $question->addTime;
        $info['username'] = $question->username;
        $info['photo'] = $question->user->getPhoto();
        
        $list = [];
        foreach ($answers as $answer) {
            $row = [];
            $row['id'] = $answer->id;
            $row['username'] = $answer->username;
            $row['photo'] = $answer->user->getPhoto();
            $row['time'] = $answer->answerTime;
            $row['content'] = $answer->content;
            $row['useful'] = $answer->usefulCount;
            $replies = [];
            foreach ($answer->replies as $key => $reply) {
                $record = [];
                $record['id'] = $reply->id;
                $record['username'] = $reply->username;
                $record['photo'] = $reply->user->getPhoto();
                $record['time'] = $reply->answerTime;
                $record['content'] = $reply->content;
                $replies[] = $record;
            }
            $row['replies'] = $replies;
            $list[]  = $row;
        }
        $info['answers'] = $list;

        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data'] = $info;
        $this->backJson($rdata);
    }
}