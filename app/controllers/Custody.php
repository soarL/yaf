<?php
use tools\Log;
use tools\Redis;
use tools\BankCard;
use custody\Handler;
use models\User;
use models\Odd;
use models\UserCrtr;
use models\Recharge;
use models\Withdraw;
use models\UserBid;
use models\CustodyBatch;
use models\UserBank;
use models\BailRepay;
use models\RedpackBatch;
use tools\Counter;
use models\DegWithdraw;
use helpers\StringHelper;
use task\Task;
use custody\API;
use plugins\ancun\ACTool;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * 存管回调处理
 * @author elf <360197197@qq.com>
 */
class CustodyController extends Controller {
	public $menu = 'custody';

	/**
	 * 发标异步回调
	 */
	public function publishNotifyAction() {
		$params = $this->getAllPost(false);
		Log::write('[OPEN_ACCOUNT]发标异步回调', $params, 'custody');
		$data = json_decode($params['bgData'], true);
		if(!Handler::verify($data)) {
			Log::write('[OPEN_ACCOUNT]验签失败', [], 'custody');
			Handler::back();
		}

		$return = [];
		if($data['retCode']==Handler::SUCCESS) {
			$item = StringHelper::decodeQueryString($data['acqRes']);
            $count = Odd::where('oddNumber', $item['oddNumber'])->update(['progress'=>'published', 'publishTime'=>date('Y-m-d H:i:s'),'receiptStatus'=>1]);
		}
		Handler::back();
	}

	/**
	 * 代扣异步
	 */
	public function deductNotifyAction() {
		$params = $this->getAllPost(false);
		Log::write('[OPEN_ACCOUNT]代扣异步回调', $params, 'custody');
		$data = json_decode($params['bgData'], true);
		if(!Handler::verify($data)) {
			Log::write('[OPEN_ACCOUNT]验签失败', [], 'custody');
			Handler::back();
		}
		$return = [];
		if($data['retCode']==Handler::SUCCESS) {
			$userId = $data['userId'];
			$money = $data['money'];

			$tradeNo = Counter::getOrderID();
	        $recharge = new Recharge();
	        $recharge->serialNumber = $tradeNo;
	        $recharge->userId = $userId;
	        $recharge->money = $money;
	        $recharge->fee = 0;
	        $recharge->status = 0;
	        $recharge->time = date('Y-m-d H:i:s');
	        $recharge->payType = 'baofoo';
	        $recharge->remark = '代扣充值';
	        $recharge->payWay = 'deduct';
	        //$recharge->media = $this->getMedia();
	        $recharge->save();

            $ret['result'] = $data['retCode'];
            $ret['tradeNo'] = $tradeNo;
            $ret['status'] = 1;
            $ret['money'] = $money*100;
            $res = Recharge::after($ret);
		}
		Handler::back();
	}

	/**
	 * 开户异步回调
	 */
	public function openAccountNotifyAction() {
		$params = $this->getAllPost(false);
		Log::write('[OPEN_ACCOUNT]开户异步回调', $params, 'custody');
		$data = json_decode($params['bgData'], true);
		if(!Handler::verify($data)) {
			Log::write('[OPEN_ACCOUNT]验签失败', [], 'custody');
			Handler::back();
		}
		$return = [];
		if($data['retCode']==Handler::SUCCESS) {
			$item = StringHelper::decodeQueryString($data['acqRes']);
			$birth = StringHelper::getBirthdayByCardnum($item['cardnum']);
			$sex = StringHelper::getSexByCardnum($item['cardnum']);
			User::where('userId', $item['userId'])->update([
				'custody_id'=>$data['accountId'], 
				'cardnum'=>$item['cardnum'],
				'name'=>$item['name'],
				'sex'=>$sex, 
				'birth'=>$birth, 
				'cardstatus'=>'y',
				'certificationTime'=>date('Y-m-d H:i:s'),
				'bindThirdTime'=>date('Y-m-d H:i:s'),
                'is_custody_pwd'=>1
			]);
			
			Redis::updateUser([
				'userId'=>$item['userId'],
				'custody_id'=>$data['accountId'],
				'cardnum'=>$item['cardnum'],
				'name'=>$item['name'],
			]);

			$binInfo = BankCard::getBinInfo($item['bankNum']);
			UserBank::insert([
				'userId'=>$item['userId'], 
				'bankNum'=>$item['bankNum'], 
				'createAt'=>date('Y-m-d H:i:s'), 
				'updateAt'=>date('Y-m-d H:i:s'),
				'binInfo'=>$binInfo
			]);

			$user = User::where('userId', $item['userId'])->first();
			$acTool = new ACTool($user, 'user');
			$acTool->send();
			$acTool = new ACTool($user, 'user', 1);
	    	$acTool->send();
		}
		Handler::back();
	}

	/**
	 * 设置密码异步回调
	 */
	public function passwdNotifyAction() {
		$params = $this->getAllPost(false);
		Log::write('[PASSWD]设置密码异步回调', $params, 'custody');
		$data = json_decode($params['bgData'], true);

		if(!Handler::verify($data)) {
			Log::write('[PASSWD]验签失败', [], 'custody');
			Handler::back();
		}

		$return = [];
		if($data['retCode']==Handler::SUCCESS) {
			User::where('custody_id', $data['accountId'])->update(['is_custody_pwd'=>1]);
		}
		
		Handler::back();
	}

	/**
	 * 重置密码异步回调
	 */
	public function rePasswdNotifyAction() {
		$params = $this->getAllPost(false);
		Log::write('[REPASSWD]重置密码异步回调', $params, 'custody');
		$data = json_decode($params['bgData'], true);

		if(!Handler::verify($data)) {
			Log::write('[REPASSWD]验签失败', [], 'custody');
			Handler::back();
		}

		$return = [];
		// authCode
		if($data['retCode']==Handler::SUCCESS) {
			
		}

		Handler::back();
	}

	/**
	 * 修改手机号异步回调
	 */
	public function mobileModifyNotifyAction() {
		$params = $this->getAllPost(false);
		Log::write('[MOBILE_MODIFY]设置密码异步回调', $params, 'custody');
		$data = json_decode($params['bgData'], true);

		if(!Handler::verify($data)) {
			Log::write('[MOBILE_MODIFY]验签失败', [], 'custody');
			Handler::back();
		}

		$return = [];
		if($data['retCode']==Handler::SUCCESS) {
			User::where('custody_id', $data['accountId'])->update(['phone'=>$data['mobile']]);
		} else {
			
		}
		
		Handler::back();
	}

	/**
	 * 投标同步回调
	 */
	public function bidReturnAction() {
		$oddNumber = $this->getQuery('num');

		$params = $this->getAllPost(false);

		if(isset($params['retCode'])) {
			Log::write('[BID]投标同步回调', $params, 'custody');
			if($params['retCode']==Handler::SUCCESS) {
				Flash::success('投标申请成功！');
			} else {
				Flash::error($params['retMsg']);
			}
		} else {
			Flash::error('投标申请失败！');
		}

		$this->redirect('/odd/'.$oddNumber);
	}

	/**
	 * 投标异步回调
	 */
	public function bidNotifyAction() {
		$params = $this->getAllPost(false);
		Log::write('[BID]投标异步回调', $params, 'custody');
		$data = json_decode($params['bgData'], true);

		if(!API::verify($data)) {
			Log::write('[BID]验签失败', [], 'custody');
			Handler::back();
		}

		$return = [];
		if($data['retCode']==API::SUCCESS) {
			$return = ['status'=>1, 'tradeNo'=>$data['requestNo'], 'result'=>$data['retCode']];
		} else {
			$return = ['status'=>0, 'tradeNo'=>$data['requestNo'], 'result'=>$data['retCode']];
		}

		UserBid::after($return);

		Handler::back();
	}

	/**
	 * 充值异步回调
	 */
	public function rechargeNotifyAction() {
		$params = $this->getAllPost(false);
		Log::write('[RECHARGE]充值异步回调', $params, 'custody');
		$data = json_decode($params['bgData'], true);

		if(!Handler::verify($data)) {
			Log::write('[RECHARGE]验签失败', [], 'custody');
			Handler::back();
		}

		$return = [];
		$serialNumber = $data['requestNo'];
		$return['tradeNo'] = $serialNumber;
		$return['result'] = $data['retCode'];
		if($data['retCode']==Handler::SUCCESS) {
			$return['status'] = 1;
		} else {
			$return['status'] = 0;
		}

		Recharge::after($return);
		
		Handler::back();
	}


	/**
	 * 提现同步回调
	 */
	public function withdrawReturnAction() {
		$tradeNo = $this->getQuery('tradeNo');
		Log::write('[WITHDRAW]提现同步回调', [$tradeNo], 'custody');

		$data = Withdraw::where('status',0)->where('tradeNo',$tradeNo)->first();
		if($data){
			User::where('userId',$data['userId'])->update(['fundMoney'=> DB::RAW('fundMoney -'. $data['outMoney']), 'frozenMoney'=> DB::RAW('frozenMoney +'. $data['outMoney'])]);
			Withdraw::where('tradeNo',$tradeNo)->update(['status'=>3]);
			Flash::success('提现申请成功！');
		}

		$this->redirect(WEB_USER.'/account/withdraw');
	}

	/**
	 * 提现异步回调
	 */
	public function withdrawNotifyAction() {
		$params = $this->getAllPost(false);
		Log::write('[WITHDRAW]提现异步回调', $params, 'custody');
		$data = json_decode($params['bgData'], true);

		if(!Handler::verify($data)) {
			Log::write('[WITHDRAW]验签失败', [], 'custody');
			Handler::back();
		}

		$return = [];
		$tradeNo = $data['requestNo'];
		$return['tradeNo'] = $tradeNo;
		$return['result'] = $data['retCode'];
		// CE999028 大额提现已成功受理
		if($data['retCode']==Handler::SUCCESS || $data['retCode']=='CE999028' || in_array($data['retCode'], Withdraw::$unknowCodes)) {
			$return['status'] = 1;
		} else {
			$return['status'] = 0;
		}

		Withdraw::after($return);

		Handler::back();
	}

	/**
	 * 购买债权异步回调
	 */
	public function crtrNotifyAction() {
		$params = $this->getAllPost(false);
		Log::write('[CRTR]购买债权异步回调', $params, 'custody');
		$data = json_decode($params['bgData'], true);

		if(!Handler::verify($data)) {
			Log::write('[CRTR]验签失败', [], 'custody');
			Handler::back();
		}

		$return = [];
		if($data['retCode']==Handler::SUCCESS) {
			$return = ['status'=>1, 'tradeNo'=>$data['orderId'], 'result'=>$data['retCode'], 'authCode'=>''];
		} else {
			$return = ['status'=>0, 'tradeNo'=>$data['orderId'], 'result'=>$data['retCode'], 'authCode'=>''];
		}

		UserCrtr::after($return);

		Handler::back();
	}

	/**
	 * 自动投标签约异步回调
	 */
	public function autoBidAuthNotifyAction() {
		$params = $this->getAllPost(false);
		Log::write('[AUTH_BID_AUTH]自动投标签约异步回调', $params, 'custody');
		$data = json_decode($params['bgData'], true);

		if(!Handler::verify($data)) {
			Log::write('[AUTH_BID_AUTH]验签失败', [], 'custody');
			Handler::back();
		}

		$return = [];
		// authCode
		if($data['retCode']==Handler::SUCCESS) {
			User::where('custody_id', $data['accountId'])->update(['auto_bid_auth'=>$data['orderId']]);
		} else {
			
		}

		Handler::back();
	}

	/**
	 * 自动债转签约异步回调
	 */
	public function autoCreditAuthNotifyAction() {
		$params = $this->getAllPost(false);
		Log::write('[AUTH_CREDIT_AUTH]自动债转签约异步回调', $params, 'custody');
		$data = json_decode($params['bgData'], true);

		if(!Handler::verify($data)) {
			Log::write('[AUTH_CREDIT_AUTH]验签失败', [], 'custody');
			Handler::back();
		}

		$return = [];
		// authCode
		if($data['retCode']==Handler::SUCCESS) {
			User::where('custody_id', $data['accountId'])->update(['auto_credit_auth'=>$data['orderId']]);
		} else {
			
		}

		Handler::back();
	}

	/**
	 * 批次放款合法性检查异步回调
	 */
	public function batchLendPayAuthNotifyAction() {
		$params = $this->getAllPost(false);
		Log::write('[batchLendPayAuth]批次放款合法性检查异步回调', $params, 'custody');
		$data = json_decode($params['bgData'], true);

		if(!Handler::verify($data)) {
			Log::write('[batchLendPayAuth]验签失败', [], 'custody');
			Handler::back();
		}

		$status = 0;
		if($data['retCode'] != Handler::SUCCESS) {
			$status = -1;
		}

		$result = [
			'retCode' => $data['retCode'],
			'txAmount' => $data['txAmount'],
			'txCounts' => $data['txCounts'],
		];

		$count = CustodyBatch::where('batchNo', $data['acqRes'])->update([
			'checkTime'=>date('Y-m-d H:i:s'), 
			'checkResult'=>json_encode($result), 
			'status'=>$status
		]);

		Handler::back();
	}

	/**
	 * 批次放款异步回调
	 */
	public function batchLendPayNotifyAction() {
		$params = $this->getAllPost(false);
		Log::write('[batchLendPay]批次放款异步回调', $params, 'custody');
		$data = json_decode($params['bgData'], true);

		if(!Handler::verify($data)) {
			Log::write('[batchLendPay]验签失败', [], 'custody');
			Handler::back();
		}

		$batch = CustodyBatch::where('batchNo', $data['requestNo'])->where('status', 0)->first();
		if(!$batch) {
			Handler::back();
		}

		$status = 0;
		if($data['retCode']==Handler::SUCCESS) {
			$status = 1;
		} else {
			$status = -1;
		}
		$result = [
			'retCode' => $data['retCode'],
			// 'sucAmount' => $data['sucAmount'],
			// 'sucCounts' => $data['sucCounts'],
			// 'failAmount' => $data['failAmount'],
			// 'failCounts' => $data['failCounts'],
		];
		
		$count = CustodyBatch::where('batchNo', $batch->batchNo)->where('status', 0)->update([
			'returnTime'=>date('Y-m-d H:i:s'), 
			'returnResult'=>json_encode($result), 
			'status' => $status
		]);

		if($status==1) {
			Task::add('rehear', ['oddNumber'=>$batch->refNum, 'step'=>2]);
		}
		Handler::back();
	}

	/**
	 * 批次还款合法性检查异步回调
	 */
	public function batchRepayAuthNotifyAction() {
		$params = $this->getAllPost(false);
		Log::write('[batchRepayAuth]批次还款合法性检查异步回调', $params, 'custody');
		$data = json_decode($params['bgData'], true);

		if(!Handler::verify($data)) {
			Log::write('[batchRepayAuth]验签失败', [], 'custody');
			Handler::back();
		}

		$status = 0;
		if($data['retCode'] != Handler::SUCCESS) {
			$status = -1;
		}

		$result = [
			'retCode' => $data['retCode'],
			'txAmount' => $data['txAmount'],
			'txCounts' => $data['txCounts'],
		];

		$count = CustodyBatch::where('batchNo', $data['acqRes'])->where('status', 0)->update([
			'checkTime'=>date('Y-m-d H:i:s'), 
			'checkResult'=>json_encode($result), 
			'status'=>$status
		]);

		Handler::back();
	}

	/**
	 * 批次还款异步回调
	 */
	public function batchRepayNotifyAction() {
		$params = $this->getAllPost(false);
		Log::write('[batchRepay]批次还款异步回调', $params, 'custody');

		$data = json_decode($params['bgData'], true);

		if(!Handler::verify($data)) {
			Log::write('[batchRepay]验签失败', [], 'custody');
			Handler::back();
		}

		$batch = CustodyBatch::where('batchNo', $data['acqRes'])->where('status', 0)->first();
		if(!$batch) {
			Handler::back();
		}

		$batch->handle($data);

		Handler::back();
	}

	/**
	 * 批次代偿合法性检查异步回调
	 */
	public function batchBailRepayAuthNotifyAction() {
		$params = $this->getAllPost(false);
		Log::write('[batchBailRepayAuth]批次代偿合法性检查异步回调', $params, 'custody');
		$data = json_decode($params['bgData'], true);

		if(!Handler::verify($data)) {
			Log::write('[batchBailRepayAuth]验签失败', [], 'custody');
			Handler::back();
		}

		$status = 0;
		if($data['retCode'] != Handler::SUCCESS) {
			$status = -1;
		}

		$result = [
			'retCode' => $data['retCode'],
			'txAmount' => $data['txAmount'],
			'txCounts' => $data['txCounts'],
		];

		$count = CustodyBatch::where('batchNo', $data['acqRes'])->update([
			'checkTime'=>date('Y-m-d H:i:s'), 
			'checkResult'=>json_encode($result), 
			'status' => $status,
		]);

		Handler::back();
	}

	/**
	 * 批次代偿异步回调
	 */
	public function batchBailRepayNotifyAction() {
		$params = $this->getAllPost(false);
		Log::write('[batchBailRepay]批次代偿异步回调', $params, 'custody');
		$data = json_decode($params['bgData'], true);

		if(!Handler::verify($data)) {
			Log::write('[batchBailRepay]验签失败', [], 'custody');
			Handler::back();
		}

		$batch = CustodyBatch::where('batchNo', $data['acqRes'])->where('status', 0)->first();
		if(!$batch) {
			Handler::back();
		}
		
		$batch->handle($data);

		Handler::back();
	}

	/**
	 * 批次结束债权合法性检查异步回调
	 */
	public function batchCreditEndAuthNotifyAction() {
		$params = $this->getAllPost(false);
		Log::write('[batchCreditEndAuth]批次结束债权合法性检查异步回调', $params, 'custody');

		$data = json_decode($params['bgData'], true);

		if(!Handler::verify($data)) {
			Log::write('[batchCreditEndAuth]验签失败', [], 'custody');
			Handler::back();
		}

		$status = 0;
		if($data['retCode'] != Handler::SUCCESS) {
			$status = -1;
		}

		$result = [
			'retCode' => $data['retCode'],
			'txCounts' => $data['txCounts'],
		];
		$count = CustodyBatch::where('batchNo', $data['acqRes'])->where('status', 0)->update([
			'checkTime'=>date('Y-m-d H:i:s'), 
			'checkResult'=>json_encode($result), 
			'status' => $status,
		]);

		Handler::back();
	}

	/**
	 * 批次结束债权异步回调
	 */
	public function batchCreditEndNotifyAction() {
		$params = $this->getAllPost(false);
		Log::write('[batchCreditEnd]批次结束债权异步回调', $params, 'custody');
		$data = json_decode($params['bgData'], true);

		if(!Handler::verify($data)) {
			Log::write('[batchCreditEnd]验签失败', [], 'custody');
			Handler::back();
		}

		$status = 0;
		if($data['retCode']==Handler::SUCCESS) {
			$status = 1;
		} else {
			$status = -1;
		}

		$result = [
			'retCode' => $data['retCode'],
			'sucCounts' => $data['sucCounts'],
			'failCounts' => $data['failCounts'],
		];

		CustodyBatch::where('batchNo', $data['acqRes'])->update([
			'returnTime'=>date('Y-m-d H:i:s'), 
			'returnResult'=>json_encode($result), 
			'status'=>$status
		]);

		Handler::back();
	}

	/**
	 * 批次还担保账户垫款合法性检查异步回调
	 */
	public function batchRepayBailAuthNotifyAction() {
		$params = $this->getAllPost(false);
		Log::write('[batchRepayBailAuth]批次还担保账户垫款合法性检查异步回调', $params, 'custody');

		$data = json_decode($params['bgData'], true);

		if(!Handler::verify($data)) {
			Log::write('[batchRepayBailAuth]验签失败', [], 'custody');
			Handler::back();
		}

		$status = 0;
		if($data['retCode'] != Handler::SUCCESS) {
			$status = -1;
		}

		$result = [
			'retCode' => $data['retCode'],
			'txCounts' => $data['txCounts'],
			'txAmount' => $data['txAmount'],
		];

		$item = StringHelper::decodeQueryString($data['acqRes']);
		$count = CustodyBatch::where('batchNo', $item['batchNo'])->where('status', 0)->update([
			'checkTime'=>date('Y-m-d H:i:s'), 
			'checkResult'=>json_encode($result), 
			'status' => $status,
		]);

		Handler::back();
	}

	/**
	 * 批次还担保账户垫款异步回调
	 */
	public function batchRepayBailNotifyAction() {
		$params = $this->getAllPost(false);
		Log::write('[batchRepayBail]批次还担保账户垫款异步回调', $params, 'custody');
		$data = json_decode($params['bgData'], true);

		if(!Handler::verify($data)) {
			Log::write('[batchRepayBail]验签失败', [], 'custody');
			Handler::back();
		}

		$status = 0;
		if($data['retCode'] == Handler::SUCCESS) {
			$status = 1;
		} else {
			$status = 2;
		}

		$result = [
			'retCode' => $data['retCode'],
			'sucAmount' => $data['sucAmount'],
			'sucCounts' => $data['sucCounts'],
			'failAmount' => $data['failAmount'],
			'failCounts' => $data['failCounts'],
		];

		$item = StringHelper::decodeQueryString($data['acqRes']);

		CustodyBatch::where('batchNo', $item['batchNo'])->update([
			'returnTime'=>date('Y-m-d H:i:s'), 
			'returnResult'=>json_encode($result), 
			'status'=>$status
		]);
		
		$bailRepay = BailRepay::where('orgBatchNo', $item['orgBatchNo'])->first(['id', 'oddNumber', 'period']);

		BailRepay::with(['odd'=>function($q){
			$q->select('oddNumber', 'userId');
		}])->where('orgBatchNo', $item['orgBatchNo'])->update([
			'status' => $status,
			'batchNo' => $item['batchNo'],
			'returnTime' => date('Y-m-d H:i:s'),
			'result' => $data['retCode']
		]);
		
		$dbUser = User::where('username', User::ACCT_DB)->first(['userId', 'fundMoney', 'frozenMoney']);
		$user = User::where('userId', $bailRepay->odd->userId)->first(['userId', 'fundMoney', 'frozenMoney']);

		$status1 =  User::where('userId', $user->userId)->update(['frozenMoney'=>DB::raw('frozenMoney - '.$data['sucAmount'])]);
		$status2 =  User::where('userId', '')->update(['fundMoney'=>DB::raw('fundMoney + '.$data['sucAmount'])]);
        if($status){
			$time = date('Y-m-d H:i:s');
            $remark = '[还垫付款]解冻标的@oddNumber{'.$bailRepay->oddNumber.'}，第'.$bailRepay->period.'期还款'.$data['txAmount'].'元。';
            $log = [];
            $log['type'] = 'nor-bailrepay';
            $log['mode'] = 'unfreeze';
            $log['mvalue'] = $data['sucAmount'];
            $log['userId'] = $user->userId;
            $log['remark'] = $remark;
            $log['remain'] = $user->fundMoney;
            $log['frozen'] = $user->frozenMoney - $data['sucAmount'];
            $log['time'] = $time;
            $remark = '标的@oddNumber{'.$bailRepay->oddNumber.'}，第'.$bailRepay->period.'期垫付还款'.$data['txAmount'].'元回款。';
            $dbLog = [];
            $dbLog['type'] = 'nor-bailrepay';
            $dbLog['mode'] = 'in';
            $dbLog['mvalue'] = $data['sucAmount'];
            $dbLog['userId'] = $dbUser->userId;
            $dbLog['remark'] = $remark;
            $dbLog['remain'] = $dbUser->fundMoney + $data['sucAmount'];
            $dbLog['frozen'] = $dbUser->frozenMoney;
            $dbLog['time'] = $time;
            MoneyLog::insert([$log, $dbLog]);
        }

		Handler::back();
	}

	/**	
	 * 银行卡解绑
	 */
	public function cardUnBindNotifyAction() {
		$params = $this->getAllPost(false);
		Log::write('[CARD_BIND]解绑银行卡异步回调', $params, 'custody');
		$data = json_decode($params['bgData'], true);

		if(!Handler::verify($data)) {
			Log::write('[CARD_BIND]验签失败', [], 'custody');
			Handler::back();
		}

		$return = [];
		if($data['retCode']==Handler::SUCCESS) {
			$item = StringHelper::decodeQueryString($data['acqRes']);
			//UserBank::where('userId', $item['userId'])->where('bankNum', $item['bankNum'])->update(['status'=>0]);
            UserBank::where('userId', $item['userId'])->update(['status'=>0]);
			// $item = StringHelper::decodeQueryString($data['acqRes']);
			// $binInfo = BankCard::getBinInfo($item['bankNum']);
			// UserBank::insert([
			// 	'userId'=>$item['userId'], 
			// 	'bankNum'=>$item['bankNum'], 
			// 	'createAt'=>date('Y-m-d H:i:s'), 
			// 	'updateAt'=>date('Y-m-d H:i:s'),
			// 	'binInfo'=>$binInfo
			// ]);
		}

		Handler::back();
	}

	/**	
	 * 银行卡绑定
	 */
	public function cardBindNotifyAction() {
		$params = $this->getAllPost(false);
		Log::write('[CARD_BIND]绑定银行卡异步回调', $params, 'custody');
		$data = json_decode($params['bgData'], true);

		if(!Handler::verify($data)) {
			Log::write('[CARD_BIND]验签失败', [], 'custody');
			Handler::back();
		}

		$return = [];
		if($data['retCode']==Handler::SUCCESS) {
			$item = StringHelper::decodeQueryString($data['acqRes']);
			$binInfo = BankCard::getBinInfo($item['bankNum']);
			UserBank::insert([
				'userId'=>$item['userId'], 
				'bankNum'=>$item['bankNum'], 
				'createAt'=>date('Y-m-d H:i:s'), 
				'updateAt'=>date('Y-m-d H:i:s'),
				'binInfo'=>$binInfo
			]);
		}

		Handler::back();
	}
	/**	
	 * 修改手机号
	 */
	public function updatePhoneNotifyAction() {
		$params = $this->getAllPost(false);
		Log::write('[UPDATE_PHONE]修改手机号异步回调', $params, 'custody');
		$data = json_decode($params['bgData'], true);

		if(!Handler::verify($data)) {
			Log::write('[UPDATE_PHONE]验签失败', [], 'custody');
			Handler::back();
		}

		$return = [];
		if($data['retCode']==Handler::SUCCESS) {
			$item = StringHelper::decodeQueryString($data['acqRes']);
			User::where('userId', $item['userId'])->update(['phone'=>$item['phone']]);
		}

		Handler::back();
	}

	/**	
	 * 受托支付申请回调
	 */
	public function trustPayNotifyAction() {
		$params = $this->getAllPost(false);
		Log::write('[TRUST_PAY]受托支付申请回调', $params, 'custody');
		$data = json_decode($params['bgData'], true);

		if(!Handler::verify($data)) {
			Log::write('[TRUST_PAY]验签失败', [], 'custody');
			Handler::back();
		}

		$return = [];
		if($data['retCode']==Handler::SUCCESS) {
			Odd::where('oddNumber', _pton($data['productId']))->update(['receiptStatus'=>1]);
		}

		Handler::back();
	}

	/**
     * 批次发红包合法性检查异步回调
     */
    public function batchVoucherPayAuthNotifyAction() {
        $params = $this->getAllPost(false);
        Log::write('[batchVoucherPayAuth]批次发红包合法性检查异步回调', $params, 'custody');
        $data = json_decode($params['bgData'], true);

        if(!Handler::verify($data)) {
            Log::write('[batchVoucherPayAuth]验签失败', [], 'custody');
            Handler::back();
        }

        $status = 0;
        if($data['retCode'] != Handler::SUCCESS) {
            $status = -1;
        }

        $result = [
            'retCode' => $data['retCode'],
            'txAmount' => $data['txAmount'],
            'txCounts' => $data['txCounts'],
        ];

        $count = CustodyBatch::where('batchNo', $data['acqRes'])->where('status', 0)->update([
            'checkTime'=>date('Y-m-d H:i:s'), 
            'checkResult'=>json_encode($result), 
            'status'=>$status
        ]);

        Handler::back();
    }

    /**
     * 批次发红包异步回调
     */
    public function batchVoucherPayNotifyAction() {
        $params = $this->getAllPost(false);
        Log::write('[batchVoucherPay]批次发红包异步回调', $params, 'custody');

        $data = json_decode($params['bgData'], true);

        if(!Handler::verify($data)) {
            Log::write('[batchVoucherPay]验签失败', [], 'custody');
            Handler::back();
        }

        $batch = CustodyBatch::where('batchNo', $data['acqRes'])->where('status', 0)->first();
        if(!$batch) {
            Handler::back();
        }

        $batch->handle($data);

        Handler::back();
    }

    public function degWithdrawNotifyAction() {
    	$params = $this->getAllPost(false);
        Log::write('[degWithdraw]受托支付提现异步回调', $params, 'custody');

        $data = json_decode($params['bgData'], true);

    	if(!API::verify($data)) {
			Log::write('[degWithdraw]验签失败', [], 'custody');
			Handler::back();
		}

		$return = [];
		if($data['retCode']==API::SUCCESS) {
			$return = ['status'=>1, 'tradeNo'=>$data['requestNo'], 'result'=>$data['retCode']];
		} else {
			$return = ['status'=>0, 'tradeNo'=>$data['requestNo'], 'result'=>$data['retCode']];
		}

		DegWithdraw::after($return);

		Handler::back();
    }
}
