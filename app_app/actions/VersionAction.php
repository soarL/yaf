<?php
use models\OddMoney;
use traits\handles\ITFAuthHandle;

/**
 * VersionAction
 * 版本信息
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class VersionAction extends Action {
    use ITFAuthHandle;

    public function execute() {
        $params = $this->getAllQuery();
        $this->authenticate($params);

		$rdata = [];
		$rdata['status'] = 1;
		$rdata['msg'] = '获取成功！';
		$rdata['data']['vCode'] = 2017091101;
		$rdata['data']['downloadUrl'] = 'https://asset.hcjrfw.com/common/app/xwsd_v2.5.apk';
        $rdata['data']['content'] = '[版本更新] 新增功能';
        $this->backJson($rdata);
    }
}