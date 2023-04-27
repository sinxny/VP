<style>
#tblNdeWelder td, #tblNdeWelder th {
    border: 1px solid #A0A0A0;
}
.weldingSum {
    padding-left: 10px !important;
}
.table-welder th {
    background-color:#b8daff;
}
</style>
<script>
var vm = new Vue({
    el: '#app',
    data: {
        icon: '<i class="fa-solid fa-caret-up"></i>',
        collapse : false,
        ndeWelderList : [],
        jno : sessionStorage.getItem("jno"),
        jobName : sessionStorage.getItem("jobName"),
        isDownError: false,
        weldingDate : new Date().toISOString().substring(0, 10),
        noData : false,
        isChangeData : false,
        init: true
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
            if(jno) {
                var url = "https://wcf.htenc.co.kr/apipwim/getndewelder?jno=" + this.jno;
                axios.get(url)
                .then(function(response) {
                    var welder = response["data"];
                    if(welder["ResultType"] == "Success" && welder["Value"].length != 0) {
                        data.ndeWelderList = welder["Value"];
                        data.noData = false;
                        data.init = false;
                    } else {
                        data.noData = true;
                        data.init = false;
                    }
                })
                .finally(function() {
                    $(".dx-loadpanel-content").removeClass("dx-state-visible").addClass("dx-state-invisible");
                })
            }
        },
        // 최신목록 내보내기
        exportWelderExcel() {
            var url = "welding/nde_by_welder_download_excel.php?jno=" + this.jno + "&jobName=" + this.jobName;
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
        // 0 or 공백은 회계형식
        numberToAccounting(num) {
            if(num == 0 || num == '') {
                return "-";
            } else {
                return num;
            }
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
        <span class="d-flex flex-row-reverse" v-show="!noData">
            <!-- <button type="button" class="btn btn-outline-primary btn-sm text-left mr-2 text-center" style="width:130px;" @click="selDocDownload" :disabled="selectList.length == 0" title="선택 다운로드">
                <i class="fa-solid fa-check" style="font-size:large"></i> 선택 다운로드
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm text-left mr-2 text-center" style="width:130px;" @click="allDocDownload" :disabled="latestList.length == 0" title="전체 다운로드">
                <i class="fa-solid fa-floppy-disk" style="font-size:large"></i> 전체 다운로드
            </button> -->
            <button type="button" class="btn btn-outline-primary btn-sm text-left ml-3 text-center" style="width:130px;" @click="exportWelderExcel" title="목록 내보내기">
                <i class="fa-solid fa-file-export" style="font-size:large"></i> 목록 내보내기
            </button>
        </span>
        <!-- <button type="button" class="btn btn-outline-dark btn-sm" v-html="icon" @click="collapseChange"></button> -->
    </div>
</div>
<div v-show="!noData && jno && !init">
    <div style="height: 80vh;overflow:auto">
        <table class="table table-bordered fixHeadColumn" id="tblNdeWelder">
            <thead>
                <tr class="table-welder">
                    <th rowspan="2" width="70px">No.</th>
                    <th rowspan="2" width="300px">WELDER</th>
                    <th rowspan="2" width="100px">RTorPAUT<br />SELECTION</th>
                    <th rowspan="2" width="100px">SHOOT</th>
                    <th rowspan="2" width="100px">BALANCE</th>
                    <th width="100px">RESULT</th>
                    <th rowspan="2" width="100px">REPAIR<br />PROGRESS(%)</th>
                    <th colspan="2">RT</th>
                    <th rowspan="2" width="100px">REPAIR FILM<br />PROGRESS(%)</th>
                    <th rowspan="2">REMARK</th>
                </tr>
                <tr class="table-welder">
                    <th>REPAIR</th>
                    <th width="100px">USED FILM</th>
                    <th width="100px">REPAIR FILM</th>
                </tr>
            </thead>
            <tbody>
                <tr :key="index" v-for="(welder, index) in ndeWelderList">
                    <td class="text-center">{{ index + 1 }}</td>
                    <td style="padding-left:10px !important">{{ welder.WELDER_REG_NO }}</td>
                    <td class="text-right" style="padding-right:10px !important">{{ numberToAccounting(welder.RT_UT_SEL) }}</td>
                    <td class="text-right" style="padding-right:10px !important">{{ numberToAccounting(welder.SHOOT) }}</td>
                    <td class="text-right" style="padding-right:10px !important">{{ numberToAccounting(welder.BALANCE) }}</td>
                    <td class="text-right" style="padding-right:10px !important">{{ numberToAccounting(welder.REPAIR) }}</td>
                    <td class="text-right" style="padding-right:10px !important">{{ numberToAccounting(welder.REPAIR_PROGRESS) }}</td>
                    <td class="text-right" style="padding-right:10px !important">{{ numberToAccounting(welder.USED_FILM) }}</td>
                    <td class="text-right" style="padding-right:10px !important">{{ numberToAccounting(welder.REPAIR_FILM) }}</td>
                    <td class="text-right" style="padding-right:10px !important">{{ numberToAccounting(welder.REPAIR_FILM_PROGRESS) }}</td>
                    <td>{{ welder.Remark }}</td>
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