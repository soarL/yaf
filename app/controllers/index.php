<?php
use models\User;

class IndexController extends Controller {
	public function indexAction() {//默认Action
		$data = User::get();
		_dd($data);
		$this->display('index',['data'=>$data]);
	}
}
