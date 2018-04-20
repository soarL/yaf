<?php
use models\User;

class IndexController extends Controller {
	public function indexAction() {//默认Action
		$data = User::get();
		header( "HTTP/1.1 502 Not Found" );
		$this->display('index',['data'=>$data]);
	}
}