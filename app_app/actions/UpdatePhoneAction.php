<?php
use custody\Handler;
use models\User;
use traits\handles\ITFAuthHandle;
use helpers\StringHelper;

/**
 * UpdatePhoneAction
 * 修改手机号
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class UpdatePhoneAction extends Action {
    use ITFAuthHandle;
    
    public function execute() {
        $params = $this->getAllQuery();
        $this->authenticate($params, ['userId'=>'用户ID', 'phone'=>'新手机号']);

        $phone = $params['phone'];
        $user = $this->getUser();

        if($phone==$user->phone) {
            $this->displayBasic('info', ['status'=>0, 'msg'=>'您的手机号已经是该号码！']);
        } else if(User::isPhoneExist($phone)) {
            $this->displayBasic('info', ['status'=>0, 'msg'=>'手机号已存在！']);
        }

        $data = [];
        $data['accountId'] = $user->custody_id;
        $data['option'] = '1';
        $data['mobile'] = $phone;
        $data['retUrl'] = WEB_MAIN.'/go/info';
        $data['notifyUrl'] = WEB_MAIN.'/custody/updatePhoneNotify';
        $data['acqRes'] = StringHelper::encodeQueryString(['userId'=>$user->userId, 'phone'=>$phone]);
        $data['channel'] = Handler::M_APP;

        $handler = new Handler('mobileModify', $data);
        $handler->form();
    }
}