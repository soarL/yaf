<?php
use Admin as Controller;
use models\Role;
use models\User;
use models\OddMoney;
use models\OldData;
use models\AuthAction;
use models\Permission;
use models\Recharge;
use models\UserBank;
use models\UserDuein;
use models\UserUnbindBank;
use models\RechargeAgree;
use models\Sms;
use Illuminate\Database\Capsule\Manager as DB;
use tools\Pager;
use forms\admin\PermissionForm;
use forms\admin\RoleForm;
use forms\admin\UserForm;
use forms\admin\ActionForm;
use traits\PaginatorInit;
use helpers\ExcelHelper;
use models\LoginLog;
use models\Invest;
use business\Trial;
use helpers\IDHelper;
use custody\API;
use custody\Handler;
use models\UserBespoke;
/**
 * UserController
 * 用户管理
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class UserController extends Controller {
	use PaginatorInit;

	public $menu = 'user';

	public function llAction(){
		Trial::runTrial('Timing');exit;
		Repayment::entrance('regular','XFJR201703080000138','4','5','1','1','');
	}
	/**
     * 用户列表
     * @return mixed
     */
	public function listAction() {
		$this->submenu = 'user';
		$excel = $this->getQuery('excel', 0);
		$queries = $this->queries->defaults(['searchType'=>'username', 'searchContent'=>'', 'userType'=>'', 'beginTime'=>'', 'endTime'=>'', 'order'=>'', 'comments'=>'']);

		$builder = User::whereRaw('1=1')->with('waiter');
		if($queries->searchContent!='') {
			$searchContent = trim($queries->searchContent);
			$builder->where($queries->searchType, 'like','%'.$searchContent.'%');
		}
		if($queries->beginTime!='') {
            $builder->where('addtime', '>=', $queries->beginTime . ' 00:00:00');
        }
        if($queries->endTime!='') {
            $builder->where('addtime', '<=', $queries->endTime . ' 23:59:59');
        }
		if($queries->userType!='') {
			$builder->where('userType', $queries->userType);
		}
        if($queries->channel!='') {
            $builder->where('channel_id', $queries->channel);
        }
		if($queries->order!=''){
			$builder->orderBy($queries->order,'desc');
		}
		if($excel) {
			$users = $builder->get();
		} else {
			$users = $builder->paginate();
		}
		$userIds = [];
		foreach ($users as $key => $value) {
			$userIds[] = $value->userId;
		}
		$stayMoney = Invest::select(DB::raw('sum(benJIn) stay, userId'))
            ->where('status','0')->whereIn('userId', $userIds)
            ->groupBy('userId')
            ->get();
        $stayList = [];
		foreach ($stayMoney as $key => $value) {
            $stayList[$value->userId] = $value->stay;
		}
		$cityList = [];
		foreach ($users as $key => $value) {
			if(!$excel) {
				$value->username = _hide_phone($value->username);
				$value->phone = _hide_phone($value->phone);
			}
			if($value->cardnum){
            	$cityList[$value->userId] = IDHelper::getAddress($value->cardnum);
            }else{
            	$cityList[$value->userId] = '';
            }
		}

		if($excel) {
			$other = [
				'title' => '用户列表',
				'columns' => [
					'userId' => ['name'=>'用户ID', 'type'=>'string'],
					'username' => ['name'=>'用户名', 'type'=>'string'],
					'fundMoney' => ['name'=>'账户余额'],
					'stayMoney' => ['name'=>'待收'],
					'integral' => ['name'=>'积分'],
					'addtime' => ['name'=>'注册日期'],
					'name' => ['name'=>'姓名'],
					'phone' => ['name'=>'手机'],
					'thirdAccountStatus' => ['name'=>'托管'],
					'email' => ['name'=>'邮箱'],
					'comments' => ['name'=>'备注'],
				],
			];
			$excelRecords = [];
			foreach ($users as $row) {
				$row['stayMoney']  = empty($stayList[$row['userId']])?'0':$stayList[$row['userId']];
				$row['integral'] = $row['integral']/100;
				$row['name'] = ($row->cardstatus=='y')?$row['name'].'[认证]':$row['name'];
				$row['phone'] = ($row->phonestatus=='y')?$row['phone'].'[认证]':$row['phone'];
				$row['email'] = ($row->emailstatus=='y')?$row['email'].'[认证]':$row['email'];
				$row['thirdAccountStatus'] = ($row['thirdAccountStatus'] == 1)?'已开通':'';
				$excelRecords[] = $row;
			}
			ExcelHelper::getDataExcel($excelRecords, $other);
		} else {
			$users->appends($queries->all());
		}
		$users->appends($queries->all());
		$this->display('list', ['users'=>$users, 'queries'=>$queries ,'stayMoney' =>$stayList ,'cityList' =>$cityList]);
	}


	/**
     * 备注编辑
     */
	public function commentsAction(){
        $key = $this->getQuery('key');
        $value = $this->getQuery('value');
        $id = $this->getQuery('id');
        $re = DB::table('system_userinfo')->where('userId',$id)->update([$key=>$value]);
        $rdata['status'] = $re;
        $this->backJson($rdata);
	}

	/**
	 * 设置合伙人
	 */
	public function setPartnerAction() {
		$userId = $this->getQuery('userId');
		$hcparter = $this->getQuery('hcparter');
		$tuijian = $this->getUser();
		$user = User::where('tuijian', $tuijian->phone)->where('userId',$userId)->first();
		if($user){
			if($hcparter == 1){
				if($tuijian->hcparters->count() >= 10){
					Flash::error('合伙人数量超过限制！');
					$this->redirect('/admin/user/recommended');
				}
			}
			$user->hcparter = $hcparter;
			$status = $user->save();
			Flash::success('操作成功！');
			$this->redirect('/admin/user/recommended');
		}
		Flash::error('用户不存在！');
		$this->redirect('/admin/user/recommended');
	}

	/**
	 * 推荐人列表
	 * @return [type] [description]
	 */
	public function recommendedAction() {
		$this->submenu = 'recommended';
		$excel = $this->getQuery('excel', 0);
		$date = $this->getQuery('date', 0);
		$userId = $this->getQuery('userId', 0);

		$queries = $this->queries->defaults(['searchType'=>'username', 'searchContent'=>'', 'userType'=>'', 'beginTime'=>'', 'endTime'=>'', 'order'=>'', 'comments'=>'']);

		if($date){
			$startDate = date('Ym',strtotime($date)).'00';
			$endDate = date('Ym',strtotime($date.' +1 month')).'00';
		}else{
			$startDate = date('Ym',strtotime('-1 month')).'00';
			$endDate = date('Ym',strtotime('+1 month')).'00';
		}

		$builder = User::whereRaw('1=1')->with('waiter')->with(['UserDuein'=>function($query)use($startDate,$endDate){
				$query->where('date','>',$startDate)->where('date','<',$endDate);
			}]);

		if($userId){
			$tuijian = User::find($userId);
			if($tuijian){
				$thisuser = $this->getUser();
				if($tuijian->tuijian != $thisuser->phone){
					echo "走错路了兄弟";
					exit;
				}
			}else{
				echo "走错路了兄弟";
				exit;
			}
		}else{
			$tuijian = $this->getUser();
		}

		$builder->where('tuijian', $tuijian->phone);

		if($queries->searchContent!='') {
			$searchContent = trim($queries->searchContent);
			$builder->where($queries->searchType, 'like','%'.$searchContent.'%');
		}
		if($queries->beginTime!='') {
            $builder->where('addtime', '>=', $queries->beginTime . ' 00:00:00');
        }
        if($queries->endTime!='') {
            $builder->where('addtime', '<=', $queries->endTime . ' 23:59:59');
        }
		if($queries->userType!='') {
			$builder->where('userType', $queries->userType);
		}
        if($queries->channel!='') {
            $builder->where('channel_id', $queries->channel);
        }
		if($queries->order!=''){
			if($queries->order == 'duein'){
				$builder->leftjoin('user_duein',function($q){
					$q->on('system_userinfo.userId','=','user_duein.userId')->where('user_duein.date', '=',date('Ymd',strtotime('-1 day')));
				})->orderBy('stay','desc');
			}else{
				$builder->orderBy($queries->order,'desc');
			}
		}

		$users = $builder->get();
		$pusers = $builder->paginate();
		$userIds = [];
		foreach ($users as $key => $value) {
			$userIds[] = $value->userId;
		}
		$stayMoney = Invest::select(DB::raw('sum(benJIn) stay, userId'))
            ->where('status','0')->whereIn('userId', $userIds)
            ->groupBy('userId')
            ->get();
        $stayList = [];
		foreach ($stayMoney as $key => $value) {
            $stayList[$value->userId] = $value->stay;
		}

		$emonth = 0;
		$tmonth = 0;
		$dueinData = [];

		if(!$date){
			$month = date('Ym').'00';

			$cityList = [];
			foreach ($users as $key => $value) {
				foreach ($value->UserDuein as $key => $duein) {
					if(isset($dueinData[$duein->date])){
						$dueinData[$duein->date] += $duein->stay;
					}else{
						$dueinData[$duein->date] = $duein->stay;	
					}
				}

				if($value->cardnum){
	            	$cityList[$value->userId] = IDHelper::getAddress($value->cardnum);
	            }else{
	            	$cityList[$value->userId] = '';
	            }
			}

			foreach ($dueinData as $key => $value) {
				if($tuijian->recruit){
					$total = '9999999';
				}else{
					$total = $value;
				}
				if($key > $month){
					$tmonth += UserDuein::calcCommission($total,$value);
				}elseif($key < $month){
					$emonth += UserDuein::calcCommission($total,$value);
				}
			}
		}else{
			foreach ($users as $key => $value) {
				foreach ($value->UserDuein as $key => $duein) {
					if(isset($dueinData[$duein->date])){
						$dueinData[$duein->date] += $duein->stay;
					}else{
						$dueinData[$duein->date] = $duein->stay;	
					}
				}
			}
		}

		if($excel) {
			$other = [
				'title' => '佣金详情',
				'columns' => [
					'name' => ['name'=>'姓名', 'type'=>'string'],
					'type' => ['name'=>'佣金/待收', 'type'=>'string'],
				],
			];

			foreach ($dueinData as $key => $value) {
				$other['columns'][$key] = ['name'=>$key];
			}

			$other['columns']['total'] = ['name'=>'合计'];

			$excelRecords = [];
			foreach ($users as $row) {
				if($row->UserDuein->count() > 0){

					$data['type'] = '佣金';
					$data['name'] = $row['name'];
					$tmp = [0,0];
					foreach ($row->UserDuein as $key => $value) {
						if($tuijian->recruit){
							$total = '9999999';
						}else{
							$total = $dueinData[$value->date];
						}
						$a = UserDuein::calcCommission($total,$value->stay);
						$data[$value->date] = $a;
						$tmp[0] += $a;
						$tmp[1] += $value->stay;
					}
					$data['total'] = $tmp[0];
					$excelRecords[] = $data;

					$data['type'] = '待收';
					$data['name'] = $row['name'];
					foreach ($row->UserDuein as $key => $value) {
						$data[$value->date] = $value->stay;
						$tmp[1] += $value->stay;
					}
					$data['total'] = '';
					$excelRecords[] = $data;
				}
			}
			ExcelHelper::getDataExcel($excelRecords, $other);
		} else {
			$pusers->appends($queries->all());
		}
		$this->display('recommended', ['users'=>$pusers, 'queries'=>$queries ,'stayMoney' =>$stayList ,'cityList' =>$cityList, 'emonth' =>$emonth, 'tmonth' =>$tmonth]);
	}


	/**
	 * 合伙人列表
	 * @return [type] [description]
	 */
	public function partnerAction() {
		$this->submenu = 'partner';
		$excel = $this->getQuery('excel', 0);

		$queries = $this->queries->defaults(['searchType'=>'username', 'searchContent'=>'', 'userType'=>'', 'beginTime'=>'', 'endTime'=>'', 'order'=>'', 'comments'=>'']);

		if($excel){
			$startDate = '0';
			$endDate = '99999999';
		}else{
			$startDate = date('Ym',strtotime('-1 month')).'00';
			$endDate = date('Ym',strtotime('+1 month')).'00';
		}

		$builder = User::whereRaw('1=1')->with(['tuijians'=>function($a)use($startDate,$endDate){$a->with(['UserDuein'=>function($q)use($startDate,$endDate){
					$q->where('date','>',$startDate)->where('date','<',$endDate)->orderBy('date','asc');}]);
				}])->where('system_userinfo.hcparter','1');

		$tuijian = $this->getUser();

		$builder->where('system_userinfo.tuijian', $tuijian->phone);

		if($queries->searchContent!='') {
			$searchContent = trim($queries->searchContent);
			$builder->where($queries->searchType, 'like','%'.$searchContent.'%');
		}
		if($queries->beginTime!='') {
            $builder->where('addtime', '>=', $queries->beginTime . ' 00:00:00');
        }
        if($queries->endTime!='') {
            $builder->where('addtime', '<=', $queries->endTime . ' 23:59:59');
        }
		if($queries->userType!='') {
			$builder->where('userType', $queries->userType);
		}
        if($queries->channel!='') {
            $builder->where('channel_id', $queries->channel);
        }
		if($queries->order!=''){
			if($queries->order == 'duein'){
				$builder->leftjoin('system_userinfo as tuijians','system_userinfo.phone','=','tuijians.tuijian')
				->leftjoin('user_duein',function($q){
					$q->on('tuijians.userId','=','user_duein.userId')->where('user_duein.date', '=',date('Ymd',strtotime('-1 day')));
				})->groupBy('system_userinfo.userId')->select('system_userinfo.*',DB::raw('sum(user_duein.stay) as totalstay'))->orderBy('totalstay','desc');
			}else{
				$builder->orderBy($queries->order,'desc');
			}
		}

		if($excel) {
			$users = $builder->get();
		} else {
			$users = $builder->paginate();
		}
		$userIds = [];

		$dueData = [];

		foreach ($users as $key => $value) {
			foreach ($value->tuijians as $k => $tuijian) {
				foreach ($tuijian->UserDuein as $item) {
					if(isset($dueData[$value->userId][$item->date])){
						$dueData[$value->userId][$item->date] += $item->stay;
					}else{
						$dueData[$value->userId][$item->date] = $item->stay;
					}
					
					if($item->date == date('Ymd',strtotime('-1 day'))){
						$value->tuistay += $item->stay;
					}
				}
			}
			$userIds[] = $value->userId;
		}

		if(!$excel){
			$month = date('Ym').'00';
			$emonth = 0;
			$tmonth = 0;
			foreach ($dueData as $key => $value) {
				foreach ($value as $k => $v) {
					if($k > $month){
						$tmonth += UserDuein::calcCommission($v,$v);
					}elseif($k < $month){
						$emonth += UserDuein::calcCommission($v,$v);
					}
				}
			}
			$tmonth = round($tmonth * 0.2,2);
			$emonth = round($emonth * 0.2,2);

			$cityList = [];
			foreach ($users as $key => $value) {
				if($value->cardnum){
	            	$cityList[$value->userId] = IDHelper::getAddress($value->cardnum);
	            }else{
	            	$cityList[$value->userId] = '';
	            }
			}

		}else{
			$dueinData = [];
			foreach ($users as $key => $value) {
				foreach ($value->UserDuein as $key => $duein) {
					if(isset($dueinData[$duein->date])){
						$dueinData[$duein->date] += $duein->stay;
					}else{
						$dueinData[$duein->date] = $duein->stay;	
					}
				}
			}
		}

		if($excel) {
			$other = [
				'title' => '佣金详情',
				'columns' => [
					'name' => ['name'=>'姓名', 'type'=>'string'],
				],
			];

			foreach ($dueData as $key => $value) {
				foreach ($value as $k => $v) {
					$month = substr($k, 0,6);
					$other['columns'][$month] = ['name'=>$month.'月'];
				}
			}

			$other['columns']['total'] = ['name'=>'合计'];

			$resData = [];
			foreach ($dueData as $key => $value) {
				foreach ($value as $k => $v) {
					$month = substr($k, 0,6);
					if(isset($resData[$key][$month])){
						$resData[$key][$month] += UserDuein::calcCommission($v,$v);
					}else{
						$resData[$key][$month] = UserDuein::calcCommission($v,$v);
					}
				}
			}
			$excelRecords = [];
			foreach ($users as $row) {
				if(isset($resData[$row->userId])){
					$row['name'] = $row['name'];
					$tmp = 0;
					foreach ($resData[$row->userId] as $key => $value) {
						$row[$key] = round($value*0.2,2);
						$tmp += $row[$key];
					}
					$row['total'] = $tmp;
					$excelRecords[] = $row;
				}
			}
			ExcelHelper::getDataExcel($excelRecords, $other);
		} else {
			$users->appends($queries->all());
		}
		$this->display('partner', ['users'=>$users, 'queries'=>$queries ,'cityList' =>$cityList, 'emonth' =>$emonth, 'tmonth' =>$tmonth]);
	}

	/**
     * 合伙人排行
     * @return mixed
     */
	public function partnerOrderAction() {
		$this->submenu = 'partnerOrder';
		$queries = $this->queries->defaults(['order'=>'']);

		$builder = User::from('system_userinfo as user')->with(
						['parent'=>function($q){
							$q->with(['tuijians'=>function($q){
								$q->where('addtime','>',date('Y-m-d 00:00:00'));
							}]);
						}]
					)->leftjoin('user_duein as ud',function($a){
							$a->on('ud.userId','=','user.userId')->where('ud.date','=',date('Ymd',strtotime('-1 day')));
						}
					)->leftjoin('user_duein as tud',function($a){
							$a->on('tud.userId','=','user.userId')->where('tud.date','=',date('Ymd',strtotime('-2 day')));
						}
					)->leftjoin(DB::raw('(SELECT count(*) count,tuijian from system_userinfo GROUP BY tuijian) as t'), 't.tuijian', '=', 'user.tuijian')
					->whereNotNull('t.tuijian')
					->leftjoin('system_userinfo','system_userinfo.phone','=','user.tuijian')->where('system_userinfo.hcparter','1')
					->groupBy('user.tuijian')->select(DB::raw('sum(ud.stay) as stays,sum(tud.stay) as tstays'),'t.count','user.tuijian');
		if($queries->order!='') {
			$builder->orderBy($queries->order, 'desc');
		}	

		// $builder = User::with(['UserDuein'=>function($q){$q->where('date','=',date('Ymd',strtotime('-1 day')));}])
		// 		->with(['parent'=>function($q){
		// 			$q->with(['tuijians'=>function($q){
		// 				$q->where('addtime','>',date('Y-m-d 00:00:00'))->where('addtime','<',date('Y-m-d 00:00:00',strtotime('-1 day')));
		// 			}]);
		// 		}])->leftjoin('work_oddinterest_invest as s','system_userinfo.userId', '=', 's.userId')
		// 		->leftjoin(DB::raw('(SELECT count(*) count,tuijian,name tuiName from system_userinfo GROUP BY tuijian) as t'), 't.tuijian', '=', 'system_userinfo.tuijian')
		// 		->whereRaw('system_userinfo.tuijian in (select phone from system_userinfo where hcparter =1)')
		// 		->where('s.status','0')->groupBy('system_userinfo.tuijian')->select(DB::raw('sum(benJin) as stay'),'t.count','t.tuijian','system_userinfo.userId','system_userinfo.tuijian');
		// if($queries->order!='') {
		// 	$builder->orderBy($queries->order, 'desc');
		// }
		$records = $builder->paginate();

		// $stay = [];
		// foreach ($records as $key => $value) {
		// 	$stay[$key] =  User::where('phone',$value->tuijian)->select('name')->first();
		// }
		$records->appends($queries->all());
		$this->display('partnerOrder', ['records'=>$records, 'queries'=>$queries]);
	}

	/**
     * 验证码列表
     * @return mixed
     */
	public function smsAction() {
		$this->submenu = 'sms';
		$queries = $this->queries->defaults(['searchType'=>'username', 'searchContent'=>'', 'userType'=>'', 'beginTime'=>'', 'endTime'=>'']);

		$builder = Sms::whereRaw('1=1');
		if($queries->searchContent!='') {
			$builder->where($queries->searchType, $queries->searchContent);
		}
		if($queries->beginTime!='') {
			$builder->where('addtime', '>=', $queries->beginTime);
		}
		if($queries->endTime!='') {
			$builder->where('addtime', '<=', $queries->endTime);
		}
		if($queries->userType!='') {
			$builder->where('userType', $queries->userType);
		}
		$records = $builder->orderBy('sendTime', 'desc')->paginate();
		$records->appends($queries->all());
		$this->display('sms', ['records'=>$records, 'queries'=>$queries]);
	}

	/**
     * 查看用户
     * @return mixed
     */
	public function showAction() {
		$this->submenu = 'user';
		$userId = $this->getQuery('userId', false);
		$user = false;
		$balance = false;
		if(!$userId) {
			$username = $this->getQuery('username', '');
			$name = $this->getQuery('name', '');
			$phone = $this->getQuery('phone', '');
			$cardnum = $this->getQuery('cardnum', '');
			$email = $this->getQuery('email', '');
			if($username!='') {
				$user = User::where('username', $username)->first();
			} else if($name!='') {
				$user = User::where('name', $name)->first();
			} else if($phone!='') {
				$user = User::where('phone', $phone)->first();
			} else if($cardnum!='') {
				$user = User::where('cardnum', $cardnum)->first();
			} else if($email!='') {
				$user = User::where('email', $email)->first();
			}
		} else {
			$user = User::find($userId);
		}
		if(!$user) {
			Flash::error('用户不存在！');
			$this->redirect('/admin/user/list');
		} else {
            $result = API::accountQuery($user->userId);
            $cAccount = false;
            if($result['status']==1) {
                $cAccount = $result['data'];
            }
		}
        $oldTenderMoney = OldData::getTenderMoneyByUser($userId);
        $tenderMoney = $oldTenderMoney + OddMoney::getTenderMoneyByUser($userId);
        $bank = UserBank::where('userId', $userId)->where('status', '1')->first();

		$frozenOdds = OddMoney::where('status','0')->where('userId', $userId)->get([DB::raw('distinct oddNumber')]);

		$this->display('show', ['user'=>$user, 'frozenOdds'=>$frozenOdds, 'tenderMoney'=>$tenderMoney, 'cAccount'=>$cAccount, 'bank'=>$bank]);	
	}

	/**
     * 修改用户
     * @return mixed
     */
	public function updateAction() {
		$this->submenu = 'user';
		$userId = $this->getQuery('userId');
		$user = User::find($userId);
		$roles = Role::all();
		$hasRoles = $user->roles;
		$roleIds = [];
		foreach ($hasRoles as $role) {
			$roleIds[] = $role->id;
		}
		$this->display('form', ['user'=>$user, 'roles'=>$roles, 'hasRoles'=>$roleIds]);	
	}

	/**
     * 保存用户
     * @return mixed
     */
	public function saveAction() {
		$params = $this->getAllPost();
        $params['scene'] = 'user';
		$form = new UserForm($params);
		if($form->update()) {
			Flash::success('操作成功！');
			$this->redirect('/admin/user/list');
		} else {
			Flash::error($form->posError());
			$this->goBack();
		}
	}

	/**
     * 角色列表
     * @return mixed
     */
	public function rolesAction() {
		$this->submenu = 'role';
		$roles = Role::paginate();
		$this->display('roles', ['roles'=>$roles]);	
	}

	/**
     * 权限列表
     * @return mixed
     */
	public function permissionsAction() {
		$this->submenu = 'permission';
		$permissions = Permission::paginate();
		$this->display('permissions', ['permissions'=>$permissions]);
	}

	/**
     * 添加权限
     * @return mixed
     */
	public function addPermissionAction() {
		$this->submenu = 'permission';
		$permission = new Permission();
		$this->display('permissionForm', ['permission'=>$permission]);
	}

	/**
     * 修改权限
     * @return mixed
     */
	public function updatePermissionAction() {
		$this->submenu = 'permission';
		$id = $this->getQuery('id');
		$permission = Permission::find($id);
		$this->display('permissionForm', ['permission'=>$permission]);
	}

	/**
     * 保存权限
     * @return mixed
     */
	public function savePermissionAction() {
		$params = $this->getAllPost();
		$form = new PermissionForm($params);
		if($form->save()) {
			Flash::success('操作成功！');
			$this->redirect('/admin/user/permissions');
		} else {
			Flash::error($form->posError());
			$this->goBack();
		}
	}

	/**
     * 删除权限
     * @return mixed
     */
	public function deletePermissionAction() {
		$id = $this->getPost('id', 0);
		$permission = Permission::find($id);
		if($permission->delete()) {
            $rdata['status'] = 1;
            $rdata['info'] = '删除成功！';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '删除失败！';
            $this->backJson($rdata);
        }
	}

	/**
     * 添加角色
     * @return mixed
     */
	public function addRoleAction() {
		$this->submenu = 'role';
		$permissions = Permission::all();
		$role = new Role();
		$perms = [];
		$this->display('roleForm', ['role'=>$role, 'permissions'=>$permissions, 'perms'=>$perms]);
	}

	/**
     * 修改角色
     * @return mixed
     */
	public function updateRoleAction() {
		$this->submenu = 'role';
		$id = $this->getQuery('id');
		$role = Role::find($id);
		$permissions = Permission::all();
		$perms = $role->perms;
		$permsIds = [];
		foreach ($perms as $perm) {
			$permsIds[] = $perm->id;
		}
		$this->display('roleForm', ['role'=>$role, 'permissions'=>$permissions, 'perms'=>$permsIds]);
	}

	/**
     * 保存角色
     * @return mixed
     */
	public function saveRoleAction() {
		$params = $this->getAllPost();
		$form = new RoleForm($params);
		if($form->save()) {
			Flash::success('操作成功！');
			$this->redirect('/admin/user/roles');
		} else {
			Flash::error($form->posError());
			$this->goBack();
		}
	}

	/**
     * 删除角色
     * @return mixed
     */
	public function deleteRoleAction() {
		$id = $this->getPost('id', 0);
		$role = Role::find($id);
		if($role->delete()) {
            $rdata['status'] = 1;
            $rdata['info'] = '删除成功！';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '删除失败！';
            $this->backJson($rdata);
        }
	}

	/**
     * 管理员列表
     * @return mixed
     */
	public function adminsAction() {
		$this->submenu = 'admin';
		$queries = $this->queries->defaults(['searchType'=>'username', 'searchContent'=>'', 'beginTime'=>'', 'endTime'=>'']);
		$builder = User::has('roles')->with('roles');
		if($queries->searchContent!='') {
			$builder->where($queries->searchType, $queries->searchContent);
		}
		if($queries->beginTime!='') {
			$builder->where('addtime', '>=', $queries->beginTime);
		}
		if($queries->endTime!='') {
			$builder->where('addtime', '<=', $queries->endTime);
		}
		$users = $builder->orderBy('addtime', 'desc')->paginate();
		$users->appends($queries->all());
		$this->display('admins', ['users'=>$users, 'queries'=>$queries]);
	}

	/**
     * 行为列表
     * @return mixed
     */
	public function actionsAction() {
		$this->submenu = 'action';
		$queries = $this->queries->defaults(['searchType'=>'id', 'searchContent'=>'', 'beginTime'=>'', 'endTime'=>'']);

		$builder = AuthAction::whereRaw('1=1');
		if($queries->searchContent!='') {
			$builder->where($queries->searchType, $queries->searchContent);
		}
		if($queries->beginTime!='') {
			$builder->where('created_at', '>=', $queries->beginTime);
		}
		if($queries->endTime!='') {
			$builder->where('created_at', '<=', $queries->endTime);
		}
		$actions = $builder->orderBy('created_at', 'desc')->paginate();
		$actions->appends($queries->all());
		$this->display('actions', ['actions'=>$actions, 'queries'=>$queries]);
	}

	/**
     * 行为树形列表
     * @return mixed
     */
	public function actTreeAction() {
		$this->submenu = 'action';
		$queries = $this->queries->defaults(['searchType'=>'id', 'searchContent'=>'', 'beginTime'=>'', 'endTime'=>'']);
		$actions = AuthAction::tree();
		$this->display('actTree', ['actions'=>$actions, 'queries'=>$queries]);
	}

	/**
     * 获取行为信息
     * @return mixed
     */
	public function getActInfoAction() {
		$this->submenu = 'action';
		$id = $this->getPost('id', 0);
		$action = AuthAction::find($id);
		$rdata = [];
		if($action) {
			$rdata = ['status'=>1, 'action'=>$action];
		} else {
			$rdata = ['status'=>0, 'info'=>'行为不存在！'];
		}
		$this->backJson($rdata);
	}

	/**
     * 保存行为信息
     * @return mixed
     */
	public function saveActInfoAction() {
		$this->submenu = 'action';
		$params = $this->getAllPost();
		$form = new ActionForm($params);
		$rdata = [];
		if($form->save()) {
			Flash::success('操作成功！');
			$rdata['status'] = 1;
			$rdata['info'] = '操作成功！';
		} else {
			$rdata['status'] = 0;
			$rdata['info'] = $form->posError();
		}
		$this->backJson($rdata);
	}

	/**
     * 添加行为
     * @return mixed
     */
	public function addActAction() {
		$this->submenu = 'action';
		$action = new AuthAction();
		$action->rank = 0;
		$topActions = AuthAction::where('parent_id', 0)->get();
		$this->display('actionForm', ['action'=>$action, 'topActions'=>$topActions]);
	}

	/**
     * 修改行为
     * @return mixed
     */
	public function updateActAction() {
		$this->submenu = 'action';
		$id = $this->getQuery('id');
		$action = AuthAction::with('perm')->where('id', $id)->first();
		$topActions = AuthAction::where('parent_id', 0)->get();
		$this->display('actionForm', ['action'=>$action, 'topActions'=>$topActions]);
	}

	/**
     * 保存行为
     * @return mixed
     */
	public function saveActAction() {
		$params = $this->getAllPost();
		$form = new ActionForm($params);
		if($form->save()) {
			Flash::success('操作成功！');
			$this->redirect('/admin/user/actions');
		} else {
			Flash::error($form->posError());
			$this->goBack();
		}
	}

	/**
     * 删除行为
     * @return mixed
     */
	public function deleteActAction() {
		$id = $this->getPost('id', 0);
		$action = AuthAction::find($id);
		if($action->delete()) {
			AuthAction::where('parent_id', $id)->delete();
            $rdata['status'] = 1;
            $rdata['info'] = '删除成功！';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '删除失败！';
            $this->backJson($rdata);
        }
	}

	/**
	 *	用户资金操作
	 */
	public function moneyAction(){
		$this->submenu = 'user';
		$queries = $this->queries->defaults(['userId'=>'']);
		$userId = $queries->userId;
		$money = User::where('userId',$userId)->pluck('fundMoney');
		$userMoney = API::queryUserMoney($userId);
		$fields = Recharge::where('userId',$userId)->orderBy('id','desc')->paginate(15);
		$fields->appends($queries->all());
		$this->display('money', ['list'=>$fields,'money'=>$money,'queries'=>$queries, 'balance'=>$userMoney['balance']]);
	}


	/**
	 * 写入资金日志
	 */
	public function moneylogAction(){
		$data = $this->getAllPost();
		$data['time'] = date("Y-m-d H:i:s");
		$data['operator'] = $this->getUser()->userId;
		$re = Recharge::insert($data);
		if($re){
            Flash::success('操作成功！');
            $this->redirect('/admin/user/money?userId='.$data['userId']);
        } else {
            Flash::error('操作失败');
            $this->redirect('/admin/user/money?userId='.$data['userId']);
        }
	}

	/**
	 * 接口操作客户金额
	 */
	public function increaseAction(){
		$queries = $this->getAllQuery();
		$data = [];
		if($queries['mode'] == 'out'){
			$data['cmd'] = 'lessMoney';
		}else{
			$data['cmd'] = 'addMoney';
		}
		$data['id'] = $queries['id'];
		$data['type'] = $queries['status'];
		$data['userId'] = $queries['userId'];
		$data['time'] = microtime();
		$result = API::increase($data);
		if($result) {
            Flash::success('操作成功！');
            $this->redirect('/admin/user/money?userId='.$data['userId']);
        } else {
            Flash::error('操作失败');
            $this->redirect('/admin/user/money?userId='.$data['userId']);
        }
	}

	/**
	 * 推广
	 * @return mixed
	 */
    public function spreadAction() {
    	$u1 = $this->getQuery('promoter');
    	$u2 = $this->getQuery('bePromoter');
		$re = DB::table('user_friend')->leftjoin("system_userinfo as u1",'user_friend.userId','=','u1.userId')
				->leftjoin("system_userinfo as u2",'user_friend.friend','=','u2.userId')
				->orderBy('user_friend.id','desc');
        if (!empty($u1)){
            $re = $re->where('u1.username','=',$u1);
        }
        if (!empty($u2)){
            $re = $re->where('u2.username','=',$u2);
        }
		$fields = $re->select('user_friend.*','u1.username as p_username','u2.username as b_username')->paginate(15);
        $re = $this->pagin($fields);
        $this->display('spread',['list'=>$fields]);
    }

    /**
     * 约标详情
     */
    public function bespokesAction(){
    	$this->menu = 'repayment';
    	$this->submenu = 'bespokes';
		
        $queries = $this->queries->defaults(['searchType'=>'name', 'searchContent'=>'','sortBy'=>'created_at' ,'sortType'=>'desc','beginTime'=>'', 'endTime'=>'', 'excel'=>0]);

        $excel = $queries->excel;

		$builder = UserBespoke::with(['user'=>function($q) {$q->select('userId', 'phone', 'name');}]);

        if($queries->searchContent!='') {
			$builder->whereHas('user', function($q) use($queries) {
				$q->where($queries->searchType, $queries->searchContent);
			});
        }

        if($queries->type!=''){
			$builder->where('month', $queries->type);
        }
        if($queries->status!=''){
			$builder->where('status',$queries->status);
        }
        if($queries->beginTime!='') {
            if($queries->timeType == 'subscribe'){
				$builder->where('time', '>=', $queries->beginTime);
            }else{
				$builder->where('created_at', '>=', $queries->beginTime);
            }
        }
        if($queries->endTime!='') {
            if($queries->timeType == 'subscribe'){
				$builder->where('time', '<=', $queries->endTime);
            }else{
				$builder->where('created_at', '<=', $queries->endTime);
            }
        }
		$builder->orderBy($queries->sortBy, $queries->sortType);
		if($excel) {
			$data = $builder->get()->toArray();
			$other = [
					'title' => '标的管理',
					'columns' => [
							'name' => ['name'=>'用户姓名'],
							'phone' => ['name'=>'联系方式'],
							'money' => ['name'=>'预约金额'],
							'time' => ['name'=>'预约投标时间'],
							'month' => ['name'=>'预约类型'],
							'status' => ['name'=>'状态'],
							'created_at' => ['name'=>'登记时间'],
							'remark' => ['name'=>'备注'],
					],
			];
			$excelRecords = [];
			foreach ($data as $row) {
				$row['name'] = $row['user']['name'];
				$row['phone'] = $row['user']['phone'];
				$excelRecords[] = $row;
			}
			ExcelHelper::getDataExcel($excelRecords, $other);
		}
    	$list = $builder->paginate(15);
    	$list->appends($queries->all());
        $this->display('bespokes',['list'=>$list, 'queries'=>$queries]);
    }

    /**
     * 约标状态
     */
    public function besAction(){
    	$id = $this->getQuery('id');
    	$status = $this->getQuery('status');
    	$re = DB::table('user_bespokes')->where('id',$id)->update(['status'=>$status]);
    	if($re) {
            Flash::success('操作成功！');
            $this->redirect('/admin/user/bespokes');
        } else {
            Flash::error('操作失败');
            $this->redirect('/admin/user/bespokes');
        }
    }

    /**
     * 约标备注
     */
    public function besremarkAction(){
        $id = $this->getPost('id', 0);
        $remark = $this->getPost('remark', '');
    	$status = DB::table('user_bespokes')->where('id',$id)->update(['remark'=>$remark]);
	 	if($status) {
            Flash::success('操作成功!');
            $rdata['status'] = 1;
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = API::$msg;
            $this->backJson($rdata);
        }
    }

	public function checkAction(){
        $id = $this->getPost('id', 0);
        $status = $this->getPost('status', '');
        $update = DB::table('user_bespokes')->where('id',$id)->update(['status'=>$status]);
        if($update) {
            $rdata['status'] = 1;
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = API::$msg;
            $this->backJson($rdata);
        }
    }

     /*
     * 查看绑定申请
     */
    public function unbindAction(){
    	$this->submenu= 'unbind';
        $re = DB::table('user_bank_unbind as unbind')->leftjoin('user_bank_account AS bank','unbind.bankId','=','bank.id')->where('unbind.status','0')->select('unbind.id as unbindId','unbind.addTime','unbind.userId','unbind.bankId','bank.bankNum','bank.bank','bank.bankUsername')->orderBy('unbind.addTime','DESC')->paginate(15);
        $this->display('unbind', ['list'=>$re]);

   }
   
   /*
    * 解绑操作
    */
   public function doUnbindAction(){
   		$bankId = $this->getQuery('bankId', 0);
   		$unbindId = $this->getQuery('unbindId', 0);
		if(!$bankId||!$unbindId){
		   	$rdata['status'] = 0;
        	$rdata['info'] = '参数错误，请联系技术！';
        	$this->backJson($rdata);
		}
		$unbindCard = UserUnbindBank::find($unbindId);
		$userBank = UserBank::find($bankId);

		if(!$unbindCard||!$userBank) {
        	$unbindCard->status = -1;
			$unbindCard->save();
        	$rdata['status'] = 0;
        	$rdata['info'] = '银行卡或解绑申请不存在！';
        	$this->backJson($rdata);
		}
		if($unbindCard->userId != $userBank->userId){
			$unbindCard->status = -1;
			$unbindCard->save();
        	$rdata['status'] = 0;
        	$rdata['info'] = '用户ID与银行卡ID不匹配！';
        	$this->backJson($rdata);
		}
		$userBank->status = 0;
		$unbindCard->status = 2;
		
		if($userBank->save()&&$unbindCard->save()) {
			RechargeAgree::where('id', $userBank->agreeID)->delete();
			Flash::success('解绑成功！');
			$rdata['status'] = 1;
		} else {
			$rdata['status'] = 0;
        	$rdata['info'] = '解绑失败，请联系技术！';
		}
		$this->backJson($rdata);
   }

    /**
     * 上传金额
     */
    public function updateXLSAction(){
	    set_time_limit(0);
	    DB::table('tmp')->delete();
	    $filename = '/tmp/1.'.pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
	    move_uploaded_file($_FILES['file']['tmp_name'], $filename);
		$data = ExcelHelper::format_excel2array($filename);
		$user = DB::table('system_userinfo')->select('userId','fundMoney')->get();
		foreach($data as $value){
			foreach ($user as $key => $val) {
				if($val->userId == $value['C']){
					DB::table('tmp')->insert(['userId'=>$value['C'],'money'=>$value['E'],'fundMoney'=>$val->fundMoney]);
					unset($user[$key]);
					break;
				}
			}
		}
		unlink($filename);
		Flash::success('操作成功！');
        $this->redirect('/admin/user/dif');
    }


    public function difAction(){
    	$this->submenu = 'dif';
    	$data = DB::table('tmp')->select('userId','money','fundMoney')->whereRaw('fundMoney != money')->where('userId','!=','1508000')->get();
    	$this->display('dif',['data'=>$data]);
    }

    public function emailAction(){
    	$this->menu = 'sms';
    	$this->submenu = 'email';
    	$this->display('email');
    }

    public function sendMailAction(){
    	$userId = $this->getPost('userId');
    	$array['email'] = DB::table('system_userinfo')->where('userId',$userId)->first()->email;
    	$array['html'] = $this->getPost('html');
    	$array['title'] = $this->getPost('title');
    	if(API::sendMail($array)){
            Flash::success('操作成功！');
            $this->redirect('/admin/user/email');
        } else {
            Flash::error('操作失败');
            $this->redirect('/admin/user/email');
        }
    }
    
    /**
     * 登录日志列表
     * @return mixed
     */
    public function loginLogAction() {
    	$this->submenu = 'loginlog';
    	$excel = $this->getQuery('excel', 0);
    	$queries = $this->queries->defaults(['userId'=>'', 'beginTime'=>'', 'endTime'=>'']);
    	$builder = LoginLog::with('user')->whereRaw('1=1');
    	if($queries->userId){
    		$builder->where('userId', '=', $queries->userId);
    	}
    	if($queries->beginTime!='') {
    		$builder->where('loginTime', '>=', $queries->beginTime . ' 00:00:00');
    	}
    	if($queries->endTime!='') {
    		$builder->where('loginTime', '<=', $queries->endTime . ' 23:59:59');
    	}
    	if($excel) {
    		$logs = $builder->orderBy('loginTime', 'desc')->get();
    		$other = [
    			'title' => '登录日志',
    			'columns' => [
    				'id' => ['name' => '编号'],
    				'userId' => ['name' => '用户编号'],
    				'loginTime' => ['name' => '登录时间']
    			],
    		];
    		ExcelHelper::getDataExcel($logs, $other);
    	} else {
    		$logs = $builder->orderBy('loginTime', 'desc')->paginate(20);
    		$logs->appends($queries->all());
    	}
    	$this->display('loginLog', ['logs'=>$logs, 'queries'=>$queries]);
    }

    /**
     * 用户资金同步
     */
    public function syncMoneyAction() {
        $userId = $this->getPost('userId', '');
        $user = User::where('userId', $userId)->first(['userId', 'custody_id', 'fundMoney', 'frozenMoney', 'username']);

        $rdata = [];
        if(!$user) {
            $rdata['status'] = 0;
            $rdata['info'] = '用户不存在！';
            $this->backJson($rdata);
        }
        if($user->custody_id=='') {
            $rdata['status'] = 0;
            $rdata['info'] = '用户未开通存管！';
            $this->backJson($rdata);
        }

        $data = [];
        $data['accountId'] = $user->custody_id;
        $data['startDate'] = date('Ymd', time()-2*24*3600);
        $data['endDate'] = date('Ymd');
        $data['type'] = 9;
        $data['tranType'] = '7820';
        $data['pageNum'] = 1;
        $data['pageSize'] = 50;
        $handler = new Handler('accountDetailsQuery', $data);
        $result = $handler->api();
        if($result['retCode']==Handler::SUCCESS) {
            $list = json_decode($result['subPacks'], true);
            foreach ($list as $item) {
                $params = [];
                $params['tradeNo'] = $item['inpDate'].$item['inpTime'].$item['traceNo'];
                $params['cid'] = $item['accountId'];
                $params['money'] = $item['txAmount'];
                $params['flag'] = $item['txFlag'];
                $params['tranType'] = $item['tranType'];
                API::syncLog($params);
            }
        }

        $result = API::syncMoney($user);
        if($result['status']) {
            Flash::success('同步成功！');
            $rdata['status'] = 1;
            $rdata['info'] = $result['msg'];
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = $result['msg'];
            $this->backJson($rdata);
        }
    }

    /**
     * 用户同步信息
     */
    public function syncInfoAction() {
        $userId = $this->getPost('userId', '');
        $type = $this->getPost('type', '');
        $user = User::where('userId', $userId)->first();
        $rdata = [];
        if(!$user) {
            $rdata['status'] = 0;
            $rdata['info'] = '用户不存在！';
            $this->backJson($rdata);
        }
        if($user->custody_id=='') {
            $rdata['status'] = 0;
            $rdata['info'] = '用户未开通存管！';
            $this->backJson($rdata);
        }
        $result = [];
        if($type=='password') {
            $result = API::syncPassword($user);
        } else if($type=='auto_bid') {
            $result = API::syncAuth($user, 1);
        } else if($type=='auto_credit') {
            $result = API::syncAuth($user, 2);
        } else if($type=='bank_card') {
            $result = API::refreshUserBank($user);
        } else {
            $result = ['status'=>0, 'msg'=>'同步类型错误！'];
        }
        if($result['status']) {
            Flash::success('同步成功！');
            $rdata['status'] = 1;
            $rdata['info'] = '同步成功！';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = $result['msg'];
            $this->backJson($rdata);
        }
    }

    /**
     * 修改手机号[仅限于修改未开通存管的手机号]
     */
    public function updatePhoneAction() {
        $userId = $this->getPost('userId', '');
        $phone = $this->getPost('phone', '');

        if($phone=='') {
            $rdata['status'] = 0;
            $rdata['info'] = '请输入手机号！';
            $this->backJson($rdata);
        }

        $user = User::where('userId', $userId)->first();
        $rdata = [];
        if(!$user) {
            $rdata['status'] = 0;
            $rdata['info'] = '用户不存在！';
            $this->backJson($rdata);
        }
        if($user->custody_id!='') {
            $rdata['status'] = 0;
            $rdata['info'] = '用户已经开通存管，需要用户自行修改！';
            $this->backJson($rdata);
        }
        if($user->phone==$phone) {
            $rdata['status'] = 0;
            $rdata['info'] = '新手机号与旧手机号一致，无需修改！';
            $this->backJson($rdata);
        }

        $count = User::where('phone', $phone)->where('userId', '<>', $userId)->count();
        if($count>0) {
            $rdata['status'] = 0;
            $rdata['info'] = '该手机号已经存在，不能修改！';
            $this->backJson($rdata);
        }

        $user->phone = $phone;
        if($user->save()) {
            Flash::success('修改成功！');
            $rdata['status'] = 1;
            $rdata['info'] = '修改成功！';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '修改失败！';
            $this->backJson($rdata);
        }
    }

    /**
     * 删除账户[仅限于删除未开通存管的账户]
     */
    public function deleteAccountAction() {
        $userId = $this->getPost('userId', '');
        $user = User::where('userId', $userId)->first();
        $rdata = [];
        if(!$user) {
            $rdata['status'] = 0;
            $rdata['info'] = '用户不存在！';
            $this->backJson($rdata);
        }
        if($user->custody_id!='') {
            $rdata['status'] = 0;
            $rdata['info'] = '用户已经开通存管，不能删除！';
            $this->backJson($rdata);
        }
        if($user->delete()) {
            Flash::success('删除成功！');
            $rdata['status'] = 1;
            $rdata['info'] = '删除成功！';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '删除失败！';
            $this->backJson($rdata);
        }
    }

	/**
	 * Q群管理
     */
	public function qqGroupAction()
	{
		$this->submenu = 'qqGroup';
		$queries = $this->queries->defaults(['qqGroup'=>'2','sortBy'=>'addtime' ,'sortType'=>'asc']);
        $builder = User::with(['oddMoney'=>function($query) {$query->orderBy('time','desc')->select(['userId','time']);}]);
        if($queries->qqGroup!=''){
            $builder->where('qqGroup',$queries->qqGroup);
        }
        $users = $builder->orderBy($queries->sortBy,$queries->sortType)->paginate();
        $userIds = [];
        foreach ($users as $key => $value) {
            $userIds[] = $value->userId;
        }
        $stayMoney = Invest::select(DB::raw('sum(benJIn) stay, userId'))
            ->where('status','0')->whereIn('userId', $userIds)
            ->groupBy('userId')
            ->get();

        $stayList = [];
        foreach ($stayMoney as $key => $value) {
            $stayList[$value->userId] = $value->stay;
        }
        $users->appends($queries->all());
		$this->display('qqGroup',['list'=>$users, 'queries'=>$queries ,'stayMoney' =>$stayList]);

	}

    /**
     * Q群变更
     */
    public function qqGroupChangeAction()
    {
        $id = $this->getPost('id', 0);
        $group = $this->getPost('qqGroup', '');
        $update = DB::table('system_userinfo')->where('id',$id)->update(['qqGroup'=>$group]);
        if($update) {
            Flash::success('操作成功!');
            $rdata['status'] = 1;
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = API::$msg;
            $this->backJson($rdata);
        }
    }
}
