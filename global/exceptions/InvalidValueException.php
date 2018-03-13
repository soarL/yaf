<?php
namespace exceptions;

use base\Exception;

class InvalidValueException extends Exception {
	
    public function getName() {
        return 'Invalid Return Value';
    }
}
