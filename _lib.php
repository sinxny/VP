<?php define("_LIB_INCLUDE_", "Include OK");
//Report all errors except E_NOTICE
error_reporting(E_ALL ^ E_NOTICE);
//error_reporting( E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_WARNING);
ini_set( "display_errors", 1 );

$isLocal = false;
define("_CRYPT_KEY_", "hi1004@");


define("_HTTPS_", $_SERVER['HTTPS'] === "on" ? true : false);
define("_ROOT_URL_", (_HTTPS_ == true ? "https://" : "http://") . $_SERVER["HTTP_HOST"] . "/");
$_SERVER["DOCUMENT_ROOT"] = str_replace("\\","/", $_SERVER["DOCUMENT_ROOT"]); // str_replace("\\", "/", "") : Microsoft Windows에서 경로를 \로 표시되는것을 /로 변경....
define("_ROOT_PATH_", $_SERVER["DOCUMENT_ROOT"]);
define("_LIB_PATH_" , str_replace("\\","/", dirname(__FILE__)) . "/../lib/");
$_lib_url = str_replace("\\", "/", str_replace($_SERVER["DOCUMENT_ROOT"], "", _LIB_PATH_));
if($_lib_url == _LIB_PATH_)
{
    $_lib_url =  _ROOT_URL_ . "lib/";
}
define("_LIB_URL_"  , $_lib_url);
define("_API_PATH_" , str_replace("\\","/", dirname(__FILE__)) . "/api/");
$_api_url = str_replace("\\", "/", str_replace($_SERVER["DOCUMENT_ROOT"], "", _API_PATH_));
if($_api_url == _API_PATH_)
{
    //$_api_url =  "/api/";
    $_api_url =  _ROOT_URL_ . "api/";
}
define("_API_URL_"  , $_api_url);
// $isLocal = FALSE;
//Timesheet DB
$isSessionFlag = $isSessionFlag??false;
if (!$isSessionFlag) 
{
    @session_start();
}

define("_DB_Class_", "oci8");
if (!$isLocal) {
    $server         = "ora01.htenc.co.kr";
    $port           = 1521;
    $service_name   = "ORCL";
    //$sid            = "ORCL";
    //$dbtns          = "(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = $server)(PORT = $port)) (CONNECT_DATA = (SERVICE_NAME = $service_name) (SID = $sid)))";
    $dbtns          = "(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = $server)(PORT = $port)) (CONNECT_DATA = (SERVICE_NAME = $service_name) ))";
    
    define("_DB_Host_Oracle_"    , false);
    define("_DB_User_Oracle_"    , "hibiz");//61.33.147.38
    define("_DB_Pass_Oracle_"    , "VGsFPFJnVD1ReAI2WDdUZ1A2Vjc=");
    define("_DB_Name_Oracle_"    , $dbtns);
    
    define("_DB_Host_OCI_Common_"    , false);
    define("_DB_User_OCI_Common_"     , "common");//61.33.147.38
    define("_DB_Pass_OCI_Common_"    , "BzNWaQE7B2oCPlE6C2UHNANhBTEDYA==");
    define("_DB_Name_OCI_Common_"    , $dbtns);
}
else {
    $server         = "testdb.htenc.co.kr";
    $port           = 1521;
    $service_name   = "ORCL";
    //$sid            = "ORCL";
    //$dbtns          = "(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = $server)(PORT = $port)) (CONNECT_DATA = (SERVICE_NAME = $service_name) (SID = $sid)))";
    $dbtns          = "(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = $server)(PORT = $port)) (CONNECT_DATA = (SERVICE_NAME = $service_name) ))";
    
    define("_DB_Host_Oracle_"    , false);
    define("_DB_User_Oracle_"    , "hibiz");
    define("_DB_Pass_Oracle_"    , "VGsFPFJnVD1ReAI2WDdUZ1A2Vjc=");
    define("_DB_Name_Oracle_"    , $dbtns);
    
    define("_DB_Host_OCI_Common_"    , false);
    define("_DB_User_OCI_Common_"     , "common");//61.33.147.38
    define("_DB_Pass_OCI_Common_"    , "BzNWaQE7B2oCPlE6C2UHNANhBTEDYA==");
    define("_DB_Name_OCI_Common_"    , $dbtns);
}

define("_DB_Host_Mssql_Duzon_"    , "biz.htenc.co.kr");
define("_DB_Name_Mssql_Duzon_"    , "Neo_BizBox");
define("_DB_User_Mssql_Duzon_"    , "bizbox");
define("_DB_Pass_Mssql_Duzon_"    , "bizbox");

//require_once _LIB_PATH_ . "fun.class.php";
require_once __DIR__ . "/fun.class_2022.php";
//require_once _LIB_PATH_ . "db_oci8.php";
//require_once _LIB_PATH_ . "db_pdooci.php";
require_once __DIR__ . "/db_pdooci_2022.php";
//require_once _LIB_PATH_ . "db_local.php";

require_once _LIB_PATH_ . "user.class.php";
require_once _LIB_PATH_ . "page.class.php";

function convertUTF8toEUCKR(&$item) {
    $item = iconv("UTF-8", "EUC-KR//TRANSLIT", $item);
}

function convertEUCKRtoUTF8(&$item) {
    $item = iconv("EUC-KR", "UTF-8//TRANSLIT", $item);
}

class DB extends DB_PDO_OCI
{
    //protected $Host;
    //protected $Database;
    //protected $User;
    //protected $Password = "";
    public $RecordAll= array();
    public $isNumRow = false;
    
    public function __construct() {
        //$this->Host = _DB_Host_Oracle_;
        $this->User = _DB_User_Oracle_;
        //$this->Password = _DB_Pass_Oracle_;
        $this->setPassword(_DB_Pass_Oracle_);
        $this->Database = _DB_Name_Oracle_;
		$this->Charset = DB_PDO_OCI_CHARSET::KO16MSWIN949->value;
    }
    
    public function connection($user, $password, $database, $charset = null) {
        //$this->Host = $host;
        $this->User = $user;
        //$this->Password = $password;
        $this->setPassword($password);
        $this->Database = $database;
		if(isset($charset) && !is_null($charset) && !$charset)
		{
			$this->Charset = $charset;
		}
        parent::connect();
    }


    public function query($Query_String, $params = array()) {
        $stat = true;
        unset($this->RecordAll);
        $this->isNumRow = false;

        $Query_String = trim($Query_String);
        if ($Query_String == "") {
            return;
        }

        $this->connect();

        try {
            $this->Stmt=$this->Conn->prepare(iconv("UTF-8", "EUC-KR//TRANSLIT", $Query_String));
            if(!$this->Stmt) {
                $this->Error=$this->Conn->errorInfo();
            } 
            else {
                if( is_array($params) ) {
                    //array_walk($params, array($this, 'convertUTF8toEUCKR'));
                    array_walk($params, 'convertUTF8toEUCKR');
                }
                foreach (array_keys($params) as $key) {
                    // oci_bind_by_name($stid, $key, $val) does not work
                    // because it binds each placeholder to the same location: $val
                    // instead use the actual location of the data: $ba[$key]
                    $this->Stmt->bindValue($key, $params[$key]);
                }
                $this->Stmt->execute();
            }
        }
        catch(PDOException $e){
            $this->Error=$this->Conn->errorInfo();
            //ORA-01403 : No data found
			$this->halt($Query_String);
            if ($this->Error[1]!=1403 && $this->Error[1]!=0 && $this->sqoe) {
                //echo "<BR><FONT color=red><B>".$this->Error[2]."<BR>Query :\"$Query_String\"</B></FONT>";
            }
			
            $stat = false;
        }

        $this->Row=0;

        if($this->Debug) {
            printf("Debug: query = %s<br>\n", iconv("UTF-8", "EUC-KR", $Query_String));
        }

        if (strtoupper(substr($Query_String, 0, 1)) == "S" || strtoupper(substr($Query_String, 0, 1)) == "W"){
            $this->isNumRow = true;
            $this->RecordAll = $this->Stmt->fetchAll();
            $this->RowCount = count($this->RecordAll);
        }
        else {
            $this->isNumRow = false;
        }
        return $stat;
    }

    public function next_record() {
        unset($this->Record);
        if ($this->isNumRow){
            if ($this->RowCount > $this->Row){
                $row = $this->RecordAll[$this->Row];
                foreach($row as $key=>$val) 
                {
                    if(isset($val) && !is_null($val) && is_string($val))
                    {
                        $this->Record[$key] = iconv("EUC-KR", "UTF-8//TRANSLIT", $val);
                    }
                    if($this->Debug) {
                        echo"<b>[{$key}]</b>:".$this->Record[$key]."<br>\n";
                    }
                }
                $stat = true;
            } 
            else {
                if ($this->Debug) {
                    printf("<br>ID: %d,Rows: %d<br>\n", $this->Conn,$this->num_rows());
                }
                $errInfo=$this->Conn->errorInfo();
                if(1403 == $errInfo[1]) { # 1043 means no more records found
                    $this->Error="";
                } 
                else {
                    if($this->Debug) {
                        printf("<br>Error: %s", $errInfo[2]);
                    }
                    $this->Error=$errInfo[2];
                }
                $stat=false;
            }
            $this->Row += 1;
        } 

        return $stat;
    }
    
    public function record_all() : array
    {
        $result = array();
        $idx = 0;
        foreach($this->RecordAll as $row)
        {
            $result[$idx] = array();
            foreach($row as $key => $val) 
            {         
                if(isset($val) && !is_null($val) && is_string($val))
                {
                    $result[$idx][$key] = iconv("EUC-KR", "UTF-8//TRANSLIT", $val);
                }
            }
            $idx++;
        }
        return $result;
    }
    
    public function getRecordAll() : array
    {
        return $this->record_all();
    }
    
    public function query_limit($SQL, $limitStartRow = 0, $limitCount = 0)
    {
        //echo $Query_String . " LIMIT " . $Offset . ", " . $Count;
        if($limitStartRow >= 0 && $limitCount >= 0){
            switch(_DB_Class_){
                case "mysql":
                    $limitStartRow >= 0 ? $limitStartRow = $limitStartRow . ", ": "";
                    $strQueryString = $SQL . " LIMIT " . $limitStartRow . $limitCount;
                    //echo $SQL;
                    break;
                case "oci8":
                case "oracle":
                    $nStartRow = $limitStartRow;
                    $nEndRow = $nStartRow + $limitCount;
/*
                    $strQueryString  = "SELECT * FROM ";
                    $strQueryString .= "       (SELECT T.*,rownum AS rnum FROM ";
                    $strQueryString .= "           (" . $SQL . ") T ";
                    $strQueryString .= "       ) ";
                    $strQueryString .= "WHERE rnum > " . $nStartRow . " AND rownum <= " . $nEndRow;*/
                    /* 위아래 쿼리중 뭐가 좋을까 생각 고민고민) */
                    $strQueryString =  "SELECT * FROM ";
                    $strQueryString .= "  (SELECT rownum AS rnum, T.* FROM ";
                    $strQueryString .= "      (" . $SQL . ") T";
                    $strQueryString .= "  WHERE rownum <= " . $nEndRow . ") ";
                    $strQueryString .= "WHERE rnum > " . $nStartRow;
                    //echo $strQueryString;
                    break;
                default:
                    $strQueryString = $SQL;
            }//end switch(_DB_Class_)
        } else {
            $strQueryString = $SQL;
        }//end if($Offset >= 0 AND $Count > 0)
        //echo $SQL;
        return $this->query($strQueryString);
    }//end function query_limit();
}

class CommonDB extends DB_PDO_OCI
{
    public function __construct() {
        //$this->Host = _DB_Host_Oracle_;
        $this->User = _DB_User_OCI_Common_;
        //$this->Password = _DB_Pass_Oracle_;
        $this->setPassword(_DB_Pass_OCI_Common_);
        $this->Database = _DB_Name_OCI_Common_;
	$this->Charset = DB_PDO_OCI_CHARSET::KO16MSWIN949->value;
    }
    public function connection($user, $password, $database, $charset = null) {
        //$this->Host = $host;
        $this->User = $user;
        //$this->Password = $password;
        $this->setPassword($password);
        $this->Database = $database;
		if(isset($charset) && !is_null($charset) && !$charset)
		{
			$this->Charset = $charset;
		}
        parent::connect();
    }
}