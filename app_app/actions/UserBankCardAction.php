<?php
use models\UserBank;
use models\Lottery;
use tools\Banks;
use Illuminate\Database\Capsule\Manager as DB;
use traits\handles\ITFAuthHandle;

/**
 * UserBankCardAction
 * APP用户银行卡
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class UserBankCardAction extends Action {
    use ITFAuthHandle;

    public function execute() {
    	$params = $this->getAllQuery();
    	$this->authenticate($params, ['userId'=>'用户ID']);
        
        $user = $this->getUser();
        $userId = $user->userId;

        $this->pv('ag');
        
        $bank = UserBank::where('userId', $userId)->where('status', '1')->first();
        $data = [];
        if($bank) {
            $data['id'] = $bank->id;
            $data['bankNum'] = $bank->bankNum;
            $data['bank'] = $bank->bank;
            $data['bankIco'] = WEB_ASSET.'/common/images/banks/'.$bank->bank.'.png';
            $data['binInfo'] = $bank->binInfo;
        } else {
            $data['id'] = 0;
            $data['bankNum'] = '';
            $data['bank'] = 0;
            $data['bankIco'] = '';
            $data['binInfo'] = '';
        }

        $lotteryCount = Lottery::where('userId', $userId)
            ->where('type', 'withdraw')
            ->where('status', Lottery::STATUS_NOUSE)
            ->where('endtime', '>', date('Y-m-d H:i:s'))
            ->count();

        $data['lotteryCount'] = $lotteryCount;

        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功';
        $rdata['data'] = $data;
        $this->backJson($rdata);
    }
}