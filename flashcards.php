<?php
// --------------------------------------------------------------------------------------
// Author:            David Osorio, jdavidosori96@gmail.com
// Upwork profile:    https://www.upwork.com/freelancers/~010be696c9ded003b5
// Date:              January 2020
// PHP version:       7.2.3
// --------------------------------------------------------------------------------------
// SETUP: Change path to include main TCPDF library and customize defined constants on config/tcpdf_config.php
require_once('../upwork-pdf-form/tcpdf/tcpdf_include.php');
// Extend TCPDF with a custom function. AddBackgroundImage() allows to set an image as full page background
require_once('../upwork-pdf-form/tcpdf/tcpdf_bg_image.php');
// PHP library to fetch data from a spreadsheet
require_once('../upwork-xls-to-mysql/XLSXReader.php');
// Imagick script to convert input RGB image (main) to CMYK (main_cmyk)
require_once('rgb_to_cmyk.php');

// NOTE: PDF_PAGE_FORMAT should be instead of array(69.85, 95.25). Check "How to include TCPDF.txt" note
// NOTE: Example how to call script and customize input parameters
$config = array("outputName" => "Aboriginal_Canadians_Flashcards.pdf");
$excel = array("excelFile" => "Sample Data.xlsx", "sheetName" => "Sheet1");
createPDF($config, $excel);


/**
 * @param config            Associative array, contains 20 keys:
    * @param outputName       Output PDF filename
    * @param outputMode       I: view on browser. D: download directly
 	  * @param cardBack         Add this as a background image to use as a card back
	  * @param rectWidth        Rounded rectangle border width. Default: 0.5
	  * @param rectColor        Rounded rectangle and small squares border color (CMYK array)
	  * @param rectRadio        Rounded rectangle corner radio, 0 means no rounded corner. Default: 2.4
	  * @param imageColor       Main image border color (CMYK array)
	  * @param barColor         Middle bar background color (CMYK array)
	  * @param squareColor      Small squares in middle bar background color (CMYK array)
	  * @param squareFontColor  Small squares letter/text font color (CMYK array)
	  * @param font             Title and body font. Default: dejavusans
    * @param fontColor        Title and body font color (CMYK array)
    * @param numberFont       Card number font. Default: helvetica
	  * @param numberColor      Card number font color (CMYK array)
    * @param numberFontStyle  Card number font style. Default: normal
	  * @param numberFontSize   Card number font size. Default: 6
	  * @param titleFontStyle   Card category font style. Default: bold
	  * @param titleFontSize    Card category font size. Default: 12
	  * @param bodyFontStyle    Card body font style. Default: bold
	  * @param bodyFontSize     Card body font size. Default: 14
 * @param excel             Associative array, contains 2 keys:
	  * @param excelFile        Input data XLSX filepath (required)
	  * @param sheetName        Input data sheet name (required)
**/
function createPDF($config, $excel) {
	if(isset($excel['excelFile']) && isset($excel['sheetName'])) {
    // Data parameters
    $xlsx = new XLSXReader($excel['excelFile']);
    $data = $xlsx->getSheetData($excel['sheetName']);

    // Config parameters
    $outputName = isset($config['outputName']) ? $config['outputName'] : 'Cards.pdf';
		$outputMode = isset($config['outputMode']) ? $config['outputMode'] : 'I';
    $cardBack = isset($config['cardBack']) ? $config['cardBack'] : 'card_back.jpg';
    $rectWidth = isset($config['rectWidth']) ? $config['rectWidth'] : 0.5;
		$rectColor = isset($config['rectColor']) ? $config['rectColor'] : array(0, 0, 0, 78);
		$rectRadio = isset($config['rectRadio']) ? $config['rectRadio'] : 2.4;
		$imageColor = isset($config['imageColor']) ? $config['imageColor'] : array(0, 0, 0, 26);
    $barColor = isset($config['barColor']) ? $config['barColor'] : array(40, 0, 1, 16);
		$squareColor = isset($config['squareColor']) ? $config['squareColor'] : array(0, 0, 0, 26);
		$squareFontColor = isset($config['squareFontColor']) ? $config['squareFontColor'] : array(75, 68, 67, 90);
		$font = isset($config['font']) ? $config['font'] : 'dejavusans';
    $fontColor = isset($config['fontColor']) ? $config['fontColor'] : array(0, 3, 3, 43);
    $numberFont = isset($config['numberFont']) ? $config['numberFont'] : 'helvetica';
    $numberColor = isset($config['numberColor']) ? $config['numberColor'] : array(0, 0, 0, 78);
		$numberFontStyle = isset($config['numberFontStyle']) ? $config['numberFontStyle'] : '';
		$numberFontSize = isset($config['numberFontSize']) ? $config['numberFontSize'] : 6;
		$titleFontStyle = isset($config['titleFontStyle']) ? $config['titleFontStyle'] : 'B';
		$titleFontSize = isset($config['titleFontSize']) ? $config['titleFontSize'] : 12;
		$bodyFontStyle = isset($config['bodyFontStyle']) ? $config['bodyFontStyle'] : 'B';
    $bodyFontSize = isset($config['bodyFontSize']) ? $config['bodyFontSize'] : 14;

    // Image folder paths on "images/"
    $bgFolder = 'flashcards_bg/';
    $bgFolderCMYK = 'flashcards_bg_cmyk/';
    $mainFolder = 'main/';
    $mainFolderCMYK = 'main_cmyk/';
    $iconFolder = 'icons/';

    // Create new PDF document
    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, array(69.85, 95.25), true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor(PDF_AUTHOR);
    $pdf->SetTitle(PDF_TITLE);

    // Set header, footer and default monospaced fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // Set margins
    $pdf->SetMargins(1, 1, 1);
    $pdf->SetHeaderMargin(0);
    $pdf->SetFooterMargin(0);

    // Remove default header and footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, 1);

    // Set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // Convert all border images to CMYK
    if($bg = opendir(__DIR__ . '/images/' . $bgFolder)) {
      while(($image = readdir($bg)) !== false) {
        convertRGBtoCMYK($image, $bgFolder, $bgFolderCMYK);
      }
      closedir($bg);
    }

    for($i = 1; $i < count($data); $i++) { // First row on sheet contains column names
      $image = $data[$i][5];
      // If the image was successfully converted to CMYK, add a page, otherwise PDF deck will skip current card
      if(convertRGBtoCMYK($image, $mainFolder, $mainFolderCMYK)) {
        // Add card back (emtpy page)
        $pdf->AddPage();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $bgImage = $bgFolderCMYK . $cardBack;
        $pdf->AddBackgroundImage($bgImage);

        // Add card face (a custom border template to fill in values)
        $pdf->AddPage();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $bgImage = $bgFolderCMYK . $data[$i][11];
        $pdf->AddBackgroundImage($bgImage);

        // Add rounded rectangle
        $pdf->SetLineStyle(array('width' => $rectWidth, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $rectColor));
        $pdf->RoundedRect(9.2, 8, 52, 7, $rectRadio, '1111', '');

        // Add card category
        $pdf->SetFont($font, $titleFontStyle, $titleFontSize);
        $pdf->SetTextColor($fontColor[0], $fontColor[1], $fontColor[2], $fontColor[3]);
        $pdf->SetXY(9.2, 8);
        $pdf->Cell(52, 7, $data[$i][1], 0, 0, 'C', 0);

        // Add card number
        $pdf->SetFont($numberFont, $numberFontStyle, $numberFontSize);
        $pdf->SetTextColor($numberColor[0], $numberColor[1], $numberColor[2], $numberColor[3]);
        $pdf->SetXY(54, 8.5);
        $pdf->Cell(5, 0, $data[$i][0], 0, 0, 'L', 0);

        // Add card main image (must be JPG)
        $pdf->SetXY(9, 16.4);
        $pdf->SetDrawColor($imageColor[0], $imageColor[1], $imageColor[2], $imageColor[3]);
        $pdf->Image('images/' . $mainFolderCMYK . $data[$i][5], $pdf->GetX(), $pdf->GetY(), 52.3, 33, 'JPG', '', '', false, 300, '', false, false, 1, false, false, false);

        // Add middle bar
        $pdf->SetFillColor($barColor[0], $barColor[1], $barColor[2], $barColor[3]);
        $pdf->SetDrawColor(0, 0, 0, 26); // Light gray border left and right the middle bar (to resemble shadow)
        $pdf->SetXY(6.9, 50.6);
        $pdf->Cell(56.5, 7.2, '', 'LR', 0, 'C', 1);

        // Add 5 middle squares. If input data is empty, then no square is added
        $pdf->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $rectColor));
        $pdf->SetFillColor($squareColor[0], $squareColor[1], $squareColor[2], $squareColor[3]);
        $pdf->SetTextColor($squareFontColor[0], $squareFontColor[1], $squareFontColor[2], $squareFontColor[3]);
        $x = 11.23;
        $distance = 10.5;
        $str = '';
        $strLen = 0;
        for($j = 6; $j < 11; $j++) { // "square" columns start at 6 on Excel file
          $str = $data[$i][$j];
          $strLen = strlen($str);
          if($strLen > 0) {
            $pdf->RoundedRect($x, 51.3, 5.8, 5.8, 1, '1111', 'DF'); // Add square background
            if(strpos($str, '.' ) !== false) // Icons contain a file extension: "."
              $pdf->Image('images/' . $iconFolder . $str, $x + 0.7, 52.3, 4, 4, 'PNG', '', '', false, 300, '', false, false, 1, false, false, false);
            else { // Otherwise square contents is a letter/text
              $pdf->SetXY($x, 51.3);
              switch($strLen) {
                case 1:
                case 2:
                  $pdf->SetFont('helvetica', '', 9);
                  $pdf->Cell(5.8, 5.8, $str, 0, 0, 'C', 0);
                  break;
                default: // Add first 3 characters for long strings
                  $pdf->SetFont('helvetica', '', 7);
                  $pdf->Cell(5.8, 5.8, substr($str, 0, 3), 0, 0, 'C', 0);
              }
            }
          }
          $x += $distance;
        }

        // Add card body (3 strings)
        $pdf->SetFont($font, $bodyFontStyle, $bodyFontSize);
        $pdf->SetTextColor($fontColor[0], $fontColor[1], $fontColor[2], $fontColor[3]);
        $pdf->SetXY(7.05, 61);
        $pdf->Cell(56.35, 7, $data[$i][2], 0, 0, 'C', 0);
        $pdf->SetXY(7.05, 70);
        $pdf->Cell(56.35, 7, $data[$i][3], 0, 0, 'C', 0);
        $pdf->SetXY(7.05, 79);
        $pdf->Cell(56.35, 7, $data[$i][4], 0, 0, 'C', 0);
      }
    }
    // Close and output PDF document
		$pdf->Output($outputName, $outputMode);
  }
}