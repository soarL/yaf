<?php
namespace forms\admin;
use models\News;

/**
 * ArticleForm|form类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class ArticleForm extends \Form {
	public $article = false;

	public function init() {
		if($this->id && $this->id!='') {
			$this->article = News::find($this->id);
		} else {
			$this->article = false;
		}
	}

	public function defaults() {
		return ['news_num'=>0, 'news_order'=>0];
	}

	public function rules() {
		return [
			[['news_title', 'news_type'], 'required'],
			[['news_num', 'news_order'], 'type', ['type'=>'int']],
		];
	}

	public function labels() {
		return [
        	'news_title' => '标题',
        	'news_keywords' => '关键字',
        	'news_abstract' => '描述',
        	'news_type' => '类型',
        	'news_order' => '排序',
        	'news_num' => '点击量',
        	'news_body' => '内容',
        ];
	}

	public function save() {
		if($this->check()) {
			$article = $this->article;
			$user = $this->getUser();
			if(!$article) {
				$article = new News();
				$article->news_time = $this->news_time?$this->news_time:date('Y-m-d H:i:s');
				$article->news_user = $user->username;
			}else{
				$this->oldnews_type = $article->news_type;
			}
			if($this->news_time){
				$article->news_time = $this->news_time;
			}
			$article->news_title = $this->news_title;
			$article->news_keywords = $this->news_keywords;
			$article->news_abstract = $this->news_abstract;
			$article->news_type = $this->news_type;
			$article->news_num = $this->news_num;
			$article->news_order = $this->news_order;
			$article->news_body = $this->news_body;
			$article->news_image = $this->imageUrl;

			if($article->save()) {
				return true;
			} else {
				$this->addError('form', '操作失败！');
				return false;
			}
		} else {
			return false;
		}
	}
}