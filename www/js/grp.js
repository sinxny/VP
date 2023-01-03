// 천단위 콤마
function numberToCommas(n) {
    if (n == "") {
        return "";
    }
    //음수 계산
    var minus = '';
    if(n.charAt(0) == "-") {
        var minus = "-";
    }

    //문자열, 소수점2개 제한
    n = n.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
    //콤마 지우기
    n = n.replace(/\,/g, '');

    //소수점 유무
    if(n.indexOf('.') > 0) {
        var decimal = n.split('.');
        var integer = decimal[0].replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,');
        //마이너스 붙이기
        return minus + integer + '.' + decimal[1];
    } else {
        //마이너스 붙이기
        return minus + n.replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,');
    }
}

//날짜 mim/max 값 넣기
function dateMinMaxAppend() {
    $("input[type=date]").each(function() {
        //min값
        $(this).attr("min", "2015-01-01");
        //max값
        var today = new Date();
        var toYear = today.getFullYear() + 1;
        var maxDt = toYear + '-12-31';

        $(this).attr("max", maxDt);
    });
}

// 배열 특정 키 제거 배열로 반환
function ArrayKeyRemove(arr, key) {
    var newArr = new Array();
    for(k in arr) {
        if(arr.hasOwnProperty) {
            if(k != key) {
                newArr[k] = arr[k];
            }
        }
    }
    return newArr;
}