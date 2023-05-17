<?php
ini_set('memory_limit','-1');
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
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
$sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_DEFAULT);
// 용지 크기
$sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
// 자동 맞춤
$sheet->getPageSetup()->setFitToWidth(1);
$sheet->getPageSetup()->setFitToHeight(0);

// 폰트사이즈 / 폰트 이름
$spreadsheet->getDefaultStyle()->getFont()->setSize(11);
$spreadsheet->getDefaultStyle()->getFont()->setName('맑은 고딕');

$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(2, 2);

$jno = $_GET["jno"];
$jobName = $_GET["jobName"];
$weldingDate = $_GET["weldingDate"];
$group = $_GET["group"];

// 헤더
$today = new DateTime();
$dateTime = $today->format('Y-m-d');
$sheet->setCellValue('A1', "Welding Day");
$sheet->mergeCells("A1:B2");
$sheet->setCellValue('C1', $jobName);
$sheet->mergeCells("C1:H2");
$sheet->getStyle('C1')->getFont()->setSize(16);
$sheet->getStyle("A1:H2")->getFont()->setBold(true);
$sheet->setCellValue('I1', "Print Date");
$sheet->setCellValue('J1', $dateTime);
$sheet->setCellValue('I2', "날짜 Date");
$sheet->setCellValue('J2', $weldingDate);
$sheet->getStyle("I2:J2")->getFont()->setBold(true);
$sheet->getStyle("I2:J2")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');
$sheet->getStyle("A1:J2")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

// 헤더 틀 고정
$spreadsheet->getActiveSheet()->freezePane("A4");

// 반복할 행
$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 3);

if($group == "Area") {
    $grpTitle = "구역";
} else if ($group == "Unit") {
    $grpTitle = "경계";
} else if ($group == "Level") {
    $grpTitle = "위치";
}
$sheet->setCellValue('A3', "업체명\nCompany");
$sheet->setCellValue('B3', $grpTitle. "\n" .$group);
$sheet->setCellValue('C3', "재질\nMaterial Group");
$sheet->setCellValue('D3', "총 물량 (D/I)\nTotal");
$sheet->setCellValue('E3', "누계 (D/I)\nPrevious");
$sheet->setCellValue('F3', "금일 물량 (D/I)\nTo Day Work");
$sheet->setCellValue('G3', "합 계 (D/I)\nAccumulative");
$sheet->setCellValue('H3', "잔여물량 (D/I)\nRemain");
$sheet->setCellValue('I3', "진행률 (%)\nWork Progress");
$sheet->setCellValue('J3', "비고\nRemark");

// 줄바꿈
$sheet->getStyle("A3:J3")->getAlignment()->setWrapText(true);

$sheet->getStyle("A3:J3")->getFont()->setSize(10);
$sheet->getStyle("A3:J3")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('BDD7EE');
$sheet->getStyle("A3:J3")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

$url = "http://wcfservice.hi-techeng.co.kr/apipwim/getweldingtoday?jno={$jno}&today={$weldingDate}&group={$group}";

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
    $rowCnt = 4;
    $isSameCom = '';
    $isSameArea = '';
    $comStRow = '';
    $areaStRow = '';
    for($i=0; $i < count($responseResult->Value); $i++) {
    $weldingData = $responseResult->Value;
        // Company
        if($isSameCom != $weldingData[$i]->Company) {
            $comEdRow = $rowCnt - 1;
            if($weldingData[$i]->Step == "0") {
                $sheet->setCellValue('A'.$rowCnt, "종합 물량 합계_" . $weldingData[$i]->Company);
            } else {
                $sheet->setCellValue('A'.$rowCnt, $weldingData[$i]->Company);
            }
            if($rowCnt != 4 && ($comEdRow - $comStRow != 0)) {
                $sheet->mergeCells("A{$comStRow}:A{$comEdRow}");
            }
            $comStRow = $rowCnt;
        }
        $isSameCom = $weldingData[$i]->Company;
        // Area
        if($isSameArea != $weldingData[$i]->{$group}) {
            $areaEdRow = $rowCnt - 1;
            if($rowCnt != 4 && ($areaEdRow - $areaStRow != 0)) {
                $sheet->mergeCells("B{$areaStRow}:B{$areaEdRow}");
            }
            if($weldingData[$i]->Step == 2) {
                if($weldingData[$i]->{$group} == "Welding Sum") {
                    $sheet->setCellValue('B'.$rowCnt, "용접 합계_" . $weldingData[$i]->{$group});
                } else {
                    $sheet->setCellValue('B'.$rowCnt, "비용접 합계_" . $weldingData[$i]->{$group});
                }
            } else {
                $sheet->setCellValue('B'.$rowCnt, $weldingData[$i]->{$group});
            }
            // $sheet->getStyle('B'.$rowCnt)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('A9D08E');
            $areaStRow = $rowCnt;
        }
        $isSameArea = $weldingData[$i]->{$group};
        // Material Group
        if($weldingData[$i]->Step > 2 || !$weldingData[$i]->Step) {
            $sheet->setCellValue('C'.$rowCnt, $weldingData[$i]->{'Material Group'});
        }
        // $sheet->getStyle('C'.$rowCnt)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('E2EFDA');
        if($weldingData[$i]->Step == 3) {
            $sheet->getStyle("C{$rowCnt}:J{$rowCnt}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2CC');
        } else if($weldingData[$i]->Step == 2) {
            $sheet->mergeCells("B{$rowCnt}:C{$rowCnt}");
            $sheet->getStyle("B{$rowCnt}:J{$rowCnt}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FCE4D6');
        } else if($weldingData[$i]->Step == 1) {
            $sheet->mergeCells("A{$rowCnt}:C{$rowCnt}");
            $sheet->getStyle("A{$rowCnt}:J{$rowCnt}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('E6E6FA');
        } else if($weldingData[$i]->Step == 0) {
            $sheet->mergeCells("A{$rowCnt}:C{$rowCnt}");
            $sheet->getStyle("A{$rowCnt}:J{$rowCnt}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('F4B084');
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
        // To Day Work
        $toDayWork = str_replace(",", "", $weldingData[$i]->{'To Day Work'});
        $sheet->setCellValue('F'.$rowCnt, $toDayWork);
        if($toDayWork == 0 || $toDayWork == ''){
            $sheet->getStyle("F{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else {
            $sheet->getStyle("F{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.00');
        }
        // Accumulative
        $accumulative = str_replace(",", "", $weldingData[$i]->Accumulative);
        $sheet->setCellValue('G'.$rowCnt, $accumulative);
        if($accumulative == 0 || $accumulative == ''){
            $sheet->getStyle("G{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else {
            $sheet->getStyle("G{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.00');
        }
        // Remain
        $remain = str_replace(",", "", $weldingData[$i]->Remain);
        $sheet->setCellValue('H'.$rowCnt, $remain);
        if($remain == 0 || $remain == ''){
            $sheet->getStyle("H{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else {
            $sheet->getStyle("H{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.00');
        }
        // Work Progress
        $workProgress = $weldingData[$i]->{'Work Progress'};
        $sheet->setCellValue('I'.$rowCnt, $workProgress);
        if($workProgress == 0 || $workProgress == ''){
            $sheet->getStyle("I{$rowCnt}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING);
        } else {
            $sheet->getStyle("I{$rowCnt}")->getNumberFormat()->setFormatCode('#,##0.00');
        }
        // Remark
        $sheet->setCellValue('J'.$rowCnt, $weldingData[$i]->Remark);
        
        $rowCnt++;
    }
}

// 들여쓰기
$sheet->getStyle('B4:J'.$rowCnt)->getAlignment()->setIndent(1);

// 표 그리기
$rowCnt--;
$sheet->getStyle("A1:J{$rowCnt}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
$sheet->getStyle("I1:I2")->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);
$sheet->getStyle("J1:J2")->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);

// 행 가운데 정렬
$sheet->getStyle('A4:A'.$rowCnt)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('B4:B'.$rowCnt)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

// 셀 높이
for($i = 1; $i <= $rowCnt; $i++) {
    if($i != 3) {
        $sheet->getRowDimension($i)->setRowHeight(22);
    }
}

// 텍스트 맞춤
$sheet->getStyle("A1:J{$rowCnt}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

// 흐림 효과 방지
$sheet->getStyle("Z{$rowCnt}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

// 칼럼 사이즈
$sheet->getColumnDimension('A')->setAutoSize(true);
$sheet->getColumnDimension('B')->setWidth(15);
$sheet->getColumnDimension('C')->setAutoSize(true);
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('E')->setWidth(15);
$sheet->getColumnDimension('F')->setWidth(15);
$sheet->getColumnDimension('G')->setWidth(15);
$sheet->getColumnDimension('H')->setWidth(15);
$sheet->getColumnDimension('I')->setWidth(15);
$sheet->getColumnDimension('J')->setWidth(15);
$sheet->getRowDimension(3)->setRowHeight(40);

// 파일명
$title = "WELD(DAY)_{$jobName}_{$weldingDate}";
$title = rawurlencode($title);
// 쉼표 깨짐 현상
$title = str_replace("%2C",',',$title);

// Rename worksheet
$sheet->setTitle("WELD(DAY)");
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
