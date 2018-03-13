<?php
namespace custody;

use Yaf\Registry;
use tools\Log;
use tools\BankCard;
use tools\Redis;
use models\UserBid;
use models\User;
use models\UserBank;
use models\Redpack;
use models\MoneyLog;
use models\OddMoney;
use models\CustodyBatch;
use models\RedpackBatch;
use models\Odd;
use models\SyncLog;
use models\Recharge;
use models\CustodyFullLog;
use models\CustodyLog;
use models\DegWithdraw;
use task\Task;
use helpers\NetworkHelper;
use tools\Counter;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * API
 * 封装一些通用的存管接口方法
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class API {

    const SIGN_KEY = 'sign';
    const SUCCESS = '00000';
    const API_TOKEN = 'liuchenhui123';

    /**
     * 获取URL
     * @return string URL
     */
    public static function getUrl() {
        $custodyApi = Registry::get('config')->get('custody.api');
        return $custodyApi . '/index.php?r=bank';
    }

    public static function verify($params) {
        if(!isset($params[self::SIGN_KEY])) {
            return false;
        }
        $sign = $params[self::SIGN_KEY];
        //$sign = base64_decode($sign);
        unset($params[self::SIGN_KEY]);
        ksort($params);
        reset($params);
        $string = '';
        foreach ($params as $key => $value) {
            $string .= $value;
        }
        $realSign = md5($string.self::API_TOKEN);
        return $realSign == $sign;
    }

    public static function success($data, $only=false) {
        if($only) {
            if($data['respCode']==self::SUCCESS && $data['status']=='SUCCESS') {
                return true;
            }
        }
        if($data['respCode']==self::SUCCESS && ($data['status']=='SUCCESS' || $data['status']=='ONSALE' || $data['status']=='ACCEPT')) {
            return true;
        }
        return false;
    }
    
    /**
     * 同步更新银行卡信息
     * @return boolean 是否成功
     */
    public static function refreshUserBank(User $user) {
        $data = [];
        $data['accountId'] = $user->custody_id;
        $data['state'] = '1';

        $handler = new Handler('cardBindDetailsQuery', $data);
        $result = $handler->api();

        Log::write('[REFRESH_USER_BANK]绑卡关系查询同步返回', $result, 'custody');

        $rdata = [];
        if($result['retCode']==Handler::SUCCESS) {
            $list = json_decode($result['subPacks'], true);
            if(count($list)>0) {
                $realBankCard = $list[0];
                $bankCard = UserBank::where('userId', $user->userId)->where('status', 1)->first();
                if(!$bankCard||$bankCard->bankNum!=$realBankCard['cardNo']) {
                    UserBank::where('userId', $user->userId)->where('status', 1)->update(['status'=>0]);
                    $binInfo = BankCard::getBinInfo($realBankCard['cardNo']);

                    $bankCard = new UserBank();
                    $bankCard->userId = $user->userId;
                    $bankCard->bankNum = $realBankCard['cardNo'];
                    $bankCard->bank = 0;
                    $bankCard->binInfo = $binInfo;
                    $bankCard->bankUsername = $user->name;
                    $bankCard->status = 1;
                    if($bankCard->save()) {
                        $rdata['status'] = 1;
                        $rdata['msg'] = '同步成功！';
                    } else {
                        $rdata['status'] = 0;
                        $rdata['msg'] = '同步异常，请联系客服！';
                    }
                } else {
                    $rdata['status'] = 1;
                    $rdata['msg'] = '您的银行卡信息与存管一致！';
                }
            } else {
                UserBank::where('userId', $user->userId)->where('status', 1)->update(['status'=>0]);
                $rdata['status'] = 1;
                $rdata['msg'] = '同步成功，您暂时还没有绑定银行卡！';
            }
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $result['retMsg'];
        }
        return $rdata;
    }
    
    /**
     * 撤销投标申请
     * @return array 返回信息
     */
    public static function bidCancel(UserBid $order) {
        $requestNo = Counter::getOrderID();
        $data = [];
        $data['serviceName'] = 'CANCEL_PRE_FREEZE';
        $data['requestNo'] = $requestNo;
        $data['preRequestNo'] = $order->tradeNo;
        $data['amount'] = $order->bidMoney;

        $url = self::getUrl();
        $result = NetworkHelper::post($url, $data);
        $result = json_decode($result, true);

        Log::write('[BID_CANCEL]撤销投标申请同步返回', $result, 'custody');
        
        UserBid::after(['status'=>0, 'tradeNo'=>$order->tradeNo, 'result'=>'CANCLE']);

        $rdata = [];
        if($result['respCode']==self::SUCCESS) {
            $rdata['status'] = 1;
            $rdata['msg'] = '撤销投标成功！';
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $result['respMsg'];
        }

        return $rdata;
    }

    /**
     * 债权撤销
     * @return array 返回信息
     */
    public static function debtCancel(OddMoney $oddMoney) {
        $data = [];
        $data['accountId'] = User::getCID($oddMoney->userId);
        $data['orderId'] = Handler::SEQ_PL;
        $data['txAmount'] = $oddMoney->money;
        $data['productId'] = _ntop($oddMoney->oddNumber);
        $data['orgOrderId'] = $oddMoney->tradeNo;
        $data['acqRes'] = '';

        $handler = new Handler('bidCancel', $data);
        $result = $handler->api();

        $oddMoney->status = 4;

        $rdata = [];
        if($result['retCode']==Handler::SUCCESS && $oddMoney->save()) {

            User::where('userId', $oddMoney->userId)->update([
                'frozenMoney'=>DB::raw('frozenMoney-'.$oddMoney->money),
                'fundMoney'=>DB::raw('fundMoney+'.$oddMoney->money)
            ]);

            $remark = '撤销投资标的@oddNumber{'.$oddMoney->oddNumber.'}冻结的'.$oddMoney->money.'元';
            MoneyLog::log($oddMoney->userId, 'nor-cancel-tender', 'cancel', $oddMoney->money, $remark);

            Odd::where('oddNumber', $oddMoney->oddNumber)->update(['frozenMoney'=>DB::raw('successMoney-'.$oddMoney->money)]);
            Odd::disBid($oddMoney->oddNumber, $oddMoney->money);

            $rdata['status'] = 1;
            $rdata['msg'] = '债权撤销成功！';
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $result['retMsg'];
        }
        return $rdata;
    }

    /**
     * 发放红包
     * @return array 返回信息
     */
    public static function redpack($userId, $money, $type, $remark='') {
        $rpUser = User::where('userId', User::ACCT_RP)->first(['userId', 'fundMoney']);
        $rdata = [];
        if($rpUser->fundMoney<$money) {
            $rdata['status'] = 0;
            $rdata['msg'] = '红包账户余额不足！';
            $rdata['code'] = 'XW000003';
            return $rdata;
        }

        if($remark=='') {
            $remark = '发放红包'.$money.'元';
        }

        $requestNo = Counter::getOrderID();
        $data = [];
        $data['serviceName'] = 'RED_PACKET';
        $data['requestNo'] = $requestNo;
        $data['amount'] = $money;
        $data['userNo'] = $userId;
        
        $result = self::post($data);

        if(self::success($result)) {
            $redpack = new Redpack();
            $redpack->userId = $userId;
            $redpack->money = $money;
            $redpack->remark = $remark;
            $redpack->type = $type;
            $redpack->status = 1;
            $redpack->addtime = date('Y-m-d H:i:s');
            $redpack->orderId = $requestNo;
            $redpack->save();
            
            User::where('userId', $userId)->update(['fundMoney'=>DB::raw('fundMoney+'.$money),'withdrawMoney'=>DB::raw('withdrawMoney+'.$money)]);
            User::where('userId', User::ACCT_RP)->update(['fundMoney'=>DB::raw('fundMoney-'.$money)]);
            MoneyLog::log($userId, $type, 'in', $money, $remark);
            MoneyLog::log(User::ACCT_RP, $type, 'out', $money, $remark);

            $rdata['status'] = 1;
            $rdata['msg'] = '发放红包成功！';
            $rdata['code'] = $result['respCode'];
            $rdata['orderId'] = $redpack->orderId;
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $result['retMsg'];
            $rdata['code'] = $result['respCode'];
        }

        return $rdata;
    }

    /**
     * 查询账户信息
     * @param  string $userId    用户ID
     * @return array             返回信息
     */
    public static function accountQuery($userId) {
        $data = [];
        $data['serviceName'] = 'USER_QUERY';
        $data['userNo'] = $userId;

        $result = self::post($data);

        $rdata = [];
        if(self::success($result)) {
            $rdata['status'] = true;
            $rdata['data'] = [
                'userNo' => $result['userNo'],
                'name' => $result['name'], // 姓名
                'activeStatus' => $result['activeStatus'],
                'checkStatus' => $result['checkStatus'],
                'userRole' => $result['userRole'],
                'accessType' => $result['accessType'],
                'balance' => $result['balance'], // 可用余额
                'availableAmount' => $result['availableAmount'], // 账面余额 = 可用余额 + 冻结金额 
                'freezeAmount' => $result['freezeAmount'],
                'bankCardNo' => $result['bankCardNo'],
                'bankCode' => $result['bankCode'],
                'mobile' => $result['mobile'],
                'authList' => $result['authList']
            ];
            $rdata['msg'] = '查询成功！';
        } else {
            $rdata['status'] = false;
            $rdata['msg'] = '查询失败！';
            
        }
        return $rdata;
    }

    public static function endOdd($oddNumber) {
        $siteUrl = WEB_MAIN;
        $odd = Odd::where('oddNumber', $oddNumber)->first();
        if(!$odd) {
            $rdata['status'] = 0;
            $rdata['msg'] = '标的不存在！';
            return $rdata;
        }
        $oddMoneys = OddMoney::where('oddNumber', $oddNumber)->where('status', 1)->where('type', '<>', 'loan')->get();
        $data = [];
        $data['acqRes'] = Handler::BNQ_PL;
        $data['notifyURL'] = $siteUrl.'/custody/batchCreditEndAuthNotify';
        $data['retNotifyURL'] = $siteUrl.'/custody/batchCreditEndNotify';
        $count = 0;
        foreach ($oddMoneys as $oddMoney) {
            $data['subPacks'][] = [
                'accountId' => User::getCID($odd->userId),
                'orderId' => $oddMoney->getOrderID('end'),
                'forAccountId' => User::getCID($oddMoney->userId),
                'productId' => _ntop($oddNumber),
                'authCode' =>$oddMoney->authCode,
            ];
            $count ++;
        }
        $data['txCounts'] = $count;
        $data['subPacks'] = json_encode($data['subPacks']);
        $handler = new Handler('batchCreditEnd', $data, true);
        $result = $handler->api();

        if($result['received']=='success') {
            CustodyBatch::insert([
                'batchNo' => $handler->getBN(),
                'type' => 'batchCreditEnd',
                'sendTime' => date('Y-m-d H:i:s'),
                'refNum'=>$oddNumber,
                'sendData'=> json_encode(['count'=>$count])
            ]);
            $rdata['status'] = 1;
            $rdata['msg'] = '结束债权申请成功！';
            return $rdata;
        }else{
            $rdata['status'] = 0;
            $rdata['msg'] = '结束债权申请失败！';
            return $rdata;
        }
    }

    /**
     * 发布标的
     * @param  models\Odd $odd   标的
     * @return array             结果
     */
    public static function publish($odd) {
        $rdata = [];
        if($odd->progress!='prep') {
            $rdata['status'] = false;
            $rdata['msg'] = '标的进程错误！';
            return $rdata;
        }
        $borrowStyle = 0;
        if($odd->oddBorrowStyle=='week'){
            $borrowStyle = 7;
        }else if ($odd->oddBorrowStyle=='month'){
            $borrowStyle = 30;
        }

        $data = [];
        $data['userNo'] = $odd->userId;
        $data['serviceName'] = 'CREATE_PROJECT';
        $data['projectNo'] = $odd->oddNumber;
        $data['projectAmount'] = $odd->oddMoney;
        $data['projectName'] = $odd->oddTitle;
        $data['projectDesc'] = '资金周转';
        $data['projectType'] = 'STANDARD';

        $data['projectPeriod'] = $odd->oddBorrowPeriod * $borrowStyle;
        $data['annualRate'] = $odd->oddYearRate * 100;
        $data['repaymentWay'] = $odd->oddRepaymentStyle == 'monthpay'?'INTEREST_FIRST':($odd->oddRepaymentStyle == 'matchpay'?'FIXED_REPAYMENT':'');
        $data['userDevice'] = 'PC';

        if($odd->receiptUserId==$odd->userId) {
            $data['projectType'] = 'STANDARD';
        } else {
            $data['projectType'] = 'DELEGATE';
            $data['extension[accountType]'] = 'USER_NO';
            $data['extension[userNo]'] = $odd->receiptUserId;
        }

        $result = self::post($data);
        if(self::success($result)) {
            $count = Odd::where('oddNumber', $odd->oddNumber)->update(['progress'=>'published', 'publishTime'=>date('Y-m-d H:i:s')]);
            if($count==0) {
                $rdata['status'] = false;
                $rdata['msg'] = '修改标的状态失败！';
                return $rdata;
            } 

            $rdata['status'] = true;
            $rdata['msg'] = '请求成功！';
            return $rdata;
        } else {
            $rdata['status'] = false;
            $rdata['msg'] = $result['respMsg'];
            return $rdata;
        }
    }

    public static function cancelOdd($odd) {
        $data = [];
        $data['accountId'] = User::getCID($odd->userId);
        $data['productId'] = _ntop($odd->oddNumber);
        $data['raiseDate'] = _date('Ymd', $odd->publishTime);
        $data['acqRes'] = '';
        $handler = new Handler('debtRegisterCancel', $data);
        $result = $handler->api();
        if($result['retCode']==Handler::SUCCESS) {
            Odd::where('oddNumber', $odd->oddNumber)->update([
                'progress'=>'fail', 
                'oddTrialTime'=>date('Y-m-d H:i:s'), 
                'oddTrialRemark'=>'初审拒绝',
            ]);
            return [
                'status' => true,
                'msg' => '撤销成功！',
            ];
        } else {
            return [
                'status' => false,
                'msg' => '撤销失败！原因：['.$result['retCode'].']'.$result['retMsg'],
            ];
        }
    }

    public static function cancelRedpack($redpack, $remark='') {
        $rpUser = User::where('username', User::ACCT_RP)->first(['userId', 'fundMoney', 'custody_id']);

        $orderId = $redpack->orderId;
        $data = [];
        $data['accountId'] = $rpUser->custody_id;
        $data['txAmount'] = $redpack->money;
        $data['forAccountId'] = User::getCID($redpack->userId);
        $data['orgTxDate'] = substr($orderId, 0, 8);
        $data['orgTxTime'] = substr($orderId, 8, 6);
        $data['orgSeqNo'] = substr($orderId, 14);
        $handler = new Handler('voucherPayCancel', $data);
        $result = $handler->api();
        if($result['retCode']==Handler::SUCCESS) {
            $redpack->status = 2;
            if($redpack->save()) {
                if($remark=='') {
                    $remark = '撤销红包'.$redpack->money.'元';
                }
                User::where('userId', $redpack->userId)->update(['fundMoney'=>DB::raw('fundMoney-'.$redpack->money)]);
                User::where('username', User::ACCT_RP)->update(['fundMoney'=>DB::raw('fundMoney+'.$redpack->money)]);
                MoneyLog::log($redpack->userId, 'rpk-cancel', 'out', $redpack->money, $remark);
                MoneyLog::log(User::ACCT_RP, 'rpk-cancel', 'in', $redpack->money, $remark);

                $rdata['status'] = true;
                $rdata['msg'] = '撤销红包成功！';
                return $rdata;
            } else {
                $rdata['status'] = false;
                $rdata['msg'] = '撤销红包失败！';
                return $rdata;
            }
        } else {
            $rdata['status'] = false;
            $rdata['msg'] = $result['retMsg'];
            return $rdata;
        }
    }

    public static function syncPassword($user) {
        $data = [];
        $data['accountId'] = $user->custody_id;
        $handler = new Handler('passwordSetQuery', $data);
        $result = $handler->api();
        $rdata =[];
        if($result['retCode']==Handler::SUCCESS) {
            if($result['pinFlag']==1) {
                User::where('userId', $user->userId)->update(['is_custody_pwd'=>1]);
            } else {
                User::where('userId', $user->userId)->update(['is_custody_pwd'=>0]);
            }
            $rdata['status'] = true;
            $rdata['msg'] = '更新成功！';
            return $rdata;
        } else {
            $rdata['status'] = false;
            $rdata['msg'] = $result['retMsg'];
            return $rdata;
        }
    }

    /**
     * 用户签约同步
     * @param  models/User  $user   用户
     * @param  integer      $type   类型 1自动投标 2自动债转
     * @return array                同步信息
     */
    public static function syncAuth($user, $type=1) {
        $data = [];
        $data['type'] = $type;
        $data['accountId'] = $user->custody_id;
        $handler = new Handler('creditAuthQuery', $data);
        $result = $handler->api();
        $rdata =[];
        $col = $type==1?'auto_bid_auth':'auto_credit_auth';
        if($result['retCode']==Handler::SUCCESS) {
            if($result['state']==1) {
                User::where('userId', $user->userId)->update([$col=>$result['orderId']]);
            } else {
                User::where('userId', $user->userId)->update([$col=>'']);
            }

            $rdata['status'] = true;
            $rdata['msg'] = '更新成功！';
            return $rdata;
        } else {
            $rdata['status'] = false;
            $rdata['msg'] = $result['retMsg'];
            return $rdata;
        }
    }

    public static function redpackBatch($redpacks, $refNum='') {
        foreach ($redpacks as $key => $value) {
            self::redpack($value['userId'], $value['money'], $value['type'], $value['remark']);
        }
        return true;

        $data['acqRes'] = Handler::BNQ_PL;
        $data['notifyURL'] = WEB_MAIN.'/custody/batchVoucherPayAuthNotify';
        $data['retNotifyURL'] = WEB_MAIN.'/custody/batchVoucherPayNotify';
        $count = 0;
        $money = 0;
        foreach ($redpacks as $item) {
            $comType = '001';
            if($item['type']=='rpk-interest') {
                $comType = '002';
            }
            $data['subPacks'][] = [
                'voucherType' => $comType,
                'txAmount' => $item['money'],
                'orderId' => $item['orderId'],
                'forAccountId' => $item['custody_id'],
                'name' => $item['name'],
                'tradeDesc' => $item['remark'],
            ];
            $count ++;
            $money += $item['money'];
        }
        $data['txAmount'] = $money;
        $data['txCounts'] = $count;
        $data['subPacks'] = json_encode($data['subPacks']);
        $handler = new Handler('batchVoucherPay', $data, true);
        $retData = $handler->api();
        
        if(isset($retData['received']) && $retData['received']=='success') {
            RedpackBatch::insert([
                'batchNo' => $handler->getBN(),
                'addTime' => date('Y-m-d H:i:s'),
                'items' => json_encode($redpacks),
                'status' => 0,
            ]);

            CustodyBatch::insert([
                'batchNo'=>$handler->getBN(),
                'type'=>'batchVoucherPay',
                'sendTime'=>date('Y-m-d H:i:s'),
                'refNum'=>$refNum,
                'sendData'=> json_encode(['count'=>$count, 'amount'=>$money])
            ]);

            $rdata['msg'] = '请求银行存管批次发红包接口成功！';
            $rdata['status'] = true;
            return $rdata;
        }else{
            $rdata['msg'] = '请求银行存管批次发红包接口失败！';
            $rdata['status'] = false;
            return $rdata;
        }
    }

    /**
     * 同步指定日期的全流水文件
     * @param  string $date 日期Ymd
     * @return array        同步结果
     */
    public static function syncFullLogs($date) {
        $result = Handler::file('file-aleve', $date);
        $items = [
            'BANK'=>4,
            'CARDNBR'=>19,
            'AMOUNT'=>17,
            'CUR_NUM'=>3,
            'CRFLAG'=>1,
            'VALDATE'=>8,
            'INPDATE'=>8,
            'RELDATE'=>8,
            'INPTIME'=>8,
            'TRANNO'=>6,
            'ORI_TRANNO'=>6,
            'TRANSTYPE'=>4,
            'DESLINE'=>42,
            'CURR_BAL'=>17,
            'FORCARDNBR'=>19,
            'REVIND'=>1,
            'RESV'=>200,
        ];

        $result = json_decode($result, true);
        $rdata = [];
        if($result['returnCode']!='0000') {
            $rdata['status'] = false;
            $rdata['msg'] = '[ERROR]同步全流水结果：['.$result['returnCode'].']'.$result['returnMSG'];
            return $rdata;
        }

        $rows = [];
        $result = explode(PHP_EOL, $result['FILE']);
        $count = 0;
        $resStr = '';
        foreach ($result as $content) {
            if(trim($content)=='') {
                continue;
            }
            $row = [];
            $content = iconv('utf-8', 'gbk', $content);
            foreach ($items as $key => $val) {
                $res = CFile::popStr($content, $val);
                $content = $res[1];
                if($key=='RESV') {
                    continue;
                }
                $row[strtolower($key)] = $res[0];
            }
            $row['amount'] = intval($row['amount'])/100;
            $row['curr_bal'] = intval($row['curr_bal'])/100;
            $rows[] = $row;
            $count ++;
            if($count%100==0) {
                $status = CustodyFullLog::insert($rows);
                if($status) {
                    $resStr .= '[SUCCESS]同步全流水100条成功！' . PHP_EOL;
                } else {
                    $resStr .= '[ERROR]同步全流水100条成功！' . PHP_EOL;
                }
                $rows = [];
            }
        }
        $lastNum = count($rows);
        if($lastNum>0) {
            $status = CustodyFullLog::insert($rows);
            if($status) {
                $resStr .= '[SUCCESS]同步全流水'.$lastNum.'条成功！' . PHP_EOL;
            } else {
                $resStr .= '[ERROR]同步全流水'.$lastNum.'条失败！' . PHP_EOL;
            }
        }
        
        $resStr .= '同步全流水['.$date.']完成！';

        $rdata['status'] = false;
        $rdata['msg'] = $resStr;
        return $rdata;
    }

    /**
     * 同步指定日期的对账流水文件
     * @param  string $date 日期Ymd
     * @return array        同步结果
     */
    public static function syncLogs($date) {
        $result = Handler::file('file-eve', $date);
        $items = [
            'ACQCODE'=>11,
            'SEQNO'=>6,
            'CENDT'=>10,
            'CARDNBR'=>19,
            'AMOUNT'=>12,
            'CRFLAG'=>1,
            'MSGTYPE'=>4,
            'PROCCODE'=>6,
            'MERTYPE'=>4,
            'TERM'=>8,
            'RETSEQNO'=>12,
            'CONMODE'=>2,
            'AUTRESP'=>6,
            'FORCODE'=>11,
            'CLRDATE'=>4,
            'OLDSEQNO'=>6,
            'OPENBRNO'=>6,
            'TRANBRNO'=>6,
            'ERVIND'=>1,
            'TRANSTYPE'=>4,
        ];

        $result = json_decode($result, true);
        $rdata = [];
        if($result['returnCode']!='0000') {
            $rdata['status'] = false;
            $rdata['msg'] = '[ERROR]同步流水结果：['.$result['returnCode'].']'.$result['returnMSG'];
            return $rdata;
        }

        $rows = [];
        $result = explode(PHP_EOL, $result['FILE']);
        $count = 0;
        $resStr = '';
        foreach ($result as $content) {
            if(trim($content)=='') {
                continue;
            }
            $row = [];
            $content = iconv('utf-8', 'gbk', $content);
            foreach ($items as $key => $val) {
                $res = CFile::popStr($content, $val);
                $content = $res[1];
                $row[strtolower($key)] = $res[0];
            }
            $row['transdate'] = $date;
            $row['amount'] = intval($row['amount'])/100;
            $rows[] = $row;
            $count ++;
            if($count%100==0) {
                $status = CustodyLog::insert($rows);
                if($status) {
                    $resStr .= '[SUCCESS]同步流水100条成功！' . PHP_EOL;
                } else {
                    $resStr .= '[ERROR]同步流水100条失败！' . PHP_EOL;
                }
                $rows = [];
            }
        }
        $lastNum = count($rows);
        if($lastNum>0) {
            $status = CustodyLog::insert($rows);
            if($status) {
                $resStr .= '[SUCCESS]同步流水'.$lastNum.'条成功！' . PHP_EOL;
            } else {
                $resStr .= '[ERROR]同步流水'.$lastNum.'条失败！' . PHP_EOL;
            }
        }
        
        $resStr .= '同步流水['.$date.']完成！';

        $rdata['status'] = false;
        $rdata['msg'] = $resStr;
        return $rdata;
    }

    /**
     * 复审
     * @param  string $odd          标的对象
     * @param  array  $oddMoneys    债权对象数组
     */
    public static function rehear($odd, $oddMoneys) {
        $fee = $odd->serviceFee;
        $requestNo = Counter::getOrderID();
        $data['serviceName'] = 'CONFIRM_LOAN';
        $data['requestNo'] = $requestNo;
        $data['projectNo'] = $odd->oddNumber;
        if($fee>0) {
            $data['commission'] = $fee;
        }

        $count = 0;
        $amount = 0;
        foreach ($oddMoneys as $oddMoney) {
            $details[$count]['preRequestNo'] = $oddMoney->tradeNo;
            $details[$count]['amount'] = $oddMoney->money;
            $count ++;
            $amount += $oddMoney->money;
        }

        $data['details'] = json_encode($details,JSON_UNESCAPED_UNICODE);
        $result = self::post($data);

        if(self::success($result)) {
            CustodyBatch::insert([
                'batchNo'=>$requestNo,
                'type'=>'CONFIRM_LOAN',
                'sendTime'=>date('Y-m-d H:i:s'),
                'refNum'=>$odd->oddNumber,
                'sendData'=> json_encode(['count'=>$count, 'amount'=>$amount]),
                'status'=>1
            ]);
            Odd::where('oddNumber', $odd->oddNumber)->update(['progress'=>'review']);

            $key = Redis::getKey('oddRemain', ['oddNumber'=>$odd->oddNumber]);
            Redis::delete($key);

            Task::add('rehear', ['oddNumber'=>$odd->oddNumber, 'step'=>2]);

            $rdata['msg'] = '请求银行存管批次放款接口成功！';
            $rdata['status'] = true;
            return $rdata;
        }else{
            CustodyBatch::insert([
                'batchNo'=>$requestNo,
                'type'=>'CONFIRM_LOAN',
                'sendTime'=>date('Y-m-d H:i:s'),
                'refNum'=>$odd->oddNumber,
                'sendData'=> json_encode(['count'=>$count, 'amount'=>$amount]),
                'status'=>2
            ]);
            $rdata['msg'] = '请求银行存管批次放款接口失败！原因：'.$result['respMsg'];
            $rdata['status'] = false;
            return $rdata;
        }
    }

    /**
     * 还款
     * @param  array  $info    标的对象
     * @param  array  $list    债权对象数组
     */
    public static function repay($info, $list, $mode='normal') {
        if($mode=='bail' ) {
            $serviceName = 'CONFIRM_COMPENSATORY';
        } else {
            $serviceName = 'CONFIRM_REPAYMENT';
        }

        $rdata = [];
        if(!isset($info['oddNumber']) || !isset($info['period']) || !isset($info['type']) || !isset($info['frozenNo'])) {
            $rdata['msg'] = '参数缺失！';
            $rdata['status'] = false;
            return $rdata;
        }

        $oddNumber = $info['oddNumber'];
        $period = $info['period'];
        $type = $info['type'];
        $frozenNo = $info['frozenNo'];
        $fee = isset($info['fee'])?$info['fee']:0;
        
        $requestNo = Counter::getOrderID();
        $data['serviceName'] = $serviceName;
        $data['requestNo'] = $requestNo;
        $data['preRequestNo'] = $frozenNo;
        $data['projectNo'] = $oddNumber;
        if($fee>0) {
            $data['commission'] = $fee;
        }

        $count = 0;
        $amountTotal = 0;
        foreach ($list as $item) {
            $amount = round($item['subsidy'] + $item['capital'] + $item['interest'], 2);
            $details[$count]['userNo'] = $item['userId'];
            $details[$count]['amount'] = $amount;
            $details[$count]['commission'] = round($item['service'], 2);
            $details[$count]['dividend'] = round($item['extra'], 2);
            $count ++;
            $amountTotal += $amount;
        }
        $data['details'] = json_encode($details,JSON_UNESCAPED_UNICODE);
        $result = self::post($data);

        if(self::success($result)) {
            CustodyBatch::insert([
                'batchNo' => $requestNo,
                'type' => $serviceName,
                'sendTime' => date('Y-m-d H:i:s'),
                'refNum'=>$oddNumber,
                'sendData'=> json_encode(['count'=>$count, 'amount'=>$amountTotal, 'period'=>$period, 'type'=>$type]),
                'status'=>1,
            ]);

            Task::add('repay', [
                'oddNumber'=>$oddNumber, 
                'period'=>$period, 
                'type'=>$type, 
                'step'=>2
            ]);

            $rdata['msg'] = '请求银行存管还款确认接口成功！';
            $rdata['status'] = true;
            return $rdata;
        }else{
            CustodyBatch::insert([
                'batchNo' => $requestNo,
                'type' => $serviceName,
                'sendTime' => date('Y-m-d H:i:s'),
                'refNum'=>$oddNumber,
                'sendData'=> json_encode(['count'=>$count, 'amount'=>$amountTotal, 'period'=>$period, 'type'=>$type]),
                'status'=>2,
            ]);
            $rdata['msg'] = '请求银行存管还款确认接口失败！原因：'.$result['respMsg'];
            $rdata['status'] = false;
            return $rdata;
        }
    }

    public static function changeOddStatus($oddNumber, $status) {
        $requestNo = Counter::getOrderID();
        $data = [];
        $data['serviceName'] = 'MODIFY_PROJECT';
        $data['requestNo'] = $requestNo;
        $data['projectNo'] = $oddNumber;
        $data['projectStatus'] = $status;
        $url = self::getUrl();
        $result = NetworkHelper::post($url, $data);
        Log::write('放款接口返回:'.$result, [], 'custody');
        $result = json_decode($result, true);
        $rdata = [];
        if(self::success($result)) {
            $rdata['msg'] = '银行存管改变标的状态成功！';
            $rdata['status'] = true;
            return $rdata;
        } else {
            $rdata['msg'] = '银行存管改变标的状态失败！';
            $rdata['status'] = true;
            return $rdata;
        }
    }

    public static function query($requestNo, $tradeType) {
        $data = [];
        $data['serviceName'] = 'TRANSACTION_QUERY';
        $data['requestNo'] = $requestNo;
        $data['tradeType'] = $tradeType;

        $result = self::post($data);

        $rdata = [];
        if($result['respCode']==self::SUCCESS) {
            $rdata['status'] = true;
            $rdata['msg'] = '请求成功！';
            $rdata['data'] = $result['detail'];
            $rdata['code'] = $result['respCode'];
        } else {
            $rdata['status'] = false;
            $rdata['msg'] = '请求失败！原因:'.$result['respMsg'];
            $rdata['code'] = $result['respCode'];
        }
        return $rdata;
    }

    public static function platformFrozen($money, $repayUser, $oddNumber, $type, $logInfo) {
        $user = User::where('userId', $repayUser)->first(['userId', 'fundMoney', 'frozenMoney']);//User::ACCT_DB
        $requestNo = Counter::getOrderID();
        $data = [];
        $data['serviceName'] = 'PLATFORM_PRE_FREEZE';
        $data['requestNo'] = $requestNo;
        $data['userNo'] = User::ACCT_DB;
        $data['amount'] = $money;
        $data['bizType'] = $type;
        $data['projectNo'] = $oddNumber;

        $result = self::post($data);

        $rdata = [];
        $rdata['requestNo'] = $requestNo;
        if(self::success($result)) {
            $user->frozen($money, $logInfo);
            $rdata['status'] = true;
            $rdata['msg'] = '请求成功！';
        } else {
            $rdata['status'] = false;
            $rdata['msg'] = '请求失败！原因:'.$result['respMsg'];
        }
        return $rdata;
    }

    public static function bid($trade, $bonus=0) {
        $data = [];
        $data['serviceName'] = 'USER_PRE_FREEZE';
        $data['bizType'] = 'TENDER';
        $data['userNo'] = $trade->userId;
        $data['requestNo'] = $trade->tradeNo;
        $data['amount'] = $trade->bidMoney;
        $data['projectNo'] = $trade->oddNumber;
        $data['expired'] = date('YmdHis', time()+5*60);
        $data['remark'] = $trade->remark;
        if($bonus>0) {
            // 抵扣红包金额
            $data['preMarketingAmount'] = $bonus;
        }
        $data['callbackUrl'] = WEB_MAIN.'/odd/'.$trade->oddNumber;
        $data['userDevice'] = $trade->media;
        return self::form($data, false);
    }

    public static function crtr($trade, $crtr) {
        $data = [];
        $data['serviceName'] = 'USER_PRE_FREEZE';
        $data['bizType'] = 'CREDIT_ASSIGNMENT';
        $data['userNo'] = $trade->userId;
        $data['requestNo'] = $trade->tradeNo;
        $data['amount'] = $trade->money + $trade->interest;
        $data['projectNo'] = $crtr->oddNumber;
        $data['expired'] = date('YmdHis', time()+5*60);
        $data['remark'] = $trade->remark;
        $data['callbackUrl'] = WEB_MAIN.'/crtr/view/num/'.$trade->batchNo;
        $data['userDevice'] = $trade->media;
        $data['creditsaleRequestNo'] = $crtr->tradeNo;
        return self::form($data, false);
    }

    public static function transfer($oddMoney) {
        $requestNo = Counter::getOrderID();
        $data = [];
        $data['serviceName'] = 'DEBENTURE_SALE';
        $data['requestNo'] = $requestNo;
        $data['projectNo'] = $oddMoney->oddNumber;
        $data['userNo'] = $oddMoney->userId;
        $data['saleShare'] = $oddMoney->remain;

        $result = self::post($data);

        $rdata = [];
        $rdata['requestNo'] = $requestNo;
        if(self::success($result)) {
            $rdata['status'] = true;
            $rdata['msg'] = '请求成功！';
        } else {
            $rdata['status'] = false;
            $rdata['msg'] = '请求失败！原因:'.$result['respMsg'];
        }
        return $rdata;
    }

    public static function finishCrtr($oddNumber, $trades) {
        $requestNo = Counter::getOrderID();
        $data = [];
        $data['serviceName'] = 'CONFIRM_DEBENTURE_TRANSFER';
        $data['requestNo'] = $requestNo;
        $data['projectNo'] = $oddNumber;

        $i = 0;
        foreach ($trades as $key => $trade) {
            $details[$key]['preRequestNo'] = $trade->tradeNo;
            $details[$key]['commission'] = $trade->fee;
            $i ++;
        }

        $data['details'] = json_encode($details,JSON_UNESCAPED_UNICODE);
        $result = self::post($data);

        $rdata = [];
        if(self::success($result)) {
            $rdata['status'] = true;
            $rdata['msg'] = '请求成功！';
        } else {
            $rdata['status'] = false;
            $rdata['msg'] = '请求失败！原因:'.$result['respMsg'];
        }
        return $rdata;
    }

    public static function openCustody($info, $device='PC') {
        $data = [];
        $data['serviceName'] = 'PERSONAL_REGISTER';
        $data['userNo'] = $info['userId'];
        $data['realName'] = $info['name'];
        $data['idCardType'] = 'PRC_ID';
        $data['idCardNo'] = $info['cardnum'];
        $data['userRole'] = 'INVESTOR';
        $data['mobile'] = $info['phone'];
        $data['bankCardNo'] = $info['bankNum'];
        $data['verifyCardChannel'] = 'XINYAN';
        $data['checkType'] = 'LIMIT';
        $datA['userLimitType'] = 'ID_CARD_NO_UNIQUE';
        $data['authList'] = 'TENDER,CREDIT_ASSIGNMENT';
        $data['callbackUrl'] = WEB_USER . '/user/safe?estimate';
        $data['userDevice'] = $device;
        return self::form($data);
    }

    public static function recharge($trade) {
        $data = [];
        $data['requestNo'] = $trade->serialNumber;
        $data['serviceName'] = 'RECHARGE';
        $data['userNo'] = $trade->userId;
        $data['amount'] = $trade->money;
        $data['payCompany'] = 'BAOFU';
        $data['rechargeWay'] = $trade->payWay;
        $data['callbackUrl'] = WEB_USER.'/account/recharge';
        $data['userDevice'] = $trade->media;
        return self::form($data);
    }

    public static function withdraw($trade) {
        $data = [];
        $data['requestNo'] = $trade->tradeNo;
        $data['serviceName'] = 'WITHDRAW';
        $data['userNo'] = $trade->userId;
        $data['amount'] = $trade->outMoney;
        $data['commission'] = $trade->fee;
        $data['callbackUrl'] = WEB_MAIN.'/Custody/withdrawReturn?tradeNo='.$trade->tradeNo;
        $data['userDevice'] = $trade->media;
        return self::form($data);
    }

    public static function bindBankCard($userId, $mobile, $media='PC') {
        $requestNo = Counter::getOrderID();
        $data = [];
        $data['requestNo'] = $requestNo;
        $data['serviceName'] = 'PERSONAL_BIND_BANKCARD';
        $data['userNo'] = $userId;
        
        /*$data['bankCardNo'] = '';*/
        $data['mobile'] = $mobile;

        $data['verifyCardChannel'] = 'XINYAN';
        $data['checkType'] = 'LIMIT';
        $data['callbackUrl'] = WEB_USER.'/account/bank';
        $data['userDevice'] = $media;
        return self::form($data);
    }

    public static function unbindBankCard($userId) {
        $requestNo = Counter::getOrderID();
        $data = [];
        $data['requestNo'] = $requestNo;
        $data['serviceName'] = 'UNBIND_BANKCARD_DIRECT';
        $data['userNo'] = $userId;
        $result =  self::post($data);
        $rdata = [];
        $rdata['requestNo'] = $requestNo;
        if(self::success($result)) {

            UserBank::where('userId', $userId)->where('status', 1)->update(['status'=>0]);

            $rdata['status'] = true;
            $rdata['msg'] = '请求成功！';
        } else {
            $rdata['status'] = false;
            $rdata['msg'] = '请求失败！原因:'.$result['respMsg'];
        }
        return $rdata;
    }

    public static function degWithdraw($odd) {
        $amount = $odd->oddMoney - $odd->serviceFee;

        $requestNo = Counter::getOrderID();
        $tradeNo = '';
        $record = [];
        $record['tradeNo'] = $requestNo;
        $record['oddNumber'] = $odd->oddNumber;
        $record['userId'] = $odd->userId;
        $record['money'] = $amount;
        $record['addTime'] = date('Y-m-d H:i:s');
        $status = DegWithdraw::insert($record);

        $rdata = [];
        if($status) {
            $data = [];
            $data['requestNo'] = $requestNo;
            $data['serviceName'] = 'DELEGATE_WITHDRAW';
            $data['userNo'] = $odd->userId;
            $data['amount'] = $amount;
            $data['projectNo'] = $odd->oddNumber;
            $data['commission'] = 0;
            $result =  self::post($data);
            
            if(self::success($result)) {
                $rdata['status'] = true;
                $rdata['msg'] = '请求成功！';
            } else {
                $rdata['status'] = false;
                $rdata['msg'] = '请求失败！原因:'.$result['respMsg'];
            }
        } else {
            $rdata['status'] = false;
            $rdata['msg'] = '保存数据失败！';
        }

        return $rdata;
    }

    public static function autoBid($userId, $oddNumber, $money, $bonus=0) {
        $requestNo = Counter::getOrderID();
        $data = [];
        $data['requestNo'] = $requestNo;
        $data['serviceName'] = 'USER_AUTO_PRE_TRANSACTION';
        $data['userNo'] = $userId;
        $data['bizType'] = 'TENDER';
        $data['amount'] = $money;
        if($bonus>0) {
            $data['preMarketingAmount'] = $bonus;
        }
        $data['remark'] = '自动投标';
        $data['projectNo'] = $oddNumber;
        $result =  self::post($data);
        $rdata = [];
        $rdata['requestNo'] = $requestNo;
        if(self::success($result)) {
            $rdata['status'] = true;
            $rdata['msg'] = '请求成功！';
        } else {
            $rdata['status'] = false;
            $rdata['msg'] = '请求失败！原因:'.$result['respMsg'];
        }
        return $rdata;
    }

    public static function resetPassword($userId, $media='PC') {
        $requestNo = Counter::getOrderID();
        $data = [];
        $data['requestNo'] = $requestNo;
        $data['serviceName'] = 'RESET_PASSWORD';
        $data['userNo'] = $userId;
        $data['userDevice'] = $media;
        $data['callbackUrl'] = WEB_USER . '/user/safe';
        return self::form($data);
    }

    public static function modifyPhone($userId, $mobile, $media='PC') {
        $requestNo = Counter::getOrderID();
        $data = [];
        $data['requestNo'] = $requestNo;
        $data['serviceName'] = 'MODIFY_MOBILE';
        $data['userNo'] = $userId;
        $data['mobile'] = $mobile;
        $data['userDevice'] = $media;
        $data['callbackUrl'] = WEB_USER . '/user/safe';
        return self::form($data);
    }

    public static function post($data) {

        $rdata = [];
        if(!isset($data['serviceName']) || !$data['serviceName']) {
            $rdata['status'] = false;
            $rdata['msg'] = '请出传入服务标识！';
            return $rdata;
        }
        $url = '';
        $custodyApi = Registry::get('config')->get('custody.api');
        if($data['serviceName'] =='TRANSACTION_QUERY') {
            $url = $custodyApi . '/index.php?r=bank/default/trade-search';
        } else if($data['serviceName'] =='USER_QUERY') {
            $url = $custodyApi . '/index.php?r=bank/default/user-search';
        } else {
            $url = $custodyApi . '/index.php?r=bank';
        }

        Log::write('接口请求:'.$url, $data, 'custody');

        $result = NetworkHelper::post($url, $data);

        Log::write('接口返回:'.$result, [], 'custody');

        return json_decode($result, true);
    }

    public static function form($params, $isExport=false) {
        $debug = false;

        $inputType = 'hidden';
        $buttonHtml = '';
        $bodyHtml = '<body onload="document.form1.submit()">';
        if($debug) {
            $inputType = 'text';
            $buttonHtml = '<button type="submit">提交</button>';
            $bodyHtml = '<body>';
        }

        $custodyApi = Registry::get('config')->get('custody.api');
        $url = $custodyApi . '/index.php?r=bank';
        $html = '<html><head><title>页面跳转中，请务关闭...</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>';

        $html.= $bodyHtml;

        $html .= '<body><form action="' . $url . '" method="post" name="form1">';

        foreach($params as $key => $value) {
            $html .= '<input type="' . $inputType . '" name="' . $key . '" value="' . $value . '">';
        }
        
        $html .= $buttonHtml;

        $html .= '</form></body></html>';
        return $html;
    }
}
