<?php
use models\AuthAction;
/**
 * Admin
 * 后台控制器基类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Admin extends Controller {

    public function goHome() {
    	$this->redirect('/admin/index/index');
    }

    protected function displayParams() {
        $params = [];
        $params['keywords'] = '';
        $params['description'] = '';
        $params['menus'] = AuthAction::menus($this->getUser());
        $params['activeMenu'] = AuthAction::getActiveMenu($params['menus'], $this->menu);
        $params['title'] = AuthAction::getTitle($params['menus'], $this->menu, $this->submenu);
        return $params;
    }

}
