<?php
use models\OddMoney;
use helpers\StringHelper;
use traits\handles\ITFAuthHandle;

/**
 * OddTendersAction
 * APP标的投资记录数据
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class OddTendersAction extends Action {
    use ITFAuthHandle;

    public function execute() {
    	$params = $this->getAllQuery();
        $this->authenticate($params, ['oddNumber'=>'标的号']);

        $oddNumber = $params['oddNumber'];
        $page = $params['page'];
        $pageSize = $params['pageSize'];
        $skip = ($page-1)*$pageSize;

        $builder = OddMoney::where('oddNumber', $oddNumber)->where('type', 'invest')->where('status', '<>', 4);
        $count = $builder->count();

        $tenders = $builder->with('user')->skip($skip)->limit($pageSize)->get();

        $records = [];
        foreach ($tenders as $key => $tender) {
            $newTender = [];
            $newTender['key'] = ($page-1)*$pageSize+$key+1;
            $newTender['username'] = StringHelper::getHideUsername($tender->user->username);
            $newTender['money'] = $tender->money;
            $newTender['time'] = $tender->time;
            $newTender['media'] = $tender->media;
            $newTender['autoOrder'] = $tender->order>0?$tender->order:'无';
            $newTender['bidType'] = $tender->remark;
            $records[$key] = $newTender;
        }

        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data']['records'] = $records;
        $rdata['data']['page'] = $page;
        $rdata['data']['count'] = $count;
        $this->backJson($rdata);
    }
}