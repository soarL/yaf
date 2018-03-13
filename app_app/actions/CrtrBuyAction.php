<?php
use models\Crtr;
use models\UserCrtr;
use helpers\StringHelper;
use traits\handles\ITFAuthHandle;

/**
 * CrtrBuyAction
 * APP债权转让在买记录数据
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class CrtrBuyAction extends Action {
    use ITFAuthHandle;
    
    public function execute() {
    	$params = $this->getAllQuery();
        $this->authenticate($params, ['id'=>'债权编号']);

        $id = $params['id'];
        $page = $params['page'];
        $pageSize = $params['pageSize'];
        $skip = ($page-1)*$pageSize;

        $crtr = Crtr::find($id);
        $builder = UserCrtr::where('crtr_id', $id)->where('status', '0');
        $count = $builder->count();
        $tenders = $builder->with('user')->skip($skip)->limit($pageSize)->get();
        $newTenders = [];
        foreach ($tenders as $key => $tender) {
            $newTender = [];
            $newTender['key'] = $skip+$key+1;
            $newTender['username'] = StringHelper::getHideUsername($tender->user->username);
            $newTender['money'] = $tender->money;
            $newTender['time'] = $tender->addTime;
            $lastTime = (strtotime($tender->addTime)+6*60)-time();
            $newTender['lastTime'] = $lastTime;
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