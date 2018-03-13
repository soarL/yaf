<?php
namespace forms;
use Yaf\Registry;
class UserInfoForm extends \Form {

	public function defaults() {
		return [
			'income' => 0,
			'adder' => '',
			'ethnic' => '',
			'educational' => '',
		];
	}

	public function rules() {
		return [
			['sex', 'enum', ['values'=>['man', 'women']]],
			['maritalstatus', 'enum', ['values'=>['y', 'n']]],
			['income', 'type', ['type'=>'number']],
		];
	}

	public function labels() {
		return [
        	'sex' => '性别',
        	'maritalstatus' => '婚姻状况',
        	'ethnic' => '民族',
        	'city' => '省市',
        	'adder' => '具体地址',
        	'educational' => '学历',
        	'income' => '月收入',
        ];
	}

	public function update() {
		if($this->check()) {
			$user = $this->getUser();
			$user->sex = $this->sex;
			$user->maritalstatus = $this->maritalstatus;
			$user->ethnic = $this->ethnic;
			$user->city = $this->city;
			$user->adder = $this->adder;
			$user->educational = $this->educational;
			$user->income = $this->income==0?null:$this->income;
			if($user->save()) {
				return true;
			} else {
				$this->addError('form', '更新失败！');
				return false;
			}
		} else {
			return false;
		}
	}
}