<?php
use models\Crtr;
use models\OddMoney;
use helpers\StringHelper;
use traits\handles\ITFAuthHandle;

/**
 * BuyRecordsAction
 * APP债权转让购买记录
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class BuyRecordsAction extends Action {
    use ITFAuthHandle;
    
    public function execute() {
    	$params = $this->getAllQuery();
        $this->authenticate($params, ['id'=>'ID']);

        $id = $params['id'];
        $page = $params['page'];
        $pageSize = $params['pageSize'];
        $skip = ($page-1)*$pageSize;
        
        $builder = OddMoney::where('type', 'credit')->where('cid', $id);
        
        $count = $builder->count();

        $tenders = $builder->with('user')->skip($skip)->limit($pageSize)->get();

        $records = [];
        foreach ($tenders as $key => $tender) {
            $record = [];
            $record['normalKey'] = ($page-1)*$pageSize+$key+1;
            $record['username'] = StringHelper::getHideUsername($tender->user->username);
            $record['money'] = $tender->money;
            $record['time'] = $tender->time;
            $records[] = $record;
        }

        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data']['records'] = $records;
        $rdata['data']['page'] = $page;
        $rdata['data']['count'] = $count;
        $this->backJson($rdata);
    }
}