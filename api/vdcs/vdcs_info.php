<?php if(!defined("_API_INCLUDE_")) exit;

//ini_set('memory_limit','10240M');
//ini_set('post_max_size','10000M');
ini_set('default_socket_timeout', -1);
// ini_set('default_socket_timeout', 180);
set_time_limit(0);

//$request_model_type = "latest";
/*
enum RequestVdcsModelType : string
{
    case Latest = "LATEST";
    case DocHistory = "DOC_HISTORY";
    case None = "None";
}
 * 
 */
//$requestVdcsModelType = RequestVdcsModelType::None;

$isNaviActive = false;
$navi_page = -1;
$navi_offset = 20;
$iStartRow = -1;
$nLimitCount = $navi_offset;

if(isset($post) && is_array($post) && array_key_exists("navi_page", $post))
{
    $navi_page = intval($post["navi_page"]);
    if(!$navi_page || $navi_page <= 0)
    {
        $navi_offset = -1;
    }
}

if(isset($post) && is_array($post) && array_key_exists("navi_offset", $post))
{
    $navi_offset = $post["navi_offset"];
    if(!$navi_offset || $navi_offset <= 0)
    {
        $navi_offset = 20;
    }
}

//$so_all = null; //전체
//$so_doc = null; //Doc. No. / Title
//$so_vn = null;  //Vendor Name
//$so_tr = null;  //TR No.
//$so_ti = null;  //Tag / Item No. 
//$so_rt = null;  //RFQ. No. / Title
//$so_dc = null;  //Discipline Code
//$so_rc = null;  //Result Code - so_rc



$m_where = null;
$SearchList = null;
/*
$OptSearchCase = "OR";
if(isset($post) && is_array($post) && array_key_exists("so_research", $post))
{
    //$so_all = $post["so_all"];
    $OptSearchCase = "AND";
}
*/
if(isset($post) && is_array($post) && array_key_exists("so_all", $post))
{
    //$so_all = $post["so_all"];
    $SearchList["so_all"] = $post["so_all"];
}

if(isset($post) && is_array($post) && array_key_exists("so_doc", $post))
{
    $SearchList["so_doc"] = $post["so_doc"];
}
if(isset($post) && is_array($post) && array_key_exists("so_doc_no", $post))
{
    $SearchList["so_doc_no"] = $post["so_doc_no"];
}
if(isset($post) && is_array($post) && array_key_exists("so_doc_ti", $post))
{
    $SearchList["so_doc_ti"] = $post["so_doc_ti"];
}

if(isset($post) && is_array($post) && array_key_exists("so_vn", $post))
{
    $SearchList["so_vn"] = $post["so_vn"];
}
if(isset($post) && is_array($post) && array_key_exists("so_tr", $post))
{
    $SearchList["so_tr"] = $post["so_tr"];
}
if(isset($post) && is_array($post) && array_key_exists("so_ti", $post))
{
    $SearchList["so_ti"] = $post["so_ti"];
}
if(isset($post) && is_array($post) && array_key_exists("so_rt", $post))
{
    $SearchList["so_rt"] = $post["so_rt"];
}
if(isset($post) && is_array($post) && array_key_exists("so_dc", $post))
{
    $SearchList["so_dc"] = $post["so_dc"];
}
if(isset($post) && is_array($post) && array_key_exists("so_rc", $post))
{
    $SearchList["so_rc"] = $post["so_rc"];
}

if(isset($SearchList) && is_array($SearchList) && count($SearchList) > 0)
{
    foreach ($SearchList as $_col => $_keywords)
    {
        $arr = explode("♡", $_keywords);
        if(isset($arr) && is_array($arr))
        {
            foreach ($arr as $_val) 
            {
                $m_where .= GetQueryWhereStringCase($_col, $_val);
            }
        }
    }
}
//echo $m_where;
//exit;
function GetQueryWhereStringCase($col, $val)
{
    $return = null;
    if(isset($col) && !is_null($col) && trim($col) != "" && isset($val) && !is_null($val) && trim($val) != "")
    {
        $col = strtolower(trim($col));
        $val = strtoupper(trim($val));
        switch ($col)
        {
            case "so_doc" :
                $return .= " AND ( ";
                $return .= " UPPER(doc_num) LIKE '%{$val}%' ";
                $return .= " OR UPPER(doc_title) LIKE '%{$val}%' ";
                $return .= " ) ";
                break;
            case "so_doc_no" :
                $return .= " AND UPPER(doc_num) LIKE '%{$val}%' ";
                break;
            case "so_doc_ti" :
                $return .= " AND UPPER(doc_title) LIKE '%{$val}%' ";
                break;
            
            case "so_vn" :
                $return .= " AND UPPER(from_comp_name) LIKE '%{$val}%' ";
                break;
            case "so_tr" :
                $return .= " AND UPPER(tr_doc_num) LIKE '%{$val}%' ";
                break;
            case "so_ti" :
                $return .= " AND UPPER(doc_tag_item) LIKE '%{$val}%' ";
                break;
            case "so_rt" :
                $return .= " AND ( ";
                $return .= " UPPER(doc_rfq_num) LIKE '%{$val}%' ";
                $return .= " OR UPPER(doc_rfq_title) LIKE '%{$val}%' ";
                $return .= " ) ";
                break;
            case "so_dc" :
                $return .= " AND UPPER(doc_func_cd) = '{$val}' ";
                break;
            case "so_rc" :
				if($val == "NULL")
				{
					$return .= " AND doc_status_nick is NULL ";
				}
				else
				{
					$return .= " AND UPPER(doc_status_nick) = '{$val}' ";
				}
                break;
            case "so_all" :
                $return .= " AND ( ";
                $return .= "   UPPER(doc_num) LIKE '%{$val}%' ";
                $return .= "   OR UPPER(doc_title) LIKE '%{$val}%' ";
                $return .= "   OR UPPER(from_comp_name) LIKE '%{$val}%' ";
                $return .= "   OR UPPER(tr_doc_num) LIKE '%{$val}%' ";
                $return .= "   OR UPPER(doc_tag_item) LIKE '%{$val}%' ";
                $return .= "   OR UPPER(doc_rfq_num) LIKE '%{$val}%' ";
                $return .= "   OR UPPER(doc_rfq_title) LIKE '%{$val}%' ";
                $return .= "   OR UPPER(tr_func_cd) = '{$val}' ";
                $return .= "   OR UPPER(doc_status_nick) = '{$val}' ";
                $return .= " ) ";
                break;
            default :
                break;
        }
    }
    return $return;
}

$sd_date_type = null;
$sd_date_start = null;
$sd_date_end = null;
if(isset($post) && is_array($post) && array_key_exists("sd_type", $post))
{
    //$SearchList["sd_date_type"] = $post["sd_date_type"];
    $sd_date_type = strtoupper($post["sd_type"]);
    
    if(isset($post) && is_array($post) && array_key_exists("sd_start_date", $post))
    {
        //$SearchList["sd_date_start"] = $post["sd_date_start"];
        $sd_date_start = $post["sd_start_date"];
    }
    if(isset($post) && is_array($post) && array_key_exists("sd_end_date", $post))
    {
        //$SearchList["sd_date_end"] = $post["sd_date_end"];
        $sd_date_end = $post["sd_end_date"];
    }
    if($sd_date_start || $sd_date_end)
    {
        switch($sd_date_type)
        {
            case "DISTRIBUTE":
            case "REPLY":
                $m_where .= GetQueryWhereDateCase($sd_date_type, $sd_date_start, $sd_date_end);
                break;
            default:
                break;
        }
    }
}
function GetQueryWhereDateCase($col, $start_val, $end_val)
{
    $return = null;
    $strColName = null;
    $strColName2 = null;
    switch($col)
    {
        case "DISTRIBUTE" :
            $strColName = "DOC_DISTRIBUTE_DATE";
            $strColName2 = "DOC_DISTRIBUTE_DATE_STR";
            break;
        case "REPLY" :
            $strColName = "DOC_REPLY_DATE";
            $strColName2 = "DOC_REPLY_DATE_STR";
            break;
        default :
            $strColName = null;
                break;
    }
    if($strColName)
    {
        $return .= " AND ";
        if($start_val && $end_val)
        {
            $return .= " ( {$strColName} BETWEEN TO_DATE('{$start_val}', 'YYYY-MM-DD') AND TO_DATE('{$end_val}', 'YYYY-MM-DD') )";
            //$return .= " ( {$strColName2} BETWEEN '{$start_val}' AND '{$end_val}' )";
        }
        else if($start_val)
        {
            $return .= " {$strColName} >= TO_DATE('{$start_val}','YYYY-MM-DD') ";
            //$return .= " {$strColName2} >= '{$start_val}' ";
        } 
        else if($end_val)
        {
            $return .= " {$strColName} <= TO_DATE('{$end_val}','YYYY-MM-DD') ";
            //$return .= " {$strColName2} <= '{$end_val}' ";
        }
        $return .= " ";
    }
    //echo $return;
    //exit;
    return $return;
}

try 
{
    
    $SQL_LatestWith = "WITH DC AS
(
SELECT DC.BIND_JNO JNO
    , DC.*
    , DECODE(TR.TR_STATUS, 'F', DC.DOC_STATUS, DECODE(TR.TR_STATUS, 'Z', DC.DOC_STATUS, NULL)) DOC_RESULT_NO
	, TR.TR_DOC_NUM, TR.TR_DOC_TITLE, TR.TR_DOC_TYPE, TR.TR_ISSUE_STATUS
	, TR.TR_RFQ_NUM, TR.TR_RFQ_TITLE, TR.TR_PO_NUM
	, TR.TR_FUNC_NO, TR.TR_LE_UNO, TR.TR_LE_TITLE, TR.TR_DCC_UNO, TR.TR_DCC_TITLE, TR.DEPLOY_UNO, TR.DEPLOY_TYPE, TR.DEPLOY_DATE, TR.DEPLOY_EXPIRY_DATE
	, TR.TR_RECEIVE_DATE, TR.TR_ISSUE_DATE, TR.TR_DUE_DATE, TR.TR_FORECAST_DATE, TR.TR_ACTUAL_DATE, TR.TR_RETURN_DATE, TR.TR_STEP_INTVAL, TR.TR_STATUS
	, TR.TR_TAG_ITEM, TR.FROM_COMP_NAME TR_FROM_COMP_NAME
    --, NVL(DECODE(TR.TR_STATUS, 'F', DC.DEPLOY_FILE_NAME), DC.DOC_FILE_NAME) ATCH_FILE_NAME
    , DC.DOC_NUM || '_r' || DC.DOC_REV_NUM || ' ' || DC.DOC_TITLE || '.pdf'  DEFAULT_FILE_NAME
	, DC.DOC_FILE_NAME ATCH_FILE_NAME
    , NVL(DECODE(TR.TR_STATUS, 'F', DC.DEPLOY_FILE_SAVE), DC.DOC_FILE_SAVE) ATCH_FILE_SAVE
    , NVL(DECODE(TR.TR_STATUS, 'F', DC.DEPLOY_FILE_PATH), DC.DOC_FILE_PATH) ATCH_FILE_PATH
    , NVL(DECODE(TR.TR_STATUS, 'F', DC.DEPLOY_FILE_SIZE), DC.DOC_FILE_SIZE) ATCH_FILE_SIZE
    , NVL(DECODE(TR.TR_STATUS, 'F', DC.DEPLOY_FILE_CHECK), DC.DOC_FILE_CHECK) ATCH_FILE_CHECK
    , NVL(DECODE(TR.TR_STATUS, 'F', DC.DEPLOY_FILE_TYPE), DC.DOC_FILE_TYPE) ATCH_FILE_TYPE
    , NVL(NVL(DECODE(TR.TR_STATUS, 'F', DC.DEPLOY_UNO), DC.REG_UNO), TR.TR_LE_UNO) ATCH_FILE_UNO
    , NVL(DECODE(TR.TR_STATUS, 'F', DC.DEPLOY_DATE), DC.REG_DATE) ATCH_FILE_DATE
    , NVL(DECODE(TR.TR_STATUS, 'F', 'REPLY'), 'DISTRIBUTE') ATCH_TYPE
	, TR.REG_DATE TR_REG_DATE, TR.MOD_DATE TR_MOD_DATE
FROM VDCS_VPDC_SET DC
	, VDCS_VPTR_SET TR
WHERE 1 = 1
	AND DC.TR_NO = TR.TR_NO
	AND DC.DOC_STATUS <> '4'
	AND DC.IS_USE = 'Y' AND TR.IS_USE = 'Y'
),
TAG_GROUP AS
(
SELECT JNO, MS_NO, LISTAGG (TAG_ITEM, '，') WITHIN GROUP (ORDER BY TAG_ITEM) AS TAG_ITEM_AGG FROM VDCS_VPMS_TAG
GROUP BY JNO, MS_NO
),
DC_GROUP AS
(
SELECT DC.JNO, DC.MS_NO, COUNT (0) AS DOC_CNT, COUNT( DECODE(DC.TR_STATUS, 'F', 1) ) REPLY_CNT
	, MIN(DC.DOC_NO) keep (dense_rank first order by DC.REG_DATE) FIRST_DOC_NO
    , MAX(DC.DOC_NO) keep (dense_rank last order by DC.REG_DATE) LAST_DOC_NO
    --, MIN(DC.TR_NO) keep (dense_rank first order by DC.REG_DATE) FIRST_TR_NO
    --, MAX(DC.TR_NO) keep (dense_rank last order by DC.REG_DATE) LAST_TR_NO
	, LISTAGG (DC.TR_NO, '，') WITHIN GROUP (ORDER BY DC.REG_DATE) AS TR_NO_AGG
	, LISTAGG (DC.TR_DOC_NUM, '，') WITHIN GROUP (ORDER BY DC.REG_DATE) AS TR_DOC_NUM_AGG
	, LISTAGG (DC.DOC_NO, '，') WITHIN GROUP (ORDER BY DC.REG_DATE) AS DOC_NO_AGG
	, LISTAGG (DC.DOC_NUM, '，') WITHIN GROUP (ORDER BY DC.REG_DATE) AS DOC_NUM_AGG
	, LISTAGG (DC.DOC_TITLE, '，') WITHIN GROUP (ORDER BY DC.REG_DATE) AS DOC_TITLE_AGG
	, LISTAGG (DC.DOC_FILE_NAME, '，') WITHIN GROUP (ORDER BY DC.REG_DATE) AS DOC_FILE_NAME_AGG
	, MAX(DC.DOC_STATUS) keep (dense_rank last order by DC.REG_DATE) LAST_DOC_STATUS
	--, MAX(DC.DOC_TITLE) keep (dense_rank last order by DC.REG_DATE) LAST_DOC_TITLE
	, MIN(DC.TR_ISSUE_DATE) keep (dense_rank first order by DC.REG_DATE) FIRST_TR_ISSUE_DATE
    , MAX(DC.TR_ISSUE_DATE) keep (dense_rank last order by DC.REG_DATE)  LAST_TR_ISSUE_DATE
    , MIN(DC.TR_ACTUAL_DATE) keep (dense_rank first order by DC.REG_DATE) FIRST_TR_ACTUAL_DATE
    , MAX(DC.TR_ACTUAL_DATE) keep (dense_rank last order by DC.REG_DATE)  LAST_TR_ACTUAL_DATE
    , MIN(DC.REG_DATE) keep (dense_rank first order by DC.REG_DATE) FIRST_REG_DATE
    , MAX(DC.MOD_DATE) keep (dense_rank last order by DC.REG_DATE)  LAST_MOD_DATE
    , MIN(DC.REG_UNO) keep (dense_rank first order by DC.REG_DATE) REG_UNO
    , MAX(DC.MOD_UNO) keep (dense_rank last order by DC.REG_DATE)  MOD_UNO
    , MIN(DC.REG_USER) keep (dense_rank first order by DC.REG_DATE) REG_USER
    , MAX(DC.MOD_USER) keep (dense_rank last order by DC.REG_DATE)  MOD_USER
    , MIN(DC.REG_AGENT) keep (dense_rank first order by DC.REG_DATE) REG_AGENT
    , MAX(DC.MOD_AGENT) keep (dense_rank last order by DC.REG_DATE)  MOD_AGENT
	, MIN(DC.REG_DATE) keep (dense_rank first order by DC.REG_DATE) REG_DATE
    , MAX(DC.MOD_DATE) keep (dense_rank last order by DC.REG_DATE)  MOD_DATE
FROM DC
WHERE 1 = 1
GROUP BY JNO, MS_NO
ORDER BY JNO, MS_NO
),
A AS (
    SELECT
	MS.JNO, MS.MS_NO, DCG.DOC_CNT, DCG.REPLY_CNT
	, DC.TR_NO, DC.TR_DOC_NUM, DC.TR_DOC_TITLE, DC.TR_STATUS
	, DC.DOC_NO, DCG.FIRST_DOC_NO, DCG.LAST_DOC_NO, DC.DOC_NO LATEST_DOC_NO
	, DC.DOC_STATUS, DC.TR_FUNC_NO, DC.DOC_FUNC_NO
	, NVL(MS.DOC_NUM, DC.DOC_NUM) DOC_NUM, MS.DOC_NUM ENV_NUM, DC.DOC_NUM LATEST_DOC_NUM
	, NVL(MS.DOC_TITLE, DC.DOC_TITLE) DOC_TITLE, MS.DOC_TITLE ENV_TITLE, DC.DOC_TITLE LATEST_DOC_TITLE
	, NVL(MS.DOC_REV_NUM, DC.DOC_REV_NUM) DOC_REV_NUM, MS.DOC_REV_NUM ENV_REV_NUM, DC.DOC_REV_NUM LATEST_DOC_REV_NUM
	, NVL(NVL(MS.DOC_RFQ_NUM, DC.DOC_RFQ_NUM), DC.TR_RFQ_NUM) DOC_RFQ_NUM, MS.DOC_RFQ_NUM ENV_RFQ_NUM, NVL(DC.DOC_RFQ_NUM, TR_RFQ_NUM) LATEST_DOC_RFQ_NUM
	, NVL(DC.DOC_PO_NUM, DC.TR_PO_NUM) DOC_PO_NUM, DC.TR_PO_NUM, DC.DOC_PO_NUM LATEST_DOC_PO_NUM
	, NVL(NVL(MS.FROM_COMP_NAME, DC.TR_FROM_COMP_NAME), DC.TR_FROM_COMP_NAME) FROM_COMP_NAME, MS.FROM_COMP_NAME ENV_FROM_COMP_NAME, DC.TR_FROM_COMP_NAME, DC.FROM_COMP_NAME LATEST_FROM_COMP_NAME
	--, DC.TR_RFQ_TITLE
	, NVL(MS.DOC_FILE_NM, DC.DOC_FILE_NAME) DOC_FILE_NM, MS.DOC_FILE_NM ENV_FILE_NM, DC.DOC_FILE_NAME LATEST_FILE_NAME
	, DC.DOC_DESC LATEST_DOC_DESC, DC.TR_RFQ_TITLE LATEST_RFQ_TITLE, DC.TR_RFQ_TITLE DOC_RFQ_TITLE
	, DCG.FIRST_TR_ISSUE_DATE, DCG.LAST_TR_ISSUE_DATE, DC.TR_ISSUE_DATE LATEST_TR_ISSUE_DATE
	, DCG.FIRST_TR_ACTUAL_DATE, DCG.LAST_TR_ACTUAL_DATE, NVL(NVL(DC.TR_ACTUAL_DATE, DCG.LAST_TR_ACTUAL_DATE),DCG.FIRST_TR_ACTUAL_DATE) LATEST_TR_ACTUAL_DATE
	, NVL(NVL(TAG.TAG_ITEM_AGG, MS.DOC_TAG_ITEM), DC.DOC_TAG_ITEM) AS DOC_TAG_ITEM, DC.DOC_TAG_ITEM LATEST_DOC_TAG_ITEM
	, DCG.TR_NO_AGG, DCG.TR_DOC_NUM_AGG, DCG.DOC_NO_AGG, DCG.DOC_NUM_AGG, DCG.DOC_TITLE_AGG, DCG.DOC_FILE_NAME_AGG
	--, DCG.TR_NO_AGG, DCG.DOC_NO_AGG, DCG.DOC_NUM_AGG, DCG.DOC_TITLE_AGG
	, TFT.FUNC_CD TR_FUNC_CD, TFT.FUNC_NAME TR_FUNC_NAME
	, DFT.FUNC_CD DOC_FUNC_CD, DFT.FUNC_NAME DOC_FUNC_NAME
	, DRT.CODE_NAME DOC_STATUS_NAME, DRT.CODE_NAME_NICK DOC_STATUS_NICK, DRT.DESCR DOC_STATUS_DESCR
	, DC.DOC_RESULT_NO, TRT.CODE_NAME DOC_RESULT_NAME, TRT.CODE_NAME_NICK DOC_RESULT_NICK, TRT.DESCR DOC_RESULT_DESCR
	, DCG.FIRST_REG_DATE, DCG.LAST_MOD_DATE
	, DC.DEFAULT_FILE_NAME, DC.ATCH_FILE_NAME, DC.ATCH_FILE_SAVE, DC.ATCH_FILE_PATH, DC.ATCH_FILE_SIZE, DC.ATCH_FILE_CHECK, DC.ATCH_FILE_TYPE, DC.ATCH_FILE_DATE, DC.ATCH_FILE_UNO, DC.ATCH_TYPE
	, 'Y' AS IS_USE
	, DCG.REG_UNO, DCG.MOD_UNO, DCG.REG_USER, DCG.MOD_USER, DCG.REG_AGENT, DCG.MOD_AGENT, DCG.REG_DATE, DCG.MOD_DATE
    FROM VDCS_VPMS_ENV MS
	, DC
	, DC_GROUP DCG
	, TAG_GROUP TAG
	, SYS_FUNC_TYPE TFT
	, SYS_FUNC_TYPE DFT
	, (SELECT * FROM SYS_DOC_RESULT_TYPE WHERE CODE_GROUP_NO = 7) DRT
	, (SELECT * FROM SYS_DOC_RESULT_TYPE WHERE CODE_GROUP_NO = 7) TRT
    WHERE 1 = 1
	AND ( MS.JNO = DCG.JNO AND MS.MS_NO = DCG.MS_NO )
	AND ( DCG.JNO = DC.JNO AND DCG.LAST_DOC_NO = DC.DOC_NO )
	AND ( MS.JNO = TAG.JNO(+) AND MS.MS_NO = TAG.MS_NO(+) )
	AND DC.DOC_STATUS = DRT.CODE_CD(+)
	AND DC.DOC_RESULT_NO = TRT.CODE_CD(+)
	AND DC.TR_FUNC_NO = TFT.FUNC_NO(+)
	AND DC.DOC_FUNC_NO = DFT.FUNC_NO(+)
)";
    
    
$SQL_LatestWith2 = "WITH DC AS
(
SELECT DC.BIND_JNO JNO
    , DC.*
    , DECODE(TR.TR_STATUS, 'F', DC.DOC_STATUS, DECODE(TR.TR_STATUS, 'Z', DC.DOC_STATUS, NULL)) DOC_RESULT_NO
	, TR.TR_DOC_NUM, TR.TR_DOC_TITLE, TR.TR_DOC_TYPE, TR.TR_ISSUE_STATUS
	, TR.TR_RFQ_NUM, TR.TR_RFQ_TITLE, TR.TR_PO_NUM
	, TR.TR_FUNC_NO, TR.TR_LE_UNO, TR.TR_LE_TITLE, TR.TR_DCC_UNO, TR.TR_DCC_TITLE, TR.DEPLOY_UNO, TR.DEPLOY_TYPE, TR.DEPLOY_DATE, TR.DEPLOY_EXPIRY_DATE
	, TR.TR_RECEIVE_DATE, TR.TR_ISSUE_DATE, TR.TR_DUE_DATE, TR.TR_FORECAST_DATE, TR.TR_ACTUAL_DATE
        , DECODE(TR.TR_STATUS, 'F', 
            CASE DC.DOC_STATUS
                WHEN '' THEN NULL
                WHEN '0' THEN NULL
                WHEN '1' THEN NULL
                WHEN '100' THEN NULL
                ELSE TR.TR_RETURN_DATE
            END
            ) TR_RETURN_DATE
        , TR.TR_STEP_INTVAL, TR.TR_STATUS
	, TR.TR_TAG_ITEM, TR.FROM_COMP_NAME TR_FROM_COMP_NAME
    --, NVL(DECODE(TR.TR_STATUS, 'F', DC.DEPLOY_FILE_NAME), DC.DOC_FILE_NAME) ATCH_FILE_NAME
    , DC.DOC_NUM || '_r' || DC.DOC_REV_NUM || ' ' || DC.DOC_TITLE || '.pdf'  DEFAULT_FILE_NAME
	, DC.DOC_FILE_NAME ATCH_FILE_NAME
    --, NVL(DECODE(TR.TR_STATUS, 'F', DC.DEPLOY_FILE_SAVE), DC.DOC_FILE_SAVE) ATCH_FILE_SAVE
    --, NVL(DECODE(TR.TR_STATUS, 'F', DC.DEPLOY_FILE_PATH), DC.DOC_FILE_PATH) ATCH_FILE_PATH
    --, NVL(DECODE(TR.TR_STATUS, 'F', DC.DEPLOY_FILE_SIZE), DC.DOC_FILE_SIZE) ATCH_FILE_SIZE
    --, NVL(DECODE(TR.TR_STATUS, 'F', DC.DEPLOY_FILE_CHECK), DC.DOC_FILE_CHECK) ATCH_FILE_CHECK
    --, NVL(DECODE(TR.TR_STATUS, 'F', DC.DEPLOY_FILE_TYPE), DC.DOC_FILE_TYPE) ATCH_FILE_TYPE
    --, NVL(DECODE(TR.TR_STATUS, 'F', DC.DEPLOY_DATE), DC.REG_DATE) ATCH_FILE_DATE
    --, NVL(DECODE(TR.TR_STATUS, 'F', 'REPLY'), 'DISTRIBUTE') ATCH_TYPE
    --, NVL(NVL(DECODE(TR.TR_STATUS, 'F', DC.DEPLOY_UNO), DC.REG_UNO), TR.TR_LE_UNO) ATCH_FILE_UNO
    , NVL(DECODE(TR.TR_STATUS, 'F', 'REPLY', DECODE(DC.DOC_STATUS, '0', 'ISSUE') ), 'DISTRIBUTE') ATCH_TYPE
    , NVL(DECODE(DC.DOC_STATUS, NULL, DC.DOC_FILE_SAVE, '100', DC.DOC_FILE_SAVE, '999', DC.DOC_FILE_SAVE), DC.DEPLOY_FILE_SAVE) ATCH_FILE_SAVE
    , NVL(DECODE(DC.DOC_STATUS, NULL, DC.DOC_FILE_PATH, '100', DC.DOC_FILE_PATH, '999', DC.DOC_FILE_SAVE), DC.DEPLOY_FILE_PATH) ATCH_FILE_PATH
    , NVL(DECODE(DC.DOC_STATUS, NULL, DC.DOC_FILE_SIZE, '100', DC.DOC_FILE_SIZE, '999', DC.DOC_FILE_SIZE), DC.DEPLOY_FILE_SIZE) ATCH_FILE_SIZE
    , NVL(DECODE(DC.DOC_STATUS, NULL, DC.DOC_FILE_CHECK, '100', DC.DOC_FILE_CHECK, '999', DC.DOC_FILE_CHECK), DC.DEPLOY_FILE_CHECK) ATCH_FILE_CHECK
    , NVL(DECODE(DC.DOC_STATUS, NULL, DC.DOC_FILE_TYPE, '100', DC.DOC_FILE_TYPE, '999', DC.DOC_FILE_TYPE), DC.DEPLOY_FILE_TYPE) ATCH_FILE_TYPE
    , NVL(DECODE(DC.DOC_STATUS, NULL, DC.REG_DATE, '100', DC.REG_DATE, '999', DC.REG_DATE), DC.DEPLOY_DATE) ATCH_FILE_DATE
    , NVL(DECODE(DC.DOC_STATUS, NULL, TR.TR_LE_UNO, '100', TR.TR_LE_UNO, '999', TR.TR_LE_UNO), DC.DEPLOY_UNO) ATCH_FILE_UNO
	, TR.REG_DATE TR_REG_DATE, TR.MOD_DATE TR_MOD_DATE
FROM VDCS_VPDC_SET DC
	, VDCS_VPTR_SET TR
WHERE 1 = 1
	AND DC.TR_NO = TR.TR_NO
	AND ( DC.DOC_STATUS IS NULL OR ( DC.DOC_STATUS IS NOT NULL AND DC.DOC_STATUS <> '4' ) )
	AND DC.IS_USE = 'Y' AND TR.IS_USE = 'Y'
),
TAG_GROUP AS
(
SELECT JNO, MS_NO, LISTAGG (TAG_ITEM, '，') WITHIN GROUP (ORDER BY TAG_ITEM) AS TAG_ITEM_AGG FROM VDCS_VPMS_TAG
GROUP BY JNO, MS_NO
),
DC_GROUP AS
(
SELECT DC.JNO, DC.MS_NO, COUNT (0) AS DOC_CNT, COUNT( DECODE(DC.TR_STATUS, 'F', 1) ) REPLY_CNT
	, MIN(DC.DOC_NO) keep (dense_rank first order by DC.REG_DATE) FIRST_DOC_NO
    , MAX(DC.DOC_NO) keep (dense_rank last order by DC.REG_DATE) LAST_DOC_NO
    --, MIN(DC.TR_NO) keep (dense_rank first order by DC.REG_DATE) FIRST_TR_NO
    --, MAX(DC.TR_NO) keep (dense_rank last order by DC.REG_DATE) LAST_TR_NO
	, LISTAGG (DC.TR_NO, '，') WITHIN GROUP (ORDER BY DC.REG_DATE) AS TR_NO_AGG
	, LISTAGG (DC.TR_DOC_NUM, '，') WITHIN GROUP (ORDER BY DC.REG_DATE) AS TR_DOC_NUM_AGG
	, LISTAGG (DC.DOC_NO, '，') WITHIN GROUP (ORDER BY DC.REG_DATE) AS DOC_NO_AGG
	, LISTAGG (DC.DOC_NUM, '，') WITHIN GROUP (ORDER BY DC.REG_DATE) AS DOC_NUM_AGG
	, LISTAGG (DC.DOC_TITLE, '，') WITHIN GROUP (ORDER BY DC.REG_DATE) AS DOC_TITLE_AGG
	, LISTAGG (DC.DOC_FILE_NAME, '，') WITHIN GROUP (ORDER BY DC.REG_DATE) AS DOC_FILE_NAME_AGG
	, MAX(DC.DOC_STATUS) keep (dense_rank last order by DC.REG_DATE) LAST_DOC_STATUS
	--, MAX(DC.DOC_TITLE) keep (dense_rank last order by DC.REG_DATE) LAST_DOC_TITLE
	, MIN(DC.TR_ISSUE_DATE) keep (dense_rank first order by DC.REG_DATE) FIRST_TR_ISSUE_DATE
    , MAX(DC.TR_ISSUE_DATE) keep (dense_rank last order by DC.REG_DATE)  LAST_TR_ISSUE_DATE
    , MIN(DC.TR_ACTUAL_DATE) keep (dense_rank first order by DC.REG_DATE) FIRST_TR_ACTUAL_DATE
    , MAX(DC.TR_ACTUAL_DATE) keep (dense_rank last order by DC.REG_DATE)  LAST_TR_ACTUAL_DATE
    , MIN(DC.TR_RETURN_DATE) keep (dense_rank first order by DC.REG_DATE) FIRST_TR_RETURN_DATE
    , MAX(DC.TR_RETURN_DATE) keep (dense_rank last order by DC.REG_DATE)  LAST_TR_RETURN_DATE
    , MIN(DC.REG_DATE) keep (dense_rank first order by DC.REG_DATE) FIRST_REG_DATE
    , MAX(DC.MOD_DATE) keep (dense_rank last order by DC.REG_DATE)  LAST_MOD_DATE
    , MIN(DC.REG_UNO) keep (dense_rank first order by DC.REG_DATE) REG_UNO
    , MAX(DC.MOD_UNO) keep (dense_rank last order by DC.REG_DATE)  MOD_UNO
    , MIN(DC.REG_USER) keep (dense_rank first order by DC.REG_DATE) REG_USER
    , MAX(DC.MOD_USER) keep (dense_rank last order by DC.REG_DATE)  MOD_USER
    , MIN(DC.REG_AGENT) keep (dense_rank first order by DC.REG_DATE) REG_AGENT
    , MAX(DC.MOD_AGENT) keep (dense_rank last order by DC.REG_DATE)  MOD_AGENT
	, MIN(DC.REG_DATE) keep (dense_rank first order by DC.REG_DATE) REG_DATE
    , MAX(DC.MOD_DATE) keep (dense_rank last order by DC.REG_DATE)  MOD_DATE
FROM DC
WHERE 1 = 1
GROUP BY JNO, MS_NO
ORDER BY JNO, MS_NO
),
AA AS (
SELECT
	MS.JNO, MS.MS_NO, DCG.DOC_CNT, DCG.REPLY_CNT
	, DC.TR_NO, DC.TR_DOC_NUM, DC.TR_DOC_TITLE, DC.TR_STATUS
	, DC.DOC_NO, DCG.FIRST_DOC_NO, DCG.LAST_DOC_NO, DC.DOC_NO LATEST_DOC_NO
	, DC.DOC_STATUS, DC.TR_FUNC_NO, DC.DOC_FUNC_NO
	, NVL(MS.DOC_NUM, DC.DOC_NUM) DOC_NUM, MS.DOC_NUM ENV_NUM, DC.DOC_NUM LATEST_DOC_NUM
	, NVL(MS.DOC_TITLE, DC.DOC_TITLE) DOC_TITLE, MS.DOC_TITLE ENV_TITLE, DC.DOC_TITLE LATEST_DOC_TITLE
	, NVL(MS.DOC_REV_NUM, DC.DOC_REV_NUM) DOC_REV_NUM, MS.DOC_REV_NUM ENV_REV_NUM, DC.DOC_REV_NUM LATEST_DOC_REV_NUM
	, NVL(NVL(MS.DOC_RFQ_NUM, DC.DOC_RFQ_NUM), DC.TR_RFQ_NUM) DOC_RFQ_NUM, MS.DOC_RFQ_NUM ENV_RFQ_NUM, NVL(DC.DOC_RFQ_NUM, TR_RFQ_NUM) LATEST_DOC_RFQ_NUM
	, NVL(DC.DOC_PO_NUM, DC.TR_PO_NUM) DOC_PO_NUM, DC.TR_PO_NUM, DC.DOC_PO_NUM LATEST_DOC_PO_NUM
	, NVL(NVL(MS.FROM_COMP_NAME, DC.FROM_COMP_NAME), DC.TR_FROM_COMP_NAME) FROM_COMP_NAME, MS.FROM_COMP_NAME ENV_FROM_COMP_NAME, DC.TR_FROM_COMP_NAME, DC.FROM_COMP_NAME LATEST_FROM_COMP_NAME
	--, DC.TR_RFQ_TITLE
	, NVL(MS.DOC_FILE_NM, DC.DOC_FILE_NAME) DOC_FILE_NM, MS.DOC_FILE_NM ENV_FILE_NM, DC.DOC_FILE_NAME LATEST_FILE_NAME
	, DC.DOC_DESC LATEST_DOC_DESC, DC.TR_RFQ_TITLE LATEST_RFQ_TITLE
	, DCG.FIRST_TR_ISSUE_DATE, DCG.LAST_TR_ISSUE_DATE, DC.TR_ISSUE_DATE LATEST_TR_ISSUE_DATE
	, DCG.FIRST_TR_ACTUAL_DATE, DCG.LAST_TR_ACTUAL_DATE, NVL(NVL(DC.TR_ACTUAL_DATE, DCG.LAST_TR_ACTUAL_DATE),DCG.FIRST_TR_ACTUAL_DATE) LATEST_TR_ACTUAL_DATE
        , DCG.FIRST_TR_RETURN_DATE, DCG.LAST_TR_RETURN_DATE, NVL(DC.TR_RETURN_DATE, DCG.LAST_TR_RETURN_DATE) LATEST_TR_RETURN_DATE
	, NVL(NVL(TAG.TAG_ITEM_AGG, MS.DOC_TAG_ITEM), DC.DOC_TAG_ITEM) AS DOC_TAG_ITEM, DC.DOC_TAG_ITEM LATEST_DOC_TAG_ITEM
	, DCG.TR_NO_AGG, DCG.TR_DOC_NUM_AGG, DCG.DOC_NO_AGG, DCG.DOC_NUM_AGG, DCG.DOC_TITLE_AGG, DCG.DOC_FILE_NAME_AGG
	--, DCG.TR_NO_AGG, DCG.DOC_NO_AGG, DCG.DOC_NUM_AGG, DCG.DOC_TITLE_AGG
	, TFT.FUNC_CD TR_FUNC_CD, TFT.FUNC_NAME TR_FUNC_NAME
	, DFT.FUNC_CD DOC_FUNC_CD, DFT.FUNC_NAME DOC_FUNC_NAME
	, DRT.CODE_NAME DOC_STATUS_NAME, DRT.CODE_NAME_NICK DOC_STATUS_NICK, DRT.DESCR DOC_STATUS_DESCR
	, DC.DOC_RESULT_NO, TRT.CODE_NAME DOC_RESULT_NAME, TRT.CODE_NAME_NICK DOC_RESULT_NICK, TRT.DESCR DOC_RESULT_DESCR
	, DCG.FIRST_REG_DATE, DCG.LAST_MOD_DATE
	, DC.DEFAULT_FILE_NAME, DC.ATCH_FILE_NAME, DC.ATCH_FILE_SAVE, DC.ATCH_FILE_PATH, DC.ATCH_FILE_SIZE, DC.ATCH_FILE_CHECK, DC.ATCH_FILE_TYPE, DC.ATCH_FILE_DATE, DC.ATCH_FILE_UNO, DC.ATCH_TYPE
	, 'Y' AS IS_USE
	, DCG.REG_UNO, DCG.MOD_UNO, DCG.REG_USER, DCG.MOD_USER, DCG.REG_AGENT, DCG.MOD_AGENT, DCG.REG_DATE, DCG.MOD_DATE
FROM VDCS_VPMS_ENV MS
	, DC
	, DC_GROUP DCG
	, TAG_GROUP TAG
	, SYS_FUNC_TYPE TFT
	, SYS_FUNC_TYPE DFT
	, (SELECT * FROM SYS_DOC_RESULT_TYPE WHERE CODE_GROUP_NO = 7) DRT
	, (SELECT * FROM SYS_DOC_RESULT_TYPE WHERE CODE_GROUP_NO = 7) TRT
WHERE 1 = 1
	AND ( MS.JNO = DCG.JNO AND MS.MS_NO = DCG.MS_NO )
	AND ( DCG.JNO = DC.JNO AND DCG.LAST_DOC_NO = DC.DOC_NO )
	AND ( MS.JNO = TAG.JNO(+) AND MS.MS_NO = TAG.MS_NO(+) )
	AND DC.DOC_STATUS = DRT.CODE_CD(+)
	AND DC.DOC_RESULT_NO = TRT.CODE_CD(+)
	AND DC.TR_FUNC_NO = TFT.FUNC_NO(+)
	AND DC.DOC_FUNC_NO = DFT.FUNC_NO(+)
), 
A AS 
(
    SELECT 
        AA.*
        --, AA.LATEST_DOC_RFQ_NUM DOC_RFQ_NUM
        , AA.LATEST_RFQ_TITLE DOC_RFQ_TITLE
        , AA.LATEST_TR_ISSUE_DATE DOC_DISTRIBUTE_DATE
        , TO_CHAR(AA.LATEST_TR_ISSUE_DATE, 'YYYY-MM-DD') DOC_DISTRIBUTE_DATE_STR
        --, AA.LATEST_TR_ACTUAL_DATE DOC_REPLY_DATE
        , DECODE(AA.DOC_RESULT_NO, NULL, NULL, AA.LATEST_TR_ACTUAL_DATE) DOC_REPLY_DATE
        , DECODE(AA.DOC_RESULT_NO, NULL, NULL, TO_CHAR(AA.LATEST_TR_ACTUAL_DATE, 'YYYY-MM-DD') ) DOC_REPLY_DATE_STR
        , DECODE(AA.DOC_RESULT_NO, NULL, NULL, AA.LATEST_TR_RETURN_DATE) DOC_RETURN_DATE
        , DECODE(AA.DOC_RESULT_NO, NULL, NULL, TO_CHAR(AA.LATEST_TR_RETURN_DATE, 'YYYY-MM-DD') ) DOC_RETURN_DATE_STR
        , TRUNC(SYSDATE) TO_DAY_DATE
        , TO_CHAR(TRUNC(SYSDATE), 'YYYY-MM-DD') TO_DAY_DATE_STR
        , SYSDATE NOW_DATE
        , TO_CHAR(SYSDATE, 'YYYY-MM-DD HH24:MI:SS') NOW_DATE_STR
    FROM AA
) ";

    $SQL_LatestFull ="{$SQL_LatestWith2}
SELECT 
	ROW_NUMBER () OVER (ORDER BY REG_DATE, JNO, MS_NO) ROWNO, --ROWNUM RNUM,
	A.* 
FROM A
WHERE 1 = 1";
    
    switch ($requestVdcsModelType)
    {
        case RequestVdcsModelType::DocHistory :
            $ms_no = -1;
            if(isset($post) && is_array($post) && array_key_exists("ms_no", $post))
            {
                $ms_no = intval($post["ms_no"]);
            }
            /** 2022.12.20 이전 쿼리
            $SQL = "WITH 
TR AS (
SELECT 
    TR.*,
    FT.FUNC_CD AS TR_FUNC_CD, FT.FUNC_NAME AS TR_FUNC_NAME, FT.FUNC_TITLE AS TR_FUNC_TITLE, 
    CONCAT ('[' || FT.FUNC_CD || ']',
             FT.FUNC_NAME || ' - ' || FT.FUNC_TITLE)
         TR_FUNC_STR,
         
    DECODE(TR_STATUS, 'I', DECODE (TR_DUE_DATE,
             NULL, NULL,
             TO_NUMBER (TRUNC (SYSDATE) - TRUNC (TR_DUE_DATE)))
         , NULL)
         AS TR_OVER_DUE,
     TO_CHAR (TR_ISSUE_DATE, 'YYYY-MM-DD')
         TR_ISSUE_DATE_STR,
     TO_CHAR (TR_DUE_DATE, 'YYYY-MM-DD')
         TR_DUE_DATE_STR,
     TO_CHAR (TR_RECEIVE_DATE, 'YYYY-MM-DD')
         TR_RECEIVE_DATE_STR,
     TO_CHAR (TR_FORECAST_DATE, 'YYYY-MM-DD')
         TR_FORECAST_DATE_STR,
     TO_CHAR (TR_ACTUAL_DATE, 'YYYY-MM-DD')
         TR_ACTUAL_DATE_STR,
     TO_CHAR (TR.REG_DATE, 'YYYY-MM-DD')
         TR_REG_DATE_STR,
     CASE TR.TR_STATUS
         WHEN 'I' THEN '진행 중'
         WHEN 'C' THEN '취소'
         WHEN 'F' THEN '완료'
         WHEN 'S' THEN '발신 중'
         WHEN 'G' THEN '수신 중'
         WHEN 'H' THEN 'HOLD'
         WHEN 'P' THEN '검토 중'
         WHEN 'Y' THEN '승인'
         ELSE TR.TR_STATUS
     END
         AS TR_STATUS_STR,         
    J.JOB_NO, J.JOB_NAME, J.JOB_SD, J.JOB_ED, J.JOB_STATE,
    CASE J.JOB_STATE
         WHEN 'Y' THEN '진행 중'
         WHEN 'H' THEN 'HOLD'
         WHEN 'C' THEN '취소'
         WHEN 'S' THEN '완료'
         WHEN 'T' THEN '임시'
         WHEN 'N' THEN '사용 안함'
         ELSE J.JOB_STATE
     END JOB_STATE_STR
FROM VDCS_VPTR_SET TR
    , (SELECT FUNC_NO,FUNC_CD,FUNC_NAME,FUNC_TITLE FROM SYS_FUNC_TYPE) FT
    , SYS_JOB_INFO J
WHERE 1 = 1
    AND TR.TR_FUNC_NO = FT.FUNC_NO(+)
    AND TR.JNO = J.JNO(+)
),
RT as
(
SELECT * FROM SYS_DOC_RESULT_TYPE WHERE CODE_GROUP_NO = 7
),
DC AS
(
SELECT * FROM VDCS_VPDC_SET WHERE IS_USE = 'Y' --and doc_no = :doc_no
),
A AS
(
SELECT 
           DC.DOC_NO,
           TR.JNO,
           DC.TR_NO,
           TR.TR_FUNC_NO FUNC_NO,
           TR.TR_FUNC_CD FUNC_CD,
           DC.BIND_JNO,
           DC.FNO,
           DC.MS_NO,
           DC.DOC_NUM,
           DC.DOC_PO_NUM,
           DC.DOC_RFQ_NUM,
           DC.DOC_TYPE,
           DC.DOC_TITLE,
           DC.DOC_DESC,
           DC.DOC_FUNC_NO,
           DC.DOC_CODE1,
           DC.DOC_CODE2,
           DC.DOC_REV_NUM,
           DC.DOC_PAGE_CNT,
           DC.DOC_TAG_ITEM,
           DC.DOC_SORT_NO,
           DC.DOC_REMARK,
           DC.DOC_STATUS,
           DC.DOC_FILE_CHECK,
           DC.DOC_FILE_NAME,
           DC.DOC_FILE_SAVE,
           DC.DOC_FILE_PATH,
           DC.DOC_FILE_SIZE,
           DC.DOC_FILE_TYPE,
           DC.DEPLOY_FILE_CHECK,
           DC.DEPLOY_FILE_NAME,
           DC.DEPLOY_FILE_SAVE,
           DC.DEPLOY_FILE_PATH,
           DC.DEPLOY_FILE_SIZE,
           DC.DEPLOY_FILE_TYPE,
           DC.DEPLOY_REMARK,
           DC.DEPLOY_DATE,
           DC.DEPLOY_UNO,
           DC.FROM_COMP_NO,
           DC.FROM_COMP_NAME,
           DC.FROM_COMP_OPT,
           DC.FROM_COMP_REMARK,
           DC.TO_COMP_NO,
           DC.TO_COMP_NAME,
           DC.TO_COMP_OPT,
           DC.TO_COMP_REMARK,
           TR.TR_DOC_NUM,
           TR.TR_DOC_TITLE,
           TR.TR_FUNC_NO,
           TR.TR_FUNC_STR,
           TR.TR_FUNC_NAME,
           TR.TR_FUNC_TITLE,
           TR.TR_STATUS,
           TR.TR_STATUS_STR,
           TR.TR_RECEIVE_DATE,
           TR.TR_RECEIVE_DATE_STR,
           TR.TR_DUE_DATE,
           TR.TR_DUE_DATE_STR,
           TR.TR_ISSUE_DATE,
           TR.TR_ISSUE_DATE_STR,
           TR.TR_ACTUAL_DATE,
           TR.TR_ACTUAL_DATE_STR,
           TR.JOB_NO,
           TR.JOB_NAME,
           TR.JOB_SD,
           TR.JOB_ED,
           TR.JOB_STATE,
           TR.JOB_STATE_STR,
           RT.CODE_NAME DOC_STATUS_NAME,
           RT.CODE_NAME_NICK DOC_STATUS_NICK,
           RT.DESCR DOC_STATUS_DESCR,
           --DF.LAST_MK_NO SEND_LAST_MK_NO,
           --DF.SEND_UNO,
           --DF.SEND_TITLE,
           --DF.SEND_RESULT,
           --DF.SEND_MESSAGE,
           --DF.REMARK_STR SEND_REMARK_STR,
           --DF.IS_STATUS IS_SEND_STATUS,
           --DF.IS_AUTH_COMMENT,
           --DECODE(DF.DOC_NO, NULL, 'N', 'Y') IS_SEND_EXITS,
           DC.IS_USE,
           DC.RAW_GUID,
           DC.REG_DATE,
           DC.REG_AGENT,
           DC.REG_USER,
           DC.MOD_DATE,
           DC.MOD_AGENT,
           DC.MOD_USER
      FROM DC
        , TR
        , RT
        --, (SELECT * FROM VDCS_VPDC_FUNC WHERE IS_USE = 'Y') DF
     WHERE 1 = 1 
        AND DC.TR_NO = TR.TR_NO
        AND DC.DOC_STATUS = RT.CODE_CD(+)
        --AND DC.DOC_NO = DF.DOC_NO(+)
        --AND DC.TR_NO = 1016
        --and tr.tr_no = :tr_no
        --and dc.tr_no = :tr_no
        --and dc.doc_no = :doc_no
    ORDER BY JNO, FUNC_CD, DOC_NO, TR_NO, DOC_NO --ORDER BY JNO, TR_NO, DOC_NO
    --ORDER BY JNO, FUNC_CD, DOC_NUM, TR_NO, DOC_NO --ORDER BY JNO, TR_NO, DOC_NO
)
SELECT 
    ROWNUM RNUM,
    --ROW_NUMBER () OVER (ORDER BY JNO, TR_NO DESC, DOC_NO DESC)     ROWNO,
    ROW_NUMBER () OVER (ORDER BY JNO, FUNC_CD, DOC_NUM DESC, TR_NO DESC, DOC_NO DESC)     ROWNO,
     A.DOC_NO,A.JNO,A.TR_NO,A.FUNC_NO,A.FUNC_CD,A.BIND_JNO,A.FNO,A.MS_NO,A.DOC_NUM,A.DOC_PO_NUM,A.DOC_RFQ_NUM,A.DOC_TYPE,A.DOC_TITLE,A.DOC_DESC,A.DOC_FUNC_NO,A.DOC_CODE1,A.DOC_CODE2,A.DOC_REV_NUM,A.DOC_PAGE_CNT,A.DOC_TAG_ITEM,A.DOC_SORT_NO,A.DOC_REMARK
     ,A.DOC_STATUS
     ,A.DOC_FILE_CHECK,A.DOC_FILE_NAME,A.DOC_FILE_SAVE,A.DOC_FILE_PATH,A.DOC_FILE_SIZE,A.DOC_FILE_TYPE
     ,A.DEPLOY_FILE_CHECK,A.DEPLOY_FILE_NAME,A.DEPLOY_FILE_SAVE,A.DEPLOY_FILE_PATH,A.DEPLOY_FILE_SIZE,A.DEPLOY_FILE_TYPE,A.DEPLOY_REMARK,A.DEPLOY_DATE,A.DEPLOY_UNO
     ,A.FROM_COMP_NO,A.FROM_COMP_NAME,A.FROM_COMP_OPT,A.FROM_COMP_REMARK,A.TO_COMP_NO,A.TO_COMP_NAME,A.TO_COMP_OPT,A.TO_COMP_REMARK
     ,A.TR_DOC_NUM,A.TR_DOC_TITLE,A.TR_FUNC_NO,A.TR_FUNC_STR,A.TR_FUNC_NAME,A.TR_FUNC_TITLE,A.TR_STATUS,A.TR_STATUS_STR
     ,A.TR_RECEIVE_DATE,A.TR_RECEIVE_DATE_STR
     ,A.TR_DUE_DATE,A.TR_DUE_DATE_STR
     ,A.TR_ISSUE_DATE,A.TR_ISSUE_DATE_STR
     ,A.TR_ACTUAL_DATE,A.TR_ACTUAL_DATE_STR
     ,A.JOB_NO,A.JOB_NAME,A.JOB_SD,A.JOB_ED,A.JOB_STATE,A.JOB_STATE_STR
     ,A.DOC_STATUS_NAME,A.DOC_STATUS_NICK,A.DOC_STATUS_DESCR
     ,A.TR_RECEIVE_DATE HIST_RECEIVE_DATE,A.TR_RECEIVE_DATE_STR HIST_RECEIVE_DATE_STR
     ,A.TR_ISSUE_DATE HIST_DISTRIBUTE_DATE, A.TR_ISSUE_DATE_STR HIST_DISTRIBUTE_DATE_STR
     ,A.TR_DUE_DATE HIST_DUE_DATE,A.TR_DUE_DATE_STR HIST_DUE_DATE_STR
     ,A.DEPLOY_DATE HIST_ISSUE_DATE,TO_CHAR (A.DEPLOY_DATE, 'YYYY-MM-DD') HIST_ISSUE_DATE_STR
     ,A.TR_ACTUAL_DATE HIST_REPLY_DATE,A.TR_ACTUAL_DATE_STR HIST_REPLY_DATE_STR
    ,A.IS_USE,A.RAW_GUID,A.REG_DATE,A.REG_AGENT,A.REG_USER,A.MOD_DATE,A.MOD_AGENT,A.MOD_USER
FROM A
WHERE 1 = 1 AND JNO = {$jno}";
            */
            /** 2023-01-19 속도 문제로 변경
            $SQL = "WITH  --V_VDCS_VPDC_INFO
TR AS (
SELECT 
    TR.*,
    FT.FUNC_CD AS TR_FUNC_CD, FT.FUNC_NAME AS TR_FUNC_NAME, FT.FUNC_TITLE AS TR_FUNC_TITLE, 
    CONCAT ('[' || FT.FUNC_CD || ']', FT.FUNC_NAME || ' - ' || FT.FUNC_TITLE) TR_FUNC_STR, 
    DECODE(TR_STATUS, 'I', DECODE (TR_DUE_DATE,
             NULL, NULL,
             TO_NUMBER (TRUNC (SYSDATE) - TRUNC (TR_DUE_DATE)))
         , NULL)
         AS TR_OVER_DUE,
     TO_CHAR (TR_ISSUE_DATE, 'YYYY-MM-DD')
         TR_ISSUE_DATE_STR,
     TO_CHAR (TR_DUE_DATE, 'YYYY-MM-DD')
         TR_DUE_DATE_STR,
     TO_CHAR (TR_RECEIVE_DATE, 'YYYY-MM-DD')
         TR_RECEIVE_DATE_STR,
     TO_CHAR (TR_FORECAST_DATE, 'YYYY-MM-DD')
         TR_FORECAST_DATE_STR,
     TO_CHAR (TR_ACTUAL_DATE, 'YYYY-MM-DD')
         TR_ACTUAL_DATE_STR,
     TO_CHAR (TR.REG_DATE, 'YYYY-MM-DD')
         TR_REG_DATE_STR,
     CASE TR.TR_STATUS
         WHEN 'I' THEN '진행 중(In Progress)'
         WHEN 'C' THEN '취소(Cancel)'
         WHEN 'F' THEN '완료(Reply)'
         WHEN 'S' THEN '발신 중'
         WHEN 'G' THEN '수신 중'
         WHEN 'H' THEN '대기(Hold)'
         WHEN 'P' THEN '검토 중(Closed)'
         WHEN 'Y' THEN '승인(Approval)'
         WHEN 'Z' THEN 'V/P Final'
         ELSE TR.TR_STATUS
     END
         AS TR_STATUS_STR,
    J.JOB_NO, J.JOB_NAME, J.JOB_SD, J.JOB_ED, J.JOB_STATE,
    CASE J.JOB_STATE
         WHEN 'Y' THEN '진행 중'
         WHEN 'H' THEN 'HOLD'
         WHEN 'C' THEN '취소'
         WHEN 'S' THEN '완료'
         WHEN 'T' THEN '임시'
         WHEN 'N' THEN '사용 안함'
         ELSE J.JOB_STATE
     END JOB_STATE_STR
FROM VDCS_VPTR_SET TR
    , (SELECT FUNC_NO,FUNC_CD,FUNC_NAME,FUNC_TITLE FROM SYS_FUNC_TYPE) FT
    , SYS_JOB_INFO J
WHERE 1 = 1
    AND TR.TR_FUNC_NO = FT.FUNC_NO(+)
    AND TR.JNO = J.JNO(+)
),
RT as
(
SELECT * FROM SYS_DOC_RESULT_TYPE WHERE CODE_GROUP_NO = 7
),
DC AS
(
SELECT 
      MS.JNO
    , DC.*
    , MS.DOC_NUM ENV_DOC_NUM, MS.DOC_TITLE ENV_DOC_TITLE, MS.DOC_RFQ_NUM ENV_DOC_RFQ_NUM, MS.FROM_COMP_NAME ENV_FROM_COMP_NAME, MS.DOC_TAG_ITEM ENV_DOC_TAG_ITEM
    , NVL(MS.DOC_NUM, DC.DOC_NUM) DLG_DOC_NUM, NVL(MS.DOC_TITLE, DC.DOC_TITLE) DLG_DOC_TITLE, NVL(MS.DOC_RFQ_NUM, DC.DOC_RFQ_NUM) DLG_DOC_RFQ_NUM, NVL(MS.FROM_COMP_NAME, DC.FROM_COMP_NAME) DLG_FROM_COMP_NAME, NVL(MS.DOC_TAG_ITEM, DC.DOC_TAG_ITEM) DLG_DOC_TAG_ITEM
 FROM VDCS_VPDC_SET DC
    , VDCS_VPMS_ENV MS
 WHERE DC.MS_NO = MS.MS_NO(+)
    AND DC.IS_USE = 'Y'
),
A AS
(
SELECT 
           DC.DOC_NO,
           TR.JNO,
           DC.TR_NO,
           TR.TR_FUNC_NO FUNC_NO,
           TR.TR_FUNC_CD FUNC_CD,
           DC.BIND_JNO,
           DC.FNO,
           DC.MS_NO,
           DC.DLG_DOC_NUM DOC_NUM,--DC.DOC_NUM,
           DC.DOC_PO_NUM,
           DC.DLG_DOC_RFQ_NUM DOC_RFQ_NUM,
           DC.DOC_TYPE,
           DC.DLG_DOC_TITLE DOC_TITLE,
           DC.DOC_DESC,
           DC.DOC_FUNC_NO,
           DC.DOC_CODE1,
           DC.DOC_CODE2,
           DC.DOC_REV_NUM,
           DC.DOC_PAGE_CNT,
           DC.DLG_DOC_TAG_ITEM DOC_TAG_ITEM,
           DC.DOC_SORT_NO,
           DC.DOC_REMARK,
           DC.DOC_STATUS,
           DC.DOC_FILE_CHECK,
           DC.DOC_FILE_NAME,
           DC.DOC_FILE_SAVE,
           DC.DOC_FILE_PATH,
           DC.DOC_FILE_SIZE,
           DC.DOC_FILE_TYPE,
           DC.DEPLOY_FILE_CHECK,
           DC.DEPLOY_FILE_NAME,
           DC.DEPLOY_FILE_SAVE,
           DC.DEPLOY_FILE_PATH,
           DC.DEPLOY_FILE_SIZE,
           DC.DEPLOY_FILE_TYPE,
           DC.DEPLOY_REMARK,
           DC.DEPLOY_DATE,
           DC.DEPLOY_UNO,
           DC.FROM_COMP_NO,
           NVL(DC.DLG_FROM_COMP_NAME, TR.FROM_COMP_NAME) FROM_COMP_NAME,
           DC.FROM_COMP_OPT,
           DC.FROM_COMP_REMARK,
           DC.TO_COMP_NO,
           DC.TO_COMP_NAME,
           DC.TO_COMP_OPT,
           DC.TO_COMP_REMARK,
           TR.TR_DOC_NUM,
           TR.TR_DOC_TITLE,
           TR.TR_REMARK,
           TR.TR_FUNC_NO,
           TR.TR_FUNC_STR,
           TR.TR_FUNC_NAME,
           TR.TR_FUNC_TITLE,
           TR.TR_STATUS,
           TR.TR_STATUS_STR,
           TR.TR_DUE_DATE,
           TR.TR_DUE_DATE_STR,
           TR.TR_ACTUAL_DATE,
           TR.TR_ACTUAL_DATE_STR,
           TR.TR_RECEIVE_DATE,
           TR.TR_FORECAST_DATE,
           TR.TR_RETURN_DATE,
           TR.TR_STEP_INTVAL,
           TR.JOB_NO,
           TR.JOB_NAME,
           TR.JOB_SD,
           TR.JOB_ED,
           TR.JOB_STATE,
           TR.JOB_STATE_STR,
           RT.CODE_NAME DOC_STATUS_NAME,
           RT.CODE_NAME_NICK DOC_STATUS_NICK,
           RT.DESCR DOC_STATUS_DESCR,
           --DF.LAST_MK_NO SEND_LAST_MK_NO,
           --DF.SEND_UNO,
           --DF.SEND_TITLE,
           --DF.SEND_RESULT,
           --DF.SEND_MESSAGE,
           --DF.REMARK_STR SEND_REMARK_STR,
           --DF.IS_STATUS IS_SEND_STATUS,
           --DF.IS_AUTH_COMMENT,
           --DECODE(DF.DOC_NO, NULL, 'N', 'Y') IS_SEND_EXIST,
           DC.IS_USE,
           DC.RAW_GUID,
           DC.REG_DATE,
           DC.REG_AGENT,
           DC.REG_USER,
           DC.MOD_DATE,
           DC.MOD_AGENT,
           DC.MOD_USER,
           TR.TR_ISSUE_DATE,
           TR.IS_USE IS_TR_USE
           --, DC.DLG_DOC_NUM
           , DC.ENV_DOC_NUM
           , DC.DOC_NUM SET_DOC_NUM
           , DC.DLG_DOC_TITLE
           , DC.ENV_DOC_TITLE
           , DC.DOC_TITLE SET_DOC_TITLE
           --, DC.DLG_DOC_RFQ_NUM
           , DC.ENV_DOC_RFQ_NUM
           , DC.DOC_RFQ_NUM SET_DOC_RFQ_NUM
           --, DC.DLG_DOC_TAG_ITEM
           , DC.ENV_DOC_TAG_ITEM
           , DC.DOC_TAG_ITEM SET_DOC_TAG_ITEM
           --, DC.DLG_FROM_COMP_NAME
           , DC.ENV_FROM_COMP_NAME
           , DC.FROM_COMP_NAME SET_FROM_COMP_NAME
      FROM DC
        , TR
        , RT
        --, (SELECT * FROM VDCS_VPDC_FUNC WHERE IS_USE = 'Y') DF
     WHERE 1 = 1 
        --AND TR.IS_USE = 'Y'
        AND DC.TR_NO = TR.TR_NO
        AND DC.DOC_STATUS = RT.CODE_CD(+)
        --AND DC.DOC_NO = DF.DOC_NO(+)
        --AND DC.TR_NO = 1016
        --and tr.tr_no = :tr_no
        --and dc.tr_no = :tr_no
        --and dc.doc_no = :doc_no
    --ORDER BY tr_no, doc_no, reg_date
),
AA AS (
SELECT 
    A.*
    , A.DOC_STATUS DOC_STATUS_NO
    , DECODE(A.TR_STATUS, 'F', A.DOC_STATUS, DECODE(A.TR_STATUS, 'Z', A.DOC_STATUS)) DOC_RESULT_NO
    , DECODE(A.TR_STATUS, 'F', A.DOC_STATUS_NICK, DECODE(A.TR_STATUS, 'Z', A.DOC_STATUS_NICK)) DOC_RESULT_NICK
    , DECODE(A.TR_STATUS, 'F', A.DOC_STATUS_NAME, DECODE(A.TR_STATUS, 'Z', A.DOC_STATUS_NAME)) DOC_RESULT_NAME
    , DECODE(A.TR_STATUS, 'F', A.DOC_STATUS_DESCR, DECODE(A.TR_STATUS, 'Z', A.DOC_STATUS_DESCR)) DOC_RESULT_DESCR
    , A.DOC_FILE_NAME ATCH_FILE_NAME
    , NVL(DECODE(A.TR_STATUS, 'F', A.DEPLOY_FILE_SAVE, DECODE(A.DOC_STATUS, '0', A.DEPLOY_FILE_SAVE) ), A.DOC_FILE_SAVE)  ATCH_FILE_SAVE
    , NVL(DECODE(A.TR_STATUS, 'F', A.DEPLOY_FILE_PATH, DECODE(A.DOC_STATUS, '0', A.DEPLOY_FILE_PATH) ), A.DOC_FILE_PATH) ATCH_FILE_PATH
    , NVL(DECODE(A.TR_STATUS, 'F', A.DEPLOY_FILE_SIZE, DECODE(A.DOC_STATUS, '0', A.DEPLOY_FILE_SIZE) ), A.DEPLOY_FILE_SIZE) ATCH_FILE_SIZE
    , NVL(DECODE(A.TR_STATUS, 'F', A.DEPLOY_FILE_CHECK, DECODE(A.DOC_STATUS, '0', A.DEPLOY_FILE_CHECK) ), A.DEPLOY_FILE_CHECK) ATCH_FILE_CHECK
    , NVL(DECODE(A.TR_STATUS, 'F', A.DEPLOY_FILE_TYPE, DECODE(A.DOC_STATUS, '0', A.DEPLOY_FILE_TYPE) ), A.DEPLOY_FILE_TYPE) ATCH_FILE_TYPE
    --, NVL(DECODE(A.TR_STATUS, 'F', 'REPLY'), 'DISTRIBUTE') ATCH_TYPE
    , NVL(DECODE(A.TR_STATUS, 'F', 'REPLY', DECODE(A.DOC_STATUS, '0', 'ISSUE') ), 'DISTRIBUTE') ATCH_TYPE
    , NVL(NVL(DECODE(A.TR_STATUS, 'F', A.TR_ACTUAL_DATE, DECODE(A.DOC_STATUS, '0', A.DEPLOY_DATE) ), A.TR_RECEIVE_DATE), A.REG_DATE) ATCH_DATE
    , NVL(A.TR_RECEIVE_DATE, A.REG_DATE) RECEIVE_DATE
    , NVL(A.TR_ISSUE_DATE, A.REG_DATE) DISTRIBUTE_DATE
    , A.DEPLOY_DATE ISSUE_DATE
    , DECODE(A.TR_STATUS, 'F', A.TR_ACTUAL_DATE) REPLY_DATE
FROM A
)
SELECT 
    ROWNUM RNUM,
    ROW_NUMBER () OVER (ORDER BY JNO, TR_NO, DOC_NO) ROWNO,
    --ROWNUM AS ROWNO,
    AA.*
	,AA.TR_RECEIVE_DATE HIST_RECEIVE_DATE, TO_CHAR(AA.TR_RECEIVE_DATE, 'YYYY-MM-DD') HIST_RECEIVE_DATE_STR
    ,AA.TR_ISSUE_DATE HIST_DISTRIBUTE_DATE, TO_CHAR(AA.TR_ISSUE_DATE, 'YYYY-MM-DD') HIST_DISTRIBUTE_DATE_STR
    ,AA.TR_DUE_DATE HIST_DUE_DATE, TO_CHAR(AA.TR_DUE_DATE, 'YYYY-MM-DD') HIST_DUE_DATE_STR
    ,AA.DEPLOY_DATE HIST_ISSUE_DATE, TO_CHAR(AA.DEPLOY_DATE, 'YYYY-MM-DD') HIST_ISSUE_DATE_STR
    ,AA.TR_ACTUAL_DATE HIST_REPLY_DATE, TO_CHAR(AA.TR_ACTUAL_DATE, 'YYYY-MM-DD') HIST_REPLY_DATE_STR
FROM AA
WHERE 1 = 1 
  AND is_tr_use = 'Y' AND is_use = 'Y'  --AND (doc_status IS NULL OR doc_status <> '4') 
  AND JNO = {$jno}
";
  
            if(isset($ms_no) && $ms_no > 0)
            {
                $SQL .= " AND MS_NO = {$ms_no}";
            }
            
            */
            
            $mWhere = null;
            if(isset($jno) && $jno > 0){
                $mWhere .= " AND TR.JNO = '{$jno}' ";
            }
            if(isset($ms_no) && $ms_no > 0)
            {
                $mWhere .= " AND DC.MS_NO = '{$ms_no}'";
            }
            
            $SQL = "WITH CD_FUNC AS 
(
	SELECT * FROM S_FUNC_CODE
	--SELECT * FROM COMMON.V_COMM_FUNC_CODE
),
CD_TR_STATUS AS
(
	SELECT * FROM CODE_ITEM_INFO 
	WHERE CODE_GROUP_NO = 20
	--ORDER BY VIEW_ORDER
),
CD_DOC_REST AS 
(
	SELECT * 
	FROM VDCS_REST_TYPE
),
TR AS
(
	SELECT * FROM (
		SELECT 
			 J.JOB_NO, J.JOB_NAME, J.JOB_STATE, J.JOB_STATE_STR
			 , J.COMP_CODE, J.COMP_NICK, J.COMP_NAME
			 , J.ORDER_COMP_NICK, J.ORDER_COMP_NAME
			,TR.*
			, TO_NUMBER(NVL(DECODE(TR.TR_STATUS, 'I', DECODE(TR.TR_DUE_DATE, NULL, NULL, TO_NUMBER (TRUNC (SYSDATE) - TRUNC (TR.TR_DUE_DATE))), NULL), NULL)) TR_OVER_DUE
			, CFT.FUNC_CD TR_FUNC_CD, CFT.FUNC_NAME TR_FUNC_NAME, CFT.FUNC_TITLE TR_FUNC_TITLE, '[' || CFT.FUNC_CD || ']' || CFT.FUNC_NAME TR_FUNC_CAPTION
			, CTS.CODE_NAME_NICK TR_STATUS_STR
		FROM VDCS_VPTR_SET	TR
			, V_PMS_JOB_INFO J
			, CD_FUNC CFT
			, CD_TR_STATUS CTS
		WHERE 1 = 1 
			AND TR.IS_USE = 'Y'
			AND J.JNO = TR.JNO
			AND TR.TR_FUNC_NO = CFT.FUNC_NO(+)
			AND TR.TR_STATUS = CTS.CODE_CD(+)
	)
	WHERE 1 = 1 
),
DC AS 
(
	SELECT 
		  TR.JNO JNO
		, DC.*
		, TR.TR_FUNC_NO, TR.TR_DOC_NUM, TR.TR_DOC_TITLE, TR.TR_STATUS
		, TR.DEPLOY_UNO TR_DEPLOY_UNO, TR.DEPLOY_DATE TR_DEPLOY_DATE, TR.DEPLOY_REMARK TR_DEPLOY_REMARK
		, TR.TR_RECEIVE_DATE AS HIST_RECEIVE_DATE, TO_CHAR(TR.TR_RECEIVE_DATE, 'YYYY-MM-DD') AS HIST_RECEIVE_DATE_STR
		, TR.TR_ISSUE_DATE AS HIST_DISTRIBUTE_DATE, TO_CHAR(TR.TR_ISSUE_DATE, 'YYYY-MM-DD') AS HIST_DISTRIBUTE_DATE_STR
		, TR.TR_DUE_DATE AS HIST_DUE_DATE, TO_CHAR(TR.TR_DUE_DATE, 'YYYY-MM-DD') AS HIST_DUE_DATE_STR
		, DC.DEPLOY_DATE  AS HIST_ISSUE_DATE, TO_CHAR(DC.DEPLOY_DATE, 'YYYY-MM-DD') AS HIST_ISSUE_DATE_STR
		, TR.TR_ACTUAL_DATE AS HIST_REPLY_DATE, TO_CHAR(TR.TR_ACTUAL_DATE, 'YYYY-MM-DD') AS HIST_REPLY_DATE_STR
		, CDR.*
	FROM VDCS_VPDC_SET DC
		, TR
		, CD_DOC_REST CDR
	WHERE DC.IS_USE = 'Y'
		AND DC.TR_NO = TR.TR_NO
		AND DC.DOC_STATUS = CDR.RESULT_NO(+)
                " . $mWhere . "
),
A AS 
(
	SELECT
		ROWNUM RNUM,
    	ROW_NUMBER () OVER (ORDER BY DC.JNO, DC.MS_NO, DC.TR_NO, DC.DOC_NO) ROWNO
		, DC.JNO, DC.MS_NO, DC.DOC_NO, DC.TR_NO 
		, DC.DOC_NUM, DC.DOC_TITLE, DC.DOC_REV_NUM, DC.DOC_RFQ_NUM, DC.DOC_TAG_ITEM
		, DC.TR_FUNC_NO, DC.DOC_FUNC_NO AS FUNC_NO, CFT.FUNC_CD FUNC_CD, CFT.FUNC_NAME FUNC_NAME, CFT.FUNC_TITLE FUNC_TITLE, '[' || CFT.FUNC_CD || ']' || CFT.FUNC_NAME FUNC_CAPTION
		, TR.TR_DOC_NUM, TR.TR_DOC_TITLE, TR.TR_STATUS, TR.TR_STATUS_STR
		, DC.DOC_STATUS, DC.RESULT_CD DOC_STATUS_NICK, DC.RESULT_NAME  DOC_STATUS_NAME, DC.RESULT_DESC DOC_STATUS_DESCR
		, DC.DOC_FILE_CHECK, DC.DOC_FILE_NAME, DC.DOC_FILE_SAVE, DC.DOC_FILE_PATH, DC.DOC_FILE_SIZE, DC.DOC_FILE_TYPE
		, DC.DEPLOY_FILE_CHECK, DC.DEPLOY_FILE_NAME, DC.DEPLOY_FILE_SAVE, DC.DEPLOY_FILE_PATH, DC.DEPLOY_FILE_SIZE, DC.DEPLOY_FILE_TYPE
		, DC.DEPLOY_REMARK, DC.DEPLOY_DATE, DC.DEPLOY_UNO
		, DC.HIST_RECEIVE_DATE, DC.HIST_RECEIVE_DATE_STR, DC.HIST_DISTRIBUTE_DATE,DC.HIST_DISTRIBUTE_DATE_STR, DC.HIST_DUE_DATE, DC.HIST_DUE_DATE_STR, DC.HIST_ISSUE_DATE, DC.HIST_ISSUE_DATE_STR, DC.HIST_REPLY_DATE, DC.HIST_REPLY_DATE_STR
	FROM DC
		, TR
		, CD_FUNC CFT
	WHERE 1 = 1
		AND ( DC.JNO = TR.JNO AND DC.TR_NO = TR.TR_NO )
		AND DC.DOC_FUNC_NO = CFT.FUNC_NO(+)
)
SELECT A.* FROM A
WHERE 1 = 1
";
            
            
            $SQL .= " ORDER BY JNO, MS_NO, TR_NO DESC, DOC_NO DESC";
if(isset($_SERVER) && $_SERVER["REMOTE_ADDR"] == "10.10.103.221")
{
	//echo $SQL;
	//exit;
}
            break;
        case RequestVdcsModelType::Latest :
        default :
            $nTotalCount = 0;
            if($navi_page >= 0)
            {
                //$SQL = "SELECT COUNT(*) CNT FROM V_VDCS_VPMS_LATEST WHERE IS_USE = 'Y'";
                $SQL = "{$SQL_LatestWith2}
                    SELECT COUNT(*) CNT FROM A 
                    WHERE IS_USE = 'Y' AND JNO = " . $jno;
                if(isset($m_where) && !is_null($m_where) && trim($m_where) != "")
                {
                    $SQL .= " " . $m_where;
                }
                
if(isset($_SERVER) && $_SERVER["REMOTE_ADDR"] == "10.10.103.221")
{
	//echo $SQL;
	//exit;
}
                $db->query($SQL);
                $db->next_record();
                $nTotalCount = intval($db->f("cnt")??0);
                //$nTotalCount = 20;
                if($nTotalCount > 0)
                {
                    $isNaviActive = true;
                    $iStartRow = ($navi_page - 1) * $navi_offset;
                    $nLimitCount = intval($navi_offset);
                    $Result[_RESULT_NAVI_][_RESULT_NAVI_ROW_TOTAL_] = $nTotalCount;
                    $Result[_RESULT_NAVI_][_RESULT_NAVI_ROW_START_] = $iStartRow + 1;
                    $Result[_RESULT_NAVI_][_RESULT_NAVI_ROW_OFFSET_] = $nLimitCount;
                    $Result[_RESULT_NAVI_][_RESULT_NAVI_ROW_END_] = $iStartRow + $nLimitCount;
                    $Result[_RESULT_NAVI_][_RESULT_NAVI_PAGE_TOTAL_] = ceil($nTotalCount / $nLimitCount);
                    $Result[_RESULT_NAVI_][_RESULT_NAVI_PAGE_CURR_] = $navi_page;
                }
            }
            //$SQL = "SELECT * FROM V_VDCS_VPMS_LATEST WHERE JNO = " . $jno;
            $SQL = $SQL_LatestFull . " AND JNO = " . $jno;
            if(isset($m_where) && !is_null($m_where) && trim($m_where) != "")
            {
                $SQL .= " " . $m_where;
            }
            $SQL .= " ORDER BY JNO, TR_FUNC_NO, DOC_RFQ_NUM, DOC_NUM, TR_DOC_NUM, TR_NO, DOC_NO";
            
        break;
    }
    if(!isset($SQL)) exit;
    
    
if(isset($_SERVER) && $_SERVER["REMOTE_ADDR"] == "10.10.103.221")
{
	//echo $SQL;
	//exit;
}
            
    if($isNaviActive == true && $iStartRow >= 0 && $nLimitCount > 0)
    {
        $db->query_limit($SQL, $iStartRow, $nLimitCount);
    }
    else
    {
        $db->query($SQL);
    }
    if($db->nf())
    {
        //$Result[_RESULT_UNO_] = $LoginUNo;
        
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
} 
catch (Exception $ex) 
{
    //$isCancel = true;
    //echo $Fun->print_r($ex);
    throw $ex;
}