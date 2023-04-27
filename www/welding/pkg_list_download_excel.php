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
$date = $today->format('Y-m-d');
$sheet->setCellValue('A1', "PKG List");
$sheet->mergeCells("A1:C2");
$sheet->setCellValue('D1', $jobName);
$sheet->mergeCells("D1:Y2");
$sheet->getStyle('D1')->getFont()->setSize(16);
$sheet->setCellValue('Z1', "날짜 Date");
$sheet->mergeCells("Z1:Z2");
$sheet->setCellValue('AA1', $date);
$sheet->mergeCells("AA1:AB2");
$sheet->getStyle("Z1:AB2")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');
$sheet->getStyle("A1:AB2")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A1:AB4")->getFont()->setBold(true);

$sheet->setCellValue('A3', "COMPANY");
$sheet->setCellValue('B3', "NO.");
$sheet->setCellValue('C3', "PKG. NO.");
// $sheet->setCellValue('D3', "NDE%");
$sheet->setCellValue('D3', "MAIN LINE CONDITION");
$sheet->setCellValue('D4', "Fluid");
$sheet->setCellValue('E4', "Line No");
// $sheet->setCellValu('G4', "Line Class");
$sheet->setCellValue('F4', "Test\nFluid");
$sheet->setCellValue('G4', "Operating\nPressure");
$sheet->setCellValue('H4', "Design\nPressure");
$sheet->setCellValue('I4', "Test\nPressure");
$sheet->setCellValue('J3', "Method\nCLIENT");
$sheet->setCellValue('K3', "인허가\n항목");
$sheet->setCellValue('L3', "TOTAL\nWELDING\nD/INCH");
$sheet->setCellValue('M3', "COMPLETE\nD/INCH");
$sheet->setCellValue('N3', "WELDING\nPROGRESS\n(%)");
$sheet->setCellValue('O3', "TOTAL\nPWHT QTY");
$sheet->setCellValue('P3', "PWHT\nON\nPROGRESS\nQTY");
$sheet->setCellValue('Q3', "PWHT\nCOMPLETE\nQTY");
$sheet->setCellValue('R3', "Walk Down\nReady");
$sheet->setCellValue('S3', "Punch W/D");
$sheet->setCellValue('S4', "SUBCON\nWalk Down");
$sheet->setCellValue('T4', "HTENG\nWalk Down");
$sheet->setCellValue('U3', "A Punch\nClear DATE");
$sheet->setCellValue('V3', "TEST DATE");
$sheet->setCellValue('V4', "Plan");
$sheet->setCellValue('W4', "Request");
$sheet->setCellValue('X4', "Actual");
$sheet->setCellValue('Y4', "B Punch");
$sheet->setCellValue('Z4', "Flushing");
$sheet->setCellValue('AA4', "Box-Up");
$sheet->setCellValue('AB3', "REMARK");
$sheet->getStyle("A3:AB4")->getFont()->setSize(10);
$sheet->getStyle("A3:I4")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('004377');
$sheet->getStyle("J3:U4")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('0067B4');
$sheet->getStyle("V3:AB4")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('1199FF');
$sheet->getStyle("A3:AB4")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A3:AB4')->getFont()->getColor()->setARGB('FFFFFF');

// 셀 병합
$sheet->mergeCells("A3:A4");
$sheet->mergeCells("B3:B4");
$sheet->mergeCells("C3:C4");
// $sheet->mergeCells("D3:D4");
$sheet->mergeCells("D3:I3");
$sheet->mergeCells("J3:J4");
$sheet->mergeCells("K3:K4");
$sheet->mergeCells("L3:L4");
$sheet->mergeCells("M3:M4");
$sheet->mergeCells("N3:N4");
$sheet->mergeCells("O3:O4");
$sheet->mergeCells("P3:P4");
$sheet->mergeCells("Q3:Q4");
$sheet->mergeCells("R3:R4");
// $sheet->mergeCells("S3:S4");
// $sheet->mergeCells("T3:T4");
$sheet->mergeCells("S3:T3");
$sheet->mergeCells("U3:U4");
$sheet->mergeCells("V3:AA3");
$sheet->mergeCells("AB3:AB4");

// 들여쓰기
$sheet->getStyle("A3:AB4")->getAlignment()->setWrapText(true);

$url = "http://wcfservice.htenc.co.kr/apipwim/getpackage?jno={$jno}";

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
    $pkgData = $responseResult->Value;
    for($i=0; $i < count($responseResult->Value); $i++) {
        // SUBCON
        $sheet->setCellValue('A'.$rowCnt, $pkgData[$i]->COMPANY_NAME);
        // NO.
        $sheet->setCellValue('B'.$rowCnt, $pkgData[$i]->NO);
        // PKG. NO
        $sheet->setCellValue('C'.$rowCnt, $pkgData[$i]->PKG_NO);
        // NDE%
        // $nde = str_replace(",", "", $pkgData[$i]->NDE);
        // $sheet->setCellValue('D'.$rowCnt, $nde);
        // if($nde == 0 || $nde == ''){
        //     $sheet->getStyle("D{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        // } else if(strpos($nde, ".")) {
        //     $sheet->getStyle("D{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.0#');
        // } else {
        //     $sheet->getStyle("D{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0');
        // }
        // Fluid
        $sheet->setCellValue('D'.$rowCnt, $pkgData[$i]->FLUID);
        // Line No
        $sheet->setCellValue('E'.$rowCnt, $pkgData[$i]->LINE_NO);
        // Line Class
        // $sheet->setCellValue('F'.$rowCnt, $pkgData[$i]->LINE_CLASS);
        // Test Fluid
        $sheet->setCellValue('F'.$rowCnt, $pkgData[$i]->TEST_FLUID);
        // Operating Pressure
        $operationPressure = str_replace(",", "", $pkgData[$i]->OPERATION_PRESSURE);
        $sheet->setCellValue('G'.$rowCnt, $operationPressure);
        if($operationPressure == 0 || $operationPressure == ''){
            $sheet->getStyle("G{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else if(strpos($operationPressure, ".")) {
            $sheet->getStyle("G{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.0#');
        } else {
            $sheet->getStyle("G{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0');
        }
        // Design Pressure
        $designPressure = str_replace(",", "", $pkgData[$i]->DESIGN_PRESSURE);
        $sheet->setCellValue('H'.$rowCnt, $designPressure);
        if($designPressure == 0 || $designPressure == ''){
            $sheet->getStyle("H{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else if(strpos($designPressure, ".")) {
            $sheet->getStyle("H{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.0#');
        } else {
            $sheet->getStyle("H{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0');
        }
        // Test Pressure
        $testPressure = str_replace(",", "", $pkgData[$i]->TEST_PRESSURE);
        $sheet->setCellValue('I'.$rowCnt, $testPressure);
        if($testPressure == 0 || $testPressure == ''){
            $sheet->getStyle("I{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else if(strpos($testPressure, ".")) {
            $sheet->getStyle("I{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.0#');
        } else {
            $sheet->getStyle("I{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0');
        }
        // Method CLIENT
        $sheet->setCellValue('J'.$rowCnt, $pkgData[$i]->METHOD_CLIENT);
        // 인허가 항목
        $sheet->setCellValue('K'.$rowCnt, $pkgData[$i]->LICENSING);
        // TOTAL WELDING D/INCH
        $totalDiaInch = str_replace(",", "", $pkgData[$i]->TOTAL_DIA_INCH);
        $sheet->setCellValue('L'.$rowCnt, $totalDiaInch);
        if($totalDiaInch == 0 || $totalDiaInch == ''){
            $sheet->getStyle("L{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else if(strpos($totalDiaInch, ".")) {
            $sheet->getStyle("L{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.00');
        } else {
            $sheet->getStyle("L{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0');
        }
        // COMPLETE D/INCH
        $completeDiaInch = str_replace(",", "", $pkgData[$i]->COMPLETE_DIA_INCH);
        $sheet->setCellValue('M'.$rowCnt, $completeDiaInch);
        if($completeDiaInch == 0 || $completeDiaInch == ''){
            $sheet->getStyle("M{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else if(strpos($completeDiaInch, ".")) {
            $sheet->getStyle("M{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.00');
        } else {
            $sheet->getStyle("M{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0');
        }
        // WELDING PROGRESS(%)
        $weldingProgress = str_replace(",", "", $pkgData[$i]->WELDING_PROGRESS);
        $sheet->setCellValue('N'.$rowCnt, $weldingProgress);
        if($weldingProgress == 0 || $weldingProgress == ''){
            $sheet->getStyle("N{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else if(strpos($weldingProgress, ".")) {
            $sheet->getStyle("N{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.0#');
        } else {
            $sheet->getStyle("N{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0');
        }
        // TOTAL PWHT QTY
        $totalPwht = str_replace(",", "", $pkgData[$i]->TOTAL_PWHT);
        $sheet->setCellValue('O'.$rowCnt, $totalPwht);
        if($totalPwht == 0 || $totalPwht == ''){
            $sheet->getStyle("O{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else if(strpos($totalPwht, ".")) {
            $sheet->getStyle("O{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.0#');
        } else {
            $sheet->getStyle("O{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0');
        }
        // PWHT ON PROGRESS QTY
        $pwhtProgress = str_replace(",", "", $pkgData[$i]->PWHT_PROGRESS);
        $sheet->setCellValue('P'.$rowCnt, $pwhtProgress);
        if($pwhtProgress == 0 || $pwhtProgress == ''){
            $sheet->getStyle("P{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else if(strpos($pwhtProgress, ".")) {
            $sheet->getStyle("P{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.0#');
        } else {
            $sheet->getStyle("P{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0');
        }
        // PWHT COMPLETE QTY
        $completePwht = str_replace(",", "", $pkgData[$i]->COMPLETE_PWHT);
        $sheet->setCellValue('Q'.$rowCnt, $completePwht);
        if($completePwht == 0 || $completePwht == ''){
            $sheet->getStyle("Q{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else if(strpos($completePwht, ".")) {
            $sheet->getStyle("Q{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.0#');
        } else {
            $sheet->getStyle("Q{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0');
        }
        // Walk Down Ready
        $sheet->setCellValue('R'.$rowCnt, $pkgData[$i]->WALK_DOWN_READY);
        // SUBCON Walk Down
        $sheet->setCellValue('S'.$rowCnt, $pkgData[$i]->SUBCON_WALK_DOWN);
        // HTENG Walk Down
        $sheet->setCellValue('T'.$rowCnt, $pkgData[$i]->HTENC_WALK_DOWN);
        // A Punch Clear DATE
        $sheet->setCellValue('U'.$rowCnt, $pkgData[$i]->A_PUNCH_CLEAR_DATE);
        // Plan
        $sheet->setCellValue('V'.$rowCnt, $pkgData[$i]->PLAN);
        // Request
        $sheet->setCellValue('W'.$rowCnt, $pkgData[$i]->REQUEST);
        // Actual
        $sheet->setCellValue('X'.$rowCnt, $pkgData[$i]->ACTUAL);
        // B Punch
        $sheet->setCellValue('Y'.$rowCnt, $pkgData[$i]->B_PUNCH);
        // Flushing
        $sheet->setCellValue('Z'.$rowCnt, $pkgData[$i]->FLUSHING);
        // Box-Up
        $sheet->setCellValue('AA'.$rowCnt, $pkgData[$i]->BOX_UP);
        // REMARK
        $sheet->setCellValue('AB'.$rowCnt, $pkgData[$i]->REMARK);

        $rowCnt++;
    }
}


// 들여쓰기
$sheet->getStyle('C5:C'.$rowCnt)->getAlignment()->setIndent(1);
$sheet->getStyle('E5:E'.$rowCnt)->getAlignment()->setIndent(1);
$sheet->getStyle('G5:I'.$rowCnt)->getAlignment()->setIndent(1);
$sheet->getStyle('L5:Q'.$rowCnt)->getAlignment()->setIndent(1);
$sheet->getStyle('AB5:AB'.$rowCnt)->getAlignment()->setIndent(1);

// 표 그리기
$rowCnt--;
$sheet->getStyle("A1:AB{$rowCnt}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
$sheet->getStyle("Z1:Z2")->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);
$sheet->getStyle("AA1:AA2")->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);
$sheet->getStyle("A3:AB3")->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
$sheet->getStyle("AB3:AB{$rowCnt}")->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
$sheet->getStyle("A{$rowCnt}:AB{$rowCnt}")->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

// TEST DATE 항목 배경색 
$sheet->getStyle("V5:AB{$rowCnt}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('E7F3FF');

// 행 가운데 정렬
$sheet->getStyle('A5:B'.$rowCnt)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('D5:D'.$rowCnt)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('F5:F'.$rowCnt)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('G5:I'.$rowCnt)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
$sheet->getStyle('F5:F'.$rowCnt)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('J5:K'.$rowCnt)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('R5:AA'.$rowCnt)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

// 셀 높이
for($i = 1; $i <= $rowCnt; $i++) {
    if($i != 3 || $i != 4) {
        $sheet->getRowDimension($i)->setRowHeight(22);
    }
}

// 텍스트 맞춤
$sheet->getStyle("A1:AD{$rowCnt}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

// 칼럼 사이즈
$sheet->getColumnDimension('A')->setWidth(15);
$sheet->getColumnDimension('B')->setWidth(5);
$sheet->getColumnDimension('C')->setWidth(28);
$sheet->getColumnDimension('D')->setWidth(6);
$sheet->getColumnDimension('E')->setWidth(150);
$sheet->getColumnDimension('F')->setWidth(10);
$sheet->getColumnDimension('G')->setWidth(10);
$sheet->getColumnDimension('H')->setWidth(10);
$sheet->getColumnDimension('I')->setWidth(10);
$sheet->getColumnDimension('J')->setWidth(17);
$sheet->getColumnDimension('K')->setWidth(17);
$sheet->getColumnDimension('L')->setWidth(12);
$sheet->getColumnDimension('M')->setWidth(12);
$sheet->getColumnDimension('N')->setWidth(12);
$sheet->getColumnDimension('O')->setWidth(12);
$sheet->getColumnDimension('P')->setWidth(12);
$sheet->getColumnDimension('Q')->setWidth(12);
$sheet->getColumnDimension('R')->setWidth(12);
$sheet->getColumnDimension('S')->setWidth(12);
$sheet->getColumnDimension('T')->setWidth(12);
$sheet->getColumnDimension('U')->setWidth(12);
$sheet->getColumnDimension('V')->setWidth(12);
$sheet->getColumnDimension('W')->setWidth(12);
$sheet->getColumnDimension('X')->setWidth(12);
$sheet->getColumnDimension('Y')->setWidth(12);
$sheet->getColumnDimension('Z')->setWidth(12);
$sheet->getColumnDimension('AA')->setWidth(12);
$sheet->getColumnDimension('AB')->setWidth(12);
$sheet->getRowDimension(3)->setRowHeight(30);
$sheet->getRowDimension(4)->setRowHeight(30);

// 틀 고정
$sheet->freezePane("D5");

// 파일명
$title = "PKG LIST_{$jobName}_{$date}";
$title = rawurlencode($title);

// Rename worksheet
$sheet->setTitle("PKG LIST");
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
