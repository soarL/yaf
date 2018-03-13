<?php
use exceptions\HttpException;
use Yaf\Registry;
use plugins\lianlian\lib\LLpayNotify;
use plugins\lianlian\lib\JSON;
use plugins\lianlian\Config as LLConfig;
use tools\BFBank;
use models\User;
use models\Recharge;
use models\UserBid;
use models\UserCrtr;
use models\Queue;
use models\AutoInvest;
use helpers\StringHelper;
/**
 * @author elf <360197197@qq.com>
 */
class ApplicationmobileController extends Controller {
	public $menu = 'application';

	public function createThirdAccountAction() {
		$redirectUrl = WEB_APP.'/pages/tgzh.html';

		$request = $this->getRequest();

		$numberId = $request->getPost('number_id', '');
		$mode = $request->getPost('mode', '');
		$nickname = $request->getPost('nick_name', '');
		$realname = $request->getPost('real_name', '');
		$cardno = $request->getPost('card_no', '');
		$status = $request->getPost('status', '');
		$sign = $request->getPost('sign_info', '');

		$merchantKey = Registry::get('config')->get('third')->get('key');

		$string = 'number_id='.$numberId
			.'&mode='.$mode
			.'&nick_name='.$nickname
			.'&status='.$status
			//.'&real_name='.$realname
			//.'&card_no='.$cardno
			.'&merchantKey='.$merchantKey;

		$computeSign = strtolower(md5($string));
		
		$rdata = [];
		if($computeSign!=$sign) {
			$rdata['resultCode'] = '11';
			$rdata['resultMsg'] = '签名验证失败';
			$this->redirect($redirectUrl.'?resultCode='.$rdata['resultCode']);
		}

		$user = User::find($nickname);
		if(!$user) {
			$rdata['resultCode'] = '11';
			$rdata['resultMsg'] = '开通一麻袋账户失败！';
			$this->redirect($redirectUrl.'?resultCode='.$rdata['resultCode']);
		}

		if($user->thirdAccountStatus=='1') {
			$rdata['resultCode'] = '11';
			$rdata['resultMsg'] = '您的一麻袋账户已经开通！';
			$this->redirect($redirectUrl.'?resultCode='.$rdata['resultCode']);
		}

		//判断返回信息
		if($status == '00') {
			$user->afterBindThird();
			$rdata['resultCode'] = '00';
			$rdata['resultMsg'] = '开通一麻袋账户成功！';
			$this->redirect($redirectUrl.'?resultCode='.$rdata['resultCode']);
		} else {
			$rdata['resultCode'] = '11';
			$rdata['resultMsg'] = '开通一麻袋账户失败！';
			$this->redirect($redirectUrl.'?resultCode='.$rdata['resultCode']);
		}
	}

	public function bidReturnAction() {
		Log::write('手机同步返回', 'bid');
		$redirectUrl = WEB_APP.'/pages/tzlb.html';
		$params = $this->getRequest()->getPost();
		$result = UserBid::after($params);
		$resultCode = '';
		if($result['state']) {
			$resultCode = '00';
		} else {
			$resultCode = '11';
		}
		$this->redirect($redirectUrl.'?resultCode='.$resultCode);
	}

	public function crtrReturnAction($num=0) {
		$redirectUrl = WEB_APP.'/pages/tzlb.html';
		$rdata = [];
		$params = $this->getAllPost();
		$result = UserCrtr::after($params);
		if($result['state']=='success') {
			$rdata['resultCode'] = '00';
			$rdata['resultMsg'] = '购买成功！';
			$this->redirect($redirectUrl.'?resultCode='.$rdata['resultCode']);
		} else {
			$rdata['resultCode'] = '11';
			$rdata['resultMsg'] = $result['msg'];
			$this->redirect($redirectUrl.'?resultCode='.$rdata['resultCode']);
		}
	}

	public function backThirdAuthAction() {
		$mode = $this->getRequest()->getPost('mode');
		$numberId = $this->getRequest()->getPost('number_id');
		$nickName = $this->getRequest()->getPost('nick_name');
		$status = $this->getRequest()->getPost('status');
		$sign = $this->getRequest()->getPost('sign_info');
		$merchantKey = Registry::get('config')->get('third')->get('key');
		$computeSign = strtolower(md5('number_id='.$numberId.'&mode='.$mode.'&nick_name='.$nickName.'&status='.$status.'&merchantKey='.$merchantKey));
		$thirdAccountAuth = -1;
		if($status=='00') {
			$thirdAccountAuth = 1;
		} else if($status=='88') {
			$thirdAccountAuth = 0;
		}
		$redirectUrl = WEB_APP.'/pages/tgzh.html';
		$rdata = [];
		if($sign==$computeSign&&($status=='00'||$status=='88')&&$thirdAccountAuth!=-1) {
			$resultStatus = User::where('userId', $nickName)->update(['thirdAccountAuth'=>$thirdAccountAuth]);
			if($resultStatus) {
				if($status=='00') {
					$rdata['resultCode'] = '00';
					$rdata['resultMsg'] = '授权成功！';
				} else {
					Queue::out($nickName);
        			AutoInvest::where('userId', $nickName)->update(['autostatus'=>'0']);
					$rdata['resultCode'] = '00';
					$rdata['resultMsg'] = '取消授权成功！';
				}
			} else {
				if($status=='00') {
					$rdata['resultCode'] = '11';
					$rdata['resultMsg'] = '授权失败！';
				} else {
					$rdata['resultCode'] = '11';
					$rdata['resultMsg'] = '取消授权失败！';
				}
			}
		} else {
			$rdata['resultCode'] = '11';
			$rdata['resultMsg'] = '取消授权失败！';
		}
		$this->redirect($redirectUrl.'?resultCode='.$rdata['resultCode']);
	}

	public function llReturnAction() {
		Log::write('手机连连同步返回', 'recharge');
		$llConfig = LLConfig::$params;
		$llpayNotify = new LLpayNotify($llConfig);
		$status = $llpayNotify->verifyReturn();
		$redirectUrl = WEB_APP.'/pages/userindex.html';
		$rdata = [];
		if ($status) {
			$request = $this->getRequest();
			$oid_partner = $request->getPost('oid_partner','');
			$dt_order= $request->getPost('dt_order','');
			$no_order = $request->getPost('no_order','');
			$oid_paybill = $request->getPost('oid_paybill','');
			$money_order = $request->getPost('money_order','');
			$result_pay =  $request->getPost('result_pay','');
			$settle_date =  $request->getPost('settle_date','');
			$info_order =  $request->getPost('info_order','');
			$pay_type =  $request->getPost('pay_type','');
			$bank_code =  $request->getPost('bank_code','');
			$sign_type = $request->getPost('sign_type','');
			$sign = $request->getPost('sign','');

			if($result_pay == 'SUCCESS') {
				$data['tradeNo'] = $no_order;
				$data['money'] = $money_order;
				$data['fee'] = 0;
				$data['result'] = $result_pay;
				$result = Recharge::afterSuccess($data);
				if($result['status']==1) {
					$rdata['resultCode'] = '00';
					$rdata['resultMsg'] = '充值成功！';
					$this->redirect($redirectUrl.'?resultCode='.$rdata['resultCode']);
				} else {
					$rdata['resultCode'] = '11';
					$rdata['resultMsg'] = $result['info'];
					$this->redirect($redirectUrl.'?resultCode='.$rdata['resultCode']);
				}
		    }else {
		    	$data['tradeNo'] = $no_order;
				$data['money'] = $money_order;
				$data['fee'] = 0;
				$data['result'] = $result_pay;
				Recharge::afterFail($data);
		    	$rdata['resultCode'] = '11';
				$rdata['resultMsg'] = '充值失败！';
				$this->redirect($redirectUrl.'?resultCode='.$rdata['resultCode']);
		    }
		} else {
			$rdata['resultCode'] = '11';
			$rdata['resultMsg'] = '数据异常！';
			$this->redirect($redirectUrl.'?resultCode='.$rdata['resultCode']);
		}
	}

	public function bfReturnAction() {
		Log::write('手机宝付同步返回', 'recharge');
		$params = $this->getAllPost(true);
		$content = $params['data_content'];

		$publicKey = BFBank::getKey('public', 'bf');
		$dataStr = StringHelper::bfVerify($content, $publicKey);
		
		Log::write('宝付:'.$dataStr, 'recharge');
		$results = json_decode($dataStr, true);
		$redirectUrl = WEB_APP.'/pages/userindex.html';

		$rdata = [];
		if (isset($results['resp_code'])) {
			$resp_code = $results['resp_code'];
			if($resp_code == '0000') {
				$data['tradeNo'] = $results['trans_id'];
				$data['money'] = $results['succ_amt'];
				$data['fee'] = 0;
				$data['result'] = $resp_code;
				$data['thirdSerialNo'] = $results['trans_no'];
				$result = Recharge::afterSuccess($data);
				if($result['status']==1) {
					$rdata['resultCode'] = '00';
					$rdata['resultMsg'] = '充值成功！';
					$this->redirect($redirectUrl.'?resultCode='.$rdata['resultCode']);
				} else {
					$rdata['resultCode'] = '11';
					$rdata['resultMsg'] = $result['info'];
					$this->redirect($redirectUrl.'?resultCode='.$rdata['resultCode']);
				}
		    } else {
				$data['tradeNo'] = $results['trans_id'];
				$data['money'] = $results['succ_amt'];
				$data['fee'] = 0;
				$data['result'] = $resp_code;
				$data['thirdSerialNo'] = $results['trans_no'];
				Recharge::afterFail($data);
				$rdata['resultCode'] = '11';
				$rdata['resultMsg'] = '充值失败！';
				$this->redirect($redirectUrl.'?resultCode='.$rdata['resultCode']);
		    }
		} else {
			$rdata['resultCode'] = '11';
			$rdata['resultMsg'] = '数据异常！';
			$this->redirect($redirectUrl.'?resultCode='.$rdata['resultCode']);
		}
	}
}