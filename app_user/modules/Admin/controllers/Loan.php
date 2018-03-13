<?php
use Admin as Controller;
use Illuminate\Database\Capsule\Manager as DB;
use traits\PaginatorInit;
use models\Odd;
use models\OddInfo;
use models\OddCopy;
use models\User;
use models\OddMoney;
use models\VideoArea;
use models\OddTrace;
use models\Gps;
use custody\API;
use custody\Handler;
use custody\Code;
use business\AITool;
use business\RehearHandler;
use helpers\NetworkHelper;
use helpers\ExcelHelper;
use tools\Redis;
use tools\Log;
use task\Task;
use models\Interest;
use helpers\DateHelper;

/**
 * 标的相关操作
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class LoanController extends Controller{
    use PaginatorInit;

    public $menu = 'repayment';

    /**
     * 贷后列表
     * @return mixed
     */
    public function traceAction() {
        $this->submenu = 'trace';
        $queries = $this->queries->defaults(['searchType'=>'username', 'searchContent'=>'', 'type'=>'today', 'status'=>0]);

        $builder = Interest::whereRaw('1=1')->with('user','odd');
        if($queries->searchContent!='') {
            $builder->where($queries->searchType, $queries->searchContent);
        }
        $builder->where('status', 0);

        $records = $builder->orderBy('endtime','asc')->paginate(15);
        $now = date('Y-m-d');
        foreach ($records as $record) {
            $record->etime = DateHelper::getIntervalDay($record->endtime, $now);
            $record->oddType = Odd::$oddTypes[$record->odd->oddType];
        }
        $records->appends($queries->all());

        $key = Redis::getKey('repayIngQueue');
        $ingList = Redis::sMembers($key);

        $this->display('trace', ['records'=>$records, 'queries'=>$queries, 'ingList'=>$ingList]);
    }

    /**
     * 编辑借款
     */
    public function traceEditAction(){
        $id = $this->getQuery('id', '');
        $oddNumber = $this->getQuery('oddNumber', '');
        if($id){
            $oddTrace = OddTrace::where('id', $id)->first();
        } else {
            $oddTrace = new OddTrace();
        }
        $this->display('traceEdit', [
            'oddTrace'=>$oddTrace, 
            'oddNumber'=>$oddNumber
        ]);
    }

    /**
     * 贷后详情
     * @return [type] [description]
     */
    public function traceDetailAction(){
        $this->submenu = 'trace';
        $queries = $this->queries;
        $records = OddTrace::where('oddNumber',$queries->oddNumber)->paginate(15);
        $this->display('traceDetail', ['records'=>$records,'queries'=>$queries]);   
    }

    /**
     * 贷后信息删除
     * @return [type] [description]
     */
    public function traceDelAction(){
        $oddNumber = $this->getQuery('oddNumber', '');
        $id = $this->getQuery('id', '');
        OddTrace::where('id',$id)->delete();
        Flash::success('操作成功！');
        $this->redirect('/admin/loan/traceDetail?oddNumber='.$oddNumber);
    }

    /**
     * 贷后信息保存
     * @return [type] [description]
     */
    public function saveTraceAction(){
        $params = $this->getAllPost();
        if($params['id']){
            $oddTrace = OddTrace::where('id',$params['id'])->first();
            if($oddTrace->type != 'base'){
                $oddTrace->info = $params['info'];
                $oddTrace->addtime = $params['addtime'];
                $oddNumber = $oddTrace->oddNumber;
            }else{
                $oddNumber = $oddTrace->oddNumber;
                $oddTrace->info = json_encode($params['info']);
            }
            $res = $oddTrace->save();
        }else{
            $oddTrace = new oddTrace();
            $oddNumber = $params['oddNumber'];
            $res = $oddTrace::insert(['info'=>$params['info'],'type'=>'normal','addtime'=>$params['addtime'],'oddNumber'=>$params['oddNumber']]);
        }
        Flash::success('操作成功！');
        $this->redirect('/admin/loan/traceDetail?oddNumber='.$oddNumber);
    }

    /**
     * 用户列表
     * @return mixed
     */
    public function gpsAction() {
        $this->submenu = 'user';
        $queries = $this->queries->defaults(['oddNumber'=>'username','order'=>'oddNumber','ley'=>'','value'=>'']);

        $builder = Odd::with('gps');
        if($queries->value!='') {
            $value = trim($queries->value);
            $builder->where($queries->key, 'like','%'.$value.'%');
        }

        if($queries->order!=''){
            $builder->orderBy($queries->order,'desc');
        }

        $gps = $builder->paginate();
       
        $gps->appends($queries->all());
        $this->display('gps', ['list'=>$gps, 'queries'=>$queries]);
    }

    /**
     * 备注编辑
     */
    public function savegpsAction(){
        $key = $this->getQuery('key');
        $value = $this->getQuery('value');
        $id = $this->getQuery('id');
        $gps = Gps::where('oddNumber',$id)->first();
        if($gps){
            $gps->$key = $value;
            $re = $gps->save();
        }else{
            $gps = new Gps();
            $re = $gps::insert([$key=>$value,'oddNumber'=>$id]);
        }
        $rdata['status'] = $re;
        $this->backJson($rdata);
    }

    /**
     * 筹款列表 
     */
    public function investsAction() {
        $this->submenu = 'invests';
        $queries = $this->queries->defaults(['searchType'=>'', 'searchContent'=>'', 'startTime'=>'', 'endTime'=>'', 'excel'=>0]);
        $builder = OddMoney::with(['odd' => function ($odd) {
        	$odd->select('oddNumber', 'oddTitle', 'oddBorrowStyle', 'oddBorrowPeriod', 'investType');
        }])->where('type', '!=', 'loan')->where('status', '!=', '-1');
        if ($queries->searchContent != '') {
        	$builder->where($queries->searchType, $queries->searchContent);
        }
        if ($queries->startTime != '') {
        	$builder->where('time', '>=', $queries->startTime.' 00:00:00');
        }
        if ($queries->endTime != '') {
        	$builder->where('time', '<=', $queries->endTime.' 23:59:59');
        }
        $sumBuilder = clone $builder;
        $sumMoney = $sumBuilder->sum('money');
        $builder->orderBy('time', 'desc');
        if ($queries->excel) {
        	$records = $builder->get();
        	$other = [
        		'title' => '筹款列表',
        		'columns' => [
        			'userId' => ['name' => '用户ID'],
        			'investType' => ['name' => '投标类型'],
        			'oddTitle' => ['name' => '标的名称'],
        			'oddPeriod' => ['name' => '标的周期'],
        			'money' => ['name' => '	投资金额'],
        			'time' => ['name' => '时间'],
        			'remark' => ['name' => '备注'],
        			'result' => ['name' => '状态']
        		],
        	];
        	$excelRecords = [];
        	foreach ($records as $row) {
        		$row['investType']  = $row->odd->investType==1?'手动':'自动';
        		$row['oddTitle'] = $row->odd->oddTitle;
        		$row['oddPeriod'] = $row->odd->getPeriod();
        		$row['result'] = isset($row->task->result)?$row->task->result:'';
        		$excelRecords[] = $row;
        	}
        	ExcelHelper::getDataExcel($excelRecords, $other);
        } else {
        	$records = $builder->paginate(20);
        	$records->appends($queries->all());
        }
        $this->display('invests', ['records'=>$records, 'queries'=>$queries, 'sumMoney'=>$sumMoney]);
    }

    /**
     * 添加提前还款
     */
    public function addAdvanceAction() {
        $oddNumber = $this->getQuery('oddNumber');
        $status = Odd::where('oddNumber', $oddNumber)->update(['fronStatus'=>'1']);
        if($status){
            Flash::success('进入提前还款队列成功！');
            $rdata['status'] = 1;
            $rdata['info'] = '进入提前还款队列成功！';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '进入提前还款队列失败！';
            $this->backJson($rdata);
        }
    }

    /**
     * 添加逾期还款
     */
    public function addDelayAction() {
        $oddNumber = $this->getQuery('oddNumber');
        $handler = new RepayHandler($oddNumber);
        $rdata = $handler->delay();
        if($rdata['status']) {
            Flash::success('操作成功！');
            $rdata['status'] = 1;
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = $rdata['msg'];
            $this->backJson($rdata);
        }
    }

    /**
     * 初审列表
     */
    public function trialListAction(){
        $this->submenu='trial-list';
        $queries = $this->queries->defaults(['username'=>'', 'beginTime'=>'', 'endTime'=>'']);
        $key1 = Redis::getKey('oddAutoQueue');
        $key2 = Redis::getKey('trialQueue');
        $key3 = Redis::getKey('trialIngQueue');

        $autoList =  Redis::lRange($key1, 0, -1);
        $trialList =  Redis::lRange($key2, 0, -1);
        $ingList = Redis::lRange($key3, 0, -1);

        $username = $queries->username;
        $oddBuilder = Odd::whereRaw('(progress=? or (progress=? and isATBiding=1))', ['published', 'start'])
            ->with(['user'=>function($q)  {
            $q->select(['userId', 'username']);}]);

        if ($username && $queries->search == '2'){
            $oddBuilder = $oddBuilder->whereHas('user',function($query)use($username){
                $query->where('username',$username);
            });
        }

        if ($queries->beginTime && $queries->search == '2'){
            $oddBuilder = $oddBuilder->where('addtime','>',$queries->beginTime.'00:00:00');
        }
        if ($queries->endTime && $queries->search == '2'){
            $oddBuilder = $oddBuilder->where('addtime','<',$queries->endTime.'23:59:59');
        }
        if ($queries->period  && $queries->search == '2'){
            $oddBuilder = $oddBuilder->where('oddBorrowPeriod',$queries->period);
        }
        if ($queries->investType !='' && $queries->search == '2'){
            $oddBuilder = $oddBuilder->where('investType',$queries->investType);
        }
        $odds = $oddBuilder
            ->orderBy('openTime', 'asc')
            ->orderBy('investType', 'asc')
            ->get();
        $autoOdds = Odd::with(['user'=>function($q) { $q->select(['userId', 'username']);}])
            ->where('progress', 'start')
            ->whereIn('oddNumber', $autoList)
            ->orderBy('openTime', 'asc')
            ->get();

        $prepOddsBuilder = Odd::with(['user'=>function($q) {
            $q->select(['userId', 'username']);}]);

        if ($username && $queries->search == '1'){
            $prepOddsBuilder = $prepOddsBuilder->whereHas('user',function($query)use($username){
                $query->where('username',$username);
            });
        }

        if ($queries->beginTime && $queries->search == '1'){
            $prepOddsBuilder = $prepOddsBuilder->where('addtime','>',$queries->beginTime.'00:00:00');
        }
        if ($queries->endTime && $queries->search == '1'){
            $prepOddsBuilder = $prepOddsBuilder->where('addtime','<',$queries->endTime.'23:59:59');
        }
        if ($queries->period  && $queries->search == '1'){
            $prepOddsBuilder = $prepOddsBuilder->where('oddBorrowPeriod',$queries->period);
        }
        if ($queries->investType !='' && $queries->search == '1'){
            $prepOddsBuilder = $prepOddsBuilder->where('investType',$queries->investType);
        }

        $prepOdds = $prepOddsBuilder
            ->where('progress', 'prep')
            ->orderBy('openTime', 'asc')
            ->get();
        $this->display('trialList', [
            'odds'=>$odds, 
            'autoOdds'=>$autoOdds, 
            'prepOdds'=>$prepOdds, 
            'trialList'=>$trialList, 
            'ingList'=>$ingList,
            'queries'=>$queries
        ]);
    }

    /**
     * 启动初审队列
     */
    public function runTrialAction(){
        $key = Redis::getKey('trialQueue');
        if($key=='') {
            $rdata['status'] = 0;
            $rdata['info'] = '队列不存在！';
            $this->backJson($rdata);
        }

        $list =  Redis::lRange($key, 0, -1);
        if(!$list || count($list)==0) {
            $rdata['status'] = 0;
            $rdata['info'] = '队列不存在或者队列中无标的！';
            $this->backJson($rdata);
        }
        
        $params = [];
        $params['odds'] = $list;
        
        $rdata = [];
        if(Task::add('trial', $params, 10)) {
            Redis::delete($key);
            $ingKey = Redis::getKey('trialIngQueue');
            foreach ($list as $oddNumber) {
                Redis::lPush($ingKey, $oddNumber);
            }
            $rdata['status'] = 1;
            $rdata['info'] = '启动成功！';
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '启动失败！';
        }
        $this->backJson($rdata);
    }

    /**
     * 进入队列
     */
    public function inQueueAction(){
        $oddNumber = $this->getQuery('oddNumber', '');
        if($oddNumber=='') {
            Flash::error('标的号错误！');
            $this->redirect('/admin/loan/trialList');
        }
        $odd = Odd::where('oddNumber', $oddNumber)->first(['receiptUserId', 'receiptStatus', 'oddNumber', 'progress', 'userId']);
        if(!$odd) {
            Flash::error('标的不存在！');
            $this->redirect('/admin/loan/trialList');
        }
        if($odd->progress<>'published') {
            Flash::error('标的状态错误！');
            $this->redirect('/admin/loan/trialList');
        }
        $typeKey = 'trialQueue';
        $key = Redis::getKey($typeKey);
        if(Redis::lPush($key, $oddNumber)) {
            Flash::success('操作成功！');
            $this->redirect('/admin/loan/trialList');
        } else {
            Flash::error('操作失败');
            $this->redirect('/admin/loan/trialList');
        }
    }

    /**
     * 退出初审队列
     */
    public function outQueueAction(){
        $oddNumber = $this->getQuery('oddNumber', '');
        $type = $this->getQuery('type', 'trial');
        if($oddNumber=='') {
            Flash::error('标的号错误！');
            $this->redirect('/admin/loan/trialList');
        }
        $typeKey = ($type=='auto'?'oddAutoQueue':'trialQueue');
        $key = Redis::getKey($typeKey);
        if(Redis::lRem($key, $oddNumber, 0)) {
            Flash::success('操作成功！');
            $this->redirect('/admin/loan/trialList');
        } else {
            Flash::error('操作失败');
            $this->redirect('/admin/loan/trialList');
        }
    }

    /**
     * 初审、自动投标标的队列排序
     */
    public function sortQueueAction(){
        $type = $this->getQuery('type', 'trial');
        $oddNumber = $this->getQuery('oddNumber', '');
        $refer = $this->getQuery('refer', '');
        $sortType = $this->getQuery('sortType', 'next');

        $typeKey = ($type=='auto'?'oddAutoQueue':'trialQueue');

        $status = false;
        if($sortType=='next') {
            $status = Redis::lInsert($typeKey, Redis::AFTER, $refer, $oddNumber);
        } else {
            $status = Redis::lInsert($typeKey, Redis::BEFORE, $refer, $oddNumber);
        }

        if($status) {
            Flash::success('操作成功！');
            $this->redirect('/admin/loan/triallist');
        } else {
            Flash::error('操作失败');
            $this->redirect('/admin/loan/triallist');
        }
    }

    /**
     * 借款单查询
     * @return mixed
     */
    public function listAction() {
        $this->submenu = 'oddlist';
        $queries = $this->queries->defaults([
            'title'=>'', 'startTime'=>'', 'endTime'=>'', 
            'progress'=>'all', 'oddType'=>'all', 'searchType'=>'', 
            'searchContent'=>'', 'isFinish'=>'n', 'oddRepaymentStyle'=>'all'
        ]);
        $excel = $this->getQuery('excel', 0);
        $builder = Odd::with(['user'=>function($q) { $q->select('userId', 'name', 'username', 'fundMoney');}]);
        $searchContent = $queries->searchContent;
        if($searchContent!='') {
            if(in_array($queries->searchType, ['userId', 'username', 'phone', 'name'])) {
                $user = User::where($queries->searchType, $searchContent)->first();
                $builder->where('userId', $user->userId);
            } else {
                $builder->whereRaw($queries->searchType.' like "%'.$searchContent.'%"');
            }
        }

        if($queries->isFinish=='y') {
            $builder->whereHas('user', function($q) {
                $q->where('fundMoney', '>', 0);
            });
        }

        if($queries->startTime!='') {
            $builder->where('openTime', '>=', $queries->startTime.' 00:00:00');
        }
        if($queries->endTime!='') {
            $builder->where('openTime', '<=', $queries->endTime.' 23:59:59');
        }
        if($queries->progress!='all') {
            if($queries->progress=='finish') {
                $builder->where('progress', 'start')->whereRaw('successMoney=oddMoney');
            } else {
                $builder->where('progress', $queries->progress);
            }
        }
        if($queries->oddType!='all') {
            $builder->where('oddType', '=', $queries->oddType);
        } else {
            $builder->where('oddType', '<>', 'xiaojin');
        }
        if($queries->oddRepaymentStyle!='all') {
            $builder->where('oddRepaymentStyle', '=', $queries->oddRepaymentStyle);
        }


        if($excel) {
            $records = $builder->get();
            $other = [
                'title' => '借款列表',
                'columns' => [
                    'username' => ['name'=>'用户名', 'type'=>'string'],
                    'fundMoney' => ['name'=>'用户余额'],
                    'oddTitle' => ['name'=>'借款标题'],
                    'oddMoney' => ['name'=>'借款金额'],
                    'percent' => ['name'=>'完成比例'],
                    'period' => ['name'=>'借款期数'],
                    'oddReward' => ['name'=>'年利率'],
                    'time' => ['name'=>'发布时间'],
                    'RepayType' => ['name'=>'借款类型'],
                    'PRGName' => ['name'=>'状态'],
                ],
            ];
            $excelRecords = [];
            foreach ($records as $row) {
                $item = [];
                $item['username'] = $row->user->username;
                $item['fundMoney'] = $row->user->fundMoney;
                $item['oddTitle'] = $row->oddTitle;
                $item['oddMoney'] = $row->oddMoney;
                $item['percent'] = $row->getPercent();
                $item['period'] = $row->getPeriod();
                $item['period'] = $row->getPeriod();
                $item['oddReward'] = $row->oddReward*100;
                $item['time'] = $row->openTime;
                $item['RepayType'] = $row->getRepayType();
                $item['PRGName'] = $row->getPRGName();
                $excelRecords[] = $item;
            }
            ExcelHelper::getDataExcel($excelRecords, $other);
        }


        $columns = ['oddNumber', 'fronStatus', 'appointUserId', 'oddType', 'oddTitle', 'oddMoney', 'successMoney', 'oddBorrowPeriod', 'oddYearRate', 'openTime', 'oddBorrowStyle', 'progress', 'userId', 'oddRepaymentStyle', 'oddRehearTime', 'oddReward', 'oddStyle'];
        $records = $builder->orderBy('id', 'desc')->paginate(10, $columns);
        $records->appends($queries->all());
        
        $key = Redis::getKey('rehearIngQueue');
        $ingList = Redis::sMembers($key);

        $this->display('list',['records' => $records, 'queries' => $queries, 'ingList'=>$ingList]);
    }

    /**
     * 复审
     * @return mixed
     */
    public function rehearAction() {
        set_time_limit(0);
        $oddNumber = $this->getPost('oddNumber', '');

        $key = Redis::getKey('rehearIngQueue');
        if(!Redis::sAdd($key, $oddNumber)) {
            $rdata['status'] = 0;
            $rdata['info'] = '标的正在复审！';
            $this->backJson($rdata);
        }

        $handler = new RehearHandler(['oddNumber'=>$oddNumber, 'step'=>1]);
        $result = $handler->handle();
        
        $rdata = [];
        if($result['status']) {
            Flash::success('复审成功！');
            $rdata['status'] = 1;
            $rdata['info'] = '复审成功！';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = $result['msg'];
            Log::write($result['msg'], [], 'rehear', 'ERROR');
            $this->backJson($rdata);
        }
    }

    /**
     * 自动投标
     * @return mixed
     */
    public function autoBidAction() {
        set_time_limit(0);
        $oddNumber = $this->getPost('oddNumber', '');
        $odd = Odd::where('oddNumber', $oddNumber)->first([
            'oddNumber', 
            'oddYearRate', 
            'investType',
            'oddMoney',
            'userId',
            'oddBorrowValidTime',
            'oddBorrowPeriod',
            'oddType',
            'oddBorrowStyle',
            'progress',
            'isCr',
            'receiptUserId',
        ]);
        if(!$odd) {
            $rdata['status'] = 0;
            $rdata['info'] = '标的不存在！';
            $this->backJson($rdata);
        }
        $result = AITool::run($odd);
        $rdata = [];
        if($result['status']) {
            Flash::success('自动投标完成');
            $rdata['status'] = 1;
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = $result['msg'];
            $this->backJson($rdata);
        }
    }

    /**
     * 批量自动投标
     * @return mixed
     */
    public function autoBidBatchAction() {
        $key = Redis::getKey('oddAutoQueue');
        $list =  Redis::lRange($key, 0, -1);
        $rdata = [];
        if(!$list || count($list)==0) {
            $rdata['status'] = 0;
            $rdata['info'] = '队列不存在或者队列中无标的！';
            $this->backJson($rdata);
        }

        $ingKey = Redis::getKey('autoInvesting');
        $ing = Redis::get($ingKey);
        if($ing) {
            $rdata['status'] = 0;
            $rdata['info'] = '标的['.$ing.']正在自动投标，请稍后再试！';
            $this->backJson($rdata);
        }

        if(Task::add('autobid', ['odds'=>$list])) {
            Odd::whereIn('oddNumber', $list)->update(['isATBiding'=>1, 'lookstatus'=>1]);
            Redis::delete($key);
            Flash::success('添加自动投标任务成功！');
            $rdata['status'] = 1;
            $rdata['info'] = '添加自动投标任务成功！';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '添加自动投标任务失败！';
            $this->backJson($rdata);
        }
    }

    /**
     * 编辑借款
     */
    public function editAction(){
        $oddNumber = $this->getQuery('oddNumber', '');
        $continue = $this->getQuery('continue', 0);
        $garage = VideoArea::get();
        $odd = null;
        $receiptUser = null;
        $info = null;
        if($oddNumber){
            $odd = Odd::where('oddNumber', $oddNumber)->first();
            if($odd->receiptUserId) {
                $receiptUser = User::where('userId', $odd->receiptUserId)->first(['userId', 'username']);
            }
            $info = OddInfo::where('oddNumber', $odd->oddNumber)->first();
            if(isset($info) && $info->cardNum){
                $info->cardNum = json_decode($info->cardNum,true);
                $info->cardType = json_decode($info->cardType,true);
                $info->cardIDCode = json_decode($info->cardIDCode,true);
                $info->cardEngine = json_decode($info->cardEngine,true);
                $info->insuranceMoney = json_decode($info->insuranceMoney,true);
                $info->insuranceType = json_decode($info->insuranceType,true);
            }
            $userinfo = User::where('userId', $odd->userId)->first();
        } else {
            $odd = new Odd();
            $odd->isCr = 1;
            $odd->startMoney = 100;
            $info = new OddInfo();
            $userinfo = new User();
        }
        $this->display('edit', [
            'odd'=>$odd, 
            'info'=>$info, 
            'userinfo'=>$userinfo, 
            'garage'=>$garage, 
            'receiptUser'=>$receiptUser, 
            'continue'=>$continue
        ]);
    }


    /**
     * 保存借款
     */
    public function saveAction(){
        $params = $this->getAllPost();
        $odd = null;
        $isUpdate = false;
        if(isset($params['oddNumber']) && $params['oddNumber']) {
            $odd = Odd::where('oddNumber', $params['oddNumber'])->first();
            $isUpdate = true;
        } else {
            $odd = new Odd();
        }

        if($params['oddTitle'] == ''){
            $rdata['status'] = 0;
            $rdata['info'] = '请输入借款标题!';
            $this->backJson($rdata);
        }
        
        if($params['oddMoney'] == 0){
            $rdata['status'] = 4;
            $rdata['info'] = '发标金额不能为零!';
            $this->backJson($rdata);
        }

        if($params['openTime'] == ''){
            $rdata['status'] = 0;
            $rdata['info'] = '请选择发标时间!';
            $this->backJson($rdata);
        }

        if($params['oddType'] == ''){
            $rdata['status'] = 0;
            $rdata['info'] = '请选择标的类型!';
            $this->backJson($rdata);
        }

        $user = User::where('username', $params['username'])->first();
        
        if(!$user) {
            $rdata['status'] = 0;
            $rdata['info'] = '借款用户不存在!';
            $this->backJson($rdata);
        }
        if($user->custody_id=='') {
            $rdata['status'] = 0;
            $rdata['info'] = '借款用户未开通存管!';
            $this->backJson($rdata);
        }
        if((!$odd->progress || $odd->progress=='prep' || $odd->progress=='published') 
            && $params['investType']==0 && $params['lookstatus']==1) {
            $params['lookstatus'] = 0;
        }

        $custodyEnv = Handler::getConfig('env');
        if($custodyEnv=='uat' || $custodyEnv=='sit') {
            $params['recUsername'] = '';
        }
        
        $odd->oddYearRate = floatval($params['oddYearRate'])/100;
        $odd->oddReward = floatval($params['oddReward'] / 100);
        $odd->userId = $user->userId;
        $odd->operator = $this->getUser()->userId;
        $odd->oddMoney = $params['oddMoney'];
        $odd->oddTitle = $params['oddTitle'];
        $odd->investType = $params['investType'];
        $odd->riskLevel = $params['riskLevel'];
        $odd->oddStyle = $params['oddStyle'];
        $odd->oddType = $params['oddType'];
        $odd->startMoney = $params['startMoney'];
        $odd->oddBorrowPeriod = $params['oddBorrowPeriod'];
        $odd->oddRepaymentStyle = $params['oddRepaymentStyle'];
        $odd->oddBorrowValidTime = $params['oddBorrowValidTime'];
        $odd->oddBorrowStyle = $params['oddBorrowStyle'];
        $odd->openTime = $params['openTime'];
        $odd->serviceFee = $params['serviceFee']?$params['serviceFee']:0;
        
        //$odd->isCr = $params['isCr'];
        $odd->lookstatus = $params['lookstatus'];
        
        $receiptUser = null;
        if(isset($params['recUsername']) && $params['recUsername']!='') {
            $receiptUser = User::where('username', $params['recUsername'])->first(['userId', 'username', 'custody_id']);
        } else {
            $receiptUser = $user;
        }
        
        if(!$receiptUser) {
            $rdata['status'] = 0;
            $rdata['info'] = '收款人不存在!';
            $this->backJson($rdata);
        }
        if($receiptUser->custody_id=='') {
            $rdata['status'] = 0;
            $rdata['info'] = '收款人未开通存管!';
            $this->backJson($rdata);
        }
        $odd->receiptUserId = $receiptUser->userId;

        $info = [];
        $info['oddLoanRemark'] = $params['oddLoanRemark'];
        $info['repaySource'] = $params['repaySource'];
        $info['overdueTreat'] = $params['overdueTreat'];
        $info['idPhotos'] = $params['idPhotos'];
        $info['oddExteriorPhotos'] = $params['oddExteriorPhotos'];
        $info['oddPropertyPhotos'] = $params['oddPropertyPhotos'];
        $info['otherPhotos'] = $params['otherPhotos'];

        $info['cardNum'] = json_encode($params['cardNum']);
        $info['cardType'] = json_encode($params['cardType']);
        $info['cardIDCode'] = json_encode($params['cardIDCode']);
        $info['cardEngine'] = json_encode($params['cardEngine']);
        $info['insuranceMoney'] = json_encode($params['insuranceMoney']);
        $info['insuranceType'] = json_encode($params['insuranceType']);
        
        $info['totalInsurance'] = round($params['totalInsurance'],2);
        $info['insuranceCompany'] = $params['insuranceCompany'];

        $info['needcardnum'] = $params['needcardnum'];
        if($params['needcardnum'] == '350105197812231517'){
            $info['needname'] = '李雍';
        }elseif($params['needcardnum'] == '350111198403083914'){
            $info['needname'] = '高辉';
        }elseif($params['needcardnum'] == '350121196905093518'){
            $info['needname'] = '林鼎';
        }

        $info['third'] = $params['third'];
        $info['thirdname'] = $params['thirdname'];
        $info['thirdcard'] = $params['thirdcard'];
        $info['houseaddr'] = $params['houseaddr'];
        $info['housecard'] = $params['housecard'];
        $info['housespace'] = $params['housespace'];

        $userinfo = [];
        $userinfo['city'] = $params['city'];
        $userinfo['credit'] = $params['credit'];
        $userinfo['maritalstatus'] = $params['maritalstatus'];
        $userinfo['sex'] = $params['sex'];
        $userinfo['hidename'] = $params['hidename'];
        $userinfo['companyType'] = $params['companyType'];

        if(!$isUpdate) {
            $odd->addtime = date('Y-m-d H:i:s');
            $odd->oddNumber = Odd::generateNumber();
        }

        if($odd->save()) {
            if($isUpdate) {
                OddInfo::where('oddNumber', $odd->oddNumber)->update($info);
            } else {
                $info['oddNumber'] = $odd->oddNumber;
                OddInfo::insert($info);
            }
            
            User::where('userId', $odd->userId)->update($userinfo);

            Flash::success('操作成功!');
            $rdata['status'] = 1;
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '有点问题!';
            $this->backJson($rdata);
        }
    }

    public function publishAction() {
        $oddNumber = $this->getPost('oddNumber', '');
        $rdata = [];
        if($oddNumber=='') {
            $rdata['status'] = 0;
            $rdata['info'] = '请传入标的号!';
            $this->backJson($rdata);
        }
        $odd = Odd::where('oddNumber', $oddNumber)->first();
        if(!$odd) {
            $rdata['status'] = 0;
            $rdata['info'] = '标的不存在!';
            $this->backJson($rdata);
        }
        $result = API::publish($odd);
        if($result['status']) {
            $rdata['status'] = 1;
            $rdata['info'] = '发布成功!';
            Flash::success('发布成功!');
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = $result['msg'];
            $this->backJson($rdata);
        }
    }

    /**
     * 发布、初审拒绝
     */
    public function refuseAction() {
        $oddNumber = $this->getQuery('oddNumber', '');
        $rdata = [];
        if($oddNumber=='') {
            $rdata['status'] = 0;
            $rdata['info'] = '请传入标的号!';
            $this->backJson($rdata);
        }
        $odd = Odd::where('oddNumber', $oddNumber)->first();
        if(!$odd) {
            $rdata['status'] = 0;
            $rdata['info'] = '标的不存在!';
            $this->backJson($rdata);
        }
        if($odd->progress!='published' && $odd->progress!='prep') {
            $rdata['status'] = 0;
            $rdata['info'] = '标的状态不对!';
            $this->backJson($rdata);
        }

        $result = [];
        if($odd->progress=='published') {
            $result = API::cancelOdd($odd);
        } else {
            $count = Odd::where('oddNumber', $odd->oddNumber)->update([
                'progress'=>'fail', 
                'oddTrialTime'=>date('Y-m-d H:i:s'), 
                'oddTrialRemark'=>'发布拒绝',
            ]);
            if($count) {
                $result['status'] = true;
                $result['msg'] = '发布拒绝成功！';
            } else {
                $result['status'] = false;
                $result['msg'] = '数据异常！';
            }
        }
        
        if($result['status']) {
            Flash::success('初审拒绝成功!');
            $rdata['status'] = 1;
            $rdata['info'] = '初审拒绝成功!';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = $result['msg'];
            $this->backJson($rdata);
        }
    }

    /**
     * 获取用户信息
     */
    public function getUserAction(){
        $username = $this->getQuery('username');
        $user = User::where('username', $username)->first(['username', 'credit', 'sex', 'city', 'userId', 'custody_id', 'phone', 'name', 'maritalstatus', 'userType', 'hidename', 'companyType']);
        if($user){
            $this->backJson(['status'=>1, 'info'=>'用户存在！', 'user'=>$user]);
        }else{
            $this->backJson(['status'=>0, 'info'=>'用户不存在！']);
        }
    }

    /**
     * 约标操作
     */
    public function orderAction(){
        $userId = $this->getPost('userId', '');
        if(!$userId){
        	$userId = '';
        }
        $oddNumber = $this->getPost('oddNumber');
        if(is_numeric($oddNumber)){
            $re = Odd::where('oddNumber',$oddNumber)->update(['appointUserId'=>$userId]);
        }else{
            $re = '';
        }
        if($re) {
            $rdata['info'] = '操作成功!';
            $this->backJson($rdata);
        } else {
            $rdata['info'] = '有点问题!';
            $this->backJson($rdata);
        }
    }

    /**
     * 标的还原
     */
    public function rebackAction() {
        $oddNumber = $this->getPost('oddNumber', '');
        $odd = Odd::where('oddNumber', $oddNumber)->first();
        $rdata = [];
        if(!$odd) {
            $rdata['status'] = 0;
            $rdata['info'] = '原标不存在!';
            $this->backJson($rdata);
        }
        if($odd->progress!='fail') {
            $rdata['status'] = 0;
            $rdata['info'] = '原标未失效!';
            $this->backJson($rdata);
        }

        $newOdd = $odd->replicate(['id']);
        $newOdd->oddNumber = Odd::generateNumber();
        $newOdd->progress = 'prep';
        $newOdd->addtime = date('Y-m-d H:i:s');
        $newOdd->successMoney = 0;
        $newOdd->oddTrialRemark = '';
        $newOdd->oddRehearRemark = '';
        $newOdd->successMoney = 0;
        $newOdd->receiptStatus = 0;
        $status1 = $newOdd->push();

        $info = OddInfo::where('oddNumber', $oddNumber)->first();
        $newInfo = $info->replicate(['id']);
        $newInfo->oddNumber = $newOdd->oddNumber;
        $status2 = $newInfo->push();

        if($status1 && $status2) {
            Flash::success('标的还原成功，可以开始修改信息了！');
            $rdata['status'] = 1;
            $rdata['info'] = '标的还原成功!';
            $rdata['oddNumber'] = $newOdd->oddNumber;
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '标的还原异常!';
            $this->backJson($rdata);
        }
    }
}
