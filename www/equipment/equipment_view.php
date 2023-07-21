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
                    
                    $("#sheet0 td, #sheet0 th").not(".column0").each(function() {
                        var text = $(this).html();
                        var tbClass = $(this).attr("class");
            
                        if(text == "&nbsp;") {
                            $(this).remove();
                        }

                    });
                    
                    // 폰트 10% 증가
                    $("#sheet0 td, #sheet0 th").each(function() {
                        var fontSize = $(this).css("fontSize");
                        if(fontSize) {
                            fontValue = fontSize.replace("px", "");
                            fontValue = Number(fontValue) + Number((fontValue * 0.1));
    
                            $(this).css("fontSize", fontValue + 'px');
                        }

                        $(this).css("padding-left", "5px");
                    });
    
                    $("colgroup").remove();
                    // $("body").removeClass("modal-open");

                    // 전체적인 크기 확장
                    var tblWidth = $("#sheet0").outerWidth();
                    tblWidth = Number(tblWidth) + Number((tblWidth * 0.4));
                    $("#sheet0").css("width", tblWidth + 'px');

                } else {
                    vueData.noData = true;
                }

            })
            .catch(function(error){
                console.log(error);
            });
        }
    }
})
</script>
<div id="app" style="margin-bottom:30px;margin-top:0.65rem;overflow: auto">
    <div class="alert alert-warning" v-show="noData">
        <strong>조건에 맞는 결과가 없습니다.</strong>
    </div>
    <div class="alert alert-danger text-center" v-show="noRight">
        <strong>메뉴를 사용할 권한이 없습니다.</strong>
    </div>
</div>