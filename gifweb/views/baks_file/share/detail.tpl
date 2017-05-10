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
<{if $answer_info_empty}>
<div class="conent">
    <div class="none"><p>问题、答案不存在或者已被关闭</p></div>
</div>
<{else}>
<!--header-->
    <div class="head">
        <span class="photo fl"><img src="<{$u_info.avatar}>"/><span></span></span>
        <div class="h-box fl">
            <p><{$u_info.nickname}></p>
            <input type="hidden" id ='qid' value="<{$q_info.qid}>">
            <span class="time"><{if $q_info.update_time > $q_info.create_time}>编辑于：<{$q_info.update_time_u}><{else}>发布于：<{$q_info.create_time_c}><{/if}></span>
        </div>
        <{if $u_info.level}><div class="level">LV<{$u_info.level}></div><{/if}>
    </div>
<div class="conent">
    <div class="c-box1">
       <{$qc_info}>
    </div>
    <div class="c-box2">
        <div class="b2-txt fl"><span>回答<{$q_info.normal_answer_count + $q_info.hot_answer_count}></span><em>|</em><span>关注<{$q_info.follow_count + $q_info.virtual_follow_count}></span></div>
        <!--<a class="follow fr" href="">关注</a>-->
    </div>
    <div class="c-box3">
        <{if $ah_info}>
        <div class="hot">热门回答</div>
        <{/if}>
        <{foreach $ah_info as $k => $v}>
        <div class="role">
            <span class="photo"><img src="<{$v.u_info.avatar}>"/><span></span></span>
            <div class="h-box">
                <p><{$v.u_info.nickname}></p>
            </div>
            <{if $v.u_info.level}><div class="level">LV<{$v.u_info.level}></div><{/if}>
            <div class="ballot"><span>赞</span> <{$v.mark_up_rank_0_count + $v.mark_up_rank_1_count + $v.mark_up_virtual_count}></div>
        </div>
        <div class="answer">
            <div class="answer-txt"><a href="http://gl.games.sina.com.cn/share/index?aid=<{$v.aid}>"  style='color:#333;'><{$v.a_content}></a></div>
            <{if $v.a_img_count > 0}>
            <div class="a-message"><i></i><span><{$v.a_img_count}></span></div>
            <{/if}>
            <span class="begintime"><{$v.ctime}></span>
        </div>
        <{/foreach}>
    </div>
    <div class="c-box3" id="newanswer" >
        <{if $a_info}>
        <div class="hot">最新回答</div>
        <{/if}>
        <{foreach $a_info as $k => $v}>
        <div class="role">
            <span class="photo fl"><img src="<{$v.u_info.avatar}>"/><span></span></span>
            <div class="h-box fl">
                <p><{$v.u_info.nickname}></p>
            </div>
            <{if $v.u_info.level}><div class="level">LV<{$v.u_info.level}></div><{/if}>
            <div class="ballot"><span>赞</span> <{$v.mark_up_rank_0_count + $v.mark_up_rank_1_count + $v.mark_up_virtual_count}></div>
        </div>
        <div class="answer">
            <div class="answer-txt"><a href="http://gl.games.sina.com.cn/share/index?aid=<{$v.aid}>"  style='color:#333;'><{$v.a_content}></a></div>
            <{if $v.a_img_count > 0}>
            <div class="a-message"><i></i><span><{$v.a_img_count}></span></div>
            <{/if}>
            <span class="begintime"><{$v.ctime}></span>
        </div>
        <{/foreach}>
    </div>
    <{if $a_info_empty == 2 }>
    <div class="none"><p>还没有人回答哦，</p><p>快来成为第一位答主吧~</p></div>
    <{/if}>
</div>
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
