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

//Element 별 유효성 검사
function validateElement(id) {
    var name = $("label[for='" + id + "']").text();
    var obj = document.getElementById(id);
    var isReadOnly = obj.readOnly;
    if (isReadOnly) {
        obj.readOnly = false;
    }
    obj.setCustomValidity("");
    if (obj.hasAttribute("required")) {
        if (obj.validity.valueMissing || !checkRequired(obj.value)) {
            obj.setCustomValidity(name + "은(는) 필수 입력입니다.");
        }
        else {
            obj.setCustomValidity("");
        }
    }
    //if (obj.validity.valid && obj.hasAttribute("minlength")) {
    if (!obj.validity.customError && obj.hasAttribute("minlength")) {
        if (obj.validity.tooShort) {
            var len = obj.getAttribute("minlength");
            obj.setCustomValidity(name + "은(는) " + len + "자 이상으로 입력해주세요.");
        }
        else {
            obj.setCustomValidity("");
        }
    }
    //if (obj.validity.valid && obj.hasAttribute("maxlength")) {
    if (!obj.validity.customError && obj.hasAttribute("maxlength")) {
        if (obj.validity.tooLong) {
            var len = obj.getAttribute("maxlength");
            obj.setCustomValidity(name + "은(는) " + len + "자 이하로 입력해주세요.");
        }
        else {
            obj.setCustomValidity("");
        }
    }
    //if (obj.validity.valid && obj.hasAttribute("min")) {
    if (!obj.validity.customError && obj.hasAttribute("min")) {
        var min = obj.getAttribute("min");
        if (obj.validity.rangeUnderflow) {
            obj.setCustomValidity(name + "은(는) " + min + " 이상으로 입력해주세요.");
        }
        else {
            obj.setCustomValidity("");
        }
    }
    //if (obj.validity.valid && obj.hasAttribute("max")) {
    if (!obj.validity.customError && obj.hasAttribute("max")) {
        var max = obj.getAttribute("max");
        if (obj.validity.rangeOverflow) {
            obj.setCustomValidity(name + "은(는) " + max + " 이하로 입력해주세요.");
        }
        else {
            obj.setCustomValidity("");
        }
    }

    var valid = true;
    var msg = [];
    var formGroup = $(obj).closest(".form-group");
    formGroup.find(".validateElement").each(function() {
        var elemId = $(this).attr("id");
        var elem = document.getElementById(elemId);
        if (elem.validity.valid) {
            $(elem).removeClass('is-valid is-invalid');
        }
        else {
            $(elem).addClass("is-invalid");
            msg.push(elem.validationMessage);
        }

        valid = valid && elem.validity.valid;
    });
    if (valid) {
        formGroup.find(".invalid-feedback").html("");
        formGroup.find(".invalid-feedback").hide();
    }
    else {
        formGroup.find(".invalid-feedback").html(msg.join("<br />"));
        formGroup.find(".invalid-feedback").show();
    }

    var valid = obj.validity.valid;

    if (isReadOnly) {
        obj.readOnly = true;
    }

    return valid;
}

/*
 * 필수 입력 체크
 */
function checkRequired( val ) {
    var trimVal = $.trim( val );
    if (trimVal == "" || trimVal == null) {
        return false;
    }
    return true;
}