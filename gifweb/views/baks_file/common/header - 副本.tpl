<!DOCTYPE html>
<html lang="en" class="supported">
<head>
    <meta name="baidu-site-verification" content="DdNBmQN0fX" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title><{if $seo.title}><{$seo.title}><{else}>全民手游攻略<{/if}></title>
	<meta name="keywords" content="<{if $seo.keywords}><{$seo.keywords}><{else}>手游攻略大全，专业游戏问答社区，手游问答，单机攻略，网游攻略<{/if}>"/>
	<meta name="description" content="<{if $seo.description}><{$seo.description}><{else}>全民手游攻略为玩家量身打造的一款最全的手游攻略，及最专业的问答社区。这里有梦幻西游攻略，全民飞机大战攻略，大话西游攻略，全民突击攻略，火影忍者攻略，王者荣耀攻略，热血传奇攻略，全民无双攻略，天天爱消除攻略，神武2攻略等。<{/if}>"/>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no,initial-scale=1">
    <meta name="msapplication-config" content="none">
	<!--<meta name="apple-itunes-app" content="app-id=1048841352">-->
    <!-- Bootstrap -->
    <link href="/gifweb/static/css/bootstrap.min.css?<{$smarty.const.CSS_VERSION}>" rel="stylesheet">
	<link rel="shortcut icon" href="http://www.wan68.com/gifweb/static/images/v1/16.png" type="image/x-icon" />
    <link rel="stylesheet" href="/gifweb/static/css/common.css?<{$smarty.const.CSS_VERSION}>">
	<script src="/gifweb/static/js/jquery-1.11.3.min.js"></script>
	<script src="/gifweb/static/js/bootstrap.min.js"></script>
    <!--[if lt IE 9]>
    <script src="//cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="/gifweb/static/js/respond.min.js"></script>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <script src="http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script>
    <![endif]-->
	<!--[if IE]>
    <style type="text/css"> 
    .user-detail i{vertical-align: text-top;}
    </style> 
    <![endif]-->
	<script type="text/javascript">
		/*搜索处理*/
		function searchSomeThing(){
			var url="/search/index/";
			var keyword = $("#search_keyword").val();
			window.location.href = url+keyword;
		}
	</script>
</head>
<body class="home png">
<{if $help_header !='1'}>
	<{if $isMobile}>
	    <div id="Fm9n1y8" style="display: none; z-index: 29; width: 100%; height: 50px; background-color: rgb(46, 46, 46);">
	        <div style="margin: 0 auto;width: 320px;height: 50px;zoom: 1;">
	            <img id="closefm" style="width: 13px;height: 13px;display: inline;margin: 18px 0 0 12px;float: left;" src="/gifweb/static/images/v1/top-x.png" >
	            <a href="javascript:;">
	                <img style="width: 40px;height: 40px;display: inline;float: left;margin: 5px 0 0 11px;" src="/gifweb/static/images/v1/120x120.png">
	            </a>
	            <div style='width: 155px;display: inline;float: left;text-align: center;color: #fff;font-family: " 华文黑体 细体";margin-left: 7px;'>
	                <span style="font-size: 13px;padding: 8px 0 5px 0;display: inline-block;line-height: 11px;">全民手游攻略</span>
	                <span style="font-size: 12px; display: inline-block;">玩家必备手游神奇</span>
	            </div>
	            <a href="http://e.games.sina.com.cn/statistic/index/?url=541850ce86ae32639884a233ffa936d1" class="J_ping" style="font-size: 12px; color: #E7E6E6; background-color: #5677fc; border-radius: 4px; width: 65px; height: 30px; line-height: 30px; text-align: center; float: right; margin: 10px 12px 0 0;">
	                <span>立即下载</span>
	            </a>
	        </div>
	    </div>
	<{/if}>
<{/if}>

<header>
    <nav class="cont-width">
        <div class="clear" id="headtop">
			<a href='/'>
				<div class="logo"><i class="icon"></i><i class="icon2"></i></div>
			</a>
            <ul>
                <li class="search">
                    <a href="/search?page=" class="phonesearch"><i class="icon"></i></a>
                    <div class="pcsearch">
                        <div class="inputback">
                    	<form action="/search" method="get" name="searchForm" onSubmit="return checkNavSearch();" >
		                    <input type="text" name="search_keyword" id="header_keyword" placeholder="搜索游戏、攻略、问答"  maxlength="40" class="form-control" autocomplete="off">
		                    <input type="hidden" name="type" value="<{$type}>" >
                			<input type="hidden" name="gameId" value="<{$data.gameId}>" >
		                    <input type="submit"  value="" class="iconpc search-btn" ></i>
                            <!-- <span class="deletebtn"></span> -->
		                </form>
                        </div>
                    </div>
                </li>
                <li class="ask">
                    <a href="/question/ask/<{$data.askgid}>"><i class="icon"></i><i class="icon2"></i><span>提问</span>
                        <!--
						<div class="askQuestion">
                            <i class="icon2"></i>
                            <input type="text" placeholder="游戏、攻略、问答" class="form-control"><div class="asksearch"><i disabled="disabled" name="submit" class="icon2 search-btn"></i>></div>
                            <ul>
                                <li>关于炉石传说我想知道....</li>
                                <li>关于炉石传说我想知道....</li>
                                <li>关于炉石传说我想知道....</li>
                                <li>关于炉石传说我想知道....</li>
                                <li>关于炉石传说我想知道....</li>
                            </ul>
                        </div>
						-->
                    </a>
                </li>
                <li class="login">
					<{if $userinfo.uid}>
					<a href="/user/" class=""><i class="photo"><img src="<{$userinfo.avatar}>" /></i></a><a href="/user/logout?backUrl=<{$back_url}>" class=""><span>退出</span></a>
					<{else}>
                	<a href="/user/login?backUrl=<{$back_url}>" class="login"><i class="icon"></i><i class="icon2"></i><span>登录</span></a>
					<{/if}>
                </li>
                <li class="download"><a href="http://e.games.sina.com.cn/statistic/index/?url=541850ce86ae32639884a233ffa936d1" style="padding-right: 15px;"><i class="icon"></i><span>下载</span></a></li>
            </ul>
        </div>
        <div class="navTab">
            <a href="/" class="tab <{if $data.navflag eq 'index'}>select<{/if}>">首页</a>
            <a href="/zq" class="tab <{if $data.navflag eq 'zq'}>select<{/if}>">专区</a>
            <a href="/user" val="user" class="tab <{if $navflag eq 'user'}>select<{/if}>">个人中心</a>
        </div>
    </nav>
</header>
<div class="highlight"> <div></div> </div>
<script>
    
</script>
<script>
    if(!getCookie("downtip") || !(getCookie("downtip") == "true")){
        $("#Fm9n1y8").show();
    }
    //关闭下载提示
    $("#closefm").on("click",function(){
        setCookie("downtip","true");
        $("#Fm9n1y8").remove();
    });
    //首页、专区、个人中心导航  
    $(".navTab a").on("click",function(){
        var uid = <{$userinfo.uid|default:0}>;
        if($(this).attr("val") == "user"){
            if(uid){
                $(".navTab a").removeClass("select");
                $(this).addClass("select");
            }else{
               $(this).removeClass("select");
            }
        }else{
            $(".navTab a").removeClass("select");
            $(this).addClass("select");
        }
        
    });
    
    function checkNavSearch(){
    	var header_keyword = $("#header_keyword").val();
    	if(header_keyword == ''){
    		myPop('搜索内容不能为空');
    		return false;
    	}else{
    		return true;
    	}
    }

    //设置cookie
    function setCookie(name,value)
    {
        var Days = 1; 
        var exp = new Date(); 
        exp.setTime(exp.getTime() + Days*24*60*60*1000); 
        document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString(); 
    }
    function getCookie(name)
    {
        var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
        if(arr=document.cookie.match(reg))
            return unescape(arr[2]);
        else
            return null;
    }
    function delCookie(name)
    {
        var exp = new Date();
        exp.setTime(exp.getTime() - 1);
        var cval=getCookie(name);
        if(cval!=null)
        document.cookie= name + "="+cval+";expires="+exp.toGMTString();
    }

</script>