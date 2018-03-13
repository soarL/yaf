<?php
use models\News;
use models\Question;
use traits\handles\ITFAuthHandle;

/**
 * InfosAction
 * APP讯息接口
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class NewsAction extends Action {
    use ITFAuthHandle;

    public function execute() {
    	$params = $this->getAllQuery();
    	$this->authenticate($params, ['id'=>'ID']);

        $id = $this->getQuery('id', 0);

        $news = News::find($id);

            
        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data']['id'] = $news->id;
        $rdata['data']['title'] = $news->news_title;
        $rdata['data']['click'] = $news->news_num;
        $rdata['data']['content'] = $news->news_body;
        $rdata['data']['time'] = $news->news_time;
        $this->backJson($rdata);
    }
}