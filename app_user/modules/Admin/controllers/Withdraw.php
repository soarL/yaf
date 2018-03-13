<?php
use Admin as Controller;
use tools\API;
use models\Withdraw;
use models\User;
use models\UserBank;
use Yaf\Registry;
use helpers\NetworkHelper;
use traits\PaginatorInit;
use Illuminate\Database\Capsule\Manager as DB;
use helpers\ExcelHelper;
use helpers\ArrayHelper;
use forms\WithdrawFormOld as WithdrawForm;
use custody\Handler;

/**
 * WithdrawController
 * 提现管理
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class WithdrawController extends Controller {
	use PaginatorInit;

	public $menu = 'withdraw';

	/**
     * 提现列表
     * @return  mixed
     */
	public function listAction() {
		$this->submenu = 'list';

        $excel = $this->getQuery('excel', 0);
        $queries = $this->queries->defaults([
            'searchType'=>'tradeNo', 
            'searchContent'=>'', 
            'status'=>'unrehear',
            'beginTime'=>'', 
            'endTime'=>''
        ]);

        $searchType = $queries->searchType;
        $searchContent = $queries->searchContent;
        $status = $queries->status;
        $beginTime = $queries->beginTime;
        $endTime = $queries->endTime;

        $statusList = ['success'=>1, 'fail'=>2, 'unfinished'=>0, 'unrehear'=>3 ];
        
        $builder = Withdraw::with('user')->whereRaw('1=1');

        if($searchContent!='') {
	        $searchCol = '';
	        $searchValue = '';
	        if($searchType=='username') {
	        	$searchCol = 'userId';
	        	$user = User::where('username', $searchContent)->first(['userId']);
	        	$searchValue = $user?$user->userId:'';
	        }

	        if($searchType=='userId') {
	        	$searchCol = 'userId';
	        	$searchValue = $searchContent;
	        }

	        if($searchType=='tradeNo') {
	        	$searchCol = 'tradeNo';
	        	$searchValue = $searchContent;
	        }

	        if($searchValue!='') {
	        	$builder->where($searchCol, $searchValue);
			}
		}

        if($status!='all') {
        	$builder->where('status', $statusList[$status]);
        }

        if($beginTime!='') {
            $beginTime .= ' 00:00:00';
            $builder->where('addTime', '>=', $beginTime);
        }

        if($endTime!='') {
            $endTime .= ' 23:59:59';
            $builder->where('addTime', '<=', $endTime);
        }

        $records = null;
        $statistics = null;

        if($excel) {
            $records = $builder->orderBy('addTime', 'desc')->get();
            $other = [
                'title' => '网站充值',
                'columns' => [
                    'tradeNo' => ['name'=>'订单号', 'type'=>'string'],
                    'userId' => ['name'=>'用户ID', 'type'=>'string'],
                    'username' => ['name'=>'用户名', 'type'=>'string'],
                    'outMoney' => ['name'=>'金额'],
                    'fee' => ['name'=>'手续费'],
                    'realMoney' => ['name'=>'实际到账金额'],
                    'status' => ['name'=>'状态'],
                    'addTime' => ['name'=>'时间'],
                ],
            ];
            $excelRecords = [];
            foreach ($records as $row) {
                $row['tradeNo']  = $row['tradeNo'];
                $row['userId'] = $row['userId'];
                $row['fee'] = $row['fee'];
                $row['realMoney'] = $row['outMoney'] - $row['fee'];
                $row['username'] = $row['user']['username'];
                $row['status'] = ArrayHelper::getValue([0=>'成功', 1=>'未完成', 2=>'失败', 3=>'待审核'], $row['status']);
                $excelRecords[] = $row;
            }
            ExcelHelper::getDataExcel($excelRecords, $other);
        } else {
        	$statBuilder = clone $builder;
            $records = $builder->orderBy('addTime', 'desc')->paginate(20);
            $records->appends($queries->all());
            $statistics = $statBuilder->first([DB::raw('count(*) total_num'), DB::raw('sum(outMoney) total_money'), DB::raw('sum(fee) total_fee')]);
        }
        $this->display('list', ['records'=>$records, 'queries'=>$queries, 'statistics'=>$statistics]);
	}

    public function refuseAction() {
        $tradeNo = $this->getPost('tradeNo', '');
        $time = date('Y-m-d H:i:s', time());
        $withdraw = Withdraw::where('status',3)->where('tradeNo', $tradeNo)->first();
        $rdata = [];
        if(!$withdraw) {
            $rdata['status'] = 0;
            $rdata['msg'] = '操作失败，订单不存在或者订单不可补单！';
            $this->backJson($rdata);
        }
        $baofoo = Registry::get('config')->get('baofoo')->get('open');
        if($baofoo){
            $withdraw->status = 0;
            $withdraw->validTime = $time;
            if($withdraw->save()){
                $withdraw->user->updateAfterWithdrawE($withdraw->outMoney,$withdraw->useInvestMoney);
                $rdata['status'] = 1;
                $rdata['msg'] = '操作完成！';
                $this->backJson($rdata);
                exit;
            }
        }
    }

	public function packAction() {
		$tradeNo = $this->getPost('tradeNo', '');
        $time = date('Y-m-d H:i:s', time());//-20*60
		$withdraw = Withdraw::where(function($q) {
                $q->where('status', 3)->orWhere(function($q) {
                    $q->where('status', 1)->whereIn('result', Withdraw::$unknowCodes);
                });
            })
            ->where('tradeNo', $tradeNo)
            ->where('addTime', '<=', $time)
            ->first();
        $rdata = [];
        if(!$withdraw) {
            $rdata['status'] = 0;
            $rdata['msg'] = '补单失败，订单不存在或者订单不可补单！';
            $this->backJson($rdata);
        }

        $baofoo = Registry::get('config')->get('baofoo')->get('open');
        if($baofoo){
            $transData = json_decode($withdraw->xml,true);
            $withdrawForm = new WithdrawForm;
            $code = $withdrawForm->baofooSdkPost($transData);
            $withdraw = Withdraw::where('tradeNo', $tradeNo)->first();
            $withdraw->returnxml = json_encode($withdrawForm->result);
            $withdraw->result = $code;
            //$withdraw->status = 1;
            $withdraw->save();

            $data['tradeNo'] = $tradeNo;
            $data['result'] = $code;
            if($code == '0000'){
                $data['status'] = 1;
            }else{
                $data['status'] = 0;
                $withdrawForm->addError('form', $withdrawForm->result['trans_content']['trans_head']['return_msg']);
            }

            Withdraw::after($data);

            $rdata['status'] = 1;
            $rdata['msg'] = '补单完成！';
            $this->backJson($rdata);
            exit;
        }

        $data  = [];
        $data['accountId'] = User::getCID($withdraw->userId);
        $data['orgTxDate'] = substr($tradeNo, 0, 8);
        $data['orgTxTime'] = substr($tradeNo, 8, 6);
        $data['orgSeqNo'] = substr($tradeNo, 14);

        $handler = new Handler('fundTransQuery', $data);
        $result = $handler->api();
        $status = 0;
        if($result['retCode']==Handler::SUCCESS) {
            if($result['orFlag']!=1) {
                $status = 1;
            }
        }
        
        $return = [];
        $return['tradeNo'] = $tradeNo;
        $return['result'] = $result['retCode'];
        $return['status'] = $status;
        Withdraw::after($return);

        $rdata['status'] = 1;
        $rdata['msg'] = '补单完成！';
        $this->backJson($rdata);
	}

    /**
     * 提现重发
     * @return [type] [description]
     */
    public function resendAction() {
        $tradeNo = $this->getPost('tradeNo');
        $withdraw = Withdraw::where('tradeNo', $tradeNo)->first();
        if($withdraw) {
            if($withdraw->status!=0) {
                $rdata['status'] = 0;
                $rdata['msg'] = '该笔提现不可重发！';
                $this->backJson($rdata);
            }
            $transData = base64_encode($withdraw->xml);
            $postTo = Registry::get('config')->get('third')->get('base_url').'/hostingWithdrawCash';
            $result = NetworkHelper::post($postTo, ['transData'=>$transData]);
            $result = json_decode($result,true);
            if($result['resCode']=='0000') {
                $withdraw->onSuccess(['result'=>$result['resCode']]);
                $rdata['status'] = 1;
                $rdata['msg'] = '提现成功！';
                $this->backJson($rdata);
            } else {
                $withdraw->onFail(['result'=>$result['resCode']]);
                $rdata['status'] = 0;
                if($result['resCode']=='1009') {
                    $rdata['msg'] = '提现失败，一麻袋余额不足！';
                } else {
                    $rdata['msg'] = '提交一麻袋失败['.$result['resCode'].']！';
                }
                $this->backJson($rdata);
            }
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = '重发失败，该订单不存在！';
            $this->backJson($rdata);
        }
    }

    /**
     * 提现审核
     * @return mixed
     */
	public function handleAction() {
		$tradeNo = $this->getPost('tradeNo');
		$handleStatus = $this->getPost('status');
		$withdraw = Withdraw::where('tradeNo', $tradeNo)->first();
		if($withdraw) {
			if($withdraw->status!=3) {
				$rdata['status'] = 0;
				$rdata['msg'] = '该笔提现不可审核！';
				$this->backJson($rdata);
			}
			if($handleStatus==1) {
				$user = User::find($withdraw->userId);
				$transData = base64_encode($withdraw->xml);
				$postTo = Registry::get('config')->get('third')->get('base_url').'/hostingWithdrawCash';
		        $result = NetworkHelper::post($postTo,['transData'=>$transData]);
		        $result = json_decode($result,true);
		        if($result['resCode']=='0000') {
		        	$withdraw->onSuccess(['result'=>$result['resCode']]);
					$rdata['status'] = 1;
					$rdata['msg'] = '审核成功！';
					$this->backJson($rdata);
		        } else {
		        	$withdraw->onFail(['result'=>$result['resCode']]);
		        	$rdata['status'] = 0;
		        	if($result['resCode']=='1009') {
		        		$rdata['msg'] = '审核失败，一麻袋余额不足！';
		        	} else {
		        		$rdata['msg'] = '审核失败，提交一麻袋失败！';
		        	}
					$this->backJson($rdata);
		        }
			} else {
				$withdraw->onFail(['result'=>'refuse']);
	        	$rdata['status'] = 1;
				$rdata['msg'] = '审核成功！';
				$this->backJson($rdata);
			}
		} else {
			$rdata['status'] = 0;
			$rdata['msg'] = '审核失败，该订单不存在！';
			$this->backJson($rdata);
		}
	}

	/**
     * 用户银行卡
     * @return  mixed
     */
	public function banksAction() {
		$this->submenu = 'banks';
		
		$queries = $this->queries->defaults(['searchType'=>'username', 'searchContent'=>'', 'status'=>'all']);
        $searchType = $queries->searchType;
        $searchContent = $queries->searchContent;
        $status = $queries->status;

        $user = null;
        $builder = UserBank::with('user');

        if($searchContent!='') {
	        if($searchType=='username') {
	        	$user = User::where('username', $searchContent)->first();
	        } else if($searchType=='phone') {
	        	$user = User::where('phone', $searchContent)->first();
	        } else if($searchType=='userId') {
	        	$user = User::where('userId', $searchContent)->first();
	        } else if($searchType=='bankNum') {
	        	$builder->where('bankNum', $searchContent);
	        }
        }
        
        if($user) {
        	$builder->where('userId', $user->userId);
        }

		$statusList = ['success'=>1, 'fail'=>0];
        if($status!='all') {
        	$builder->where('status', $statusList[$status]);
        }
        $records = $builder->orderBy('createAt', 'desc')->paginate(20);
        $records->appends($queries->all());

		$this->display('banks', ['records'=>$records, 'queries'=>$queries]);
	}
}