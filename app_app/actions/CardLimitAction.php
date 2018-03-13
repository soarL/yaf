<?php
use models\UserBank;
use helpers\StringHelper;
use custody\Handler;
use custody\Code;
use traits\handles\ITFAuthHandle;

/**
 * CardLimitAction
 * 银行卡充值限额
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class CardLimitAction extends Action {
    use ITFAuthHandle;

    public function execute() {
        $params = $this->getAllPost();
        $this->authenticate($params, ['userId'=>'用户ID']);

        $user = $this->getUser();

        $bank = UserBank::where('userId', $user->userId)->where('status', '1')->first();

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

        $banks = \Data::get('banks');
        $list = [];
        foreach ($banks as $key => $item) {
            $item['bankIcon'] = WEB_ASSET.'/common/app/bankIcons/'.$item['bankIcon'];
            $list[] = $item;
        }
        $data['limitList'] = $list;

        $rdata = [];
        $rdata['status'] = 1;
        $rdata['msg'] = 'success';
        $rdata['data'] = $data;
        $this->backJson($rdata);
    }
}