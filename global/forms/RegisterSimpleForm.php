<?php
namespace forms;
use models\User;
use models\Sms;

/**
 * RegisterSimpleForm
 * 简单注册表单
 *     基础信息只需要: 手机号、手机验证码、密码
 *     可选信息包括：推广码、渠道码
 *
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class RegisterSimpleForm extends \Form {
    public $user;

    public function defaults() {
        return ['spreadUser'=>'', 'pm_key'=>''];
    }

    public function rules() {
        return [
            [['phone', 'password', 'smsCode'], 'required'],
            ['phone', 'validateSmsCode'],
            ['phone', 'validatePhone'],
            ['password','validatePassword'],
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

    public function validatePhone() {
        if(!preg_match("/1\d{10}$/",$this->phone)) {
            $this->addError('phone', '手机号码格式不正确！');
        } else {
            $this->user = User::isPhoneExist($this->phone);
            if($this->user) {
                $this->addError('phone', '该手机号已经被占用！');
            } else if(User::isUsernameExist($this->phone)) {
                $this->addError('phone', '该手机号已经被占用！');
            }
        }
    }

    public function validatePassword() {
        if(strlen($this->password)<6) {
            $this->addError('password', '登录密码长度不能小于6位！');
        }
    }
    
    public function validateSmsCode() {
        $result = Sms::checkCode($this->phone, $this->smsCode, 'register');
        if($result['status']==0) {
            $this->addError('password', $result['info']);
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
