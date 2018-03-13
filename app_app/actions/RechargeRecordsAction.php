<?php
use models\Recharge;
use Illuminate\Database\Capsule\Manager as DB;
use traits\handles\ITFAuthHandle;

/**
 * RechargeRecordsAction
 * APP充值记录
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class RechargeRecordsAction extends Action {
    use ITFAuthHandle;

    public function execute() {
    	$params = $this->getAllQuery();
    	$this->authenticate($params, ['userId'=>'用户ID']);
        
        $user = $this->getUser();
        $userId = $user->userId;

        $this->pv('ah');

        $timeBegin = $this->getQuery('startTime', '');
        $timeEnd = $this->getQuery('endTime', '');
        $page = $this->getQuery('page', 1);
        $pageSize = $this->getQuery('pageSize', 5);
        $payWay = $this->getQuery('payWay', 'all');
        $skip = ($page-1)*$pageSize;

        $builder = Recharge::where('userId', $userId)->where('status', 1);
        if($timeBegin!='') {
            $builder->where('time', '>=', $timeBegin.' 00:00:00');
        }
        if($timeEnd!='') {
            $builder->where('time', '<=', $timeEnd.' 23:59:59');
        }

        if($payWay!='all') {
            $builder->where('payWay', $payWay);
        }

        $result = $builder->where('status', 1)->first([DB::raw('count(*) total'), DB::raw('sum(money) money')]);
        $count = $result->total;
        $money = $result->money;
        $recharges = $builder->orderBy('time', 'desc')->skip($skip)->limit($pageSize)->get();

        $records = [];
        foreach ($recharges as $recharge) {
            $row = [];
            $row['tradeNo'] = $recharge->serialNumber;
            $row['money'] = $recharge->money;
            $row['status'] = $recharge->status;
            $row['time'] = $recharge->time;
            $row['media'] = $recharge->media;
            $row['payType'] = $recharge->getPayTypeName();
            $row['payWay'] = $recharge->payWay;
            $records[] = $row;
        }

        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data']['page'] = $page;
        $rdata['data']['count'] = $count;
        $rdata['data']['records'] = $records;
        $rdata['data']['totalCount'] = $count;
        $rdata['data']['totalMoney'] = $money===null?0:$money;
        $this->backJson($rdata);
    }
}