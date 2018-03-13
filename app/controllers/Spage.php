<?php
use models\News;
use exceptions\HttpException;

class SpageController extends Controller {
    public $menu = 'spage';

    public function showAction() {
        $id = $this->getQuery('id', 0);
        $app = $this->getQuery('app', 0);
        if($id==0) {
            throw new HttpException(404);
        }
        $news = News::find($id);
        if(!$news || $news->news_type<>'report') {
            throw new HttpException(404);
        }
        $this->title = $news['news_title'].'_汇诚普惠网贷投资的可靠平台';
        $this->keywords = $news['news_keywords'];
        $this->description = $news['news_abstract'];
        $news->news_num = $news->news_num + 1;
        $news->save();
        $content = _decode($news->news_body);
        $this->display('show', ['title'=>$this->title, 'content'=>$content, 'app'=>$app]);
    }
}
