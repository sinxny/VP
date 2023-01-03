<?php define("_API_INCLUDE_", "Include OK");
@ini_set('memory_limit','-1');
@ini_set('default_socket_timeout', -1);
if(!defined("_LIB_INCLUDE_"))
{
    require_once __DIR__ . '/_inc.php';
}

define("_WSDL_TRANSFERWEB_URL_", "http://file.htenc.co.kr/transferweb/Service1.svc?singleWsdl");

//$Fun->print_r($db);
define("_RESULT_TYPE_", "ResultType");
define("_RESULT_VALUE_", "Value");
define("_RESULT_VALUE_TYPE_", "ValueType");
define("_RESULT_VALUE_COUNT_", "ValueCount");
define("_RESULT_MESSAGE_", "Message");
define("_RESULT_NAVI_", "Navigator"); //Pagination
define("_RESULT_NAVI_ROW_TOTAL_", "TotalRow");
define("_RESULT_NAVI_ROW_START_", "StartRow");
define("_RESULT_NAVI_ROW_END_", "EndRow");
define("_RESULT_NAVI_ROW_OFFSET_", "OffsetRow");
define("_RESULT_NAVI_PAGE_TOTAL_", "TotalPage");
define("_RESULT_NAVI_PAGE_CURR_", "CurrentPage");
define("_RESULT_UNO_", "uno");
/**
 * BEGIN - DB Tables
 */
class DB_TABLES
{
    const USER_INFO = "S_SYS_USER_INFO";
    const JOB_INFO = "S_PMS_JOB_INFO";
    const FUNC_CODE = "COMMON.V_COMM_FUNC_CODE";
}
class JOB_TABLES extends DB_TABLES
{
    const JOB_CODE_SET = "S_PMS_CODE_SET";
    const JOB_LOC_CODE = "S_PMS_LOC_CODE";
    const JOB_COMPANY_INFO = "S_PMS_COMPANY_INFO";
    const JOB_MEMBER_LIST = "S_JOB_MEMBER_LIST";
}
class VDCS_TABLES extends JOB_TABLES
{
    const VDCS_VPMS_ENV = "VDCS_VPMS_ENV";
    const VDCS_VPTR_SET = "VDCS_VPTR_SET";
    const VDCS_VPDC_SET = "VDCS_VPDC_SET";
    const VDCS_VPMS_TAG = "VDCS_VPMS_TAG";
    const SYS_FUNC_TYPE = "SYS_FUNC_TYPE";
    const SYS_DOC_RESULT_TYPE = "SYS_DOC_RESULT_TYPE";
    const DOCS_PUBL_FILE = "DOCS_PUBL_FILE";
}
/**
 * END - DB Tables
 */

enum JOB_FILTER_TYPE : string
{
    case None = "NONE";
    case All = "ALL";
    case My = "MY";
    case Staff = "STAFF";
    case VdcsUse = "VDCS_USE";
}

enum RequestJobModelType : string
{
    case None = "NONE";
    case JobInfo = "JOB_INFO";
    case SatffInfo = "STAFF_INFO";
}

enum ResultType : string
{
    case Fail = "Fail";
    case Success = "Success";
    case Notice = "Notice";
    case Warning = "Warning";
    case None = "None";
}

enum RequestVdcsModelType : string
{
    case None = "None";
    case Latest = "LATEST";
    case DocHistory = "DOC_HISTORY";
    case DocDistributeDownload = "DOC_DE_DOWNLOAD";
    case DocReplyDownload = "DOC_RE_DOWNLOAD";
    case DocLatestDownload = "DOC_LE_DOWNLOAD";
    case LatestSingleDownload = "LATEST_SINGLE_DOWNLOAD";
    case LatestMultiDownload = "LATEST_MULTI_DOWNLOAD";
    case LatestZipDownload = "LATEST_ZIP_DOWNLOAD";
    case LatestZipInfo = "LATEST_ZIP_INFO";
}

$Value = null;
$Result = array(
    _RESULT_TYPE_ => ResultType::Fail, 
    _RESULT_VALUE_ => &$Value, 
    _RESULT_VALUE_TYPE_ => null,
    _RESULT_VALUE_COUNT_ => -1,
    _RESULT_MESSAGE_ => "Not Found Data" );
$_api = null;
//$Fun->print_r($Result);
$isCancel = false;
$isAccept = false;
$isPrintResult = false;
$LoginUNo = null;
if(isset($user) && isset($user->uno))
{
    $LoginUNo = $user->uno;
}
$request_model_type = null;
if(isset($post) && is_array($post) && array_key_exists("mode", $post))
{
    $request_model_type = strtoupper($post["mode"]);
}
//$request_model_type = null;
if(isset($post) && is_array($post) && array_key_exists("model", $post))
{
    $request_model_type = strtoupper($post["model"]);
}

/*
class TABLES : string
{
    case JOB_INFO = "S_JOB_INFO";
}
 */
/*
function DoPrintResponse2($is_print=true, $input_code = null, $input_message = null, $input_data = null, $input_data_type = null, $is_unicode = true)
{
    echo $is_print;
    echo $input_code;
    echo $input_message;
    echo $input_data;
    echo $input_data_type;
    echo $is_unicode;
    echo $is_print;
    echo $input_code;
    echo $input_message;
    echo $input_data;
    echo $input_data_type;
    echo $is_unicode;
    echo $is_print;
    echo $input_code;
    echo $input_message;
    echo $input_data;
    echo $input_data_type;
    echo $is_unicode;
    echo $is_print;
    echo $input_code;
    echo $input_message;
    echo $input_data;
    echo $input_data_type;
    echo $is_unicode;
}
 */


function DoPrintResultResponse($is_print=true, $input_code = null, $input_message = null, $input_data = null, $input_data_type = null, $is_unicode = true)
{
    global $isCancel, $Result;
    if(isset($input_code))
    {
        $Result[_RESULT_TYPE_] = $input_code;
    }
    if(isset($input_message))
    {
        $Result[_RESULT_MESSAGE_] = $input_message;
    }
    SetResultValue($input_data, $input_data_type);
    if($isCancel == true || $is_print == true)
    {
        @header("Content-type: application/json;"); 
        if(!$is_unicode)
        {
            echo json_encode($Result, JSON_UNESCAPED_UNICODE);
        }
        else
        {
            echo json_encode($Result);
        }
        //exit;
        if($isCancel == true)
        {
            exit;
        }
    }
}

function SetResultValue($input_data, $input_count = null, $input_data_type = null)
{
    global $Result;
    if(!isset($Result) || !$Result){
        $Result = array();
    }
    
    if(isset($input_data))
    {
        $Result[_RESULT_TYPE_] = ResultType::Success->value;
        $Result[_RESULT_VALUE_] = $input_data;
        $Result[_RESULT_VALUE_COUNT_] = $input_count ?? count($input_data);
        $Result[_RESULT_MESSAGE_] = ResultType::Success;
        if(!isset($input_data_type))
        {
            $Result[_RESULT_VALUE_TYPE_] = gettype($Result[_RESULT_VALUE_]);
        }
    }
}

function SetResultType($input_result_type)
{
    $Result[_RESULT_TYPE_] = $input_result_type;
}

if(isset($post) && is_array($post))
{
    if( !array_key_exists("api_key", $post) || !array_key_exists("api_pass", $post) )
    {
        $isCancel = true;
        //$Result["Result"] = "Error";
        //$Result["Message"] = "Input Error.";
        DoPrintResultResponse(true, "Error", "Input Error.");
    }
    try
    {
        $SQL = "SELECT api_no, api_module, api_name, api_desc, api_auth, parent_no, regex_pattern, regex_flags, regex_option, case_page, case_major, case_minor, case_option, is_create, is_read, is_update, is_delete, is_admin, is_spatial, is_option_major, is_option_minor, val01, val02, val03, val04, val05, val06, val07, val08, val09, val10 FROM COMMON.COMM_OAPI_KEY WHERE 1 = 1";
        $SQL .= sprintf("\n AND API_KEY = '%s' AND API_PASS = '%s'", $post["api_key"], $post["api_pass"]);
        $db->query($SQL);
        if(!$db->nf())
        {
            $isCancel = true;
            //$Result["Result"] = "Fail";
            //$Result["Message"] = "Not Found Data!";
            DoPrintResultResponse($isCancel, "Fail", "Not Found Data!");
        }

        $db->next_record();
        $_api = $db->Record;
        
        if(isset($_api) && is_array($_api))
        {
            $pattern = $db->f("regex_pattern");
            $Result['Module'] = $_api["api_module"];
            if(!$pattern || $pattern == "*")
            {
                $isAccept = true;
            } 
            else 
            {
                $isAccept = false;
                //echo $remote_addr;
                //exit;
                if(preg_match($pattern, $remote_addr))
                {
                    $isAccept = true;
                }
            }
            
        }
        //$response = array("aaa" => "1111");
        //DoPrintResultJson(true, "Success", "Accept.");
        if($isAccept == false && isset($LoginUNo) && $LoginUNo)
        {
            $isAccept = true;
        }
        if($isAccept == false)
        {
            $isCancel = true;
            DoPrintResultResponse(true, "Warning", "Not Accept.");
        }
        else 
        {
            $Result[_RESULT_TYPE_] = "Notice";
            $Result[_RESULT_MESSAGE_] = "Accept.";
        }
    }
    catch(Exception $ex)
    {
        $isCancel = true;
        echo $Fun->print_r($ex);
    }
}


if($isCancel == true || $isAccept != true || $isPrintResult == true)
{
    DoPrintResultResponse(true);
    exit;
}