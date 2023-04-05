<?php
ini_set("display_errors", 1);
session_start();
require_once '../../../VP/api/_inc.php';

$SQL = "SELECT U.UNO, U.USER_NAME, U.DUTY_NAME, D.DEPT_PATH_S 
        FROM S_SYS_USER_SET U, COMMON.V_BIZ_DEPT_SET D
        WHERE U.DEPT_ID = D.DEPT_NO
        AND (IS_STATE = 'Y' OR IS_STATE = 'S')
        AND U.UNO NOT IN (1, 9562, 9716, 9414, 9946, 95, 19)
        AND U.TEAM_ID NOT IN (90)
        ORDER BY U.DEPT_CD, U.DUTY_CD, U.JOBDUTY_ID, U.JOIN_DATE, U.USER_NAME";

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