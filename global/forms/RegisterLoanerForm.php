<?php
namespace forms;
use models\User;
use models\Sms;
use helpers\StringHelper;
use tools\Redis;
use tools\BankCard;
use tools\Log;
use models\UserBank;
use helpers\NetworkHelper;

/**
 * RegisterSimpleForm
 * 简单注册表单
 *     基础信息只需要: 手机号、手机验证码、密码
 *     可选信息包括：推广码、渠道码
 *
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class RegisterLoanerForm extends \Form {
    public $user;

    public function defaults() {
        return ['spreadUser'=>'', 'pm_key'=>''];
    }

    public function rules() {
        return [
            [['phone', 'password', 'smsCode', 'name', 'cardnum', 'bankNum'], 'required'],
            ['phone', 'validatePhone'],
            ['password','validatePassword'],
            ['phone', 'validateSmsCode'],
            ['cardnum', 'validateCardnum'],
            ['bankNum', 'validateBankCard'],
            ['bankNum','validateBank'],
        ];
    }

    public function validateBank(){
        $url = 'https://carapi.91hc.com/index.php?r=api/baofu/bind-user-bank';
        $data = [
            'phone'=>$this->phone,
            'name'=>$this->name,
            'bankCode'=>$this->bankName,
            'bankNum'=>$this->bankNum,
            'cardnum'=>$this->cardnum,
            'money'=>'0.01',
        ];

        $result = json_decode(NetworkHelper::CurlPost($url,$data),true);
        if($result['ret'] != '0000'){
            $this->addError('phone', $result['msg']);
        }
    }
    
    public function labels() {
        return [
            "name" => '真实姓名',
            "cardnum" =>   '身份证',
            'phone' => '手机号码',
            'password' => '登录密码',
            'smsCode' => '短信验证码',
            'spreadUser' => '推荐用户',
            'bankNum' => '银行卡号'
        ];
    }

    public function validatePhone() {
        if(!preg_match("/1\d{10}$/",$this->phone)) {
            $this->addError('phone', '手机号码格式不正确！');
        } else {
            if(User::isPhoneExist($this->phone)) {
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

    public function validateCardnum() {
        $user = $this->getUser();
        if(User::isIDCardExist($this->cardnum, $user->userId)) {
            $this->addError('cardnum', '身份证号已存在！'); return;
        }
        $age = StringHelper::getAgeByBirthday(StringHelper::getBirthdayByCardnum($this->cardnum));
        if($age<18) {
            $this->addError('cardnum', '未满18周岁不能开户！'); return;
        }
        $status = true;
        // $status = API::identify(['name'=>$this->realname, 'cardnum'=>$this->cardnum]);
        if(!$status) {
            $this->addError('cardnum', '实名认证失败！'); return;
        }
    }

    public function validateBankCard() {
        if(!$this->hasErrors()) {
            if($this->bankNum==null||$this->bankNum=='') {
                $this->addError('bankNum', '请输入银行卡号！');
            }
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
                $user = $this->user;
                $birth = StringHelper::getBirthdayByCardnum($this->cardnum);
                $sex = StringHelper::getSexByCardnum($this->cardnum);
                User::where('userId', $user['userId'])->update([
                    'custody_id'=>$this->user->userId,
                    'cardnum'=>$this->cardnum,
                    'name'=>$this->name,
                    'sex'=>$sex, 
                    'birth'=>$birth, 
                    'userType'=>2,
                    'cardstatus'=>'y',
                    'certificationTime'=>date('Y-m-d H:i:s'),
                    'bindThirdTime'=>date('Y-m-d H:i:s'),
                    'is_custody_pwd'=>1
                ]);
                
                Redis::updateUser([
                    'userId'=>$user['userId'],
                    'custody_id'=>$user['userId'],
                    'cardnum'=>$this->cardnum,
                    'name'=>$this->name,
                ]);

                $binInfo = BankCard::getBinInfo($this->bankNum);
                
                $bankData = file_get_contents('https://ccdcapi.alipay.com/validateAndCacheCardInfo.json?_input_charset=utf-8&cardNo='.$this->bankNum.'&cardBinCheck=true');
                $bankData = json_decode($bankData,true);
                Log::write('银行信息:', [$bankData], 'baofoo');

                UserBank::insert([
                    'bankCName'=>BankCard::getBankCName($bankData['bank']),
                    'bankName'=>$bankData['bank'],
                    'phone'=>$this->phone,
                    'userId'=>$user['userId'], 
                    'bankNum'=>$this->bankNum, 
                    'createAt'=>date('Y-m-d H:i:s'), 
                    'updateAt'=>date('Y-m-d H:i:s'),
                    'binInfo'=>$binInfo,
                    'cardType' => '101',
                    // 'validDate' => isset($params['valid_date'])?$params['valid_date']:'',
                    // 'cvv' => isset($params['cvv'])?$params['cvv']:'',
                    //'bindId' => $form->result['bind_id']
                ]);


                $key = Redis::getKey('ancunQueue');
                $params = [$key];
                $list[] = json_encode(['key'=>$user->userId, 'type'=>'user', 'flow'=>0]);
                $list[] = json_encode(['key'=>$user->userId, 'type'=>'user', 'flow'=>1]);
                $params = array_merge($params, $list);
                call_user_func_array(array('tools\Redis', 'lpush'), $params);

                return true;
            } else {
                $this->addError('form', '注册失败');
                return false;
            }
        }
        return false;
    }
}
