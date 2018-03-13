<?php
use traits\handles\ITFAuthHandle;
use tools\Banks;
/**
 * BanksAction
 * APP地区数据
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class BanksAction extends Action {
    use ITFAuthHandle;

    public function execute() {
    	$params = $this->getAllQuery();
        $this->authenticate($params);

        $records = Banks::getBanks('banks');

        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data']['records'] = $records;
        $this->backJson($rdata);
    }
}