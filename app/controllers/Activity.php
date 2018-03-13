<?php
use traits\PaginatorInit;
use tools\Queries;
use models\Activity;
use models\ActUserPrize;
use models\ActPrize;
use models\ActUserAddress;
use models\User;
use models\GQLottery;
use models\Lottery;
use models\Attribute;
use helpers\NumberHelper;
use factories\RedisFactory;
use Illuminate\Database\Capsule\Manager as DB;
/**
 * ActivityController
 * 最新活动
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class ActivityController extends Controller {
	use PaginatorInit;

	public $menu = 'activity';
	public $submenu = 'list';
	private $exchangeBegin = '2016-12-12 00:00:00';
	private $exchangeEnd = '2016-12-26 00:00:00';

	public $actions = [
		'newyear2018' => 'actions/act/NewYear2018Action.php',
		'newyear2017' => 'actions/act/NewYear2017Action.php',
		'redpack201706' => 'actions/act/Redpack201706Action.php',
		'billion' => 'actions/act/BillionAction.php',
		'activity201709' => 'actions/act/Activity201709Action.php',
	];

	/**
	 * 首页
	 * @return mixed
	 */
	public function listAction() {
		$this->submenu = 'list';
		$activities = Activity::where('lookStatus','y')
			->where('type', 'online')
			->orderBy('startDate', 'desc')
			->paginate(6);
		$this->display('list',['activities'=>$activities]);
	}

	/**
	 * 首页
	 * @return mixed
	 */
	public function offlineAction() {
		$this->submenu = 'offline';
		$records = Activity::where('lookStatus', 'y')->where('type', 'offline')->orderBy('startDate', 'desc')->get();
		$this->display('offline',['records'=>$records]);
	}

	/**
	 * 子页
	 * @return mixed
	 */
	public function showAction() {
		$this->submenu = 'show';
		$this->display('show');
	}

	/**
	 * 活动页（红色页）
	 * @return mixed
	 */
	public function regAction() {
		$this->submenu = 'reg';
		$this->display('reg');
	}

	/**
	 * 投监会考察主题页面
	 * @return mixed
	 */
	public function tjInspectAction() {
		$this->submenu = 'show';
		$this->display('tjInspect');
	}

	/**
	 * 金融办会议主题页面
	 * @return mixed
	 */
	public function jrMeetingAction() {
		$this->submenu = 'show';
		$this->display('jrMeeting');
	}


	/**
	 * 活动页面（现金卷兑换活动：2016.1.23—2016.2.23）
	 * @return mixed
	 */
	public function exchangeAction() {
		$user = $this->getUser();
		$address = false;
		$records = [];
		if($user) {
			$address = ActUserAddress::where('userId', $user->userId)->first();
			$records = ActUserPrize::where('userId', $user->userId)->with('prize')->get();
		}
		$allRecords = ActUserPrize::where('status', 1)
			->orWhere('status', 2)
			->with('user', 'prize')
			->orderBy('addtime', 'desc')
			->limit(20)
			->get();
		$this->display('exchange', ['allRecords'=>$allRecords, 'records'=>$records, 'user'=>$user, 'address'=>$address]);
	}

	/**
	 * 兑换奖品
	 * @return mixed
	 */
	public function doExchangeAction() {

		$beginTime = strtotime($this->exchangeBegin);
		$endTime = strtotime($this->exchangeEnd);
		if($beginTime>time()) {
			$this->backJson(['status'=>0, 'info'=>date('Y年m月d日', $beginTime).'零点开放兑换！']);
		}
		if($endTime<time()) {
			$this->backJson(['status'=>0, 'info'=>'兑换已经结束！']);
		}
		$user = $this->getUser();
		$userId = $user->userId;
		$prizeId = intval($this->getPost('prizeId', 0));
		$noNeed = [1, 2, 3, 4, 5, 6, 7, 8];
		if((!in_array($prizeId, $noNeed))&&(!ActUserAddress::isUserSet($userId))) {
			$this->backJson(['status'=>-1, 'info'=>'您还未设置收货地址，请先设置！']);
		}
		$prize = ActPrize::find($prizeId);
		if(!$prize) {
			$this->backJson(['status'=>0, 'info'=>'奖品不存在！']);
		}

		if($user->imiMoney<$prize->prizeCash) {
			$this->backJson(['status'=>0, 'info'=>'现金券不足，兑换失败！']);
		}

		$userPrize = new ActUserPrize();
		$userPrize->prizeId = $prize->id;
		$userPrize->userId = $userId;
		$userPrize->status = 1;
		$userPrize->addtime = date('Y-m-d H:i:s');

		if($userPrize->save()) {
			$data = [];
			$data['imiFreezeMoney'] = $user->imiFreezeMoney + $prize->prizeCash;
			$data['imiMoney'] = $user->imiMoney - $prize->prizeCash;
			User::where('userId', $userId)->update($data);

			Flash::success('申请成功！');
			$this->backJson(['status'=>1, 'info'=>'申请成功！']);
		}
	}

	/**
	 * 取消兑换奖品
	 * @return mixed
	 */
	public function deleteExchangeAction() {
		$beginTime = strtotime($this->exchangeBegin);
		$endTime = strtotime($this->exchangeEnd);
		if($beginTime>time()) {
			$this->backJson(['status'=>0, 'info'=>date('Y年m月d日', $beginTime).'零点开放兑换！']);
		}
		if($endTime<time()) {
			$this->backJson(['status'=>0, 'info'=>'兑换已经结束！']);
		}
		$user = $this->getUser();
		$userId = $user->userId;
		$recordId = intval($this->getPost('recordId', 0));
		$record = ActUserPrize::find($recordId);
		if(!$record) {
			$this->backJson(['status'=>0, 'info'=>'兑换记录不存在！']);
		}
		/*if ($record['status']==1) {
			$this->backJson(['status'=>0, 'info'=>'兑换已审核，请联系客服修改！']);
		}*/
		if ($record->status==2) {
			$this->backJson(['status'=>0, 'info'=>'兑换已发货，请联系客服！']);
		}
		if ($record->status==-1) {
			$this->backJson(['status'=>0, 'info'=>'兑换审核失败，请联系客服！']);
		}

		$prize = $record->prize;

		$status = $record->delete();
		if($status) {
			$data = [];
			$data['imiFreezeMoney'] = $user->imiFreezeMoney - $prize->prizeCash;
			$data['imiMoney'] = $user->imiMoney + $prize->prizeCash;
			User::where('userId', $userId)->update($data);
			Flash::success('取消成功！');
			$this->backJson(['status'=>1, 'info'=>'取消成功！']);
		}
	}

	public function setAddressAction() {
		$user = $this->getUser();
		$userId = $user->userId;
		$params = $this->getAllPost(true);

		$userAddress = null;
		if(ActUserAddress::isUserSet($userId)) {
			$userAddress = ActUserAddress::where('userId', $userId)->first();
		} else {
			$userAddress = new ActUserAddress();
		}
		$userAddress->userId = $userId;
		$userAddress->address = $params['address'];
		$userAddress->addressDetail = $params['addressDetail'];
		$userAddress->addtime = date('Y-m-d H:i:s');
		$userAddress->name = $params['name'];
		$userAddress->phone = $params['phone'];
		$userAddress->zipcode = $params['zipcode'];

		if($userAddress->save()) {
			Flash::success('设置地址成功！');
			$this->backJson(['status'=>1, 'info'=>'设置地址成功！']);
		} else {
			$this->backJson(['status'=>0, 'info'=>'设置地址失败！']);
		}
	}

	/**
	 * 获取奖品信息ajax
	 * @return mixed
	 */
	public function getActPrizeAction() {
		$beginTime = strtotime($this->exchangeBegin);
		$endTime = strtotime($this->exchangeEnd);
		if($beginTime>time()) {
			$this->backJson(['status'=>0, 'info'=>date('Y年m月d日', $beginTime).'零点开放兑换！']);
		}
		if($endTime<time()) {
			$this->backJson(['status'=>0, 'info'=>'兑换已经结束！']);
		}

		$user = $this->getUser();
		$userId = $user->userId;
		$noNeed = [1, 2, 3, 4, 5, 6, 7, 8];
		$prizeId = intval($this->getPost('prizeId', 0));
		if((!in_array($prizeId, $noNeed))&&(!ActUserAddress::isUserSet($userId))) {
			$this->backJson(['status'=>-1, 'info'=>'您还未设置收货地址，请先设置！']);
		}
		$prize = ActPrize::find($prizeId);
		$rdata = [];
		if($prize) {
			$rdata['cash'] = $user->imiMoney;
			$rdata['prize'] = $prize;
			$rdata['status'] = 1;
			$this->backJson($rdata);
		} else {
			$rdata['info'] = '奖品不存在！';
			$rdata['status'] = 0;
			$this->backJson($rdata);
		}
	}

	/**
	 * 推荐活动
	 * @return mixed
	 */
	public function spreadAction() {
		$this->submenu = 'spread';
		$this->display('spread');
	}

	/**
	 * 交流会活动
	 * @return mixed
	 */
	public function jiaoliuAction() {
		$this->submenu = 'jiaoliu';
		$this->display('jiaoliu');
	}

	/**
	 * 国庆活动（2016）
	 * @return mixed
	 */
	public function guoqing2016Action() {
		$this->submenu = 'guoqing2016';
		
		$user = $this->getUser();
		$gqLotteries = [];
		if($user) {
			$gqLotteries = GQLottery::where('userId', $user->userId)
				->orderByRaw('field(type, ?, ?, ?)', ['A', 'B', 'C'])
				->orderBy('created_at', 'asc')
				->get();
		}
		$result = GQLottery::groupBy('type')->get(['type', DB::raw('count(*) as total')]);
		$counts = ['A'=>0, 'B'=>0, 'C'=>0];
		foreach ($result as $row) {
			$counts[$row->type] = $row->total;
		}

		$this->display('guoqing2016', ['user'=>$user, 'gqLotteries'=>$gqLotteries, 'counts'=>$counts]);
	}

	/**
	 * 国庆活动（2016）- 获取抽奖券
	 * @return mixed
	 */
	public function getGQLotteryAction() {
		if(time()<strtotime('2016-10-16 00:00:00')) {
			$rdata['status'] = 0;
			$rdata['info'] = '10月16日零时开放兑换！';
			$this->backJson($rdata);
		}
		if(time()>=strtotime('2016-10-18 00:00:00')) {
			$rdata['status'] = 0;
			$rdata['info'] = '兑换时间已经结束！';
			$this->backJson($rdata);
		}
		$types = [
			'A'=>['need'=>50000],
			'B'=>['need'=>20000],
			'C'=>['need'=>10000],
		];

		$type = $this->getPost('type', 'A');

		if(!isset($types[$type])) {
			$rdata['status'] = 0;
			$rdata['info'] = '抽奖券不存在！';
			$this->backJson($rdata);
		}
		$ctype = $types[$type];

		$user = $this->getUser();

		if($user->imiMoney<$ctype['need']) {
			$rdata['status'] = 0;
			$rdata['info'] = '您的幸运币不足！';
			$this->backJson($rdata);
		}
		
		DB::beginTransaction();
		$attribute = Attribute::where('identity', 'gq_num_'.$type)->lock()->first();
		$num = 0;
		if($attribute) {
			$num = $attribute->value;
			$attribute->value = $attribute->value + 1;
			$attribute->save();
		} else {
			$attribute = new Attribute();
			$attribute->name = '国庆抽奖券'.$type.'的数量';
			$attribute->identity = 'gq_num_'.$type;
			$attribute->type = 'string';
			$attribute->status = 0;
			$attribute->value = 1;
			$attribute->save();
		}

		$num += 1;
		$num = NumberHelper::zeroPrefix($num, 4);
    	$num = $type . $num;

		$gqLottery = new GQLottery();
		$gqLottery->userId = $user->userId;
		$gqLottery->type = $type;
		$gqLottery->num = $num;

		if($gqLottery->save()) {
			$user->imiMoney = $user->imiMoney - $ctype['need'];
			$user->save();
			DB::commit();
			$rdata['status'] = 1;
			$rdata['info'] = '获取抽奖券成功！';
			$rdata['data']['imiMoney'] = $user->imiMoney;
			$rdata['data']['lottery']['num'] = $gqLottery->num;
			$rdata['data']['lottery']['type'] = $gqLottery->type;
			$this->backJson($rdata);
		} else {
			DB::rollback();
			$rdata['status'] = 0;
			$rdata['info'] = '获取抽奖券失败，请联系客服！';
			$this->backJson($rdata);
		}
	}

	/**
	 * 国庆活动（2016）- 获取加息券
	 * @return mixed
	 */
	public function getLotteryAction() {
		if(time()<strtotime('2016-10-16 00:00:00')) {
			$rdata['status'] = 0;
			$rdata['info'] = '10月16日零时开放兑换！';
			$this->backJson($rdata);
		}
		if(time()>=strtotime('2016-10-19 00:00:00')) {
			$rdata['status'] = 0;
			$rdata['info'] = '兑换时间已经结束！';
			$this->backJson($rdata);
		}
		$types = [
			1=>['need'=>10000, 'name'=>'0.1%加息券', 'money_rate'=>'0.001', 'type'=>'interest'],
			2=>['need'=>20000, 'name'=>'0.2%加息券', 'money_rate'=>'0.002', 'type'=>'interest'],
			3=>['need'=>40000, 'name'=>'0.4%加息券', 'money_rate'=>'0.004', 'type'=>'interest'],
			4=>['need'=>60000, 'name'=>'0.6%加息券', 'money_rate'=>'0.006', 'type'=>'interest'],
			5=>['need'=>80000, 'name'=>'0.8%加息券', 'money_rate'=>'0.008', 'type'=>'interest'],
			6=>['need'=>100000, 'name'=>'1%加息券', 'money_rate'=>'0.01', 'type'=>'interest']
		];

		$type = $this->getPost('type', 0);
		if(!isset($types[$type])) {
			$rdata['status'] = 0;
			$rdata['info'] = '奖券不存在！';
			$this->backJson($rdata);
		}
		$ctype = $types[$type];

		$user = $this->getUser();

		$rdata = [];
		if($user->cashMoney<$ctype['need']) {
			$rdata['status'] = 0;
			$rdata['info'] = '您的活动投资金额不足！';
			$this->backJson($rdata);
		}

		$lottery = Lottery::where('type', $ctype['type'])
			->where('status', Lottery::STATUS_NOGET)
			->where('money_rate', $ctype['money_rate'])
			->first();

		if(!$lottery) {
			$rdata['status'] = 0;
			$rdata['info'] = '奖券不足，请联系客服！';
			$this->backJson($rdata);
		}

		if($lottery->assign($user)) {
			
			$user->cashMoney = $user->cashMoney - $ctype['need'];
			$user->save();

			$rdata['status'] = 1;
			$rdata['info'] = '获取奖券成功！';
			$rdata['data']['cashMoney'] = $user->cashMoney;
			$this->backJson($rdata);
		} else {
			$rdata['status'] = 0;
			$rdata['info'] = '获取奖券失败，请联系客服！';
			$this->backJson($rdata);
		}
	}

	public function meetingAction() {
		$this->submenu = 'meeting';
		$user = $this->getUser();
		$redis = RedisFactory::create();
		if($this->isPost()) {
			if(!$user) {
				$rdata['status'] = 0;
				$rdata['info'] = '请先登录！';
				$this->backJson($rdata);
			}
			$status = $redis->sAdd('meeting_users' , $user->userId);
			if($status) {
				$rdata['status'] = 1;
				$rdata['info'] = '报名成功！';
				Flash::success('报名成功！');
				$this->backJson($rdata);
			} else {
				$rdata['status'] = 0;
				$rdata['info'] = '您已经报名，请勿重复操作！';
				$this->backJson($rdata);
			}
		} else {
			$isMeeting = false;
			if($user) {
				$isMeeting = $redis->sIsMember('meeting_users', $user->userId);
			}
			$this->display('meeting', ['user'=>$user, 'isMeeting'=>$isMeeting]);
		}
	}

	public function visitAction() {
		$this->display('visit');
	}

	public function visit201705Action() {
		$this->display('visit201705');
	}
}