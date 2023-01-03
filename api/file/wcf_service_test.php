<?php if(!defined("_API_INCLUDE_")) exit;

if(!isset($isArchiveMultiFileToZip) || $isArchiveMultiFileToZip != true || !isset($DownloadFileListArr) || !is_array($DownloadFileListArr))
{
    $isArchiveMultiFileToZip = false;
}
$isDeleteArchiveMultiFileToZip = false;
$isArchiveMultiFileToZipUseWcf = false;


$FileFullName = null;
$FileSize = -1;
$SaveName = null;
$TempFullName = null;

if($isArchiveMultiFileToZip == true)
{
    $saveRemoteTempDirPath = "AppStorageFiles\\Temp\\VDCS\\Latest";
    if($isArchiveMultiFileToZipUseWcf == true)
    {
        $saveRemoteTempDirPath .= "\\CreateDirectoryWeb";
    }
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
    $isDir_CreateArchiveWeb = $retvalCreateDirectoryWeb->CreateDirectoryWebResult->Result == "True" ? true : false;
    if($isDir_CreateArchiveWeb == true)
    {
        $filesForCompression = array();
        if($isArchiveMultiFileToZipUseWcf != true)
        {
            //exit;
        }
        else
        {
            
        }
        foreach ($DownloadFileListArr as $_row => $_val) 
        {
            $filesForCompression[] = $_val["file_path"] . "\\" . $_val["file_save"];
            //$Fun->print_r($filesMD5ChecksumValue);
            //exit;
        }
        $filesMD5ChecksumValue = md5(json_encode($filesForCompression));
        //echo $filesMD5ChecksumValue;
        //$saveZipFileName = $saveRemoteTempDirPath . "\\" . date("Ymd") . "T" . date("His") . "_" . str_pad($jno, 5, "0",STR_PAD_LEFT) . "_VDCS_Latest_{$filesMD5ChecksumValue}.zip";
        $TempFullName = $saveRemoteTempDirPath . "\\" . str_pad($jno, 5, "0",STR_PAD_LEFT) . "_VDCS_Latest_{$filesMD5ChecksumValue}.zip";
        
        $retvalCheckFileExistWeb = $client->CheckFileExistWeb(array(
                        'strFileName' => $TempFullName,
                    ));
        //print_r($retvalCheckFileExistWeb);
        //exit;
        $isFileExist = $retvalCheckFileExistWeb->CheckFileExistWebResult->Result == "True" ? true : false;
        
        
        
        
        if($isFileExist == false)
        {
            if($isArchiveMultiFileToZipUseWcf == true)
            {
                $retvalCreateArchiveWeb = $client->CreateArchiveWeb(array(
                        'strSaveZipFileName' => $TempFullName,
                        'strFilesForCompression' => $filesForCompression
                    ));
                $strErrorMessage = $retvalCreateArchiveWeb->CreateArchiveWebResult->ErrorMessage;
                if($strErrorMessage)
                {
                    echo $strErrorMessage;
                    exit;
                }
                $TempFullName = $retvalCreateArchiveWeb->CreateArchiveWebResult->Result;

                $retvalCheckFileExistWeb = $client->CheckFileExistWeb(array(
                            'strFileName' => $TempFullName,
                        ));
                $isFileExist = $retvalCheckFileExistWeb->CheckFileExistWebResult->Result == "True" ? true : false;
            }   
            else
            {
                
            }
        }
        
        if($isFileExist != true)
        {
            $FileFullName = null;
            $DownloadFileInfo = null;
            $isArchiveMultiFileToZip = false;
            $isDeleteArchiveMultiFileToZip = false;
        }
        $DownloadFileInfo = array(
                    "file_check" => $filesMD5ChecksumValue,
                    "file_name" => basename($TempFullName),
                    "file_save" => basename($TempFullName),
                    "file_path" => $saveRemoteTempDirPath,
                    "file_size" => -1,
                    "file_type" => "application/x-zip-compressed",
                );
    }
}

if($isArchiveMultiFileToZip == true)
{
    //$FileSize = strlen($contents);
    //var_dump($DownloadFileInfo);
    //echo "aaaaa";
    //exit;
}

if(!isset($DownloadFileInfo) || !is_array($DownloadFileInfo)) exit;



if(!$DownloadFileInfo["file_name"])
{
    $DownloadFileInfo["file_name"] = $DownloadFileInfo["file_save"];
}
if($DownloadFileInfo["file_size"])
{
    $FileSize = intval($DownloadFileInfo["file_size"]??0);
}

if($DownloadFileInfo["file_save"] && $DownloadFileInfo["file_path"])
{
    $FileFullName = $DownloadFileInfo["file_path"] . "\\" . $DownloadFileInfo["file_save"];
}
if(!$FileFullName) exit;



if (substr( $FileFullName, 0, 3 ) == "G:\\") 
{
    $FileFullName = str_replace("G:\\", "", $FileFullName);
}
$ext = strrchr($FileFullName, '.');



$SaveName = trim($DownloadFileInfo["file_name"]);
if(!$SaveName)
{
    $SaveName = basename($FileFullName);// . "." . $ext;
}

$wsdl = 'http://file.hi-techeng.co.kr/transferweb/Service1.svc?singleWsdl';
$client = new SoapClient($wsdl, array(
        'trace' => true,
        'encoding'=>'UTF-8',
        'exceptions'=>true,
        'cache_wsdl'=>WSDL_CACHE_NONE,
        'soap_version' => SOAP_1_1
));
//$FileFullName = "eula.1028.txt";

$retvalDownloadFileWeb = $client->DownloadFileWeb(array('strFileName' => $FileFullName));
//print_r($retval);
echo $retvalDownloadFileWeb->DownloadFileWebResult->ErrorMessage;
$contents = base64_decode($retvalDownloadFileWeb->DownloadFileWebResult->FileBinary);

if($contents)
{
    
    
    if( ($isDirect == true || $isWebView == true) && $ext != ".zip")
    {
        $mimeType = $DownloadFileInfo["file_type"];
        if(!$mimeType)
        {
            $mimeType = $Fun->getMimeType($FileFullName);
        }
        if($mimeType)
        {
            @header("Content-type: " . $mimeType);
        }
        else
        {
            //@header("Content-type: application/octet-stream");
            @header("Content-type: application/force-download");
        }
        if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Edge') !== false)) 
        {
            $SaveName = rawurlencode($SaveName);
            @header("Content-Disposition: inline; filename='" . $SaveName . "'");
        }
        //IE
        else if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE || strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== FALSE) 
        {
            $SaveName = iconv("UTF-8","EUC-KR//TRANSLIT", $SaveName);
            @header("Content-Disposition: inline; filename=" . $SaveName);
        }
        else 
        {
            @header("Content-Disposition: inline; filename=\"" . $SaveName . "\"");
        }
    }
    else 
    {
        @header("Content-type: application/force-download");
        /*
        if($mimeType)
        {
            @header("Content-type: " . $mimeType);
        }
        else
        {
            //@header("Content-type: application/octet-stream");
            @header("Content-type: application/force-download");
        }
        
        */

        //@header('Content-Disposition: attachment; filename="' . basename($SaveName) . '"');
        //IE EDGE
        if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Edge') !== false)) 
        {
                $SaveName = rawurlencode($SaveName);
                @header("Content-Disposition: attachment; filename='" . $SaveName . "'");
        }
        //IE
        else if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE || strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== FALSE) 
        {
                $SaveName = iconv("UTF-8","EUC-KR//TRANSLIT", $SaveName);
                @header("Content-Disposition: attachment; filename=" . $SaveName);
        }
        else 
        {
                @header("Content-Disposition: attachment; filename=\"" . $SaveName . "\"");
        }
    }

    if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Edge') !== false)) 
    {
            @header('Cache-Control: private, no-transform, no-store, must-revalidate');
            //@header("Content-Disposition: attachment; filename='" . $SaveName . "'");
            @header('Pragma: no-cache');
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
    
    if($FileSize)
    {
        @header("Content-Length: {$FileSize}");
    }
    
    //실제 내용을 출력 함으로써 다운로드 실행
    echo $contents;
    
    if($TempFullName && $isArchiveMultiFileToZip == true && $isDeleteArchiveMultiFileToZip == true && $ext == ".zip" && $FileSize)
    {
        $extTemp = strrchr($TempFullName, '.');
        if($extTemp == ".zip")
        {
            try
            {
                $retvalDeleteFileWeb = $client->DeleteFileWeb(array('strFileName' => $TempFullName));
            } catch (Exception $ex) {
                ;;
            }
        }
    }
    exit;
}