<?php
use models\UserBank;
use helpers\StringHelper;
use custody\Handler;
use custody\Code;
use traits\handles\ITFAuthHandle;

/**
 * CardUnbindAction
 * 用户银行卡解绑
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class CardUnbindAction extends Action {
    use ITFAuthHandle;
    
    public function execute() {
        $params = $this->getAllPost();
        $this->authenticate($params, ['bankNum'=>'银行卡号', 'userId'=>'用户ID']);
        $bankNum = $params['bankNum'];

        $rdata = [];
        $user = $this->getUser();
        if($user->fundMoney!=0) {
            $rdata['status'] = 0;
            $rdata['msg'] = '账户余额不为零，不可解绑！';
            $this->backJson($rdata);
        }

        $bank = UserBank::where('status', 1)->where('userId', $user->userId)->first();
        if(!$bank || $bank->bankNum!=$bankNum) {
            $rdata['status'] = 0;
            $rdata['msg'] = '银行卡号错误！';
            $this->backJson($rdata);
        }
        
        $data['accountId'] = $user->custody_id;
        $data['idType'] = '01';
        $data['idNo'] = $user->cardnum;
        $data['name'] = $user->name;
        $data['mobile'] = $user->phone;
        $data['cardNo'] = $bankNum;
        $acqRes = ['userId'=>$user->userId, 'bankNum'=>$bankNum];
        $data['acqRes'] = StringHelper::encodeQueryString($acqRes);

        $handler = new Handler('cardUnbind', $data);
        $result = $handler->api();

        if($result['retCode']==Handler::SUCCESS) {
            UserBank::where('userId', $user->userId)->where('bankNum', $bankNum)->update(['status'=>0]);
            $rdata['status'] = 1;
            $rdata['msg'] = '解绑成功！';
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = Code::getMsg($result['retCode']);
        }
        
        $this->backJson($rdata);
    }
}