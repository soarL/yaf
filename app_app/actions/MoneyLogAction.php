<?php
use models\MoneyLog;
use traits\handles\ITFAuthHandle;

/**
 * MoneyLogAction
 * APP资金记录数据
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class MoneyLogAction extends Action {
    use ITFAuthHandle;
    
    public function execute() {
    	$params = $this->getAllQuery();
        $this->authenticate($params, ['userId'=>'用户ID']);

        $user = $this->getUser();
        $userId = $user->userId;
        
        $timeBegin = $this->getQuery('timeBegin', '');
        $timeEnd = $this->getQuery('timeEnd', '');
        $type = $this->getQuery('type', 'all');

        $page = $params['page'];
        $pageSize = $params['pageSize'];
        $skip = ($page-1)*$pageSize;

        $builder = MoneyLog::where('userId', $userId)->where('mode', '<>', 'sync');
        if($type!='all') {
            $builder->where('type', $type);
        }
        if($timeBegin!='') {
            $builder->where('time', '>=', $timeBegin.' 00:00:00');
        }
        if($timeEnd!='') {
            $builder->where('time', '<=', $timeEnd.' 23:59:59');
        }
        $count = $builder->count();
        $logs = $builder->orderBy('time', 'desc')->orderBy('id', 'desc')->skip($skip)->limit($pageSize)->get();

        $records = [];
        foreach ($logs as $key => $log) {
            $record = [];
            $record['type'] = $log->getTypeName();
            $record['money'] = $log->mvalue;
            $record['mode'] = $log->mode;
            $record['time'] = $log->time;
            $records[$key] = $record;
        }

        $types = [];
        foreach (MoneyLog::$types as $k => $t) {
            $types[] = [$k=>$t];
        }

        $rdata = [];
        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data']['records'] = $records;
        $rdata['data']['count'] = $count;
        $rdata['data']['page'] = $page;
        $rdata['data']['types'] = $types;
        $this->backJson($rdata);
    }
}