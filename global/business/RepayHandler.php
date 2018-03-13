<?php
namespace business;

use models\Interest;
use models\MoneyLog;
use models\Invest;
use models\Odd;
use models\CustodyBatch;
use models\OddMoney;
use models\Integration;
use models\User;
use helpers\DateHelper;
use custody\Handler;
use custody\API;
use models\Sms;
use tools\Log;
use tools\Redis;
use task\Task;
use task\Handler as BaseHandler;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * 用于还款的工具类
 * params:
 *     oddNumber    要还的标的号
 *     period       要还的期数
 *     type         还款类型 normal advance delay
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class RepayHandler extends BaseHandler {
    private $odd;
    private $repayUser;
    private $oddNumber;
    private $period;
    private $type;
    private $time;
    private $types = ['normal', 'advance', 'delay'];
    private $typeName = '正常还款';
    private $repayName = '借款人';
    private $step;
    private $stayRows = [];
    private $invests;
    private $repay;
    private $siteUrl;
    private $frozenNo;
    private $bailType = 'none';

    public function init() {
        $this->siteUrl = WEB_MAIN;

        $this->oddNumber = isset($this->params['oddNumber'])?$this->params['oddNumber']:'';
        $this->period = isset($this->params['period'])?$this->params['period']:0;
        $this->type = isset($this->params['type'])?$this->params['type']:'normal';
        $this->step = isset($this->params['step'])?$this->params['step']:1;
        $this->replace = isset($this->params['replace'])?$this->params['replace']:0;
        $this->time = date('Y-m-d H:i:s');
        if($this->type=='advance') {
            $this->typeName = '提前还款';
        } else if($this->type=='delay') {
            $this->typeName = '逾期还款';
        }
    }

    public function handle() {
        $result = $this->common();
        if(!$result['status']) {
            return $result;
        }
        if($this->step==1) {
            return $this->post();
        } else {
            return $this->success();
        }
    }

    private function common() {
        Log::write('-------------['.$this->oddNumber.']第'.$this->period.'期'.$this->typeName.'，步骤'.$this->step.'-------------', [], 'repayment');

        $this->odd = Odd::where('oddNumber', $this->oddNumber)->first(['oddNumber', 'fronStatus', 'oddBorrowPeriod', 'oddRepaymentStyle', 'oddBorrowStyle', 'userId', 'isCr', 'receiptUserId']);

        $rdata = [];
        if(!$this->odd) {
            $rdata['msg'] = '标的不存在！';
            $rdata['status'] = false;
            return $rdata;
        }

        if($this->type=='delay'&&$this->odd->oddBorrowPeriod!=$this->period) {
            $rdata['msg'] = '['.$this->oddNumber.']非最后一期还款暂不支持逾期！';
            $rdata['status'] = false;
            return $rdata;
        }

        if($this->type=='advance'&&$this->odd->fronStatus==0) {
            $rdata['msg'] = '['.$this->oddNumber.']请先将标的加入提前还款队列！';
            $rdata['status'] = false;
            return $rdata;
        }

        $statusVal = $this->step==1?0:-1;

        /* 获取借款标，本金和利息 */
        $this->repay = Interest::where('oddNumber', $this->oddNumber)
            // ->where('qishu', $this->period)
            ->where('status', $statusVal)
            ->orderBy('qishu', 'asc')
            ->first(['id', 'benJin', 'interest', 'oddMoneyId', 'zongEr', 'realinterest', 'realAmount', 'addtime', 'endtime', 'subsidy', 'qishu', 'reward', 'replace']);

        if(!$this->repay) {
            $rdata['msg'] = '['.$this->oddNumber.']还款记录不存在或该还款已还！';
            $rdata['status'] = false;
            return $rdata;
        }

        if($this->repay->qishu!=$this->period) {
            $rdata['msg'] = '['.$this->oddNumber.']您前面还有其他期数未还！';
            $rdata['status'] = false;
            return $rdata;
        }

        /*if($this->type!='advance') {
            if(strtotime($this->time) < strtotime(date('Y-m-d 00:00:00', strtotime($this->repay->endtime)))) {
                $rdata['msg'] = '还款时间未到，请勿还款！';
                $rdata['status'] = false;
                return $rdata;
            }
        }*/
        if(!$this->replace){
            $this->replace = $this->repay->replace;
        }
    
        if(isset($this->replace) && $this->replace) {
            $this->repayName = '担保人';
            $this->repayUser = User::where('userId', $this->odd->receiptUserId)->first();
        } else {
            $this->repayName = '借款人';
            $this->repayUser = User::where('userId', $this->odd->userId)->first();
            //$rdata['msg'] = '['.$this->oddNumber.']目前只支持平台代偿，其他还款方式还未开发！';
            //$rdata['status'] = false;
            //return $rdata;
        }
        $this->companyUser = User::where('userId', User::ACCT_DB)->first();

        if(!$this->repayUser) {
            $rdata['msg'] = '['.$this->oddNumber.']还款用户不存在！';
            $rdata['status'] = false;
            return $rdata;
        }

        if($this->step==1 && $this->type=='normal' && $this->repayUser->fundMoney<($this->repay->zongEr-$this->repay->reward)) {
            $rdata['msg'] = '['.$this->oddNumber.']还款帐户余额不足！';
            $rdata['status'] = false;
            return $rdata;
        }

        // if($this->step==1 && $this->type=='normal' && $this->companyUser->fundMoney<($this->repay->reward)) {
        //     $rdata['msg'] = '['.$this->oddNumber.']公司帐户余额不足！';
        //     $rdata['status'] = false;
        //     return $rdata;
        // }

        /* 获取投资者收益列表 */
        $this->invests = Invest::with([
            'user' => function($q) { $q->select('userId', 'name', 'custody_id', 'phone', 'sex', 'integral', 'fundMoney', 'frozenMoney', 'investMoney');}, 
            'oddMoney' => function($q) { $q->select('id', 'authCode', 'tradeNo', 'money', 'remain');}
        ])->where('oddNumber', $this->oddNumber)->where('status', $statusVal)->where('qishu', $this->period)->get();

        $builder = Invest::where('oddNumber', $this->oddNumber)->where('status', $statusVal)->groupBy('oddMoneyId');
        if($this->step==1) {
            $stayList = $builder->get([DB::raw('sum(benJin) as stayCapital'), 'oddMoneyId']);
            foreach ($stayList as $stayItem) {
                $this->stayRows[$stayItem['oddMoneyId']] = [
                    'capital'=>floatval($stayItem['stayCapital']), 
                ];
            }

            // 获取剩余所有待收本金【用于积分计算】
            $stayList = Invest::where('oddNumber', $this->oddNumber)
                ->where('qishu', '>=', $this->period)
                ->groupBy('oddMoneyId')
                ->get([DB::raw('sum(benJin) as stayCapital'), 'oddMoneyId']);
            foreach ($stayList as $stayItem) {
                $this->stayRows[$stayItem['oddMoneyId']]['capitalAll'] = floatval($stayItem['stayCapital']);
            }
        } else {
            // 用于正常还款多期一起还的时候区分于提前还款[测试时用]
            if($this->type=='normal'||$this->type=='delay') {
                $builder->where('qishu', $this->period);
            }
            // 获取当期待收本金、罚息、待收利息、服务费、加息
            $stayList = $builder->get([
                DB::raw('sum(benJin) as stayCapital'), 
                DB::raw('sum(subsidy) as staySubsidy'), 
                DB::raw('sum(realinterest) as stayInterest'), 
                DB::raw('sum(serviceMoney) as stayService'), 
                DB::raw('sum(extra) as stayExtra'), 
                DB::raw('sum(reward) as stayReward'), 
                'oddMoneyId'
            ]);
            foreach ($stayList as $stayItem) {
                $this->stayRows[$stayItem['oddMoneyId']] = [
                    'capital'=>floatval($stayItem['stayCapital']), 
                    'subsidy'=>floatval($stayItem['staySubsidy']),
                    'interest'=>floatval($stayItem['stayInterest']),
                    'service'=>floatval($stayItem['stayService']),
                    'extra'=>floatval($stayItem['stayExtra']),
                    'reward'=>floatval($stayItem['stayReward']),
                ];
            }
        }

        if($this->odd->oddRepaymentStyle=='monthpay') {//$this->replace && 
            if($this->type=='advance' || $this->period == $this->odd->oddBorrowPeriod) {
                $this->bailType = 'last';
            } else {
                $this->bailType = 'normal';
            }
        }

        $rdata['msg'] = '['.$this->oddNumber.']初始化成功！';
        $rdata['status'] = true;
        return $rdata;
    }

    private function post() {
        $delay = 0;
        $realInvestDay = 0;
        $advanceRate = 0;
        if($this->type=='delay') {
            $delay = DateHelper::getIntervalDay($this->time, $this->repay->endtime);
        } else if($this->type=='advance') {
            $realInvestDay = DateHelper::getIntervalDay($this->time, $this->repay->addtime);
            if($this->odd->oddRepaymentStyle=='matchpay') {
                $advanceRate = 0.003;
            }
        }

        $apiData = [];
        $hasFail = false;
        $repayTotal = 0;
        $repayReward = 0;
        $repayService = 0;
        $integralLogs = [];

        $integralRate = Integration::getRate($this->odd->oddBorrowPeriod, $this->odd->oddBorrowStyle);

        DB::beginTransaction();
        foreach ($this->invests as $invest) {
            $apiRow = [];

            // 本金以及利息
            $realCapital = 0;
            $realInterest = 0;
            if($this->type=='advance') {
                $realCapital = $this->stayRows[$invest->oddMoneyId]['capital'];
                $realInterest = round(($invest->interest*($realInvestDay/30)), 2);
                ///$reward = round(($invest->reward*($realInvestDay/30)), 2);
            } else {
                $realCapital = $invest->benJin;
                $realInterest = $invest->interest;
                //$reward = $invest->reward;
            }
            $apiRow['capital'] = $realCapital;
            $apiRow['interest'] = $realInterest;

            // 逾期、提前罚息
            $subsidy = 0;
            $realSubsidy = 0;
            if($this->type=='delay') {
                $subsidy = $invest->benJin*$delay*0.0006;
                $realSubsidy = $subsidy;
            } else if ($this->type=='advance' && $this->odd->oddRepaymentStyle=='matchpay') {
                $subsidy = round($invest->benJin*$advanceRate, 2);
                $realSubsidy = round($realCapital*$advanceRate, 2);
            }
            $apiRow['subsidy'] = $realSubsidy;

            // 实际加息券加息计算
            $extra = $invest->extra;
            if($invest->extra>0 && $this->type=='advance') {
                $realDay = DateHelper::getIntervalDay($invest->endtime, $invest->addtime);
                $extra = $invest->extra*($realInvestDay)/$realDay;
            }

            $reward = $invest->reward;
            if($invest->reward>0 && $this->type=='advance') {
                $realDay = DateHelper::getIntervalDay($invest->endtime, $invest->addtime);
                $reward = $invest->reward*($realInvestDay)/$realDay;
            }
            $apiRow['reward'] = $reward;

            $integralItem = $this->getIntegralLog($invest, $integralRate);
            $integralLogs[] = $integralItem;

            if($integralItem['integral']>0) {
                $invest->user->integral += $integralItem['integral'];
                $status = User::where('userId', $invest->userId)->update([
                    'integral'=>DB::raw('integral+'.$integralItem['integral']),
                ]);

                if($status) {
                    Log::write('更新投资人['.$invest->userId.']帐户积分成功！', [], 'repayment');
                } else {
                    Log::write('更新投资人['.$invest->userId.']帐户积分失败！', [], 'repayment', 'ERROR');
                    $hasFail = true;
                }
            }

            // 利息服务费
            $item = $invest->user->getTenderGrade();
            $item['feePer'] = 0.06;

            // 加息部分收利息服务费
            $serviceMoney = round($item['feePer'] * ($realInterest + $extra), 2);
            
            // 加息部分不收利息服务费
            // $serviceMoney = round($item['feePer'] * $realInterest, 2);
            
            $apiRow['service'] = $serviceMoney;

            $row = [];
            $row['realAmount'] = $invest->benJin + $realInterest;
            $row['realinterest'] = $realInterest;
            $row['serviceMoney'] = $serviceMoney;
            $row['subsidy'] = $subsidy;
            $row['extra'] = $extra;
            $row['status'] = -1;
            $row['operatetime'] = $this->time;
            $status = Invest::where('id', $invest->id)->update($row);
            if($status){
                $apiRow['orderID'] = $invest->getOrderID('pay');
                $apiRow['userId'] = $invest->user->userId;
                $apiRow['authCode'] = $invest->oddMoney->authCode;
                $apiRow['tradeNo'] = $invest->oddMoney->tradeNo;
                $apiRow['orgMoney'] = $invest->oddMoney->money;
                $apiRow['remain'] = $invest->oddMoney->remain;
                $apiRow['extra'] = $extra;
                $apiRow['reward'] = $reward;
                $apiData[] = $apiRow;
                $repayReward += $apiRow['reward'];
                $repayTotal = $repayTotal + $apiRow['subsidy'] + $apiRow['interest'] + $apiRow['capital'];
                $repayService = $repayService + $serviceMoney;
                Log::write('更新投资人['.$invest->userId.']回款表成功！', [], 'repayment');
            } else {
                $hasFail = true;
                Log::write('更新投资人['.$invest->userId.']回款表失败！', [], 'repayment', 'ERROR');
            }
        }

        if($hasFail) {
            DB::rollBack();
            $rdata['msg'] = '['.$this->oddNumber.']更新投资人回款表存在失败，回款失败！';
            $rdata['status'] = false;
            return $rdata;
        }

        // 更新借款表
        $status = false;
        if($this->type=='advance') {
            $curInterest = round(($this->repay->interest*($realInvestDay/30)),2);
            $status = Interest::where('id', $this->repay->id)->update([
                'status'=>-1, 
                'operatetime'=>$this->time, 
                'realAmount'=>DB::raw('benJin+'.$curInterest), 
                'subsidy'=>DB::raw('benJin*'.$advanceRate),
                'realinterest'=>$curInterest,
                'replace'=>$this->replace
            ]);
            $count1 = Interest::where('oddNumber', $this->oddNumber)->where('qishu', '>', $this->period)->update([
                'status'=>-1, 
                'operatetime'=>$this->time, 
                'realAmount'=>DB::raw('benJin'), 
                'subsidy'=>DB::raw('benJin*'.$advanceRate),
                'realinterest'=>0
            ]);
            $count2 = Invest::where('oddNumber', $this->oddNumber)->where('qishu', '>', $this->period)->update([
                'status'=> -1, 
                'operatetime'=>$this->time, 
                'realAmount'=>DB::raw('benJin'), 
                'subsidy'=>DB::raw('benJin*'.$advanceRate),
                'extra'=>0,
                'realinterest'=>0
            ]);
        } else {
            $status = Interest::where('oddNumber', $this->oddNumber)->where('qishu', $this->period)->update([
                'status'=> -1, 
                'operatetime'=>$this->time, 
                'realAmount'=>$this->repay->zongEr, 
                'realinterest'=>$this->repay->interest, 
                'subsidy'=>DB::raw('benJin*'.$delay.'*0.0006'),
                'replace'=>isset($this->replace)?$this->replace:0
            ]);
        }

        if($status){
            Log::write('更新借款人还款表成功！', [], 'repayment');
        }else{
            DB::rollBack();
            $rdata['msg'] = '['.$this->oddNumber.']更新借款人还款表失败！';
            $rdata['status'] = false;
            return $rdata;
        }

        $failCount = Integration::batchInsert($integralLogs);
        if($failCount==0){
            Log::write('更新积分日志成功！', [], 'repayment');
        }else{
            DB::rollBack();
            $rdata['msg'] = '['.$this->oddNumber.']更新积分日志失败！';
            $rdata['status'] = false;
            return $rdata;
        }

        DB::commit();

        $result = [];
        $remark = '['.$this->typeName.']冻结标的@oddNumber{'.$this->oddNumber.'}，第'.$this->period.'期还款：'.$repayTotal.'元';
        $result = API::platformFrozen($repayTotal, $this->repayUser->userId, $this->oddNumber, 'COMPENSATORY', ['remark'=>$remark, 'type'=>'nor-repayment', 'time'=>$this->time]);
        // if($this->replace) {
        // } else {
        //     $remark = '['.$this->typeName.']冻结标的@oddNumber{'.$this->oddNumber.'}，第'.$this->period.'期还款：'.$repayTotal.'元。';
        //     $result = API::frozen($this->repayUser, $repayTotal, ['remark'=>$remark, 'type'=>'nor-repayment', 'time'=>$this->time]);
        // }

        if($result['status']) {
            $this->frozenNo = $result['requestNo'];
            Log::write('请求银行存管冻结金额接口成功！', [], 'repayment');
        } else {
            $rdata['msg'] = '['.$this->oddNumber.']'.$result['msg'];
            $rdata['status'] = false;
            return $rdata;
        }

        $result = $this->api($apiData);
        if($result['status']) {
            Log::write('请求银行存管批次还款接口成功！', [], 'repayment');
            $rdata['status'] = true;
            $rdata['msg'] = '['.$this->oddNumber.']提交信息完成！';
            return $rdata;
        } else {
            $rdata['msg'] = '['.$this->oddNumber.']请求银行存管批次还款接口失败！';
            $rdata['status'] = false;
            return $rdata;
        }
    }

    private function success() {
        $phones = [];
        $moneyLogs = [];
        $redpacks = [];
        $repayCapital = 0;
        $repayInterest = 0;
        $repaySubsidy = 0;
        $repayReward = 0;
        // 仅用于代偿情况下
        $repayService = 0;

        $hasFail = false;
        $apiData = [];
        DB::beginTransaction();
        $acctfee = User::where('userId', User::ACCT_FEE)->first();
        $totalCapital = 0;
        foreach ($this->invests as $invest) {
            $phones[] = $invest->user->phone;
            $capital = $this->stayRows[$invest->oddMoneyId]['capital'];
            $totalCapital += $capital;
            $subsidy = $this->stayRows[$invest->oddMoneyId]['subsidy'];
            $interest = $this->stayRows[$invest->oddMoneyId]['interest'];
            $service = $this->stayRows[$invest->oddMoneyId]['service'];
            $extra = $this->stayRows[$invest->oddMoneyId]['extra'];
            if($invest->extra>0 && $this->type=='advance') {
                $realDay = DateHelper::getIntervalDay($invest->endtime, $invest->addtime);
                $extra = $invest->extra*($realInvestDay)/$realDay;
            }
            $reward = $this->stayRows[$invest->oddMoneyId]['reward'];
            if($invest->reward>0 && $this->type=='advance') {
                $realDay = DateHelper::getIntervalDay($invest->endtime, $invest->addtime);
                $reward = $invest->reward*($realInvestDay)/$realDay;
            }
            $repayReward += $reward;
            $repayCapital += $capital;
            $repaySubsidy += $subsidy;
            $repayInterest += $interest;
            $repayService += $service;
            $changeMoney = $capital + $interest + $subsidy - $service;
            $status = User::where('userId', $invest->userId)->update([
                'fundMoney'=>DB::raw('fundMoney+'.$changeMoney),
                'investMoney'=>DB::raw('investMoney+'.$changeMoney),
            ]);

            if($status) {
                Log::write('更新投资人['.$invest->userId.']帐户金额成功！', [], 'repayment');
            } else {
                Log::write('更新投资人['.$invest->userId.']帐户金额失败！', [], 'repayment', 'ERROR');
                $hasFail = true;
            }

            $debtStatus = 1;
            if($this->type=='advance' || $this->period == $this->odd->oddBorrowPeriod) {
                $debtStatus = 3;
            }
            if($capital>0 || $debtStatus==3) {
                $debtData = [];
                $debtData['remain'] = DB::raw('remain-'.$capital);
                $debtData['status'] = $debtStatus;

                $status = OddMoney::where('id', $invest->oddMoneyId)->update($debtData);
                if($status) {
                    Log::write('更新投资记录['.$invest->oddMoneyId.']剩余本金成功！', [], 'repayment');
                } else {
                    Log::write('更新投资记录['.$invest->oddMoneyId.']剩余本金失败！', [], 'repayment', 'ERROR');
                    $hasFail = true;
                }
            }

            // 加息卷加息，使用红包方式发放
            if($extra>0) {
                $redpacks = [
                    'userId' => $invest->user->userId, 
                    'custody_id' => $invest->user->custody_id,
                    'name' => $invest->user->name,
                    'type' => 'rpk-interest', 
                    'money' => $extra, 
                    'remark' => '['.$this->typeName.']获得标的@oddNumber{'.$this->oddNumber.'}，第'.$invest->qishu.'期加息券加息：'.$extra.'元。',
                    'orderId' => $invest->getOrderID('extra'),
                ];
                $result = API::redpackBatch([$redpacks], $this->oddNumber);
            }
            // 标的加息，使用红包方式发放
            if($reward>0) {
                $redpacks = [
                    'userId' => $invest->user->userId, 
                    'custody_id' => $invest->user->custody_id,
                    'name' => $invest->user->name,
                    'type' => 'rpk-reward', 
                    'money' => $reward, 
                    'remark' => '['.$this->typeName.']获得标的@oddNumber{'.$this->oddNumber.'}，第'.$invest->qishu.'期项目加息：'.$reward.'元。',
                    'orderId' => $invest->getOrderID('reward'),
                ];
                $result = API::redpackBatch([$redpacks], $this->oddNumber);
            }

            $changeMoney += $service;
            $remark = '['.$this->typeName.']获得标的@oddNumber{'.$this->oddNumber.'}，第'.$invest->qishu.'期回款：'.$changeMoney.'元。'
                . '其中本金：'.$capital.'元，利息：'.$interest.'元';
           
            if($subsidy>0) {
                $remark .= '，罚息'.$subsidy.'元。';
            } else {
                $remark .= '。';
            }
            $invest->user->fundMoney += $changeMoney;
            $moneyLogs[] = [
                'type' => 'nor-recvpayment',
                'mode' => 'in',
                'mvalue' => $changeMoney,
                'userId' => $invest->userId,
                'remark' => $remark,
                'remain' => $invest->user->fundMoney,
                'frozen' => $invest->user->frozenMoney,
                'time' => $this->time,
            ];

            // 利息服务费分离
            if($service>0) {
                $invest->user->fundMoney -= $service;
                $feeRemark = '['.$this->typeName.']支出标的@oddNumber{'.$this->oddNumber.'}，第'.$invest->qishu.'期回款利息服务费：'.$service.'元。';
                $moneyLogs[] = [
                    'type' => 'fee-interest',
                    'mode' => 'out',
                    'mvalue' => $service,
                    'userId' => $invest->userId,
                    'remark' => $feeRemark,
                    'remain' => $invest->user->fundMoney,
                    'frozen' => $invest->user->frozenMoney,
                    'time' => $this->time,
                ];

                
                $moneyLogs[] = [
                    'type' => 'fee-interest',
                    'mode' => 'in',
                    'mvalue' => $service,
                    'userId' => User::ACCT_FEE,
                    'remark' => $feeRemark,
                    'remain' => $acctfee->fundMoney,
                    'frozen' => $acctfee->frozenMoney,
                    'time' => $this->time,
                ];
                $acctfee->fundMoney = $acctfee->fundMoney + $service;

            }

            $apiRow = [];
            $apiRow['endOrderID'] = $invest->getOrderID('end');
            $apiRow['custody_id'] = $invest->user->custody_id;
            $apiRow['authCode'] = $invest->oddMoney->authCode;
            $apiData[] = $apiRow;

            if($this->type == 'advance'){
                $msg['phone'] = $invest->user->phone;
                $msg['msgType'] = 'advanceRepay';
                $msg['params'] = [
                                    $invest->user->getPName(),
                                    $this->oddNumber,
                                    round($capital,2),
                                    round($interest+$reward+$extra,2),
                                    $service,
                                    round($capital-$interest+$reward+$extra-$service,2),
                                ];
                $msgs[] = $msg;
            }else{
                if($capital){
                    $msg['userId'] = $invest->user->userId;
                    $msg['phone'] = $invest->user->phone;
                    $msg['msgType'] = 'normalRepay';
                    $msg['params'] = [
                                        $invest->user->getPName(),
                                        $this->oddNumber,
                                        $invest->qishu,
                                        round($capital,2),
                                        round($interest+$reward+$extra,2),
                                        $service,
                                        round($capital-$interest+$reward+$extra-$service,2),
                                    ];
                    $msgs[] = $msg;
                }else{
                    $msg['userId'] = $invest->user->userId;
                    $msg['phone'] = $invest->user->phone;
                    $msg['msgType'] = 'interestRepay';
                    $msg['params'] = [
                                        $invest->user->getPName(),
                                        $this->oddNumber,
                                        $invest->qishu,
                                        round($interest+$reward+$extra,2),
                                        $service,
                                        round($interest+$reward+$extra-$service,2),
                                    ];
                    $msgs[] = $msg;
                    
                }
            }
        }

        if($capital>0 || $debtStatus==3) {
            $debtData = [];
            $debtData['remain'] = DB::raw('remain-'.$totalCapital);
            $debtData['status'] = $debtStatus;
            $status = OddMoney::where('id', $this->repay->oddMoneyId)->update($debtData);
            if($status) {
                Log::write('更新借款记录['.$this->repay->oddMoneyId.']剩余本金成功！', [], 'repayment');
            } else {
                Log::write('更新借款记录['.$this->repay->oddMoneyId.']剩余本金失败！', [], 'repayment', 'ERROR');
                $hasFail = true;
            }
        }


        if($hasFail) {
            DB::rollBack();
            $rdata['msg'] = '['.$this->oddNumber.']更新投资人帐户金额存在失败，回款失败！';
            $rdata['status'] = false;
            return $rdata;
        }

        $builder1 = Interest::where('oddNumber', $this->oddNumber)->where('status', -1);
        $builder2 = Invest::where('oddNumber', $this->oddNumber)->where('status', -1);
        if($this->type=='advance') {
            $builder1->where('qishu', '>=', $this->period);
            $builder2->where('qishu', '>=', $this->period);
        } else {
            $builder1->where('qishu', $this->period);
            $builder2->where('qishu', $this->period);
        }

        $status1 = $builder1->update([
            'status'=> $this->getInterestStatus(), 
            'operatetime'=>$this->time, 
        ]);
        $status2 = $builder2->update([
            'status'=> $this->getInvestStatus(), 
            'operatetime'=>$this->time, 
        ]);

        if($status1 && $status2){
            Log::write('更新借款人还款表成功！', [], 'repayment');
        }else{
            DB::rollBack();
            $rdata['msg'] = '['.$this->oddNumber.']更新借款人还款表失败！';
            $rdata['status'] = false;
            return $rdata;
        }

        $repayChangeMoney = $repayCapital + $repayInterest + $repaySubsidy;
        $realRepayChangeMoney = $repayChangeMoney;// - $repayReward
        // if($this->odd->isCr) {
        //     $repayChangeMoney -= $repayService;
        // }

        $status = User::where('userId', $this->repayUser->userId)->update([
            'frozenMoney'=>DB::raw('frozenMoney-'.$realRepayChangeMoney)
        ]);

        if($status){
            Log::write('更新'.$this->repayName.'['.$this->repayUser->userId.']帐户金额成功！', [], 'repayment');
        }else{
            DB::rollBack();
            $rdata['msg'] = '['.$this->oddNumber.']更新'.$this->repayName.'['.$this->repayUser->userId.']帐户金额失败！';
            $rdata['status'] = false;
            return $rdata;
        }

        if($repayService>0) {
            $status = User::where('userId', User::ACCT_FEE)->update([
                'fundMoney'=>DB::raw('fundMoney+'.$repayService)
            ]);
            if($status){
                Log::write('更新手续费帐户金额成功！', [], 'repayment');
            }else{
                DB::rollBack();
                $rdata['msg'] = '['.$this->oddNumber.']更新手续费帐户金额失败！';
                $rdata['status'] = false;
                return $rdata;
            }
        }

        $remark = '['.$this->typeName.']解冻标的@oddNumber{'.$this->oddNumber.'}，第'.$invest->qishu.'期还款：'.$realRepayChangeMoney.'元。'
            .'其中本金：'.$repayCapital.'元，利息：'.$repayInterest.'元';
        if($repaySubsidy>0) {
            $remark .= '，罚息：'.$repaySubsidy.'元';
        }
        // if($this->odd->isCr) {
        //     $remark .= '（利息服务费：'.$repayService.'元）';
        // }
        $moneyLogs[] = [
            'type' => 'nor-repayment',
            'mode' => 'unfreeze',
            'mvalue' => $realRepayChangeMoney,
            'userId' => $this->repayUser->userId,
            'remark' => $remark,
            'remain' => $this->repayUser->fundMoney,
            'frozen' => $this->repayUser->frozenMoney - $realRepayChangeMoney,
            'time' => $this->time,
        ];
        $failCount = MoneyLog::batchInsert($moneyLogs);
        if($failCount==0){
            Log::write('更新资金日志成功！', [], 'repayment');
        }else{
            DB::rollBack();
            $rdata['msg'] = '['.$this->oddNumber.']更新资金日志失败！';
            $rdata['status'] = false;
            return $rdata;
        }

        if($this->type=='advance' || $this->period == $this->odd->oddBorrowPeriod) {
            $finishType = isset(Odd::$finishTypes[$this->type])?Odd::$finishTypes[$this->type]:1;
            $status = Odd::where('oddNumber', $this->oddNumber)->update([
                'progress'=>'end', 
                'finishType'=>$finishType, 
                'finishTime'=>$this->time
            ]);
            if ($status) {
                Log::write('改变标的progress成功！', [], 'repayment');
            } else {
                DB::rollBack();
                $rdata['msg'] = '['.$this->oddNumber.']改变标的progress失败！';
                $rdata['status'] = false;
                return $rdata;
            }
        }

        DB::commit();

        $key = Redis::getKey('repayIngQueue');
        Redis::sRem($key, $this->repay->id);

        // if(count($redpacks)>0) {
        //     $result = API::redpackBatch($redpacks, $this->oddNumber);
        //     if($result['status']) {
        //         Log::write($result['msg'], [], 'repayment');
        //     } else {
        //         Log::write($result['msg'], [], 'repayment', 'ERROR');
        //     }
        // }

        foreach ($msgs as $key => $value) {
            Sms::send($value);
        }

        /*if($this->type=='advance') {
            $content = '【汇诚普惠】尊敬的用户：您好，您在本平台投资的编号：' . $oddNumber . '项目借款人已提前还款，详情请登录我的账户-投资管理-提前还款进行查看。';
            Task::add('sms', [
                'content' => $content,
                'phone' => implode(',', $phones),
                'type' => 0
            ]);
        }*/

        if ($this->type=='advance' || $this->period == $this->odd->oddBorrowPeriod) {
            $result = API::changeOddStatus($this->oddNumber, 'FINISH');
            if($result['status']) {
                Log::write('请求银行存管截标成功！', [], 'repayment');
            } else {
                $rdata['msg'] = '['.$this->oddNumber.']请求银行存管截标失败！';
                $rdata['status'] = false;
                return $rdata;
            }
        }


        $rdata['msg'] = '['.$this->oddNumber.']还款完成！';
        $rdata['status'] = true;
        return $rdata;
    }

    /**
     * 还款接口
     */
    private function api($list) {
        $mode = 'normal';
        if($this->odd->isCr) {
            $mode = 'bail';
        }
        $info = ['oddNumber'=> $this->oddNumber, 'period'=>$this->period, 'type'=>$this->type, 'frozenNo'=>$this->frozenNo];
        return API::repay($info, $list, $mode);
    }

    /**
     * 获取积分日志
     * @param  Invest $invest 回款实例
     * @param  double $rate   积分比例
     * @return array          积分日志
     */
    private function getIntegralLog($invest, $rate) {
        $principal = $this->stayRows[$invest->oddMoneyId]['capitalAll'];

        $dm = 1;
        if($this->odd->oddBorrowStyle=='week') {
            $dm = 7;
        } else if($this->odd->oddBorrowStyle=='month') {
            $dm = 30;
        }

        //计算投资天数
        $day = DateHelper::getIntervalDay($invest->addtime, date('Y-m-d'))/$dm;

        $integral = ceil($day*$principal*$rate/100)*100;

        $remark = '标的@oddNumber{'.$invest->oddNumber.'}第'.$this->period.'期回款[待收本金:'.$principal.'元]，获得积分'.(intval($integral/100));
        return [
            'ref_id' => $invest->id,
            'userId' => $invest->userId,
            'type' => 'recvpayment',
            'money' => $principal,
            'integral' => $integral,
            'total' => $invest->user->integral + $integral,
            'remark' => $remark,
            'created_at' => $this->time,
            'updated_at' => $this->time
        ];
    }

    private function getInvestStatus() {
        if($this->type=='normal') {
            return 1;
        } else if($this->type=='advance') {
            return 3;
        } else if($this->type=='delay') {
            return 4;
        } else {
            return 0;
        }
    }

    private function getInterestStatus() {
        if($this->type=='normal') {
            return 1;
        } else if($this->type=='advance') {
            return 2;
        } else if($this->type=='delay') {
            return 3;
        } else {
            return 0;
        }
    }
}
