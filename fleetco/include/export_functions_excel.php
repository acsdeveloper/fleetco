<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

function ExportExcelInit($arrdata, $arrwidth)
{
	global $cCharset;
	$objPHPExcel = new Spreadsheet();
	$objPHPExcel->getProperties()->setCreator("PHP");
	$objASIndex = $objPHPExcel->setActiveSheetIndex(0);
	$objASIndex->setTitle("Export");
	$col = 0;
	foreach ($arrdata as $field => $data)
	{
		$data = mb_convert_encoding((string)$data, 'UTF-8', $cCharset ?: 'UTF-8');
		if (substr($data, 0, 1) == '=')
			$data = '="' . str_replace('"', '""', $data) . '"';
		$colLetter = Coordinate::stringFromColumnIndex($col + 1);
		$objASIndex->setCellValue($colLetter . '1', $data);
		$objASIndex->getColumnDimension($colLetter)->setWidth($arrwidth[$field]);
		$col++;
	}

	return $objPHPExcel;
}

function ExportExcelRecord($arrdata, $datatype, $numberRow, $objPHPExcel, $pageObj)
{
	global $cCharset, $locale_info;
	$col = -1;
	$objASIndex = $objPHPExcel->setActiveSheetIndex(0);
	$objASheet  = $objPHPExcel->getActiveSheet();
	$rowDim     = $objASIndex->getRowDimension($numberRow + 1);

	foreach ($arrdata as $field => $data)
	{
		$col++;
		$colLetter = Coordinate::stringFromColumnIndex($col + 1);
		$colDim    = $objASIndex->getColumnDimension($colLetter);

		if ($datatype[$field] == "binary")
		{
			if (!$data)
				continue;
			if (!function_exists("imagecreatefromstring"))
			{
				$objASIndex->setCellValue($colLetter . ($numberRow + 1), mlang_message("LONG_BINARY"));
				continue;
			}
			$error_handler = set_error_handler("empty_error_handler");
			$gdImage = imagecreatefromstring($data);
			if ($error_handler)
				set_error_handler($error_handler);
			if ($gdImage)
			{
				$objDrawing = new MemoryDrawing();
				$objDrawing->setImageResource($gdImage);
				$objDrawing->setCoordinates($colLetter . ($numberRow + 1));
				$objDrawing->setWorksheet($objASheet);

				$width  = $objDrawing->getWidth() * 0.143;
				$height = $objDrawing->getHeight() * 0.75;

				if ($rowDim->getRowHeight() < $height)
					$rowDim->setRowHeight($height);

				$objASheet->getColumnDimension($colLetter)->setAutoSize(false);

				if ($colDim->getWidth() < $width)
					$colDim->setWidth($width);
			}
		}
		elseif ($datatype[$field] == "file")
		{
			$arr = my_json_decode($row[$field]);
			if (count($arr) == 0)
			{
				$data = mb_convert_encoding((string)$data, 'UTF-8', $cCharset ?: 'UTF-8');
				if ($data == "<img src=\"images/no_image.gif\" />")
					$arr[] = array("name" => "images/no_image.gif");
				else
				{
					if (substr($data, 0, 1) == '=')
						$data = '="' . str_replace('"', '""', $data) . '"';
					$objASIndex->setCellValue($colLetter . ($numberRow + 1), $data);
					continue;
				}
			}
			$offsetY = 0;
			$height  = 0;
			foreach ($arr as $img)
			{
				if (!file_exists($img["name"]) || !$img["name"])
				{
					$data = mb_convert_encoding((string)$data, 'UTF-8', $cCharset ?: 'UTF-8');
					if (substr($data, 0, 1) == '=')
						$data = '="' . str_replace('"', '""', $data) . '"';
					$objASIndex->setCellValue($colLetter . ($numberRow + 1), $data);
					continue;
				}
				$objDrawing = new Drawing();
				$objDrawing->setPath($img["name"]);
				$objDrawing->setCoordinates($colLetter . ($numberRow + 1));
				$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
				$objDrawing->setOffsetY($offsetY);

				$width   = $objDrawing->getWidth() * 0.143;
				$height  = $height + $objDrawing->getHeight() * 0.75;
				$offsetY = $offsetY + $objDrawing->getHeight();

				if ($rowDim->getRowHeight() < $height)
					$rowDim->setRowHeight($height);

				$objASheet->getColumnDimension($colLetter)->setAutoSize(false);

				if ($colDim->getWidth() < $width)
					$colDim->setWidth($width);
			}
		}
		else
		{
			$data = mb_convert_encoding((string)$data, 'UTF-8', $cCharset ?: 'UTF-8');
			if (substr($data, 0, 1) == '=')
				$data = '="' . str_replace('"', '""', $data) . '"';
			$objASIndex->setCellValue($colLetter . ($numberRow + 1), $data);
			if ($datatype[$field] == "date")
			{
				$objASIndex->getStyle($colLetter . ($numberRow + 1))
					->getNumberFormat()
					->setFormatCode($locale_info["LOCALE_SSHORTDATE"] . " hh:mm:ss");
			}
		}
	}
}

function ExportExcelTotals($arrTotal, $arrTotalMessage, $row, $objPHPExcel)
{
	global $cCharset;
	$col      = 1;
	$objASIndex = $objPHPExcel->setActiveSheetIndex(0);
	foreach ($arrTotal as $key => $value)
	{
		if ($value)
		{
			$colLetter = Coordinate::stringFromColumnIndex($col);
			$objASIndex->setCellValue(
				$colLetter . ($row + 1),
				$arrTotalMessage[$key] . mb_convert_encoding((string)$value, 'UTF-8', $cCharset ?: 'UTF-8')
			);
		}
		$col++;
	}
}

function ExportExcelSave($filename, $format, $objPHPExcel)
{
	global $cCharset;
	$filename     = mb_convert_encoding((string)$filename, 'UTF-8', $cCharset ?: 'UTF-8');
	$writerFormat = ($format === 'Excel2007') ? 'Xlsx' : 'Xls';

	if ($format == "Excel2007")
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	else
		header('Content-Type: application/vnd.ms-excel');

	header('Content-Disposition: attachment;filename="' . $filename . '";');
	header('Cache-Control: max-age=0');

	$objWriter = IOFactory::createWriter($objPHPExcel, $writerFormat);
	$objWriter->save('php://output');
}
