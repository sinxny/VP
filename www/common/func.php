<?php
//페이지 목록
function getPageList($pageNo, $totalCnt, $customPageUnit, $componentName = "") {
    global $pageUnit;
    // 페이징 개수
    $pageListUnit = 10;

    if (!empty($customPageUnit)) {
        $pageUnit = $customPageUnit;
    }
    //총 페이지 수
    $maxPage = floor(($totalCnt-1)/$pageUnit) + 1;
    if ($maxPage <= 0) {
        $pageNo = 1;
        $maxPage = 1;
    }
    else if ($pageNo > $maxPage) {
        $pageNo = $maxPage;
    }

    //화면에 표시할 페이지 목록
    $showPageListNo = ceil($pageNo/$pageListUnit);
    //시작 페이지
    $pageStartNo = (($showPageListNo - 1) * $pageListUnit) + 1;
    //마지막 페이지
    $pageEndNo = $showPageListNo * $pageListUnit;
    if ($pageEndNo > $maxPage) {
        $pageEndNo = $maxPage;
    }
    //첫 페이지
    $pageList  = "<li class='page-item";
    if ($pageNo == 1) {
        $pageList .= " disabled";
    }
    $pageList .= "'><a class='page-link' href='javascript:void(0);' id='pageNo_1'><i class='fas fa-angle-double-left'></i></a></li>";
    //이전 페이지 목록
    $pageList .= "<li class='page-item";
    if ($showPageListNo == 1) {
        $pageList .= " disabled";
    }
    $pageList .= "'><a class='page-link' href='javascript:void(0);' ";
    if ($showPageListNo > 1) {
        $pageList .= " id=pageNo_" . ($pageStartNo-1);
    }
    $pageList .= " ><i class='fas fa-angle-left'></i></a></li>";
    //표시할 페이지 목록
    for($i = $pageStartNo; $i <= $pageEndNo; $i++) {
        $pageList .= "<li class='page-item";
        //해당 페이지
        if ($pageNo == $i) {
            $pageList .= " active";
        }
        $pageList .= "'><a class='page-link' href='javascript:void(0);' ";
        if ($pageNo != $i) {
            $pageList .= "id='pageNo_{$i}' ";
        }
        $pageList .= "> {$i} </a></li>";
    }
    //다음 페이지 목록
    $pageList .= "<li class='page-item";
    if ($pageEndNo == $maxPage) {
        $pageList .= " disabled";
    }
    $pageList .= "'><a class='page-link' href='javascript:void(0);' ";
    if ($pageEndNo < $maxPage) {
        $pageList .= " id='pageNo_" . ($pageEndNo+1) . "'";
    }
    $pageList .= " ><i class='fas fa-angle-right'></i></a></li>";
    //끝 페이지
    $pageList .= "<li class='page-item";
    if ($pageNo == $maxPage) {
    $pageList .= " disabled";
    }
    $pageList .= "'><a class='page-link' href='javascript:void(0);' id='pageNo_{$maxPage}' ><i class='fas fa-angle-double-right'></i></a></li>";

    return $pageList;
}
// 숫자->영어 서수로 변경
function numberToOrdinal($num) {
    if((substr($num, -1) == 1) && ($num != 11)) {
        return $num . "st";
    } else if ((substr($num, -1) == 2) && ($num != 12)) {
        return $num . "nd";
    } else if ((substr($num, -1) == 3) && ($num != 13)) {
        return $num . "rd";
    } else {
        return $num . "th";
    }
}
?>