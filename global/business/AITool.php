<?php
namespace business;

use models\Odd;
use models\User;
use models\AutoInvest;
use models\OddMoney;
use models\MoneyLog;
use models\Lottery;
use models\Redpack;
use tools\Log;
use tools\Redis;
use custody\Handler;
use custody\API;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * AITool自动投标工具类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class AITool {

    public static $rpUser;
    public static $rpChange = 0;

    /**
     * 开始自动投标
     * @param  models\Odd $odd 标的对象
     * @return array           结果信息
     */
    public static function run($odd) {
        $ingKey = Redis::getKey('autoInvesting');
        $ing = Redis::get($ingKey);
        $rdata = [];
        if($ing) {
            $rdata['status'] = false;
            $rdata['msg'] = '标的['.$ing.']正在自动投标，请稍后再试！';
            return $rdata;
        } else {
            Redis::set($ingKey, $odd->oddNumber);
        }

        self::$rpUser = User::where('username', User::ACCT_RP)->first(['userId', 'custody_id', 'fundMoney', 'frozenMoney']);

        Odd::where('oddNumber', $odd->oddNumber)->where('isATBiding', 0)->update(['isATBiding'=>1, 'lookstatus'=>1]);
        
        Log::write('标的['.$odd->oddNumber.']自动投标开始！', [], 'autoInvest');

        $maxPercent = 1;
        // 计算最大投资比率
        // if($odd->oddMoney<=30000) {
        //     $maxPercent = 0.50;
        // } else if ($odd->oddMoney<=100000) {
        //     $maxPercent = 0.25;
        // } else {
        //     $maxPercent = 0.20;
        // }
        // if($odd->oddBorrowPeriod>6) {
        //     $maxPercent = 1;
        // }
        $remain = $odd->getRemain();
        $key = Redis::getKey('autoInvestQueue');
        $order = 0;
        $failUsers = [];
        $oddMoneys = [];
        $userMoneys = [];
        $logs = [];
        $redpacks = [];
        $successMoney = 0;
        $investCount = [];
        $useLotteryUsers = [];

        $que_length = Redis::lLEN($key);

        $continue = 1;
        while ($continue == 1){
            $continue = 0;
            $endUser = [];
            for ($que_index=$que_length; $que_index > 0; $que_index--) { 
                $userId = Redis::lIndex($key,$que_index-1);
                if(in_array($userId, $failUsers)){
                    continue;
                }
                Log::write('用户['.$userId.']开始投资！', [], 'autoInvest');
                $order ++;
                $time = date('Y-m-d H:i:s');

                if($remain<50) {
                    $failUsers[] = $userId;
                    Log::write('标的金额不足！', [], 'autoInvest');
                    break;
                }

                if($userId==$odd->userId) {
                    $failUsers[] = $userId;
                    Log::write('用户['.$userId.']不能投自己的标的！', [], 'autoInvest');
                    continue;
                }

                // if(isset($investCount[$userId]) && $investCount[$userId]>=3) {
                //     $failUsers[] = $userId;
                //     Log::write('用户['.$userId.']投资该标的超过3次，不能再投！', [], 'autoInvest');
                //     continue;
                // }
                $autoInvest = AutoInvest::with([
                    'user'=>function($q) { $q->select(['userId', 'auto_bid_auth', 'fundMoney', 'frozenMoney', 'custody_id']);},
                    'lottery'=>function($q) { $q->select('id', 'type', 'status', 'money_rate', 'money_lower', 'money_uper', 'period_lower', 'period_uper', 'userId');}
                ])->where('userId', $userId)->first();

                if(!$autoInvest || !$autoInvest->user) {
                    $failUsers[] = $userId;
                    Log::write('用户['.$userId.']自动投标设置不存在或对应用户不存在！', [], 'autoInvest');
                    continue;
                }

                if($autoInvest->autostatus==0 || $autoInvest->staystatus==1) {
                    $failUsers[] = $userId;
                    Log::write('用户['.$userId.']自动投标状态关闭或占队不投！', [], 'autoInvest');
                    continue;
                }

                if($autoInvest->investMoneyLower>$autoInvest->investMoneyUper) {
                    $failUsers[] = $userId;
                    Log::write('用户['.$userId.']设置的最低投资金额大于最高投资金额！', [], 'autoInvest');
                    continue;
                }

                
                $typeMatch = false;
                $types = $autoInvest->getTypes();
                foreach ($types as $type) {
                    if($type['period']==$odd->oddBorrowPeriod && $type['periodType']==$odd->oddBorrowStyle && $type['type']==$odd->oddType){
                        $typeMatch = true;
                        break;
                    }
                }

                if(!$typeMatch) {
                    $failUsers[] = $userId;
                    Log::write('用户['.$userId.']投标类型不匹配！--'.$autoInvest->types.'--', [], 'autoInvest');
                    continue;
                }

                $canAccountMoney = $autoInvest->user->fundMoney - $autoInvest->investEgisMoney;

                if($autoInvest->investMoneyUper!=-1 && $canAccountMoney>$autoInvest->investMoneyUper) {
                    $canAccountMoney = $autoInvest->investMoneyUper;
                }
                //$minInvestMoney = ($autoInvest->investMoneyLower<50)?50:$autoInvest->investMoneyLower;
                $minInvestMoney = 50;

                $canOddMoney = floatval($odd->oddMoney * $maxPercent);
                $maxInvestMoney = $canAccountMoney<$canOddMoney?$canAccountMoney:$canOddMoney;

                if($maxInvestMoney<$minInvestMoney) {
                    $failUsers[] = $userId;
                    Log::write('用户['.$userId.']最低可投金额大于最高可投金额！', [], 'autoInvest');
                    continue;
                }
                if($remain<$minInvestMoney) {
                    $failUsers[] = $userId;
                    Log::write('用户['.$userId.']最低可投金额大于剩余可投金额！', [], 'autoInvest');
                    continue;
                }

                if($autoInvest->status == '1' && $canAccountMoney >= $autoInvest->investMoneyLower){
                    $autoInvest->status = 2;
                    $autoInvest->total = $canAccountMoney;
                    $totalMoney = $canAccountMoney;
                }elseif($autoInvest->status == '2'){
                    $totalMoney = $autoInvest->total - $autoInvest->successMoney;
                    if($totalMoney < 50){
                         $failUsers[] = $userId;
                        Log::write('用户['.$userId.']最低可投金额大于剩余可投金额！', [], 'autoInvest');
                        continue;  
                    }
                    $userCanMoney = $autoInvest->user->fundMoney - $autoInvest->investEgisMoney;
                    if($totalMoney > $userCanMoney){
                        $totalMoney = $userCanMoney;
                    }
                    if($totalMoney < $maxInvestMoney){
                        $maxInvestMoney = $totalMoney;
                    }
                }else{
                    $failUsers[] = $userId;
                    Log::write('用户['.$userId.']stauts异常！', [], 'autoInvest');
                    continue;
                }

                // 获取投资倍数
                $multiple = 50; 
                $investMoney = ($remain>$maxInvestMoney)?$maxInvestMoney:$remain;
                $num = floor($investMoney/$multiple);
                $money = $num*$multiple;

                // 奖券使用
                $result = self::useLottery($autoInvest, $odd, $money, $totalMoney);
                if($result['status']==0 && $autoInvest->lottery_id>0){
                    $failUsers[] = $userId;
                    continue;
                }elseif($result['status']==-1 && $autoInvest->lottery_id==0){
                    $failUsers[] = $userId;
                    continue;
                }
                Log::write($result['msg'], [], 'autoInvest');
                // 抵扣红包
                $rpMoney = 0;
                $lotteryId = 0;
                if($result['status']==1) {
                    $lotteryId = $result['data']['id'];
                    if($autoInvest->lottery_id>0){
                        $autoInvest->lottery_id = -1;
                        //$useLotteryUsers[] = $userId;
                    }
                    if($result['data']['type']=='invest_money') {
                        $rpMoney = $result['data']['money'];
                        $remark = '投资标的@oddNumber{'.$odd->oddNumber.'}获得抵扣红包'.$rpMoney.'元';
                        $redpacks[] = [
                            'userId' => $autoInvest->userId,
                            'money' => $rpMoney,
                            'remark' => $remark,
                            'type' => 'rpk-investmoney',
                            'status' => 1,
                            'addtime' => $time,
                            'orderId' => $result['data']['orderId']
                        ];
                        $autoInvest->user->fundMoney += $rpMoney;
                        $logs[] = [
                            'userId'=> $autoInvest->userId,
                            'type'=> 'rpk-investmoney',
                            'mode'=> 'in',
                            'mvalue'=> $rpMoney,
                            'remark'=> $remark,
                            'remain'=> $autoInvest->user->fundMoney,
                            'frozen'=> $autoInvest->user->frozenMoney,
                            'time'=> $time
                        ];
                    }
                }
                //Log::write($result['msg'], [], 'autoInvest');

                $result = API::autoBid($userId, $odd->oddNumber, $money, $rpMoney);
                if(!$result['status']) {
                    $failUsers[] = $userId;
                    Log::write($result['msg'], [], 'autoInvest', 'ERROR');
                    continue;
                }

                $continue = 1;
                $autoInvest->successMoney += $money;

                if(($autoInvest->total - $autoInvest->successMoney) < 50){
                    $autoInvest->status = 1;
                    $autoInvest->total = 0;
                    $autoInvest->successMoney = 0;
                    // $autoInvest->save();
                    $endUser[] = $userId;
                }
                $autoInvest->save();

                $remain = $remain - $money;
                $successMoney += $money;

                $oddMoneys[] = [
                    'oddNumber' => $odd->oddNumber,
                    'type' => 'invest',
                    'money' => $money,
                    'remain' => $money,
                    'userId' => $userId,
                    'remark' => '自动投标',
                    'time' => $time,
                    'status' => '0',
                    'tradeNo' => $result['requestNo'],
                    'authCode' => '',
                    'order' => $order,
                    'lotteryId' => $lotteryId,
                ];

                $autoInvest->user->fundMoney -= $money;
                $autoInvest->user->frozenMoney += $money;
                $logs[] = [
                    'type' => 'nor-tender',
                    'mode' => 'freeze',
                    'mvalue' => $money,
                    'userId' => $userId,
                    'remark' => '[自动]投资标的@oddNumber{'.$odd->oddNumber.'},冻结'.$money.'元',
                    'remain' => $autoInvest->user->fundMoney,
                    'frozen' => $autoInvest->user->frozenMoney,
                    'time' => $time
                ];

                $status = User::where('userId', $autoInvest->userId)->update([
                    'fundMoney' => DB::raw('fundMoney-'.($money-$rpMoney)),
                    'frozenMoney' => DB::raw('frozenMoney+'.$money),
                ]);

                if($status){
                    if(isset($investCount[$userId])) {
                        $investCount[$userId] += 1;
                    } else {
                        $investCount[$userId] = 1;
                    }
                    Log::write('用户['.$userId.']成功投出'.$money.'元！', [], 'autoInvest');
                } else {
                    Log::write('用户['.$userId.']成功投出'.$money.'元，但是更新金额失败！', [], 'autoInvest', 'ERROR');
                }
            }

            Log::write('一轮结束！', [], 'autoInvest');
            foreach ($endUser as $value) {
                Redis::lRem($key,$value, 0);
                Redis::lPush($key, $value);
            }
        }



        // AutoInvest::whereIn('userId', $useLotteryUsers)->update(['lottery_id'=>'-1']);

        $status = OddMoney::insert($oddMoneys);
        if(!$status) {
            Log::write('标的['.$odd->oddNumber.']插入OddMoney数据有问题！', $oddMoneys, 'autoInvest', 'ERROR');
        }

        $status = MoneyLog::insert($logs);
        if(!$status) {
            Log::write('标的['.$odd->oddNumber.']插入MoneyLog数据有问题！', $logs, 'autoInvest', 'ERROR');
        }

        if(count($redpacks)>0) {
            $status = User::where('userId', self::$rpUser->userId)->update([
                'fundMoney' => DB::raw('fundMoney-'.self::$rpChange)
            ]);
            MoneyLog::log(User::ACCT_RP, 'nor-tender', 'out', self::$rpChange, '标的['.$odd->oddNumber.'],自动投标红包'.self::$rpChange.'元');
            if(!$status) {
                Log::write('修改红包账户资金异常！', [], 'autoInvest', 'ERROR');
            }

            $status = Redpack::insert($redpacks);
            if(!$status) {
                Log::write('插入红包数据有问题！', $redpacks, 'autoInvest', 'ERROR');
            }
        }

        for($i=count($failUsers); $i>0; $i--) { 
            //Redis::rPush($key, $failUsers[$i-1]);
        }

        $updateData = ['successMoney'=>DB::raw('successMoney+'.$successMoney), 'isATBiding'=>0];
        if($remain==0) {
            $updateData['fullTime'] = date('Y-m-d H:i:s');
        }
        $remainKey = Redis::getKey('oddRemain', ['oddNumber'=>$odd->oddNumber]);
        Redis::decr($remainKey, intval(bcmul($successMoney, 100)));

        $status = Odd::where('oddNumber', $odd->oddNumber)->update($updateData);
        if(!$status) {
            Log::write('标的['.$odd->oddNumber.']更新标的状态失败！', [], 'autoInvest', 'ERROR');
        }

        Redis::delete($ingKey);

        Log::write('标的['.$odd->oddNumber.']自动投标结束！', [], 'autoInvest');
        Log::write('---------------------------------', [], 'autoInvest');

        $rdata['status'] = true;
        $rdata['msg'] = '标的['.$odd->oddNumber.']自动投标完成！';
        return $rdata;
    }

    private static function useLottery($autoInvest, $odd, $money, $totalMoney) {
        $rdata = [];
        if($autoInvest->lottery_id > 0){
            if($autoInvest->lottery) {
                $lottery = $autoInvest->lottery;
                if($lottery->status<>Lottery::STATUS_FROZEN) {
                    $rdata['status'] = 0;
                    $rdata['msg'] = '用户['.$autoInvest->userId.']使用券['.$lottery->id.']状态异常！';
                    return $rdata;
                } else if(!$lottery->checkPeriod($odd->oddBorrowPeriod) || !$lottery->checkMoney($money)) {
                    $rdata['status'] = 0;
                    $rdata['msg'] = '用户['.$autoInvest->userId.']使用券['.$lottery->id.']不符合使用条件！';
                    return $rdata;
                }
                $count = Lottery::where('id', $lottery->id)->where('status',Lottery::STATUS_FROZEN)->update(['status'=>Lottery::STATUS_USED, 'used_at'=>date('Y-m-d H:i:s')]);
                if($count==0) {
                    $rdata['status'] = 0;
                    $rdata['msg'] = '用户['.$autoInvest->userId.']使用券['.$lottery->id.']更新状态失败！';
                    return $rdata;
                }
                if($lottery->type=='invest_money') {
                    $remark = '投资获得抵扣红包'.$lottery->money_rate.'元';
                    $data = [];
                    $data['accountId'] = self::$rpUser->custody_id;
                    $data['amount'] = $lottery->money_rate;
                    $data['forAccountId'] = $autoInvest->user->userId;
                    $data['desLineFlag'] = '1';
                    $data['desLine'] = $remark;
                    $handler = new Handler('RED_PACKET', $data);
                    $result = $handler->api();
                    if($result['retCode']==Handler::SUCCESS) {
                        self::$rpChange += $lottery->money_rate;
                        $rdata['status'] = 1;
                        $rdata['msg'] = '用户['.$autoInvest->userId.']使用券['.$lottery->id.']成功！';
                        $rdata['data']['id'] = $lottery->id;
                        $rdata['data']['type'] = $lottery->type;
                        $rdata['data']['money'] = $lottery->money_rate;
                        $rdata['data']['orderId'] = $handler->getSN();
                        return $rdata;
                    } else {
                        $rdata['status'] = 0;
                        $rdata['msg'] = '用户['.$autoInvest->userId.']使用券['.$lottery->id.']使用失败，错误代码['.$result['retCode'].']！';
                        return $rdata;
                    }
                } else {
                    $rdata['status'] = 1;
                    $rdata['msg'] = '用户['.$autoInvest->userId.']使用券['.$lottery->id.']成功！';
                    $rdata['data']['id'] = $lottery->id;
                    $rdata['data']['type'] = $lottery->type;
                    return $rdata;
                }
            }
            $rdata['status'] = 0;
            $rdata['msg'] = '用户['.$autoInvest->userId.']设置奖券与该标的不符！';
            return $rdata;
        }elseif($autoInvest->lottery_id == 0){
            $lotterys = Lottery::where('userId',$autoInvest->userId)->where('status',Lottery::STATUS_NOUSE)->where('endtime','>',date('Y-m-d H:i:s'))->orderBy('money_rate','desc')->orderBy('money_lower','asc')->get();//->orderBy('period_lower','desc')
            foreach ($lotterys as $key => $lottery) {
                if($lottery->status<>Lottery::STATUS_NOUSE) {
                    continue;
                }

                if($lottery->checkMoney($totalMoney) && !$lottery->checkMoney($money)){
                    $rdata['status'] = -1;
                    $rdata['msg'] = '用户['.$autoInvest->userId.']设置奖券与该标的不符！';
                    return $rdata;
                }

                if(!$lottery->checkPeriod($odd->oddBorrowPeriod) || !$lottery->checkMoney($money)) {
                    continue;
                }
                $count = Lottery::where('id', $lottery->id)->where('status',Lottery::STATUS_NOUSE)->update(['status'=>Lottery::STATUS_USED, 'used_at'=>date('Y-m-d H:i:s')]);
                if($count==0) {
                    continue;
                }
                if($lottery->type=='invest_money') {
                    $remark = '投资获得抵扣红包'.$lottery->money_rate.'元';
                    $data = [];
                    $data['accountId'] = self::$rpUser->custody_id;
                    $data['amount'] = $lottery->money_rate;
                    $data['forAccountId'] = $autoInvest->user->userId;
                    $data['desLineFlag'] = '1';
                    $data['desLine'] = $remark;
                    $handler = new Handler('RED_PACKET', $data);
                    $result = $handler->api();
                    if($result['retCode']==Handler::SUCCESS) {
                        self::$rpChange += $lottery->money_rate;
                        $rdata['status'] = 1;
                        $rdata['msg'] = '用户['.$autoInvest->userId.']使用券['.$lottery->id.']成功！';
                        $rdata['data']['id'] = $lottery->id;
                        $rdata['data']['type'] = $lottery->type;
                        $rdata['data']['money'] = $lottery->money_rate;
                        $rdata['data']['orderId'] = $handler->getSN();
                        return $rdata;
                    } else {
                        $rdata['status'] = 0;
                        $rdata['msg'] = '用户['.$autoInvest->userId.']使用券['.$lottery->id.']使用失败，错误代码['.$result['retCode'].']！';
                        return $rdata;
                    }
                } else {
                    $rdata['status'] = 1;
                    $rdata['msg'] = '用户['.$autoInvest->userId.']使用券['.$lottery->id.']成功！';
                    $rdata['data']['id'] = $lottery->id;
                    $rdata['data']['type'] = $lottery->type;
                    return $rdata;
                }
            }
            $rdata['status'] = 0;
            $rdata['msg'] = '用户['.$autoInvest->userId.']未使用奖券！';
        }else{
            $rdata['status'] = 0;
            $rdata['msg'] = '用户['.$autoInvest->userId.']未使用奖券！';
            return $rdata;
        }
        return $rdata;
    }

    private static function api($oddNumber, $autoInvest, $money) {
        $data = [];
        $data['accountId'] = $autoInvest->user->custody_id;
        $data['orderId'] = Handler::SEQ_PL;
        $data['txAmount'] = $money;
        $data['productId'] = _ntop($oddNumber);
        $data['frzFlag'] = '1'; // 冻结
        $data['contOrderId'] = $autoInvest->user->auto_bid_auth;
        $handler = new Handler('bidAutoApply', $data);
        $tradeNo = $handler->getSN();
        $result = $handler->api();
        
        $rdata = [];
        if($result['retCode']==Handler::SUCCESS) {
            $rdata['status'] = true;
            $rdata['msg'] = '用户['.$autoInvest->userId.']自动投标发送银行存管成功！';
            $rdata['data']['tradeNo'] = $tradeNo;
            $rdata['data']['authCode'] = $result['authCode'];
        }else{
            $rdata['status'] = false;
            $rdata['msg'] = '用户['.$autoInvest->userId.']自动投标发送银行存管失败，retCode['.$result['retCode'].']！';
        }
        return $rdata;
    }

    public static function runBatch($list) {
        if(count($list)==0) {
            return [
                'status' => true,
                'msg' => '标的号数量不能为0'
            ];
        }
        
        Odd::whereIn('oddNumber', $list)->where('isATBiding', 0)->update(['isATBiding'=>1, 'lookstatus'=>1]);

        $odds = Odd::whereIn('oddNumber', $list)->get([
            'oddNumber', 
            'oddYearRate', 
            'investType',
            'oddMoney',
            'oddType',
            'userId',
            'oddBorrowValidTime',
            'oddBorrowPeriod',
            'oddBorrowStyle',
            'progress',
            'isCr',
            'receiptUserId',
        ]);

        $msgList = [];
        foreach ($odds as $odd) {
            $result = self::run($odd);
            $msgList[] = $result['msg'];
        }
        return [
            'status' => true,
            'msg' => implode(' | ', $msgList)
        ];
    }
}
