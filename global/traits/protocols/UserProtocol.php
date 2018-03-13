<?php
namespace traits\protocols;

use plugins\tcpdf\PDF;
use plugins\tcpdf\TCPDFConfig;
use exceptions\HttpException;
use helpers\StringHelper;
use helpers\FileHelper;
use helpers\PDFHelper;
use tools\Config;

trait UserProtocol {
    /**
     * 生成用户合同(PDF)
     * @param  string  $output 输出类型
     * @return mixed
     */
	private function generateUser($output='D') {
		$pdf = PDFHelper::getProtocolPDF('用户协议');
        $params = '';
        FileHelper::txt2pdf($pdf, 'protocols/user.txt', $params);
        $pdf->Ln();
        $pdf->writeHTML('<div style="color:#fff;text-align: right;"><span>电子签章公司章专用1</span></div>', true, false, true, false, '');
        $pdf->Ln();
        $txt = '二0一六年四月二十二日';
        $pdf->Write(6, $txt, '', 0, 'R', true, 0, false, false, 0);
        
        //Close and output PDF document
        if($output=='D') {
            $file = 'user_protocol.pdf';
            $pdf->Output($file, 'D');
            return $file;
        } else if($output=='F') {
            $fileName = 'user_protocol.pdf';
            $file = APP_PATH.'/public/protocols/'.$fileName;
            if(!file_exists($file)) {
                $pdf->Output($file, 'F');
            }
            return $fileName;
        } else {
            die('获取合同失败！');
        }
        
	}
}