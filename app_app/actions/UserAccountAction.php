<?php
use models\User;
use models\Invest;
use models\OddMoney;
use models\OldData;
use models\GradeSum;
use models\Lottery;
use Illuminate\Database\Capsule\Manager as DB;
use traits\handles\ITFAuthHandle;

/**
 * UserAccountAction
 * APP账户信息接口
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class UserAccountAction extends Action {
    use ITFAuthHandle;

    public function execute() {
    	$params = $this->getAllQuery();
    	$this->authenticate($params, ['userId'=>'用户ID']);
        
        $user = $this->getUser();
        $userId = $user->userId;

        $this->pv('as');

        $tenderInfo = Invest::getUserTenderInfo($userId);
        $oldTenderMoney = OldData::getTenderMoneyByUser($userId);
        $tenderMoney = $oldTenderMoney + OddMoney::getTenderMoneyByUser($userId);

        $info = [];
        $info['allMoney'] = $user->fundMoney + $user->frozenMoney;
        $info['fundMoney'] = $user->fundMoney;
        $info['frozenMoney'] = $user->frozenMoney;
        $info['freeWithdraw'] = $user->investMoney;

        $info['tenderAll'] = $tenderMoney;
        $info['stayAll'] = $tenderInfo['stayAll'];
        $info['stayPrincipal'] = $tenderInfo['stayPrincipal'];
        $info['stayInterest'] = $tenderInfo['stayInterest'];
        $info['hasInterest'] = $tenderInfo['hasInterest'];
        $info['serviceMoney'] = $tenderInfo['hasSC'];
        $info['realInterest'] = $tenderInfo['hasInterest'] - $tenderInfo['hasSC'];

        $info['actLot'] = $user->imiMoney;
        $info['actMoney'] = $user->cashMoney;
        $info['integral'] = intval($user->integral/100);

        $transferInfo = Invest::getTransferInfo($userId);
        $crtrInfo = Invest::getCrtrInfo($userId);

        $info['transferMoney'] = $transferInfo['principal'];
        $info['transferInterest'] = $transferInfo['interest'];
        $info['crtrMoney'] = $crtrInfo['principal'];
        $info['crtrInterest'] = $crtrInfo['interest'];
        $spreadMoney = GradeSum::where('friend', $userId)->sum('money');
        $info['spreadMoney'] = $spreadMoney;
        $info['lastSpreadMoney'] = $user->gradeSum;
        
        $info['lotteries']['interest'] = 0;
        $info['lotteries']['withdraw'] = 0;
        $info['lotteries']['invest_money'] = 0;
        $results = Lottery::where('userId', $userId)
            ->where('endtime', '>', date('Y-m-d H:i:s'))
            ->where('status', Lottery::STATUS_NOUSE)
            ->groupBy('type')
            ->get([DB::raw('count(*) as total'), 'type']);
        foreach ($results as $key => $result) {
            $info['lotteries'][$result->type] = $result->total;
        }

        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data'] = $info;
        $this->backJson($rdata);
    }
}