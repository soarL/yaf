<?php
namespace helpers;
/**
 * ExcelHelper
 * Excel帮助类，用于生成Excel表格，需要PHPExcel辅助。
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */

use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Reader_Excel5 as Excel5;
use PHPExcel_Reader_Excel2007 as Excel2007;
use PHPExcel_Cell_DataType as DataType;
use PHPExcel_Style_Fill as Fill;
use PHPExcel_RichText as RichText;

class ExcelHelper {
	const SAVE_PATH = './';

	/**
	 * 读取excel
	 * @param  string  $filePath [description]
	 * @param  integer $sheet    [description]
	 * @return array
	 */
    public static function format_excel2array($filePath='',$sheet=0){
        $PHPReader = new PHPExcel();        //建立reader对象
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        if( $extension =='xlsx' ){
			$PHPReader = new Excel2007();
		}else{
			$PHPReader = new Excel5();
		}
        $PHPExcel = $PHPReader->load($filePath);        //建立excel对象
        $currentSheet = $PHPExcel->getSheet($sheet);        //**读取excel文件中的指定工作表*/
        $allColumn = $currentSheet->getHighestColumn();        //**取得最大的列号*/
        $allRow = $currentSheet->getHighestRow();        //**取得一共有多少行*/
        $data = array();
        for($rowIndex=1;$rowIndex<=$allRow;$rowIndex++){        //循环读取每个单元格的内容。注意行从1开始，列从A开始
                for($colIndex='A'; $colIndex<=$allColumn; $colIndex++){
                        $addr = $colIndex.$rowIndex;
                        $cell = $currentSheet->getCell($addr)->getValue();
                        if($cell instanceof RichText){ //富文本转换字符串
                                $cell = $cell->__toString();
                        }
                        $data[$rowIndex][$colIndex] = $cell;
                }
        }
        return $data;
	}

	/**
	 * 下载excle文件
	 * @param  string $records     数据
	 * @param  string $other  	   其他数据
	 */
	public static function getDataExcel($records, $other = []) {
		$excel = new PHPExcel();

		$excel->getProperties()
			->setCreator('汇诚普惠')
			->setLastModifiedBy('汇诚普惠')
			->setTitle($other['title'])
			->setSubject($other['title'])
			->setDescription('Test document for PHPExcel, generated using PHP classes.')
			->setKeywords('office PHPExcel php')
			->setCategory('Test result file');

		$columns = $other['columns'];
		$i = 0;
		$headerStyle = [
			'font' => [
				'color' => ['rgb' => 'ffffff'],
				'bold' => true,
			],
			'fill' => [
				'startcolor' => ['argb' => '00a65a'],
				'type' => Fill::FILL_SOLID,
			],
		];
		foreach ($columns as $column) {
			$colmnName = self::getColmnName($i);
			$cellName = $colmnName . '1';
			$excel->getActiveSheet()->setCellValue($cellName, $column['name']);

			if(isset($column['width'])) {
				$excel->getActiveSheet()->getColumnDimension($colmnName)->setWidth($column['width']);
			} else {
				$excel->getActiveSheet()->getColumnDimension($colmnName)->setAutoSize(true);
			}

			// $excel->getActiveSheet()->getStyle($cellName)->getFont()->setBold(true);
			// $excel->getActiveSheet()->getStyle($cellName)->getFill()->setFillType(Fill::FILL_SOLID);
			// $excel->getActiveSheet()->getStyle($cellName)->getFill()->getStartColor()->setARGB('00a65a');

			$excel->getActiveSheet()->getStyle($cellName)->applyFromArray($headerStyle);
			$i++;
		}

		$excel->getActiveSheet()->setTitle($other['title']);
		$excel->setActiveSheetIndex(0);

		$columnKeys = array_keys($columns);

		foreach ($records as $rKey => $record) {
			foreach ($columnKeys as $cKey => $columnKey) {
				$colmnName = self::getColmnName($cKey);
				$cellName = $colmnName . ($rKey+2);
				if(isset($columns[$columnKey]['type'])&&$columns[$columnKey]['type']=='string') {
					$excel->getActiveSheet()->setCellValueExplicit($cellName, $record[$columnKey], DataType::TYPE_STRING);
					$excel->getActiveSheet()->getStyle($cellName)->getNumberFormat()->setFormatCode('@');
				} else {
					$excel->getActiveSheet()->setCellValue($cellName, $record[$columnKey]);
				}
			}
		}
		self::download($excel, $other['title']);
	}

	public static function bigExcel($other, $getResult) {
		$excel = new PHPExcel();

		$excel->getProperties()
			->setCreator('汇诚普惠')
			->setLastModifiedBy('汇诚普惠')
			->setTitle($other['title'])
			->setSubject($other['title'])
			->setDescription('Test document for PHPExcel, generated using PHP classes.')
			->setKeywords('office PHPExcel php')
			->setCategory('Test result file');

		$columns = $other['columns'];
		$columnKeys = array_keys($columns);

		$excel->getActiveSheet()->setTitle($other['title'] . '-0');
		$excel->setActiveSheetIndex(0);
		self::initHeader($excel, $columns);

		$outCount = 0;
		$sheetCount = 0;
		$i = 0;
		while ( $records = $getResult($outCount) ) {
			foreach ($records as $rKey => $record) {
				/*var_dump($record);
				echo '<br>';
				echo '<br>';*/
				foreach ($columnKeys as $cKey => $columnKey) {
					$colmnName = self::getColmnName($cKey);
					$cellName = $colmnName . ($rKey+2);
					if(isset($columns[$columnKey]['type'])&&$columns[$columnKey]['type']=='string') {
						$excel->getActiveSheet()->setCellValueExplicit($cellName, $record[$columnKey], DataType::TYPE_STRING);
						$excel->getActiveSheet()->getStyle($cellName)->getNumberFormat()->setFormatCode('@');
					} else {
						$excel->getActiveSheet()->setCellValue($cellName, $record[$columnKey]);
					}
				}
				$tmpSheetCount = intval(($i + 1)/10);
				if($tmpSheetCount>$sheetCount) {
					$sheetCount = $tmpSheetCount;
					$excel->createSheet();
					/*var_dump('expression');
					echo '<br>';*/
					$excel->setActiveSheetIndex($sheetCount);
					$excel->getActiveSheet()->setTitle($other['title'].'-'.$sheetCount);
					self::initHeader($excel, $columns);
				}
				$i++;
			}
			$outCount += count($records);
		}
		self::download($excel, $other['title']);
	}

	public static function initHeader($excel, $columns) {
		$i = 0;
		$headerStyle = [
			'font' => [
				'color' => ['rgb' => 'ffffff'],
				'bold' => true,
			],
			'fill' => [
				'startcolor' => ['argb' => '00a65a'],
				'type' => Fill::FILL_SOLID,
			],
		];
		foreach ($columns as $column) {
			$colmnName = self::getColmnName($i);
			$cellName = $colmnName . '1';
			$excel->getActiveSheet()->setCellValue($cellName, $column['name']);

			if(isset($column['width'])) {
				$excel->getActiveSheet()->getColumnDimension($colmnName)->setWidth($column['width']);
			} else {
				$excel->getActiveSheet()->getColumnDimension($colmnName)->setAutoSize(true);
			}

			// $excel->getActiveSheet()->getStyle($cellName)->getFont()->setBold(true);
			// $excel->getActiveSheet()->getStyle($cellName)->getFill()->setFillType(Fill::FILL_SOLID);
			// $excel->getActiveSheet()->getStyle($cellName)->getFill()->getStartColor()->setARGB('00a65a');

			$excel->getActiveSheet()->getStyle($cellName)->applyFromArray($headerStyle);
			$i++;
		}
	}

	/**
	 * 保存excle文件
	 * @param  PHPExcel $excel   phpexcel对象
	 * @param  string $fileName  下载文件名
	 * @param  string $type      类型：Excel2007 | Excel5
	 */
	public static function save($excel, $fileName, $type = 'Excel2007') {
		$fullFileName = $fileName;

		if($type=='Excel2007') {
			$fullFileName .= '.xlsx';
		} else if($type=='Excel5') {
			$fullFileName .= '.xls';
		}

		$fullFileName = self::SAVE_PATH . $fullFileName;

		$writer = PHPExcel_IOFactory::createWriter($excel, $type);
		$writer->save($fullFileName);
	}

	/**
	 * 下载excle文件
	 * @param  PHPExcel $excel   phpexcel对象
	 * @param  string $fileName  下载文件名
	 * @param  string $type      类型：Excel2007 | Excel5
	 */
	public static function download($excel, $fileName, $type = 'Excel2007') {
		$fullFileName = $fileName;

		if($type=='Excel2007') {
			$fullFileName .= '.xlsx';
			// Redirect output to a client’s web browser (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'.$fullFileName.'"');
			header('Cache-Control: max-age=0');
			// If you're serving to IE 9, then the following may be needed
			header('Cache-Control: max-age=1');

			// If you're serving to IE over SSL, then the following may be needed
			header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
			header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
			header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
			header ('Pragma: public'); // HTTP/1.0
		} else if($type=='Excel5') {
			$fullFileName .= '.xls';
			// Redirect output to a client’s web browser (Excel5)
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="'.$fullFileName.'"');
			header('Cache-Control: max-age=0');
			// If you're serving to IE 9, then the following may be needed
			header('Cache-Control: max-age=1');

			// If you're serving to IE over SSL, then the following may be needed
			header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
			header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
			header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
			header ('Pragma: public'); // HTTP/1.0
		}

		$writer = PHPExcel_IOFactory::createWriter($excel, $type);
		$writer->save('php://output');
		exit(0);
	}

	/**
	 * 获取列名, 最高只能到ZZ
	 * @param  integer $num  列序号
	 * @return string        列名
	 */
	public static function getColmnName($num) {
		$column = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

		$times = intval($num/26) - 1;
		$less = $num%26;

		if($times < 26) {
			if($times < 0) {
				return $column[$less];
			} else {
				return $column[$times] . $column[$less];
			}
			
		} else {
			return '';
		}
	}
}
