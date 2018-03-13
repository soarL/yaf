<?php
// This controller use illuminate/database.
use traits\PaginatorInit;
use models\Filiale;
use models\History;
use models\Activity;
use models\Job;
use models\Odd;
use models\OddMoney;
use models\Department;
use tools\Redis;
use helpers\StringHelper;
use models\Interest;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * AboutController
 * 关于我们控制器
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class AboutController extends Controller {
	use PaginatorInit;

	public $menu = 'about';
	public $submenu = 'about';

	public function indexAction() {
		$this->submenu = 'index';
		$this->display('index');
	}

	public function infoAction() {
		$this->submenu = 'info';
		$this->display('info');
	}

	public function developAction() {
		$this->submenu = 'develop';
		$this->display('develop');
	}

	public function custodyAction() {
		$this->submenu = 'custody';
		$this->display('custody');
	}

	public function contactAction() {
		$this->submenu = 'contact';
		$this->display('contact');
	}

	public function superviseAction() {
		$this->submenu = 'supervise';
		$this->display('supervise');
	}

	public function clauseAction() {
		$this->submenu = 'clause';
		$this->display('clause');
	}

	public function riskAction() {
		$this->submenu = 'risk';
		$this->display('risk');
	}

	public function mapAction() {
		$this->submenu = 'map';
		$this->display('map');
	}

	public function a1Action() {
		$this->submenu = 'a1';
		$this->display('a1');
	}
	public function a2Action() {
		$this->submenu = 'a2';
		$this->display('a2');
	}
	public function a3Action() {
		$this->submenu = 'a3';
		$this->display('a3');
	}
	public function a4Action() {
		$this->submenu = 'a4';
		$this->display('a4');
	}
	public function czAction() {
		$this->submenu = 'cz';
		$this->display('cz');
	}
	public function opendataAction() {
		$this->submenu = 'opendata';

		$opendata = Redis::get('opendata');
		$opendata = json_decode($opendata,true);
		if(!isset($opendata['date']) || $opendata['date'] != date('Ymd')){
			$opendata['date'] = date('Ymd');
			$oddData = Odd::whereIn('progress',['run','end'])->select(DB::raw('sum(oddMoney) total,count(id) count'))->first();
			$opendata['total'] = round($oddData->total/10000,2);
			$opendata['count'] = $oddData->count;

			$oddMoneyData = OddMoney::with('user')->where('status','<>','-1')->groupBy('type','userId')->select(DB::raw('sum(money) total,sum(remain) remainTotal,type,userId'))->orderBy('total','desc')->get();
			$opendata['investerCount'] = 0;
			$opendata['loanerCount'] = 0;
			$opendata['tenLoaner'] = 0;
			$opendata['firstLoaner'] = 0;
			$opendata['totalLoaner'] = 0;
			$opendata['investerMan'] = 0;
			$opendata['investerWomen'] = 0;
			$opendata['invester18'] = 0;
			$opendata['invester26'] = 0;
			$opendata['invester31'] = 0;
			$opendata['invester36'] = 0;
			$opendata['invester41'] = 0;
			$opendata['invester46'] = 0;
			$first = 0;
			$ten = 0;
			foreach ($oddMoneyData as $key => $value) {
				if($value->type == 'loan'){
					if($first == 0){
						$first = 1;
						$opendata['firstLoaner'] = $value->remainTotal;
					}
					if($ten < 10){
						$ten ++;
						$opendata['tenLoaner'] += $value->remainTotal;
					}

					$opendata['totalLoaner'] += $value->remainTotal;

					$opendata['loanerCount'] += 1;
				}else{
					if($value->user->sex == 'man'){
						$opendata['investerMan']++;
					}else{
						$opendata['investerWomen']++;
					}
					if(1){//strtotime($value->user->addtime) < strtotime('2018-01-01')
						$age = StringHelper::getAgeByBirthday($value->user->birth);
						switch ($age) {
							case $age <= 25:
								$opendata['invester18'] ++;
								break;
							case $age <= 30 && $age >= 26:
								$opendata['invester26'] ++;
								break;
							case $age <= 35 && $age >= 31:
								$opendata['invester31'] ++;
								break;
							case $age <= 40 && $age >= 36:
								$opendata['invester36'] ++;
								break;
							case $age <= 45 && $age >= 41:
								$opendata['invester41'] ++;
								break;
							case $age >= 46:
								$opendata['invester46'] ++;
								break;
							default:
								break;
						}

						$opendata['investerCount'] += 1;
						
					}
				}
			}
			$opendata['investerManRate'] = round($opendata['investerMan']/$opendata['investerCount']*100,2);
			$opendata['investerWomenRate'] = round($opendata['investerWomen']/$opendata['investerCount']*100,2);

			$opendata['invester18'] = round($opendata['invester18']/$opendata['investerCount']*100,2);
			$opendata['invester26'] = round($opendata['invester26']/$opendata['investerCount']*100,2);
			$opendata['invester31'] = round($opendata['invester31']/$opendata['investerCount']*100,2);
			$opendata['invester36'] = round($opendata['invester36']/$opendata['investerCount']*100,2);
			$opendata['invester41'] = round($opendata['invester41']/$opendata['investerCount']*100,2);
			$opendata['invester46'] = round($opendata['invester46']/$opendata['investerCount']*100,2);

			$opendata['tenLoaner'] = round($opendata['tenLoaner']/$opendata['totalLoaner']*100,2);
			$opendata['firstLoaner'] = round($opendata['firstLoaner']/$opendata['totalLoaner']*100,2);

			$oddMoneyData = OddMoney::where('remain','>','0')->groupBy('type','userId')->select(DB::raw('sum(money) total,sum(remain) remainTotal,type'))->orderBy('total','desc')->get();
			$opendata['nInvesterCount'] = 0;
			$opendata['nLoanerCount'] = 0;
			foreach ($oddMoneyData as $key => $value) {
				if($value->type == 'invest'){
					$opendata['nInvesterCount'] += 1;
				}elseif($value->type == 'loan'){
					$opendata['nLoanerCount'] += 1;
				}
			}

			$opendata['totalZoneEr'] = round(Interest::where('status','0')->sum('zongEr') / 10000,2);

			$odds = Odd::with('interests')->whereRaw(DB::raw('userId in (select userId from system_userinfo where blackstatus = 1)'))->get();
			$opendata['totalBlack'] = 0;
			foreach ($odds as $key => $value) {
				if($value->interests){
					foreach ($value->interests as $key => $interest) {
						if($interest->status == 0){
							$opendata['totalBlack'] += $interest->zongEr;
						}
					}
				}
			}

			$odds = Interest::where('status','3')->groupBy('oddNumber')->select(DB::raw('sum(zongEr) total'))->get();
			$opendata['delay'] = 0;
			$opendata['delayTotal'] = 0;
			foreach ($odds as $key => $value) {
				$opendata['delay'] ++;
				$opendata['delayTotal'] += $value->total;
			}
			$opendata['delayTotal'] = round($opendata['delayTotal']/10000,2);

			Redis::set('opendata',json_encode($opendata));
		}

		$this->display('opendata',['data'=>$opendata]);
	}
	public function ratesAction() {
		$this->submenu = 'rates';
		$this->display('rates');
	}
	public function spreadsAction() {
		$this->submenu = 'spreads';
		$this->display('spreads');
	}
	public function borrowAction() {
		$this->submenu = 'agreement/borrow';
		$this->display('agreement/borrow');
	}
	public function debtAction() {
		$this->submenu = 'agreement/debt';
		$this->display('agreement/debt');
	}
	public function promiseAction() {
		$this->submenu = 'promise';
		$this->display('promise');
	}
	public function honorAction() {
		$this->submenu = 'honor';
		$this->display('honor');
	}
	public function eventsAction() {
		$this->submenu = 'events';
		$this->display('events');
	}
	public function reportAction(){
		$this->submenu = "report";
		$this->display("report");
	}
	public function auditAction(){
		$this->submenu = "audit";
		$this->display("audit");
	}
	public function lagelAction(){
		$this->submenu = "lagel";
		$this->display("lagel");
	}
	public function houseloanAction(){
		$this->submenu = "houseloan";
		$this->display("houseloan");
	}
	public function carloanAction(){
		$this->submenu = "carloan";
		$this->display("carloan");
	}
	public function riskmanageAction(){
		$this->submenu = "riskmanage";
		$this->display("riskmanage");
	}
	public function riskassessAction(){
		$this->submenu = "riskassess";
		$this->display("riskassess");
	}
	public function overdueAction(){
		$this->submenu = "overdue";
		$this->display("overdue");
	}
	public function preloanAction(){
		$this->submenu = "preloan";
		$this->display("preloan");
	}
	public function centerloanAction(){
		$this->submenu = "centerloan";
		$this->display("centerloan");
	}
	public function afterloanAction(){
		$this->submenu = "afterloan";
		$this->display("afterloan");
	}
	public function ctrAction(){
		$this->submenu = "ctr";
		$this->display("ctr");
	}
	public function loanCompanyselfAction(){
		$this->submenu = "loanCompanyself";
		$this->display("loanCompanyself");
	}
	public function borrownoticeAction(){
		$this->submenu = "borrownotice";
		$this->display("borrownotice");
	}
}