<?php

$jno = $_GET["jno"];

ini_set( "display_errors", 1 );
$url = "http://wcfservice.htenc.co.kr/apipwim/getndeiso?jno={$jno}";

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
    $ndeData = $responseResult->Value;

    if(!is_string($ndeData)) {
        for($i=0; $i < count($responseResult->Value); $i++) {
            $ndeData[$i]->NO = $i+1;
        }
    } else {
        $ndeData = null;
    }
    
    echo json_encode($ndeData);
}
?>