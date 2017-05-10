<!-- 返回顶部 -->
<a href="#" class="goTop"><i class="icon"></i></a>
<script type="text/javascript">
	$(window).scroll(function(){
        gotop();
    });
    $(window).resize(function(){
    	gotop();
    });
    function gotop(){
    	var stop = $(window).scrollTop();
        var dh = $(window).height();
        if(stop>=dh){
            $(".goTop").show("fast");
        }else{
        	 $(".goTop").hide("fast");
        }
    }
    $(window).resize();
</script>
<!--底部模块-->
<div class="phonebottom">
	<div class="f-container clear">
	    <ul class="ull">
	        <li><a href="/user/?act=follow_game"><i class="icon li1"></i>关注游戏</a></li>
	        <li><a href="/user/?act=follow_article"><i class="icon li2"></i>攻略收藏</a></li>
	        <li><a href="/user/?act=question"><i class="icon li3"></i>我的提问</a></li>
	        <li><a href="/user/?act=my_message"><i class="icon li4"></i>我的通知</a></li>
	    </ul>
	    <div class="vtlline"></div>
	    <ul class="ulr">
	        <li><a href="/user/?act=answers"><i class="icon li5"></i>我的回答</a></li>
	        <li><a href="/user/?act=follow_question"><i class="icon li6"></i>我关注的问题</a></li>
	        <li><a href="/user/?act=follow_answers"><i class="icon li7"></i>答案收藏</a></li>
	        <li><a href="/help/"><i class="icon li8"></i>更多帮助</a></li>
	    </ul>
	</div>
</div>