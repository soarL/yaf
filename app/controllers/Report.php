<?php
/**
 * ReportController
 * 运营报告
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class ReportController extends Controller {
    public $menu = 'data';
    public $submenu = 'data';

    public function dataAction() {
        $month = $this->getQuery('month', 'data');
        $this->display($month);
    }
}