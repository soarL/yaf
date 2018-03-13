<?php
namespace traits;

use Yaf\Request_Abstract;
use Yaf\Response_Abstract;
use Yaf\Registry;
use exceptions\HttpException;
use tools\Printer;
use tools\WebError;
use models\AuthAction;

trait ActionControl {
    private $isXml;
    private $user;

    /**
     * 在路由结束之后触发，用于登陆检查
     * @param Yaf_Request_Abstract $request
     * @param Yaf_Response_Abstract $response
     */
    public function routerShutdown(Request_Abstract $request, Response_Abstract $response) {
        Registry::set('isAuth', true);
        $this->isXml = $request->isXmlHttpRequest();
        $this->user = $this->getUser();
        $perm = $this->getPerm($request);
        $this->isAuth($perm)||$this->authError();
    }

    public function isAuth($perm) {
        if($perm) {
            if($this->user) {
                if(!$this->user->can($perm, true)) {
                    return false;
                }
            } else {
                return false;
            }
        }
        return true;
    }

    private function getPerm($request) {
        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        $link = strtolower('/'.$controller.'/'.$action);
        $siteinfo = Registry::get('siteinfo');
        $authAction = AuthAction::where('domain', $siteinfo['domain'])->where('module', $module)->where('link', $link)->first();
        if($authAction) {
            return $authAction->identifier;
        } else {
            return false;
        }
    }

    private function authError() {
        if($this->isXml) {
            Printer::json(['status'=>WebError::NOPERM, 'info'=>'权限不足！']);
        } else {
            die('没有权限!');
        }
    }

    private function getUser() {
        $user = null;
        if(!Registry::has('user')) {
            $userID = Registry::get('session')->get('userID');
            if($userID) {
                $user = User::where('userId', $userID)->first();
            }
            Registry::set('user', $user);
        } else {
            $user = Registry::get('user');
        }
        return $user;
    }
}
