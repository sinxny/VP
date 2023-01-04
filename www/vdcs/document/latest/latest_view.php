<style>
.tblLatestList td {
    cursor:pointer;
}
/* #tblLatestList tbody tr:hover {
    background-color:#FFE5CC;
} */
.rowActive {
    background-color:#FFE5CC;
}
.downloadImg{
    cursor:pointer;
}
.resultFinal {
    color:blue;
}
.resultNull {
    font-style: italic;
}
</style>
<script>
var vm = new Vue({
    el: '#app',
    data: {
        icon: '<i class="fa-solid fa-caret-up"></i>',
        collapse : false,
        latestList : [],
        docHistory : [],
        jno : sessionStorage.getItem("jno"),
        jobName : sessionStorage.getItem("jobName"),
        selectDoc : null,
        showHistory : false,
        pageNo : 1,
        totalCnt : null,
        totalCntString : null, 
        totalPage : null,
        naviOffset : 15,
        pageList : null,
        isRebrowsing : false,
        researchOption : 'so_all',
        researchText : '',
        searchList : {},
        selectHistory : null,
        searchHtml : '',
        selectList : [],
        checkList : [''],
        sd_start_date : '',
        sd_end_date : '',
        sdOption : '0',
        so_dc : '',
        so_rc : '',
        noData : false,
        researchSave : ''
    },
    created() {
        // 최신문서 데이터 불러오기
        this.getLatestData();

        // 날짜 min/max값 넣기
        dateMinMaxAppend();
    },
    computed: {
        allSelected: {
            get: function() {
                return this.checkList.length === this.selectList.length;
            },
            set: function(e) {
                this.selectList = e ? this.checkList : [];
            }
        }
    },
    methods: {
        //Tools 열고 닫기
        collapseChange() {
            if(this.collapse === false) {
                this.collapse = true;
                this.icon = '<i class="fa-solid fa-caret-down"></i>';
                $("#tools").collapse("hide");
            } else {
                this.collapse = false;
                this.icon = '<i class="fa-solid fa-caret-up"></i>';
                $("#tools").collapse("show");
            }
        },
        // 데이터 가져오기
        getLatestData() {
        var data = this;
            var jno = data.jno;
            var searchCondition = '';

            if(this.isRebrowsing) {
                $.each(this.searchList, function(i, condition) {
                    searchCondition += "&" + i + "=" + condition;
                });
            } else {
                searchCondition = "&" + this.researchOption + "=" + this.researchSave;
            }
            searchCondition += "&sd_type=" + this.sdOption + "&sd_start_date=" + this.sd_start_date + "&sd_end_date=" + this.sd_end_date;
            searchCondition += "&so_dc=" + this.so_dc + "&so_rc=" + this.so_rc;

            var url = '/api/vdcs/?api_key=d6c814548eeb6e41722806a0b057da30&api_pass=BQRUQAMXBVY=&mode=latest&jno='+ jno + '&navi_page='+ this.pageNo +'&navi_offset=' + this.naviOffset + searchCondition;
            axios.get(url).then(
                function(response) {
                    var latest = response["data"];
                    if(latest["Message"] == "Success") {
                        var latestList = latest["Value"];
                        data.latestList = latestList;
                        
                        data.checkList = [];
                        // 체크박스 리스트
                        $.each(latestList, function(i, info) {
                            data.checkList.push(info["doc_no"]);
                        });
                        
                        // 페이지 요소 data에 저장
                        data.totalPage = latest["Navigator"]["TotalPage"];
                        data.totalCnt = latest["Navigator"]["TotalRow"];
                        data.totalCntString = numberToCommas(latest["Navigator"]["TotalRow"].toString());

                        // 페이지 정보
                        $("#pageInfo").show();

                        data.noData = false;
                    } else if(latest["Value"] == null) {
                        data.latestList = [];

                        data.pageNo = 1;
                        data.totalPage = 1;
                        data.totalCnt = 0;
                        data.totalCntString = 0;

                        // history 목록, pdf 숨기기
                        data.showHistory = false;
                        data.selectDoc = null;
                        data.noData = true;
                    }
                    // 페이징 바 가져오기
                    data.getPagination();
                    // 선택 박스 초기화
                    data.selectList = [];
            })
            .finally(function () {
                // 검색 배경
                // if(data.researchOption == "so_all" && data.researchSave) {
                //     $(".tblLatestList td").each(function() {
                //         if($(this).text().match(data.researchSave)) {
                //             const redRegExp = new RegExp(data.researchSave);

                //             var ans = $(this).text().replace(redRegExp, "<span style='background-color:pink'>" + data.researchSave + "</span>")
                //             $(this).html(ans);
                //         }
                //     });
                // } else if (data.researchOption != "so_all" && data.researchSave) {
                //     $("." + data.researchOption).each(function() {
                //         if($(this).text().match(data.researchSave)) {
                //             const redRegExp = new RegExp(data.researchSave);

                //             var ans = $(this).text().replace(redRegExp, "<span style='background-color:pink'>" + data.researchSave + "</span>")
                //             $(this).html(ans);
                //         }
                //     });
                // }
            });
        },
        // 행 클릭
        docRowClick(docNum) {
            // Latest 행 active
            this.selectDoc = docNum;
            // docHistory Table 활성화
            this.showHistory = true;

            var data = this;
            var jno = data.jno;

            axios.get('/api/vdcs/?api_key=d6c814548eeb6e41722806a0b057da30&api_pass=BQRUQAMXBVY=&mode=doc_history&jno='+ jno + '&ms_no=' + docNum ).then(
                function(response) {
                    var docHistory = response["data"];
                    if(docHistory["Message"] == "Success") {
                        var docHistory = docHistory["Value"];
                        data.docHistory = docHistory
                        data.selectHistory = docHistory[0]["doc_no"];
                        data.historyRowClick(data.selectHistory);
                    };
                }
            );
        },
        // 페이징바 가져오기
        getPagination() {
            var property = this;

            var url = '../../../common/pagination.php';
            var data = {
                pageNo : this.pageNo,
                totalCnt : this.totalCnt,
                customPageUnit : this.naviOffset
            }
            axios.post(url, data)
            .then(function(response) {
                property.pageList = response["data"]["pageList"];
            })
            .catch(function(error){
                console.log(error);
            });
        },
        // 페이지 이동하기
        onPageNoClick(event) {
            var obj = event.target.tagName;
            
            if(obj == "A") {
                var pageId = event.target.id;
                if(pageId) {
                    pageNo = pageId.split('_');
                    if(this.pageNo != pageNo[1]) {
                        this.pageNo = pageNo[1];
                        this.getLatestData();
                        this.showHistory = false;
                        this.selectDoc = null;
                        this.selectList = [];
                    }
                }
            } else if (obj == "I") {
                pageId = $(event.target).closest("a").attr("id");
                pageNo = pageId.split('_');
                if(this.pageNo != pageNo[1]) {
                    this.pageNo = pageNo[1];
                    this.getLatestData();
                    this.showHistory = false;
                    this.selectDoc = null;
                    this.selectList = [];
                }
            }
        },
        // 검색 버튼
        btnSearchClick() {
            // 결과 내 재검색 on
            if(this.isRebrowsing == true) {
                var extOption = Object.keys(this.searchList).includes(this.researchOption);

                if(extOption) {
                    var element = this.searchList[this.researchOption].split("♡");

                    var overlap = 0
                    var data = this;
                    $.each(element, function(i, text) {
                        if(text == data.researchText) {
                            overlap++;
                        }
                    });

                    if(overlap == 0) {
                        if(this.researchText) {
                            this.searchList[this.researchOption] = this.searchList[this.researchOption] + '♡' + this.researchText
                        }
                    }
                } else {
                    if(this.researchText) {
                        this.searchList[this.researchOption] = this.researchText;
                    }
                }
                this.updateWord();

                this.pageNo = 1;
                this.getLatestData();

                // 검색창 초기화
                this.researchText = '';
            } else {
                this.pageNo = 1;
                this.researchSave = this.researchText;
                this.getLatestData();
            }
        },
        // Distribute File 다운로드
        distributeDownload(doc_no) {
            location.href = '/api/vdcs/?api_key=d6c814548eeb6e41722806a0b057da30&api_pass=BQRUQAMXBVY=&model=DOC_DE_DOWNLOAD&jno='+ this.jno +'&doc_no='+ doc_no;
        },
        // Issue File 다운로드
        IssueDownload(doc_no) {
            location.href = '/api/vdcs/?api_key=d6c814548eeb6e41722806a0b057da30&api_pass=BQRUQAMXBVY=&model=DOC_RE_DOWNLOAD&jno='+ this.jno +'&doc_no='+ doc_no;
        },
        // History 행 클릭
        historyRowClick(doc_no) {
            // Histroy 행 active
            this.selectHistory = doc_no;

            // Pdf 미리보기
            $("#previewPdf").load('../../../pdfjs-3.0.279-dist/web/pdfSmViewer.php?jno=' + this.jno + '&doc_no=' + this.selectHistory);
        },
        // 검색어 삭제
        deleteWord(event) {
            var obj = event.target;
            // 검색옵션 삭제
            if($(obj).hasClass("deleteOption")) {
                var optionNm = $(obj).siblings("span").attr("class");
                delete this.searchList[optionNm];
                this.updateWord();
            }
            // 검색어 삭제 
            else if($(obj).hasClass("deleteWord")) {
                var optionNm = $(obj).siblings("span").attr("class");
                var wordNm = $(obj).siblings("span").text();

                var searchElement = this.searchList[optionNm].split("♡");

                searchElement = searchElement.filter(function(data) {
                    return data != wordNm
                });

                if(searchElement.length == 0) {
                    delete this.searchList[optionNm];
                } else {
                    this.searchList[optionNm] = searchElement.join('♡');
                }

                this.updateWord();
            }

            this.btnSearchClick();
        },
        // 검색어 업데이트
        updateWord() {
            var data = this;
            data.searchHtml = '';
            $.each(data.searchList, function(i, text) {
                var optionNm = $('option[value="'+ i +'"]').text();
                data.searchHtml += '<span class="badge badge-info mr-2" style="font-size:small">';
                data.searchHtml += '<span class="'+ i +'">' + optionNm + '</span>';
                data.searchHtml += ' <span class="deleteOption" style="color:black;cursor:pointer">X</span></span>';

                var searchElement = text.split("♡");
                $.each(searchElement, function(j, element) {
                    data.searchHtml += '<span class="mr-2" style="background-color:#CCE5FF">';
                    data.searchHtml += '<span class="'+ i +'">' + element + '</span>';
                    data.searchHtml += ' ' + '<span class="deleteWord" style="color:black;cursor:pointer;">X</span></span>';
                });
            });
            if(data.so_dc != '') {
                data.searchHtml += '<span class="badge badge-info mr-2" style="font-size:small">';
                data.searchHtml += '<span class="공종(Disc.)">공종(Disc.)</span>';
                data.searchHtml += '</span>';
                data.searchHtml += '<span class="mr-2" style="background-color:#CCE5FF">';
                data.searchHtml += '<span class="'+ data.so_dc +'">' + data.so_dc + '</span>';
                data.searchHtml += '</span>';
            }
            if(data.so_rc != '') {
                data.searchHtml += '<span class="badge badge-info mr-2" style="font-size:small">';
                data.searchHtml += '<span class="Result#">Result#</span>';
                data.searchHtml += '</span>';
                data.searchHtml += '<span class="mr-2" style="background-color:#CCE5FF">';
                data.searchHtml += '<span class="'+ data.so_rc +'">' + data.so_rc + '</span>';
                data.searchHtml += '</span>';
            }
        },
        // 결과 내 검색 사용여부
        isUseRebrowsing() {
            if(this.isRebrowsing) {
                this.searchList = {}
                this.searchHtml = ''
                this.getLatestData();
            } else {
                if(this.researchText) {
                    this.searchList[this.researchOption] = this.researchText;
                    this.researchText = '';
                }
                this.updateWord();
            }
        },
        // 선택 다운로드
        selDocDownload() {
            var selDoc = this.selectList.join(",")

            location.href = '/api/vdcs/?api_key=d6c814548eeb6e41722806a0b057da30&api_pass=BQRUQAMXBVY=&model=DOC_LE_DOWNLOAD&jno='+ this.jno +'&doc_no=' + selDoc;
        },
        // 최신목록 내보내기
        exportLatestExcel() {
            var url = '/vdcs/document/latest/latest_list_download_excel.php?jno=' + this.jno + "&jobName=" + this.jobName;
            location.href = url;
            
            const excelDown = new XMLHttpRequest();
            excelDown.open("GET", url, true);
            
            $("#modalLoading").modal("show");
            
            excelDown.send();
            var startDown = new Date();
            excelDown.onload = function() {
                var endDown = new Date();
                var downTime = endDown - startDown;
                setTimeout(function() {
                    $("#modalLoading").modal("hide");
                }, downTime);
            }
        },
        // 전체 다운로드
        allDocDownload() {
            var data = this;
            var url = "/api/vdcs/?api_key=d6c814548eeb6e41722806a0b057da30&api_pass=BQRUQAMXBVY=&model=LATEST_ZIP_INFO&jno=" + data.jno;
            axios.post(url)
            .then(function(response) {
                if((response["data"]["Message"]) == "Success") {
                    var value = response["data"]["Value"];
                    $("#confirmModal .modal-body").html(`전체 다운로드는 대용량인 관계로 전일 22시를 기준으로 생성됩니다.<br />
                                                        이점 감안하시기 바랍니다.<br /><br />
                                                        - 파일 생성일자 : ${value["file_date_str"]}<br />
                                                        - 파일 크기 : ${value["file_size_str"]}`);
                    $("#confirmModal").modal("show");
                    var url = "/api/vdcs/?api_key=d6c814548eeb6e41722806a0b057da30&api_pass=BQRUQAMXBVY=&model=LATEST_ZIP_DOWNLOAD&jno=" + data.jno;
                    $("#btnConfirm").on("click", function() {
                        location.href = url;
            
                        const allDownload = new XMLHttpRequest();
                        allDownload.open("GET", url, true);
            
                        $("#modalLoading").modal("show");
            
                        allDownload.send();
                        var startDown = new Date();
                        allDownload.onload = function() {
                            var endDown = new Date();
                            var downTime = endDown - startDown;
                            setTimeout(function() {
                                $("#modalLoading").modal("hide");
                            }, downTime);
                        }
                    });
                }
            })
            .catch(function(error){
                console.log(error);
            });
        }
    }
})
</script>
<div id="app" style="margin-bottom:30px">
<form id="mainForm" name="mainForm">
<div class="row mb-1">
    <div class="col-md-1">
        <i class="fa-solid fa-magnifying-glass"></i> <b style="font-size:large">Search</b>
    </div>
    <div class="col-md text-right">
        <span v-show="jno">
            <button type="button" class="btn btn-outline-primary btn-sm text-left mr-2 text-center" style="width:130px;" @click="selDocDownload" :disabled="selectList.length == 0" title="선택 다운로드">
                <i class="fa-solid fa-check" style="font-size:large"></i> 선택 다운로드
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm text-left mr-2 text-center" style="width:130px;" @click="allDocDownload" :disabled="latestList.length == 0" title="전체 다운로드">
                <i class="fa-solid fa-floppy-disk" style="font-size:large"></i> 전체 다운로드
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm text-left mr-2 text-center" style="width:130px;" @click="exportLatestExcel" :disabled="latestList.length == 0" title="목록 내보내기">
                <i class="fa-solid fa-file-export" style="font-size:large"></i> 목록 내보내기
            </button>
        </span>
        <button type="button" class="btn btn-outline-dark btn-sm" v-html="icon" @click="collapseChange"></button>
    </div>
</div>
<div class="row">
    <div class="container-fluid mb-1 p-1 mx-3 border collapse show" style="border-radius:10px;" id="tools">
        <div class="row mt-2">
            <div class="col-md row">
                <div class="col-md-1">
                    <label class="ml-2" style="padding-top:0.2rem;color:#A0A0A0">검색조건</label>
                </div>
                <div class="col-md-4">
                    <div class="input-group-prepend">
                        <label for="" style="padding-top:0.2rem">기간</label>
                        <select class="form-control ml-2" style="width:min-content" v-model="sdOption">
                            <option value="0">선택</option>
                            <option value="distribute">배포일</option>
                            <option value="reply">회신일</option>
                        </select>
                        <input type="date" class="form-control mx-2 text-center" v-model="sd_start_date"/>~ 
                        <input type="date" class="form-control ml-2 text-center" v-model="sd_end_date"/>
                    </div>
                </div>
                <div class="col-md-2" style="padding-left:0 !important">
                    <div class="row" style="float:right">
                        <select class="form-control mr-2" style="width:min-content" v-model="so_dc" @click="btnSearchClick">
                            <option value="">공종(Disc.)</option>
                            <option value="STAT">STAT(고정기기)</option>
                            <option value="ROTA">ROTA(회전기계)</option>
                            <option value="INST">INST(계기)</option>
                            <option value="ELEC">ELEC(전기)</option>
                        </select>
                        <select class="form-control" style="width:min-content" v-model="so_rc" @click="btnSearchClick">
                            <option value="">Result#</option>
                            <option value="A">A</option>
                            <option value="N">N</option>
                            <option value="RFC">RFC</option>
                            <option value="R">R</option>
                            <option value="NN">NN</option>
                            <option value="F">F</option>
                            <option value="UN">UN</option>
                            <option value="NULL">(NULL)</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group mb-2">
                        <div class="input-group-prepend">
                            <select class="form-control ml-2" v-model="researchOption">
                                <option value="so_all">전체</option>
                                <option value="so_doc_no">문서번호</option>
                                <option value="so_doc_ti">문서제목</option>
                                <option value="so_vn">Vendor</option>
                                <option value="so_tr">TR No.</option>
                                <option value="so_rt">RFQ. No. / Title</option>
                                <option value="so_ti">Item / Tag No.</option>
                                <!-- <option value="so_dc">공종</option> -->
                                <!-- <option value="so_rc">Result #</option> -->
                            </select>
                        </div>
                        <input type="text" class="form-control" v-model="researchText" @keydown.enter.prevent="btnSearchClick"/>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-info" id="btnSearch" @click="btnSearchClick" :disabled="!jno" title="검색"><i class="fa-solid fa-magnifying-glass"></i></button>
                        </div>
                    </div>
                </div>
                <div class="col-md-1 text-right" style="padding:0 !important; padding-top:0.2rem !important">
                    <div class="custom-control custom-switch" style="padding:0 !important" title="결과 내 재검색">
                        <input type="checkbox" class="custom-control-input" id="rebrowsing" v-model="isRebrowsing" @click="isUseRebrowsing">
                        <label class="custom-control-label" for="rebrowsing">결과 내 재검색</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="ml-3" v-html="searchHtml" v-show="isRebrowsing" @click="deleteWord($event)"></div>
    </div>
</div>
<div v-show="jno">
    <!-- pc화면 -->
    <div class="d-none d-xl-block">
        <div class="row">
            <div class="col-3" style="padding-right:0 !important">
                <table class="table table-bordered table-sm tblLatestList" style="height:min-content;">
                    <thead class="thead-light">
                        <tr>
                            <th><input type="checkbox" v-model="allSelected"/></th>
                            <th>공종</th>
                            <th title="Doc. No.">문서번호</th>
                            <th>Rev.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr :key="doc.ms_no" v-for="doc in latestList" :class="{'rowActive' : (doc.ms_no == selectDoc), 'resultFinal' : (doc.doc_status_nick == 'F'), 'resultNull' : (doc.doc_status_nick == '')}">
                            <td class="text-center"><input type="checkbox" v-model="selectList" :value="doc.doc_no"/></td>
                            <td class="text-center" @click="docRowClick(doc.ms_no)">{{ doc.tr_func_cd }}</td>
                            <td @click="docRowClick(doc.ms_no)" style="width:230px" :title="doc.doc_num"><div class="text-ellipsis so_doc_no">{{ doc.doc_num }}</div></td>
                            <td class="text-center" @click="docRowClick(doc.ms_no)">{{ doc.doc_rev_num }}</td>
                        </tr>
                    </tbody>
                </table>
                <div id="pageInfo" style="display:none;padding-left:25px" v-show="!noData">Pages : <span style="color:red">{{ pageNo }}</span> - {{ totalPage }} / Rows : {{ totalCntString }}</div>
            </div>
            <div class="col-8" style="padding:0 !important;">
                <div class="table-responsive tblLatestList">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th class="responsiveTblRow" title="Doc. Title">문서제목</th>
                                <th class="responsiveTblRow" title="Vendor Name">Vendor</th>
                                <th class="responsiveTblRow">TR No.</th>
                                <th class="responsiveTblRow" title="Distribute Date">배포일</th>
                                <th class="responsiveTblRow" title="Reply Date">회신일</th>
                                <th class="responsiveTblRow">RFQ. No.</th>
                                <th class="responsiveTblRow">RFQ. Title</th>
                                <th class="responsiveTblRow">Item / Tag No.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr :key="doc.ms_no" v-for="doc in latestList" :class="{'rowActive' : (doc.ms_no == selectDoc), 'resultFinal' : (doc.doc_status_nick == 'F'), 'resultNull' : (doc.doc_status_nick == '')}">
                                <td class="responsiveTblRow so_doc_ti" @click="docRowClick(doc.ms_no)">{{ doc.doc_title }}</td>
                                <td class="text-center responsiveTblRow so_vn" @click="docRowClick(doc.ms_no)">{{ doc.from_comp_name }}</td>
                                <td class="responsiveTblRow so_tr" @click="docRowClick(doc.ms_no)">{{ doc.tr_doc_num }}</td>
                                <td class="text-center responsiveTblRow" @click="docRowClick(doc.ms_no)">{{ doc.doc_distribute_date_str }}</td>
                                <td class="text-center responsiveTblRow" @click="docRowClick(doc.ms_no)">{{ doc.doc_reply_date_str }}</td>
                                <td class="responsiveTblRow so_rt" @click="docRowClick(doc.ms_no)">{{ doc.doc_rfq_num }}</td>
                                <td class="responsiveTblRow so_rt" @click="docRowClick(doc.ms_no)">{{ doc.doc_rfq_title }}</td>
                                <td class="responsiveTblRow so_ti" @click="docRowClick(doc.ms_no)">{{ doc.doc_tag_item }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col" style="padding-left:0 !important">
                <table class="table table-bordered table-sm tblLatestList" style="height:min-content">
                    <thead class="thead-light">
                        <tr>
                            <th>Cnt</th>
                            <th title="Result Code">
                                Rslt #
                                <div class="tooltipBox"><i class="fa-solid fa-circle-question"></i>
                                    <span class="tooltiptext"><img class="img-thumbnail" src="../../../images/result_code_tooltip.png" /></span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr :key="doc.ms_no" v-for="doc in latestList" :class="{'rowActive' : (doc.ms_no == selectDoc), 'resultFinal' : (doc.doc_status_nick == 'F'), 'resultNull' : (doc.doc_status_nick == '')}">
                            <td class="text-center" @click="docRowClick(doc.ms_no)">{{ doc.doc_cnt }}</td>
                            <td class="text-center" @click="docRowClick(doc.ms_no)">{{ doc.doc_status_nick }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- 모바일 화면 -->
    <div class="table-responsive d-xl-none d-lg-block">
        <table class="table table-bordered table-sm tblLatestList">
            <thead class="thead-light">
                <tr> 
                    <th style="white-space: nowrap;min-width: 1rem"><input type="checkbox" v-model="allSelected"/></th>
                    <th class="responsiveTblRow" title="VP Discipline Code">Disc.</th>
                    <th class="responsiveTblRow">Doc. No.</th>
                    <th class="responsiveTblRow">Rev.</th>
                    <th class="responsiveTblRow">Doc. Title</th>
                    <th class="responsiveTblRow" title="Vendor Name">Vendor</th>
                    <th class="responsiveTblRow">TR No.</th>
                    <th class="responsiveTblRow">Dist. Date</th>
                    <th class="responsiveTblRow">Reply Date</th>
                    <th class="responsiveTblRow">RFQ. No.</th>
                    <th class="responsiveTblRow">RFQ. Title</th>
                    <th class="responsiveTblRow">Item/Tag No.</th>
                    <th class="responsiveTblRow">Cnt</th>
                    <th class="responsiveTblRow" title="Result Code">
                        Rslt #
                        <div class="tooltipBox"><i class="fa-solid fa-circle-question"></i>
                            <span class="tooltiptext"><img class="img-thumbnail" src="../../../images/result_code_tooltip.png" /></span>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr :key="doc.ms_no" v-for="doc in latestList" :class="{'rowActive' : (doc.ms_no == selectDoc), 'resultFinal' : (doc.doc_status_nick == 'F'), 'resultNull' : (doc.doc_status_nick == '')}">
                    <td class="text-center" style="white-space: nowrap;min-width: 1rem"><input type="checkbox" v-model="selectList" :value="doc.doc_no"/></td>
                    <td class="responsiveTblRow text-center" @click="docRowClick(doc.ms_no)">{{ doc.tr_func_cd }}</td>
                    <td class="responsiveTblRow" @click="docRowClick(doc.ms_no)">{{ doc.doc_num }}</td>
                    <td class="responsiveTblRow text-center" @click="docRowClick(doc.ms_no)">{{ doc.doc_rev_num }}</td>
                    <td class="responsiveTblRow" @click="docRowClick(doc.ms_no)">{{ doc.doc_title }}</td>
                    <td class="responsiveTblRow text-center" @click="docRowClick(doc.ms_no)">{{ doc.from_comp_name }}</td>
                    <td class="responsiveTblRow" @click="docRowClick(doc.ms_no)">{{ doc.tr_doc_num }}</td>
                    <td class="responsiveTblRow text-center" @click="docRowClick(doc.ms_no)">{{ doc.doc_distribute_date_str }}</td>
                    <td class="responsiveTblRow text-center" @click="docRowClick(doc.ms_no)">{{ doc.doc_reply_date_str }}</td>
                    <td class="responsiveTblRow" @click="docRowClick(doc.ms_no)">{{ doc.doc_rfq_num }}</td>
                    <td class="responsiveTblRow" @click="docRowClick(doc.ms_no)">{{ doc.doc_rfq_title }}</td>
                    <td class="responsiveTblRow" @click="docRowClick(doc.ms_no)">{{ doc.doc_tag_item }}</td>
                    <td class="responsiveTblRow text-center" @click="docRowClick(doc.ms_no)">{{ doc.doc_cnt }}</td>
                    <td class="responsiveTblRow text-center" @click="docRowClick(doc.ms_no)">{{ doc.doc_status_nick }}</td>
                </tr>
            </tbody>
        </table>
    </div>
        <div class="alert alert-warning" v-show="noData">
        <strong>조건에 맞는 결과가 없습니다.</strong>
    </div>
    <ul class="pagination pagination-sm justify-content-center" v-html="pageList" @click="onPageNoClick($event)"></ul>
    <div class="row">
        <div class="col-md-8">
            <table class="table table-bordered table-sm" id="tblDocHistory" v-show="showHistory">
                <thead class="thead-light">
                    <tr>
                        <!-- <th><input type="checkbox" /></th> -->
                        <th>No</th>
                        <th>Rev.</th>
                        <th title="VP Discipline Code">공종</th>
                        <th>문서번호</th>
                        <th>TR No.</th>
                        <th>접수일</th>
                        <th>배포일</th>
                        <th>회신일</th>
                        <th title="Result Code">Rslt #</th>
                        <th title="Distribute">Dist.</th>
                        <th>Reply</th>
                    </tr>
                </thead>
                <tbody>
                    <tr :key="history.doc_no" v-for="history in docHistory" :class="{'rowActive' : (history.doc_no == selectHistory), 'resultFinal' : (history.doc_status_nick == 'F'), 'resultNull' : (history.doc_status_nick == '')}">
                        <!-- <td><input type="checkbox" /></td> -->
                        <td class="text-center" @click="historyRowClick(history.doc_no)">{{ history.rowno }}</td>
                        <td class="text-center" @click="historyRowClick(history.doc_no)">{{ history.doc_rev_num }}</td>
                        <td class="text-center" @click="historyRowClick(history.doc_no)">{{ history.func_cd }}</td>
                        <td @click="historyRowClick(history.doc_no)">{{ history.doc_num }}</td>
                        <td @click="historyRowClick(history.doc_no)">{{ history.tr_doc_num }}</td>
                        <td class="text-center" @click="historyRowClick(history.doc_no)">{{ history.hist_receive_date_str }}</td>
                        <td class="text-center" @click="historyRowClick(history.doc_no)">{{ history.hist_distribute_date_str }}</td>
                        <td class="text-center" @click="historyRowClick(history.doc_no)">{{ history.hist_reply_date_str }}</td>
                        <td class="text-center" @click="historyRowClick(history.doc_no)">{{ history.doc_status_nick }}</td>
                        <td class="text-center"><span class="downloadImg" @click="distributeDownload(history.doc_no)" title="배포문서 다운로드"><img src="../../../images/preview.png" /></span></td>
                        <td class="text-center"><span class="downloadImg" v-show="history.doc_status && history.doc_status_nick != 'F'" @click="IssueDownload(history.doc_no)" title="회신문서 다운로드"><img src="../../../images/outlook.png"/></span></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-4 text-center" v-show="showHistory">
            <div class="container-fluid p-1 mb-1 border" id="previewPdf"></div>
        </div>
    </div>
</div>
<div class="alert alert-success text-center" v-show="!jno">
  <strong>PROJECT을 선택하세요.</strong>
</div>
<div id="modalLoading" class="modal modal-loading" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <i class="fa fa-spinner fa-pulse fa-3x text-primary"></i>
        </div>
    </div>
</div>
</form>
</div>