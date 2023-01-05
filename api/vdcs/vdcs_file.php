<?php if(!defined("_API_INCLUDE_")) exit;

require_once __DIR__ . "/../_mime_type.php";

ini_set("display_errors", "On");
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
//ini_set('memory_limit','512M');
ini_set('memory_limit','2048M');
//ini_set('memory_limit','10240M');
ini_set('default_socket_timeout', 180);
set_time_limit(0);

//header('Content-Type: text/html; charset=UTF-8');
//header('Content-Type: text/plain');
//header('Content-Type: text/plain; charset=UTF-8');

$isArchiveMultiFileToZip = false;
$doc_no = -1;
$job_no = "";
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
            //echo $docNoListStr;
            //exit;
            //$isMultiToZip = true;
        }
    }
}

//BEGIN SQL ==================
/*
$SQL_DocInfo = "SELECT BIND_JNO JNO, TR_NO
    , DOC_NO, DOC_NUM, DOC_REV_NUM, DOC_TITLE
    , DOC_FILE_CHECK, DOC_FILE_NAME, DOC_FILE_SAVE, DOC_FILE_PATH, DOC_FILE_SIZE, DOC_FILE_TYPE
    , DOC_STATUS, DEPLOY_DATE, DEPLOY_UNO
    , DEPLOY_FILE_CHECK, DEPLOY_FILE_NAME, DEPLOY_FILE_SAVE, DEPLOY_FILE_PATH, DEPLOY_FILE_SIZE, DEPLOY_FILE_TYPE
    , IS_USE, REG_DATE, MOD_DATE 
FROM " . VDCS_TABLES::VDCS_VPDC_SET . " 
WHERE BIND_JNO = {$jno} 
";
*/
$SQL_DocInfo = "WITH A AS(
    SELECT 
        DC.BIND_JNO JNO, J.JOB_NO, DC.TR_NO, DC.DOC_NO, DC.DOC_NUM, DC.DOC_REV_NUM, DC.DOC_TITLE
        , TR.TR_FUNC_NO, TFT.FUNC_CD TR_FUNC_CD
        , DC.DOC_FUNC_NO, DFT.FUNC_CD DOC_FUNC_CD
        , DC.DOC_STATUS, DRT.CODE_NAME DOC_STATUS_NAME, DRT.CODE_NAME_NICK DOC_STATUS_NICK, DRT.DESCR DOC_STATUS_DESCR
        , DC.DEPLOY_DATE, DC.DEPLOY_UNO , DC.DEPLOY_FILE_CHECK, DC.DEPLOY_FILE_NAME, DC.DEPLOY_FILE_SAVE, DC.DEPLOY_FILE_PATH, DC.DEPLOY_FILE_SIZE, DC.DEPLOY_FILE_TYPE 
        , DC.DOC_FILE_CHECK, DC.DOC_FILE_NAME, DC.DOC_FILE_SAVE, DC.DOC_FILE_PATH, DC.DOC_FILE_SIZE, DC.DOC_FILE_TYPE 
        , DC.IS_USE, DC.REG_DATE, DC.MOD_DATE 
    FROM " . VDCS_TABLES::VDCS_VPDC_SET . " DC
        , " . VDCS_TABLES::VDCS_VPTR_SET . " TR
        , " . VDCS_TABLES::JOB_INFO . " J
        , " . VDCS_TABLES::SYS_FUNC_TYPE . " DFT
        , " . VDCS_TABLES::SYS_FUNC_TYPE . " TFT
        , (SELECT * FROM " . VDCS_TABLES::SYS_DOC_RESULT_TYPE . " WHERE CODE_GROUP_NO = 7) DRT
    WHERE 1 = 1
        AND DC.TR_NO = TR.TR_NO
        AND TR.JNO = J.JNO(+) 
        AND DC.DOC_FUNC_NO = DFT.FUNC_NO(+) 
        AND TR.TR_FUNC_NO = TFT.FUNC_NO(+) 
        AND DC.DOC_STATUS = DRT.CODE_CD(+)
)
SELECT * FROM A
WHERE 1 = 1
    AND JNO = {$jno}
";
if($requestVdcsModelType == RequestVdcsModelType::DocDistributeDownload || 
        $requestVdcsModelType == RequestVdcsModelType::DocReplyDownload || 
        $requestVdcsModelType == RequestVdcsModelType::DocLatestDownload)
{
    if(!$doc_no) exit;  
    //echo $docNoListStr;
    //exit;
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
//echo $SQL;
//exit;
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
    if(isset($_SERVER) && $_SERVER["REMOTE_ADDR"] == "10.10.103.221")
    {
        //$Fun->print_($SQL);
	//exit;
    }
    $db->query($SQL);
    $nCount = intval($db->nf()??0);

    $DownloadFileInfo = null;
    
    if($nCount > 0)
    {
        $DownloadFileListArr = array();
        $DocumentInfoListArr = array();
        while($db->next_record())
        {
            $rec = null;
            $row = $db->Record;
            //print_r($row);
            //exit;
            $job_no = $db->f("job_no");
            if($db->f("doc_status") && $db->f("deploy_file_save"))
            {
                $strRuleBaseFileName = "{$db->f("tr_func_cd")}\\{$db->f("doc_num")}_r{$db->f("doc_rev_num")} {$db->f("doc_title")}【RE-{$db->f("doc_status_nick")}】.pdf";
                $rec = array(
                    "file_check" => $db->f("deploy_file_check"),
                    "file_name" => $strRuleBaseFileName, //$db->f("deploy_file_name"),
                    "file_save" => $db->f("deploy_file_save"),
                    "file_path" => $db->f("deploy_file_path"),
                    "file_size" => $db->f("deploy_file_size"),
                    "file_type" => $db->f("deploy_file_type"),
                );
            }
            
            if(!$rec)
            {
                $strRuleBaseFileName = "{$db->f("tr_func_cd")}\\{$db->f("doc_num")}_r{$db->f("doc_rev_num")} {$db->f("doc_title")}【DE】.pdf";
                $rec = array(
                    "file_check" => $db->f("doc_file_check"),
                    "file_name" => $strRuleBaseFileName, //$db->f("doc_file_name"),
                    "file_save" => $db->f("doc_file_save"),
                    "file_path" => $db->f("doc_file_path"),
                    "file_size" => $db->f("doc_file_size"),
                    "file_type" => $db->f("doc_file_type"),
                );
            }
            //$Fun->print_r($rec);
            //exit;
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
        require_once __DIR__ . "/../file/php_service.php";
        exit;
    }
}
else if($requestVdcsModelType == RequestVdcsModelType::LatestZipDownload || $requestVdcsModelType == RequestVdcsModelType::LatestZipInfo)
{
    $SQL = "WITH A AS
(
    SELECT A.*, TO_CHAR(A.file_date, 'YYYY-MM-DD HH24:MI') FILE_DATE_STR FROM " . VDCS_TABLES::DOCS_PUBL_FILE . " A 
    WHERE A.jno = {$jno} AND A.MAJOR_CD = 'VDCS' AND A.MINOR_CD = 'LATEST' 
    ORDER BY A.REG_DATE DESC, A.CNO DESC
)
SELECT A.* FROM A WHERE ROWNUM <= 1"
;
    $db->query($SQL);
    if($db->next_record())
    {
        @ini_set('memory_limit','10240M');
        @ini_set('post_max_size','10000M');
        @ini_set('default_socket_timeout', -1);
        $nCount = intval($db->nf()??0);
        $rec = $db->Record;
        //$dateFileDate = new DateTime($db->f("file_date"));
        //$strFileDate = $dateFileDate->format("Y-m-d");
        $strFileSizeUnit = $Fun->getStrSize($db->f("file_size"));
        $DownloadFileInfo = array(
            "jno" => $db->f("jno"),
            "cno" => $db->f("cno"),
            "major_cd" => $db->f("major_cd"),
            "minor_cd" => $db->f("minor_cd"),
            "file_check" => $db->f("file_check"),
            "file_name" => $db->f("file_name"),
            "file_save" => $db->f("file_save"),
            "file_path" => $db->f("file_path"),
            "file_type" => $db->f("file_type"),
            "file_size" => $db->f("file_size"),
            "file_size_str" => $strFileSizeUnit,
            "file_date" => $db->f("file_date"),
            "file_date_str" => $db->f("file_date_str"),
            "file_json_check" => $db->f("file_json_check"),
            "file_json_count" => $db->f("file_json_count"),
            "file_ori_size" => $db->f("file_ori_size")
        );
        //print_r($DownloadFileInfo);
        //exit;
        $FileFullName = "F:\\" . $db->f("file_path") . "\\" . $db->f("file_save");
        $FileOriginalName = $db->f("file_name");
        
        
        if($requestVdcsModelType == RequestVdcsModelType::LatestZipDownload){
            require_once __DIR__ . "/../file/php_download.php";
        }
        else if($requestVdcsModelType == RequestVdcsModelType::LatestZipInfo)
        {
            //echo $SQL;
            //exit;
            $Result["ResultType"] = ResultType::Success->value;
            SetResultValue($DownloadFileInfo);
            DoPrintResultResponse(true);
        }
        exit;
    }
}
require_once __DIR__ . "/../file/wcf_service.php";
exit;