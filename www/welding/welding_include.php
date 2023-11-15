<div class="alert alert-success text-center" v-show="!jno">
  <strong>PROJECT를 선택하세요.</strong>
</div>
<div class="alert border text-center" v-show="!jno">
    <div class="mb-4 text-center p-2" style="font-weight:bold;">공사관리</div>
    <div class="m-2">Welding, NDE, PKG 데이터 관련 문의 : 김종희 차장(☎1596)</div>
    <div class="m-2">프로젝트 정보 및 조직도 관련 문의 : 프로젝트 PM / PE</div>
    <div class="m-2">시스템 운영 및 에러 관련 문의 : 박시은 사원(☎1074)</div>
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