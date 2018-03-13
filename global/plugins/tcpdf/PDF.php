<?php
namespace plugins\tcpdf;
/**
 * @author elf <360197197@qq.com>
 */
class PDF extends TCPDF {

	public function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false) {
		parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
	}


	public function protocolOneTable($header,$data) {
		// Colors, line width and bold font
		$this->SetFillColor(255, 255, 255);
		$this->SetTextColor(102,102,102);
		$this->SetDrawColor(204,204,204);
		$this->SetLineWidth(0.1);
		// $this->SetFont('', 'B');
		// Header
		$w = array(24, 18, 28, 18, 15, 15, 24, 24, 18);
		$num_headers = count($header);
		for($i = 0; $i < $num_headers; ++$i) {
			// $this->Cell($w[$i], 12, $header[$i], 1, 2, 'C', 1);
			$this->MultiCell($w[$i], 14, $header[$i], 1, 'C', false, 0, '', '', true, 0, false, true, 14, 'M', false);
		}
		$this->Ln();
		// Color and font restoration
		// $this->SetFillColor(224, 235, 255);
		$this->SetTextColor(0);
		$this->SetTextColor(102,102,102);
		// $this->SetLineWidth(0.1);
		// $this->SetFont('');
		// Data
		$fill = 0;
		$height = 14;
		foreach($data as $row) {
			$this->MultiCell($w[0], $height, $row[0], 'LRB', 'C', false, 0, '', '', true, 0, false, true, $height, 'M', false);
			$this->MultiCell($w[1], $height, $row[1], 'LRB', 'C', false, 0, '', '', true, 0, false, true, $height, 'M', false);
			$this->MultiCell($w[2], $height, $row[2], 'LRB', 'C', false, 0, '', '', true, 0, false, true, $height, 'M', false);
			$this->MultiCell($w[3], $height, $row[3], 'LRB', 'C', false, 0, '', '', true, 0, false, true, $height, 'M', false);
			$this->MultiCell($w[4], $height, $row[4], 'LRB', 'C', false, 0, '', '', true, 0, false, true, $height, 'M', false);
			$this->MultiCell($w[5], $height, $row[5], 'LRB', 'C', false, 0, '', '', true, 0, false, true, $height, 'M', false);
			$this->MultiCell($w[6], $height, $row[6], 'LRB', 'C', false, 0, '', '', true, 0, false, true, $height, 'M', false);
			$this->MultiCell($w[7], $height, $row[7], 'LRB', 'C', false, 0, '', '', true, 0, false, true, $height, 'M', false);
			$this->MultiCell($w[8], $height, $row[8], 'LRB', 'C', false, 0, '', '', true, 0, false, true, $height, 'M', false);
			$this->Ln();
			$fill=!$fill;
		}
		$this->Cell(array_sum($w), 0, '', 'T');
		$this->Ln();
	}

	public function protocolTwoTable($header,$data) {
		// Colors, line width and bold font
		$this->SetFillColor(255, 255, 255);
		$this->SetTextColor(102,102,102);
		$this->SetDrawColor(204,204,204);
		$this->SetLineWidth(0.1);
		// $this->SetFont('', 'B');
		// Header
		$w = array(76, 18, 20, 36, 28);
		$num_headers = count($header);
		for($i = 0; $i < $num_headers; ++$i) {
			$this->Cell($w[$i], 8, $header[$i], 1, 0, 'C', 1);
		}
		$this->Ln();
		// Color and font restoration
		// $this->SetFillColor(224, 235, 255);
		$this->SetTextColor(0);
		$this->SetTextColor(102,102,102);
		// $this->SetLineWidth(0.1);
		// $this->SetFont('');
		// Data
		$fill = 0;
		foreach($data as $row) {
			$this->Cell($w[0], 8, $row[0], 'LR', 0, 'C', $fill);
			$this->Cell($w[1], 8, $row[1], 'LR', 0, 'C', $fill);
			$this->Cell($w[2], 8, $row[2], 'LR', 0, 'C', $fill);
			$this->Cell($w[3], 8, $row[3], 'LR', 0, 'C', $fill);
			$this->Cell($w[4], 8, $row[4], 'LR', 0, 'C', $fill);
			$this->Ln();
			$fill=!$fill;
		}
		$this->Cell(array_sum($w), 0, '', 'T');
		$this->Ln();
	}

	public function protocolThreeTable($header,$data) {
		// Colors, line width and bold font
		$this->SetFillColor(255, 255, 255);
		$this->SetTextColor(102,102,102);
		$this->SetDrawColor(204,204,204);
		$this->SetLineWidth(0.1);
		// $this->SetFont('', 'B');
		// Header
		$w = array(24, 18, 28, 18, 15, 15, 24, 24, 18);
		$num_headers = count($header);
		for($i = 0; $i < $num_headers; ++$i) {
			// $this->Cell($w[$i], 12, $header[$i], 1, 2, 'C', 1);
			$this->MultiCell($w[$i], 14, $header[$i], 1, 'C', false, 0, '', '', true, 0, false, true, 14, 'M', false);
		}
		$this->Ln();
		// Color and font restoration
		// $this->SetFillColor(224, 235, 255);
		$this->SetTextColor(0);
		$this->SetTextColor(102,102,102);
		// $this->SetLineWidth(0.1);
		// $this->SetFont('');
		// Data
		$fill = 0;
		$height = 14;
		foreach($data as $row) {
			$this->MultiCell($w[0], $height, $row[0], 'LRB', 'C', false, 0, '', '', true, 0, false, true, $height, 'M', false);
			$this->MultiCell($w[1], $height, $row[1], 'LRB', 'C', false, 0, '', '', true, 0, false, true, $height, 'M', false);
			$this->MultiCell($w[2], $height, $row[2], 'LRB', 'C', false, 0, '', '', true, 0, false, true, $height, 'M', false);
			$this->MultiCell($w[3], $height, $row[3], 'LRB', 'C', false, 0, '', '', true, 0, false, true, $height, 'M', false);
			$this->MultiCell($w[4], $height, $row[4], 'LRB', 'C', false, 0, '', '', true, 0, false, true, $height, 'M', false);
			$this->MultiCell($w[5], $height, $row[5], 'LRB', 'C', false, 0, '', '', true, 0, false, true, $height, 'M', false);
			$this->MultiCell($w[6], $height, $row[6], 'LRB', 'C', false, 0, '', '', true, 0, false, true, $height, 'M', false);
			$this->MultiCell($w[7], $height, $row[7], 'LRB', 'C', false, 0, '', '', true, 0, false, true, $height, 'M', false);
			$this->MultiCell($w[8], $height, $row[8], 'LRB', 'C', false, 0, '', '', true, 0, false, true, $height, 'M', false);
			$this->Ln();
			$fill=!$fill;
		}
		$this->Cell(array_sum($w), 0, '', 'T');
		$this->Ln();
	}

	public function protocolFourTable($header,$data) {
		// Colors, line width and bold font
		$this->SetFillColor(255, 255, 255);
		$this->SetTextColor(102,102,102);
		$this->SetDrawColor(204,204,204);
		$this->SetLineWidth(0.1);
		// $this->SetFont('', 'B');
		// Header
		$w = array(61, 61, 61);
		$num_headers = count($header);
		for($i = 0; $i < $num_headers; ++$i) {
			// $this->Cell($w[$i], 12, $header[$i], 1, 2, 'C', 1);
			$this->MultiCell($w[$i], 8, $header[$i], 1, 'C', false, 0, '', '', true, 0, false, true, 8, 'M', false);
		}
		$this->Ln();
		// Color and font restoration
		// $this->SetFillColor(224, 235, 255);
		$this->SetTextColor(0);
		$this->SetTextColor(102,102,102);
		// $this->SetLineWidth(0.1);
		// $this->SetFont('');
		// Data
		$fill = 0;
		$height = 8;
		foreach($data as $row) {
			$this->MultiCell($w[0], $height, $row[0], 'LRB', 'C', false, 0, '', '', true, 0, false, true, $height, 'M', false);
			$this->MultiCell($w[1], $height, $row[1], 'LRB', 'C', false, 0, '', '', true, 0, false, true, $height, 'M', false);
			$this->MultiCell($w[2], $height, $row[2], 'LRB', 'C', false, 0, '', '', true, 0, false, true, $height, 'M', false);
			$this->Ln();
			$fill=!$fill;
		}
		$this->Cell(array_sum($w), 0, '', 'T');
		$this->Ln();
	}

	public function protocolFiveTable($header,$data, $pageSize=18) {
		// Colors, line width and bold font
		$this->SetFillColor(255, 255, 255);
		$this->SetTextColor(102,102,102);
		$this->SetDrawColor(204,204,204);
		$this->SetLineWidth(0.1);
		// $this->SetFont('', 'B');
		// Header
		$w = array(92, 92);
		$num_headers = count($header);
		for($i = 0; $i < $num_headers; ++$i) {
			// $this->Cell($w[$i], 12, $header[$i], 1, 2, 'C', 1);
			$this->MultiCell($w[$i], 8, $header[$i], 1, 'C', false, 0, '', '', true, 0, false, true, 8, 'M', false);
		}
		$this->Ln();
		// Color and font restoration
		// $this->SetFillColor(224, 235, 255);
		$this->SetTextColor(0);
		$this->SetTextColor(102,102,102);
		// $this->SetLineWidth(0.1);
		// $this->SetFont('');
		// Data
		$fill = 0;
		$height = 8;

		$more = [];
		foreach($data as $key => $row) {
			if($key<$pageSize) {
				$this->MultiCell($w[0], $height, $row[0], 'LRB', 'C', false, 0, '', '', true, 0, false, true, $height, 'M', false);
				$this->MultiCell($w[1], $height, $row[1], 'LRB', 'C', false, 0, '', '', true, 0, false, true, $height, 'M', false);
				$this->Ln();
				$fill=!$fill;
			} else {
				$more[] = $row;
			}
		}
		
		if(count($more)) {
			$this->AddPage();
			$this->protocolFiveTable($header, $more);
		} else {
			$this->Cell(array_sum($w), 0, '', 'T');
			$this->Ln();
		}
	}

	public function writeFromFile($file, $data=[]) {
        $content = \Data::getFileContent($file);
        $list = explode("\n", $content);
        
        foreach ($list as $line) {
        	$line = trim($line);
        	if($line=='') {
        		$this->Ln();
        	} else if($line=='@P') {
        		$this->AddPage();
        	} else if(strpos($line, '@H')===0) {
        		$this->SetFont('stsongstdlight', 'B', 10);
        		$this->Write(12, substr($line, 2), '', 0, 'L', true, 0, false, false, 0);
        	} else {
        		$this->SetFont('stsongstdlight', '', 10);
        		$this->Write(6, $line, '', 0, 'L', true, 0, false, false, 0);
        	}
        }
    }
}