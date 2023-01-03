<?php 
//require_once __DIR__ . '/_api.php';
//if(!defined("_API_INCLUDE_")) exit;

if(!defined("_LIB_INCLUDE_"))
{
    require_once __DIR__ . "/../_lib.php";
}
require_once __DIR__ . "/_mime_type.php";

ini_set("display_errors", "On");
//@error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
@error_reporting(E_ALL & ~E_NOTICE);

@header("Content-Type:text/html;charset=utf-8");
@header("access-control-allow-origin: *");
@header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
@header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
@header("Cache-Control: no-store, no-cache, must-revalidate");
@header("Cache-Control: post-check=0, pre-check=0", false);
@header("Pragma: no-cache");

$post = $Fun->getVars();
$remote_addr = $_SERVER["REMOTE_ADDR"];

if(isset($post) && is_array($post) && array_key_exists("doc_no", $post))
{
    $request_model_type = strtoupper($post["mode"]);
}