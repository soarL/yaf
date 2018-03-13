<?php
namespace traits;

use Yaf\Request_Abstract;
use Yaf\Response_Abstract;
use exceptions\HttpException;
use tools\Config;
use Yaf\Registry;
use models\User;
use tools\Printer;
use tools\WebError;
use tools\SpecailIni;
use tools\Log;

trait Access {
    private $errorType = 'normal';

    private $request;
    private $config;

    private $closeReason;
    private $closeTime;
    private $openTime;

    private $clientIp;
    private $method;
    private $userID;
    private $isAjax;

    /**
     * 在路由结束之后触发，用于访问方式的检查
     * @param Yaf\Request_Abstract $request
     * @param Yaf\Response_Abstract $response
     */
    public function routerShutdown(Request_Abstract $request, Response_Abstract $response) {
        $this->config = Config::yaml('access', Config::APP);
        $this->request = $request;

        $this->method = strtolower($request->getMethod());
        $this->isAjax = $request->isXmlHttpRequest();
        $siteinfo = Registry::get('siteinfo');
        $this->clientIp = $siteinfo['clientIp'];
        $this->userID = $this->getUserID();

        if($this->config) {
            $map = $this->config['map'];
            $rules = $this->config['rules'];
            $list = $this->getList();
            foreach ($map as $item) {
                if($item['group']=='*'||in_array($item['group'], $list)) {
                    $this->isAccess($rules[$item['rule']])||$this->accessError();
                }
            }
        }
    }

    private function getList() {
        $groups = $this->config['groups'];
        $module = $this->request->getModuleName();
        $controller = $this->request->getControllerName();
        $action = $this->request->getActionName();
        $list = [];
        foreach ($groups as $key => $group) {
            if(isset($group[$module])) {
                $moduleItem = $group[$module];
                if($moduleItem=='*') {
                    $list[] = $key;
                } else if(is_array($moduleItem)) {
                    foreach ($moduleItem as $name => $actions) {
                        if(strpos($name, '!')===0) {
                            $realName = trim(substr($name, 1));
                            if($realName==$controller) {
                                if((is_array($actions) && !in_array($action, $actions)) || 
                                    (is_string($actions) && $actions!=$action)) {

                                    $list[] = $key;
                                }
                            }
                        } else if($name==$controller) {
                            if((is_array($actions) && in_array($action, $actions)) ||
                               (is_string($actions) && ($actions=='*'||$actions==$action))) {
                                $list[] = $key;
                            }
                        }
                    }
                }
            }
        }
        return $list;
    }

    private function isAccess($rule) {
        if(isset($rule['method'])) {
            if(is_array($rule['method'])&&!in_array($this->method, $rule['method'])) {
                return false;
            } else if(is_string($rule['method'])&&$this->method!=$rule['method']) {
                return false;
            }
        }
        if(isset($rule['login'])&&$rule['login']===true&&$this->userID=='') {
            $this->errorType = 'unlogin';
            return false;
        }

        if(isset($rule['ajax'])&&$rule['ajax']===true&&$this->isAjax==false) {
            return false;
        }

        $ipType = 'allow';
        $ipList = [];
        if(isset($rule['ip'])) {
            $ipList = $rule['ip']['list'];
            $ipType = $rule['ip']['type'];
            if($ipType=='allow') {
                if(!in_array($this->clientIp, $ipList)&&!in_array('0.0.0.0', $ipList)) {
                    return false;
                }
            } else if($ipType=='ban') {
                if(in_array($this->clientIp, $ipList)||in_array('0.0.0.0', $ipList)) {
                    return false;
                }
            }
        }

        if(isset($rule['time'])) {
            $timeRule = $rule['time'];
            $open = isset($timeRule['open'])?$timeRule['open']:null;
            $reason = isset($timeRule['reason'])?$timeRule['reason']:'';
            $openList = isset($timeRule['open'])?$timeRule['open']:[];
            $isClose = false;
            $begin = null;
            $end = null;
            if(is_array($open) && in_array($this->clientIp, $open)) {

            } else {
                if(!is_array($openList) || !in_array($this->clientIp, $openList)) {
                    $now = time();
                    if(isset($timeRule['begin'])&&$timeRule['begin']!=null) {
                        $begin = strtotime($timeRule['begin']);
                    }
                    if(isset($timeRule['end'])&&$timeRule['end']!=null) {
                        $end = strtotime($timeRule['end']);
                    }
                    if($begin==null && $end==null) {
                        $isClose = true;
                    } else if($begin==null && $end>=$now) {
                        $isClose = true;
                    } else if($begin<=$now && $end==null) {
                        $isClose = true;
                    } else if($now>=$begin&&$now<=$end) {
                        $isClose = true;
                    }
                }
            }
            if($isClose) {
                $this->closeTime = date('Y-m-d H:i:s', $begin);
                $this->openTime = date('Y-m-d H:i:s', $end);
                $this->closeReason = $reason;
                $this->errorType = 'serverClose';
                return false;
            }
        }
        return true;
    }

    private function accessError() {
        if($this->errorType=='unlogin') {
            if($this->isAjax) {
                Printer::json(['status'=>WebError::UNLOGIN, 'info'=>'未登录！']);
            } else {
                $siteinfo = Registry::get('siteinfo');
                Registry::get('session')->set(self::RETURN_URL, $siteinfo['fullUrl']);
                \Flash::error('请先登录！');
                header("Location:" . WEB_LOGIN.'?login');
                exit(0);
            }
        } else if($this->errorType=='serverClose') {
            if($this->isAjax) {
                Printer::json(['status'=>WebError::MAINT, 'info'=>'服务器正在维护！']);
            } else {
                $closeData = [];
                $closeData['closeTime'] = $this->closeTime;
                $closeData['openTime'] = $this->openTime;
                $closeData['reason'] = $this->closeReason;
                Registry::set('serverClose', $closeData);
                throw new HttpException(503);
            }
        } else {
            if($this->isAjax) {
                Printer::json(['status'=>WebError::ERRORACCESS, 'info'=>'访问错误！']);
            } else {
                throw new HttpException(404);
            }
        }
    }

    private function getUserID() {
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
        $userID = '';
        if($user) {
            $userID = $user->getKey();
        }
        return $userID;
    }
}