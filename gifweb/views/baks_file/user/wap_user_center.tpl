<{include file='common/header.tpl'}>
<link rel="stylesheet" href="/gl/static/css/common.css">
<link rel="stylesheet" href="/gl/static/css/userinfo.css">
<!-- <script language="javascript" type="text/javascript" src="/gl/static/js/ajaxfileupload.js"></script> -->

<script>
$(function(){
	$.ajax({
	 	type: "POST",
	 	data: {},
        dataType: "json",
	    url: '/user/getLevelExp',
	    dataType:'json',
	    success: function(data){
	     //console.log(data);
	     $('.progress-bar').attr('aria-valuenow',data.data.pct);
	     $('.progress-bar').css('width',function(index, value) {return data.data.pct+'%';});
	    /* $('#exps_per').html(data.data.pct+'%');*/
		 $('#exps').html("经验值&nbsp;&nbsp;"+data.data.totalExperience+"/"+data.data.nextLevelExperience);
	    }
	  });
})

</script>

<div class="highlight"> <div></div> </div>
<div class="nav">
    <div class="maintop">
        <div class="myuserinfo">
            <a class="userphoto" href="javascript:;"> <div id="container" class="editor" style="width:100%;height: 100%;"><img src="<{$userinfo.avatar}>" id="upload" class="img-circle" /></div><input name="upfile" type="file" id="inputFile" accept="image/*" /><{if $userinfo.rank eq 1}><span class="shen" onclick="location.href='/help/';">神</span><{/if}></a>
            <div class="user-dt">
                <div class="myname">
                    <p class="namep"><{$userinfo.nickname}></p><input type="text" placeholder="2-14字符,支持中英文、数字" class="nameinput hidden"><i class="infoicon" id="editname"></i><input type="hidden" id="old_name" value="<{$userinfo.nickname}>" />
                </div>
                <span class="label label-color" onclick="location.href='/help/';">LV<{$userinfo.level}></span>
                <div class="pgsdiv">
                    <div class="progress myprogress">
                        <div class="progress-bar"  role="progressbar" aria-valuenow="1" aria-valuemin="0" aria-valuemax="100" style="width: 1%;">
                        </div>
                    </div>
                   <!--  <span class="barnum" id="exps_per">0</span> -->
					<span class="barnum" id="exps"></span>
                </div>
            </div>
        </div>
        <div class="info-login"><i></i><p>点击登录</p></div>

    </div>
</div>
<div id="main" class="pccont-width">
    <div class="wrap">
                <div class="maincenter">
                    <ul class="phoneAction row nav active-info">
                        <li class="col-xs-12 col-sm-12 <{if $act=='follow_game'}>active<{/if}>">
                            <a href="#gameslist" is_show_data='<{if !$list}>0<{else}>1<{/if}>'><i class="iconpc games"></i>关注游戏<span class="caret"></span>
                            </a>
                            <div class="makeEdit" style="display: <{if $act=='follow_game'}>block<{else}>none<{/if}>;"  >
                                <div class="delete">
                                    <i class="infoicon deletepic"></i>
                                </div>
                                <div class="edit-btn">
                                    <span href="javascript:void(0)" class="cancel">取消</span>
                                    <span href="#" class="deleted" id="follow_game">删除</span>
                                </div>
                            </div>
                        </li>
                        <div class="tab-content cont">
							
                            <div id="gameslist" class="tab-pane contlist <{if $act=='follow_game'}>active<{/if}>">
                            	<!--<{if !$list}>-->
	                            <div class="cont-empty">
	                                <div><i><img src="/gl/static/images/v1/empty.png"></i><p>这里什么都没有哦~</p></div>
	                            </div>
								<!--<{else}>-->
                                <ul class="btn-group" data-toggle="buttons" id="gamelist_ul">
									<!--<{foreach from=$list item=g key=key}>-->
                                    <li id="follow_game_li<{$g.absId}>">
                                        <label class="btn infoicon checkboxs" data-del="<{$g.absId}>">
                                            <input type="checkbox" value="<{$g.absId}>" autocomplete="off" />
                                            <input type="hidden" class="hidden_input" value="0" />
                                        </label>
                                        <div class="game-detail">
                                            <div class="clear" onclick="location.href='http:\/\/www.wan68.com\/zq\/juhe_page\/<{$g.absId}>';">
                                                <div class="fl gameimg"><img src="<{$g.absImage}>"/></div>
                                                <div class="fl gmname">
                                                    <a href="http:\/\/www.wan68.com\/zq\/juhe_page\/<{$g.absId}>"><p><{$g.abstitle}></p></a>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
									<!--<{/foreach}>-->
                                </ul>
                                <{if $page_data.curr_page < $page_data.pages}>
                                <div class="addmore" id="more_follow_game" page-data="1" wap-data-api="follow_game"><i class="icon"></i>更多</div>
                                <{/if}>
                            	<!--<{/if}>-->
                            </div>
                        </div>
                        <!--------------------------攻略收藏 start----------------------------->
                        <li class="col-xs-12 col-sm-12 <{if $act=='follow_article'}>active<{/if}>">
                            <a href="#glsclist"  is_show_data='<{if !$follow_article_data.list}>0<{else}>1<{/if}>'><i class="iconpc glsc"></i>攻略收藏<span class="caret"></span></a>
                            <div class="makeEdit" style="display: <{if $act=='follow_article'}>block<{else}>none<{/if}>;"  >
                                <div class="delete">
                                    <i class="infoicon deletepic"></i>
                                </div>
                                <div class="edit-btn">
                                    <span href="javascript:void(0)" class="cancel">取消</span>
                                    <span href="#" class="deleted" id="follow_gl">删除</span>
                                </div>
                            </div>
                        </li>
                        
                        <div class="tab-content cont">
                            
                            <div id="glsclist" class="tab-pane contlist <{if $act=='follow_article'}>active<{/if}>">
                            	<!--<{if !$follow_article_data.list}>-->
		                        <div class="cont-empty">
		                            <div><i><img src="/gl/static/images/v1/empty.png"></i><p>这里什么都没有哦~</p></div>
		                        </div>
								<!--<{else}>-->
                                <ul class="btn-group" data-toggle="buttons" id="followarticlelist_ul" >
                                	
                                	<!--<{foreach from=$follow_article_data.list item=item key=key}>-->
                                    <li id="follow_gl_li<{$item.absId}>">
                                        <label class="btn infoicon checkboxs" data-del="<{$item.absId}>">
                                            <input type="checkbox" autocomplete="off" checked>
                                            <input type="hidden" class="hidden_input" value="0" />
                                        </label>
                                        <div class="glsc-detail" onclick="togameUrls('<{$item.absId}>',2);">
                                            <h2><{$item.abstitle}></h2>
                                            <span><{$item.updateTime}></span>
                                        </div>
                                    </li>
                                    <!--<{/foreach}>-->
                                </ul>
                                <{if $follow_article_data.page_data.curr_page < $follow_article_data.page_data.pages}>
                                <div class="addmore" id="more_follow_article" page-data="1" wap-data-api="follow_article"><i class="icon"></i>更多</div>
                                <{/if}>
                                <!--<{/if}>-->
                            </div>
                            
                        </div>
                        
                        <!--------------------------攻略收藏 end----------------------------->
                        
                        <!----------------------我的提问  start----------------------------------------->
                        <li class="col-xs-12 col-sm-12 <{if $act=='question'}>active<{/if}>">
                            <a href="#asklist"><i class="iconpc ask"></i>我的提问<span class="caret"></span></a></li>
                        <div class="tab-content cont">
                            <div id="asklist" class="tab-pane contlist <{if $act=='question'}>active<{/if}>">
                            	<!--<{if !$my_ask_data.list}>-->
		                        <div class="cont-empty">
		                            <div><i><img src="/gl/static/images/v1/empty.png"></i><p>这里什么都没有哦~</p></div>
		                        </div>
								<!--<{else}>-->
                                <ul id="questionlist_ul">
                                	<!--<{foreach from=$my_ask_data.list item=item key=key}>-->
                                    <li>
                                        <div class="ask-detail"  onclick="togameUrls('<{$item.absId}>',3);">
                                            <h2><{if $item.status eq 1}>[已关闭]<{/if}><{$item.abstitle}></h2>
                                            <div class="divbottom">
                                                <span>有<{$item.answerCount|default:0}>个回答</span>
                                                <span class="txt-align-r"><{$item.updateTime}></span>
                                            </div>
                                        </div>
                                    </li>
                                   <!--<{/foreach}>-->
                                </ul>
                                <{if $my_ask_data.page_data.curr_page < $my_ask_data.page_data.pages}>
                                 <div class="addmore" id="more_question" page-data="1" wap-data-api="question"><i class="icon"></i>更多</div>
                                 <{/if}>
                            <!--<{/if}>-->
                            </div>
                        </div>
                        
                        <!----------------------我的提问  end----------------------------------------->
                        
                        <!----------------------我的回答  start----------------------------------------->
                        <li class="col-xs-12 col-sm-12 <{if $act=='answers'}>active<{/if}>">
                            <a href="#answerlist"><i class="iconpc answer"></i>我的回答<span class="caret"></span></a></li>
                        <div class="tab-content cont">
                            <div id="answerlist" class="tab-pane contlist <{if $act=='answers'}>active<{/if}>">
                            	<!--<{if !$my_answers_data.list}>-->
		                        <div class="cont-empty">
		                            <div><i><img src="/gl/static/images/v1/empty.png"></i><p>这里什么都没有哦~</p></div>
		                        </div>
								<!--<{else}>-->
                                <ul id="answerslist_ul">
                                	<!--<{foreach from=$my_answers_data.list item=item key=key}>-->
                                    <li>
                                        <div class="answer-detail" >
                                            <h2 class="oneline" onclick="togameUrls('<{$item.questionInfo.absId}>',3);"><{if $item.questionInfo.status eq 1}>[已关闭]<{/if}><{substr_forecast str=$item.questionInfo.abstitle num='50' dot='...'}></h2>
                                            <div class="cont-detail clear">
                                                <span class="fl da">答</span>
                                                <div class="fl answer-cont">
                                                    <p onclick="togameUrls('<{$item.absId}>',4);"><{if $item.status eq 1}>[已关闭]<{/if}>“<{substr_forecast str=$item.abstitle num='60' dot='...'}>”</p>
                                                    <span><{$item.updateTime}></span>
                                                </div>
                                            </div>

                                        </div>
                                    </li>
                                   <!--<{/foreach}>-->
                                </ul>
                                <{if $my_answers_data.page_data.curr_page < $my_answers_data.page_data.pages}>
                                <div class="addmore" id="more_answers" page-data="1" wap-data-api="answers"><i class="icon"></i>更多</div>
                                <{/if}>
                            <!--<{/if}>-->
                            </div>
                        </div>
                        
                        <!----------------------我的回答  end----------------------------------------->
                         <!----------------------我关注的问题  start----------------------------------------->
                        <li class="col-xs-12 col-sm-12 <{if $act=='follow_question'}>active<{/if}>">
                            <a href="#gzwtlist" is_show_data='<{if !$follow_question_data.list}>0<{else}>1<{/if}>'><i class="iconpc gzwt"></i>我关注的问题<span class="caret"></span></a>
                            <div class="makeEdit"  style="display: <{if $act=='follow_question'}>block<{else}>none<{/if}>;"  >
                                <div class="delete">
                                    <i class="infoicon deletepic"></i>
                                </div>
                                <div class="edit-btn">
                                    <span href="javascript:void(0)" class="cancel">取消</span>
                                    <span href="#" class="deleted" id="follow_question">删除</span>
                                </div>
                            </div>
                        </li>
                        <div class="tab-content cont">
                            <div id="gzwtlist" class="tab-pane contlist <{if $act=='follow_question'}>active<{/if}>">
                            	<!--<{if !$follow_question_data.list}>-->
		                        <div class="cont-empty">
		                            <div><i><img src="/gl/static/images/v1/empty.png"></i><p>这里什么都没有哦~</p></div>
		                        </div>
								<!--<{else}>-->
                                <ul class="btn-group" data-toggle="buttons" id="followquestionlist_ul">
                                	<!--<{foreach from=$follow_question_data.list item=item key=key}>-->
                                    <li id="follow_question_li<{$item.absId}>">
                                        <label class="btn infoicon checkboxs" data-del="<{$item.absId}>">
                                            <input type="checkbox" autocomplete="off" checked>
                                            <input type="hidden" class="hidden_input" value="0" />
                                        </label>
                                        <div class="ask-detail"  onclick="togameUrls('<{$item.absId}>',5);">
                                            <h2><{if $item.status eq 1}>[已关闭]<{/if}><{substr_forecast str=$item.abstitle num='60' dot='...'}></h2>
                                            <div class="divbottom">
                                                <span>有<{$item.answerCount}>个回答</span>
                                                <span class="txt-align-r"><{$item.updateTime}></span>
                                            </div>
                                        </div>
                                    </li>
                                    <!--<{/foreach}>-->
                                </ul>
                                <{if $follow_question_data.page_data.curr_page < $follow_question_data.page_data.pages}>
                                <div class="addmore" id="more_follow_question" page-data="1" wap-data-api="follow_question"><i class="icon"></i>更多</div>
                                <{/if}>
                            <!--<{/if}>-->
                            </div>
                        </div>
                        <!----------------------我关注的问题  end----------------------------------------->
                        <li class="col-xs-12 col-sm-12 <{if $act=='follow_answers'}>active<{/if}>">
                            <a href="#dasclist" is_show_data='<{if !$follow_answer_data.list}>0<{else}>1<{/if}>'><i class="iconpc dasc"></i>答案收藏<span class="caret"></span></a>
                            <div class="makeEdit" style="display: <{if $act=='follow_answers'}>block<{else}>none<{/if}>;"  >
                                <div class="delete">
                                    <i class="infoicon deletepic"></i>
                                </div>
                                <div class="edit-btn">
                                    <span class="cancel">取消</span>
                                    <span class="deleted" id="follow_answer">删除</span>
                                </div>
                            </div>
                        </li>
                        <div class="tab-content cont">
                            <div id="dasclist" class="tab-pane contlist <{if $act=='follow_answers'}>active<{/if}>">
                            	<!--<{if !$follow_answer_data.list}>-->
		                        <div class="cont-empty">
		                            <div><i><img src="/gl/static/images/v1/empty.png"></i><p>这里什么都没有哦~</p></div>
		                        </div>
								<!--<{else}>-->
                                <ul class="btn-group" data-toggle="buttons" id="followanswerlist_ul">
                                	<!--<{foreach from=$follow_answer_data.list item=item key=key}>-->
                                    <li id="follow_answer_li<{$item.absId}>">
                                        <label class="btn infoicon checkboxs" data-del="<{$item.absId}>">
                                            <input type="checkbox" autocomplete="off" checked>
                                            	<input type="hidden" class="hidden_input" value="0" />
                                        </label>
                                        <div class="answer-detail" >
                                            <h2 class="oneline" onclick="togameUrls('<{$item.questionInfo.absId}>',3);"><{if $item.questionInfo.status eq 1}>[已关闭]<{/if}><{substr_forecast str=$item.questionInfo.abstitle num='50' dot='...'}></h2>
                                            <div class="cont-detail clear">
                                                <span class="da">答</span>
                                                <div class="answer-cont">
                                                    <p  onclick="togameUrls('<{$item.absId}>',6);"><{if $item.status eq 1}>[已关闭]<{/if}>“<{substr_forecast str=$item.abstitle num='60' dot='...'}>”</p>
                                                    <span><{$item.updateTime}></span>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <!--<{/foreach}>-->
                                </ul>
                                <{if $follow_answer_data.page_data.curr_page < $follow_answer_data.page_data.pages}>
                                <div class="addmore" id="more_follow_answers" page-data="1" wap-data-api="follow_answers"><i class="icon"></i>更多</div>
                                <{/if}>
                            <!--<{/if}>-->
                            </div>
                        </div>
                        <li class="col-xs-12 col-sm-12 <{if $act=='my_message'}>active<{/if}>">
                            <a href="#mymsglist" is_show_data='<{if !$get_message_data.returns}>0<{else}>1<{/if}>'><i class="iconpc mymsg"></i>我的通知<span class="caret"></span></a>
                            <div class="makeEdit" style="display: <{if $act=='my_message'}>block<{else}>none<{/if}>;"  >
                                <div class="delete" ><i class="infoicon deletepic"></i></div>
                                <div class="edit-btn">
                                    <span href="javascript:void(0)" class="cancel">取消</span>
                                    <span href="#" class="deleted" id="get_message">删除</span>
                                </div>
                            </div>
                        </li>
                        <div class="tab-content cont">
                            <div id="mymsglist" class="tab-pane contlist <{if $act=='my_message'}>active<{/if}>">

                                <!--<{if !$get_message_data.returns}>-->
                                <div class="cont-empty">
                                    <div><i><img src="/gl/static/images/v1/empty.png"></i><p>这里什么都没有哦~</p></div>
                                </div>
                                <!--<{else}>-->

                                <ul class="btn-group" data-toggle="buttons">
                                    <!--<{foreach from=$get_message_data.returns item=item key=key}>-->
                                    <li id="get_message_li<{$item.absId}>">
                                        <label class="btn infoicon checkboxs" data-del="<{$item.absId}>">
                                            <input type="checkbox" autocomplete="off" checked>
                                            <input type="hidden" class="hidden_input" value="0" />
                                        </label>
                                        <div class="mymsg-detail clear">
                                        	
                                        	
                                        	<{if $item.flag=='0' || $item.flag=='4'}>
                                            <span class="infoicon system"></span>
                                            <div class="msg-cont"  <{if $item.url}>onclick="location.href='<{$item.url}>';"<{/if}> >
                                                <div class="systip">
                                                    <span><{if $item.status eq 1}>[已关闭]<{/if}><{$item.title}></span>
                                                    <{if $item.flag=='0'}><span class="txt-align-r"><{$item.updateTime}></span><{/if}>
                                                </div>
                                                <span><{$item.subtitle}></span>
                                            </div>
                                            <{else}>
                                            	
                                            <{if $item.flag==1}><span class="msgda">答</span><{/if}>
                                            <{if $item.flag==2}><span class="ping">评</span><{/if}>
                                            <{if $item.flag==3}><span class="zan">赞</span><{/if}>
                                            
                                            <div class="msg-cont" <{if $item.url}>onclick="location.href='<{$item.url}>';"<{/if}> >
                                                <p><{$item.title}></p>
                                                <span><{$item.subtitle}></span>
                                            </div>
                                            <{/if}>
                                            
                                            
                                        </div>
                                    </li>
                                    <!--<{/foreach}>-->
                                </ul>
                                <!--<{/if}>-->
                            </div>
                        </div>
                    </ul>
                </div>
    </div>
</div>
<div class="popTip" id="popTip" style='display:none;'>
    <p>操作成功 </p>
</div>
<script>
    $(document).ready(function(){

        $(window).resize();

    })
    $(window).resize(function(){
        var pageWidth = $(window).width();
        var h = 0;
        if(pageWidth >= 980){
            h =285;
        }else{
            h = parseInt(pageWidth/375*142);
        }

        $(".maintop").height(h);
        var pt = parseInt((h -$('.myuserinfo').height())/2);
        $('.myuserinfo').css("padding",pt+'px 0');
    })
    

   $(function(){
    //点击打开文件选择器
    $("#upload").click(function() {
        $('#inputFile').click();
    });
});

//选择文件之后执行上传
/*$('.myuserinfo').on('change','#inputFile',function() {
    myPop('修改中...',10);
    $.ajaxFileUpload({
    	type:'GET',
        url:'/user/edit_user',
        secureuri:false,
        fileElementId:'inputFile',//file标签的id
        dataType: 'json',//返回数据的类型
        data:{'action':1,},//一同上传的数据
        success: function (data, status) {
            //把图片替换
            if(data.result=='200')
            {
            	$('#upload').attr('src',data.data.data);
            }
            myPop(data.message);
            
        },
        error: function (data, status, e) {
            myPop(e);
        }
    });
});*/

</script>
<script src="/gl/static/js/userinfo.js?v=1001"></script>
<script type="text/javascript">
    (function(){
        // name space
        window.page = {};

        // disabled default events
        document.documentElement.addEventListener('touchmove', function(e){
            //e.preventDefault();
        });
    })();
</script>
<script language="javascript" type="text/javascript" src="/gl/static/js/upload.min.js"></script>

<script>

    Zepto(function($){
        page.eidtor = new tg.ImageEditor({
            trigger: $('#inputFile'),
            container: $('#container'),
            stageX:  $('#container')[0].offsetLeft,
            event: {
                beforechange: function(){
                    myPop('修改中...',10);
                },
                change: function(){
                   page.eidtor.toDataURL(function(data){
                        $.ajax({
                            type:'post',
                            url:'/user/edit_user',
                            dataType: 'json',//返回数据的类型
                            data:{'pic':data,},//一同上传的数据
                            success: function (data, status) {
                                //把图片替换
                                if(data.result=='200')
                                {
                                    $('#upload').attr('src',data.data.data);
                                    $("#headtop i.photo img").attr("src", data.data.data);
                                }
                                myPop(data.message);
                                
                            },
                            error: function (data, status, e) {
                                myPop(e);
                            }
                        });
                    })
                }
            }
        });
    
    });

</script>

<{include file='common/footer.tpl'}>