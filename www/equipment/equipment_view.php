<style>
@media (max-width: 1900px) {
    #sheet0 {
        width: 70% !important;
    }
}
#sheet0 {
    width: 50%;
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
                    
                    $("td, th").not(".column0").each(function() {
                        var text = $(this).html();
                        var tbClass = $(this).attr("class");
            
                        if(text == "&nbsp;") {
                            $(this).remove();
                        }
                    });
    
                    $("colgroup").remove();
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
<div id="app" style="margin-bottom:30px">
    <div class="alert alert-warning" v-show="noData">
        <strong>조건에 맞는 결과가 없습니다.</strong>
    </div>
    <div class="alert alert-danger text-center" v-show="noRight">
        <strong>메뉴를 사용할 권한이 없습니다.</strong>
    </div>
</div>