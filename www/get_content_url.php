<?php
$subMenu = $_GET["subMenu"];



switch($subMenu) {
    //최신문서(Latest)
    case "vpLatest" :
        $pageUrl = "vdcs/document/latest/latest_view.php";
    default: 
        $pageUrl = "vdcs/document/latest/latest_view.php";
        break;
}

if(isset($_SERVER) && $_SERVER["REMOTE_ADDR"] == "10.10.103.221")
{
    //$Fun->print_($SQL);
    //exit;
    //$pageUrl = "vdcs/document/latest/latest_view_jhp.php";
}

$result = array(
    "pageUrl" => $pageUrl
);

echo json_encode($result);
?>