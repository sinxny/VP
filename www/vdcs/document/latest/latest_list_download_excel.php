<?php
ini_set('memory_limit','-1');
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

// require_once "../../../_inc.php";
require_once "../../../vendor/autoload.php";

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

//폰트사이즈
$spreadsheet->getDefaultStyle()->getFont()->setSize(10);
// 헤더 폰트 굵게
$sheet->getStyle('A1:O2')->getFont()->setBold(true);

$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(2, 2);

$jno = $_GET["jno"];
$jobName = $_GET["jobName"];

// 헤더
$sheet->setCellValue('A1', "JNO : " . $jno );
$sheet->setCellValue('C1', "PROJECT : " . $jobName);

$sheet->setCellValue('A2', "공종");
$sheet->setCellValue('B2', "문서번호");
$sheet->setCellValue('C2', "Rev.");
$sheet->setCellValue('D2', "문서제목");
$sheet->setCellValue('E2', "Vendor");
$sheet->setCellValue('F2', "TR No.");
$sheet->setCellValue('G2', "배포일");
$sheet->setCellValue('H2', "회신일");
$sheet->setCellValue('I2', "RFQ. NO.");
$sheet->setCellValue('J2', "RFQ. Title");
$sheet->setCellValue('K2', "Item / Tag No.");
$sheet->setCellValue('L2', "Count");
$sheet->setCellValue('M2', "Result #");

// 헤더 틀 고정
$spreadsheet->getActiveSheet()->freezePane("A3");

// 헤더 배경색 지정
$sheet->getStyle('A2:M2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('DCDCDC');

$url = "http://vp.htenc.co.kr/api/vdcs/?api_key=d6c814548eeb6e41722806a0b057da30&api_pass=BQRUQAMXBVY=&jno={$jno}&mode=latest";

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
    $rowCnt = 3;
    for($i=0; $i < count($responseResult->Value); $i++) {
    $latestData = $responseResult->Value;

        // 공종
        $sheet->setCellValue('A'.$rowCnt, $latestData[$i]->doc_func_cd);
        // 문서번호
        $sheet->setCellValue('B'.$rowCnt, $latestData[$i]->doc_num);
        // Rev.
        $sheet->setCellValue('C'.$rowCnt, $latestData[$i]->doc_rev_num);
        // 문서제목
        $sheet->setCellValue('D'.$rowCnt, $latestData[$i]->doc_title);
        // 제작사
        $sheet->setCellValue('E'.$rowCnt, $latestData[$i]->from_comp_name);
        // TR No.
        $sheet->setCellValue('F'.$rowCnt, $latestData[$i]->tr_doc_num);
        // 배포일
        $sheet->setCellValue('G'.$rowCnt, $latestData[$i]->doc_distribute_date_str);
        // 회신일
        $sheet->setCellValue('H'.$rowCnt, $latestData[$i]->doc_reply_date_str);
        // RFQ. No.
        $sheet->setCellValue('I'.$rowCnt, $latestData[$i]->doc_rfq_num);
        // RFQ. Title
        $sheet->setCellValue('J'.$rowCnt, $latestData[$i]->doc_rfq_title);
        // 아이템/태그
        $sheet->setCellValue('K'.$rowCnt, $latestData[$i]->doc_tag_item);
        // Cnt
        $sheet->setCellValue('L'.$rowCnt, $latestData[$i]->doc_cnt);
        // Rslt #
        $sheet->setCellValue('M'.$rowCnt, $latestData[$i]->doc_status_nick);

        $rowCnt++;
    }
}

// 들여쓰기
$sheet->getStyle('B3:B'.$rowCnt)->getAlignment()->setIndent(1);
$sheet->getStyle('D3:D'.$rowCnt)->getAlignment()->setIndent(1);
$sheet->getStyle('F3:F'.$rowCnt)->getAlignment()->setIndent(1);
$sheet->getStyle('I3:I'.$rowCnt)->getAlignment()->setIndent(1);
$sheet->getStyle('J3:J'.$rowCnt)->getAlignment()->setIndent(1);
$sheet->getStyle('K3:K'.$rowCnt)->getAlignment()->setIndent(1);

// 자동 필터
$spreadsheet->getActiveSheet()->setAutoFilter('A2:M'.$rowCnt);

// 표 그리기
$rowCnt--;
$sheet->getStyle('A2:M'.$rowCnt)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

// 헤더 칼럼 가운데 정렬
$sheet->getStyle('G1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('H1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
$sheet->getStyle('A2:M2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

// 행 가운데 정렬
$sheet->getStyle('A3:A'.$rowCnt)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('C3:C'.$rowCnt)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('E3:E'.$rowCnt)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('G3:H'.$rowCnt)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('L3:M'.$rowCnt)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

// 셀 높이
for($i = 1; $i <= $rowCnt; $i++) {
    $sheet->getRowDimension($i)->setRowHeight(-1);
}

// 텍스트 맞춤
$sheet->getStyle('A1:M'.$rowCnt)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

//자동 줄바꿈
$sheet->getStyle('A3:M'.$rowCnt)->getAlignment()->setWrapText(true);

// 칼럼 사이즈 자동 조정
$sheet->getColumnDimension('A')->setWidth(6);
$sheet->getColumnDimension('B')->setAutoSize(true);
// $sheet->getColumnDimension('B')->setWidth(30);
$sheet->getColumnDimension('C')->setWidth(5);
$sheet->getColumnDimension('D')->setAutoSize(true);
// $sheet->getColumnDimension('D')->setWidth(50);
$sheet->getColumnDimension('E')->setWidth(16);
$sheet->getColumnDimension('F')->setWidth(28);
$sheet->getColumnDimension('G')->setWidth(11);
$sheet->getColumnDimension('H')->setWidth(11);
// $sheet->getColumnDimension('I')->setWidth(18);
$sheet->getColumnDimension('I')->setAutoSize(true);
// $sheet->getColumnDimension('J')->setAutoSize(true);
$sheet->getColumnDimension('J')->setWidth(35);
$sheet->getColumnDimension('K')->setWidth(60);
// $sheet->getColumnDimension('K')->setAutoSize(true);
$sheet->getColumnDimension('L')->setWidth(6);
$sheet->getColumnDimension('M')->setWidth(7);

// 확대/축소
$sheet->getSheetView()->setZoomScale(90);

$today = new DateTime();
$title = $jno . "_VDCS_Latest_List";

setcookie("fileDownload", true, 0, "/");
// Rename worksheet
$sheet->setTitle($title);
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
