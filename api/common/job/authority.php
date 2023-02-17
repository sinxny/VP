<?php require_once '../../_inc.php';
    ini_set("display_error", 1);
    session_start();
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body, true);

    $jno = $data["jno"];
    $uno = $_SESSION["user"]["uno"];

    $SQL = "SELECT *
            FROM S_JOB_MEMBER_LIST
            WHERE JNO = {$jno}
            AND UNO = {$uno}";
    $db->query($SQL);
    if($db->nf() == 0) {
        $externalRight = "N";
    } else {
        $externalRight = "Y";
    }

    $result = array(
        "externalRight" => $externalRight
    );

    echo json_encode($result);
?>