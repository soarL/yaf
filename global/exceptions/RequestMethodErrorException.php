<?php
namespace exceptions;

use base\Exception;

class RequestMethodErrorException extends Exception {
	
    public function getName() {
        return 'Request Method Error';
    }
}