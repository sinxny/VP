<?php define("_INC_INCLUDE_", "Include OK");
if(!defined("_LIB_INCLUDE_"))
{
    require_once __DIR__ . "/../_lib.php";
}
error_reporting(E_ALL);
ini_set("display_errors", 1);
@header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
@header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
@header("Cache-Control: no-store, no-cache, must-revalidate" );
@header("Cache-Control: post-check=0, pre-check=0", false);
@header("Pragma: no-cache");

$db = new DB;
//$db->query("SELECT * FROM TAB");

$isLogin = $user->uno;
?>