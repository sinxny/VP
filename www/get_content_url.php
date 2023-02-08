<?php
$subMenu = $_POST["subMenu"];

switch($subMenu) {
    //최신문서(Latest)
    case "vpLatest" :
        $pageUrl = "vdcs/document/latest/latest_view.php";
        break;
    case "WELDING_DAY" :
        $pageUrl = "welding/welding_day_view.php";
        break;
    // case "Report2" :
    //     $pageUrl = "vdcs/document/latest/latest_view.php";
    // case "Report3" :
    //     $pageUrl = "vdcs/document/latest/latest_view.php";
    // case "Report4" :
    //     $pageUrl = "vdcs/document/latest/latest_view.php";
    // case "Report5" :
    //     $pageUrl = "vdcs/document/latest/latest_view.php";
    // default: 
    //     $pageUrl = "vdcs/document/latest/latest_view.php";
    //     break;
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