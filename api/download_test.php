<?php
ini_set("display_errors", "On");
//@error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
error_reporting(E_ALL & ~E_NOTICE);

ini_set('memory_limit','10240M');
ini_set('post_max_size','10000M');
ini_set('default_socket_timeout', -1);
ini_set('default_socket_timeout', 180);
ini_set('max_execution_time', '500');
set_time_limit(0);

$FileSize = -1;
$FileFullName = "F:\\AppStorageFiles\\Publish\\HIBIZ\\VDCS\\Latest\\17045\\20221229_VDCS_Latest_All_17045_21YS-TT-KPB -0083.zip"; //LP(II)
$FileFullName = "F:\\AppStorageFiles\\Publish\\HIBIZ\\VDCS\\Latest\\16601\\20221229_VDCS_Latest_All_16601_21YS-TT-KMCI-0040.zip"; //MDI&FOX

$SaveName = basename($FileFullName);// . "." . $ext;
$SaveName = iconv("UTF-8","EUC-KR//TRANSLIT", $SaveName);
$SaveName = rawurlencode($SaveName);

//@header("Content-type: application/octet-stream");
header("Content-type: application/force-download");
header("Content-Disposition: attachment; filename=\"" . basename($SaveName) . "\"");

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

@header('Expires: 0');
@header('Content-Transfer-Encoding: binary');
@header('Accept-Ranges: bytes');

$FileSize = filesize($FileFullName);
if(isset($FileSize) && !is_null($FileSize) && $FileSize && intval($FileSize) > 0)
{
    @header("Content-Length: {$FileSize}");
}

ob_clean();
flush();
//$contents = base64_decode($retvalDownloadFileWeb->DownloadFileWebResult->FileBinary);

readfile($FileFullName);
//echo $contents;
die();