<?php
use tools\Pager;
use tools\ThirdToken;
use models\Odd;
use models\User;
use models\OddMoney;
use models\OldOdd;
use models\Interest;
use helpers\NetworkHelper;
use helpers\StringHelper;
use Yaf\Registry;

/**
 * ThirdController
 * 第三方数据接口
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class ThirdController extends Controller {

    public function tianyanAction($type) {
    	$request = $this->getRequest();

    	if($type=='token') { // 获取token
    		$username = $request->getPost('username', '');
    		$password = $request->getPost('password', '');
    		$token = ThirdToken::getThirdToken('tianyan', $username, $password);
    		$rdata = [];
    		if(!$token) {
    			$rdata['result'] = -1;
    			$rdata['data'] = ['token'=>null];
    			$this->backJson($rdata);
    		}
    		$rdata['result'] = 1;
    		$rdata['data'] = ['token'=>$token];
    		$this->backJson($rdata);
    	} else if($type=='loans') { // 获取借款列表
    		$token = $request->getPost('token');
			$pageSize = $request->getPost('page_size', 1000);
			$pageIndex = $request->getPost('page_index', 1);
			$status = $request->getPost('status', 0);
			$timeFrom = $request->getPost('time_from', '');
			$timeTo = $request->getPost('time_to', '');
			if(!ThirdToken::checkThirdToken('tianyan', $token)) {
				$rdata['result_code'] = -1;
				$rdata['result_msg'] = '未授权的访问!';
				$rdata['page_count'] = 0;
				$rdata['page_index'] = 0;
				$rdata['loans'] = null;
				$this->backJson($rdata);
			}
			// Log::write('token:'.$token.'pageSize:'.$pageSize.'pageIndex:'.$pageIndex.'status:'.$status.'  '.$timeFrom.'  '.$timeTo, 'test');
			$builder = Odd::with('tenders')->where('oddType', '<>', 'special');

			if($timeFrom!='') {
				$builder->where('oddTrialTime', '>=', $timeFrom);
			}
			if($timeTo!='') {
				$builder->where('oddTrialTime', '<', $timeTo);
			}
			if($status==0) {
				$builder->where('progress', 'start');
			} else {
				$builder->whereIn('progress', ['run', 'end', 'fron', 'delay']);
			}
			$builder->orderBy('oddTrialTime', 'desc');

			$count = $builder->count();
			$pager = new Pager(['total'=>$count, 'request'=>$request, 'pageParam'=>'page_index', 'pageSize'=>$pageSize]);

			$loans = $builder->skip($pager->getOffset())->limit($pager->getLimit())->get();

			$newLoans = [];
			foreach ($loans as $loan) {
				$newLoan = [];
				$newLoan['id'] = $loan->oddNumber; //标号
				$newLoan['url'] = WEB_MAIN.'/odd/'.$loan->oddNumber; //标的链接地址
				$newLoan['platform_name'] = '汇诚普惠'; //平台名称
				$newLoan['title'] = $loan->oddTitle; //标名称
				$newLoan['username'] = ''; //借款用户
				$oddStatus = 0;
				if($loan->progress=='start') {
					$oddStatus = 0;
				} else if($loan->progress=='run'||$loan->progress=='end'||$loan->progress=='fron'||$loan->progress=='delay') {
					$oddStatus = 1;
				} else {
					$oddStatus = -1;
				}
				$newLoan['status'] = $oddStatus; //借款标的状态
				$newLoan['userid'] = $loan->userId; //借款用户ID
				$newLoan['c_type'] = 2; //借款类型
				$newLoan['amount'] = $loan->oddMoney; //借款金额
				$newLoan['rate'] = $loan->oddYearRate; //借款年利率
				$newLoan['period'] = $loan->oddBorrowPeriod; //借款期限
				$borrowStyle = 1;
				if($loan->oddBorrowStyle=='day') {
					$borrowStyle = 1;
				} else if($loan->oddBorrowStyle=='month') {
					$borrowStyle = 1;
				} else {
					$borrowStyle = -1;
				}
				$newLoan['p_type'] = $borrowStyle; //期限类型

				$repayStyle = 0;
				if($loan['oddRepaymentStyle']=='monthpay') {
					$repayStyle = 2;
				} else if($loan['oddRepaymentStyle']=='matchpay') {
					$repayStyle = 1;
				} else {
					$repayStyle = 0;
				}
				$newLoan['pay_way'] = $repayStyle; //还款方式
				$per = round($loan->getPercent());
				$newLoan['process'] = $per/100; //完成百分比
				$newLoan['reward'] = 0; //投标奖励
				$newLoan['guarantee'] = 0; //担保奖励
				$newLoan['start_time'] = $loan->oddTrialTime; //创建时间
				$newLoan['end_time'] = $loan->fullTime; //满标时间
				$newLoan['invest_num'] = $loan->getTenderTime(); //投资次数
				$newLoan['c_reward'] = 0; //续投奖励
				$newLoans[] = $newLoan;
			}
			$rdata['result_code'] = 1;
			$rdata['result_msg'] = '获取数据成功！';
			$rdata['page_count'] = $pager->getTotalPage();
			$rdata['page_index'] = $pageIndex;
			$rdata['loans'] = $newLoans;
			$this->backJson($rdata);
    	} else if($type=='data') { // 获取投资记录
    		$token = $request->getPost('token');
			$pageSize = $request->getPost('page_size', 1000);
			$pageIndex = $request->getPost('page_index', 1);
			$id = $request->getPost('id', 0);
			$timeFrom = $request->getPost('time_from', '');
			$timeTo = $request->getPost('time_to', '');
			if(!ThirdToken::checkThirdToken('tianyan', $token)) {
				$rdata['result_code'] = -1;
				$rdata['result_msg'] = '未授权的访问!';
				$rdata['page_count'] = 0;
				$rdata['page_index'] = 0;
				$rdata['loans'] = null;
				$this->backJson($rdata);
			}

			$builder = OddMoney::with('user')->where('type', 'invest');

			if($timeFrom!='') {
				$builder->where('time', '>=', $timeFrom);
			}
			if($timeFrom!='') {
				$builder->where('time', '<', $timeTo);
			}
			if($id!=0) {
				$builder->where('oddNumber', $id);
			} else {
				$rdata['result_code'] = -1;
				$rdata['result_msg'] = '缺少参数!';
				$rdata['page_count'] = 0;
				$rdata['page_index'] = 0;
				$rdata['loans'] = null;
				$this->backJson($rdata);
			}
			$builder->orderBy('time', 'desc');
			$count = $builder->count();
			$pager = new Pager(['total'=>$count, 'request'=>$request, 'pageParam'=>'page_index', 'pageSize'=>$pageSize]);
			$loans = $builder->skip($pager->getOffset())->limit($pager->getLimit())->get();
			$newLoans = [];
			foreach ($loans as $loan) {
				$newLoan['id'] = $loan->oddNumber;
				$newLoan['useraddress'] = '';
				$newLoan['link'] = WEB_MAIN.'/odd/'.$loan->oddNumber;
				$newLoan['username'] = StringHelper::getHideUsername($loan->user->username);
				$newLoan['userid'] = $loan->userId;
				if(strpos($loan['remark'], 'AUTOMATIC')!==false) {
					$newLoan['type'] = '自动';
				} else {
					$newLoan['type'] = '手动';
				}
				$newLoan['money'] = $loan->money;
				$newLoan['account'] = $loan->money;
				$newLoan['status'] = '成功';
				$newLoan['add_time'] = $loan->time;
				$newLoans[] = $newLoan;
			}
			$rdata['result_code'] = 1;
			$rdata['result_msg'] = '获取数据成功！';
			$rdata['page_count'] = $pager->getTotalPage();
			$rdata['page_index'] = $pageIndex;
			$rdata['loans'] = $newLoans;
			$this->backJson($rdata);
    	}
    }

    public function zhijiaAction($type) {
    	$request = $this->getRequest();
    	$siteinfo = Registry::get('siteinfo');
    	if($type=='token') { // 获取token
    		Log::write('loans-IP:'.$siteinfo['clientIp'],'thirddata');
    		$username = $request->getQuery('username', '');
    		$password = $request->getQuery('password', '');
    		Log::write('loans-POST:username='.$username.'--password='.$password,'thirddata');
    		$token = ThirdToken::getThirdToken('zhijia', $username, $password);
    		$rdata = [];
    		if(!$token) {
    			$rdata['return'] = 0;
    			$rdata['data'] = ['token'=>null];
    			$this->backJson($rdata);
    		}
    		$rdata['return'] = 1;
    		$rdata['data'] = ['token'=>$token];
    		$this->backJson($rdata);
    	} else if($type=='loans') { // 获取借款列表
    		Log::write('loans-IP:'.$siteinfo['clientIp'],'thirddata');
    		$token = $request->getQuery('token');
    		$date = $request->getQuery('date', '');
			$pageSize = $request->getQuery('pageSize', 20);
			$page = $request->getQuery('page', 1);
			Log::write('loans-POST:token='.$token.'--date='.$date.'--pageSize='.$pageSize.'--page='.$page,'thirddata');
			if(!ThirdToken::checkThirdToken('zhijia', $token)) {
				echo 'token值无效';exit(0);
			}

			if(strtotime($date.' 00:00:00')<strtotime('2015-08-19 00:00:00')) {
				$this->zhijiaOldData($request, $date, $page, $pageSize);
			}

			$builder = Odd::with('tenders.user', 'user')->where('oddType', '<>', 'special')
				->whereIn('progress', ['run', 'end', 'fron', 'delay']);

			if($date!='') {
				$dateBegin = $date.' 00:00:00';
				$dateEnd = $date.' 23:59:59';
				$builder->where('oddRehearTime', '>=', $dateBegin)->where('oddRehearTime', '<=', $dateEnd);
			}

			$builder->orderBy('oddRehearTime', 'desc');
			$totalAmount = $builder->sum('oddMoney');
			$count = $builder->count();
			$pager = new Pager(['total'=>$count, 'request'=>$request, 'pageParam'=>'page', 'pageSize'=>$pageSize]);
			$loans = $builder->skip($pager->getOffset())->limit($pager->getLimit())->get();
			$newLoans = [];
			foreach ($loans as $loan) {
				$newLoan = [];
				$newLoan['projectId'] = $loan->oddNumber; //标号
				$newLoan['title'] = $loan->oddTitle; //标名称
				$newLoan['amount'] = $loan->oddMoney; //借款金额
				$per = round($loan->getPercent());
				$newLoan['schedule'] = $per; //完成百分比
				$newLoan['interestRate'] = ($loan->oddYearRate*100).'%'; //借款年利率
				$newLoan['deadline'] = $loan->oddBorrowPeriod; //借款期限
				$borrowStyle = '天';
				if($loan->oddBorrowStyle=='day') {
					$borrowStyle = '天';
				} else {
					$borrowStyle = '月';
				}
				$newLoan['deadlineUnit'] = $borrowStyle; //期限类型
				$newLoan['reward'] = 0; //投标奖励

            	if($loan->oddType=='diya'||$loan->oddType=='danbao') {
					$oddType = '抵押标';
            	} else if($loan->oddType=='xingyong') {
					$oddType = '质押标';
            	} else if($loan->oddType=='newhand') {
            		$oddType = '活动标';
            	}
				$newLoan['type'] = $oddType; //借款类型
				$repayStyle = 1;
				if($loan->oddRepaymentStyle=='monthpay') {
					$repayStyle = 5;
				} else if($loan->oddRepaymentStyle=='matchpay') {
					$repayStyle = 2;
				} else {
					$repayStyle = 1;
				}
				$newLoan['repaymentType'] = $repayStyle; //还款方式

				$investList = [];
				foreach ($loan->tenders as $tender) {
					if($tender->type=='invest') {
						$newInvest = [];
						$username = 'jinling0099';
						if($tender->user) {
							$username = $tender->user->username;
						}
						$newInvest['subscribeUserName'] = $username;
						$newInvest['amount'] = $tender->money;
						$newInvest['validAmount'] = $tender->money;
						$newInvest['addDate'] = $tender->time;
						$newInvest['status'] = 1;
						if(strpos($tender['remark'], 'AUTOMATIC')!==false) {
							$newInvest['type'] = 1;
						} else {
							$newInvest['type'] = 0;
						}
						$investList[] = $newInvest;
					}
				}

				$newLoan['subscribes'] = $investList; //投资人数据
				$newLoan['province'] = ''; //借款用户所在身份
				$newLoan['city'] = ''; //借款用户所在城市
				$username = 'liuchex89';
				if($loan->user) {
					$username = $loan->user->username;
				}
				$newLoan['userName'] = $username; //借款用户ID
				$newLoan['userAvatarUrl'] = '';
				//$newLoan['amountUsedDesc'] = $loan->oddUse; //借款用途
				$newLoan['revenue'] = 0; //平台营收
				$newLoan['loanUrl'] = 'http://www.hcjrfw.com/odd/'.$loan->oddNumber; //标的链接地址
				$newLoan['successTime'] = $loan->oddRehearTime; //满标时间
				$newLoan['publishTime'] = $loan->oddTrialTime; //创建时间
				$newLoans[] = $newLoan;
			}
			$rdata['totalPage'] = $pager->getTotalPage();
			$rdata['currentPage'] = $page;
			$rdata['totalCount'] = $count;
			$rdata['totalAmount'] = $totalAmount;
			$rdata['borrowList'] = $newLoans;
			$this->backJson($rdata);
    	}
    }

    public function zhijiaOldData($request, $date, $page, $pageSize) {
    	$builder = OldOdd::with('tenders.user', 'user')->where('status', '3');

		if($date!='') {
			$dateBegin = $date.' 00:00:00';
			$dateEnd = $date.' 23:59:59';
			$builder->where('borrow_success_time', '>=', strtotime($dateBegin))
				->where('borrow_success_time', '<=', strtotime($dateEnd));
		}

		$builder->orderBy('borrow_success_time', 'desc');
		$totalAmount = $builder->sum('account');
		$count = $builder->count();
		$pager = new Pager(['total'=>$count, 'request'=>$request, 'pageParam'=>'page', 'pageSize'=>$pageSize]);
		$loans = $builder->skip($pager->getOffset())->limit($pager->getLimit())->get();
		$newLoans = [];
		foreach ($loans as $loan) {
			$newLoan = [];
			$newLoan['projectId'] = $loan->borrow_nid; //标号
			$newLoan['title'] = $loan->name; //标名称
			$newLoan['amount'] = $loan->account; //借款金额
			$newLoan['schedule'] = 100; //完成百分比
			$newLoan['interestRate'] = $loan->borrow_apr.'%'; //借款年利率
			$newLoan['deadline'] = $loan->borrow_period; //借款期限
			$borrowStyle = '天';
			if($loan->borrow_style=='endday') {
				$borrowStyle = '天';
			} else {
				$borrowStyle = '月';
			}
			$newLoan['deadlineUnit'] = $borrowStyle; //期限类型
			$newLoan['reward'] = 0; //投标奖励

        	if($loan->borrow_type=='pawn') {
				$oddType = '质押标';
        	} else if($loan->borrow_type=='day') {
        		$oddType = '活动标';
        	} else {
        		$oddType = '其他';
        	}
			$newLoan['type'] = $oddType; //借款类型
			$repayStyle = 1;
			if($loan->borrow_style=='endmonth') {
				$repayStyle = 5;
			} else {
				$repayStyle = 1;
			}
			$newLoan['repaymentType'] = $repayStyle; //还款方式

			$investList = [];
			foreach ($loan->tenders as $tender) {
				if($tender->status==1) {
					$newInvest = [];
					$username = 'jinling0099';
					if($tender->user) {
						$username = $tender->user->username;
					}
					$newInvest['subscribeUserName'] = $username;
					$newInvest['amount'] = $tender->account;
					$newInvest['validAmount'] = $tender->account;
					$newInvest['addDate'] = date('Y-m-d H:i:s', $tender->addtime);
					$newInvest['status'] = 1;
					if($tender->contents=='自动投标') {
						$newInvest['type'] = 1;
					} else {
						$newInvest['type'] = 0;
					}
					$investList[] = $newInvest;
				}
			}

			$newLoan['subscribes'] = $investList; //投资人数据
			$newLoan['province'] = ''; //借款用户所在身份
			$newLoan['city'] = ''; //借款用户所在城市
			$username = 'liuchex89';
			if($loan->user) {
				$username = $loan->user->username;
			}
			$newLoan['userName'] = $username; //借款用户ID
			$newLoan['userAvatarUrl'] = '';
			$newLoan['amountUsedDesc'] = ''; //借款用途
			$newLoan['revenue'] = 0; //平台营收
			$newLoan['loanUrl'] = 'http://www.hcjrfw.com/invest/a'.$loan->borrow_nid.'.html'; //标的链接地址
			$newLoan['successTime'] = date('Y-m-d H:i:s', $loan->borrow_success_time); //满标时间
			$newLoan['publishTime'] = date('Y-m-d H:i:s', $loan->addtime); //创建时间
			$newLoans[] = $newLoan;
		}
		$rdata['totalPage'] = $pager->getTotalPage();
		$rdata['currentPage'] = $page;
		$rdata['totalCount'] = $count;
		$rdata['totalAmount'] = $totalAmount;
		$rdata['borrowList'] = $newLoans;
		$this->backJson($rdata);
    }

    public function jialuAction($type) {
    	$request = $this->getRequest();

    	if($type=='token') { // 获取token
    		$username = $request->getQuery('username', '');
    		$password = $request->getQuery('password', '');
    		$token = ThirdToken::getThirdToken('jialu', $username, $password);
    		$rdata = [];
    		if(!$token) {
    			$rdata['result'] = -1;
    			$rdata['data'] = ['token'=>null];
    			$this->backJson($rdata);
    		}
    		$rdata['result'] = 1;
    		$rdata['data'] = ['token'=>$token];
    		$this->backJson($rdata);
    	} else if($type=='loans') { // 获取借款列表
    		$token = $request->getQuery('token');
			$pageSize = $request->getQuery('page_size', 10000);
			$pageIndex = $request->getQuery('page_index', 1);
			$status = $request->getQuery('status', 1);
			$date = $request->getQuery('date', '');

			if(!ThirdToken::checkThirdToken('jialu', $token)) {
				echo '未授权的访问!';exit(0);
			}

			$builder = Odd::with('tenders', 'user')->where('oddType', '<>', 'special');
			if($date!='') {
				$dateBegin = $date.' 00:00:00';
				$dateEnd = $date.' 23:59:59';
				$builder->where('oddTrialTime', '>=', $dateBegin)->where('oddTrialTime', '<=', $dateEnd);
			}
			if($status==0) {
				$builder->where('progress', 'start');
			} else {
				$builder->whereIn('progress', ['run', 'end', 'fron', 'delay']);
			}

			$builder->orderBy('oddTrialTime', 'desc');
			$count = $builder->count();
			$pager = new Pager(['total'=>$count, 'request'=>$request, 'pageParam'=>'page_index', 'pageSize'=>$pageSize]);
			$loans = $builder->skip($pager->getOffset())->limit($pager->getLimit())->get();
			$newLoans = [];
			foreach ($loans as $loan) {
				$newLoan = [];
				$newLoan['SITE_CD'] = '汇诚普惠'; //平台名称
				$newLoan['BID_ID'] = $loan->oddNumber; //标号
				$newLoan['BORROWER_NAME'] = $loan->user->username; //借款用户
				$newLoan['BORROWER_UID'] = $loan->userId; //借款用户ID
				$newLoan['BID_TP'] = 2; //借款类型
				$newLoan['AMOUNT'] = $loan->oddMoney; //借款金额
				$newLoan['PERIOD'] = $loan->oddBorrowPeriod; //借款期限
				$borrowStyle = 'd';
				if($loan->oddBorrowStyle=='day') {
					$borrowStyle = 'd';
				} else if($loan->oddBorrowStyle=='month') {
					$borrowStyle = 'm';
				} else {
					$borrowStyle = 's';
				}
				$newLoan['PERIOD_TP'] = $borrowStyle; //期限类型
				$repayStyle = 0;
				if($loan->oddRepaymentStyle=='monthpay') {
					$repayStyle = 3;
				} else if($loan->oddRepaymentStyle=='matchpay') {
					$repayStyle = 1;
				} else {
					$repayStyle = 0;
				}
				$newLoan['RTN_TP'] = $repayStyle; //还款方式
				$newLoan['RATE'] = $loan->oddYearRate*100; //借款年利率
				$newLoan['REWARD_RT'] = 0; //投标奖励
				$per = round($loan->getPercent());
				$newLoan['BID_STATUS'] = $per; //完成百分比
				$newLoan['BID_OVER_TIME'] = $loan->fullTime; //满标时间
				$newLoan['BID_TITLE'] = $loan->oddTitle; //标名称
				$newLoans[] = $newLoan;
			}
			$this->backJson($newLoans);
    	} else if($type=='invest') { // 获取投资记录
    		$token = $request->getQuery('token');
			$pageSize = $request->getQuery('page_size', 1000);
			$pageIndex = $request->getQuery('page_index', 1);
			$id = $request->getQuery('id', 0);
			$date = $request->getQuery('date', '');

			if(!ThirdToken::checkThirdToken('jialu', $token)) {
				echo '未授权的访问!';exit(0);
			}

			$builder = OddMoney::with('odd', 'user')->where('type', 'invest');

			if($date!='') {
				$dateBegin = $date.' 00:00:00';
				$dateEnd = $date.' 23:59:59';
				$builder->where('time', '>=', $dateBegin)->where('time', '<=', $dateEnd);
			}
			if($id!=0) {
				$builder->where('oddNumber', $id);
			}
			$builder->orderBy('time', 'desc');
			$count = $builder->count();
			$pager = new Pager(['total'=>$count, 'request'=>$request, 'pageParam'=>'page_index', 'pageSize'=>$pageSize]);
			$loans = $builder->skip($pager->getOffset())->limit($pager->getLimit())->get();
			$newLoans = [];
			foreach ($loans as $loan) {
				$newLoan['SITE_CD'] = '汇诚普惠'; //平台名称
				$newLoan['BID_ID'] = $loan->oddNumber; //标号
				$newLoan['INVESTOR_NAME'] = $loan->user->username;
				$newLoan['INVESTOR_UID'] = $loan->userId;
				$newLoan['INV_TIME'] = $loan->time;
				$newLoan['AMOUNT'] = $loan->money;
				$newLoan['BID_STATUS'] = $loan->odd->getPercent();
				$newLoans[] = $newLoan;
			}
			$this->backJson($newLoans);
    	}
    }

    public function dailuopanAction($type) {
    	$request = $this->getRequest();

    	if($type=='token') { // 获取token
    		$username = $request->getQuery('username', '');
    		$password = $request->getQuery('password', '');
    		$token = ThirdToken::getThirdToken('dailuopan', $username, $password);
    		$rdata = [];
    		if(!$token) {
    			$rdata['result'] = -1;
    			$rdata['data'] = ['token'=>null];
    			$this->backJson($rdata);
    		}
    		$rdata['result'] = 1;
    		$rdata['data'] = ['token'=>$token];
    		$this->backJson($rdata);
    	} else if($type=='data') { // 获取借款列表
    		$token = $request->getQuery('token');
			$pageSize = $request->getQuery('pageSize', 20);
			$page = $request->getQuery('page', 1);
			$date = $request->getQuery('date', date('Y-m-d'));
			if(!ThirdToken::checkThirdToken('dailuopan', $token)) {
				$rdata['result'] = -1;
				$rdata['resultmsg'] = '未授权的访问!';
				$rdata['totalPage'] = 0;
				$rdata['currentPage'] = 0;
				$rdata['borrowList'] = null;
				$this->backJson($rdata);
			}
			$timeFrom = $date . ' 00:00:00';
			$timeTo = $date . ' 23:59:59';
			$builder = Odd::with('user', 'tenders.user')->whereIn('progress', ['run', 'end', 'fron', 'delay']);

			if($timeFrom!='') {
				$builder->where('oddTrialTime', '>=', $timeFrom);
			}
			if($timeTo!='') {
				$builder->where('oddTrialTime', '<', $timeTo);
			}
			$builder->orderBy('oddTrialTime', 'desc');
			$count = $builder->count();
			$pager = new Pager(['total'=>$count, 'request'=>$request, 'pageParam'=>'page', 'pageSize'=>$pageSize]);
			$loans = $builder->skip($pager->getOffset())->limit($pager->getLimit())->get();
			$newLoans = [];
			foreach ($loans as $loan) {
				$newLoan['projectId'] = $loan->oddNumber;
				$newLoan['title'] = $loan->oddTitle;
				$newLoan['amount'] = $loan->oddMoney;
				$newLoan['schedule'] = $loan->getPercent();
				$newLoan['interestRate'] = ($loan->oddYearRate*100).'%';
				$newLoan['deadline'] = $loan->oddBorrowPeriod;
				if($loan->oddBorrowStyle=='month') {
					$newLoan['deadlineUnit'] = '月';	
				} else {
					$newLoan['deadlineUnit'] = '天';
				}
				$oddType = '';
				if($loan->oddType=='diya') {
					$oddType  = '抵押标';
				} else if($loan->oddType=='xingyong') {
					$oddType  = '质押标';
				} else if($loan->oddType=='danbao') {
					$oddType  = '融资租赁标';
				}
				$newLoan['type'] = $oddType;
				$repaymentType = 5;
				if($loan->oddRepaymentStyle=='monthpay') {
			      $repaymentType = 5;
			    } else if($loan->oddRepaymentStyle=='matchpay') {
			      $repaymentType = 2;
			    }
				$newLoan['repaymentType'] = $repaymentType;
				$username = 'liuchex89';
				if($loan->user) {
					$username = $loan->user->username;
				}
				$newLoan['userName'] = $username;
				//$newLoan['amountUsedDesc'] = $loan->oddUse;
				$newLoan['loanUrl'] = WEB_MAIN.'/odd/'.$loan->oddNumber;
				$newLoan['successTime'] = $loan->fullTime;
				$newLoan['publishTime'] = $loan->oddTrialTime;
				$list = [];
				foreach ($loan->tenders as $tender) {
					if($tender->type=='invest') {
						$row = [];
						$username = 'jinling0099';
						if($tender->user) {
							$username = $tender->user->username;
						}
						$row['subscribeUserName'] = $username;
						$row['amount'] = $tender->money;
						$row['validAmount'] = $tender->money;
						$row['addDate'] = $tender->time;
						$row['status'] = 1;
						if(strpos($tender->remark, 'AUTOMATIC')!==false) {
							$row['type'] = 1;
						} else {
							$row['type'] = 0;
						}
						$list[] = $row;
					}
				}
				$newLoan['subscribes'] = $list;
				$newLoans[] = $newLoan;
			}
			$rdata['result'] = 1;
			$rdata['resultmsg'] = '获取数据成功！';
			$rdata['totalPage'] = $pager->getTotalPage();
			$rdata['currentPage'] = $page;
			$rdata['borrowList'] = $newLoans;
			$this->backJson($rdata);
    	}
    }

    public function duozhuanAction($type) {
    	if($type=='token') { // 获取token
    		$username = $this->getQuery('username', '');
    		$password = $this->getQuery('password', '');
    		$token = ThirdToken::getThirdToken('duozhuan', $username, $password);
    		$rdata = [];
    		if(!$token) {
    			$rdata['result'] = -1;
    			$rdata['data'] = ['token'=>null];
    			$this->backJson($rdata);
    		}
    		$rdata['result'] = 1;
    		$rdata['data'] = ['token'=>$token];
    		$this->backJson($rdata);
    	} else if($type=='prepay') {
	    	$token = $this->getQuery('token', '');
			$pageSize = $this->getQuery('pageSize', 20);
			$page = $this->getQuery('page', 1);
			$timeBegin = $this->getQuery('timeBegin', '');
			$timeEnd = $this->getQuery('timeEnd', '');
			if(!ThirdToken::checkThirdToken('duozhuan', $token)) {
				$rdata['result'] = -1;
				$rdata['resultmsg'] = '未授权的访问!';
				$rdata['totalPage'] = 0;
				$rdata['currentPage'] = 0;
				$rdata['records'] = null;
				$this->backJson($rdata);
			}
	    	$builder = Interest::with('odd')
	    		->where('status', Interest::STATUS_PREV)
	    		->where('realAmount', '>', 0);
	    	if($timeEnd!='') {
				$builder->where('operatetime', '<=', $timeEnd);
	    	}
	    	if($timeBegin!='') {
				$builder->where('operatetime', '>=', $timeBegin);
	    	}
			$count = $builder->count();
			$pager = new Pager(['total'=>$count, 'request'=>$this->getRequest(), 'pageParam'=>'page', 'pageSize'=>$pageSize]);
			$interests = $builder->orderBy('operatetime', 'desc')->skip($pager->getOffset())->limit($pager->getLimit())->get();

	    	$list = [];
	    	foreach ($interests as $interest) {
	    		if(!$interest->odd) {
	    			continue;
	    		}
	    		$row = [];
	    		$row['odd_no'] = $interest->oddNumber;
	    		$row['odd_repay_period'] = $interest->odd->oddBorrowPeriod;
	    		$row['odd_repay_type'] = $interest->odd->oddRepaymentStyle;
	    		$row['odd_trial_time'] = $interest->odd->oddTrialTime;
	    		$row['odd_moeny'] = $interest->odd->oddMoney;
	    		$row['repay_time'] = $interest->operatetime;
	    		$row['repay_principal'] = $interest->realAmount - $interest->realinterest;

	    		$begin = date('Y-m-d 00:00:00', strtotime($interest->addtime));
	    		$end = date('Y-m-d 00:00:00', strtotime($interest->operatetime));
	    		$day = (strtotime($end) - strtotime($begin)) / (24*60*60);
	    		$row['interest_day'] = ($interest->qishu-1)*30 + intval($day);
	    		$list[] = $row;
	    	}
	    	$rdata = [];
	    	$rdata['result'] = 1;
			$rdata['resultmsg'] = '获取数据成功！';
			$rdata['totalPage'] = $pager->getTotalPage();
			$rdata['currentPage'] = $page;
			$rdata['records'] = $list;
			$this->backJson($rdata);
	    } else if($type=='matchpay') {
	    	$token = $this->getQuery('token', '');
			$pageSize = $this->getQuery('pageSize', 20);
			$qishu = $this->getQuery('qishu', 0);
			$page = $this->getQuery('page', 1);
			$timeBegin = $this->getQuery('timeBegin', '');
			$timeEnd = $this->getQuery('timeEnd', '');
			if(!ThirdToken::checkThirdToken('duozhuan', $token)) {
				$rdata['result'] = -1;
				$rdata['resultmsg'] = '未授权的访问!';
				$rdata['totalPage'] = 0;
				$rdata['currentPage'] = 0;
				$rdata['records'] = null;
				$this->backJson($rdata);
			}
	    	$builder = Interest::with('odd')
	    		->whereHas('odd', function($q) {
	    			$q->where('oddRepaymentStyle', 'matchpay');
	    		})
	    		->whereIn('status', Interest::$finished)
	    		->where('realAmount', '>', 0);
	    	if($timeEnd!='') {
				$builder->where('operatetime', '<=', $timeEnd);
	    	}
	    	if($timeBegin!='') {
				$builder->where('operatetime', '>=', $timeBegin);
	    	}
	    	if($qishu!=0) {
	    		$builder->where('qishu', $qishu);
	    	}
	    	$count = $builder->count();
			$pager = new Pager(['total'=>$count, 'request'=>$this->getRequest(), 'pageParam'=>'page', 'pageSize'=>$pageSize]);
			$interests = $builder->skip($pager->getOffset())->limit($pager->getLimit())->get();

	    	$list = [];
	    	foreach ($interests as $interest) {
	    		if(!$interest->odd) {
	    			continue;
	    		}
	    		$row = [];
	    		$row['odd_no'] = $interest->oddNumber;
	    		$row['odd_repay_period'] = $interest->odd->oddBorrowPeriod;
	    		$row['odd_repay_type'] = $interest->odd->oddRepaymentStyle;
	    		$row['odd_trial_time'] = $interest->odd->oddTrialTime;
	    		$row['odd_moeny'] = $interest->odd->oddMoney;
	    		$row['repay_period'] = $interest->qishu;
	    		$row['repay_time'] = $interest->endtime;
	    		$row['rest_principal'] = $interest->getRestPrincipal();
	    		$list[] = $row;
	    	}
	    	$rdata = [];
	    	$rdata['result'] = 1;
			$rdata['resultmsg'] = '获取数据成功！';
			$rdata['totalPage'] = $pager->getTotalPage();
			$rdata['currentPage'] = $page;
			$rdata['records'] = $list;
			$this->backJson($rdata);
    	}
    }

    public function testAction() {

    	/* 天眼测试 begin */
    	/*$url = 'http://www.hcjrfw.com/api/third/tianyan/type/token';
    	$data = [];
    	$data['username'] = 'tianyan';
    	$data['password'] = 'xwsd_tianyan';

    	$result = NetworkHelper::post($url, $data);
    	$row = json_decode($result, true);
    	$token = '';
    	if($row['result']==1) {
    		$token = $row['data']['token'];
    		echo $token . '<br>';
    	} else {
    		echo 'Get token error!';exit(0);
    	}

    	// loan
    	$url = 'http://www.hcjrfw.com/api/third/tianyan/type/loans';
    	$data = [];
    	$data['token'] = $token;
    	$data['page_size'] = 200;
    	$data['page_index'] = 1;
    	$data['status'] = 1;
    	$data['time_from'] = '2016-07-26 00:00:00';
    	$data['time_to'] = '2016-07-27 00:00:00';*/

    	//data
    	/*$url = 'http://www.hcjrfw.com/api/third/tianyan/type/data';
    	$data = [];
    	$data['token'] = $token;
    	$data['page_size'] = 20;
    	$data['page_index'] = 1;
    	$data['id'] = '20160720000009';
    	$data['time_from'] = '2016-07-19 00:00:00';
    	$data['time_to'] = '2016-07-21 00:00:00'; 	*/
    	/* 天眼测试 end */

    	/* 网袋之家测试 begin */
    	/*$url = 'http://www.hcjrfw.com/api/third/zhijia/type/token';
    	$data = [];
    	$data['username'] = 'zhijia';
    	$data['password'] = 'xwsd_zhijia';

    	$result = NetworkHelper::curlRequest($url, $data);
    	$row = json_decode($result, true);
    	$token = '';
    	if($row['return']==1) {
    		$token = $row['data']['token'];
    		echo $token . '<br>';
    	} else {
    		echo 'Get token error!';exit(0);
    	}
		$url = 'http://www.hcjrfw.com/api/third/zhijia/type/loans';
    	$data = [];
    	$data['token'] = $token;
    	$data['date'] = '2016-07-29';
    	$data['pageSize'] = 20;
    	$data['page'] = 1;*/
    	/* 网袋之家测试 end */

    	/* 网袋之家测试 begin */
    	$url = 'http://www.hcjrfw.com/api/third/duozhuan/type/token';
    	$data = [];
    	$data['username'] = 'duozhuan';
    	$data['password'] = 'xwsd_duozhuan';

    	$result = NetworkHelper::curlRequest($url, $data);
    	$row = json_decode($result, true);
    	$token = '';
    	if($row['result']==1) {
    		$token = $row['data']['token'];
    		echo $token . '<br>';
    	} else {
    		echo 'Get token error!';exit(0);
    	}
		$url = 'http://www.hcjrfw.com/api/third/duozhuan/type/matchpay';
    	$data = [];
    	$data['token'] = $token;
    	$data['timeBegin'] = '2016-10-13 00:00:00';
    	$data['timeEnd'] = '2016-10-13 23:59:59';
    	$data['qishu'] = 0;
    	$data['pageSize'] = 5;
    	$data['page'] = 1;
    	/* 网袋之家测试 end */

    	/* 佳璐测试 begin */
    	/*$url = WEB_MAIN.'/api/third/jialu/type/token';
    	$data = [];
    	$data['username'] = 'jialu';
    	$data['password'] = 'xwsd_jialu';

    	$result = NetworkHelper::curlRequest($url, $data);
    	var_dump($result);
    	$row = json_decode($result, true);
    	$token = '';
    	if($row['result']==1) {
    		$token = $row['data']['token'];
    		echo $token . '<br>';
    	} else {
    		echo 'Get token error!';exit(0);
    	}*/

    	// loans
    	/*$url = WEB_MAIN.'/api/third/jialu/type/loans';
    	$data = [];
    	$data['token'] = $token;
    	$data['page_size'] = 20;
    	$data['page_index'] = 1;
    	$data['status'] = 1;
    	$data['date'] = '2016-07-20';*/

    	//invest
    	/*$url = WEB_MAIN.'/api/third/jialu/type/invest';
    	$data = [];
    	$data['token'] = $token;
    	$data['page_size'] = 20;
    	$data['page_index'] = 1;
    	$data['id'] = '20160720000009';
    	$data['date'] = '2016-07-20';*/
    	/* 佳璐测试 end */

    	$result = NetworkHelper::curlRequest($url, $data);
    	echo $result;
    	echo '<br>';
    	$result = json_decode($result, true);
    	echo '<pre>';
    	var_dump($result);
    	echo '</pre>';
    }
}