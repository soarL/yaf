<?php
namespace forms;
use models\User;
use models\Sms;
class ForgetForm extends \Form {
	public function rules() {
		return [
			[['phone', 'captcha'], 'required'],
			['captcha', 'captcha'],
			['phone', 'validatePhone'],
		];
	}

	public function labels() {
		return [
        	'phone' => '手机号',
        	'captcha' => '验证码',
        ];
	}

	public function validatePhone() {
		if(!User::isPhoneExist($this->phone)) {
			$this->addError('phone', '手机号不存在！');
		}
	}

	public function send() {
		if($this->check()) {
			$data = [];
            $data['userId'] = '';
            $data['phone'] = $this->phone;
            $data['msgType'] = 'forget';
			$data['code'] = Sms::generateCode(Sms::CODE_LENGTH);
        	$data['params'] = [$data['code'],Sms::$msg[$data['msgType']],15];            
            $result = Sms::send($data);
            if($result['status']==1) {
            	return true;
            } else {
            	$this->addError('form', $result['info']);
				return false;
            }
		} else {
			return false;
		}
	}
}