<?php
    require_once "func.php";

    error_reporting(E_ALL);
    ini_set("display_errors", 1);

    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body, true);
    
    $pageNo = $data["pageNo"];
    $totalCnt = $data["totalCnt"];
    $customPageUnit = $data["customPageUnit"];

    $pageList = getPageList($pageNo, $totalCnt, $customPageUnit);


    $result = array(
        "pageList" => $pageList
    );

    echo json_encode($result);
?>