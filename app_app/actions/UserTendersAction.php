<?php
use models\OddMoney;
use models\Odd;
use traits\handles\ITFAuthHandle;

/**
 * UserTendersAction
 * APP投资记录
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class UserTendersAction extends Action {
    use ITFAuthHandle;

    public function execute() {
        $params = $this->getAllQuery();
        $this->authenticate($params, ['userId'=>'用户ID', 'type'=>'类型']);
        
        $user = $this->getUser();
        $userId = $user->userId;

        $this->pv('an');
        
        $type = $params['type'];
        $page = $params['page'];
    	$pageSize = $params['pageSize'];
    	$skip = ($page-1)*$pageSize;

        $builder = OddMoney::with('odd', 'invests')->where('type', 'invest')->where('userId', $userId)->whereRaw('(status=\'0\' or status=\'1\')');
        if($type=='run') {
            $type = 'repaying';
        } else if($type=='end') {
            $type = 'finished';
        }

        if($type!='all') {
            $prgs = Odd::$progressTypes[$type];
            $builder->whereHas('odd', function($q) use ($prgs){
                $q->whereIn('progress', $prgs);
            });
        }

        $count = $builder->count();
        $records = $builder->orderBy('time', 'desc')->skip($skip)->limit($pageSize)->get();
        $oddMoneys = [];
		foreach ($records as $record) {
			$oddMoney = [];
			$oddMoney['id'] = $record->id;
			$oddMoney['oddNumber'] = $record->oddNumber;
            $oddMoney['oddTitle'] = $record->odd->oddTitle;
			$oddMoney['oddMoney'] = $record->odd->oddMoney;
            $oddMoney['oddPeriod'] = $record->odd->getPeriod();
            $oddMoney['money'] = $record->money;
            $oddMoney['interest'] = $record->getInterest();
            $oddMoney['time'] = $record->time;
            $oddMoney['endtime'] = $record->getEndDay();
            $oddMoney['lotteryId'] = $record->lotteryId;
			$oddMoneys[] = $oddMoney;
		}
		$rdata = [];
		$rdata['status'] = 1;
		$rdata['msg'] = '获取成功！';
		$rdata['data']['page'] = $page;
		$rdata['data']['count'] = $count;
		$rdata['data']['records'] = $oddMoneys;
        $this->backJson($rdata);
    }
}