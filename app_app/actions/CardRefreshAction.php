<?php
use custody\API;
use traits\handles\ITFAuthHandle;

/**
 * CardRefreshAction
 * 用户银行卡信息同步
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class CardRefreshAction extends Action {
    use ITFAuthHandle;
    
    public function execute() {
        $params = $this->getAllPost();
        $this->authenticate($params, ['userId'=>'用户ID']);
        
        $user = $this->getUser();
        $result = API::refreshUserBank($user);

        $rdata = [];
        $rdata['status'] = $result['status'];
        $rdata['msg'] = $result['msg'];
        $this->backJson($rdata);
    }
}