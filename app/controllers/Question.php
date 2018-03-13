<?php
use models\Question;
use models\QuestionAnswer;
use models\UserSign;
use models\QuestionAnswerUseful;
use forms\QuestionForm;
use forms\QuestionReplyForm;
use forms\QuestionAnswerForm;
use helpers\StringHelper;
use tools\Pager;
use tools\Scws;
use traits\PaginatorInit;
/**
 * QuestionController
 * 用户提问控制器
 * 搜索使用 sphinx检索引擎 + scws分词
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class QuestionController extends Controller {
	use PaginatorInit;

	public $menu = 'question';

	/**
	 * 问题列表
	 * @return mixed
	 */
	public function listAction() {
		$user = $this->getUser();
		$queries = $this->queries->defaults(['type'=>'all']);
		$type = $queries->type;
		$builder = Question::where('status', Question::STATUS_ACTIVE);
		if($type=='ceo') {
			$builder->where('type', 'ceo');
		}
		$hotBuilder = clone $builder;
		$hotsBuilder = clone $builder;

		$hotQuestion = $hotBuilder->orderBy('sort', 'desc')->orderBy('hitCount', 'desc')->orderBy('addTime', 'desc')->first();
		
		if($type=='hot') {
			$builder->orderBy('hitCount', 'desc')->orderBy('addTime', 'desc');
		} else {
			$builder->orderBy('sort', 'desc')->orderBy('addTime', 'desc');
		}

		$questions = $builder->paginate(8);
		$questions->appends($queries->all());

		$rankingList = QuestionAnswer::getRanking();
		$signCount = UserSign::getSignCount();
		$isUserSign = UserSign::isUserSign($user);

		$hitQuestions = $hotsBuilder->orderBy('hitCount', 'desc')->limit(3)->get();
		
		$this->display('list', [
			'user'=>$user, 
			'questions'=>$questions, 
			'hotQuestion'=>$hotQuestion, 
			'hitQuestions'=>$hitQuestions, 
			'rankingList'=>$rankingList,
			'signCount'=>$signCount,
			'isUserSign'=>$isUserSign,
			'queries'=>$queries
		]);
	}

	/**
	 * 问题内容
	 * @return mixed
	 */
	public function viewAction() {
		$id = $this->getQuery('id', 0);
		$user = $this->getUser();
		$question = Question::find($id);
		$hitCount = $question->hitCount + 1;
		$question->save();

		$queries = $this->queries;
		$answers = QuestionAnswer::with('replies')->where('questionId', $id)->where('parentId', 0)->where('status', 1)->paginate();
		$answers->appends($queries->all());

		$rankingList = QuestionAnswer::getRanking();
		$signCount = UserSign::getSignCount();
		$isUserSign = UserSign::isUserSign($user);
		$hitQuestions = Question::orderBy('hitCount', 'desc')->limit(8)->get();
		
		if(strtotime($question['addTime'])<strtotime('2017-05-08 00:00:00')) {
			$this->title = $question['title'].' - 汇诚普惠 hcjrfw.com - 融资租赁 - 车贷平台 - 车贷p2p - 福建网贷';
		} else {
			$this->title = $question['title'].'_汇诚普惠网贷投资的可靠平台';
		}

		$this->display('view', [
			'user'=>$user, 
			'question'=>$question, 
			'answers'=>$answers, 
			'rankingList'=>$rankingList,
			'signCount'=>$signCount,
			'isUserSign'=>$isUserSign,
			'hitQuestions'=>$hitQuestions
		]);
	}

	/**
	 * 提问 (ajax)
	 * @return mixed
	 */
	public function askAction() {
		$params = $this->getRequest()->getPost();
		$form  = new QuestionForm($params);
		$rdata = [];
		if($form->ask()) {
			$rdata['status'] = 1;
			if($form->question['status']==Question::STATUS_ACTIVE) {
				Flash::success('提问成功！');
			} else {
				Flash::success('提问成功，等待审核！');
			}
			$this->backJson($rdata);
		} else {
			$rdata['status'] = 0;
			$rdata['info'] = $form->posError();
			$this->backJson($rdata);
		}
	}

	/**
	 * 回答问题
	 * @return mixed
	 */
	public function answerAction() {
		$params = $this->getRequest()->getPost();
		$form  = new QuestionAnswerForm($params);
		if($form->answer()) {
			if($form->answer['status']==1) {
				Flash::success('回答成功！');
			} else {
				Flash::success('回答成功，等待审核！');
			}
		} else {
			Flash::error($form->posError());
		}
		$questionId = $form->questionId;
		$this->redirect(Url::to('/question/view?id='.$questionId));
	}

	/**
	 * 回复答案 (ajax)
	 * @return mixed
	 */
	public function replyAction() {
		$params = $this->getRequest()->getPost();
		$form  = new QuestionReplyForm($params);
		$rdata = [];
		if($form->reply()) {
			$reply = [];
			$rdata['status'] = 1;
			$rdata['reply'] = $form->reply;
			$this->backJson($rdata);
		} else {
			$rdata['info'] = $form->posError();
			$rdata['status'] = 0;
			$this->backJson($rdata);
		}
	}

	/**
	 * 点击有用 (ajax)
	 * @return mixed
	 */
	public function usefulAction() {
		$answerId = $this->getPost('answerId', 0);
		$user = $this->getUser();
		$row = QuestionAnswerUseful::where('username', $user->username)->where('answerId', $answerId)->first();
		$rdata = [];
		if($row) {
			$rdata['status'] = 0;
			$rdata['info'] = '您已经支持过该答案了！';
			$this->backJson($rdata);
		}
		$answer = QuestionAnswer::find($answerId);
		if(!$answer) {
			$rdata['status'] = 0;
			$rdata['info'] = '答案不存在！';
			$this->backJson($rdata);
		}
		$useful = new QuestionAnswerUseful();
		$useful->username = $user->username;
		$useful->answerId = $answerId;
		$useful->addTime = date('Y-m-d H:i:s');
		if($useful->save()) {
			$answer->usefulCount = $answer->usefulCount + 1;
			$answer->save();
			$rdata['status'] = 1;
			$rdata['info'] = '支持成功！';
		} else {
			$rdata['status'] = 0;
			$rdata['info'] = '支持失败！';
		}
		$this->backJson($rdata);
	}

	/**
	 * 签到 (ajax)
	 * @return mixed
	 */
	public function signAction() {
		$result = UserSign::sign($this->getUser());
		$rdata = [];
		if($result['status']==1) {
			$rdata['status'] = 1;
			$rdata['info'] = '签到成功！';
			$this->backJson($rdata);
		} else {
			$rdata['status'] = 0;
			$rdata['info'] = $result['info'];
			$this->backJson($rdata);
		}
	}

	/**
	 * 问题查询
	 * @return mixed
	 */
	public function searchAction() {
		$keyword = trim($this->getQuery('keyword', ''));
		$index = "questions";
		//========================================分词
		$words = "";
		$wordList = [];
		if($keyword!='') {
			$keyword = str_replace(' ', '', $keyword);
			$scws = new Scws(['text'=>$keyword]);
			$scws->setMulti(true);
			$wordList = $scws->getWords();
			
			foreach($wordList as $w) {
				$words = $words.'|('.$w['word'].')';
			}
			$words = trim($words,'|');
		}

		//========================================搜索
		$sc = new SphinxClient();
		$sc->SetServer('120.76.166.26',9312);
		// $sc->SetServer('127.0.0.1',9312);
		#$sc->SetMatchMode(SPH_MATCH_ALL);
		$sc->SetMatchMode(SPH_MATCH_EXTENDED2);
		$sc->SetArrayResult(TRUE);

		$pageSize = 8;
		$page = $this->getRequest()->getQuery('page', 1);
		$offset = $pageSize * ($page - 1);
		
		$sc->setLimits($offset, $pageSize, 1000);
		$res = $sc->Query($words,$index);
		$pager = new Pager(['total'=>$res['total'], 'request'=>$this->getRequest(), 'pageSize'=>$pageSize]);

		$user = $this->getUser();
		$rankingList = QuestionAnswer::getRanking();
		$signCount = UserSign::getSignCount();
		$isUserSign = UserSign::isUserSign($user);
		$opts = array(  
		    'before_match' => '<span style="color:#ff0000;font-weight:bold;">',
		    'after_match' => '</span>',
		    'chunk_separator' => '...',
		    'limit'=> 60, 
		    'around' => 5,
		    'single_passage' => true,
		    'exact_phrase' => false  
		);
		$titleList = [];
		$contentList = [];
		foreach ($res['matches'] as $key => $row) {
			$titleList[$key] = $row['attrs']['title'];
			$contentList[$key] = $row['attrs']['content'];
		}
		$titleList = $sc->buildExcerpts($titleList , $index , $words, $opts);
		$contentList = $sc->buildExcerpts($contentList , $index , $words, $opts);
		$questions = [];
		foreach ($res['matches'] as $key => $row) {
			$row['attrs']['title'] = $titleList[$key];
			$row['attrs']['content'] = $contentList[$key];
			$questions[] = $row['attrs'];
		}
		$this->display('search', [
			'pager'=>$pager, 
			'user'=>$user,
			'questions'=>$questions,
			'rankingList'=>$rankingList,
			'signCount'=>$signCount,
			'isUserSign'=>$isUserSign,
			'total'=>$res['total']
		]);
	}
}