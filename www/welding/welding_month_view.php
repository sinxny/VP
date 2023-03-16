<style>
.level3 {
    background-color:#FFF2CC;
}
.level2 {
    background-color:#FCE4D6;
}
.level1 {
    background-color:#E6E6FA;
}
.level0 {
    background-color:#F4B084;
}
.materialGrp {
    background-color: #E2EFDA;
}
.areaColor {
    background-color: #A9D08E;
    text-align: center;
}
.weldingSum {
    padding-left: 10px !important;
}
.companyColor {
    background-color: white;
}
.tblWeldingMonth th, .tblWeldingMonth td{
    border: 1px solid #A0A0A0 !important;
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
        today : '',
        nextday : '',
        noData : false,
        isChangeData : false,
        headerDateList : [],
        dateCnt : 0,
        init: true
    },
    created() {
        // 날짜 min/max값 넣기
        dateMinMaxAppend();

        // 31일전 날짜를 기본값으로
        var stDate = new Date();
        var ntDate = new Date(stDate.setDate(stDate.getDate() - 30));
        var year = ntDate.getFullYear();
        var month = String(ntDate.getMonth() + 1);
        month = month.padStart(2, '0');
        var day = String(ntDate.getDate());
        day = day.padStart(2, '0');

        this.today = [year, month, day].join('-');

        var now = new Date();
        year = now.getFullYear();
        month = String(now.getMonth() + 1);
        month = month.padStart(2, '0');
        day = String(now.getDate());
        day = day.padStart(2, '0');

        this.nextday = [year, month, day].join('-');

        // 최신문서 데이터 불러오기
        this.getWeldingMonthData();
    },
    methods: {
        // 데이터 가져오기
        getWeldingMonthData() {
            $(".dx-loadpanel-content").removeClass("dx-state-invisible").addClass("dx-state-visible");
            var data = this;
            var jno = data.jno;
            if(jno) {
                var url = "https://wcf.htenc.co.kr/apipwim/getweldingmonth?jno=" + this.jno + "&today=" + this.today + "&nextday=" + this.nextday;
                axios.get(url)
                .then(function(response) {
                        var welding = response["data"];
                        if(welding["ResultType"] == "Success") {
                            data.weldingDayList = welding["Value"];
                            data.noData = false;
                            
                            // 날짜 헤더
                            var weldingKeys = Object.keys(data.weldingDayList[0]);
                            
                            // 날짜 값 가져오기
                            var regex = RegExp(/^\d{4}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])$/);
                            var dateList = [];
                            $.each(weldingKeys, function(i, value) {
                                if ( regex.test(value) ) {
                                    dateList.push(value);
                                }
                            });
                            data.headerDateList = dateList;
                            data.dateCnt = dateList.length;
                            data.init = false;
                        } else {
                            data.noData = true;
                            data.init = false;
                        }
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
                            rows.not(":eq(0)").remove();
                        }
                    });

                    // 같은 Area 행 병합
                    var sameCnt = 1;
                    var criteria = '';
                    var area = '';
                    var removeObj = [];
                    $(".rowspanArea").each(function(i, obj) {
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

                    // sticky left값 설정
                    $(".fixLeftFirst").css("left", 0);
                    var leftArray = {
                        1: "fixLeftFirst",
                        2: "fixLeftSecond",
                        3: "fixLeftThird",
                        4: "fixLeftFourth",
                        5: "fixLeftFiveth"
                    }

                    var width = 0;
                    for(var i=1; i < Object.keys(leftArray).length; i++) {
                        width += $("." + leftArray[i]).eq(1).outerWidth();
                        $("." + leftArray[i+1]).css("left", width);
                    }

                    // sticky right값 설정
                    $(".fixRightFirst").css("right", 0);
                    var RightArray = {
                        1: "fixRightFirst",
                        2: "fixRightSecond",
                        3: "fixRightThird",
                        4: "fixRightFourth"
                        // 5: "fixLeftFiveth"
                    }

                    width = 0;
                    for(var i=1; i < Object.keys(RightArray).length; i++) {
                        width += $("." + RightArray[i]).eq(1).outerWidth();
                        $("." + RightArray[i+1]).css("right", width);
                    }

                    $(".dx-loadpanel-content").removeClass("dx-state-visible").addClass("dx-state-invisible");
                });
            }
        },
        // 최신목록 내보내기
        exportWeldingExcel() {
            // this.weldingDateChange();
            var url = "welding/welding_month_download_excel.php?jno=" + this.jno + "&today=" + this.today + "&jobName=" + this.jobName + "&nextday=" + this.nextday;
            this.axiosDownload(url, "GET");
        },
        // 쿠키 삭제
        deleteCookie(name) {
            document.cookie = name + '=; expires=Thu, 01 Jan 1999 00:00:10 GMT;';
        },
        // axios 다운로드
        axiosDownload(url, method) {
            $("#modalLoading").modal("show");
            $(".dx-loadpanel-content").removeClass("dx-state-invisible").addClass("dx-state-visible");
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
                $(".dx-loadpanel-content").removeClass("dx-state-visible").addClass("dx-state-invisible");
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
            if ( !regex.test(this.today) || !regex.test(this.nextday)) {
                alert("날짜 값이 잘못되었습니다.");
            } else if (this.today > this.nextday) {
                var temp = this.today;
                this.today = this.nextday;
                this.nextday = temp;

                this.getWeldingMonthData();
            } else {
                this.getWeldingMonthData();
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
<div class="row mb-1" v-show="!noData && jno && !init">
    <!-- <div class="col-md-1">
        <i class="fa-solid fa-magnifying-glass"></i> <b style="font-size:large">Search</b>
    </div> -->
    <div class="col-md text-right">
        <span class="d-flex flex-row-reverse align-items-center" v-show="!noData">
            <!-- <button type="button" class="btn btn-outline-primary btn-sm text-left mr-2 text-center" style="width:130px;" @click="selDocDownload" :disabled="selectList.length == 0" title="선택 다운로드">
                <i class="fa-solid fa-check" style="font-size:large"></i> 선택 다운로드
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm text-left mr-2 text-center" style="width:130px;" @click="allDocDownload" :disabled="latestList.length == 0" title="전체 다운로드">
                <i class="fa-solid fa-floppy-disk" style="font-size:large"></i> 전체 다운로드
            </button> -->
            <button type="button" class="btn btn-outline-primary btn-sm text-left ml-3 text-center" style="width:130px;" @click="exportWeldingExcel" title="목록 내보내기">
                <i class="fa-solid fa-file-export" style="font-size:large"></i> 목록 내보내기
            </button>
            <input type="date" class="form-control ml-2" style="height:30px" v-model="nextday" @change="weldingDateChange" @keydown="dateBanKey($event)"/> ~
            <input type="date" class="form-control mr-2" style="height:30px" v-model="today" @change="weldingDateChange" @keydown="dateBanKey($event)"/>
        </span>
        <!-- <button type="button" class="btn btn-outline-dark btn-sm" v-html="icon" @click="collapseChange"></button> -->
    </div>
</div>
<div v-show="!noData && jno && !init">
    <div style="height: 80vh;overflow:auto">
        <table class="table table-bordered table-sm tblWeldingMonth fixHeadColumn">
            <thead>
                <tr class="table-primary">
                    <th class="responsiveTblRow fixLeft fixLeftFirst" rowspan="2">Company</th>
                    <th class="responsiveTblRow fixLeft fixLeftSecond" rowspan="2">Area</th>
                    <th class="responsiveTblRow fixLeft fixLeftThird" rowspan="2">Material Group</th>
                    <th class="responsiveTblRow fixLeft fixLeftFourth" rowspan="2">Total</th>
                    <th class="responsiveTblRow fixLeft fixLeftFiveth" rowspan="2">Previous</th>
                    <th :colspan="dateCnt">Work Dia-inch for Monthly</th>
                    <th rowspan="2" class="responsiveTblRow fixRight fixRightFourth">Accumulative</th>
                    <th rowspan="2" class="responsiveTblRow fixRight fixRightThird">Remain</th>
                    <th rowspan="2" class="responsiveTblRow fixRight fixRightSecond">Work Progress(%)</th>
                    <th rowspan="2" class="responsiveTblRow fixRight fixRightFirst">Remark</th>
                </tr>
                <tr class="table-primary">
                    <th :key="index" v-for="(date, index) in headerDateList" class="responsiveTblRow text-center" style="font-weight:normal !important">{{ date }}</th>
                </tr>
            </thead> 
            <tbody>
                <tr :key="index" v-for="(welding, index) in weldingDayList" :class="{'level3' : (welding.Level) == 3, 'level2' : (welding.Level) == 2 ,'level1' : (welding.Level) == 1, 'level0' : (welding.Level) == '0'}">
                    <td :class="['rowspanCom', 'text-center', 'fixLeft', 'fixLeftFirst', 'responsiveTblRow', {'companyColor': (welding.Level) == ''}, {'level1': (welding.Level) == 1}, {'level0': (welding.Level) == '0'}]" :colspan="(welding.Level == '1') || (welding.Level == '0') ? 3 : 0">{{ welding.Company }}</td>
                    <td :class="['rowspanArea', {'areaColor' : (welding.Level) == ''}, {'level2' : (welding.Level) == 2}, {'weldingSum' : (welding.Level) == 2}, 'fixLeft', 'fixLeftSecond', 'responsiveTblRow']" :colspan="welding.Level == 2 ? 2 : 0" v-if="(welding.Level > 1) || (welding.Level == '')">{{ welding.Area }}</td>
                    <td :class="[{'materialGrp' : (welding.Level) == ''},{'level3' : (welding.Level) == 3}, 'fixLeft', 'fixLeftThird', 'responsiveTblRow']" v-if="(welding.Level > 2) || (welding.Level == '')" style="padding-left:10px !important">{{ welding["Material Group"] }}</td>
                    <td :class="['text-right', 'responsiveTblRow', 'fixLeft', 'fixLeftFourth', {'level3' : (welding.Level) == 3}, {'level2' : (welding.Level) == 2}, {'level1': (welding.Level) == 1}, {'level0': (welding.Level) == '0'}, {'companyColor': (welding.Level) == ''}]" style="padding-right:10px !important">{{ numberToAccounting(welding.Total) }}</td> 
                    <td :class="['text-right', 'responsiveTblRow', 'fixLeft', 'fixLeftFiveth', {'level3' : (welding.Level) == 3}, {'level2' : (welding.Level) == 2}, {'level1': (welding.Level) == 1}, {'level0': (welding.Level) == '0'}, {'companyColor': (welding.Level) == ''}]" style="padding-right:10px !important">{{ numberToAccounting(welding.Previous) }}</td> 
                    <td :key="index" v-for="(date, index) in headerDateList" class="text-right responsiveTblRow" style="padding-right:10px !important"> {{ numberToAccounting(welding[date]) }} </td>
                    <td :class="['text-right', 'responsiveTblRow', 'fixRight', 'fixRightFourth', {'level3' : (welding.Level) == 3}, {'level2' : (welding.Level) == 2}, {'level1': (welding.Level) == 1}, {'level0': (welding.Level) == '0'}, {'companyColor': (welding.Level) == ''}]" style="padding-right:10px !important">{{ numberToAccounting(welding.Accumulative) }}</td> 
                    <td :class="['text-right', 'responsiveTblRow', 'fixRight', 'fixRightThird', {'level3' : (welding.Level) == 3}, {'level2' : (welding.Level) == 2}, {'level1': (welding.Level) == 1}, {'level0': (welding.Level) == '0'}, {'companyColor': (welding.Level) == ''}]" style="padding-right:10px !important">{{ numberToAccounting(welding.Remain) }}</td> 
                    <td :class="['text-right', 'responsiveTblRow', 'fixRight', 'fixRightSecond', {'level3' : (welding.Level) == 3}, {'level2' : (welding.Level) == 2}, {'level1': (welding.Level) == 1}, {'level0': (welding.Level) == '0'}, {'companyColor': (welding.Level) == ''}]" style="padding-right:10px !important">{{ numberToAccounting(welding["Work Progress"]) }}</td> 
                    <td :class="['responsiveTblRow', 'fixRight', 'fixRightFirst', {'level3' : (welding.Level) == 3}, {'level2' : (welding.Level) == 2}, {'level1': (welding.Level) == 1}, {'level0': (welding.Level) == '0'}, {'companyColor': (welding.Level) == ''}]">{{ welding.Remark }}</td>
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