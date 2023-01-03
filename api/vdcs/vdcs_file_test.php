<?php if(!defined("_API_INCLUDE_")) exit;

require_once __DIR__ . "/../_mime_type.php";

ini_set("display_errors", "On");
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
//ini_set('memory_limit','512M');
ini_set('memory_limit','2048M');
ini_set('default_socket_timeout', 180);
set_time_limit(0);

//header('Content-Type: text/html; charset=UTF-8');
//header('Content-Type: text/plain');
//header('Content-Type: text/plain; charset=UTF-8');

$isArchiveMultiFileToZip = false;
$doc_no = 15529;
$docNoListArr = null;
$docNoListStr = null;
$DownloadFileInfo = null;
$DownloadFileListArr = null;

if(isset($post) && is_array($post) && array_key_exists("doc_no", $post))
{
    $doc_no = $post["doc_no"];
    //$Fun->print_r($doc_no);
    //exit;
    if($doc_no)
    {
        $docNoListArr = explode(",", $doc_no);
        if($docNoListArr && is_array($docNoListArr) && count($docNoListArr) > 1)
        {
            $docNoListStr = implode(",", $docNoListArr);
            //$isMultiToZip = true;
        }
    }
}

//BEGIN SQL ==================
$SQL_DocInfo = "SELECT BIND_JNO JNO, TR_NO
    , DOC_NO, DOC_NUM
    , DOC_FILE_CHECK, DOC_FILE_NAME, DOC_FILE_SAVE, DOC_FILE_PATH, DOC_FILE_SIZE, DOC_FILE_TYPE
    , DOC_STATUS, DEPLOY_DATE, DEPLOY_UNO
    , DEPLOY_FILE_CHECK, DEPLOY_FILE_NAME, DEPLOY_FILE_SAVE, DEPLOY_FILE_PATH, DEPLOY_FILE_SIZE, DEPLOY_FILE_TYPE
    , IS_USE, REG_DATE, MOD_DATE 
FROM " . VDCS_TABLES::VDCS_VPDC_SET . " 
WHERE BIND_JNO = {$jno} 
";
if($requestVdcsModelType == RequestVdcsModelType::DocDistributeDownload || 
        $requestVdcsModelType == RequestVdcsModelType::DocReplyDownload || 
        $requestVdcsModelType == RequestVdcsModelType::DocLatestDownload)
{
    if(!$doc_no) exit;  
    
    if($docNoListStr)
    {
        $SQL = $SQL_DocInfo . " AND DOC_NO IN ({$docNoListStr}) ";
    }
    else
    {
        $SQL = $SQL_DocInfo . " AND DOC_NO = {$doc_no} ";
    }
}
//END SQL ==================
if(!$SQL) exit;

$DownloadFileInfo= null;

$isDirect = false;
if(isset($post) && is_array($post) && array_key_exists("direct", $post))
{
    switch(strtoupper($post["direct"])){
        case "Y" :
        case "1" :
        case "TRUE":
            $isDirect = true;
            break;
    }
}

$isWebView = false;
if(isset($post) && is_array($post) && array_key_exists("webview", $post))
{
    switch(strtoupper($post["webview"])){
        case "Y" :
        case "1" :
        case "TRUE":
            $isWebView = true;
            break;
    }
}


if($requestVdcsModelType == RequestVdcsModelType::DocDistributeDownload)
{
    //echo $SQL;
    $db->query($SQL);
    if($db->next_record())
    {
        $nCount = intval($db->nf()??0);
        /*
        $is_private = $db->f("is_private");
        $is_success = "N";
        if($is_private == "Y" && $is_conn_private != "Y")
        {
            echo "[Block external downloads.] 외부 다운로드를 차단 합니다.";
            $is_success = "B";
            exit;
        }
        */
        $rec = $db->Record;
        //$Fun->print_r($rec);
        
        $DownloadFileInfo = array(
            "file_check" => $db->f("doc_file_check"),
            "file_name" => $db->f("doc_file_name"),
            "file_save" => $db->f("doc_file_save"),
            "file_path" => $db->f("doc_file_path"),
            "file_size" => $db->f("doc_file_size"),
            "file_type" => $db->f("doc_file_type"),
        );
        //require_once __DIR__ . "/../file/wcf/wcf_service.php";
    }
}
else if($requestVdcsModelType == RequestVdcsModelType::DocReplyDownload)
{
    $db->query($SQL);
    if($db->next_record())
    {
        $nCount = intval($db->nf()??0);
        /*
        $is_private = $db->f("is_private");
        $is_success = "N";
        if($is_private == "Y" && $is_conn_private != "Y")
        {
            echo "[Block external downloads.] 외부 다운로드를 차단 합니다.";
            $is_success = "B";
            exit;
        }
        */
        $rec = $db->Record;
        //$Fun->print_r($rec);

        $DownloadFileInfo = array(
            "file_check" => $db->f("deploy_file_check"),
            "file_name" => $db->f("deploy_file_name"),
            "file_save" => $db->f("deploy_file_save"),
            "file_path" => $db->f("deploy_file_path"),
            "file_size" => $db->f("deploy_file_size"),
            "file_type" => $db->f("deploy_file_type"),
        );
    }
}
else if($requestVdcsModelType == RequestVdcsModelType::DocLatestDownload)
{
    $db->query($SQL);
    $nCount = intval($db->nf()??0);
    
    $DownloadFileInfo = null;
    
    if($nCount > 0)
    {
        $DownloadFileListArr = array();
        while($db->next_record())
        {
            //$rec = null;
            //$row = $db->Record;
            if($db->f("doc_status") && $db->f("doc_file_save"))
            {
                $rec = array(
                    "file_check" => $db->f("deploy_file_check"),
                    "file_name" => $db->f("deploy_file_name"),
                    "file_save" => $db->f("deploy_file_save"),
                    "file_path" => $db->f("deploy_file_path"),
                    "file_size" => $db->f("deploy_file_size"),
                    "file_type" => $db->f("deploy_file_type"),
                );
            }
            else
            {
                $rec = array(
                    "file_check" => $db->f("doc_file_check"),
                    "file_name" => $db->f("doc_file_name"),
                    "file_save" => $db->f("doc_file_save"),
                    "file_path" => $db->f("doc_file_path"),
                    "file_size" => $db->f("doc_file_size"),
                    "file_type" => $db->f("doc_file_type"),
                );
            }
            //$Fun->print_r($rec);
            $DownloadFileListArr[] = $rec;
            //$filesForCompression[] = $rec["file_path"] . "\\" . $rec["file_save"];
        }
        if($nCount > 1 && is_array($DownloadFileListArr) && count($DownloadFileListArr) > 0)
        {
            $isArchiveMultiFileToZip = true;
            $saveRemoteTempDirPath = "AppStorageFiles\\Temp\\CreateArchiveWeb";
            if(isset($DownloadFileListArr) && is_array($DownloadFileListArr))
            {
                $wsdl = _WSDL_TRANSFERWEB_URL_;
                $client = new SoapClient($wsdl, array(
                        'trace' => true,
                        'encoding'=>'UTF-8',
                        'exceptions'=>true,
                        'cache_wsdl'=>WSDL_CACHE_NONE,
                        'soap_version' => SOAP_1_1
                ));
                
                //임시 폴더 생성
                $retvalCreateDirectoryWeb = $client->CreateDirectoryWeb(array(
                    'strCreateDirectory' => $saveRemoteTempDirPath
                ));
            }
            //print_r($DownloadFileListArr) ;
            //exit;
        }
        else
        {
            $isArchiveMultiFileToZip = false;
            $DownloadFileInfo = $DownloadFileListArr[0];
            $DownloadFileListArr = null;
        }
    }
}
require_once __DIR__ . "/../file/wcf_service_test.php";
exit;