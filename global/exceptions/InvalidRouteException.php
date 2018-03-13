<?php
namespace exceptions;

use base\Exception;

class InvalidRouteException extends Exception {
	
    public function getName() {
        return 'Invalid Route';
    }
}
