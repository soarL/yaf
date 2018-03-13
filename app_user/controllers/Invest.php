<?php

/**
 * InvestController
 * 旧系统标的及投资数据
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class InvestController extends Controller {
	public $menu = 'odd';

	public function viewAction($num=0) {
		$num = htmlspecialchars($num);
		$this->redirect(WEB_MAIN.'/invest/a'.$num.'.html');
	}
}
