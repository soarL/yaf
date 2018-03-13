<?php
use helpers\StringHelper;
use helpers\NumberHelper;
use Yaf\Registry;
use Illuminate\Database\Capsule\Manager as DB;
use tools\Qrcode;
use models\CustomService;
use models\UserVip;
use models\UserVipLog;
use models\Odd;
use models\OddMoney;
use models\UserDuein;
use models\Invest;
use models\OddClaims;
use models\Sms;
use models\Email;
use models\Reward;
use models\UserLink;
use models\UserOffice;
use models\UserHouse;
use models\User;
use models\Queue;
use models\AutoInvest;
use models\UserSetting;
use models\Lottery;
use models\Recharge;
use forms\UserInfoForm;
use forms\UserLinkForm;
use forms\UserHouseForm;
use forms\UserCompanyForm;
use forms\UpdateLoginpassForm;
use forms\CardnumAuthForm;
use forms\AutoInvestForm;
use forms\SetEmailForm;
use forms\UpdateEmailForm;
use forms\TransferForm;
use forms\DelTransferForm;
use tools\Pager;
use custody\API;
use plugins\mail\Mail;
use traits\PaginatorInit;
use custody\Handler;
use exceptions\HttpException;
use helpers\IDHelper;
use helpers\ExcelHelper;

/**
 * UserController
 * 用户中心
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class UserController extends Controller {
    use PaginatorInit;

    public $menu = 'account';
    
    /**
     * 个人信息-个人资料
     * @return mixed
     */
	public function infoAction() {
        $this->submenu = 'info';
        $this->mode = 'info';
        $user = $this->getUser();
        $userId = $user->userId;
        $userInfo = $user->prepareInfo();
        $userHouse = UserHouse::where('userId', $userId)->first();
        $houseInfo = $userHouse?$userHouse->prepareInfo():UserHouse::noneInfo();
        $userCompany = UserOffice::where('userId', $userId)->first();
        $companyInfo = $userCompany?$userCompany->prepareInfo():UserOffice::noneInfo();
        $userLink = UserLink::where('userId', $userId)->first();
        $contactInfo = $userLink?$userLink->prepareInfo():UserLink::noneInfo();
		$this->display('info', [
            'userInfo'=>$userInfo,
            'houseInfo'=>$houseInfo,
            'companyInfo'=>$companyInfo,
            'contactInfo'=>$contactInfo
        ]);
	}

    /**
     * 个人信息-获取用户信息(ajax)
     * @return mixed
     */
    public function ajaxGetInfoAction() {
        $user = $this->getUser();
        $userId = $user->userId;
        $type = $this->getPost('type', '');
        $rdata = [];
        $info = false;
        if($type=='basic') {
            $info = $user->prepareInfo();
            $rdata['status'] = 1;
            $rdata['info'] = $info;
        } else if($type=='house') {
            $info = UserHouse::where('userId', $userId)->first();
            $info = $info?$info->prepareInfo():UserHouse::noneInfo();
            $rdata['status'] = 1;
            $rdata['info'] = $info;
        } else if($type=='company') {
            $info = UserOffice::where('userId', $userId)->first();
            $info = $info?$info->prepareInfo():UserOffice::noneInfo();
            $rdata['status'] = 1;
            $rdata['info'] = $info;
        } else if($type=='contact') {
            $info = UserLink::where('userId', $userId)->first();
            $info = $info?$info->prepareInfo():UserLink::noneInfo();
            $rdata['status'] = 1;
            $rdata['info'] = $info;
        } else {
            $rdata['status'] = 0;
        }
        $this->backJson($rdata);
    }

    /**
     * 用户名是否存在
     * @return mixed
     */
	public function isUsernameExistAction() {
        $username = $this->getPost('username');
        $rdata = [];
        if(User::isUsernameExist($username)) {
            $rdata['status'] = 1;
            $rdata['info'] = '昵称存在!';
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '昵称不存在!';
        }
        $this->backJson($rdata);
	}

    /**
     * 手机号是否存在
     * @return mixed
     */
    public function isPhoneExistAction() {
        $phone = $this->getPost('phone');
        $rdata = [];
        if(User::isPhoneExist($phone)) {
            $rdata['status'] = 1;
            $rdata['info'] = '手机存在!';
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '手机不存在!';
        }
        $this->backJson($rdata);
    }

    /**
     * 身份证号是否存在
     * @return boolean [description]
     */
    public function isIDCardExistAction() {
        $cardnum = $this->getPost('cardnum');
        $rdata = [];
        if(User::isIDCardExist($cardnum)) {
            $rdata['status'] = 1;
            $rdata['info'] = '身份证号存在!';
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '身份证号不存在!';
        }
        $this->backJson($rdata);
    }

    /**
     * 借款记录
     * @return mixed
     */
    public function loanRecordAction() {
        $user = $this->getUser();
        $this->submenu = 'loan';
        $this->mode = 'loanRecord';
        $queries = $this->queries->defaults(['type'=>'start']);
        $type = $queries->type;
        $builder = Odd::with(['interests'=>function($query) { $query->select('oddNumber', 'status', 'endtime');}])
            ->where('userId', $user->userId);
        if($type=='start'||$type=='run'||$type=='fail'||$type=='end') {
            $builder->where('progress', $type);
        }
        // $select = ['oddNumber', 'oddYearRate', 'oddBorrowStyle', 'oddBorrowPeriod', 'oddMoney', 'oddRepaymentStyle', 'oddTrialTime', 'oddRehearTime'];
        $loanList = $builder->paginate();
        $loanList->appends($queries->all());

        $this->display('loanRecord', ['loanList'=>$loanList, 'queries'=>$queries]);
    }

    /**
     * 投资详情
     * @return mixed
     */
    public function detailAction() {
        $this->submenu = 'account';
        $this->mode = 'investRecord';
        $user = $this->getUser();
        $queries = $this->queries->defaults(['type'=>'repaying', 'timeBegin'=>'', 'timeEnd'=>'']);
        $type = $queries->type;
        $timeBegin = $queries->timeBegin;
        $timeEnd = $queries->timeEnd;
        
        $builder = OddMoney::with('odd', 'ancun')->where('type', 'invest')->where('userId', $user->userId);
        $builder2 = OddMoney::with('odd', 'ancun')->where('type', 'credit')->where('userId', $user->userId);

        if($timeBegin!='') {
            $builder->where('time', '>=', $timeBegin . ' 00:00:00');
            $builder2->where('time', '>=', $timeBegin . ' 00:00:00');
        }
        if($timeEnd!='') {
            $builder->where('time', '<', $timeEnd . ' 23:59:59');
            $builder2->where('time', '<', $timeEnd . ' 23:59:59');
        }

        if($type!='all') {
            $prgs = Odd::$progressTypes[$type];
            $builder->whereHas('odd', function($q) use ($prgs){
                $q->whereIn('progress', $prgs);
            });
            $builder2->whereHas('odd', function($q) use ($prgs){
                $q->whereIn('progress', $prgs);
            });
        }

        $debts = $builder->orderBy('time', 'desc')->paginate();
        $debts->appends($queries->all());

        $creditdebts = $builder2->orderBy('time', 'desc')->paginate();
        $creditdebts->appends($queries->all());

        $userId = $user->userId;
        $row = Invest::where('userId', $userId)
            ->where('status', 0)
            ->whereHas('oddMoney', function($q) {
                $q->where('type', 'credit');
            })
            ->whereHas('odd', function($q) {
                $q->where('oddType', 'house-mor');
            })->first([
                DB::raw('sum(benJin) totalCapital'), 
                DB::raw('sum(interest) totalInterest')
            ]);
        $houseCreditStayCapital = floatval($row->totalCapital);
        $houseCreditStayInterest = floatval($row->totalInterest);
        $row = Invest::where('userId', $userId)
            ->where('status', 0)
            ->whereHas('oddMoney', function($q) {
                $q->where('type', 'credit');
            })
            ->whereHas('odd', function($q) {
                $q->where('oddType', 'auto-ins');
            })->first([
                DB::raw('sum(benJin) totalCapital'), 
                DB::raw('sum(interest) totalInterest')
            ]);
        $insCreditStayCapital = floatval($row->totalCapital);
        $insCreditStayInterest = floatval($row->totalInterest);

        $row = Invest::where('userId', $userId)
            ->where('status', 0)
            ->whereHas('oddMoney', function($q) {
                $q->where('type', 'invest');
            })
            ->whereHas('odd', function($q) {
                $q->where('oddType', 'house-mor');
            })->first([
                DB::raw('sum(benJin) totalCapital'), 
                DB::raw('sum(interest) totalInterest')
            ]);
        $houseInvestStayCapital = floatval($row->totalCapital);
        $houseInvestStayInterest = floatval($row->totalInterest);
        $row = Invest::where('userId', $userId)
            ->where('status', 0)
            ->whereHas('oddMoney', function($q) {
                $q->where('type', 'invest');
            })
            ->whereHas('odd', function($q) {
                $q->where('oddType', 'auto-ins');
            })->first([
                DB::raw('sum(benJin) totalCapital'), 
                DB::raw('sum(interest) totalInterest')
            ]);
        $insInvestStayCapital = floatval($row->totalCapital);
        $insInvestStayInterest = floatval($row->totalInterest);

        $this->display('detail', [
            'debts'=>$debts, 
            'creditdebts'=>$creditdebts,
            'queries'=>$queries,
            'houseCreditStayCapital'=>round(floatval($houseCreditStayCapital), 2),
            'houseCreditStayInterest'=>round(floatval($houseCreditStayInterest), 2),
            'insCreditStayCapital'=>round(floatval($insCreditStayCapital), 2),
            'insCreditStayInterest'=>round(floatval($insCreditStayInterest), 2),
            'houseInvestStayCapital'=>round(floatval($houseInvestStayCapital), 2),
            'houseInvestStayInterest'=>round(floatval($houseInvestStayInterest), 2),
            'insInvestStayCapital'=>round(floatval($insInvestStayCapital), 2),
            'insInvestStayInterest'=>round(floatval($insInvestStayInterest), 2),
        ]);
    }

    /**
     * 债权转让记录
     * @return mixed
     */
    public function assignmentAction() {
        $this->submenu = 'invest';
        $this->mode = 'assignment';
        $user = $this->getUser();
        $userId = $user->userId;
        $queries = $this->queries->defaults(['type'=>'out']);
        $type = $queries->type;
        $builder = null;
        if($type=='out') {
            $builder = OddClaims::getOutBuilder($userId);
        } else if($type=='buy') {
            $builder = OddClaims::getBuyBuilder($userId);
        } else if($type=='back') {
            $builder = OddClaims::getBackBuilder($userId);
        } else if($type=='del') {
            $builder = OddClaims::getDelBuilder($userId);
        }
        
        $claims = $builder->paginate();
        $claims->appends($queries->all());

        $this->display('assignment', ['claims'=>$claims, 'queries'=>$queries]);
    }

    /**
     * 债权转让记录(新)
     * @return mixed
     */
    public function assignAction() {
        $this->submenu = 'invest';
        $this->mode = 'assign';
        $user = $this->getUser();
        $userId = $user->userId;
        $queries = $this->queries->defaults(['type'=>'can', 'timeBegin'=>'', 'timeEnd'=>'']);
        $type = $queries->type;
        $timeBegin = $queries->timeBegin;
        $timeEnd = $queries->timeEnd;
        $builder = null;
        if($type=='can') {
            $builder = OddMoney::getCanTransferBuilder($userId);
        } else if($type=='sell') {
            $builder = OddMoney::getSellBuilder($userId);
        } else if($type=='buy') {
            $builder = OddMoney::getBuyBuilder($userId);
        } else if($type=='ing') {
            $builder = OddMoney::getIngBuilder($userId);
        } else if($type=='repay') {
            $builder = OddMoney::getRepayBuilder($userId);
        } else if($type=='over') {
            $builder = OddMoney::getOverBuilder($userId);
        }
        if($timeBegin!='') {
            $builder->where('time', '>=', $timeBegin . ' 00:00:00');
        }
        if($timeEnd!='') {
            $builder->where('time', '<', $timeEnd . ' 23:59:59');
        }
        $oddMoneys = $builder->paginate();
        $oddMoneys->appends($queries->all());
        $this->display('assign', ['oddMoneys'=>$oddMoneys, 'queries'=>$queries, 'user'=>$user]);
    }

    /**
     * 自动投标
     * @return mixed auto_credit_auth autoy_bid_auth
     */
    public function autoInvestAction() {
        $this->submenu = 'invest';
        $this->mode = 'autoInvest';
        $user = $this->getUser();
        $userId = $user->userId;
        $mode = $this->getQuery('mode', 0);
        // if($user->auto_bid_auth=='') {
        //     Flash::error('您还未进行自动投标签约');
        //     $this->redirect('/user/custodyAuth');
        // }
        if($user->custody_id=='') {
            Flash::error('您还未进行实名认证！');
            $this->redirect('/account/custody');
        }
        
        $autoInvest = AutoInvest::where('userId', $userId)->first();

        $typeIds = [];
        $periods = [];
        if($autoInvest) {
            $protocol = true;
            $typeIds = $autoInvest->getTypeIDList();
            foreach ($typeIds as $typeId) {
                if(isset(AutoInvest::$types[$typeId])){
                    $periods[] = AutoInvest::$types[$typeId]['period'] . AutoInvest::$types[$typeId]['periodType'];
                }
            }
            if($mode===null) {
                $mode = $autoInvest->mode;
            }
        } else {
            $protocol = false;
            $autoInvest = new AutoInvest();
            $autoInvest->status = 0;
        }

        $invalidPer = '0%';
        $validPer = '0%';
        $preInfo = ['allMoney'=>0, 'invalidMoney'=>0, 'validMoney'=>0, 'validNum'=>0, 'invalidNum'=>0, 'allNum'=>0];
        $location = '--';
        $inQueue = '--';
        $queue = new Queue();
        $allInfo = $queue->getQueuesInfo('all');
        if($autoInvest->queue) {
            $preInfo = $autoInvest->queue->getQueuesInfo('pre');
            $validPer = $preInfo['allMoney']?round($preInfo['validMoney']/$preInfo['allMoney'], 4)*100:0;
            $invalidPer =  round((100 - $validPer), 2).'%';
            $validPer = round($validPer, 2).'%';
            $location = $autoInvest->queue->location;
            $inQueue = $autoInvest->investable()?'有效队列':'无效队列';
        }

        $allPeriods = [];
        foreach (AutoInvest::$types as $type) {
            if($type['status']==1) {
                $periodName = $type['period'] . $type['periodType'];
                if(!in_array($periodName, $allPeriods)) {
                    $allPeriods[] = $periodName;
                }
            }
        }

        if($autoInvest->status == 0){
            $total = 0;
        }elseif($autoInvest->total){
            $total = $autoInvest->total;
        }else{
            $total = $user->fundMoney - $autoInvest->investEgisMoney;
            if($total < $autoInvest->investMoneyLower){
                $total = 0;
            }else{
                if($total > $autoInvest->investMoneyUper){
                    $total = $autoInvest->investMoneyUper;
                }
            }
        }
        $total = number_format($total,2);

        $data = [];
        $data['allPeriods'] = $allPeriods;
        $data['autoInvest'] = $autoInvest;
        $data['types'] = AutoInvest::$types;
        $data['oddTypes'] = AutoInvest::$oddTypes;
        $data['status'] = AutoInvest::$status;
        $data['typeIds'] = $typeIds;
        $data['total'] = $total;
        $data['allType'] = count($typeIds) == count(AutoInvest::$types);
        $data['periods'] = $periods;
        $data['preInfo'] = $preInfo;
        $data['allInfo'] = $allInfo;
        $data['location'] = $location;
        $data['invalidPer'] = $invalidPer;
        $data['validPer'] = $validPer;
        $data['inQueue'] = $inQueue;
        $data['user'] = $user;
        $data['mode'] = $mode;
        $data['protocol'] = $protocol;
        $lotteries = Lottery::whereIn('type', ['invest_money', 'interest', 'money'])
            ->where('userId', $user->userId)
            ->where('endtime', '>', date('Y-m-d H:i:s'))
            ->whereIn('status', [Lottery::STATUS_NOUSE, Lottery::STATUS_FROZEN])
            ->get();

        $records = [];
        foreach ($lotteries as $lottery) {
            $row = [];
            $row['money_lower'] = $lottery->money_lower;
            $row['period_lower'] = $lottery->period_lower;
            $row['id'] = $lottery->id;
            $row['name'] = $lottery->getName();
            $records[] = $row;
        }
        $data['lotteries'] = $records;
        $tpl = 'autoInvest';
        if($mode==1) {
            $tpl = 'autoInvestSimple';
        }
        $this->display($tpl, $data);
    }

    /**
     * 自动投标
     * @return mixed
     */
    public function oldAutoInvestAction() {
        $this->submenu = 'invest';
        $this->mode = 'autoInvest';
        $user = $this->getUser();
        $userId = $user->userId;
        if($user->thirdAccountAuth==0) {
            Flash::error('未授权给汇诚普惠，请先授权！');
            $this->redirect('/account/third');
        }
        $autoInvest = AutoInvest::where('userId', $userId)->first();
        $location = false;
        $preMoney = 0;
        if($autoInvest) {
            if($autoInvest->autostatus==1) {
                $queue = Queue::where('userId', $userId)->first();
                $location = $queue->location;
                $queues = Queue::where('location', '<', $location)->get(['userId']);
                $users = [];
                foreach ($queues as $row) {
                    $users[] = $row->userId;
                }
                if(count($users)>0) {
                    $preMoney = User::whereIn('userId', $users)->sum('fundMoney');
                }
            }
        } else {
            $autoInvest = new AutoInvest();
        }
        $this->display('autoInvest', ['autoInvest'=>$autoInvest, 'location'=>$location, 'preMoney'=>$preMoney]);
    }

    /**
     * 自动投标设置
     * @return mixed
     */
    public function ajaxAutoInvestAction() {
        $params = $this->getAllPost();
        $form = new AutoInvestForm($params);
        $rdata = [];
        if($form->update()) {
            $rdata['status'] = 1;
            Flash::success('设置成功！');
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = $form->posError();
        }
        $this->backJson($rdata);
    }

    /**
     * 修改基本资料
     */
    public function infoBasicAction() {
        if($this->isGet()) {
            $user = $this->getUser();
            $this->display('infoBasic', ['user'=>$user]);
        } else {
            $form = new UserInfoForm($this->getAllPost());
            if($form->update()) {
                Flash::success('更新成功！');
                $this->redirect('/user/info#basic');
            } else {
                Flash::error($form->posError());
                $this->redirect('/user/infobasic');
            }
        }
    }

    /**
     * 修改联系资料
     */
    public function infoContactAction() {
        if($this->isGet()) {
            $user = $this->getUser();
            $info = UserLink::where('userId', $user->userId)->first();
            $this->display('infoContact', ['info'=>$info]);
        } else {
            $form = new UserLinkForm($this->getAllPost());
            if($form->update()) {
                Flash::success('更新成功！');
                $this->redirect('/user/info#contact');
            } else {
                Flash::error($form->posError());
                $this->redirect('/user/infoContact');
            }
        }
    }

    /**
     * 修改单位资料
     */
    public function infoCompanyAction() {
        if($this->isGet()) {
            $user = $this->getUser();
            $info = UserOffice::where('userId', $user->userId)->first();
            $this->display('infoCompany', ['info'=>$info]);
        } else {
            $form = new UserCompanyForm($this->getAllPost());
            if($form->update()) {
                Flash::success('更新成功！');
                $this->redirect('/user/info#company');
            } else {
                Flash::error($form->posError());
                $this->redirect('/user/infoCompany');
            }
        }
    }

    /**
     * 修改房产资料
     */
    public function infoHouseAction() {
        if($this->isGet()) {
            $user = $this->getUser();
            $info = UserHouse::where('userId', $user->userId)->first();
            $this->display('infoHouse', ['info'=>$info]);
        } else {
            $form = new UserHouseForm($this->getAllPost());
            if($form->update()) {
                Flash::success('更新成功！');
                $this->redirect('/user/info#house');
            } else {
                Flash::error($form->posError());
                $this->redirect('/user/infoHouse');
            }
        }
    }

    /**
     * 个人信息-安全中心
     * @return mixed
     */
    public function safeAction() {
        $this->submenu = 'setting';
        $this->mode = 'safe';
        $user = $this->getUser();
        $this->display('safe', ['user'=>$user]);
    }

    /**
     * 更新邮箱验证
     * @return mixed
     */
    public function validateUpdateEmailAction($code, $email) {
        $user = $this->getUser();
        $result = Email::checkCode($email, $code, 'checkUpdateEmail', $user->userId);
        if($result['status']==1) {
            $this->display('updateEmail', ['step'=>'validate']);       
        } else {
            Flash::error($result['info']);
            $this->redirect('/user/safe');
        }
    }

    /**
     * 修改邮箱验证
     * @return mixed
     */
    public function validateUpdateEmailTwoAction($code, $email) {
        $user = $this->getUser();
        $result = Email::checkCode($email, $code, 'updateEmail', $user->userId);
        if($result['status']==1) {
            if(User::isEmailExist($email)) {
                Flash::error('该邮箱已存在！');
                $this->redirect('/user/safe');  
            }
            $log = Email::where('sendCode', $code)->where('email', $email)->first();
            User::where('userId', $log->userId)->update(['email'=>$log->email]);
            Flash::success($result['info']);
            $this->redirect('/user/safe');  
        } else {
            Flash::error($result['info']);
            $this->redirect('/user/safe');
        }
    }

    /**
     * 修改邮箱
     * @return mixed
     */
    public function updateEmailAction() {
        $user = $this->getUser();
        if($user->emailstatus=='n') {
            Flash::error('您还未设置常用邮箱，请先设置常用邮箱！');
            $this->redirect('/user/setEmail');
        }
        if($this->isPost()) {
            $params = $this->getAllPost();
            $form = new UpdateEmailForm($params);
            if($form->send()) {
                Flash::success('邮件发送成功，请登录邮箱进行验证！');
                $this->redirect('/user/safe');
            } else {
                Flash::error($form->posError());
                $this->redirect('/user/updateEmail');
            }
        } else {
            $this->display('updateEmail', ['step'=>'send', 'email'=>$user->email]);
        }
    }

    /**
     * 设置邮箱
     * @return mixed
     */
    public function setEmailAction() {
        $user = $this->getUser();
        if($user->emailstatus=='y') {
            Flash::error('您已设置常用邮箱，您可修改常用邮箱！');
            $this->redirect('/user/updateEmail');
        }
        if($this->isPost()) {
            $params = $this->getAllPost();
            $form = new SetEmailForm($params);
            if($form->send()) {
                Flash::success('邮件发送成功，请登录邮箱进行验证！');
                $this->redirect('/user/safe');   
            } else {
                Flash::error($form->posError());
                $this->redirect('/user/setEmail');
            }
        } else {
            $this->display('setEmail');
        }
    }

    /**
     * 设置邮箱验证
     * @return mixed
     */
    public function validateSetEmailAction($code, $email) {
        $user = $this->getUser();
        $userId = $user->userId;
        $result = Email::checkCode($email, $code, 'setEmail', $userId);
        if($result['status']==1) {
            $status = User::where('userId', $userId)->update(['emailstatus'=>'y', 'email'=>$email]);
            if($status) {
                Flash::success('设置邮箱成功!');
                $this->redirect('/user/safe');
            } else {
                Flash::error('设置邮箱失败!');
                $this->redirect('/user/safe');
            }
        } else {
            Flash::error($result['info']);
            $this->redirect('/user/safe');   
        }
    }

    /**
     * 实名认证
     * @return mixed
     */
    public function cardnumAuthAction() {
        $user = $this->getUser();
        if($user->cardstatus=='y') {
            Flash::error('您已进行实名认证，不可修改！');
            $this->redirect('/user/safe');
        }
        if($this->isPost()) {
            $params = $this->getAllPost();
            $form = new CardnumAuthForm($params);
            if($form->update()) {
                Flash::success('认证成功！');
                $this->redirect('/user/safe');   
            } else {
                Flash::error($form->posError());
                $this->redirect('/user/cardnumAuth');
            }
        } else {
            $this->display('cardnumAuth');
        }
    }
	
    /**
     * 更新登录密码
     * @return mixed
     */
    public function updateLoginpassAction() {
        if($this->isPost()) {
            $params = $this->getAllPost();
            $form = new UpdateLoginpassForm($params);
            if($form->update()) {
                User::logout();
                Flash::success('修改登录密码成功,请重新登录！');
                $this->redirect('/login');
            } else {
                Flash::error($form->posError());
                $this->redirect('/user/updateLoginpass');
            }
        } else {
            $this->display('updateLoginpass');
        }   
    }

    /**
     * VIP申请页面
     * @return mixed
     */
    public function vipAction() {
        $user = $this->getUser();
        $customServices = CustomService::where('dept_id', 11)->orderBy(DB::raw('rand()'))->get(['uid', 'user_name']);
        $userVip = UserVip::getVipByUser($user->userId);
        $customService = null;
        if($userVip) {
            $customService = CustomService::where('uid', $userVip->customService)->where('dept_id', 11)->first();
        }
        $this->display('vip', ['customServices'=>$customServices, 'userVip'=>$userVip, 'customService'=>$customService]);
    }

    /**
     * VIP申请
     * @return mixed
     */
    public function vipApplyAction() {
        $user = $this->getUser();
        $code = $this->getPost('time', 0);
        $customService = $this->getPost('customService', 0);
        $result = UserVip::getTMByCode($code);
        $rdata = [];
        if(!$result) {
            $rdata['status'] = 0;
            $rdata['info'] = '请选择开通时长！';
            $this->backJson($rdata);
        }
        if(!CustomService::checkIsCustomService($customService)) {
            $rdata['status'] = 0;
            $rdata['info'] = '客服不存在！';
            $this->backJson($rdata);
        }

        $isNeedAuth = false;

        $time = $result['time'];
        $money = $result['money'];

        $log = new UserVipLog();
        $log->userId = $user->userId;
        $log->addTime = date('Y-m-d H:i:s');
        $log->applyTime = $time;
        $log->applyMoney = $money;
        $log->customService = $customService;

        if(!$isNeedAuth) {
            $log->status = 1; // 若需要审核请改为0
        }
        $log->save();

        if(!$isNeedAuth) {
            $userVip = UserVip::where('userId', $user->userId)->first();
            if($userVip) {
                $endTime = '';
                if($userVip->status==0) {
                    $endTime = date('Y-m-d H:i:s', ($time + time()));
                } else {
                    $endTime = date('Y-m-d H:i:s', (strtotime($userVip->endTime) + $time));
                }
                $userVip->endTime = $endTime;
                $userVip->customService = $customService;
                $userVip->status = 1;
                $userVip->save();
            } else {
                $userVip = new UserVip();
                $userVip->grade = 1; //暂时无VIP等级分别
                $userVip->userId = $user->userId;
                $userVip->addTime = date('Y-m-d H:i:s');
                $userVip->endTime = date('Y-m-d H:i:s', ($time + time()));
                $userVip->customService = $customService;
                $userVip->status = 1;
                $userVip->save();
            }
            Flash::success('开通成功！');
            $rdata['status'] = 1;
            $rdata['info'] = '开通成功！';
            $this->backJson($rdata);
        }
        Flash::success('提交成功，请耐心等待客服审核！');
        $rdata['status'] = 1;
        $rdata['info'] = '提交成功，请耐心等待客服审核！';
        $this->backJson($rdata);
    }

    /**
     * 债权转让(新)
     * @return mixed
     */
    public function transferAction() {
        $params = $this->getAllPost();

        $form = new TransferForm($params);
        $rdata = [];
        if($form->transfer()) {
            Flash::success('转让申请成功，等待其他用户认购！');
            $rdata['status'] = 1;
            $this->backJson($rdata);
        } else {
            $rdata['info'] = $form->posError();
            $rdata['status'] = 0;
            $this->backJson($rdata);
        }
    }

    /**
     * 撤销债权转让(新)
     * @return mixed
     */
    public function delTransferAction() {
        $params = $this->getAllPost();
        $form = new DelTransferForm($params);
        $rdata = [];
        if($form->delete()) {
            Flash::success('撤销转让成功！');
            $rdata['status'] = 1;
            $this->backJson($rdata);
        } else {
            $rdata['info'] = $form->posError();
            $rdata['status'] = 0;
            $this->backJson($rdata);
        }
    }

    /**
     * 获取投资奖励金额
     * @return mixed
     */
    public function getTenderRewardAction() {
        $user = $this->getUser();
        $oddMoneyId = $this->getPost('oddMoneyId');
        $reward = Reward::getTenderReard($user->userId, $oddMoneyId);    
        $rdata['status'] = 1;
        $rdata['reward'] = $reward;
        $this->backJson($rdata);
    }

    /**
     * 获取投资信息
     * @return mixed
     */
    public function getTenderInfoAction() {
        $oddMoneyId = $this->getPost('oddMoneyId');
        $oddMoney = OddMoney::find($oddMoneyId);
        $result = $oddMoney->getTenderInfo();
        $rdata['status'] = 1;
        $rdata['tenderInfo'] = $result;
        $this->backJson($rdata);
    }

    /**
     * 用户设置
     * @return mixed
     */
    public function settingAction() {
        $this->submenu = 'info';
        $this->mode = 'setting';
        $user = $this->getUser();
        $setting = UserSetting::where('userId', $user->userId)->first();
        if(!$setting) {
            $setting = ['spread_show'=>1];
        }
        $this->display('setting', ['setting'=>$setting]);
    }

    /**
     * 用户设置
     * @return mixed
     */
    public function doSettingAction() {
        $user = $this->getUser();
        $name = $this->getPost('name', null);
        $value = $this->getPost('value', null);
        $settings = ['spread_show'];
        $rdata = [];
        if(!in_array($name, $settings)) {
            $rdata['status'] = 0;
            $rdata['info'] = '设置错误！';
            $this->backJson($rdata);
        }
        if($value===null) {
            $rdata['status'] = 0;
            $rdata['info'] = '设置错误！';
            $this->backJson($rdata);
        }
        $user = $this->getUser();
        $setting = UserSetting::where('userId', $user->userId)->first();
        if($setting) {
            $setting->$name = $value;
        } else {
            $setting  = new UserSetting();
            $setting->userId = $user->userId;
            $setting->$name = $value;
        }
        if($setting->save()) {
            $rdata['status'] = 1;
            $rdata['info'] = '设置成功！'; 
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '设置错误！';
        }
        $this->backJson($rdata);
    }

    /**
     * 更新存管密码
     * @return mixed
     */
    public function resetCustodyPasswdAction() {
        $user = $this->getUser();
        echo API::resetPassword($user->userId);
    }

    /**
     * 自动投标/债转签约页面
     * @return mixed
     */
    public function custodyAuthAction() {
        $user = $this->getUser();
        $this->display('custodyAuth', ['bidAuth'=>$user->auto_bid_auth, 'creditAuth'=>$user->auto_credit_auth]);
    }

    /**
     * 自动投标/债转签约
     * @param  string $mode bid|credit
     * @return mixed
     */
    public function doCustodyAuthAction($mode) {
        $user = $this->getUser();

        if($mode=='bid') {
            $data = [];
            $data['userNo'] = $user->userId;
            $data['authList'] = 'TENDER';
            
            // $data['orderId'] = Handler::SEQ_PL;
            // $data['txAmount'] = 99999999;
            // $data['totAmount'] = 99999999;
            // $data['forgotPwdUrl'] = WEB_SAFE;
            $data['callbackUrl'] = WEB_USER.'/user/custodyAuth';
            $data['notifyUrl'] = WEB_MAIN.'/custody/autoBidAuthNotify';
            $data['acqRes'] = '';

            $handler = new Handler('MEMBER_AUTH_API', $data);
            $handler->form();
        } else if($mode=='credit') {
            $data = [];
            $data['userNo'] = $user->userId;
            $data['authList'] = 'CREDIT_ASSIGNMENT';


            // $data['orderId'] = Handler::SEQ_PL;
            // $data['forgotPwdUrl'] = WEB_SAFE;
            $data['callbackUrl'] = WEB_USER.'/user/custodyAuth';
            $data['notifyUrl'] = WEB_MAIN.'/custody/autoCreditAuthNotify';
            $data['acqRes'] = '';

            $handler = new Handler('MEMBER_AUTH_API', $data);
            $handler->form();
        } else {
            throw new HttpException(404);
        }
    }

    /**
     * 修改手机号码
     * @return mixed
     */
    public function updatePhoneAction() {
        $user = $this->getUser();
        if($this->isPost()) {
            $phone = $this->getPost('phone', '');
            if($phone=='') {
                Flash::error('请输入手机号！');
                $this->redirect('/user/safe');
            } else if($phone==$user->phone) {
                Flash::error('您的手机号已经是该号码！');
                $this->redirect('/user/safe');
            } else if(User::isPhoneExist($phone)) {
                Flash::error('手机号已存在！');
                $this->redirect('/user/safe');
            }

            echo API::modifyPhone($user->userId, $phone);
        } else {
            //$this->display('updatePhone', ['phone'=>$user->phone]);
        }
    }

    public function trustPayAction() {     
        if($this->isPost()) {
            $user = $this->getUser();
            $oddNumber = $this->getPost('oddNumber', '');
            if($oddNumber=='') {
                Flash::error('请输入标的号');
                $this->redirect('/user/trustPay');
            }
            $odd = Odd::where('oddNumber', $oddNumber)->first(['oddNumber', 'progress', 'userId', 'receiptUserId']);
            if(!$odd) {
                Flash::error('标的不存在');
                $this->redirect('/user/trustPay');
            }
            if($odd->userId!=$user->userId) {
                Flash::error('这不是您自己的借款');
                $this->redirect('/user/trustPay');
            }
            if($odd->receiptUserId=='') {
                Flash::error('后台未设置收款人账户');
                $this->redirect('/user/trustPay');
            }

            $data = [];
            $data['accountId'] = User::getCID($odd->userId);
            $data['productId'] = _ntop($oddNumber);
            $data['idType'] = '01';
            $data['idNo'] = $user->cardnum;
            $data['receiptAccountId'] = User::getCID($odd->receiptUserId);
            $data['forgotPwdUrl'] = WEB_SAFE;
            $data['retUrl'] = WEB_USER.'/user/trustPay';
            $data['notifyUrl'] = WEB_MAIN.'/custody/trustPayNotify';
            $data['acqRes'] = '';
            $handler = new Handler('trusteePay', $data);
            $result = $handler->form();
        } else {
            $this->display('trustPay');
        }
    }

    public function messageAction() {
        $this->display('message');
    }

    public function spreadAction() {
        $this->submenu = 'special';
        $user = $this->getUser();
        $spreadCode = $user->getSpreadCode();
        $spreadUrl = WEB_MAIN.'/page/spread/index.html?spreadCode='.$spreadCode;
        $imgUrl = Qrcode::getPng($spreadUrl,$spreadCode);

        $spreadUrl = WEB_MAIN.'/page/packet/luodi.html?spreadCode='.$spreadCode;
        $imgUrl2 = Qrcode::getPng($spreadUrl,'l'.$spreadCode);

        $spreadUrl = WEB_MAIN.'/page/packet/index.html?spreadCode='.$spreadCode;
        $imgUrl3 = Qrcode::getPng($spreadUrl,'i'.$spreadCode);

        $this->display('spread', ['spreadUrl'=>$spreadUrl,'imgUrl'=>WEB_USER.$imgUrl,'imgUrl2'=>WEB_USER.$imgUrl2,'imgUrl3'=>WEB_USER.$imgUrl3]);
    }

    public function friendsAction() {
        $tuijian = Registry::get('user');
        $excel = $this->getQuery('excel', 0);
        $date = $this->getQuery('date', 0);
        $userId = $this->getQuery('userId', 0);

        $queries = $this->queries->defaults(['searchType'=>'username', 'searchContent'=>'', 'userType'=>'', 'beginTime'=>'', 'endTime'=>'', 'order'=>'', 'comments'=>'']);

        if($date){
            $startDate = date('Ym',strtotime($date)).'00';
            $endDate = date('Ym',strtotime($date.' +1 month')).'00';
        }else{
            $startDate = date('Ym',strtotime('-1 month')).'00';
            $endDate = date('Ym',strtotime('+1 month')).'00';
        }

        $builder = User::whereRaw('1=1')->with('waiter')->with(['UserDuein'=>function($query)use($startDate,$endDate){
                $query->where('date','>',$startDate)->where('date','<',$endDate);
            }]);

        $builder->where('tuijian', $tuijian->phone);

        if($queries->searchContent!='') {
            $searchContent = trim($queries->searchContent);
            $builder->where($queries->searchType, 'like','%'.$searchContent.'%');
        }
        if($queries->beginTime!='') {
            $builder->where('addtime', '>=', $queries->beginTime . ' 00:00:00');
        }
        if($queries->endTime!='') {
            $builder->where('addtime', '<=', $queries->endTime . ' 23:59:59');
        }
        if($queries->userType!='') {
            $builder->where('userType', $queries->userType);
        }
        if($queries->channel!='') {
            $builder->where('channel_id', $queries->channel);
        }
        if($queries->order!=''){
            if($queries->order == 'duein'){
                $builder->leftjoin('user_duein',function($q){
                    $q->on('system_userinfo.userId','=','user_duein.userId')->where('user_duein.date', '=',date('Ymd',strtotime('-1 day')));
                })->orderBy('stay','desc');
            }else{
                $builder->orderBy($queries->order,'desc');
            }
        }

        if($excel) {
            $users = $builder->get();
        } else {
            $users = $builder->paginate();
        }
        $userIds = [];
        foreach ($users as $key => $value) {
            $userIds[] = $value->userId;
        }
        $stayMoney = Invest::select(DB::raw('sum(benJIn) stay, userId'))
            ->where('status','0')->whereIn('userId', $userIds)
            ->groupBy('userId')
            ->get();
        $stayList = [];
        foreach ($stayMoney as $key => $value) {
            $stayList[$value->userId] = $value->stay;
        }

        $emonth = 0;
        $tmonth = 0;
        $dueinData = [];

        if(!$date){
            $month = date('Ym').'00';

            $cityList = [];
            foreach ($users as $key => $value) {
                foreach ($value->UserDuein as $key => $duein) {
                    if(isset($dueinData[$duein->date])){
                        $dueinData[$duein->date] += $duein->stay;
                    }else{
                        $dueinData[$duein->date] = $duein->stay;    
                    }
                }

                if($value->cardnum){
                    $cityList[$value->userId] = IDHelper::getAddress($value->cardnum);
                }else{
                    $cityList[$value->userId] = '';
                }
            }

            foreach ($dueinData as $key => $value) {
                if($key > $month){
                    $tmonth += UserDuein::calcCommission($value,$value);
                }elseif($key < $month){
                    $emonth += UserDuein::calcCommission($value,$value);
                }
            }
        }else{
            foreach ($users as $key => $value) {
                foreach ($value->UserDuein as $key => $duein) {
                    if(isset($dueinData[$duein->date])){
                        $dueinData[$duein->date] += $duein->stay;
                    }else{
                        $dueinData[$duein->date] = $duein->stay;    
                    }
                }
            }
        }

        if($excel) {
            $other = [
                'title' => '佣金详情',
                'columns' => [
                    'name' => ['name'=>'姓名', 'type'=>'string'],
                ],
            ];

            foreach ($dueinData as $key => $value) {
                $other['columns'][$key] = ['name'=>$key];
            }

            $other['columns']['total'] = ['name'=>'合计'];

            $excelRecords = [];
            foreach ($users as $row) {
                if($row->UserDuein->count() > 0){
                    $row['name'] = $row['name'];
                    $tmp = [0,0];
                    foreach ($row->UserDuein as $key => $value) {
                        $a = UserDuein::calcCommission($dueinData[$value->date],$value->stay);
                        $row[$value->date] = $a.'/'.$value->stay;
                        $tmp[0] += $a;
                        $tmp[1] += $value->stay;
                    }
                    $row['total'] = $tmp[0].'/'.$tmp[1];
                    $excelRecords[] = $row;
                }
            }
            ExcelHelper::getDataExcel($excelRecords, $other);
        } else {
            $users->appends($queries->all());
        }
        $this->display('friends', ['friends'=>$users, 'queries'=>$queries ,'stayMoney' =>$stayList ,'cityList' =>$cityList, 'emonth' =>$emonth, 'tmonth' =>$tmonth]);
    }

}