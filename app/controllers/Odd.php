<?php
use helpers\StringHelper;
use Yaf\Registry;
use models\Odd;
use models\OddInfo;
use models\User;
use models\OddMoney;
use models\UserOffice;
use models\Interest;
use models\OddClaims;
use models\UserBid;
use models\Lottery;
use models\OddTrace;
use forms\BidForm;
use tools\Pager;
use tools\API;
use tools\Log;
use models\Gps;
use helpers\HtmlHelper;
use exceptions\HttpException;
use traits\PaginatorInit;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * OddController
 * 标的控制器
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class OddController extends Controller {
	use PaginatorInit;

	public $menu = 'odd';

	/**
	 * 标的列表
	 * @return mixed
	 */
	public function listAction() {
		$this->submenu = 'odd';
		$queries = $this->queries->defaults(['rate'=>'', 'time'=>'', 'sort'=>'normal', 'order'=>'desc']);
		$time = $queries->time;
		$rate = $queries->rate;

		$user = $this->getUser();
        $userId = $user?$user->userId:null;

		$builder = Odd::getListBuilder($userId)->where('oddStyle','normal');

		$searchTime = Odd::getSearchTime($time);
		if($searchTime) {
			$builder->whereRaw($searchTime);
		}

		$searchRate = Odd::getSearchType($rate);
		if($searchRate) {
			$builder->whereRaw($searchRate);
		}
		$sort = $queries->sort;
		$odds = Odd::sortList($builder,$sort,$queries->order)->paginate('5');
		$odds->appends($queries->all());

		$this->display('list',['odds'=>$odds,'queries'=>$queries,'idata'=>$this->getOrderButton()]);
	}

	/**
	 * 新手标的列表
	 * @return mixed
	 */
	public function newAction() {
		$this->submenu = 'odd';
		$queries = $this->queries->defaults(['rate'=>'', 'time'=>'', 'sort'=>'normal', 'order'=>'desc']);
		$time = $queries->time;
		$rate = $queries->rate;

		$user = $this->getUser();
        $userId = $user?$user->userId:null;

		$builder = Odd::getNewBuilder($userId);

		$searchTime = Odd::getSearchTime($time);
		if($searchTime) {
			$builder->whereRaw($searchTime);
		}

		$searchRate = Odd::getSearchType($rate);
		if($searchRate) {
			$builder->whereRaw($searchRate);
		}
		$sort = $queries->sort!='normal'?$queries->sort:'oddTrialTime';
		$odds = Odd::sortList($builder,$sort,$queries->order)->paginate('5');
		$odds->appends($queries->all());

		$this->display('new',['odds'=>$odds,'queries'=>$queries,'idata'=>$this->getOrderButton()]);
	}

	public function getOrderButton(){
		$queries = $this->queries->defaults(['rate'=>'', 'time'=>'', 'sort'=>'oddTrialTime', 'order'=>'desc']);
		$data['normal'] = ['order'=>'desc','b'=>'↓','class'=>''];
		$data['oddYearRate'] = ['order'=>'desc','b'=>'↓','class'=>''];
		$data['oddBorrowPeriod'] = ['order'=>'desc','b'=>'↓','class'=>''];
		$data['oddTrialTime'] = ['order'=>'desc','b'=>'↓','class'=>''];
		$data[$queries->sort]['class'] = 'biaoSeach3Li';
		$data[$queries->sort]['order'] = $queries->order=='desc'?'asc':'desc';
		$data[$queries->sort]['b'] = $queries->order=='desc'?'↓':'↑';
		return $data;
	}
	/**
	 * 标的详情
	 * @return mixed
	 */
	public function viewAction($num=0) {
		$user = $this->getUser();
        $userId = $user?$user->userId:null;

		$odd = Odd::getBuilder($num, $userId)->with('gps')->first();
		if(!$odd) {
            throw new HttpException(404);
        }

        $info = OddInfo::where('oddNumber', $num)->first();
        if(!$info){
        	$info = new Oddinfo();
        }

		$borrower = User::find($odd->userId);
		$user = Registry::get('user');

		$builder = OddMoney::where('oddNumber', $num)->where('type', 'invest')->where('status', '<>', 4);
		$count = $builder->count();
		$totalMoney = $builder->sum('money');

		$oddUserCompany = UserOffice::where('userId', $odd->userId)->first();
		$borrowMoney = Odd::where('userId', $odd->userId)->whereIn('progress', ['run','end'])->sum('oddMoney');
		$borrowSuccessCount = Odd::where('userId', $odd->userId)->whereIn('progress', ['run','end'])->count();
		$borrowCount = Odd::where('userId', $odd->userId)->whereIn('progress', ['start', 'run','end'])->count();

		$stayMoney = Interest::getStayMoneyByUser($odd->userId);
		$endCount = Odd::where('userId', $odd->userId)->where('progress','end')->count();
		$this->title = $odd->oddTitle;

		$oddTraceData = OddTrace::where('oddNumber',$odd->oddNumber)->where('type','base')->first();
		$oddTraceInfo = json_decode($oddTraceData->info);
		$oddTraces = OddTrace::where('oddNumber',$odd->oddNumber)->where('type','<>','base')->orderBy('addtime','asc')->get();

		$ingData = UserBid::where('oddNumber', $num)->where('status', '0')->first([DB::raw('sum(bidMoney) ingMoney'), DB::raw('count(*) ingCount')]);

		$repayments = Interest::where('oddNumber', $num)->get();

		if(!$odd->gps){
			$odd->gps = new Gps;
		}

		$this->display('view', [
			'oddTraceInfo'=>$oddTraceInfo,
			'oddTraces'=>$oddTraces,
			'odd'=>$odd, 
			'info'=>$info,
			'borrower'=>$borrower, 
			'user'=>$user, 
			'count'=>$count, 
			'totalMoney'=>$totalMoney, 
			'oddUserCompany'=>$oddUserCompany,
			'borrowMoney'=>$borrowMoney, //成功借款金额
			'borrowCount'=>$borrowCount, //借款次数
			'borrowSuccessCount'=>$borrowSuccessCount, //成功借款次数
			'stayMoney'=>$stayMoney, //待还本息
			'endCount'=>$endCount, //还清笔数
			'ingMoney'=>$ingData->ingMoney==null?0:$ingData->ingMoney,
			'ingCount'=>$ingData->ingCount,
			'repayments'=>$repayments,
		]);
	}

	/**
	 * 某个标的的投资人列表（ajax, 含分页）
	 * @return mixed
	 */
	public function oddTendersAction() {
		$request = $this->getRequest();
		$num = $request->getPost('num');
		$builder = OddMoney::where('oddNumber', $num)->where('type', 'invest')->where('status', '<>', 4);
		$count = $builder->count();
		$pager = new Pager(['total'=>$count, 'request'=>$request, 'isDy'=>true, 'pageSize'=>10]);
		$limit = $pager->getLimit();
		$offset = $pager->getOffset();
		$tenders = $builder->with('user')->skip($offset)->limit($limit)->get();
		$newTenders = [];
		foreach ($tenders as $key => $tender) {
			$newTender = [];
			$newTender['normalKey'] = ($pager->getPage()-1)*$limit+$key+1;
			$newTender['username'] = _hide_phone($tender->user->username);
			$newTender['money'] = $tender->money;
			$newTender['time'] = $tender->time;
			// $newTender['autoOrder'] = $tender->order?$tender->order:'无';
			$newTender['bidType'] = $tender->remark;
			$newTenders[$key] = $newTender;
		}
		$template = '<tr class="dark">'
            .  '<td>#normalKey#</td>'
            .  '<td><a href="#" target="_blank">#username#</a></td>'
            .  '<td class="text-right"><em>#money#</em>元</td>'
            .  '<td>#bidType#</td>'
            .  '<td class="text-right">#time#</td>'
            // .  '<td>#autoOrder#</td>'
            .'</tr>';
		$rdata = [];
		$rdata['pager'] = $pager->html();
		$rdata['records'] = HtmlHelper::tableRecords($newTenders, $template);
		$this->backJson($rdata);
	}

	/**
	 * 某个标的在投列表（ajax, 含分页）
	 * @return mixed
	 */
	public function ingRecordsAction() {
		$request = $this->getRequest();
		$num = $request->getPost('num');
		$builder = UserBid::where('oddNumber', $num)->where('status', '0');
		$count = $builder->count();
		$pager = new Pager(['total'=>$count, 'request'=>$request, 'isDy'=>true, 'pageSize'=>10]);
		$limit = $pager->getLimit();
		$offset = $pager->getOffset();
		$tenders = $builder->with('user')->skip($offset)->limit($limit)->get();
		$newTenders = [];
		foreach ($tenders as $key => $tender) {
			$newTender = [];
			$newTender['normalKey'] = ($pager->getPage()-1)*$limit+$key+1;
			$newTender['username'] = _hide_phone($tender->user->username);
			$newTender['money'] = $tender->bidMoney;
			$newTender['time'] = $tender->addTime;
			$lastTime = (strtotime($tender->addTime)+6*60)-time();
			if($lastTime>0) {
				$lastTime = '<span class="ing-time-down" data-time="'.$lastTime.'">'.$lastTime.'秒</span>';
			} else {
				$lastTime = '<a class="btn btn-blue btn-sm" href="/odd/'.$num.'">点击刷新</a>';
			}
			$newTender['lastTime'] = $lastTime;
			$newTenders[$key] = $newTender;
		}
		$template = '<tr class="dark">'
            .  '<td>#normalKey#</td>'
            .  '<td><a href="#" target="_blank">#username#</a></td>'
            .  '<td class="text-right"><em>#money#</em>元</td>'
            .  '<td class="text-right">#time#</td>'
            .  '<td class="text-right">#lastTime#</td>'
            .'</tr>';
		$rdata = [];
		$rdata['pager'] = $pager->html();
		$rdata['records'] = HtmlHelper::tableRecords($newTenders, $template);
		$this->backJson($rdata);
	}

	/**
	 * 债权转让列表
	 * @return mixed
	 */
	public function transferListAction() {
		$this->submenu = 'transfer';
		$oddClaims = OddClaims::getRecordBuilder()->paginate();
		$this->display('transferList', ['oddClaims'=>$oddClaims]);
	}

	/**
	 * 债权转让详情
	 * @return mixed
	 */
	public function transferAction($num=0) {
		$num = intval($num);
		if($num==0) {
			throw new HttpException(404);
		}

		$id = $num-90000000;

		$claim = OddClaims::find($id);
		if(!$claim) {
			throw new HttpException(404);
		}

        $user = Registry::get('user');

        $this->title = $claim->odd->title.'--债权转让';
		$this->display('transfer', [
			'claim'=>$claim,
			'user'=>$this->getUser()
		]);
	}

	/**
	 * 投标
	 * @return mixed
	 */
	public function bidAction() {
		$params = $this->getAllPost();
		$form = new BidForm($params);
		if($form->bid()) {
			echo $form->html;
		} else {
			unset($params['paypass']);
			Log::write('投资失败', [$params,$form->posError()], 'bid');
			Flash::error($form->posError());
			$this->redirect('/odd/'.$params['oddNumber']);
		}
	}

	/**
	 * 获取用户针对某个标的最大可投资金额（ajax）
	 * @return mixed
	 */
	public function getMaxInvestAction() {
		$oddNumber = $this->getRequest()->getPost('oddNumber');
		$rdata = [];
		if(!$oddNumber) {
			$rdata['status'] = 0;
			$rdata['info'] = '标的不存在！';
			$this->backJson($rdata);
		}
		$odd = Odd::where('oddNumber', $oddNumber)
			->where('progress', 'start')
			->first(['oddNumber', 'oddType', 'appointUserId', 'oddStyle', 'investType', 'oddBorrowPeriod', 'progress']);
		if(!$odd) {
			$rdata['status'] = 0;
			$rdata['info'] = '标的不存在！';
			$this->backJson($rdata);
		}
		
		$user = Registry::get('user');

		$money = $odd->getMaxInvest($user);
		
		$rdata['status'] = 1;
		$rdata['money'] = $money;
		$this->backJson($rdata);
	}
	
	public function getOddInfoAction() {
		$oddNumber = $this->getQuery('oddNumber', '');
		if($oddNumber=='') {
			$rdata['status'] = 0;
			$rdata['info'] = '标的编号错误！';
			$this->backJson($rdata);
		}
		$oddInfo = OddInfo::where('oddNumber', $oddNumber)->first();
		if(!$oddInfo) {
			$rdata['status'] = 1;
			$rdata['data']['info'] = false;
			$this->backJson($rdata);
		}
		$info = [];
		$info['oddExteriorPhotos'] = $oddInfo->oddExteriorPhotos==''?false:$oddInfo->getImages('oddExteriorPhotos');
		$info['oddPropertyPhotos'] = $oddInfo->oddPropertyPhotos==''?false:$oddInfo->getImages('oddPropertyPhotos');
		$info['otherPhotos'] = $oddInfo->otherPhotos==''?false:$oddInfo->getImages('otherPhotos');
		$info['controlPhotos'] = $oddInfo->controlPhotos==''?false:$oddInfo->getImages('controlPhotos');
		$info['validateCarPhotos'] = $oddInfo->validateCarPhotos==''?false:$oddInfo->getImages('validateCarPhotos');
		$info['oddLoanControl'] = _decode($oddInfo->oddLoanControl);
		$info['oddLoanControlList'] = explode(',',$oddInfo->oddLoanControlList);
		$info['contractVideoUrl'] = $oddInfo->contractVideoUrl;
		$info['oddLoanRemark'] = _decode($oddInfo->oddLoanRemark);
		$rdata['status'] = 1;
		$rdata['data']['info'] = $info;
		$this->backJson($rdata);
	}

	public function getLotteriesAction() {
		$user = $this->getUser();
		$lotteries = Lottery::whereIn('type', ['invest_money', 'interest', 'money'])
			->where('userId', $user->userId)
			->where('endtime', '>', date('Y-m-d H:i:s'))
			->where('status', Lottery::STATUS_NOUSE)
			->get();

		$records = [];
		foreach ($lotteries as $lottery) {
			$row = [];
			$row['id'] = $lottery->id;
			$row['name'] = $lottery->getName();
			$row['period_lower'] = $lottery->period_lower;
			$row['period_uper'] = $lottery->period_uper;
			$row['money_lower'] = $lottery->money_lower;
			$row['money_uper'] = $lottery->money_uper;
			$records[] = $row;
		}

		$rdata['status'] = 1;
		$rdata['info'] = '获取成功！';
		$rdata['data']['lotteries'] = $records;
		$this->backJson($rdata);
	}
}
