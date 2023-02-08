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
        noData : false
    },
    created() {
        // 최신문서 데이터 불러오기
        this.getWeldingDayData();

        // 날짜 min/max값 넣기
        dateMinMaxAppend();
    },
    mounted() {
        // thead 고정
        var thWelding = $('#tblWeldingDay').find('thead th')
        $('#tblWeldingDay').closest("div.tableFixHead").on('scroll', function() {
            thWelding.css('transform', 'translateY('+ this.scrollTop +'px)');
        });
    },
    methods: {
        // 데이터 가져오기
        getWeldingDayData() {
        var data = this;
        var jno = data.jno;
        if(jno) {
            var url = "https://wcfservice.htenc.co.kr/apipwim/getweldingtoday?jno="+ jno +"&today=" + this.weldingDate;
            axios.get(url).then(
                function(response) {
                    var welding = response["data"];
                    if(welding["ResultType"] == "Success") {
                        data.weldingDayList = welding["Value"];
                        data.noData = false;
                    } else {
                        data.noData = true;
                    }
                })
                .finally(function () {
                    // 같은 Company 행 병합
                    var rowCompany = [];
                    $(".rowspanCom").each(function() {
                        var textCom = $(this).text();
                        var rows = $(".rowspanCom").filter(function() {
                            return $(this).text() === textCom;
                        })
                        if(rows.length > 1) {
                            rows.eq(0).attr("rowspan", rows.length);
                            rows.not(":eq(0)").remove();
                        }
                        rowCompany.push(textCom);
                    });
                    var company = [...new Set(rowCompany)];
    
                    // 같은 Area 행 병합
                    $(company).each(function(i, com) {
                        $("." + com).each(function() {
                            var textCom = $(this).text();
                            var rows = $("." + com).filter(function() {
                                return $(this).text() === textCom;
                            })
                            if(rows.length > 1) {
                                rows.eq(0).attr("rowspan", rows.length);
                                rows.not(":eq(0)").remove();
                            }
                        })
                    });
                });
            }
        },
        // 최신목록 내보내기
        exportWeldingExcel() {
            var url = "welding/welding_day_download_excel.php?jno=" + this.jno + "&weldingDate=" + this.weldingDate + "&jobName=" + this.jobName;
            this.ajaxDownload(url);
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
        }
    }
})
</script>
<div id="app" style="margin-bottom:30px">
<form id="mainForm" name="mainForm">
<div class="row mb-1" v-show="!noData && jno">
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
            <button type="button" class="btn btn-outline-primary btn-sm text-left ml-3 text-center" style="width:130px;" @click="exportWeldingExcel" title="목록 내보내기">
                <i class="fa-solid fa-file-export" style="font-size:large"></i> 목록 내보내기
            </button>
            <input type="date" class="form-control" style="height:30px" v-model="weldingDate"/>
        </span>
        <!-- <button type="button" class="btn btn-outline-dark btn-sm" v-html="icon" @click="collapseChange"></button> -->
    </div>
</div>
<div v-show="!noData && jno">
    <div class="tableFixHead">
        <table class="table table-bordered" id="tblWeldingDay">
            <thead>
                <tr class="table-primary">
                    <th>Company</th>
                    <th>Area</th>
                    <th>Material Group</th>
                    <th>Total</th>
                    <th>Previous</th>
                    <th>To Day Work</th>
                    <th>Accumulative</th>
                    <th>Remain</th>
                    <th>Work Progress(%)</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                <tr :key="index" v-for="(welding, index) in weldingDayList" :class="{'level3' : (welding.Level) == 3, 'level2' : (welding.Level) == 2 ,'level1' : (welding.Level) == 1, 'level0' : (welding.Level) == '0'}">
                    <td class="rowspanCom text-center" :colspan="(welding.Level == '1') || (welding.Level == '0') ? 3 : 0">{{ welding.Company }}</td>
                    <td :class="[`${welding.Company}`,{'areaColor' : (welding.Level) == ''}]" :colspan="welding.Level == 2 ? 2 : 0" v-if="(welding.Level > 1) || (welding.Level == '')" style="padding-left:10px !important">{{ welding.Area }}</td>
                    <td :class="{'materialGrp' : (welding.Level) == ''}" v-if="(welding.Level > 2) || (welding.Level == '')" style="padding-left:10px !important">{{ welding["Material Group"] }}</td>
                    <td class="text-right" style="padding-right:10px !important">{{ welding.Total }}</td>
                    <td class="text-right" style="padding-right:10px !important">{{ welding.Previous }}</td>
                    <td class="text-right" style="padding-right:10px !important">{{ welding["To Day Work"] }}</td>
                    <td class="text-right" style="padding-right:10px !important">{{ welding.Accumulative }}</td>
                    <td class="text-right" style="padding-right:10px !important">{{ welding.Remain }}</td>
                    <td class="text-right" style="padding-right:10px !important">{{ welding["Work Progress"] }}</td>
                    <td>{{ welding.Remark }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="alert alert-success text-center" v-show="!jno">
  <strong>PROJECT를 선택하세요.</strong>
</div>
<div class="alert alert-warning" v-show="noData">
    <strong>조건에 맞는 결과가 없습니다.</strong>
</div>
<div id="modalLoading" class="modal modal-loading" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <i class="fa fa-spinner fa-pulse fa-3x text-primary"></i>
            <div id="percent" style="padding:1rem;color:white;display:none"></div>
        </div>
    </div>
</div>
</form>
</div>