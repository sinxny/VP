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

$result = array(
    "pageUrl" => $pageUrl
);

echo json_encode($result);
?>