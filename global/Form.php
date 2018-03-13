<?php
use Yaf\Registry;
use exceptions\InvalidParamException;

/**
 * Form
 * 表单基类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Form extends Attributes {
    const SUCCESS = '0000';

    protected $_errors;
    protected $_user;
    protected $_media = 'PC';
    protected $_validators = [];
    protected $_failOut = true;
    protected $_errorParams = [];

    // 0000表示成功，其他表示失败（默认1000，若想对某些错误特殊处理请用其他代码）
    protected $_code = '0000';

    /**
     * 构造函数
     * @param array   $params  验证参数
     * @param boolean $failOut 验证失败时是否立即停止验证[注:即使该值为false，同一参数在验证失败后也不会进行其他验证]
     */
    public function __construct(array $params = array(), $failOut=true) {
        parent::__construct($params);
        $this->_user = Registry::get('user');
        $this->_failOut = $failOut;
        if($this->defaults()) {
            foreach ($this->defaults() as $key => $value) {
                if($this->$key===null || $this->$key==='') {
                    $this->$key = $value;
                }
            }
        }
        $this->init();
    }

    public function init() {
        
    }

    public function rules() {
        return [];
    }

    public function defaults() {
        return [];
    }

    public function labels() {
        return [];
    }

    /** 已失效 验证顺序依据rules中的数组顺序 */
    public function queue() {
        return [];
    }

    public function getUser() {
        return $this->_user;
    }

    public function setUser($user) {
        $this->_user = $user;
    }

    public function getMedia() {
        return $this->_media;
    }

    public function setMedia($media) {
        $this->_media = $media;
    }

    public function check() {
        foreach ($this->rules() as $rule) {
            if($this->_failOut && $this->getCode()!=self::SUCCESS) {
                break;
            }
            if(isset($rule[1])&&is_string($rule[1])) {
                $params = isset($rule[2])?$rule[2]:[];
                if(is_string($rule[0])) {
                    if(in_array($rule[0], $this->_errorParams)) {
                        continue;
                    }
                    $this->execute($rule[1], $rule[0], $params);
                } else if(is_array($rule[0])) {
                    foreach ($rule[0] as $attribute) {
                        if(in_array($attribute, $this->_errorParams)) {
                            continue;
                        }
                        $this->execute($rule[1], $attribute, $params);
                    }
                } else {
                    throw new Exception('参数类型错误!');
                }   
            } else {
                throw new Exception('参数类型错误!');
            }
        }
        if($this->getCode()==self::SUCCESS) {
            return true;
        } else {
            return false;
        }
    }

    private function execute($validatorName, $attribute, $params=[]) {
        $validator = $this->getValidator($validatorName);
        if($validator instanceof Validator) {
            $validator->setKey($attribute);
            $validator->setValue($this->$attribute);
            $validator->setParams($params);
            $validator->setLabel($this->getLabel($attribute));
            $validator->cleanErrors();
            if(!$validator->validate()) {
                foreach ($validator->getErrors() as $error) {
                    $this->addError($attribute, $error);
                }
            }
        } else if(is_string($validator)) {
            call_user_func_array(array($this, $validator), $params);
        } else {
            throw new Exception('Validator is error!', 1);
        }
    }

    private function getValidator($validatorName) {
        if(isset($this->_validators[$validatorName])) {
            return $this->_validators[$validatorName];
        }
        $validators = Validator::$formValidators;
        if(isset($validators[$validatorName])) {
            $validatorClassName = $validators[$validatorName];
            $this->_validators[$validatorName] = new $validatorClassName();
            return $this->_validators[$validatorName];
        } else {
            if(method_exists($this, $validatorName)) {
                $this->_validators[$validatorName] = $validatorName;
                return $this->_validators[$validatorName];
            } else {
                throw new Exception('Can not get the validator of ' . $validatorName, 1);
            }
        }
    }
    
    public function addError($param, $error) {
        $this->_errorParams[] = $param;
        $this->_errors[$param][] = $error;
        $this->setCode('1000');
    }

    public function getErrors($param='') {
        if($param!='') {
            return $this->_errors[$param];
        }
        return $this->_errors;
    }

    public function posError() {
        return pos(pos($this->_errors));
    }

    public function hasErrors($param='') {
        if($param=='') {
            return count($this->_errors)>0?true:false;
        } else {
            $errors = $this->_errors;
            if(isset($errors[$param])&&count($errors[$param])>0) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function getLabel($attribute) {
        $labels = $this->labels();
        if(isset($labels[$attribute])) {
            return $labels[$attribute];
        }
        return ucwords($attribute);
    }

    public function getCode() {
        return $this->_code;
    }

    public function setCode($code) {
        return $this->_code = $code;
    }
}