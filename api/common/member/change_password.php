<?php require_once __DIR__ . '/../../_api.php';
if(!defined("_API_INCLUDE_")) exit;

$isCancel = false;
$isPrintResult = true;
//@header("Content-Type:text/html;charset=euc-kr");
/*
if(isset($_POST))
{
    $Fun->var_dump($_POST);
    $Fun->print_r($post);
    exit;
}
 * 
 */

if(!isset($_POST) || !array_key_exists("uno", $_POST) || !array_key_exists("userId", $_POST) || !array_key_exists("existPwd", $_POST) || !array_key_exists("newPwd", $_POST) || !array_key_exists("newPwdCheck", $_POST))
{
    $Result[_RESULT_TYPE_] = ResultType::Fail;
    $Result[_RESULT_MESSAGE_] = "참조 오류(Access Errors.)";
    DoPrintResultResponse($isPrintResult);
    die();
    exit;
}

$uno = $_POST["uno"];
$user_id = $_POST["userId"];
$old_pwd = $_POST["existPwd"];
$new_pwd = $_POST["newPwd"];
$check_pwd = $_POST["newPwdCheck"];

if($new_pwd != $check_pwd)
{
    $Result[_RESULT_TYPE_] = ResultType::Fail;
    $Result[_RESULT_MESSAGE_] = "신규와 확인 패스워드가 맞지 않음(Password Mismatch.)";
    DoPrintResultResponse($isPrintResult);
    die();
    exit;
}
else if($old_pwd == $new_pwd)
{
    $Result[_RESULT_TYPE_] = ResultType::Fail;
    $Result[_RESULT_MESSAGE_] = "이전 패스워드와 같음(Same as old password.)";
    DoPrintResultResponse($isPrintResult);
    die();
    exit;
}

$Result[_RESULT_TYPE_] = ResultType::None;
$Result[_RESULT_MESSAGE_] = "undefined";

/*
$serverName = _DB_Host_Mssql_; //serverName\instanceName
$connectionInfo = array( "Database"=>"HITECH", "UID"=>_DB_User_Mssql_, "PWD"=>_DB_Pass_Mssql_);
$conn = sqlsrv_connect( $serverName, $connectionInfo);
if( $conn ) {
     echo "Connection established.<br />";
}else{
     echo "Connection could not be established.<br />";
     die( print_r( sqlsrv_errors(), true));
}
$SQL = "SELECT * FROM dbo.BIZ_USER_SET";
$params = array();
$options = array();
$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
$parse = sqlsrv_query($conn, $SQL, $params, $options);
echo sqlsrv_num_rows($parse);
exit;
$Record = array();
if ($row = sqlsrv_fetch_array($parse, SQLSRV_FETCH_ASSOC)) 
{
    //$Fun->var_dump($row);
    //$Record = $row;
    foreach( sqlsrv_field_metadata( $parse ) as $fieldMetadata ) {
        $Record[strtolower($fieldMetadata["Name"])] = $row[$fieldMetadata["Name"]];
        //$Fun->print_r($fieldMetadata);
    }
    $Fun->print_r($Record);
}
exit;
 * 
 */
/*
require_once _LIB_PATH_ . "db_mssql.php";

class UserDB extends DB_MSSql {
    var $Host     = _DB_Host_Mssql_;
    var $Database = _DB_Name_Mssql_;
    var $User     = _DB_User_Mssql_;
    var $Password = _DB_Pass_Mssql_;
    var $Debug    = 0;
    
    var $Options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
    
    function query($Query_String, $params = null, $options = null) {

        if ($Query_String == "") {

            return 0;
        }

        if (!$this->Link_ID) {
            $this->connect();
        }

        #   printf("<br>Debug: query = %s<br>\n", $Query_String);
        if($this->Debug) {
            printf("Debug: query = %s<br>\n", $Query_String);
        }
        // $this->Query_ID = @mssql_query($Query_String, $this->Link_ID);
        // $this->Row = 0;
        // if (!$this->Query_ID) {
            // $this->Errno = 1;
            // $this->Error = "General Error (The MSSQL interface cannot return detailed error messages).";
            // $this->halt("Invalid SQL: ".$Query_String);
        // }
        
        
        if(!isset($options) || is_null($options))
        {
            $options = $this->Options;
        }
       
        $this->Query_ID = sqlsrv_query($this->Link_ID, $Query_String, $params, $options);
        if( $this->Query_ID === false ) {
                die( print_r(sqlsrv_errors(), true) );
                $this->Errno = 1;
                $this->Error = "General Error (The MSSQL interface cannot return detailed error messages).";
                $this->halt("Invalid SQL: ".$Query_String);
        }
        return $this->Query_ID;
    }
}
$userDB = new UserDB;
*/
$SQL = "SELECT * FROM HITECH.dbo.BIZ_USER_SET WHERE UNO = ? AND USER_ID = ? AND USER_PWD = ?";
$params = array($_POST["uno"], $_POST["userId"], $_POST["existPwd"]);
$SQL = "SELECT U.user_id uno, U.logon_cd user_id, U.logon_pwd user_pwd  FROM Neo_BizBox.dbo.TCMG_USER U WHERE U.user_id = ? AND U.logon_cd = ? AND U.logon_pwd = ?";
$params = array($_POST["uno"], $_POST["userId"], $_POST["existPwd"]);
$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

$serverName = _DB_Host_Mssql_Duzon_; //serverName\instanceName
$connectionInfo = array( "Database"=>_DB_Name_Mssql_Duzon_, "UID"=>_DB_User_Mssql_Duzon_, "PWD"=>_DB_Pass_Mssql_Duzon_);
$conn = sqlsrv_connect( $serverName, $connectionInfo);
if( $conn ) {
     //echo "Connection established.";
}else{
    //echo "Connection could not be established.";
    //die( print_r( sqlsrv_errors(), true));
    $Result[_RESULT_TYPE_] = ResultType::Fail;
    $Result[_RESULT_MESSAGE_] = "SQL 접속 오류(Connection could not be established.)";
    DoPrintResultResponse($isPrintResult);
    die();
    exit;
}

$parse = sqlsrv_query($conn, $SQL, $params, $options);
$n = sqlsrv_num_rows($parse);
if(!$n || $n <= 0)
{
    $Result[_RESULT_TYPE_] = ResultType::Fail;
    $Result[_RESULT_MESSAGE_] = "정보를 찾을 수 없음(Not Found!!)";
    
    DoPrintResultResponse($isPrintResult);
    die();
    exit;
}
else
{
    $record = array();
    if ($row = sqlsrv_fetch_array($parse, SQLSRV_FETCH_ASSOC)) 
    {
        //$record = $row;
        foreach( sqlsrv_field_metadata( $parse ) as $fieldMetadata ) {
            $record[strtolower($fieldMetadata["Name"])] = $row[$fieldMetadata["Name"]];
            //$Fun->print_r($fieldMetadata);
        }
    }
    $db_pwd = $record["user_pwd"];
    
    if($old_pwd != $db_pwd)
    {
        $Result[_RESULT_TYPE_] = ResultType::Fail;
        $Result[_RESULT_MESSAGE_] = "자료를 찾을 수 없음(Not Matching!!)";
    }
    else if($new_pwd == $db_pwd)
    {
        $Result[_RESULT_TYPE_] = ResultType::Fail;
        $Result[_RESULT_MESSAGE_] = "이전 정보와 불일치(Not Matching!!)";
    }
    else 
    {
        $params = array();
        $SQL = "SELECT option_id, option_set_code, option_value FROM FCMT_GetModuleOpton(1323) WHERE option_group = 'cm' AND option_id IN (65, 66)";
        $parse = sqlsrv_query($conn, $SQL, $params, $options);
        $n = sqlsrv_num_rows($parse);
        
        $params = array();
        $params['USER_ID'] = $uno;          //사용자 관리번호
        $params['PW'] = $old_pwd;           //기존 패스워드
        $params['NPW'] = $check_pwd;        //신규 패스워드 검증용
        $params['ENC_PW'] = "";             //암호화된 패스워드(사용 X)
        $params['NEW_PW'] = $new_pwd;       //신규 패스워드 (실제 적용)
        $params['PW_DIV'] = "1";            //변경할 패스워드 타입 (1: 로그인 2:결재 3:급여)
        $params['PW_CHK2'] = 0;             //체크 옵션2
        $params['PW_CHK3'] = 0;             //체크 옵션3
        
        if($n > 0)
        {
            while($row = sqlsrv_fetch_array($parse, SQLSRV_FETCH_ASSOC))
            {
                $record = array();
                foreach( sqlsrv_field_metadata( $parse ) as $fieldMetadata ) {
                    $record[strtolower($fieldMetadata["Name"])] = $row[$fieldMetadata["Name"]];
                    //$Fun->print_r($fieldMetadata);
                }
                //$Fun->print_r($record);
                if($record["option_set_code"] == "eOption_CM_PW_Chk2")
                {
                    $params['PW_CHK2'] = $record["option_value"];
                }
                else if($record["option_set_code"] == "eOption_CM_PW_Chk2")
                {
                    $params['PW_CHK3'] = $record["option_value"];
                }
            }
            //$Fun->print_r($params);
            //die();
            
        }
        /*
         * 
        $procedure_params = array(
            array(&$params['USER_ID'], SQLSRV_PARAM_IN), 
            array(&$params['PW'], SQLSRV_PARAM_IN), 
            array(&$params['NPW'], SQLSRV_PARAM_IN), 
            array(&$params['ENC_PW'], SQLSRV_PARAM_IN), 
            array(&$params['NEW_PW'], SQLSRV_PARAM_IN), 
            array(&$params['PW_DIV'], SQLSRV_PARAM_IN), 
            array(&$params['PW_CHK2'], SQLSRV_PARAM_IN), 
            array(&$params['PW_CHK3'], SQLSRV_PARAM_IN),
            );
         
        //$SQL = "EXEC [dbo].[PMP_USER_PW_CHANGE] @USER_ID = ?, @PW = ?, @NPW = ?, @ENC_PW = ?, @NEW_PW = ?, @PW_DIV = ?, @PW_CHK2 = ?, @PW_CHK3 = ?";
        //$stmt = sqlsrv_prepare($conn, $SQL, $procedure_params);
         *
         */
        $procedure_params = array(
                    &$params['USER_ID'], &$params['PW'], &$params['NPW'], &$params['ENC_PW'], &$params['NEW_PW'], &$params['PW_DIV'], &$params['PW_CHK2'], &$params['PW_CHK3']
                );
        $SQL = "[dbo].[PMP_USER_PW_CHANGE] ?, ?, ?, ?, ?, ?, ?, ?";
        $stmt = sqlsrv_query($conn, $SQL, $procedure_params);
        if(!$stmt) 
        {
            //$Result[_RESULT_TYPE_] = ResultType::Fail;
            //$Result[_RESULT_MESSAGE_] = "변경 작업 중 오류발생(Error during change operation)";
            
            $Result[_RESULT_TYPE_] = ResultType::Fail;
            $Result[_RESULT_MESSAGE_] = "SQL 실행 오류(SQL Execution Error)";
            
            DoPrintResultResponse($isPrintResult);
            die();
            exit;
        }
        else 
        {
            $res = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            if(!$res || !array_key_exists("return_value", $res))
            {
                $Result[_RESULT_TYPE_] = ResultType::Fail;
                $Result[_RESULT_MESSAGE_] = "SQL 결과 없음(SQL Result is Null)";
            }
            else
            {
                if(!$res["return_value"] || $res["return_value"] < 0)
                {
                    switch ($res["return_value"]) 
                    {
                        case -9:
                            $msg = "비밀번호에 주민등록번호가 포함되어 있습니다.";
                            break;
                        case -8:
                            $msg = "비밀번호에 생년월일이 포함되어 있습니다.";
                            break;
                        case -7:
                            $msg = "비밀번호에 ERP사번이 포함되어 있습니다.";
                            break;
                        case -6:
                            $msg = "비밀번호에 회사전화번호가 포함되어 있습니다.";
                            break;
                        case -5:
                            $msg = "비밀번호에 이메일아이디가 포함되어 있습니다.";
                            break;
                        case -4:
                            $msg = "비밀번호에 아이디가 포함되어 있습니다.";
                            break;
                        case -3:
                            $msg = "비밀번호에 휴대폰번호가 포함되어 있습니다.";
                            break;
                        case -2:
                            $msg = "비밀번호에 전화번호가 포함되어 있습니다.";
                            break;
                        case -1:
                            $msg = "기존 비밀번호가 일치하지 않습니다.";
                            break;
                    }
                    $Result[_RESULT_TYPE_] = ResultType::Fail;
                    $Result[_RESULT_MESSAGE_] = $msg;
                } 
                else 
                {
                    /*
                    $SQL = "UPDATE BIZ_USER_SET SET USER_PWD = :newPwd WHERE UNO = :UNO";
                    $params = array(
                        ":newPwd" => $newPwd,
                        ":UNO" => $user->uno
                    );
                    $db->query($SQL, $params);
                    */
                    
                    $commonDB = new CommonDB();
                    try
                    {
                        $commonDB->ShowError = true;
                        
                        $SQL = "UPDATE BIZ_USER_SET SET USER_PWD = '{$new_pwd}' WHERE UNO = {$uno}";
                        //$Fun->print_r($commonDB);
                        $stmt = $commonDB->query($SQL);
                        if(!$stmt)
                        {
                            $Result[_RESULT_TYPE_] = ResultType::Warning;
                            $Result[_RESULT_MESSAGE_] = "주 테이블 변경 성공했으나, 보조 테이블 변경 작업 중 오류 발생(Primary table succeeded, but Secondary table error.)";
                        }
                        else 
                        {
                            $Result[_RESULT_TYPE_] = ResultType::Success;
                            $Result[_RESULT_MESSAGE_] = "변경 작업 성공(Change Password success)";
                        }
                    } catch (Exception $ex) {
                        $Result[_RESULT_TYPE_] = ResultType::Warning;
                        $Result[_RESULT_MESSAGE_] = "주 테이블 변경 성공했으나, 보조 테이블 변경 작업 중 오류 발생(Primary table succeeded, but Secondary table error.)" . $ex->getMessage();
                    }
                }
            }
        }
        //$Result[_RESULT_TYPE_] = ResultType::Warning;
        //$Result[_RESULT_MESSAGE_] = "작업 해야 함.";
    }
}

//$db->query("SELECT * FROM TAB WHERE ROWNUM = 1");
//$Fun->print_r($db);

    //SetResultValue($data);
    /*
    $value = array(
        "0" => array("id" => "abcd", "name" => "가나다라"),
        "1" => array(0 => "ABCD", "name" => "家羅多羅"),
    );
    */
DoPrintResultResponse(true);