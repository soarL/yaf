<?php
use models\User;
use models\Crtr;
use models\UserBid;
use models\UserCrtr;
use models\CustodyLog;
use models\CustodyFullLog;
use models\Withdraw;
use models\Recharge;
use custody\Handler;
use custody\CFile;
use custody\API;
use tools\Redis;
use tools\Log;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * CustodyController
 * 存管任务
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class CustodyController extends Controller {
    public function testAction() {
        echo 'haha';
    }

    /**
     * 投标自动补单[* * * * *]
     * 5分钟超时
     * 补10分钟以内的单
     * @return mixed
     */
    public function packBidAction() {
        $time = date('Y-m-d H:i:s', time() - 600);
        $trades = UserBid::where('addTime', '>', $time)->where('status', '0')->get();
        $count = 0;
        foreach ($trades as $trade) {
            $count ++;

            $result = API::query($trade->tradeNo, 'PRETRANSACTION');
            $return = false;

            if($result['status']==API::SUCCESS) {
                if(API::success($result['data'])) {
                    $return = ['status'=>1, 'tradeNo'=>$trade->tradeNo, 'result'=>$result['code']];
                }
            }
            if(time() - strtotime($trade->addTime) > 300) {
                $return = ['status'=>0, 'tradeNo'=>$trade->tradeNo, 'result'=>'TIME_OUT'];
            }
           
            if($return) {
                $rs = UserBid::after($return);
                $this->export('投标['.$trade->tradeNo.']查询补单，结果：'.$rs['msg']);
            } else {
                $this->export('投标['.$trade->tradeNo.']暂不补单');
            }
        }
        if($count==0) {
            $this->export('没有记录！');
        }
    }

    /**
     * 债转自动补单[* * * * *]
     * 15分钟超时
     * 补20分钟以内的单
     * @return mixed
     */
    public function packCrtrAction() {
        $time = date('Y-m-d H:i:s', time() - 2400);
        $trades = UserCrtr::where('addTime', '>', $time)->where('status', '0')->get();
        $count = 0;
        foreach ($trades as $trade) {
            $count ++;
            $data  = [];
            $data['accountId'] = User::getCID($trade->userId);
            $data['orgOrderId'] = $trade->tradeNo;
            $handler = new Handler('creditInvestQuery', $data);
            $result = $handler->api();

            $return = false;
            if($result['retCode']==Handler::SUCCESS) {
                $return = ['status'=>1, 'tradeNo'=>$trade->tradeNo, 'result'=>$result['retCode'], 'authCode'=>$result['authCode']];
            } else {
                if(time() - strtotime($trade->addTime) > 900) {
                    $return = ['status'=>0, 'tradeNo'=>$trade->tradeNo, 'result'=>'XW000002', 'authCode'=>''];
                }
            }
            if($return) {
                $rs = UserCrtr::after($return);
                $this->export('债转['.$trade->tradeNo.']查询补单，结果：'.$rs['msg']);
            } else {
                $this->export('债转['.$trade->tradeNo.']暂不补单');
            }
        }
        if($count==0) {
            $this->export('没有记录！');
        }
    }

    /**
     * 完结债权转让[30 * * * *]
     * @return mixed
     */
    public function finishCrtrAction() {
        $crtrs = Crtr::with(['odd'=>function($q) {
            $q->select(['oddNumber', 'oddBorrowStyle', 'oddRepaymentStyle', 'oddBorrowPeriod', 'oddYearRate', 'oddReward', 'userId']);
        }, 'oddMoney'=>function($q) {
            $q->select(['id', 'money', 'status', 'authCode', 'tradeNo']);
        }, 'user'=>function($q) {
            $q->select(['userId', 'custody_id']);
        }])->where('progress', 'start')->get();

        foreach ($crtrs as $crtr) {
            
            $isFull = ($crtr->successMoney==$crtr->money);
            if(!$isFull&&time()<strtotime($crtr->outtime)) {
                continue;
            }

            if($crtr->finish($isFull)) {
                $this->export('完结债权转让执行成功！执行债转编号：'.$crtr->getSN());
            } else {
                $this->export('完结债权转让执行异常！');
            }
        }
    }

    /**
     * 同步银行全流水[0 3 * * *]
     * @return mixed
     */
    public function syncFullLogsAction() {
        $date = date('Ymd', time() - 24*60*60*1);
        $result = API::syncFullLogs($date);
        $this->export($result['msg']);
    }

    /**
     * 同步银行流水[30 3 * * *]
     * @return mixed
     */
    public function syncLogsAction() {
        $date = date('Ymd', time() - 24*60*60*1);
        $result = API::syncLogs($date);
        $this->export($result['msg']);
    }

    /**
     * 提现补单
     */
    public function packWithdrawAction() {
        $time = date('Y-m-d H:i:s', time()-20*60);
        $timeFrom = date('Y-m-d H:i:s', time()-24*60*60);
        $records = Withdraw::where(function($q) {
                $q->where('status', 0)->orWhere(function($q) {
                    $q->where('status', 1)->whereIn('result', Withdraw::$unknowCodes);
                });
            })
            ->where('addTime', '>=', $timeFrom)
            ->where('addTime', '<=', $time)
            ->limit(10)
            ->get();
        foreach ($records as $record) {
            $tradeNo = $record->tradeNo;
            $data  = [];
            $data['accountId'] = User::getCID($record->userId);
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
        }
    }

    /**
     * 充值补单
     */
    public function packRechargeAction() {
        $time = date('Y-m-d H:i:s', time()-20*60);
        $timeFrom = date('Y-m-d H:i:s', time()-24*60*60);
        $records = Recharge::where('status', '0')
            ->where('addTime', '>=', $timeFrom)
            ->where('addTime', '<=', $time)
            ->where('payWay', 'T')
            ->limit(10)
            ->get();
        foreach ($records as $record) {
            $serialNumber = $record->serialNumber;
            $data  = [];
            $data['accountId'] = User::getCID($record->userId);
            $data['orgTxDate'] = substr($serialNumber, 0, 8);
            $data['orgTxTime'] = substr($serialNumber, 8, 6);
            $data['orgSeqNo'] = substr($serialNumber, 14);
            $handler = new Handler('fundTransQuery', $data);
            $result = $handler->api();
            $status = 0;
            if($result['retCode']==Handler::SUCCESS) {
                if($result['orFlag']!=1) {
                    $status = 1;
                }
            }
            
            $return = [];
            $return['tradeNo'] = $serialNumber;
            $return['result'] = $result['retCode'];
            $return['status'] = $status;
            Recharge::after($return);
        }
    }
}