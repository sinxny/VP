<?php
ini_set("display_errors", 1);
session_start();
require_once '../../../VP/api/_inc.php';

// 기술연구소 목록
$adminList = array();
$SQL = "SELECT U.UNO, U.USER_NAME, U.DUTY_NAME, D.DEPT_PATH_S 
        FROM S_SYS_USER_SET U, COMMON.V_BIZ_DEPT_SET D
        WHERE U.DEPT_ID = D.DEPT_NO
        AND (IS_STATE = 'Y' OR IS_STATE = 'S')
        AND U.DEPT_ID = 90";
$db->query($SQL);
while($db->next_record()) {
    $row = $db->Record;

    $adminList[] = $row["uno"];
}
// 사장님
$adminList[] = '19';
// 부사장님
$adminList[] = '95';
// 그 외 테스트계정
$testList = array(
    "1", "9562", "9716", "9414", "9946"
);

$adminList = array_merge($adminList, $testList);

echo json_encode($adminList);

?>