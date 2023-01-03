<?php if(!defined("_API_INCLUDE_")) exit;

if(!isset($isArchiveMultiFileToZip) || $isArchiveMultiFileToZip != true || !isset($DownloadFileListArr) || !is_array($DownloadFileListArr))
{
    $isArchiveMultiFileToZip = false;
}
$isDeleteArchiveMultiFileToZip = false;
$isArchiveMultiFileToZipUseWcf = true;


$FileFullName = null;
$FileSize = -1;
$SaveName = null;
$TempFullName = null;

if($isArchiveMultiFileToZip == true)
{
    $saveLocalTempDirPath = "F:\\AppStorageFiles";
    if($isArchiveMultiFileToZipUseWcf == true)
    {
        $saveLocalTempDirPath .= "\\CreateDirectoryWeb";
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
    $Fun->mk_dir($saveLocalTempDirPath);
    
    $isDir_CreateArchiveWeb = is_dir($saveLocalTempDirPath);
    
    //$Fun->print_r($DownloadFileListArr);
    //exit;
    
    if($isDir_CreateArchiveWeb == true)
    {
        $filesForCompression = array();
        $namesForCompression = array();
        foreach ($DownloadFileListArr as $_row => $_val) 
        {
            $filesForCompression[] = $_val["file_path"] . "\\" . $_val["file_save"];
            $namesForCompression[] = $_val["file_name"];
            //$Fun->print_r($filesMD5ChecksumValue);
            //exit;
        }
        //$Fun->print_r($filesForCompression);
        //exit;
        //$filesMD5ChecksumValue = md5(json_encode($filesForCompression));
        $filesMD5ChecksumValue = md5(json_encode($DownloadFileListArr));
        //echo $filesMD5ChecksumValue;
        //$saveZipFileName = $saveRemoteTempDirPath . "\\" . date("Ymd") . "T" . date("His") . "_" . str_pad($jno, 5, "0",STR_PAD_LEFT) . "_VDCS_Latest_{$filesMD5ChecksumValue}.zip";
        //$TempFullName = $saveRemoteTempDirPath . "\\" . str_pad($jno, 5, "0",STR_PAD_LEFT) . "_VDCS_Latest_{$filesMD5ChecksumValue}.zip";
        if(isset($job_no) && $job_no)
        {
            $TempFullName = $saveRemoteTempDirPath . "\\" . date("Ymd") . "_VDCS_Latest_Selected_" . str_pad($jno, 5, "0",STR_PAD_LEFT) . "_" . $job_no .".zip";
        }
        else
        {
            $TempFullName = $saveRemoteTempDirPath . "\\" . date("Ymd") . "_VDCS_Latest_Selected_" . str_pad($jno, 5, "0",STR_PAD_LEFT) . ".zip";
        }
        //echo $TempFullName;
        //exit;
        
        $isFileExist = is_file($TempFullName);
        //if($isFileExist == false)
        {
            if($isArchiveMultiFileToZipUseWcf == true)
            {
                $wsdl = 'http://file.htenc.co.kr/transferweb/Service1.svc?singleWsdl';
                $client = new SoapClient($wsdl, array(
                        'trace' => true,
                        'encoding'=>'UTF-8',
                        'exceptions'=>true,
                        'cache_wsdl'=>WSDL_CACHE_NONE,
                        'soap_version' => SOAP_1_1
                ));
                //$FileFullName = "eula.1028.txt";
                //echo $FileFullName;
                //exit;
                
                //$saveLocalTempRootPath = $saveLocalTempDirPath . "\\" . basename($TempFullName) . "■". date("Ymd") . "T" . date("His");// . "■" .  $filesMD5ChecksumValue;
                $saveLocalTempRootPath = $saveLocalTempDirPath . "\\" . date("Y-m-d") . "\\" . date("Ymd") . "T" . date("His") . "■" . str_replace(".zip", "", basename($TempFullName));
                $saveLocalTempZipFullName =  $saveLocalTempRootPath . "\\" . basename($TempFullName);
                
                
                $idx = 0;
                foreach ($filesForCompression as $remoteFullName)
                {
                    $retvalDownloadFileWeb = $client->DownloadFileWeb(array('strFileName' => $remoteFullName));
                    //echo $retvalDownloadFileWeb->DownloadFileWebResult->ErrorMessage;
                    $contents = base64_decode($retvalDownloadFileWeb->DownloadFileWebResult->FileBinary);
                    //$saveLocalTempDirPath
                    $saveFileName = $namesForCompression[$idx];
                    $arrFileName = explode("\\", $saveFileName);
                    $strSubDir = "";
                    if(isset($arrFileName) && is_array($arrFileName) && count($arrFileName) > 1)
                    {
                        $strSubDir = $arrFileName[0];
                        $saveLocalTempWorkPath = $saveLocalTempRootPath . "\\" . $strSubDir;
                        //echo $saveTempFullName;
                        //exit;
                    }
                    else 
                    {
                        $saveLocalTempWorkPath = $saveLocalTempRootPath;
                    }
                    $Fun->mk_dir($saveLocalTempWorkPath);
                    
                    $saveTempFullName = $saveLocalTempWorkPath . "\\" . basename($saveFileName);
                    $myfile = fopen($saveTempFullName, "w") or die("Unable to open file!");
                    fwrite($myfile, $contents);
                    fclose($myfile);
                    
                    $idx++;
                }
                $za = new ZipArchive;
                $za->open($saveLocalTempZipFullName,ZipArchive::CREATE|ZipArchive::OVERWRITE);
                folderToZip($saveLocalTempRootPath, $za);
                $za->close();
                
                header('Content-Type: application/zip');
                header('Content-disposition: attachment; filename='.basename($saveLocalTempZipFullName));
                header('Content-Length: ' . filesize($saveLocalTempZipFullName));
                readfile($saveLocalTempZipFullName);
                //sleep(1);
                @unlink($saveLocalTempZipFullName);
                deleteDir($saveLocalTempRootPath);
                @rmdir($saveLocalTempRootPath);
                //die();
                exit;
            }   
            else
            {
                exit;
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
//echo $FileFullName;
//exit;
$retvalDownloadFileWeb = $client->DownloadFileWeb(array('strFileName' => $FileFullName));

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
    
    if(isset($FileSize) && !is_null($FileSize) && $FileSize && intval($FileSize) > 0)
    {
        @header("Content-Length: {$FileSize}");
    }
    ob_clean();
    flush();

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

function folderToZip($folder, &$zipFile, $subfolder = null) {
    if ($zipFile == null) {
        // no resource given, exit
        return false;
    }
    // we check if $folder has a slash at its end, if not, we append one
    $folder .= end(str_split($folder)) == "/" ? "" : "/";
    $subfolder .= end(str_split($subfolder)) == "/" ? "" : "/";
    // we start by going through all files in $folder
    $handle = opendir($folder);
    while ($f = readdir($handle)) {
        if ($f != "." && $f != "..") {
            if (is_file($folder . $f)) {
                // if we find a file, store it
                // if we have a subfolder, store it there
                if ($subfolder != null)
                    $zipFile->addFile($folder . $f, $subfolder . $f);
                else
                    $zipFile->addFile($folder . $f);
            } elseif (is_dir($folder . $f)) {
                // if we find a folder, create a folder in the zip
                $zipFile->addEmptyDir($f);
                // and call the function again
                folderToZip($folder . $f, $zipFile, $f);
            }
        }
    }
}

function deleteDir($path) {
    return is_file($path) ? @unlink($path) : array_map(__FUNCTION__, glob($path.'/*')) == @rmdir($path);
}