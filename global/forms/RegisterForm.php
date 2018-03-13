<?php
namespace forms;
use models\User;
use models\Sms;
use tools\Log;
class RegisterForm extends \Form {
    public $user;

    public function defaults() {
        return ['spreadUser'=>'', 'pm_key'=>''];
    }

	public function rules() {
		return [
			[['phone', 'password', 'smsCode'], 'required'],
			['phone', 'validatePhone'],
			['password','validatePassword'],
			['smsCode', 'validateSmsCode'],
		];
	}

	public function labels() {
		return [
        	'phone' => '手机号码',
        	'password' => '登录密码',
        	'smsCode' => '短信验证码',
        	'spreadUser' => '推荐用户'
        ];
	}

	public function validateSmsCode() {
		$result = Sms::checkCode($this->phone, $this->smsCode, 'register');
		if($result['status']==0) {
			$this->addError('password', $result['info']);
		}
	}

	public function validatePhone() {
		if(!preg_match("/1\d{10}$/",$this->phone)) {
			$this->addError('phone', '手机号码格式不正确！');
		} else {
			if(User::isPhoneExist($this->phone)) {
				$this->addError('phone', '该手机号已经被占用！');
			}
		}
	}

	public function validatePassword() {
		if(strlen($this->password)<6) {
			$this->addError('password', '登录密码长度不能小于6位！');
		}
	}

	public function register() {
		if($this->check()) {

            $data = [];
            $data['loginpass'] = $this->password;
            $data['username'] = $this->phone;
            $data['phone'] = $this->phone;
            $data['phonestatus'] = 'y';
            $data['addtime'] = date('Y-m-d H:i:s', time());
            $data['spreadUser'] = $this->spreadUser;
            $data['media'] = $this->getMedia();
            $data['pm_key'] = $this->pm_key;
            $this->user = User::addOne($data);

            $msg = [];
            $msg['phone'] = $this->user->phone;
            $msg['msgType'] = 'registerSuccess';
            $msg['userId'] = $this->user->userId;
            $msg['params'] = [
                            ];
            Sms::send($msg);

            if($this->user) {
                return true;
            } else {
                $this->addError('form', '注册失败');
                return false;
            }
		} 
		return false;
	}
}