<?php
namespace exceptions;

use base\Exception;

class InvalidConfigException extends Exception {
	
    public function getName() {
        return 'Invalid Configuration';
    }
}
