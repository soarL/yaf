<?php
use models\Odd;
use models\User;
use models\OddMoney;
use models\Invest;
use models\Crtr;
use models\Sms;
use models\UserBank;
use models\AutoInvest;
use models\RechargeAgree;
use models\Question;
use models\UserVip;
use models\CustomService;
use models\UserVipLog;
use models\Lottery;
use models\Promotion;
use forms\RechargeFormOld as RechargeForm;
use forms\app\LoginForm;
use forms\app\RegisterForm;
use forms\CardnumAuthForm;
use forms\UpdateLoginpassForm;
use forms\WithdrawForm;
use forms\app\ForgetLoginpassForm;
use forms\app\UpdateBankForm;
use forms\app\AddBankForm;
use forms\app\OrderForm;
use forms\AutoInvestForm;
use forms\BidForm;
use forms\CrtrForm;
use forms\QuestionForm;
use forms\QuestionAnswerForm;
use forms\QuestionReplyForm;
use forms\TransferForm;
use forms\DelTransferForm;
use forms\app\SetEmailForm;
use forms\UserInfoForm;
use forms\OpenCustodyForm;
use traits\handles\ITFAuthHandle;
use helpers\NetworkHelper;
use helpers\StringHelper;
use Yaf\Registry;
use tools\Uploader;
use tools\Pager;
use tools\Banks;
use custody\Handler;

/**
 * ApiController
 * app接口
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class ApiController extends Controller {
    use ITFAuthHandle;

    public $checkSign = true;

    public function init() {
        parent::init();
        // header('content-type:application/json;charset=utf8');  
        // header('Access-Control-Allow-Methods:POST'); 
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
    }

    public $actions = [
        'index' => 'actions/IndexAction.php',
        'account' => 'actions/AccountAction.php',
        'crtrs' => 'actions/CrtrsAction.php',
        'crtr' => 'actions/CrtrAction.php',
        'odds' => 'actions/OddsAction.php',
        'rechargerecords' => 'actions/RechargeRecordsAction.php',
        'withdrawrecords' => 'actions/WithdrawRecordsAction.php',
        'odd' => 'actions/OddAction.php',
        'oddrm' => 'actions/OddRMAction.php',
        'oddtenders' => 'actions/OddTendersAction.php',
        'areas' => 'actions/AreasAction.php',
        'banks' => 'actions/BanksAction.php',
        'repayments' => 'actions/RepaymentsAction.php',
        'usertenders' => 'actions/UserTendersAction.php',
        'usercrtrs' => 'actions/UserCrtrsAction.php',
        'infos' => 'actions/InfosAction.php',
        'answers' => 'actions/AnswersAction.php',
        'useraccount' => 'actions/UserAccountAction.php',
        'buyrecords' => 'actions/BuyRecordsAction.php',
        'news' => 'actions/NewsAction.php',
        'spread' => 'actions/SpreadAction.php',
        'helplist' => 'actions/HelpListAction.php',
        'helptypes' => 'actions/HelpTypesAction.php',
        'uservip' => 'actions/UserVipAction.php',
        'pv' => 'actions/PVAction.php',
        'version' => 'actions/VersionAction.php',
        'oddbuy' => 'actions/OddBuyAction.php',
        'oddrepay' => 'actions/OddRepayAction.php',
        'crtrbuy' => 'actions/CrtrBuyAction.php',
        'moneylog' => 'actions/MoneyLogAction.php',
        'useinvestlottery' => 'actions/UseInvestLotteryAction.php',
        'investlotteries' => 'actions/InvestLotteriesAction.php',
        'repaymentdetail' => 'actions/RepaymentDetailAction.php',
        'extractmoney' => 'actions/ExtractMoneyAction.php',
        'userbankcard' => 'actions/UserBankCardAction.php',
        'repaymentodds' => 'actions/RepaymentOddsAction.php',
        'calculate' => 'actions/CalculateAction.php',
        'updatephone' => 'actions/UpdatePhoneAction.php',
        'cardunbind' => 'actions/CardUnbindAction.php',
        'cardbind' => 'actions/CardBindAction.php',
        'cardrefresh' => 'actions/CardRefreshAction.php',
        'cardlimit' => 'actions/CardLimitAction.php',
        'moneylotteries' => 'actions/MoneyLotteriesAction.php',
        'syncmoney' => 'actions/SyncMoneyAction.php',
    ];

    /**
     * 用户登录
     * 需要参数：
     *  username
     *  password
     * @return  mixed
     */
    public function loginAction() {
        $params = $this->getAllPost();
        $this->authenticate($params, ['username'=>'用户名', 'password'=>'密码']);

        $form = new LoginForm($params);
        if($form->login()) {
            $user = $form->getUser();

            $data = [];
            $data['userId'] = $user->userId;
            $data['userName'] = $user->username;
            $data['phone'] = $user->phone;
            $data['fundMoney'] = $user->fundMoney;
            $data['frozenMoney'] = $user->frozenMoney;

            $data['custodyId'] = $user->custody_id;
            $data['autoBidAuth'] = $user->auto_bid_auth;
            $data['autoCreditAuth'] = $user->auto_credit_auth;
            $data['custodyPwd'] = $user->is_custody_pwd;

            $data['integral'] = intval($user->integral/100);
            $data['name'] = $user->name;
            $data['cardnum'] = $user->cardnum;
            $data['cardstatus'] = $user->cardstatus;
            $data['email'] = $user->email;
            $data['emailstatus'] = $user->emailstatus;

            $userImage = $user->getPhoto();
            $data['userimg'] = $userImage;
            $data['sex'] = $user->sex;
            $data['city'] = $user->city;
            $data['maritalstatus'] = $user->maritalstatus;

            $data['userSecret'] = substr(md5($user->loginpass.$user->friendkey.'secret'), 8, 16);

            $rdata['status'] = 1;
            $rdata['msg'] = '登录成功';
            $rdata['data'] = $data;
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $form->posError();
            $this->backJson($rdata);
        }
    }

    /**
     * 获取用户信息
     * 需要参数：
     *  userId
     * @return  mixed
     */
    public function getUserInfoAction() {
        $params = $this->getAllPost();
        $this->authenticate($params, ['userId'=>'用户ID']);
        
        $this->pv('af');

        $user = $this->getUser();
        $userId = $user->userId;
        
        $data = [];
        $data['userId'] = $user->userId;
        $data['userName'] = $user->username;
        $data['phone'] = $user->phone;
        $data['fundMoney'] = $user->fundMoney;
        $data['frozenMoney'] = $user->frozenMoney;

        $data['custodyId'] = $user->custody_id;
        $data['autoBidAuth'] = $user->auto_bid_auth;
        $data['autoCreditAuth'] = $user->auto_credit_auth;
        $data['custodyPwd'] = $user->is_custody_pwd;

        $data['integral'] = intval($user->integral/100);
        $data['name'] = $user->name;
        $data['cardnum'] = $user->cardnum;
        $data['cardstatus'] = $user->cardstatus;

        $data['email'] = $user->email;
        $data['emailstatus'] = $user->emailstatus;

        $userImage = $user->getPhoto();
        $data['userimg'] = $userImage;
        $data['sex'] = $user->sex;
        $data['city'] = $user->city;
        $data['maritalstatus'] = $user->maritalstatus;

        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功';
        $rdata['data'] = $data;
        $this->backJson($rdata);
    }

    /**
     * 用户注册
     * 需要参数：
     *  username
     *  password
     *  phone
     * @return  mixed
     */
    public function registerAction() {
        $this->init();
        $params = $this->getAllPost();
        //$this->authenticate($params, [ 'password'=>'密码', 'phone'=>'手机号', 'smsCode'=>'验证码']);
        if(empty($params['username'])){
            $params['username'] = empty($params['phone'])?'':$params['phone'];
        }

        if(isset($params['channelCode'])) {
            $params['pm_key'] = 'APP-'.$params['channelCode'];
            $count = Promotion::where('channelCode', $params['pm_key'])->count();
            if($count==0) {
                $promotion = new Promotion();
                $promotion->channelCode = $params['pm_key'];
                $promotion->channelName = $params['pm_key'];
                $promotion->save();
            }
        }

        $form = new RegisterForm($params);
        
        $media = $this->getMedia();
        $form->setMedia($media);

        if($form->register()) {
            $rdata['status'] = 1;
            $rdata['msg'] = '注册成功';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $form->posError();
            $this->backJson($rdata);
        }
    }

    /**
     * 发送短信
     * 需要参数：
     *  username
     *  password
     *  phone
     * @return  mixed
     */
    public function smsAction() {
        $params = $this->getAllPost();
        $this->authenticate($params, ['msgType'=>'用户名', 'phone'=>'手机号']);

        $data = [];
        $data['userId'] = '';
        $data['phone'] = $params['phone'];
        $data['msgType'] = $params['msgType'];
        $data['code'] = Sms::generateCode(Sms::CODE_LENGTH);
        $data['params'] = [$data['code'],Sms::$msg[$data['msgType']],15];
        $result = Sms::send($data);

        if($result['status']==1) {
            $rdata['status'] = 1;
            $rdata['msg'] = '发送成功';
            $rdata['data']['code'] = $result['code'];
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $result['info'];
            $this->backJson($rdata);
        }
    }

    /**
     * 充值
     * @return mixed
     */
    public function bfRechargeAction() {
        $params = $this->getAllPost();
        $user = $this->getUser();

        if($user->custody_id=='') {
            $rdata['status'] = 0;
            $rdata['msg'] = '您还未进行实名认证！';
            $this->backJson($rdata);
        }

        $params['payType'] = 'baofoo';
        $params['payWay'] = 'SWIFT';

        $form = new RechargeForm($params);
        if($form->recharge()) {
            if($form->result['resp_code'] != '0000'){
                $rdata['status'] = -1;
                $rdata['msg'] = $form->result['resp_msg'];
                $this->backJson($rdata); 
            }else{
                $rdata['status'] = 1;
                $rdata['business_no'] = $form->result['business_no'];
                $this->backJson($rdata); 
            }
        } else {
            $rdata['status'] = -1;
            $rdata['msg'] = $form->posError();
            $this->backJson($rdata); 
        }
    } 

    /**
     * 宝付绑卡认证2
     * @return mixed
     */
    public function confirmBFRechargeAction() {
        $params = $this->getAllQuery();
        $this->authenticate($params, ['userId'=>'用户ID']);
        
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
                $rdata['msg'] = $form->result['resp_msg'];
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
                $rdata['msg'] = '充值成功';
                $this->backJson($rdata); 
            }
        } else {
            $rdata['status'] = -1;
            $rdata['msg'] = $form->posError();
            $this->backJson($rdata); 
        }
    } 
    /**
     * 用户充值
     * @return  mixed
     */
	public function rechargeAction() {
        $params = $this->getAllQuery();
        $this->authenticate($params, ['userId'=>'用户ID']);
        $user = $this->getUser();
        if($user->custody_id=='') {
            $rdata['status'] = 0;
            $rdata['msg'] = '未开通银行存管！';
            $this->backJson($rdata);
        }

        $baofoo = Registry::get('config')->get('baofoo')->get('open');
        if($baofoo){
            $this->display('recharge',['user'=>$user,'bank'=>$user->userbank]);
            exit;
        }

        $form = new RechargeForm($params);
        $form->setUser($user);
        $form->setMedia('app');
        $form->returnUrl = WEB_MAIN.'/go/info';
        if($form->recharge()) {
            $form->handler->form();
        } else {
            $this->displayBasic('info', ['status'=>0, 'msg'=>$form->posError()]);
        }
	}

    /**
     * 风险评估
     * @return [type] [description]
     */
    public function estimateAction() {
        $params = $this->getAllQuery();
        //$this->authenticate($params, ['userId'=>'用户ID']);
        $this->display('estimate');
    }

    /**
     * 风险评估分数
     * @return mixed
     */
    public function submitEstimateAction() {
        $params = $this->getAllPost();
        $user = $this->getUser();

        $user->estimateScore = $params['score'];
        $user->save();

        $rdata['status'] = 1;
        $rdata['info'] = $user->getEstimateResult();
        $this->backJson($rdata); 
    }

    /**
     * 提现
     * 需要参数：
     *  money: 提现金额
     *  userId: 用户ID
     *  
     * @return  mixed
     */
    public function withdrawAction() {
        $params = $this->getAllQuery();
        $this->authenticate($params, ['userId'=>'用户ID']);
        $user = $this->getUser();

        $form = new WithdrawForm($params);
        $form->setUser($user);
        $form->setMedia('app');
        $form->returnUrl = WEB_MAIN.'/go/info';
        
        if($form->withdraw()) {
            $form->handler->form();
        } else {
            $this->displayBasic('info', ['status'=>0, 'msg'=>$form->posError()]);
        }
    }

    /**
     * 获取提现手续费
     * 需要参数：
     *  money: 提现金额
     *  userId: 用户ID
     * @return  mixed
     */
    public function withdrawFeeAction() {
        $params = $this->getAllPost();
        $this->authenticate($params, ['userId'=>'用户ID', 'money'=>'金额']);
        $money = $params['money'];
        
        $user = $this->getUser();
        $lottery = $this->getPost('lottery', 0);
        if($lottery==1) {
            $lottery = true;
        } else {
            $lottery = false;
        }

        $rdata = [];
        if(is_numeric($money)) {
            $fee = $user->getWithdrawFee($money, $lottery);
            $rdata['status'] = 1;
            $rdata['msg'] = '提交成功，请耐心等待审核!';
            $rdata['data']['fee'] = $fee;
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = '提现金额错误!';
        }
        $this->backJson($rdata);
    }

    /**
     * 开通存管
     * 需要参数：
     *  userId: 用户ID
     *  bankNum: 银行卡号
     *  name: 姓名
     *  cardnum: 身份证号
     * @return  mixed
     */
    public function openCustodyAction() {
    	$params = $this->getAllQuery();
    	$this->authenticate($params, ['userId'=>'用户ID']);
    	$user = $this->getUser();
        $form = new OpenCustodyForm($params);
        $form->setUser($user);
        $form->setMedia('app');
        $form->returnUrl = WEB_MAIN . '/go/info';
        
    	if($form->open()) {
            $form->handler->form();
        } else {
            $this->displayBasic('info', ['status'=>0, 'msg'=>$form->posError()]);
        }
    }

    /**
     * 自动投标/自动债转签约
     * 需要参数：
     *  userId: 用户ID
     *  mode: 类型
     * @return  mixed
     */
    public function autoAuthAction() {
        $params = $this->getAllQuery();
        $this->authenticate($params, ['userId'=>'用户ID', 'mode'=>'类型']);
        $mode = $params['mode'];
        $user = $this->getUser();

        if($mode=='bid') {
            $data = [];
            $data['accountId'] = $user->custody_id;
            $data['orderId'] = Handler::SEQ_PL;
            $data['txAmount'] = 99999999;
            $data['totAmount'] = 99999999;
            $data['forgotPwdUrl'] = WEB_SAFE;
            $data['retUrl'] = WEB_MAIN.'/go/info';
            $data['notifyUrl'] = WEB_MAIN.'/custody/autoBidAuthNotify';
            $data['acqRes'] = '';

            $handler = new Handler('autoBidAuth', $data);
            $handler->form();
        } else if($mode=='credit') {
            $data = [];
            $data['accountId'] = $user->custody_id;
            $data['orderId'] = Handler::SEQ_PL;
            $data['forgotPwdUrl'] = WEB_SAFE;
            $data['retUrl'] = WEB_MAIN.'/go/info';
            $data['notifyUrl'] = WEB_MAIN.'/custody/autoCreditAuthNotify';
            $data['acqRes'] = '';

            $handler = new Handler('autoCreditInvestAuth', $data);
            $handler->form();
        } else {
            $this->displayBasic('info', ['status'=>0, 'msg'=>'mode值错误！']);
        }
    }

    /**
     * 获取最大可投金额
     * 需要参数:
     *  oddNumber:标的号
     *  userId:用户ID
     */
    public function getMaxInvestAction() {
        $params = $this->getAllQuery();
        $this->authenticate($params, ['oddNumber'=>'标的号', 'userId'=>'用户ID']);
        $oddNumber = $params['oddNumber'];
        
        $user = $this->getUser();

        $odd = Odd::where('oddNumber', $oddNumber)
            ->where('progress', 'start')
            ->first(['oddNumber', 'oddType', 'appointUserId', 'oddStyle', 'investType', 'progress']);

        if(!$odd) {
            $rdata['status'] = 0;
            $rdata['msg'] = '标的不存在！';
            $this->backJson($rdata);
        }

        $money = $odd->getMaxInvest($user);
        
        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功';
        $rdata['data']['money'] = $money;
        $rdata['data']['oddMoneyLast'] = $odd->getRemain();
        $this->backJson($rdata);
    }

    /**
     * 投标
     * 需要参数:
     *  money:投标金额
     *  oddNumber:标的号
     *  userId:用户ID
     */
    public function bidAction() {
        $params = $this->getAllQuery();
        $this->authenticate($params, ['oddNumber'=>'标的号', 'userId'=>'用户ID', 'money'=>'投标金额', 'paypass'=>'支付密码']);
        
        $user = $this->getUser();

        $form = new BidForm($params);
        $form->setUser($user);
        $form->setMedia('app');
        $form->returnUrl = WEB_MAIN . '/go/info';

        $rdata = [];
        if($form->bid()) {
            $form->handler->form();
        } else {
            $this->displayBasic('info', ['status'=>0, 'msg'=>$form->posError()]);
        }
    }

    /**
     * 获取用户针对某个债权的最大可购买金额
     * @return mixed
     */
    public function getMaxBuyAction() {
        $params = $this->getAllQuery();
        $this->authenticate($params, ['id'=>'转让号', 'userId'=>'用户ID']);
        $id = $params['id'];
        
        $user = $this->getUser();

        $rdata = [];
        $crtr = Crtr::where('id', $id)->where('progress', 'start')->first(['id', 'progress']);
        if(!$crtr) {
            $rdata['status'] = 0;
            $rdata['msg'] = '债权不存在或已售出！';
            $this->backJson($rdata);
        }

        $userMoney = $user->fundMoney;
        $remain = $crtr->getRemain();

        if($userMoney>=$crtr->oddMoneyLast) {
            $rdata['status'] = 1;
            $rdata['msg'] = '获取成功';
            $rdata['data']['money'] = $remain;
            $rdata['data']['moneyLast'] = $remain;
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 1;
            $rdata['msg'] = '获取成功';
            $rdata['data']['money'] = $userMoney;
            $rdata['data']['moneyLast'] = $remain;
            $this->backJson($rdata);
        }
    }

    /**
     * 债权转让
     * @return mixed
     */
    public function buyAction() {
        $params = $this->getAllQuery();
        $this->authenticate($params, ['id'=>'转让号', 'userId'=>'用户ID', 'money'=>'投标金额', 'paypass'=>'支付密码']);
        
        $user = $this->getUser();

        if(!$user) {
            $this->displayBasic('info', ['status'=>0, 'msg'=>'用户不存在！']);
        }
        
        $form = new CrtrForm($params);
        $form->setUser($user);
        $form->setMedia('app');

        $rdata = [];
        if($form->buy()) {
            $form->handler->form();
        } else {
            $this->displayBasic('info', ['status'=>0, 'msg'=>$form->posError()]);
        }
    }

    /**
     * 找回登录密码
     * @return mixed
     */
    public function forgetLoginpassAction() {
        $params = $this->getAllPost();
        $this->authenticate($params);

        $form = new ForgetLoginpassForm($params);
        if($form->update()) {
            $rdata['status'] = 1;
            $rdata['msg'] = '登录密码找回成功！';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $form->posError();
            $this->backJson($rdata);
        }
    }

    /**
     * 修改登录密码
     * @return mixed
     */
    public function updateLoginpassAction() {
        $params = $this->getAllPost();
        $this->authenticate($params, ['oldpass'=>'旧密码', 'userId'=>'用户ID', 'loginpass'=>'新密码', 'loginpassSure'=>'确认新密码']);
        $rdata = [];
        $form = new UpdateLoginpassForm($params);
        if($form->update()) {
            $rdata['status'] = 1;
            $rdata['msg'] = '登录密码修改成功！';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $form->posError();
            $this->backJson($rdata);
        }
    }

    /**
     * 设置存管密码
     * @return mixed
     */
    public function setCustodypassAction() {
        $params = $this->getAllQuery();
        $this->authenticate($params, ['userId'=>'用户ID']);
        $user = $this->getUser();
        if($user->custody_id=='') {
            $this->displayBasic('info', ['status'=>0, 'msg'=>'您还未开通银行存管！']);
        }

        $data = [];
        $data['accountId'] = $user->custody_id;
        $data['idType'] = '01';
        $data['idNo'] = $user->cardnum;
        $data['name'] = $user->name;
        $data['mobile'] = $user->phone;
        $data['retUrl'] = WEB_MAIN.'/go/info';
        $data['notifyUrl'] = WEB_MAIN.'/custody/passwdNotify';
        $data['acqRes'] = '';
        $data['channel'] = Handler::M_APP;

        $handler = new Handler('passwordSet', $data);
        $handler->form();
    }

    /**
     * 修改存管密码
     * @return mixed
     */
    public function updateCustodypassAction() {
        $params = $this->getAllQuery();
        $this->authenticate($params, ['userId'=>'用户ID']);
        $user = $this->getUser();
        if($user->custody_id=='') {
            $this->displayBasic('info', ['status'=>0, 'msg'=>'您还未开通银行存管！']);
        }

        $data = [];
        $data['accountId'] = $user->custody_id;
        $data['idType'] = '01';
        $data['idNo'] = $user->cardnum;
        $data['name'] = $user->name;
        $data['mobile'] = $user->phone;
        $data['retUrl'] = WEB_MAIN.'/go/info';
        $data['notifyUrl'] = WEB_MAIN.'/custody/rePasswdNotify';
        $data['acqRes'] = '';
        $data['channel'] = Handler::M_APP;

        $handler = new Handler('passwordReset', $data);
        $handler->form();
    }

    /**
     * 排队信息
     * @return mixed
     */
    public function queueInfoAction() {
        $params = $this->getAllQuery();
        $this->authenticate($params, ['userId'=>'用户ID']);

        $user = $this->getUser();
        $userId = $user->userId;

        $autoInvest = AutoInvest::where('userId', $userId)->first();

        $position = '无效队列';
        if($autoInvest) {
            $position = $autoInvest->investable()?'有效队列':'无效队列';
        } else {
            $autoInvest = new AutoInvest();
        }

        $preInfo = ['allMoney'=>0, 'invalidMoney'=>0, 'validMoney'=>0, 'validNum'=>0, 'invalidNum'=>0, 'allNum'=>0];
        
        // $aftInfo = ['allMoney'=>0, 'invalidMoney'=>0, 'validMoney'=>0, 'validNum'=>0, 'invalidNum'=>0, 'allNum'=>0];
        
        $location = '--';
        $inQueue = '--';
        if($autoInvest->queue) {
            $preInfo = $autoInvest->queue->getQueuesInfo('pre');
            
            // $aftInfo = $autoInvest->queue->getQueuesInfo('aft');
            
            $location = $autoInvest->queue->location;
        } else {
            $preInfo['monthNum'] = [1=>0, 2=>0, 3=>0, 6=>0, 12=>0, 24=>0];
            $preInfo['monthMoney'] = [1=>0, 2=>0, 3=>0, 6=>0, 12=>0, 24=>0];
        }

        $queue = new Queue();
        $allInfo = $queue->getQueuesInfo('all');

        $rdata = [];
        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data']['preInfo'] = $preInfo;
        $rdata['data']['allInfo'] = $allInfo;
        // $rdata['data']['aftInfo'] = $aftInfo;
        $rdata['data']['location'] = $location;
        $rdata['data']['inQueue'] = $position;

        $this->pv('am');

        $autoInvest = AutoInvest::where('userId', $userId)->first();
        $lotteryID = 0;
        $lotteryName = '';
        if(!$autoInvest) {
            $autoInvest = new AutoInvest();
        } else {
            $lotteryID = $autoInvest->lottery_id;
            $lottery = Lottery::where('id', $lotteryID)->first();
            if($lottery) {
                $lotteryName = $lottery->getName();
            }
        }
        $status = AutoInvest::$status;

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
        $successMoney = $autoInvest->successMoney?number_format($autoInvest->successMoney,2):'0.00';


        $autoInfo = [];
        $autoInfo['dueTotal'] = $total;
        $autoInfo['dueSuccessMoney'] = $successMoney;
        $autoInfo['dueStatus'] = $status[$autoInvest->status]['name'];
        $autoInfo['autostatus'] = $autoInvest->autostatus;
        $autoInfo['investMoneyUper'] = sprintf("%.0f", $autoInvest->investMoneyUper);
        $autoInfo['investMoneyLower'] = sprintf("%.0f", $autoInvest->investMoneyLower);
        $autoInfo['investEgisMoney'] = sprintf("%.0f", $autoInvest->investEgisMoney);
        $autoInfo['moneyType'] = $autoInvest->moneyType;
        $autoInfo['staystatus'] = $autoInvest->staystatus;
        $autoInfo['mode'] = $autoInvest->mode==null?0:$autoInvest->mode;
        $autoInfo['lotteryID'] = $lotteryID;
        $autoInfo['lotteryName'] = $lotteryName;
        
        $autoInfo['types'] = $autoInvest->getTypeIDList();

        $rdata['data']['fundMoney'] = $user->fundMoney;
        $rdata['data']['autoInvest'] = $autoInfo;

        $list = [];
        foreach (AutoInvest::$types as $key => $item) {
            $item['id'] = $key;
            $list[] = $item;
        }
        $rdata['data']['types'] = $list;

        $this->backJson($rdata);
    }

    /**
     * 自动投标设置数据获取
     * @return mixed
     */
    // public function autoInfoAction() {
    //     $params = $this->getAllQuery();
    //     $this->authenticate($params, ['userId'=>'用户ID']);

    //     $user = $this->getUser();
    //     $userId = $user->userId;

    //     $this->pv('am');

    //     $autoInvest = AutoInvest::where('userId', $userId)->first();
    //     $lotteryID = 0;
    //     $lotteryName = '';
    //     if(!$autoInvest) {
    //         $autoInvest = new AutoInvest();
    //     } else {
    //         $lotteryID = $autoInvest->lottery_id;
    //         $lottery = Lottery::where('id', $lotteryID)->first();
    //         if($lottery) {
    //             $lotteryName = $lottery->getName();
    //         }
    //     }

    //     $autoInfo = [];
    //     $autoInfo['autostatus'] = $autoInvest->autostatus;
    //     $autoInfo['investMoneyUper'] = sprintf("%.0f", $autoInvest->investMoneyUper);
    //     $autoInfo['investMoneyLower'] = sprintf("%.0f", $autoInvest->investMoneyLower);
    //     $autoInfo['investEgisMoney'] = sprintf("%.0f", $autoInvest->investEgisMoney);
    //     $autoInfo['moneyType'] = $autoInvest->moneyType;
    //     $autoInfo['staystatus'] = $autoInvest->staystatus;
    //     $autoInfo['mode'] = $autoInvest->mode==null?0:$autoInvest->mode;
    //     $autoInfo['lotteryID'] = $lotteryID;
    //     $autoInfo['lotteryName'] = $lotteryName;
        
    //     $autoInfo['types'] = $autoInvest->getTypeIDList();

    //     $rdata = [];
    //     $rdata['status'] = 1;
    //     $rdata['msg'] = '获取成功！';
    //     $rdata['data']['fundMoney'] = $user->fundMoney;
    //     $rdata['data']['autoInvest'] = $autoInfo;

    //     $list = [];
    //     foreach (AutoInvest::$types as $key => $item) {
    //         $item['id'] = $key;
    //         $list[] = $item;
    //     }
    //     $rdata['data']['types'] = $list;
    //     $this->backJson($rdata);
    // }

    /**
     * 自动投标设置
     * @return mixed
     */
    public function autoSetAction() {
        $params = $this->getAllPost();
        $this->authenticate($params, ['userId'=>'用户ID']);

        $user = $this->getUser();
        $userId = $user->userId;
        
        $this->pv('ae');

        $types = $this->getPost('types', '');
        $types = explode(',', $types);
        
        $params['types'] = $types;
        
        $form = new AutoInvestForm($params);

        $user = User::find($params['userId']);
        $form->setUser($user);

        $rdata = [];
        if($form->update()) {
            $rdata['status'] = 1;
            $rdata['msg'] = '设置成功！';
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $form->posError();
        }
        $this->backJson($rdata);
    }

    /**
     * 问答-提问
     * @return mixed
     */
    public function askAction() {
        $params = $this->getAllPost();
        $this->authenticate($params, ['userId'=>'用户ID']);

        $user = $this->getUser();
        $userId = $user->userId;
        
        $this->pv('ac');

        $form  = new QuestionForm($params);
        $form->setUser($user);

        $rdata = [];
        if($form->ask()) {
            $rdata['status'] = 1;
            if($form->question['status']==Question::STATUS_ACTIVE) {
                $rdata['msg'] = '提问成功！';
            } else {
                $rdata['msg'] = '提问成功，等待审核！';
            }
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $form->posError();
        }
        $this->backJson($rdata);
    }

    /**
     * 问答-回答
     * @return mixed
     */
    public function answerAction() {
        $params = $this->getAllPost();
        $this->authenticate($params, ['userId'=>'用户ID']);

        $user = $this->getUser();
        $userId = $user->userId;

        $form  = new QuestionAnswerForm($params);
        $form->setUser($user);

        if($form->answer()) {
            $rdata['status'] = 1;
            if($form->answer['status']==1) {
                $rdata['msg'] = '回答成功！';
            } else {
                $rdata['msg'] = '回答成功，等待审核！';
            }
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $form->posError();
        }
        $this->backJson($rdata);
    }

    /**
     * 问答-回复
     * @return mixed
     */
    public function replyAction() {
        $params = $this->getAllPost();
        $this->authenticate($params, ['userId'=>'用户ID']);

        $user = $this->getUser();
        $userId = $user->userId;

        $form  = new QuestionReplyForm($params);
        $form->setUser($user);
        $rdata = [];
        if($form->reply()) {
            $rdata['msg'] = '回复成功！';
            $rdata['status'] = 1;
        } else {
            $rdata['msg'] = $form->posError();
            $rdata['status'] = 0;
        }
        $this->backJson($rdata);
    }

    /**
     * 借款
     * @return mixed
     */
    public function orderAction() {
        $params = $this->getAllPost();
        $this->authenticate($params);
        $form = new OrderForm($params);
        $rdata = [];
        if($form->save()) {
            $rdata['msg'] = '恭喜您提交成功，我们的客服尽快联系您！';
            $rdata['status'] = 1;
        } else {
            $rdata['msg'] = $form->posError();
            $rdata['status'] = 0;
        }
        $this->backJson($rdata);
    }

    /**
     * 债权转让
     * 需要参数：
     *  oddMoneyId: oddMoneyId
     *  userId: 用户ID
     * @return  mixed
     */
    public function transferAction() {
        $params = $this->getAllPost();
        $this->authenticate($params, ['userId'=>'用户ID']);

        $user = $this->getUser();
        $userId = $user->userId;
        
        $this->pv('ar');

        $rdata = [];
        $form = new TransferForm($params);
        $form->setUser($user);
        $media = $this->getMedia();
        $form->setMedia($media);
        if($form->transfer()) {
            $rdata['status'] = 1;
            $rdata['msg'] = '转让申请成功，等待其他用户认购！';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $form->posError();
            $this->backJson($rdata);
        }
    }

    /**
     * 债权转让服务费
     * 需要参数：
     *  oddMoneyId: oddMoneyId
     *  userId: 用户ID
     * @return  mixed
     */
    public function crtrSMAction() {
        $params = $this->getAllPost();
        $this->authenticate($params, ['oddMoneyId'=>'投资ID']);

        $oddMoney = OddMoney::where('id', $params['oddMoneyId'])->first();
        $sm = $oddMoney->getCrtrSM();
        $remain = $oddMoney->remain;

        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data'] = ['serviceMoney'=>$sm, 'remain'=>$remain];
        $this->backJson($rdata);
    }

    /**
     * 撤销债权转让
     * @return mixed
     */
    public function delTransferAction() {
        $params = $this->getAllPost();
        $this->authenticate($params, ['userId'=>'用户ID']);

        $user = $this->getUser();
        $userId = $user->userId;
        
        $this->pv('ad');

        $rdata = [];
        
        $form = new DelTransferForm($params);
        $form->setUser($user);
        if($form->delete()) {
            $rdata['status'] = 1;
            $rdata['msg'] = '撤销转让成功！';
        } else {
            $rdata['msg'] = $form->posError();
            $rdata['status'] = 0;
        }
        $this->backJson($rdata);
    }

    /**
     * 设置邮箱
     * @return mixed
     */
    public function setEmailAction() {
        $params = $this->getAllPost();
        $this->authenticate($params, ['userId'=>'用户ID']);

        $user = $this->getUser();
        $userId = $user->userId;

        $rdata = [];
        if($user->emailstatus=='y') {
            $rdata['status'] = 0;
            $rdata['msg'] = '您已设置常用邮箱，您可修改常用邮箱！';
            $this->backJson($rdata);
        }

        $form = new SetEmailForm($params);
        $form->setUser($user);
        if($form->send()) {
            $rdata['status'] = 1;
            $rdata['msg'] = '邮件发送成功，请登录邮箱进行验证！';
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $form->posError();
        }
        $this->backJson($rdata);
    }

    /**
     * VIP申请
     * @return mixed
     */
    public function vipApplyAction() {
        $params = $this->getAllPost();
        $this->authenticate($params, ['userId'=>'用户ID']);

        $user = $this->getUser();
        $userId = $user->userId;

        $rdata = [];

        $code = $this->getPost('time', 0);
        $customService = $this->getPost('customService', 0);
        $result = UserVip::getTMByCode($code);
        $rdata = [];
        if(!$result) {
            $rdata['status'] = 0;
            $rdata['msg'] = '请选择开通时长！';
            $this->backJson($rdata);
        }
        if(!CustomService::checkIsCustomService($customService)) {
            $rdata['status'] = 0;
            $rdata['msg'] = '客服不存在！';
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
            $rdata['status'] = 1;
            $rdata['msg'] = '开通成功！';
            $this->backJson($rdata);
        }
        $rdata['status'] = 1;
        $rdata['msg'] = '提交成功，请耐心等待客服审核！';
        $this->backJson($rdata);
    }

    /**
     * 设置、修改基本资料
     */
    public function setUserInfoAction() {
        $params = $this->getAllPost();

        $this->authenticate($params, ['userId'=>'用户ID']);

        $user = $this->getUser();
        $userId = $user->userId;

        $rdata = [];

        $form = new UserInfoForm($params);
        $form->setUser($user);
        if($form->update()) {
            $rdata['status'] = 1;
            $rdata['msg'] = '更新成功！';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $form->posError();
            $this->backJson($rdata);
        } 
    }

    /**
     * 设置、修改头像
     */
    public function setUserPhotoAction() {
        $params = $this->getAllPost();
        $this->authenticate($params, ['userId'=>'用户ID']);

        $user = $this->getUser();
        $userId = $user->userId;

        $rdata = [];

        $imageName = '';
        $name = 'photo';
        if(isset($_FILES[$name])&&$_FILES[$name]['size']>0) {
            $path = dirname(APP_PATH) . '/app_user/public/uploads/images/';
            $uploader = new Uploader();
            $uploader->set('path', $path);
            $uploader->set('maxsize', 200000);
            $uploader->set('allowtype', ['gif', 'png', 'jpg', 'jpeg']);
            $uploader->set('israndname', true);
            if($uploader->upload($name)) {
                $imageName = $uploader->getFileName();

                $user->userimg = $imageName;
                $user->save();

                $rdata['status'] = 1;
                $rdata['msg'] = '更新成功！';
                $rdata['data']['photo'] = WEB_USER.'/uploads/images/'.$imageName;
                $this->backJson($rdata);
            } else {
                $rdata['status'] = 0;
                $rdata['msg'] = $uploader->getErrorMsg();
                $this->backJson($rdata);
            }
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = '图片未上传';
            $this->backJson($rdata);
        }
    }

        /**
     * 推荐好友收益列表
     */
    public function getFriendMoneyAction() {
        $params = $this->getAllQuery();
        $this->authenticate($params, ['userId'=>'用户ID']);
        $tuijian = $this->getUser();

        $userId = $params['userId'];
        $page = $params['page'];
        $pageSize = $params['pageSize'];
        $skip = ($page-1)*$pageSize;
        
        $builder = User::whereRaw('1=1');
        $builder->where('tuijian', $tuijian->phone);
        
        $count = $builder->count();

        $users = $builder->with('invests')->skip($skip)->limit($pageSize)->get();

        $records = [];
        foreach ($users as $key => $user) {
            $record = [];
            $record['mvalue'] = $user->getStayMoney();
            $record['name'] = StringHelper::getHideUsername($user->name);
            $record['addtime'] = $user->addtime;
            $record['phone'] = _hide_phone($user->phone);
            $records[] = $record;
        }

        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data']['friendMoney'] = $records;
        $rdata['data']['pageNow'] = $page;
        $rdata['data']['countNum'] = $count;
        $this->backJson($rdata);
    }
}

