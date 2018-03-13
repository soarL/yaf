<?php
use models\OddMoney;
use models\Invest;
use models\User;
use traits\handles\ITFAuthHandle;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * AccountAction
 * APP账户首页数据
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class AccountAction extends Action {
    use ITFAuthHandle;

    public function execute() {
    	$params = $this->getAllQuery();
    	$this->authenticate($params, ['userId'=>'用户ID']);

        $user = $this->getUser();
        $userId = $user->userId;

        $tenderInfo = Invest::getUserTenderInfo($userId);
        $tenderMoney = OddMoney::getAllTenderMoneyByUser($userId);

        $begin = date('Y-m-01 00:00:00');
        $end = date('Y-m-01 00:00:00', strtotime('+1 month', strtotime($begin)));
        $result = Invest::where('status', '<>', 2)
        	->where('endtime', '>=', $begin)
        	->where('endtime', '<', $end)
        	->where('userId', $userId)
        	->first([DB::raw('sum(zongEr) _total'), DB::raw('count(*) _count')]);

        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data']['fundMoney'] = $user->fundMoney;
        $rdata['data']['tenderMoney'] = $tenderMoney;
        $rdata['data']['stayAllMoney'] = $tenderInfo['stayAll'];
        $rdata['data']['stayInterest'] = $tenderInfo['stayInterest'];
        $rdata['data']['hasInterest'] = $tenderInfo['hasInterest'];
        $rdata['data']['monthStayMoney'] = $result['_total'];
        $rdata['data']['monthStayCount'] = $result['_count'];

        $this->backJson($rdata);
    }
}