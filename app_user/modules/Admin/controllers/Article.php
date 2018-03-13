<?php
use Admin as Controller;
use models\News;
use models\NewsType;
use models\ExpectOdd;
use forms\admin\ArticleForm;
use traits\PaginatorInit;
use Illuminate\Database\Capsule\Manager as DB;
/**
 * ArticleController
 * 文章管理
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class ArticleController extends Controller {
    use PaginatorInit;

    public $menu = 'article';

    /**
     * 新闻列表
     * @return mixed
     */
    public function listAction() {
        $this->submenu = 'article';
        $queries = $this->queries->defaults(['title'=>'', 'beginTime'=>'', 'endTime'=>'', 'type'=>'all']);
        $title = $queries->title;
        $beginTime = $queries->beginTime;
        $endTime = $queries->endTime;
        $type = $queries->type;

        $builder = News::with('type')->whereRaw('1=1');

        if($title!='') {
            $builder->where('title', 'like', '%'.$title.'%');
        }

        if($beginTime!='') {
            $builder->where('news_time', '>=', $beginTime);
        }

        if($endTime!='') {
            $builder->where('news_time', '<=', $endTime);
        }

        if($type!='all') {
            $builder->where('news_type', '=', $type);
        }

        $newsList = $builder->orderBy('news_time', 'desc')->paginate(15);
        $newsList->appends($queries->all());

        $types = NewsType::where('type_ck', 'news')->get();

        $this->display('list', ['newsList'=>$newsList, 'queries'=>$queries, 'types'=>$types]);
    }

    /**
     * 添加新闻
     * @return mixed
     */
    public function addAction() {
        $this->submenu = 'article';
        $types = NewsType::where('type_ck', 'news')->get();
        $news = new News();
        $this->display('form', ['news'=>$news, 'types'=>$types]);
    }

    /**
     * 修改新闻
     * @return mixed
     */
    public function updateAction() {
        header("Access-Control-Allow-Origin: *");
        $this->submenu = 'article';
        $id = $this->getQuery('id');
        $news = News::find($id);
        if(!$news) {
            Flash::error('文章不存在！');
            $this->redirect('/admin/article/list');
        }
        $types = NewsType::where('type_ck', 'news')->get();
        $this->display('form', ['news'=>$news, 'types'=>$types]);
    }

    /**
     * 保存新闻
     * @return mixed
     */
    public function saveAction() {
        $params = $this->getAllPost();
        $form = new ArticleForm($params);
        if($form->save()) {
            if($form->oldnews_type){
                News::updateIndexCache($form->oldnews_type);
            }
            News::updateIndexCache($form->news_type);
            Flash::success('操作成功！');
            $this->redirect('/admin/article/list');
        } else {
            Flash::error($form->posError());
            $this->goBack();
        }
    }

    /**
     * 删除新闻
     * @return mixed
     */
    public function deleteAction() {
        $id = $this->getPost('id');
        $article = News::find($id);
        $status = false;
        if($article) {
            $status = $article->delete();
        }
        $rdata = [];
        if($status) {
            News::updateIndexCache($article->news_type);
            ExpectOdd::where('news_id', $id)->delete();
            $rdata['status'] = 1;
            $rdata['info'] = '删除成功！';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '删除失败！';
            $this->backJson($rdata);
        }
    }

    /**
     * 类型列表
     * @return mixed
     */
    public function typesAction() {
        $this->submenu = 'article-type';
        $types = NewsType::where('type_ck', 'news')->get();
        $this->display('types', ['types'=>$types]);
    }

    /**
     * 添加新闻类型
     * @return mixed
     */
    public function addTypeAction() {
        $this->submenu = 'article-type';
        $type = new NewsType();
        $this->display('typeForm', ['type'=>$type]);
    }

    /**
     * 修改新闻类型
     * @return mixed
     */
    public function updateTypeAction() {
        $this->submenu = 'article-type';
        $id = $this->getQuery('id');
        $type = NewsType::find($id);
        $this->display('typeForm', ['type'=>$type]);
    }

    /**
     * 保存新闻类型
     * @return mixed
     */
    public function saveTypeAction() {
        $params = $this->getAllPost(true);

        $type = null;
        if($params['id']!='') {
            $type = NewsType::find($params['id']);
        } else {
            $type = new NewsType();
        }

        $data = [];
        $data['type_name'] = $params['type_name'];
        $data['type_key'] = $params['type_key'];
        $data['type_ck'] = 'news';

        foreach ($data as $key => $value) {
            $type->$key = $value;
        }
        $status = $type->save();
        
        if($status) {
            Flash::success('操作成功！');
            $this->redirect('/admin/article/types');
        } else {
            Flash::error('操作失败！');
            $this->goBack();
        }
    }

    /**
     * 删除新闻类型(将会删除该类型的所有新闻)
     * @return mixed
     */
    public function deleteTypeAction() {
        $id = $this->getPost('id');
        $articleType = NewsType::find($id);
        $status = false;
        if($articleType) {
            $status = $articleType->delete();
        }
        $rdata = [];
        if($status) {
            News::where('news_type', $article->type_key)->delete();
            $rdata['status'] = 1;
            $rdata['info'] = '删除成功！';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '删除失败！';
            $this->backJson($rdata);
        }
    }

    /**
     * 发标预告
     * @return mixed
     */
    public function anncsAction() {
        $queries = $this->queries->defaults(['beginTime'=>'', 'endTime'=>'']);
        $beginTime = $queries->beginTime;
        $endTime = $queries->endTime;

        $builder = ExpectOdd::whereRaw('1=1');

        if($beginTime!='') {
            $builder->where('day', '>=', $beginTime);
        }
        if($endTime!='') {
            $builder->where('day', '<=', $endTime);
        }
        $anncs = $builder->groupBy('news_id')->paginate(20, ['news_id', 'day', DB::raw('count(*) as num'), DB::raw('sum(money) as totalMoney')]);
        $anncs->appends($queries->all());
        $this->display('anncs', ['anncs'=>$anncs, 'queries'=>$queries]);
    }

    /**
     * 发标预告
     * @return mixed
     */
    public function anncAction() {
        $newsId = $this->getQuery('id', 0);
        
        $expectOdds = [];
        $annc = false;

        if($newsId!=0) {
            $expectOdds = ExpectOdd::where('news_id', $newsId)->get();
            $annc = ExpectOdd::whereRaw('1=1')->groupBy('news_id')->having('news_id', '=', $newsId)->first();    
        }

        $this->display('anncForm', [
            'types'=>ExpectOdd::$types, 
            'periods'=>ExpectOdd::$periods, 
            'times'=>ExpectOdd::$times, 
            'expectOdds'=>$expectOdds,
            'annc'=>$annc,
        ]);
    }

    /**
     * 发标预告生成
     * @return mixed
     */
    public function generateAnncAction() {
        $params = $this->getAllPost();
        // var_dump($params);die();
        $items = $params['items'];
        $day = $params['day'];
        $newsId = $params['newsId'];
        $types = ExpectOdd::$types;
        $times = ExpectOdd::$times;
        $periods = ExpectOdd::$periods;
        $timeCountList = [];
        $typeMoneyList = [];
        $list = [];
        foreach ($times as $timeKey => $time) {
            foreach ($types as $typeKey => $type) {
                $secCount = 0;
                $secMoney = 0;
                foreach ($periods as $periodKey => $period) {
                    foreach ($items as $item) {
                        if($item['time']==$timeKey&&$item['type']==$typeKey&&$item['period']==$periodKey) {
                            $topFirst = true;
                            $secFirst = true;
                            if(isset($list[$item['time']])) {
                                $topFirst = false;
                                if(isset($list[$item['time']][$item['type']])) {
                                    $secFirst = false;
                                }
                            }
                            $secCount += 1;
                            $secMoney += $item['money'];
                            $list[$item['time']][$item['type']]['odds'][] = [
                                'time'=>$item['time'], 
                                'type'=>$item['type'], 
                                'title'=>$item['title'], 
                                'period'=>$item['period'], 
                                'money'=>$item['money'], 
                                'yearRate'=>$item['yearRate'],
                                'limitMoney'=>$item['limitMoney'],
                                'topFirst'=>$topFirst,
                                'secFirst'=>$secFirst,
                            ];
                            $list[$timeKey][$typeKey]['odds'][0]['secCount'] = $secCount;
                            $list[$timeKey][$typeKey]['odds'][0]['secMoney'] = $secMoney;
                            if(isset($timeCountList[$timeKey])) {
                                $timeCountList[$timeKey] += 1;
                            } else {
                                $timeCountList[$timeKey] = 1;
                            }
                            if(isset($typeMoneyList[$typeKey])) {
                                $typeMoneyList[$typeKey] += $item['money'];
                            } else {
                                $typeMoneyList[$typeKey] = $item['money'];
                            }
                        }
                    }
                }
            }
        }
        $datas = '';
        foreach ($list as $l) {
            foreach ($l as $s) {
                foreach ($s['odds'] as $odd) {
                    $tr = '';
                    if($odd['topFirst']) {
                        $timeWord = $odd['time']>=0&&$odd['time']<=24?$times[$odd['time']].'左右':'不定时';
                        $tr .= '<td rowspan="'.$timeCountList[$odd['time']].'">'.$timeWord.'</td>';
                    }
                    if($odd['secFirst']) {
                        $tr .= '<td rowspan="'.$odd['secCount'].'">'.$types[$odd['type']].': '.($odd['secMoney']/10000).'万元</td>';
                    }
                    $tr .= '<td>'.$odd['title'].'</td>';
                    $tr .= '<td>'.$periods[$odd['period']].'</td>';
                    $tr .= '<td>'.($odd['money']/10000).'万元</td>';
                    $tr .= '<td>'.$odd['yearRate'].'</td>';
                    $tr .= '<td>'.$odd['limitMoney'].'</td>';
                    $datas .= '<tr>'.$tr.'</tr>';
                }
            }
        }
        $topInfo = '';
        $totalMoney = 0;
        foreach ($typeMoneyList as $key => $money) {
            $totalMoney += $money;
            $topInfo .= $types[$key].': '.($money/10000).'万元&nbsp;&nbsp;&nbsp;&nbsp;';
        }
        $topInfo = '发标总额: '. ($totalMoney/10000).'万元&nbsp;&nbsp;&nbsp;&nbsp;' . $topInfo;
        $html = '<table style="width:100%; margin: 25px 0px;"><tbody>'
            . '<tr><td colspan="7">'.$topInfo.'</td></tr>'
            . '<tr><td>发标时段</td><td>发标类型</td><td>标的名称</td><td>借款周期</td><td>标的金额</td><td>标的收益</td><td>标的限额</td></tr>'
            . $datas
            . '</tbody></table>';
        
        $user = $this->getUser();

        if($newsId) {
            $article = News::find($newsId);
        } else {
            $article = new News();
            $article->news_time = date('Y-m-d H:i:s');
            $article->news_num = 0;
            $article->news_order = 0;
            $article->news_type = 'announce';
            $article->news_keywords = '';
            $article->news_abstract = '';
            $article->news_user = $user->username;
        }
        $article->news_title = date('Y年n月j日', strtotime($day)).'新标预告';
        $article->news_body = htmlspecialchars($html);

        if($article->save()) {
            News::updateIndexCache($article->news_type);
            if($newsId) {
                ExpectOdd::where('news_id', $newsId)->delete();
            }

            $rows = [];
            foreach ($items as $item) {
                $rows[] = [
                    'title'=>$item['title'], 
                    'money'=>$item['money'], 
                    'type'=>$item['type'], 
                    'period'=>$item['period'], 
                    'yearRate'=>$item['yearRate'], 
                    'limitMoney'=>$item['limitMoney'], 
                    'time'=>$item['time'], 
                    'day'=>$day, 
                    'news_id'=>$article->id,
                    'created_at'=>date('Y-m-d H:i:s'),
                    'updated_at'=>date('Y-m-d H:i:s'),
                ];
            }
            ExpectOdd::insert($rows);
            $rdata['link'] = WEB_USER.'/admin/article/update?id='.$article->id;
            $rdata['status'] = 1;
            $rdata['info'] = '生成成功！';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '生成失败！';
            $this->backJson($rdata);
        }
    }
}
