<style>
#sheet0 th, #sheet0 td {
    border-color: #aaa !important;
}
</style>
<script>
var vm = new Vue({
    el: '#app',
    data: {
        jno : sessionStorage.getItem("jno"),
        index : sessionStorage.getItem("equipIndex"),
        indexname : sessionStorage.getItem("equipMenu"),
        noData : false,
        uno : $("#uno").val(),
        teamId : $("#teamId").val(),
        noRight : false
    },
    created() {
        // 조직도 인원 가져오기
        var organiUser = importOrganization();
        if(organiUser.includes(this.uno) || this.teamId == 90) {
            // 엑셀 불러오기
            this.getExcelToHtml();
        } else {
            this.noRight = true;
        }
    },
    methods: {
        // 엑셀 불러오기
        getExcelToHtml() {
            $("#modalLoading").modal("show");
            url = 'equipment/excel_to_html.php';
            data = {
                jno : this.jno,
                indexname : this.indexname,
                index : this.index
            }
            var vueData = this;
            axios.post(url, data)
            .then(function(response) {
                var html = response["data"];
                if(html) {
                    $("#app").append(html);
                    
                    // 폰트 10% 증가
                    var fontList = [];
                    $("#sheet0 td, #sheet0 th").each(function() {
                        var fontSize = $(this).css("fontSize");
                        var text = $(this).html();
                        if(fontSize && text != '&nbsp;') {
                            fontValue = fontSize.replace("px", "");
                            fontList.push(fontValue);
                        }

                        $(this).css("padding-left", "5px");
                    });
                    minFont = Math.min.apply(null, fontList);
                    if(minFont > 12) {
                        var duRatio = 12 / minFont;

                        $("#sheet0 td, #sheet0 th").each(function() {
                            var fontSize = $(this).css("fontSize");

                            if(fontSize) {
                                fontValue = fontSize.replace("px", "");
                                fontValue = fontValue * duRatio;

                                $(this).css("fontSize", fontValue + 'px');
                            }
                        })
                    }
                    $("colgroup").remove();
                    
                    // 높이 고정
                    var excelHeight = screen.height * 0.75;
                    $("#app").css("height", excelHeight + 'px');

                    // 전체적인 크기 확장
                    var tblWidth = $("#sheet0").outerWidth();
                    tblWidth = Number(tblWidth) + Number((tblWidth * 0.4));
                    $("#sheet0").css("width", tblWidth + 'px');

                    // 타이틀 삭제
                    $("#app title").remove();
                } else {
                    vueData.noData = true;
                }

            })
            .catch(function(error){
                console.log(error);
            })
            .finally(function() {
                $("#modalLoading").modal("hide");
            });
        }
    }
})
</script>
<div id="app" style="margin-top:0.65rem;overflow: auto">
    <div class="alert alert-warning" v-show="noData">
        <strong>조건에 맞는 결과가 없습니다.</strong>
    </div>
    <div class="alert alert-danger text-center" v-show="noRight">
        <strong>메뉴를 사용할 권한이 없습니다.</strong>
    </div>
</div>