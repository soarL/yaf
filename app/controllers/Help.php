<?php
use models\Order;
use forms\OrderForm;
use tools\Areas;

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
use forms\RechargeForm;
use forms\app\LoginForm;
use forms\app\RegisterForm;
use forms\CardnumAuthForm;
use forms\UpdateLoginpassForm;
use forms\WithdrawForm;
use forms\app\ForgetLoginpassForm;
use forms\app\UpdateBankForm;
use forms\app\AddBankForm;
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

class HelpController extends Controller {
	public $menu = 'help';
	use ITFAuthHandle;

	public function indexAction() {
		$this->display('index');
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
}
