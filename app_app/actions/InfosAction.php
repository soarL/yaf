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
class InfosAction extends Action {
    use ITFAuthHandle;

    public function execute() {
    	$params = $this->getAllQuery();
    	$this->authenticate($params);
        
        $this->pv('aa');

        $timeBegin = $this->getQuery('startTime', '');
        $timeEnd = $this->getQuery('endTime', '');
        $type = $this->getQuery('type', 'all');
        $title = $this->getQuery('title', '');
        $page = $params['page'];
        $pageSize = $params['pageSize'];
        $skip = ($page-1)*$pageSize;
        
        $count = 0;
        $records = [];
        if($type=='question') {
            $builder = Question::where('status', Question::STATUS_ACTIVE)->where('type', 'ceo');

            if($title!='') {
                $builder->where('title', 'like', '%'.$title.'%');
            }

            if($timeBegin!='') {
                $builder->where('addTime', '>=', $timeBegin);
            }
            if($timeEnd!='') {
                $builder->where('addTime', '<=', $timeEnd);
            }

            $count = $builder->count();
            $questions = $builder->orderBy('sort', 'desc')->orderBy('addTime', 'desc')->skip($skip)->limit($pageSize)->get();
            foreach ($questions as $question) {
                $row = [];
                $row['id'] = $question->id;
                $row['title'] = $question->title;
                $row['click'] = $question->hitCount;
                $row['time'] = $question->addTime;
                $row['answer'] = $question->answerCount;
                $row['content'] = $question->content;
                $records[] = $row;
            }
        } else {
            $builder = News::whereRaw('1=1');

            if($type=='notice') {
                $builder->where('news_type', $type);
            } else if($type=='announce') {
                $builder->where('news_type', $type);
            }

            if($title!='') {
                $builder->where('news_title', 'like', '%'.$title.'%');
            }

            if($timeBegin!='') {
                $builder->where('news_time', '>=', $timeBegin);
            }
            if($timeEnd!='') {
                $builder->where('news_time', '<=', $timeEnd);
            }

            $count = $builder->count();
            $newsList = $builder->orderBy('news_time', 'desc')->skip($skip)->limit($pageSize)->get();

            foreach ($newsList as $news) {
                $row = [];
                $row['id'] = $news->id;
                $row['title'] = $news->news_title;
                $row['click'] = $news->news_num;
                $row['time'] = $news->news_time;
                // $row['content'] = $news->news_body;
                $records[] = $row;
            }
        }
        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data']['page'] = $page;
        $rdata['data']['count'] = $count;
        $rdata['data']['records'] = $records;
        $this->backJson($rdata);
    }
}