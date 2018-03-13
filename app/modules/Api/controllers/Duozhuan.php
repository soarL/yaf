<?php
/**
 * 多赚平台
 *
 */
use models\Interest;
use models\User;
use models\OddMoney;
use models\Odd;
use models\Invest;

class DuozhuanController extends Controller
{
    public function indexAction()
    {
        $params = $this->getAllPost();
        $page = $params['page'];
        $pagesize = $params['pageSize'];
        $userId = $params['user'];
        $key = 'e6ab138c82531025657ac29135dd726b';
        $token = MD5($page.$userId.$key);
        if($token == $params['token']){
            $userId = User::whereRaw('MD5(userId)=?',[$userId])->first(['userId']);
            $userId = $userId['userId'];
            $totalCount = OddMoney::where('status',1)->where('type','<>','loan')->where('userId',$userId)->count();
            $result = [];
            $result['totalCount'] = $totalCount;
            $result['totalPage'] = floor($result['totalCount']/$pagesize)+1;
            $offset = ($page-1)*$pagesize;
            //投标信息
            $data = OddMoney::where('status','1')->where('type','<>','loan')->where('userId',$userId)->skip($offset)->limit($pagesize)->get(['id','oddNumber','money','time']);
            //积分算 利息费率
            $integral = User::where('userId',$userId)->first(['integral']);
            $integral = $integral['integral'];
            $rate = $this->getServiceLv($integral);
            foreach($data as $value){
                $odd = Odd::where('oddNumber',$value['oddNumber'])->first(['oddTitle','oddYearRate','oddReward','oddBorrowPeriod','oddBorrowStyle','oddRehearTime','oddRepaymentStyle','progress']);
                $recordsList = [];
                $recordsList['rate'] = ($odd['oddYearRate']+$odd['oddReward'])*100;
                $recordsList['time'] = $odd['oddBorrowPeriod'];
                $recordsList['timeflag'] = $odd['oddBorrowStyle']=='month'?1:2;
                $recordsList['workDate'] = $odd['oddRehearTime'];
                $recordsList['progress'] = $odd['progress']=='end'?2:1;
                $recordsList['ptopRecordId'] = $value['oddNumber'];
                $recordsList['flagname'] = $odd['oddTitle'];
                $recordsList['loanUrl'] = 'www.hcjrfw.com/odd/'.$value['oddNumber'];
                $recordsList['money'] = $value['money'];
                $recordsList['repay'] = $odd['oddRepaymentStyle']=='monthpay'?'每月付息到期还本':'等额本息';
                $recordsList['redreward'] = 0;
                $recordsList['investtime'] = $value['time'];
                $recordsList['managementrate'] = $rate*100;
                $recordsList['reward'] = 0;
                $status = 1;
                $ret = Invest::where('oddmoneyId',$value['id'])->get();
                    foreach ($ret as $val) {
                        if (!$status) {
                            continue;
                        }
                        $repays = [];
                        $repays['ptopRepayId'] = $val['id'];
                        if($val['status'] != 0 && $val['status'] != 1){
                            $repays['money'] = $val['realMonery'] - $val['realinterest'];
                            $repays['profit'] = $val['realinterest'];
                        }else{
                            $repays['money'] = $val['benJin'];
                            $repays['profit'] = $val['interest'];
                        }
                        $repays['back'] = $val['endtime'];
                        $repays['state'] = $val['status']==0?1:2;
                        $repays['levels'] = $val['qishu'];
                        $repays['counterfee'] = $val['serviceMoney'];
                        $recordsList['repays'][] = $repays;
                        $recordsList['reward'] += $val['extra'];
                        if($val['status'] != 0 && $val['status'] != 1){
                            $status = 0;
                        }
                    }
                $result['recordsList'][] = $recordsList;
            }
            echo json_encode($result) ;
        }else{
            echo json_encode('出错了!') ;
        }
    }


    /**
     * 计算利息服务费利率
     * @param type $money 投资金额
     * @return type 利息服务费利率
     */
    function getServiceLv($money) {
        if ($money >= 0 AND $money <= 30000) {
            $lv = 0.1;
        } else if ($money > 30000 AND $money <= 150000) {
            $lv = 0.09;
        } else if ($money > 150000 AND $money <= 300000) {
            $lv = 0.08;
        } else if ($money > 300000 AND $money <= 750000) {
            $lv = 0.07;
        } else if ($money > 750000 AND $money <= 1500000) {
            $lv = 0.06;
        } else if ($money > 1500000 AND $money <= 2400000) {
            $lv = 0.05;
        } else if ($money > 2400000 AND $money <= 3500000) {
            $lv = 0.04;
        } else if ($money > 3500000 AND $money <= 6000000) {
            $lv = 0.03;
        } else if ($money > 6000000) {
            $lv = 0.01;
        } else {
            $lv = 0.1;
        }
        return $lv;
    }


    /**
     *多赚开户验证接口
     */
    public function AccountVerifyAction()
    {
        $params = $this->getAllPost();
        $params['data'] = htmlspecialchars_decode($params['data']);
        $sign = strtoupper(md5('method'.$params['method'].'partner_id'.$params['partner_id'].'timestamp'.$params['timestamp']));
        if ($sign == $params['sign']){
            $mobile = json_decode($params['data'],true);
            $phone = $mobile['phone'];
            $result = OddMoney::with(['user'=>function($query){
                $query->select('userId','addtime','phone');
            },'odd'=>function($query){
                $query->select('oddNumber','oddBorrowPeriod','oddYearRate');
            },'lottery'=>function($query){
                $query->select('id','money_rate');
            }])->whereHas('user',function($query)use($phone){
                $query->where('phone',$phone)->where(function ($query){
                    $query->where('channelCode','duozhuanAPP')->orWhere('channelCode','duozhuanPC');
                });
            })->select('id','userId','oddNumber','money','lotteryId','time')->get();

            $firstTimeId = OddMoney::whereHas('user',function($query) use($phone){
                $query->where('phone',$phone);
            })->orderBy('time','asc')->first(['id','userId']);
            $rows = [];
            foreach ($result as $item){
                $row = [];
                $row['register_time'] = $item->user->addtime;
                $row['register_phone'] = $item->user->phone;
                $row['invest_time'] = $item->time;
                $row['invest_money'] = $item->money;
                $row['invest_deadline'] = $item->odd->oddBorrowPeriod.'月标';
                $row['rate_return'] = $item->odd->oddYearRate;
                $row['deductible'] = '';
                $row['rate_hike'] = '';
                $row['cash_back'] = '';
                if (isset($item->lottery)){
                    if ($item->lottery->money_rate >= 1){
                        $row['deductible'] = $item->lottery->money_rate;
                    }else{
                        $row['rate_hike'] = $item->lottery->money_rate;
                    }
                }
                if ($item->id == $firstTimeId['id']){
                    $row['is_first'] = 1;
                }else{
                    $row['is_first'] = 0;
                }
                $rows[] = $row;
            }
            $returnData = [];
            $returnData['status'] = 1;
            $returnData['info'] = 'success';
            $returnData['data'] = $rows;
            $this->backJson($returnData);
        }else{
            $returnData = [];
            $returnData['status'] = 0;
            $returnData['info'] = '验证失败';
            $this->backJson($returnData);
        }
    }


    /**
     *标的投资人的回款记录
     */
    public function bidInvesterRepayAction()
    {
        $params = $this->getAllPost();
        $arr = [];
        $arr['timeStamp'] = $params['timeStamp'];
        $arr['projectId'] = $params['projectId'];
        $arr['key'] = 'duozhuan520';
        $arr['page'] = $params['page'];
        $arr['pageSize'] = $params['pageSize'];
        ksort($arr);
        $arr = http_build_query($arr);
        $sign = md5($arr);
        if ($params['sign'] == $sign) {
            $count = Invest::where('oddNumber',$params['projectId'])->count();
            $result = Invest::with(['user'=>function($query){
                $query->select('userId','username');
            }])->where('oddNumber',$params['projectId'])->forPage($params['page'],$params['pageSize'])->get(['endtime','benJin','interest','status','userId'])->groupBy('userId') ;
            $rows = [];
            foreach ($result as  $key =>$items){
                $row = [];
                $row['subscribeUserName'] = md5($key);
                $row['detail'] = [];
                foreach ($items as $item){
                    $repay = [];
                    $repay['settlementDate'] = strtotime($item->endtime);
                    $repay['money'] = $item->benJin;
                    $repay['interest'] = $item->interest;
                    if ($item->status == 3){
                        $repay['advancePayment'] = 1;
                    }else{
                        $repay['advancePayment'] = 2;
                    }
                    $row['detail'][] = $repay;
                }
                $rows[] = $row;
            }

        $data = [];
            $data['total'] = $count;
            $data['currentPage'] = $params['page'];
            $data['projectId'] = $params['projectId'];
            $data['errorCode'] = 200;
            $data['errorInfo'] = '';
            $data['subscribes'] = $rows;
            $this->backJson($data);
        }else{
            $data = [];
            $data['total'] = 0;
            $data['currentPage'] = $params['page'];
            $data['projectId'] = $params['projectId'];
            $data['errorCode'] = 500;
            $data['errorInfo'] = '签名错误,查无信息';
            $data['subscribes'] = '';
            $this->backJson($data);
        }
    }

    /**
     *标的借款人汇款记录
     */
    public function bidLoanerRepayAction()
    {
        $params = $this->getAllPost();
        $arr = [];
        $arr['timeStamp'] = $params['timeStamp'];
        $arr['projectId'] = $params['projectId'];
        $arr['key'] = 'duozhuan520';
        $arr['page'] = $params['page'];
        $arr['pageSize'] = $params['pageSize'];
        ksort($arr);
        $arr = http_build_query($arr);
        $sign = md5($arr);
        if ($params['sign'] == $sign){
            $count = Interest::where('oddNumber',$params['projectId'])->count();
            $result = Interest::where('oddNumber',$params['projectId'])->with(['odd'=>function($query){
                $query->select('oddNumber','oddMoney','oddRepaymentStyle','addtime');
            }])->forPage($params['page'],$params['pageSize'])->get(['endtime','benJin','interest','status','oddNumber']);
            $rows = [];
            foreach ($result as $item){
                $row = [];
                $row['settlementDate'] = strtotime($item->endtime);
                $row['money'] = $item->benJin;
                $row['interest'] = $item->interest;
                if ($item->status == 2){
                    $row['advancePayment'] = 1;
                }else{
                    $row['advancePayment'] = 2;
                }

                $rows[] = $row;
            }
            $data = [];
            $data['total'] = $count;
            $data['totalMoney'] = $result[0]->odd->oddMoney;
            if ($result[0]->odd->oddRepaymentStyle == 'monthpay'){
                $data['type'] = '按月付息';
            }else{
                $data['type'] = '等额本息';
            }
            $data['isAdvancePayment'] = $rows[0]['advancePayment'];
            $data['currentPage'] = $params['page'];
            $data['projectId'] = $params['projectId'];
            $data['addTime'] = strtotime($result[0]->odd->addtime);
            $data['errorCode'] = 200;
            $data['errorInfo'] = '';
            $data['subscribes'] = $rows;
            $this->backJson($data);
        }else{
            $data = [];
            $data['total'] = 0;
            $data['currentPage'] = $params['page'];
            $data['projectId'] = $params['projectId'];
            $data['errorCode'] = 500;
            $data['errorInfo'] = '签名错误,查无信息';
            $data['subscribes'] = '';
            $this->backJson($data);
        }

    }

}