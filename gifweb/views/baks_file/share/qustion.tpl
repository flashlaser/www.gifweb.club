<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="Cache-Control" content="no-cache" >
        <meta id="viewport" name="viewport" content="width=device-width, initial-scale=1.0,minimum-scale=1.0, maximum-scale=1.0">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link rel="apple-touch-icon" href="http://u1.sinaimg.cn/3g/image/upload/0/110/176/19509/64477c90.png" />

        <{if $answer_info_empty}><{else}><title>“<{$game_name}>”问答</title><{/if}>
        <link rel="stylesheet" href="/gl/static/css/share.css?v=1.5"/>
        <script src="/gl/static/js/zepto.js"></script>
    </head>
    <body>
    <!--header-->
    <{if $answer_info_empty}>
        <div class="conent">
            <div class="none"><p>问题、答案不存在或者已被关闭</p></div>
        </div>
    <{else}>
        <header>
            <div class="quest"><div class="icon2"></div><p><a href="http://gl.games.sina.com.cn/share/detail?qid=<{$qid}>"><{$q_content}></a></p></div>
            <div class="header">
                <span class="photo fl"><img src="<{$a_info.u_info.avatar}>"/><span></span></span>
                <div class="h-box fl">
                    <p><{$a_info.u_info.nickname}></p>
                    <span class="time"><{if $a_info.update_time > $a_info.create_time}>编辑于：<{$a_info.update_time_u}><{else}>发布于：<{$a_info.create_time_c}><{/if}></span>
                </div>
                <{if $a_info.u_info.level}><div class="level">LV<{$a_info.u_info.level}></div><{/if}>
                <div class="sep"></div>
                <div class="ballot"><span>赞</span> <{$a_info.mark_up_rank_0_count + $a_info.mark_up_rank_1_count + $a_info.mark_up_virtual_count}></div>
            </div>
        </header>
        <section class="conent">
            <div class="icon1"></div>
            <div class="c-box1">
                <{$a_content}>
            </div>
        </section>
    <{/if}>
    <!--footer-->
    <div class="footer">
        <div>
            <a href="http://e.games.sina.com.cn/statistic/index/?url=541850ce86ae32639884a233ffa936d1" class="app fl"></a>
            <p>全民手游攻略APP，最专业的手游攻略问答应用</p>
        </div>
        <div class="close"></div>
    </div>
    <script>
        $(".footer").click(function(){window.location.href="http://e.games.sina.com.cn/statistic/index/?url=541850ce86ae32639884a233ffa936d1";});
        //关闭
        $(".footer .close").click(function(){
            $(".footer").hide();
            return false;
        })
    </script>
    </body>
</html>
