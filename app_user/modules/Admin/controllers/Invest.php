<?php
use Admin as Controller;
use Illuminate\Database\Capsule\Manager as DB;
use traits\PaginatorInit;
use models\User;
use tools\API;
use models\OddMoney;
use helpers\ExcelHelper;

class InvestController extends Controller{

	public $menu = 'repayment';
	use PaginatorInit;
    var $key = false;

    /**
     * 债权列表
     */
    public function claimsAction(){
        $this->menu = 'auto';
    	$this->submenu = 'claims';
    	$excel = $this->getQuery('excel', 0);
        $queries = $this->queries->defaults(['startTime'=>'', 'endTime'=>'', 'id'=>'', 'progress'=>'start', 'searchType'=>'', 'searchContent'=>'']);
    	$list = DB::table('work_creditass')->where('progress','<>','fail');
        if($queries->searchContent!='') {
            if(in_array($queries->searchType, ['userId', 'username', 'phone', 'name'])) {
                $user = User::where($queries->searchType, $queries->searchContent)->first();
                $list->where('userId', $user->userId);
            } else {
                if($queries->searchType == 'id'){
                    $queries->searchContent -= 80000000; 
                }
                $list->where($queries->searchType,'like','%'.$queries->searchContent.'%');
                if($queries->searchType == 'id'){
                    $queries->searchContent += 80000000; 
                }
            }
        }
        if($queries->startTime!='') {
            $list->where('addtime', '>=', $queries->startTime.' 00:00:00');
        }
        if($queries->endTime!='') {
            $list->where('endtime', '<=', $queries->endTime.' 23:59:59');
        }
        if($queries->progress!='all') {
            $list->where('progress', '=',$queries->progress);
            $queries->progress = 'all';
        }
        if($excel) {
        	$list = $list->orderBy('id','desc')->get();
        	$other = [
        		'title' => '债权列表',
        		'columns' => [
        			'id' => ['name' => '转让编号'],
        			'userId' => ['name' => '操作用户'],
        			'addtime' => ['name' => '提交时间'],
        			'oddNumber' => ['name' => '原标的', 'type'=>'string'],
        			'money' => ['name' => '债权本金'],
        			'progress' => ['name' => '状态'],
        		],
        	];
        	$excelRecords = [];
        	foreach ($list as $value) {
        		$row = [];
        		$row['id']  = $value->id + 8000;
        		$row['userId'] = $value->userId;
        		$row['addtime'] = $value->addtime;
        		$row['oddNumber'] = $value->oddNumber;
        		$row['money'] = $value->money;
        		$row['progress'] = ($value->progress == 'run') ? '结束' : '筹款中';
        		$excelRecords[] = $row;
        	}
        	ExcelHelper::getDataExcel($excelRecords, $other);
        } else {
        	$list = $list->orderBy('id','desc')->paginate(15);
        	$list->appends($queries->all());
        }
        $this->display('claims',['list'=>$list,'queries'=>$queries]);
    }



    /**
     * 债权详情
     */
    public function detailAction(){
    	$this->submenu = 'claims';
    	$queries = $this->queries->defaults(['id'=>'']);
    	$id = $queries->id;
    	$data = DB::table('work_oddmoney as a')->leftjoin('work_task as s','a.tradeNo','=','s.tradeNo')->where('bid',$id)->where('a.status','<>','-1')->paginate(15);
    	$data->appends($queries->all());
    	$this->display('detail',['list'=>$data]);
    }

    /**
     * 债权复审
     */
    public function ckrehearAction(){
        $oddMoneyId = $this->getPost('oddMoneyId');
        if(!empty($oddMoneyId)){
            $array['oddNumber'] = trim($oddMoneyId);
            $array['loanServiceFees'] = 0;   //借款服务费
            $array['status'] = 'Cy';
            $status = API::rehear($array);
            if($status) {
                Flash::success('复审成功！');
                $rdata['status'] = 1;
                $this->backJson($rdata);
            } else {
                $rdata['status'] = 0;
                $rdata['info'] = API::$msg;
                $this->backJson($rdata);
            }
        }
    }


    /**
     * 投资记录
     */
    public function investsAction(){
        $this->menu = 'user';
        $this->submenu = 'invest';
        $queries = $this->queries->defaults(['startTime'=>'', 'endTime'=>'', 'type'=>'all', 'searchType'=>'', 'searchContent'=>'']);
        $list = OddMoney::with('pcrtr','ancun')->where('work_oddmoney.type','<>','loan');
        if($queries->searchContent!='') {
            if(in_array($queries->searchType, ['username', 'phone', 'name'])) {
                $user = User::where($queries->searchType, $queries->searchContent)->first();
                $list->where('work_oddmoney.userId', $user->userId);
            } else {
                $list->where('work_oddmoney.'.$queries->searchType,'like','%'.$queries->searchContent.'%');
            }
        }
        if($queries->startTime!='') {
            $list->where('addtime', '>=', $queries->startTime.' 00:00:00');
        }
        if($queries->startTime!='') {
            $list->where('addtime', '<=', $queries->endTime.' 23:59:59');
        }
        if($queries->type!='all') {
            $list->where('type', '=',$queries->type);
        }
        $list = $list->orderBy('work_oddmoney.id','desc')->paginate(15);
        $list->appends($queries->all());
        $this->display('invests',['list'=>$list,'queries'=>$queries]);
    }



}