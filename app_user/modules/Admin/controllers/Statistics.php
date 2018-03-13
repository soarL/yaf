<?php

use Admin as Controller;
use traits\PaginatorInit;
use models\Statistics;
use Illuminate\Database\Capsule\Manager as DB;
use helpers\ExcelHelper;
use helpers\ArrayHelper;
use models\Recharge;
use models\User;
use models\OddMoney;
use models\Odd;
use models\Withdraw;


/**
 * StatisticsController
 * 数据统计
 * 
 * @author chenwei <269646431@qq.com>
 * @version 1.0
 */
class StatisticsController extends Controller {
    use PaginatorInit;

    public $menu = 'statistics';
    
    /**
     * 运营部
     * @return mixed
     */
    public function operaterAction() {
        $this->submenu = 'operater';
        $excel = $this->getQuery('excel', 0);
        $queries = $this->queries->defaults(['sortBy'=>'time', 'sortType'=>'desc', 'beginTime'=>'', 'endTime'=>'']);
        $builder = Statistics::orderBy($queries->sortBy, $queries->sortType);
        if ($queries->beginTime !='') {
        	$builder->where('time', '>=', $queries->beginTime . ' 00:00:00');
        }
        if ($queries->endTime !='') {
        	$builder->where('time', '<=', $queries->endTime . ' 23:59:59');
        }

        if($excel) {
        	$data = $builder->get();
        	$other = [
        		'title' => '运营部',
        		'columns' => [
        			'id' => ['name'=>'编号'],
        			'time' => ['name'=>'日期'],
        			'registrations' => ['name'=>'注册量'],
        			'newRechargeUserNum' => ['name'=>'新用户充值（人数）'],
        			'oldRechargeUserNum' => ['name'=>'老用户充值（人数）'],
        			'rechargeMoney' => ['name'=>'充值总额'],
        			'pcRechargeMoney' => ['name'=>'PC端支付额'],
        			'appRechargeMoney' => ['name'=>'APP端支付额'],
        			'oddMoney' => ['name'=>'发标总额'],
        			'newInvestUserNum' => ['name'=>'新用户投资（人数）'],
        			'oldInvestUserNum' => ['name'=>'老用户投资（人数）'],
        			'investMoney' => ['name'=>'投资总额'],
        			'withdrawNum' => ['name'=>'提现数量'],
        			'withdrawMoney' => ['name'=>'提现总额'],
        			'newLoanUserNum' => ['name'=>'新用户借款（人数）'],
        			'oldLoanUserNum' => ['name'=>'老用户借款（人数）'],
        			'loanMoney' => ['name'=>'借款总额']
        		],
        	];
        	$excelRecords = [];
        	foreach ($data as $row) {
        		$excelRecords[] = $row;
        	}
        	ExcelHelper::getDataExcel($excelRecords, $other);
        } else {
        	$field = [
        		'sum(registrations) as sum_registrations',
        		'sum(newRechargeUserNum) as sum_newRechargeUserNum',
        		'sum(oldRechargeUserNum) as sum_oldRechargeUserNum',
        		'sum(rechargeMoney) as sum_rechargeMoney',
        		'sum(pcRechargeMoney) as sum_pcRechargeMoney',
        		'sum(appRechargeMoney) as sum_appRechargeMoney',
        		'sum(oddMoney) as sum_oddMoney',
        		'sum(newInvestUserNum) as sum_newInvestUserNum',
        		'sum(oldInvestUserNum) as sum_oldInvestUserNum',
        		'sum(investMoney) as sum_investMoney',
        		'sum(withdrawNum) as sum_withdrawNum',
        		'sum(withdrawMoney) as sum_withdrawMoney',
        		'sum(newLoanUserNum) as sum_newLoanUserNum',
        		'sum(oldLoanUserNum) as sum_oldLoanUserNum',
        		'sum(loanMoney) as sum_loanMoney'
        	];
        	$builderClone = clone $builder;
        	$data = $builder->paginate();
        	$data->appends($queries->all());
        	$sum = $builderClone->select(DB::raw(implode(',',$field)))->first();
        }
        $this->display('operater', ['data' => $data, 'queries'=>$queries, 'sum' => $sum]);
    }
    
    /**
     * 用户充值详情
     * @return mixed
     */
    public function rechargeAction() {
    	$this->submenu = 'operater';
    	$userTypes = Recharge::$userTypes;
    	$payTypes = Recharge::$payTypes;
    	$excel = $this->getQuery('excel', 0);
    	$queries = $this->queries->defaults(['sortBy'=>'userType', 'sortType'=>'desc', 'searchType'=>'', 'searchContent'=>'', 'date'=>'', 'begin'=>'', 'end'=>'']);
    	$builder = Recharge::with(['user' => function ($user){
	    		$user->select('userId', 'username');
	    	}])
	    	->where('source', 1)
	    	->where('status', 1)
	    	->where('mode', 'in')
	    	->where('operator', '<>', 'admin')
	    	->orderBy($queries->sortBy, $queries->sortType);
	    $builder = $this->rechargeWhere($builder, $queries, $userTypes, $payTypes);
    	if($excel) {
    		$recharge = $builder->get();
    		$other = [
    			'title' => '用户充值详情',
    			'columns' => [
    				'id' => ['name'=>'编号'],
    				'userType' => ['name'=>'用户类型'],
    				'username' => ['name'=>'用户名', 'type' => 'string'],
    				'time' => ['name'=>'充值时间'],
    				'money' => ['name'=>'充值金额', 'type' => 'string'],
    				'payType' => ['name'=>'支付方式'],
    			],
    		];
    		$excelRecords = [];
    		foreach ($recharge as $row) {
    			$row['userType'] = ArrayHelper::getValue($userTypes, $row->userType);
    			$row['username'] = $row->user->username;
    			$row['money'] = number_format($row->money, 2);
    			$row['payType'] = ArrayHelper::getValue($payTypes, $row->payType);
    			$excelRecords[] = $row;
    		}
    		ExcelHelper::getDataExcel($excelRecords, $other);
    	} else {
    		$builderClone = clone $builder;
    		$recharge = $builder->paginate();
    		$recharge->appends($queries->all());
    		$sum = $builderClone->sum('money');
    	}
    	$this->display('recharge', ['recharge' => $recharge, 'queries'=>$queries, 'sum' => $sum, 'payTypes' => $payTypes, 'userTypes' => $userTypes]);
    }
    
    /**
     * 大额监控
     * @return [type] [description]
     */
    public function bigDealAction(){
        $this->submenu = 'biddeal';
        $this->display('biddeal');
    }
    /**
     * 用户充值详情查询条件
     * @param object $builder
     * @param object $queries
     * @param array $userTypes
     * @param array $payTypes
     * @return object
     */
    private function rechargeWhere($builder, $queries, $userTypes, $payTypes) {
    	if ($queries->date != '') {
    		$builder->where('time', '>=', $queries->date . ' 00:00:00');
    		$builder->where('time', '<=', $queries->date . ' 23:59:59');
    	}
    	$searchContent = $queries->searchContent;
    	$begin = str_replace(',', '', $queries->begin);
    	$end = str_replace(',', '', $queries->end);
    	$existCon = $searchContent || $begin || $end;
    	if (($searchType = $queries->searchType) && $existCon) {
    		switch ($searchType) {
    			case 'username' :
    				$user = User::where('username', $searchContent)->select('userId')->first();
    				$user && $builder->where('userId', $user->userId);
    				break;
    			case 'userType' :
    				$userType = ArrayHelper::getKey($userTypes, $searchContent);
    				$userType && $builder->where('userType', $userType);
    				break;
    			case 'payType' :
    				$payType = ArrayHelper::getKey($payTypes, $searchContent);
    				$payType && $builder->where('payType', $payType);
    				break;
    			case 'money' :
    				$begin && $builder->where('money', '>=', $begin);
    				$end && $builder->where('money', '<=', $end);
    				break;
    			default :
    				break;
    		}
    	}
    	return $builder;
    }
    
    /**
     * 用户投资详情
     * @return mixed
     */
    public function investAction() {
    	$this->submenu = 'operater';
    	$excel = $this->getQuery('excel', 0);
    	$queries = $this->queries->defaults([
    		'searchType'=> '',
    		'searchContent'=>'',
    		'beginTime'=>'',
    		'endTime'=>'',
    		'begin' => '',
    		'end' => '',
    		'date' => '',
    		'sortBy' => '',
    		'sortType' => 'desc'
    	]);
        $oTable = with(new Odd());
        $omTable = with(new OddMoney());
        $uTable = with(new User());
    	$fields = [
    		_col($uTable, 'username'),
    		_col($uTable, 'addtime as registerTime'),
    		_col($oTable, 'oddTitle'),
    		_col($oTable, 'oddType'),
    		_col($oTable, 'oddYearRate'),
    		_col($oTable, 'oddBorrowStyle'),
    		_col($oTable, 'oddBorrowPeriod'),
    		_col($oTable, 'investType'),
    		_col($omTable, 'money'),
    		_col($omTable, 'time'),
    		_col($omTable, 'userType'),
    		_col($omTable, 'id')
    	];
    	
    	$builder = OddMoney::leftJoin($uTable, _col($omTable, 'userId'), '=', _col($uTable, 'userId'))
    		->leftJoin($oTable, _col($omTable, 'oddNumber'), '=', _col($oTable, 'oddNumber'))
    		->select($fields)
    		->where(_col($omTable, 'type'), 'invest');
    	// 排序
    	$builder = $this->investOrder($builder, $queries);
    	// 查询条件
    	$builder = $this->investWhere($builder, $queries);
    	
    	if($excel) {
    		$invest = $builder->get();
    		$invest = $this->investData($invest);
    		$other = [
    			'title' => '用户投资详情',
    			'columns' => [
    				'id' => ['name'=>'编号'],
    				'userType' => ['name'=>'用户类型'],
    				'username' => ['name'=>'用户名', 'type' => 'string'],
    				'registerTime' => ['name'=>'注册时间'],
    				'time' => ['name'=>'投资时间'],
    				'investType' => ['name'=>'投标类型'],
    				'oddType' => ['name'=>'标的类型'],
    				'oddTitle' => ['name'=>'标的名称'],
    				'period' => ['name'=>'标的周期'],
    				'oddYearRate' => ['name'=>'利率'],
    				'money' => ['name'=>'投资金额', 'type' => 'string'],
    			],
    		];
    		$excelRecords = [];
    		foreach ($invest as $row) {
    			$excelRecords[] = $row;
    		}
    		ExcelHelper::getDataExcel($excelRecords, $other);
    	} else {
    		$builderClone = clone $builder;
    		$invest = $builder->paginate();
    		$invest = $this->investData($invest);
    		$invest->appends($queries->all());
    		$sum = $builderClone->sum('money');
    	}
    	$this->display('invest', ['invest' => $invest, 'queries'=>$queries, 'sum' => $sum]);
    }
    
    /**
     * 用户投资详情数据处理
     * @param object $invest
     * @return object
     */
    private function investData($invest) {
    	foreach ($invest as &$value) {
    		$value->userType = OddMoney::$userTypes[$value->userType];
    		$value->investType = $value->investType == 1 ? '手动标' : '自动标';
    		$value->oddType = Odd::$oddTypes[$value->oddType]['long'];
    		$value->period = $this->getPeriod($value->oddBorrowStyle, $value->oddBorrowPeriod);
    		$value->oddYearRate = $value->oddYearRate*100 . '%';
    		$value->money = number_format($value->money, 2);
    	}
    	return $invest;
    }
    
    /**
     * 获取标的周期
     * @param string $oddBorrowStyle
     * @param string $oddBorrowPeriod
     * @return string
     */
    private function getPeriod($oddBorrowStyle, $oddBorrowPeriod) {
    	if ($oddBorrowStyle == 'month') {
    		return $oddBorrowPeriod . '个月';
    	} else if($oddBorrowStyle == 'day') {
    		return $oddBorrowPeriod . '天';
    	} else if($oddBorrowStyle == 'week') {
    		return $oddBorrowPeriod . '周';
    	} else {
    		return '其他';
    	}
    }
    
    /**
     * 用户投资详情排序
     * @param object $builder
     * @param object $queries
     * @return object
     */
    private function investOrder($builder, $queries) {
        $oTable = with(new Odd());
        $omTable = with(new OddMoney());
        $uTable = with(new User());
    	if ($sortBy = $queries->sortBy) {
    		$sortType = $queries->sortType;
    		switch ($sortBy) {
    			case 'registerTime' : 
    				$builder->orderBy('system_userinfo.addtime', $sortType);
    				break;
    			case 'oddPeriod' : 
    				$builder->orderBy('work_odd.oddBorrowStyle', $sortType)->orderBy('work_odd.oddBorrowPeriod', $sortType);
    				break;
    			default : 
    				$builder->orderBy('work_oddmoney.' . $sortBy, $sortType);
    				break;
    		}
    	}
    	return $builder;
    }
    
    /**
     * 用户充值详情查询条件
     * @param object $builder
     * @param object $queries
     * @return object
     */
    private function investWhere($builder, $queries) {
    	if ($queries->date != '') {
    		$builder->where('time', '>=', $queries->date . ' 00:00:00');
    		$builder->where('time', '<=', $queries->date . ' 23:59:59');
    	}
    	$searchContent = $queries->searchContent;
    	$beginTime = $queries->beginTime;
    	$endTime = $queries->endTime;
    	$begin = str_replace(',', '', $queries->begin);
    	$end = str_replace(',', '', $queries->end);
    	$existCon = $searchContent || $beginTime || $endTime || $begin || $end;
    	if (($searchType = $queries->searchType) && $existCon) {
    		switch ($searchType) {
    			case 'userType' :
    				$userType = ArrayHelper::getKey(OddMoney::$userTypes, $searchContent);
    				$userType && $builder->where('work_oddmoney.userType', $userType);
    				break;
    			case 'username' :
    				$builder->where('system_userinfo.username', $searchContent);
    				break;
    			case 'registerTime' :
    				$beginTime && $builder->where('system_userinfo.addtime', '>=', $beginTime . ' 00:00:00');
    				$endTime && $builder->where('system_userinfo.addtime', '<=', $endTime . ' 23:59:59');
    				break;
    			case 'investType' :
    				$investType = ArrayHelper::getKey(Odd::$investTypes, $searchContent);
    				in_array($investType, [0, 1]) && $builder->where('work_odd.investType', $investType);
    				break;
    			case 'oddType' :
    				$oddType = Odd::getTypeValueByName($searchContent);
    				$oddType && $builder->where('work_odd.oddType', $oddType);
    				break;
    			case 'oddTitle' :
    				$builder->where('work_odd.oddTitle', $searchContent);
    				break;
    			case 'oddPeriod' :
    				$oddPeriod = Odd::periodBack($searchContent);
    				$oddPeriod && $builder->where('work_odd.oddBorrowStyle', $oddPeriod['oddBorrowStyle'])->where('work_odd.oddBorrowPeriod', $oddPeriod['oddBorrowPeriod']);
    				break;
    			case 'money' :
    				$begin && $builder->where('work_oddmoney.money', '>=', $begin);
    				$end && $builder->where('work_oddmoney.money', '<=', $end);
    				break;
    			default :
    				break;
    		}
    	}
    	return $builder;
    }
    
    /**
     * 用户借款详情
     * @return mixed
     */
    public function loanAction() {
    	$this->submenu = 'operater';
    	$excel = $this->getQuery('excel', 0);
    	$queries = $this->queries->defaults([
    		'searchType'=> '',
    		'searchContent'=>'',
    		'beginTime'=>'',
    		'endTime'=>'',
    		'begin' => '',
    		'end' => '',
    		'date' => '',
    		'sortBy' => '',
    		'sortType' => 'desc'
    	]);
    	$fields = [
    		'system_userinfo.name',
    		'system_userinfo.phone',
    		'system_userinfo.city',
    		'work_oddinterest.operatetime as interestTime',
    		'work_oddmoney.money',
    		'work_oddmoney.time',
    		'work_oddmoney.userType',
    		'work_oddmoney.id'
    	];
    	$builder = OddMoney::leftJoin('system_userinfo', 'work_oddmoney.userId', '=', 'system_userinfo.userId')
    		->leftJoin('work_oddinterest', 'work_oddmoney.oddNumber', '=', 'work_oddinterest.oddNumber')
    		->select($fields)
    		->where('work_oddmoney.type', 'loan');
    	// 排序
    	$builder = $this->loanOrder($builder, $queries);
    	// 查询条件
    	$builder = $this->loanWhere($builder, $queries);
    	
    	if($excel) {
    		$loan = $builder->get();
    		$loan = $this->loanData($loan);
    		$other = [
    			'title' => '用户借款详情',
    			'columns' => [
    				'id' => ['name'=>'编号'],
    				'userType' => ['name'=>'用户类型'],
    				'name' => ['name'=>'名字', 'type' => 'string'],
    				'phone' => ['name'=>'手机号码'],
    				'city' => ['name'=>'城市时间'],
    				'time' => ['name'=>'借款时间'],
    				'money' => ['name'=>'借款金额', 'type' => 'string'],
    				'interestTime' => ['name'=>'还款时间'],
    			],
    		];
    		$excelRecords = [];
    		foreach ($loan as $row) {
    			$excelRecords[] = $row;
    		}
    		ExcelHelper::getDataExcel($excelRecords, $other);
    	} else {
    		$builderClone = clone $builder;
    		$loan = $builder->paginate();
    		$loan = $this->loanData($loan);
    		$loan->appends($queries->all());
    		$sum = $builderClone->sum('money');
    	}
    	$this->display('loan', ['loan' => $loan, 'queries'=>$queries, 'sum' => $sum]);
    }
    
    /**
     * 用户借款详情排序
     * @param object $builder
     * @param object $queries
     * @return object
     */
    private function loanOrder($builder, $queries) {
    	if ($sortBy = $queries->sortBy) {
    		$sortType = $queries->sortType;
    		switch ($sortBy) {
    			case 'interestTime' :
    				$builder->orderBy('work_oddinterest.operatetime', $sortType);
    				break;
    			default :
    				$builder->orderBy('work_oddmoney.' . $sortBy, $sortType);
    				break;
    		}
    	}
    	return $builder;
    }
    
    /**
     * 用户借款详情查询条件
     * @param object $builder
     * @param object $queries
     * @return object
     */
    private function loanWhere($builder, $queries) {
    	if ($queries->date != '') {
    		$builder->where('time', '>=', $queries->date . ' 00:00:00');
    		$builder->where('time', '<=', $queries->date . ' 23:59:59');
    	}
    	$searchContent = $queries->searchContent;
    	$beginTime = $queries->beginTime;
    	$endTime = $queries->endTime;
    	$begin = str_replace(',', '', $queries->begin);
    	$end = str_replace(',', '', $queries->end);
    	$existCon = $searchContent || $beginTime || $endTime || $begin || $end;
    	if (($searchType = $queries->searchType) && $existCon) {
    		switch ($searchType) {
    			case 'userType' :
    				$userType = ArrayHelper::getKey(OddMoney::$userTypes, $searchContent);
    				$userType && $builder->where('work_oddmoney.userType', $userType);
    				break;
    			case 'name' :
    				$builder->where('system_userinfo.name', $searchContent);
    				break;
    			case 'phone' :
    				$builder->where('system_userinfo.phone', $searchContent);
    				break;
    			case 'city' :
    				$builder->where('system_userinfo.city', $searchContent);
    				break;
    			case 'interestTime' :
    				$beginTime && $builder->where('work_oddinterest.operatetime', '>=', $beginTime . ' 00:00:00');
    				$endTime && $builder->where('work_oddinterest.operatetime', ',=', $beginTime . ' 23:59:59');
    				break;
    			case 'investType' :
    				$investType = ArrayHelper::getKey(Odd::$investTypes, $searchContent);
    				$investType && $builder->where('work_odd.investType', $investType);
    				break;
    			case 'money' :
    				$begin && $builder->where('work_oddmoney.money', '>=', $begin);
    				$end && $builder->where('work_oddmoney.money', '<=', $end);
    				break;
    			default :
    				break;
    		}
    	}
    	return $builder;
    }
    
    /**
     * 用户借款详情数据处理
     * @param object $loan
     * @return object
     */
    private function loanData($loan) {
    	foreach ($loan as &$loanRow) {
    		$loanRow->userType = OddMoney::$userTypes[$loanRow->userType];
    		$loanRow->money = number_format($loanRow->money, 2);
    	}
    	return $loan;
    }
    
    /**
     * 用户提现详情
     * @return mixed
     */
    public function withdrawAction() {
    	$this->submenu = 'operater';
    	$excel = $this->getQuery('excel', 0);
    	$queries = $this->queries->defaults([
    			'searchType'=> '',
    			'searchContent'=>'',
    			'beginTime'=>'',
    			'endTime'=>'',
    			'begin' => '',
    			'end' => '',
    			'date' => '',
    			'sortBy' => '',
    			'sortType' => 'desc'
    	]);
    	
    	$builder = Withdraw::with(['user' => function ($user){
    		$user->select('userId', 'username');
    	}]);
    	// 排序
    	if ($queries->sortBy != '' && $queries->sortType) {
    		$builder->orderBy($queries->sortBy, $queries->sortType);
    	}
    	// 查询条件
    	$builder = $this->withdrawWhere($builder, $queries);
    	 
    	if($excel) {
    		$withdraw = $builder->get();
    		$other = [
    			'title' => '提现详情',
    			'columns' => [
    				'id' => ['name'=>'编号'],
    				'username' => ['name'=>'用户名', 'type' => 'string'],
    				'addTime' => ['name'=>'提现时间'],
    				'outMoney' => ['name'=>'提现金额', 'type' => 'string'],
    				'fee' => ['name'=>'手续费', 'type' => 'string'],
    				'resMoney' => ['name'=>'实到金额', 'type' => 'string'],
    				'isLottery' => ['name'=>'使用提现卷'],
    				'bank' => ['name'=>'提现到'],
    			],
    		];
    		$excelRecords = [];
    		foreach ($withdraw as $row) {
    			$row['username'] = $row->user->username;
    			$row['outMoney'] = number_format($row->outMoney, 2);
    			$row['fee'] = number_format($row->fee, 2);
    			$row['resMoney'] = number_format($row->outMoney - $row->fee, 2);
    			$row['isLottery'] = $row->lotteryid == 0 ? '否' : '是';
    			$excelRecords[] = $row;
    		}
    		ExcelHelper::getDataExcel($excelRecords, $other);
    	} else {
    		$withdraw = $builder->paginate();
    		$withdraw->appends($queries->all());
    	}
    	$this->display('withdraw', ['withdraw' => $withdraw, 'queries'=>$queries]);
    }
    
    /**
     * 用户提现详情查询条件
     * @param object $builder
     * @param object $queries
     * @return object
     */
    private function withdrawWhere($builder, $queries) {
    	if ($queries->date != '') {
    		$builder->where('addtime', '>=', $queries->date . ' 00:00:00');
    		$builder->where('addtime', '<=', $queries->date . ' 23:59:59');
    	}
    	$searchContent = $queries->searchContent;
    	$beginTime = $queries->beginTime;
    	$endTime = $queries->endTime;
    	$begin = str_replace(',', '', $queries->begin);
    	$end = str_replace(',', '', $queries->end);
    	$existCon = $searchContent || $beginTime || $endTime || $begin || $end;
    	if (($searchType = $queries->searchType) && $existCon) {
    		switch ($searchType) {
    			case 'username' :
    				$builder->whereHas('user', function ($user)use($searchContent){
    					$user->where('username', $searchContent);
    				});
    				break;
    			case 'outMoney' :
    			case 'fee' :
    				$begin && $builder->where($searchType, '>=', $begin);
    				$end && $builder->where($searchType, '<=', $end);
    				break;
    			case 'outMoney-fee' :
    				$begin && is_numeric($begin) && $builder->whereRaw('outMoney-fee >= ' . $begin);
    				$end && is_numeric($end) && $builder->whereRaw('outMoney-fee <= ' . $end);
    				break;
    			case 'isLottery' :
    				if ($searchContent == '是') {
    					$builder->where('lotteryId', '<>', 0);
    				} elseif ($searchContent == '否') {
    					$builder->where('lotteryId', 0);
    				}
    				break;
    			case 'bank' :
    				$builder->where($searchType, $searchContent);
    				break;
    			default :
    				break;
    		}
    	}
    	return $builder;
    }
    
    /**
     * 满标情况
     * @return mixed
     */
    public function oddrateAction() {
        // ini_set('memory_limit', '1024M');
    	$this->submenu = 'oddrate';
    	$excel = $this->getQuery('excel', 0);
    	$queries = $this->queries->defaults(['sortBy' => '', 'sortType' => 'desc', 'beginTime'=>date('Y-m-d'), 'endTime'=>date('Y-m-d')]);
        if($queries->beginTime==''||$queries->endTime=='') {
            Flash::error('请选择查询时间！');
            $this->redirect('/admin/statistics/oddrate');
        }
        if((strtotime($queries->endTime.' 00:00:00')-strtotime($queries->beginTime.' 00:00:00'))>(31*24*60*60)) {
            Flash::error('查询时间范围不能大于31天！');
            $this->redirect('/admin/statistics/oddrate');
        }
        
        $oTable = with(new Odd());
        $omTable = with(new OddMoney());

    	$fields = [
    		_col($oTable, 'oddNumber'),
    		_col($oTable, 'openTime'),
    		_col($oTable, 'oddMoney'),
    		_col($oTable, 'oddMoneyLast'),
    		_col($oTable, 'oddBorrowPeriod'),
    		_col($omTable, 'money'),
    		_col($omTable, 'time')
    	];
    	$builder = Odd::leftJoin($omTable, _col($oTable, 'oddNumber'), '=', _col($omTable, 'oddNumber'))
    		// ->select($fields)
    		->where(_col($oTable, 'progress'), '<>', 'fail')
	    	->where(_col($oTable, 'oddBorrowStyle'), 'month')
    		->where(_col($omTable, 'type'), 'invest')
    		->orderBy(_col($omTable, 'time'));
    	$queries->beginTime && $builder->where(_col($oTable, 'openTime'), '>=', $queries->beginTime . ' 00:00:00');
    	$queries->endTime && $builder->where(_col($oTable, 'openTime'), '<=', $queries->endTime . ' 23:59:59');
    	$odd = $builder->get($fields);
    	$oddrate = $this->oddrateDataHandle($odd);
    	
    	// 排序
    	if (in_array($queries->sortBy, ['oddMoney', 'nowPercent'])) {
    		$oddrate = ArrayHelper::sortByColumn($oddrate, $queries->sortBy, $queries->sortType);
    	} elseif ($queries->sortBy == 'period' && $queries->sortType == 'desc') {
    		krsort($oddrate);
    	}
    	if($excel) {
    		$other = [
    			'title' => '满标情况',
    			'columns' => [
    				'period' => ['name'=>'投资周期'],
    				'oddMoney' => ['name'=>'发标金额', 'type' => 'string'],
    				'nowPercent' => ['name'=>'目前完成情况'],
    				'percent10' => ['name'=>'10%'],
    				'percent40' => ['name'=>'40%'],
    				'percent70' => ['name'=>'70%'],
    				'percent90' => ['name'=>'90%'],
    				'percent100' => ['name'=>'100%'],
    			]
    		];
    		$excelRecords = [];
    		foreach ($oddrate as $key => $row) {
    			$row['percent10'] = $row['oddRate']['percent10'];
    			$row['percent40'] = $row['oddRate']['percent40'];
    			$row['percent70'] = $row['oddRate']['percent70'];
    			$row['percent90'] = $row['oddRate']['percent90'];
    			$row['percent100'] = $row['oddRate']['percent100'];
    			$excelRecords[] = $row;
    		}
    		ExcelHelper::getDataExcel($excelRecords, $other);
    	}
    	$this->display('oddrate', ['oddrate' => $oddrate, 'queries'=>$queries]);
    }
    
    /**
     * 满标情况数据处理
     * @param object $odd
     */
    private function oddrateDataHandle($odd) {
    	$dataFormat = [];
    	foreach ($odd as $row) {
    		$dataFormat[$row->oddBorrowPeriod][$row->oddNumber]['openTime'] = $row->openTime;
    		$dataFormat[$row->oddBorrowPeriod][$row->oddNumber]['oddMoney'] = $row->oddMoney;
    		$dataFormat[$row->oddBorrowPeriod][$row->oddNumber]['oddMoneyLast'] = $row->oddMoneyLast;
    		if ($row->oddMoney == 0 || $row->oddMoneyLast == 0) {
    			$dataFormat[$row->oddBorrowPeriod][$row->oddNumber]['oddPercent'] = 1;
    		} else {
    			$dataFormat[$row->oddBorrowPeriod][$row->oddNumber]['oddPercent'] = ($row->oddMoney-$row->oddMoneyLast)/$row->oddMoney;
    		}
    		$dataFormat[$row->oddBorrowPeriod][$row->oddNumber]['invest'][] = ['money' => $row->money, 'time' => $row->time];
    	}
    	
    	// 数据初始化
    	$oddPeriodType = [1, 2, 3, 6, 12, 24];
    	$oddrate = [];
    	foreach ($oddPeriodType as $type) {
    		$oddrate[$type]['oddMoney'] = 0;
    		$oddrate[$type]['oddPercentTotal'] = 0;
    		$oddrate[$type]['oddNum'] = 0;
    	}
    	
    	foreach ($dataFormat as $key => $oddGroup) {
    		foreach ($oddGroup as $singleOdd) {
    			$oddrate[$key]['oddMoney'] += $singleOdd['oddMoney'];
    			$oddrate[$key]['oddPercentTotal'] += $singleOdd['oddPercent'];
    			$oddrate[$key]['oddNum'] += 1;
    		}
    		$oddrate[$key]['oddRate'] = $this->oddPercentRate($oddGroup);
    	}
    	
    	foreach ($oddrate as $key => &$rateGroup) {
    		$rateGroup['period'] = $key . '月标';
    		if ($rateGroup['oddNum']) {
    			$rateGroup['nowPercent'] = $rateGroup['oddPercentTotal']/$rateGroup['oddNum'];
    			unset($rateGroup['oddPercentTotal']);
    			unset($rateGroup['oddNum']);
    		}
    	
    	}
    	
    	$oddrate = $this->oddrateData($oddrate);
    	return $oddrate;
    }
    
    /**
     * 计算每种类型月标的进度均值
     * @param array $odd
     * @return array
     */
    private function oddPercentRate($oddGroup) {
    	foreach ($oddGroup as $key => &$theOdd) {
    		if (is_array($theOdd)) {
    			$theOdd['timeRate'] = $this->everyOddRate($theOdd);
    		}
    	}
    	$monthOddRate['percent10Total'] = 0;
    	$monthOddRate['percent40Total'] = 0;
    	$monthOddRate['percent70Total'] = 0;
    	$monthOddRate['percent90Total'] = 0;
    	$monthOddRate['percent100Total'] = 0;
    	$num = count($oddGroup);
    	$num100 = $num90 = $num70 = $num40 = $num10 = $num;
    	foreach ($oddGroup as $value) {
    		$monthOddRate['percent10Total'] += $value['timeRate']['percent10'];
    		$monthOddRate['percent40Total'] += $value['timeRate']['percent40'];
    		$monthOddRate['percent70Total'] += $value['timeRate']['percent70'];
    		$monthOddRate['percent90Total'] += $value['timeRate']['percent90'];
    		$monthOddRate['percent100Total'] += $value['timeRate']['percent100'];
    		if ($value['oddPercent'] < 1) {
    			$num100 -= 1;
    		}
    		if ($value['oddPercent'] < 0.9) {
    			$num90 -= 1;
    		}
    		if ($value['oddPercent'] < 0.7) {
    			$num70 -= 1;
    		}
    		if ($value['oddPercent'] < 0.4) {
    			$num40 -= 1;
    		}
    		if ($value['oddPercent'] < 0.1) {
    			$num10 -= 1;
    		}
    	}
    	$oddPercentRate['percent10'] = $monthOddRate['percent10Total'] / $num10;
    	$oddPercentRate['percent40'] = $monthOddRate['percent40Total'] / $num40;
    	$oddPercentRate['percent70'] = $monthOddRate['percent70Total'] / $num70;
    	$oddPercentRate['percent90'] = $monthOddRate['percent90Total'] / $num90;
    	$oddPercentRate['percent100'] = $monthOddRate['percent100Total'] / $num100;
    	return $oddPercentRate;
    }
    
    /**
     * 计算单个标的进度时间值
     * @param array $param
     * @return array
     */
    private function everyOddRate($oneOdd) {
    	$oddMoney = $oneOdd['oddMoney'];
    	$percent10 = $oddMoney*0.1;
    	$percent40 = $oddMoney*0.4;
    	$percent70 = $oddMoney*0.7;
    	$percent90 = $oddMoney*0.9;
    	$investMoney = 0;
    	$time['percent10'] = 0;
    	$time['percent40'] = 0;
    	$time['percent70'] = 0;
    	$time['percent90'] = 0;
    	$time['percent100'] = 0;
    	$opentime = strtotime($oneOdd['openTime']);
    	foreach ((array)$oneOdd['invest'] as $value) {
    		$investMoney += $value['money'];
    		$rateTime = strtotime($value['time']) - $opentime;
    		if ($investMoney >= $percent10) {
    			$time['percent10'] = $time['percent10'] ? $time['percent10'] : $rateTime;
    		}
    		if ($investMoney >= $percent40) {
    			$time['percent40'] = $time['percent40'] ? $time['percent40'] : $rateTime;
    		}
    		if ($investMoney >= $percent70) {
    			$time['percent70'] = $time['percent70'] ? $time['percent70'] : $rateTime;
    		}
    		if ($investMoney >= $percent90) {
    			$time['percent90'] = $time['percent90'] ? $time['percent90'] : $rateTime;
    		}
    		if ($investMoney >= $oddMoney) {
    			$time['percent100'] = $time['percent100'] ? $time['percent100'] : $rateTime;
    		}
    	}
    	return $time;
    }
    
    /**
     * 数据格式化
     * @param array $oddrate
     * @return array
     */
    private function oddrateData($oddrate) {
    	foreach ($oddrate as &$oddRow) {
    		$oddRow['oddMoney'] = number_format($oddRow['oddMoney'], 2);
    		$oddRow['nowPercent'] = isset($oddRow['nowPercent']) ? $oddRow['nowPercent'] : 0;
    		$oddRow['nowPercent'] = number_format($oddRow['nowPercent'] * 100, 2) . '%';
    		$oddRow['oddRate'] = isset($oddRow['oddRate']) ? $oddRow['oddRate'] : [
    			'percent10' => 0,
    			'percent40' => 0,
    			'percent70' => 0,
    			'percent90' => 0,
    			'percent100' => 0
    		];
    		foreach ($oddRow['oddRate'] as &$oddRateRow) {
    			$oddRateRow = $this->timeHandle($oddRateRow);
    		}
    	}
    	return $oddrate;
    }
    
    /**
     * 时间格式化
     * @param float $time
     * @return string
     */
    private function timeHandle($time) {
	    if (is_numeric($time)) {
		    $value = ["years" => 0, "days" => 0, "hours" => 0, "minutes" => 0, "seconds" => 0];
		    if ($time >= 86400) {
		        $value["days"] = floor($time/86400);
		        $time = ($time%86400);
		    }
		    if ($time >= 3600) {
		        $value["hours"] = floor($time/3600);
		        $time = ($time%3600);
		    }
		    if ($time >= 60) {
		        $value["minutes"] = floor($time/60);
		        $time = ($time%60);
		    }
		    $value["seconds"] = floor($time);
		    $t = $value["days"] . "D " . $value["hours"] . "H " . $value["minutes"] . "M " . $value["seconds"] . "S";
		    return $t;
	    } else {
	    	return (bool)FALSE;
	    }
    }
    
    	
}
