<?php
namespace exceptions;

use base\Exception;

class SqlExecuteErrorException extends Exception {
	
    public function getName() {
        return 'Sql Execute Error';
    }
}
