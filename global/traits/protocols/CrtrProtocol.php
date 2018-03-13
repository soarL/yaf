<?php
namespace traits\protocols;

use plugins\tcpdf\PDF;
use plugins\tcpdf\TCPDFConfig;
use exceptions\HttpException;
use helpers\StringHelper;
use helpers\FileHelper;
use helpers\PDFHelper;
use tools\Config;

trait CrtrProtocol {
    /**
     * 生成债权转让合同(PDF)
     * @param  Model $oddMoney 合同信息
     * @param  string  $output 输出类型
     * @param  boolean  $seal  是否盖章
     * @return mixed
     */
	private function generateCrtr($oddMoney, $output='D', $seal=true, $type='') {
        $pdf = PDFHelper::getProtocolPDF();
        $params = $oddMoney->getProtocolInfo($type);
        FileHelper::txt2pdf($pdf, 'protocols/crtr.txt', $params);
        $pdf->Ln();
        $pdf->Ln();
        $pdf->writeHTML('<div style="color:#fff;text-align: right;"><span>电子签章公司章专用1</span></div>', true, false, true, false, '');
        $pdf->Ln();
        $pdf->Ln();

        $txt = '居间服务人：福州汇诚金融外包服务有限公司';
        $pdf->Write(6, $txt, '', 0, 'R', true, 0, false, false, 0);

        $txt = '签订时间：'. _date('Y年n月j日', $oddMoney->pcrtr->endtime);
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