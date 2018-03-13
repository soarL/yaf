<?php
namespace traits;

use Yaf\Request_Abstract;
use Yaf\Response_Abstract;
use Yaf\Registry;
use exceptions\HttpException;
use tools\Config;
use tools\Printer;
use tools\WebError;
use tools\SpecailIni;

trait Authorize {
    private $isXml;
    private $user;
    private $roles = [];
    private $perms = [];

    /**
     * 在路由结束之后触发，用于登陆检查
     * @param Yaf_Request_Abstract $request
     * @param Yaf_Response_Abstract $response
     */
    public function routerShutdown(Request_Abstract $request, Response_Abstract $response) {
        $config = Config::get('authorize', '', Config::APP);

        $this->isXml = $request->isXmlHttpRequest();
        $this->user = $this->getUser();
        $rulesConfig = $config['#rules#'];

        $specailIni = new SpecailIni($config, $request);
        $rules = $specailIni->getRules();
        if($rulesConfig) {
            foreach ($rules as $rule) {
                if($rulesConfig->$rule) {
                    $this->isAuth($rulesConfig->$rule)||$this->authError();
                }
            }
        }
    }

    public function isAuth($authString) {
        $this->parseAuthString($authString);
        if(count($this->roles)) {
            if($this->user) {
                if(!$this->user->hasRole($this->roles, true)) {
                    return false;
                }
            } else {
                return false;
            }
        }
        if(count($this->perms)) {
            if($this->user) {
                if(!$this->user->can($this->perms, true)) {
                    return false;
                }
            } else {
                return false;
            }
        }
        return true;
    }

    private function parseAuthString($authString) {
        $this->resetParams();
        $rules = explode('+', $authString);
        foreach ($rules as $key => $rule) {
            $stRule = str_replace(' ', '', $rule);
            if(strpos($stRule, 'P')===0) {
                $this->perms = $this->normalParse($stRule, 'P');
            } else if(strpos($stRule, 'R')===0) {
                $this->roles = $this->normalParse($stRule, 'R');
            }
        }
    }

    private function normalParse($stRule, $type) {
        $e = '&';
        $l = strlen($type);
        $sr = trim(substr($stRule, $l), '()');
        return explode($e, $sr);
    }

    private function authError() {
        if($this->isXml) {
            Printer::json(['status'=>WebError::NOPERM, 'info'=>'权限不足！']);
        } else {
            die('没有权限!');
        }
    }

    private function resetParams() {
        $this->roles = [];
        $this->perms = [];
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