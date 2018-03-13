<?php
use Yaf\Registry;
use helpers\StringHelper;
use helpers\NetworkHelper;
use helpers\ExcelHelper;
use exceptions\HttpException;
use models\Odd;
use models\OddClaims;
use models\OddMoney;
use models\Interest;
use models\Invest;
use models\Lottery;
use models\AutoInvest;
use Illuminate\Database\Capsule\Manager as DB;
use plugins\ancun\ACTool;
use tools\WebSign;
use tools\Log;
use tools\BFBank;
use tools\BankCard;
use models\Recharge;
use models\User;
use models\Withdraw;
use models\UserBid;
use models\UserCrtr;
use models\GQLottery;
use models\UserBank;
use models\Filiale;
use models\Activity;
use models\UserDuein;
use models\Protocol;
use models\oddTrace;
use helpers\NumberHelper;
use helpers\DateHelper;
use tools\ThirdQuery;
use tools\Redis;
use custody\Handler;
use forms\WithdrawFormOld;
use custody\API;
use helpers\PDFHelper;
use helpers\IDHelper;
use helpers\FileHelper;
use business\AITool;
use business\RepayHandler;
use business\RehearHandler;
use models\CustodyBatch;
use models\UserFriend;
use models\MoneyLog;
use tools\Counter;
use task\Task;
use models\Crtr;
use models\Sms;

class TestController extends Controller {
    
  public $menu = 'test';
  public $actions = [
    'test' => 'actions/TestAction.php',
  ];

  public function abbAction(){
    //$withdraw = Withdraw::where('id','19186')->first();

    if($withdraw->user->updateAfterWithdraw($withdraw->outMoney)) {
            // 用户资金日志
            $time = $withdraw->validTime;
            $getMoney = $withdraw->outMoney - $withdraw->fee;
            $remark = '提现'.$getMoney.'元。';
            $logs = [];
            $logs[] = [
                'type' => 'nor-withdraw',
                'mode' => 'out',
                'mvalue' => $getMoney,
                'userId' => $withdraw->userId,
                'remark' => $remark,
                'remain' => $withdraw->user->fundMoney + $withdraw->fee,
                'frozen' => $withdraw->user->frozenMoney,
                'time' => $time,
            ];
            if($withdraw->fee>0) {
                User::where('userId', User::ACCT_FEE)->update([
                    'fundMoney'=>DB::raw('fundMoney+'.$withdraw->fee)
                ]);
                $feeRemark = '提现手续费'.$withdraw->fee.'元。';
                $logs[] = [
                    'type' => 'fee-withdraw',
                    'mode' => 'out',
                    'mvalue' => $withdraw->fee,
                    'userId' => $withdraw->userId,
                    'remark' => $feeRemark,
                    'remain' => $withdraw->user->fundMoney,
                    'frozen' => $withdraw->user->frozenMoney,
                    'time' => $time,
                ];

                $acctfee = User::where('userId', User::ACCT_FEE)->first();
                $feeRemark = $withdraw->userId.'提现手续费'.$withdraw->fee.'元。';
                $logs[] = [
                    'type' => 'fee-withdraw',
                    'mode' => 'in',
                    'mvalue' => $withdraw->fee,
                    'userId' => User::ACCT_FEE,
                    'remark' => $feeRemark,
                    'remain' => $acctfee->fundMoney,
                    'frozen' => $acctfee->frozenMoney,
                    'time' => $time,
                ];

                $acctfee = User::where('userId', User::ACCT_RP)->first();
                $bffee = 1;
                User::where('userId', User::ACCT_RP)->update(['fundMoney'=>DB::raw('fundMoney-'.$bffee)]);
                $feeRemark = $withdraw->userId.'宝付提现费'.$bffee.'元。';
                $logs[] = [
                    'type' => 'fee-withdraw',
                    'mode' => 'out',
                    'mvalue' => $bffee,
                    'userId' => User::ACCT_RP,
                    'remark' => $feeRemark,
                    'remain' => $acctfee->fundMoney,
                    'frozen' => $acctfee->frozenMoney,
                    'time' => $time,
                ];

            }
            MoneyLog::insert($logs);

            if($withdraw->lotteryId) {
                Lottery::where('id', $withdraw->lotteryId)->update(['status'=>Lottery::STATUS_USED, 'used_at'=>$time]);
            }

            $acTool = new ACTool($withdraw, 'withdraw');
            $acTool->send();

            $user = $withdraw->user;
            $msg['phone'] = $user->phone;
            $msg['msgType'] = 'withdraw';
            $msg['userId'] = $user->userId;
            $msg['params'] = [
                                $user->getPName(),
                                date('Y-m-d H:i:s'),
                                $withdraw->outMoney,
                                $withdraw->fee,
                                $getMoney,
                            ];
            Sms::send($msg);

            $rdata['status'] = 1;
            $rdata['info'] = '提现成功！';
        }
  }

  public function userdatasAction(){
    $a[] = ['陈顺金','35018219840129684X'];
    $a[] = ['程高和','350126195610127132'];
    $a[] = ['刘孟航','35010420090904007X'];
    $a[] = ['许炜坚','350624199008052548'];
    $a[] = ['张妩','352225197904200024'];
    $a[] = ['李小琴','350825198403044145'];
    $a[] = ['郑姗','350102198809021522'];
    $a[] = ['扬丽','350721198608140068'];
    $a[] = ['高秋平','350182198911281568'];
    $a[] = ['杨丽华','350122198507056848'];
    $a[] = ['陈昭瑾','350103201010050024'];
    $a[] = ['杨玲华','350122198406086829'];
    $a[] = ['陈一升','35012619780207154x'];
    $a[] = ['陈淑丹','350182199302072685'];
    $a[] = ['杨晓清','350102198502101544'];
    $a[] = ['陈其榕','35012619621203631X'];
    $a[] = ['林艺','350103198910091919'];
    $a[] = ['陈娇媄','350126194910302727'];
    $a[] = ['陈冬雪','232301199406164928'];
    $a[] = ['刘燕云','350126195610188082'];
    $a[] = ['黄琼','350403196811020047'];
    $a[] = ['王小环','411123198704205607'];
    $a[] = ['黄仕圣','350124197811152779'];
    $a[] = ['林晓日','350182199005051669'];
    $a[] = ['程若兰','350182198304256520'];
    $a[] = ['陈晓惠','350702198903104722'];
    $a[] = ['林前妹','350126196305057160'];
    $a[] = ['林美金','352225197303153066'];
    $a[] = ['刘月钗','350105195908240021'];
    $a[] = ['林淋','350783198808264523'];
    $a[] = ['陈丽红','350126197010300324'];
    $a[] = ['林幸娜','350103199312142741'];
    $a[] = ['李明晓','370687198807216291'];
    $a[] = ['陈淑芳','350126197702147244'];
    $a[] = ['陈美莺','350126195603018026'];
    $a[] = ['孙伟静','350111198412205020'];
    $a[] = ['刘惠英','350105196704100021'];
    $a[] = ['潘凤娟','350126197212252746'];
    $a[] = ['刘钦云','350104196704080028'];
    $a[] = ['任晶','350105198610122325'];
    $a[] = ['陈玉萍','350105195605010026'];
    $a[] = ['李家康','350105195301250039'];
    $a[] = ['潘云钦','350103197606161922'];
    $a[] = ['余锦春','350782199002280039'];
    $a[] = ['连惠华','350104198109101524'];
    $a[] = ['陈祥禧','350121198403044731'];
    $a[] = ['危丹怡','350781199807233225'];
    $a[] = ['杨豪','350102198710231511'];
    $a[] = ['任琼','350105198502192326'];
    $a[] = ['方超','350102198812286417'];
    $a[] = ['陈依惠','350126195510156315'];
    foreach ($a as $key => $value) {
        $a[$key][] = IDHelper::getProvince($value[1]);
    }
    var_dump($a);exit;
  }

  public function dsmsAction(){
    $msg['phone'] = '13075962836';
    $msg['msgType'] = 'moveMoney';
    $msg['userId'] = '100000';
    $msg['params'] = [
                        '99999',
                    ];
    Sms::send($msg);
  }

  public function moveMoneyActions(){
    set_time_limit(0);
    ignore_user_abort();
    $users = User::whereNOTNULL('oldAccountId')->where('oldAccountId','=','{2A1D9E17-D355-4973-AE90-2A079E45F860}')->get();
    foreach ($users as $key => $user) {
        if($user->oldAccountId){
            $jsonData = NetworkHelper::curlRequest('http://loan.91hc.com/reloadMoney.php?type=reload&strAccountID='.$user->oldAccountId);
            $data = json_decode($jsonData,true);
            if($data['ret'] == '0000'){
                $oldMoney = $data['data']['content']['WithdrawableMoney'] + $data['data']['content']['WithdrawalDisableMoney'];
                if($oldMoney){
                    $status =  User::where('userId', $user->userId)->update(['fundMoney'=>DB::raw('fundMoney + '.$oldMoney),'investMoney'=>DB::raw('investMoney + '.$data['data']['content']['WithdrawableMoney']),'withdrawMoney'=>DB::raw('withdrawMoney + '.$oldMoney*1.1)]);
                    if($status){
                        $dbLog = [];
                        $dbLog['type'] = 'rpk-tran';
                        $dbLog['mode'] = 'in';
                        $dbLog['mvalue'] = $oldMoney;
                        $dbLog['userId'] = $user->userId;
                        $dbLog['remark'] = '平台资金迁移, 金额:'.$oldMoney. '元';
                        $dbLog['remain'] = $user->fundMoney + $oldMoney;
                        $dbLog['frozen'] = $user->frozenMoney;
                        $dbLog['time'] = date('Y-m-d H:i:s');
                        MoneyLog::insert([$dbLog]);
                        $info = '迁移成功！'.$user->userId;

                        $msg['phone'] = $user->phone;
                        $msg['msgType'] = 'moveMoney';
                        $msg['userId'] = $user->userId;
                        $msg['params'] = [
                                            $oldMoney,
                                        ];
                        Sms::send($msg);
                        //exit;
                    }else{
                        $info = '用户资金迁移 数据写入异常！'.$user->userId;
                        Flash::error('操作失败, 请联系客服！');
                        //exit;
                    }
                }else{
                    $info = '旧系统余额为零！'.$user->userId;
                    //exit;
                }
                Log::write($info , [$data], 'move');
            }
        }
    }

  }

  public function smstAction(){
    $app_id = '1400036997';
    $strRand = rand(100000,999999);
    $data = [];
    $url = 'https://yun.tim.qq.com/v5/tlssmssvr/sendsms?sdkappid=' . $app_id . '&random=' . $strRand;
    $result = NetworkHelper::CurlPost($url, json_encode($data),array('Expect:'),'0');
    var_dump($result);exit;
  }

  public function getsexActions(){
    $users = User::whereNOTNULL('cardnum')->get();
    foreach ($users as $key => $user) {
        $cardnum = $user->cardnum;
        $length = strlen($cardnum);
        $sexNumber = '';
        if ($length == 18) {
            $sexNumber = substr($cardnum, 16, 1);
        } else {
            $sexNumber = substr($cardnum, 14, 1);
        }
        if($sexNumber%2==0) {
            $sex = 'women';
        } else {
            $sex = 'man';
        }
        if($sex != $user->sex){
            $user->sex = $sex;
            $user->save();
        }

    }
    
  }

  public function remainImproveAction(){
    $moneylogs = MoneyLog::where('userId',User::ACCT_RP)->orderBy('time','asc')->get();
    $total = 20000;
    foreach ($moneylogs as $key => $value) {
        $total = $total - $value->mvalue;
        $value->remain = $total;
        $value->save();
    }

  }

  public function addlogActions(){
    $type = 'fee-recharge';
    $recharges = Withdraw::where('status',1)->get();
    foreach ($recharges as $key => $value) {
        
        $bffee = 1;
        $acctfee = User::where('userId', User::ACCT_RP)->first();
        User::where('userId', User::ACCT_RP)->update(['fundMoney'=>DB::raw('fundMoney-'.$bffee)]);
        $feeRemark = $value->userId.'宝付提现费'.$bffee.'元。';
        $logs[] = [
            'type' => 'fee-withdraw',
            'mode' => 'out',
            'mvalue' => $bffee,
            'userId' => User::ACCT_RP,
            'remark' => $feeRemark,
            'remain' => $acctfee->fundMoney,
            'frozen' => $acctfee->frozenMoney,
            'time' => $value->validTime,
        ];

        User::where('userId', User::ACCT_FEE)->update([
                    'fundMoney'=>DB::raw('fundMoney+'.$value->fee)
                ]);
        $acctfee = User::where('userId', User::ACCT_FEE)->first();
        $feeRemark = $value->userId.'提现手续费'.$value->fee.'元。';
        $logs[] = [
            'type' => 'fee-withdraw',
            'mode' => 'in',
            'mvalue' => $value->fee,
            'userId' => User::ACCT_FEE,
            'remark' => $feeRemark,
            'remain' => $acctfee->fundMoney,
            'frozen' => $acctfee->frozenMoney,
            'time' => $value->validTime,
        ];

    }
    MoneyLog::insert($logs);

  }

  public function deductAction(){
         // $phone = '13860607774';
         // $money = '1433.31';
        $user = User::where('phone',$phone)->with('userbank')->first();
        $userbank = $user->userbank;

        $url = 'https://carapi.91hc.com/index.php?r=api/baofu/bind-user-bank';
        $data = [
            'phone'=>$user->phone,
            'name'=>$user->name,
            'bankCode'=>$userbank->bankName,
            'bankNum'=>$userbank->bankNum,
            'cardnum'=>$user->cardnum,
            'money'=>$money,
        ];
        var_dump($data);

        $tradeNo = Counter::getOrderID();
        $recharge = new Recharge();
        $recharge->serialNumber = $tradeNo;
        $recharge->userId = $user->userId;
        $recharge->money = $money;
        $recharge->fee = 0;
        $recharge->status = 0;
        $recharge->time = date('Y-m-d H:i:s');
        $recharge->payType = 'baofoo';
        $recharge->remark = '代扣充值';
        $recharge->payWay = 'deduct';
        //$recharge->media = $this->getMedia();
        $recharge->save();

        $result = json_decode(NetworkHelper::CurlPost($url,$data),true);
        var_dump($result);
        if($result['ret'] == '0000'){
            $ret['result'] = $result['ret'];
            $ret['tradeNo'] = $tradeNo;
            $ret['status'] = 1;
            $ret['money'] = $money*100;
            $res = Recharge::after($ret);
            var_dump($res);
        }else{
            //var_dump($result);
        }
  }

  public function wtddAction(){
    $odds = Odd::whereIn('work_odd.oddNumber',["20180119000001",
        "20180119000002",
        "20180119000003",
        "20180119000004",
        "20180124000001",
        "20180125000001",
        "20180126000002",
        "20180129000001",])->groupBy('work_odd.oddNumber')->get();
    foreach ($odds as $key => $odd) {
        $oddMoneys[] = [
                    'oddNumber' => $odd->oddNumber,
                    'type' => 'loan',
                    'money' => $odd->oddMoney,
                    'remain' => 0,
                    'userId' => $odd->userId,
                    'remark' => '借款',
                    'time' => $odd->oddTrialTime,
                    'status' => '0',
                    'tradeNo' => Counter::getOrderID(),
                ];
    }
       $status = OddMoney::insert($oddMoneys);
  }

  public function initdueActions(){
    $invests = Invest::where('benJin','>','0')->with('odd')->get();
    $data = [];
    foreach ($invests as $key => $value) {
        $date = date('Ymd',strtotime($value->odd->oddRehearTime));
        if(isset($data[$date][$value->userId])){
               $data[$date][$value->userId] += $value->benJin;         
        }else{
           $data[$date][$value->userId] = $value->benJin;        
        }
    }

    $stay = [];

    for ($i= 20180101; $i < 20180211; $i = date('Ymd',strtotime($i .' +1 day'))) { 
        if(!isset($data[$i])){
           
        }else{
             $item = $data[$i];
            foreach ($item as $userId => $value) {
                //$duein = new UserDuein();
                //$duein->date = $date;
                if(isset($stay[$userId])){
                    $stay[$userId] += $value;
                }else{
                    $stay[$userId] = $value;
                }
                //$duein->stay = $stay[$userId];
                //$duein->save();
            }
        }
        foreach ($stay as $userId => $value) {
            $duein = new UserDuein();
            $duein->date = $i;
            $duein->stay = $value;
            $duein->userId = $userId;
            $duein->save();
        }
    }

  }

  public function userDataAction(){
    // $users[] = ['15159634716','15606015902'];
    // $users[] = ['18649724116','15880128606'];
    // $users[] = ['18005008591','15659108889'];
    // $users[] = ['13705933110','15659108889'];
    // $users[] = ['13960731678','13890386677'];
    // $users[] = ['13615053789','13890386677'];
    // $users[] = ['15980720855','13890386677'];
    // $users[] = ['18860181794','13599428072'];

    foreach ($users as $key => $value) {
        $user = User::where('username',$value[0])->first();
        $spreadUser = User::where('username', $value[1])->first();
        $user->tuijian = $value[0];
        $status = $user->save();
        //UserFriend::addOne($spreadUser->userId, $user->userId);
    }
  }

  public function ancunAction(){
    $a = new ACTool([],[]);
    $a->awardCaForPersonal();
  }

  public function reheartestAction(){
    var_dump(API::post([]));
  }

  public function getProtocolAction(){
    $pdf = PDFHelper::getProtocolPDF();
    FileHelper::txt2pdf($pdf, 'protocols/loan.txt', []);
    $fileName = 'protocol.pdf';
    $file = APP_PATH.'/../app/public/protocols/'.$fileName;
    $pdf->Output($file, 'F');
    return $fileName;
  }

  public function packAction(){
        $tradeNo = $_GET['tradeNo'];
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
        $baofoo = Registry::get('config')->get('baofoo')->get('open');
        if($baofoo){
            //$transData = json_decode($withdraw->xml,true);
            //$withdrawForm = new WithdrawForm;
            $code = '0000';
            $withdraw = Withdraw::where('tradeNo', $tradeNo)->first();
            //$withdraw->returnxml = json_encode($withdrawForm->result);
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
  }

  public function searchAction(){
    $a = new WithdrawFormOld();
    $a->withdrawSearch();
  }

  public function resetWithdrawDataAction(){
        $data = Withdraw::where('id',$_GET['id'])->first();

        $user = User::where('userId',$data->userId)->first();;
        $bank = UserBank::where('userId', $user->userId)->where('status', '1')->first();
        $this->bank = $bank->id;

        if(1) {
            $bankInfo = UserBank::getBankInfo($this->bank);
            if(!$bankInfo) {
                $this->addError('bank', '银行卡不存在或者银行卡信息不全！');
                return false;
            }

            $remark = '用户提现';
            $realMoney = $data->outMoney - $data->fee;
            
            $withdraw = new Withdraw();
            $withdraw->userId = $user->userId;
            $withdraw->bank = $bankInfo['bank'];
            //$withdraw->province = $bankInfo['province'];
            //$withdraw->city = $bankInfo['city'];
            //$withdraw->subbranch = $bankInfo['subbranch'];
            $withdraw->bankNum = $bankInfo['bankNum'];
            $withdraw->bankUsername = $bankInfo['bankUsername'];
            $withdraw->remark = $remark;
            $withdraw->outMoney = $data->outMoney;
            $withdraw->fee = $data->fee;
            $withdraw->status = 0;
            $withdraw->addTime = date('Y-m-d H:i:s');
            //$withdraw->media = $this->getMedia();
            // if($data->lottery) {
            //     $withdraw->lotteryId = $data->lottery->id;
            //     $this->lottery->status = Lottery::STATUS_USED;
            //     $this->lottery->used_at = date('Y-m-d H:i:s');
            //     $this->lottery->save();
            // }
            
            $tradeNo = date('Ymd').substr(md5(microtime().$data->outMoney.$user->userId), 8, 16).rand(10,99);
            $withdraw->tradeNo = $tradeNo;

            $xmlData = [];
            $xmlData['tradeNo'] = $tradeNo;
            $xmlData['bank'] = $bankInfo['bank'];
            //$xmlData['province'] = $bankInfo['province'];
            //$xmlData['city'] = $bankInfo['city'];
            // $xmlData['subbranch'] = $bankInfo['subbranch'];
            $xmlData['user'] = $user;
            $xmlData['bankNum'] = $bankInfo['bankNum'];
            $xmlData['fee'] = $data->fee;
            $xmlData['remark'] = $remark;
            $xmlData['realMoney'] = $realMoney;
            $transData = $this->getBaofooData($xmlData);
            var_dump(json_encode($transData));exit;
            $withdraw->xml = json_encode($transData);
            if($withdraw->save()&&$user->updateAfterWithdrawF($this->money)) {
                //$user->userType==1 提现审核
                if(0) {
                    $code = $this->baofooSdkPost($transData);
                    $withdraw->result = $code;
                    $withdraw->save();

                    $data['tradeNo'] = $tradeNo;
                    $data['result'] = $code;
                    if($code == '0000'){
                        $data['status'] = 1;
                    }else{
                        $data['status'] = 0;
                        $this->addError('form', $this->result['trans_content']['trans_head']['return_msg']);
                    }
                    Withdraw::after($data);
                    return $data['status'];
                } else {
                    $withdraw->status = 3;
                    $withdraw->save();
                    return true;
                }
            } else {
                $this->addError('form', '提现失败！');
                return false;
            }
        } else {
            return false;
        }
  }

    public function getBaofooData($data) {
        $terminal_id = Registry::get('config')->get('baofoo')->get('pay_wid');
        $member_id = Registry::get('config')->get('baofoo')->get('pay_member_id');
        $password = Registry::get('config')->get('baofoo')->get('pay_hc_key_pw');

        $trans_reqData = [
         'trans_no' => $data['tradeNo'], 
         'trans_money' => $data['realMoney'], 
         'to_acc_name' => $data['user']->name,
         'to_acc_no' => $data['bankNum'],
         'to_bank_name' => $data['bank'],
         'trans_card_id' => $data['user']->cardnum,
         'trans_mobile' =>$data['user']->phone,
         // 'to_pro_name' => '收款人开户行省名',
         // 'to_city_name' => '收款人开户行市名', 
         //'to_acc_dept' => ''
        ];

        $contents = [
            'trans_content' => ['trans_reqDatas'=>[['trans_reqData'=>[$trans_reqData]]]]
        ];
        var_dump($contents);
        Log::write('contents', $contents, 'debug', 'DEBUG');
        $privateKey = BFBank::getKey('private', 'pay_hc');
        $content = StringHelper::bfSign(json_encode($contents), $privateKey, $password);
        $params = [
            'version' => '4.0.0',
            'terminal_id' => $terminal_id,
            'member_id' => $member_id,
            'data_type' => 'json',
            'data_content' => $content
        ];
        return $params;
    }
  public function updateBankCNameAction(){
    $bank = UserBank::where('id','>','999999')->get();
    foreach ($bank as $key => $value) {
        $name = BankCard::getBankCName($value->bankName);
        $value->bankCName = $name;
        $value->save();
        # code...
    }
  }

  public function sendLottAction(){
    ignore_user_abort(1);
    set_time_limit(0);
    $user = User::get();
    $redpacks = [
        ['money_rate'=>18, 'period'=>30, 'money_lower'=>2000],
        ['money_rate'=>28, 'period'=>30, 'money_lower'=>8000],
        ['money_rate'=>58, 'period'=>30, 'money_lower'=>10000, 'period_lower'=>6],
        ['money_rate'=>138, 'period'=>30, 'money_lower'=>50000, 'period_lower'=>3],
        ['money_rate'=>238, 'period'=>30, 'money_lower'=>50000, 'period_lower'=>6],
        ['money_rate'=>408, 'period'=>30, 'money_lower'=>80000, 'period_lower'=>12],
        // ['money_rate'=>20, 'period'=>60, 'money_lower'=>20000, 'period_lower'=>6, 'period_uper'=>24],
        // ['money_rate'=>20, 'period'=>60, 'money_lower'=>20000, 'period_lower'=>6, 'period_uper'=>24],
        // ['money_rate'=>50, 'period'=>60, 'money_lower'=>50000],
        // ['money_rate'=>50, 'period'=>60, 'money_lower'=>50000],
        // ['money_rate'=>50, 'period'=>60, 'money_lower'=>50000, 'period_lower'=>6, 'period_uper'=>24],
        // ['money_rate'=>100, 'period'=>60, 'money_lower'=>100000, 'period_lower'=>6, 'period_uper'=>24],
    ];

    foreach ($user as $key => $value) {
        $list = [];
        foreach ($redpacks as $item) {
            $params = [];
            $params['type'] = 'money';
            $params['useful_day'] = $item['period'];
            $params['remark'] = '[活动]红包奖励';
            $params['userId'] = $value->userId;
            $params['money_rate'] = $item['money_rate'];
            $params['money_lower'] = $item['money_lower'];
            if(isset($item['period_uper'])) {
                $params['period_uper'] = $item['period_uper'];
            }
            if(isset($item['period_lower'])) {
                $params['period_lower'] = $item['period_lower'];
            }
            $list[] = $params;
        }
        $status = Lottery::generateBatch($list);
        echo $status.'------';
    }


  }

  public function cancelAction() {
    $oddMoneys = OddMoney::where('oddNumber', '20170829000042')->where('userId', '2000027014')->where('money', 50)->limit(1)->get();
    foreach ($oddMoneys as $oddMoney) {
        $data = [];
        $data['accountId'] = User::getCID($oddMoney->userId);
        $data['orderId'] = Handler::SEQ_PL;
        $data['txAmount'] = $oddMoney->money;
        $data['productId'] = _ntop($oddMoney->oddNumber);
        $data['orgOrderId'] = $oddMoney->tradeNo;
        $data['acqRes'] = '';

        $handler = new Handler('bidCancel', $data);
        $result = $handler->api();

        $rdata = [];
        if($result['retCode']==Handler::SUCCESS) {
            $oddMoney->status = 4;
            $status = $oddMoney->save();
            if($status) {
                Log::write('SUCCESS-撤销投资['.$oddMoney->id.']投资'.$oddmoney->money.'元', [], 'cancel-bid');
                echo 'SUCCESS撤销投标成功！<br>';
            } else {
                Log::write('WARNING-撤销投资['.$oddMoney->id.']投资'.$oddmoney->money.'元', [], 'cancel-bid');
                echo 'WARNING撤销投标成功！<br>';
            }
        } else {
            Log::write('DANGER-撤销投资['.$oddMoney->id.']投资'.$oddmoney->money.'元['.$result['retCode'].']'.$result['retMsg'], [], 'cancel-bid');
            $rdata['status'] = 0;
            echo 'DANGER撤销失败['.$result['retCode'].']'.$result['retMsg'].'<br>';
        }
    }
  }

  public function updateBankAction(){
    $bank = UserBank::where('id','>','999999')->get();

    foreach ($bank as $key => $value) {
        $bankData = file_get_contents('https://ccdcapi.alipay.com/validateAndCacheCardInfo.json?_input_charset=utf-8&cardNo='.$value->bankNum.'&cardBinCheck=true');
        $bankData = json_decode($bankData,true);
        $value->bankName = $bankData['bank'];
        $value->save();
    }

  }

  public function readExcelAction(){
    ignore_user_abort(1);
    set_time_limit(0);

    $data = ExcelHelper::format_excel2array('./1.xlsx');
    $datakey = $data[1];
    unset($data[1]);
    foreach ($data as $key => $value) {
        unset($data[$key]);
        $userdata[$value['A']] = $value;
        foreach ($userdata[$value['A']] as $key => $v) {
            $userdata[$value['A']][$datakey[$key]] = $v;
            unset($userdata[$value['A']][$key]);
        }
    }

    foreach ($userdata as $key => $value) {
        if($value['ReferenceID'] == 'NULL')continue;

        $user = User::where('oldAccountId',$value['AccountID'])->whereNULL('tuijian')->first();
        if($user){
            $tuijian = User::where('oldAccountId',$value['ReferenceID'])->first();
            $user->tuijian = $tuijian->phone;
            $user->save();
            UserFriend::addOne($tuijian->userId, $user->userId);
        }
    }

  }

   public function excelTime($date, $time = false) {
        if(function_exists('GregorianToJD')){
            if (is_numeric( $date )) {
            $jd = GregorianToJD( 1, 1, 1970 );
            $gregorian = JDToGregorian( $jd + intval ( $date ) - 25569 );
            $date = explode( '/', $gregorian );
            $date_str = str_pad( $date [2], 4, '0', STR_PAD_LEFT )
            ."-". str_pad( $date [0], 2, '0', STR_PAD_LEFT )
            ."-". str_pad( $date [1], 2, '0', STR_PAD_LEFT )
            . ($time ? " 00:00:00" : '');
            return $date_str;
            }
        }else{
            $date=$date>25568?$date+1:25569;
            /*There was a bug if Converting date before 1-1-1970 (tstamp 0)*/
            $ofs=(70 * 365 + 17+2) * 86400;
            $date = date("Y-m-d",($date * 86400) - $ofs).($time ? " 00:00:00" : '');
        }
      return $date;
    }


  public function excelAction() {
    $other = [
        'title' => '资金流水',
        'columns' => [
            'time' => ['name'=>'时间'],
            'money' => ['name'=>'金额'],
            'type' => ['name'=>'类型'],
            'description' => ['name'=>'描述'],
            'remain' => ['name'=>'余额'],
        ],
    ];

    ExcelHelper::bigExcel($other, function($outCount) {
        if ($outCount >= 106) {
            return false;
        }
        $records = [];
        for ($i=0; $i < 10; $i++) { 
            $item = [];
            $item['time'] = date('Y-m-d H:i:s');
            $item['money'] = 100;
            $item['type'] = 1024;
            $item['description'] = $outCount . '-' . $i;
            $item['remain'] = 5000;
            $records[] = $item;
        }
        return $records;
    });

    die();
    $count ++;
    while ($count < 10000) {
        $excelRecords = [];
        $item = [];
        $item['time'] = date('Y-m-d H:i:s');
        $item['money'] = 100;
        $item['type'] = 1024;
        $item['description'] = 'sss';
        $item['remain'] = 5000;
        $excelRecords[] = $item;
        $count ++;
        if($count%1000==0) {
            ExcelHelper::getDataExcel($excelRecords, $other, false);
            $excelRecords = [];
        }
    }
    die();
    echo date('Y-m-d H:i:s', strtotime('2017-02-21 23:19:24')+30*24*60*60*2);
    $invest = Invest::find(163295);
    $day = DateHelper::getIntervalDay($invest->addtime, $invest->endtime);
    var_dump($day);
    $dayInterest = $invest->interest/$day;
    var_dump($invest->interest);
    var_dump($dayInterest);
    var_dump(round($dayInterest, 4)*$day);
    die();

    $num = tools\Counter::next('orderday', 's');
    var_dump($num);
    die();
    OddMoney::with(['odd'=>function($q){
        $q->select('oddNumber', 'oddBorrowStyle', 'oddBorrowPeriod');
    }, 'invests'=>function($q){ $q->select('id');}])
    ->with([])
    ->where('id', $moneyId)->first(['userId']);

    var_dump(StringHelper::ipton('255.255.255.255'));die();
    $oddMoney = OddMoney::with(['odd'=>function($query){
        $query->select('userId', 'oddNumber', 'oddTitle');
    }])->where('id', 54437)->first();
    die();
    $time1 = microtime(true);
    $redis = RedisFactory::create();
    $time2 = microtime(true);
    $redis->rpush('test_time', 'ssssssssssssssssssssssssssssssssssssssssssssssssssssssss');
    $time3 = microtime(true);
    var_dump(($time2-$time1)*1000);
    var_dump(($time3-$time2)*1000);die();
    $url = 'http://www.xiaowei.com/test/file';
    $url = 'https://asset.91hc.com/xfjr.php';
    $url = 'http://localhost/xfjr.php';
    $dataArray = [];
    $path = APP_PATH.'/public/page/assets/imgDown/desc1.png';
    var_dump(file_exists($path));
    $files = [
      ['postName'=>'pic', 'path'=>$path, 'type'=>'image/png', 'name'=>'desc1.png']
    ];
    $result = NetworkHelper::fsPost($url, $dataArray, $files);
    var_dump($result);
  }

  public function cardnumAction() {
    var_dump(floatval(null));die();
    $status = API::identify(['name'=>'廖金灵', 'cardnum'=>'350824199001105476']);
    var_dump($status);die();
    echo '<br>';
    echo '-----------------------<br>';
    $list = [
        ['name'=>'廖金灵', 'cardnum'=>'350824199001105476', 'phone'=>'18760419185'],
        ['name'=>'李晓钰', 'cardnum'=>'142431199502200943', 'phone'=>'13934217283'],
        ['name'=>'章守春', 'cardnum'=>'340221199002124958', 'phone'=>'13063882358'],
        ['name'=>'黎国辉', 'cardnum'=>'441481198810270053', 'phone'=>'18320092700'],
        ['name'=>'付杰', 'cardnum'=>'34220119881013243X', 'phone'=>'13776084657'],
        ['name'=>'王盼盼', 'cardnum'=>'429006198808172499', 'phone'=>'18675588638'],
        ['name'=>'王彦明', 'cardnum'=>'37020319830507591X', 'phone'=>'13658072793'],
        ['name'=>'伍泰国', 'cardnum'=>'522529197902223816', 'phone'=>'13595311368'],
        ['name'=>'梁松增', 'cardnum'=>'440981198607208613', 'phone'=>'13713506234'],
        ['name'=>'李其燕', 'cardnum'=>'342423198301242939', 'phone'=>'13484249711'],
        ['name'=>'李骋昊', 'cardnum'=>'411002199008122530', 'phone'=>'15981922993'],
        ['name'=>'朱明英', 'cardnum'=>'330825198209204527', 'phone'=>'13858011655'],
        ['name'=>'王加林', 'cardnum'=>'51138119871029697X', 'phone'=>'15680978361'],
        ['name'=>'唐建磊', 'cardnum'=>'120221198804131513', 'phone'=>'13820850505'],
        ['name'=>'李绵友', 'cardnum'=>'430523198901034178', 'phone'=>'18011894088'],
        ['name'=>'刘雄泽', 'cardnum'=>'430321198402137013', 'phone'=>'18773047797'],
        ['name'=>'倪伟', 'cardnum'=>'420602198708012518', 'phone'=>'15872276880'],
        ['name'=>'王丽丽', 'cardnum'=>'130922199201136826', 'phone'=>'18631781204'],
        ['name'=>'陈浩纯', 'cardnum'=>'420581198604070019', 'phone'=>'15880229560'],
        ['name'=>'梁刚', 'cardnum'=>'420400197605271818', 'phone'=>'13886618088'],
        ['name'=>'肖亦凤', 'cardnum'=>'441422199107090047', 'phone'=>'18688326391'],
    ];
    $v = 0;
    $f = 0;
    foreach ($list as $user) {
        $status = API::identify(['name'=>$user['name'], 'cardnum'=>$user['cardnum']]);
        if($status) {
            $v++;
            echo '用户:'.$user['name'].'['.$user['cardnum'].']实名认证成功！<br>';
        } else {
            $f++;
        }
    }
    echo '------------------------------------<br>';
    echo '成功率：'. (($v/count($list))*100) . '%';
  }

  public function tdSendAction() {
    $odd = Odd::where('oddNumber', '20180102000001')->first([
        // 'oddTrial', 
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
    $result = AITool::run($odd);
    var_dump($result);
    die();
    
    $captcha = $this->getPost('captcha', '');
    $task_id = $this->getPost('task_id', '');
    $url = 'https://api.tongdun.cn/octopus/gjj.acquire/v1';
    $params = [
        'partner_code'=>'xwsd', 
        'partner_key'=>'cd021ae4453d40a195247a80967b452a', 
        'real_name'=>'廖金灵', 
        'user_pass'=>'ljl3115316', 
        'user_name'=>'350824199001105476',
        'login_type'=>'3',
        'task_id'=>$task_id,
        'channel_code'=>'330100',
        'auth_code'=>$captcha
    ];
    $result = NetworkHelper::postTwo($url, $params);
    $result = json_decode($result, true);
    echo '<pre>';
    var_dump($result);
    echo '</pre>';
  }

  public function indexAction() {

    Task::add('repay', [
        'oddNumber'=>'20171119000001', 
        'period'=>3, 
        'type'=>'normal', 
        'step'=>2
    ]);
    die();

    $str  = '{"serviceName":"CONFIRM_COMPENSATORY","requestNo":"20171119182212000044","preRequestNo":"20171119182209000043","projectNo":"20171118000001","details[0][userNo]":"2000000010","details[0][amount]":504.58,"details[0][commission]":0.37,"details[0][dividend]":0,"details[1][userNo]":"2000000010","details[1][amount]":100.92,"details[1][commission]":0.07,"details[1][dividend]":0,"details[2][userNo]":"2000000010","details[2][amount]":4440.33,"details[2][commission]":3.23,"details[2][dividend]":0}';
    $data = json_decode($str, true);
    // var_dump($data);die();
    $result = API::post($data);
var_dump($result);
die();

    $data = API::query('20171119130911000007', 'DEBENTURE_SALE');
    var_dump($data);die();
    $params = json_decode('{"bgData":"{\"retCode\":\"00000000\",\"requestNo\":\"20171118173654000010\",\"sign\":\"a7b79673c5d5516f94e77dcb5b50935e\"}"}', true);
    $data = json_decode($params['bgData'], true);

    if(!API::verify($data)) {
        Log::write('[BID]验签失败', [], 'custody');
        Handler::back();
    }
    $return = [];
    if($data['retCode']==API::SUCCESS) {
        $return = ['status'=>1, 'tradeNo'=>$data['requestNo'], 'result'=>$data['code']];
    } else {
        $return = ['status'=>0, 'tradeNo'=>$data['requestNo'], 'result'=>$data['code']];
    }

    UserBid::after($return);
    die();
    $tradeNo = $this->getQuery('tradeNo', '');
    $userId = $this->getQuery('userId', '');
    if($tradeNo && $userId) {
        $data  = [];
        $data['accountId'] = User::getCID($userId);
        $data['orgTxDate'] = substr($tradeNo, 0, 8);
        $data['orgTxTime'] = substr($tradeNo, 8, 6);
        $data['orgSeqNo'] = substr($tradeNo, 14);
        $handler = new Handler('fundTransQuery', $data);
        $result = $handler->api();
        var_dump($result);
    }
    die();
    $result = API::syncLogs('20171009');
    var_dump($result);die();
    $batch = models\CustodyBatch::where('batchNo', '20170929000012')->first();
    $result = Task::add('repay', [
        'oddNumber'=>$batch->refNum, 
        'period'=>$batch->getResult('send', 'period'), 
        'type'=>$batch->getResult('send', 'type'), 
        'step'=>2
    ]);
    var_dump($result);
    die();
    $data =[];
    $data['type'] = 'setEmail';
    $data['email'] = '360197197@qq.com';
    $result = models\Email::send($data);
    var_dump($result);
    die();
    $batch = models\CustodyBatch::where('batchNo', '20170911000004')->first();
    Task::add('repay', [
        'oddNumber'=>$batch->refNum, 
        'period'=>$batch->getResult('send', 'period'), 
        'type'=>$batch->getResult('send', 'type'), 
        'step'=>2
    ]);
    die();
    $data = [];
    $data['retCode'] = Handler::SUCCESS;
    $data['acqRes'] = '20170904000003';
    $data['sucAmount'] = 2.49;
    $data['sucCounts'] = 3;
    $data['failAmount'] = 0;
    $data['failCounts'] = 0;
    $batch = models\CustodyBatch::where('batchNo', $data['acqRes'])->where('status', 0)->first();
    $result = $batch->handle($data);
    die();
    
    $data = [];
    $data['accountId'] = '6212461920000121760';
    $data['orderId'] = Handler::SEQ_PL;
    $data['txAmount'] = 1400.00;
    $data['productId'] = _ntop('20170815000024');
    $data['orgOrderId'] = '20170817211929008619';
    $data['acqRes'] = '';

    $handler = new Handler('bidCancel', $data);
    $result = $handler->api();
    var_dump($result);
    die();

    $handler = new RepayHandler(['oddNumber'=>'20170828000001', 'period'=>1,  'type'=>'normal', 'step'=>2]);
    $result = $handler->handle();
    var_dump($result);
    die();

    $handler = new RehearHandler(['oddNumber'=>'20170825000003', 'step'=>2]);
    $handler->handle();
    var_dump($result);
    die();


    $return = ['status'=>1, 'tradeNo'=>'20170825142703000146', 'result'=>'00000000', 'authCode'=>'1111111'];
    $rs = UserBid::after($return);
    var_dump($rs);


    die();
    $oddMoneys = OddMoney::where('oddNumber', '20170814000008')->get();
    foreach ($oddMoneys as $oddMoney) {
        if($oddMoney->tradeNo!='20170814143020000001') {
            $result = Protocol::where('oddMoneyId', $oddMoney->id)->delete();
            var_dump($result);
            $fileName = $oddMoney->generateProtocol(false);
            var_dump($fileName);
        }
    }
    die();
    $key = Redis::getKey('autoInvestQueue');

    /*Redis::lrem($key, '1000007071', 1);
    Redis::lrem($key, '1000011333', 1);
    Redis::lrem($key, '1000010527', 1);
    Redis::lrem($key, '1000011391', 1);
    Redis::lrem($key, '1000009393', 1);
    Redis::lrem($key, '1000013418', 1);
    Redis::lrem($key, '1000004993', 1);
    Redis::lrem($key, '1000019971', 1);
    Redis::lrem($key, '1000007057', 1);
    Redis::lrem($key, '1000004881', 1);
    Redis::lrem($key, '1000005286', 1);
    Redis::lrem($key, '1000016858', 1);
    Redis::lrem($key, '1000015241', 1);
    Redis::lrem($key, '1000008919', 1);
    Redis::lrem($key, '1000005218', 1);
    Redis::lrem($key, '1000007069', 1);
    Redis::lrem($key, '1000005659', 1);
    Redis::lrem($key, '1000006993', 1);
    Redis::lrem($key, '1000008641', 1);
    Redis::lrem($key, '1000006569', 1);
    Redis::lrem($key, '1000007004', 1);
    Redis::lrem($key, '1000006371', 1);
    Redis::lrem($key, '1000009905', 1);
    Redis::lrem($key, '1000007346', 1);
    Redis::lrem($key, '1000009549', 1);
    Redis::lrem($key, '1000009633', 1);
    Redis::lrem($key, '1000005222', 1);
    Redis::lrem($key, '1000010113', 1);
    Redis::lrem($key, '1000009537', 1);
    Redis::lrem($key, '1000020269', 1);
    Redis::lrem($key, '1000009484', 1);
    Redis::lrem($key, '1000007366', 1);
    Redis::lrem($key, '1000011380', 1);
    Redis::lrem($key, '1000009405', 1);
    Redis::lrem($key, '1000006878', 1);
    Redis::lrem($key, '1000009383', 1);*/

    
    $list = Redis::lRange($key, 0, -1);
    foreach ($list as $userId) {
        $count = AutoInvest::where('userId', $userId)->where('autostatus', '1')->count();
        if($count==0) {
            Redis::lrem($key, $userId, 0);
            echo $userId . '<br>';
        }
    }
    die();

    $data = []; //20170815210501000002
    $data['accountId'] = '6212461920000000014';
    $data['txAmount'] = '14.82';
    $data['forAccountId'] = '6212461920000157657';
    $data['orgTxDate'] = '20170815';
    $data['orgTxTime'] = '210501';
    $data['orgSeqNo'] = '000002';
    $handler = new Handler('voucherPayCancel', $data);
    $result = $handler->api();
    var_dump($result);
    die();

    /*$batch = models\CustodyBatch::where('batchNo', '20170807000004')->first();
    Task::add('repay', [
        'oddNumber'=>$batch->refNum, 
        'period'=>$batch->getResult('send', 'period'), 
        'type'=>$batch->getResult('send', 'type'), 
        'step'=>2
    ]);

    $batch = models\CustodyBatch::where('batchNo', '20170807000006')->first();
    Task::add('repay', [
        'oddNumber'=>$batch->refNum, 
        'period'=>$batch->getResult('send', 'period'), 
        'type'=>$batch->getResult('send', 'type'), 
        'step'=>2
    ]);*/
    die();



    
    $batch = models\CustodyBatch::where('batchNo', '20170726000014')->first();

    Task::add('repay', [
        'oddNumber'=>$batch->refNum, 
        'period'=>$batch->getResult('send', 'period'), 
        'type'=>$batch->getResult('send', 'type'), 
        'step'=>2
    ]);
    die();
    // 批次撤销
    $str = '{"bgData":"{\"bankCode\":\"30050000\",\"batchNo\":\"000014\",\"seqNo\":\"000001\",\"productId\":\"A0T101\",\"txTime\":\"184001\",\"channel\":\"000002\",\"sign\":\"p4GwdjO5jDvoMzC4PwQ+65CpCmVyvSSKeeuFqgid/iYuYMbwQp5geULbr/TUpqeFQwu5VLcBr0fX+AfcAEQreddlbHi4JzvQfjMIoUb3zQQS/hEa69w6yYe2DOvv05g1Z/kuGoK6+lUTlGy0M7cY/DlsKeiV+kAM/GntVbTQ/LE=\",\"subPacks\":\"[{\\\"authCode\\\":\\\"20170407191032395830\\\",\\\"orderId\\\":\\\"P201707261840000000055441001\\\",\\\"txIntAmount\\\":\\\"6.97\\\",\\\"txCapAmout\\\":\\\"197.44\\\",\\\"forAccountId\\\":\\\"6212461920000000857\\\",\\\"txAmount\\\":\\\"204.41\\\"},{\\\"authCode\\\":\\\"20170407191032395834\\\",\\\"orderId\\\":\\\"P201707261840000000055442001\\\",\\\"txIntAmount\\\":\\\"4.65\\\",\\\"txCapAmout\\\":\\\"131.62\\\",\\\"forAccountId\\\":\\\"6212461920000000865\\\",\\\"txAmount\\\":\\\"136.27\\\"}]\",\"retCode\":\"00000000\",\"version\":\"10\",\"retMsg\":\"成功\",\"accountId\":\"6212461920000000063\",\"sucAmount\":\"340.68\",\"failCounts\":\"0\",\"failAmount\":\"0\",\"instCode\":\"00900001\",\"txCode\":\"batchBailRepay\",\"acqRes\":\"20170726000014\",\"sucCounts\":\"2\",\"txDate\":\"20170726\"}"}';

    $rows = [];

    $row = [];
    $row['txAmount'] = 197.44;
    $row['intAmount'] = 6.97;
    $row['authCode'] = '20170407191032395830';
    $row['creditId'] = intval(substr('P201707261840000000055441001', 15, 10));
    $period = intval(substr('P201707261840000000055441001', 25));
    $row['period'] = $period;
    $rows[] = $row;

    $row = [];
    $row['txAmount'] = 131.62;
    $row['intAmount'] = 4.65;
    $row['authCode'] = '20170407191032395834';
    $row['creditId'] = intval(substr('P201707261840000000055442001', 15, 10));
    $period = intval(substr('P201707261840000000055442001', 25));
    $row['period'] = $period;
    $rows[] = $row;

    echo json_encode($rows);die();


    $data = [];
    $data['batchNo'] = '000006';
    $data['txAmount'] = '1000';
    $data['txCounts'] = '1';
    $data['acqRes'] = '';
    $handler = new Handler('batchCancel', $data);
    $result = $handler->api();
    var_dump($result);die();


    Task::add('rehear', ['oddNumber'=>'20170725000004', 'step'=>2]);die();
    echo StringHelper::encodeQueryString(['test'=>Handler::SEQ_PL, 'test1'=>Handler::BNQ_PL]);
    die();
    $result = tools\Calculator::getResult([
        'period'=>3,
        'account'=>1000,
        'repayType'=>'monthpay',
        'periodType'=>'month',
        'yearRate'=>0.14,
        'timeStatus'=>1,
        'time'=>'2017-03-31 15:21:01',
    ]);
    echo '<pre>';
    var_dump($result);
    echo '</pre>';
    die();
    $result = CTAPI::endOdd('20170705000004');
    var_dump($result);
    $result = CTAPI::endOdd('20170705000003');
    var_dump($result);
    die();

    $date = $this->getQuery('date', '20170331');
    $result = Handler::file('file-eve', $date);
    var_dump($result);die();

    $odd = Odd::where('oddNumber', '20170710000001')->first([
        'oddTrial', 
        'oddNumber', 
        'oddYearRate', 
        'investType',
        'oddMoney',
        'userId',
        'oddBorrowValidTime',
        'oddBorrowPeriod',
        'oddType',
        'oddBorrowStyle',
        'progress'
    ]);
    $result = AITool::run($odd);
    var_dump($result);
    die();

    var_dump(_ntop('20170627000001'));
    var_dump(_pton('A0RV01'));
    die();
    $result = Handler::file('file-eve', '20160915');
    var_dump($result);die();

    $beginTime = '2017-05-28';
    $endTime = '2017-06-01';
    $a = strtotime($beginTime);
    $b = strtotime($endTime);
    $aMonday = date('Y-m-d 00:00:00', ($a-((date('w', $a)==0?7:date('w', $a))-1)*24*3600));
    $bMonday = date('Y-m-d 00:00:00', ($b-((date('w', $b)==0?7:date('w', $b))-1)*24*3600));
    $begin = strtotime($aMonday);
    $end = strtotime($bMonday);
    while ($begin <= $end) {
        $list[] = date('Ymd', $begin);
        $begin += 7*24*3600;
    }
    var_dump($list);
    die();

    $crtr = Crtr::where('id', 3)->first();
    $data = [];
    $data['accountId'] = User::getCID($crtr->odd->userId);
    $data['orderId'] = $crtr->oddMoney->getOrderID('end');
    $data['productId'] = _ntop($crtr->oddNumber);
    $data['forAccountId'] = $crtr->user->custody_id;
    $data['authCode'] = $crtr->oddMoney->authCode;
    $data['acqRes'] = '';
    $handler = new Handler('creditEnd', $data);
    $result = $handler->api();
    var_dump($handler->getJson());
    var_dump($result);die();

    $handler = new RepayHandler(['oddNumber'=>'20170601000003', 'period'=>1, 'type'=>'normal', 'cr'=>false, 'step'=>1]);
    $result = $handler->handle();
    var_dump($result);
    die();

    $result = CTAPI::endOdd('20170531000005');
    var_dump($result);die();

    $crtr = Crtr::where('id', 1)->first();
    $data = [];
    $data['accountId'] = User::getCID($crtr->odd->userId);
    $data['orderId'] = $crtr->oddMoney->getOrderID('end');
    $data['productId'] = _ntop($crtr->oddNumber);
    $data['forAccountId'] = $crtr->user->custody_id;
    $data['authCode'] = $crtr->oddMoney->authCode;
    $data['acqRes'] = '';
    $handler = new Handler('creditEnd', $data);
    $result = $handler->api();
    var_dump($handler->getJson());
    var_dump($result);die();

    $subs = [];
    $subs[] = [
        'accountId' => '6212461980000200014',
        'orderId' => 'E000000000055277',
        'forAccountId' => '6212461980000400010',
        'productId' => 'A0R901',
        'authCode' => '20160911102621723133'
    ];
    $subs[] = [
        'accountId' => '6212461980000200014',
        'orderId' => 'E000000000055278',
        'forAccountId' => '6212461980000350017',
        'productId' => 'A0R901',
        'authCode' => '20160911103640723319'
    ];

    $data = [];
    $data['acqRes'] = Handler::BNQ_PL;
    $data['notifyURL'] = 'http://www.xwsdvip.com/custody/batchCreditEndAuthNotify';
    $data['retNotifyURL'] = 'http://www.xwsdvip.com/custody/batchCreditEndNotify';
    $data['txCounts'] = '2';
    $data['subPacks'] = json_encode($subs);
    $handler = new Handler('batchCreditEnd', $data, true);
    $result = $handler->api();
    var_dump($handler->getJson());
    var_dump($result);die();


    // var_dump(_pton('A0R901'));die();
    // $str = '{"txAmount":"1000","acqRes":"20170531000004","notifyURL":"http://www.xwsdvip.com/custody/batchRepayAuthNotify","retNotifyURL":"http://www.xwsdvip.com/custody/batchRepayNotify","subPacks":"[{\"accountId\":\"6212461980000200014\",\"orderId\":\"P000000000166146\",\"txAmount\":500,\"intAmount\":0,\"txFeeIn\":0,\"forAccountId\":\"6212461980000250019\",\"productId\":\"A0QN0R\",\"authCode\":\"20160910113646705734\"},{\"accountId\":\"6212461980000200014\",\"orderId\":\"P000000000166152\",\"txAmount\":500,\"intAmount\":0,\"txFeeIn\":0,\"forAccountId\":\"6212461980000250019\",\"productId\":\"A0QN0R\",\"authCode\":\"20160910115436705816\"}]","txCounts":"2","version":"10","txCode":"batchLendPay","instCode":"00900001","bankCode":"30050000","txDate":"20170531","txTime":"165323","seqNo":"000001","channel":"000002","batchNo":"000004","sign":"ihTxl7sJJpNfztRCbFGI/aIPs38b7PD6gBKgTHGw0kko6tqRJzcoTzMzs1SbVGOUv3taNrBzK8lFmrwRLZmFL0BG2Pz/eZLTwcDUcqfq96sOtiy6EyAA46pH4UaJcEJTiH3kvVrVu+B1gk88NUH04meXPYsDJ4lkkeZkEIyn2cwAvVp4vmXMNGsiXcOlgJrEY5VHpWCyqN7bMhYewQUM7g9ZlFUUl076eojBV2o9QfIKhxpvCdYB0ypsSZqp/nlJe1YTPGRpSiKdv29CXvc+InbspfGNbE36rS3Z7ixTj1I0bOuTSvETrcYrxvYTJQX6t9lnBSK/gYxmv8amyEQ1Lg=="}';
    // var_dump(json_decode($str, true));die();

    $handler = new RepayHandler(['oddNumber'=>'20170531000003', 'period'=>3, 'type'=>'normal', 'cr'=>false, 'step'=>1]);
    $result = $handler->handle();
    var_dump($result);
    die();

    Task::add('rehear', ['oddNumber'=>'20170513000027', 'step'=>2]);

    die();
    
    $data = [];
    $data['accountId'] = "6212461980000250019";
    $data['orgOrderId'] = "20170531113532000001";
    $handler = new Handler('bidApplyQuery', $data);
    $result = $handler->api();
    var_dump($result);die();

    $handler = new RepayHandler(['oddNumber'=>'20170513000023', 'period'=>6, 'type'=>'delay', 'cr'=>false, 'step'=>2]);
    $result = $handler->handle();

    $user = User::where('userId', '10689')->first();
    $redpacks = [
        ['money_rate'=>10, 'period'=>30, 'money_lower'=>10000],
        ['money_rate'=>10, 'period'=>30, 'money_lower'=>10000],
        ['money_rate'=>10, 'period'=>30, 'money_lower'=>10000],
        ['money_rate'=>10, 'period'=>30, 'money_lower'=>10000],
        ['money_rate'=>10, 'period'=>30, 'money_lower'=>10000],
        ['money_rate'=>20, 'period'=>30, 'money_lower'=>20000],
        ['money_rate'=>20, 'period'=>30, 'money_lower'=>20000],
        ['money_rate'=>20, 'period'=>60, 'money_lower'=>20000, 'period_lower'=>6, 'period_uper'=>24],
        ['money_rate'=>20, 'period'=>60, 'money_lower'=>20000, 'period_lower'=>6, 'period_uper'=>24],
        ['money_rate'=>50, 'period'=>60, 'money_lower'=>50000],
        ['money_rate'=>50, 'period'=>60, 'money_lower'=>50000],
        ['money_rate'=>50, 'period'=>60, 'money_lower'=>50000, 'period_lower'=>6, 'period_uper'=>24],
        ['money_rate'=>100, 'period'=>60, 'money_lower'=>100000, 'period_lower'=>6, 'period_uper'=>24],
    ];

    $list = [];
    foreach ($redpacks as $item) {
        $params = [];
        $params['type'] = 'invest_money';
        $params['useful_day'] = $item['period'];
        $params['remark'] = '[活动]红包奖励';
        $params['userId'] = $user->userId;
        $params['money_rate'] = $item['money_rate'];
        $params['money_lower'] = $item['money_lower'];
        if(isset($item['period_uper'])) {
            $params['period_uper'] = $item['period_uper'];
        }
        if(isset($item['period_lower'])) {
            $params['period_lower'] = $item['period_lower'];
        }
        $list[] = $params;
    }
    var_dump($list);
    $status = Lottery::generateBatch($list);
    die();

      $data = [];
      $data['request']['phone'] = 'uSSZeGNVAGCPxlqf1TnSew==';
      $result =  NetworkHelper::post('https://user.hcjrfw.com/api/dzFastRegister', http_build_query($data));
      var_dump($result);die();

    $odd = Odd::where('oddNumber', '20170513000001')->first();
    
    $odd->remain = 100;
    $odd->save();
    var_dump($odd->remain);die();

    $phone = '14444444444';
    $config = Registry::get('config');
    $data = [];
    $data['method'] = 'post_registered_success';
    $data['partner_id'] = $config->get('duozhuan.pid');
    $data['request_time'] = date('Y-m-d H:i:s',time());
    $data['sign'] = md5($data['request_time'].$data['partner_id'].$data['method'].'duozhuan_api');
    $data['request']['user_name'] = '';
    $data['request']['user_phone'] = $phone;
    $url = $config->get('duozhuan.url').'post_registered_success';
    $result = NetworkHelper::post($url, http_build_query($data));
    var_dump($result);die();

    $url = 'http://www.duoz.net/Api/Partner/post_registered_click';
    $params = [];
    $params['method'] = 'post_registered_click';
    $params['partner_id'] = '1310';
    $params['request_time'] = date('Y-m-d H:i:s');
    $params['request']['click'] = 1;
    $params['sign'] = md5($params['request_time'].'1310'.'post_registered_click'.'duozhuan_api');
    var_dump(http_build_query($params));die();
    $result = NetworkHelper::post($url, http_build_query($params));
var_dump($result);die();

    $url = 'https://api.tongdun.cn/octopus/login.fields.query/v1';
    $params = ['partner_code'=>'xwsd', 'partner_key'=>'cd021ae4453d40a195247a80967b452a', 'channel_type'=>'GJJ', 'channel_code'=>'330100'];
    $result = NetworkHelper::postTwo($url, $params);
    $result = json_decode($result, true);
    echo '<pre>';
    var_dump($result);
    die();
    try {
        $result = NetworkHelper::post('https://113.108.182.3/aipg/ProcessServlet', []);
    } catch(\Exception $e) {
        var_dump('expression');
    }
    // var_dump($result);

    echo 'ss';
    die();

    
    echo 'REDIS_STRING:' . \Redis::REDIS_STRING . '<br/>';
    echo 'REDIS_SET:' . \Redis::REDIS_SET . '<br/>';
    echo 'REDIS_LIST:' . \Redis::REDIS_LIST . '<br/>';
    echo 'REDIS_ZSET:' . \Redis::REDIS_ZSET . '<br/>';
    echo 'REDIS_HASH:' . \Redis::REDIS_HASH . '<br/>';
    echo 'REDIS_NOT_FOUND:' . \Redis::REDIS_NOT_FOUND . '<br/>';
    echo 'AFTER:' . \Redis::AFTER . '<br/>';
    echo 'BEFORE:' . \Redis::BEFORE . '<br/>';
    echo 'MULTI:' . \Redis::MULTI . '<br/>';
    echo 'PIPELINE:' . \Redis::PIPELINE . '<br/>';
    die();
    $data  = [];
    $data['accountId'] = '6212461980000000026';
    $handler = new Handler('balanceQuery', $data);
    $result = $handler->api();
var_dump($result);
    die();
    
    $data = [];
    $data['accountId'] = "6212461980000000018";
    $data['orgOrderId'] = "201705181113371000001";
    $handler = new Handler('bidApplyQuery', $data);
    $result = $handler->api();
    var_dump($result);die();
    


    $result = Redis::keys('odd_remain:*');
    var_dump($result);die();

    $str = '{"bgData":"{\"bankCode\":\"30050000\",\"seqNo\":\"000001\",\"txTime\":\"100256\",\"sign\":\"8A3CaVT8pHyf9NOQAuc6YmRo90lWpcxAuw0kRmin1cTJznygK4AyUKjCUHydgrBE/Jvs4ITGegjqyFoVLXCElVSaNUcqxswol4y6RHP/vXcMg8bmvgsTDtYRFl7+KTBC7FFFF8mZbg1tVnN6xugQjvE1SsnlJnitY6xOV3NHgKI=\",\"channel\":\"000002\",\"retCode\":\"00000000\",\"version\":\"10\",\"retMsg\":\"DBEMU.模拟器返回成功\",\"accountId\":\"6212461270000860748\",\"instCode\":\"00170001\",\"txCode\":\"accountOpen\",\"acqRes\":\"userId%3D1000011856%26cardnum%3D331002198603210645%26bankNum%3D6217001480003768386%26name%3D%E6%9E%97%E7%87%95\",\"txDate\":\"20170513\"}"}';
    $params = json_decode($str, true);

    $data = json_decode($params['bgData'], true);
    if(!Handler::verify($data)) {
        Log::write('[OPEN_ACCOUNT]验签失败', [], 'custody');
        Handler::back();
    }

    $return = [];
    if($data['retCode']==Handler::SUCCESS) {
        $item = StringHelper::decodeQueryString($data['acqRes']);
        $birth = StringHelper::getBirthdayByCardnum($item['cardnum']);
        $sex = StringHelper::getSexByCardnum($item['cardnum']);
        User::where('userId', $item['userId'])->update([
            'custody_id'=>$data['accountId'], 
            'cardnum'=>$item['cardnum'],
            'name'=>$item['name'],
            'sex'=>$sex, 
            'birth'=>$birth, 
            'cardstatus'=>'y',
            'certificationTime'=>date('Y-m-d H:i:s'),
            'bindThirdTime'=>date('Y-m-d H:i:s')
        ]);
        
        Redis::updateUser([
            'userId'=>$item['userId'],
            'custody_id'=>$data['accountId'],
            'cardnum'=>$item['cardnum'],
            'name'=>$item['name'],
        ]);

        $binInfo = BankCard::getBinInfo($item['bankNum']);
        UserBank::insert([
            'userId'=>$item['userId'], 
            'bankNum'=>$item['bankNum'], 
            'createAt'=>date('Y-m-d H:i:s'), 
            'updateAt'=>date('Y-m-d H:i:s'),
            'binInfo'=>$binInfo
        ]);
    }
    Handler::back();

    task\Task::add('test', ['val' => 'hello'], 7);
    task\Task::add('test', ['val' => 'hello'], 3);
    die();

    task\Task::add('redpack', [
        'redpacks' => [
            ['userId'=>'10689', 'money'=>5, 'type'=>'test', 'remark'=>'测试任务发红包1'],
            ['userId'=>'10689', 'money'=>5, 'type'=>'test', 'remark'=>'测试任务发红包2'],
        ],
    ]);
    die();
    /*$data = [];
    $data['accountId'] = '6212461270000960118';
    $data['reqType'] = '1';
    $data['reqTxCode'] = 'bidApply';
    $data['reqTxDate'] = '20170503';
    $data['reqTxTime'] = '132536'; //000001
    $data['reqSeqNo'] = '000001';
    $data['reqOrderId'] = '';
    $handler = new Handler('bidApplyQuery', $data);
    $result = $handler->api();
    var_dump($result);die();
    
    // $data = [];
    // $data['accountId'] = '6212461270000010146';
    // $data['orderId'] = Handler::SEQ_PL;
    // $data['productId'] = '005053';
    // $data['forAccountId'] = '6212461270000960118';
    // $data['authCode'] = '20160907172722452389';
    // $data['acqRes'] = '';
    // $handler = new Handler('creditEnd', $data);
    // $result = $handler->api();
    // var_dump($result);die();

    $data = [];
    $data['accountId'] = '6212461270000960118';
    $data['startDate'] = '20160906';
    $data['endDate'] = '20170427';
    $data['state'] = '0';
    $data['productId'] = '005053';
    $data['pageNum'] = '1';
    $data['pageSize'] = '10';
    $handler = new Handler('creditDetailsQuery', $data);
    $result = $handler->api();
    var_dump($result);die();

    $data = [];
    $data['accountId'] = '6212461270000760542';
    $data['startDate'] = '20160906';
    $data['endDate'] = '20170427';
    $data['type'] = '0';
    $data['tranType'] = '';
    $data['pageNum'] = '1';
    $data['pageSize'] = '10';
    $handler = new Handler('accountDetailsQuery', $data);
    $result = $handler->api();
    var_dump($result);die();

    $data = [];
    $data['type'] = '9';
    $data['batchNo'] = '000007';
    $data['batchTxDate'] = '20170510';
    $data['pageSize'] = '10';
    $data['pageNum'] = '1';
    $handler = new Handler('batchDetailsQuery', $data);
    $result = $handler->api();
    echo '<pre>';
    var_dump($result);
    echo '</pre>';
    die();

    /*$data = [];
    $data['type'] = '0';
    $data['batchNo'] = '000001';
    $data['batchTxDate'] = '20170510';
    $handler = new Handler('batchQuery', $data);
    $result = $handler->api();
    echo '<pre>';
    var_dump($result);
    echo '</pre>';
    die();*/

    /*$result = UserCrtr::after(['tradeNo'=>'20170505142813000001', 'result'=>'00000000', 'status'=>'1', 'authCode'=>'20160907142906505243']);
    var_dump($result);die();*/

    /*$data = [];
    $data['accountId'] = '6212461270000010146';
    $data['productId'] = '005053';
    $data['pageNum'] = '1';
    $data['pageSize'] = '10';
    $data['startDate'] = '20160906';
    $data['endDate'] = date('Ymd', time());
    $handler = new Handler('debtDetailsQuery', $data);
    $result = $handler->api();
    var_dump($result);die();*/


    $data = [];
    $data['accountId'] = '6212461270000760542';
    $data['orgOrderId'] = '20170505142813000001';
    $handler = new Handler('creditInvestQuery', $data);
    $result = $handler->api();
    var_dump($result);die();

    $a = IDHelper::getProvince('350824199001105476');
    $b = IDHelper::getAddress('350824199001105476');
    var_dump($a);
    var_dump($b);
    die();
    $str = '{"bgData":"{\"bankCode\":\"30050000\",\"authCode\":\"20160907174105502108\",\"seqNo\":\"000001\",\"productId\":\"005053\",\"txTime\":\"173919\",\"orderId\":\"20170504173919000001\",\"channel\":\"000002\",\"sign\":\"Nj6Lt8FgXaOATsOZkQGJlMlFfeB2Pt+7jtHIAdUeclVf95vZkcvP3AAyK/PkbX+M2ovQC14ocMNhd4chWd4UqeIQmhu+4qR3HmnCcm5Z3Kku/oBMlmnrUdONQ88UKjyUGAThkuH85N4TeN+Kr2lVN0mVyUQOS0V5GutLqwb2XVc=\",\"retCode\":\"00000000\",\"version\":\"10\",\"retMsg\":\"\",\"txAmount\":\"50\",\"accountId\":\"6212461270000760542\",\"name\":\"林长伟\",\"instCode\":\"00170001\",\"tsfAmount\":\"50\",\"txCode\":\"creditInvest\",\"acqRes\":\"\",\"txDate\":\"20170504\"}"}';
    $params = json_decode($str, true);
    $data = json_decode($params['bgData'], true);

    if(!Handler::verify($data)) {
        Log::write('[CRTR]验签失败', [], 'custody');
        Handler::back();
    }

    $return = [];
    if($data['retCode']==Handler::SUCCESS) {
        $return = ['status'=>1, 'tradeNo'=>$data['orderId'], 'result'=>$data['retCode']];
    } else {
        $return = ['status'=>0, 'tradeNo'=>$data['orderId'], 'result'=>$data['retCode']];
    }

    UserCrtr::after($return);

    Handler::back();

    $str = '{"bgData":"{\"bankCode\":\"30050000\",\"seqNo\":\"000001\",\"txTime\":\"125133\",\"sign\":\"7mj2+FUjPgsoMZzr30pgPvHqnAqCHHyq/NQCHJYufRbOg/wZtta7rPpH0w/KpGzRTw/p3gpjGVkNJPxtuQlzro2/e2r6Cz3YspcacULW+bWsvED8ygqX3DO3LOeATuBOJvqZNnZw5mkJuUUphbQJ43g07tUjFc0Z2IIG9f4hIwk=\",\"channel\":\"000002\",\"retCode\":\"CA100766\",\"version\":\"10\",\"retMsg\":\"DBEMU.模拟器返回成功\",\"accountId\":\"\",\"instCode\":\"00170001\",\"txCode\":\"accountOpen\",\"acqRes\":\"userId%3D10014%26cardnum%3D350126196209100012%26bankNum%3D6212221422222888333%26name%3D%E5%BC%A0%E5%90%9B%E6%BD%AE\",\"txDate\":\"20170505\"}"}';
    $params = json_decode($str, true);
    $data = json_decode($params['bgData'], true);

    if(!Handler::verify($data)) {
        Log::write('[CRTR]验签失败', [], 'custody');
        Handler::back();
    }
    die();

    $return = [];
    if($data['retCode']==Handler::SUCCESS) {
        $return = ['status'=>1, 'tradeNo'=>$data['orderId'], 'result'=>$data['retCode']];
    } else {
        $return = ['status'=>0, 'tradeNo'=>$data['orderId'], 'result'=>$data['retCode']];
    }

    UserCrtr::after($return);

    Handler::back();

    $data = [];
    $data['bankCode'] = '30050000';
    $data['seqNo'] = '000001';
    $data['txTime'] = '133308';
    $data['sign'] = 'E1xWsJrDhHnRKV2tG5cf8m9PBhYo2jogL7FVd7/KnwlLVUEy7uK9zMpZoICtUsI8n2+4UPbUZrvoiVuXK4Jb2eyWvvnxRFUud1Fa+fAbuSt9A0sYM93O6WyPiCNwAyxu2P7z7UMeOVdgKAgmiclbgEYic1IEpWqgEuwUAoM9E5o=';
    $data['channel'] = '000002';
    $data['retCode'] = '00000000';
    $data['version'] = '10';
    $data['retMsg'] = 'DBEMU.模拟器返回成功';
    $data['accountId'] = '6212461270000760542';
    $data['instCode'] = '00170001';
    $data['txCode'] = 'accountOpen';
    $data['acqRes'] = json_encode(['userId'=>'1000006964', 'cardnum'=>'350583199304024917', 'bankNum'=>'6214835913486278', 'name'=>'林长伟'], JSON_UNESCAPED_UNICODE);
    $data['txDate'] = '20170504';

    /*if(!Handler::verify($data)) {
        Log::write('[OPEN_ACCOUNT]验签失败', [], 'custody');
        Handler::back();
    }*/

    $return = [];
    if($data['retCode']==Handler::SUCCESS) {
        $item = json_decode($data['acqRes'], true);
        $birth = StringHelper::getBirthdayByCardnum($item['cardnum']);
        $sex = StringHelper::getSexByCardnum($item['cardnum']);
        User::where('userId', $item['userId'])->update([
            'custody_id'=>$data['accountId'], 
            'cardnum'=>$item['cardnum'],
            'name'=>$item['name'],
            'sex'=>$sex, 
            'birth'=>$birth, 
            'cardstatus'=>'y',
            'certificationTime'=>date('Y-m-d H:i:s'),
            'bindThirdTime'=>date('Y-m-d H:i:s')
        ]);
        
        Redis::updateUser([
            'userId'=>$item['userId'],
            'custody_id'=>$data['accountId'],
            'cardnum'=>$item['cardnum'],
            'name'=>$item['name'],
        ]);

        $binInfo = BankCard::getBinInfo($item['bankNum']);
        UserBank::insert([
            'userId'=>$item['userId'], 
            'bankNum'=>$item['bankNum'], 
            'createAt'=>date('Y-m-d H:i:s'), 
            'updateAt'=>date('Y-m-d H:i:s'),
            'binInfo'=>$binInfo
        ]);
    }
    Handler::back();
    
    /*$str = '{"batchNo":"000016","txAmount":"75000","acqRes":"20161115000067a","notifyURL":"http:\/\/www.xwsdvip.com\/custody\/batchLendPayAuthNotify","retNotifyURL":"http:\/\/www.xwsdvip.com\/custody\/batchLendPayAuthNotify","subPacks":"[{\"accountId\":\"6212461270000960118\",\"orderId\":\"20170427172629000001\",\"txAmount\":100,\"forAccountId\":\"6212461270000010146\",\"productId\":\"005053\",\"authCode\":\"20160907172722452389\"},{\"accountId\":\"6212461270000960118\",\"orderId\":\"20170428134231000001\",\"txAmount\":100,\"forAccountId\":\"6212461270000010146\",\"productId\":\"005053\",\"authCode\":\"20160907134421454751\"},{\"accountId\":\"6212461270000960118\",\"orderId\":\"20170428135233000001\",\"txAmount\":100,\"forAccountId\":\"6212461270000010146\",\"productId\":\"005053\",\"authCode\":\"20160907135313455093\"},{\"accountId\":\"6212461270000960118\",\"orderId\":\"20170428135854000001\",\"txAmount\":100,\"forAccountId\":\"6212461270000010146\",\"productId\":\"005053\",\"authCode\":\"20160907135930455136\"},{\"accountId\":\"6212461270000960118\",\"orderId\":\"20170428154135000001\",\"txAmount\":500,\"forAccountId\":\"6212461270000010146\",\"productId\":\"005053\",\"authCode\":\"20160907154209455669\"},{\"accountId\":\"6212461270000960118\",\"orderId\":\"20170430173723000001\",\"txAmount\":1000,\"forAccountId\":\"6212461270000010146\",\"productId\":\"005053\",\"authCode\":\"20160907173756457237\"},{\"accountId\":\"6212461270000960118\",\"orderId\":\"20170503132536000001\",\"txAmount\":1500,\"forAccountId\":\"6212461270000010146\",\"productId\":\"005053\",\"authCode\":\"20160907132611465092\"},{\"accountId\":\"6212461270000960118\",\"orderId\":\"20170503132821000001\",\"txAmount\":69800,\"forAccountId\":\"6212461270000010146\",\"productId\":\"005053\",\"authCode\":\"20160907132854465094\"},{\"accountId\":\"6212461270000960118\",\"orderId\":\"20170503140225000001\",\"txAmount\":1800,\"forAccountId\":\"6212461270000010146\",\"productId\":\"005053\",\"authCode\":\"20160907140305465169\"}]","txCounts":"9","version":"10","txCode":"batchLendPay","instCode":"00170001","bankCode":"30050000","txDate":"20170504","txTime":"094520","seqNo":"000001","channel":"000002","sign":"nc6sM\/wZnv2Jnirdb4Z3Kd0bT5xGki92PFYu3Aosp\/mD5TSaFv0YVLo\/oeupmHY9OwQJN\/dE+arhlhnR2vY2lsp6hrAcHyS+P7f1A4VcR0PnrQAcrOhCxA175v74rwrdVMS6jbjz83Jnhgp3cWvesmECT8Pj\/swwrsCWzJIHRoDoCjEnVIaxNSRy3U4Km4Gz1lwhEYqniwv\/Sq9bL2hJMrjJ4yC11+E6G\/zvD9jFr4ba8ML1StVX20brs3LM+mcd7XsG5rUJOxOczkJca5evI1FbI5W39V8OaOVCvkvd+p13E2tTsJu29Mh9VuROOiXQalW1X4GRns9zZ17l6lvUHQ=="}';
    $row = json_decode($str, true);s
    $row['subPacks'] = json_decode($row['subPacks'], true);
    var_dump(json_encode($row));
    die();*/
    /*$data = [];
    $data['accountId'] = '6212461270000960118';
    $data['reqType'] = '1';
    $data['reqTxCode'] = 'bidApply';
    $data['reqTxDate'] = '20170503';
    $data['reqTxTime'] = '132536'; //000001
    $data['reqSeqNo'] = '000001';
    $data['reqOrderId'] = '';
    $handler = new Handler('transactionStatusQuery', $data);
    $result = $handler->api();
    var_dump($result);die();

    $data = [];
    $data['accountId'] = '6212461270000010146';
    $data['startDate'] = '20160906';
    $data['endDate'] = '20170427';
    $data['productId'] = '005053';
    $data['pageNum'] = '1';
    $data['pageSize'] = '10';
    $handler = new Handler('debtDetailsQuery', $data);
    $result = $handler->api();
    var_dump($result);die();*/

    $data = [];
    $data['accountId'] = '6212461270000010146';
    $data['startDate'] = '20160906';
    $data['endDate'] = '20170427';
    $data['type'] = '0';
    $data['tranType'] = '';
    $data['pageNum'] = '1';
    $data['pageSize'] = '10';
    $handler = new Handler('accountDetailsQuery', $data);
    $result = $handler->api();
    var_dump($result);die();


    $user = User::where('userId', '10689')->first();
    $remark = '新手50元红包';
    $result = CTAPI::redpack($user->userId, 50.00, 'new_user', $remark);
    var_dump($result);die();

    $custody = Redis::getUser('1161188', 'custody_id');
    var_dump($custody);die();
    $v = Redis::zScore('ssssttt', '10689');
    $v = number_format($v, 0,'','');
    var_dump($v);die();
    try {
        throw new \Exception("Error Processing Request", 1);
    } catch(\Exception $e) {
        Log::write($e->getMessage(), [], 'common');
    }
    Log::write('测试：成功', [], 'common');
    die();
    $user = User::where('userId', '10689')->first();
    $result = CTAPI::refreshUserBank($user);
    var_dump($result);die();
  /*  $m = '{"bgData":"{\"bankCode\":\"30050000\",\"seqNo\":\"273188\",\"txTime\":\"153308\",\"channel\":\"000002\",\"sign\":\"slkgDg6X7hBV8qpebuPjSgQ9RECwDw8JPlbpaYt1W82TvPC/F/BqjWamPAiQo9VvosXFtJHcnnYz2jpzHVEA5gktAs28Wm2GwAzHKa3NDC6o3lScGscO+IClRZMgANnHh1tZYSUxGgVP9/GECCXwSDi5E4lUMGNAEkhDoMrqOcw=\",\"retCode\":\"00000000\",\"version\":\"10\",\"retMsg\":\"success\",\"txAmount\":\"5\",\"accountId\":\"6212461270000960118\",\"instCode\":\"00170001\",\"txCode\":\"withdraw\",\"acqRes\":\"\",\"txDate\":\"20170411\"}"}';

    $params = json_decode($m, true);
    $data = json_decode($params['bgData'], true);

    if(!Handler::verify($data)) {
        Log::write('[WITHDRAW]验签失败', [], 'custody');
        Handler::back();
    }

    $return = [];
    $tradeNo = $data['txDate'] . $data['txTime'] . $data['seqNo'];
    $return['tradeNo'] = $tradeNo;
    $return['result'] = $data['retCode'];
    if($data['retCode']==Handler::SUCCESS) {
        $return['status'] = 1;
    } else {
        $return['status'] = 0;
    }

    Withdraw::after($return);

    Handler::back();


    $m = '{"bgData":"{\"bankCode\":\"30050000\",\"seqNo\":\"526775\",\"txTime\":\"132330\",\"channel\":\"000003\",\"mobile\":\"18760419185\",\"sign\":\"jPPo0tpiCAX4m4/L3DMjw/JTJ23AT5bFy2jphae0g7PFUoiu2anapC+VwlgRAeyUF9n46FzzEVGeJ/Qy/tgbboqdAkiqPP9weabrw9KUT4tFLGjGUTdE6TZV6+APtGd1v4LaGVW/ADBoy7uN8qTUm/IZqMX87k73L13BgS8adDg=\",\"retCode\":\"00000000\",\"version\":\"10\",\"retMsg\":\"\",\"txAmount\":\"1\",\"accountId\":\"6212461270000960118\",\"instCode\":\"00170001\",\"txCode\":\"directRecharge\",\"acqRes\":\"\",\"txDate\":\"20170411\"}"}';

    $params = json_decode($m, true);
    $data = json_decode($params['bgData'], true);

    if(!Handler::verify($data)) {
        Log::write('[RECHARGE]验签失败', [], 'custody');
        Handler::back();
    }

    $return = [];
    $serialNumber = $data['txDate'] . $data['txTime'] . $data['seqNo'];
    $return['tradeNo'] = $serialNumber;
    $return['result'] = $data['retCode'];
    if($data['retCode']==Handler::SUCCESS) {
        $return['status'] = 1;
    } else {
        $return['status'] = 0;
    }

    Recharge::after($return);

    Handler::back();*/

    /*$m = '{"bgData":"{\"bankCode\":\"30050000\",\"seqNo\":\"811470\",\"txTime\":\"091354\",\"sign\":\"CJ7lmwEl6703L73d1l3BVoiSpQTt7/uXMAT0Iz7h7cFwRdiXy+65v00tI3AfWrJsUr/XyLg02BsKbjQfMSgjBqQBrqCU5eag+/AhU/n7lcTpsts2o5oIzM3F+3zmoeqZEWRo1PiQnFoQXEStNxip0Bq/1aO1SqwanM3BrjWIjN4=\",\"channel\":\"000002\",\"retCode\":\"00000000\",\"version\":\"10\",\"retMsg\":\"DBEMU.模拟器返回成功\",\"accountId\":\"6212461270000010146\",\"instCode\":\"00170001\",\"txCode\":\"accountOpen\",\"acqRes\":\"1000002169\",\"txDate\":\"20170411\"}"}';
    $params = json_decode($m, true);

    $data = json_decode($params['bgData'], true);

    if(!Handler::verify($data)) {
        Log::write('[OPEN_ACCOUNT]验签失败', [], 'custody');
        Handler::back();
    }

    $return = [];
    // authCode
    if($data['retCode']==Handler::SUCCESS) {
        User::where('userId', $data['acqRes'])->update(['custody_id', $data['custody_id']]);
    }

    Handler::back();*/

    /*$str = '{"bankCode":"30050000","seqNo":"112345","txTime":"203214","sign":"6sZAIS3myuDTgYQyaHs+MCXVwm/XYBkKTU7KtjYtA0GsF1fa1JmSJTYe1ZtpHtw4siEhKjtsY0hm58b2g+IzxLnkDTUe4euTLgoXSDQXVqyWPZpSzLmp8VeGhNOUk1FqyQCG+vjBExaf+cRYHlZLYOsYWTDKSABtJGBZ8en8qp0=","channel":"000002","retCode":"CA110150","version":"10","retMsg":"DBEMU.模拟器返回成功","accountId":"","instCode":"00170001","txCode":"accountOpen","acqRes":"","txDate":"20170401"}';
    $params = json_decode($str, true);
    $result = custody\Handler::verify($params);
    var_dump($result);die();*/

    $data = [];
    $data['accountId'] = '6212461270000010146';
    $data['option'] = '1';
    $data['mobile'] = '18760419185';
    $data['retUrl'] = 'http://www.xwsdvip.com/custody/openAccount1';
    $data['notifyUrl'] = 'http://www.xwsdvip.com/custody/openAccount';
    $handler = new Handler('mobileModify', $data);
    
    $sn = $handler->getSN();
    Log::write($sn, [], 'custody');

    die();
    $result = $handler->form();
    die();

    $data = [];
    $data['accountId'] = '6212461270000010146';
    // $data['seqNo'] = '002345'; //可不加，会自动生成
    // $data['channel'] = Handler::M_WEB;
    // $data['srvTxCode'] = 'accountOpenPlus';
    // smsCodeApply
    $handler = new Handler('cardBindDetailsQuery', $data);
    $result = $handler->api();
    var_dump($handler->getSN());
    var_dump($handler->getJson());
    var_dump($result);die();

    $data = [];
    $data['idType'] = '01';
    $data['idNo'] = '350824199001105476';
    $data['name'] = '廖金灵';
    $data['mobile'] = '18760419185';
    $data['cardNo'] = '6212261402010835780';
    $data['email'] = '360197197@qq.com';
    $data['acctUse'] = '00000';
    $data['retUrl'] = 'http://www.xwsdvip.com/custody/openAccount1';
    $data['notifyUrl'] = 'http://www.xwsdvip.com/custody/openAccount';
    // $data['seqNo'] = '112345';
    $data['channel'] = custody\Handler::M_WEB;

    $handler = new Handler('accountOpen', $data);
    $handler->form();die();

    $url = 'https://api.tongdun.cn/octopus/login.fields.query/v1';
    $params = ['partner_code'=>'xwsd', 'partner_key'=>'cd021ae4453d40a195247a80967b452a'];
    $result = NetworkHelper::fsPost($url, $params);
    var_dump($result);die();

    var_dump(urldecode('%E6%8F%90%E4%BA%A4%E6%88%90%E5%8A%9F%EF%BC%81'));
    var_dump(_cut_float(1.23723, 2));
    die();
    $bid = UserBid::find(11046);
    $bid->activity();
    die('sss');
    if(time()<strtotime('2017-01-01 00:30:00')) {
      $redis = RedisFactory::create();
      $redis->set('exnum_level1', 5);
      $redis->set('exnum_level2', 10);
      $redis->set('exnum_level3', 30);
      $redis->set('exnum_level4', 50);
      $redis->set('exnum_level5', 100);
      $redis->set('exnum_level6', 200);
    }
    die();
    $redis = RedisFactory::create();
    $redis->set('exnum_level4', 1);die();
    /*$redis = RedisFactory::create();
    for ($i=0; $i<3; $i++) { 
      $redis->lpush('prize1', 1);
    }
    for ($i=0; $i<2; $i++) { 
      $redis->lpush('prize2', 1);
    }
    for ($i=0; $i<1; $i++) { 
      $redis->lpush('prize3', 1);
    }
    die();*/
    $bid = UserBid::find(11046);
    $bid->activity();
    $bid->activity();
    $bid->activity();
    $bid->activity();
    $bid->activity();
    $bid->activity();
    $bid->activity();
    $bid->activity();
    $bid->activity();
    $bid->activity();
    die();
    $remark = '获得:1.88元,项目@crtrNumber{80001551},债权转让未结利息';
    $mark = '@crtrNumber';
    $num = preg_match_all('/(?<='.$mark.'{)\d+(?=})/', $remark, $matches);
    foreach ($matches[0] as $value) {
      $search = $mark.'{'.$value.'}';
      var_dump($search);
      $name = '';
      $link = '';
      $replace = '<a target="_blank" href="'.$link.$value.'">'.$name.'</a>';
      $remark = str_replace($search, $replace, $remark);
      var_dump($remark);
    }
    die();
    $nextRepayment = Invest::where('oddMoneyId', 49405)
      ->whereIn('status', [0, 4])
      ->orderBy('endtime', 'desc')
      ->first();
    $repayTime = strtotime(date('Y-m-d 00:00:00', strtotime($nextRepayment->endtime))) + 24*60*60;
    if($nextRepayment->status==4||($nextRepayment->status==0&&$repayTime<time())) {
      var_dump('error');die();
    }
    var_dump('success');die();
    $oddNumber = $this->getQuery('oddNumber', '');
    $odd = Odd::where('oddNumber', $oddNumber)->first();
    if($odd) {
      $odd->controlPhotos = str_replace('https://asset.hcjrfw.com/uploads/images/https:', 'https:', $odd->controlPhotos);
      $odd->oddExteriorPhotos = str_replace('https://asset.hcjrfw.com/uploads/images/https:', 'https:', $odd->oddExteriorPhotos);
      $odd->oddPropertyPhotos = str_replace('https://asset.hcjrfw.com/uploads/images/https:', 'https:', $odd->oddPropertyPhotos);
      $odd->bankCreditReport = str_replace('https://asset.hcjrfw.com/uploads/images/https:', 'https:', $odd->bankCreditReport);
      $odd->otherPhotos = str_replace('https://asset.hcjrfw.com/uploads/images/https:', 'https:', $odd->otherPhotos);
      $odd->save();
    }

    die();
    $status = API::identify(['name'=>'嘻嘻嘻', 'cardnum'=>'350824199001105476']);
    var_dump($status);
    $status = API::identify(['name'=>'廖金灵', 'cardnum'=>'350824199001105476']);
    var_dump($status);die();
    
    $mchntcd = Registry::get('config')->get('fuiou')->get('mchntcd');
    $key = Registry::get('config')->get('fuiou')->get('key');

    $result = [];
    $result['TYPE'] = '02';
    $result['VERSION'] = '2.0';
    $result['ORDERID'] = 'test1234565';
    $result['RESPONSECODE'] = '0000';
    $result['RESPONSEMSG'] = '支付成功！';
    $result['MCHNTORDERID'] = '110462a48b62d40515ad';
    $result['AMT'] = 1;
    $result['BANKCARD'] = '6212261402010835780';

    $list = [$result['TYPE'], $result['VERSION'], $result['RESPONSECODE'], $mchntcd, $result['MCHNTORDERID'], $result['ORDERID'], $result['AMT'], $result['BANKCARD'], $key];
    $sign = md5(implode('|', $list));

    $result['SIGN'] = $sign;

    $result = NetworkHelper::post("http://www.xiaowei.com/itfapp/recharge", http_build_query($result));
    $this->pr($result);
    die();
    var_dump(7&2);
    var_dump(3&2);
    die();
    var_dump(empty(''));
    $str = '单号@oddNumber{54654646465}提前还款@crtrNumber{23423222}！';
    $result = preg_match_all('/(?<=@oddNumber{)\d+(?=})/', $str, $matches);
    var_dump($result);
    var_dump($matches);
    die();
    $num = 999;
    $num = NumberHelper::zeroPrefix($num, 4);
    $num = 'A' . $num;
    var_dump($num);
    die();
    $oddMoney = OddMoney::find(40860);
    $oddMoney->generateProtocol();
    die();
    // DB::statement('update '.with(new GQLottery)->getTable().' set userId=\'10689\'');die();
    set_time_limit(0);
    DB::statement('lock tables '.with(new GQLottery)->getTable().' write');
    
    var_dump('锁定'.time());
    sleep(10);
    $rows = GQLottery::where('userId', '10099')->update(['userId'=>'10066']);
    var_dump($rows);
    DB::statement('unlock tables');
    die();
    $status = API::identify(['name'=>'嘻嘻嘻', 'cardnum'=>'350824199001105476']);
    var_dump($status);
    $status = API::identify(['name'=>'廖金灵', 'cardnum'=>'350824199001105476']);
    var_dump($status);
    die();
    set_time_limit(0);
    $id = '20160910000003';//20160910000004 //20160910000004
    $type = 'invest';

    if($type=='invest') {
      $oddMoneys = OddMoney::with('protocol')->where('oddNumber', $id)->where('type', 'invest')->get();
      foreach ($oddMoneys as $oddMoney) {
        $result = $oddMoney->generateProtocol(false);
      }
    } else if($type=='credit') {
      $oddMoneys = OddMoney::with('protocol')->where('bid', $id)->where('type', 'credit')->get();
      foreach ($oddMoneys as $oddMoney) {
        $oddMoney->generateProtocol(false);
      }
    }

    $rdata = [];
    $rdata['status'] = 1;
    $rdata['msg'] = '生成成功！';
    $this->backJson($rdata);
    die();
    $word = '&lt;p&gt;&lt;span style=&quot;font-family:宋体;font-size:16px&quot;&gt;车辆品牌型号:纳智捷优6&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;font-family:宋体;font-size:16px&quot;&gt;行驶公里数：33077KM&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;font-family:宋体;font-size:16px&quot;&gt;车身颜色：白色&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;font-family:宋体;font-size:16px&quot;&gt;排量：1.8T&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;font-family:宋体;font-size:16px&quot;&gt;购买价格：13.68万&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;font-family:宋体;font-size:16px&quot;&gt;质押估价：6万&lt;/span&gt;&lt;/p&gt;';
    $word = strip_tags(str_replace('</p>', '</p>|', _decode($word)));
    $word = trim(str_replace('：', ':', $word), '|');
    $words = explode('|', $word);
    $list = [];
    foreach ($words as $value) {
      $arr = explode(':', $value);
      $list[$arr[0]] = $arr[1];
    }
    var_dump($list);
    die();
    $res = json_decode('{"msg":"{\"status\":\"0\",\"msg\":\"ok\",\"result\":{\"idcard\":\"350824199001105476\",\"realname\":\"\u5ed6\u91d1\u7075\",\"province\":\"\u798f\u5efa\",\"city\":\"\u9f99\u5ca9\",\"town\":\"\u6b66\u5e73\u53bf\",\"sex\":\"\u7537\",\"birth\":\"1990\u5e7401\u670810\u65e5\",\"verifystatus\":\"0\",\"verifymsg\":\"\u606d\u559c\u60a8\uff0c\u8eab\u4efd\u8bc1\u6821\u9a8c\u4e00\u81f4\uff01\"}}","status":"success","data":"20160906000000000005"}', true);
    var_dump($res);
    $status = API::identify(['name'=>'嘻嘻嘻', 'cardnum'=>'350824199001105476']);
    var_dump($status);
    $status = API::identify(['name'=>'廖金灵', 'cardnum'=>'350824199001105476']);
    var_dump($status);
  }

  public function exnumAction() {
    die();
    $user = $this->getUser();
    if($user->userId!='10689') {
      die('no no no');
    }
    $redis = RedisFactory::create();
    $redis->incr('exnum_level1');
    $redis->incr('exnum_level1');
    $redis->incr('exnum_level1');
    $redis->incr('exnum_level1');
    $redis->incr('exnum_level1');
    $redis->incr('exnum_level2');
    $redis->incr('exnum_level2');
    $redis->incr('exnum_level2');
    $redis->incr('exnum_level2');
    $redis->incr('exnum_level2');
    $redis->incr('exnum_level2');
    $redis->incr('exnum_level2');
    $redis->incr('exnum_level2');
    $redis->incr('exnum_level2');
    $redis->incr('exnum_level2');
  }

  public function acAction() {
    // $recharge = Recharge::where('serialNumber', '20160927b3fc4c84dac123e850')->first();
    // $user = User::find('1000015693');
    
    $oddMoney = OddMoney::find(54550);
    $acTool = new ACTool($oddMoney, 'assign', 0);
    $r = $acTool->send();
    var_dump($r);
/*
    echo '<br>';
    echo '----------------------';
    echo '<br>';

    sleep(5);

    $acTool = new ACTool($oddMoney, 'tender', 1);
    $r = $acTool->send();
    var_dump($r);*/
  }
  
  public function twoAction() {
    die('lock');
    set_time_limit(0);
    $records = OddClaims::with('oddMoney.invests')->where('status', 1)->orderBy('addtime', 'asc')->get();
    $list = [];
    foreach ($records as $record) {
   		/*echo $record->oddmoneyId . '&nbsp;&nbsp;&nbsp;&nbsp;' 
   			. $record->userIdFrom . ' => ' . $record->userId 
   			. '&nbsp;&nbsp;&nbsp;&nbsp;' . $record->addtime . '<br>';*/
   		foreach ($record->oddMoney->invests as $invest) {
   			if(strtotime($invest->endtime)<strtotime($record->addtime)) {
   				$list[$invest->id] = $record->userIdFrom;
   			} else {
   				$list[$invest->id] = $record->userId;
   			}
   		}
    }
    $time1 = microtime(true);
    $i = 0;
    $k = 0;
    $count = count($list);
    foreach ($list as $key => $value) {
      if($i==0) {
        DB::beginTransaction();
        echo '------------- begin -----------------<br>';
      }
      
      $status = Invest::where('id', $key)->update(['userId'=>$value]);
      $str = 'False';
      if($status) {
        $str = 'True';
      }
    	echo $key . '&nbsp;&nbsp;&nbsp;&nbsp;' . $value . '&nbsp;&nbsp;&nbsp;&nbsp;' . $str . '<br>';
      if($i==50) {
        DB::commit();
        $i = 0;
        echo '------------- commit -----------------<br>';
      } else {
        if(($count-1)==$k) {
          DB::commit();
          echo '------------- commit -----------------<br>';
        }
      }

      $i++;
      $k++;
    }
    $time2 = microtime(true);
    echo '插入耗时:' . ($time2-$time1);
  }


  public function oneAction() {
    die('lock');
    set_time_limit(0);
    $begin = 0;
    $records = OddMoney::where('type', 'invest')->orderBy('time', 'asc')->get();
    $i = 0;
    $count = count($records);
    foreach ($records as $k => $record) {
      if($i==0) {
        DB::beginTransaction();
        echo '------------- begin -----------------<br>';
      }
      $status = Invest::where('oddMoneyId', $record->id)->update(['userId'=>$record->userId]);
      if($i==50) {
        DB::commit();
        $i = 0;
        echo '------------- commit -----------------<br>';
        continue;
      } else {
        if(($count-1)==$k) {
          DB::commit();
          echo '------------- commit -----------------<br>';
        }
      }
      $i++;
    }
  }

  public function threeAction() {
    die('lock');
    set_time_limit(0);
    $begin = 0;
    $records = OddMoney::where('type', 'loan')->orderBy('time', 'asc')->get();
    $i = 0;
    $count = count($records);
    foreach ($records as $k => $record) {
      if($i==0) {
        DB::beginTransaction();
        echo '------------- begin -----------------<br>';
      }
      $status = Interest::where('oddNumber', $record->oddNumber)->update(['oddMoneyId'=>$record->id]);
      var_dump($i);
      if($i==50) {
        DB::commit();
        $i = 0;
        echo '------------- commit -----------------<br>';
        continue;
      } else {
        if(($count-1)==$k) {
          DB::commit();
          echo '------------- commit -----------------<br>';
        }
      }
      $i++;
    }
  }

  public function autoAction() {
    die('lock');
    set_time_limit(0);
    $autoInvests = AutoInvest::whereRaw('1=1')->get();
    $typesJsonAll = [
      ['id'=>11, 'name'=>'1月标(16%)', 'rate'=>0.16, 'month'=>1, 'type'=>'diya'],
      ['id'=>12, 'name'=>'2月标(18%)', 'rate'=>0.18, 'month'=>2, 'type'=>'diya'],
      ['id'=>13, 'name'=>'3月标(18.6%)', 'rate'=>0.186, 'month'=>3, 'type'=>'diya'],
      ['id'=>14, 'name'=>'6月标(18.6%)', 'rate'=>0.186, 'month'=>6, 'type'=>'diya'],
      ['id'=>15, 'name'=>'12月标(19%)', 'rate'=>0.19, 'month'=>12, 'type'=>'diya'],
      ['id'=>16, 'name'=>'24月标(20%)', 'rate'=>0.20, 'month'=>24, 'type'=>'diya'],
      ['id'=>21, 'name'=>'1月标(16%)', 'rate'=>0.16, 'month'=>1, 'type'=>'xingyong'],
      ['id'=>22, 'name'=>'3月标(18.6%)', 'rate'=>0.186, 'month'=>3, 'type'=>'xingyong'],
      ['id'=>31, 'name'=>'1月标(16%)', 'rate'=>0.16, 'month'=>1, 'type'=>'danbao'],
      ['id'=>32, 'name'=>'2月标(18%)', 'rate'=>0.18, 'month'=>2, 'type'=>'danbao'],
      ['id'=>33, 'name'=>'3月标(18.6%)', 'rate'=>0.186, 'month'=>3, 'type'=>'danbao'],
      ['id'=>34, 'name'=>'6月标(18.6%)', 'rate'=>0.186, 'month'=>6, 'type'=>'danbao'],
      ['id'=>35, 'name'=>'12月标(19%)', 'rate'=>0.19, 'month'=>12, 'type'=>'danbao'],
      ['id'=>36, 'name'=>'24月标(20%)', 'rate'=>0.20, 'month'=>24, 'type'=>'danbao'],
    ];
    $typesJson1 = [
      ['id'=>11, 'name'=>'1月标(16%)', 'rate'=>0.16, 'month'=>1, 'type'=>'diya'],
      ['id'=>21, 'name'=>'1月标(16%)', 'rate'=>0.16, 'month'=>1, 'type'=>'xingyong'],
      ['id'=>31, 'name'=>'1月标(16%)', 'rate'=>0.16, 'month'=>1, 'type'=>'danbao'],
    ];
    $typesJson2 = [
      ['id'=>12, 'name'=>'2月标(18%)', 'rate'=>0.18, 'month'=>2, 'type'=>'diya'],
      ['id'=>32, 'name'=>'2月标(18%)', 'rate'=>0.18, 'month'=>2, 'type'=>'danbao'],
    ];
    $typesJson3 = [
      ['id'=>13, 'name'=>'3月标(18.6%)', 'rate'=>0.186, 'month'=>3, 'type'=>'diya'],
      ['id'=>22, 'name'=>'3月标(18.6%)', 'rate'=>0.186, 'month'=>3, 'type'=>'xingyong'],
      ['id'=>33, 'name'=>'3月标(18.6%)', 'rate'=>0.186, 'month'=>3, 'type'=>'danbao'],
    ];
    $typesJson6 = [
      ['id'=>14, 'name'=>'6月标(18.6%)', 'rate'=>0.186, 'month'=>6, 'type'=>'diya'],
      ['id'=>34, 'name'=>'6月标(18.6%)', 'rate'=>0.186, 'month'=>6, 'type'=>'danbao'],
    ];
    foreach ($autoInvests as $autoInvest) {
      $staystatus = 0;
      $typesJson = json_encode($typesJsonAll);
      $moneyType = 1;
      $investTimeStart = $autoInvest->investTimeStart;
      $investTimeEnd = $autoInvest->investTimeEnd;
      if($autoInvest->investTimeStyle=='limit') {
        if($investTimeStart>$investTimeEnd||$investTimeStart>6||($investTimeStart==0&&$investTimeEnd==0)) {
          $typesJson = json_encode($typesJsonAll);
          $staystatus = 1;
        } else {
          if(($investTimeStart==0||$investTimeStart==1)&&$investTimeEnd==1) {
            // echo '1';
            $typesJson = json_encode($typesJson1);
          } else if(($investTimeStart==0||$investTimeStart==1)&&$investTimeEnd==2) {
            $typesJson = json_encode(array_merge($typesJson1, $typesJson2));
          } else if(($investTimeStart==0||$investTimeStart==1)&&$investTimeEnd==3) {
            $typesJson = json_encode(array_merge($typesJson1, $typesJson2, $typesJson3));
          } else if(($investTimeStart==0||$investTimeStart==1)&&$investTimeEnd==4) {
            $typesJson = json_encode(array_merge($typesJson1, $typesJson2, $typesJson3));
          } else if(($investTimeStart==0||$investTimeStart==1)&&$investTimeEnd==5) {
            $typesJson = json_encode(array_merge($typesJson1, $typesJson2, $typesJson3));
          } else if(($investTimeStart==0||$investTimeStart==1)&&$investTimeEnd>=6) {
            // echo '2';
            $typesJson = json_encode($typesJsonAll);
          } else {
            if($investTimeStart==2&&$investTimeEnd==2) {
              $typesJson = json_encode($typesJson2);
            } else if($investTimeStart==2&&$investTimeEnd==3) {
              $typesJson = json_encode(array_merge($typesJson2, $typesJson3));
            } else if($investTimeStart==2&&$investTimeEnd==4) {
              $typesJson = json_encode(array_merge($typesJson2, $typesJson3));
            } else if($investTimeStart==2&&$investTimeEnd==5) {
              $typesJson = json_encode(array_merge($typesJson2, $typesJson3));
            } else if($investTimeStart==2&&$investTimeEnd>=6) {
              $typesJson = json_encode(array_merge($typesJson2, $typesJson3, $typesJson6));
            } else {
              if($investTimeStart==3&&$investTimeEnd==3) {
                $typesJson = json_encode($typesJson3);
              } else if($investTimeStart==3&&$investTimeEnd==4) {
                $typesJson = json_encode($typesJson3);
              } else if($investTimeStart==3&&$investTimeEnd==5) {
                $typesJson = json_encode($typesJson3);
              } else if($investTimeStart==3&&$investTimeEnd>=6) {
                $typesJson = json_encode(array_merge($typesJson3, $typesJson6));
              } else {
                if($investTimeStart==4&&$investTimeEnd==4) {
                  $staystatus = 1;
                } else if($investTimeStart==4&&$investTimeEnd==5) {
                  $staystatus = 1;
                } else if($investTimeStart==4&&$investTimeEnd>=6) {
                  $typesJson = json_encode($typesJson6);
                } else {
                  if($investTimeStart==5&&$investTimeEnd==5) {
                    $staystatus = 1;
                  } else if($investTimeStart==5&&$investTimeEnd>=6) {
                    $typesJson = json_encode($typesJson6);
                  } else {
                    if($investTimeStart==6&&$investTimeEnd>=6) {
                      $typesJson = json_encode($typesJson6);
                    } else {
                      $staystatus = 1;
                    }
                  }
                }
              }
            }
          }
        }
      } else {
        $typesJson = json_encode($typesJsonAll);
      }
      $investMoneyLower = $autoInvest->investMoneyLower==null?0:$autoInvest->investMoneyLower;
      $investMoneyUper = $autoInvest->investMoneyUper==null?99999999:$autoInvest->investMoneyUper;
      if($autoInvest->investMoneyLower>$autoInvest->investMoneyUper) {
        $staystatus = 1;
      }
      $autoInvest->staystatus = $staystatus;
      $autoInvest->typesJson = $typesJson;
      $autoInvest->moneyType = $moneyType;
      $status = $autoInvest->save();
      if($status) {
        echo '成功'.'<br>';
      } else {
        echo '失败'.$autoInvest->userId.'<br>';
      }
    }

  }

  public function oddAction() {
    /*$queue = models\Queue::with('autoInvest', 'user')->where('userId', '10090')->first();
    $user = $queue->user;
    $autoInvest = $queue->autoInvest;

     if($autoInvest->autostatus==1 && 
          $autoInvest->staystatus==0 && 
          ($user->fundMoney - $autoInvest->investEgisMoney) >= $autoInvest->investMoneyLower && 
          $autoInvest->typesJson != '' && 
          $autoInvest->typesJson != '[]') {

        echo 'yes';
      } else {
        echo 'no';
      }
    die();*/
    $queues = models\Queue::with('autoInvest', 'user')->orderBy('id', 'asc')->get();
    $num = 0;
    $total = 0;
    foreach ($queues as $queue) {
      $autoInvest = $queue->autoInvest;
      $user = $queue->user;

      if(!$user || !$autoInvest) {
        continue;
      }

      $moneyable = true;
      if($autoInvest->investMoneyUper!=null&&$autoInvest->investMoneyUper<$autoInvest->investMoneyLower) {
          $moneyable = false;
      }
            
      if($autoInvest->autostatus==1 && 
          $autoInvest->staystatus==0 && 
          ($user->fundMoney - $autoInvest->investEgisMoney) >= $autoInvest->investMoneyLower && 
          $moneyable &&
          $autoInvest->typesJson != '' && 
          $autoInvest->typesJson != '[]') {

          
      } else {
        $num++;
        $total = $total + $user->fundMoney;
        $str = true;
        if($autoInvest->typesJson=='' || $autoInvest->typesJson=='[]') {
          $str = 'false';
        }
        echo $user->userId . ' ------- ' . $user->username . ' ------- ' . $user->fundMoney . ' ------- ' . $user->investEgisMoney . ' -------- ' . $autoInvest->investMoneyLower . ' -------- ' . $autoInvest->investMoneyUper. ' -------- ' . $autoInvest->autostatus. ' -------- ' . $autoInvest->staystatus .'--------'. $str .'<br>';
        // echo $user->userId . '   ' . $user->username . '   ' . $user->fundMoney . '<br>';
      }
    }
    echo $num . '-----' . $total;
  }

  public function faanAction() {
    $params = $this->getAllPost();
    foreach ($params as $key => $value) {
      echo $key . '~with~' . $value . '<br>';
    }
  }

  public function queryAction() {
    $trade = $this->getQuery('trade');
    ThirdQuery::baofooRecharge($trade);
  }

  public function bankAction() {
    set_time_limit(0);
    $banks = UserBank::with('agree')->where('noAgree', '<>', '')->get();
    foreach ($banks as $bank) {
      if($bank->agree) {
        $bank->agreeID = $bank->agree->id;
        $bank->save();
      }
    }
  }

  /*public function logAction() {
    set_time_limit(0);
    $list = Invest::where('status', 1)->where('operatetime', '>=', '2016-10-29 00:00:00')->where('operatetime', '<=', '2016-10-29 13:00:00')->get();
    if(count($list)==433) {
      foreach ($list as $key => $row) {
        $remark = '获得:'.$row->realinterest.'元,项目@oddNumber{'.$row->oddNumber.'},第'.$row->qishu.'期利息';
        $status = DB::insert('insert into user_moneylog (`serialNumber`, `oddNumber`, `mkey`, `type`, `mode`, `mvalue`, `remark`, `userId`, `time`, `operator`, `status`, `xml`, `resultStatus`, `investUserId`, `remain`) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', ['20161029000000000001', $row->oddNumber, 'work', 'interest', 'in', $row->realinterest, $remark, $row->userId, '2016-10-28 12:30:00', 'sysadmin', '0', NULL, '0', '', '0']);
        $r = '失败！';
        if($status) {
          $r = '成功！';
        }
        echo $key . ' ' . $row->userId . ' ' . $remark . '    '.$r.'<br>';
      }
    }
  }*/

  public function imageAction() {
    $filiales = Filiale::whereRaw('1=1')->get();
    foreach ($filiales as $filiale) {
      $photos = $this->handlePhotos($filiale->photos);
      $filiale->photos = $photos;
      $filiale->save();
    }

    $activities = Activity::whereRaw('1=1')->get();
    foreach ($activities as $activity) {
      $photos = $this->handlePhotos($activity->photos);
      $activity->photos = $photos;
      $activity->save();
    }
  }

  public function handlePhotos($photos) {
    if(!$photos) {
      return $photos;
    }
    $list = explode('|', $photos);
    $newList = [];
    foreach ($list as $photo) {
      if(strpos($photo, '/uploads/images/')===false) {
        $newList[] = '/uploads/images/' . $photo;
      }
    }
    return implode('|', $newList);
  }

  public function getImageList($items) {
    $list = [];
    foreach ($items as $item) {
      $list[] = str_replace('https://asset.hcjrfw.com', '', $item['max']);
    }
    return $list;
  }

  public function getImages($string, $return='array', $type='all', $prev=WEB_ASSET) {
    $list = explode('|', $string);
    $result = [];
    foreach ($list as $item) {
      $img = explode(',', $item);
      if($type=='small') {
        $result[] = strpos($img[1], 'http')===0?$img[1]:$prev.$img[1];
      } else if($type=='normal') {
        $result[] = strpos($img[0], 'http')===0?$img[0]:$prev.$img[0];
      } else {
        $result[] = [
          'max'=> strpos($img[0], 'http')===0?$img[0]:$prev.$img[0],
          'min'=> strpos($img[1], 'http')===0?$img[1]:$prev.$img[1], 
          'normal'=> strpos($img[1], 'http')===0?$img[1]:$prev.$img[1]
        ];
      }
    }
    if($return=='string') {
      if($type=='all') {
        return $this->$column;
      } else {
        return implode('|', $result);
      }
    } 
    return $result;
  }

  public function gpsAction() {
    $token = $this->getToken();
    var_dump($token);
    $array = array('福州一部1','福州二部1','福州三部1','龙岩分部1','南平分部1','宁德分部1','莆田分部1','泉州分部1','厦门分部1','三明分部1','泉州贷后','海口分部1','阿代拖车');
    $data = $this->getAllImei('南平分部1',$token);
    var_dump($data);
    /*foreach($array as $v){
        $data = getAllImei($v,$token);
        foreach($data['data'] as $value){
            $sql_str = "SELECT imei FROM tmp_gps WHERE imei = '".$value['imei']."'";
            $imei = $db->getOne($sql_str);
            if($imei){
                $sql_str = "UPDATE tmp_gps SET gpstime = '".date("Y-m-d H:i:s",$value['gps_time'])."', lon = '".$value['lng']."', lat = '".$value['lat']."' WHERE imei = '".$value['imei']."'";
            }else{
                $sql_str = "INSERT INTO tmp_gps (imei,gpstime,lon,lat) VALUES ('".$value['imei']."','".date("Y-m-d H:i:s",$value['gps_time'])."','".$value['lng']."','".$value['lat']."')";
            }
            $db->execute($sql_str);
        }
        $data = "";
        $data = getAllDrive($v,$token);
        foreach($data['data'] as $val){
            $sql_str = "UPDATE tmp_gps SET tname = '".$val['number']."', addtime = '".date("Y-m-d H:i:s",$val['in_time'])."',endtime = '".date("Y-m-d H:i:s",$val['out_time'])."',chepai = '".$val['name']."'  WHERE imei= '".$val['imei']."'";
            $db->execute($sql_str);
        }
    }
    echo 'GPSOO-'.date("Y-m-d H:i:s").'OK'."\n";*/
  }

  public function xmlToArray($xmlstring) {
    $tmp = explode("\n", $xmlstring);
    $xmlstring = "";
    foreach ($tmp as $val) {
        $xmlstring .= trim($val);
    }
    return json_decode(json_encode((array) simplexml_load_string($xmlstring)), true);
  }

  public function getToken() {

    $key = '0D3B397E6D8D8943691189C09090F40C68572777';
    for ($i=1; $i<2; $i++) {
      $url = 'http://apiweb.dkwgps.com/SNService.asmx/TerbyKey?Key='.$key.'&page='.$i.'&rowcount=100';
      $xml = file_get_contents($url);
      $data = $this->xmlToArray($xml);
      var_dump($data);
      if(strpos($xml, '391216013063713')!==false) {
        var_dump($xml);
        var_dump($i);
      }
    }
    die();


    $gps_user = 'xwsd';
    $gps_pass = 'jingjing12';
    $url = 'http://api.gpsoo.net/1/auth/access_token';
    $time = time();
    $signature = md5(md5($gps_pass).$time);
    $url = 'http://api.gpsoo.net/1/auth/access_token?account='.$gps_user.'&time='.$time.'&signature='.$signature;
    var_dump($url);
    $re = file_get_contents($url);
    $array = json_decode($re,TRUE);
    if(empty($array['ret'])) return $array['access_token'];
    else return $array['msg'];
  }

  public function getAllImei($fenbu,$token){
    $url = 'http://api.gpsoo.net/1/account/monitor?access_token='.$token.'&map_type=BAIDU&target='.$fenbu.'&account=xwsd&time='.time();
    $re = file_get_contents($url);
    $array = json_decode($re,TRUE);
    return $array;
  }

  public function getAllDrive($fenbu,$token){
    $url = 'http://api.gpsoo.net/1/account/devinfo?target='.$fenbu.'&account=xwsd&access_token='.$token.'&time='.time();
    $re = file_get_contents($url);
    $array = json_decode($re,TRUE);
    return $array;
  }

  public function accashAction() {
    die('sl');
    set_time_limit(0);
    $oddMoneys = OddMoney::with('odd')
      ->where('time', '>=', '2016-11-11 00:00:00')
      ->where('time', '<', '2016-12-11 00:00:00')
      ->whereIn('status', [0, 1])
      ->where('type', '<>', 'loan')
      ->get();
    $list = [];
    $items = [];
    foreach ($oddMoneys as $oddMoney) {
      if($oddMoney->type=='credit') {
        if(strtotime($oddMoney->odd->addtime)>strtotime('2016-11-11 00:00:00')) {
          if(isset($list[$oddMoney->userId])) {
            $list[$oddMoney->userId] += $oddMoney->money;
          } else {
            $list[$oddMoney->userId] = $oddMoney->money;
          }
          if(isset($items[$oddMoney->userId])) {
            $items[$oddMoney->userId] += $this->getImiMoney($oddMoney->money, $oddMoney->odd->oddBorrowPeriod);
          } else {
            $items[$oddMoney->userId] = $this->getImiMoney($oddMoney->money, $oddMoney->odd->oddBorrowPeriod);
          }
        }
      } else {
        if(isset($list[$oddMoney->userId])) {
          $list[$oddMoney->userId] += $oddMoney->money;
        } else {
          $list[$oddMoney->userId] = $oddMoney->money;
        }
        if(isset($items[$oddMoney->userId])) {
          $items[$oddMoney->userId] += $this->getImiMoney($oddMoney->money, $oddMoney->odd->oddBorrowPeriod);
        } else {
          $items[$oddMoney->userId] = $this->getImiMoney($oddMoney->money, $oddMoney->odd->oddBorrowPeriod);
        }
      }
    }
    // imiMoney
    // cashMoney
    foreach ($list as $userId => $money) {
      $status = User::where('userId', $userId)->update(['cashMoney'=>$money, 'imiMoney'=>intval($items[$userId])]);
      if($status) {
        echo '用户'.$userId.'拥有投资金额'.$money.'成功，';
        echo '用户'.$userId.'拥有现金券'.$items[$userId].'成功<br>';
      } else {
        echo '用户'.$userId.'拥有投资金额'.$money.'失败，';
        echo '用户'.$userId.'拥有现金券'.$items[$userId].'失败<br>';
      }
    }
  }

  public function getImiMoney($money, $period) {
    if($period<=6) {
      return $money/4;
    } else if($period==12) {
      return $money/2;
    } else if($period==24) {
      return $money;
    }
    return 0;
  }

  public function getMeetingAction() {
    $user = $this->getUser();
    if(!in_array($user->userId, ['10689'])) {
      die('不要乱看哦！');
    }
    $redis = RedisFactory::create();
    $list = $redis->sMembers('meeting_users');
    $users = User::whereIn('userId', $list)->get(['phone', 'name']);
    echo '<table>';
    foreach ($users as $user) {
      echo '<tr><td>'.$user->name.'</td><td>'.$user->phone.'</td></tr>';
    }
    echo '</table>';
  }

  public function signAction() {
    /*<?xml version="1.0" encoding="UTF-8" standalone="yes"?><yimadai><accountNumber>1508000</accountNumber><tradeNo>20170813000001</tradeNo><tradeType>T002</tradeType><operType>0</operType><bidNo>20170813000001</bidNo><bidAmount>99000</bidAmount><adviceURL>http://api.hcjrfw.com/ok.php</adviceURL><tranlist><tran><outTradeNo>20170813000001100001535956887</outTradeNo><outName>1000015359</outName><inName>1000023669</inName><amount>50</amount><remark><![CDATA[自动投标：1000015359投资50-->操作成功]]></remark><secureCode>2a44e3bf3c8acbcfc159827a7cd7d4cc</secureCode></tran></tranlist></yimadai>*/
    /*
    <?xml version="1.0" encoding="UTF-8" standalone="yes"?><yimadai><accountNumber>1508000</accountNumber><tradeNo>80014769</tradeNo><tradeType>T004</tradeType><adviceURL>http://api.hcjrfw.com/ok.php</adviceURL><tranlist><tran><outTradeNo>201709070ae1216124d72aa897</outTradeNo><outName>10207</outName><inName>1000019598</inName><amount>19308.5</amount><remark><![CDATA[冻结确认]]></remark><secureCode>d13f0c21a301c047cb123b7addd6a887</secureCode></tran></tranlist></yimadai>

    <?xml version="1.0" encoding="UTF-8" standalone="yes"?><yimadai><accountNumber>1508000</accountNumber><tradeNo>80014768</tradeNo><tradeType>T004</tradeType><adviceURL>http://api.hcjrfw.com/ok.php</adviceURL><tranlist><tran><outTradeNo>2017090736db4b7e363680c034</outTradeNo><outName>10207</outName><inName>1000019598</inName><amount>4827.13</amount><remark><![CDATA[冻结确认]]></remark><secureCode>87f6ffb88ebdbafd39bcbd028050ad7b</secureCode></tran></tranlist></yimadai>
     */
    
    $office = '1508000';
    $rootTradeNo = '80014768';
    $tradeNo = '201709070ae1216124d72aa897';
    $from = '10207';
    $to = '1000019598';
    $money = '4827.13';
    $remark = '冻结确认';
    // $remark = '冻结确认';
    $key = 'b444454bfb8e929052a876ec4e46eaaa616b3de0b82eecf5af98e3636cc1bc46';

    $sign = strtolower(md5($office . $rootTradeNo . $tradeNo . $from . $to . $money . $remark . $key));
var_dump($sign);
    die();
    $office = '1508000';
    $rootTradeNo = '20170813000001';
    $tradeNo = '20170813000001100001535956887';
    $from = '1000015359';
    $to = '1000021821';
    $money = '50';
    $remark = '自动投标：'.$from.'投资'.$money.'-->操作成功';
    // $remark = '冻结确认';
    $key = 'b444454bfb8e929052a876ec4e46eaaa616b3de0b82eecf5af98e3636cc1bc46';

    $sign = strtolower(md5($office . $rootTradeNo . $tradeNo . $from . $to . $money . $remark . $key));

    echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><yimadai><accountNumber>1508000</accountNumber><tradeNo>'.$rootTradeNo.'</tradeNo><tradeType>T002</tradeType><operType>0</operType><bidNo>'.$rootTradeNo.'</bidNo><bidAmount>'.$oddMoney.'</bidAmount><adviceURL>http://api.hcjrfw.com/ok.php</adviceURL><tranlist><tran><outTradeNo>'.$tradeNo.'</outTradeNo><outName>'.$from.'</outName><inName>'.$to.'</inName><amount>'.$money.'</amount><remark><![CDATA['.$remark.']]></remark><secureCode>'.$sign.'</secureCode></tran></tranlist></yimadai>';
  }

  public function hhhAction() {
    $user = User::where('userId', '10689')->first();
    $msg = '恭喜您，新手福利升级啦！活动期间新用户享受新手标最高年化1.5%加息、提现券免费送、投资还可获50元红包！详情请进入官网查看';
    $result = Sms::dxOne($msg, $user->phone);
    Log::write('测试短信：'.$result, ['msg'=>$msg, 'phone'=>$user->phone], 'common');
    $params = [];
    $params['type'] = 'withdraw';
    $params['useful_day'] = 180;
    $params['remark'] = '[活动]新手奖励';
    $params['userId'] = $user->userId;
    $status = Lottery::generate($params);

    die();

    /*$redis = RedisFactory::create();
    $userCardNum = $redis->get('user_card_num');
    if($userCardNum) {
        $userCardNum = unserialize($userCardNum);
    } else {
        $userCardNum = [];
    }
    $userId = '1000006816';
    if(isset($userCardNum[$userId])) {
        $userCardNum[$userId] += 4;
    } else {
        $userCardNum[$userId] = 4;
    }
    $redis->set('user_card_num', serialize($userCardNum));
    die();*/

    $redis = RedisFactory::create();
    echo '1: '.$redis->lLen('prize1').'<br>';
    echo '2: '.$redis->lLen('prize2').'<br>';
    echo '3: '.$redis->lLen('prize3').'<br>';
    echo 'all: <br>';
    var_dump(unserialize($redis->get('user_card_num')));
    die();
    $redis = RedisFactory::create();
    echo '1: '.$redis->lLen('prize1').'<br>';
    echo '2: '.$redis->lLen('prize2').'<br>';
    echo '3: '.$redis->lLen('prize3').'<br>';
    for ($i=0; $i < 60; $i++) {
      if($this->isWin()) {
        echo $i.'  win';
        $prizeId = $this->getPrize();
        echo $prizeId;
        echo '<br>';
      } else {
        echo $i.'  fail<br>';
      }
    }
    echo '1: '.$redis->lLen('prize1').'<br>';
    echo '2: '.$redis->lLen('prize2').'<br>';
    echo '3: '.$redis->lLen('prize3').'<br>';
  }

  /**
   * 是否中奖
   * @return boolean
   */
  public function isWin() {
      $num = rand(1, 100);
      if(100%$num==0) {
          return true;
      } 
      return false;
  }

  /**
   * 获取的奖品ID
   * @return mixed 奖品ID，无奖品返回false
   */
  public function getPrize() {
      $packetes = [
          1=>['id'=>1, 'name'=>'5元现金红包', 'money'=>5], 
          2=>['id'=>2, 'name'=>'10元现金红包', 'money'=>10], 
          3=>['id'=>3, 'name'=>'20元现金红包', 'money'=>20]
      ];
      $id = rand(1, 3);
      $prizeId = false;
      while (!$prizeId = $this->hasPrize($id)) {
          unset($packetes[$id]);
          $keys = array_keys($packetes);
          if(count($keys)>0) {
              $id = $keys[0];
          } else {
              break;
          }
      }
      return $prizeId;
  }

  /**
   * 是否还有奖品
   * @param  integer  $id  奖品ID
   * @return boolean
   */
  public function hasPrize($id) {
      $redis = RedisFactory::create();
      if($redis->lPop('prize'.$id)) {
          return $id;
      } else {
          return false;
      }
  }

  public function yamlAction() {
    $phone = 0;
    $redis = RedisFactory::create();
    if($redis->exists('crd_phone_max')) { 
        $phone = $redis->incr('crd_phone_max');
    } else {
        $phone = 17986800000;
        $redis->set('crd_phone_max', $phone);
    }
    var_dump($phone);die();
    // phpinfo();die();
    $result = spyc_load_file(APP_PATH.'/conf/access.yml');
    var_dump($result);
  }

  public function csfnAction() {
    $base = 'http://www.xiaowei.com/api/csfn';

    $url = '/add';
    $params = [];
    $params['cardnum'] = '350824199001105430';
    $params['realname'] = '嗖嗖嗖';
    $params['isRenew'] = '0';
    $params['operator'] = '123456';
    $params['money'] = 5000;
    $params['sn'] = 'XFJR201703090000003';
    $params['period'] = 50;
    $params['title'] = '测试消费金融5';
    $params['use'] = '斯蒂芬森的';
    $params['idImages']='/uploads/images/7e775a6d_max.jpg,/uploads/images/7e775a6d_min.jpg|/uploads/images/01e0bc_max.jpg,/uploads/images/1e0bc_min.jpg';
    $params['controlList'] = '类似,来到事发,鸡丝豆腐,冷飕飕';
    $params['controlContent'] = '顺利打开解放螺丝钉解放啦三等奖';
    $params['controlImages']='/uploads/images/7e775a6d_max.jpg,/uploads/images/7e775a6d_min.jpg|/uploads/images/01e0bc_max.jpg,/uploads/images/1e0bc_min.jpg';

    /*$url = '/thirdStatus';
    $params = [];
    $params['username'] = 'ljl360197197';*/

    $sign = WebSign::sign($params);
    $params[WebSign::SIGN_KEY] = $sign;
    $url = $base . $url;
    $result = NetworkHelper::post($url, $params);
    $this->pr($result);
  }

  public function smsAction() {
    $result = Sms::dxOne('您成功领取了红包！您的汇诚普惠账户登录密码为：556115', '18760419185', 1);
    var_dump($result);
  }

  public function reLotteryAction() {
    $userId = $this->getQuery('userId', '');
    $user = $this->getUser();
    if($userId=='') {
        die('错误!');
    }
    if(!$user || $user->userId!='10689') {
        die('无权限!');
    }
    $user = User::where('userId', $userId)->first();
    if(!$user) {
        die('无用户!');
    }
    $count = Lottery::where('userId', $userId)->where('type', 'invest_money')->count();
    if($count>0) {
         die('已发送!');
    }

    $redpacks = [
        ['money_rate'=>10, 'period'=>30, 'money_lower'=>10000],
        ['money_rate'=>10, 'period'=>30, 'money_lower'=>10000],
        ['money_rate'=>10, 'period'=>30, 'money_lower'=>10000],
        ['money_rate'=>10, 'period'=>30, 'money_lower'=>10000],
        ['money_rate'=>10, 'period'=>30, 'money_lower'=>10000],
        ['money_rate'=>20, 'period'=>30, 'money_lower'=>20000],
        ['money_rate'=>20, 'period'=>30, 'money_lower'=>20000],
        ['money_rate'=>20, 'period'=>60, 'money_lower'=>20000, 'period_lower'=>6, 'period_uper'=>24],
        ['money_rate'=>20, 'period'=>60, 'money_lower'=>20000, 'period_lower'=>6, 'period_uper'=>24],
        ['money_rate'=>50, 'period'=>60, 'money_lower'=>50000],
        ['money_rate'=>50, 'period'=>60, 'money_lower'=>50000],
        ['money_rate'=>50, 'period'=>60, 'money_lower'=>50000, 'period_lower'=>6, 'period_uper'=>24],
        ['money_rate'=>100, 'period'=>60, 'money_lower'=>100000, 'period_lower'=>6, 'period_uper'=>24],
    ];

    $list = [];
    foreach ($redpacks as $item) {
        $params = [];
        $params['type'] = 'invest_money';
        $params['useful_day'] = $item['period'];
        $params['remark'] = '[活动]红包奖励';
        $params['userId'] = $userId;
        $params['money_rate'] = $item['money_rate'];
        $params['money_lower'] = $item['money_lower'];
        if(isset($item['period_uper'])) {
            $params['period_uper'] = $item['period_uper'];
        }
        if(isset($item['period_lower'])) {
            $params['period_lower'] = $item['period_lower'];
        }
        $list[] = $params;
    }
    $status = Lottery::generateBatch($list);
  }

  public function moveUsersAction() {
    $list = User::whereRaw('1=1')->where('id', '>=', 12016)->get();
    foreach ($list as $user) {
        $cardType = '01';
        if(strlen($user->cardnum)==15) {
            $cardType = '02';
        }
        $sex = StringHelper::getSexByCardnum($user->cardnum);
        $sexNum = 1;
        if($sex=='women') {
            $sexNum = 2;
        }
        $str = $this->appendStr($user->cardnum, 18)         // IDNO 18
            . $cardType                                     // IDTYPE 2
            . $this->appendStr($user->name, 60)             // NAME 60
            . $this->appendStr($sexNum, 1)                  // GEN 1
            . $this->appendStr($user->phone, 12)            // MOPHONE 12
            . '0'                                           // ACCTYPE 1
            . $this->appendStr('', 40)                      // EMAIL 40
            . $this->appendStr($user->userId, 60)           // APPID 60
            . $this->appendStr('', 9)                       // BUSID 9
            . $this->appendStr('', 30)                      // TAXID 30
            . $this->appendStr('', 20)                      // ADNO 20
            . '2'                                           // ACC-TYPE 1
            . $this->appendStr('', 2)                       // FUCOMCODE 2
            . $this->appendStr('', 100)                     // INFO 100
            . $this->appendStr('', 42)                      // CACCOUNT 42
            . $this->appendStr('', 18)                      // BUSID 18
            . $this->appendStr('', 17);                     // REVERS 17
        file_put_contents(APP_PATH.'/public/uploads/custody-post/3005-APPZX0083-100002-20170718', $str.PHP_EOL, FILE_APPEND);
    }
  }

  public function moveBidsAction() {
    $batchNo = '000001';
    $list = OddMoney::with(['odd'=>function($q) {
        $q->select('oddNumber', 'oddYearRate', 'oddBorrowPeriod', 'oddRehearTime');
    }, 'user'=>function($q) {
        $q->select('custody_id', 'userId');
    }])->whereRaw('1=1')->where('id', '>=', 55421)->where('remain', '>', 0)->where('type', '<>', 'loan')->get();
    foreach ($list as $record) {
        $user = $record->user;
        $odd = $record->odd;
        $productId = _ntop($record->oddNumber);
        $orderId = '0001740000'.$record->tradeNo;

        $endDate = date('Ymd', strtotime($odd->oddRehearTime) + ($odd->oddBorrowPeriod * 30 * 24 * 3600));
        $str = $this->appendStr('3005', 4)                  // BANK 18
            . $this->appendStr($batchNo, 6)                 // BATCH 2
            . $this->appendStr($user->custody_id, 19)       // CARDNNBR 19
            . $this->appendStr('MJ', 4)                     // FUISSUER 4
            . $this->appendStr('', 6)               // PRODUCT 6
            . $this->appendStr($orderId, 40)                // SERI-NO 40
            . $this->appendStr($this->dnum($record->remain, 2), 13, 'left', '0')            // AMOUNT 13
            . $this->appendStr(_date('Ymd', $record->time), 8)           // BUYDATE 8
            . $this->appendStr(_date('Ymd', $odd->oddRehearTime), 8)     // INTDATE 8
            . $this->appendStr('2', 1)                      // INTTYPE 1
            . $this->appendStr('', 2)                       // INTPAYDAY 2
            . $this->appendStr($endDate, 8)                 // ENDDATE 8
            . $this->appendStr($this->dnum($odd->oddYearRate, 5), 8, 'left', '0')        // YIELD 8
            . $this->appendStr('156', 3)                    // CURR 3
            . $this->appendStr($productId, 40)                    //  40
            . $this->appendStr('', 60);                    //  60
        file_put_contents(APP_PATH.'/public/uploads/custody-post/3005-BID-'.$batchNo.'-20170720', $str.PHP_EOL, FILE_APPEND);
    }
  }

  public function moveOddsAction() {
    $batchNo = '000001';
    $list = Odd::with(['user'=>function($q) {
        $q->select('custody_id', 'userId');
    }])->whereRaw('1=1')->where('id', 94)->get();
    foreach ($list as $odd) {
        $user = $odd->user;
        $productId = _ntop($odd->oddNumber);
        $str = $this->appendStr('3005', 4)
            . $this->appendStr($batchNo, 6)
            . $this->appendStr($productId, 40)
            . $this->appendStr($odd->oddUse, 60)
            . $this->appendStr($user->custody_id, 19)
            . $this->appendStr($this->dnum($odd->oddMoney, 2), 13, 'left', '0')
            . $this->appendStr(2, 1)
            . $this->appendStr('', 2)
            . $this->appendStr($odd->oddBorrowPeriod * 30, 4)
            . $this->appendStr($this->dnum($odd->oddYearRate, 5), 8, 'left', '0')
            . $this->appendStr('', 19)
            . $this->appendStr('', 19)
            . $this->appendStr('0', 1)
            . $this->appendStr('', 19)
            . $this->appendStr('0', 1)
            . $this->appendStr('', 100)
            . $this->appendStr('', 100);
        file_put_contents(APP_PATH.'/public/uploads/custody-post/3005-BIDIN-000174-'.$batchNo.'-20170719', $str.PHP_EOL, FILE_APPEND);
    }
  }

  public function handleUserFileAction() {
    $filePath = APP_PATH.'/public/uploads/custody-res/3005-APPZX0083RES-100002-20170402';
    $items = [
        'CARDNBR'=>19, 
        'IDNO'=>18, 
        'IDTYPE'=>2, 
        'FLAG'=>1, 
        'ERRCODE'=>3, 
        'NAME'=>60, 
        'ACCTYPE'=>1, 
        'APPID'=>60, 
        'MOPHONE'=>12, 
        'INFO'=>100, 
        'REVERS'=>88
    ];
    $rows = $this->parseFile($filePath, $items);
    $endFlag = '<br>';
    foreach ($rows as $row) {
        if($row['FLAG']=='S' || $row['FLAG']=='N') {
            $count = User::where('userId', $row['APPID'])->update(['custody_id'=>$row['CARDNBR']]);
            if($count==1) {
                echo '[SUCCESS]用户'.$row['APPID'].'开户成功！'.$endFlag;
            } else {
                echo '[WARNING]用户'.$row['APPID'].'开户成功，更新失败！'.$endFlag;
            }
        } else {
            echo '[ERROR]用户'.$row['userId'].'开户失败，失败原因['.$row['ERRCODE'].']'.$endFlag;
        }
    }
  }

  public function handleOddFileAction() {
    $filePath = APP_PATH.'/public/uploads/custody-res/3005-BIDINRES-000174-000001-20170402';
    $items = [
        'BANK'=>4, 
        'BATCH'=>6, 
        'BIDNBR'=>40, 
        'BID_DESC'=>60, 
        'CARDNBR'=>19, 
        'NAME'=>60, 
        'AMOUNT'=>13, 
        'LOAN_TERM'=>4, 
        'INPDATE'=>8, 
        'CARDNBR_SU'=>19, 
        'CARDNBR_MY'=>19,
        'CARDNBR_PE'=>19,
        'RSPCODE'=>2,
        'RESERVED'=>100,
        'TRDRESV'=>100,
    ];
    $rows = $this->parseFile($filePath, $items);
    $endFlag = '<br>';
    foreach ($rows as $row) {
        if($row['RSPCODE']=='00') {
            echo '[SUCCESS]标的'._pton($row['BIDNBR']).'迁移成功！'.$endFlag;
        } else {
            echo '[ERROR]标的'._pton($row['BIDNBR']).'迁移失败，失败原因['.$row['RSPCODE'].']'.$endFlag;
        }
    }
  }

  public function handleBidFileAction() {
    $filePath = APP_PATH.'/public/uploads/custody-res/3005-BIDRESP-000001-20170402';
    $items = [
        'BANK'=>4, 
        'BATCH'=>6, 
        'CARDNNBR'=>19, 
        'FUISSUER'=>4, 
        'PRODUCT'=>6, 
        'SERI-NO'=>40, 
        'AMOUNT'=>13, 
        'NAME'=>60, 
        'DEALDATE'=>8, 
        'RSPCODE'=>2, 
        'AUTHCODE'=>20,
        'BIDNBR'=>40,
        'RESE'=>60,
    ];
    $rows = $this->parseFile($filePath, $items);
    $endFlag = '<br>';
    foreach ($rows as $row) {
        if($row['RSPCODE']=='00') {
            $tradeNo =  substr($row['SERI-NO'], 10);
            $count = OddMoney::where('tradeNo', $tradeNo)->update(['authCode'=>$row['AUTHCODE']]);
            if($count==1) {
                echo '[SUCCESS]债权订单号'.$tradeNo.'迁移成功！'.$endFlag;
            } else {
                echo '[WARNING]债权订单号'.$tradeNo.'迁移成功，更新失败！'.$endFlag;
            }
        } else {
            echo '[ERROR]债权订单号'.$tradeNo.'迁移失败，失败原因['.$row['RSPCODE'].']'.$endFlag;
        }
    }
  }

  public function parseFile($filePath, $items) {
    $file = fopen($filePath, 'r');
    $rows = [];
    while(!feof($file)) {
        $row = [];
        $content = fgets($file);
        foreach ($items as $key => $val) {
            $res = $this->popStr($content, $val);
            $row[$key] = $res[0];
            $content = $res[1];
        }
        $rows[] = $row;
    }
    fclose($file);
    return $rows;
  }

  public function popStr($str, $length) {
    $sub = substr($str, 0, $length);
    $sub = iconv('gbk', 'utf-8', trim($sub));
    $less = substr($str, $length);
    return [$sub, $less];
  }

  public function appendStr($str, $length, $type='right', $s=' ') {
    $newStr = iconv('utf-8', 'gbk', $str);
    $len = strlen($newStr);
    $repeat = str_repeat($s, $length-$len);
    if($type=='left') {
        return $repeat . $newStr;
    }
    return $newStr . $repeat;
  }

  public function dnum($num, $f=2) {
    $result = intval($num * pow(10, $f));
    if($result==0) {
        return '0' . str_repeat('0', $f);
    } else {
        return $result;
    }
  }

  public function fileAction() {
    $result = API::changeOddStatus('20171118000001', 'REPAYING');
    var_dump($result);die();
    die();
    $result = json_decode('{"requestNo":"20171119102029000029","respCode":"00000","respMsg":"成功","status":"SUCCESS"}', true);
    $requestNo = $result['requestNo'];
    $odd = Odd::where('oddNumber', '20171118000001')->first();
    $count = 5000;
    $amount = 3;
    if(API::success($result)) {
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

}
