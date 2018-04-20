<?php
use models\User;

class IndexController extends Controller {
	public function indexAction() {//默认Action
		$data = User::get();
		$this->display('index',['data'=>$data]);
	}
}
