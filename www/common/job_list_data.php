<?php
require_once "../_inc.php";

$jobCondition = $_GET["jobCondition"];

if($jobCondition == "STAFF") {
    $getCondition = "&filter={$jobCondition}&uno={$user->uno}";
} else {
    $getCondition = "&filter={$jobCondition}";
}

$jobList = array();

$url = "http://vp.htenc.co.kr/api/common/job/?api_key=d6c814548eeb6e41722806a0b057da30&api_pass=BQRUQAMXBVY={$getCondition}";

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

for($i=0; $i < count($responseResult->Value); $i++) {
    $jobData = $responseResult->Value;

    $jobList[] = array(
        "jno" => $jobData[$i]->jno,
        "jobNo" => $jobData[$i]->job_no,
        "compName" => $jobData[$i]->comp_name,
        "orderCompName" => $jobData[$i]->order_comp_name,
        "jobName" => $jobData[$i]->job_name,
        "userName" => $jobData[$i]->job_pm_name,
        "jobSd" => $jobData[$i]->job_sd_str,
        "jobEd" => $jobData[$i]->job_ed_str,
        "jobState" => $jobData[$i]->job_state_name,
        "locName" => $jobData[$i]->job_loc_name,
        "jobCode" => $jobData[$i]->job_code_name,
        "jobType" => $jobData[$i]->job_type_name
    );
}

header("Content-type: application/json"); 
echo "{\"data\":" .json_encode($jobList). "}";
?>