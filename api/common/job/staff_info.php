<?php if(!defined("_API_INCLUDE_")) exit;
$comp_type = null;
if(isset($post) && is_array($post) && array_key_exists("comp_type", $post))
{
    $comp_type = strtoupper($post["comp_type"]);
}
if($comp_type == "ALL")
{
    $comp_type = null;
}
$m_orderby = "ORDER BY SORT_NO, CHARGE_SORT_VAL, CHARGE, AREA, REG_DATE";

$StaffSQL = "WITH OG AS 
(
SELECT ROW_NUMBER () OVER (ORDER BY  MIN(REG_DATE), FUNC_NAME) ROWNO
    , JNO, COMP_TYPE, FUNC_NAME
    , COUNT(*) CNT
    , MIN(REG_DATE) MIN_DATE 
FROM S_JOB_MEMBER_LIST 
WHERE COMP_TYPE = 'O' AND IS_USE = 'Y'
    AND JNO = {$jno}
GROUP BY JNO, COMP_TYPE, FUNC_NAME
), A AS
(
SELECT
    ML.MNO
    , ML.JNO
    , ML.COMP_TYPE
    , DECODE(ML.COMP_TYPE, 'H', '(주)하이테크엔지니어링', J.COMP_NAME) COMP_NAME
    , DECODE(ML.COMP_TYPE, 'H', '(주)하이테크엔지니어링', J.ORDER_COMP_NAME) ORDER_COMP_NAME
    , ML.AREA
    , ML.FUNC_CODE FUNC_NO
    , DECODE(ML.COMP_TYPE, 'H', FC.FUNC_CD, ML.FUNC_CODE) FUNC_CD
    , DECODE(ML.COMP_TYPE, 'H', FC.FUNC_NAME, ML.FUNC_NAME) FUNC_NAME
    , DECODE(ML.COMP_TYPE, 'H', FC.FUNC_TITLE, ML.FUNC_NAME) FUNC_TITLE
    , ML.CHARGE, C.CD_NM CHARGE_CD, ML.CHARGE_DETAIL
    , ML.UNO, U.USER_ID, U.USER_NAME, U.DUTY_NAME, DECODE(ML.COMP_TYPE, 'H', U.DEPT_NAME, ML.DEPT_NAME) DEPT_NAME
    , DECODE(ML.COMP_TYPE, 'H', NVL(U.USER_NAME, ML.MEMBER_NAME || '?'), ML.MEMBER_NAME) MEMBER_NAME
    , DECODE(ML.COMP_TYPE, 'H', NVL(U.DUTY_NAME, ML.GRADE_NAME || '?'), ML.GRADE_NAME) GRADE_NAME
    , DECODE(ML.COMP_TYPE, 'H', NVL(U.CELL, ML.CELL), ML.CELL) CELL
    , DECODE(ML.COMP_TYPE, 'H', NVL(U.TEL, ML.TEL), ML.TEL) TEL
    , DECODE(ML.COMP_TYPE, 'H', NVL(U.EMAIL, ML.EMAIL), ML.EMAIL) EMAIL
    , ML.CO_ID
    , U.IS_STATE, U.IS_GW_USER_USE, U.IS_ATTEND, U.IS_MOBILE_GW, U.IS_MSG, U.IS_USE IS_USER_USE
    , ML.REG_DATE
    , ML.MOD_DATE
    --, DECODE(ML.COMP_TYPE, 'H', DECODE(ML.FUNC_CODE, 80, '>0000') || fc.SORT_NO_PATH || '>' || u.duty_id, ML.sort_no) AS sort_no
    , DECODE(ML.COMP_TYPE, 'H', 
        DECODE(ML.FUNC_CODE, 80, '>0000') || '>' || LPAD(ML.FUNC_CODE, 4, '0') || '>' || LPAD(ML.AREA, 4, '0') || '>' || LPAD(C.VAL5, 6, '0') || '>' || LPAD(U.DEPT_CD, 6, '0') || '>' || LPAD(DUTY_VIEW_ORDER, 4, '0') || '>' || LPAD(U.USER_NAME, 10, '0')  || '>' || ML.SORT_NO
        --DECODE(ML.FUNC_CODE, 80, '>0000') || '>' || ML.FUNC_CODE || '>' || ML.AREA || '>' || C.VAL5 || '>' || U.DEPT_CD || '>' || U.DUTY_VIEW_ORDER || '>' || U.USER_NAME  || '>' || ML.SORT_NO
        , CASE C.VAL4
            WHEN 'PM' THEN '01>'    || LPAD(OG.ROWNO, 4, '0') || ML.CHARGE || '>' || C.VAL5 || '>' || ML.SORT_NO
            WHEN 'FUNC' THEN '02>'  || LPAD(OG.ROWNO, 4, '0') || ML.CHARGE || '>' || C.VAL5 || '>' || ML.SORT_NO
            WHEN 'CONST' THEN '03>' || LPAD(OG.ROWNO, 4, '0') || ML.CHARGE || '>' || C.VAL5 || '>' || ML.SORT_NO
            ELSE TO_CHAR(ML.sort_no)
        END 
    ) AS sort_no
    , C.VAL4 CHARGE_GROUP
    , C.VAL5 CHARGE_SORT_VAL
FROM " . JOB_TABLES::JOB_MEMBER_LIST . " ML
    , OG
    , ". JOB_TABLES::FUNC_CODE . " FC
    , (SELECT * FROM " . JOB_TABLES::JOB_CODE_SET . " WHERE MAJOR_CD ='MEMBER_CHARGE') C
    , (SELECT UNO, TO_CHAR(UNO) PM_CODE, USER_NAME, USER_ID, DUTY_ID, DUTY_CD, DUTY_VIEW_ORDER, DUTY_NAME, DEPT_ID, DEPT_CD, DEPT_NAME, CELL, TEL, EMAIL, JOIN_DATE, QUIT_DATE, IS_ADMIN, IS_MANAGER, IS_STATE, IS_GW_USER_USE, IS_ATTEND, IS_MOBILE_GW, IS_MSG, IS_USE, MOD_DATE FROM " . JOB_TABLES::USER_INFO . ") U
    , (SELECT JNO, JOB_NO, JOB_NAME, COMP_NAME, ORDER_COMP_NAME FROM " . JOB_TABLES::JOB_INFO . ") J
WHERE 1 = 1
    AND ML.IS_USE = 'Y' --AND U.IS_USE = 'Y'
    AND ( ML.JNO = OG.JNO(+) AND ML.COMP_TYPE = OG.COMP_TYPE(+) AND ML.FUNC_NAME = OG.FUNC_NAME(+) )
    AND ML.FUNC_CODE = FC.FUNC_NO(+)
    AND ML.CHARGE = C.MINOR_CD(+)
    AND ML.UNO = U.UNO(+) 
    AND ML.JNO = J.JNO(+)
    AND ( FUNC_NO IS NULL OR FUNC_NO NOT IN ('710', '720', '810', '820', '990', '999') )
 {$m_orderby}
)";
/*
if($comp_type == "H")
{
    //$m_orderby = " ORDER BY AREA, CHARGE_SORT_VAL, SORT_NO, CHARGE, REG_DATE ";
}
else
{
    //$m_orderby = " ORDER BY AREA, CHARGE_SORT_VAL, SORT_NO";
}
 * 
 */

$SQL = "{$StaffSQL}
SELECT ROWNUM RNUM
    , ROW_NUMBER () OVER (ORDER BY SORT_NO, CHARGE_SORT_VAL, AREA, CHARGE, REG_DATE) ROWNO
    , DECODE(COMP_TYPE, 'H', 'Internal', 'External') COMP_TYPE_STR
    , DECODE(COMP_TYPE, 'H', '계약자 조직', '사업주 조직') COMP_TYPE_NAME
    , A.*
FROM A
WHERE 1 = 1
    AND MEMBER_NAME <> '?'
    AND JNO = {$jno}
";
if($comp_type)
{
    $SQL .= " AND COMP_TYPE = '{$comp_type}'";
    if($comp_type == "H")
    {
        
    }
}
if(isset($_SERVER) && $_SERVER["REMOTE_ADDR"] == "10.10.103.221")
{
    //echo $SQL;
    //exit;
}
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