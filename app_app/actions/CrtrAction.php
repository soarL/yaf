<?php
use models\Crtr;
use models\UserCrtr;
use models\OddMoney;
use traits\handles\ITFAuthHandle;

/**
 * CrtrAction
 * APP债权详情页数据
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class CrtrAction extends Action {
    use ITFAuthHandle;
    
    public function execute() {
    	$params = $this->getAllQuery();
        $this->authenticate($params, ['id'=>'ID']);

        $id = $params['id'];

        $record = Crtr::find($id);
        $buyCount = OddMoney::where('type', 'credit')->where('cid', $record->id)->count();
        $ingCount = UserCrtr::where('crtr_id', $id)->where('status', '0')->count();

        $crtr = [];
        $crtr['id'] = $record->id;
        $crtr['title'] = '债权转让'.$record->getSN().'号';
        $crtr['money'] = $record->money;
        $crtr['remainInterest'] = $record->getStayInterest();
        $crtr['progress'] = $record->progress;
        $crtr['addtime'] = $record->addtime;
        $crtr['endtime'] = $record->odd->getEndTime();
        $crtr['remainDay'] = $record->getRemainDay();
        $crtr['moneyLast'] = $record->getRemain();
        $crtr['oddNumber'] = $record->oddNumber;
        $crtr['userName'] = _hide_phone($record->user->username);
        $crtr['oddYearRate'] = $record->odd->oddYearRate + $record->odd->oddReward;
        $crtr['oddReward'] = $record->odd->oddReward;
        $crtr['oddRepaymentStyle'] = $record->odd->oddRepaymentStyle;
        $crtr['buyCount'] = $buyCount;
        $crtr['ingCount'] = $ingCount;
        $crtr['schedule'] = $record->getPer($crtr['moneyLast']);
        $crtr['investedInterest'] = $record->oddMoney->getInvestedStayInterest();

        $rdata = [];
        $rdata['status'] = 1;
        $rdata['msg'] = 'success';
        $rdata['data'] = $crtr;
        $this->backJson($rdata);
    }
}