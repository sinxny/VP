<?php
ini_set("display_errors", 1);
session_start();
require_once '../../../VP/api/_inc.php';

$domain = $_SERVER["HTTP_HOST"];

// 관리자 리스트
$url = "{$domain}/admin/admin_list.php";

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
    CURLOPT_SSL_VERIFYPEER => false
));

$response = curl_exec($curl);
    // $err = curl_error($curl);
curl_close($curl);

$responseResult = json_decode($response);
$adminList = implode(',', $responseResult);

$SQL = "SELECT U.UNO, U.USER_NAME, U.DUTY_NAME, D.DEPT_PATH_S 
        FROM S_SYS_USER_SET U, COMMON.V_BIZ_DEPT_SET D
        WHERE U.DEPT_ID = D.DEPT_NO
        AND (IS_STATE = 'Y' OR IS_STATE = 'S')
        AND U.UNO NOT IN ($adminList)
        ORDER BY DECODE(U.DEPT_CD, 'AA1340', 1), DECODE(U.DEPT_CD, 'AA1370', 2), U.DEPT_CD,
        DECODE(U.JOBDUTY_CD, 'B1', 1), DECODE(U.JOBDUTY_CD, 'B2', 2), DECODE(U.JOBDUTY_NAME, '팀장', 3), DECODE(U.JOBDUTY_CD, 'D3', 2), DECODE(U.JOBDUTY_CD, 'M1', 2), U.JOBDUTY_ID, 
        U.DUTY_CD, U.JOIN_DATE, U.USER_NAME";

$db->query($SQL);
while($db->next_record()) {
    $row = $db->Record;

    $mngUserList[] = array(
        "uno" => $row["uno"],
        "userName" => $row["user_name"],
        "dutyName" => $row["duty_name"],
        "deptPath" => $row["dept_path_s"]
    );
}

$db->disconnect();

header("Content-type: application/json"); 
echo "{\"data\":" .json_encode($mngUserList). "}";
?>