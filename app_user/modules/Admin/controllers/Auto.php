<?php
use Admin as Controller;
use Yaf\Registry;
use traits\PaginatorInit;
use models\User;
use models\UserLog;
use models\AutoInvest;
use Illuminate\Database\Capsule\Manager as DB;
/**
 * AutoController
 * 后台自动投标
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class AutoController extends Controller {
	use PaginatorInit;

	public $menu = 'auto';

	/**
     * 首页
     * @return  mixed
     */
	public function indexAction() {
		$this->submenu = 'index';
		$queries = $this->queries->defaults(['searchType'=>'username', 'searchContent'=>'', 'beginTime'=>'', 'endTime'=>'']);
        $searchType = $queries->searchType;
        $searchContent = $queries->searchContent;

        $user = null;
        if($searchContent!='') {
	        if($searchType=='username') {
	        	$user = User::where('username', $searchContent)->first();
	        } else if($searchType=='phone') {
	        	$user = User::where('phone', $searchContent)->first();
	        } else if($searchType=='userId') {
	        	$user = User::where('userId', $searchContent)->first();
	        }
        }

        $builder = AutoInvest::with('user', 'queue');

        if($user) {
        	$builder->where('userId', $user->userId);
        }
        
        $records = $builder->paginate(20);
        $records->appends($queries->all());
        foreach ($records as $record){
            $arr = explode('#',trim($record->types,'#'));
            foreach ($arr as $key => $item){
                $arr_new[$key]['name'] =  AutoInvest::$types[$item]['name'];
                if (AutoInvest::$types[$item]['type'] == 'diya'){
                    $arr_new[$key]['type'] = '抵押';
                }elseif (AutoInvest::$types[$item]['type'] == 'xinyong'){
                    $arr_new[$key]['type'] = '信用';
                }else{
                    $arr_new[$key]['type'] = '担保';
                }
                $type_new[$key] = implode('-',$arr_new[$key]);
            }
            $record['type'] = implode(',',$type_new);
        }
		$this->display('index', ['records'=>$records, 'queries'=>$queries]);
	}

	/**
     * 日志
     * @return  mixed
     */
	public function logsAction() {
		$this->submenu = 'logs';
		
		$queries = $this->queries->defaults(['searchType'=>'username', 'searchContent'=>'', 'beginTime'=>'', 'endTime'=>'']);
        $beginTime = $queries->beginTime;
        $endTime = $queries->endTime;
        $searchType = $queries->searchType;
        $searchContent = $queries->searchContent;

        $user = null;
        if($searchContent!='') {
	        if($searchType=='username') {
	        	$user = User::where('username', $searchContent)->first();
	        } else if($searchType=='phone') {
	        	$user = User::where('phone', $searchContent)->first();
	        } else if($searchType=='userId') {
	        	$user = User::where('userId', $searchContent)->first();
	        }
        }
        
        $userId = $user?$user->userId:null;

        $builder = UserLog::with('user')->where('userId', $userId)->where('type', 'auto');
        if($beginTime!='') {
        	$builder->where('change_time', $beginTime . ' 00:00:00');
        }
        if($endTime!='') {
        	$builder->where('change_time', $endTime . ' 23:59:59');
        }
        
        $records = $builder->orderBy('change_time', 'desc')->paginate(20);
        $records->appends($queries->all());

		$this->display('logs', ['records'=>$records, 'queries'=>$queries]);
	}

    /**
     * 队列日志
     */
    public function quelogAction(){
        $this->submenu = 'quelog';
        $queries = $this->queries->defaults(['userId'=>'']);
        $db = DB::table('user_queuelog')->where('sqlstr','like','%INSERT%')->orderBy('id','desc');
        if($queries->userId != ''){
            $db = $db->where('sqlstr','like','%'.$queries->userId.'%');
        }
        $fields = $db->paginate(10);
        $fields->appends($queries->all());
        $i = 0;
        foreach ($fields as $key => $value) {
            $str = strstr($value->sqlstr,'VALUES');
            if($str){
                $arr = explode(',',str_replace([')','('], '', substr($str,7)));
                $list[$i]['userId'] = trim($arr[0],'"');
                $list[$i]['location'] = trim($arr[1],'"');
                $list[$i]['oddNumber'] = $value->oddNumber;
            }
            $i++;
        }
        $this->display('quelog',['list'=>$list,'fields'=>$fields]);
    }
}