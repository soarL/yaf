<?php
namespace forms;
use models\UserHouse;
use Yaf\Registry;
class UserHouseForm extends \Form {
	
	public function defaults() {
		return [
			'houserarea' => '',
			'houseryear' => '',
			'houserage' => '',
			'housermonth' => '',
			'houserbalance' => '',
			'houserpay' => '',
		];
	}

	public function rules() {
		return [
			[['houserarea', 'houseryear', 'houserage', 'housermonth', 'houserbalance'], 'type', ['type'=>'number']],
			['houserpay', 'enum', ['values'=>['y', 'n']]],
		];
	}

	public function labels() {
		return [
        	'houserarea' => '建筑面积',
        	'houseryear' => '建筑年份',
        	'houserage' => '贷款年限',
        	'housermonth' => '月供',
        	'houserbalance' => '贷款余额',
        	'houserpay' => '供款情况',
        ];
	}

	public function update() {
		if($this->check()) {
			$user = $this->getUser();
			$house = UserHouse::where('userId', $user->userId)->first();
			if(!$house) {
				$house = new UserHouse();
				$house->userId = $user->userId;
			}
			$house->houseradder = $this->houseradder;
			$house->housername1 = $this->housername1;
			$house->houserarea = $this->houserarea==''?null:$this->houserarea;
			$house->houseryear = $this->houseryear;
			$house->houserage = $this->houserage==''?null:$this->houserage;
			$house->housermonth = $this->housermonth==''?null:$this->housermonth;
			$house->houserbalance = $this->houserbalance==''?null:$this->houserbalance;
			$house->houserpay = $this->houserpay==''?null:$this->houserpay;
			$house->houserbank = $this->houserbank;

			if($house->save()) {
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