<?php
use Admin as Controller;
use traits\PaginatorInit;
use helpers\DateHelper;
use models\Interest;
use helpers\ExcelHelper;
use helpers\ArrayHelper;
use models\Odd;
use models\User;
use business\RepayHandler;
use models\Invest;
use tools\Redis;
use task\Task;
use Illuminate\Database\Capsule\Manager as DB;
/**
 * RepaymentController
 * 还款管理
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class RepaymentController extends Controller {
    use PaginatorInit;

    public $menu = 'repayment';

    /**
     * 还款列表
     * @return mixed
     */
    public function listAction() {
        $this->submenu = 'list';
        $queries = $this->queries->defaults(['searchType'=>'username', 'searchContent'=>'', 'type'=>'today', 'status'=>0]);

        $builder = Interest::whereRaw('1=1');
        if($queries->searchContent!='') {
            $builder->where($queries->searchType, $queries->searchContent);
        }
        if($queries->type=='today') {
            $builder->where('endtime', '>', date('Y-m-d 00:00:00'))->where('endtime', '<=', date('Y-m-d 23:59:59'));
        } else if($queries->type=='stay') {
            $builder->where('status', 0);
        } else if($queries->type=='over') {
            if($queries->status==0) {
                $builder->whereIn('status', [1, 2, 3]);
            } else {
                $builder->where('status', $queries->status);
            }
        } else if($queries->type=='ing') {
            $builder->where('status', -1);
        }

        $records = $builder->orderBy('endtime','asc')->paginate(15);
        $now = date('Y-m-d');
        $page = [];
        $page['benjin'] = 0;
        $page['interest'] = 0;
        foreach ($records as $record) {
            $page['benjin'] += $record->benJin;
            $page['interest'] += $record->interest;
            $record->etime = DateHelper::getIntervalDay($record->endtime, $now);
        }
        $records->appends($queries->all());

        $key = Redis::getKey('repayIngQueue');
        $ingList = Redis::sMembers($key);

        $this->display('list', ['records'=>$records ,'page'=> $page, 'queries'=>$queries, 'ingList'=>$ingList]);
    }

    /**
     * 提前还款列表
     */
    public function advanceListAction(){
        $this->submenu = 'advanceList';
        $excel = $this->getQuery('excel', 0);
        $queries = $this->queries->defaults([
            'title'=>'', 'startTime'=>'', 'endTime'=>'', 'order'=>'0', 'progress'=>'all', 'oddType'=>'all', 'searchType'=>'', 'searchContent'=>''
        ]);
        $builder = Odd::with('user')->where('fronStatus', '>', 0);
        $searchContent = $queries->searchContent;
        if($searchContent!='') {
            if(in_array($queries->searchType, ['userId', 'username', 'phone', 'name'])) {
                $user = User::where($queries->searchType, $searchContent)->first();
                $builder->where('userId', $user->userId);
            } else {
                $builder->whereRaw($queries->searchType.' like "%'.$searchContent.'%"');
            }
        }
        if($queries->startTime!='') {
            $builder->where('openTime', '>=', $queries->startTime.' 00:00:00');
        }
        if($queries->endTime!='') {
            $builder->where('openTime', '<=', $queries->endTime.' 23:59:59');
        }
        if($queries->progress!='all') {
            $builder->where('progress', '=', $queries->progress);
        }
        if($queries->oddType!='all') {
            $builder->where('oddType', '=', $queries->oddType);
        }

        $columns = ['oddNumber', 'fronStatus', 'oddType', 'oddTitle', 'oddMoney', 'oddBorrowPeriod', 'oddYearRate', 
            'openTime', 'chepai', 'progress', 'userId', 'finishTime', 'oddBorrowStyle', 'oddRehearTime'];
        if($excel) {
            $records = $builder->orderByRaw('field(progress, ?, ?)', ['run', 'end'])->orderBy('fronStatus', 'desc')->orderBy('id', 'desc')->get($columns);
            $other = [
                'title' => '提前还款列表',
                'columns' => [
                    'oddNumber' => ['name'=>'标的号', 'type'=>'string'],
                    'oddTitle' => ['name'=>'标题'],
                    'name' => ['name'=>'借款用户', 'type'=>'string'],
                    'chepai' => ['name'=>'车牌号'],
                    'fronStatus' => ['name'=>'优先级', 'type'=>'string'],
                    'oddMoney' => ['name'=>'金额'],
                    'oddBorrowPeriod' => ['name'=>'借款周期'],
                    'oddYearRate' => ['name'=>'利率'],
                    'openTime' => ['name'=>'发标时间'],
                    'endTime' => ['name'=>'还款日期'],
                    'progress' => ['name'=>'标的状态'],
                ],
            ];
            $excelRecords = [];
            foreach ($records as $row) {
                $row['name'] = $row->user->name;
                $row['endTime'] = $row->getEndTime();
                $row['fronStatus'] = ArrayHelper::getValue([1=>'普通', 2=>'优先'], $row['fronStatus']);
                $row['progress'] = ArrayHelper::getValue(['run'=>'还款中', 'fron'=>'提前还款'], $row['progress']);
                $excelRecords[] = $row;
            }
            ExcelHelper::getDataExcel($excelRecords, $other);
        }
        $records = $builder->orderByRaw('field(progress, ?, ?)', ['run', 'end'])->orderBy('fronStatus','desc')->orderBy('id', 'desc')->paginate(10, $columns);
        $records->appends($queries->all());
        $this->display('advanceList',['records' => $records, 'queries' => $queries]);

    }

    /**
     * 提前还款优先
     */
    public function takeAdvanceAction(){
        $oddNumber = $this->getQuery('oddNumber');
        $fronStatus = $this->getQuery('fronStatus');
        $status = Odd::where('oddNumber', $oddNumber)->update(['fronStatus'=>$fronStatus]);
        if($status) {
            Flash::success('操作成功！');
        } else {
            Flash::error('操作失败！');
        }
        $this->redirect('/admin/repayment/advanceList');
    }


    /**
     * 还款详情
     * @return mixed
     */
    public function detailAction() {
        $this->submenu = 'list';
        $id = $this->getQuery('id');
        $type = $this->getQuery('type');
        $interest = Interest::with(['odd'=>function($q) { $q->select('oddNumber', 'oddTitle', 'userId', 'receiptUserId'); }])->where('id', $id)->first();
        $loanMoney = User::where('userId',$interest->odd->userId)->first(['fundMoney'])->fundMoney;
        $replaceMoney = User::where('userId',$interest->odd->receiptUserId)->first(['fundMoney'])->fundMoney;
        $invests = Invest::where('oddNumber', $interest->oddNumber)->where('qishu', $interest->qishu)->get();

        $key = Redis::getKey('repayIngQueue');
        $ingList = Redis::sMembers($key);

        $this->display('detail', ['interest'=>$interest ,'invests'=> $invests ,'type'=> $type, 'ingList'=>$ingList, 'loanMoney'=>$loanMoney, 'replaceMoney'=>$replaceMoney]);
    }
    
    /**
     * 还款
     * @return mixed
     */
    public function repayAction() {
        set_time_limit(0);
        sleep(1);
        $id = $this->getPost('id', 0);
        $type = $this->getPost('type', 'normal');
        $replace = $this->getPost('replace', '0');
        $interest = Interest::find($id);
        if(!$interest) {
            $rdata['status'] = 0;
            $rdata['info'] = '还款不存在！';
            $this->backJson($rdata);
        }

        if($interest->status==1) {
            $rdata['status'] = 0;
            $rdata['info'] = '该笔还款已还完！';
            $this->backJson($rdata);
        }

        $key = Redis::getKey('repayIngQueue');
        if(!Redis::sAdd($key, $interest->id)) {
            $rdata['status'] = 0;
            $rdata['info'] = '正在还款！';
            $this->backJson($rdata);
        }
        $handler = new RepayHandler(['oddNumber'=>$interest->oddNumber, 'period'=>$interest->qishu, 'type'=>$type, 'replace'=>$replace, 'cr'=>false]);
        $result = $handler->handle();

        if($result['status']) {
            Flash::success('操作成功！');
            $rdata['status'] = 1;
            $this->backJson($rdata);
        } else {
            
            Redis::sRem($key, $interest->id);

            $rdata['status'] = 0;
            $rdata['info'] = $result['msg'];
            $this->backJson($rdata);
        }
    }

    /**
     * 批次还款
     */
    public function repayBatchAction() {
        $type = $this->getPost('type', 'selected');
        $idList = $this->getPost('idList', []);
        $repayList = [];
        if($type=='today') {
            $repayList = Interest::where('endtime', '>', date('Y-m-d 00:00:00'))->where('endtime', '<=', date('Y-m-d 23:59:59'))->where('status', 0)->get();
        } else {
            $repayList = Interest::whereIn('id', $idList)->where('status', 0)->get();    
        }
        
        $key = Redis::getKey('repayIngQueue');
        $num = 0;
        foreach ($repayList as $interest) {
            if(!Redis::sAdd($key, $interest->id)) {
                continue;
            }
            $params = ['oddNumber'=>$interest->oddNumber, 'period'=>$interest->qishu, 'type'=>'normal', 'cr'=>false];
            Task::add('repay', $params);
            $num ++;
        }

        $msg = '成功添加'.$num.'条还款任务，系统稍后将自动还款。';
        Flash::success($msg);
        $rdata = [];
        $rdata['status'] = 1;
        $rdata['info'] = $msg;
        $this->backJson($rdata);
    }
}
