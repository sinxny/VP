<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$jno = $_POST["jno"];

$url = "http://wcfservice.htenc.co.kr/hipapi/eqlistindex";

$data = array(
    "jno" => $jno
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

echo json_encode($responseResult);
?>