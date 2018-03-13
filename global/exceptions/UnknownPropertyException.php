<?php
namespace exceptions;

use base\Exception;

class UnknownPropertyException extends Exception {
	
    public function getName() {
        return 'Unknown Property';
    }
}
