<?php
use tools\WebSign;
use models\Odd;
use traits\handles\ITFAuthHandle;

/**
 * OddsAction
 * APP标的列表页数据
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class OddsAction extends Action {
    use ITFAuthHandle;

    public function execute() {
    	$params = $this->getAllQuery();
        $this->authenticate($params);
        
        $this->pv('al');

        $type = $this->getQuery('type', 'all');
        $period = $this->getQuery('period', 'all');
        $userId = $this->getQuery('userId', null);
        $sort = $this->getQuery('sort', null);
        $order = $this->getQuery('order', null);

        $periods = [[1=>'一月'], [2=>'二月'], [3=>'三月'], [6=>'六月'], [12=>'十二月'], [24=>'二十四月']];
        $types = [
            ['xingyong'=>'质押标'], 
            ['diya'=>'抵押标'], 
            ['danbao'=>'融资租赁标'], 
            ['newhand'=>'新手标'], 
            ['noNewhand'=>'非新手标'], 
            ['userdo'=>'手动标'],
            ['autodo'=>'自动标'],
        ];

        $page = $params['page'];
        $pageSize = $params['pageSize'];
        $skip = ($page-1)*$pageSize;

        $select = ['oddNumber', 'oddTitle', 'oddYearRate', 'oddReward', 'oddMoney', 'addtime', 'oddBorrowPeriod', 
            'oddBorrowStyle', 'progress', 'openTime', 'oddStyle', 'investType'];
        $builder = Odd::getListBuilder($userId);

        // 暂时不显示个人信贷标
        $builder->where('oddType', '<>', 'xiaojin');

        if($period!='all') {
            $builder->where('oddBorrowPeriod', $period)->where('oddBorrowStyle', 'month');
        }
        
        if($type!='all') {
            if($type=='newhand') {
                $builder->where('oddStyle', 'newhand');
            } else if($type=='noNewhand') {
                $builder->where('oddStyle', '<>', 'newhand');
            } else if($type=='userdo') {
                $builder->where('investType', '1');
                $builder->where('oddStyle', '<>', 'newhand');
            } else if($type=='autodo') {
                $builder->where('investType', '0');
            } else {
                $builder->where('oddType', $type);
            }
        }

        
        $builder = Odd::sortList($builder,$sort,$order);

        $count = $builder->count();
        $oddList = $builder->skip($skip)->limit($pageSize)->get($select);

        $records = [];
        foreach ($oddList as $odd) {
            $row = [];
            $row['oddNumber'] = $odd->oddNumber;
            $row['oddTitle'] = $odd->oddTitle;
            $row['oddMoney'] = $odd->oddMoney;
            $row['oddYearRate'] = $odd->oddYearRate;
            $row['oddReward'] = $odd->oddReward;
            $row['oddPeriod'] = $odd->getPeriod();
            $row['schedule'] = $odd->getPercent();
            $row['addtime'] = $odd->addtime;
            $row['progress'] = $odd->progress=='review'?'start':$odd->progress;
            $row['openTime'] = $odd->openTime;
            $row['second'] = $odd->getOpenSecond();
            $row['oddStyle'] = $odd->oddStyle;
            $row['investType'] = $odd->investType;
            $records[] = $row;
        }

        

        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data']['records'] = $records;
        $rdata['data']['page'] = $page;
        $rdata['data']['count'] = $count;
        $rdata['data']['periods'] = $periods;
        $rdata['data']['types'] = $types;
        $this->backJson($rdata);
    }
}