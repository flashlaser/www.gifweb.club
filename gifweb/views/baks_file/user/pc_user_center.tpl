<{include file='common/header.tpl'}>
<link rel="stylesheet" href="/gl/static/css/common.css">
<link rel="stylesheet" href="/gl/static/css/userinfo.css">
<script language="javascript" type="text/javascript" src="/gl/static/js/ajaxfileupload.js"></script>
<script>
var data_id = '';
var data_api= '<{$act}>';
<{if $act eq 'follow_article' }>
	data_id = 'glsclistPC';
<{elseif $act eq 'follow_question'}>
	data_id = 'gzwtlistPC';
<{elseif $act eq 'follow_answers'}>
	data_id = 'dasclistPC';
<{elseif $act eq 'question'}>
	data_id = 'asklistPC';
<{elseif $act eq 'answers'}>
	data_id = 'answerlistPC';
<{elseif $act eq 'my_message'}>
	data_id = 'mymsglistPC';
	data_api= 'get_message';
<{else}>
	data_id = 'gameslistPC';
	data_api= 'follow_game';
<{/if}>
function getList(id,api){
	if(data_id == ''){
		return false;
	}
	
	$.ajax({
		type : 'POST',
		dataType : 'json',
		cache : false,
		url : '/user/'+data_api,
		data : {is_ajax: 1},
		success:function(res){
			if (res.result == '200') {
				$("#"+data_id).html(res.data);


                if(data_id=='asklistPC' || data_id=='answerlistPC')
                {
                    $(".edit-btn-pc .pcdelete").hide();
                }
                else
                {
                    $(".edit-btn-pc .pcdelete").show();
                }


			}
			if(res.message == 0){
				$(".edit-btn-pc .pcdelete").hide();		
			}
		}
	});
}
$(document).ready(function(){
	// 加载用户经验等级
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
	
	 // 加载列表
	 getList(data_id, data_api);
})
</script>

<div class="highlight"> <div></div> </div>
<div class="nav">
    <div class="maintop">
        <div class="myuserinfo">
            <a class="userphoto" href="javascript:;"><img src="<{$userinfo.avatar}>" id="upload" class="img-circle" /><input name="upfile" type="file" id="inputFile" accept="image/*" /><{if $userinfo.rank eq 1}><span class="shen" onclick="location.href='/help/';">神</span><{/if}></a>
            <div class="user-dt">
                <div class="myname">
                    <p class="namep"><{$userinfo.nickname}></p><input type="text" placeholder="2-14字符,支持中英文、数字、'-','_'” " class="nameinput hidden"><i class="infoicon" id="editname"></i><input type="hidden" id="old_name" value="<{$userinfo.nickname}>" />
                </div>
                <a class="label label-color" href="/help/" target="_blank">LV<{$userinfo.level}></a>
                <div class="pgsdiv">
                    <div class="progress myprogress">
                        <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0;">
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
                    <div class="pcAction">
                        <ul class="row pcTabs active-info">
                            <li role="presentation" class="col-info-1 <{if $act eq 'follow_game' || !$act}>active<{/if}>">
                                <a href="javascript:;"  data-id="gameslistPC" data-api="follow_game" <{if $act eq 'follow_game' || !$act}>style="border-color: #5677fc;"<{/if}>><i class="infoiconpc games"></i>关注游戏<span class="badge"><{$user_center_count.follow_game_counts}></span> </a>
                            </li>
                            <li role="presentation" class="col-info-1 <{if $act eq 'follow_article'}>active<{/if}>">
                                <a href="javascript:;" data-id="glsclistPC" data-api="follow_article"><i class="infoiconpc glsc" <{if $act eq 'follow_article'}>style="border-color: #5677fc;"<{/if}> ></i>攻略收藏<span class="badge"><{$user_center_count.follow_article_counts}></span></a></li>
                            <li role="presentation" class="col-info-1 <{if $act eq 'question'}>active<{/if}>">
                                <a href="javascript:;"  data-id="asklistPC" data-api="question"><i class="infoiconpc ask" <{if $act eq 'question'}>style="border-color: #5677fc;"<{/if}>></i>我的提问<span class="badge"><{$user_center_count.my_ask_counts}></span></a></li>
                            <li role="presentation" class="col-info-1 <{if $act eq 'answers'}>active<{/if}>" >
                                <a href="javascript:;" data-id="answerlistPC" data-api="answers" <{if $act eq 'answers'}>style="border-color: #5677fc;"<{/if}>><i class="infoiconpc answer"></i>我的回答<span class="badge"><{$user_center_count.my_answer_counts}></span></a></li>
                            <li role="presentation" class="col-info-1 <{if $act eq 'follow_question'}>active<{/if}>" >
                                <a href="javascript:;" data-id="gzwtlistPC" data-api="follow_question" <{if $act eq 'follow_question'}>style="border-color: #5677fc;"<{/if}>><i class="infoiconpc gzwt"></i>我关注的问题<span class="badge"><{$user_center_count.follow_question_counts}></span></a></li>
                            <li role="presentation"  class="col-info-1 <{if $act eq 'follow_answers'}>active<{/if}>" >
                                <a href="javascript:;" data-id="dasclistPC" data-api="follow_answers" <{if $act eq 'follow_answers'}>style="border-color: #5677fc;"<{/if}>><i class="infoiconpc dasc"></i>答案收藏<span class="badge"><{$user_center_count.follow_answer_counts}></span></a>
                            </li>
                            <li role="presentation" class="col-info-1 <{if $act eq 'my_message'}>active<{/if}>" >
                                <a href="javascript:;" data-id="mymsglistPC" data-api="get_message" <{if $act eq 'my_message'}>style="border-color: #5677fc;"<{/if}>><i class="infoiconpc mymsg"></i>我的通知<span class="badge"><{$user_center_count.my_message}></span></a></li>
                        </ul>

                        <div class="tab-content contpc">
                            <div class="edit-btn-pc">
                                <div class="pcdelete">
                                    <i class="infoiconpc"></i>
                                    <span>编辑</span>
                                </div>
                                <div class="pcbtn-dele">
                                    <span class="cancel">取消</span>
                                    <span class="deleted">删除</span>
                                </div>
                            </div>
		                	<!------------------------------默认展示关注游戏列表--------------------------->
                            <div id="gameslistPC" role="tabpanel" class="tab-pane contlist <{if $act eq 'follow_game' || !$act}>active<{/if}>">
								<{if $list}>
                                <ul class="gamelist clearfix">
									<!--<{foreach from=$list item=g key=key}>-->
                                    <li>
                                        <div class="game-dtl">
                                            <div class="imgbox">
                                                <img style="cursor:pointer;"  alt="<{$g.abstitle}>" src="<{$g.absImage}>"/>
                                                <input type="checkbox" autocomplete="off" value="<{$g.absId}>">
                                                <i class="cimg checkbox1"></i>
                                            </div>
                                            <p><a title="<{$g.abstitle}>" target="_blank" href="/zq/juhe_page/<{$g.absId}>"><{substr_forecast str=$g.abstitle num='8'}></a></p>
                                        </div>
                                    </li>
									<!--<{/foreach}>-->
                                </ul>
								<{include file='user/page.tpl'}>
								<{else}>
								<div class="cont-empty" style="display:block;">
									<div><i><img src="/gl/static/images/v1/empty.png"></i><p>这里什么都没有哦~</p></div>
								</div>
								<{/if}>
                            </div>
							<!--攻略收藏-->
							<div id="glsclistPC" role="tabpanel" class="tab-pane contlist <{if $act eq 'follow_article'}>active<{/if}>"></div>
							<!--提问列表-->
							<div id="asklistPC" class="tab-pane contlist <{if $act eq 'question'}>active<{/if}>"></div>
							<!--回答列表-->
							<div id="answerlistPC" class="tab-pane contlist <{if $act eq 'answers'}>active<{/if}>"></div>
							<!--关注问题列表-->
							<div id="gzwtlistPC" class="tab-pane contlist <{if $act eq 'follow_question'}>active<{/if}>"></div>
							<!--关注答案列表-->
							<div id="dasclistPC" class="tab-pane contlist <{if $act eq 'follow_answers'}>active<{/if}>"></div>
							<!--通知列表-->
							<div id="mymsglistPC" class="tab-pane contlist <{if $act eq 'my_message'}>active<{/if}>"></div>	
						
                        </div>
                    </div>

                </div>
    </div>
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


function togameUrls(id)
{
	location.href='http://www.wan68.com/zq/juhe_page/'+id;
}

   $(function(){
    //点击打开文件选择器
    $("#upload").click(function() {
        $('#inputFile').click();
    });
});

//选择文件之后执行上传
$('.myuserinfo').on('change','#inputFile',function() {
    myPop('修改中...',10);
    $.ajaxFileUpload({
    	type:'GET',
        url:'/user/edit_user',
        secureuri:false,
        fileElementId:'inputFile',//file标签的id
        dataType: 'json',//返回数据的类型
        data:{'action':1},//一同上传的数据
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
});


</script>
<script src="/gl/static/js/userinfo.js?v=1001"></script>
<script>
(function(jQuery){  
if(jQuery.browser) return;  
jQuery.browser = {};  
jQuery.browser.mozilla = false;  
jQuery.browser.webkit = false;  
jQuery.browser.opera = false;  
jQuery.browser.msie = false;  
var nAgt = navigator.userAgent;  
jQuery.browser.name = navigator.appName;  
jQuery.browser.fullVersion = ''+parseFloat(navigator.appVersion);  
jQuery.browser.majorVersion = parseInt(navigator.appVersion,10);  
var nameOffset,verOffset,ix;  
// In Opera, the true version is after "Opera" or after "Version"  
if ((verOffset=nAgt.indexOf("Opera"))!=-1) {  
jQuery.browser.opera = true;  
jQuery.browser.name = "Opera";  
jQuery.browser.fullVersion = nAgt.substring(verOffset+6);  
if ((verOffset=nAgt.indexOf("Version"))!=-1)  
jQuery.browser.fullVersion = nAgt.substring(verOffset+8);  
}  
// In MSIE, the true version is after "MSIE" in userAgent  
else if ((verOffset=nAgt.indexOf("MSIE"))!=-1) {  
jQuery.browser.msie = true;  
jQuery.browser.name = "Microsoft Internet Explorer";  
jQuery.browser.fullVersion = nAgt.substring(verOffset+5);  
}  
// In Chrome, the true version is after "Chrome"  
else if ((verOffset=nAgt.indexOf("Chrome"))!=-1) {  
jQuery.browser.webkit = true;  
jQuery.browser.name = "Chrome";  
jQuery.browser.fullVersion = nAgt.substring(verOffset+7);  
}  
// In Safari, the true version is after "Safari" or after "Version"  
else if ((verOffset=nAgt.indexOf("Safari"))!=-1) {  
jQuery.browser.webkit = true;  
jQuery.browser.name = "Safari";  
jQuery.browser.fullVersion = nAgt.substring(verOffset+7);  
if ((verOffset=nAgt.indexOf("Version"))!=-1)  
jQuery.browser.fullVersion = nAgt.substring(verOffset+8);  
}  
// In Firefox, the true version is after "Firefox"  
else if ((verOffset=nAgt.indexOf("Firefox"))!=-1) {  
jQuery.browser.mozilla = true;  
jQuery.browser.name = "Firefox";  
jQuery.browser.fullVersion = nAgt.substring(verOffset+8);  
}  
// In most other browsers, "name/version" is at the end of userAgent  
else if ( (nameOffset=nAgt.lastIndexOf(' ')+1) <  
(verOffset=nAgt.lastIndexOf('/')) )  
{  
jQuery.browser.name = nAgt.substring(nameOffset,verOffset);  
jQuery.browser.fullVersion = nAgt.substring(verOffset+1);  
if (jQuery.browser.name.toLowerCase()==jQuery.browser.name.toUpperCase()) {  
jQuery.browser.name = navigator.appName;  
}  
}  
// trim the fullVersion string at semicolon/space if present  
if ((ix=jQuery.browser.fullVersion.indexOf(";"))!=-1)  
jQuery.browser.fullVersion=jQuery.browser.fullVersion.substring(0,ix);  
if ((ix=jQuery.browser.fullVersion.indexOf(" "))!=-1)  
jQuery.browser.fullVersion=jQuery.browser.fullVersion.substring(0,ix);  
jQuery.browser.majorVersion = parseInt(''+jQuery.browser.fullVersion,10);  
if (isNaN(jQuery.browser.majorVersion)) {  
jQuery.browser.fullVersion = ''+parseFloat(navigator.appVersion);  
jQuery.browser.majorVersion = parseInt(navigator.appVersion,10);  
}  
jQuery.browser.version = jQuery.browser.majorVersion;  
})(jQuery);  
</script>
<{include file='common/footer.tpl'}>