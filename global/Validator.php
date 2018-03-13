<?php
class Validator extends Object {
    protected $key;
    protected $value;
    protected $label;
    protected $params;
    protected $errors = [];

	public static $formValidators = [
        'boolean' => 'validators\BooleanValidator',
        'compare' => 'validators\CompareValidator',
        'date' => 'validators\DateValidator',
        'double' => 'validators\NumberValidator',
        'each' => 'validators\EachValidator',
        'email' => 'validators\EmailValidator',
        'exist' => 'validators\ExistValidator',
        'required' => 'validators\RequiredValidator',
        'captcha' => 'validators\CaptchaValidator',
        'chineseName' => 'validators\ChineseNameValidator',
        'phoneNumber' => 'validators\PhoneNumberValidator',
        'enum' => 'validators\EnumValidator',
        'type' => 'validators\TypeValidator',
        'idCard' => 'validators\IdCardValidator',
    ];

	public function __construct($value=null) {
        $this->value = $value;
	}

	public function validate() {

	}

    public function setLabel($label) {
        $this->label = $label;
    }

    public function setKey($key) {
        $this->key = $key;
    }

    public function setValue($value) {
        $this->value = $value;
    }

    public function setParams($params) {
        $this->params = $params;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function addError($error) {
        $this->errors[] = $error;
    }

    public function cleanErrors() {
        $this->errors = [];
    }

    public function hasError() {
        return count($this->errors)>0?true:false;
    }
}
