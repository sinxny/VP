<?php
ini_set('memory_limit','-1');
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
ini_set( "display_errors", 1 );

// require_once "../../../_inc.php";
require_once "../vendor/autoload.php";
require_once "../common/func.php";

// $request_body = file_get_contents('php://input');
// $data = json_decode($request_body, true);

// 도메인
$domain = $_SERVER["HTTP_HOST"];

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();

// Add header data
$sheet = $spreadsheet->getActiveSheet();

// 용지 방향
$sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
// 용지 크기
$sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A3);
// 자동 맞춤
$sheet->getPageSetup()->setFitToWidth(1);
$sheet->getPageSetup()->setFitToHeight(0);

// 폰트사이즈 / 폰트 이름
$spreadsheet->getDefaultStyle()->getFont()->setSize(11);
$spreadsheet->getDefaultStyle()->getFont()->setName('맑은 고딕');

$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(2, 2);

$jno = $_GET["jno"];
$jobName = $_GET["jobName"];

// 헤더
$today = new DateTime();
$dateTime = $today->format('Y-m-d');
$sheet->setCellValue('A1', $jobName);
$sheet->mergeCells("A1:O1");
$sheet->getStyle('A1')->getFont()->setSize(16);
$sheet->setCellValue('A2', "By ISO DWG");
$sheet->mergeCells("A2:B2");
$sheet->getStyle("A1:A2")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A1:A2")->getFont()->setBold(true);
$sheet->setCellValue('M2', "Period");
$sheet->getStyle('M2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
$sheet->setCellValue('N2', $dateTime);
$sheet->mergeCells("N2:O2");
$sheet->getStyle("N2")->getFont()->setBold(true);
$sheet->getStyle("N2:O2")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("M2:O2")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');

$sheet->setCellValue('A3', "No.");
$sheet->setCellValue('B3', "ISO DWG NO.");
$sheet->setCellValue('C3', "PKG NO.");
$sheet->setCellValue('D3', "NDE RATE(%)");
$sheet->setCellValue('E3', "DWG WELD TOTAL JOINT");
$sheet->setCellValue('E4', "BW");
$sheet->setCellValue('F4', "SW");
$sheet->setCellValue('G3', "TARGET JOINT");
$sheet->setCellValue('H3', "SELECTION");
$sheet->setCellValue('H4', "RT");
$sheet->setCellValue('I4', "PAUT");
$sheet->setCellValue('J4', "MT");
$sheet->setCellValue('K4', "PT");
$sheet->setCellValue('L3', "REPORT JOINT");
$sheet->setCellValue('M3', "BALANCE");
$sheet->setCellValue('N3', "PROGRESS(%)");
$sheet->setCellValue('O3', "REMARK");
$sheet->getStyle("A3:O4")->getFont()->setSize(10);
$sheet->getStyle("A3:O4")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('DDEBF7');
$sheet->getStyle("A3:O4")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

$sheet->mergeCells("A3:A4");
$sheet->mergeCells("B3:B4");
$sheet->mergeCells("C3:C4");
$sheet->mergeCells("D3:D4");
$sheet->mergeCells("E3:F3");
$sheet->mergeCells("G3:G4");
$sheet->mergeCells("H3:K3");
$sheet->mergeCells("L3:L4");
$sheet->mergeCells("M3:M4");
$sheet->mergeCells("N3:N4");
$sheet->mergeCells("O3:O4");

$sheet->getRowDimension(3)->setRowHeight(33);
$sheet->getRowDimension(4)->setRowHeight(33);

$url = "http://wcfservice.hi-techeng.co.kr/apipwim/getndeiso?jno={$jno}";

$curl = curl_init();

curl_setopt_array($curl, array(
        // CURLOPT_PORT => "80",
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array(
        "cache-control: no-cache",
        "content-type: text/plain; charset=utf-8"
    ),
));

$response = curl_exec($curl);
    // $err = curl_error($curl);
curl_close($curl);

$responseResult = json_decode($response);

if($responseResult->ResultType = "Success") {
    $rowCnt = 5;
    $dwgData = $responseResult->Value;
    for($i=0; $i < count($responseResult->Value); $i++) {
        // NO.
        $sheet->setCellValue('A'.$rowCnt, $i+1);
        // ISO DWG NO.
        $sheet->setCellValue('B'.$rowCnt, $dwgData[$i]->DRW_NO);
        // PKG NO.
        $sheet->setCellValue('C'.$rowCnt, $dwgData[$i]->PKG_NO);
        // NDE RATE(%)
        $ndeRate = str_replace(",", "", $dwgData[$i]->NDE_RATE);
        $sheet->setCellValue('D'.$rowCnt, $ndeRate);
        if($ndeRate == 0 || $ndeRate == ''){
            $sheet->getStyle("D{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else if(strpos($ndeRate, ".")) {
            $sheet->getStyle("D{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.0#');
        } else {
            $sheet->getStyle("D{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0');
        }
        // BW
        $bw = str_replace(",", "", $dwgData[$i]->BW);
        $sheet->setCellValue('E'.$rowCnt, $bw);
        if($bw == 0 || $bw == ''){
            $sheet->getStyle("E{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else if(strpos($bw, ".")) {
            $sheet->getStyle("E{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.0#');
        } else {
            $sheet->getStyle("E{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0');
        }
        // SW
        $sw = str_replace(",", "", $dwgData[$i]->SW);
        $sheet->setCellValue('F'.$rowCnt, $sw);
        if($sw == 0 || $sw == ''){
            $sheet->getStyle("F{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else if(strpos($sw, ".")) {
            $sheet->getStyle("F{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.0#');
        } else {
            $sheet->getStyle("F{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0');
        }
        // TARGET JOINT
        $targetJoint = str_replace(",", "", $dwgData[$i]->TARGET_JOINT);
        $sheet->setCellValue('G'.$rowCnt, $targetJoint);
        if($targetJoint == 0 || $targetJoint == ''){
            $sheet->getStyle("G{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else if(strpos($targetJoint, ".")) {
            $sheet->getStyle("G{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.0#');
        } else {
            $sheet->getStyle("G{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0');
        }
        // RT
        $rt = str_replace(",", "", $dwgData[$i]->RT);
        $sheet->setCellValue('H'.$rowCnt, $rt);
        if($rt == 0 || $rt == ''){
            $sheet->getStyle("H{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else if(strpos($rt, ".")) {
            $sheet->getStyle("H{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.0#');
        } else {
            $sheet->getStyle("H{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0');
        }
        // PAUT
        $paut = str_replace(",", "", $dwgData[$i]->UT);
        $sheet->setCellValue('I'.$rowCnt, $paut);
        if($paut == 0 || $paut == ''){
            $sheet->getStyle("I{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else if(strpos($paut, ".")) {
            $sheet->getStyle("I{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.0#');
        } else {
            $sheet->getStyle("I{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0');
        }
        // MT
        $mt = str_replace(",", "", $dwgData[$i]->MT);
        $sheet->setCellValue('J'.$rowCnt, $mt);
        if($mt == 0 || $mt == ''){
            $sheet->getStyle("J{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else if(strpos($mt, ".")) {
            $sheet->getStyle("J{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.0#');
        } else {
            $sheet->getStyle("J{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0');
        }
        // PT
        $pt = str_replace(",", "", $dwgData[$i]->PT);
        $sheet->setCellValue('K'.$rowCnt, $pt);
        if($pt == 0 || $pt == ''){
            $sheet->getStyle("K{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else if(strpos($pt, ".")) {
            $sheet->getStyle("K{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.0#');
        } else {
            $sheet->getStyle("K{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0');
        }
        // REPORT JOINT
        $reportJoint = str_replace(",", "", $dwgData[$i]->REPORT_JOINT);
        $sheet->setCellValue('L'.$rowCnt, $reportJoint);
        if($reportJoint == 0 || $reportJoint == ''){
            $sheet->getStyle("L{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else if(strpos($reportJoint, ".")) {
            $sheet->getStyle("L{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.0#');
        } else {
            $sheet->getStyle("L{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0');
        }
        // BALANCE
        $balance = str_replace(",", "", $dwgData[$i]->BALANCE);
        $sheet->setCellValue('M'.$rowCnt, $balance);
        if($balance == 0 || $balance == ''){
            $sheet->getStyle("M{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else if(strpos($balance, ".")) {
            $sheet->getStyle("M{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.0#');
        } else {
            $sheet->getStyle("M{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0');
        }
        // PROGRESS(%)
        $progress = str_replace(",", "", $dwgData[$i]->PROGRESS);
        $sheet->setCellValue('N'.$rowCnt, $progress);
        if($progress == 0 || $progress == ''){
            $sheet->getStyle("N{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else if(strpos($progress, ".")) {
            $sheet->getStyle("N{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.0#');
        } else {
            $sheet->getStyle("N{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0');
        }
        // Remark
        $sheet->setCellValue('O'.$rowCnt, $dwgData[$i]->REMARK);
        $rowCnt++;
    }
}

// 들여쓰기
$sheet->getStyle('A5:O'.$rowCnt)->getAlignment()->setIndent(1);

// 셀 높이
for($i = 1; $i <= $rowCnt; $i++) {
    if($i != 3 && $i != 4) {
        $sheet->getRowDimension($i)->setRowHeight(22);
    }
}

//자동 줄바꿈
// $sheet->getStyle('A3:O'.$rowCnt)->getAlignment()->setWrapText(true);

// 텍스트 맞춤
$sheet->getStyle("A1:O{$rowCnt}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

// 자동 필터
$sheet->setAutoFilter("A4:O{$rowCnt}");

// 표 그리기
$rowCnt--;
$sheet->getStyle("A3:O{$rowCnt}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

// 칼럼 사이즈
$sheet->getColumnDimension('A')->setWidth(9);
$sheet->getColumnDimension('B')->setWidth(22);
$sheet->getColumnDimension('C')->setWidth(20);;
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('E')->setWidth(15);
$sheet->getColumnDimension('F')->setWidth(15);
$sheet->getColumnDimension('G')->setWidth(15);
$sheet->getColumnDimension('H')->setWidth(15);
$sheet->getColumnDimension('I')->setWidth(15);
$sheet->getColumnDimension('J')->setWidth(15);
$sheet->getColumnDimension('K')->setWidth(15);
$sheet->getColumnDimension('L')->setWidth(15);
$sheet->getColumnDimension('M')->setWidth(15);
$sheet->getColumnDimension('N')->setWidth(15);
$sheet->getColumnDimension('O')->setWidth(15);

// 파일명
$title = "nde_by_iso_report";

// Rename worksheet
$sheet->setTitle($title);
setcookie("fileDownload", true, 0, "/");
// Redirect output to a client’s web browser (Excel2007)
@header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
//IE EDGE
if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Edge') !== FALSE)) {
    $title = rawurlencode($title);
    @header('Content-Disposition: attachment;filename="' . $title . '.xlsx"');
    @header('Cache-Control: private, no-transform, no-store, must-revalidate');
    @header('Pragma: no-cache');
}
//IE
else if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE || strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== FALSE) {
    $title = iconv("UTF-8","EUC-KR", $title);
    @header('Content-Disposition: attachment;filename=' . $title . '.xlsx');
    @header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    @header('Pragma: public'); // HTTP/1.0
}
else {
    @header('Content-Disposition: attachment;filename="' . $title . '.xlsx"');
    @header('Cache-Control: private, no-transform, no-store, must-revalidate');
    @header('Pragma: no-cache');
}
@header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
@header('Cache-Control: max-age=1');

$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save('php://output');
exit;
?>
