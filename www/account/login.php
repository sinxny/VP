<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

include "../_inc.php";

$request_model_type = $_POST["mode"];

if ("LOGIN" == $request_model_type) {
    $userId = $_POST["login_user_id"];
    $password = $_POST["login_password"];

    if($user->setLogin($userId, $password, $login_message)){
        $isLogin = true;
    }
    // LG 개별 로그인
    else if ($userId == "lgchem" && $password == "lgchem") {
        $isLogin = true;
        $_SESSION["user"]["user_name"] = "LG화학";
        $_SESSION["user"]["user_id"] = $userId;
        $_SESSION["user"]["is_attend"] = "LG";
        $_SESSION["user"]["uno"] = "LG";
    }

    $result = array(
        "isLogin" => $isLogin,
        "msg" => $login_message
    );

    echo json_encode($result);
}
?>