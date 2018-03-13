<?php
namespace forms;
use tools\Calculator;
class CalculateForm extends \Form {
	public $result;

	public function rules() {
		return [
			[['yearRate', 'repayType', 'account', 'periodType', 'period'], 'required'],
			['period', 'type', ['type'=>'int']],
			['account', 'type', ['type'=>'float']],
			['yearRate', 'type', ['type'=>'float']],
			['periodType', 'enum', ['values'=>['month', 'week']]],
			['repayType', 'enum', ['values'=>['1', '2', '3']]],
		];
	}

	public function labels() {
		return [
        	'period' => '期限',
        	'yearRate' => '年利率',
			'periodType' => '期限类型',
        	'account' => '金额',
        	'repayType' => '还款方式',
        ];
	}

	public function calculate() {
		if($this->check()) {
			$data = [];
			$data['account'] = $this->account;
	        $data['yearRate'] = $this->yearRate/100;
	        $data['period'] = $this->period;
	        $data['repayType'] = $this->repayType;
	        $data['periodType'] = $this->periodType;
			$this->result = Calculator::getResult($data);
			return true;
		} else {
			return false;
		}
	}
}