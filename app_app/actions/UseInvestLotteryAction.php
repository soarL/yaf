<?php
use models\OddMoney;
use models\Lottery;
use tools\API;
use traits\handles\ITFAuthHandle;

/**
 * UseInvestLotteryAction
 * APP使用加息券
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class UseInvestLotteryAction extends Action {
    use ITFAuthHandle;
    
    public function execute() {
    	$params = $this->getAllPost();
        $this->authenticate($params, ['userId'=>'用户ID', 'oddMoneyId'=>'投资ID', 'lotteryId'=>'加息券ID']);

        $rdata = [];
        $rdata['status'] = 1;
        $rdata['msg'] = '停止使用！';
        $this->backJson($rdata);

        $user = $this->getUser();
        $userId = $user->userId;

        $oddMoneyId = $params['oddMoneyId'];
        $lotteryId = $params['lotteryId'];

        $oddMoney = OddMoney::find($oddMoneyId);
        $lottery = Lottery::find($lotteryId);

        $result = $lottery->investCanUse($oddMoney);
        if($result['status']) {
            $status = API::lottery(['lotteryId'=>$lotteryId, 'oddMoneyId'=>$oddMoneyId]);
            if($status) {
                $rdata['status'] = 1;
                $rdata['msg'] = '使用成功！';
            } else {
                $rdata['status'] = 0;
                $rdata['msg'] = '使用失败！';
            }
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $result['msg'];
        }
        $this->backJson($rdata);
    }
}