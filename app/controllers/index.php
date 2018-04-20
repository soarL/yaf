<?php
use models\User;
use Illuminate\Database\Capsule\Manager as DB;

class IndexController extends Controller {
	public function indexAction() {//默认Action
		$data = User::get();

		$this->display('index',['data'=>$data]);
	}
}
