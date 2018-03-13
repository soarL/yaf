<?php
use models\User;
use Illuminate\Database\Capsule\Manager as DB;

class IndexController extends Controller {
	public function indexAction() {//Ä¬ÈÏAction
		$users = User::all()->toArray();
		$this->display('index',["users"=>$users]);
	}
}