<?php
namespace traits;

use Yaf\Dispatcher;
use Yaf\Registry;
use exceptions\Exception;
use exceptions\HttpException;
use exceptions\UserException;
use tools\Printer;

trait ErrorActions {
    
    public function init() {
        Dispatcher::getInstance()->disableView();
    }

    public function errorAction($exception) {      
        // $request = Dispatcher::getInstance()->getRequest();
        $siteinfo = Registry::get('siteinfo');
        $request = $this->getRequest();
        _dd($request);
        $method = strtolower($request->getMethod());
        $data = [];

        if ($exception instanceof HttpException) {
            $code = $exception->statusCode;
            header("http/1.1 " . $code . " " . $exception->getName()); 
            header("status: " . $code . " " . $exception->getName());
        } else {
            $code = $exception->getCode();
        }

        if ($exception instanceof UserException) {
            $message = $exception->getMessage();
        } else {
            if($code==\YAF\ERR\NOTFOUND\MODULE||$code==\YAF\ERR\NOTFOUND\CONTROLLER||$code==\YAF\ERR\NOTFOUND\ACTION||$code==\YAF\ERR\NOTFOUND\VIEW) {
                header("http/1.1 404 not found"); 
                header("status: 404 not found");
                $code = 404;
            } else {
                header("http/1.1 500 Internal Server Error"); 
                header("status: 500 Internal Server Error");
                $code = 500;
            }
        }

        if($code==404) {
            if($method=='cli') {
                echo 'Error! This command doesn\'t exist!';
                exit(0);
            } else {
                $this->display('404');
            }
        } else if($code==503) {
            $this->display('503');
        } else {
            if(APP_ENV=='product') {
                if($method=='cli') {
                    echo 'Error! Program internal error!';exit(0);
                } else {
                    $this->display('500');
                }
            } else {
                if($method=='cli') {
                    print_r($exception);
                    exit(0);
                } else {
                    Printer::pretty($exception, true);
                }
            }
        }
    }
}