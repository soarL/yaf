<?php
use models\OddMoney;
use models\Lottery;
use traits\handles\ITFAuthHandle;

/**
 * InvestLotteriesAction
 * APP投资可用的加息券
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class InvestLotteriesAction extends Action {
    use ITFAuthHandle;
    
    public function execute() {
    	$params = $this->getAllQuery();
        $this->authenticate($params, ['oddMoneyId'=>'投资ID', 'userId'=>'用户ID']);
        
        $user = $this->getUser();
        $userId = $user->userId;

        $type = $this->getQuery('type', 'interest');
        $oddMoneyId = $params['oddMoneyId'];
        $page = $this->getQuery('page', 1);
        $pageSize = $this->getQuery('pageSize', 5);
        $skip = ($page-1)*$pageSize;

        $oddMoney = OddMoney::find($oddMoneyId);

        $builder = Lottery::where('userId', $userId)
            ->where('type', $type)
            ->where('status', Lottery::STATUS_NOUSE)
            ->where('endtime', '>', date('Y-m-d H:i:s'));
        $count = $builder->count();
        $lotteries = $builder->orderBy('endtime', 'asc')->skip($skip)->limit($pageSize)->get();
        
        $list = [];
        foreach ($lotteries as $lottery) {
            $result = $lottery->investCanUse($oddMoney);
            if($result['status']) {
                $row = [];
                $row['id'] = $lottery->id;
                $row['name'] = ($lottery->money_rate*100).'%加息券';
                $row['type'] = $lottery->getPeriodType();
                $row['money'] = $lottery->getMoneyType();
                $row['endtime'] = $lottery->endtime;
                $list[] = $row;
            }
        }

        $rdata = [];
        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data']['records'] = $list;
        $rdata['data']['count'] = $count;
        $rdata['data']['page'] = $page;
        $this->backJson($rdata);
    }
}