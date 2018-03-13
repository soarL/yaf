<?php
// This controller use illuminate/database.
use exceptions\HttpException;
use models\News;
use models\NewsType;
use traits\PaginatorInit;

/**
 * NewsController
 * 新闻资讯控制器
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class NewsController extends Controller {
	use PaginatorInit;

	public $menu = 'news';

	/**
	 * 新闻列表页面
	 * @return mixed
	 */
	public function listAction() {
		$queries = $this->queries->defaults(['type'=>'news', 'keyword'=>'']);

		$query = News::whereRaw('news_type=?', [$queries->type]);
		if($queries->keyword!='') {
			$query->where('news_title', 'like', '%'.$queries->keyword.'%');
		}

		$types = NewsType::whereRaw('1=1')->get();
		$curType = NewsType::where('type_key', $queries->type)->first();

		$newsList = $query->orderBy('news_time', 'desc')->paginate(10);
		$newsList->appends($queries->all());
		$this->display('list', ['newsList'=>$newsList, 'queries'=>$queries, 'curType'=>$curType, 'types'=>$types]);
	}

	/**
	 * 新闻详情页面
	 * @param  integer $id 新闻ID
	 * @return mixed
	 */
	public function showAction($id=0) {
		$id = intval($id);
		if($id==0) {
			throw new HttpException(404);
		}
		$news = News::find($id);
		if(!$news) {
			throw new HttpException(404);
		}
		if(strtotime($news->news_time)<strtotime('2017-05-08 00:00:00')) {
			$this->title = $news['news_title'].' - 汇诚普惠 hcjrfw.com - 融资租赁 - 车贷平台 - 车贷p2p - 福建网贷';
		} else {
			$this->title = $news['news_title'].'_汇诚普惠网贷投资的可靠平台';
		}
		$this->keywords = $news['news_keywords'];
		$this->description = $news['news_abstract'];
		$news->news_num = $news->news_num + 1;
		$news->save();

		$hotNews = News::where('news_type', $news->news_type)->orderBy('news_num', 'desc')->limit(10)->get();

		$types = NewsType::whereRaw('1=1')->get();
		$curType = NewsType::where('type_key', $news->news_type)->first();

		$nextId = News::where('news_type', $news->news_type)->where('news_time', '>', $news->news_time)->value('id');
		$prepId = News::where('news_type', $news->news_type)->where('news_time', '<', $news->news_time)->value('id');

		if(!$prepId) {
			$prepId = $id;
		}

		if(!$nextId) {
			$nextId = $id;
		}

		$this->display('show', ['news'=>$news, 'curType'=>$curType, 'types'=>$types, 'hotNews'=>$hotNews, 'nextId'=>$nextId, 'prepId'=>$prepId]);
	}
	
}
