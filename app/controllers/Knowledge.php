<?php
// This controller use illuminate/database.
use exceptions\HttpException;
use models\Odd;
use models\Article;
use traits\PaginatorInit;

/**
 * KnowledgeController
 * 财富文库控制器
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class KnowledgeController extends Controller {
	use PaginatorInit;

	public $menu = 'knowledge';
	public $submenu = 'knowledge';

	/**
	 * 首页
	 * @return mixed
	 */
	public function indexAction() {
		$types = Article::types();
		$this->display('index', ['types'=>$types]);
	}

	/**
	 * 列表页
	 * @return mixed
	 */
	public function listAction() {
		$queries = $this->queries->defaults(['type'=>'众筹知识']);

		$where = ['rootType' => $queries->type];

		$types = Article::types();
		$articles = Article::where($where)->orderBy('id', 'desc')->paginate();
		$articles->appends($queries->all());

		$user = $this->getUser();
        $userId = $user?$user->userId:null;

        $builder = Odd::getListBuilder($userId);
        $builder = Odd::sortList($builder);
        $odds = $builder->limit(3)->get();
		$this->display('list', ['types'=>$types, 'articles'=>$articles, 'queries'=>$queries, 'odds'=>$odds]);
	}

	/**
	 * 子页
	 * @return mixed
	 */
	public function viewAction($id=0) {
		$id = intval($id);
		if($id==0) {
			throw new HttpException(404);
		}

		$article = Article::find($id);
		if(!$article) {
			throw new HttpException(404);	
		}
		
		$user = $this->getUser();
        $userId = $user?$user->userId:null;

        $builder = Odd::getListBuilder($userId);
        $builder = Odd::sortList($builder);
        $odds = $builder->limit(3)->get();
        
		if($article['addtime']!=null && strtotime($article['addtime'])<strtotime('2017-05-08 00:00:00')) {
			$this->title = $article['title'].' - 汇诚普惠 hcjrfw.com - 融资租赁 - 车贷平台 - 车贷p2p - 福建网贷';
		} else {
			$this->title = $article['title'].'_汇诚普惠网贷投资的可靠平台';
		}

        $types = Article::types();
		$this->display('view', ['types'=>$types, 'article'=>$article, 'odds'=>$odds]);
	}
}