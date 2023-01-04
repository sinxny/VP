<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
@header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
@header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
@header("Cache-Control: no-store, no-cache, must-revalidate" );
@header("Cache-Control: post-check=0, pre-check=0", false);
@header("Pragma: no-cache");

$_src = null;
$jno = null;
$doc_no = null;

if( isset($_REQUEST) && !is_null($_REQUEST) )
{
	if(array_key_exists("jno", $_REQUEST))
	{
		$jno = $_REQUEST["jno"];
	}
	if(array_key_exists("doc_no", $_REQUEST))
	{
		$doc_no = $_REQUEST["doc_no"];
	}
	$_src = "/pdfjs-3.0.279-dist/web/viewer.php?model=DOC_LE_DOWNLOAD&jno={$jno}&doc_no={$doc_no}&webview=Y";
	//echo $_src;
}
if( !isset($_src) || is_null($_src) || !$_src ) exit;
?><!DOCTYPE>
<html>
<head>
<meta charset="utf-8" />
<title>VDCS - PDF WebViewer</title>
<style>
	body {
		margin:0;
	}
	iframe {
		width:100%; height:100%;
		margin:0px 0px 0px 0px;
		padding:0px 0px 0px 0px;
	}
</style>
</head>
<body>
	<iframe name="vdcsPdfViewer" src="<?php echo $_src; ?>" width="100%" height="100%" frameborder="0" align="absmiddle" scrolling="no" seamless></iframe>
</body>
</html>