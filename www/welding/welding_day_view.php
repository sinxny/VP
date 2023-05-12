<style>
.step3 {
    background-color:#FFF2CC;
}
.step2 {
    background-color:#FCE4D6;
}
.step1 {
    background-color:#E6E6FA;
}
.step0 {
    background-color:#F4B084;
}
.materialGrp {
    /* background-color: #E2EFDA; */
}
.areaColor {
    /* background-color: #A9D08E; */
    text-align: center;
}
#tblWeldingDay td, #tblWeldingDay th {
    border: 1px solid #A0A0A0;
}
.weldingSum {
    padding-left: 10px !important;
}
</style>
<script>
var vm = new Vue({
    el: '#app',
    data: {
        icon: '<i class="fa-solid fa-caret-up"></i>',
        collapse : false,
        weldingDayList : [],
        jno : sessionStorage.getItem("jno"),
        jobName : sessionStorage.getItem("jobName"),
        isDownError: false,
        weldingDate : new Date().toISOString().substring(0, 10),
        noData : true,
        isChangeData : false,
        init: true,
        selectGrp: 'Area',
        grpTitle: ''
    },
    created() {
        // 최신문서 데이터 불러오기
        this.getWeldingDayData();

        // 날짜 min/max값 넣기
        dateMinMaxAppend();
    },
    methods: {
        // 데이터 가져오기
        getWeldingDayData() {
            $(".dx-loadpanel-content").removeClass("dx-state-invisible").addClass("dx-state-visible");
            var data = this;
            var jno = data.jno;

            data.init = true;

            // 칼럼명 변경
            if(this.selectGrp == "Area") {
                this.grpTitle = "구역";
            } else if(this.selectGrp == "Unit") {
                this.grpTitle = "경계";
            } else if(this.selectGrp == "Level") {
                this.grpTitle = "위치";
            }
            if(jno) {
                var url = "https://wcf.htenc.co.kr/apipwim/getweldingtoday?jno="+ jno +"&today=" + this.weldingDate + "&group=" + this.selectGrp;
                axios.get(url).then(
                    function(response) {
                        $("td").show();
                        $(".rowspanCom").attr("rowspan", '');
                        var welding = response["data"];
                        if(welding["ResultType"] == "Success") {
                            data.weldingDayList = welding["Value"];
                            data.noData = false;
                        } else {
                            data.noData = true;
                        }
                        data.init = false;

                    })
                    .finally(function () {
                        // 같은 Company 행 병합
                        $(".rowspanCom").each(function() {
                            var textCom = $(this).text();
                            var rows = $(".rowspanCom").filter(function() {
                                return $(this).text() === textCom;
                            })
                            if(rows.length > 1) {
                                rows.eq(0).attr("rowspan", rows.length);
                                rows.not(":eq(0)").hide();
                            }
                        });

                        // 같은 Area 행 병합
                        var sameCnt = 1;
                        var criteria = '';
                        var area = '';
                        var removeObj = [];
                        $(".rowspanArea").each(function(i, obj) {
                            var classIndex = $(obj).attr("class").search("step_");
                            var step = $(obj).attr("class").substr(classIndex, 6).split("_");
                            if((step[1] > 1) || (step[1] == '')) {
                                $(obj).show();
                            } else {
                                $(obj).hide();
                            }

                            if(area == $(obj).text()) {
                                sameCnt++;
                            } else {
                                $(".rowspanArea").eq(criteria).attr("rowspan", sameCnt);
                                for(var j = 1; j <= sameCnt - 1; j++) {
                                    removeObj.push(criteria + j);
                                }
                                sameCnt = 1;
                                criteria = i;
                            }
                            area = $(obj).text();
                        });

                        $.each(removeObj.reverse(), function(i, num) {
                            $(".rowspanArea").eq(num).hide();
                        });

                        // Material Grp
                        $(".materialStep").each(function(i, obj) {
                            var classIndex = $(obj).attr("class").search("step_");
                            var step = $(obj).attr("class").substr(classIndex, 6).split("_");

                            var step = $(obj).attr("class").substr(classIndex, 6).split("_");
                            if((step[1] > 2) || (step[1] == '')){
                                $(obj).show();
                            } else {
                                $(obj).hide();
                            }
                        });

                        $(".dx-loadpanel-content").removeClass("dx-state-visible").addClass("dx-state-invisible");
                    });
            }
        },
        // 최신목록 내보내기
        exportWeldingExcel() {
            this.weldingDateChange();
            var url = "welding/welding_day_download_excel.php?jno=" + this.jno + "&weldingDate=" + this.weldingDate + "&jobName=" + this.jobName + "&group=" + this.selectGrp;
            this.axiosDownload(url, "GET");
        },
        // 쿠키 삭제
        deleteCookie(name) {
            document.cookie = name + '=; expires=Thu, 01 Jan 1999 00:00:10 GMT;';
        },
        // axios 다운로드
        axiosDownload(url, method) {
            $("#modalLoading").modal("show");
            axios({
                url: url,
                method: method,
                responseType: "blob" // 응답 데이터 타입 정의
            })
            .then(function(response) {
                // 다운로드 파일 이름을 추출하는 함수
                const extractDownloadFilename = (response) => {
                    const disposition = response.headers["content-disposition"];
                    const fileName = decodeURI(
                    disposition
                        .match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/)[1]
                        .replace(/['"]/g, "")
                    );
                    return fileName;
                };
                const blob = new Blob([response.data]);
                const fileObjectUrl = window.URL.createObjectURL(blob);

                const link = document.createElement("a");
                link.href = fileObjectUrl;
                link.style.display = "none";
                link.download = extractDownloadFilename(response);

                // 다운로드 파일의 이름은 직접 지정 할 수 있습니다.
                // link.download = "sample-file.xlsx";

                // 링크를 body에 추가하고 강제로 click 이벤트를 발생시켜 파일 다운로드를 실행시킵니다.
                document.body.appendChild(link);
                link.click();
                link.remove();

                // 다운로드가 끝난 리소스(객체 URL)를 해제합니다.
                window.URL.revokeObjectURL(fileObjectUrl);
            })
            .catch(function(error){
                console.log(error);
            })
            .finally(function() {
                $("#modalLoading").modal("hide");
            });
        },
        // percenatage
        showPer(per) {
            $("#percent").text(per + "%");
            $("#percent").show();
        },
        // 파일 다운로드
        ajaxDownload(url) {
            var data = this;
            var downInfo = $.ajax({
                url: url,
                type : 'GET',
                xhrFields: {  //response 데이터를 바이너리로 처리한다.
                responseType: 'blob'
                },
                beforeSend: function() {
                    $("#modalLoading").modal("show");
                    data.showPer(0);
                },
                xhr: function() {  //XMLHttpRequest 재정의 가능
                    var xhr = $.ajaxSettings.xhr();
                    xhr.onprogress = function(e) {
                        data.showPer(Math.floor(e.loaded / e.total * 100));
                    };
                    return xhr;
                },  
                success : function(response) {
                    // 다운로드 파일 이름을 추출하는 함수
                    const extractDownloadFilename = (response) => {
                        const disposition = downInfo.getResponseHeader('Content-Disposition');
                        const fileName = decodeURI(
                        disposition
                            .match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/)[1]
                            .replace(/['"]/g, "")
                        );
                        return fileName;
                    };
                    const blob = new Blob([response]);
                    const fileObjectUrl = window.URL.createObjectURL(blob);

                    const link = document.createElement("a");
                    link.href = fileObjectUrl;
                    link.style.display = "none";
                    link.download = extractDownloadFilename(response);

                    // 다운로드 파일의 이름은 직접 지정 할 수 있습니다.
                    // link.download = "sample-file.xlsx";

                    // 링크를 body에 추가하고 강제로 click 이벤트를 발생시켜 파일 다운로드를 실행시킵니다.
                    document.body.appendChild(link);
                    link.click();
                    link.remove();

                    // 다운로드가 끝난 리소스(객체 URL)를 해제합니다.
                    window.URL.revokeObjectURL(fileObjectUrl);
                },
                complete: function() {
                    if(data.isDownError) {
                        data.isDownError = false;
                    } else {
                        $("#modalLoading").modal("hide");
                        $("#percent").hide();
                    }
                },
                error:function(request,status,error){
                    data.isDownError = true;
                    data.ajaxDownload(url);
                }
            });
        },
        // 날짜 데이터 변경
        weldingDateChange() {
            var regex = RegExp(/^\d{4}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])$/);
            if ( !regex.test(this.weldingDate) ) {
                alert("날짜 값이 잘못되었습니다.");
            } else {
                this.getWeldingDayData();
            }
        },
        // 0 or 공백은 회계형식
        numberToAccounting(num) {
            if(num == 0 || num == '') {
                return "-";
            } else {
                return num;
            }
        },
        // 날짜 키보드 입력 제한
        dateBanKey(event) {
            event.preventDefault();
        }
    }
})
</script>
<div id="app" style="margin-bottom:30px">
<form id="mainForm" name="mainForm">
<div class="row mb-1" v-show="!noData && jno">
    <div class="col-md-1">
        <select class="form-control" style="height:30px" v-model="selectGrp" @change="getWeldingDayData">
            <option value="Area">Area</option>
            <option value="Unit">Unit</option>
            <option value="Level">Level</option>
        </select>
    </div>
    <div class="col-md text-right">
        <span class="d-flex flex-row-reverse" v-show="!noData">
            <button type="button" class="btn btn-outline-primary btn-sm text-left ml-3 text-center" style="width:130px;" @click="exportWeldingExcel" title="목록 내보내기">
                <i class="fa-solid fa-file-export" style="font-size:large"></i> 목록 내보내기
            </button>
            <input type="date" class="form-control" style="height:30px" v-model="weldingDate" @change="weldingDateChange" @keydown="dateBanKey($event)"/>
        </span>
    </div>
</div>
<div v-show="!noData && jno">
    <div style="height: 80vh;overflow:auto">
        <table class="table table-bordered fixHeadColumn" id="tblWeldingDay">
            <thead>
                <tr class="table-primary">
                    <th style="width:8%">업체명<br />Company</th>
                    <th style="width:8%">{{ grpTitle }}<br />{{ selectGrp }}</th>
                    <th style="width:8%">재질<br />Material Group</th>
                    <th style="width:9%">총 물량 (D/I)<br />Total</th>
                    <th style="width:9%">누계 (D/I)<br />Previous</th>
                    <th style="width:9%">금일 물량 (D/I)<br />To Day Work</th>
                    <th style="width:9%">합 계 (D/I)<br />Accumulative</th>
                    <th style="width:9%">잔여물량 (D/I)<br />Remain</th>
                    <th style="width:9%">진행률 (%)<br />Work Progress</th>
                    <th>비고<br />Remark</th>
                </tr>
            </thead>
            <tbody>
                <tr :key="index" v-for="(welding, index) in weldingDayList" :class="{'step3' : (welding.Step) == 3, 'step2' : (welding.Step) == 2 ,'step1' : (welding.Step) == 1, 'step0' : (welding.Step) == '0'}">
                    <td class="rowspanCom text-center" :colspan="(welding.Step == '1') || (welding.Step == '0') ? 3 : 0">
                        <span v-show="(welding.Step) == '0'">종합 물량 합계_</span>{{ welding.Company }}
                    </td>
                    <td :class="['rowspanArea' ,{'areaColor' : (welding.Step) == ''},{'weldingSum' : (welding.Step) == 2}, `step_${welding.Step}`]" :colspan="welding.Step == 2 ? 2 : 0">
                        <div v-show="(welding.Step) == 2 && !init">
                            {{ welding[selectGrp] == "Welding Sum" ? "용접 합계_" + welding[selectGrp] : "비용접 합계_" + welding[selectGrp] }}
                        </div>
                        <div v-show="(welding.Step) == ''">
                            {{ welding[selectGrp] }}
                        </div>
                    </td>
                    <td :class="[{'materialGrp' : (welding.Step) == ''}, 'materialStep', `step_${welding.Step}`]" style="padding-left:10px !important">{{ welding["Material Group"] }}</td>
                    <td class="text-right" style="padding-right:10px !important">{{ numberToAccounting(welding.Total) }}</td>
                    <td class="text-right" style="padding-right:10px !important">{{ numberToAccounting(welding.Previous) }}</td>
                    <td class="text-right" style="padding-right:10px !important">{{ numberToAccounting(welding["To Day Work"]) }}</td>
                    <td class="text-right" style="padding-right:10px !important">{{ numberToAccounting(welding.Accumulative) }}</td>
                    <td class="text-right" style="padding-right:10px !important">{{ numberToAccounting(welding.Remain) }}</td>
                    <td class="text-right" style="padding-right:10px !important">{{ numberToAccounting(welding["Work Progress"]) }}</td>
                    <td>{{ welding.Remark }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="alert alert-success text-center" v-show="!jno">
  <strong>PROJECT를 선택하세요.</strong>
</div>
<div class="alert alert-warning" v-show="noData && !init">
    <strong>조건에 맞는 결과가 없습니다.</strong>
</div>
<div id="modalLoading" class="modal modal-loading" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <!-- <i class="fa fa-spinner fa-pulse fa-3x text-primary"></i> -->
            <!-- <div id="percent" style="padding:1rem;color:white;display:none"></div> -->
        </div>
    </div>
</div>
<div class="dx-overlay-content dx-loadpanel-content dx-state-visible" style="width: 200px; height: 90px; z-index: 1501; left: 50%; top: 50%;" v-show="jno">
    <div class="dx-loadpanel-content-wrapper">
        <div class="dx-loadpanel-indicator dx-loadindicator dx-widget">
            <div class="dx-loadindicator-wrapper">
                <div class="dx-loadindicator-content">
                    <div class="dx-loadindicator-icon">
                        <div class="dx-loadindicator-segment dx-loadindicator-segment7"></div>
                        <div class="dx-loadindicator-segment dx-loadindicator-segment6"></div>
                        <div class="dx-loadindicator-segment dx-loadindicator-segment5"></div>
                        <div class="dx-loadindicator-segment dx-loadindicator-segment4"></div>
                        <div class="dx-loadindicator-segment dx-loadindicator-segment3"></div>
                        <div class="dx-loadindicator-segment dx-loadindicator-segment2"></div>
                        <div class="dx-loadindicator-segment dx-loadindicator-segment1"></div>
                        <div class="dx-loadindicator-segment dx-loadindicator-segment0"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="dx-loadpanel-message">Loading...</div>
    </div>
</div>
</form>
</div>