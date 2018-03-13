<?php
use models\FreQuestion;
use traits\handles\ITFAuthHandle;

/**
 * HelpListAction
 * APP帮助中心接口
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class HelpListAction extends Action {
    use ITFAuthHandle;

    public function execute() {
    	$params = $this->getAllQuery();
    	$this->authenticate($params);

        $type = $this->getQuery('type', 'all');

        $builder = FreQuestion::whereRaw('1=1');

        if($type!='all') {
            $builder->where('type', $type);
        }

        $questions = $builder->get()->toArray();

        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data']['records'] = $questions;
        $this->backJson($rdata);
    }
}