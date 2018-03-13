<?php
use models\OddInfo;
use helpers\StringHelper;
use traits\handles\ITFAuthHandle;

/**
 * OddRMAction
 * APP标的风险控制信息
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class OddRMAction extends Action {
    use ITFAuthHandle;

    public function execute() {
    	$params = $this->getAllQuery();
        $this->authenticate($params, ['oddNumber'=>'标的号']);

        $oddNumber = $params['oddNumber'];

        $select = ['oddNumber', 'otherPhotos', 'oddPropertyPhotos'];

        $oddInfo = OddInfo::where('oddNumber', $oddNumber)->first($select);
        if(!$oddInfo){
            $oddInfo = new OddInfo();
        }
        // 借款资料
        $controlVars = explode(',', $oddInfo->oddLoanControlList);
        $controlVars = $controlVars?$controlVars:[];

        // 产权图片
        $oddPropertyPhotos = $oddInfo->getImages('oddPropertyPhotos');

        // 借款手续
        $otherPhotos = $oddInfo->getImages('otherPhotos');

        // 风控图片
        //$controlPhotos = $oddInfo->getImages('controlPhotos');

        // 验车图片
        //$validateCarPhotos = $oddInfo->getImages('validateCarPhotos');

        // 央行征信
        $bankCreditReport = $oddInfo->getImages('bankCreditReport');

        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data']['oddNumber'] = $oddNumber;
        $rdata['data']['controlVars'] = $controlVars;
        $rdata['data']['oddPropertyPhotos'] = $oddPropertyPhotos;
        $rdata['data']['otherPhotos'] = $otherPhotos;
        //$rdata['data']['controlPhotos'] = $controlPhotos;
        //$rdata['data']['validateCarPhotos'] = $validateCarPhotos;
        $rdata['data']['bankCreditReport'] = $bankCreditReport;
        $rdata['data']['oddLoanControl'] = $oddInfo->oddLoanControl;
        $this->backJson($rdata);
    }
}