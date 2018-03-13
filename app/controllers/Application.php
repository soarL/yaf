<?php
use models\Recharge;
use models\User;
use models\Withdraw;
use models\UserBid;
use models\UserCrtr;
use models\Queue;
use models\AutoInvest;
use exceptions\HttpException;
use Yaf\Registry;
use plugins\lianlian\lib\LLpayNotify;
use plugins\lianlian\lib\JSON;
use plugins\lianlian\Config as LLConfig;
use tools\MSBank;
use tools\BFBank;
use helpers\StringHelper;

/**
 * @author elf <360197197@qq.com>
 */
class ApplicationController extends Controller {
	public $menu = 'application';

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
		
		if($computeSign!=$sign) {
			echo '签名验证失败';
			exit(0);
		}

		$user = User::find($nickname);
		if(!$user) {
			Flash::error('开通一麻袋账户失败！');
			$this->redirect(WEB_USER.'/account/third');		
		}

		if($user->thirdAccountStatus=='1') {
			Flash::success('您的一麻袋账户已经开通！');
			$this->redirect(WEB_USER.'/account/third');		
		}

		//判断返回信息
		if($status == '00') {
			$user->afterBindThird();
			Flash::success('开通一麻袋账户成功！');
			$this->redirect(WEB_USER.'/account/third');
		} else {
			Flash::error('开通一麻袋账户失败！');
			$this->redirect(WEB_USER.'/account/third');
		}
	}

	public function createThirdAccountCompanyAction() {
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
			.'&real_name='.$realname
			.'&card_no='.$cardno
			.'&merchantKey='.$merchantKey;

		$computeSign = strtolower(md5($string));
		
		if($computeSign!=$sign) {
			echo '签名验证失败';
			exit(0);
		}

		$user = User::find($nickname);
		if(!$user) {
			Flash::error('开通一麻袋账户失败！');
			$this->redirect(WEB_USER.'/account/third');		
		}

		if($user->thirdAccountStatus=='1') {
			Flash::success('您的一麻袋账户已经开通！');
			$this->redirect(WEB_USER.'/account/third');		
		}

		//判断返回信息
		if($status == '00') {
			$user->afterBindThird();
			Flash::success('开通一麻袋账户成功！');
			$this->redirect(WEB_USER.'/account/third');
		} else {
			Flash::error('开通一麻袋账户失败！');
			$this->redirect(WEB_USER.'/account/third');
		}
	}

	/**
	 * 充值异步返回接口
	 */
	public function rechargeAdviceAction() {
		$request = $this->getRequest();
	    $numberId = $request->getPost('number_id');
	    $mode = $request->getPost('mode');
		$tradeNo = $request->getPost('out_trade_no');
	    $amount = $request->getPost('amount');
	    $fee = $request->getPost('fee');
		$nickName= $request->getPost('nick_name');
		$status= $request->getPost('status');
		$sign= $request->getPost('sign_info');

		$merchantKey = Registry::get('config')->get('third')->get('key');
		$signStr = 'number_id='.$numberId.'&mode=recharge&out_trade_no='.$tradeNo.'&amount='.$amount.'&fee='.$fee.'&nick_name='.$nickName.'&status='.$status.'&merchantKey='.$merchantKey;
		$computeSign = strtolower(md5($signStr));
		Log::write('异步返回:'.$signStr, 'recharge');
		if($computeSign!=$sign) {
			Log::write($sign.'---'.$computeSign, 'recharge');
			echo '签名验证失败';
			exit(0);
		}
		if($status == '00') {
			$data['nickName']=$nickName;
			$data['tradeNo']=$tradeNo;
			$data['money']=$amount;
			$data['fee']=$fee;
			$data['result']=$status;
			$result = Recharge::afterSuccess($data);
			if($result['status']==1) {
				echo 'ok';exit(0);
			} else {
				echo 'error';exit(0);
			}
		} else {
			$data['nickName']=$nickName;
			$data['tradeNo']=$tradeNo;
			$data['money']=$amount;
			$data['fee']=$fee;
			$data['result']=$status;
			Recharge::afterFail($data);
			echo 'ok';exit(0);
		}
	}
	
	/**
	 * 充值页面返回接口
	 */
	public function rechargeReturnAction() {
		$request = $this->getRequest();
	    $numberId = $request->getPost('number_id');
	    $mode = $request->getPost('mode');
		$tradeNo = $request->getPost('out_trade_no');
	    $amount = $request->getPost('amount');
	    $fee = $request->getPost('fee');
		$nickName= $request->getPost('nick_name');
		$status= $request->getPost('status');
		$sign= $request->getPost('sign_info');

		$merchantKey = Registry::get('config')->get('third')->get('key');
		$signStr = 'number_id='.$numberId.'&mode=recharge&out_trade_no='.$tradeNo.'&amount='.$amount.'&fee='.$fee.'&nick_name='.$nickName.'&status='.$status.'&merchantKey='.$merchantKey;
		$computeSign = strtolower(md5($signStr));
		Log::write('同步返回:'.$signStr, 'recharge');
		if($computeSign!=$sign) {
			Log::write($sign.'---'.$computeSign, 'recharge');
			Flash::error('数据错误，充值失败！');
			$this->redirect(WEB_USER.'/account/recharge');
		}
		if($status == '00') {
			$data['nickName']=$nickName;
			$data['tradeNo']=$tradeNo;
			$data['money']=$amount;
			$data['fee']=$fee;
			$data['result']=$status;
			$result = Recharge::afterSuccess($data);
			if($result['status']==1) {
				Flash::success('充值成功！');
				$this->redirect(WEB_USER.'/account/recharge');
			} else {
				Flash::error($result['info']);
				$this->redirect(WEB_USER.'/account/recharge');
			}
		} else {
			$data['nickName']=$nickName;
			$data['tradeNo']=$tradeNo;
			$data['money']=$amount;
			$data['fee']=$fee;
			$data['result']=$status;
			Recharge::afterFail($data);
			Flash::error('充值失败！');
			$this->redirect(WEB_USER.'/account/recharge');
		}
	}

	public function withdrawBackAction() {
		Log::write('提现异步返回', 'withdraw');
		// ob_start();
		$request = $this->getRequest();
		//商户数字账号
	    $numberId = $request->getPost('number_id');
	    //类型 
	    $mode = $request->getPost('mode');
		//商户订单号 
		$tradeNo = $request->getPost('out_trade_no');
	    //充值金额
	    $amount = $request->getPost('amount');
	    //手续费
	    $fee = $request->getPost('fee');
	    //昵称
		$nickName = $request->getPost('nick_name');
		$status= $request->getPost('status');
		$sign= $request->getPost('sign_info');
		$merchantKey = Registry::get('config')->get('third')->get('key');
		$signStr = 'number_id='.$numberId.'&mode='.$mode.'&out_trade_no='.$tradeNo.'&amount='.$amount.'&fee='.$fee.'&nick_name='.$nickName.'&status='.$status.'&merchantKey='.$merchantKey;
		$computeSign = strtolower(md5($signStr));
		Log::write($signStr, 'withdraw');
		
		if($computeSign!=$sign) {
			Log::write('签名错误:'.$sign.'---'.$computeSign, 'withdraw');
			echo '签名验证失败';
			exit(0);
		}
		
		$withdraw = Withdraw::where('tradeNo', $tradeNo)->first();
		if(!$withdraw) {
			echo '订单号不存在';
			exit(0);
		}

		if($status=='00') {
			$withdraw->onSuccess(['result'=>$status]);
			echo 'OK';
		} else if($status=='88') {
			$withdraw->onFail(['result'=>$status]);
			echo 'OK';
		} else {
			echo 'OK';
		}
	}

	public function bidAdviceAction() {
		Log::write('异步返回', 'bid');
		$params = $this->getRequest()->getPost();
		$result = UserBid::after($params);
		echo 'ok';
	}

	public function bidReturnAction($num='') {
		Log::write('同步返回', 'bid');
		if($num=='') {
			throw new HttpException(404);
		}
		$status = false;
		$params = $this->getRequest()->getPost();
		$result = UserBid::after($params);
		if($result['state']=='success') {
			Flash::success('投标成功！');
		} else {
			Flash::error($result['msg']);
		}
		$this->redirect(Url::to('/odd/view', ['num'=>$num]));
	}

	public function crtrAdviceAction() {
		Log::write('异步返回', 'crtr');
		$params = $this->getRequest()->getPost();
		$result = UserCrtr::after($params);
		echo 'ok';
	}

	public function crtrReturnAction($num=0) {
		Log::write('同步返回', 'crtr');
		if($num==0) {
			throw new HttpException(404);
		}
		$status = false;
		$params = $this->getRequest()->getPost();
		$result = UserCrtr::after($params);
		if($result['state']=='success') {
			Flash::success('购买成功！');
		} else {
			Flash::error($result['msg']);
		}
		$this->redirect(Url::to('/crtr/view', ['num'=>$num]));
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
		if($sign==$computeSign&&($status=='00'||$status=='88')&&$thirdAccountAuth!=-1) {
			$resultStatus = User::where('userId', $nickName)->update(['thirdAccountAuth'=>$thirdAccountAuth]);
			if($resultStatus) {
				if($status=='00') {
					Flash::success('授权成功！');
				} else {
					Queue::out($nickName);
					AutoInvest::where('userId', $nickName)->update(['autostatus'=>'0']);
					Flash::success('取消授权成功！');
				}
			} else {
				if($status=='00') {
					Flash::error('授权失败！');
				} else {
					Flash::error('取消授权失败！');	
				}
			}
		} else {
			Flash::error('授权失败！');
		}
		$this->redirect(WEB_USER.'/account/third');
	}

	public function llNotifyAction() {
		Log::write('连连异步返回', 'recharge');
		$llConfig = LLConfig::$params;
		$llpayNotify = new LLpayNotify($llConfig);
		$status = $llpayNotify->verifyNotify();
		$rdata = [];
		if ($status) {
			$is_notify = true;
			$json = new JSON();
			$str = file_get_contents("php://input");
			Log::write($str, 'recharge');
			$val = $json->decode($str);
			$oid_partner = trim($val->{'oid_partner'});
			$dt_order = trim($val->{'dt_order'});
			$no_order = trim($val->{'no_order'});
			$oid_paybill = trim($val->{'oid_paybill'});
			$money_order = trim($val->{'money_order'});
			$result_pay = trim($val->{'result_pay'});
			$settle_date = trim($val->{'settle_date' });
			//$info_order = trim($val->{'info_order'});
			$pay_type = trim($val->{'pay_type'});
			$bank_code = trim($val->{'bank_code'});
			$sign_type = trim($val->{'sign_type'});
			$sign = trim($val->{'sign'});
			if($result_pay == 'SUCCESS') {
				$data['tradeNo'] = $no_order;
				$data['money'] = $money_order;
				$data['fee'] = 0;
				$data['result'] = $result_pay;
				if($pay_type=='D') {
					$no_agree = trim($val->{'no_agree'});
					$id_type = trim($val->{'id_type'});
					$id_no = trim($val->{'id_no'});
					$acct_name = trim($val->{'acct_name'});
					$data['payType'] = $pay_type;
					$data['noAgree'] = $no_agree;
					$data['idType'] = $id_type;
					$data['idNo'] = $id_no;
					$data['acctName'] = $acct_name;
					$data['bankCode'] = $bank_code;
				}
				$result = Recharge::afterSuccess($data);
				if($result['status']==1) {
					Log::write('success1', 'recharge');
					echo "{'ret_code':'0000','ret_msg':'交易成功'}";exit(0);
				} else {
					Log::write('success2', 'recharge');
					echo "{'ret_code':'0000','ret_msg':'交易成功'}";exit(0);
				}
		    }else {
				$data['tradeNo'] = $no_order;
				$data['money'] = $money_order;
				$data['fee'] = 0;
				$data['result'] = $result_pay;
				Recharge::afterFail($data);
				Log::write('success3', 'recharge');
		    	echo "{'ret_code':'0000','ret_msg':'交易成功'}";exit(0);
		    }
		} else {
			Log::write('success4', 'recharge');
			echo "{'ret_code':'9999','ret_msg':'验签失败'}";exit(0);
		}
	}

	public function llReturnAction() {
		Log::write('连连同步返回', 'recharge');
		$llConfig = LLConfig::$params;
		$llpayNotify = new LLpayNotify($llConfig);
		$status = $llpayNotify->verifyReturn();
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
					Flash::success('充值成功！');
					$this->redirect(WEB_USER.'/account/recharge');
				} else {
					Flash::error($result['info']);
					$this->redirect(WEB_USER.'/account/recharge');
				}
		    }else {
		    	$data['tradeNo'] = $no_order;
				$data['money'] = $money_order;
				$data['fee'] = 0;
				$data['result'] = $result_pay;
				Recharge::afterFail($data);
		    	Flash::error('充值失败！');
				$this->redirect(WEB_USER.'/account/recharge');
		    }
		} else {
			Flash::error('数据异常！');
			$this->redirect(WEB_USER.'/account/recharge');
		}
	}

	public function msNotifyAction() {
		Log::write('民生异步返回', 'recharge');
		$params = $this->getAllPost(true);
		$oid_partner = $params['oid_partner'];
		$no_order = $params['no_order'];
		$oid_paybill = $params['oid_paybill'];
		$money_order = $params['money_order'];
		$resp_type = $params['resp_type'];
		$resp_code = $params['resp_code'];
		$resp_msg = $params['resp_msg'];
		$settle_date = $params['settle_date'];
		$sign = $params['sign'];

		$pubKey = MSBank::getKey('public', 'ms');
		$privateKey = MSBank::getKey('private', 'xwsd');

		unset($params['sign']);

		$dataStr = StringHelper::createLinkString(StringHelper::paramsSort($params, true));

		$status = StringHelper::rsaVerify($dataStr, $sign, $pubKey);

		$rdata = [];
		if ($status) {
			Log::write('民生:'.$no_order.'---'.$resp_type, 'recharge');
			if($resp_type == 'S') {
				$data['tradeNo'] = $no_order;
				$data['money'] = $money_order;
				$data['fee'] = 0;
				$data['result'] = $resp_type;

				$result = Recharge::afterSuccess($data);

				if($result['status']==1) {

					$rdata['resp_code'] = '00';
					$rdata['resp_msg'] = '交易成功';
					$returnSign = StringHelper::rsaSign(StringHelper::createLinkString(StringHelper::paramsSort($rdata, true)), $privateKey);
					$rdata['sign'] = $returnSign;

					$this->backJson($rdata);
				} else {

					$rdata['resp_code'] = '88';
					$rdata['resp_msg'] = '交易失败';
					$returnSign = StringHelper::rsaSign(StringHelper::createLinkString(StringHelper::paramsSort($rdata, true)), $privateKey);
					$rdata['sign'] = $returnSign;
					$this->backJson($rdata);
				}
			} else if($resp_type == 'R') {
				$data['tradeNo'] = $no_order;
				$data['money'] = $money_order;
				$data['fee'] = 0;
				$data['result'] = $resp_type;
				$result = Recharge::afterInHand($data);

				$rdata['resp_code'] = '00';
				$rdata['resp_msg'] = '交易成功';
				$returnSign = StringHelper::rsaSign(StringHelper::createLinkString(StringHelper::paramsSort($rdata, true)), $privateKey);
				$rdata['sign'] = $returnSign;
				$this->backJson($rdata);
		    } else {
				$data['tradeNo'] = $no_order;
				$data['money'] = $money_order;
				$data['fee'] = 0;
				$data['result'] = $result_pay;
				Recharge::afterFail($data);

				$rdata['resp_code'] = '88';
				$rdata['resp_msg'] = '交易失败';
				$returnSign = StringHelper::rsaSign(StringHelper::createLinkString(StringHelper::paramsSort($rdata, true)), $privateKey);
				$rdata['sign'] = $returnSign;
				$this->backJson($rdata);
		    }
		} else {
			$rdata['resp_code'] = '88';
			$rdata['resp_msg'] = '验签失败';
			$returnSign = StringHelper::rsaSign(StringHelper::createLinkString(StringHelper::paramsSort($rdata, true)), $privateKey);
			$rdata['sign'] = $returnSign;
			$this->backJson($rdata);
		}
	}

	public function msReturnAction() {
		Log::write('民生同步返回', 'recharge');

		$params = $this->getAllPost(true);
		$oid_partner = $params['oid_partner'];
		$no_order = $params['no_order'];
		$oid_paybill = $params['oid_paybill'];
		$money_order = $params['money_order'];
		$resp_type = $params['resp_type'];
		$resp_code = $params['resp_code'];
		$resp_msg = $params['resp_msg'];
		$settle_date = $params['settle_date'];
		$sign = $params['sign'];
		
		$pubKey = MSBank::getKey('public', 'ms');
		$privateKey = MSBank::getKey('private', 'xwsd');

		unset($params['sign']);

		$dataStr = StringHelper::createLinkString(StringHelper::paramsSort($params, true));

		$status = StringHelper::rsaVerify($dataStr, $sign, $pubKey);

		if ($status) {
			Log::write('民生:'.$no_order.'---'.$resp_type, 'recharge');
			if($resp_type == 'S') {
				$data['tradeNo'] = $no_order;
				$data['money'] = $money_order;
				$data['fee'] = 0;
				$data['result'] = $resp_type;
				$result = Recharge::afterSuccess($data);
				if($result['status']==1) {
					Flash::success('充值成功！');
					$this->redirect(WEB_USER.'/account/recharge');
				} else {
					Flash::error($result['info']);
					$this->redirect(WEB_USER.'/account/recharge');
				}
			} else if($resp_type == 'R') {
				$data['tradeNo'] = $no_order;
				$data['money'] = $money_order;
				$data['fee'] = 0;
				$data['result'] = $resp_type;
				$result = Recharge::afterInHand($data);
				Flash::error('充值处理中！');
				$this->redirect(WEB_USER.'/account/recharge');
		    } else {
		    	$data['tradeNo'] = $no_order;
				$data['money'] = $money_order;
				$data['fee'] = 0;
				$data['result'] = $result_pay;
				Recharge::afterFail($data);
		    	Flash::error('充值失败！');
				$this->redirect(WEB_USER.'/account/recharge');
		    }
		} else {
			Flash::error('数据异常！');
			$this->redirect(WEB_USER.'/account/recharge');
		}
	}

	public function frozenRelieveAdviceAction() {
		Log::write('【冻结完成】返回', 'frozen');

		$numberId = $this->getPost('number_id', '');
		$mode = $this->getPost('mode', '');
		$orderNo = $this->getPost('order_no', '');
		$amount = $this->getPost('total_amount', '');
		$size = $this->getPost('total_size', '');
		$status = $this->getPost('status', '');
		$sign = $this->getPost('sign_info', '');

		$merchantKey = Registry::get('config')->get('third')->get('key');

		$string = 'number_id='.$numberId.'&mode='.$mode.'&order_no='.$orderNo.'&total_amount='.$amount.'&total_size='.$size.'&status='.$status;

		Log::write('数据：' . $string . '&sign_info=' . $sign, 'frozen');

		$computeSign = strtolower(md5($string.'&merchantKey='.$merchantKey));
		
		Log::write('验证：' . $sign . '-----' . $computeSign, 'frozen');

		if($computeSign!=$sign) {
			echo '签名验证失败';
			exit(0);
		}
	}

	public function frozenBackAdviceAction() {
		Log::write('【冻结退回】返回', 'frozen');

		$numberId = $this->getPost('number_id', '');
		$mode = $this->getPost('mode', '');
		$orderNo = $this->getPost('order_no', '');
		$amount = $this->getPost('total_amount', '');
		$size = $this->getPost('total_size', '');
		$status = $this->getPost('status', '');
		$sign = $this->getPost('sign_info', '');

		$merchantKey = Registry::get('config')->get('third')->get('key');

		$string = 'number_id='.$numberId.'&mode='.$mode.'&order_no='.$orderNo.'&total_amount='.$amount.'&total_size='.$size.'&status='.$status;

		Log::write('数据：' . $string . '&sign_info=' . $sign, 'frozen');

		$computeSign = strtolower(md5($string.'&merchantKey='.$merchantKey));
		
		Log::write('验证：' . $sign . '-----' . $computeSign, 'frozen');
		
		if($computeSign!=$sign) {
			echo '签名验证失败';
			exit(0);
		}
	}

	public function bfNotifyAction() {
		Log::write('宝付异步返回', 'recharge');
		$params = $this->getAllPost(true);
		$content = $params['data_content'];
		Log::write('宝付Content:'.$content, 'recharge');
		$publicKey = BFBank::getKey('public', 'bf');
		$dataStr = StringHelper::bfVerify($content, $publicKey);

		Log::write('宝付:'.$dataStr, 'recharge');
		$results = json_decode($dataStr, true);

		if (isset($results['resp_code'])) {
			$resp_code = $results['resp_code'];
			if($resp_code == '0000') {
				$data['tradeNo'] = $results['trans_id'];
				$data['money'] = $results['succ_amt'];
				$data['fee'] = 0;
				$data['result'] = $resp_code;
				$data['thirdSerialNo'] = $results['trans_no'];
				$data['agreement'] = json_decode($results['req_reserved'], true);
				$result = Recharge::afterSuccess($data);
				echo 'OK';exit(0);
		    } else {
				$data['tradeNo'] = $results['trans_id'];
				$data['money'] = $results['succ_amt'];
				$data['fee'] = 0;
				$data['result'] = $resp_code;
				$data['thirdSerialNo'] = $results['trans_no'];
				Recharge::afterFail($data);
				echo 'OK';exit(0);
		    }
		} else {
			echo '参数错误！';
		}
	}

	public function bfReturnAction() {
		Log::write('宝付同步返回', 'recharge');
		$params = $this->getAllPost(true);
		$content = $params['data_content'];

		$publicKey = BFBank::getKey('public', 'bf');
		$dataStr = StringHelper::bfVerify($content, $publicKey);

		Log::write('宝付:'.$dataStr, 'recharge');
		$results = json_decode($dataStr, true);
		if (isset($results['resp_code'])) {
			$resp_code = $results['resp_code'];
			if($resp_code == '0000') {
				$data['tradeNo'] = $results['trans_id'];
				$data['money'] = $results['succ_amt'];
				$data['fee'] = 0;
				$data['result'] = $resp_code;
				$data['thirdSerialNo'] = $results['trans_no'];
				$data['agreement'] = json_decode($results['req_reserved'], true);
				$result = Recharge::afterSuccess($data);
				if($result['status']==1) {
					Flash::success('充值成功！');
					$this->redirect(WEB_USER.'/account/recharge');
				} else {
					Flash::error($result['info']);
					$this->redirect(WEB_USER.'/account/recharge');
				}
		    } else {
				$data['tradeNo'] = $results['trans_id'];
				$data['money'] = $results['succ_amt'];
				$data['fee'] = 0;
				$data['result'] = $resp_code;
				$data['thirdSerialNo'] = $results['trans_no'];
				Recharge::afterFail($data);
				Flash::error('充值失败！');
				$this->redirect(WEB_USER.'/account/recharge');
		    }
		} else {
			Flash::error('数据异常！');
			$this->redirect(WEB_USER.'/account/recharge');
		}
	}

	public function bfWebNotifyAction() {

		$bfKey = Registry::get('config')->get('baofoo')->get('key');
		$params = $this->getAllPost(true);
		Log::write('宝付异步返回:'.json_encode($params), 'recharge');

		$signList = [];
		$signList['MemberID'] = $params['MemberID'];
		$signList['TerminalID'] = $params['TerminalID'];
		$signList['TransID'] = $params['TransID'];
		$signList['Result'] = $params['Result'];
		$signList['ResultDesc'] = $params['ResultDesc'];
		$signList['FactMoney'] = $params['FactMoney'];
		$signList['AdditionalInfo'] = $params['AdditionalInfo'];
		$signList['SuccTime'] = $params['SuccTime'];
		$signList['Md5Sign'] = $bfKey;

		$computeSigns = [];
		foreach ($signList as $key => $value) {
			$computeSigns[] = $key . '=' . $value;
		}
		$sign = md5(implode('~|~', $computeSigns));
		$reciveSign = $params['Md5Sign'];

		if($sign == $reciveSign) {
			if($params['Result']==1) {
				$data['tradeNo'] = $params['TransID'];
				$data['money'] = $params['FactMoney'];
				$data['status'] = 1;
				$data['fee'] = 0;
				$data['result'] = $params['Result'];
				$result = Recharge::after($data);
				echo 'OK';exit(0);
			} else {
				$data['tradeNo'] = $results['TransID'];
				$data['money'] = $results['FactMoney'];
				$data['fee'] = 0;
				$data['result'] = $params['Result'];
				Recharge::after($data);
				echo 'OK';exit(0);
			}
		} else {
			echo '参数错误！';
		}
	}

	public function bfWebReturnAction() {

		$bfKey = Registry::get('config')->get('baofoo')->get('key');
		$params = $this->getAllQuery(true);
		Log::write('宝付同步返回'.json_encode($params), 'recharge');

		$signList = [];
		$signList['MemberID'] = $params['MemberID'];
		$signList['TerminalID'] = $params['TerminalID'];
		$signList['TransID'] = $params['TransID'];
		$signList['Result'] = $params['Result'];
		$signList['ResultDesc'] = $params['ResultDesc'];
		$signList['FactMoney'] = $params['FactMoney'];
		$signList['AdditionalInfo'] = $params['AdditionalInfo'];
		$signList['SuccTime'] = $params['SuccTime'];
		$signList['Md5Sign'] = $bfKey;

		$computeSigns = [];
		foreach ($signList as $key => $value) {
			$computeSigns[] = $key . '=' . $value;
		}
		$sign = md5(implode('~|~', $computeSigns));
		$reciveSign = $params['Md5Sign'];

		if($sign == $reciveSign) {
			if($params['Result']==1) {
				Flash::success('充值成功！');
				$this->redirect(WEB_USER.'/account/recharge');
				// $data['tradeNo'] = $params['TransID'];
				// $data['money'] = $params['FactMoney'];
				// $data['fee'] = 0;
				// $data['result'] = $params['ResultDesc'];
				// $result = Recharge::after($data);

				// if($result['status']==1) {
				// } else {
				// 	Flash::error($result['info']);
				// 	$this->redirect(WEB_USER.'/account/recharge');
				// }
			} else {
				$data['tradeNo'] = $results['TransID'];
				$data['money'] = $results['FactMoney'];
				$data['fee'] = 0;
				$data['result'] = $params['ResultDesc'];
				Recharge::after($data);
				Flash::error('充值失败！');
				$this->redirect(WEB_USER.'/account/recharge');
			}
		} else {
			Flash::error('数据异常！');
			$this->redirect(WEB_USER.'/account/recharge');
		}
	}
}	