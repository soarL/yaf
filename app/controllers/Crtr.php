<?php
use helpers\StringHelper;
use Yaf\Registry;
use tools\Pager;
use models\Odd;
use models\User;
use models\OddMoney;
use models\Crtr;
use models\UserCrtr;
use forms\CrtrForm;
use tools\API;
use helpers\HtmlHelper;
use exceptions\HttpException;
use traits\PaginatorInit;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * CrtrController
 * 标的控制器
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class CrtrController extends Controller {
	use PaginatorInit;

	public $menu = 'crtr';

	/**
	 * 债权转让列表(新)
	 * @return mixed
	 */
	public function listAction() {
		$this->submenu = 'crtr';

		$queries = $this->queries->defaults(['rate'=>'', 'time'=>'', 'sort'=>'normal', 'order'=>'desc']);
		$time = $queries->time;
		$rate = $queries->rate;

		$builder = Crtr::getListBuilder();

		$searchTime = Crtr::getSearchTime($time);
		$builder->join('work_odd',function($join){$join->on('work_odd.oddNumber','=','work_creditass.oddNumber')->on(DB::raw('(UNIX_TIMESTAMP(work_odd.oddRehearTime) + work_odd.oddBorrowPeriod * 30*24*60*60 - '.time().') >='.$GLOBALS['begin']),DB::raw(''),DB::raw(''))->on(DB::raw('(UNIX_TIMESTAMP(work_odd.oddRehearTime) + work_odd.oddBorrowPeriod * 30*24*60*60 - '.time().') <'.$GLOBALS['end']),DB::raw(''),DB::raw(''));})->select('work_creditass.*','work_odd.oddYearRate',DB::raw('(UNIX_TIMESTAMP(work_odd.oddRehearTime) + work_odd.oddBorrowPeriod * 30*24*60*60 - '.time().') as qixian'));

		$searchRate = Odd::getSearchType($rate);
		if($searchRate) {
			$builder->whereRaw($searchRate);
		}

		$sort = $queries->sort!='normal'?$queries->sort:'';
		$builder = Crtr::sortList($builder,$sort,$queries->order);
		$crtrs = $builder->paginate('5');
		$crtrs->appends($queries->all());
		$this->display('list', ['crtrs'=>$crtrs,'queries'=>$queries,'idata'=>$this->getOrderButton()]);
	}

	public function getOrderButton(){
		$queries = $this->queries->defaults(['rate'=>'', 'time'=>'', 'sort'=>'addtime', 'order'=>'desc']);
		$data['normal'] = ['order'=>'desc','b'=>'↓','class'=>''];
		$data['oddYearRate'] = ['order'=>'desc','b'=>'↓','class'=>''];
		$data['qixian'] = ['order'=>'desc','b'=>'↓','class'=>''];
		$data['addtime'] = ['order'=>'desc','b'=>'↓','class'=>''];
		$data[$queries->sort]['class'] = 'biaoSeach3Li';
		$data[$queries->sort]['order'] = $queries->order=='desc'?'asc':'desc';
		$data[$queries->sort]['b'] = $queries->order=='desc'?'↓':'↑';
		return $data;
	}

	/**
	 * 债权转让详情(新)
	 * @return mixed
	 */
	public function viewAction($num=0) {
		$num = intval($num);
		$id = $num - Crtr::SN_PRE;
		
        $user = $this->getUser();
        $crtr = Crtr::find($id);
        
        if(!$crtr) {
			throw new HttpException(404);
		}

        $this->title = $crtr->odd->oddTitle.'--债权转让';

        $builder = OddMoney::where('type', 'credit')->where('cid', $crtr->id);

        $count = $builder->count();
		$totalMoney = $builder->sum('money');

		$oddMoney = OddMoney::where('id',$crtr->oddmoneyId)->first();
		$originInterest = $oddMoney->getInvestedStayInterest();

		$ingData = UserCrtr::where('crtr_id', $id)->where('status', '0')->first([DB::raw('sum(money) ingMoney'), DB::raw('count(*) ingCount')]);

		$this->display('view', [
			'crtr'=>$crtr,
			'user'=>$user,
			'count'=>$count, 
			'rate'=>$originInterest/($oddMoney->money - $oddMoney->successMoney),
			'totalMoney'=>$totalMoney,
			'ingMoney'=>$ingData->ingMoney==null?0:$ingData->ingMoney,
			'ingCount'=>$ingData->ingCount,
		]);
	}

	/**
	 * 债权转让
	 * @return mixed
	 */
	public function buyAction() {
		$params = $this->getAllPost();
		$form = new CrtrForm($params);
		$rdata = [];
        if($form->buy()) {
			echo $form->html;
		} else {
			Flash::error($form->posError());
			$this->redirect('/crtr/view/num/'.Crtr::SN($params['id']));
		}
	}

	public function purchasersAction() {
		$request = $this->getRequest();
		$id = $request->getPost('num');
		$builder = OddMoney::where('type', 'credit')->where('cid', $id);
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
			$newTenders[$key] = $newTender;
		}
		$template = '<tr class="dark">'
            .  '<td>#normalKey#</td>'
            .  '<td><a href="#" target="_blank">#username#</a></td>'
            .  '<td class="text-right"><em>#money#</em>元</td>'
            .  '<td class="text-right">#time#</td>'
            .'</tr>';
		$rdata = [];
		$rdata['pager'] = $pager->html();
		$rdata['records'] = HtmlHelper::tableRecords($newTenders, $template);
		$this->backJson($rdata);
	}

	/**
	 * 某个债权的在买列表（ajax, 含分页）
	 * @return mixed
	 */
	public function ingRecordsAction() {
		$request = $this->getRequest();
		$id = $request->getPost('num');
		$crtr = Crtr::find($id);
		$builder = UserCrtr::where('crtr_id', $id)->where('status', '0');
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
			$newTender['time'] = $tender->addTime;
			$lastTime = (strtotime($tender->addTime)+6*60)-time();
			if($lastTime>0) {
				$lastTime = '<span class="ing-time-down" data-time="'.$lastTime.'">'.$lastTime.'秒</span>';
			} else {
				$lastTime = '<a class="btn btn-blue btn-sm" href="/crtr/view/num/'.$crtr->getSN().'">点击刷新</a>';
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
	 * 获取用户针对某个债权的最大可购买金额（ajax）
	 * @return mixed
	 */
	public function getMaxBuyAction() {
		$id = $this->getPost('id', 0);
		$user = $this->getUser();
		$rdata = [];
		$crtr = Crtr::where('id', $id)->first(['id', 'progress']);
		if(!$crtr) {
			$rdata['status'] = 0;
			$rdata['info'] = '债权不存在或已售出！';
			$this->backJson($rdata);
		}

		$remain = $crtr->getRemain();
		$userMoney = $user->fundMoney;
		if($userMoney>=$remain) {
			$rdata['status'] = 1;
			$rdata['money'] = $remain;
			$this->backJson($rdata);
		} else {
			$rdata['status'] = 1;
			$rdata['money'] = $userMoney;
			$this->backJson($rdata);
		}
	}
}
