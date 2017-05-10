<{include file="./common/header.tpl"}>

<link rel="stylesheet" type="text/css" href="/gl/static/css/swiper.min.css">
<link rel="stylesheet" href="/gl/static/css/answer.css">
<script type="text/javascript" src="/gl/static/js/support.js"></script><script type="text/javascript" src="/gl/static/js/swiper3.1.0.jquery.min.js"></script>
<div id="main" class="cont-width">
    <div class="row">
        <div class="leftPart">
            <div class="content">
                <div class="singleTitle">
                    <div class="box-title">
                        <div class="phone-area"><span class="line"></span>"<{$data['gameInfo']['abstitle']}>"问答</div>
                    </div>
                </div>
                <div class="c-box1">
                    <div class="detail questionDetail">
                        <span style="font-size:12px;">创建时间：<{$data.createTime}></span>
                    	<div class="interlocutionTitle">
                        	<a class="photo"><img src="<{$data['questionInfo']['info']['author']['headImg']}>"/><span></span></a>
                        	<a href='/help/'>
                            	<{if $data['questionInfo']['info']['author']['medalLevel']}><div class="shen">神</div><{/if}>
                            </a>
                        	<div class="h-box">
                            	<p title='<{$data['questionInfo']['info']['author']['nickName']}>'>
									<{substr_forecast str=$data['questionInfo']['info']['author']['nickName'] num='20' dot='...'}>
                            		<a href='/help/'>
                                		<{if $data['questionInfo']['info']['author']['uLevel']>0}>
                                			<span class="label label-color">LV<{$data['questionInfo']['info']['author']['uLevel']}></span>
                                		<{/if}>
                                    </a>
                            	</p>
                            	<span class="time"><{if !$data['questionInfo']['info']['updateType']}>发布于<{else}>编辑于<{/if}>：<{$data['questionInfo']['info']['updateTime']}></span>
                        	</div>
                        	<a href="javascript:void(0)" onclick="moreBtnClick()"><div class="moreBtn"><img src="/gl/static/images/v1/moreBtn.png"/></div></a>
                        	<div class="questionMoreIcons clear" id="questionMoreIcons" style="display: none">
                        		<i class="zqzwpc"></i>
                            	<!--<a href="#" class=""><i class="questionMoreIcon i1"></i></a>-->
								<{if $data['questionInfo']['info']['author']['guid'] == $data['selfuid']}>
									<a href="/question/ask/<{$data['gameInfo']['absId']}>/<{$data['questionInfo']['info']['absId']}>" class=""><i class="questionMoreIcon i2"></i></a>
								<{/if}>
								
                            	<{if $data.guid}>
									<a href="javascript:;" class="qreport"><i class="questionMoreIcon i3"></i></a>
								<{else}>
									<a href="javascript:;" onclick='gologin()' class="qreport"><i class="questionMoreIcon i3"></i></a>
								<{/if}>
								<script>
									function getShareMessage(){
										return '我在全民手游攻略，一起来答疑解惑吧！';
									}
								</script>
                            	<a href="javascript:void((function(s,d,e){try{}catch(e){}var%20f='http://v.t.sina.com.cn/share/share.php?',u='<{$data.qshareurl}>',p=['url=',e(u),'&title=<{$data.qshare_content|truncate:60:"..."}>&appkey=691988791&pic=<{$data.qshare_pic_url}>'].join('');function a(){if(!window.open([f,p].join(''),'mb'))u.href=[f,p].join('');};if(/Firefox/.test(navigator.userAgent)){setTimeout(a,0)}else{a()}})(screen,document,encodeURIComponent));" title="分享到新浪微博"  class=""><i class="questionMoreIcon i4"></i></a>
                            	<!--<a href="#" class=""><i class="questionMoreIcon i5"></i></a>-->
                            	<a data='qq' href="javascript:(function(){window.open('http://connect.qq.com/widget/shareqq/index.html?url='+ encodeURIComponent('<{$data.qshareurl}>')+ '&desc='+getShareMessage()+'&title=全民手游攻略分享&pics=<{$data.qshare_pic_url}>&site=全民手游攻略&summary=<{$data.qshare_content|truncate:30:"..."}>','分享至QQ好友');})()" class=""><i class="questionMoreIcon i6"></i></a>
                            	<!--<a href="#" class=""><i class="questionMoreIcon i7"></i></a>-->
                            	<a href="javascript:(function(){window.open('http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url='+ encodeURIComponent('<{$data.qshareurl}>')+ '&d='+getShareMessage()+'&title=全民手游攻略分享&pics=<{$data.qshare_pic_url}>&site=全民手游攻略&summary=<{$data.qshare_content|truncate:30:"..."}>','分享至QQ空间');})()" data='qzone' title="分享至QQ空间" class=""><i class="questionMoreIcon i8"></i></a>
                        	</div>
                    	</div>
                    	<div class="zw-cont">
                        	<div class="cont-del">
                            	<p class=""><{$data['questionInfo']['info']['content']}></p>
                        	</div>
							<!--
                        	<div class="cont-del pic_article">
                            	<div class="img_box">
                                	<img src="/gl/static/images/v1/img4.jpg">
                            	</div>
                        	</div>
							-->
                    	</div>
                    	<div class="interlocutionGz">
                        	<div class="b2-txt fl"><span>回答<{$data['questionInfo']['info']['answerCount']}></span><em>|</em><span>关注<span id='questionatnum'><{$data['questionInfo']['info']['attentionCount']}></span></span></div>
							
						<{if $data.guid}>
							<{if $data['questionInfo']['info']['attentioned']}>
								<a class="follow fr active" href="javascript:;" onclick="doajax(4,1)">已关注</a>
							<{else}>
								<a class="follow fr" href="javascript:;" onclick="doajax(4,0)">关注</a>
							<{/if}>
						<{else}>	
							<a class="follow fr" href="javascript:;" onclick='gologin()'>关注</a>
						<{/if}>

						</div>
					</div>
                </div>
                <div class="c-box1">
                    <div class="detail">
                    	<div class="interlocutionTitle">
                        	<a class="photo"><img src="<{$data['author']['headImg']}>"/><span></span></a>
                        	<a href='/help/'>
                            	<{if $data['author']['medalLevel']}><div class="shen">神</div><{/if}>
                            </a>
                        	<div class="h-box">
                            	<p title =='<{$data['author']['nickName']}>'>
								<{substr_forecast str=$data['author']['nickName'] num='20' dot='...'}>
                            		<a href='/help/'>
                                		<{if $data['author']['uLevel'] > 0}>
                                			<span class="label label-color">LV<{$data['author']['uLevel']}></span>
                                		<{/if}>
                                	</a>
                            	</p>
                            	<span class="time">发布于：<{$data['updateTime']}></span>
                        	</div>
                        	<a href="javascript:;" id='showupdown'>
								<div class="zanBtn clear">
								    <span class="zanNum"><{$data['agreeCount']}></span>
                                	<div class="upDown">
                                    	<div class="zanUpDiv"><div class="zanUp <{if $data['hasAgree']}>active<{/if}>"></div></div>
                                    	<div class="zanDownDiv"><div class="zanDown <{if $data['hasCombat']}>active<{/if}>"></div></div>
                                	</div>
                        
                            	</div>
                        	</a>
                    	</div>
                    	<div class="zw-cont">
                        	<div class="cont-del">
                            	<p class="">
									<{$data['content']}>
								</p>
                        	</div>
                    	</div>
                    	<div class="answerMoreIcons clear">
							<{if $data.guid}>
								<a href="javascript:;" class="collect <{if $data['hasCollect']}>active<{/if}>" <{if $data['hasCollect']}>onclick="doajax(3,1)"<{else}>onclick="doajax(3,0)"<{/if}>><i class="answerMoreIcon i1"></i><span>收藏</span></a>
								
							<{else}>	
								<a href="javascript:;" onclick='gologin()' class="collect"><i class="answerMoreIcon i1"></i><span>收藏</span></a>
							<{/if}>

							<a href="javascript:;" onclick='dodownload();' class="coet"><i class="answerMoreIcon i2"></i><span>评论</span></a>
                        	<a href="javascript:;" class="share"><i class="answerMoreIcon i3"></i><span>分享</span></a>
							<!--
                        	<a href="javascript:;" class="moremore"><i class="answerMoreIcon i4"></i><span>更多</span></a>
							-->
							<{if $data['author']['guid'] == $data['selfuid']}>
								<a href="/question/answer/<{$data['questionInfo']['info']['absId']}>/<{$data.absId}>" class="edit"><i class="answerMoreIcon i5"></i><span>编辑</span></a>
								<a href="javascript:;" onclick='dodel();' class="delete"><i class="answerMoreIcon i6"></i><span>删除</span></a>
							<{/if}>
							<a href="javascript:;" class="report"><i class="answerMoreIcon i7"></i><span>举报</span></a>
                    	</div>
						<div class="questionMoreIcons shareIcons clear" style="display: none">
							<i class="zqzwpc"></i>
                        	<a href="javascript:void((function(s,d,e){try{}catch(e){}var%20f='http://v.t.sina.com.cn/share/share.php?',u='<{$data.ashareurl}>',p=['url=',e(u),'&title=【<{$data.ashare_content|truncate:60:"..."}>】<{$data.ashare_content|truncate:100:"..."}>&appkey=691988791&pic=<{$data.ashare_pic_url}>'].join('');function a(){if(!window.open([f,p].join(''),'mb'))u.href=[f,p].join('');};if(/Firefox/.test(navigator.userAgent)){setTimeout(a,0)}else{a()}})(screen,document,encodeURIComponent));" title="分享到新浪微博" class=""><i class="questionMoreIcon i4"></i></a>
                        	<!--<a href="javascript:void(0)" class=""><i class="questionMoreIcon i5"></i></a>-->
                        	<a data='qq' href="javascript:(function(){window.open('http://connect.qq.com/widget/shareqq/index.html?url='+ encodeURIComponent('<{$data.ashareurl}>')+ '&desc='+getShareMessage()+'&title=<{$data.ashare_content|truncate:60:"..."}>&pics=<{$data.ashare_pic_url}>&site=全民手游攻略&summary=<{$data.ashare_content|truncate:100:"..."}>','分享至QQ好友');})()" class=""><i class="questionMoreIcon i6"></i></a>
                        	<!--<a href="javascript:void(0)" class=""><i class="questionMoreIcon i7"></i></a>-->
                        	<a href="javascript:(function(){window.open('http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url='+ encodeURIComponent('<{$data.ashareurl}>')+ '&desc='+getShareMessage()+'&title=<{$data.ashare_content|truncate:60:"..."}>&pics=<{$data.ashare_pic_url}>&site=全民手游攻略&summary=<{$data.ashare_content|truncate:100:"..."}>','分享至QQ空间');})()" data='qzone' title="分享至QQ空间" class=""><i class="questionMoreIcon i8"></i></a>
                    	</div>
                    </div>
					<{if $data['questionInfo']['info']['answerCount'] > 1}>
						<a href='/question/info/<{$data['questionInfo']['absId']}>'>
							<div class="addmore">还有<{$data['questionInfo']['info']['answerCount']-1}>条精彩回答，去查看>></div>
						</a>
					<{/if}>
					<!--
					<div class="tk">
						<div class="tkTitle"><span>为答案投票</span></div>
						<div class="tpC clear">
							<a href="javascript:void(0)" <{if $data['hasAgree']}>onclick="doajax(1,0)" class="agreea active"<{else}>onclick="doajax(1,1)" class="agreea"<{/if}>><div class="agree"><div class="agreeIcon"></div><span>赞同</span></div></a>
							<a href="javascript:void(0)" <{if $data['hasCombat']}>onclick="doajax(2,0)" class="naya active"<{else}>onclick="doajax(2,1)"class="naya"<{/if}>><div class="nay"><div class="nayIcon"></div><span>反对</span></div></a>
						</div>
					</div>
					-->
					<div class="tkBg">
						<div class="tk">
							<a href="javascript:void(0)" onclick="tk_hide()"><div class="chacha"></div></a>
							<div class="tkTitle"><span>为答案投票</span></div>
							<div class="tpC clear">
								<a href="javascript:void(0)" <{if $data['hasAgree']}>onclick="doajax(1,0)" class="agreea active"<{else}>onclick="doajax(1,1)" class="agreea"<{/if}>><div class="agree"><div class="agreeIcon"></div><span id='agreedes'><{if $data['hasAgree']}>已<{/if}>赞同</span></div></a>
								<a href="javascript:void(0)" <{if $data['hasCombat']}>onclick="doajax(2,0)" class="naya active"<{else}>onclick="doajax(2,1)"class="naya"<{/if}>><div class="nay"><div class="nayIcon"></div><span id='naydes'><{if $data['hasCombat']}>已<{/if}>反对</span></div></a>
							</div>
						</div>
					</div>

					<div class="tkBg2" style='display:none;'>
						<div class="tk2">
							<input type='hidden' id='rtype' name='rtype' value='' />
							<div class="tk2Up">
								<a href="javascript:void(0)" onclick="dojubao(5,1)"><div class="first">广告垃圾</div></a>
								<a href="javascript:void(0)" onclick="dojubao(5,2)"><div>淫秽色情</div></a>
								<a href="javascript:void(0)" onclick="dojubao(5,3)"><div>虚假消息</div></a>
								<a href="javascript:void(0)" onclick="dojubao(5,4)"><div>敏感信息</div></a>
								<a href="javascript:void(0)" onclick="dojubao(5,0)"><div>其它</div></a>
							</div>
							<div class="tk2Down">
								<a href="javascript:void(0)" id='cancelreport'>取消</a>
							</div>
						</div>
					</div>
			<div class="popMobileshare">
				<div class="mobilesharebox">
					<div class="mobileshare">
						<div class="swiper-wrapper">
							<section class="swiper-slide">
								<div class="mobileshareIcons clear">
									<div class='qdiv'>
										<a href="javascript:void((function(s,d,e){try{}catch(e){}var%20f='http://v.t.sina.com.cn/share/share.php?',u='<{$data.qshareurl}>',p=['url=',e(u),'&title=<{$data.qshare_content|truncate:60:"..."}>&appkey=691988791&pic=<{$data.qshare_pic_url}>'].join('');function a(){if(!window.open([f,p].join(''),'mb'))u.href=[f,p].join('');};if(/Firefox/.test(navigator.userAgent)){setTimeout(a,0)}else{a()}})(screen,document,encodeURIComponent));" title="分享到新浪微博" ><div class="mobileshareIcon"><i class="icon i1"></i><p>新浪微博</p></div></a>
										<!--
										<a data='qq' href="javascript:mb_share('<{$data.qshare_content|truncate:60:"..."}>','<{$data.qshareurl}>')" ><div class="mobileshareIcon"><i class="icon i3"></i><p>腾讯QQ</p></div></a>
										-->
										<a href="javascript:(function(){window.open('http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url='+ encodeURIComponent('<{$data.qshareurl}>')+ '&title='+getShareMessage()+'&title=全民手游攻略分享&pics=<{$data.qshare_pic_url}>&site=全民手游攻略&summary=<{$data.qshare_content|truncate:30:"..."}>','分享至QQ空间');})()" data='qzone' title="分享至QQ空间"><div class="mobileshareIcon"><i class="icon i5"></i><p>QQ空间</p></div></a>
										<a href="javascript:;"><div class="mobilejustify_fix"></div></a>
									</div>

									<div class='adiv' style='display:none;'>
										<a href="javascript:void((function(s,d,e){try{}catch(e){}var%20f='http://v.t.sina.com.cn/share/share.php?',u='<{$data.ashareurl}>',p=['url=',e(u),'&title=【<{$data.qshare_content|truncate:60:"..."}>】<{$data.ashare_content|truncate:100:"..."}>&appkey=691988791&pic=<{$data.ashare_pic_url}>'].join('');function a(){if(!window.open([f,p].join(''),'mb'))u.href=[f,p].join('');};if(/Firefox/.test(navigator.userAgent)){setTimeout(a,0)}else{a()}})(screen,document,encodeURIComponent));" title="分享到新浪微博"><div class="mobileshareIcon"><i class="icon i1"></i><p>新浪微博</p></div></a>
										<!--
										<a data='qq' href="javascript:mb_share('<{$data.ashare_content|truncate:60:"..."}>','<{$data.ashareurl}>')" ><div class="mobileshareIcon"><i class="icon i3"></i><p>腾讯QQ</p></div></a>
										-->
										<a href="javascript:(function(){window.open('http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url='+ encodeURIComponent('<{$data.ashareurl}>')+ '&desc='+getShareMessage()+'&title=<{$data.qshare_content|truncate:60:"..."}>&pics=<{$data.ashare_pic_url}>&site=全民手游攻略&summary=<{$data.ashare_content|truncate:100:"..."}>','分享至QQ空间');})()" data='qzone' title="分享至QQ空间" ><div class="mobileshareIcon"><i class="icon i5"></i><p>QQ空间</p></div></a>
										<a href="javascript:;"><div class="mobilejustify_fix"></div></a>
									</div>
									<!--
									<a href="javascript:;"><div class="mobileshareIcon"><i class="icon i2"></i><p>微信</p></div></a>
									-->

									<!--
									<a href="javascript:;"><div class="mobileshareIcon"><i class="icon i4"></i><p>微信朋友圈</p></div></a>
									-->
								</div>
							</section>
						</div>
						 <div class="swiper-pagination"></div>
					</div>
					<div class="mobilereport qdiv">
						<div class="mobilereportIcons clear">
						<{if $data.guid}>
							<a href="javascript:;" onclick="reportFun(0);" id="举报"><div class="mobilereportIcon"><i class="icon i1"></i><p>举报</p></div></a>
						<{else}>
							<a href="javascript:;" onclick='gologin()' id="举报"><div class="mobilereportIcon"><i class="icon i1"></i><p>举报</p></div></a>
						<{/if}>
						<{if $data['questionInfo']['info']['author']['guid'] == $data['selfuid']}>
							<a href="/question/ask/<{$data['gameInfo']['absId']}>/<{$data['questionInfo']['info']['absId']}>" id="编辑"><div class="mobilereportIcon"><i class="icon i2"></i><p>编辑</p></div></a>
						<{/if}>
							<!--<a href="javascript:;" id="删除"><div class="mobilereportIcon"><i class="icon i3"></i><p>删除</p></div></a>-->
							<a href="javascript:;"><div class="mobilereportIcon mobileleft_fix">&nbsp;</div></a>
							<a href="javascript:;"><div class="mobilereportIcon mobileleft_fix">&nbsp;</div></a>
							<a href="javascript:;"><div class="mobilejustify_fix"></div></a>
						</div>
					</div>
				</div>
			</div>
					
				</div>
            </div>
        </div>
		<!--pc右侧公共-->
		<{include file="./common/moudle_pc_right.tpl"}>
    </div>
</div>
<div class="goTop"><i class="icon"></i></div>
<{include file="./common/moudle_footer.tpl"}>
<script>
    var pageWidth = 0;
    var mySwiper;

 	function moreBtnClick(){
        if(pageWidth<=996){
			$(".qdiv").show();
			$(".adiv").hide();
			$(".popMobileshare").show('fast');

	   }
	   else{
		   $("#questionMoreIcons").toggle(200);
	   }
    }
    $(".moremore").click(function(){
        $(".moremore").hide();
        $(".edit").show();
        $(".delete").show();
        $(".report").show();
    })
	$('#showupdown').click(function(){
		<{if $data.guid}>
			<{if $data.is_ban}>
				confirm_ban();
			<{else}>
				$('.tkBg').show();
			<{/if}>

		<{else}>
			gologin();
		<{/if}>
	});
	
	function tk_hide(){
		$('.tkBg').hide();
	}
	$(".share").click(function(){
        if(pageWidth<=996)
		{
			$(".adiv").show();
			$(".qdiv").hide();
			$(".popMobileshare").show('fast');
		}
		else{
			$(".shareIcons").toggle(200);
		}
    });
	
	$('.qreport').click(function(){
		reportFun(0);
	});
	
	
	$('.report').click(function(){
		reportFun(1);
	});

	$('#cancelreport').click(function(){
		hideall();
	});
	
	//举报
	function reportFun(flag){
		$('#rtype').val(flag);
		$('.tkBg2').show();
		$('.popMobileshare').hide();
	}
	function hideall(){
		//$(".questionMoreIcons").hide();
		$('.tkBg2').hide();
		$(".moreBtn").show();
	}

	$(document).ready(function() {
            pageWidth = $(window).width();
            /*mySwiper = new Swiper('#mobileshare',{
                //direction: 'vertical',//竖着swip
                //noSwipingClass : 'stop-swiping',//不让swip
                onSlideChangeEnd: function(swiper){
                    //console.log(mySwiper.activeIndex);
                },
                loop: false,
                noSwiping : true
            });*/
		$(".popMobileshare").on("click",function(e){
			if($(this).hasClass("popMobileshare")){
				 $(".popMobileshare").hide("fast");
				 $(".adiv").hide();
                 $(".qdiv").hide();
			}
		})
		$(".popMobileshare").height("100%");
	    $(".popMobileshare").hide();
        });
</script>

<script>
	<{if $data.guid}>
	//踩，赞，收藏操作
	function doajax(flag,type){
		switch(flag){
			case 1: //执行赞/取消赞操作(答案)
				var url = "/ajax_fun/answer_praise_operate/<{$data['absId']}>/" + type;
				break;
			case 2: //执行踩/取消踩操作(答案)
				var url = "/ajax_fun/answer_cai_operate/<{$data['absId']}>/" + type;
				break;
			case 3: //执行收藏/取消收藏操作(答案)
				var url = "/follow/answer_collect?mark=<{$data['absId']}>&action=" + type;	
				break;
			case 4: //问题关注/取消关注(问题)
				var url = "/follow/question_attention?mark=<{$data['questionInfo']['absId']}>&action=" + type;	
				break;
			case 5: //问题举报/答案举报
				//根据参数判断
				var rtype = $('#rtype').val();

				if(rtype == '0'){ //举报问题
					var url = "/ajax_fun/complaint_add/<{$data['questionInfo']['absId']}>/0/" + type;	
				}else if(rtype == '1'){ //举报答案
					var url = "/ajax_fun/complaint_add/<{$data['absId']}>/1/" + type;	
				}else{
					//alert('问题或答案标识丢失');
					myPop('问题或答案标识丢失');
				}
			
				break;
				
			default:
				//alert('操作标识丢失');
				myPop('操作标识丢失');
				return false;
				break;
		}
		
		gl_api_fun(url,flag,type);
	}
	<{/if}>
	
	function dojubao(flag,type){
		switch(flag){
			case 1: //执行赞/取消赞操作(答案)
				var url = "/ajax_fun/answer_praise_operate/<{$data['absId']}>/" + type;
				break;
			case 2: //执行踩/取消踩操作(答案)
				var url = "/ajax_fun/answer_cai_operate/<{$data['absId']}>/" + type;
				break;
			case 3: //执行收藏/取消收藏操作(答案)
				var url = "/follow/answer_collect?mark=<{$data['absId']}>&action=" + type;	
				break;
			case 4: //问题关注/取消关注(问题)
				var url = "/follow/question_attention?mark=<{$data['questionInfo']['absId']}>&action=" + type;	
				break;
			case 5: //问题举报/答案举报
				//根据参数判断
				var rtype = $('#rtype').val();

				if(rtype == '0'){ //举报问题
					var url = "/ajax_fun/complaint_add/<{$data['questionInfo']['absId']}>/0/" + type;	
				}else if(rtype == '1'){ //举报答案
					var url = "/ajax_fun/complaint_add/<{$data['absId']}>/1/" + type;	
				}else{
					//alert('问题或答案标识丢失');
					myPop('问题或答案标识丢失');
				}
			
				break;
				
			default:
				//alert('操作标识丢失');
				myPop('操作标识丢失');
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
								$('.agreea').addClass('active');
								$('.agreea').attr('onclick','doajax(1,0)');
								
								//关联操作
								$('.naya').removeClass('active');
								$('.naya').attr('onclick','doajax(2,1)');
								$('#agreedes').text('已赞同');
								$('.zanUp').addClass('active');
								$('#naydes').text('反对');
								
								$('.zanDown').removeClass('active');
							}else{
								$('.agreea').removeClass('active');
								$('.agreea').attr('onclick','doajax(1,1)');
								
								$('.zanUp').removeClass('active');
								$('#agreedes').text('赞同');
							}
							
							//修改数量
							$('.zanNum').text(e.data.agreeCount);
							myPop('操作成功');
							break;
						case 2: //执行踩/取消踩操作
							if(type == 1){
								$('.naya').addClass('active');
								$('.naya').attr('onclick','doajax(2,0)');
								
								//关联操作
								$('.agreea').removeClass('active');
								$('.agreea').attr('onclick','doajax(1,1)');
								
								$('.zanDown').addClass('active');
								$('#naydes').text('已反对');
								$('.zanUp').removeClass('active');
								$('#agreedes').text('赞同');
							}else{
								$('.naya').removeClass('active');
								$('.naya').attr('onclick','doajax(2,1)');
								
								$('.zanDown').removeClass('active');
								$('#naydes').text('反对');
							}
							
							//修改数量
							$('.zanNum').text(e.data.agreeCount);
							myPop('操作成功');
							break;
						case 3: //执行收藏/取消收藏操作
							if(type != 1){
								$('.collect').addClass('active');
								$('.collect').attr('onclick','doajax(3,1)');
								myPop('收藏成功');
							}else{
								$('.collect').removeClass('active');
								$('.collect').attr('onclick','doajax(3,0)');
								myPop('取消成功');
							}
							break;
						case 4: //问题关注/取消关注(问题)
							if(type != 1){
								$('.follow').addClass('active');
								$('.follow').attr('onclick','doajax(4,1)');
								$('.follow').text('已关注');
								
								//数量维护
								var questionatnum = $('#questionatnum').text();
								$('#questionatnum').text(parseInt(questionatnum) + 1);
								myPop('关注成功');
								setTimeout(function () {dodownload()}, 1000)
							}else{
								$('.follow').removeClass('active');
								$('.follow').attr('onclick','doajax(4,0)');
								$('.follow').text('关注');
								
								//数量维护
								var questionatnum = $('#questionatnum').text();
								$('#questionatnum').text(parseInt(questionatnum) - 1);
								myPop('取消成功');
							}
							break;
						case 5: //问题/答案举报
							hideall();
							myPop('举报成功');
							//alert('举报成功');
							break;
					
						default:
							//alert('操作标识丢失');
							myPop('操作标识丢失');
							return false;
							break;
					}
					tk_hide();
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
        <div class="modal-content confirm">
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
	function dodel(){
		var del_message = "确定要删除该条回答？";
		$('.modal-body').text(del_message);
		
		$('#myModal .modal-footer .btn-default').text('取消');
		$('#myModal .modal-footer .btn-primary').text('确定删除').show();
		
		$('#myModal .modal-footer .btn-primary').click(function(){
			$("#myModal").modal('hide');
			var del_url = '/answer/answer_del/<{$data['absId']}>/';
			window.location.href = del_url;
		});
		
		$("#myModal").modal('show');
	}
	
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
		$('#myModal .modal-footer .btn-primary').text('我知道了').hide();
		
		$('#myModal .modal-footer .btn-primary').click(function(){
			go_back();
		});
		
		$("#myModal").modal('show');
	}

</script>
<{if $data.flag == 1}>
	<div class="popdiv popSUCC">
		<div class="tipcont">
			<i><img src="/gl/static/images/v1/gold_icon.png"/></i>
			<p>回答问题奖励<span>1</span>经验值</p>
		</div>
	</div>
	<script>
		$(function(){
			setTimeout(function(){$('.popdiv.popSUCC').fadeOut('slow');},2000);
		});
	</script>
<{/if}>
<{if $data.flag == 2}>
	<script>
		$(function(){
			myPop('提交成功');
		});
	</script>
<{/if}>
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
<{include file="./common/footer.tpl"}>
