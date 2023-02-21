<?php
error_reporting(E_ALL);
session_start();
ini_set("display_errors", 1);
@header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
@header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
@header("Cache-Control: no-store, no-cache, must-revalidate" );
@header("Cache-Control: post-check=0, pre-check=0", false);
@header("Pragma: no-cache");

$isLogin = @$_SESSION["user"]["user_id"];

if(isset($isLogin)) {	
	$_src = null;
	$jno = null;
	$doc_no = null;
	$pdfPage = null;
	
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
		if(array_key_exists("pdfPage", $_REQUEST))
		{
			$pdfPage = $_REQUEST["pdfPage"];
		}
		if(array_key_exists("model", $_REQUEST))
		{
			$model = $_REQUEST["model"];
		} else {
			$model = "DOC_LE_DOWNLOAD";
		}
		$_src = "/pdfjs-3.0.279-dist/web/viewer.php?model=DOC_LE_DOWNLOAD&jno={$jno}&doc_no={$doc_no}&webview=Y&pdfPage={$pdfPage}&model={$model}";
		//echo $_src;
	}
	if( !isset($_src) || is_null($_src) || !$_src ) exit;
?><!DOCTYPE>
<html>
<head>
<meta charset="utf-8" />
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
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
	<div>
		<iframe name="vdcsPdfViewer" src="<?php echo $_src; ?>" width="100%" height="100%" frameborder="0" align="absmiddle" scrolling="no" seamless></iframe>
	</div>
	<script>
		var isStaff = '<?php echo $_SESSION["user"]["is_attend"]?>';
		if(isStaff == "N") {
			url = "../api/common/job/authority.php";
			data = {
				jno: <?php echo $jno?>
			}
			axios.post(url, data)
			.then(function(response) {
				var externalRight = response["data"]["externalRight"];
				sessionStorage.setItem("externalRight", externalRight);
				if(externalRight == "N") {
					alert("해당 PROJECT에 접근 권한이 없습니다.");
					location.href = document.location.origin;
				}
			})
			.catch(function(error){
				console.log(error);
			});
		}
	</script>
</body>
</html>
<?php 
} else {
	require_once "header.php";
	require_once "account/login_view.php";
}
?>