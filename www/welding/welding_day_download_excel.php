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
$weldingDate = $_GET["weldingDate"];

// 헤더
$today = new DateTime();
$dateTime = $today->format('Y-m-d H:i');
$sheet->setCellValue('C1', $jobName);
$sheet->setCellValue('D1', $jno);
$sheet->getStyle('D1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
$sheet->setCellValue('I1', "By Day");
$sheet->mergeCells("I1:J1");
$sheet->setCellValue('C2', "해당사항 없는 ITEM은 미출력되도록");
$sheet->setCellValue('H2', "Period");
$sheet->getStyle('H2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
$sheet->setCellValue('I2', "2021-05-06");
$sheet->mergeCells("I2:J2");
$sheet->getStyle("I1:I2")->getFont()->setBold(true);
$sheet->getStyle("I1:I2")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("I2:I2")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');

$sheet->setCellValue('A3', "Company");
$sheet->setCellValue('B3', "Area");
$sheet->setCellValue('C3', "Material Group");
$sheet->setCellValue('D3', "Total");
$sheet->setCellValue('E3', "Previous");
$sheet->setCellValue('F3', "To Day Work");
$sheet->setCellValue('G3', "Accumulative");
$sheet->setCellValue('H3', "Remain");
$sheet->setCellValue('I3', "Work Progress(%)");
$sheet->setCellValue('J3', "Remark");
$sheet->getStyle("A3:J3")->getFont()->setSize(10);
$sheet->getStyle("A3:J3")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('BDD7EE');
$sheet->getStyle("A3:J3")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

$url = "http://wcfservice.hi-techeng.co.kr/apipwim/getweldingtoday?jno={$jno}&today={$weldingDate}";

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
            $sheet->setCellValue('A'.$rowCnt, $weldingData[$i]->Company);
            if($rowCnt != 4 && ($comEdRow - $comStRow != 0)) {
                $sheet->mergeCells("A{$comStRow}:A{$comEdRow}");
            }
            $comStRow = $rowCnt;
        }
        $isSameCom = $weldingData[$i]->Company;
        // Area
        if($isSameArea != $weldingData[$i]->Area) {
            $areaEdRow = $rowCnt - 1;
            if($rowCnt != 4 && ($areaEdRow - $areaStRow != 0)) {
                $sheet->mergeCells("B{$areaStRow}:B{$areaEdRow}");
            }
            $sheet->setCellValue('B'.$rowCnt, $weldingData[$i]->Area);
            $sheet->getStyle('B'.$rowCnt)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('A9D08E');
            $areaStRow = $rowCnt;
        }
        $isSameArea = $weldingData[$i]->Area;
        // Material Group
        if($weldingData[$i]->Level > 2 || !$weldingData[$i]->Level) {
            $sheet->setCellValue('C'.$rowCnt, $weldingData[$i]->{'Material Group'});
        }
        $sheet->getStyle('C'.$rowCnt)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('E2EFDA');
        if($weldingData[$i]->Level == 3) {
            $sheet->getStyle("C{$rowCnt}:J{$rowCnt}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2CC');
        } else if($weldingData[$i]->Level == 2) {
            $sheet->mergeCells("B{$rowCnt}:C{$rowCnt}");
            $sheet->getStyle("B{$rowCnt}:J{$rowCnt}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FCE4D6');
        } else if($weldingData[$i]->Level == 1) {
            $sheet->mergeCells("A{$rowCnt}:C{$rowCnt}");
            $sheet->getStyle("A{$rowCnt}:J{$rowCnt}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('E6E6FA');
        } else if($weldingData[$i]->Level == 0) {
            $sheet->mergeCells("A{$rowCnt}:C{$rowCnt}");
            $sheet->getStyle("A{$rowCnt}:J{$rowCnt}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('F4B084');
        }
        // Total
        $sheet->setCellValue('D'.$rowCnt, $weldingData[$i]->Total);
        // Previous
        $sheet->setCellValue('E'.$rowCnt, $weldingData[$i]->Previous);
        // To Day Work
        $sheet->setCellValue('F'.$rowCnt, $weldingData[$i]->{'To Day Work'});
        // Accumulative
        $sheet->setCellValue('G'.$rowCnt, $weldingData[$i]->Accumulative);
        // Remain
        $sheet->setCellValue('H'.$rowCnt, $weldingData[$i]->Remain);
        // Work Progress
        $sheet->setCellValue('I'.$rowCnt, $weldingData[$i]->{'Work Progress'});
        // Remark
        $sheet->setCellValue('J'.$rowCnt, $weldingData[$i]->Remark);

        $rowCnt++;
    }
}

// 들여쓰기
$sheet->getStyle('B4:J'.$rowCnt)->getAlignment()->setIndent(1);

// 표 그리기
$rowCnt--;
$sheet->getStyle("A3:J{$rowCnt}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

// 행 가운데 정렬
$sheet->getStyle('A4:A'.$rowCnt)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

// 셀 높이
for($i = 1; $i <= $rowCnt; $i++) {
    if($i != 3) {
        $sheet->getRowDimension($i)->setRowHeight(22);
    }
}

// 텍스트 맞춤
$sheet->getStyle("A1:J{$rowCnt}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

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
$sheet->getRowDimension(3)->setRowHeight(33);

// 파일명
$title = "welding_day_report";

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
