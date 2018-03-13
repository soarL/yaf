<?php
namespace tools;

class SpecailIni {
    private $config;
    private $request;
    private $module;
    private $controller;
    private $action;
    private $tags = [];
    private $exps = [];

    function __construct($config, $request) {
        $this->config = $config;
        $this->request = $request;
        $this->module = $request->getModuleName();
        $this->controller = $request->getControllerName();
        $this->action = $request->getActionName();
        $moduleConfig = $this->config->{$this->module};
        if($moduleConfig) {
            foreach ($moduleConfig as $key => $value) {
                if(strpos($key, '%')===0) {
                    $this->parseTag($key, $value);
                } else {
                    $this->parseExp($key, $value);
                }
            }
        }
    }

    public function getRules() {
        $rules = [];
        $moduleConfig = $this->config->{$this->module};
        if($moduleConfig) {
            foreach ($this->tags as $key => $tag) {
                $e = str_replace('%', '@', $key);
                if(isset($tag[$this->controller])&&$moduleConfig->$e) {
                    $actions = $tag[$this->controller]['actions'];
                    $type = $tag[$this->controller]['type'];
                    if($actions=='*') {
                        $rules[$e] = $moduleConfig->$e;
                    } else {
                        if($type=='forward'&&in_array($this->action, $actions)) {
                            $rules[$e] = $moduleConfig->$e;
                        } else if($type=='reverse'&&!in_array($this->action, $actions)) {
                            $rules[$e] = $moduleConfig->$e;
                        }
                    }
                }
            }
            if($moduleConfig['*']) {
                $rules['*'] = $moduleConfig['*'];
            }
            $controller = $this->controller;
            if($moduleConfig->$controller) {
                $rules[$controller] = $moduleConfig->$controller;
            }
            $action = $this->controller . '@' . $this->action;
            if($moduleConfig->$action) {
                $rules[$action] = $moduleConfig->$action;
            }
        }
        return $rules;
    }

    private function parseExp($name, $value) {
        $this->exps[$name] = $value;
    }

    private function parseTag($name, $value) {
        $string = str_replace(' ', '', $value);
        preg_match_all('/(^|[-\+]{1})\w*(\[.*?\])?/', $string, $matchs);
        foreach ($matchs[0] as $str) {
            $epaResult = $this->epa($str);
            $this->tags[$name][$epaResult[0]] = ['actions'=>$epaResult[1], 'type'=>$epaResult[2]];
        }
    }

    private function epa($str) {
        $result = preg_match('/\[.*\]/', $str, $matchs);
        $bef = '';
        $aft = '';
        $type = '';
        if($result) {
            $bef = str_replace($matchs[0], '', $str);
            $aft = $this->arrtolower(explode(',', trim($matchs[0], '[]')));
        } else {
            $bef = $str;
            $aft = '*';
        }
        if(strpos($bef, '+')===0) {
            $bef = str_replace('+', '', $bef);
            $type = 'forward';
        } else if(strpos($bef, '-')===0) {
            $bef = str_replace('-', '', $bef);
            $type = 'reverse';
        } else {
            $type = 'forward';
        }
        return [$bef, $aft, $type];
    }

    private function arrtolower($array) {
        $lowerArray = [];
        foreach ($array as $value) {
            $lowerArray[] = strtolower($value);
        }
        return $lowerArray;
    }
}