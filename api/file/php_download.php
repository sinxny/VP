<?php if(!defined("_API_INCLUDE_")) exit;

if(!isset($FileFullName) || !$FileFullName) exit;

ini_set("display_errors", "On");
//@error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
error_reporting(E_ALL & ~E_NOTICE);

ini_set('memory_limit','10240M');
ini_set('post_max_size','10000M');
ini_set('default_socket_timeout', -1);
ini_set('default_socket_timeout', 180);
set_time_limit(0);

$FileSize = -1;
$SaveName = null;
//$FileFullName = "F:\\AppStorageFiles\\Publish\\HIBIZ\\VDCS\\Latest\\17045\\20221229_VDCS_Latest_All_17045_21YS-TT-KPB -0083.zip"; //LP(II)
//$FileFullName = "F:\\AppStorageFiles\\Publish\\HIBIZ\\VDCS\\Latest\\16601\\20221229_VDCS_Latest_All_16601_21YS-TT-KMCI-0040.zip"; //MDI&FOX
if(isset($FileOriginalName) && $FileOriginalName)
{
    $SaveName = basename($FileOriginalName);// . "." . $ext;
}
if(!isset($SaveName) || !$SaveName)
{
    $SaveName = basename($FileFullName);// . "." . $ext;
}
$SaveName = iconv("UTF-8","EUC-KR//TRANSLIT", $SaveName);
$SaveName = rawurlencode($SaveName);

//header("Content-Type: application/octet-stream");
header("Content-Type: application/force-download");
header('Content-Transfer-Encoding: binary');
header("Content-Disposition: attachment; filename=\"" . basename($SaveName) . "\"");
/*
if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Edge') !== false)) 
{
        header('Cache-Control: private, no-transform, no-store, must-revalidate');
        //@header("Content-Disposition: attachment; filename='" . $SaveName . "'");
        header('Pragma: no-cache');
}
//IE
else if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE || strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== FALSE) 
{
        @header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        //@header("Content-Disposition: attachment; filename=" . $SaveName);
        @header('Pragma: public');
}
else 
{
        @header('Cache-Control: private, no-transform, no-store, must-revalidate');
        //@header("Content-Disposition: attachment; filename=\"" . $SaveName . "\"");
        @header('Pragma: no-cache');
}
*/
header("Cache-Control:cache,must-revalidate");
header("Pragma:no-cache");

header('Expires: 0');
//header('Accept-Ranges: bytes');

$FileSize = filesize($FileFullName);
if(isset($FileSize) && !is_null($FileSize) && $FileSize && intval($FileSize) > 0)
{
    header("Content-Length: {$FileSize}");
}

ob_clean();
flush();

/*
readfile($FileFullName);
setcookie("fileDownload", true, 0, "/");
ob_flush();
flush();
die();
*/
//ob_end_clean();
//$contents = base64_decode($retvalDownloadFileWeb->DownloadFileWebResult->FileBinary);
//readfile($FileFullName);

if ($fp = fopen($FileFullName, "rb")) //isset($_SERVER) && $_SERVER["REMOTE_ADDR"] == "10.10.103.221" && 
{
    //fpassthru($fp);
    //@fclose($fp);
    while(!feof($fp)){
        $buf = fread($fp,8192); //4096 = 4MB, 8192 = 8MB
        $read = strlen($buf);
        print($buf);
        //ob_flush();
        //flush();
        //
        //echo fgets($fp);
        //ob_flush();
        //flush();
    }
    @fclose($fp);
}
else
{
    readfile($FileFullName);
}
//echo $contents;
setcookie("fileDownload", true, 0, "/");
flush();
sleep(2);
die();