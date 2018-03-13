<?php
namespace traits\protocols;

use plugins\tcpdf\PDF;
use plugins\tcpdf\TCPDFConfig;
use exceptions\HttpException;
use helpers\StringHelper;
use tools\Config;

trait AssignmentProtocol {
    /**
     * 生成债权转让合同(PDF)
     * @param  arrat $protocolData 合同信息
     * @param  string  $output 输出类型
     */
	private function generateAssignment($protocolData, $output='D') {
		$pdf = new PDF(TCPDFConfig::get('PDF_PAGE_ORIENTATION'), TCPDFConfig::get('PDF_UNIT'), TCPDFConfig::get('PDF_PAGE_FORMAT'), true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(TCPDFConfig::get('PDF_CREATOR'));
        $pdf->SetAuthor('汇诚普惠');
        $pdf->SetTitle('债权转让合同');
        $pdf->SetSubject('债权转让合同');
        $pdf->SetKeywords('汇诚普惠, 债权转让, 合同');

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(TCPDFConfig::get('PDF_FONT_MONOSPACED'));

        // set margins
        $pdf->SetMargins(TCPDFConfig::get('PDF_MARGIN_LEFT'), 8, TCPDFConfig::get('PDF_MARGIN_RIGHT'));

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, TCPDFConfig::get('PDF_MARGIN_BOTTOM'));

        // set image scale factor
        $pdf->setImageScale(TCPDFConfig::get('PDF_IMAGE_SCALE_RATIO'));

        // set font
        $pdf->SetFont('stsongstdlight', '', 20);

        // add a page
        $pdf->AddPage();

        // set some text to print
        $pdf->setJPEGQuality(0);
        $pdf->Image(WEB_ASSET.'/common/images/xwsdlogo.jpg', '', '', 40, 0, 'JPG', '', 'T', false, 10, '', false, false, 0, false, false, false);
        $txt = '汇诚普惠电子借款合同';
        // print a block of text using Write()
        $pdf->SetFontSize(15);
        $pdf->SetTextColor(119,119,119);
        $pdf->Write(15, $txt, '', 0, 'R', true, 0, false, false, 0);

        $txt = '债权转让协议书';
        $pdf->SetTextColor(102,102,102);
        $pdf->SetFontSize(18);
        $pdf->Write(15, $txt, '', 0, 'C', true, 0, false, false, 0);

        $txt = '甲方（出借人）： ';
        $pdf->SetTextColor(119,119,119);
        $pdf->SetFontSize(10);
        $pdf->Write(10, $txt, '', 0, 'L', true, 0, false, false, 0);
        $header = ['投标标题', '利率', '转让价格', '转让时间', '购买者'];
        $data = [[$protocolData['oddTitle'], ($protocolData['oddYearRate']*100).'%', $protocolData['money'], $protocolData['time'], $protocolData['buyUser']]];
        $pdf->protocolTwoTable($header,$data);

        // $pdf->SetFont('stsongstdlight', '', 20);
        $txt = '（注：因计算中存在四舍五入，最后一期应收本息与之前略有不同！）';
        $pdf->SetTextColor(255,0,0);
        $pdf->SetFontSize(8);
        $pdf->Write(0, $txt, '', 0, 'L', true, 0, false, false, 0);

        $pdf->Ln();

        // $pdf->SetTextColor(204,204,204);
        $pdf->SetTextColor(119,119,119);
        // $pdf->SetTextColor(51,51,51);
        $pdf->SetFontSize(10);
        $txt = '协议号：'.$protocolData['proSerial'];
        $pdf->Write(10, $txt, '', 0, 'L', true, 0, false, false, 0);
        $txt = '转让人（以下简称甲方）： '.$protocolData['sellUser'];
        $pdf->Write(10, $txt, '', 0, 'L', true, 0, false, false, 0);
        $txt = '受让人（以下简称乙方）： '.$protocolData['buyUser'];
        $pdf->Write(10, $txt, '', 0, 'L', true, 0, false, false, 0);
        $txt = '债务人： '.$protocolData['oddUser'];
        $pdf->Write(10, $txt, '', 0, 'L', true, 0, false, false, 0);
        $txt = '平台方：福建汇诚普惠科技发展有限公司';
        $pdf->Write(10, $txt, '', 0, 'L', true, 0, false, false, 0);
        
        $pdf->Ln();

        $txt = '鉴于甲方在福建汇诚普惠科技发展有限公司运营管理的"汇诚普惠"网站平台（以下简称平台）上对债务人拥有合法债权，现甲方将其债权通过平台转让给乙方。双方达成如下协议：';
        $pdf->Write(6, $txt, '', 0, 'L', true, 0, false, false, 0);

        $txt = '01、转让债权标的：转让债权标的即甲方发标时的金额（债权本金及利息）';
        $pdf->Write(6, $txt, '', 0, 'L', true, 0, false, false, 0);
        $txt = '02、债权转让价格：转让债权标的即甲方发标时的价格。乙方应一次性支付转让价款。';
        $pdf->Write(6, $txt, '', 0, 'L', true, 0, false, false, 0);
        $txt = '03、本债权自本协议生效之日起转移，甲方的所有权利义务同时转移给乙方。甲乙双方具有向债务人送达债权转让通知的义务。';
        $pdf->Write(6, $txt, '', 0, 'L', true, 0, false, false, 0);
        $txt = '04、甲方因转让债权而产生的相关费用具体收费标准详见"资费说明"。各项费用的调整，以公告的"资费说明"为准，并对调整后发生的借款发生效力。';
        $pdf->Write(6, $txt, '', 0, 'L', true, 0, false, false, 0);
        $txt = '05、乙方承继甲方债权后可以再转让给第三人。';
        $pdf->Write(6, $txt, '', 0, 'L', true, 0, false, false, 0);
        $txt = '06、乙方保证其支付受让标的债权的资金来源合法，乙方是该资金的合法所有人，如果第三方对资金归属、合法性问题发生争议，由乙方自行负责解决。';
        $pdf->Write(6, $txt, '', 0, 'L', true, 0, false, false, 0);
        $txt = '07、甲乙双方任何一方违反本协议的约定，使得本协议的全部或部分不能履行，均应承担违约责任，并赔偿对方因此遭受的损失（包括但不限于由此产生的诉讼费和律师费）。';
        $pdf->Write(6, $txt, '', 0, 'L', true, 0, false, false, 0);
        $txt = '08、在本协议履行过程中，如发生任何争执或纠纷，且协商不成的，双方约定向平台住所地人民法院提起诉讼。';
        $pdf->Write(6, $txt, '', 0, 'L', true, 0, false, false, 0);
        $txt = '09、甲方、乙方通过平台发标、投标转让债权时，视为接受本协议的条款，视为已认真阅读和理解本协议所有内容并自愿按本协议相关约定履行各自的权利义务。';
        $pdf->Write(6, $txt, '', 0, 'L', true, 0, false, false, 0);
        $txt = '10、本协议自转让资金支付到甲方账户起生效。';
        $pdf->Write(6, $txt, '', 0, 'L', true, 0, false, false, 0);

        $pdf->Ln();
        $pdf->Ln();
        $txt = _date('Y年m月d日', $protocolData['time']);
        $pdf->Write(6, $txt, '', 0, 'R', true, 0, false, false, 0);
        $pdf->setJPEGQuality(0);
        $pdf->Image(WEB_ASSET.'/common/images/gongzhang.png', 144, 240, 50, 0, 'PNG', '', 'T', false, 10, '', false, false, 0, false, false, false);
        //Close and output PDF document
        if($output=='D') {
            $file = $protocolData['proSerial'].'_protocol.pdf';
            $pdf->Output($file, 'D');
            return $file;
        } else if($output=='F') {
            $fileName = $protocolData['proSerial'].'_protocol.pdf';
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