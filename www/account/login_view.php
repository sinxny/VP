<style>
p.emptyBox {
    height: 100px;
}

@media screen and (max-height: 700px) {
    p.emptyBox {
        display: none;
    }
}

@media screen and (min-width: 576px) {
    .container {
        max-width: 540px;
    }
}

.was-validated .form-control:valid, .form-control.is-valid { 
    border-color:#ced4da !important;
    background-image: inherit !important;
    box-shadow:inherit !important;
}

* {
    font-size: 14px;
}
</style>
<script type="text/javascript">
$(document).ready(function(){
    //로그인 버튼 클릭
    $("#btnLogin").on("click", doLogin);

    var menuRight = '<?php if(isset($menuRight) && $menuRight) { echo $menuRight; } ?>';

    if(menuRight == "all") {

    } else if(menuRight == "vp") {
        $("#loginLabel").text("VDCS Web");
    } else if(menuRight == "cm") {
        $("#loginLabel").text("CMS");
    }
});

//엔터 키 입력
function onLoginKeyPressDown(e) {
    var cd = e.which || e.keyCode;
    if (cd == 13) {
        doLogin(e);
    }
}

//로그인 실행
function doLogin(e) {
    var form = $("#loginForm");
    //유효성 체크 실패
    if (form[0].checkValidity() === false) {
        e.preventDefault();
        e.stopPropagation();

        form.addClass('was-validated');
    }
    //유효성 체크 성공
    else {
        $("#mode").val("LOGIN");

        $.ajax({ 
            type: "POST", 
            url: "account/login.php", 
            data: $("#loginForm").serialize(), 
            dataType: "json", 
            success: function(result) {
                //로그인 성공
                if (result["isLogin"]) {
                    $("#mode").val("INIT");
                    $("#loginForm").attr({
                        action:result["url"], 
                        method:"post", 
                        target:"_self"
                    }).submit();
                }
                //로그인 실패
                else {
                    //에러 메시지 표시
                    $("#divLoginValidationMsg").empty().html(result["msg"]).show();
                }
            },
            error: function (request, status, error) {
                alert("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
            }
        });
    }
}
</script>
<form id="loginForm" name="loginForm" class="needs-validation">
<div class="container">
<p class="emptyBox"></p>
<div class="d-flex justify-content-center">
<img alt="HiTech Engineering" src="../images/hi-tech_logo_2021.png" height="26px" width="180px" />
</div>
<div class="d-flex justify-content-center p-4">
<h1 style="color: #006699;" id="loginLabel"></h1>
</div>
<div id="divLoginValidationMsg" class="alert alert-danger" style="display: none;"></div>
<div class="form-group">
    <label for="login_user_id">아이디 : </label>
    <input type="text" id="login_user_id" name="login_user_id" class="form-control" onkeypress="javascript:onLoginKeyPressDown(event);" required />
    <div class="invalid-feedback">ID는 필수 입력입니다.</div>
</div><br />
<div class="form-group">
    <label for="login_password">패스워드 : </label>
    <input type="password" id="login_password" name="login_password" class="form-control" onkeypress="javascript:onLoginKeyPressDown(event);" required />
    <div class="invalid-feedback">PW는 필수 입력입니다.</div>
</div>
<br />
<button type="button" id="btnLogin" name="btnLogin" class="btn btn-block btn-primary" >LOGIN</button>
<br /><br />
<input type="hidden" id="mode" name="mode" />
</div>
</form>
