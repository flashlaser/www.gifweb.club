<{include file='common/header.tpl'}>
<style>
   .highlight {height: 0; }
</style>
<script src="/gl/static/js/user.js"></script>
<div class="login-container">
    <div class="">
        <div class="h-top"><i></i></div>
        <div class="user-detail">
            <div class="userinfo">
				<form name="myform" id="loginForm" action="/user/signin" method="post">
                <div class="u-info username">
					<i class="loginicon"></i><input type="text" name="phone" id="phone" value="" placeholder="请输入手机号" tabindex="1" required="required"/>
				</div>
                <div class="u-info password">
					<i class="loginicon"></i><input type="text" name="password" id="password" value="" placeholder="请输入验证码" tabindex="2"/>
                    <div class="herline"></div><span class="color47" style="cursor:pointer;">获取验证码</span>
				</div>
                <div class="errormsg" style="display:none"><i class="loginicon"></i><span id="errormsg">验证码输入错误！</span></div>
                <div class="loginbtn complete" style="cursor:pointer;">登录</div>
                <!--<div class="loginbtn complete">登录</div>-->
				<input type="hidden" name="back_url" value="<{$back_url}>" />
				</form>
            </div>
            <div class="otherLogin">
                <div class="otip"><div class="line"></div><span>第三方账号登录</span></div>
                <div class="othertb">
                    <!-- <a href="javascript:;" class="wx"><i class="loginicon"></i></a> -->
                    <a href="https://api.weibo.com/oauth2/authorize?client_id=691988791&response_type=code&redirect_uri=http%3A%2F%2Fwww.wan68.com%2Frespond%2Fwbdo%2F<{$back_url_64}>" class="wb"><i class="loginicon"></i></a>
                    <a href="https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=101261744&redirect_uri=http%3a%2f%2fwww.wan68.com%2frespond%2fqqdo%2F<{$back_url_64}>&state=3d6be0a4035d839573b04816624a415e" class="qq"><i class="loginicon"></i></a>
                </div>
            </div>
        </div>
    </div>
    <div class="agreement"><span>同意</span><a href="/user/agreement">《手游攻略用户服务协议》</a></div>
</div>
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="/gl/static/js/support.js" charset="utf-8"></script>
<script>

    $(window).resize(function(){
        var pageWidth = $(window).width();
        var pageHeight = $(window).height();
        if(pageWidth >= 997 ){
            $(".cont-width").show();
            $(".h-top").height(288);
        }else{
            $(".cont-width").hide();
            $(".h-top").height(parseInt($(window).width()/375*142.5));
        }
        init($(".user-detail").width());
    })
    $(window).resize();
    function init(w){
        var obi = $(".othertb i");
        for(var i in obi ){
            if(i < (obi.length-1)){
                $(obi[i]).css('margin-right',parseInt((w-46*3)/4));
            }
        }
    }
</script>

<{include file='common/footer.tpl'}>
