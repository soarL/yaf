<?php 
namespace exceptions;

use base\Exception;

class InvalidParamException extends \BadMethodCallException {
    public function getName() {
        return 'Invalid Parameter';
    }
}