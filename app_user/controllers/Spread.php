<?php
use Yaf\Registry;
use tools\Pager;
use tools\API;

use models\MoneyLog;
use models\User;
use models\GradeSum;
use models\UserFriend;
use models\SpreadExtract;
use traits\PaginatorInit;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * SpreadController
 * 推广控制器
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class SpreadController extends Controller {
	use PaginatorInit;

	public $menu = 'account';
	public $submenu = 'free';
	public $mode = 'spread';
	
	/**
	 * 推广首页
	 * @return mixed
	 */
	public function indexAction() {
		$page = $this->getQuery('page', 1);
		$user = Registry::get('user');
		$userId = $user->userId;

		$friends = UserFriend::getFriendRecursive($userId);
		
		$spreadUrl = WEB_USER.'/register/'.$user->getSpreadCode();

		$count = count($friends);
		
		$pager = new Pager(['total'=>$count, 'request'=>$this->getRequest()]);

		$friendList = [];
		$total = 0;
		if($count<$pager->getLimit()) {
			$total = $count;
		} else {
			$total = $pager->getOffset() + $pager->getLimit();
		}
		$from = $pager->getOffset();
		for ($i=$from; $i<$total; $i++) {
			$friend = $friends[$i];
			$money = MoneyLog::whereRaw('userId=? and type=? and mode=?', [$userId, 'spread', 'in'])->sum('mvalue');
			$friendSum = GradeSum::whereRaw('friend=? and userId=?', [$userId, $friend['friend']])->sum('money');
			$friend['username'] = User::where('userId', $friend['friend'])->value('username');
			$friend['money'] = $money+$friendSum;
			$friendList[] = $friend;
		}

		$sum = GradeSum::where('friend', $userId)->sum('money');
		$this->display('index', ['spreadUrl'=>$spreadUrl, 'friends'=>$friendList, 'pager'=>$pager, 'user'=>$user, 'sum'=>$sum]);
	}

	/**
	 * 推广收入详情
	 * @return mixed
	 */
	public function detailAction() {
		$userId = Registry::get('user')->userId;
		$queries = $this->queries->defaults(['username'=>'', 'timeBegin'=>'', 'timeEnd'=>'']);

		$friend = false;
		if($queries->username!='') {
			$friend = User::where('username', $queries->username)->first();
		}

		$builder = MoneyLog::where('userId', $userId)
			->where('type', 'spread')
			->where('mode', 'in');
		if($friend) {
			$builder->where('investUserId', $friend['userId']);
		} else {
			if($queries->username!='') {
				$builder->where('investUserId', 0);
			}
		}
		if($queries->timeBegin!='') {
			$builder->where('time', '>=', $queries->timeBegin.' 00:00:00');
		}
		if($queries->timeEnd!='') {
			$builder->where('time', '<=', $queries->timeEnd.' 23:59:59');	
		}

		$rewards = $builder->with('investUser', 'odd')->orderBy('time', 'desc')->paginate(20);

		$this->display('detail',['rewards'=>$rewards, 'queries'=>$queries]);
	}

	/**
	 * 推广收入列表(新规则)
	 * @return mixed
	 */
	public function listAction() {
		$user = Registry::get('user');
		
		$queries = $this->queries->defaults(['username'=>'', 'timeBegin'=>'', 'timeEnd'=>'']);

		$builder = GradeSum::where('friend', $user->userId)
			->whereDoesntHave('clientSetting', function($q) {
				$q->where('spread_show', 0);
			});
		if($queries->username!='') {
			$builder->where('username', $queries->username);
		}
		if($queries->timeBegin!='') {
			$builder->where('createDate', '>=', $queries->timeBegin);
		}
		if($queries->timeEnd!='') {
			$builder->where('createDate', '<=', $queries->timeEnd);	
		}
		$records = $builder->orderBy('createDate', 'desc')->paginate(20);
		$records->appends($queries->all());

		$this->display('list',['records'=>$records, 'queries'=>$queries]);
	}

	/**
	 * 提取推广奖励(新规则)
	 * @return mixed
	 */
	public function getMoneyAction() {
		$money = floatval($this->getPost('money', 0));
		$user = $this->getUser();
		$userId = $user->userId;

		$rdata = [];
		if($money<0.01) {
			$rdata['info'] = '提取金额不能小于0.01元！';
            $rdata['status'] = 0;
            $this->backJson($rdata);
		}

		$money = _cut_float($money, 2);
		$gradeSum = $user->gradeSum;
		
		if($gradeSum<$money) {
			$rdata['info'] = '推荐奖励余额不足！';
            $rdata['status'] = 0;
            $this->backJson($rdata);
		}

		$data = [];
		$data['money'] = $money;
		$data['userId'] = $userId;
		$data['remark'] = '提取推荐奖励'.$money.'元';
        $status = API::addMoney($data);

        if($status) {
        	$spreadExtract = new SpreadExtract();
        	$spreadExtract->extract_money = $money;
        	$spreadExtract->userId = $userId;
        	$spreadExtract->created_at = date('Y-m-d H:i:s');
        	$spreadExtract->save();

	        $tradeNo = date('Ymd').(80000000+$spreadExtract->id).rand(10,99);

            User::where('userId', $userId)->update([
                'gradeSum'=>DB::raw('gradeSum-'.$money), 
                'fundMoney'=>DB::raw('fundMoney+'.$money)
            ]);

	        $log = [];
			$log['serialNumber'] = $tradeNo;
			$log['type'] = 'extract';
			$log['mode'] = 'in';
			$log['mvalue'] = $money;
			$log['remark'] = $data['remark'];

			$user->fundMoney = $user->fundMoney + $money;
	        MoneyLog::addOne($log, $user);

            Flash::success('提取推荐奖励成功！');
            $rdata['status'] = 1;
            $this->backJson($rdata);
        } else {
            $rdata['info'] = '提取推荐奖励失败！';
            $rdata['status'] = 0;
            $this->backJson($rdata);
        }
	}

	/**
	 * 推广奖励提取记录
	 * @return mixed
	 */
	public function extractRecordAction() {
		$user = $this->getUser();
		$userId = $user->userId;
		$queries = $this->queries->defaults(['timeBegin'=>'', 'timeEnd'=>'']);
		
		$builder = SpreadExtract::where('userId', $userId);

		if($queries->timeBegin!='') {
			$builder->where('created_at', '>=', $timeBegin.'00:00:00');
		}
		if($queries->timeEnd!='') {
			$builder->where('created_at', '<=', $timeEnd.'23:59:59');
		}
		
		$records = $builder->orderBy('created_at', 'desc')->paginate(20);
		$records->appends($queries->all());

		$this->display('extractRecord',['records'=>$records, 'queries'=>$queries]);
	}

}