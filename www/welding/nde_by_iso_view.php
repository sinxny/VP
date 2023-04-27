<style>
    #gridContainer {
        height: 83.4vh;
        font-family: Arial,"Malgun Gothic",sans-serif !important; /*,"AppleGothicNeoSD"*/
    }
    .dx-header-row .dx-cell-focus-disabled {
        text-align:center !important;
        vertical-align: middle !important;
        background-color: #b8daff;
        color: black;
        font-weight: bold;
    }
    .dx-datagrid-search-panel {
        height: 30px;
    }
    .dx-datagrid-nowrap .dx-header-row>td>.dx-datagrid-text-content {
        white-space: normal !important;
    }
    .dx-datagrid-header-panel {
        height: 35px
    }
    .dx-column-indicators {
        float: right !important;
    }
    .dx-datagrid-text-content {
        font-size: 11px !important;
    }
    .dx-datagrid-table th, .dx-datagrid-table td {
        border: 2px solid #A0A0A0 !important;
    }
    .dx-datagrid-headers {
        border : none !important;
    }.dx-datagrid-rowsview {
        border : none !important;
    }
    .dx-pages {
        left: -40%;
        float: right;
        position: relative;
    }
</style>
<script>
    var vm = new Vue({
        el: '#app',
        data: {
            jno: sessionStorage.getItem("jno"),
            jobName: sessionStorage.getItem("jobName"),
            isDownError: false,
            noData: false,
            init: true
        },
        created() {
            // NDE BY ISO 데이터 불러오기
            this.getNdeIsoData();
        },
        methods: {
            // 데이터 가져오기
            getNdeIsoData() {
                // 데이터 여부 확인
                var data = this;
                $(".dx-loadpanel-content").removeClass("dx-state-invisible").addClass("dx-state-visible");
                axios({
                    url: 'welding/nde_by_iso_data.php?jno=' + this.jno,
                    method: "GET"
                })
                .then(function(response) {
                    if(response["data"] == null || response["data"].length == 0) {
                        data.noData = true;
                        data.init = false;
                    } else {
                        data.noData = false;
                        data.init = false;
                    }
                })
                .finally(function() {
                    // 통합 검색창 삭제
                    $(".dx-toolbar-after .dx-datagrid-search-panel").remove();
                    // 로딩창 숨기기
                    $(".dx-loadpanel-content").removeClass("dx-state-visible").addClass("dx-state-invisible");
                })

                if(this.noData == false) {
                    var pageSize = 18;
                    $(() => {
                        const dataGrid = $('#gridContainer').dxDataGrid({
                            dataSource: 'welding/nde_by_iso_data.php?jno=' + this.jno,
                            showRowLines: true,
                            showColumnLines: true,
                            columns: [
                                {
                                    dataField:'NO',
                                    caption: 'NO.',
                                    width: 70,
                                    alignment: "center"
                                },
                                {
                                    dataField: 'DRW_NO',
                                    caption: 'ISO DWG NO.'
                                },
                                {
                                    dataField: 'PKG_NO',
                                    caption: 'PKG NO.'
                                },
                                {
                                    dataField: 'NDE_RATE',
                                    caption: 'NDE RATE(%)',
                                    width: 87.5,
                                    dataType: 'number'
                                },
                                {
                                    caption: 'DWG WELD TOTAL JOINT',
                                    columns: [
                                        {
                                            dataField: 'BW',
                                            caption: 'BW',
                                            width: 87.5
                                        },
                                        {
                                            dataField: 'SW',
                                            caption: 'SW',
                                            width: 87.5
                                        }
                                    ]
                                },
                                {
                                    dataField: 'TARGET_JOINT',
                                    caption: 'TARGET JOINT',
                                    width: 80
                                },
                                {
                                    caption: 'SELECTION',
                                    columns: [
                                        {
                                            dataField: 'RT',
                                            caption: 'RT',
                                            width: 87.5
                                        },
                                        {
                                            dataField: 'UT',
                                            caption: 'PAUT',
                                            width: 87.5
                                        },
                                        {
                                            dataField: 'MT',
                                            caption: 'MT',
                                            width: 87.5
                                        },
                                        {
                                            dataField: 'PT',
                                            caption: 'PT',
                                            width: 87.5
                                        }
                                    ]
                                },
                                {
                                    dataField: 'REPORT_JOINT',
                                    caption: 'REPORT JOINT',
                                    width: 87.5
                                },
                                {
                                    dataField: 'BALANCE',
                                    caption: 'BALANCE',
                                    width: 87.5
                                },
                                {
                                    dataField: 'PROGRESS',
                                    caption: 'PROGRESS(%)',
                                    width: 115
                                },
                                {
                                    dataField: 'REMARK',
                                    caption: 'REMARK'
                                }
                            ],
                            // keyExpr: 'ID',
                            columnsAutoWidth: true,
                            showBorders: true,
                            filterRow: {
                                visible: true,
                                applyFilter: 'auto'
                            },
                            searchPanel: {
                                visible: true,
                                width: 240,
                                placeholder: 'Search...'
                            },
                            headerFilter: {
                                visible: true
                            },
                            paging: {
                                pageSize: pageSize,
                            },
                            pager: {
                                showNavigationButtons: true
                            },
                            onCellPrepared(e) {
                                const dataGrid = $("#gridContainer").dxDataGrid("instance");
                                const dataSource = dataGrid.getDataSource();
                                const recordCount = dataSource.items().length;

                                // 헤더 스타일
                                if (e.rowType == "header" && e.column.caption == "RT") {
                                    $(e.cellElement.get(0)).attr("style", "border-left: 3px solid red !important;border-top : 3px solid red !important;")
                                } else if(e.rowType == "header" && (e.column.caption == "PAUT" || e.column.caption == "MT")) {
                                    $(e.cellElement.get(0)).attr("style", "border-top : 3px solid red !important;")
                                } else if (e.rowType == "header" && e.column.caption == "PT") {
                                    $(e.cellElement.get(0)).attr("style", "border-right: 3px solid red !important;border-top : 3px solid red !important;")
                                }
                                // RT
                                if(e.columnIndex == 7) {
                                    if(e.rowType == "filter") {
                                        $(e.cellElement.get(0)).closest(".dx-editor-cell").attr("style", "border-left: 3px solid red !important");
                                    } 
                                    else if (e.rowType != "header"){
                                        $(e.cellElement.get(0)).attr("style", "border-left: 3px solid red !important; text-align: right")
                                        if(e.rowIndex == pageSize - 1 || e.rowIndex == recordCount - 1) {
                                            $(e.cellElement.get(0)).attr("style", "border-left: 3px solid red !important; border-bottom: 3px solid red !important; text-align: right")
                                        }
                                    }
                                }
                                // PAUT, MT 
                                else if(e.rowType != "filter" && (e.columnIndex == 8 || e.columnIndex == 9)) {
                                    if(e.rowIndex == pageSize - 1 || e.rowIndex == recordCount - 1) {
                                        $(e.cellElement.get(0)).attr("style", "border-bottom: 3px solid red !important; text-align: right")
                                    }
                                } 
                                // PT
                                else if(e.columnIndex == 10) {
                                    if(e.rowType == "filter") {
                                        $(e.cellElement.get(0)).closest(".dx-editor-cell").attr("style", "border-right: 3px solid red !important")
                                    } 
                                    else if (e.rowType != "header"){
                                        $(e.cellElement.get(0)).attr("style", "border-right: 3px solid red !important; text-align: right")
                                        if(e.rowIndex == pageSize - 1 || e.rowIndex == recordCount - 1) {
                                            $(e.cellElement.get(0)).attr("style", "border-right: 3px solid red !important; border-bottom: 3px solid red !important; text-align: right")
                                        }
                                    }
                                }

                                // 돋보기 색깔 넣기
                                $(e.cellElement.get(0)).find("input").on("keyup", function() {
                                    var isValue = $(this).next("div").hasClass("dx-state-invisible");
                                    if(isValue) {
                                        $(this).closest("td").find("i").attr("style", "color: red !important");
                                    } else {
                                        $(this).closest("td").find("i").attr("style", "");
                                    }
                                });

                                $(".dx-texteditor-input").each(function() {
                                    var isValue = $(this).next("div").hasClass("dx-state-invisible");
                                    if(isValue) {
                                        $(this).closest("td").find("i").attr("style", "color: red !important");
                                    } else {
                                        $(this).closest("td").find("i").attr("style", "");
                                    }
                                });
                            }
                        }).dxDataGrid('instance');
    
                        // 목록 내보내기 버튼 클릭
                        var btnExcel = `<button type="button" class="btn btn-outline-primary btn-sm text-left ml-3 mt-1" style="width:130px;" id="btnExportExcel" title="목록 내보내기">
                                            <i class="fa-solid fa-file-export" style="font-size:large"></i> 목록 내보내기
                                        </button>`;
                        $(".dx-toolbar-after").append(btnExcel);
                        $("#btnExportExcel").on('click', this.exportNdeExcel);

                        // 필터 clear 버튼
                        var btnNoFilter = `<button type="button" class="btn btn-outline-primary btn-sm text-left mt-1" style="width:auto;" id="btnCancelFilter" title="목록 내보내기">
                                                <i class="fa-solid fa-filter-circle-xmark"></i> 필터 초기화
                                            </button>`;
                        $(".dx-toolbar-before").append(btnNoFilter);
                        $("#btnCancelFilter").on('click', function() {
                            dataGrid.clearFilter();
                            $(".dx-datagrid-filter-row").find("i").attr("style", "");
                        });
                    });
                }
            },
            // 목록 내보내기 버튼 클릭
            exportNdeExcel() {
                var url = "welding/nde_by_iso_download_excel.php?jno=" + this.jno + "&jobName=" + this.jobName;
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
                    .catch(function(error) {
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
                    type: 'GET',
                    xhrFields: { //response 데이터를 바이너리로 처리한다.
                        responseType: 'blob'
                    },
                    beforeSend: function() {
                        $("#modalLoading").modal("show");
                        data.showPer(0);
                    },
                    xhr: function() { //XMLHttpRequest 재정의 가능
                        var xhr = $.ajaxSettings.xhr();
                        xhr.onprogress = function(e) {
                            data.showPer(Math.floor(e.loaded / e.total * 100));
                        };
                        return xhr;
                    },
                    success: function(response) {
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
                        if (data.isDownError) {
                            data.isDownError = false;
                        } else {
                            $("#modalLoading").modal("hide");
                            $("#percent").hide();
                        }
                    },
                    error: function(request, status, error) {
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
        <div class="row mb-1 px-4" v-show="!noData && jno && !init">
            <div class="dx-viewport">
                <div class="demo-container">
                    <div id="gridContainer"></div>
                </div>
            </div>
        </div>
        <div class="alert alert-success text-center" v-show="!jno">
            <strong>PROJECT를 선택하세요.</strong>
        </div>
        <div class="alert alert-warning" v-show="jno && noData">
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
        <div class="dx-overlay-content dx-loadpanel-content dx-state-invisible" style="width: 200px; height: 90px; z-index: 1501; left: 50%; top: 50%;" v-show="jno">
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