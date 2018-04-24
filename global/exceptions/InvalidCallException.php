<?php
namespace exceptions;

use base\Exception;

class InvalidCallException extends Exception {
	
    public function getName() {
        return 'Invalid Call';
    }
}
