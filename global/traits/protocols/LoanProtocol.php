<?php
namespace traits\protocols;

use TCPDF;
use plugins\tcpdf\TCPDFConfig;
use exceptions\HttpException;
use helpers\StringHelper;
use helpers\PDFHelper;
use helpers\FileHelper;
use tools\Config;

trait LoanProtocol {
    /**
     * 生成借款合同(PDF)
     * @param  models/OddMoney  $oddMoney  投资信息
     * @param  string           $output    输出类型
     * @param  boolean          $seal      是否盖章
     * @return mixed
     */
	private function generateLoan($oddMoney, $output='D', $seal=true, $type='') {
		$pdf = PDFHelper::getProtocolPDF();
        $params = $oddMoney->getProtocolInfo($type);

        $tmpl = $params['tmpl'];
        FileHelper::txt2pdf($pdf, 'protocols/'.$tmpl.'.txt', $params);
        if(!$seal) {
            $pdf->Ln();
            //$pdf->writeHTML('<div style="color:#fff;text-align: right;"><span>汇诚普惠平台章</span><span>&nbsp&nbsp</span></div>', true, false, true, false, '');
        }

        if($seal) {
            $html = '<div style="text-align: right;"><img src="'.WEB_ASSET.'/common/images/gongzhan.jpg" style="border:none;height:148px; width:148px;"/></div>';
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Write(6, '福州汇诚金融外包服务有限公司', '', 0, 'R', true, 0, false, false, 0);
        }
        $txt = _date('Y年m月d日', $params['rehearTime']);
        $pdf->Write(6, $txt, '', 0, 'R', true, 0, false, false, 0);
        //Close and output PDF document
        if($output=='D') {
            $file = $oddMoney->tradeNo.'_protocol.pdf';
            $pdf->Output($file, 'D');
            return $file;
        } else if($output=='F') {
            $ex = $type?'H':'';
            $fileName = $ex.$oddMoney->tradeNo.'_protocol.pdf';
            $file = APP_PATH.'/../app/public/protocols/'.$fileName;
            $pdf->Output($file, 'F');
            return $fileName;
        } else {
            die('获取合同失败！');
        }
        
	}

    /**
     * 生成借款协议(PDF)
     * @param  models/OddMoney  $oddMoney  投资信息
     * @param  string           $output    输出类型
     * @param  boolean          $seal      是否盖章
     * @return mixed
     */
    private function generateLoanAdd($oddMoney, $output='D', $seal=true, $type = '') {
        $pdf = PDFHelper::getProtocolPDF();
        $params = $oddMoney->getProtocolInfoAdd($type);

        $tmpl = $params['tmpl'];
        FileHelper::txt2pdf($pdf, 'protocols/'.$tmpl.'.txt', $params);
      
        // $txt = '乙方：'.$params[''];
        // $pdf->Write(15, $txt, '', 0, 'L', true, 0, false, false, 0);
        $pdf->AddPage();
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Ln();
        // $pdf->writeHTML('<div><span>乙方：'.$params['needname'].'
        // </span><span style="color:#fff;">&nbsp&nbsp&nbsp&nbsp&nbsp</span><span>丙方：福州汇诚金融外包服务有限公司</span></div><div><span style="color:#fff;">电子签章投资章专用</span> <span style="color:#fff;">&nbsp&nbsp&nbsp&nbsp&nsp&nb</span>
        // <span style="color:#fff;">汇诚普惠平台章</span></div><div><span>日期：'._date('Y年m月d日', $params['rehearTime']).'</span><span style="color:#fff;">&nbsp&nbsp&nbsp&nb</span><span>日期：'._date('Y年m月d日', $params['rehearTime']).'</span></div>', true, false, true, false, '');

        // $txt = '日期：'._date('Y年m月d日', $params['rehearTime']);
        // $pdf->Write(15, $txt, '', 0, 'L', true, 0, false, false, 0);

        // $txt = '丙方：福州汇诚金融外包服务有限公司';
        // $pdf->Write(15, $txt, '', 0, 'L', true, 0, false, false, 0);
        // if(!$seal) {
        //     $pdf->Ln();
        //     $pdf->writeHTML('<div style="color:#fff;text-align: right;"><span>汇诚普惠平台章</span></div>', true, false, true, false, '');
        // }

        // $txt = '日期：'._date('Y年m月d日', $params['rehearTime']);
        // $pdf->Write(15, $txt, '', 0, 'L', true, 0, false, false, 0);

        // $txt = _date('Y年m月d日', $params['rehearTime']);
        // $pdf->Write(6, $txt, '', 0, 'R', true, 0, false, false, 0);
        //Close and output PDF document
        if($output=='D') {
            $file = $oddMoney->tradeNo.'_protocol.pdf';
            $pdf->Output($file, 'D');
            return $file;
        } else if($output=='F') {
            $ex = $type?'H':'';
            $fileName = $ex.'A'.$oddMoney->tradeNo.'_protocol.pdf';
            $file = APP_PATH.'/../app/public/protocols/'.$fileName;
            $pdf->Output($file, 'F');
            return $fileName;
        } else {
            die('获取合同失败！');
        }
        
    }

}