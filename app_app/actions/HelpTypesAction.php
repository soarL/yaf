<?php
use models\FreQuestion;
use traits\handles\ITFAuthHandle;

/**
 * HelpTypesAction
 * APP帮助中心类型接口
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class HelpTypesAction extends Action {
    use ITFAuthHandle;

    public function execute() {
    	$params = $this->getAllQuery();
    	$this->authenticate($params);

        $types = FreQuestion::whereRaw('1=1')->groupBy('type')->get(['type'])->toArray();
        $list = [];
        foreach ($types as $type) {
            $list[] = $type['type'];
        }
        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data']['records'] = $list;
        $this->backJson($rdata);
    }
}