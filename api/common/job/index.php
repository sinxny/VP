<?php require_once __DIR__ . '/../../_api.php';
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

$jno = 16185;
if(isset($post) && is_array($post) && array_key_exists("jno", $post))
{
    $jno = $post["jno"];
}
if(!$jno) exit;



$requestJobModelType = RequestJobModelType::JobInfo;
if(isset($request_model_type))//if(isset($post) && is_array($post) && array_key_exists("mode", $post))
{
    //$request_model_type = strtoupper($post["mode"]);
    $requestJobModelType = RequestJobModelType::tryFrom($request_model_type)??RequestJobModelType::JobInfo;
}


try
{
    switch($requestJobModelType)
    {
        case RequestJobModelType::SatffInfo :
            require_once __DIR__ . '/staff_info.php';
            break;
        case RequestJobModelType::JobInfo :
        default :
            require_once __DIR__ . '/job_info.php';
            break;
    }
    
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