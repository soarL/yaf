<?php
namespace models;

use Yaf\Registry;
use Illuminate\Database\Eloquent\Model;
use traits\AuthorizeUser;
use helpers\DateHelper;
use helpers\StringHelper;
use models\UserSpreadCode;
use models\UserBank;
// use plugins\bbs\BBSUnion;
use plugins\ancun\ACTool;
use tools\DuoZhuan;
use tools\API;
use tools\Redis;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * User|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class User extends Model {
	use AuthorizeUser;

	/**
	 * 担保账户用户名
	 */
	const ACCT_DB = 'ACCOUNT_NO_001';

	/**
	 * 红包账户用户名
	 */
	const ACCT_RP = 'ACCOUNT_NO_002';
	
	/**
	 * 手续费账户用户名
	 */
	const ACCT_FEE = 'ACCOUNT_NO_003';

	/**
	 * 有效用户状态
	 */
	const STATUS_ACTIVE = 1;

	/**
	 * 锁定用户状态

	 */
	const STATUS_LOCK = 0;

	/**
	 * 无效用户状态
	 */
	const STATUS_FAIL = -1;

	/**
	 * 是否COOKIE登录
	 */
	const IS_COOKIE_IDENTITY = true;

	/**
	 * 用户信息COOKIE
	 */
	const COOKIE_IDENTITY = '_identity';

	/**
	 * 用户信息COOKIE时长(秒)
	 */
	const COOKIE_IDENTITY_TIME = 172800;

	protected $table = 'system_userinfo';

	protected $primaryKey = 'userId';

	public $timestamps = false;

	public $incrementing = false;

	public function invests(){
		return $this->hasMany('models\Invest', 'userId', 'userId');
	}

	public function UserDuein(){
		return $this->hasMany('models\UserDuein', 'userId', 'userId');
	}

	public function tuijians() {
		return $this->hasMany('models\User', 'tuijian','phone');
	}

	public function withdraw() {
		return $this->hasMany('models\Withdraw', 'userId');
	}

	public function recharge() {
		return $this->hasMany('models\Recharge', 'userId');
	}

	public function parent() {
		return $this->belongsTo('models\User', 'tuijian','phone');
	}

	public function hcparters() {
		return $this->hasMany('models\User', 'tuijian','phone')->where('hcparter','1');
	}

	public function debts() {
		return $this->hasMany('models\OddMoney', 'userId');
	}

	public function lotterys() {
		return $this->hasMany('models\Lottery', 'userId')->where('status',1);
	}

	public function company() {
		return $this->hasOne('models\UserOffice', 'userId');
	}
	
	public function estimate() {
		return $this->hasOne('models\UserEstimate', 'userId')->where('status',1);
	}

	public function userbank() {
		return $this->hasOne('models\UserBank', 'userId')->where('status',1);
	}
	/**
	 * 获取用户推广码
	 * @return string         推广码
	 */
	public function getSpreadCode() {
		return UserSpreadCode::getSpreadCode($this->userId,1);
	}

	/**
	 * 客服
	 * @return [type] [description]
	 */
	public function waiter() {
		return $this->belongsTo('models\User', 'service');
	}

	/**
	 * 用户普通登录
	 * @param  string $username 用户名/手机号
	 * @param  string $password 密码
	 * @param  boolean $isRemember 是否记住登录
	 * @return boolean          是否登录成功
	 */
	public static function loginNormal($username, $password, $isRemember=false) {
		$user = self::where('username', $username)->first();
		if(!$user) {
			$user = self::where('phone', $username)->first();
		}
		if(!$user) {
			return ['status'=>0, 'info'=>'用户不存在！'];
		}
		if($user->status!=self::STATUS_ACTIVE) {
			$info = '';
			if($user->status==self::STATUS_LOCK) {
				$lockToStamp = strtotime($user->lockTo);
				if($lockToStamp>=time()) {
					$remindTime = $lockToStamp - time();
					$info = '您的帐号已经被锁定,剩余锁定时间'.DateHelper::getDaySpecial($remindTime).'。';
					return ['status'=>0, 'info'=>$info];
				}
			} else if($user->status==self::STATUS_FAIL) {
				$info = '您的帐号已经失效！';
				return ['status'=>0, 'info'=>$info];
			}
		}

		$password_secret = $user->loginpass;
		if($user->password($password)!=$password_secret) {

			$loginErrTime = $user->loginErrTime;
			$lockTo = null;
			$info = '';
			$accountStatus = 1;
			if($loginErrTime<4) {
				$info = '密码错误，再错'.(5-($loginErrTime+1)).'次账户将被锁定！';
				$accountStatus = 1;
			} else if($loginErrTime>=4) {
				$lockTo = date('Y-m-d H:i:s',(time() + 10*60));
				$info = '密码错误，您的账户将被锁定10分钟！';
				$accountStatus = 0;
			}
			$loginErrTime++;
			$user->loginErrTime = $loginErrTime;
			$user->lockTo = $lockTo;
			$user->status = $accountStatus;
			$user->save();

			return ['status'=>0, 'info'=>$info];
		}

		self::doLogin($user);

		if(self::IS_COOKIE_IDENTITY&&$isRemember) {
			$siteinfo = Registry::get('siteinfo');
			$authString = md5($user->friendkey.$user->loginpass.$siteinfo['clientIp']);
			$cookieData = ['auth_string'=>$authString, 'username'=>$user->username, 'ip'=>$siteinfo['clientIp']];
			$_identity = StringHelper::encrypt(json_encode($cookieData), 'E');
			setcookie(self::COOKIE_IDENTITY, $_identity, time()+self::COOKIE_IDENTITY_TIME, '/', WEB_DOMAIN);
		}

		// 更新会话id
		session_regenerate_id();
		self::afterLogin($user, $isRemember);

		return ['status'=>1, 'info'=>'登录成功！'];
	}

	/**
	 * 用户交易密码验证
	 * @param  string $username 用户名/手机号
	 * @param  string $password 密码
	 * @return boolean          是否登录成功
	 */
	public static function paypassNormal($user, $password) {
		if($user->status!=self::STATUS_ACTIVE) {
			$info = '';
			if($user->status==self::STATUS_LOCK) {
				$lockToStamp = strtotime($user->lockTo);
				if($lockToStamp>=time()) {
					$remindTime = $lockToStamp - time();
					$info = '您的帐号已经被锁定,剩余锁定时间'.DateHelper::getDaySpecial($remindTime).'。';
					return ['status'=>0, 'info'=>$info];
				}
			} else if($user->status==self::STATUS_FAIL) {
				$info = '您的帐号已经失效！';
				return ['status'=>0, 'info'=>$info];
			}
		}

		$password_secret = $user->paypass;
		if($user->password($password)!=$password_secret) {

			$loginErrTime = $user->loginErrTime;
			$lockTo = null;
			$info = '';
			$accountStatus = 1;
			if($loginErrTime<4) {
				$info = '密码错误，再错'.(5-($loginErrTime+1)).'次账户将被锁定！';
				$accountStatus = 1;
			} else if($loginErrTime>=4) {
				$lockTo = date('Y-m-d H:i:s',(time() + 10*60));
				$info = '密码错误，您的账户将被锁定10分钟！';
				$accountStatus = 0;
			}
			$loginErrTime++;
			$user->loginErrTime = $loginErrTime;
			$user->lockTo = $lockTo;
			$user->status = $accountStatus;
			$user->save();

			return ['status'=>0, 'info'=>$info];
		}
		return ['status'=>1, 'info'=>'验证成功！'];
	}

	/**
	 * 使用cookie登录
	 * @return boolean
	 */
	public static function loginByCookie() {
		if(!self::isLogin()&&isset($_COOKIE[self::COOKIE_IDENTITY])) {
			$_identity = $_COOKIE[self::COOKIE_IDENTITY];
			$data = StringHelper::encrypt($_identity, 'D');
			$data = json_decode($data, true);

			$username = $data['username'];
			$user = self::where('username', $username)->first();
			if(!$user) {
				setcookie(self::COOKIE_IDENTITY, null, time()-1,  '/', WEB_DOMAIN);
				return false;
			}

			if($user->status!=self::STATUS_ACTIVE) {
				setcookie(self::COOKIE_IDENTITY, null, time()-1,  '/', WEB_DOMAIN);
				return false;
			}

			$siteinfo = Registry::get('siteinfo');
			$authString = md5($user->friendkey.$user->loginpass.$siteinfo['clientIp']);
			if($data['auth_string']!==$authString) {
				setcookie(self::COOKIE_IDENTITY, null, time()-1,  '/', WEB_DOMAIN);
				return false;
			}

			//单点登录
			$ssid = Redis::get('ssid:'.$user->userId);
        	if(!isset($_COOKIE['PHPSESSID']) || $_COOKIE['PHPSESSID'] != $ssid){
        		setcookie(self::COOKIE_IDENTITY, null, time()-1,  '/', WEB_DOMAIN);
				return false;
        	}

			self::doLogin($user);

			// 延长cookie过期时间
			setcookie(self::COOKIE_IDENTITY, $_COOKIE[self::COOKIE_IDENTITY], time()+self::COOKIE_IDENTITY_TIME, '/', WEB_DOMAIN);

			// 更新会话id
			session_regenerate_id();
        	Redis::set('ssid:'.$user->userId,session_id());

			self::afterLogin($user);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 用户APP普通登录
	 * @param  string $username 用户名/手机号
	 * @param  string $password 密码
	 * @return boolean          是否登录成功
	 */
	public static function loginApp($username, $password) {
		$user = self::where('username', $username)->first();
		if(!$user) {
			$user = self::where('phone', $username)->first();
		}
		if(!$user) {
			return ['status'=>0, 'info'=>'用户不存在！'];
		}

		if($user->status!=self::STATUS_ACTIVE) {
			$info = '';
			if($user->status==self::STATUS_LOCK) {
				$lockToStamp = strtotime($user->lockTo);
				if($lockToStamp>=time()) {
					$remindTime = $lockToStamp - time();
					$info = '您的帐号已经被锁定,剩余锁定时间'.DateHelper::getDaySpecial($remindTime).'。';
					return ['status'=>0, 'info'=>$info];
				}
			} else if($user->status==self::STATUS_FAIL) {
				$info = '您的帐号已经失效！';
				return ['status'=>0, 'info'=>$info];
			}
		}

		$password_secret = $user->loginpass;
		
		if($user->password($password)!=$password_secret ) {

			$loginErrTime = $user->loginErrTime;
			$lockTo = null;
			$info = '';
			$accountStatus = 1;
			if($loginErrTime<4) {
				$info = '密码错误，再错'.(5-($loginErrTime+1)).'次账户将被锁定！';
				$accountStatus = 1;
			} else if($loginErrTime>=4) {
				$lockTo = date('Y-m-d H:i:s',(time() + 10*60));
				$info = '密码错误，您的账户将被锁定10分钟！';
				$accountStatus = 0;
			}
			$loginErrTime++;
			$user->loginErrTime = $loginErrTime;
			$user->lockTo = $lockTo;
			$user->status = $accountStatus;
			$user->save();

			return ['status'=>0, 'info'=>$info];
		}

		Registry::set('user', $user);
		
		self::afterLogin($user, 'app');

		return ['status'=>1, 'info'=>'登录成功！'];
	}

	/**
	 * 登录某个用户
	 * @param  Model $user 用户
	 * @return mixed
	 */
	public static function doLogin($user) {
		$userSession = [];
		$userSession['id'] = $user->id;
		$userSession['username'] = $user->username;
		$userSession['userId'] = $user->userId;
		$userSession['userimg'] = $user->userimg;
		$session = Registry::get('session');
		$session->set('user',$userSession);
		$session->set('userID', $user->userId);
		Registry::set('user', $user);
	}

	/**
	 * 用户登录后执行
	 * @param  Model $user 用户
	 * @return mixed
	 */
	public static function afterLogin($user, $isRemember = false) {
		$user->loginErrTime = 0;
		$user->lockTo = null;
		$user->status = 1;
		$user->update();
		LoginLog::log($user->userId);
		Mail::receiveByUsername($user->username);

		//单点登录
		$ssid = Redis::get('ssid:'.$user->userId);
    	if(session_id() != $ssid && $ssid){
    		\Flash::success('当前账号已在线，前一会话将被强行退出!！');
    	}

		//登录ssid
		$isRemember = $isRemember? 1 : 0;
    	$second = 3600 + ($isRemember * 7 * 24 * 3600);
		Redis::setex('ssid:'.$user->userId, $second, session_id());

		//if($media=='pc') {
			/** BBS union login begin **/
	        /*$user = Registry::get('user');
	        $email = $user->email;
	        if(!$email) {
	        	$email = 'random'.$user->userId.'@hcjrfw.com';
	        }
	        $unionResult = BBSUnion::login($user->username, substr($user->loginpass, 8, 16), $email);
	        if($unionResult['status']==200) {
	            $script = $unionResult['script'];
	            Registry::get('session')->set('bbsUnion', $script);
	        }*/
	        /** BBS union login end **/
        //}
	}

	/**
	 * 查看用户是否登录
	 * @return boolean 是否登录
	 */
	public static function isLogin() {
		$session = Registry::get('session');
		return $session->has('user');
	}

	/**
	 * 用户退出
	 * @return mixed
	 */
	public static function logout() {
		$session = Registry::get('session');
		Redis::set('ssid:'.$session->user['userId'],'');
		$session->del('user');
		$session->del('userID');
		if(self::IS_COOKIE_IDENTITY) {
			setcookie(self::COOKIE_IDENTITY, null, time()-1,  '/', WEB_DOMAIN);
		}
		
		/** BBS union logout begin **/
        /*$script = BBSUnion::logout();
        $session->set('bbsUnion', $script);*/
        /** BBS union logout end **/
	}

	/**
	 * 添加一个用户
	 * @param array $data 用户数组
	 */
	public static function addOne($data) {
		$userData = $data;
		$spreadUser = false;
		if(isset($userData['spreadUser'])&&$userData['spreadUser']!='') {
			$spreadUser = self::where('username', $userData['spreadUser'])->first();
		}
		unset($userData['spreadUser']);
		if($spreadUser) {
			$userData['tuijian'] = $spreadUser->username;
		}
		$secret = _secret(5);
		$userData['friendkey'] = $secret;
		$userData['loginpass'] = _password($userData['loginpass'],$secret);
		if(isset($userData['paypass'])) {
			$userData['paypass'] = _password($userData['paypass'],$secret);
		}

		/** 运营推广标识 **/
		if(isset($userData['pm_key'])) {
			if($userData['pm_key']!='') {
				$count = Promotion::where('channelCode', $userData['pm_key'])->count();
				if($count) {
					$userData['channelCode'] = $userData['pm_key'];
				}
			}
			unset($userData['pm_key']);
		}
		
		$user = new self();
		$user->incrementing = true;
		$user->setKeyName('id');
		foreach ($userData as $key => $value) {
			$user->$key = $value;
		}
		$user->userId = self::generateUserId();
		$status = $user->save();
		if($status) {
			if($spreadUser) {
				UserFriend::addOne($spreadUser->userId, $user->userId);
			}

			Redis::setUser([
				'userId'=>$user->userId,
				'phone'=>$user->phone,
			]);
			
			if(time()>=strtotime('2017-06-12 00:00:00')) {
	            $params = [];
                $params['type'] = 'withdraw';
                $params['useful_day'] = 180;
                $params['remark'] = '[活动]新手奖励';
                $params['userId'] = $user->userId;
                $status = Lottery::generate($params);

	            if(time()>=strtotime('2017-07-10 00:00:00')) {
		            $msg = '恭喜您注册汇诚普惠账号成功！您已收到380元红包券，有效期限1-2个月，请马上登录汇诚普惠使用！专注车贷，预期收益15~19%';
		            Sms::dxOne($msg, $user->phone);
		            $redpacks = [
					    ['money_rate'=>18, 'period'=>30, 'money_lower'=>2000],
					    ['money_rate'=>28, 'period'=>30, 'money_lower'=>8000],
					    ['money_rate'=>58, 'period'=>30, 'money_lower'=>10000, 'period_lower'=>6],
					    ['money_rate'=>138, 'period'=>30, 'money_lower'=>50000, 'period_lower'=>3],
					    ['money_rate'=>238, 'period'=>30, 'money_lower'=>50000, 'period_lower'=>6],
					    ['money_rate'=>408, 'period'=>30, 'money_lower'=>80000, 'period_lower'=>12],
					    // ['money_rate'=>20, 'period'=>60, 'money_lower'=>20000, 'period_lower'=>6, 'period_uper'=>24],
					    // ['money_rate'=>20, 'period'=>60, 'money_lower'=>20000, 'period_lower'=>6, 'period_uper'=>24],
					    // ['money_rate'=>50, 'period'=>60, 'money_lower'=>50000],
					    // ['money_rate'=>50, 'period'=>60, 'money_lower'=>50000],
					    // ['money_rate'=>50, 'period'=>60, 'money_lower'=>50000, 'period_lower'=>6, 'period_uper'=>24],
					    // ['money_rate'=>100, 'period'=>60, 'money_lower'=>100000, 'period_lower'=>6, 'period_uper'=>24],
					];

					$list = [];
					foreach ($redpacks as $item) {
					    $params = [];
					    $params['type'] = 'money';
					    $params['useful_day'] = $item['period'];
					    $params['remark'] = '[活动]红包奖励';
					    $params['userId'] = $user->userId;
					    $params['money_rate'] = $item['money_rate'];
					    $params['money_lower'] = $item['money_lower'];
					    if(isset($item['period_uper'])) {
					        $params['period_uper'] = $item['period_uper'];
					    }
					    if(isset($item['period_lower'])) {
					        $params['period_lower'] = $item['period_lower'];
					    }
					    $list[] = $params;
					}
					$status = Lottery::generateBatch($list);
				}
            }
			return $user;
		}
		return null;
	}

	/**
	 * 添加一个用户
	 * @param array $data 用户数组
	 */
	public static function addExcel($data) {
		$userData = $data;
		$spreadUser = false;
		if(isset($userData['spreadUser'])&&$userData['spreadUser']!='') {
			$spreadUser = self::where('username', $userData['spreadUser'])->first();
		}
		unset($userData['spreadUser']);
		if($spreadUser) {
			$userData['tuijian'] = $spreadUser->username;
		}
		//$secret = _secret(5);
		//$userData['friendkey'] = $secret;
		$userData['loginpass'] = $userData['loginpass'];
		
		$user = new self();
		$user->incrementing = true;
		$user->setKeyName('id');
		foreach ($userData as $key => $value) {
			$user->$key = $value;
		}
		$user->userId = self::generateUserId();
		if($user->custody_id){
			$user->custody_id = $user->userId;
			$user->is_custody_pwd = 1;
		}
		$status = $user->save();
		if($status) {
			if($spreadUser) {
				UserFriend::addOne($spreadUser->userId, $user->userId);
			}

			Redis::setUser([
				'userId'=>$user->userId,
				'phone'=>$user->phone,
			]);

			return $user;
		}
		return null;
	}

	/**
	 * 生成userId
	 * @param  int  $id   用户id
	 * @return string     用户userId
	 */
	public static function generateUserId() {
		$key = Redis::getKey('userMaxNum');
		$num = Redis::incr($key);
		$offset = 2000000000;
		$userId = $offset + intval($num);
		return ''.$userId;
	}

	/**
	 * 用户名是否存在
	 * @param  string   $username 用户名
	 * @return boolean            是否存在
	 */
	public static function isUsernameExist($username) {
		$count = self::where('username', $username)->count();
		if($count>0) {
			return true;
		}
		return false;
	}

	/**
	 * 手机号是否存在
	 * @param  string  $phone 手机号
	 * @return boolean        是否存在
	 */
	public static function isPhoneExist($phone) {
		$user = self::where('phone', $phone)->first();
		if($user) {
			return $user;
		}
		return false;
	}

	/**
	 * 身份证号是否存在
	 * @param  string  $cardnum 身份证号
	 * @param  string  $userId  该用户会被忽略
	 * @return boolean          是否存在
	 */
	public static function isIDCardExist($cardnum, $userId='') {
		$builder = self::where('cardnum', $cardnum);
		if($userId) {
			$builder->where('userId', '<>', $userId);
		}
		$count = $builder->count();
		if($count>0) {
			return true;
		}
		return false;
	}

	/**
	 * 邮箱是否存在
	 * @param  string  $email 邮箱
	 * @return boolean        是否存在
	 */
	public static function isEmailExist($email) {
		$count = self::where('email', $email)->count();
		if($count>0) {
			return true;
		}
		return false;
	}

	/**
	 * 加密密码
	 * @param  string $password 密码
	 * @return string           加密字符串
	 */
	public function password($password) {
		return md5($password.$this->friendkey);
	}

	public function getPName(){
		$sexName = $this->sex=='man'?'先生':'女士';
		return _substr($this->name, 0, 1) . $sexName;
	}

	/**
	 * 通过生日获取年龄
	 * @return int              年龄
	 */
	public function getAge() {
		$timestamp = strtotime($this->birth);
		$year = intval(date('Y', $timestamp));
		$month = intval(date('m', $timestamp));
		$day = intval(date('d', $timestamp));
		$nowYear = intval(date('Y'));
		$nowMonth = intval(date('m'));
		$nowDay = intval(date('d'));
		$age = $nowYear - $year;
		if($month>$nowMonth) {
			$age = $age - 1;
		} else {
			if($month==$nowMonth) {
				if($day>$nowDay) {
					$age = $age - 1;
				}
			}
		}
		return $age;
	}

	/**
	 * 提现后更新用户信息
	 * @param  double  $money   提现金额
	 * @return boolean 是否更新成功
	 */
	public function updateAfterWithdrawF($money) {
		if($this->investMoney > $money){
			return self::where('userId', $this->userId)->where('withdrawMoney', '>=', $money)->update(['frozenMoney'=>DB::raw('frozenMoney+'.$money),'fundMoney'=>DB::raw('fundMoney-'.$money), 'withdrawMoney'=>DB::raw('withdrawMoney-'.$money), 'investMoney'=>DB::raw('investMoney-'.$money)]);
		}else{
			return self::where('userId', $this->userId)->where('withdrawMoney', '>=', $money)->update(['frozenMoney'=>DB::raw('frozenMoney+'.$money),'fundMoney'=>DB::raw('fundMoney-'.$money), 'withdrawMoney'=>DB::raw('withdrawMoney-'.$money),'investMoney'=>0]);
		}
	}

	/**
	 * 提现后更新用户信息
	 * @param  double  $money   提现金额
	 * @return boolean 是否更新成功
	 */
	public function updateAfterWithdraw($money) {
		return self::where('userId', $this->userId)->update(['frozenMoney'=>DB::raw('frozenMoney-'.$money)]);
	}

	/**
	 * 提现后更新用户信息
	 * @param  double  $money   提现金额
	 * @return boolean 是否更新成功
	 */
	public function updateAfterWithdrawE($money,$investMoney = 0) {
		return self::where('userId', $this->userId)->update(['frozenMoney'=>DB::raw('frozenMoney-'.$money),'fundMoney'=>DB::raw('fundMoney+'.$money), 'withdrawMoney'=>DB::raw('withdrawMoney+'.$money), 'investMoney'=>DB::raw('investMoney+'.$investMoney)]);
	}

	/**
	 * 获取用户提现手续费
	 * @param  double  $money   提现金额
	 * @param  boolean  $lottery   是否使用提现券(免除2元手续费)
	 * @return double           手续费
	 */
	public function getWithdrawFee($money, $lottery=false) {
		if($this->privilege==1) {
			return 0;
		}

		$fee = 0;
        if($money>$this->investMoney) {
            $fee = $fee + ($money-$this->investMoney) * Withdraw::FEE_PER;
            $fee = round($fee ,2);
            $this->useInvestMoney = $this->investMoney;
        }else{
        	$this->useInvestMoney = $money;
        }

        if($this->userType == 2 || $this->userType == 3){
			return $fee;
		}

        if($lottery) {
            return $fee;
        }
		return $fee + Withdraw::FEE_BASE;
	}

	/**
	 * 检查用户支付密码
	 * @param  string $password  支付密码
	 * @return boolean           是否正确
	 */
	public function checkPaypass($password) {
		if($this->password($password) != $this->paypass) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * 检查用户登录密码
	 * @param  string $password  支付密码
	 * @return boolean           是否正确
	 */
	public function checkLoginpass($password) {
		if($this->password($password) != $this->loginpass) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * 是否管理员
	 * @return boolean           是否管理员
	 */
	public function isAdmin() {
		return count($this->roles)>0?true:false;
	}

	/**
	 * 获取用户投资积分和等级
	 * @return array               	 积分[integration]与等级[grade]以及收取的投资管理费比例[feePer]
	 */
	public function getTenderGrade() {
		$integration = $this->integral/100;
		$grade = 1;
		$feePer = 0.08;
		if($integration<=300) {
			$grade = 1;
			$feePer = 0.08;
		} else if($integration>300&&$integration<=1500) {
			$grade = 2;
			$feePer = 0.07;
		} else if($integration>1500&&$integration<=3000) {
			$grade = 3;
			$feePer = 0.06;
		} else if($integration>3000&&$integration<=7500) {
			$grade = 4;
			$feePer = 0.05;
		} else if($integration>7500&&$integration<=15000) {
			$grade = 5;
			$feePer = 0.04;
		} else if($integration>15000&&$integration<=24000) {
			$grade = 6;
			$feePer = 0.03;
		} else if($integration>24000&&$integration<=35000) {
			$grade = 7;
			$feePer = 0.02;
		} else if($integration>35000&&$integration<=60000) {
			$grade = 8;
			$feePer = 0.01;
		} else if($integration>60000) {
			$grade = 9;
			$feePer = 0;
		}
		return ['integration'=>$integration, 'grade'=>$grade, 'feePer'=>$feePer];
	}
	
	/**
	 * 预处理用户信息（用于用户资料展示）
	 * @return array       用户信息
	 */
	public function prepareInfo() {
		$info = [];
		$word = '未填写';
		if($this->name==null||$this->name=='') {
			$info['name'] = $word;
		} else {
			$info['name'] = StringHelper::getHideName($this->name);
		}

		if($this->birth=='') {
			$info['age'] = $word;
		} else {
			$age = StringHelper::getAgeByBirthday($this->birth);
			$info['age'] = $age;
		}
		if($this->cardnum==null||$this->cardnum=='') {
			$info['cardnum'] = $word;
		} else {
			$info['cardnum'] = StringHelper::getHideCardnum($this->cardnum);
		}

		if($this->sex=='man') {
			$info['sex'] = '男';
		} else {
			$info['sex'] = '女';
		}
		$info['ethnic'] = $this->ethnic==''?$word:$this->ethnic;
		if($this->maritalstatus=='y') {
			$info['maritalstatus'] = '已婚';
		} else {
			$info['maritalstatus'] = '未婚';
		}
		$info['city'] = $this->city==''?$word:$this->city;
		$info['adder'] = $this->adder==''?$word:$this->adder;
		$info['educational'] = $this->educational==''?$word:$this->educational;
		$info['income'] = $this->income==''?$word:$this->income;
		return $info;
	}

	/**
	 * 绑定第三方账户后执行
	 * @return mixed
	 */
	public function afterBindThird() {
		if(time()<strtotime('2015-08-25 00:00:00')) {
			$packStatus = API::redPack(['userId'=>$this->userId]);
			// Log::write('送50元红包，结果:'.$packStatus, 'redpack');
		}
		$this->thirdAccountStatus = '1';
		$this->bindThirdTime = date('Y-m-d H:i:s');
		$this->save();
		
		$acTool = new ACTool($this, 'user');
		$acTool->send();
		$acTool = new ACTool($this, 'user', 1);
    	$acTool->send();
	}

	public function getPhoto() {
		return $this->userimg==''?WEB_ASSET.'/common/images/portrait.jpg':WEB_ASSET.'/uploads/images/'.$this->userimg;
	}

	/**
	 * 待收
	 */
	public function getStayMoney() {
		$money = 0;
		foreach ($this->invests as $invest) {
			if($invest->status==0) {
				$money+=$invest->zongEr;
			}
		}
		return $money;	
	}
	
	/**
	 * 获取第一个注册的用户
	 */
	public static function getFirst() {
		return self::whereNotNull('addTime')->orderBy('addTime', 'asc')->first();
	}

	public function getSex() {
		if($this->sex=='man') {
			return '男';
		} else if($this->sex=='women') {
			return '女';
		} else {
			return '未知';
		}
	}

	public static function getCID($userId) {
		return $userId;
	}

	public function frozen($money, $params=[]) {
		$count = User::where('userId', $this->userId)->update([
            'fundMoney'=>DB::raw('fundMoney-'.$money),
            'frozenMoney'=>DB::raw('frozenMoney+'.$money)
        ]);
        if($count) {
	        $type = isset($params['type'])?$params['type']:'';
	        $remark = isset($params['remark'])?$params['remark']:'';
	        $time = isset($params['time'])?$params['time']:date('Y-m-d H:i:s');
	        $moneyLog = [
	            'type' => $type,
	            'mode' => 'freeze',
	            'mvalue' => $money,
	            'userId' => $this->userId,
	            'remark' => $remark,
	            'remain' => $this->fundMoney - $money,
	            'frozen' => $this->frozenMoney + $money,
	            'time' => $time,
	        ];
	        MoneyLog::insert($moneyLog);
	        return true;
        } else {
        	return false;
        }
	}

	public function getStayCapital() {
		$capital = 0;
		foreach ($this->debts as $debt) {
			$capital += $debt->remain;
		}
		return $capital;
	}

    public function getEstimateResult() {
    	$score = $this->estimateScore;
        $result = "";
        if ($score <= 20) {
            $result = "保守型";
        }
        else if ($score <= 40) {
            $result = "谨慎型";
        }
        else if ($score <= 60)
        {
            $result = "稳健型";
        }
        else if ($score <= 80)
        {
            $result = "进取型";
        }
        else
        {
            $result = "激进型";
        }

        return $result;
    }

    public function getEstimateLevel() {
    	$score = $this->estimateScore;
        $result = "";
        if ($score <= 20) {
            $result = "1";
        }
        else if ($score <= 40) {
            $result = "2";
        }
        else if ($score <= 60)
        {
            $result = "3";
        }
        else if ($score <= 80)
        {
            $result = "4";
        }
        else
        {
            $result = "5";
        }

        return $result;
    }

    public function getEstimateDescription() {
    	$score = $this->estimateScore;
        $result = "";
        if ($score <= 20) {
            $result = "风险承受能力极低，对收益要求不高，但追求资本金绝对安全。";
        }
        else if ($score <= 40) {
            $result = "风险承受度较低，能容忍一定幅度的本金损失，止损意识强。";
        }
        else if ($score <= 60)
        {
            $result = "风险承受度适中，偏向于资产均衡配置，能够承受一定的投资风险，同时，对资产收益要求高于保守型、谨慎型投资者。";
        }
        else if ($score <= 80)
        {
            $result = "偏向于激进的资产配置，对风险有较高的承受能力，投资收益预期相对较高，可以接受短期负面波动，愿意承担全部收益包括本金可能损失的风险。";
        }
        else
        {
            $result = "对风险有非常高的承受能力，资产配置以高风险投资品种为主，投机性强，利用市场波动赢取差价，追求在较短周期内的收益最大化。";
        }

        return $result;
    }



}
