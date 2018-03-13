<?php
namespace traits;

use helpers\StringHelper;
use Yaf\Registry;
use tools\Pager;
use models\Sms;
use models\Email;
use models\User;
use validators\PhoneNumberValidator;
use validators\EmailValidator;
use validators\CaptchaValidator;
use tools\Banks;
use tools\Areas;
use tools\Captcha;
use forms\CalculateForm;
use forms\RegisterSimpleForm;
use forms\LoginForm;
use forms\RegisterLoanerForm;
use forms\RegisterLoanCompanyForm;



trait CommonActions {

    public function loginAction() {
        $params = $this->getAllPost();
        $form = new LoginForm($params);
        $rdata = [];
        if($form->login()) {
            $rdata['status'] = 1;
            $rdata['info'] = '登录成功!';
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = $form->posError();
        }
        $this->backJson($rdata);
    }

    public function sendSmsAction() {
        $phone = $this->getRequest()->getPost('phone', '');
        $msgType = $this->getRequest()->getPost('msgType', '');
        $captcha = $this->getRequest()->getPost('captcha', '');
        $rdata = [];
        if($phone=='') {
            $rdata['status'] = 0;
            $rdata['info'] = '手机号为空！';
            $this->backJson($rdata);
        }
        if(!PhoneNumberValidator::isPhone($phone)) {
            $rdata['status'] = 0;
            $rdata['info'] = '手机号错误！';
            $this->backJson($rdata);
        }
        if($captcha=='') {
            $rdata['status'] = 0;
            $rdata['info'] = '验证码为空！';
            $this->backJson($rdata);
        }
        $validator = new CaptchaValidator($captcha);
        if(!$validator->validate()) {
            $rdata['status'] = 0;
            $rdata['info'] = '验证码错误！';
            $this->backJson($rdata);
        }
        if($msgType=='') {
            $rdata['status'] = 0;
            $rdata['info'] = '短信类型为空！';
            $this->backJson($rdata);
        }
        
        $user = Registry::get('session')->get('user');
        $userId = '';
        if($user) {
            $userId = $user['userId'];
        }
        $data = [];
        $data['userId'] = $userId;
        $data['phone'] = $phone;
        $data['msgType'] = $msgType;
        $data['code'] = Sms::generateCode(Sms::CODE_LENGTH);
        $data['params'] = [$data['code'],Sms::$msg[$data['msgType']],15];
        $result = Sms::send($data);
        if($result['status']==1) {
            $rdata['status'] = 1;
            $rdata['info'] = '发送成功！';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = $result['info'];
            $this->backJson($rdata);
        }
    }

	/**
	 * 发送手机短信
	 * @return mixed
	 */
	public function ajaxSendSmsAction() {
		if($this->getRequest()->isXmlHttpRequest()) {
			$phone = $this->getRequest()->getPost('phone', '');
			$msgType = $this->getRequest()->getPost('msgType', '');
			$rdata = [];
			if($phone=='') {
				$rdata['status'] = 0;
				$rdata['info'] = '手机号为空！';
				$this->backJson($rdata);
			}
			if(!PhoneNumberValidator::isPhone($phone)) {
				$rdata['status'] = 0;
				$rdata['info'] = '手机号错误！';
				$this->backJson($rdata);
			}
			if($msgType=='') {
				$rdata['status'] = 0;
				$rdata['info'] = '短信类型为空！';
				$this->backJson($rdata);
			}
			
			$user = Registry::get('session')->get('user');
			$userId = '';
			if($user) {
				$userId = $user['userId'];
			}
			$data = [];
			$data['userId'] = $userId;
			$data['phone'] = $phone;
			$data['msgType'] = $msgType;
            $data['code'] = Sms::generateCode(Sms::CODE_LENGTH);
            $data['params'] = [$data['code'],Sms::$msg[$data['msgType']],15];
			$result = Sms::send($data);
			if($result['status']==1) {
				$rdata['status'] = 1;
				$rdata['info'] = '发送成功！';
				$this->backJson($rdata);
			} else {
				$rdata['status'] = 0;
				$rdata['info'] = $result['info'];
				$this->backJson($rdata);
			}
		}
	}

	/**
	 * 检查手机验证码
	 * @return mixed
	 */
	public function ajaxCheckSmsAction() {
		if($this->getRequest()->isXmlHttpRequest()) {
			$code = $this->getRequest()->getPost('code', '');
			$msgType = $this->getRequest()->getPost('msgType', '');
			$phone = $this->getRequest()->getPost('phone', '');
			$rdata = [];
			if($phone=='') {
				$rdata['status'] = 0;
				$rdata['info'] = '手机号不能为空！';
				$this->backJson($rdata);
			}
			if(!PhoneNumberValidator::isPhone($phone)) {
				$rdata['status'] = 0;
				$rdata['info'] = '手机号错误！';
				$this->backJson($rdata);
			}
			if($code=='') {
				$rdata['status'] = 0;
				$rdata['info'] = '短信验证码不能为空！';
				$this->backJson($rdata);
			}
			if($msgType=='') {
				$rdata['status'] = 0;
				$rdata['info'] = '短信类型不能为空！';
				$this->backJson($rdata);
			}

			$result = Sms::checkCode($phone, $code, $msgType);
			if($result['status']==1) {
				$rdata['status'] = 1;
				$rdata['info'] = '验证成功！';
				$this->backJson($rdata);
			} else {
				$rdata['status'] = 0;
				$rdata['info'] = $result['info'];
				$this->backJson($rdata);
			}
		}
	}

	/**
	 * 发送邮件
	 * @return mixed
	 */
	public function ajaxSendEmailAction() {
		if($this->getRequest()->isXmlHttpRequest()) {
			$email = $this->getRequest()->getPost('email', '');
			$type = $this->getRequest()->getPost('type', '');
			$rdata = [];
			if($email=='') {
				$rdata['status'] = 0;
				$rdata['info'] = '邮箱不能为空！';
				$this->backJson($rdata);
			}
			if(!EmailValidator::isEmail($email)) {
				$rdata['status'] = 0;
				$rdata['info'] = '邮箱错误！';
				$this->backJson($rdata);
			}
			if($type=='') {
				$rdata['status'] = 0;
				$rdata['info'] = '类型不能为空！';
				$this->backJson($rdata);
			}

			$result = Email::send(['type'=>'checkUpdateEmail', 'email'=>$email]);
			if($result['status']==1) {
				$rdata['status'] = 1;
				$rdata['info'] = '发送成功！';
				$this->backJson($rdata);
			} else {
				$rdata['status'] = 0;
				$rdata['info'] = $result['info'];
				$this->backJson($rdata);
			}
		}
	}

	/**
	 * 获取银行列表
	 * @return mixed
	 */
	public function banksAction() {
        if($this->getRequest()->isXmlHttpRequest()) {
        	$banks = Banks::getBanks();
            $rdata = [];
            $rdata['status'] = 1;
            $rdata['banks'] = $banks;
            $this->backJson($rdata);
        }
    }

    /**
	 * 借贷款利息计算
	 * @return mixed
	 */
    public function calculateAction() {
        if($this->getRequest()->isXmlHttpRequest()) {
        	$params = $this->getRequest()->getPost();
        	$form = new CalculateForm($params);
        	$rdata = [];
        	if($form->calculate()) {
        		$rdata['status'] = 1;
        		$rdata['data']['result'] = $form->result;
        	} else {
        		$rdata['status'] = 0;
        		$rdata['info'] = $form->posError();
        	}
        	$this->backJson($rdata);
        }
    }

    /**
	 * 获取地区列表
	 * @return mixed
	 */
    public function areasAction() {
        $params = $this->getRequest()->getPost();
        $provinceId = isset($params['provinceId'])?$params['provinceId']:0;
        $cityId = isset($params['cityId'])?$params['cityId']:0;
        $rdata = [];
        if($provinceId<0||$cityId<0) {
            $rdata['status'] = 0;
            $this->backJson($rdata);
        }
        if($provinceId==0) {
            $rdata['status'] = 1;
            $rdata['provinces'] = Areas::getProvinces();
        } else {
            if($cityId==0) {
                $rdata['status'] = 1;
                $rdata['citys'] = Areas::getCitys($provinceId);
            } else {
                $rdata['status'] = 1;
                $rdata['areas'] = Areas::getAreas($provinceId, $cityId);
            }
        }
        $this->backJson($rdata);
    }

    /**
     * 验证码图片
     * @return void
     */
    public function captchaAction() {
        Captcha::set();
    }

    /**
     * 检查验证码是否正确
     * @return mixed
     */
    public function ajaxCheckCaptchaAction() {
        $captcha = $this->getRequest()->getPost('captcha');
        $rdata = [];
        if(Captcha::check($captcha)) {
            $rdata['status'] = 1;
            $rdata['info'] = '验证码正确!';
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '验证码错误!';
        }
        $this->backJson($rdata);
    }

    /**
     * 检查是否登录
     * @return mixed
     */
    public function isLoginAction() {
        if($this->getUser()) {
            $this->backJson(['status'=>1]);
        } else {
            $this->backJson(['status'=>0]);
        }
    }

    /**
     * 发送手机验证码
     * @return mixed
     */
    public function smsAction() {
        $phone = $this->getPost('phone', '');
        $msgType = $this->getPost('msgType', '');
        $rdata = [];
        if($phone=='') {
            $rdata['status'] = 0;
            $rdata['info'] = '手机号为空！';
            $this->backJson($rdata);
        }
        if(!PhoneNumberValidator::isPhone($phone)) {
            $rdata['status'] = 0;
            $rdata['info'] = '手机号错误！';
            $this->backJson($rdata);
        }
        if($msgType=='') {
            $rdata['status'] = 0;
            $rdata['info'] = '短信类型为空！';
            $this->backJson($rdata);
        }

        $user = Registry::get('session')->get('user');
        $userId = '';
        if($user) {
            $userId = $user['userId'];
        }
        $data = [];
        $data['userId'] = $userId;
        $data['phone'] = $phone;
        $data['msgType'] = $msgType;
        $data['code'] = Sms::generateCode(Sms::CODE_LENGTH);
        $data['params'] = [$data['code'],Sms::$msg[$data['msgType']],15];
        $result = Sms::send($data);
        if($result['status']==1) {
            $rdata['status'] = 1;
            $rdata['info'] = '发送成功！';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = $result['info'];
            $this->backJson($rdata);
        }
    }

     /**
     * 借款注册接口
     */
    public function registerLoanCompanyAction() {
        $params = $this->getAllPost();
        $form = new RegisterLoanCompanyForm($params);
        if($form->register()) {
            //$this->redirect('/page/logins/loan.html');
            $this->backJson([
                'status' => 1,
                'info' => '注册成功！',
                'url' => '/page/logins/loan.html'
            ]);
        } else {
            $this->backJson([
                'status' => 0,
                'info' => $form->posError()
            ]);
        }
    }

     /**
     * 借款注册接口
     */
    public function registerLoanerAction() {
        $params = $this->getAllPost();
        $form = new RegisterLoanerForm($params);
        if($form->register()) {
            //$this->redirect('/page/logins/loan.html');
            $this->backJson([
                'status' => 1,
                'info' => '注册成功！',
                'url' => '/page/logins/loan.html'
            ]);
        } else {
            $this->backJson([
                'status' => 0,
                'info' => $form->posError()
            ]);
        }
    }

     /**
     * 通用注册接口
     */
    public function registerAction() {
        $params = $this->getAllPost();
        $form = new RegisterSimpleForm($params);
        if($form->register()) {
            $this->backJson([
                'status' => 1,
                'info' => '注册成功！',
                'userId' => $form->user->userId,
            ]);
        } else {
            $this->backJson([
                'status' => 0,
                'info' => $form->posError(),
                'userId' => isset($form->user)?$form->user->userId:'',
            ]);
        }
    }

    public function getSpreadUsersAction() {
        $code = $this->getPost('code', '');

        $user = User::leftjoin('user_spread_code','user_spread_code.userId','=','system_userinfo.userId')->where('user_spread_code.spreadCode',$code)->first();

        if($user) {
            $this->backJson([
                'status' => 1,
                'info' => '获取成功',
                'data' => ['spreadUser'=>$user->username]
            ]);
        } else {
            $this->backJson([
                'status' => 0,
                'info' => '获取失败',
                'data' => ['spreadUser'=>'']
            ]);
        }
    }
    
    public function getSpreadUserAction() {
        $code = $this->getPost('code', '');

        $user = User::leftjoin('user_spread_code','user_spread_code.userId','=','system_userinfo.userId')->where('user_spread_code.spreadCode',$code)->first();
        if($user) {
            $this->backJson([
                'status' => 1,
                'info' => '获取成功',
                'data' => ['spreadUser'=>$user->username]
            ]);
        } else {
            $this->backJson([
                'status' => 0,
                'info' => '获取失败',
                'data' => ['spreadUser'=>'']
            ]);
        }
    }
}
