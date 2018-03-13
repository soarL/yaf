<?php
namespace exceptions;

class UnknownMethodException extends \BadMethodCallException {
	
    public function getName() {
        return 'Unknown Method';
    }
}
