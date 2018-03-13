<?php
namespace forms;
use models\User;
use models\Sms;
use models\Xinghuo;
use tools\Log;
class XhztRegisterForm extends \Form {
    public $user;

    public function defaults() {
        return ['spreadUser'=>'', 'pm_key'=>'', 'isCheckSms'=>1];
    }

	public function rules() {
		return [
			[['username', 'phone', 'password', 'smsCode'], 'required'],
			['username', 'validateUsername'],
			['phone', 'validatePhone'],
			['password','validatePassword'],
			['smsCode', 'validateSmsCode'],
		];
	}

	public function labels() {
		return [
			'username' => '昵称',
        	'phone' => '手机号码',
        	'password' => '登录密码',
        	'smsCode' => '短信验证码',
        	'spreadUser' => '推荐用户'
        ];
	}

	public function validateUsername() {
		$length = strlen($this->username);
		if($length<4||$length>24) {
			$this->addError('username', '昵称长度为4-16个字符之间！');
		} else {
			if(!preg_match("/^[a-zA-Z0-9_\x{4e00}-\x{9fa5}]{2,16}$/u",$this->username)) {
				$this->addError('username', '昵称格式不正确！');
			} else {
				if(User::isUsernameExist($this->username)) {
					$this->addError('username', '该昵称已经被占用！');
				}
			}
		}
	}

	public function validateSmsCode() {
        if($this->isCheckSms==1) {
			$result = Sms::checkCode($this->phone, $this->smsCode, 'register');
			if($result['status']==0) {
				$this->addError('password', $result['info']);
			}
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

            if($this->isCheckSms==0) {
            	$data = ['phone'=>$this->phone, 'msgType'=>'xhztRegister'];
            	$data['code'] = Sms::generateCode(Sms::CODE_LENGTH);
        		$data['params'] = [$data['code'],Sms::$msg[$data['msgType']],15];
                $result = Sms::send($data);
                if($result['status']==0) {
                    $this->addError('msgType', $result['info']);
                    return false;
                }
                $data['loginpass'] = $result['code'];
            }
            
            $data['username'] = $this->username;
            $data['phone'] = $this->phone;
            $data['phonestatus'] = 'y';
            $data['addtime'] = date('Y-m-d H:i:s', time());
            $data['spreadUser'] = $this->spreadUser;
            $data['media'] = $this->getMedia();
            $data['pm_key'] = $this->pm_key;
            $data['name'] = $this->user_name;
            $data['cardnum'] = $this->user_identity;
            $data['channel_id'] = $this->channel_id;

            $this->user = User::addOne($data);
            if($this->user) {
                Xinghuo::addOne($this->user_id,$data['phone'],$this->token,$this->user->userId);
                return true;
            } else {
                $this->addError('form', '注册失败');
                return false;
            }
		} 
		return false;
	}
}