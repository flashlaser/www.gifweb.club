<{include file="./common/header.tpl"}>
<link rel="stylesheet" type="text/css" href="/gl/static/css/swiper.min.css">
<link rel="stylesheet" href="/gl/static/css/question.css">
<script type="text/javascript" src="/gl/static/js/support.js"></script>
<script type="text/javascript" src="/gl/static/js/swiper3.1.0.jquery.min.js"></script>
<div id="main" class="cont-width">
    <div class="row">
        <div class="leftPart">
            <div class="content">
                <div class="singleTitle">
                    <div class="box-title">
                        <div class="phone-area"><span class="line"></span>"<{$data.gameInfo.abstitle}>"问答</div>
                    </div>
                </div>
                <div class="c-box1">
                    <div class="detail questionDetail">
                        <span style="font-size:12px;">创建时间：<{$data.createTime}></span>
                        <div class="interlocutionTitle">
                            <span class="photo"><img src="<{$data.author.headImg}>"/><span></span>
                            
                            </span>
                            <input type="hidden" id="question_content_info" value="<{$data.share_content}>"/>
                            <{if $data.author.medalLevel ==1}>
                                <a href="/help/"><div class="shen">神</div></a>
                            <{/if}>
                            <div class="h-box">
                                <p><{$data.author.nickName}>
                                	<{if $data.author.uLevel >0}><a href="/help/"><span class="label label-color">LV<{$data.author.uLevel}></span></a><{/if}>
                                </p>
                                <span class="time"><{if $data.updateType ==1}>编辑于<{else}>发布于<{/if}>：<{$data.updateTime}></span>
                            </div>
                            <a href="javascript:void(0)" onclick="moreBtnClick('<{$data.original_content}>','<{$data.attribute.images.0.url}>')"><div class="moreBtn"><img src="/gl/static/images/v1/moreBtn.png"/></div></a>
                            <div class="questionMoreIcons clear" id="questionMoreIcons" style="display: none">
                            	<i class="zqzwpc"></i>
                            	<{if $data.author.guid == $uid}>
	                                <!--<a href="#" class=""><i class="questionMoreIcon i1"></i></a>-->
	                                <a href="/question/ask/<{$data.gameInfo.absId}>/<{$data.absId}>" class=""><i class="questionMoreIcon i2"></i></a>
                                <{/if}>
                                <a href="javascript:void(0)" onclick="report(0,<{$data.absId}>)" class=""><i class="questionMoreIcon i3"></i></a>
                                <{if $data.attribute.images.0.url}>
                                	<a href="javascript:void((function(s,d,e){try{}catch(e){}var%20f='http://v.t.sina.com.cn/share/share.php?',u='http://www.wan68.com/<{$smarty.server.REQUEST_URI}>',p=['url=',e(u),'&title=<{$data.share_content}>&appkey=691988791&pic=<{$data.attribute.images.0.url}>'].join('');function a(){if(!window.open([f,p].join(''),'mb'))u.href=[f,p].join('');};if(/Firefox/.test(navigator.userAgent)){setTimeout(a,0)}else{a()}})(screen,document,encodeURIComponent));" title="分享到新浪微博"  ><i class="questionMoreIcon i4"></i></a>
                                <{else}>
                                	<a href="javascript:void((function(s,d,e){try{}catch(e){}var%20f='http://v.t.sina.com.cn/share/share.php?',u='http://www.wan68.com/<{$smarty.server.REQUEST_URI}>',p=['url=',e(u),'&title=<{$data.share_content}>&appkey=691988791&pic=http://www.wan68.com/gl/static/images/foot_logo.png'].join('');function a(){if(!window.open([f,p].join(''),'mb'))u.href=[f,p].join('');};if(/Firefox/.test(navigator.userAgent)){setTimeout(a,0)}else{a()}})(screen,document,encodeURIComponent));" title="分享到新浪微博"  ><i class="questionMoreIcon i4"></i></a>
                                <{/if}>
                                <!--<a href="#" class=""><i class="questionMoreIcon i5"></i></a>-->
                                <!--<a href="#" class=""><i class="questionMoreIcon i6"></i></a>-->
                                <{if $data.attribute.images}>
                                	<a data='qq' href="javascript:(function(){window.open('http://connect.qq.com/widget/shareqq/index.html?url=http://www.wan68.com<{$smarty.server.REQUEST_URI}>&desc='+getShareMessageQuestion()+'&title=全民手游攻略分享&pics=<{$data.attribute.images.0.url}>&site=全民手游攻略&summary=我在全民手游攻略给你分享，快来看看吧!','分享至QQ好友', '');})()" ><i class="questionMoreIcon i6"></i></a>
                                <{else}>
                                	<a data='qq' href="javascript:(function(){window.open('http://connect.qq.com/widget/shareqq/index.html?url=http://www.wan68.com<{$smarty.server.REQUEST_URI}>&desc='+getShareMessageQuestion()+'&title=全民手游攻略分享&pics=http://www.wan68.com/gl/static/images/foot_logo.png&site=全民手游攻略&summary=我在全民手游攻略给你分享，快来看看吧!','分享至QQ好友', '');})()" ><i class="questionMoreIcon i6"></i></a>
                                <{/if}>
                                <!--<a href="#" class=""><i class="questionMoreIcon i7"></i></a>-->
                                
                                <{if $data.attribute.images}>
                                	<a href="javascript:(function(){window.open('http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url='+ encodeURIComponent(location.href)+ '&title='+getShareMessageQuestion()+'&title=全民手游攻略分享&pics=<{$data.attribute.images.0.url}>&site=全民手游攻略&summary=我在全民手游攻略给你分享，快来看看吧!','分享至QQ空间', '');})()" data='qzone' title="分享至QQ空间"><i class="questionMoreIcon i8"></i></a>
                             	<{else}>
                                	<a href="javascript:(function(){window.open('http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url='+ encodeURIComponent(location.href)+ '&title='+getShareMessageQuestion()+'&title=全民手游攻略分享&pics=http://www.wan68.com/gl/static/images/foot_logo.png&site=全民手游攻略&summary=我在全民手游攻略给你分享，快来看看吧!','分享至QQ空间', '');})()" data='qzone' title="分享至QQ空间"><i class="questionMoreIcon i8"></i></a>
                             	<{/if}>
                             </div>
                             
                                <script>
									function getShareMessageQuestion(){
										return $("#question_content_info").val();
									}
								</script>
                        </div>
                        <div class="zw-cont">
                            <div class="cont-del">
                                <p class=""><{$data.content}></p>
                            </div>
                        </div>
                        <div class="interlocutionGz">
                            <div class="b2-txt fl"><span>回答<{$data.answerCount}></span><em>|</em><span>关注<span  id='questionatnum'><{$data.attentionCount}></span></span></div>
                            <{if $data.attentioned}>
								<a class="follow fr active" href="javascript:;" onclick="doajax(4,1,'<{$uid}>')">已关注</a>
							<{else}>
								<{if $uid !=''}>
									<a class="follow fr" href="javascript:;" onclick="doajax(4,0,'<{$uid}>')">关注</a>
								<{else}>
									<a class="follow fr" href="javascript:;" onclick="window.location.href='/user/login?backUrl='+window.location.href;">关注</a>
								<{/if}>
							<{/if}>
                        </div>
                    </div>
                </div>
                <div class="c-box1">
                    <a href="/question/answer/<{$data.absId}>" class="reply"><div class="replyPc"><i class="replyIcon"></i><span>我要回答</span></div></a>
                </div>
                <div class="c-box1">
                    <{if $answer_hot_info_count > 0 }>
	                    <div class="pcComn-tle single-pcCome-tle"><p>热门回答</p></div>
	                    <div class="hot">热门回答</div>
                    <{/if}>
	        		<{foreach $answer_hot_info as $k => $v}>
	                    <div class="detail" id="hot_answer_div_<{$v.aid}>">
	                        <div class="interlocutionTitle">
	                            <span class="photo"><img src="<{$v.u_info.avatar}>"/><span></span></span>
	                            <{if $v.u_info.rank ==1}>
                                	<a href="/help/"><div class="shen">神</div></a>
                                <{/if}>
	                            <div class="h-box">
	                                <p><{$v.u_info.nickname}>
	                                	<{if $v.u_info.level >0}><a href="/help/"><span class="label label-color">LV<{$v.u_info.level}></span></a><{/if}>
	                                </p>
	                                <span class="time"><{if $v.updateType ==1}>编辑于<{else}>发布于<{/if}>：<{$v.ctime}></span>
	                            </div>
	                            <a href="javascript:void(0)" onclick="zan(<{$v.aid}>,'hot')">
	                                <div class="zanBtn clear">
	                                	<span class="zanNum" id="zanNum_hot_<{$v.aid}>"><{$v.agreeCount}></span>
	                                    <div class="upDown">
	                                        <div class="zanUpDiv"><div class="zanUp <{if $v.hasAgree}>active<{/if}>"  id="zanUp_hot_<{$v.aid}>"></div></div>
	                                        <div class="zanDownDiv"><div class="zanDown  <{if $v.hasCombat}>active<{/if}>"  id="zanDown_hot_<{$v.aid}>"></div></div>
	                                    </div>
	                                </div>
	                            </a>
	                        </div>
	                        <div class="zw-cont">
	                            <div class="cont-del" id="hot_cont_<{$v.aid}>">
	                                <p class="cont">
	                                	<{$v.content}>
	                                	<{if $v.a_img_count >0 || $v.more_content == 1}>
	                                	<span class="showMorePic">
	                                		<{if $v.a_img_count > 0 }>
                                        		<i class="icon"><span class="picNum"><{$v.a_img_count}></span></i>
                                        	<{/if}>
                                        	<input type="hidden" name="a_content" id="a_content_<{$v.aid}>" value="<{$v.a_content.content}>">
                                        	
                                        	<a href="javascript:void(0);" onclick="openAll(<{$v.aid}>,'hot',1);"  class="pc_open_all"><span>展开全部</span><i class="arrowPc"></i></a>
                                    	</span>
	                                	<{/if}>
                                    </p>
	                                <{if $v.a_img_count >0 || $v.more_content == 1}>
                                    	<a href="javascript:;" class="mobileA openUp" onclick="openAll(<{$v.aid}>,'hot',1);" class="wap_open_all" ><i class="arrowMobile" ></i></a>
	                                <{/if}>
	                            </div>
	                            <div class="cont-del" id="hot_cont_more_<{$v.aid}>" style="display:none;">
	                                <p class="cont">
	                                	<{$v.a_content.content}>
	                                	<span class="showMorePic">
                                        	<a href="javascript:void(0);" onclick="openAll(<{$v.aid}>,'hot',2);"  class="pc_open_all"><span>收回</span><i class="arrowPc arrowPc2"></i></a>
                                    	</span>
                                    </p>
                                    <a href="javascript:;" class="mobileA openUp" onclick="openAll(<{$v.aid}>,'hot',2);" class="wap_open_all" ><i class="arrowMobile2" ></i></a>
	                            </div>
	                        </div>
	                        <div class="answerMoreIcons clear">
	                            <a href="javascript:void(0)" class="collect <{if $v.hasCollect}>active<{/if}>" id="collect_hot_<{$v.aid}>" <{if $uid}><{if $v.hasCollect}>onclick="doajax(3,1,'<{$uid}>',<{$v.aid}>,'hot')"<{else}>onclick="doajax(3,0,'<{$uid}>',<{$v.aid}>,'hot')"<{/if}> <{else}> onclick="window.location.href='/user/login?backUrl='+window.location.href;" <{/if}>><i class="answerMoreIcon i1"></i><span>收藏</span></a>
	                            <a href="javascript:void(0)" class="coet"  onclick='dodownload();'><i class="answerMoreIcon i2"></i><span>评论</span></a>
	                            <a href="javascript:void(0);" class="share" onclick="share('<{$v.aid}>','hot','<{$v.attribute.images}>','<{$v.share_content}>');"><i class="answerMoreIcon i3"></i><span>分享</span></a>
	                            <{if $v.u_info.uid == $uid}>
		                            <a href="/question/answer/<{$data.absId}>/<{$v.aid}>"  target="_blank" class="edit" id="edit_hot_<{$v.aid}>"><i class="answerMoreIcon i5"></i><span>编辑</span></a>
		                            <a href="javascript:void(0);" onclick="answer_del(<{$v.aid}>);"  data-target="#myModal" data-toggle="modal" class="delete" id="delete_hot_<{$v.aid}>"><i class="answerMoreIcon i6"></i><span>删除</span></a>
	                            <{/if}>
		                        <a href="javascript:void(0)" class="report"  id="report_hot_<{$v.aid}>" onclick="report(1,<{$v.aid}>)" ><i class="answerMoreIcon i7"></i><span>举报</span></a>
	                        </div>
	                        <input type="hidden" id="new_content_info_<{$v.aid}>" value="<{$v.content}>">
	                        <div class="questionMoreIcons shareIcons clear" id="shareIcons_hot_<{$v.aid}>" style="display: none">
	                        	<i class="zqzwpc"></i>
	                        	
	                        	<{if $v.attribute.images}>
                                	<a href="javascript:void((function(s,d,e){try{}catch(e){}var%20f='http://v.t.sina.com.cn/share/share.php?',u='http://www.wan68.com/answer/info/<{$v.aid}>',p=['url=',e(u),'&title=<{$v.content}>&appkey=691988791&pic=<{$v.attribute.images}>'].join('');function a(){if(!window.open([f,p].join(''),'mb'))u.href=[f,p].join('');};if(/Firefox/.test(navigator.userAgent)){setTimeout(a,0)}else{a()}})(screen,document,encodeURIComponent));" title="分享到新浪微博"  ><i class="questionMoreIcon i4"></i></a>
                                <{else}>
                                	<a href="javascript:void((function(s,d,e){try{}catch(e){}var%20f='http://v.t.sina.com.cn/share/share.php?',u='http://www.wan68.com/answer/info/<{$v.aid}>',p=['url=',e(u),'&title=<{$v.content}>&appkey=691988791&pic=http://www.wan68.com/gl/static/images/foot_logo.png'].join('');function a(){if(!window.open([f,p].join(''),'mb'))u.href=[f,p].join('');};if(/Firefox/.test(navigator.userAgent)){setTimeout(a,0)}else{a()}})(screen,document,encodeURIComponent));" title="分享到新浪微博"  ><i class="questionMoreIcon i4"></i></a>
                                <{/if}>
                                <{if $v.attribute.images}>
                                	<a data='qq' href="javascript:(function(){window.open('http://connect.qq.com/widget/shareqq/index.html?url=http://www.wan68.com/answer/info/<{$v.aid}>&desc='+getShareMessageHot(<{$v.aid}>)+'&title=全民手游攻略分享&pics=<{$v.attribute.images}>&site=全民手游攻略&summary=我在全民手游攻略给你分享，快来看看吧!','分享至QQ好友', '');})()" ><i class="questionMoreIcon i6"></i></a>
                                <{else}>
                                	<a data='qq' href="javascript:(function(){window.open('http://connect.qq.com/widget/shareqq/index.html?url=http://www.wan68.com/answer/info/<{$v.aid}>&desc='+getShareMessageHot(<{$v.aid}>)+'&title=全民手游攻略分享&pics=http://www.wan68.com/gl/static/images/foot_logo.png&site=全民手游攻略&summary=我在全民手游攻略给你分享，快来看看吧!','分享至QQ好友', '');})()" ><i class="questionMoreIcon i6"></i></a>
                                <{/if}>
                                <{if $v.attribute.images}>
                                	<a href="javascript:(function(){window.open('http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=http://www.wan68.com/answer/info/<{$v.aid}>&title='+getShareMessageHot(<{$v.aid}>)+'&title=全民手游攻略分享&pics=<{$data.attribute.images}>&site=全民手游攻略&summary=我在全民手游攻略给你分享，快来看看吧!','分享至QQ空间', '');})()" data='qzone' title="分享至QQ空间"><i class="questionMoreIcon i8"></i></a>
                             	<{else}>
                                	<a href="javascript:(function(){window.open('http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=http://www.wan68.com/answer/info/<{$v.aid}>&title='+getShareMessageHot(<{$v.aid}>)+'&title=全民手游攻略分享&pics=http://www.wan68.com/gl/static/images/foot_logo.png&site=全民手游攻略&summary=我在全民手游攻略给你分享，快来看看吧!','分享至QQ空间', '');})()" data='qzone' title="分享至QQ空间"><i class="questionMoreIcon i8"></i></a>
                             	<{/if}>
                                <script>
									function getShareMessageHot(id){
										return $("#new_content_info_"+id).val();
									}
								</script>
	                        </div>
	                    </div>
	                     <hr class="f0line">
						<div class="tkBg" id="tkBg_hot_<{$v.aid}>"style="display: none">
							<div class="tk">
								<a href="javascript:void(0)" onclick="chacha(<{$v.aid}>,'hot')"><div class="chacha"></div></a>
								<div class="tkTitle"><span>为答案投票</span></div>
								<div class="tpC clear">
									<a href="javascript:void(0)" <{if $v.hasAgree}>onclick="doajax(1,0,'<{$uid}>',<{$v.aid}>,'hot')" class="agreea active"<{else}>onclick="doajax(1,1,'<{$uid}>',<{$v.aid}>,'hot')" class="agreea"<{/if}> id="agreeaClass_hot_<{$v.aid}>"><div class="agree"><div class="agreeIcon"></div><span>赞同</span></div></a>
									<a href="javascript:void(0)" <{if $v.hasCombat}>onclick="doajax(2,0,'<{$uid}>',<{$v.aid}>,'hot')" class="naya active"<{else}>onclick="doajax(2,1,'<{$uid}>',<{$v.aid}>,'hot')"class="naya"<{/if}> id="nayaClass_hot_<{$v.aid}>"><div class="nay"><div class="nayIcon"></div><span>反对</span></div></a>
								</div>
							</div>
						</div>
	        		<{/foreach}>
                </div>
                <div class="c-box1" id="newanswer">
                    <{if $answer_info_count > 0 }>
	                    <div class="pcComn-tle single-pcCome-tle"><p>最新回答</p></div>
	                    <div class="hot firstnew">最新回答</div>
                    <{/if}>
	        		<{foreach $answer_info as $k => $v}>
	                    <div class="detail" id="answer_div_<{$v.aid}>">
	                        <div class="interlocutionTitle">
	                            <span class="photo"><img src="<{$v.u_info.avatar}>"/><span></span></span>
	                            <{if $v.u_info.rank ==1}>
                                	<a href="/help/"><div class="shen">神</div></a>
                                <{/if}>
	                            <div class="h-box">
	                                <p><{$v.u_info.nickname}>
	                                	<{if $v.u_info.level >0}><a href="/help/"><span class="label label-color">LV<{$v.u_info.level}></span></a><{/if}>
	                                </p>
	                                <span class="time"><{if $v.updateType ==1}>编辑于<{else}>发布于<{/if}>：<{$v.ctime}></span>
	                            </div>
	                            <a href="javascript:void(0)" onclick="zan(<{$v.aid}>,'new')">
	                                <div class="zanBtn clear">
	                                	<span class="zanNum" id="zanNum_<{$v.aid}>"><{$v.agreeCount}></span>
	                                    <div class="upDown">
	                                        <div class="zanUpDiv"><div class="zanUp <{if $v.hasAgree}>active<{/if}>" id="zanUp_<{$v.aid}>"></div></div>
	                                        <div class="zanDownDiv"><div class="zanDown <{if $v.hasCombat}>active<{/if}>" id="zanDown_<{$v.aid}>"></div></div>
	                                    </div>
	                                </div>
	                            </a>
	                        </div>
	                        <div class="zw-cont">
	                            <div class="cont-del"id="new_cont_<{$v.aid}>">
	                            	<p class="cont" >
	                                	<{$v.content}>
	                                	<{if $v.a_img_count >0 || $v.more_content == 1}>
	                                	<span class="showMorePic">
	                                		<{if $v.a_img_count > 0 }>
                                        		<i class="icon"><span class="picNum"><{$v.a_img_count}></span></i>
                                        	<{/if}>
                                        	<a href="javascript:void(0);" onclick="openAll(<{$v.aid}>,'new',1);"  class="pc_open_all"><span>展开全部</span><i class="arrowPc"></i></a>
                                    	</span>
	                                	<{/if}>
                                    </p>
                                    <{if $v.a_img_count >0 || $v.more_content == 1}>
                                    	<a href="javascript:;" class="mobileA openUp" onclick="openAll(<{$v.aid}>,'new',1);" class="wap_open_all" ><i class="arrowMobile"></i></a>
	                                <{/if}>
	                            </div>
	                            <div class="cont-del" id="new_cont_more_<{$v.aid}>" style="display:none;">
	                                <p class="cont">
	                                	<{$v.a_content.content}>
	                                	<span class="showMorePic">
                                        	<a href="javascript:void(0);" onclick="openAll(<{$v.aid}>,'new',2);"  class="pc_open_all"><span>收回</span><i class="arrowPc2"></i></a>
                                    	</span>
                                    </p>
                                    <a href="javascript:;" class="mobileA openUp" onclick="openAll(<{$v.aid}>,'new',2);" class="wap_open_all" ><i class="arrowMobile2" ></i></a>
	                            </div>
	                        </div>
	                        <div class="answerMoreIcons clear">
	                            <a href="javascript:void(0)" class="collect <{if $v.hasCollect}>active<{/if}>" id="collect_<{$v.aid}>" <{if $uid}><{if $v.hasCollect}>onclick="doajax(3,1,'<{$uid}>',<{$v.aid}>,'new')"<{else}>onclick="doajax(3,0,'<{$uid}>',<{$v.aid}>,'new')"<{/if}>  <{else}>onclick="window.location.href='/user/login?backUrl='+window.location.href;"  <{/if}>><i class="answerMoreIcon i1"></i><span>收藏</span></a>
	                            <a href="javascript:void(0)" class="coet" onclick='dodownload();'><i class="answerMoreIcon i2"></i><span>评论</span></a>
	                            <a href="javascript:void(0);" class="share" onclick="share('<{$v.aid}>','new','<{$v.attribute.images}>','<{$v.content}>');"><i class="answerMoreIcon i3"></i><span>分享</span></a>
	                            <{if $v.u_info.uid == $uid}>
		                            <a href="/question/answer/<{$data.absId}>/<{$v.aid}>" target="_blank" class="edit" id="edit_<{$v.aid}>"><i class="answerMoreIcon i5"></i><span>编辑</span></a>
		                            
		                            <a href="javascript:void(0);" onclick="answer_del(<{$v.aid}>);"  data-target="#myModal" data-toggle="modal" class="delete" id="delete_<{$v.aid}>"><i class="answerMoreIcon i6"></i><span>删除</span></a>
		                            
	                            <{/if}>
	                            <a href="javascript:void(0)" class="report" id="report_<{$v.aid}>"  onclick="report(1,<{$v.aid}>);" ><i class="answerMoreIcon i7"></i><span>举报</span></a>
	                        </div>
	                        <input type="hidden" id="new_content_info_<{$v.aid}>" value="<{$v.content}>">
	                        <div class="questionMoreIcons shareIcons clear" id="shareIcons_new_<{$v.aid}>" style="display: none">
	                        	<i class="zqzwpc"></i>
	                        	
	                        	<{if $v.attribute.images}>
                                	<a href="javascript:void((function(s,d,e){try{}catch(e){}var%20f='http://v.t.sina.com.cn/share/share.php?',u='http://www.wan68.com/answer/info/<{$v.aid}>',p=['url=',e(u),'&title=<{$v.content}>&appkey=691988791&pic=<{$v.attribute.images}>'].join('');function a(){if(!window.open([f,p].join(''),'mb'))u.href=[f,p].join('');};if(/Firefox/.test(navigator.userAgent)){setTimeout(a,0)}else{a()}})(screen,document,encodeURIComponent));" title="分享到新浪微博"  ><i class="questionMoreIcon i4"></i></a>
                                <{else}>
                                	<a href="javascript:void((function(s,d,e){try{}catch(e){}var%20f='http://v.t.sina.com.cn/share/share.php?',u='http://www.wan68.com/answer/info/<{$v.aid}>',p=['url=',e(u),'&title=<{$v.content}>&appkey=691988791&pic=http://www.wan68.com/gl/static/images/foot_logo.png'].join('');function a(){if(!window.open([f,p].join(''),'mb'))u.href=[f,p].join('');};if(/Firefox/.test(navigator.userAgent)){setTimeout(a,0)}else{a()}})(screen,document,encodeURIComponent));" title="分享到新浪微博"  ><i class="questionMoreIcon i4"></i></a>
                                <{/if}>
                                <{if $v.attribute.images}>
                                	<a data='qq' href="javascript:(function(){window.open('http://connect.qq.com/widget/shareqq/index.html?url=http://www.wan68.com/answer/info/<{$v.aid}>&desc='+getShareMessageHot(<{$v.aid}>)+'&title=全民手游攻略分享&pics=<{$v.attribute.images}>&site=全民手游攻略&summary=我在全民手游攻略给你分享，快来看看吧!','分享至QQ好友', '');})()" ><i class="questionMoreIcon i6"></i></a>
                                <{else}>
                                	<a data='qq' href="javascript:(function(){window.open('http://connect.qq.com/widget/shareqq/index.html?url=http://www.wan68.com/answer/info/<{$v.aid}>&desc='+getShareMessageHot(<{$v.aid}>)+'&title=全民手游攻略分享&pics=http://www.wan68.com/gl/static/images/foot_logo.png&site=全民手游攻略&summary=我在全民手游攻略给你分享，快来看看吧!','分享至QQ好友', '');})()" ><i class="questionMoreIcon i6"></i></a>
                                <{/if}>
                                <{if $v.attribute.images}>
                                	<a href="javascript:(function(){window.open('http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=http://www.wan68.com/answer/info/<{$v.aid}>&title='+getShareMessageHot(<{$v.aid}>)+'&title=全民手游攻略分享&pics=<{$data.attribute.images}>&site=全民手游攻略&summary=我在全民手游攻略给你分享，快来看看吧!','分享至QQ空间', '');})()" data='qzone' title="分享至QQ空间"><i class="questionMoreIcon i8"></i></a>
                             	<{else}>
                                	<a href="javascript:(function(){window.open('http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=http://www.wan68.com/answer/info/<{$v.aid}>&title='+getShareMessageHot(<{$v.aid}>)+'&title=全民手游攻略分享&pics=http://www.wan68.com/gl/static/images/foot_logo.png&site=全民手游攻略&summary=我在全民手游攻略给你分享，快来看看吧!','分享至QQ空间', '');})()" data='qzone' title="分享至QQ空间"><i class="questionMoreIcon i8"></i></a>
                             	<{/if}>
                             	<script>
									function getShareMessageHot(id){
										return $("#new_content_info_"+id).val();
									}
								</script>
	                        </div>
	                    </div>
	                    <hr class="f0line">
						<div class="tkBg" id="tkBg_<{$v.aid}>"style="display: none">
							<div class="tk">
								<a href="javascript:void(0)" onclick="chacha(<{$v.aid}>,'new')"><div class="chacha"></div></a>
								<div class="tkTitle"><span>为答案投票</span></div>
								<div class="tpC clear">
									<a href="javascript:void(0)" <{if $v.hasAgree}>onclick="doajax(1,0,'1',<{$v.aid}>,'new')" class="agreea active"<{else}>onclick="doajax(1,1,'<{$uid}>',<{$v.aid}>,'new')" class="agreea"<{/if}> id="agreeaClass_<{$v.aid}>"><div class="agree"><div class="agreeIcon"></div><span>赞同</span></div></a>
									<a href="javascript:void(0)" <{if $v.hasCombat}>onclick="doajax(2,0,'1',<{$v.aid}>,'new')" class="naya active"<{else}>onclick="doajax(2,1,'<{$uid}>',<{$v.aid}>,'new')"class="naya"<{/if}>  id="nayaClass_<{$v.aid}>"><div class="nay"><div class="nayIcon"></div><span>反对</span></div></a>
								</div>
							</div>
						</div>
	        		<{/foreach}>
	        		<{if $answer_info_count > 10}>
                    <div class="addmore"><i class="icon"></i>更多</div>
                    <{/if}>
                </div>
                <a href="/question/answer/<{$data.absId}>" class="reply"><div class="replyMobile"><i class="replyIcon"></i><span>我要回答</span></div></a>
                <!--<div class="replyMobile"><a href="javascript:;" class="reply"><i class="replyIcon"></i><span>我要回答</span></a></div>-->
                
                <div class="tkBg2" style="display: none">
                    <div class="tk2">
							<input type='hidden' id='rtype' name='rtype' value='' />
							<input type='hidden' id='ids' name='ids' value='' />
                        <div class="tk2Up">
                            <a href="javascript:void(0)" onclick="doajax(5,1)"><div class="first">广告垃圾</div></a>
                            <a href="javascript:void(0)" onclick="doajax(5,2)"><div>淫秽色情</div></a>
                            <a href="javascript:void(0)" onclick="doajax(5,3)"><div>虚假消息</div></a>
                            <a href="javascript:void(0)" onclick="doajax(5,4)"><div>敏感信息</div></a>
                            <a href="javascript:void(0)" onclick="doajax(5,0)"><div>其它</div></a>
                        </div>
                        <div class="tk2Down">
                            <a href="javascript:void(0)" onclick="cancel()">取消</a>
                        </div>
                    </div>
                </div>
				<div class="popMobileshare">
					<div class="mobilesharebox">
						<div class="mobileshare">
	                        <div class="swiper-wrapper">
	                            <section class="swiper-slide">
	                                <div class="mobileshareIcons clear">
	                                    <a href="javascript:;" id="shareWB"><div class="mobileshareIcon"><i class="icon i1"></i><p>新浪微博</p></div></a>
	                                    <a href="javascript:;" id="shareQQ"><div class="mobileshareIcon"><i class="icon i3"></i><p>腾讯QQ</p></div></a>
	                                    <a href="javascript:;" id="shareQZ"><div class="mobileshareIcon"><i class="icon i5"></i><p>QQ空间</p></div></a>
	                                    <a href="javascript:;"><div class="mobilejustify_fix"></div></a>
	                                </div> 
	                            </section>
	                        </div>
	                        <div class="swiper-pagination"></div>
	                    </div>
						<div class="mobilereport">
							<div class="mobilereportIcons clear">
								<a href="javascript:;" onclick="report(0,<{$data.absId}>);$('.popMobileshare').hide();" id="举报"><div class="mobilereportIcon"><i class="icon i1"></i><p>举报</p></div></a>
								<{if $data.author.guid == $uid}>
									<a href="/question/ask/<{$data.gameInfo.absId}>/<{$data.absId}>" id="编辑"><div class="mobilereportIcon"><i class="icon i2"></i><p>编辑</p></div></a>
								<{/if}>
								<a href="javascript:;"><div class="mobilereportIcon mobileleft_fix">&nbsp;</div></a>
								<a href="javascript:;"><div class="mobilejustify_fix"></div></a>
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

<{include file="./common/moudle_footer.tpl"}>
<script>
	//我要回答 浮动设置
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
       // console.log("stop:"+stop+";ft.top:"+ft.top);
        if(ft.top<=stop+dh){
            $("div.replyMobile").removeClass("fixter");
        }else{
        	$("div.replyMobile").addClass("fixter");
        }
    }
	//展开全部
	function openAll(id,type,open_type){
		if(type=="hot"){
			if(open_type == 1){
				$("#hot_cont_"+id).hide();
				$("#hot_cont_more_"+id).show();
			}else{
				$("#hot_cont_"+id).show();
				$("#hot_cont_more_"+id).hide();
			}
		}
		if(type=="new"){
			if(open_type == 1){
				$("#new_cont_"+id).hide();
				$("#new_cont_more_"+id).show();
			}else{
				$("#new_cont_"+id).show();
				$("#new_cont_more_"+id).hide();
			}
		}
	}

    function moreBtnClick(content,pic_img){
        if(pageWidth<=996)
        {
        	if(pic_img !=''){
        		var pics = pic_img;
        	}else{
        		var pics = "http://www.wan68.com/gl/static/images/foot_logo.png";
        	}
        	var weibo = "javascript:void((function(s,d,e){try{}catch(e){}var%20f='http://v.t.sina.com.cn/share/share.php?',u='http://www.wan68.com/<{$smarty.server.REQUEST_URI}>',p=['url=',e(u),'&title="+content+"&appkey=691988791&pic="+pics+"'].join('');function a(){if(!window.open([f,p].join(''),'mb'))u.href=[f,p].join('');};if(/Firefox/.test(navigator.userAgent)){setTimeout(a,0)}else{a()}})(screen,document,encodeURIComponent));";
        	
        	var qq = "javascript:mb_share('"+content+"','http://www.wan68.com/<{$smarty.server.REQUEST_URI}>')";
        	//var qq = "javascript:(function(){window.open('http://connect.qq.com/widget/shareqq/index.html?url="+ encodeURIComponent(location.href)+ "&desc="+content+"&title=全民手游攻略分享&pics="+pics+"&site=全民手游攻略&summary=我在全民手游攻略给你分享，快来看看吧!','分享至QQ好友', '');})()";
        	var qz = "javascript:(function(){window.open('http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url='+ encodeURIComponent(location.href)+ '&title="+content+"&title=全民手游攻略分享&pics="+pics+"&site=全民手游攻略&summary=我在全民手游攻略给你分享，快来看看吧!','分享至QQ空间', '');})()";
        	
        	$('#shareWB').attr('href',weibo);
        	$('#shareQQ').attr('href',qq);
        	$('#shareQZ').attr('href',qz);
            $(".popMobileshare").show("fast");
        }
        else{
            $("#questionMoreIcons").toggle(200);
        }

    }
    function zan(aid,type){
    	if('<{$uid}>' !=''){
    		if('<{$data.is_ban}>' == 0){
    			if(type =='new'){
        			$("#tkBg_"+aid).show();
        		}else{
        			$("#tkBg_hot_"+aid).show();
        		}
        	}else{
				confirm_ban();
        	}
    	}else{
    		window.location.href='/user/login?backUrl='+window.location.href;
    	}
    }
    
	function confirm_ban(){
		var del_message = "您的帐号已被管理员严禁发言，有问题请在意见反馈中提交，您还可以加客服QQ：2271250263或客服Q群：460025819进行咨询";
		$('.modal-body').text(del_message);
		
		$('#myModals .modal-footer .btn-default').text('我知道了').hide();
		$('#myModals .modal-footer .btn-primary').text('我知道了');
		
		$('#myModals .modal-footer .btn-primary').click(function(){
			$("#myModals").modal('hide');
		});
		
		$("#myModals").modal('show');
	}
    function chacha(aid,type){
    	if(type=='new'){
        	$("#tkBg_"+aid).hide();
    	}else{
        	$("#tkBg_hot_"+aid).hide();
    	}
    }
    
    function share(id,type,pic_img,info){
    if(pageWidth<=996)
        {
        	if(info != '' ){
        		var content = info;
        	}else{
        		var content = getShareMessageHot(id);
        	}
        	if(pic_img !=''){
        		var pics = pic_img;
        	}else{
        		var pics = "http://www.wan68.com/gl/static/images/foot_logo.png";
        	}
        	var weibo = "javascript:void((function(s,d,e){try{}catch(e){}var%20f='http://v.t.sina.com.cn/share/share.php?',u='http://www.wan68.com/answer/info/"+id+"',p=['url=',e(u),'&title="+content+"&appkey=691988791&pic="+pics+"'].join('');function a(){if(!window.open([f,p].join(''),'mb'))u.href=[f,p].join('');};if(/Firefox/.test(navigator.userAgent)){setTimeout(a,0)}else{a()}})(screen,document,encodeURIComponent));";
        	
        	var qq = "javascript:mb_share('"+content+"','http://www.wan68.com/answer/info/"+id+"')";
        	//var qq = "javascript:(function(){window.open('http://connect.qq.com/widget/shareqq/index.html?url=http://www.wan68.com/answer/info/"+id+"&desc="+content+"&title=全民手游攻略分享&pics="+pics+"&site=全民手游攻略&summary=我在全民手游攻略给你分享，快来看看吧!','分享至QQ好友', '');})()";
        	var qz = "javascript:(function(){window.open('http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=http://www.wan68.com/answer/info/"+id+"&title="+content+"&title=全民手游攻略分享&pics="+pics+"&site=全民手游攻略&summary=我在全民手游攻略给你分享，快来看看吧!','分享至QQ空间', '');})()";
        	
        	$('#shareWB').attr('href',weibo);
        	$('#shareQQ').attr('href',qq);
        	$('#shareQZ').attr('href',qz);
            $(".popMobileshare").show('fast');
            $(".popMobileshare .mobilereport").hide('fast');
        }
        else{
            if(type=='hot'){
            	$("#shareIcons_hot_"+id).toggle(200);
        	}
        	if(type=='new'){
            	$("#shareIcons_new_"+id).toggle(200);
        	}
        }
    }
    function report(type,id){
		$('#rtype').val(type);
		$('#ids').val(id);
        $(".tkBg2").show();
    }
    function cancel(){
        $(".tkBg2").hide();
    }
    function moremore (id,type){
    	if(type){
       	 	$("#moremore_hot_"+id).hide();
	        $("#edit_hot_"+id).show();
	        $("#delete_hot_"+id).show();
	        $("#report_hot_"+id).show();
        }else{
       	 	$("#moremore_"+id).hide();
	        $("#edit_"+id).show();
	        $("#delete_"+id).show();
	        $("#report_"+id).show();
       	}
    }
    // 
    function answer_del(aid){
		$("#answer_id").val(aid);
    }
    function del_answer(){
		var aid = $("#answer_id").val();
		$.ajax({
			'async' : true,// 使用异步的Ajax请求
			'type' : "get",
			'cache':false,
			'url' : "/ajax_fun/answer_del/"+aid+"/",
			'dataType' : "json",
			success : function(e){
				//console.log(e);
				if(e.result == 200){
					$('#myModal').modal('hide');
					$("#hot_answer_div_"+aid).hide();
					$("#answer_div_"+aid).hide();
					myPop('操作成功');
					//alert('操作成功');
				}
			}
		});
    }
    
	function dodownload(msg){
		if(msg){
			var del_message = msg;
		}else{
			var del_message = "安装全民手游攻略app，就可以对您感兴趣的答案进行评论";
		}
		$('.modal-body').text(del_message);
		
		$('#myModal .modal-footer .btn-default').text('取消');
		$('#myModal .modal-footer .btn-primary').text('安装');
		
		$('#myModal .modal-footer .btn-primary').click(function(){
			var del_url = '/download/';
			$("#myModal").modal('hide');
			window.open(del_url);  
		});
		
		$("#myModal").modal('show');
	}
</script>

<script>
	//踩，赞，收藏操作
	function doajax(flag,type,uid,aid,zan_type){
		switch(flag){
			case 1: //执行赞/取消赞操作(答案)
				var url = "/ajax_fun/answer_praise_operate/"+aid+"/" + type;
				break;
			case 2: //执行踩/取消踩操作(答案)
				var url = "/ajax_fun/answer_cai_operate/"+aid+"/" + type;
				break;
			case 3: //执行收藏/取消收藏操作(答案)
				var url = "/follow/answer_collect?mark="+aid+"&action=" + type;	
				break;
			case 4: //问题关注/取消关注(问题)
				var url = "/follow/question_attention?mark="+<{$data['absId']}>+"&action=" + type;	
				break;
			case 5: //问题举报/答案举报
				//根据参数判断
				var rtype = $('#rtype').val();
				var ids = $('#ids').val();
				
				if(rtype == '0'){ //举报问题
					var url = "/ajax_fun/complaint_add/"+ids+"/0/" + type;	
				}else if(rtype == '1'){ //举报答案
					var url = "/ajax_fun/complaint_add/"+ids+"/1/" + type;	
				}else{
					myPop('问题或答案标识丢失');
				}
			
				break;
				
			default:
				myPop('操作标识丢失');
				return false;
				break;
		}
		gl_api_fun(url,flag,type,aid,zan_type);
	}
	
	function gl_api_fun(api_url,flag,type,aid,zan_type){
	
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
								$('#agreeaClass_hot_'+aid+' span').html('已赞同');
								$('#agreeaClass_'+aid+' span').html('已赞同');
								$('#agreeaClass_hot_'+aid).addClass('active');
								$('#agreeaClass_hot_'+aid).attr('onclick','doajax(1,0,"<{$uid}>",'+aid+',\'hot\')');
								$('#agreeaClass_'+aid).addClass('active');
								$('#agreeaClass_'+aid).attr('onclick','doajax(1,0,"<{$uid}>",'+aid+',\'new\')');
								
								//关联操作
								$('#nayaClass_hot_'+aid+' span').html('反对');
								$('#nayaClass_'+aid+' span').html('反对');
								
								$('#nayaClass_hot_'+aid).removeClass('active');
								$('#nayaClass_hot_'+aid).attr('onclick','doajax(2,1,"<{$uid}>",'+aid+',\'hot\')');
								$('#nayaClass_'+aid).removeClass('active');
								$('#nayaClass_'+aid).attr('onclick','doajax(2,1,"<{$uid}>",'+aid+',\'new\')');
								$('#zanUp_hot_'+aid).addClass('active');
								$('#zanDown_hot_'+aid).removeClass('active');
								$('#zanUp_'+aid).addClass('active');
								$('#zanDown_'+aid).removeClass('active');
							}else{
								$('#agreeaClass_hot_'+aid+' span').html('赞同');
								$('#agreeaClass_'+aid+' span').html('赞同');
								$('#agreeaClass_hot_'+aid).removeClass('active');
								$('#agreeaClass_hot_'+aid).attr('onclick','doajax(1,1,"<{$uid}>",'+aid+',\'hot\')');
								$('#agreeaClass_'+aid).removeClass('active');
								$('#agreeaClass_'+aid).attr('onclick','doajax(1,1,"<{$uid}>",'+aid+',\'new\')');
								
								$('#zanUp_'+aid).removeClass('active');
								$('#zanUp_hot_'+aid).removeClass('active');
							}
							
							//修改数量
							$('#zanNum_hot_'+aid).text(e.data.agreeCount);
							$('#zanNum_'+aid).text(e.data.agreeCount);
							myPop('操作成功');
							break;
						case 2: //执行踩/取消踩操作
							if(type == 1){
								$('#nayaClass_hot_'+aid+' span').html('已反对');
								$('#nayaClass_'+aid+' span').html('已反对');
								$('#nayaClass_hot_'+aid).addClass('active');
								$('#nayaClass_hot_'+aid).attr('onclick','doajax(2,0,"<{$uid}>",'+aid+',\'hot\')');
								$('#nayaClass_'+aid).addClass('active');
								$('#nayaClass_'+aid).attr('onclick','doajax(2,0,"<{$uid}>",'+aid+',\'new\')');
								
								//关联操作
								$('#agreeaClass_hot_'+aid+' span').html('赞同');
								$('#agreeaClass_'+aid+' span').html('赞同');
								$('#agreeaClass_hot_'+aid).removeClass('active');
								$('#agreeaClass_hot_'+aid).attr('onclick','doajax(1,1,"<{$uid}>",'+aid+',\'hot\')');
								$('#agreeaClass_'+aid).removeClass('active');
								$('#agreeaClass_'+aid).attr('onclick','doajax(1,1,"<{$uid}>",'+aid+',\'new\')');
								
								$('#zanUp_hot_'+aid).removeClass('active');
								$('#zanDown_hot_'+aid).addClass('active');
								$('#zanDown_'+aid).addClass('active');
								$('#zanUp_'+aid).removeClass('active');
							}else{
								$('#nayaClass_hot_'+aid+' span').html('反对');
								$('#nayaClass_'+aid+' span').html('反对');
								$('#nayaClass_hot_'+aid).removeClass('active');
								$('#nayaClass_hot_'+aid).attr('onclick','doajax(2,1,"<{$uid}>",'+aid+',\'hot\')');
								
								$('#nayaClass_'+aid).removeClass('active');
								$('#nayaClass_'+aid).attr('onclick','doajax(2,1,"<{$uid}>",'+aid+',\'new\')');
								
								$('#zanDown_hot_'+aid).removeClass('active');
								$('#zanDown_'+aid).removeClass('active');
							}
							
							//修改数量
							$('#zanNum_hot_'+aid).text(e.data.agreeCount);
							$('#zanNum_'+aid).text(e.data.agreeCount);
							myPop('操作成功');
							break;
						case 3: //执行收藏/取消收藏操作
							if(type != 1){
								$('#collect_hot_'+aid).addClass('active');
								$('#collect_hot_'+aid).attr('onclick','doajax(3,1,"<{$uid}>",'+aid+',\'hot\')');
								$('#collect_'+aid).addClass('active');
								$('#collect_'+aid).attr('onclick','doajax(3,1,"<{$uid}>",'+aid+',\'new\')');
								myPop('收藏成功');
							}else{
								$('#collect_hot_'+aid).removeClass('active');
								$('#collect_hot_'+aid).attr('onclick','doajax(3,0,"<{$uid}>",'+aid+',\'hot\')');
								$('#collect_'+aid).removeClass('active');
								$('#collect_'+aid).attr('onclick','doajax(3,0,"<{$uid}>",'+aid+',\'new\')');
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
								setTimeout(function(){
									dodownload('安装全民手游攻略APP，可以实时收到问题动态消息哦~')
								},1000);
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
							myPop('操作标识丢失');
							//alert('操作标识丢失');
							return false;
							break;
					}
					chacha(aid,zan_type);
				} else {
					myPop('操作失败');
					//alert('操作失败');
				}
			}
		});
	}
	
	function hideall(){
		$('.tkBg2').hide();
		$(".moreBtn").show();
	}
	
</script>
<script>
	var offset = 2; //默认分页位置
	var firstflag = true; //第一次载入的时候，防止第一个分类没有数据
	var loading_tips = $('.addmore'); //加载更多
	var in_loading = false; //初始话载入状态
	var openflag = false;
	
	//载入产品方法
	function load_info() {
		//判断是否载入
		if(!in_loading) {
			//无刷新获取攻略列表
			$.ajax({
				url:"/ajax_fun/get_qa_list_api/" + <{$data.absId}> + '/' + offset + '/',
				type:"get",
				dataType:"json",
				cache : false,
				async:false,
				beforeSend : function () {
				
				},
				success:function(r) {
					if(r.result == '200') {
						if(r.data.enoughflag == '2') {
							data = r.data.data.data;
							//console.log(data);
							
							var html = "";
							for(var i=0;i<data.length;i++) {
								html += '<div class="detail" id="answer_div_'+data[i].aid+'">';
								html += '<div class="interlocutionTitle">';
								html += '<span class="photo"><img src="'+data[i].u_info.avatar+'"/><span></span></span>';
								if (data[i].u_info.rank ==1){
                                	html += '<a href="/help/"><div class="shen">神</div></a>';
                                }
								html += '<div class="h-box">';
								html += '<p>'+data[i].u_info.nickname;
								if(data[i].u_info.level >0){
									html += '<a href="/help/"><span class="label label-color">LV'+data[i].u_info.level+'</span></a>';
								}
								html += '</p>';
								if(data[i].updateType ==1){
									html += '<span class="time">编辑于：'+data[i].ctime+'</span>';
								}else{
									html += '<span class="time">发布于：'+data[i].ctime+'</span>';
								}
								html += '</div>';
								html += '<a href="javascript:void(0);" onclick="zan('+data[i].aid+',\'new\');">';
								html += '<div class="zanBtn clear">';
								html += '<span class="zanNum" id="zanNum_'+data[i].aid+'">'+data[i].agreeCount+'</span>';
								html += '<div class="upDown">';
								if(data[i].hasAgree){
									html += '<div class="zanUpDiv"><div class="zanUp active" id="zanUp_'+data[i].aid+'"></div></div>';
								}else{
									html += '<div class="zanUpDiv"><div class="zanUp" id="zanUp_'+data[i].aid+'"></div></div>';
								}
								if(data[i].hasCombat){
									html += '<div class="zanDownDiv"><div class="zanDown active"  id="zanDown_'+data[i].aid+'"></div></div>';
								}else{
									html += '<div class="zanDownDiv"><div class="zanDown" id="zanDown_'+data[i].aid+'"></div></div>';
								}
								html += '</div>';
								html += '</div>';
								html += '</a>';
								html += '</div>';
								html += '<div class="zw-cont">';
								html += '<div class="cont-del" id="new_cont_'+data[i].aid+'">';
								html += '<p class="cont" >';
								html += data[i].content;
								
		                        if(data[i].a_img_count >0 || data[i].more_content == 1){
									html += '<span class="showMorePic">';
									if(data[i].a_img_count > 0 ){
										html += '<i class="icon"><span class="picNum">'+data[i].a_img_count+'</span></i>';
									}
									html += '<input type="hidden" name="a_content" id="new_content_'+data[i].aid+'" value="'+data[i].a_content.content+'">';
									html += '<a href="javascript:void(0);" onclick="openAll('+data[i].aid+',\'new\',1);" class="pc_open_all" ><span>展开全部</span><i class="arrowPc"></i></a>';
									html += '</span>';
		                        }
		                        html += '</p>';  	
                                if(data[i].a_img_count >0 || data[i].more_content == 1){
		                        	html += '<a href="javascript:;" class="mobileA openUp" onclick="openAll('+data[i].aid+',\'new\',1);" class="wap_open_all"><i class="arrowMobile" ></i></a>';  
		                        }
		                        html += '</div>';  
		                        html += '<div class="cont-del" id="new_cont_more_'+data[i].aid+'" style="display:none;">';  
		                        html += '<p class="cont">';  
		                        html += data[i].a_content.content;  
		                        html += '<span class="showMorePic">'; 
		                        html += '<a href="javascript:void(0);" onclick="openAll('+data[i].aid+',\'new\',2);"  class="pc_open_all"><span>收回</span><i class="arrowPc2"></i></a>'; 
		                        html += '</span>'; 
		                        html += '</p>'; 
		                        html += '<a href="javascript:;" class="mobileA openUp" onclick="openAll('+data[i].aid+',\'new\',2);" class="wap_open_all" ><i class="arrowMobile2" ></i></a>'; 
		                        html += '</div>';  
		                        html += '</div>';  
		                        html += '<div class="answerMoreIcons clear">';
		                        
		                        if('<{$uid}>' !=''){
		                        	html += '<a href="javascript:void(0);" onclick="window.location.href=\'/user/login?backUrl=\''+window.location.href+';" class="collect" id="collect_'+data[i].aid+'"  ><i class="answerMoreIcon i1"></i><span>收藏</span></a>';
		                        }else{
		                        	html += '<a href="javascript:void(0);" class="collect" id="collect_'+data[i].aid+'" if(data[i].hasCollect){onclick="doajax(3,1,\'<{$uid}>\','+data[i].aid+',\'new\');"} else { onclick="doajax(3,0,\'<{$uid}>\','+data[i].aid+',\'new\');"} } ><i class="answerMoreIcon i1"></i><span>收藏</span></a>';
		                        }
		                        if('<{$uid}>' !=''){
		                        	html += '<a href="javascript:void(0);" class="coet" onclick="dodownload();" ><i class="answerMoreIcon i2"></i><span>评论</span></a>';
		                        }else{
		                        	html += '<a href="javascript:void(0);" onclick="window.location.href=\'/user/login?backUrl=\''+window.location.href+';" class="coet" ><i class="answerMoreIcon i2"></i><span>评论</span></a>';
		                        }
		                        html += '<a href="javascript:void(0);" class="share" onclick="share('+data[i].aid+',\'new\',\'' + data[i].attribute.images + '\',\''+data[i].content+'\');"><i class="answerMoreIcon i3"></i><span>分享</span></a>';
		                        if(data[i].u_info.uid == '<{$uid}>'){
			                        html += '<a href="/question/answer/<{$data.absId}>/'+data[i].aid+'" target="_blank" class="edit" id="edit_'+data[i].aid+'"><i class="answerMoreIcon i5"></i><span>编辑</span></a>';
			                        html += '<a href="javascript:void(0);" onclick="answer_del('+data[i].aid+');"  data-target="#myModal" data-toggle="modal" class="delete" id="delete_'+data[i].aid+'" ><i class="answerMoreIcon i6"></i><span>删除</span></a>';
		                        }
		                           
		                        html += '<a href="javascript:void(0);" class="report" id="report_'+data[i].aid+'" onclick="report(1,'+data[i].aid+');"><i class="answerMoreIcon i7"></i><span>举报</span></a>';
		                        html += '</div>'; 
		                        html += '<input type="hidden" id="new_content_info_'+data[i].aid+'" value="<{$v.content}>">'; 
		                        html += '<div class="questionMoreIcons shareIcons clear" id="shareIcons_new_'+data[i].aid+'" style="display: none">'; 
		                        html += '<i class="zqzwpc"></i><a href="http://service.weibo.com/share/share.php?url=http://www.wan68.com/answer/info/'+data[i].aid+'&title='+data[i].content+'&appkey=691988791&pic='+data[i].attribute.images+'#_loginLayer_1451463215713" title="分享到新浪微博" target="_blank" ><i class="questionMoreIcon i4"></i></a>';
		                        
		                        if(data[i].attribute.images){
		                        	html += '<a href="http://connect.qq.com/widget/shareqq/index.html?url=http://www.wan68.com/answer/info/'+data[i].aid+'&desc='+data[i].content+'&title=全民手游攻略分享&pics='+data[i].attribute.images+'&site=全民手游攻略&summary=我在全民手游攻略给你分享，快来看看吧!" title="分享至QQ好友"  data="qq" target="_blank"  ><i class="questionMoreIcon i6"></i></a>';
		                        }else{
		                        	html += '<a href="http://connect.qq.com/widget/shareqq/index.html?url=http://www.wan68.com/answer/info/'+data[i].aid+'&desc='+data[i].content+'&title=全民手游攻略分享&pics=http://www.wan68.com/gl/static/images/foot_logo.png&site=全民手游攻略&summary=我在全民手游攻略给你分享，快来看看吧!" title="分享至QQ好友"  data="qq" target="_blank"  ><i class="questionMoreIcon i6"></i></a>';
		                        }
		                        if(data[i].attribute.images){
		                        	html += '<a href="http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=http://www.wan68.com/answer/info/'+data[i].aid+'&title='+data[i].content+'&title=全民手游攻略分享&pics='+data[i].attribute.images+'&site=全民手游攻略&summary=我在全民手游攻略给你分享，快来看看吧!" title="分享至QQ空间" data="qzone"  target="_blank"  ><i class="questionMoreIcon i8" ></i></a>';
		                        }else{
		                        	html += '<a href="http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=http://www.wan68.com/answer/info/'+data[i].aid+'&title='+data[i].content+'&title=全民手游攻略分享&pics=http://www.wan68.com/gl/static/images/foot_logo.png&site=全民手游攻略&summary=我在全民手游攻略给你分享，快来看看吧!" title="分享至QQ空间" data="qzone"  target="_blank"  ><i class="questionMoreIcon i8" ></i></a>';
		                        }
		                        html += '</div>';     
		                        html += '</div>';  
		                        html += '<div class="tkBg" id="tkBg_'+data[i].aid+'"style="display: none">'; 
		                        html += '<div class="tk">'; 
		                        html += '<a href="javascript:void(0);" onclick="chacha('+data[i].aid+');"><div class="chacha"></div></a>'; 
		                        html += '<div class="tkTitle"><span>为答案投票</span></div>'; 
		                        html += '<div class="tpC clear">'; 
		                        if(data[i].hasAgree){
		                       		html += '<a href="javascript:void(0);"  onclick="doajax(1,0,\'<{$uid}>\','+data[i].aid+',\'new\');" class="agreea active" id="agreeaClass_'+data[i].aid+'"><div class="agree"><div class="agreeIcon"></div><span>赞同</span></div></a>'; 
		                        }else{
		                         	html += '<a href="javascript:void(0);" onclick="doajax(1,1,\'<{$uid}>\','+data[i].aid+',\'new\');" class="agreea" id="agreeaClass_'+data[i].aid+'"><div class="agree"><div class="agreeIcon"></div><span>赞同</span></div></a>'; 
		                        }
		                        if(data[i].hasCombat){
		                       		html += '<a href="javascript:void(0);" onclick="doajax(2,0,\'<{$uid}>\','+data[i].aid+',\'new\');" class="naya active" id="nayaClass_'+data[i].aid+'"><div class="nay"><div class="nayIcon"></div><span>反对</span></div></a>'; 
		                        }else{
		                         	html += '<a href="javascript:void(0);" onclick="doajax(2,1,\'<{$uid}>\','+data[i].aid+',\'new\');" class="naya"  id="nayaClass_'+data[i].aid+'"><div class="nay"><div class="nayIcon"></div><span>反对</span></div></a>'; 
		                        }
		                        html += '</div>';    
			                    html += '</div>';  
								html += '</div>';  
							}
							$('.addmore').before(html);
							offset += 1;
							if (data.length < 10) {
								loading_tips.hide();
							} else {
								loading_tips.show();
							}
						}else{
							if(openflag){
								myPop('没有更多了噢~');
								//alert('没有更多了噢~');
							}
							loading_tips.hide();
						}
					}else{
						alert('获取数据失败');
					}
				}
			});
		}
	}
</script>
<script type="text/javascript" src="/gl/static/js/share.js"></script>
<script>
var pageWidth = 0, pageHeight = 0;
var mySwiper;
$(document).ready(function(){
	//显示更多信息
	$('.addmore').on('click', function () {
		openflag = true;
		load_info();
    	$(".pc_open_all").hide();
	});
	
	
	pageWidth = $(window).width();
    pageHeight = $(window).height();
    if(pageWidth <= 996){ //wap
    	$(".pc_open_all").hide();
    }
    if(pageWidth >= 997){ //pc
    	$(".wap_open_all").hide();
    }
    $(window).resize();
});
</script>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog">
        <div class="modal-content confirm">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p>确认删除该答案嘛？</p>
            </div>
            <div class="modal-footer">
            	<input type="hidden" id="answer_id" value=""/>
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" onclick="del_answer();">确认删除</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="myModals" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog">
        <div class="modal-content confirm">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p>确认删除该答案嘛？</p>
            </div>
            <div class="modal-footer">
            	<input type="hidden" id="answer_id" value=""/>
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" onclick="del_answer();">确认删除</button>
            </div>
        </div>
    </div>
</div>

<{if $isPop == 2}>
	<script>
		$(function(){
			myPop('操作成功');
		});
	</script>
<{/if}>
<{if $isPop == 1}>
	<div class="popdiv popSUCC">
		<div class="tipcont">
			<i><img src="/gl/static/images/v1/gold_icon.png"/></i>
			<p>提问奖励<span>10</span>经验值</p>
		</div>
	</div>
	<script>
		$(function(){
			setTimeout(function(){$('.popdiv.popSUCC').fadeOut('slow');},2000);
		});
		function b(b){return"function"==a(b)};
        function c(a){return null!=a&&a==a.window};
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
    function mb_share(titile,urls){
        window.location.href = b("mqqapi://share/to_fri?src_type=web&version=1&file_type=news", {share_id: "1101685683",title: Base64.encode(titile),thirdAppDisplayName: Base64.encode("全民手游攻略"),url: Base64.encode(urls)});
    }
</script>

<{include file="./common/footer.tpl"}>