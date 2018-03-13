<?php
use tools\Areas;
use traits\handles\ITFAuthHandle;

/**
 * AreasAction
 * APP地区数据
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class AreasAction extends Action {
    use ITFAuthHandle;
    
    public function execute() {
    	$params = $this->getAllQuery();
        $this->authenticate($params);

        $id = $this->getQuery('id', 0);
        $records = [];
        if($id==0) {
            $records = Areas::getProvinces();
        } else {
            $records = Areas::getCitys($id);
        }

        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data']['records'] = $records;
        $this->backJson($rdata);
    }
}