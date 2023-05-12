<?php
ini_set('memory_limit','-1');
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
ini_set( "display_errors", 1 );

// require_once "../../../_inc.php";
require_once "../vendor/autoload.php";
require_once "../common/func.php";

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
$today = $_GET["today"];
$nextday = $_GET["nextday"];

// 헤더
$printDate = new DateTime();
$dateTime = $printDate->format('Y-m-d');
$sheet->setCellValue('C1', $jobName);
$sheet->getStyle('C1')->getFont()->setSize(16);
$sheet->setCellValue('A1', "Welding Monthly");
$sheet->mergeCells("A1:B2");
// $sheet->getStyle("A1:A2")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A1:C2")->getFont()->setBold(true);

$sheet->setCellValue('A3', "업체명\nCompany");
$sheet->setCellValue('B3', "구역\nArea");
$sheet->setCellValue('C3', "재질\nMaterial\nGroup");
$sheet->setCellValue('D3', "총 물량 (D/I)\nTotal");
$sheet->setCellValue('E3', "누계 (D/I)\nPrevious");
$sheet->setCellValue('F3', "선택 기간 물량_Work Dia-inch for Date (D/I)");

$sheet->mergeCells("A3:A4");
$sheet->mergeCells("B3:B4");
$sheet->mergeCells("C3:C4");
$sheet->mergeCells("D3:D4");
$sheet->mergeCells("E3:E4");

// 헤더 행높이
$sheet->getRowDimension(3)->setRowHeight(25);
$sheet->getRowDimension(4)->setRowHeight(25);

$url = "http://wcfservice.htenc.co.kr/apipwim/getweldingmonth?jno={$jno}&today={$today}&nextday={$nextday}";

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
    $weldingHeader = (array) $responseResult->Value[0];
    $weldingHeader = array_keys($weldingHeader);

    // 날짜 헤더 데이터 추출
    $dataHeader = array();
    foreach($weldingHeader as $value) {
        $dateReg = "/^([0-9]{4})-([0-9]{2})-([0-9]{2})/";
        if(preg_match($dateReg, $value)) {
            array_push($dataHeader, $value);
        }
    }

    $col='F';
    foreach($dataHeader as $key => $date) {
        $sheet->setCellValue($col."4", $date);
        if($key != count($dataHeader) - 1) {
            $col++;
        }
    }

    $sheet->mergeCells("F3:{$col}3");
    $col++;
    $sheet->setCellValue($col."3", "합 계 (D/I)\nAccumulative");
    $sheet->mergeCells("{$col}3:{$col}4");
    $sheet->mergeCells("C1:{$col}2");
    $col++;
    $sheet->setCellValue($col."1", "Print Date");
    $sheet->setCellValue($col."2", "날짜 Date");
    $sheet->getStyle($col."2")->getFont()->setBold(true);
    $sheet->setCellValue($col."3", "잔여물량 (D/I)\nRemain");
    $sheet->mergeCells("{$col}3:{$col}4");
    $periodCol = $col;
    $col++;
    $sheet->setCellValue($col."1", $dateTime);
    $sheet->setCellValue($col."2", $today . " ~ ". $nextday);
    $sheet->getStyle($col."2")->getFont()->setBold(true);
    $preCol = $col;
    $sheet->setCellValue($col."3", "진행률 (%)\nWork Progress");
    $sheet->mergeCells("{$col}3:{$col}4");
    $lastCol = ++$col;
    $sheet->mergeCells("{$preCol}1:{$lastCol}1");
    $sheet->mergeCells("{$preCol}2:{$lastCol}2");
    $sheet->getStyle("{$preCol}2:{$lastCol}2")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("{$periodCol}2:{$lastCol}2")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');
    $sheet->setCellValue($lastCol."3", "비고\nRemark");
    $sheet->mergeCells("{$lastCol}3:{$lastCol}4");
    $sheet->getStyle("A3:{$lastCol}4")->getFont()->setSize(10);
    $sheet->getStyle("A3:{$lastCol}4")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('BDD7EE');
    $sheet->getStyle("A1:{$lastCol}4")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    
    // 줄바꿈
    $sheet->getStyle("A3:{$lastCol}3")->getAlignment()->setWrapText(true);

    $rowCnt = 5;
    $isSameCom = '';
    $isSameArea = '';
    $comStRow = '';
    $areaStRow = '';
    for($i=0; $i < count($responseResult->Value); $i++) {
        $weldingData = $responseResult->Value;
        // Company
        if($isSameCom != $weldingData[$i]->Company) {
            $comEdRow = $rowCnt - 1;
            if($weldingData[$i]->Level == "0") {
                $sheet->setCellValue('A'.$rowCnt, "종합 물량 합계_" . $weldingData[$i]->Company);
            } else {
                $sheet->setCellValue('A'.$rowCnt, $weldingData[$i]->Company);
            }
            if($rowCnt != 5 && ($comEdRow - $comStRow != 0)) {
                $sheet->mergeCells("A{$comStRow}:A{$comEdRow}");
            }
            $comStRow = $rowCnt;
        }
        $isSameCom = $weldingData[$i]->Company;
        // Area
        if($isSameArea != $weldingData[$i]->Area) {
            $areaEdRow = $rowCnt - 1; 
            if($rowCnt != 5 && ($areaEdRow - $areaStRow != 0)) {
                $sheet->mergeCells("B{$areaStRow}:B{$areaEdRow}");
            }
            if($weldingData[$i]->Level == 2) {
                if($weldingData[$i]->Area == "Welding Sum") {
                    $sheet->setCellValue('B'.$rowCnt, "용접 합계_" . $weldingData[$i]->Area);
                } else {
                    $sheet->setCellValue('B'.$rowCnt, "비용접 합계_" . $weldingData[$i]->Area);
                }
            } else {
                $sheet->setCellValue('B'.$rowCnt, $weldingData[$i]->Area);
            }
            // $sheet->getStyle('B'.$rowCnt)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('A9D08E');
            $areaStRow = $rowCnt;
        }
        $isSameArea = $weldingData[$i]->Area;
        // Material Group
        if($weldingData[$i]->Level > 2 || !$weldingData[$i]->Level) {
            $sheet->setCellValue('C'.$rowCnt, $weldingData[$i]->{'Material Group'});
        }
        // $sheet->getStyle('C'.$rowCnt)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('E2EFDA');
        if($weldingData[$i]->Level == 3) {
            $sheet->getStyle("C{$rowCnt}:{$lastCol}{$rowCnt}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2CC');
        } else if($weldingData[$i]->Level == 2) {
            $sheet->mergeCells("B{$rowCnt}:C{$rowCnt}");
            $sheet->getStyle("B{$rowCnt}:{$lastCol}{$rowCnt}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FCE4D6');
        } else if($weldingData[$i]->Level == 1) {
            $sheet->mergeCells("A{$rowCnt}:C{$rowCnt}");
            $sheet->getStyle("A{$rowCnt}:{$lastCol}{$rowCnt}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('E6E6FA');
        } else if($weldingData[$i]->Level == 0) {
            $sheet->mergeCells("A{$rowCnt}:C{$rowCnt}");
            $sheet->getStyle("A{$rowCnt}:{$lastCol}{$rowCnt}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('F4B084');
        }
        // Total
        $total = str_replace(",", "", $weldingData[$i]->Total);
        $sheet->setCellValue('D'.$rowCnt, $total);
        if($total == 0 || $total == ''){
            $sheet->getStyle("D{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else {
            $sheet->getStyle("D{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.00');
        }
        // Previous
        $previous = str_replace(",", "", $weldingData[$i]->Previous);
        $sheet->setCellValue('E'.$rowCnt, $previous);
        if($previous == 0 || $previous == ''){
            $sheet->getStyle("E{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else {
            $sheet->getStyle("E{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.00');
        }
        // 날짜 별 value
        $col = "F";
        foreach($dataHeader as $date) {
            $dateValue = $weldingData[$i]->{$date};
            $sheet->setCellValue("{$col}{$rowCnt}", $dateValue);
            if($dateValue == 0 || $dateValue == ''){
                $sheet->getStyle("{$col}{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
            } else {
                $sheet->getStyle("{$col}{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.00');
            }
            $col++;
        }
        // Accumulative
        $accumulative = str_replace(",", "", $weldingData[$i]->Accumulative);
        $sheet->setCellValue("{$col}{$rowCnt}", $accumulative);
        if($accumulative == 0 || $accumulative == ''){
            $sheet->getStyle("{$col}{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else {
            $sheet->getStyle("{$col}{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.00');
        }
        $col++;
        // Remain
        $remain = str_replace(",", "", $weldingData[$i]->Remain);
        $sheet->setCellValue("{$col}{$rowCnt}", $remain);
        if($remain == 0 || $remain == ''){
            $sheet->getStyle("{$col}{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else {
            $sheet->getStyle("{$col}{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.00');
        }
        $col++;
        // Work Progress
        $workProgress = $weldingData[$i]->{'Work Progress'};
        $sheet->setCellValue("{$col}{$rowCnt}", $workProgress);
        if($workProgress == 0 || $workProgress == ''){
            $sheet->getStyle("{$col}{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else {
            $sheet->getStyle("{$col}{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.00');
        }
        $col++;
        // Remark
        $sheet->setCellValue("{$col}{$rowCnt}", $weldingData[$i]->Remark);

        $rowCnt++;
    }
}

// 들여쓰기
$sheet->getStyle("B5:{$lastCol}{$rowCnt}")->getAlignment()->setIndent(1);

// 표 그리기
$rowCnt--;
$sheet->getStyle("A1:{$lastCol}{$rowCnt}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
$sheet->getStyle("{$periodCol}1:{$periodCol}2")->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);
$sheet->getStyle("{$preCol}1:{$preCol}2")->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);

// 행 가운데 정렬
$sheet->getStyle('A5:A'.$rowCnt)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

// 셀 높이
for($i = 1; $i <= $rowCnt; $i++) {
    if($i != 3 && $i != 4) {
        $sheet->getRowDimension($i)->setRowHeight(22);
    }
}

// 텍스트 맞춤
$sheet->getStyle("A1:{$lastCol}{$rowCnt}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

// 칼럼 사이즈
$sheet->getColumnDimension('A')->setWidth(15);
$sheet->getColumnDimension('B')->setWidth(15);
$sheet->getColumnDimension('C')->setWidth(15);
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('E')->setWidth(15);
for($col='F'; true; $col++) {
    $sheet->getColumnDimension($col)->setWidth(15);
    if($col == $lastCol) {
        break;
    }
}

// 틀 고정
$sheet->freezePane("F5");

// 파일명
$title = "WELDING MONTH_{$jobName}_{$today}-{$nextday}";
$title = rawurlencode($title);
// 쉼표 깨짐 현상
$title = str_replace("%2C",',',$title);

// Rename worksheet
$sheet->setTitle("WELDING MONTH");
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
