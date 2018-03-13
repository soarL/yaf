<?php
namespace traits\handles;

use Yaf\Registry;
use tools\WebSign;
use tools\AppPV;
use tools\Log;
use models\User;

/**
 * ITFAuthHandle
 * 接口验证-控制器方法分离
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
trait ITFAuthHandle {

    private $_media = 'app';

    public function authenticate($params, $expects=array()) {
        $checkSign = true;
        if($this instanceof \Controller) {
            $checkSign = $this->checkSign;
        } else if($this instanceof \Action) {
            $checkSign = $this->_controller->checkSign;
        }
        if(!WebSign::check($params, $expects, $checkSign)) {
            $action = $this->getRequest()->getActionName();
            $msg = WebSign::getMsg();
            $signCompare = WebSign::getSignCompare();
            Log::write('APP请求接口['.$action.']错误：'.$msg.$signCompare, $params, 'apperror', 'ERROR');

            $rdata['status'] = 0;
            $rdata['msg'] = $msg;
            $this->backJson($rdata);
        }
        // 增加用户判断
        if(isset($params['userId'])) {
            $user = User::find($params['userId']);
            Registry::set('user', $user);
            if(isset($expects['userId'])) {
                if(!$user) {
                    $action = $this->getRequest()->getActionName();
                    Log::write('APP请求接口['.$action.']错误：用户不存在！', $params, 'apperror', 'ERROR');

                    $rdata['status'] = 0;
                    $rdata['msg'] = '用户不存在！';
                    $this->backJson($rdata);
                }

                $userSecret = substr(md5($user->loginpass.$user->friendkey.'secret'), 8, 16);
                if(isset($params['userSecret'])&&$userSecret!=$params['userSecret']) {
                    $rdata['status'] = 88;
                    $rdata['msg'] = '密码已失效，请重新登录！';
                    $this->backJson($rdata);
                }
            }
        }

        if(isset($params['media'])) {
            $this->_media = $params['media'];
        }
    }

    public function pv($key) {
    	AppPV::add($key);
    }

    public function getMedia() {
        return $this->_media;
    }
}