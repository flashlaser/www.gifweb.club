<!--通用底部-->
<footer>
    <div class="phonebottom">
        <div class="bottom">
            <div class="social">
                <div class="rwm"><img src="/gl/static/images/v1/rwm.png"/></div>
                <p>最全的手游攻略</p>
            </div>
            <div class="credits">
                <p>客服QQ：2786799258</p>
                <p>客服Q群：249095807</p>
                <p>工作时间：周一-周五10:00-19:00</p>
            </div> 
        </div>
    </div>
    <{if $link_url ==1}>
    <div class="pcBottom">
        <div class="friendLink">友情链接：
            <a href="http://games.sina.com.cn">新浪游戏</a><a href="http://www.97973.com">97973手游网</a>
        </div>
        <hr style="  border-top: 1px solid #363636;width: 1000px;">
        <div>
            <p>Copyright © 2016 NEW WORLD Corporation, All Rights Reserved</p>
            <p>安徽新游创梦网络技术有限公司 版权所有</p>
        </div>
    </div>
    <{/if}>
</footer>
<div class="popTip" style='display:none;'><p>操作成功</p></div>
<script type="text/javascript">
    $(document).ready(function(){
        //对placeholder的支持
        if( !('placeholder' in document.createElement('input')) ){

            $('input[placeholder],textarea[placeholder]').each(function(){
                var that = $(this),
                    text= that.attr('placeholder');
                if(that.val()===""){
                    that.val(text).addClass('placeholder');
                }
                that.focus(function(){
                    if(that.val()===text){
                        that.val("").removeClass('placeholder');
                    }
                })
                    .blur(function(){
                        if(that.val()===""){
                            that.val(text).addClass('placeholder');
                        }
                    })
                    .closest('form').submit(function(){
                        if(that.val() === text){
                            that.val('');
                        }
                    });
            });
        }
    });
//pop弹出函数
function myPop(){
	var msg = arguments[0] ? arguments[0] : '操作成功'; //第一个参数
	var timelen = arguments[1] ? arguments[1] : 1; //第二个参数
	
	$('.popTip').html('<p>' + msg + '</p>').fadeIn("slow");
	setTimeout(function(){$('.popTip').fadeOut('slow');},timelen * 1000);
}
/*百度统计*/
var _hmt = _hmt || [];
(function() {
	var hm = document.createElement("script");
	hm.src = "//hm.baidu.com/hm.js?457beebb622d3780f367cfa604ba91a9";
	var s = document.getElementsByTagName("script")[0]; 
	s.parentNode.insertBefore(hm, s);
})();
</script>
</body>
</html>