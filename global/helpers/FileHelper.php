<?php
namespace helpers;

/**
 * FileHelper
 * 文件帮助类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class FileHelper {
	public static function arrayToXml($array, $dom = false, $item = false) {
		if (!$dom) {
        $dom = new \DOMDocument("1.0");
	    }
	    if (!$item) {
	        $item = $dom->createElement("root");
	        $dom->appendChild($item);
	    }
	    foreach ($array as $key => $val) {
	        $itemx = $dom->createElement(is_string($key) ? $key : "item");
	        $item->appendChild($itemx);
	        if (!is_array($val)) {
	            $text = $dom->createTextNode($val);
	            $itemx->appendChild($text);
	        } else {
	            self::arrayToXml($val, $dom, $itemx);
	        }
	    }
	    $xml = $dom->saveXML();
	    $tmp = explode("\n", $xml);
	    $xmlString = "";
	    foreach ($tmp as $val) {
	        $xmlString .= $val;
	    }
	    return $xmlString;
	}

	public static function txt2pdf($pdf, $fileName, $params=[]) {
		$content = \Data::getFileContent($fileName);
        $list = explode("\n", $content);
        
        foreach ($list as $k => $line) {
        	$line = trim($line);
        	$line = self::parseVal($line, $params, 'pdf');
        	if($line=='') {
        		$pdf->Ln();
        	} else if($line=='@P') {
        		$pdf->AddPage();
        	} else if(strpos($line, '@H')===0) {
        		$pdf->SetFont('stsongstdlight', 'B', 14);
        		$pdf->Write(12, substr($line, 2), '', 0, 'L', true, 0, false, false, 0);
            } else if(strpos($line, '@M')===0) {
                $pdf->SetFont('stsongstdlight', '', 28);
                $pdf->SetTextColor(255,0,0);
                $pdf->SetFontSize(8);
                $pdf->Write(0, substr($line, 2), '', 0, 'L', true, 0, false, false, 0);
                $pdf->SetTextColor(119,119,119);
            } else if(strpos($line, '@T')===0) {
                $pdf->SetTextColor(102,102,102);
                $pdf->SetFontSize(18);
                $pdf->Write(15, substr($line, 2), '', 0, 'C', true, 0, false, false, 0);
            } else if(strpos($line, '@D')===0) {
                self::writePDFTable($pdf, $line, $params);
            } else if(strpos($line, '@N')===0) {
                self::writePDFTableN($pdf, $line, $params);
            } else if(strpos($line, '@S')===0) {
                $pdf->Write(12, ' ', '', 0, 'L', true, 0, false, false, 0);
                self::writePDFSeal($pdf, $line, $params);
                $pdf->Write(12, ' ', '', 0, 'L', true, 0, false, false, 0);
            } else {
                if($k == 30){
                    //echo $line;exit;
                    $pdf->SetTextColor(102,102,102);
                }
        		$pdf->SetFont('stsongstdlight', '', 13);
        		$pdf->Write(10, $line, '', 0, 'L', true, 0, false, false, 0);
        	}
        }
	}

	public static function txt2html($fileName, $params=[]) {
		$content = \Data::getFileContent($fileName);
        $list = explode("\n", $content);
        
        foreach ($list as $line) {
        	$line = trim($line);
        	$line = self::parseVal($line, $params);
        	if($line=='') {
        		echo '<br/>';
        	} else if($line=='@P') {
        		echo '<br/>';
        	} else if(strpos($line, '@H')===0) {
        		echo '<h4>'.substr($line, 2).'</h4>';
            } else if(strpos($line, '@M')===0) {
                echo '';
            } else if(strpos($line, '@T')===0) {
                $link = '';
                //$link = '<a target="_blank" class="download" href="">【下载合同】</a>';
                echo '<div class="main-title">'.substr($line, 2).$link.'</div>';
            } else if(strpos($line, '@D')===0) {
                self::writeHtmlTable($line, $params);
        	} else {
        		echo '<p>'.$line.'</p>';
        	}
        }
	}

	public static function parseVal($line, $params, $type='html') {
		if(strpos($line, '@V')!==false) {
    		$count = preg_match_all('/@V{.*?}/', $line, $matches);
    		if($count) {
        		foreach ($matches[0] as $str) {
        			$content = substr($str, 3, -1);
        			$rows = explode('|', $content);
        			if(isset($params[$rows[0]])) {
        				$line = str_replace($str, $params[$rows[0]], $line);
        			} else {
                        $default = isset($rows[1])?$rows[1]:'';
                        if($type=='html') {
                            $default = '<u>'.str_replace('_', '&nbsp;', $default).'</u>';
                        } else {
                            $default = str_replace('_', ' ', $default);
                        }
        				$line = str_replace($str, $default, $line);
        			}
        		}
    		}
    	}
    	return $line;
	}

    public static function writePDFTable($pdf, $line, $params) {
        $count = preg_match_all('/@D{.*?}/', $line, $matches);
        if($count) {
            foreach ($matches[0] as $str) {
                $name = substr($str, 3, -1);
                $header = $params[$name]['header'];
                $data = $params[$name]['data'];
                $style = isset($params[$name]['style'])?$params[$name]['style']:[];
                PDFHelper::writeTable($pdf, $header, $data, $style);
            }
        }
    }

    public static function writePDFSeal($pdf, $line, $params) {
        $count = preg_match_all('/@S{.*?}/', $line, $matches);
        if($count) {
            foreach ($matches[0] as $str) {
                $name = substr($str, 3, -1);
                //$data = $params[$name]['data'];
                //$style = isset($params[$name]['style'])?$params[$name]['style']:[];
                $pdf->writeHTML($params[$name], true, false, true, false, '');
            }
        }
    }

    public static function writePDFTableN($pdf, $line, $params) {
        $count = preg_match_all('/@N{.*?}/', $line, $matches);
        if($count) {
            foreach ($matches[0] as $str) {
                $name = substr($str, 3, -1);
                $data = $params[$name]['data'];
                $style = isset($params[$name]['style'])?$params[$name]['style']:[];
                PDFHelper::writeTableN($pdf, $data, $style);
            }
        }
    }

    public static function writeHtmlTable($line, $params) {
        $count = preg_match_all('/@D{.*?}/', $line, $matches);
        if($count) {
            foreach ($matches[0] as $str) {
                $name = substr($str, 3, -1);
                $header = $params[$name]['header'];
                $data = $params[$name]['data'];
                $style = isset($params[$name]['style'])?$params[$name]['style']:[];
                $w = isset($style['rowWidth'])?$style['rowWidth']:false;
                $html = '';
                foreach ($header as $k => $col) {
                    $width = '';
                    if($w && $w[$k]) {
                        $width = 'width="'.$w[$k].'"';
                    }
                    $html .= '<th '.$width.'>'.$col.'</th>';
                }
                $thead = '<thead><tr>'.$html.'</tr></thead>';
                $html = '';
                foreach ($data as $i => $row) {
                    $html .= '<tr>';
                    foreach ($row as $k => $val) {
                        $html .= '<td>'.$val.'</td>';
                    }
                    $html .= '</tr>';
                }
                $tbody = '<tbody>' . $html . '</tbody>';
                echo '<div class="table-wrapper mb10"><table class="table">'.$thead.$tbody.'</table></div>';
            }
        }
    }
}