<?php
use traits\handles\ITFAuthHandle;
use tools\Calculator;

/**
 * CalculateAction
 * 计算器
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class CalculateAction extends Action {
    use ITFAuthHandle;
    
    public function execute() {
    	$params = $this->getAllQuery();
        $this->authenticate($params, ['repayType'=>'还款方式', 'account'=>'投资金额', 'period'=>'投资期限', 'yearRate'=>'年化率', 'periodType'=>'期限类型']);

        $data = [];
        $data['account'] = $params['account'];
        $data['repayType'] = $params['repayType'];
        $data['period'] = $params['period'];
        $data['yearRate'] = $params['yearRate'];
        $data['periodType'] = $params['periodType'];
        $result = Calculator::getResult($data);

        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data']['interest'] = $result['sumInterest'];
        
        $this->backJson($rdata);
    }
}