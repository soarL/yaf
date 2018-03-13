<?php
use models\Invest;
use traits\handles\ITFAuthHandle;

/**
 * RepaymentDetailAction
 * APP还款详情
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class RepaymentDetailAction extends Action {
    use ITFAuthHandle;

    public function execute() {
    	$params = $this->getAllQuery();
    	$this->authenticate($params, ['userId'=>'用户ID']);

        $user = $this->getUser();
        $userId = $user->userId;

        $page = $this->getQuery('page', 1);
        $pageSize = $this->getQuery('pageSize', 5);
        $timeBegin = $this->getQuery('timeBegin', '');
        $timeEnd = $this->getQuery('timeEnd', '');
        $oddMoneyId = $this->getQuery('oddMoneyId', 0);
        $day = $this->getQuery('day', '');
        $skip = ($page-1)*$pageSize;

        if($timeBegin!='') {
            $timeBegin = $timeBegin . ' 00:00:00';
        }
        if($timeEnd!='') {
            $timeEnd = $timeEnd . ' 23:59:59';
        }
        
        $builder = Invest::getRepaymentsBuilder($userId, $timeBegin, $timeEnd, 'all', '', $oddMoneyId);
        $count = $builder->count();
        $repayments = $builder->skip($skip)->limit($pageSize)->get();

        $records = [];
        foreach ($repayments as $repayment) {
            $row = [];
            $row['qishu'] = $repayment->qishu;
            $row['endtime'] = _date('Y-m-d', $repayment->endtime);
            $row['amount'] = $repayment->getAmount();
            $row['realMoney'] = $repayment->realAmount;
            $row['serviceMoney'] = $repayment->serviceMoney;
            $row['extra'] = $repayment->extra;
            $row['subsidy'] = $repayment->subsidy;
            $row['status'] = $repayment->status;
            $records[] = $row;
        }

        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data']['page'] = $page;
        $rdata['data']['count'] = $count;
        $rdata['data']['records'] = $records;
        $this->backJson($rdata);
    }
}