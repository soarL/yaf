<?php
namespace business;

use business\AITool;
use task\Handler as BaseHandler;

/**
 * 自动投标的工具类
 * params
 *     odds 标的号数组
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class AutoBidHandler extends BaseHandler {
    public function handle(){
        $list = isset($this->params['odds'])?$this->params['odds']:[];

        $result = AITool::runBatch($list);

        $rdata['status'] = true;
        $rdata['msg'] = $result['msg'];
        return $rdata;
    }
}
