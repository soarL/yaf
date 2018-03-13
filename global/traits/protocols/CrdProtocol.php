<?php
namespace traits\protocols;

use plugins\tcpdf\PDF;
use plugins\tcpdf\TCPDFConfig;
use exceptions\HttpException;
use helpers\StringHelper;
use helpers\FileHelper;
use helpers\PDFHelper;
use tools\Config;

trait CrdProtocol {
    /**
     * 生成个人信贷合同(PDF)
     * @param  models/OddMoney  $oddMoney  投资信息
     * @param  models/Odd       $odd       借款信息
     * @param  string  $output 输出类型
     * @param  boolean  $seal  是否盖章
     * @return mixed
     */
	private function generateCrd($oddMoney, $output='D', $seal=true) {
		$pdf = PDFHelper::getProtocolPDF();
        $params = $oddMoney->getProtocolInfo();
        $tmpl = $params['tmpl'];
        FileHelper::txt2pdf($pdf, 'protocols/'.$tmpl.'.txt', $params);
        
        $pdf->Ln();
        $pdf->writeHTML('<div style="color:#fff;text-align: right;"><span>电子签章公司章专用1</span></div>', true, false, true, false, '');
        $pdf->Ln();

        // $txt = '甲方：';
        // $pdf->Write(15, $txt, '', 0, 'L', true, 0, false, false, 0);

        // $txt = '日期：'._date('Y年m月d日', $oddMoney->time);
        // $pdf->Write(15, $txt, '', 0, 'L', true, 0, false, false, 0);

        // $txt = '乙方（签字并按捺）：';
        // $pdf->Write(15, $txt, '', 0, 'L', true, 0, false, false, 0);

        // $txt = '日期：'._date('Y年m月d日', $oddMoney->time);
        // $pdf->Write(15, $txt, '', 0, 'L', true, 0, false, false, 0);

        // $txt = '丙方（盖章）：福建汇诚普惠科技发展有限公司';
        // $pdf->Write(15, $txt, '', 0, 'L', true, 0, false, false, 0);

        // $txt = '授权代表（签字）：';
        // $pdf->Write(15, $txt, '', 0, 'L', true, 0, false, false, 0);

        // $txt = '日期：'._date('Y年m月d日', $oddMoney->time);
        // $pdf->Write(15, $txt, '', 0, 'L', true, 0, false, false, 0);

        // $txt = '丁方（盖章）：福建省众鑫盈资产管理有限公司';
        // $pdf->Write(15, $txt, '', 0, 'L', true, 0, false, false, 0);

        // $txt = '授权代表（签字）：';
        // $pdf->Write(15, $txt, '', 0, 'L', true, 0, false, false, 0);
        
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