<?php
use models\User;
use Illuminate\Database\Capsule\Manager as DB;

class IndexController extends Controller {
	public function indexAction() {//默认Action
		$users = User::where('id','>',1)->orderBy('id','desc')->get();
		$this->display('index',["users"=>$users]);
	}
}