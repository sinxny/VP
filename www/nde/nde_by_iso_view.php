<style>
    #gridContainer {
        height: 82.05vh;
    }
    .dx-header-row .dx-cell-focus-disabled {
        text-align:center !important;
        vertical-align: middle !important;
        background-color: #b8daff;
        color: black;
    }
    .dx-datagrid-search-panel {
        height: 30px;
    }
    .dx-datagrid-nowrap .dx-header-row>td>.dx-datagrid-text-content {
        white-space: normal !important;
    }
</style>
<script>
    var vm = new Vue({
        el: '#app',
        data: {
            jno: sessionStorage.getItem("jno"),
            jobName: sessionStorage.getItem("jobName"),
            isDownError: false,
            noData: false
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
                axios({
                    url: 'nde/nde_by_iso_data.php?jno=' + this.jno,
                    method: "GET"
                })
                .then(function(response) {
                    if(response["data"] == null || response["data"].length == 0) {
                        data.noData = true;
                    } else {
                        data.noData = false;
                    }
                })

                if(this.noData == false) {
                    $(() => {
                        const dataGrid = $('#gridContainer').dxDataGrid({
                            dataSource: 'nde/nde_by_iso_data.php?jno=' + this.jno,
                            showRowLines: true,
                            showColumnLines: true,
                            columns: [
                                {
                                    dataField:'NO',
                                    caption: 'NO.',
                                    width: 60,
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
                                    width: 80,
                                    dataType: 'number'
                                },
                                {
                                    caption: 'DWG WELD TOTAL JOINT',
                                    columns: [
                                        {
                                            dataField: 'BW',
                                            caption: 'BW',
                                            width: 80
                                        },
                                        {
                                            dataField: 'SW',
                                            caption: 'SW',
                                            width: 80
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
                                            width: 65
                                        },
                                        {
                                            dataField: 'UT',
                                            caption: 'PAUT',
                                            width: 65
                                        },
                                        {
                                            dataField: 'MT',
                                            caption: 'MT',
                                            width: 65
                                        },
                                        {
                                            dataField: 'PT',
                                            caption: 'PT',
                                            width: 65
                                        }
                                    ]
                                },
                                {
                                    dataField: 'REPORT_JOINT',
                                    caption: 'REPORT JOINT',
                                    width: 80
                                },
                                {
                                    dataField: 'BALANCE',
                                    caption: 'BALANCE',
                                    width: 87.5
                                },
                                {
                                    dataField: 'PROGRESS',
                                    caption: 'PROGRESS(%)'
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
                                pageSize: 18,
                            },
                        }).dxDataGrid('instance');
    
                        const applyFilterTypes = [{
                            key: 'auto',
                            name: 'Immediately',
                        }, {
                            key: 'onClick',
                            name: 'On Button Click',
                        }];
    
                        const applyFilterModeEditor = $('#useFilterApplyButton').dxSelectBox({
                            items: applyFilterTypes,
                            value: applyFilterTypes[0].key,
                            valueExpr: 'key',
                            displayExpr: 'name',
                            onValueChanged(data) {
                                dataGrid.option('filterRow.applyFilter', data.value);
                            },
                        }).dxSelectBox('instance');
    
                        $('#filterRow').dxCheckBox({
                            text: 'Filter Row',
                            value: true,
                            onValueChanged(data) {
                                dataGrid.clearFilter();
                                dataGrid.option('filterRow.visible', data.value);
                                applyFilterModeEditor.option('disabled', !data.value);
                            },
                        });
    
                        $('#headerFilter').dxCheckBox({
                            text: 'Header Filter',
                            value: true,
                            onValueChanged(data) {
                                dataGrid.clearFilter();
                                dataGrid.option('headerFilter.visible', data.value);
                            },
                        });
    
                        var btnExcel = `<button type="button" class="btn btn-outline-primary btn-sm text-left ml-3 text-center mt-1" style="width:130px;" id="btnExportExcel" title="목록 내보내기">
                                        <i class="fa-solid fa-file-export" style="font-size:large"></i> 목록 내보내기
                                    </button>`;
                        $(".dx-toolbar-after").append(btnExcel);
                        
                        // 목록 내보내기 버튼 클릭
                        $("#btnExportExcel").on('click', this.exportNdeExcel);
    
                        function getOrderDay(rowData) {
                            return (new Date(rowData.OrderDate)).getDay();
                        }
                    });
                }
            },
            // 목록 내보내기 버튼 클릭
            exportNdeExcel() {
                var url = "nde/nde_by_iso_download_excel.php?jno=" + this.jno + "&jobName=" + this.jobName;
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
                    .catch(function(error) {
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
        <div class="row mb-1 px-4" v-show="!noData && jno">

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
                    <i class="fa fa-spinner fa-pulse fa-3x text-primary"></i>
                    <!-- <div id="percent" style="padding:1rem;color:white;display:none"></div> -->
                </div>
            </div>
        </div>
    </form>
</div>