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
$sheet->mergeCells("A1:K1");
$sheet->getStyle('A1')->getFont()->setSize(16);
$sheet->setCellValue('A2', "By WELDER");
$sheet->mergeCells("A2:B2");
$sheet->getStyle("A1:A2")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A1:A2")->getFont()->setBold(true);
$sheet->setCellValue('I2', "날짜 Date");
$sheet->getStyle('I2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
$sheet->setCellValue('J2', $dateTime);
$sheet->mergeCells("J2:K2");
$sheet->getStyle("J2")->getFont()->setBold(true);
$sheet->getStyle("J2:K2")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("I2:K2")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');

$sheet->setCellValue('A3', "No.");
$sheet->setCellValue('B3', "WELDER");
$sheet->setCellValue('C3', "RTorPAUT\nSELECTION");
$sheet->setCellValue('D3', "SHOOT");
$sheet->setCellValue('E3', "BALANCE");
$sheet->setCellValue('F3', "RESULT");
$sheet->setCellValue('F4', "REPAIR");
$sheet->setCellValue('G3', "REPAIR\nPROGRESS(%)");
$sheet->setCellValue('H3', "RT");
$sheet->setCellValue('H4', "USED FILM");
$sheet->setCellValue('I4', "REPAIR FILM");
$sheet->setCellValue('J3', "REPAIR FILM\nPROGRESS(%)");
$sheet->setCellValue('K3', "REMARK");
$sheet->getStyle("A3:K4")->getFont()->setSize(10);
$sheet->getStyle("A3:K4")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FCE4D6');
$sheet->getStyle("A3:K4")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

// 줄바꿈
$sheet->getStyle("C3")->getAlignment()->setWrapText(true);
$sheet->getStyle("G3")->getAlignment()->setWrapText(true);
$sheet->getStyle("J3")->getAlignment()->setWrapText(true);

$sheet->mergeCells("A3:A4");
$sheet->mergeCells("B3:B4");
$sheet->mergeCells("C3:C4");
$sheet->mergeCells("D3:D4");
$sheet->mergeCells("E3:E4");
$sheet->mergeCells("G3:G4");
$sheet->mergeCells("H3:I3");
$sheet->mergeCells("J3:J4");
$sheet->mergeCells("K3:K4");

$sheet->getRowDimension(3)->setRowHeight(33);
$sheet->getRowDimension(4)->setRowHeight(33);

$url = "http://wcfservice.hi-techeng.co.kr/apipwim/getndewelder?jno={$jno}";

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
    $weldingData = $responseResult->Value;
    for($i=0; $i < count($responseResult->Value); $i++) {
        // NO.
        $sheet->setCellValue('A'.$rowCnt, $i+1);
        // WELDER
        $welderRegNo = $weldingData[$i]->WELDER_REG_NO;
        $sheet->setCellValue('B'.$rowCnt, $welderRegNo);
        // RTorPAUT SELECTION
        $rtUtSel = str_replace(",", "", $weldingData[$i]->RT_UT_SEL);
        $sheet->setCellValue('C'.$rowCnt, $rtUtSel);
        if($rtUtSel == 0 || $rtUtSel == ''){
            $sheet->getStyle("C{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else if(strpos($rtUtSel, ".")) {
            $sheet->getStyle("C{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.00');
        } else {
            $sheet->getStyle("C{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0');
        }
        // SHOOT
        $shoot = str_replace(",", "", $weldingData[$i]->SHOOT);
        $sheet->setCellValue('D'.$rowCnt, $shoot);
        if($shoot == 0 || $shoot == ''){
            $sheet->getStyle("D{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else if(strpos($shoot, ".")) {
            $sheet->getStyle("D{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.00');
        } else {
            $sheet->getStyle("D{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0');
        }
        // BALANCE
        $balance = str_replace(",", "", $weldingData[$i]->BALANCE);
        $sheet->setCellValue('E'.$rowCnt, $balance);
        if($balance == 0 || $balance == ''){
            $sheet->getStyle("E{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else if(strpos($balance, ".")) {
            $sheet->getStyle("E{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.00');
        } else {
            $sheet->getStyle("E{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0');
        }
        // REPAIR
        $repair = str_replace(",", "", $weldingData[$i]->REPAIR);
        $sheet->setCellValue('F'.$rowCnt,$repair);
        if($repair == 0 || $repair == ''){
            $sheet->getStyle("F{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else if(strpos($repair, ".")) {
            $sheet->getStyle("F{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.00');
        } else {
            $sheet->getStyle("F{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0');
        }
        // REPAIR PROGRESS(%)
        $repairProgress = str_replace(",", "", $weldingData[$i]->REPAIR_PROGRESS);
        $sheet->setCellValue('G'.$rowCnt, $repairProgress);
        if($repairProgress == 0 || $repairProgress == ''){
            $sheet->getStyle("G{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else {
            $sheet->getStyle("G{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.00');
        }
        // USED FILM
        $usedFilm = str_replace(",", "", $weldingData[$i]->USED_FILM);
        $sheet->setCellValue('H'.$rowCnt, $usedFilm);
        if($usedFilm == 0 || $usedFilm == ''){
            $sheet->getStyle("H{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else if(strpos($usedFilm, ".")) {
            $sheet->getStyle("H{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.00');
        } else {
            $sheet->getStyle("H{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0');
        }
        // REPAIR FILM
        $repairFilm = str_replace(",", "", $weldingData[$i]->REPAIR_FILM);
        $sheet->setCellValue('I'.$rowCnt, $repairFilm);
        if($repairFilm == 0 || $repairFilm == ''){
            $sheet->getStyle("I{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else if(strpos($repairFilm, ".")) {
            $sheet->getStyle("I{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.00');
        } else {
            $sheet->getStyle("I{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0');
        }
        // REPAIR FILM PROGRESS(%)
        $repairFilmProgress = str_replace(",", "", $weldingData[$i]->REPAIR_FILM_PROGRESS);
        $sheet->setCellValue('J'.$rowCnt, $repairFilmProgress);
        if($repairFilmProgress == 0 || $repairFilmProgress == ''){
            $sheet->getStyle("J{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else {
            $sheet->getStyle("J{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.00');
        }
        // REMARK
        $sheet->setCellValue('K'.$rowCnt, $weldingData[$i]->REMARK);
        $rowCnt++;
    }
}

// 들여쓰기
$sheet->getStyle('A5:K'.$rowCnt)->getAlignment()->setIndent(1);

// 셀 높이
for($i = 1; $i <= $rowCnt; $i++) {
    if($i != 3 && $i != 4) {
        $sheet->getRowDimension($i)->setRowHeight(22);
    }
}

//자동 줄바꿈
// $sheet->getStyle('A3:O'.$rowCnt)->getAlignment()->setWrapText(true);

// 텍스트 맞춤
$sheet->getStyle("A1:K{$rowCnt}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

// 표 그리기
$rowCnt--;
$sheet->getStyle("A3:K{$rowCnt}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

// 칼럼 사이즈
$sheet->getColumnDimension('A')->setWidth(9);
$sheet->getColumnDimension('B')->setWidth(22);
$sheet->getColumnDimension('C')->setWidth(20);
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('E')->setWidth(15);
$sheet->getColumnDimension('F')->setWidth(15);
$sheet->getColumnDimension('G')->setWidth(15);
$sheet->getColumnDimension('H')->setWidth(15);
$sheet->getColumnDimension('I')->setWidth(15);
$sheet->getColumnDimension('J')->setWidth(15);
$sheet->getColumnDimension('K')->setWidth(15);

// 파일명
$title = "nde_by_welder_report";

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
