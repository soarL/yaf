<?php

use tools\Log;
use custody\Handler;

/**
 * GoController
 * <不知道如何描述>
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class GoController extends Controller {
    public $menu = 'go';
    public $submenu = 'go';

    public function rechargeAction() {
        $params = $this->getAllPost();

        Log::write('[RECHARGE]充值同步返回', $params, 'custody');

        $url = WEB_USER . '/account/recharge';
        $msg = '充值失败！';
        $status = 0;
        if(isset($params['retCode'])&&$params['retCode']==Handler::SUCCESS) {
            $status = 1;
            $msg = '充值成功！';
        }

        $this->go($url, $status, $msg);
    }

    public function infoAction() {
        $type = $this->getQuery('type', '');
        $msg = '操作完成！';
        $status = 1;
        $this->displayBasic('info', ['status'=>$status, 'msg'=>$msg]);
    }

    private function go($url, $status, $msg) {
        $channel = $this->getQuery('c', '');

        if($channel==Handler::M_APP) {
            $this->displayBasic('info', ['status'=>$status, 'msg'=>$msg]);
        } else {
            if($status) {
                Flash::success($msg);
            } else {
                Flash::error($msg);
            }
            $this->redirect($url);
        }
    }
}