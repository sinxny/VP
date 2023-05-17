<style>
.jqx-dropdownbutton-popup {
    border: 1px solid #808080 !important;
}
.jqx-grid-column-header div div{
    text-align: center !important;
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
                var adminList = getAdminList();
                $(oriPersonList).each(function(i, person) {
                    if(!adminList.includes(i)) {
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
                                    html += '<li item-checked="true">WELD(DAY)</li>';
                                } else {
                                    html += '<li>WELD(DAY)</li>';
                                }
                                if(codeList.includes("w_month")) {
                                    html += '<li item-checked="true">WELD(MONTH)</li>';
                                } else {
                                    html += '<li>WELD(MONTH)</li>';
                                }
                                if(codeList.includes("n_iso")) {
                                    html += '<li item-checked="true">NDE(By DWG)</li>';
                                } else {
                                    html += '<li>NDE(By DWG)</li>';
                                }
                                if(codeList.includes("n_welder")) {
                                    html += '<li item-checked="true">NDE(By WELDER)</li>';
                                } else {
                                    html += '<li>NDE(By WELDER)</li>';
                                }
                                if(codeList.includes("pkg")) {
                                    html += '<li item-checked="true">PKG LIST</li>';
                                } else {
                                    html += '<li>PKG LIST</li>';
                                }
                                html += `                    </ul>
                                                        </li>
                                                        <div class="text-right mr-2 py-2">
                                                            <button class="btn btn-sm btn-outline-secondary closeTree">닫기</button>
                                                        </div>
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
                                                        <li item-expanded='true'>Welding
                                                            <ul>
                                                                <li item-checked="true">WELD(DAY)</li>
                                                                <li item-checked="true">WELD(MONTH)</li>
                                                                <li>NDE(By DWG)</li>
                                                                <li>NDE(By WELDER)</li>
                                                                <li>PKG LIST</li>
                                                            </ul>
                                                        </li>
                                                        <div class="text-right mr-2 py-2">
                                                            <button class="btn btn-sm btn-outline-secondary closeTree">닫기</button>
                                                        </div>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>`
                            }
                            html += '</td>'
                            // overUno = person["uno"];
                        }
                    }
                });
                
                // db user
                $(userList).each(function(i, user) {
                    if(!adminList.includes(user)) {
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
                            html += '<li item-checked="true">WELD(DAY)</li>';
                        } else {
                            html += '<li>WELD(DAY)</li>';
                        }
                        if(codeList.includes("w_month")) {
                            html += '<li item-checked="true">WELD(MONTH)</li>';
                        } else {
                            html += '<li>WELD(MONTH)</li>';
                        }
                        if(codeList.includes("n_iso")) {
                            html += '<li item-checked="true">NDE(By DWG)</li>';
                        } else {
                            html += '<li>NDE(By DWG)</li>';
                        }
                        if(codeList.includes("n_welder")) {
                            html += '<li item-checked="true">NDE(By WELDER)</li>';
                        } else {
                            html += '<li>NDE(By WELDER)</li>';
                        }
                        if(codeList.includes("pkg")) {
                            html += '<li item-checked="true">PKG LIST</li>';
                        } else {
                            html += '<li>PKG LIST</li>';
                        }
                        html += `                    </ul>
                                                </li>
                                                <div class="text-right mr-2 py-2">
                                                    <button class="btn btn-sm btn-outline-secondary closeTree">닫기</button>
                                                </div>
                                            </ul>
                                        </div>
                                    </div>
                                </div>`;
                        html += '</td>';
                        html += '</tr>';
                    }
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
    // 헤더 가운데 정렬
    var cellsrenderer = function (row, column, value) {
	    return '<div style="text-align: center">' + value + '</div>';
    }
    // prepare the data
    var source = {
        datatype: "json",
        datafields: [{
                name: 'uno',
                type: 'int',
                renderer: cellsrenderer
            },
            {
                name: 'userName',
                type: 'string',
                renderer: cellsrenderer
            },
            {
                name: 'dutyName',
                type: 'string',
                renderer: cellsrenderer
            },
            {
                name: 'deptPath',
                type: 'string',
                renderer: cellsrenderer
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
        width: "795",
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
        columns: [
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
            },
            {
                text: '부서',
                datafield: 'deptPath'
            }
        ]
    });
    $("#jqxdropdownbutton").jqxDropDownButton({
        width: "98%", height: 30
    });
    $("#userListGrid").on('rowselect', function (event) {
        var userList = [];
        $(".dropMenuButton").each(function() {
            var userId = $(this).attr("id").split("_");
            var uno = userId[1];
            userList.push(uno);
        });
        var args = event.args;
        var row = $("#userListGrid").jqxGrid('getrowdata', args.rowindex);

        // 중복 체크
        if(!userList.includes(row["uno"].toString())) {
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
                                            <li>WELD(DAY)</li>
                                            <li>WELD(MONTH)</li>
                                            <li>NDE(By DWG)</li>
                                            <li>NDE(By WELDER)</li>
                                            <li>PKG LIST</li>
                                        </ul>
                                    </li>
                                    <div class="text-right mr-2 py-2">
                                        <button class="btn btn-sm btn-outline-secondary closeTree">닫기</button>
                                    </div>
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
        }
    });
}

// 권한 체크 드롭다운
function dropDownMenuRight() {
    $(".dropMenuButton").jqxDropDownButton({ 
        width: "auto",
        height: 25,
        dropDownVerticalAlignment: 'bottom'
    });
    // 마지막 드롭다운 위로 펼침
    var lastIndex = $(".dropMenuButton").length - 1;
    if(lastIndex > 3) {
        $(".dropMenuButton").last().jqxDropDownButton({ 
            width: "auto",
            height: 25,
            dropDownVerticalAlignment: 'top'
        }); 
        $(".dropMenuButton").eq(lastIndex - 1).jqxDropDownButton({ 
            width: "auto",
            height: 25,
            dropDownVerticalAlignment: 'top'
        });
        $(".dropMenuButton").eq(lastIndex - 2).jqxDropDownButton({ 
            width: "auto",
            height: 25,
            dropDownVerticalAlignment: 'top'
        }); 
    }
    $(".jqxTree").jqxTree({ 
        width: 200,
        height: "auto",
        hasThreeStates: true,
        checkboxes: true
    });

    $('.jqxTree').on('checkChange', function (event) {
        menuRightText(this);

        // 권한 저장
        menuRightSave(this);
    });
                
    // 트리 닫기 버튼
    $(".closeTree").on('click', function() {
        $(".dropMenuButton").jqxDropDownButton("close");
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
            $("#dropBtn_" + uno[1]).jqxDropDownButton('setContent', parentText + " [" + labelList + "]");
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
    if(label == "WELD(DAY)") {
        return 'w_day';
    } else if (label == "WELD(MONTH)") {
        return 'w_month';
    } else if (label == "NDE(By DWG)") {
        return 'n_iso';
    } else if (label == "NDE(By WELDER)") {
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

// 관리자 리스트
function getAdminList() {
    var url = '/admin/admin_list.php';
    var dbUserList = [];
    $.ajax({
        type: "GET",
        url: url,
        async: false,
        dataType: "json",
        success: function(result) {
            dbUserList = result;
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
                <div class="modal-body p-4">
                    <div id="userSetting">
                        <div class="row pb-4 pl-4" style="align-items: center">
                            <div class="col-3 text-center d-flex align-content-center justify-content-center">
                                직원 추가
                            </div>
                            <div class="col-9 pr-5">
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
                                        <th width="5%">구분</th>
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