<?php
namespace traits\protocols;
use plugins\tcpdf\PDF;
use plugins\tcpdf\TCPDFConfig;
use exceptions\HttpException;
use helpers\StringHelper;
use helpers\PDFHelper;
use helpers\FileHelper;
use tools\Config;

trait LeaseProtocol {
	/**
     * 生成融资租赁合同(PDF)
     * @param  models/OddMoney  $oddMoney   投资信息
     * @param  string           $output     输出类型
     * @param  boolean          $seal       是否盖章
     * @return mixed
     */
	private function generateLease($oddMoney, $output='D', $seal=true) {
		$pdf = PDFHelper::getProtocolPDF();
        $params = $oddMoney->getProtocolInfo();
        FileHelper::txt2pdf($pdf, 'protocols/lease.txt', $params);
        
        if(!$seal) {
            $pdf->writeHTML('<div style="color:#fff;text-align: right;"><span>电子签章公司章专用1</span><span>电子签章公司章专用2</span></div>', true, false, true, false, '');
            $pdf->Ln();
        }
        $pdf->Ln();
        if($seal) {
            $html = '<div style="text-align: right;"><img src="'.WEB_ASSET.'/common/images/gongzhang1.png" style="border:none;height:148px; width:290px;"/></div>';
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Ln();
            $html = '<div style="text-align: right;"><img src="'.WEB_ASSET.'/common/images/gongzhang.png" style="border:none;height:148px; width:290px;"/></div>';
            $pdf->writeHTML($html, true, false, true, false, '');
        }
        $txt = _date('Y年m月d日', $oddMoney->time);
        $pdf->Write(6, $txt, '', 0, 'R', true, 0, false, false, 0);
        //Close and output PDF document
        if($output=='D') {
            $file = $oddMoney->tradeNo.'_protocol.pdf';
            $pdf->Output($file, 'D');
            return $file;
        } else if($output=='F') {
            $fileName = $oddMoney->tradeNo.'_protocol.pdf';
            $file = APP_PATH.'/../app/public/protocols/'.$fileName;
            $pdf->Output($file, 'F');
            return $fileName;
        } else {
            die('获取合同失败！');
        }
        
	}
}