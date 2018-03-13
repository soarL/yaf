<?php
use Yaf\Controller_Abstract;
class IndexController extends Controller_Abstract {

   public function indexAction() {//默认Action
       $this->display("index", ['content'=>'hello world']);
   }
   public function aboutAction(){
   		$data =["xs"=>"asdfasd","aasd"=>"xsxss"];
   		echo json_encode($data);
   }
}


?>