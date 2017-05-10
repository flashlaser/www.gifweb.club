<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta property="wb:webmaster" content="f2683c166ddfc491" />
    <meta name="keywords" content="新闻  news 免费 推荐 搞笑  美女">
    <meta name="description" content="90后人群精心打造的app，户任何时间和状态下轻松愉快的阅读需求">
    <!-- no cache headers -->
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="no-cache">
    <meta HTTP-EQUIV="Cache-Control" CONTENT="no-cache, must-revalidate">
    <meta HTTP-EQUIV="expires" CONTENT="0">
    <!-- end no cache headers -->
    <title><{$data.game_name}>攻略下载</title>
    <link type="image/x-icon" rel="shortcut icon" href="/gl/static/images/v1/16.png" />
    <link href="/gl/static/css/download/swiper.3.1.2.min.css">
    <link href="/gl/static/css/download/main2.css" rel="stylesheet">

    <script src="//cdn.bootcss.com/jquery/2.1.4/jquery.min.js"></script>
    
    <script>
		function getDevice2(url){
			if (/iphone|nokia|sony|ericsson|mot|samsung|sgh|lg|philips|panasonic|alcatel|lenovo|cldc|midp|wap|android|iPod|iPad/i.test(navigator.userAgent.toLowerCase())) {
				  window.location.href= url; 
			}
		}
    </script>
</head>
<body>
<article>
    <div class="content-view">

    </div>
</article>
<section>
    <script type="text/template" id="wap">
        <div class="wap-page swiper-container swiper-container-vertical">
            <div class="share">
                <span onclick="share('weibo')" title="分享微博">
                    <img src="/gl/static/images/download/wap/weibo_share.png">
                </span>
                <span onclick="share('qzone')" title="分享到qq空间">
                    <img src="/gl/static/images/download/wap/Qzone_share.png">
                </span>
                <span class="weixin">
                    <img src="/gl/static/images/download/wap/relation_share.png">
                </span>
                <div class="erpop" style="display: none">
                    <img src="<{gl_img_url($data.wx_code)}>">
                </div>
            </div>
            <div class="swiper-wrapper ">
            
        		<{foreach $data.img_list as $k => $v}>
	                	<{if $v}>
	                <div class="wap-header wap-card swiper-slide">
		                    <img src="<{gl_img_url($v)}>">
		                    <{if $k==1}>
			                    <a href="javascript:void(0)" class="wap-down-ios"  style="display:none;" ></a>
			                    <a href="javascript:void(0)" onclick="getDevice2('<{$data.jump_url}>');" class="wap-down-android"></a>
			                    <img id="iosma" src="/gl/static/images/download/wap/gongzhonghao.png" alt="ios二维码" class="erweima">
			                    <img id="abdroidma" src="http://store.games.sina.com.cn/<{$data.t_d_code}>" alt="二维码" class="erweima">
		                    <{/if}>
	                </div>
		                    <{/if}>
        		<{/foreach}>
        
                <div class="wap-footer swiper-slide">
                    <table><tr></tr>
                    <tr><td>
                        <a href="http://www.wan68.com/download" class="wap-contact" target="_blank">
                            <span class="wap-contact-icon"></span>全民手游攻略
                        </a><br>
                        <img src="/gl/static/images/download/wap/gongzhonghao.png" class="wap-erwm">
                        <p class="weixinNum">微信公众号 全民手游攻略</p>
                        <div>
                            <p>Copyright © 2016 NEW WORLD</p>
                            <p>Corporation, All Rights Reserved</p>
                            <p>安徽新游创梦网络技术有限公司 版权所有<p>
                        </div>
                    </tr></td></table>
                </div>
            </div>
        </div>
    </script>
</section>

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<!--<script src="/gl/static/js/jquery.min.js"></script>-->
<script src="/gl/static/js/download/swiper.3.1.2.jquery.min.js"></script>
<script src="/gl/static/js/download/main.js?v=2"></script>
<script>
    //分享
    function share(p){
        var _t = encodeURI('<{$data.game_name}>攻略下载');
        var _url = encodeURIComponent(document.location);
        var _appkey = encodeURI(""); //appkey
        var _pic = encodeURI('http://n.sinaimg.cn/c6abfe21/20151019/gl_share.png'); //图片
        var _site = 'www.wan68.com/download'; //你的网站地址
        var _params = '?url='+_url+'&appkey='+_appkey+'&site='+_site+'&pic='+_pic+'&title='+_t;
        var _qweibo = 'http://v.t.qq.com/share/share.php'+_params;
        var _qzone = 'http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey'+_params;
        var _weibo = 'http://service.t.sina.com.cn/share/share.php'+_params;
        var _kaixin = 'http://www.kaixin001.com/repaste/bshare.php'+_params;
        var _renren = 'http://share.renren.com/share/buttonshare.do'+_params;
        var _pengyou ='http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey'+_params;
        var _baidu ='http://www.baidu.com/home/page/show/url'+_params;
        var _wangyi='http://t.163.com/article/user/checkLogin.do'+_params;
        if ('qweibo' == p){
            var url = _qweibo;
        }else if('qzone' == p){
            var url = _qzone;
        }else if('weibo' == p){
            var url = _weibo;
        }else if('kaixin' == p){
            var url = _kaixin;
        }else if('renren' == p){
            var url = _renren;
        }else if('pengyou' == p){
            var url = _pengyou;
        }else if('baidu' == p){
            var url = _baidu;
        }else if('wangyi' == p){
            var url = _wangyi;
        }
        window.open(url,'_blank');
    }

    $(".weixin").hover(function(){
        $(".erpop").show();
    },function(){
        $(".erpop").hide();
    })

</script>
</body>
</html>
