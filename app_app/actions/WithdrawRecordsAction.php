<?php
use models\Withdraw;
use Illuminate\Database\Capsule\Manager as DB;
use traits\handles\ITFAuthHandle;

/**
 * WithdrawRecordsAction
 * APP提现记录
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class WithdrawRecordsAction extends Action {
    use ITFAuthHandle;

    public function execute() {
    	$params = $this->getAllQuery();
    	$this->authenticate($params, ['userId'=>'用户ID']);
        
        $user = $this->getUser();
        $userId = $user->userId;

        $this->pv('ai');

        $timeBegin = $this->getQuery('startTime', '');
        $timeEnd = $this->getQuery('endTime', '');
        $status = $this->getQuery('status', 'all');
        $page = $this->getQuery('page', 1);
        $pageSize = $this->getQuery('pageSize', 5);
        $skip = ($page-1)*$pageSize;

        $builder = Withdraw::where('userId', $userId);
        
        $successBuilder = clone $builder;
        $successResult = $successBuilder->where('status', 1)->first([DB::raw('count(*) total'), DB::raw('sum(outMoney) money')]);

        if($status=='success') {
            $builder->where('status', 1);
        } else if($status=='fail') {
            $builder->whereIn('status', [0, 2]);
        } else if($status=='handle') {
            $builder->whereIn('status', [3]);
        } else {
            $builder->whereIn('status', [0, 1, 2, 3]);
        }

        if($timeBegin!='') {
            $builder->where('addTime', '>=', $timeBegin.' 00:00:00');
        }
        if($timeEnd!='') {
            $builder->where('addTime', '<=', $timeEnd.' 23:59:59');
        }

        $count = $builder->count();
        $withdraws = $builder->orderBy('addTime', 'desc')->skip($skip)->limit($pageSize)->get();

        $records = [];
        foreach ($withdraws as $withdraw) {
            $row = [];
            $row['tradeNo'] = $withdraw->tradeNo;
            $row['money'] = $withdraw->outMoney;
            $row['fee'] = $withdraw->fee;
            $row['status'] = $withdraw->status;
            $row['time'] = $withdraw->addTime;
            $row['media'] = $withdraw->media;
            $records[] = $row;
        }

        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data']['page'] = $page;
        $rdata['data']['count'] = $count;
        $rdata['data']['records'] = $records;
        $rdata['data']['totalCount'] = $successResult->total;
        $rdata['data']['totalMoney'] = $successResult->money===null?0:$successResult->money;
        $this->backJson($rdata);
    }
}