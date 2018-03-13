<?php
use models\Crtr;
use traits\handles\ITFAuthHandle;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * CrtrsAction
 * APP债权转让列表页数据
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class CrtrsAction extends Action {
    use ITFAuthHandle;

    public function execute() {
    	$params = $this->getAllQuery();
        $this->authenticate($params);

        $this->pv('at');
        
        $page = $params['page'];
        $pageSize = $params['pageSize'];
        $skip = ($page-1)*$pageSize;
        $sort = $this->getQuery('sort', null);
        $order = $this->getQuery('order', null);

        $builder = Crtr::getListBuilder()->join('work_odd','work_odd.oddNumber','=','work_creditass.oddNumber')->select('work_creditass.*','work_odd.oddYearRate',DB::raw('(UNIX_TIMESTAMP(work_odd.oddRehearTime) + work_odd.oddBorrowPeriod * 30*24*60*60 - '.time().') as qixian'));;
        $count = $builder->count();
        $builder = Crtr::sortList($builder,$sort,$order);
        $crtrs = $builder->skip($skip)->limit($pageSize)->get();

        $records = [];
        foreach ($crtrs as $crtr) {
            $row = [];
            $row['id'] = $crtr->id;
            $row['title'] = '转让项目'.$crtr->getSN().'号';
            $row['oddYearRate'] = $crtr->odd->oddYearRate + $crtr->odd->oddReward;
            $row['oddReward'] = $crtr->odd->oddReward;
            $row['remainDay'] = $crtr->getRemainDay();
            $row['moneyLast'] = $crtr->getRemain();
            $row['money'] = $crtr->money;
            $row['oddNumber'] = $crtr->odd->oddNumber;
            $row['time'] = $crtr->addtime;
            $row['progress'] = $crtr->progress;
            $row['schedule'] = $crtr->getPer($row['moneyLast']);
            $records[]  = $row;
        }

        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data']['records'] = $records;
        $rdata['data']['page'] = $page;
        $rdata['data']['count'] = $count;
        $this->backJson($rdata);
    }
}