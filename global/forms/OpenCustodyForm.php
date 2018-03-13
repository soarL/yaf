<?php
namespace forms;

use Yaf\Registry;
use models\User;
use custody\API;
use helpers\StringHelper;

class OpenCustodyForm extends \Form {
    public $html;
    public $apitype = 'pc';

    public function defaults() {
        return ['type'=>'normal'];
    }

    public function rules() {
        return [
            [['cardnum', 'name', 'bankNum'], 'required'],
            ['cardnum', 'idCard'],
            ['cardnum', 'validateCardnum'],
        ];
    }

    public function labels() {
        return [
            'cardnum' => '身份证号',
            'name' => '姓名',
            'bankNum' => '银行卡号'
        ];
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

    public function open() {
        if($this->check()) {
            $user = $this->getUser();
            $info = [
                'userId'=>$user->userId, 
                'cardnum'=>strtoupper($this->cardnum), 
                'bankNum'=>$this->bankNum, 
                'name'=>$this->name,
                'phone'=>$user->phone,
            ];

            $this->html = API::openCustody($info, $this->getMedia());

            return true;
        } else {
            return false;
        }
    }
}
