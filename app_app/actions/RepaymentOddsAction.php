<?php
use traits\handles\ITFAuthHandle;
use models\Invest;

/**
 * RepaymentOddsAction
 * APP回款日历标的详情
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class RepaymentOddsAction extends Action {
    use ITFAuthHandle;

    public function execute() {
    	$params = $this->getAllQuery();
        $this->authenticate($params, ['userId'=>'用户ID']);

        $user = $this->getUser();
        $userId = $user->userId;
        
        $day = $this->getQuery('day', date('Y-m-d'));

        $firstDay = $day . ' 00:00:00';
        $lastDay = $day . ' 23:59:59';

        $repayments = Invest::getRepaymentsBuilder($userId, $firstDay, $lastDay)->get();

        $odds = [];
        foreach ($repayments as $repayment) {
            $odd = [];
            $odd['oddNumber'] = $repayment->oddMoney->oddNumber;
            $odd['oddTitle'] = $repayment->oddMoney->odd->oddTitle;
            $odd['oddPeriod'] = $repayment->oddMoney->odd->getPeriod();
            $odd['oddYearRate'] = $repayment->oddMoney->odd->oddYearRate;
            $odd['money'] = $repayment->oddMoney->money;
            $odd['status'] = $repayment->status;
            $odds[] = $odd;
        }

        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data']['odds'] = $odds;
        
        $this->backJson($rdata);
    }
}