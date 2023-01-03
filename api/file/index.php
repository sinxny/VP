<?php require_once __DIR__ . '/../_api.php';
if(!defined("_API_INCLUDE_")) exit;

if(!isset($Result) && !is_array($Result) 
        || !array_key_exists("Module", $Result) || is_null($Result["Module"])
        || !isset($_api) || !is_array($_api) 
        || $isCancel == true 
        || $isAccept != true) 
{
    exit;
}

$module = $Result['Module'];
try
{
    require_once __DIR__ . '/wcf_service.php';
    //$Fun->print_r($_api);
    /*
    switch($module)
    {
        case "VDCS":
        case "VDCS_Test":
            break;
        default :
            $Result['Result'] = "Fail";
            $Result['Message'] = "Not Supported!";
            break;
    }
     * 
     */
} 
catch (Exception $ex) 
{
    $isCancel = true;
    echo $Fun->print_r($ex);
}
finally 
{
    if($isCancel != true)
    {
        DoPrintResultResponse(true);
    }
}
