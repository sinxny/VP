<?php
$subMenu = $_POST["subMenu"];

if(strpos($subMenu, "equipment") === 0) {
    list($subMenu, $cno) = explode("_", $subMenu);
}

switch($subMenu) {
    //최신문서(Latest)
    case "vpLatest" :
        $pageUrl = "vdcs/document/latest/latest_view.php";
        break;
    case "w_day" :
        $pageUrl = "welding/welding_day_view.php";
        break;
    case "w_month" :
        $pageUrl = "welding/welding_month_view.php";
        break;
    case "n_iso" :
        $pageUrl = "welding/nde_by_iso_view.php";
        break;
    case "n_welder" :
        $pageUrl = "welding/nde_by_welder_view.php";
        break;
    case "pkg" :
        $pageUrl = "welding/pkg_list_view.php";
        break;
    case "equipment" :
        $pageUrl = "equipment/equipment_view.php";
        break;
    // cm 권한 없음
    case "noRight" :
        $pageUrl = "no_right.php";
        break;
    default: 
        $pageUrl = "no_right.php";
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