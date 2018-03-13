<?php
namespace forms;

use models\Order;

class OrderForm extends \Form {

	public function rules() {
		return [
			[['name', 'phone', 'address', 'acreage', 'need_money', 'year_rate', 'house_type', 'cardnum'], 'required'],
			['need_money', 'validateMoney'],
			['phone', 'phoneNumber'],
			['cardnum', 'idCard'],
			['phone', 'validatePhone'],
			['year_rate', 'validateYearRate'],
			['house_type', 'validateHouseType'],
		];
	}

	public function labels() {
		return [
        	'name' => '姓名',
        	'phone' => '手机号',
        	'cardnum' => '身份证号',
        	'address' => '房产地址',
        	'acreage' => '房产面积',
        	'need_money' => '需求资金',
        	'year_rate' => '预期年化率',
        	'house_type' => '房产性质',
        ];
	}

	public function validateMoney() {
		if(is_numeric($this->need_money)) {
			if($this->need_money<=0) {
				$this->addError('need_money', '借款金额必须大于0！');
			}
		} else {
			$this->addError('need_money', '请输入正确的借款金额！');
		}
	}

	public function validatePhone() {
		$count = Order::where('phone', $this->phone)->where('add_time', 'like', date('Y-m-d').'%')->count();
		if($count>0) {
			$this->addError('phone', '您今天已经提交了申请，请耐心等待审核！');
		}
	}

	public function validateYearRate() {
		if(!isset(Order::$yearRates)) {
			$this->addError('year_rate', '请选择预期年化率！');
		}
	}

	public function validateHouseType() {
		if(!isset(Order::$types)) {
			$this->addError('house_type', '请选择房产性质！');
		}
	}
}