<?php
use models\UserBank;
use helpers\StringHelper;
use custody\Handler;
use custody\Code;
use traits\handles\ITFAuthHandle;

/**
 * CardBindAction
 * 用户银行卡绑定
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class CardBindAction extends Action {
    use ITFAuthHandle;
    
    public function execute() {
        $params = $this->getAllQuery();
        $this->authenticate($params, ['bankNum'=>'银行卡号', 'userId'=>'用户ID']);
        $bankNum = $params['bankNum'];
        
        $user = $this->getUser();

        $data['accountId'] = $user->custody_id;
        $data['idType'] = '01';
        $data['idNo'] = $user->cardnum;
        $data['name'] = $user->name;
        $data['mobile'] = $user->phone;
        $data['cardNo'] = $bankNum;
        $data['retUrl'] = WEB_MAIN.'/go/info';
        $data['notifyUrl'] = WEB_MAIN.'/custody/cardBindNotify';
        $data['userIP'] = '';
        $acqRes = ['userId'=>$user->userId, 'bankNum'=>$bankNum];
        $data['acqRes'] = StringHelper::encodeQueryString($acqRes);

        $handler = new Handler('cardBind', $data);
        $handler->form();
    }
}