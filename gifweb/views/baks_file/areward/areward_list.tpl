<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="author" content="yzy"/>
    <meta name="renderer" content="webkit">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>打赏排行榜-土豪榜top10\包养榜top10</title>
    <link href="/gl/static/css/bootstrap.min.css" rel="stylesheet">
    <link href="/gl/static/css/dstop.css?v=1" rel="stylesheet">
    <script src="/gl/static/js/jquery-1.11.3.min.js"></script>
    <!-- no cache headers -->
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="no-cache">
    <meta HTTP-EQUIV="Cache-Control" CONTENT="no-cache, must-revalidate">
    <meta HTTP-EQUIV="expires" CONTENT="0">
    <!-- end no cache headers -->
</head>
<body>
    <div>
        <a href="/areward/areward_list/1" <{if $action ==1}>class="current"<{/if}> onclick="THTop()">土豪榜TOP10</a>
        <span>|</span>
        <a href="/areward/areward_list/2" <{if $action ==2}>class="current"<{/if}> onclick="BYTop()">包养榜TOP10</a>
    </div>

    <div class="titlename">
        <span>排名</span>
        <span>用户名</span>
        <span>打赏金额</span>
    </div>

    <div class="toplist">
        <ul>
		    <{foreach $data as $k => $v}>
            <li>
                <a href="?SRRankUid=<{$v.guid}>">
                    <span class="no">
                    	<{if $k == 1}>
                        	<img src="/gl/static/images/areward/top_1_label.png?v=2">
                        <{else if $k == 2}>
                        	<img src="/gl/static/images/areward/top_2_label.png?v=2">
                        <{else if $k == 3}>
                        	<img src="/gl/static/images/areward/top_3_label.png?v=2">
                        <{else}>
                        	<{$k}>
                        <{/if}>
                    </span>
                    <span class="photo">
                   		<{if $v.medalLevel ==1}>
                        	<img class="godimg" src="/gl/static/images/areward/god_icon.png?v=2">
                        <{/if}>
                        <img class="head" src="<{$v.headImg}>">
                    </span>
                    <span><{$v.nickName}></span>
                    <span>¥<{$v.moeny}></span>
                </a>
            </li>
            <{/foreach}>
        </ul>
    </div>

<script>
    function THTop(){

    }
    function BYTop(){
        $(".current").removeClass("current");
    }

</script>
</body>
</html>