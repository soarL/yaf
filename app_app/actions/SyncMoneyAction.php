<?php
use models\User;
use custody\Handler;
use custody\API;
use traits\handles\ITFAuthHandle;

/**
 * SyncMoneyAction
 * 同步资金接口
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class SyncMoneyAction extends Action {
    use ITFAuthHandle;

    public function execute() {
        $params = $this->getAllQuery();
        $this->authenticate($params, ['userId'=>'用户ID']);

        $user = $this->getUser();
        if($user->custody_id=='') {
            $rdata['status'] = 1;
            $rdata['info'] = '用户未开通存管！';
            $this->backJson($rdata); 
        }

        $data = [];
        $data['accountId'] = $user->custody_id;
        $data['startDate'] = date('Ymd', time()-2*24*3600);
        $data['endDate'] = date('Ymd');
        $data['type'] = 9;
        $data['tranType'] = '7820';
        $data['pageNum'] = 1;
        $data['pageSize'] = 50;
        $handler = new Handler('accountDetailsQuery', $data);
        $result = $handler->api();
        if($result['retCode']==Handler::SUCCESS) {
            $list = json_decode($result['subPacks'], true);
            foreach ($list as $item) {
                $params = [];
                $params['tradeNo'] = $item['inpDate'].$item['inpTime'].$item['traceNo'];
                $params['cid'] = $item['accountId'];
                $params['money'] = $item['txAmount'];
                $params['flag'] = $item['txFlag'];
                $params['tranType'] = $item['tranType'];
                API::syncLog($params);
            }
        }

        $result = API::syncMoney($user);
        if($result['status']) {
            $rdata['status'] = 1;
            $rdata['msg'] = $result['msg'];
            $rdata['data'] = $result['data'];
            $this->backJson($rdata); 
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $result['msg'];
            $this->backJson($rdata); 
        }
    }
}