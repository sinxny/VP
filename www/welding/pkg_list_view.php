<style>
.tblPkgList th, .tblPkgList td{
    border: 1px solid #A0A0A0 !important;
}
.tblPkgList thead { 
    position: sticky; 
    top: 0; 
    z-index: 1;
}
.tblPkgList .leftFixFirst { 
    position: sticky;
    left: 0;
}
.tblPkgList .leftFixSecond { 
    position: sticky;
}
.tblPkgList .leftFixThird { 
    position: sticky;
}
.mainColor {
    background-color: #004377;
    color: white
}
.punchColor {
    background-color: #0067B4;
    color: white
}
.testColor {
    background-color: #1199FF;
    color: white
}
.babyblue {
    background-color: #E7F3FF;
}
</style>
<script>
var vm = new Vue({
    el: '#app',
    data: {
        pkgDataList: [],
        jno: sessionStorage.getItem("jno"),
        jobName: sessionStorage.getItem("jobName"),
        isDownError: false,
        noData: false,
        init: true
    },
    created() {
        // PKG 데이터 불러오기
        this.getPkgListData();
    },
    methods: {
        // 데이터 가져오기
        getPkgListData() {
            $(".dx-loadpanel-content").removeClass("dx-state-invisible").addClass("dx-state-visible");
            var data = this;
            var jno = data.jno;
            if(jno) {
                var url = "https://wcf.htenc.co.kr/apipwim/getpackage?jno=" + this.jno
                axios.get(url)
                .then(function(response) {
                    var pkgData = response["data"];
                    if(pkgData["ResultType"] == "Success") {
                        data.pkgDataList = pkgData["Value"];
                        if(data.pkgDataList.length > 0) {
                            data.noData = false;
                        } else {
                            data.noData = true;
                        }
                        data.init = false;
                    } else {
                        data.noData = true;
                        data.init = false;
                    }
                })
                .finally(function () {
                    // sticky left값 설정
                    var firstWidth = $(".leftFixFirst").eq(1).outerWidth();
                    $(".leftFixSecond").css("left", firstWidth);
                    var secondWidth = $(".leftFixFirst").eq(1).outerWidth() + $(".leftFixSecond").eq(1).outerWidth();
                    $(".leftFixThird").css("left", secondWidth);

                    $(".dx-loadpanel-content").removeClass("dx-state-visible").addClass("dx-state-invisible");
                });
            }
        },
        // 최신목록 내보내기
        exportPkgExcel() {
            var url = "welding/pkg_list_download_excel.php?jno=" + this.jno + "&jobName=" + this.jobName;
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
            <button type="button" class="btn btn-outline-primary btn-sm text-left ml-3 text-center" style="width:130px;" @click="exportPkgExcel" title="목록 내보내기">
                <i class="fa-solid fa-file-export" style="font-size:large"></i> 목록 내보내기
            </button>
        </span>
    </div>
</div>
<div v-show="!noData && jno && !init">
    <div style="height: 80vh;overflow:auto">
        <table class="table table-bordered table-sm tblPkgList fixHeadColumn">
            <thead style="position: sticky; top:0">
                <tr>
                    <th class="leftFixFirst responsiveTblRow mainColor" rowspan="2">COMPANY</th>
                    <th class="leftFixSecond mainColor" rowspan="2" style="min-width:70px;">NO.</th>
                    <th class="leftFixThird responsiveTblRow mainColor" rowspan="2">PKG. NO.</th>
                    <!-- <th class="responsiveTblRow" rowspan="2">NDE%</th> -->
                    <th class="responsiveTblRow mainColor" colspan="6">MAIN LINE CONDITION</th>
                    <th class="responsiveTblRow punchColor" rowspan="2">Method<br />CLIENT</th>
                    <th class="responsiveTblRow punchColor" rowspan="2">인허가<br />항목</th>
                    <th class="responsiveTblRow punchColor" rowspan="2">TOTAL<br />WELDING<br />D/INCH</th>
                    <th class="responsiveTblRow punchColor" rowspan="2">COMPLETE<br />D/INCH</th>
                    <th class="responsiveTblRow punchColor" rowspan="2">WELDING<br />PROGRESS (%)</th>
                    <th class="responsiveTblRow punchColor" rowspan="2">TOTAL<br />PWHT QTY</th>
                    <th class="responsiveTblRow punchColor" rowspan="2">PWHT<br />ON<br />PROGRESS<br />QTY</th>
                    <th class="responsiveTblRow punchColor" rowspan="2">PWHT<br />COMPLETE<br />QTY</th>
                    <th class="responsiveTblRow punchColor" rowspan="2">Walk Down<br />Ready</th>
                    <th class="responsiveTblRow punchColor" colspan="2">Punch W/D</th>
                    <th class="responsiveTblRow punchColor" rowspan="2">A Punch<br />Clear DATE</th>
                    <th class="responsiveTblRow testColor" colspan="6">TEST DATE</th>
                    <th class="responsiveTblRow testColor" rowspan="2">REMARK</th>
                </tr>
                <tr>
                    <th class="responsiveTblRow mainColor">Fluid</th>
                    <th class="responsiveTblRow mainColor">Line No</th>
                    <!-- <th class="responsiveTblRow">Line Class</th> -->
                    <th class="responsiveTblRow mainColor">Test<br />Fluid</th>
                    <th class="responsiveTblRow mainColor">Operating<br />Pressure</th>
                    <th class="responsiveTblRow mainColor">Design<br />Pressure</th>
                    <th class="responsiveTblRow mainColor">Test<br />Pressure</th>
                    <th class="responsiveTblRow punchColor">SUBCON<br />Walk Down</th>
                    <th class="responsiveTblRow punchColor">HTENC<br />Walk Down</th>
                    <th class="responsiveTblRow testColor">Plan</th>
                    <th class="responsiveTblRow testColor">Request</th>
                    <th class="responsiveTblRow testColor">Actual</th>
                    <th class="responsiveTblRow testColor">B Punch</th>
                    <th class="responsiveTblRow testColor">Flushing</th>
                    <th class="responsiveTblRow testColor">Box-Up</th>
                </tr>
            </thead> 
            <tbody>
                <tr :key="index" v-for="(pkg, index) in pkgDataList">
                    <td class="responsiveTblRow text-center leftFixFirst" style="background-color:white">{{ pkg.COMPANY_NAME }}</td>
                    <td class="text-center leftFixSecond" style="min-width:70px; background-color:white">{{ pkg.NO }}</td>
                    <td class="responsiveTblRow leftFixThird" style="padding-left: 10px !important; background-color:white">{{ pkg.PKG_NO }}</td>
                    <!-- <td class="responsiveTblRow text-center">{{ numberToAccounting(pkg.NDE) }}</td> -->
                    <td class="responsiveTblRow text-center">{{ pkg.FLUID }}</td>
                    <td class="responsiveTblRow" style="padding-left: 10px !important">{{ pkg.LINE_NO }}</td>
                    <!-- <td class="responsiveTblRow text-center">{{ pkg.LINE_CLASS }}</td> -->
                    <td class="responsiveTblRow text-center">{{ pkg.TEST_FLUID }}</td>
                    <td class="responsiveTblRow text-right" style="padding-right: 10px !important">{{ numberToAccounting(pkg.OPERATION_PRESSURE) }}</td>
                    <td class="responsiveTblRow text-right" style="padding-right: 10px !important">{{ numberToAccounting(pkg.DESIGN_PRESSURE) }}</td>
                    <td class="responsiveTblRow text-right" style="padding-right: 10px !important">{{ numberToAccounting(pkg.TEST_PRESSURE) }}</td>
                    <td class="responsiveTblRow text-center">{{ pkg.METHOD_CLIENT }}</td>
                    <td class="responsiveTblRow text-center">{{ pkg.LICENSING }}</td>
                    <td class="responsiveTblRow text-right" style="padding-right: 10px !important">{{ numberToAccounting(pkg.TOTAL_DIA_INCH) }}</td>
                    <td class="responsiveTblRow text-right" style="padding-right: 10px !important">{{ numberToAccounting(pkg.COMPLETE_DIA_INCH) }}</td>
                    <td class="responsiveTblRow text-right" style="padding-right: 10px !important">{{ numberToAccounting(pkg.WELDING_PROGRESS) }}</td>
                    <td class="responsiveTblRow text-right" style="padding-right: 10px !important">{{ numberToAccounting(pkg.TOTAL_PWHT) }}</td>
                    <td class="responsiveTblRow text-right" style="padding-right: 10px !important">{{ numberToAccounting(pkg.PWHT_PROGRESS) }}</td>
                    <td class="responsiveTblRow text-right" style="padding-right: 10px !important">{{ numberToAccounting(pkg.COMPLETE_PWHT) }}</td>
                    <td class="responsiveTblRow text-center">{{ pkg.WALK_DOWN_READY }}</td>
                    <td class="responsiveTblRow text-center">{{ pkg.SUBCON_WALK_DOWN }}</td>
                    <td class="responsiveTblRow text-center">{{ pkg.HTENC_WALK_DOWN }}</td>
                    <td class="responsiveTblRow text-center">{{ pkg.A_PUNCH_CLEAR_DATE }}</td>
                    <td class="responsiveTblRow text-center babyblue">{{ pkg.PLAN }}</td>
                    <td class="responsiveTblRow text-center babyblue">{{ pkg.REQUEST }}</td>
                    <td class="responsiveTblRow text-center babyblue">{{ pkg.ACTUAL }}</td>
                    <td class="responsiveTblRow text-center babyblue">{{ pkg.B_PUNCH }}</td>
                    <td class="responsiveTblRow text-center babyblue">{{ pkg.FLUSHING }}</td>
                    <td class="responsiveTblRow text-center babyblue">{{ pkg.BOX_UP }}</td>
                    <td class="responsiveTblRow babyblue" style="padding-left: 10px !important">{{ pkg.REMARK }}</td>
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