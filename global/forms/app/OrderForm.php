<?php
namespace forms\app;
use tools\Areas;
use models\Order;
use models\Sms;
class OrderForm extends \Form {

    public function defaults() {
        return [
            'checkSms' => 0,
            'smsCode' => '',
        ];
    }

	public function rules() {
		return [
			[['name', 'phone', 'province', 'city', 'money'], 'required'],
			['province', 'validateProvince'],
			['city', 'validateCity'],
            ['money', 'validateMoney'],
            ['name', 'chineseName'],
            ['phone', 'phoneNumber'],
            ['checkSms', 'validateSms'],
		];
	}

	public function labels() {
		return [
        	'name' => '姓名',
        	'phone' => '手机号',
        	'money' => '借款金额',
        	'province' => '省份',
        	'city' => '城市',
        ];
	}

    public function validateSms() {
        if($this->checkSms) {
            $result = Sms::checkCode($this->phone, $this->smsCode, 'orderLoan');
            if($result['status']==0) {
                $this->addError('checkSms', $result['info']);
            }
        }
    }

	public function validateMoney() {
		if(is_numeric($this->money)) {
			if($this->money<=0||$this->money%50!=0) {
				$this->addError('money', '借款金额必须大于0，且为50的倍数！');
			}
		} else {
			$this->addError('money', '请输入正确的借款金额！');
		}
	}

	public function validateProvince() {
		if($this->province>0) {
			if(!Areas::isProvinceExist($this->province)) {
				$this->addError('province', '省份不存在！');
			}
		} else {
			$this->addError('province', '请选择省份！');
		}
	}

	public function validateCity() {
		if($this->city>0) {
			if(!Areas::isCityExist($this->province, $this->city)) {
				$this->addError('city', '城市不存在！');
			}
		} else {
			$this->addError('city', '请选择城市！');
		}
	}

	public function save() {
		if($this->check()) {
			$order = new Order();
            $order->name = $this->name;
            $order->phone = $this->phone;
            $order->province = Areas::getProvinceName($this->province);
            $order->city = Areas::getCityName($this->province, $this->city);
            $order->need_money = $this->money;
            $order->add_time = date('Y-m-d H:i:s');
            if($order->save()) {
                return true;
            } else {
            	$this->addError('form', '很抱歉，提交失败！');
            	return false;
            }
		} else {
			return false;
		}
	}
}