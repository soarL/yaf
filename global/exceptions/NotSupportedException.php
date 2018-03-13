<?php
namespace exceptions;

use base\Exception;

class NotSupportedException extends Exception {
	
    public function getName() {
        return 'Not Supported';
    }
}
