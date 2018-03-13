<?php
use Yaf\Registry;
use models\Odd;
use models\LookOdd;
use models\LookVote;
use helpers\NetworkHelper;
use helpers\StringHelper;
use helpers\HtmlHelper;
use tools\Pager;
use traits\PaginatorInit;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * LookController
 * 查标
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class LookController extends Controller {
    use PaginatorInit;

    public $menu = 'account';
    public $submenu = 'look';

    public function indexAction() {
        $this->mode = 'index';
        $votes = LookVote::with('odd')
            ->groupBy('oddNumber')
            ->orderBy('voteNum', 'desc')
            ->limit(10)
            ->get([DB::raw('count(userId) as voteNum'), 'oddNumber']);

        $lookOdds = LookOdd::with('odd')->orderBy('period', 'desc')->orderBy('created_at', 'desc')->limit(8)->get();

        $this->display('index', ['ranks'=>$votes, 'lookOdds'=>$lookOdds]);
    }

    public function historyAction() {
        $this->mode = 'history';
        $queries = $this->queries->defaults(['period'=>'', 'beginTime'=>'', 'endTime'=>'']);

        $period = $queries->period;
        $beginTime = $queries->beginTime;
        $endTime = $queries->endTime;

        $builder = LookOdd::with('odd');

        if($period!='') {
            $builder->where('period', $period);
        }

        if($beginTime!='') {
            $builder->where('created_at', '>=', $beginTime.' 00:00:00');
        }

        if($endTime!='') {
            $builder->where('created_at', '<=', $endTime.' 23:59:59');
        }

        $records = $builder->orderBy('period', 'desc')->orderBy('created_at', 'desc')->paginate(15);
        $records->appends($queries->all());

        $this->display('history', ['records'=>$records, 'queries'=>$queries]);
    }

    public function getOddsAction() {
        $year = $this->getPost('year', '');
        $month = $this->getPost('month', '');
        $day = $this->getPost('day', '');
        $date = _date('Y-m-d', $year . '-' . $month . '-' . $day);
        $dateBegin = $date . ' 00:00:00';
        $dateEnd = $date . ' 23:59:59';
        $builder = Odd::where('progress', 'run')
            ->where('isUserLook', 'n')
            ->where('oddTrialTime', '>=', $dateBegin)
            ->where('oddTrialTime', '<=', $dateEnd);

        $count = $builder->count();
        $pager = new Pager(['total'=>$count, 'request'=>$this->getRequest(), 'isDy'=>true, 'pageSize'=>8]);
        $limit = $pager->getLimit();
        $offset = $pager->getOffset();
        $odds = $builder->skip($offset)->limit($limit)->get(['oddTitle', 'oddType', 'oddNumber']);
        $newOdds = [];
        foreach ($odds as $odd) {
            $newOdd = [];
            if($odd->oddType=='diya') {
                $newOdd['oddType'] = '<span class="pledgeico" title="抵押标">抵</span>';
            } else if($odd->oddType=='xingyong') {
                $newOdd['oddType'] = '<span class="guaranteeico" title="质押标">质</span>';
            } else if($odd->oddType=='danbao') {
                $newOdd['oddType'] = '<span class="rongziico" title="融资租赁标">融</span>';
            } else {
                $newOdd['oddType'] = '<span class="pledgeico" title="抵押标">抵</span>';
            }
            $newOdd['oddTitle'] = $newOdd['oddType'] 
                . '<a class="link" href="' . WEB_MAIN . '/odd/' . $odd->oddNumber . '" target="_blank">'
                . $odd->oddTitle . '</a>';
            $newOdd['oddNumber'] = $odd->oddNumber;
            $newOdd['voteCount'] = $odd->getVoteCount();
            $newOdds[] = $newOdd;
        }

        $rdata = [];
        $rdata['pager'] = $pager->html();
        $rdata['records'] = $newOdds;
        $this->backJson($rdata);
    }

    public function voteAction() {
        $week = date('w');
        if(!in_array($week, [0, 1, 2, 4, 5, 6])) {
            $rdata['status'] = 0;
            $rdata['info'] = '今天不是投票日！';
            $this->backJson($rdata);
        }

        $user = $this->getUser();
        $oddNumber = $this->getPost('oddNumber', '');

        $odd = Odd::where('oddNumber', $oddNumber)->whereNotIn('progress', ['fail', 'end'])->first();
        if(!$odd) {
            $rdata['status'] = 0;
            $rdata['info'] = '标的不存在或不可投票！';
            $this->backJson($rdata);
        }

        $count = LookVote::where('userId', $user->userId)->count();
        if($count>0) {
            $rdata['status'] = 0;
            $rdata['info'] = '您已经投过票了！';
            $this->backJson($rdata);
        }

        $lookVote = new LookVote();
        $lookVote->oddNumber = $odd->oddNumber;
        $lookVote->userId = $user->userId;
        if($lookVote->save()) {
            $rdata['status'] = 1;
            $rdata['info'] = '投票成功！';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '投票失败！';
            $this->backJson($rdata);
        }
    }
}
