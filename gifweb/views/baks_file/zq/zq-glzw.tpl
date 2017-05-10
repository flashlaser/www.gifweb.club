<{include file="../common/header.tpl"}>
<link rel="stylesheet" type="text/css" href="/gl/static/css/swiper.min.css">
<link rel="stylesheet" href="/gl/static/css/zqzw.css">
<script type="text/javascript" src="http://n.sinaimg.cn/97973/ScrollPic.js"></script>
<script type="text/javascript" src="/gl/static/js/support.js"></script>
<script type="text/javascript" src="/gl/static/js/swiper3.1.0.jquery.min.js"></script>
<div id="main" class="cont-width">
    <div class="row">
        <div class="leftPart">
            <div class="content">
                <div class="glzw">
                    <div class="zwTitle">
                        <h2><{$data['abstitle']}></h2>
                        <div class="dateSource"><span><{$data['source']}></span>&nbsp;&nbsp;<span><{$data['updateTime']}></span>
                            <div class="comment">
                                <a href="javascript:;" onclick='dodownload();'><i class="zqzwicon"></i><span>评论</span></a>
                            </div>
                            <div class="row pcactiondiv">
								<{if $data.guid}>
                                <a <{if $data['praised']}>onclick='doajax(1,0)'<{else}>onclick='doajax(1,1)'<{/if}> href="javascript:;" class="col-md-2 use <{if $data['praised']}>active<{/if}>"><i class="zqzwpc i1"></i><span>受用</span></a>
                                <a <{if $data['treaded']}>onclick='doajax(2,0)'<{else}>onclick='doajax(2,1)'<{/if}> href="javascript:;" class="col-md-2 useless <{if $data['treaded']}>active<{/if}>"><i class="zqzwpc i2"></i><span>不好</span></a>
                                <a <{if $data['collected']}>onclick='doajax(3,1)'<{else}>onclick='doajax(3,0)'<{/if}> href="javascript:;" class="col-md-2 collect <{if $data['collected']}>active<{/if}>"><i class="zqzwpc i3"></i><span class='collectmsg'><{if $data['collected']}>已<{/if}>收藏</span></a>
                                <{else}>
									<a href="javascript:;" onclick='gologin()' class="col-md-2 use"><i class="zqzwpc i1"></i><span>受用</span></a>
									<a href="javascript:;" onclick='gologin()' class="col-md-2 useless"><i class="zqzwpc i2"></i><span>不好</span></a>
									<a href="javascript:;" onclick='gologin()' class="col-md-2 collect"><i class="zqzwpc i3"></i><span>收藏</span></a>
								<{/if}>
								<a href="javascript:;" onclick='dodownload();' class="col-md-2 coet"><i class="zqzwpc i4"></i><span>评论</span></a>
                                <a href="javascript:;" class="col-md-2 share"><i class="zqzwpc i5"></i><span>分享</span></a>
                            </div>
                            <div class="row pcshareList" style='display:none;'>
								<script>
									function getShareMessage(){
										return '<{$data['abstitle']}>';
									}
								</script>
                                <i class="zqzwpc sj"></i>
                                <a href="javascript:void((function(s,d,e){try{}catch(e){}var%20f='http://v.t.sina.com.cn/share/share.php?',u='<{$data.qshareurl}>',p=['url=',e(u),'&title=<{$data['abstitle']}>&appkey=691988791&pic=<{$data.share_imgurl}>'].join('');function a(){if(!window.open([f,p].join(''),'mb'))u.href=[f,p].join('');};if(/Firefox/.test(navigator.userAgent)){setTimeout(a,0)}else{a()}})(screen,document,encodeURIComponent));" title="分享到新浪微博"  class="col-xs-4 "><i class="zqzwpc i1"></i></a>
                                <!-- <a href="#" class="col-xs-2 "><i class="zqzwpc i2"></i></a>-->
                                <a data='qq' href="javascript:(function(){window.open('http://connect.qq.com/widget/shareqq/index.html?url='+ encodeURIComponent(location.href)+ '&desc='+getShareMessage()+'&title=全民手游攻略分享&pics=<{$data.share_imgurl}>&site=全民手游攻略&summary=我在全民手游攻略给你分享，快来看看吧!','分享至QQ好友', '');})()" class="col-xs-4"><i class="zqzwpc i3"></i></a>
                                <!-- <a href="#" class="col-xs-2 "><i class="zqzwpc i4"></i></a>-->
                                <a href="javascript:(function(){window.open('http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url='+ encodeURIComponent(location.href)+ '&title='+getShareMessage()+'&title=全民手游攻略分享&pics=<{$data.share_imgurl}>&site=全民手游攻略&summary=我在全民手游攻略给你分享，快来看看吧!','分享至QQ空间', '');})()" data='qzone' title="分享至QQ空间" class="col-xs-4 "><i class="zqzwpc i5"></i></a>

							</div>
                        </div>
                    </div>
                    <hr class="f0line">
					<script src="http://games.sina.com.cn/307/2014/5/yibu.js"></script>
					<script type="text/javascript">
						/**
						 * 全局数据
						 * video_url 视频链接
						 * channel 频道
						 * newsid 新闻id
						 * group 默认为0
						 */
						var ARTICLE_DATA = {
							//评论微博转发视频地址
							video_url:'',
							//评论微博转发图片地址，可置空会自动取图
							pic_url:'',
							//频道
							channel:'yx',
							//新闻id
							newsid:'comos-<{$data['newsid']}>',
							//组，默认为0
							group:'0',
							//是否固定评论框，默认为1固定,ipad,iphone不固定
							cmntFix:0,
							//发布时间
							pagepubtime:'2014-12-08',
							source: '新浪游戏',
							sourceUrl: 'http://games.sina.com.cn/',
							channelId: 2,
							autoLogin:1,
							// 最新评论第一页评论数
							firstPageNum:5,
							//分页评论数
							pageNum:20,
							//热帖评论数
							hotPageNum:5,
							// 最多点击“更多”次数
							clickMoreNum:1,
							encoding : "utf-8"
						};
						var ARTICLE_JSS = {
							jq:'http://i0.sinaimg.cn/dy/js/jquery/jquery-1.7.2.min.js',
							sab:'http://ent.sina.com.cn/js/470/20130205/sab.js',
							sinalib:'http://news.sina.com.cn/js/87/20110714/205/sinalib.js',
							subshow:'http://i3.sinaimg.cn/ty/sinaui/subshow/subshow2012070701.min.js',
							weiboAll:'http://news.sina.com.cn/js/268/2011/1110/16/weibo-all.js',
							sdfigure:'http://ent.sina.com.cn/js/470/20121129/sdfigure_v2.js',
							hdfigure:'http://news.sina.com.cn/js/87/20121218/hdfigure_v2.js',
							sinflash:'http://i1.sinaimg.cn/home/sinaflash.js',
							weiboCard:'http://ent.sina.com.cn/js/20120914/weibocard.js',
							guess:'http://ent.sina.com.cn/js/470/20130205/guess.js',
							allcont:'http://fashion.sina.com.cn/js/4/20130912/icontent/allcontent_new.js',
							shareOnWeibo:'http://news.sina.com.cn/js/87/20111011/227/shareonweibo.js',
							weiboCard2013:'http://tech.sina.com.cn/js/717/20131127/content/weibocard2013.js',
							wbUsersRec:'http://news.sina.com.cn/js/87/20140623/wbUsesRec.js'
						};
					</script>
					
					
                    <div class="zw-cont">
						<{$data['content']}>
                    </div>

                </div>
                <div class="row actionBox clear">
                    <hr class="seprLine">
					<{if $data.guid}>
						<a <{if $data['praised']}>onclick='doajax(1,0)'<{else}>onclick='doajax(1,1)'<{/if}> href="javascript:;" class="col-xs-3 col-md-3 use <{if $data['praised']}>active<{/if}>"><i class="zqzwicon i1"></i><span>很受用</span></a>
						<a <{if $data['treaded']}>onclick='doajax(2,0)'<{else}>onclick='doajax(2,1)'<{/if}> href="javascript:;" class="col-xs-3 col-md-3 useless <{if $data['treaded']}>active<{/if}>"><i class="zqzwicon i2 "></i><span>然并卵</span></a>
						<a <{if $data['collected']}>onclick='doajax(3,1)'<{else}>onclick='doajax(3,0)'<{/if}> href="javascript:;" class="col-xs-3 col-md-3 collect <{if $data['collected']}>active<{/if}>"><i class="zqzwicon i3"></i><span class='collectmsg'><{if $data['collected']}>已<{/if}>收藏</span></a>
					<{else}>
						<a href="javascript:;" onclick='gologin()' class="col-xs-3 col-md-3 use"><i class="zqzwicon i1"></i><span>很受用</span></a>
						<a href="javascript:;" onclick='gologin()' class="col-xs-3 col-md-3 useless"><i class="zqzwicon i2"></i><span>然并卵</span></a>
						<a href="javascript:;" onclick='gologin()' class="col-xs-3 col-md-3 collect"><i class="zqzwicon i3"></i><span>收藏</span></a>
					<{/if}>
				   <a href="javascript:;" onclick="openpop();" class="col-xs-3 col-md-3 share"><i class="zqzwicon i4"></i><span>分享</span></a>
                </div>
				<!--
                <div class="addmore"><i class="icon"></i>更多</div>
				-->
				<div class="popMobileshare">
					<div class="mobilesharebox">
						<div class="mobileshare" id="mobileshare">
	                        <div class="swiper-wrapper">
	                            <section class="swiper-slide">
	                                <div class="mobileshareIcons clear">
	                                    <a href="javascript:void((function(s,d,e){try{}catch(e){}var%20f='http://v.t.sina.com.cn/share/share.php?',u='<{$data.qshareurl}>',p=['url=',e(u),'&title=<{$data['abstitle']}>&appkey=691988791&pic=<{$data.share_imgurl}>'].join('');function a(){if(!window.open([f,p].join(''),'mb'))u.href=[f,p].join('');};if(/Firefox/.test(navigator.userAgent)){setTimeout(a,0)}else{a()}})(screen,document,encodeURIComponent));" title="分享到新浪微博"  ><div class="mobileshareIcon"><i class="icon i1"></i><p>新浪微博</p></div></a>
										<!--
	                                    <a href="javascript:;"><div class="mobileshareIcon"><i class="icon i2"></i><p>微信</p></div></a>
										-->
	                                    <a data='qq' href="javascript:mb_share('<{$data['abstitle']}>',location.href)"><div class="mobileshareIcon"><i class="icon i3"></i><p>腾讯QQ</p></div></a>
										<a href="javascript:(function(){window.open('http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url='+ encodeURIComponent(location.href)+ '&title='+getShareMessage()+'&title=全民手游攻略分享&pics=<{$data.share_imgurl}>&site=全民手游攻略&summary=我在全民手游攻略给你分享，快来看看吧!','分享至QQ空间', '');})()" data='qzone' title="分享至QQ空间"><div class="mobileshareIcon"><i class="icon i5"></i><p>QQ空间</p></div></a>
	                                    <a href="javascript:;"><div class="mobilejustify_fix"></div></a>
	                                </div>
	                            </section>
	                        </div>
							<!--
	                        <div class="swiper-pagination"></div>
							-->
	                    </div>
						
					</div>
                </div>
            </div>
        </div>
		<!--pc右侧公共-->
		<{include file="../common/moudle_pc_right.tpl"}>
    </div>
</div>
<{include file="../common/moudle_footer.tpl"}>
<script type="text/javascript" src="/gl/static/js/share.js"></script>
<script>
	$(document).ready(function(){
		//pc 分享
	    $(".pcactiondiv .share").click(function(){
	        $(".pcshareList").toggle(200);
	    });
	    //mobile分享 浮动设置
	     changeFilter();
	    //
	    if($("embed").length>0){
	    	$("embed").attr("wmode","transparent");
	    	var obj=document.createElement("param");
           	obj.name="wmode";
           	obj.value="transparent";

	    	//$('<param name="wmode" value="transparent" />').insertBefore($("embed"));
	    	$(obj).insertBefore($("embed"));
	    }
	});
    $(window).scroll(function(){
        changeFilter();
    });
    $(window).resize(function(){
    	changeFilter();
    });
    function changeFilter(){
    	var stop = $(window).scrollTop();
        var dh = $(window).height();
        var ft = $(".f-container").offset();
        if(ft.top<=stop+dh){
            $("div.actionBox ").removeClass("fixter");
        }else{
        	$("div.actionBox ").addClass("fixter");
        }
    }
    //打开mobile分享
    function openpop(){
		$(".popMobileshare").show("fast");
    }
   
</script>


<script>
	//踩，赞，收藏操作
	function doajax(flag,type){
		switch(flag){
			case 1: //执行赞/取消赞操作
				if(<{$data.is_ban}>){
					confirm_ban();
					return false;
				}
			
				var url = "/ajax_fun/raiders_praise_operate/<{$data['newsid']}>/" + type;
				break;
			case 2: //执行踩/取消踩操作
				if(<{$data.is_ban}>){
					confirm_ban();
					return false;
				}
				
				var url = "/ajax_fun/raiders_cai_operate/<{$data['newsid']}>/" + type;
				break;
			case 3: //执行收藏/取消收藏操作
				var url = "/follow/gl_collect?mark=<{$data['newsid']}>&action=" + type;	
				break;
			default:
				myPop('操作标识丢失');
				//alert('');
				return false;
				break;
		}
		
		gl_api_fun(url,flag,type);
	}

	function gl_api_fun(api_url,flag,type){
		$.ajax({
			'async' : true,// 使用异步的Ajax请求
			'type' : "get",
			'cache':false,
			'url' : api_url,
			'dataType' : "json",
			success : function(e){
				//console.log(e);
				if(e.result == 200){
					switch(flag){
						case 1: //执行赞/取消赞操作
							if(type == 1){ //执行赞
								myPop('操作成功');
								$('.use').addClass('active');
								$('.use').attr('onclick','doajax(1,0)');
								
								//关联操作
								$('.useless').removeClass('active');
								$('.useless').attr('onclick','doajax(2,1)');
							}else{
								myPop('已取消');
								$('.use').removeClass('active');
								$('.use').attr('onclick','doajax(1,1)');
							}
							
							break;
						case 2: //执行踩/取消踩操作
							if(type == 1){
								myPop('操作成功');
								$('.useless').addClass('active');
								$('.useless').attr('onclick','doajax(2,0)');
								
								//关联操作
								$('.use').removeClass('active');
								$('.use').attr('onclick','doajax(1,1)');
							}else{
								myPop('已取消');
								$('.useless').removeClass('active');
								$('.useless').attr('onclick','doajax(2,1)');
							}
							
							break;
						case 3: //执行收藏/取消收藏操作
							if(type != 1){
								$('.collect').addClass('active');
								$('.collect').attr('onclick','doajax(3,1)');
								$('.collectmsg').text('已收藏');
								myPop('收藏成功');
							}else{
								$('.collect').removeClass('active');
								$('.collect').attr('onclick','doajax(3,0)');
								$('.collectmsg').text('收藏');
								myPop('取消成功');
							}
							break;
						default:
							//alert('操作标识丢失');
							myPop('操作标识丢失');
							return false;
							break;
					}
				} else {
					myPop('操作失败');
					//alert('操作失败');
				}
			}
		});
	}
	
	//跳转到登录页
	function gologin(){
		var url = "/user/login?backUrl=" + location.href;
		window.location.href = url;
	}
</script>
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog">
        <div class="modal-content confirm" style="width: 100%;">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p>One fine body&hellip;</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>
<script>
	function dodownload(){
		var del_message = "安装全民手游攻略app，就可以对您感兴趣的答案进行评论";
		$('.modal-body').text(del_message);
		
		$('#myModal .modal-footer .btn-default').text('取消');
		$('#myModal .modal-footer .btn-primary').text('安装').show();
		
		$('#myModal .modal-footer .btn-primary').click(function(){
			$("#myModal").modal('hide');
			var del_url = '/download/';
			window.open(del_url); 
			
		});
		
		$("#myModal").modal('show');
	}
	
	function confirm_ban(){
		var del_message = "您的帐号已被管理员严禁发言，有问题请在意见反馈中提交，您还可以加客服QQ：2271250263或客服Q群：460025819进行咨询";
		$('.modal-body').text(del_message);
		
		$('#myModal .modal-footer .btn-default').text('我知道了');
		$('#myModal .modal-footer .btn-primary').text('确定').hide();
		
		$('#myModal .modal-footer .btn-primary').click(function(){
			$("#myModal").modal('hide');
			go_back();
		});
		
		$("#myModal").modal('show');
	}
</script>

<script>
    var Base64 = {_keyStr: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode: function(a) {
        var b, c, d, e, f, g, h, i = "", j = 0;
        for (a = Base64._utf8_encode(a); j < a.length; )
            b = a.charCodeAt(j++), c = a.charCodeAt(j++), d = a.charCodeAt(j++), e = b >> 2, f = (3 & b) << 4 | c >> 4, g = (15 & c) << 2 | d >> 6, h = 63 & d, isNaN(c) ? g = h = 64 : isNaN(d) && (h = 64), i = i + this._keyStr.charAt(e) + this._keyStr.charAt(f) + this._keyStr.charAt(g) + this._keyStr.charAt(h);
        return i
    },decode: function(a) {
        var b, c, d, e, f, g, h, i = "", j = 0;
        for (a = a.replace(/[^A-Za-z0-9\+\/\=]/g, ""); j < a.length; )
            e = this._keyStr.indexOf(a.charAt(j++)), f = this._keyStr.indexOf(a.charAt(j++)), g = this._keyStr.indexOf(a.charAt(j++)), h = this._keyStr.indexOf(a.charAt(j++)), b = e << 2 | f >> 4, c = (15 & f) << 4 | g >> 2, d = (3 & g) << 6 | h, i += String.fromCharCode(b), 64 != g && (i += String.fromCharCode(c)), 64 != h && (i += String.fromCharCode(d));
        return i = Base64._utf8_decode(i)
    },_utf8_encode: function(a) {
        a = a.replace(/\r\n/g, "\n");
        for (var b = "", c = 0; c < a.length; c++) {
            var d = a.charCodeAt(c);
            128 > d ? b += String.fromCharCode(d) : d > 127 && 2048 > d ? (b += String.fromCharCode(d >> 6 | 192), b += String.fromCharCode(63 & d | 128)) : (b += String.fromCharCode(d >> 12 | 224), b += String.fromCharCode(d >> 6 & 63 | 128), b += String.fromCharCode(63 & d | 128))
        }
        return b
    },_utf8_decode: function(a) {
        for (var b = "", c = 0, d = c1 = c2 = 0; c < a.length; )
            d = a.charCodeAt(c), 128 > d ? (b += String.fromCharCode(d), c++) : d > 191 && 224 > d ? (c2 = a.charCodeAt(c + 1), b += String.fromCharCode((31 & d) << 6 | 63 & c2), c += 2) : (c2 = a.charCodeAt(c + 1), c3 = a.charCodeAt(c + 2), b += String.fromCharCode((15 & d) << 12 | (63 & c2) << 6 | 63 & c3), c += 3);
        return b
    }};
    function b(a, b) {
        for (var c in b)
            a += -1 === a.indexOf("?") ? "?" + c + "=" + b[c] : "&" + c + "=" + b[c];
        return a
    }
    function c(a, b) {
        var c;
        for (var d in b)
            c = new RegExp(d + "=" + b[d], "g"), a = a.replace(c, "");
        return a
    }
    function mb_share(titile, shareulr){
        window.location.href = b("mqqapi://share/to_fri?src_type=web&version=1&file_type=news", {share_id: "1101685683",title: Base64.encode(titile),thirdAppDisplayName: Base64.encode("全民手游攻略"),url: Base64.encode(shareulr),img:"http://www.wan68.com/gl/static/images/foot_logo.png"});
    }
</script>
<{include file="../common/footer.tpl"}>