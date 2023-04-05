<style>
.jqx-dropdownlist-content {
    width: 90% !important;
}
</style>
<script>
$(document).ready(function() {
    showUserList();

    // 잡 여부에 따라 화면 변경
    var jno = sessionStorage.getItem("jno");

    if(jno) {
        $("#userSetting").show();
        $("#noProject").hide();
    } else {
        $("#userSetting").hide();
        $("#noProject").show();
    }

    // 조직도 포함 직원
    $.ajax({
        type: "GET",
        url: "/api/common/job/?api_key=d6c814548eeb6e41722806a0b057da30&api_pass=BQRUQAMXBVY=&model=STAFF_INFO&jno=" + jno,
        dataType: "json",
        success: function(result) {
            if(result["ResultType"] == "Success") {
                var oriPersonList = result["Value"];
                var personList = [];
                $(oriPersonList).each(function(i, person) {
                    personList.push(person["uno"]);
                })

                var overList = [];
                $(oriPersonList).each(function(i, person) {
                    var over = personList.lastIndexOf(person["uno"]);
                    if(over != i) {
                        overList.push(over);
                    }
                })

                // db 권한 사용자 리스트
                var dbRightList = getDbRightList();
                var userList = Object.keys(dbRightList);

                var html = '';
                $(oriPersonList).each(function(i, person) {
                    if(person["comp_type_str"] == "Internal" && !overList.includes(i)) {
                        html += '<tr>';
                        html += '<td class="text-center">'
                        html += '<i class="fa-solid fa-sitemap"></i>';
                        html += '</td>'
                        html += '<td class="text-center">'
                        html += person["member_name"];
                        html += '</td>'
                        html += '<td class="text-center">'
                        html += person["dept_name"];
                        html += '</td>'
                        html += '<td>'
                        if(userList.includes(person["uno"])) {
                            html += `<div class='jqxWidget'>
                                        <div class="dropMenuButton" id="dropBtn_${person["uno"]}">
                                            <div style="border: none;" class='jqxTree uno_${person["uno"]}'>
                                                <ul>
                                                    <li item-expanded='true'>Welding
                                                        <ul>`;
                            var codeList = dbRightList[person["uno"]]["codeText"];
                            if(codeList.includes("w_day")) {
                                html += '<li item-checked="true">WELDING DAY</li>';
                            } else {
                                html += '<li>WELDING DAY</li>';
                            }
                            if(codeList.includes("w_month")) {
                                html += '<li item-checked="true">WELDING MONTH</li>';
                            } else {
                                html += '<li>WELDING MONTH</li>';
                            }
                            if(codeList.includes("n_iso")) {
                                html += '<li item-checked="true">NDE BY ISO</li>';
                            } else {
                                html += '<li>NDE BY ISO</li>';
                            }
                            if(codeList.includes("n_welder")) {
                                html += '<li item-checked="true">NDE BY WELDER</li>';
                            } else {
                                html += '<li>NDE BY WELDER</li>';
                            }
                            if(codeList.includes("pkg")) {
                                html += '<li item-checked="true">PKG LIST</li>';
                            } else {
                                html += '<li>PKG LIST</li>';
                            }
                            html += `                    </ul>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>`;
                            // 조직도 인원 제외
                            userList = userList.filter((user) => {
                                return user != person["uno"];
                            });
                        } else {
                            html += `<div class='jqxWidget'>
                                        <div class="dropMenuButton" id="dropBtn_${person["uno"]}">
                                            <div style="border: none;" class='jqxTree uno_${person["uno"]}'>
                                                <ul>
                                                    <li item-checked='true' item-expanded='true'>Welding
                                                        <ul>
                                                            <li>WELDING DAY</li>
                                                            <li>WELDING MONTH</li>
                                                            <li>NDE BY ISO</li>
                                                            <li>NDE BY WELDER</li>
                                                            <li>PKG LIST</li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>`
                        }
                        html += '</td>'
                        overUno = person["uno"];
                    }
                });
                
                // db user
                $(userList).each(function(i, user) {
                    html +=  '<tr>';
                    html += '<td class="text-center">'
                    html += '</td>'
                    html += '<td class="text-center">'
                    html += dbRightList[user]["userName"];
                    html += '</td>'
                    html += '<td class="text-center">'
                    html += dbRightList[user]["deptName"];
                    html += '</td>'
                    html += '<td>'
                    html += `<div class='jqxWidget'>
                                <div class="dropMenuButton" id="dropBtn_${user}">
                                    <div style="border: none;" class='jqxTree uno_${user}'>
                                        <ul>
                                            <li item-expanded='true'>Welding
                                                <ul>`;
                    var codeList = dbRightList[user]["codeText"];
                    if(codeList.includes("w_day")) {
                        html += '<li item-checked="true">WELDING DAY</li>';
                    } else {
                        html += '<li>WELDING DAY</li>';
                    }
                    if(codeList.includes("w_month")) {
                        html += '<li item-checked="true">WELDING MONTH</li>';
                    } else {
                        html += '<li>WELDING MONTH</li>';
                    }
                    if(codeList.includes("n_iso")) {
                        html += '<li item-checked="true">NDE BY ISO</li>';
                    } else {
                        html += '<li>NDE BY ISO</li>';
                    }
                    if(codeList.includes("n_welder")) {
                        html += '<li item-checked="true">NDE BY WELDER</li>';
                    } else {
                        html += '<li>NDE BY WELDER</li>';
                    }
                    if(codeList.includes("pkg")) {
                        html += '<li item-checked="true">PKG LIST</li>';
                    } else {
                        html += '<li>PKG LIST</li>';
                    }
                    html += `                    </ul>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>`;
                    html += '</td>';
                    html += '</tr>';
                });

                $("#tblMenuRight tbody").append(html);
            }
        },
        complete: function() {
            // 권한 체크 드롭다운
            dropDownMenuRight();

            // 체크 된 항목 표시
            $('.jqxTree').each(function () {
                menuRightText(this)
            });
        }
    })
})

// 직원리스트
function showUserList() {
    var url = "/common/user_list_data.php";
    // prepare the data
    var source = {
        datatype: "json",
        datafields: [{
                name: 'uno',
                type: 'int'
            },
            {
                name: 'userName',
                type: 'string'
            },
            {
                name: 'dutyName',
                type: 'string'
            },
            {
                name: 'deptPath',
                type: 'string'
            }
        ],
        id: 'jno',
        url: url,
        pager: function(pagenum, pagesize, oldpagenum) {
            // callback called when a page or page size is changed.
        }
    };
    var dataAdapter = new $.jqx.dataAdapter(source);
    $("#userListGrid").jqxGrid({
        width: "740",
        source: dataAdapter,
        sortable: true,
        pageable: true,
        autorowheight: true,
        autoheight: true,
        altrows: true,
        autoloadstate: false,
        autosavestate: false,
        columnsresize: true,
        columnsreorder: true,
        showfilterrow: true,
        filterable: true,
        pagermode: 'simple',
        selectionmode: 'singlerow',
        altrows: true,
        ready: function() {
            
        },
        columns: [{
                text: '부서',
                datafield: 'deptPath'
            },
            {
                text: '성명',
                datafield: 'userName',
                width: 150,
                cellsalign: 'center'
            },
            {
                text: '직급',
                datafield: 'dutyName',
                width: 150,
                cellsalign: 'center'
            }
        ]
    });
    $("#jqxdropdownbutton").jqxDropDownButton({
        width: "100%", height: 30
    });
    $("#userListGrid").on('rowselect', function (event) {
        var args = event.args;
        var row = $("#userListGrid").jqxGrid('getrowdata', args.rowindex);
        var dept = row["deptPath"].split(">");
        var deptName = dept[1];
        var html = '';
        html += '<tr>';
        html += '<td class="text-center">'
        html += '</td>'
        html += '<td class="text-center">'
        html += row["userName"];
        html += '</td>'
        html += '<td class="text-center">'
        html += deptName;
        html += '</td>'
        html += '<td>'
        html += `<div class='jqxWidget'>
                    <div class="dropMenuButton" id="dropBtn_${row["uno"]}">
                        <div style="border: none;" class='jqxTree uno_${row["uno"]}'>
                            <ul>
                                <li item-expanded='true'>Welding
                                    <ul>
                                        <li>WELDING DAY</li>
                                        <li>WELDING MONTH</li>
                                        <li>NDE BY ISO</li>
                                        <li>NDE BY WELDER</li>
                                        <li>PKG LIST</li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>`
        html += '</td>'
        html += '</tr>';

        $("#tblMenuRight tbody").append(html);

        $('#jqxdropdownbutton').jqxDropDownButton('close');
        // 스크롤 조정
        $('.modal-body').scrollTop($('.modal-body')[0].scrollHeight);
        // 권한 체크 드롭다운
        dropDownMenuRight();
        
        // $("#selUnoSV").val(row["uno"]);
        // var dropDownContent = '<div id="selectRow" style="position: relative; margin-left: 3px; margin-top: 6px;">'+ row["userName"] + " " + row["dutyName"] +'</div>';
        // $("#jqxdropdownbutton").jqxDropDownButton('setContent', dropDownContent);
        // $(".selSV").find(".invalid-feedback").html('');
        // $(".selSV").find(".invalid-feedback").hide();
    });
}

// 권한 체크 드롭다운
function dropDownMenuRight() {
    $(".dropMenuButton").jqxDropDownButton({ width: "auto", height: 25 });
    $(".jqxTree").jqxTree({ 
        width: 200,
        height: 220,
        hasThreeStates: true,
        checkboxes: true
    });

    $('.jqxTree').on('checkChange', function (event) {
        menuRightText(this);

        // 권한 저장
        menuRightSave(this);
    });
}

// 권한 라벨 변경
function menuRightText(obj) {
    var item = $(obj).jqxTree('getCheckedItems');
    var labelList = [];
    var classNm = $(obj).attr("class").split(" ");
    var uno = classNm[1].split("_");
    if (item.length == 0) {
        $("#dropBtn_" + uno[1]).jqxDropDownButton('setContent', '');
    } else {
        if(item[0]["parentId"] == 0) {
            $("#dropBtn_" + uno[1]).jqxDropDownButton('setContent', item[0]["label"] + " (ALL)");
        } else {
            for(var i=0; i < item.length; i++) {
                labelList.push(item[i]["label"]);
            }
            var parent = item[0]["parentId"];
            var parentText = $("#" + parent).children(".jqx-tree-item").text();
            var labelText = labelList.join(", ");
            $("#dropBtn_" + uno[1]).jqxDropDownButton('setContent', parentText + " (" + labelList + ")");
        }
    }
}

// 권한 저장
function menuRightSave(obj) {
    var item = $(obj).jqxTree('getCheckedItems');
    var labelList = [];
    var codeList = [];
    var labelText = '';
    var codeText = '';
    var classNm = $(obj).attr("class").split(" ");
    // 권한 부여할 사람
    var uno = classNm[1].split("_");
    uno = uno[1];
    // 프로그램명
    var program = "CM";
    // 등록자
    var writeUno = $("#uno").val();
    var jno = sessionStorage.getItem("jno");

    if (item.length == 0) {
        labelText = 'empty';
        codeText = 'empty';
    } else {
        // all
        if(item[0]["parentId"] == 0) {
            for(var i=1; i < item.length; i++) {
                labelList.push(item[i]["label"]);
                codeList.push(tranRightCode(item[i]["label"]));
            }
        } 
        // part
        else {
            for(var i=0; i < item.length; i++) {
                labelList.push(item[i]["label"]);
                codeList.push(tranRightCode(item[i]["label"]));
            }
        }

        // 빈값일 경우
        if(codeList.length == 0 && labelList.length == 0) {
            labelText = 'empty';
            codeText = 'empty';
        } else {
            labelText = labelList.join(",");
            codeText = codeList.join(",");
        }

    }

    var url = `https://wcfservice.htenc.co.kr/SINGINTEGRATIONSYSTEM/setauth/${writeUno}/${program}/${jno}/${uno}/${codeText}/${labelText}`;

    $.ajax({
        type: "GET",
        url: url,
        dataType: "json",
        success: function(result) {
            console.log(result);
        }
    })
}

// 권한코드 생성
function tranRightCode(label) {
    if(label == "WELDING DAY") {
        return 'w_day';
    } else if (label == "WELDING MONTH") {
        return 'w_month';
    } else if (label == "NDE BY ISO") {
        return 'n_iso';
    } else if (label == "NDE BY WELDER") {
        return 'n_welder';
    } else if (label == "PKG LIST") {
        return 'pkg';
    }
}

// db 권한 사용자 리스트
function getDbRightList() {
    var jno = sessionStorage.getItem("jno");
    var url = `https://wcfservice.htenc.co.kr/SINGINTEGRATIONSYSTEM/getauthall/CM/${jno}`;
    var dbUserList = [];
    $.ajax({
        type: "GET",
        url: url,
        async: false,
        dataType: "json",
        success: function(result) {
            if(result["ResultType"] == "Success") {
                var dbRightList = result["Value"];
                var codeList = [];

                var overUno = ''
                $(dbRightList).each(function(i, data) {
                    if(overUno == data["UNO"]) {
                        codeList.push(data["AUTH_CODE"]);
                    } else {
                        codeList = [];
                        codeList.push(data["AUTH_CODE"]);
                    }
                    var codeText = codeList.join(",");
                    dbUserList[data["UNO"]] = {uno:data["UNO"], userName: data["USER_NAME"], deptName: data["DEPT_NAME"], codeText: codeText};
                    overUno = data["UNO"]
                });
            }
        }
    })
    
    return dbUserList;
}
</script>
<div>
    <div class="modal fade" id="modalRightSetting" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">사용자 권한</h4>
                    <button type="button" class="close btn-close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-5">
                    <div id="userSetting">
                        <div class="row my-5">
                            <div class="col-2 text-center">
                                직원 추가
                            </div>
                            <div class="col-8">
                                <div id="jqxdropdownbutton">
                                    <div style="border-color: transparent;" id="userListGrid">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="px-5">
                            <table class="table table-bordered" id="tblMenuRight">
                                <thead>
                                    <tr>
                                        <th width="5%"></th>
                                        <th width="10%">직원명</th>
                                        <th width="10%">부서명</th>
                                        <th>권한</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="alert alert-success text-center" style="display:none" id="noProject">
                      <strong>PROJECT를 선택하세요.</strong>
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer">
                    <div class="container">
                        <div class="d-flex justify-content-around">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>