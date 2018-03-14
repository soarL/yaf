<?php
use models\User;
use Illuminate\Database\Capsule\Manager as DB;

class IndexController extends Controller {
	public function indexAction() {//默认Action
		$users = User::where('id','>',1)->orderBy('id','desc')->get();
		foreach ($users as $key => $value) {
			echo "<br>";
			echo $value['id'];
			echo "<br>";
			echo $value['username'];
			echo "<br>";
			echo $value['password'];
		};
		exit;
		$this->display('index',["users"=>$users]);
	}
}