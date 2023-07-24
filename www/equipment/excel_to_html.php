<?php
// error_reporting(E_ALL);
// ini_set('display_errors', '1');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Html;

require_once "../vendor/autoload.php";

$url = "http://wcfservice.htenc.co.kr/hipapi/eqlisttempl";

$request_body = file_get_contents('php://input');
$data = json_decode($request_body, true);

$jno = $data["jno"];
$index = $data["index"];
$indexname = $data["indexname"];

$data = array(
    "jno" => $jno,
    "index" => $index,
    "indexname" => $indexname
);

$jsonData = json_encode($data);

$curl = curl_init();

curl_setopt_array($curl, array(
        // CURLOPT_PORT => "80",
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_POST => 1,
    CURLOPT_POSTFIELDS => $jsonData,
    // CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array(
        "cache-control: no-cache",
        "content-type: application/json; charset=utf-8"
    ),
));

$response = curl_exec($curl);
    // $err = curl_error($curl);
curl_close($curl);

$responseResult = json_decode($response);

$fileVtempFilePathalue = '';
if($responseResult->ResultType == "Success") {
    $fileValue = $responseResult->Value;

    $decodedData = base64_decode($fileValue);

    $tempFilePath = tempnam(sys_get_temp_dir(), 'excel');
    file_put_contents($tempFilePath, $decodedData);
} else {
    exit;
}

// Excel 파일 경로
// $inputFile = 'EQ_List.xlsx';

// .xlsx 파일 로드
$spreadsheet = IOFactory::load($tempFilePath);

$sheet = $spreadsheet->getActiveSheet();

// 인쇄 영역 값 가져오기
$pageSetup = $sheet->getPageSetup();
$printArea = $pageSetup->getPrintArea();

$maxRow = preg_replace("/[A-Za-z]+\d+:[A-Za-z]+/", "", $printArea);
$maxCol = preg_replace("/([A-Za-z]+\d+:)([A-Za-z]+)(\d+)/", "$2", $printArea);
$maxCol = getAlphabetOrder($maxCol);

// HTML 작성기 생성
$htmlWriter = new Html($spreadsheet);

$htmlContent = $htmlWriter->generateHTMLHeader();
$htmlContent .= $htmlWriter->generateStyles(true);
$htmlContent .= $htmlWriter->generateSheetData($maxRow, $maxCol);

$htmlContent = str_replace('_x000d_', '', $htmlContent);

// HTML 출력
echo $htmlContent;

// 알파벳 순서 확인 함수
function getAlphabetOrder($letter) {
    $lowercaseLetter = strtolower($letter); // 대소문자 구분 없이 처리하기 위해 소문자로 변환
    $order = ord($lowercaseLetter) - ord('a') + 1;
    return $order;
}
?>