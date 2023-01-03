<?php if(!defined("_API_INCLUDE_")) exit;

define("_MIN_JOB_DATE_", "2015-01-01");
$uno = null;
$job_filter = JOB_FILTER_TYPE::None;
$jno_agg_list = null;

$JobSQL = "SELECT 
        J.JNO, J.JOB_NO, J.JOB_NAME, J.JOB_SD, TO_CHAR(J.JOB_SD, 'YYYY-MM-DD') JOB_SD_STR, J.JOB_ED, TO_CHAR(J.JOB_ED, 'YYYY-MM-DD') JOB_ED_STR
        , J.JOB_LOC, L.LOC_NAME JOB_LOC_NAME
        , J.JOB_STATE, S.CD_NM JOB_STATE_NAME
        , J.JOB_CODE, C.CD_NM JOB_CODE_NAME
        , J.JOB_TYPE, T.CD_NM JOB_TYPE_NAME
        , J.JOB_PM, PM.USER_NAME JOB_PM_NAME, PM.USER_ID JOB_PM_ID, PM.DUTY_NAME JOB_PM_DUTY_NAME, PM.DEPT_NAME JOB_PM_DEPT_NAME
        , J.COMP_CODE, CO.COMP_NICK, CO.COMP_NAME, CO.COMP_ETC
        , J.ORDER_COMP_CODE, OC.COMP_NICK ORDER_COMP_NICK, OC.COMP_NAME ORDER_COMP_NAME, OC.COMP_ETC ORDER_COMP_ETC
        , J.JOB_ETC, J.JOB_PE, J.JOB_SITE, J.FLOW_JNO
        , J.REG_DATE, J.MOD_DATE
    FROM " . JOB_TABLES::JOB_INFO . " J
        , (SELECT LOC_CODE, LOC_NAME FROM " . JOB_TABLES::JOB_LOC_CODE . ") L
        , (SELECT COMP_NO, TO_CHAR(COMP_NO) COMP_CODE, COMP_NAME, COMP_NICK, COMP_ETC FROM " . JOB_TABLES::JOB_COMPANY_INFO . ") CO
        , (SELECT COMP_NO, TO_CHAR(COMP_NO) COMP_CODE, COMP_NAME, COMP_NICK, COMP_ETC FROM " . JOB_TABLES::JOB_COMPANY_INFO . ") OC
        , (SELECT UNO, TO_CHAR(UNO) PM_CODE, USER_NAME, USER_ID, DUTY_NAME, DEPT_NAME FROM " . JOB_TABLES::USER_INFO . ") PM
        , (SELECT MINOR_CD, CD_NM FROM " . JOB_TABLES::JOB_CODE_SET . " WHERE MAJOR_CD = 'JOB_STATE' AND IS_USE = 'Y') S
        , (SELECT MINOR_CD, CD_NM FROM " . JOB_TABLES::JOB_CODE_SET . " WHERE MAJOR_CD = 'JOB_CODE' AND IS_USE = 'Y') C
        , (SELECT MINOR_CD, CD_NM FROM " . JOB_TABLES::JOB_CODE_SET . " WHERE MAJOR_CD = 'JOB_TYPE' AND IS_USE = 'Y') T
    WHERE 1 = 1
        AND J.JOB_LOC = L.LOC_CODE(+)
        AND J.COMP_CODE = CO.COMP_CODE(+)
        AND J.ORDER_COMP_CODE = OC.COMP_CODE(+)
        AND J.JOB_PM = PM.PM_CODE(+)
        AND J.JOB_STATE = S.MINOR_CD(+)
        AND J.JOB_CODE = C.MINOR_CD(+)
        AND J.JOB_TYPE = T.MINOR_CD(+)
        AND J.JOB_SD IS NOT NULL --AND J.JOB_SD >= TO_DATE('2020-01-01', 'YYYY-MM-DD')
    ORDER BY J.JNO DESC
";

$StaffSQL = "SELECT
    --ML.JNO, ML.COMP_TYPE, ML.FUNC_CODE, NVL(FC.FUNC_NAME, ML.FUNC_NAME) FUNC_NAME,  
    ML.MNO
    , ML.JNO
    , ML.COMP_TYPE
    , ML.FUNC_CODE FUNC_NO
    , FC.FUNC_CD FUNC_CD
    , DECODE(ML.COMP_TYPE, 'H', FC.FUNC_NAME, ML.FUNC_NAME) FUNC_NAME
    , DECODE(ML.COMP_TYPE, 'H', FC.FUNC_TITLE, ML.FUNC_NAME) FUNC_TITLE
    , ML.CHARGE, C.CD_NM CHARGE_CD
    , ML.UNO, U.USER_ID, U.USER_NAME, U.DUTY_NAME, U.DEPT_NAME
    , DECODE(ML.COMP_TYPE, 'H', NVL(U.USER_NAME, ML.MEMBER_NAME || '?'), ML.MEMBER_NAME) MEMBER_NAME
    , DECODE(ML.COMP_TYPE, 'H', NVL(U.DUTY_NAME, ML.GRADE_NAME || '?'), ML.GRADE_NAME) GRADE_NAME
    , DECODE(ML.COMP_TYPE, 'H', NVL(U.CELL, ML.CELL), ML.CELL) CELL
    , DECODE(ML.COMP_TYPE, 'H', NVL(U.TEL, ML.TEL), ML.TEL) TEL
    , DECODE(ML.COMP_TYPE, 'H', NVL(U.EMAIL, ML.EMAIL), ML.EMAIL) EMAIL
    , U.IS_STATE, U.IS_GW_USER_USE, U.IS_ATTEND, U.IS_MOBILE_GW, U.IS_MSG, U.IS_USE IS_USER_USE
    , ML.REG_DATE
    , ML.MOD_DATE
    , DECODE(ML.COMP_TYPE, 'H', fc.SORT_NO_PATH || '>' || u.duty_id, ML.sort_no) AS sort_no
    --, FC.FUNC_CD, FC.FUNC_NAME, FC.FUNC_TITLE, FC.PARENT_NO FUNC_PARENT_CD, FC.IS_LEAF IS_FUNC_LEAF, FC.LEVEL_NO FUNC_LEVEL_NO
FROM " . JOB_TABLES::JOB_MEMBER_LIST . " ML
    , ". JOB_TABLES::FUNC_CODE . " FC
    , (SELECT * FROM " . JOB_TABLES::JOB_CODE_SET . " WHERE MAJOR_CD ='MEMBER_CHARGE') C
    , (SELECT UNO, TO_CHAR(UNO) PM_CODE, USER_NAME, USER_ID, DUTY_ID, DUTY_CD, DUTY_NAME, DEPT_ID, DEPT_CD, DEPT_NAME, CELL, TEL, EMAIL, JOIN_DATE, QUIT_DATE, IS_ADMIN, IS_MANAGER, IS_STATE, IS_GW_USER_USE, IS_ATTEND, IS_MOBILE_GW, IS_MSG, IS_USE, MOD_DATE FROM " . JOB_TABLES::USER_INFO . ") U
WHERE 1 = 1
    AND ML.IS_USE = 'Y' AND U.IS_USE = 'Y'
    AND ML.FUNC_CODE = FC.FUNC_NO(+)
    AND ML.CHARGE = C.MINOR_CD(+)
    AND ML.UNO = U.UNO
";

if(isset($post["filter"]))
{
    $job_filter = JOB_FILTER_TYPE::tryFrom(strtoupper($post["filter"])) ?? JOB_FILTER_TYPE::All;
}
if($job_filter == JOB_FILTER_TYPE::My || $job_filter == JOB_FILTER_TYPE::Staff)
{
    if(isset($post["uno"]))
    {
        $uno = $post["uno"];
    }
    if(!$uno && isset($user) && is_resource($user))
    {
        $uno = $user->uno;
    }
    if(!$uno)
    {
        $uno = 9216;
    }
    if($job_filter != JOB_FILTER_TYPE::My)
    {
        $job_filter == JOB_FILTER_TYPE::Staff;
    }
}
else if($job_filter == JOB_FILTER_TYPE::VdcsUse)
{
    /**
    $SQL = 
"WITH A AS (
    SELECT JNO, COUNT(*) CNT FROM VDCS_VPTR_SET WHERE IS_USE = 'Y' GROUP BY JNO
) SELECT LISTAGG(JNO, ',') WITHIN GROUP(ORDER BY JNO) AS AGG_JNO FROM A
";
    $db->query($SQL);
    if($db->nf())
    {
        $db->next_record();
        $jno_agg_list = $db->f("agg_jno");
    }
    //echo $jno_vdcs_list;
     *
     */
}



switch($job_filter)
{
    case JOB_FILTER_TYPE::VdcsUse:
        /*
        $SQL = "WITH J AS (" . $JobSQL . ")
            SELECT * 
            FROM J
            WHERE 1 = 1 ";
        if(isset($jno_agg_list))
        {
            $SQL .= " AND J.JNO IN (" . $jno_agg_list . ")";
        }
        else
        {
            $SQL .= " AND J.JOB_SD >= TO_DATE('2020-01-01', 'YYYY-MM-DD')";
        }*/
        $SQL = "WITH J AS (
            " . $JobSQL . "
            ), TR AS (
                SELECT JNO, COUNT(*) CNT FROM " . VDCS_TABLES::VDCS_VPTR_SET . " WHERE IS_USE = 'Y' GROUP BY JNO
            )
            SELECT J.*, TR.CNT TR_CNT 
            FROM J, TR
            WHERE 1 = 1
                AND J.JNO = TR.JNO
";
        
        //echo $SQL;
        //exit;
        break;
    case JOB_FILTER_TYPE::Staff:
        $SQL = "WITH J AS (
                " . $JobSQL . "
            ), STAFF AS (
                " . $StaffSQL . "
            )
            SELECT J.*
            FROM J, STAFF
            WHERE 1 = 1
                AND J.JNO = STAFF.JNO
                AND STAFF.UNO = " . $uno
;
        break;
    case JOB_FILTER_TYPE::All :
    case JOB_FILTER_TYPE::None :
    default :
        $SQL = "WITH J AS (" . $JobSQL . ")
            SELECT * 
            FROM J
            WHERE 1 = 1
                AND J.JOB_SD >= TO_DATE('" . _MIN_JOB_DATE_ . "', 'YYYY-MM-DD')
";
        break;
}
function convertEUCKRtoUTF8All(&$array, $slang = "EUC-KR") 
{
    if( is_array($array) ) {
        // class 일경우 array_walk ($array, array($this, 'trimAll'));
        array_walk ($array, 'convertEUCKRtoUTF8All');
    } else if(isset($array) && !is_null($array) && is_string($array)) {
          //$array = iconv($slang,$tlang,$array);
        if (iconv("UTF-8","UTF-8",$array) == $array) {
          //$array = $array;
        } else {
          $array = iconv("CP949","UTF-8",$array);
        }
    }
}
//echo $SQL;
$db->query($SQL);
if($db->nf())
{
    header("Content-type: application/json;charset=utf-8");
    //@header("Content-type: charset=euc-kr");
    //@header("Content-type: html/plan; charset=euc-kr");
    //@header("Content-type: html/plan; charset=utf-8");
    $n = $db->nf();
    $data = $db->getRecordAll();
    /*
    $value = array();
    $index = 0;
    while ($db->next_record()){
        $row = $db->Record;
        $value[$index] = $row;
        $index++;
    }
    //$data = $db->RecordAll;
    $Fun->iconv_utf8All($value);
     * 
     */
    
    SetResultValue($data);
    /*
    $value = array(
        "0" => array("id" => "abcd", "name" => "가나다라"),
        "1" => array(0 => "ABCD", "name" => "家羅多羅"),
    );
    */
    $isPrintResult = true;
    //echo json_encode($Result, JSON_UNESCAPED_UNICODE);
}