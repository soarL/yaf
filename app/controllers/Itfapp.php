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
use tools\Log;
/**
 * @author elf <360197197@qq.com>
 */
class ItfappController extends Controller {
	public $menu = 'itfapp';

	public function createThirdAccountAction() {
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
			$this->display('info', ['status'=>0, 'msg'=>'签名验证失败!']);
		}

		$user = User::find($nickname);
		if(!$user) {
			$this->display('info', ['status'=>0, 'msg'=>'开通一麻袋账户失败!']);
		}

		if($user->thirdAccountStatus=='1') {
			$this->display('info', ['status'=>0, 'msg'=>'您的一麻袋账户已经开通!']);
		}

		//判断返回信息
		if($status == '00') {
			$user->afterBindThird();
			$this->display('info', ['status'=>1, 'msg'=>'开通一麻袋账户成功!']);
		} else {
			$this->display('info', ['status'=>0, 'msg'=>'开通一麻袋账户失败!']);
		}
	}

	public function bidReturnAction() {
		$params = $this->getRequest()->getPost();
		$result = UserBid::after($params);
		$resultCode = '';
		if($result['state']) {
			$status = 1;
			$msg = '投资成功!';
		} else {
			$status = 0;
			$msg = '投资成功!';
		}
		$this->display('info', ['status'=>$status, 'msg'=>$msg]);
	}

	public function crtrReturnAction($num=0) {
		$params = $this->getAllPost();
		$result = UserCrtr::after($params);
		if($result['state']=='success') {
			$this->display('info', ['status'=>1, 'msg'=>'购买成功!']);
		} else {
			$this->display('info', ['status'=>0, 'msg'=>$result['msg']]);
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
		$rdata = [];
		if($sign==$computeSign&&($status=='00'||$status=='88')&&$thirdAccountAuth!=-1) {
			$resultStatus = User::where('userId', $nickName)->update(['thirdAccountAuth'=>$thirdAccountAuth]);
			if($resultStatus) {
				if($status=='00') {
					$status = 1;
					$msg = '授权成功！';
				} else {
					Queue::out($nickName);
        			AutoInvest::where('userId', $nickName)->update(['autostatus'=>'0']);
        			$status = 1;
					$msg = '取消授权成功！';
				}
			} else {
				if($status=='00') {
					$status = 0;
					$msg = '授权失败！';
				} else {
					$status = 0;
					$msg = '取消授权失败！';
				}
			}
		} else {
			$status = 0;
			$msg = '取消授权失败！';
		}
		$this->display('info', ['status'=>$status, 'msg'=>$msg]);
	}

	public function rechargeAction() {
		Log::write('富友异步返回', [], 'recharge');
		$params = $this->getAllPost(true);
		Log::write('参数：', $params, 'recharge');
		$mchntcd = Registry::get('config')->get('fuiou')->get('mchntcd');
		$key = Registry::get('config')->get('fuiou')->get('key');

		$payWay = $params['TYPE'];
		$version = $params['VERSION'];
		$orderID = $params['ORDERID'];
		$code = $params['RESPONSECODE'];
		$msg = $params['RESPONSEMSG'];
		$tradeNo = $params['MCHNTORDERID'];
		$amount = $params['AMT'];
		$bankCard = $params['BANKCARD'];

		$list = [$payWay, $version, $code, $mchntcd, $tradeNo, $orderID, $amount, $bankCard, $key];
        $sign = md5(implode('|', $list));

        if($sign!=$params['SIGN']) {
        	Log::write('验证失败！', [], 'recharge');
        	echo '验证失败！';exit(0);
        }

		if ($code=='0000') {
			$data['tradeNo'] = $tradeNo;
			$data['money'] = intval($amount/100);
			$data['fee'] = 0;
			$data['result'] = $code;
			$data['thirdSerialNo'] = $orderID;
			$result = Recharge::afterSuccess($data);
			Log::write('OK', [], 'recharge');
			echo 'OK';exit(0);
		} else {
			$data['tradeNo'] = $tradeNo;
			$data['money'] = intval($amount/100);
			$data['fee'] = 0;
			$data['result'] = $code;
			$data['thirdSerialNo'] = $orderID;
			Recharge::afterFail($data);
			Log::write('FAIL', [], 'recharge');
			echo 'OK';exit(0);
		}
	}
}
