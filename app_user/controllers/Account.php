<?php
use Yaf\Registry;
use forms\AddBankForm;
use forms\UpdateBankForm;
use forms\WithdrawFormOld as WithdrawForm;
use forms\RechargeFormOld as RechargeForm;
use forms\OpenCustodyForm;
use forms\OpenBaofooForm;
use tools\Log;
use helpers\NetworkHelper;
use plugins\lianlian\lib\LLapi;
use models\Invest;
use models\UserVip;
use models\UserBank;
use models\UserUnbindBank;
use models\Redpack;
use models\UserEstimate;
use models\User;
use models\AutoInvest;
use models\MoneyLog;
use models\Recharge;
use models\RechargeAgree;
use models\Attribute;
use models\Withdraw;
use models\OldLog;
use models\OldData;
use models\OddMoney;
use models\Interest;
use models\UserBespoke;
use models\Lottery;
use models\News;
use models\Integration;
use traits\PaginatorInit;
use exceptions\HttpException;
use custody\Handler;
use custody\API;
use custody\Code;
use tools\Redis;
use tools\BankCard;
use helpers\StringHelper;
use helpers\ExcelHelper;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * AccountController
 * 用户中心
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class AccountController extends Controller {
    use PaginatorInit;

	public $menu = 'account';
    public $submenu = 'account';

    /**
     * 账户总览
     * @return  mixed
     */
    public function indexAction() {
        $this->submenu = 'account';
        $user = $this->getUser();
        $userId = $user->userId;

        $tenderInfo = Invest::getUserTenderInfo($userId);

        $backPrincipal = Interest::getBackPrincipalByUser($userId);
        $backInterest = Interest::getBackInterestByUser($userId);
        $backAllMoney = $backPrincipal + $backInterest;

        $getInterest = Invest::getTotalInterestByUser($userId);
        
        $userVip = UserVip::getVipByUser($userId);
        
        $tenderGrade = $user->getTenderGrade();
        
        $redpackStatus = Redpack::canUserGet($user);
        
        $tenderMoney = OddMoney::getTenderMoneyByUser($userId);

        $other = Invest::where('status', '<>', Invest::STATUS_OUT)->where('userId', $userId)->first([
            DB::raw('sum(extra) as extraMoney'), 
            DB::raw('sum(subsidy) as subsidyMoney'),
        ]);

        $row = Invest::where('userId', $userId)->where('status', 0)->whereHas('oddMoney', function($q) {
            $q->where('type', 'credit');
        })->first([DB::raw('sum(benJin) totalCapital'), DB::raw('sum(interest)+sum(reward) totalInterest')]);
        $creditStayCapital = floatval($row->totalCapital);
        $creditStayInterest = floatval($row->totalInterest);


        $row = Invest::where('userId', $userId)->where('status', 0)->whereHas('oddMoney', function($q) {
            $q->where('type', 'invest');
        })->first([DB::raw('sum(benJin) totalCapital'), DB::raw('sum(interest)+sum(reward) totalInterest')]);
        $investStayCapital = floatval($row->totalCapital);
        $investStayInterest = floatval($row->totalInterest);

        $oldMoney = 0;
        if($user->oldAccountId){
            $jsonData = file_get_contents('http://loan'.WEB_DOMAIN.'/reloadMoney.php?type=get&strAccountID='.$user->oldAccountId);
            $data = json_decode($jsonData,true);
            if($data['ret'] == '0000'){
                $oldMoney = $data['data']['content']['WithdrawableMoney'] + $data['data']['content']['WithdrawalDisableMoney'];
            }
        }


        $lotteryCount = Lottery::where('userId', $user->userId)
            ->where('status', Lottery::STATUS_NOUSE)
            ->where('endtime', '>', date('Y-m-d H:i:s'))
            ->count();
        $this->display('index', [
            'user'=>$user, 
            'oldMoney'=>$oldMoney, 
            'tenderInfo'=>$tenderInfo, 
            'getInterest'=>$getInterest,
            'backPrincipal'=>$backPrincipal, 
            'backInterest'=>$backInterest,
            'backAllMoney'=>$backAllMoney,
            'tenderMoney'=>$tenderMoney,
            'userVip'=>$userVip,
            'tenderGrade'=>$tenderGrade,
            'redpackStatus'=>$redpackStatus,
            'subsidy'=>$other->subsidyMoney,
            'extra'=>$other->extraMoney,
            'lotteryCount'=>$lotteryCount,
            'investStayCapital'=>$investStayCapital,
            'investStayInterest'=>$investStayInterest,
            'creditStayCapital'=>$creditStayCapital,
            'creditStayInterest'=>$creditStayInterest,
        ]);
    }
    
    /**
     * 资金迁移
     * @return [type] [description]
     */
    public function moveOldMoneyAction() {
        $user = $this->getUser();
        if($user->oldAccountId){
            $jsonData = NetworkHelper::curlRequest('http://loan'.WEB_DOMAIN.'/reloadMoney.php?type=reload&strAccountID='.$user->oldAccountId);
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
                        Flash::success('迁移成功！');
                        exit;
                    }else{
                        Log::write('用户资金迁移 数据写入异常'.$user->userId, [$data], 'error');
                        Flash::error('操作失败, 请联系客服！');
                        exit;
                    }
                }else{
                    Flash::error('旧系统余额为零！');
                    exit;
                }
            }
        }
        Flash::error('操作失败！');
    }

    /**
     * 关闭提示
     * @return [type] [description]
     */
    public function closeMessageAction(){
        $user = $this->getUser();
        $queries = $this->queries->defaults(['type'=>'']);
        if($queries->type == 'handMessage'){
            $user->autoHandMessage = 1;
            $user->save();
        }
        if($queries->type == 'issign'){
            $user->issign = 1;
            $user->save();
        }
        if($queries->type == 'isseal'){
            $user->isseal = 1;
            $user->save();
        }
    }

    /**
     * 资金日志
     */
    public function moneyExcelAction() {
        $user = $this->getUser();
        $queries = $this->queries->defaults(['type'=>'all', 'timeBegin'=>'', 'timeEnd'=>'']);
        $timeBegin = $queries->timeBegin;
        $timeEnd = $queries->timeEnd;
        $type = $queries->type;
        $builder = MoneyLog::where('userId', $user->userId)->where('mode', '<>', 'sync');
        if($type!='all') {
            $builder->where('type', $type);
        }
        if($timeBegin!='') {
            $builder->where('time', '>=', $timeBegin.' 00:00:00');
        }
        if($timeEnd!='') {
            $builder->where('time', '<=', $timeEnd.' 23:59:59');
        }
        $logs = $builder->orderBy('time', 'desc')->orderBy('id', 'desc');
        $records = $builder->get();
        $other = [
            'title' => '资金日志',
            'columns' => [
                'time' => ['name'=>'日期'],
                'type' => ['name'=>'交易类型'],
                'mode' => ['name'=>'出入'],
                'mvalue' => ['name'=>'金额'],
                'remain' => ['name'=>'可用余额'],
                'remark' => ['name'=>'备注'],
                //'frozen' => ['name'=>'冻结余额'],
            ],
        ];
        $excelRecords = [];
        foreach ($records as $row) {
            $item = [];
            $item['userId'] = $row->userId;
            $item['username'] = $row->user->username;
            $item['type'] = $row->getTypeName();
            $item['mvalue'] = $row->mvalue;
            $item['mode'] = $row->getModeName();
            $item['remark'] = $row->remark;
            $item['time'] = $row->time;
            $item['remain'] = $row->remain;
            $item['frozen'] = $row->frozen;
            $excelRecords[] = $item;
        }
        ExcelHelper::getDataExcel($excelRecords, $other);
    }

    /**
     * 资金账户--资金记录
     * @return  mixed
     */
    public function logsAction() {
        $this->mode = 'logs';
        $user = $this->getUser();
        $userId = $user->userId;
        $queries = $this->queries->defaults(['type'=>'all', 'timeBegin'=>'', 'timeEnd'=>'']);
        if($queries->download){
            $this->moneyExcelAction();
            return;
        }
        $timeBegin = $queries->timeBegin;
        $timeEnd = $queries->timeEnd;
        $type = $queries->type;
        $builder = MoneyLog::where('userId', $userId)->where('mode', '<>', 'sync');
        if($type!='all') {
            $builder->where('type', $type);
        }
        if($timeBegin!='') {
            $builder->where('time', '>=', $timeBegin.' 00:00:00');
        }
        if($timeEnd!='') {
            $builder->where('time', '<=', $timeEnd.' 23:59:59');
        }
        $logs = $builder->orderBy('time', 'desc')->orderBy('id', 'desc')->paginate(15);
        $logs->appends($queries->all());
        $this->display('logs', ['logs'=>$logs, 'queries'=>$queries, 'types'=>MoneyLog::$types]);
    }

    /**
     * 资金账户--账户提现
     * @return  mixed
     */
    public function withdrawAction() {
        $this->mode = 'withdraw';
        $user = $this->getUser();
        if($user->custody_id=='') {
            Flash::error('您还未进行实名认证！');
            $this->redirect('/account/custody');
        }
        if($user->is_custody_pwd==0) {
            Flash::error('您还未设置存管密码！');
            $this->redirect('/user/safe');
        }
        $bank = UserBank::where('userId', $user->userId)->where('status', '1')->first();
        if(!$bank) {
            Flash::error('您还未绑定银行卡，请先绑定您的常用银行卡！');
            $this->redirect('/account/custody');
        }
        $lotteryCount = Lottery::where('userId', $user->userId)
            ->where('status', Lottery::STATUS_NOUSE)
            ->where('type', 'withdraw')
            ->where('endtime', '>', date('Y-m-d H:i:s'))
            ->count();
        $this->display('withdraw', ['bank'=>$bank, 'user'=>$user, 'lotteryCount'=>$lotteryCount]);
    }

    /**
     * 资金账户--ajax执行充值
     * @return  mixed
     */
    public function doWithdrawAction() {
        $params = $this->getAllPost();
        $form = new WithdrawForm($params);

        $baofoo = Registry::get('config')->get('baofoo')->get('open');
        if($baofoo){
            if($form->withdraw()) {
                Flash::success('申请成功！');
                $this->redirect('/account');
            } else {
                Flash::error($form->posError());
                $this->redirect('/account/withdraw');
            }
            exit;
        }

        if($form->withdraw()) {
            echo $form->html;
        } else {
            Flash::error($form->posError());
            $this->redirect('/account/withdraw');
        }
    }

    /**
     * 资金账户--账户提现--ajax获取提现手续费
     * @return  mixed
     */
    public function getWithdrawFeeAction() {
        $money = $this->getPost('money', 0);
        $lottery = $this->getPost('lottery', 0);
        if($lottery==1) {
            $lottery = true;
        } else {
            $lottery = false;
        }
        $user = $this->getUser();
        $rdata = [];
        if(is_numeric($money)) {
            $fee = $user->getWithdrawFee($money, $lottery);
            $rdata['fee'] = $fee;
            $rdata['status'] = 1;
        } else {
            $rdata['status'] = 0;
        }
        $this->backJson($rdata);
    }

    /**
     * 资金账户--充值记录
     * @return  mixed
     */
    public function rechargeRecordAction() {
        $this->mode = 'rechargeRecord';
        $user = $this->getUser();
        $userId = $user->userId;
        $queries = $this->queries->defaults(['type'=>'all', 'timeBegin'=>'', 'timeEnd'=>'', 'way'=>'all']);
        $timeBegin = $queries->timeBegin;
        $timeEnd = $queries->timeEnd;
        $type = $queries->type;
        $way = $queries->way;

        $builder = Recharge::where('userId', $userId)->where('status', 1);
        
        if($type!='all') {
            if($type=='yemadai') {
                $builder->whereRaw('(payType=? or payType=?)', ['yemadai', '']);
            } else {
                $builder->where('payType', $type);
            }
        }
        if($way!='all') {
            $builder->where('payWay', $way);
        }
        if($timeBegin!='') {
            $builder->where('time', '>=', $timeBegin.' 00:00:00');
        }
        if($timeEnd!='') {
            $builder->where('time', '<=', $timeEnd.' 23:59:59');
        }
        
        $totalResult = $builder->first([DB::raw('sum(money) totalMoney'), DB::raw('count(*) totalCount')]);

        $rechargeList = $builder->orderBy('time', 'desc')->paginate(20);
        $rechargeList->appends($queries->all());
        $this->display('rechargeRecord', ['rechargeList'=>$rechargeList, 'queries'=>$queries, 'totalResult'=>$totalResult, 'payTypes'=>Recharge::$payTypes, 'payWays'=>Recharge::$payWays]);
    }

    /**
     * 资金账户--提现记录
     * @return  mixed
     */
    public function withdrawRecordAction() {
        $this->mode = 'withdrawRecord';
        $user = $this->getUser();
        $queries = $this->queries->defaults(['status'=>'all', 'timeBegin'=>'', 'timeEnd'=>'']);
        $timeBegin = $queries->timeBegin;
        $timeEnd = $queries->timeEnd;
        $status = $queries->status;

        $builder = Withdraw::where('userId', $user->userId);
        if($status=='success') {
            $builder->where('status', 1);
        } else if($status=='fail') {
            $builder->whereIn('status', [0, 2]);
        } else if($status=='handle') {
            $builder->whereIn('status', [3]);
        } else {
            $builder->whereIn('status', [0, 1, 2, 3]);
        }
        if($timeBegin!='') {
            $builder->where('addTime', '>=', $timeBegin.' 00:00:00');
        }
        if($timeEnd!='') {
            $builder->where('addTime', '<=', $timeEnd.' 23:59:59');
        }
        
        $totalResult = $builder->first([
            DB::raw('sum(fee) totalFee'), 
            DB::raw('sum(outMoney) totalMoney'), 
            DB::raw('count(*) totalCount'), 
            DB::raw('sum(outMoney-fee) totalRelMoney')
        ]);

        $withdrawList = $builder->orderBy('addTime', 'desc')->paginate(20);
        $withdrawList->appends($queries->all());
    	$this->display('withdrawRecord', ['withdrawList'=>$withdrawList, 'queries'=>$queries, 'totalResult'=>$totalResult]);
    }

    /**
     * 资金账户--银行存管--绑定银行卡
     * @return  mixed
     */
    public function bindBankCardAction() {
        $user = $this->getUser();

        $baofoo = Registry::get('config')->get('baofoo')->get('open');
        if($baofoo){
            $type = $this->getQuery('type', 'normal');
            $user = $this->getUser();
            $bank = null;
            $this->display('bindBankCard', ['bank'=>$bank, 'user'=>$user, 'type'=>$type]);
            exit;
        }

        echo API::bindBankCard($user->userId, $user->phone);
    }

    public function baofooUnbindBankCard(){
        $bankNum = $this->getPost('bankNum', '');
        $user = $this->getUser();
        if($bankNum=='') {
            Flash::error('请求异常！');
            $this->redirect('/account/bank');
        }
        if($user->fundMoney!=0) {
            Flash::error('账户余额不为零，不可解绑！');
            $this->redirect('/account/bank');
        }

        $bank = UserBank::where('status', 1)->where('userId', $user->userId)->first();
        if(!$bank || $bank->bankNum!=$bankNum) {
            Flash::error('银行卡信息错误！');
            $this->redirect('/account/bank');
        }

        $form = new RechargeForm();
        if($form->unbindCard()) {
            if($form->result['resp_code'] != '0000'){
                Flash::error($form->result['resp_msg']);
                $this->redirect('/account/bank');
            }else{
                UserBank::where('userId', $user->userId)->update(['status'=>0]);
                
                Flash::success('解绑成功！');
                $this->redirect('/account/bank');
            }
        } else {
            Flash::error($form->posError());
            $this->redirect('/account/bank');
        }

    }

    /**
     * 资金账户--银行存管--解绑银行卡
     * @return  mixed
     */
    public function unbindBankCardAction() {
        $baofoo = Registry::get('config')->get('baofoo')->get('open');
        if($baofoo){
            $this->baofooUnbindBankCard();
            exit;
        }

        $bankNum = $this->getPost('bankNum', '');
        $user = $this->getUser();
        if($bankNum=='') {
            Flash::error('请求异常！');
            $this->redirect('/account/bank');
        }
        if($user->fundMoney!=0) {
            Flash::error('账户余额不为零，不可解绑！');
            $this->redirect('/account/bank');
        }

        $bank = UserBank::where('status', 1)->where('userId', $user->userId)->first();
        if(!$bank || $bank->bankNum!=$bankNum) {
            Flash::error('银行卡信息错误！');
            $this->redirect('/account/bank');
        }

        $result = API::unbindBankCard($user->userId);
        $rdata = [];
        if($result['status']) {
            Flash::success('解绑成功！');
            $this->redirect('/account/bank');
        } else {
            Flash::error($result['msg']);
            $this->redirect('/account/bank');
        }
    }

    /**
     * 资金账户--银行存管--同步银行卡信息
     * @return  mixed
     */
    public function refreshBankCardAction() {
        $user = $this->getUser();
        $result = API::refreshUserBank($user);

        $rdata = [];
        if($result['status']==1) {
            Flash::success($result['msg']);
        }

        $rdata['status'] = $result['status'];
        $rdata['info'] = $result['msg'];

        $this->backJson($rdata);
    }

    /**
     * 账户总览--领取红包
     * @return  mixed
     */
    public function getRedpackAction() {
        $rdata = [];
        $rdata['status'] = 0;
        $rdata['info'] = '暂时不能领取！';
        $this->backJson($rdata);
        $user = $this->getUser();
        $userId = $this->getUser()->userId;
        $getStatus = Redpack::canUserGet($user);
        if($getStatus==1) {
            $remark = '新手50元红包';
            $packStatus = API::redpack($user->userId, 50, 'rpk-newuser', $remark);

            $rdata = [];
            if($packStatus) {
                Flash::success('领取红包成功！');
                $rdata['status'] = 1;
            } else {
                $rdata['status'] = 0;
                $rdata['info'] = '领取失败！';
            }
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '您未达到领取条件或已经领取！';
            $this->backJson($rdata);
        }
    }

    /**
     * 资金账户--资金记录--旧系统资金记录
     * @return  mixed
     */
    public function oldlogsAction() {
        $this->mode = 'logs';
        $queries = $this->queries->defaults(['type'=>'all', 'timeBegin'=>'', 'timeEnd'=>'']);
        $type = $queries->type;
        $timeBegin = $queries->timeBegin;
        $timeEnd = $queries->timeEnd;
        $user = $this->getUser();

        $builder = OldLog::where('user_id', $user->userId);

        if($type!='all') {
            $builder->where('type', $type);
        }
        if($timeBegin!='') {
            $builder->where('addtime', $timeBegin.' 00:00:00');
        }
        if($timeEnd!='') {
            $builder->where('addtime', $timeEnd.' 23:59:59');
        }

        $logs = $builder->paginate(15);
        $logs->appends($queries->all());

        $this->display('oldlogs', [
            'logs'=>$logs,
            'queries'=>$queries,
            'types'=>OldLog::$types,
        ]);
    }

    /**
     * 资金账户--回款日历
     * @return  mixed
     */
    public function ajaxRepaymentsAction() {
        $year = intval($this->getPost('year'));
        $month = intval($this->getPost('month'));
        if($month<10) {
            $month = '0'.$month;
        }
        $firstDay = $year . '-' . $month .'-01';
        $lastDay = date('Y-m-d',strtotime("$firstDay +1 month -1 day"));
        $firstDay .= ' 00:00:00';
        $lastDay .= ' 23:59:59';
        
        $user = $this->getUser();
        $userId = $user->userId;

        $repayments = Invest::getRepaymentsBuilder($userId, $firstDay, $lastDay)->get();
        $dayCount = intval(date('d', strtotime($lastDay))) - intval(date('d', strtotime($firstDay))) + 1;
        $repaymentList = [];
        foreach ($repayments as $repayment) {
            $key = '';
            if($repayment->status==0) {
                $key = intval(date('d', strtotime($repayment['endtime'])));
            } else {
                $key = intval(date('d', strtotime($repayment['operatetime'])));
            }

            if(isset($repaymentList[$key])) {
                $repaymentList[$key]['benJin'] += $repayment->benJin;
                $repaymentList[$key]['interest'] += $repayment->interest;
                $repaymentList[$key]['oughtMoney'] += $repayment->zongEr;
                $repaymentList[$key]['realMoney'] += $repayment->realAmount;
                $repaymentList[$key]['serviceMoney'] += $repayment->serviceMoney;
                $repaymentList[$key]['count'] ++;

                if(isset($repaymentList[$key][$repayment->getStatus()])){
                    $repaymentList[$key][$repayment->getStatus()] ++;
                }else{
                    $repaymentList[$key][$repayment->getStatus()] = 1;
                }
                
                if($repayment->status==Invest::STATUS_REPAYING) {
                    $repaymentList[$key]['status'] = 'ing';
                } else {
                    if($repaymentList[$key]['status']=='over' && $repayment->status==Invest::STATUS_STAY) {
                        $repaymentList[$key]['status'] = 'ing';
                    } else if($repaymentList[$key]['status']=='stay' && $repayment->status!=Invest::STATUS_STAY) {
                        $repaymentList[$key]['status'] = 'ing';
                    }
                }
            } else {
                $repaymentList[$key]['benJin'] = $repayment->benJin;
                $repaymentList[$key]['interest'] = $repayment->interest;
                $repaymentList[$key]['oughtMoney'] = $repayment->zongEr;
                $repaymentList[$key]['realMoney'] = $repayment->realAmount;
                $repaymentList[$key]['serviceMoney'] = $repayment->serviceMoney;
                $repaymentList[$key]['count'] = 1;
                
                $repaymentList[$key]['status'] = $repayment->getStatus();
                $repaymentList[$key][$repayment->getStatus()] = 1;
            }
        }
        $monthRepayments = [];
        for ($i=0; $i < $dayCount; $i++) {
            if(isset($repaymentList[$i+1])) {
                $monthRepayments[] = $repaymentList[$i+1];  
            } else {
                $monthRepayments[] = ['benJin'=>0, 'interest'=>0, 'oughtMoney'=>0, 'realMoney'=>0, 'serviceMoney'=>0, 'status'=>'none'];
            }
        }

        $rdata['status'] = 1;
        $rdata['repayments'] = $monthRepayments;
        $this->backJson($rdata);
    }

    public function getRepaymentAction() {
        $date = intval($this->getPost('date'));
        $user = $this->getUser();

        $beginTime = _date('Y-m-d 00:00:00', $date);

        $item = Invest::where(['userId'=>$user->userId,'status'=>0])->where('endtime','>',$beginTime)->orderBy('endtime','asc')->first();
        if(!$item){
            $nextTime = '';
        }else{
            $nextTime = $item->endtime;
        }

        $endTime = _date('Y-m-d 23:59:59', $date);
        $repayments = Invest::with(['odd'=>function($q){
                $q->select('oddNumber', 'oddTitle', 'oddBorrowPeriod');
            }])->where('userId', $user->userId)
            ->whereRaw('((status=0 and endtime>=? and endtime<=?) or (status<>0 and operatetime>=? and operatetime<=?))', [$beginTime, $endTime, $beginTime, $endTime])->get();

        $count = 0;
        $amount = 0;
        $list = [];
        foreach ($repayments as $key => $repayment) {
            $count ++;
            $amount += $repayment->zongEr;
            $list[] = [
                'url' => WEB_MAIN.'/odd/'.$repayment->oddNumber,
                'title'=>$repayment->odd->oddTitle, 
                'amount'=>$repayment->zongEr, 
                'planDate'=>_date('Y-m-d', $repayment->endtime), 
                'realDate'=>$repayment->operatetime?_date('Y-m-d', $repayment->operatetime):'',
                'status'=>$repayment->getStatus(),
                'qishu'=>$repayment->qishu . '/' . $repayment->odd->oddBorrowPeriod 
            ];
        }
        $rdata['data']['nextTime'] = date('Ymd',strtotime($nextTime));
        $rdata['status'] = 1;
        $rdata['data']['list'] = $list;
        $rdata['data']['count'] = $count;
        $rdata['data']['amount'] = $amount;
        $this->backJson($rdata);
    }

    /**
     * 资金账户--回款记录(只可按天查询)
     * @return  mixed
     */
    public function repaymentRecordAction() {
        $this->submenu = 'invest';
        $this->mode = 'repaymentRecord';
        $queries = $this->queries->defaults(['oddNumber'=>'', 'timeBegin'=>'', 'timeEnd'=>'', 'day'=>'', 'oddMoneyId'=>'']);
        $oddNumber = $queries->oddNumber;
        $day = $queries->day;
        $oddMoneyId = $queries->oddMoneyId;
        $timeBegin = $queries->timeBegin;
        $timeEnd = $queries->timeEnd;
        if($timeBegin!='') {
            $timeBegin = $timeBegin . ' 00:00:00';
        }
        if($timeEnd!='') {
            $timeEnd = $timeEnd . ' 23:59:59';
        }
        if($day!='') {
            $timeBegin = $day . ' 00:00:00';
            $timeEnd = $day . ' 23:59:59';
            $queries->timeBegin = $queries->timeEnd = $day;
        }
        $user = $this->getUser();
        $userId = $user->userId;
        $repayments = Invest::getRepaymentsBuilder($userId, $timeBegin, $timeEnd, 'all', $oddNumber, $oddMoneyId)->orderBy('endtime', 'desc')->paginate(15);
        $repayments->appends($queries->all());
        $this->display('repaymentRecord', ['repayments'=>$repayments, 'queries'=>$queries]);
    }

    /**
     * 提前还款记录
     * @return mixed
     */
    public function prepaysAction() {
        $this->submenu = 'invest';
        $this->mode = 'prepays';
        $id = $this->getQuery('id', 0);
        $user = $this->getUser();
        $records = Invest::getPrepayBuilder($user->userId)->paginate();
        $this->display('prepays', ['records'=>$records]);
    }

    /**
     * 逾期记录
     * @return mixed
     */
    public function overduesAction() {
        $this->submenu = 'invest';
        $this->mode = 'overdues';
        $id = $this->getQuery('id', 0);
        $user = $this->getUser();
        $records = Invest::getOverdueBuilder($user->userId)->paginate();
        $this->display('overdues', ['records'=>$records]);
    }

    /**
     * 预约标
     * @return mixed
     */
    public function bespokeAction() {
        $moneyList = UserBespoke::$moneyList;
        $monthList = UserBespoke::$monthList;
        if($this->isPost()) {
            $user = $this->getUser();
            $money = $this->getPost('money', 0);
            $month = $this->getPost('month');
            $time = $this->getPost('time', '');
            if($money==0) {
                Flash::error('请选择预约金额！');
                $this->redirect('/account/bespoke');
            }
            if(!$month) {
                Flash::error('请选择投资期限！');
                $this->redirect('/account/bespoke');
            }
            if($time=='') {
                Flash::error('请输入预约时间！');
                $this->redirect('/account/bespoke');
            }
            $todayBegin = date('Y-m-d 00:00:00');
            $todayEnd = date('Y-m-d 23:59:59');
            $count = UserBespoke::where('userId', $user->userId)
                ->where('created_at', '>', $todayBegin)
                ->where('created_at', '<=', $todayEnd)
                ->count();
            if($count>0) {
                Flash::error('每天只能申请一次！');
                $this->redirect('/account/bespoke');
            }
            $count = UserBespoke::where('userId', $user->userId)->where('time', $time)->first();
            if($count>0) {
                Flash::error('您已经申请过了！');
                $this->redirect('/account/bespoke');
            }
            $monthStrList = [];
            foreach ($month as $m) {
                $monthStrList[] = $monthList[$m];
            }
            $bespoke = new UserBespoke();
            $bespoke->userId = $user->userId;
            $bespoke->money = $moneyList[$money];
            $bespoke->month = implode(',', $monthStrList);
            $bespoke->time = $time;
            if($bespoke->save()) {
                Flash::success('申请约标成功, 我们的客服会尽快联系您！');
                $this->redirect('/account');
            } else {
                Flash::error('申请约标失败！');
                $this->redirect('/account');
            }
        } else {
            $this->display('bespoke', ['moneyList'=>$moneyList, 'monthList'=>$monthList]);
        }
    }

    /**
     * 资金账户--我的优惠券
     * @return  mixed
     */
    public function lotteriesAction() {
        $this->submenu = 'special';
        $this->mode = 'lottery';
        $queries = $this->queries->defaults(['status'=>'nouse', 'type'=>'all']);
        $status = $queries->status;
        // $type = $queries->type;

        $user = $this->getUser();
        $userId = $user->userId;
        $builder = Lottery::where('userId', $userId);
        /*if($type!='all') {
            $builder->where('type', $type);
        }*/
        //$builder->where('type', 'invest_money');
        if($status!='all') {
            if($status=='over') {
                $builder->where('status', Lottery::STATUS_NOUSE)->where('endtime', '<', date('Y-m-d H:i:s'));
            } else if($status=='used') {
                $builder->where('status', Lottery::STATUS_USED)->orwhere('status', Lottery::STATUS_FROZEN);
            } else if($status=='nouse') {
                $builder->where('status', Lottery::STATUS_NOUSE)->where('endtime', '>', date('Y-m-d H:i:s'));
            } else if($status=='nouse') {
                $builder->where('status', Lottery::STATUS_FROZEN);
            }
        }
        $useMoney = Lottery::where('userId', $userId)->where('status', Lottery::STATUS_USED)->sum('money_rate');
        $canMoney = Lottery::where('userId', $userId)->where('status', Lottery::STATUS_NOUSE)->where('endtime', '>', date('Y-m-d H:i:s'))->sum('money_rate');

        $lotteries = $builder->orderBy('get_at', 'desc')->paginate(15);
        $lotteries->appends($queries->all());
        $this->display('lotteries', [
            'lotteries'=>$lotteries, 
            'queries'=>$queries, 
            'canMoney'=>floatval($canMoney), 
            'types'=>Lottery::$types, 
            'useMoney'=>floatval($useMoney), 
        ]);
    }

    /**
     * 资金账户--获取优惠券
     * @return  mixed
     */
    public function getLotteryAction() {
        $sn = $this->getPost('sn');
        $lottery = Lottery::where('sn', $sn)->where('status', Lottery::STATUS_NOGET)->first();
        $rdata = [];
        if(!$lottery) {
            $rdata['status'] = 0;
            $rdata['info'] = '优惠券不存在！';
            $this->backJson($rdata);
        }
        $user = $this->getUser();
        if($lottery->assign($user)) {
            $rdata['status'] = 1;
            Flash::success('恭喜您，获取成功！');
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '获取失败！';
            $this->backJson($rdata);
        }
    }

    /**
     * 资金账户--获取加息券
     * @return  mixed
     */
    public function investLotteriesAction() {
        $oddMoneyId = $this->getPost('oddMoneyId');

        $oddMoney = OddMoney::find($oddMoneyId);
        $user = $this->getUser();
        $userId = $user->userId;

        $lotteries = Lottery::where('userId', $userId)
            ->where('type', 'interest')
            ->where('status', Lottery::STATUS_NOUSE)
            ->where('endtime', '>', date('Y-m-d H:i:s'))
            ->orderBy('endtime', 'asc')
            ->get();

        $list = [];
        foreach ($lotteries as $lottery) {
            $result = $lottery->investCanUse($oddMoney);
            if($result['status']) {
                $row = [];
                $row['id'] = $lottery->id;
                $row['name'] = ($lottery->money_rate*100).'%加息券';
                $row['type'] = $lottery->getPeriodType();
                $row['money'] = $lottery->getMoneyType();
                $row['endtime'] = $lottery->endtime;
                $list[] = $row;
            }
        }

        $rdata = [];
        $rdata['status'] = 1;
        $rdata['lotteries'] = $list;
        $this->backJson($rdata);
    }

    /**
     * 资金账户--使用加息券
     * @return  mixed
     */
    public function useInvestLotteryAction() {
        $oddMoneyId = $this->getPost('oddMoneyId', 0);
        $lotteryId = $this->getPost('lotteryId', 0);

        $oddMoney = OddMoney::find($oddMoneyId);
        $lottery = Lottery::find($lotteryId);
        $user = $this->getUser();
        $userId = $user->userId;

        $rdata = [];

        $result = $lottery->investCanUse($oddMoney);
        if($result['status']) {
            $status = API::lottery(['lotteryId'=>$lotteryId, 'oddMoneyId'=>$oddMoneyId]);
            if($status) {
                Flash::success('使用成功！');
                $rdata['status'] = 1;
            } else {
                $rdata['status'] = 0;
                $rdata['info'] = '使用失败！';
            }
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = $result['msg'];
        }

        $this->backJson($rdata);
    }
        
    /**
     * 资金账户--积分记录
     * @return  mixed
     */
    public function integrationAction() {
        $this->mode = 'integration';
        $user = $this->getUser();
        $userId = $user->userId;
        $queries = $this->queries->defaults(['type'=>'all', 'timeBegin'=>'', 'timeEnd'=>'']);
        $timeBegin = $queries->timeBegin;
        $timeEnd = $queries->timeEnd;
        $type = $queries->type;
        $builder = Integration::where('userId', $userId);
        if($type!='all') {
            $builder->where('type', $type);
        }
        if($timeBegin!='') {
            $builder->where('created_at', '>=', $timeBegin.' 00:00:00');
        }
        if($timeEnd!='') {
            $builder->where('created_at', '<=', $timeEnd.' 23:59:59');
        }
        $logs = $builder->orderBy('created_at', 'desc')->paginate(15);
        $logs->appends($queries->all());
        $this->display('integration', ['logs'=>$logs, 'queries'=>$queries, 'types'=>Integration::$types]);
    }

    public function moneyUnfreezeAction() {
        $id = $this->getPost('id', 0);
        $user = $this->getUser();
        $lottery = Lottery::where('id', $id)->first();
        $rdata = [];
        if(!$lottery) {
            $rdata['status'] = 0;
            $rdata['info'] = '奖券不存在！';
            $this->backJson($data);
        }
        $result = $lottery->isUnfreeze();
        if($result[0]==0) {
            $rdata['status'] = 0;
            $rdata['info'] = $result[1];
            $this->backJson($rdata); 
        }

        if($lottery->unfreeze()) {
            $data = [];
            $data['money'] = $lottery->money_rate;
            $data['userId'] = $lottery->userId;
            $data['remark'] = '解冻红包券'.$lottery->money_rate.'元';
            $status = API::addMoney($data);

            if($status) {

                $tradeNo = date('Ymd').(70000000+$lottery->id).rand(10,99);

                $status = User::where('userId', $lottery->userId)->update([
                    'fundMoney'=>DB::raw('fundMoney+'.$lottery->money_rate)
                ]);

                if($status) {
                    $log = [];
                    $log['serialNumber'] = $tradeNo;
                    $log['type'] = 'unfreeze';
                    $log['mode'] = 'in';
                    $log['mvalue'] = $lottery->money_rate;
                    $log['remark'] = $data['remark'];

                    $user->fundMoney = $user->fundMoney + $lottery->money_rate;
                    MoneyLog::addOne($log, $user);

                    Flash::success('解冻成功！');
                    $rdata['status'] = 1;
                    $rdata['info'] = '解冻成功！';
                    $this->backJson($rdata);
                } else {
                    Log::write('[unfreeze]解冻异常，平台添加金额失败，券ID：'.$lottery->id, [], 'error');
                    $rdata['status'] = 0;
                    $rdata['info'] = '解冻异常，请联系客服！';
                    $this->backJson($rdata);
                }
            } else {
                Log::write('[unfreeze]解冻异常，API添加金额失败，券ID：'.$lottery->id, [], 'error');
                $rdata['status'] = 0;
                $rdata['info'] = '解冻异常，请联系客服！';
                $this->backJson($rdata);
            }
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '解冻失败！';
            $this->backJson($rdata); 
        }
    }

    /**
     * 风险评估
     * @return mixed
     */
    public function estimateAction() {
        $this->mode = 'estimate';
        $this->display('estimate');
    }

    /**
     * 风险评估分数
     * @return mixed
     */
    public function submitEstimateAction() {
        $this->mode = 'estimate';
        $user = $this->getUser();
        $score = $this->getPost('score',-1);

        $user->estimateScore = $score;
        $user->save();

        if(isset($user->estimate)){
            $user->estimate->status = 0;
            $user->estimate->save();
        }

        $estimate = new UserEstimate;
        $estimate->userId = $user->userId;
        $estimate->addTime = date('Y-m-d H:i:s');
        $estimate->score = $score;
        $estimate->status = 1;
        $estimate->save();

        Flash::success('操作成功!');
        $rdata['status'] = 1;
        $rdata['info'] = '操作成功!';
        $this->backJson($rdata); 
    }

    /**
     * 宝付绑卡认证
     * @return mixed
     */
    public function baofooAction() {
        $this->mode = 'custody';
        $type = $this->getQuery('type', 'normal');
        $user = $this->getUser();
        $bank = null;
        if($user->custody_id!='' && $user->userType!=3) {
            Flash::error('您已经通过认证！');
            $this->redirect('/user/safe');
        }
        if($user->userType == 3){
            $type = 'company';
        }
        $bank = UserBank::where('userId',$user->userId)->first();
        if(!$bank){
            $bank = new UserBank;
        }
        $this->display('baofoo', ['bank'=>$bank, 'user'=>$user, 'type'=>$type]);
    }

    public function openCompanyAction() {
        $user = $this->getUser();
        $param = $this->getAllPost();
        $userBank = UserBank::where('userId',$user->userId)->first();
        if(!$userBank){
            $userBank = new UserBank;
            $userBank->userId = $user->userId;
            $userBank->createAt = date('Y-m-d H:i:s');
        }
        $userBank->updateAt = date('Y-m-d H:i:s');
        $userBank->enterpriseName = $param['enterpriseName'];
        $userBank->bankLicense = $param['bankLicense'];
        $userBank->legal = $param['legal'];
        $userBank->legalIdCardNo = $param['legalIdCardNo'];
        $userBank->bankUsername = $param['bankUsername'];
        $userBank->contactPhone = $param['contactPhone'];
        $userBank->bankNum = $param['bankNum'];
        $userBank->province = $param['province'];
        $userBank->city = $param['city'];
        $userBank->USCI = $param['USCI'];
        $userBank->subbranch = $param['subbranch'];
        $userBank->bankName = $param['bankName'];
        $userBank->bankCName = BankCard::getBankCName($param['bankName']);
        $userBank->save();
        $user->custody_id = $user->userId;
        $user->userType = 3;
        $user->name = $param['enterpriseName'];
        $user->cardnum = $param['legalIdCardNo'];
        $user->save();
        Flash::success('信息提交成功！');
        $this->redirect('/user/safe');
    }

    /**
     * 宝付绑卡认证1
     * @return mixed
     */
    public function openBaofoo1Action() {
        $params = $this->getAllPost();
        $form = new OpenBaofooForm($params);
        if($form->open1()) {
            if($form->result['resp_code'] != '0000'){
                $rdata['status'] = -1;
                $rdata['info'] = $form->result['resp_msg'];
                $this->backJson($rdata); 
            }else{
                $rdata['status'] = 1;
                $rdata['unique_code'] = $form->result['trans_id'];
                $this->backJson($rdata); 
            }
        } else {
            $rdata['status'] = -1;
            $rdata['info'] = $form->posError();
            $this->backJson($rdata); 
        }
    } 

    /**
     * 宝付绑卡
     * @return mixed
     */
    public function bindBaofooAction() {
        $params = $this->getAllPost();
        $form = new OpenBaofooForm($params);
        if($form->open2()) {
            if($form->result['resp_code'] != '0000'){
                $rdata['status'] = -1;
                $rdata['info'] = $form->result['resp_msg'];
                $this->backJson($rdata); 
            }else{
                $user = $this->getUser();
                $binInfo = BankCard::getBinInfo($params['bankNum']);

                $bankData = file_get_contents('https://ccdcapi.alipay.com/validateAndCacheCardInfo.json?_input_charset=utf-8&cardNo='.$params['bankNum'].'&cardBinCheck=true');
                $bankData = json_decode($bankData,true);
                Log::write('银行信息:', [$bankData], 'baofoo');

                UserBank::insert([
                    'bankCName'=>BankCard::getBankCName($bankData['bank']),
                    'bankName'=>$bankData['bank'],
                    'userId'=>$user['userId'], 
                    'bankNum'=>$params['bankNum'], 
                    'createAt'=>date('Y-m-d H:i:s'), 
                    'updateAt'=>date('Y-m-d H:i:s'),
                    'binInfo'=>$binInfo,
                    'cardType' => $params['card_type'],
                    'validDate' => isset($params['valid_date'])?$params['valid_date']:'',
                    'cvv' => isset($params['cvv'])?$params['cvv']:'',
                    'bindId' => $form->result['bind_id']
                ]);

                Flash::success('绑定成功!');
                $rdata['status'] = 1;
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
    public function openBaofoo2Action() {
        $params = $this->getAllPost();
        $form = new OpenBaofooForm($params);
        if(strlen($params['paypass'])<6 || !$params['paypass']) {
            $form->addError('paypass', '支付密码长度不能小于6位！');
        }
        if($form->open2()) {
            if($form->result['resp_code'] != '0000'){
                $rdata['status'] = -1;
                $rdata['info'] = $form->result['resp_msg'];
                $this->backJson($rdata); 
            }else{
                $user = $this->getUser();
                $birth = StringHelper::getBirthdayByCardnum($params['cardnum']);
                $sex = StringHelper::getSexByCardnum($params['cardnum']);
                User::where('userId', $user['userId'])->update([
                    'paypass'=>$user->password($params['paypass']),
                    'custody_id'=>$user['userId'], 
                    'cardnum'=>$params['cardnum'],
                    'name'=>$params['name'],
                    'sex'=>$sex, 
                    'birth'=>$birth, 
                    'cardstatus'=>'y',
                    'certificationTime'=>date('Y-m-d H:i:s'),
                    'bindThirdTime'=>date('Y-m-d H:i:s'),
                    'is_custody_pwd'=>1
                ]);
                
                Redis::updateUser([
                    'userId'=>$user['userId'],
                    'custody_id'=>$user['userId'],
                    'cardnum'=>$params['cardnum'],
                    'name'=>$params['name'],
                ]);

                $binInfo = BankCard::getBinInfo($params['bankNum']);
                
                $bankData = file_get_contents('https://ccdcapi.alipay.com/validateAndCacheCardInfo.json?_input_charset=utf-8&cardNo='.$params['bankNum'].'&cardBinCheck=true');
                $bankData = json_decode($bankData,true);
                Log::write('银行信息:', [$bankData], 'baofoo');

                UserBank::insert([
                    'bankCName'=>BankCard::getBankCName($bankData['bank']),
                    'bankName'=>$bankData['bank'],
                    'phone'=>$user->phone,
                    'userId'=>$user['userId'], 
                    'bankNum'=>$params['bankNum'], 
                    'createAt'=>date('Y-m-d H:i:s'), 
                    'updateAt'=>date('Y-m-d H:i:s'),
                    'binInfo'=>$binInfo,
                    'cardType' => $params['card_type'],
                    'validDate' => isset($params['valid_date'])?$params['valid_date']:'',
                    'cvv' => isset($params['cvv'])?$params['cvv']:'',
                    'bindId' => $form->result['bind_id']
                ]);


                $key = Redis::getKey('ancunQueue');
                $params = [$key];
                $list[] = json_encode(['key'=>$user->userId, 'type'=>'user', 'flow'=>0]);
                $list[] = json_encode(['key'=>$user->userId, 'type'=>'user', 'flow'=>1]);
                $params = array_merge($params, $list);
                call_user_func_array(array('tools\Redis', 'lpush'), $params);

                Flash::success('认证成功!');
                $rdata['status'] = 1;
                $this->backJson($rdata); 
            }
            
        } else {
            $rdata['status'] = -1;
            $rdata['info'] = $form->posError();
            $this->backJson($rdata); 
        }
    } 

    /**
     * 银行存管
     * @return mixed
     */
    public function custodyAction() {
        $baofoo = Registry::get('config')->get('baofoo')->get('open');
        if($baofoo){
            $this->baofooAction();
            exit;
        }
        
        $this->mode = 'custody';
        $type = $this->getQuery('type', 'normal');
        $user = $this->getUser();
        $bank = null;
        if($user->custody_id!='' && $user->custody_id!=1) {
            Flash::error('您已经开通存管！');
            $this->redirect('/user/safe');
        }
        $this->display('custody', ['bank'=>$bank, 'user'=>$user, 'type'=>$type]);
    }

    /**
     * 开通银行存管
     * @return mixed
     */
    public function openCustodyAction() {
        $params = $this->getAllPost();
        $form = new OpenCustodyForm($params);
        if($form->open()) {
            echo $form->html;
        } else {
            Flash::error($form->posError());
            $this->redirect('/account/custody');
        }
    }

    /**
     * 资金账户--账户充值——快速充值页面
     * @return  mixed
     */
    public function rechargeAction() {
        $this->mode = 'recharge';
        $type = $this->getQuery('type', 'normal');
        $user = $this->getUser();

        if($user->custody_id=='') {
            Flash::error('您还未进行实名认证！');
            $this->redirect('/account/custody');
        }

        $bank = UserBank::where('userId', $user->userId)->where('status', 1)->first();
        if(!$bank) {
            Flash::error('您还未绑定银行卡，请先绑定您的常用银行卡！');
            $this->redirect('/account/bank');
        }
        $bank->num = substr($bank->bankNum,-4);

        $baofoo = Registry::get('config')->get('baofoo')->get('open');
        if($baofoo){
            $this->display('rechargeold', ['user'=>$user, 'bank'=>$bank, 'type'=>$type]);
            exit;
        }

        $this->display('recharge', ['user'=>$user, 'bank'=>$bank, 'type'=>$type]);
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
                $data['money'] = $results['succ_amt'];
                $data['fee'] = 0;
                $data['status'] = -1;
                $data['result'] = $resp_code;
                $data['thirdSerialNo'] = $results['trans_no'];
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


    /**
     * 充值
     * @return mixed
     */
    public function doRechargeAction() {
        $params = $this->getAllPost();

        $user = $this->getUser();

        $baofoo = Registry::get('config')->get('baofoo')->get('open');
        if($baofoo){
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
     * 用户资金同步 (暂无)
     */
    public function syncMoneyAction() {
        $user = $this->getUser();
        if($user->custody_id=='') {
            Flash::success('用户未开通存管！');
            $rdata['status'] = 1;
            $rdata['info'] = '用户未开通存管！';
            $this->backJson($rdata); 
        }

        $data = [];
        $data['accountId'] = $user->custody_id;
        $data['startDate'] = date('Ymd', time()-2*24*3600);
        $data['endDate'] = date('Ymd');
        $data['type'] = 9;
        $data['tranType'] = '7820';
        $data['pageNum'] = 1;
        $data['pageSize'] = 50;
        $handler = new Handler('accountDetailsQuery', $data);
        $result = $handler->api();
        if($result['retCode']==Handler::SUCCESS) {
            $list = json_decode($result['subPacks'], true);
            foreach ($list as $item) {
                $params = [];
                $params['tradeNo'] = $item['inpDate'].$item['inpTime'].$item['traceNo'];
                $params['cid'] = $item['accountId'];
                $params['money'] = $item['txAmount'];
                $params['flag'] = $item['txFlag'];
                $params['tranType'] = $item['tranType'];
                API::syncLog($params);
            }
        }

        $result = API::syncMoney($user);
        if($result['status']) {
            $rdata['status'] = 1;
            $rdata['info'] = $result['msg'];
            $rdata['data'] = $result['data'];
            $this->backJson($rdata); 
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = $result['msg'];
            $this->backJson($rdata); 
        }
    }

    public function bankAction() {
        $user = $this->getUser();
        if($user->custody_id=='') {
            Flash::error('您还未进行实名认证！');
            $this->redirect('/account/custody');
        }
        $bank = UserBank::where('userId', $user->userId)->where('status', '1')->first();
        $this->display('bank', ['bank'=>$bank, 'user'=>$user]);
    }
}