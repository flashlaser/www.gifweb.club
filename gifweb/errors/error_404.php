<?php if($_REQUEST['deviceId']){ ?>
{"result":404,"data":[],"message":"uri error"}
<?php }else{ ?>
<!DOCTYPE html>
<html lang="zh-CN" class="supported">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>404-您访问的页面不存在</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no,initial-scale=1">
    <meta name="msapplication-config" content="none">
    <!-- Bootstrap -->
    <link href="/gl/static/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/gl/static/css/common.css">
    <link rel="stylesheet" href="/gl/static/css/error.css">
    <!--[if lt IE 9]>
    <script src="//cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="//cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <script src="http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script>
    <![endif]-->
    <script src="/gl/static/js/jquery-1.11.3.min.js"></script>
	<script type="text/javascript">
		/*百度统计*/
		var _hmt = _hmt || [];
		(function() {
			var hm = document.createElement("script");
			hm.src = "//hm.baidu.com/hm.js?457beebb622d3780f367cfa604ba91a9";
			var s = document.getElementsByTagName("script")[0]; 
			s.parentNode.insertBefore(hm, s);
		})();
	</script>
</head>
<body class="home">
<header>
    <nav class="cont-width">
        <div class="clear" id="headtop">
            <div class="logo"><i class="icon"></i><i class="icon2"></i></div>
            <ul>
                <li class="search" style="display:none">
                    <a href="#" class="phonesearch"><i class="icon"></i></a>
                    <a href="#" class="pcsearch">
					<form action="/search" method="post" name="searchForm">
					<input type="text" placeholder="搜索游戏、攻略、问答" name="search_keyword">
					<input type="submit" name="submit" id="" value="" class="iconpc search-btn">
					</form>
					</a>
                </li>
                <li class="ask" style="display:none">
                    <a href="javascript:;"><i class="icon" ></i><i class="icon2"></i><span>提问</span>
                        <div class="askQuestion">
                            <i class="icon2"></i>
                            <input type="text" placeholder="游戏、攻略、问答"><div class="asksearch"><input type="submit" name="submit" id="" value="" class="icon2 search-btn"></div>
                            <ul>
                                <li>关于炉石传说我想知道....</li>
                                <li>关于炉石传说我想知道....</li>
                                <li>关于炉石传说我想知道....</li>
                                <li>关于炉石传说我想知道....</li>
                                <li>关于炉石传说我想知道....</li>
                            </ul>
                        </div>
                    </a>
                </li>
                <li class="login" style="display:none">
					<a href="javascript:;" class="" style="display:none"><i class="photo"><img src="/gl/static/images/v1/img1.jpg" /></i><span>退出</span></a>
                    <a href="#" class="login" style="display: none;"><i class="icon"></i><i class="icon2"></i><span>登录</span></a>
                </li>
                <li class="download"><a href="#" style="padding-right: 15px;"><i class="icon"></i><span>下载</span></a></li>
            </ul>
        </div>
        <div class="navTab">
            <a href="/" class="tab select">首页</a>
            <a href="/zq" class="tab">专区</a>
            <a href="/user" class="tab">个人中心</a>
        </div>
    </nav>
</header>
<div class="highlight"> <div></div> </div>
<div id="main" class="cont-width">
    <div class="wrap">
        <div class="role"><img src="/gl/static/images/v1/404.png"/></div>
        <div class="absoluteleft">
            <div class="txt-tip">
                <p class="p1">404-您访问的页面不存在</p>
                <p class="p2" style="display:none"><em>3</em>秒后页面自动跳转到上一页</p>
            </div>
            <div class="backbtn">
                <a href="/" class="backhome">返回首页</a>
                <a href="javascript:history.go(-1);" class="backprev">返回上一页</a>
            </div>
            <div class="pc-add">
                <div class="gameservice">
                    <div class="pcrwm clear">
                        <div class="rwm fl"><img src="/gl/static/images/v1/rwmpc.jpg"/></div>
                        <div class="fl"><p>全民手游攻略APP</p><p class="p2">最专业的手游攻略问答社区</p><a href="#" class="pcdownload">立即下载</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<footer>
    <div class="phonebottom">
        <div class="bottom">
            <div class="social">
                <div class="rwm"><img src="/gl/static/images/v1/rwm.jpg"/></div>
                <p>最全的手游攻略</p>
            </div>
            <div class="credits">
                <p>反馈建议：service@quanmin.com</p>
                <p>客服电话：010-59974050</p>
                <p>工作时间：周一—周五 10：00 -19：00</p>
            </div>
        </div>
    </div>
    <div class="pcBottom">
        <div>
            <p>Copyright © 1996-<?php echo date('Y');?> SINA Corporation, All Rights Reserved</p>
            <p>新浪游梦创想网络技术有限公司  版权所有</p>
        </div>
    </div>
</footer>
<script>
	
    $(document).ready(function(){
        var backtime = 2;
        var time = setInterval(function(){
            $(".txt-tip .p2 em").html(backtime);
            if(backtime>0){
                backtime--;
            }else{
                clearInterval(time);
                //返回上一页事件
                window.location.href='/';
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
<script src="/gl/static/js/bootstrap.min.js"></script>

</body>
</html>
<?php } ?>