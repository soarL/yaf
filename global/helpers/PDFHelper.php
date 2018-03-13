<?php
namespace helpers;
/**
 * PDFHelper
 * PDF帮助类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */

use TCPDF;

class PDFHelper {

    public static function getProtocolPDF($title = '借款合同') {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('汇诚普惠');
        $pdf->SetTitle($title);
        $pdf->SetSubject($title);
        $pdf->SetKeywords('汇诚普惠, 借款, 合同');
        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        //$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 005', PDF_HEADER_STRING);

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, 8, PDF_MARGIN_RIGHT);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set font
        $pdf->SetFont('stsongstdlight', '', 20);

        // add a page
        $pdf->AddPage();

        // set some text to print
        $pdf->setJPEGQuality(0);
        $pdf->Image(WEB_ASSET.'/common/images/hcphlogo.jpg', '', '', 40, 0, 'JPG', '', 'T', false, 10, '', false, false, 0, false, false, false);
        $txt = '汇诚普惠电子'.$title;
        // print a block of text using Write()
        $pdf->SetFontSize(15);
        $pdf->SetTextColor(119,119,119);
        $pdf->Write(15, $txt, '', 0, 'R', true, 0, false, false, 0);
        return $pdf;
    }
    public static function writeTableN($pdf, $data, $style=[]) {
        // Color and font restoration
        $pdf->SetFillColor(224, 235, 255);
        $pdf->SetTextColor(0);
        $pdf->SetFont('');

        $w = [];
        $num_lies = count($data[0]);
        if(isset($style['rowWidth'])) {
            $w = $style['rowWidth'];
        } else {
            $defaultWidth = 180/$num_lies;
            for($i = 0; $i < $num_lies; ++$i) {
                $w[$i] = $defaultWidth;
            }
        }

        $h = [];
        $num_rows = count($data);
        if(isset($style['rowHeight'])) {
            $h = $style['rowHeight'];
        } else {
            $defaultHeight = 8;
            for($i = 0; $i < $num_rows; ++$i) {
                $h[$i] = $defaultHeight;
            }
        }

        // Data
        $fill = 0;
        foreach($data as $k => $row) {
            foreach ($row as $key => $value) {
                $pdf->MultiCell($w[$key], $h[$k], $value, '0', 'M', $fill, 0);
            }
            $pdf->Ln();
            $fill=!$fill;
        }
        $pdf->Cell(array_sum($w), 0, '', 'T');
    }

    public static function writeTable($pdf, $header, $data, $style=[]) {
        $pdf->SetFillColor(255, 0, 0);
        $pdf->SetTextColor(255);
        $pdf->SetDrawColor(128, 0, 0);
        $pdf->SetLineWidth(0.3);
        $pdf->SetFont('', 'B');
        // Header
        $w = [];
        $num_headers = count($header);
        if(isset($style['rowWidth'])) {
            $w = $style['rowWidth'];
        } else {
            $defaultWidth = 180/$num_headers;
            for($i = 0; $i < $num_headers; ++$i) {
                $w[$i] = $defaultWidth;
            }
        }
        for($i = 0; $i < $num_headers; ++$i) {
            $pdf->Cell($w[$i], 7, $header[$i], 1, 0, 'C', 1);
        }
        $pdf->Ln();
        // Color and font restoration
        $pdf->SetFillColor(224, 235, 255);
        $pdf->SetTextColor(0);
        $pdf->SetFont('');
        // Data
        $fill = 0;
        foreach($data as $row) {
            foreach ($row as $key => $value) {
                $pdf->Cell($w[$key], 6, $value, 'LR', 0, 'L', $fill);
            }
            $pdf->Ln();
            $fill=!$fill;
        }
        $pdf->Cell(array_sum($w), 0, '', 'T');
    }
}