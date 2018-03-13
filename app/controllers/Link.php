<?php
// This controller use illuminate/database.
use models\Link;

/**
 * NewsController
 * 新闻资讯控制器
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class LinkController extends Controller {
	public $menu = 'link';
	public $submenu = 'index';

	public function indexAction() {
		$this->submenu = 'index';
		$links = Link::where('link_status', 1)->where('link_type', 'link')->orderBy('link_sort', 'desc')->get();
		$this->display('index', ['links'=>$links]);
	}
}
