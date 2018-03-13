<?php
namespace task\handlers;

use task\Handler;
use custody\API;
use models\User;
use tools\Log;

/**
 * RedpackHandler
 * 红包发送处理者
 *
 * params:
 *     redpacks[
 *         [userId, money, type, remark]
 *     ]  
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class RedpackHandler extends Handler {

    public function handle() {
        $redpacks = isset($this->params['redpacks'])?$this->params['redpacks']:[];
        $rdata = [];
        if(count($redpacks)>150) {
            $rdata['status'] = 0;
            $rdata['msg'] = '每次发送红包不能超过150个！';
            return $rdata;
        }
    
        foreach ($redpacks as $redpack) {
            API::redpack($redpack['userId'], $redpack['money'], $redpack['type'], $redpack['remark']);
        }

        $rdata['status'] = 1;
        $rdata['msg'] = '发放完成！';
        return $rdata;
    }
}