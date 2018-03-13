<?php
use models\Interest;
use helpers\StringHelper;
use traits\handles\ITFAuthHandle;

/**
 * OddBuyAction
 * APP标的还款记录数据
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class OddRepayAction extends Action {
    use ITFAuthHandle;
    
    public function execute() {
    	$params = $this->getAllQuery();
        $this->authenticate($params, ['oddNumber'=>'标的号']);

        $oddNumber = $params['oddNumber'];
        $page = $params['page'];
        $pageSize = $params['pageSize'];
        $skip = ($page-1)*$pageSize;

        $builder = Interest::where('oddNumber', $oddNumber);
        $count = $builder->count();

        $tenders = $builder->skip($skip)->limit($pageSize)->get();
        $newTenders = [];
        foreach ($tenders as $key => $tender) {
            $newTender = [];
            $newTender['qishu'] = $tender['qishu'];
            $newTender['endtime'] = $tender['endtime'];
            $newTender['zongEr'] = $tender['zongEr'];
            $newTender['benJin'] = $tender['benJin'];
            $newTender['interest'] = $tender['interest'];
            $newTender['status'] = $tender['status'];
            $newTenders[$key] = $newTender;
        }

        $rdata = [];
        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data']['records'] = $newTenders;
        $rdata['data']['count'] = $count;
        $rdata['data']['page'] = $page;
        $this->backJson($rdata);
    }
}