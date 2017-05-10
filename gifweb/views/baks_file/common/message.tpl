<{include file='common/header.tpl'}>
<link rel="stylesheet" href="/gl/static/css/error.css">

<div id="main" class="cont-width">
    <div class="wrap">
        <div class="role"><img src="/gl/static/images/v1/404.png"/></div>
        <div class="absoluteleft">
            <div class="txt-tip">
                <p class="p1"><{$message}></p>
                <p class="p2"><em>3</em>秒后页面自动跳转到上一页</p>
            </div>
            <div class="backbtn">
                <a href="/" class="backhome">返回首页</a>
                <a href="javascript:history.go(-1);" class="backprev">返回上一页</a>
            </div>
            <div class="pc-add">
                <div class="gameservice">
                    <div class="pcrwm clear">
                        <div class="rwm fl"><img src="/gl/static/images/v1/rwmpc.jpg"/></div>
                        <div class="fl"><p>全民手游攻略APP</p><p class="p2">最专业的手游攻略问答社区</p><a href="/download" class="pcdownload">立即下载</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function(){
	var backtime = 3;
	var time = setInterval(function(){
		$(".txt-tip .p2 em").html(backtime);
		if(backtime>0){
			backtime--;
		}else{
			clearInterval(time);
			//返回上一页事件
			<{if $back_url}>
			 window.location.href="<{$back_url}>";
			 <{else}>
			 window.history.back(-1); 
			 <{/if}>
		}
	},1000);
	$(window).resize();
});
$(window).resize(function(){
        var pageWidth = $(window).width();
        if(pageWidth >= 997 ){
            $(".ask a").click(function(){
                $(".askQuestion").toggle();
            });
            var pageHeight = $(window).height();
            var h = pageHeight -$("header").height()- $('footer').height()-$(".highlight").height();
            $('#main').height(h);
        }
    })
</script>

<{include file='common/footer.tpl'}>