<?php
use exceptions\UnknownPropertyException;
use exceptions\UnknownMethodException;
class Attributes {
	protected $_params;

	public function __construct(array $params = array()) {
		$this->_params = $params;
	}
    
	public function __get($name) {
		if(isset($this->_params[$name])) {
			return $this->_params[$name];
		} else {
            return null;
		}
    }

    public function __set($name, $value) {
        $this->_params[$name] = $value;
    	/*if(isset($this->_params[$name])) {
    		$this->_params[$name] = $value;
    	} else {
    		throw new UnknownPropertyException('Setting unknown property: ' . get_class($this) . '::' . $name);
    	}*/
    }

    public function __isset($name) {
        if (isset($this->_params[$name])) {
            return $this->_params[$name] !== null;
        } else {
            return false;
        }
    }

    public function __unset($name) {
        if (isset($this->_params[$name])) {
            $this->_params[$name] = null;
        }
    }

    public function addAttribute($name, $value) {
        $this->_params[$name] = $value;
    }

    public function hasProperty($name) {
        return isset($this->_params[$name]);
    }

    public function hasMethod($name) {
        return method_exists($this, $name);
    }

    public function __call($name, $params) {
        throw new UnknownMethodException('Calling unknown method: ' . get_class($this) . "::$name()");
    }
}