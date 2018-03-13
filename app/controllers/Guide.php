<?php
/**
 * GuideController
 * 新手指导控制器
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class GuideController extends Controller {
	public $menu = 'guide';
	public $submenu = 'guide';

	public function indexAction() {
		$this->submenu = 'guide';
		$this->display('index');
	}

	public function questionAction() {
		$this->submenu = 'question';
		$this->display('question');
	}

	public function helpAction() {
		$this->submenu = 'help';
		$this->display('help');
	}

}