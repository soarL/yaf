<?php
namespace exceptions;

use base\Exception;

class UnknownClassException extends Exception {
	
    public function getName() {
        return 'Unknown Class';
    }
}
