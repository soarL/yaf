<?php
use models\User;
use Illuminate\Database\Capsule\Manager as DB;

class IndexController extends Controller {
	public function indexAction() {//Ĭ��Action
		$users = User::all()->toArray();
		$this->display('index',["users"=>$users]);
	}
}