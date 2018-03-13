<?php
use models\Lottery;
use traits\handles\ITFAuthHandle;

/**
 * MoneyLotteriesAction
 * APP投资可用的券
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class MoneyLotteriesAction extends Action {
    use ITFAuthHandle;
    
    public function execute() {
        $params = $this->getAllQuery();
        $this->authenticate($params, ['userId'=>'用户ID']);
        
        $user = $this->getUser();
        $userId = $user->userId;

        $pageType = $this->getQuery('pageType', 0);

        $page = $this->getQuery('page', 1);
        $pageSize = $this->getQuery('pageSize', 5);
        $skip = ($page-1)*$pageSize;

        $builder = Lottery::whereIn('type', ['invest_money', 'interest'])
            ->where('userId', $user->userId)
            ->where('endtime', '>', date('Y-m-d H:i:s'))
            ->where('status', Lottery::STATUS_NOUSE);

        $count = $builder->count();
        $lotteries = $builder->orderBy('endtime', 'asc')->skip($skip)->limit($pageSize)->get();

        $records = [];
        foreach ($lotteries as $lottery) {
            $row = [];
            $row['id'] = $lottery->id;
            $row['name'] = $lottery->getName();
            $row['type'] = $lottery->type;
            $row['period_lower'] = $lottery->period_lower;
            $row['period_uper'] = $lottery->period_uper;
            $row['money_lower'] = $lottery->money_lower;
            $row['money_uper'] = $lottery->money_uper;
            $row['money_rate'] = $lottery->money_rate;
            $row['endtime'] = $lottery->endtime;
            $records[] = $row;
        }

        $rdata = [];
        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data']['records'] = $records;
        $rdata['data']['count'] = $count;
        $rdata['data']['page'] = $page;
        $this->backJson($rdata);
    }
}