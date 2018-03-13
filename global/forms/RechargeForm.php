<?php
namespace forms;
use models\Recharge;
use models\UserBank;
use Yaf\Registry;
use tools\Log;
use tools\Counter;
use custody\API;

class RechargeForm extends \Form {
    public $html;

    public function rules() {
        return [
            [['money'], 'required'],
            ['money', 'validateMoney'],
        ];
    }

    public function labels() {
        return [
            'money' => '充值金额',
            'payWay' => '充值方式',
        ];
    }
    
    public function validateMoney() {
        if(!is_numeric($this->money)) {
            $this->addError('money', '充值金额必须为数字！');
        } else {
            if($this->money<100) {
                $this->addError('money', '充值金额不能小于100元！');
            } else {
                if($this->money>5000000) {
                    $this->addError('money', '单笔充值不能超过500万！');
                }   
            }
        }
    }

    public function recharge() {
        if($this->check()) {
            $user = $this->getUser();

            $tradeNo = Counter::getOrderID();

            $recharge = new Recharge();
            $recharge->serialNumber = $tradeNo;
            $recharge->userId = $user->userId;
            $recharge->money = $this->money;
            $recharge->fee = 0;
            $recharge->status = 0;
            $recharge->time = date('Y-m-d H:i:s');
            $recharge->payType = 'custody';
            $recharge->remark = '在线充值';
            $recharge->payWay = $this->payWay;
            $recharge->media = $this->getMedia();
            if($recharge->save()) {
                
                $this->html = API::recharge($recharge);

                return true;
            } else {
                $this->addError('form', '充值失败！');
                return false;
            }
        } else {
            return false;
        }
    }
}
