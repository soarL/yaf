<?php
use helpers\StringHelper;
use Yaf\Registry;
use models\Odd;
use models\OddInfo;
use models\User;
use models\OddMoney;
use models\UserOffice;
use models\Interest;
use models\OddClaims;
use models\UserBid;
use models\Recharge;
use forms\RechargeFormOld as RechargeForm;
use models\Lottery;
use forms\BidForm;
use business\RepayHandler;
use tools\Pager;
use tools\API;
use tools\Redis;
use tools\Log;
use models\Gps;
use helpers\HtmlHelper;
use exceptions\HttpException;
use traits\PaginatorInit;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * OddController
 * 标的控制器
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class LoanerController extends Controller {
	use PaginatorInit;

	public $menu = 'odd';

	/**
	 * 标的列表
	 * @return mixed
	 */
	public function loanerDataAction() {
		$user = $this->getUser();
		if(!$user){
			$data['status'] = '0';
			$this->backJson($data);
		}

		$data = [];
		$data['name'] = $user->name;
		$data['userType'] = $user->userType;
		$data['card_num'] = _hide_cardnum($user->cardnum);
		$data['fundMoney'] = $user->fundMoney;
		$data['legal'] = _hide_name($user->userbank->legal);
		$data['bank_name'] = $user->userbank->bankCName;
		$data['bank_num'] = _hide_banknum($user->userbank->bankNum);

		$res = Odd::where('userId',$user->userId)->where('progress','<>','fail')->select(DB::raw('sum(oddMoney) total'))->first();
		$data['loanBenJin'] = $res->total;
		$res = OddMoney::where('userId',$user->userId)->where('type','loan')->select(DB::raw('sum(remain) total'))->first();
		$data['waitBenJin'] = $res->total;
		$res = Interest::where('userId',$user->userId)->where('status','0')->orderBy('endtime','asc')->first();
		if($res){
			$data['nearRepay'] = $res->zongEr;
			$data['nextRepay'] = $res->endtime;
            if(date('Y-m-d',strtotime($res->endtime)) != date('Y-m-d')){
			    $data['nextId'] = '';
            }else{
                $data['nextId'] = $res->id;
            }
		}

		$interestData = [];
		$interests = Interest::where('userId',$user->userId)->orderBy('id','asc')->get();
		$data['interestDatas'] = [];
		if(isset($interests)){
			$status = ['0' => '未还', '1' => '已还', '2' => '提前', '3' => '逾期', '-1' => '还款中'];
			foreach ($interests as $key => $value) {
				$item = [];
				$item['qishu'] = $value->qishu;
				$data['interestDatas'][$value->oddNumber]['oddTitle'] = $value->odd->oddTitle;
				$data['interestDatas'][$value->oddNumber]['oddNumber'] = $value->oddNumber;
				$item['endtime'] = $value->endtime;
				$item['benJin'] = $value->benJin;
				$item['interest'] = $value->interest;
				$item['status'] = $status[$value->status];
				if(isset($value->oddMoney->ancun)){
					$data['interestDatas'][$value->oddNumber]['recordUrl'] = "https://www.51cunzheng.com/searchResult?r=".$value->oddMoney->ancun->recordNo;
					//$item['recordNo'] = $value->oddMoney->ancun->recordNo;
				}
				$data['interestDatas'][$value->oddNumber]['list'][] = $item;
			}
		}

		foreach ($data['interestDatas'] as $key => $value) {
			if(!isset($value['recordUrl'])){
				$value['recordUrl'] = false;
			}
			$data['interestData'][] = $value;
		}
		unset($data['interestDatas']);

		$this->backJson($data);
	}

    /**
     * 还款
     * @return mixed
     */
    public function repayAction() {
        set_time_limit(0);
        sleep(1);
        $user = $this->getUser();
        $id = $this->getPost('id', 0);
        $type = $this->getPost('type', 'normal');
        //$replace = $this->getPost('replace', '0');
        $interest = Interest::where('userId',$user->userId)->find($id);
        if(!$interest) {
            $rdata['status'] = 0;
            $rdata['info'] = '还款不存在！';
            $this->backJson($rdata);
        }

        if($interest->status==1) {
            $rdata['status'] = 0;
            $rdata['info'] = '该笔还款已还完！';
            $this->backJson($rdata);
        }

        $key = Redis::getKey('repayIngQueue');
        if(!Redis::sAdd($key, $interest->id)) {
            $rdata['status'] = 0;
            $rdata['info'] = '正在还款！';
            $this->backJson($rdata);
        }
        $handler = new RepayHandler(['oddNumber'=>$interest->oddNumber, 'period'=>$interest->qishu, 'type'=>$type, 'replace'=>1, 'cr'=>false]);
        $result = $handler->handle();

        if($result['status']) {
            Flash::success('操作成功！');
            $rdata['status'] = 1;
            $this->backJson($rdata);
        } else {
            Redis::sRem($key, $interest->id);
            $rdata['status'] = 0;
            $rdata['info'] = $result['msg'];
            $this->backJson($rdata);
        }
    }

    /**
     * 充值
     * @return mixed
     */
    public function doRechargeAction() {
        $params = $this->getAllPost();

        $user = $this->getUser();

        $baofoo = Registry::get('config')->get('baofoo')->get('open');
        if($baofoo){
            header("Access-Control-Allow-Origin: *");
            $this->doBFRechargeAction();
            exit;
        }
        //开通银行存管
        if($user->custody_id=='') {
            Flash::error('您还未进行实名认证！');
            $this->redirect('/account/custody');
        }

        $form = new RechargeForm($params);
        if($form->recharge()) {
            echo $form->html;
        } else {
            Flash::error($form->posError());
            $this->redirect('/account/recharge');
        }
    }

    /**
     * 充值
     * @return mixed
     */
    public function doBFRechargeAction() {
        $params = $this->getAllPost();
        $user = $this->getUser();

        if($user->custody_id=='') {
            Flash::error('您还未进行实名认证！');
            $this->redirect('/account/custody');
        }

        $form = new RechargeForm($params);
        if($form->recharge()) {
            if($form->html){
                echo $form->html;
                exit;
            }
            if($form->result['resp_code'] != '0000'){
                $rdata['status'] = -1;
                $rdata['info'] = $form->result['resp_msg'];
                $this->backJson($rdata); 
            }else{
                $rdata['status'] = 1;
                $rdata['business_no'] = $form->result['business_no'];
                $this->backJson($rdata); 
            }
        } else {
            $rdata['status'] = -1;
            $rdata['info'] = $form->posError();
            $this->backJson($rdata); 
        }
    } 

        /**
     * 宝付绑卡认证2
     * @return mixed
     */
    public function confirmBFRechargeAction() {
        $params = $this->getAllPost();
        $form = new RechargeForm($params);
        if($form->baofooConfirmRecharge()) {
            $results = $form->result;
            $resp_code = $results['resp_code'];
            if($form->result['resp_code'] != '0000'){
                $data['tradeNo'] = $results['trans_id'];
                $data['fee'] = 0;
                $data['status'] = -1;
                $data['result'] = $resp_code;
                Recharge::after($data);

                $rdata['status'] = -1;
                $rdata['info'] = $form->result['resp_msg'];
                $this->backJson($rdata); 
            }else{
                $data['tradeNo'] = $results['trans_id'];
                $data['money'] = $results['succ_amt'];
                $data['fee'] = 0;
                $data['result'] = $resp_code;
                $data['status'] = 1;
                $data['thirdSerialNo'] = $results['trans_id'];
                $result = Recharge::after($data);

                $rdata['status'] = 1;
                $rdata['info'] = '充值成功';
                $this->backJson($rdata); 
            }
        } else {
            $rdata['status'] = -1;
            $rdata['info'] = $form->posError();
            $this->backJson($rdata); 
        }
    } 


}
