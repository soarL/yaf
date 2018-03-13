<?php
use models\OddMoney;
use traits\handles\ITFAuthHandle;

/**
 * CrtrRecordsAction
 * APP债转记录
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class UserCrtrsAction extends Action {
    use ITFAuthHandle;

    public function execute() {
        $params = $this->getAllQuery();
        $this->authenticate($params, ['userId'=>'用户ID']);
        
        $user = $this->getUser();
        $userId = $user->userId;

        $this->pv('ao');
        
        $type = $params['type'];
        $page = $params['page'];
    	$pageSize = $params['pageSize'];
    	$skip = ($page-1)*$pageSize;

        $builder = null;
        if($type=='sell') {
            $builder = OddMoney::getSellBuilder($userId);
        } else if($type=='buy') {
            $builder = OddMoney::getBuyBuilder($userId);
        } else if($type=='ing') {
            $builder = OddMoney::getIngBuilder($userId);
        } else if($type=='repay') {
            $builder = OddMoney::getRepayBuilder($userId);
        } else if($type=='over') {
            $builder = OddMoney::getOverBuilder($userId);
        } else {
        	$builder = OddMoney::getCanTransferBuilder($userId);
        }
        $count = $builder->count();
        $records = $builder->with(['lottery'=>function($q) {
            $q->select('id', 'type');    
        }])->orderBy('time', 'desc')->skip($skip)->limit($pageSize)->get();
        $oddMoneys = [];
		foreach ($records as $record) {
			$oddMoney = [];

            // 购买记录
            if($type=='buy'||$type=='repay'||$type=='over') {
                $oddMoney['id'] = $record->id;
                $oddMoney['crtrId'] = $record->parent->crtr->id;
    			$oddMoney['money'] = $record->money;
                $oddMoney['endtime'] = $record->getEndDay();
                $oddMoney['remainDay'] = $record->parent->crtr->getRemainDay();
                $oddMoney['time'] = $record->time;
    			$oddMoney['interest'] = $record->getInterest();
                $oddMoney['oddNumber'] = $record->oddNumber;
                $oddMoney['lotteryId'] = $record->lotteryId;
                $oddMoney['lotteryType'] = $record->lottery?$record->lottery->type:'none';
            } else if($type=='sell') {
                // 转让记录
                $oddMoney['id'] = $record->id;
                $oddMoney['crtrId'] = $record->crtr->id;
                $oddMoney['money'] = $record->crtr->money;
                $oddMoney['interest'] = $record->getStayInterest(true);
                $oddMoney['time'] = $record->crtr->addtime;
                $oddMoney['oddNumber'] = $record->oddNumber;
                $oddMoney['lotteryId'] = $record->lotteryId;
                $oddMoney['lotteryType'] = $record->lottery?$record->lottery->type:'none';
            } else if($type=='ing') {
                // 转让中记录
                $oddMoney['id'] = $record->id;
                $oddMoney['crtrId'] = $record->crtr->id;
                $oddMoney['oddYearRate'] = $record->odd->oddYearRate + $record->odd->oddReward;
                $oddMoney['money'] = $record->crtr->money;
                $oddMoney['interest'] = $record->getStayInterest();
                $oddMoney['time'] = $record->crtr->addtime;
                $oddMoney['endtime'] = $record->getEndDay();
                $oddMoney['remainDay'] = $record->crtr->getRemainDay();
                $oddMoney['lotteryId'] = $record->lotteryId;
                $oddMoney['lotteryType'] = $record->lottery?$record->lottery->type:'none';
            } else if($type=='can') {
                // 可转让
                $oddMoney['id'] = $record->id;
                $oddMoney['oddNumber'] = $record->oddNumber;
                $oddMoney['oddTitle'] = $record->odd->oddTitle;
                $oddMoney['oddMoney'] = $record->odd->oddMoney;
                $oddMoney['oddPeriod'] = $record->odd->getPeriod();
                $oddMoney['money'] = $record->money;
                $oddMoney['interest'] = $record->getInterest();
                $oddMoney['time'] = $record->time;
                $oddMoney['endtime'] = $record->getEndDay();
                $oddMoney['crtrSM'] = $record->getCrtrSM();
                $oddMoney['remain'] = $record->remain;
                $oddMoney['lotteryId'] = $record->lotteryId;
                $oddMoney['lotteryType'] = $record->lottery?$record->lottery->type:'none';
            }
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