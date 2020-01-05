<?php
  require_once('../upwork-pdf-form/tcpdf/tcpdf_include.php');   // TCPDF library
  require_once('../upwork-pdf-form/tcpdf/tcpdf_bg_image.php');  // Extend TCPDF with a custom function
  require_once('../upwork-xls-to-mysql/XLSXReader.php');        // PHP library to fetch data from a spreadsheet
  $xlsx = new XLSXReader('Sample Data.xlsx');                   // NOTE: Modify parameter: Excel file
  $data = $xlsx->getSheetData('Sheet1');                        // NOTE: Modify parameter: Sheet name

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

  for($i = 1; $i < count($data); $i++) { // First row on sheet contains column names
    $pdf->AddPage();
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Add custom border
    $bg_image = 'flashcard_' . $data[$i][11];
    $pdf->AddBackgroundImage($bg_image);

    // Add card category
    $pdf->SetFont('dejavusans', 'B', 12);
    $pdf->SetTextColor(146, 142, 142);
    $pdf->SetXY(7.05, 8.5);
    $pdf->Cell(56.35, 0, $data[$i][1], 0, 0, 'C', 0);

    // Add card number
    $pdf->SetFont('helvetica', '', 6);
    $pdf->SetTextColor(55, 55, 55);
    $pdf->SetXY(54, 8.5);
    $pdf->Cell(5, 0, $data[$i][0], 0, 0, 'L', 0);

    // Add card main image
    $pdf->SetXY(9, 16.4);
    $pdf->SetDrawColor(189, 189, 189);
    $pdf->Image('images/' . $data[$i][5], $pdf->GetX(), $pdf->GetY(), 52.3, 33, 'JPG', '', '', false, 300, '', false, false, 1, false, false, false);

    // Add card text (3 strings)
    $pdf->SetFont('dejavusans', 'B', 14);
    $pdf->SetTextColor(146, 142, 142);
    $pdf->SetXY(7.05, 61);
    $pdf->Cell(56.35, 0, $data[$i][2], 0, 0, 'C', 0);
    $pdf->SetXY(7.05, 70);
    $pdf->Cell(56.35, 0, $data[$i][3], 0, 0, 'C', 0);
    $pdf->SetXY(7.05, 79);
    $pdf->Cell(56.35, 0, $data[$i][4], 0, 0, 'C', 0);
  }

  $pdf->Output('Flashcards.pdf', 'i');