<?php
class AboutController extends Controller
{
	public function indexAction(){
		$this->display('index',['mulu'=>'xx']);
	}
	public function formAction(){
		$this->display('form');
	}
	public function formdataAction(){
		$username = $this->getRequest()->getPost('username');
		$password = $this->getRequest()->getPost('password');
		$data = [
			'username'=>$username,
			'password'=>$password,
		]; 
		$this->display('result',$data);
	}
}