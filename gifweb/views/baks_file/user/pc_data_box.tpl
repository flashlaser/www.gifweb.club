<!--<{if $act eq 'follow_game'}>-->
<!--我关注的游戏-->
<ul class="gamelist clearfix">
	<!--<{foreach from=$list item=g key=key}>-->
	<li>
		<div class="game-dtl">
			<div class="imgbox">
				<img title="<{$g.abstitle}>" src="<{$g.absImage}>" style="cursor:pointer;"/>
				<input type="checkbox" autocomplete="off" value="<{$g.absId}>">
				<i class="cimg checkbox1"></i>
			</div>
			<p><a title="<{$g.abstitle}>"  target="_blank" href="/zq/juhe_page/<{$g.absId}>"><{substr_forecast str=$g.abstitle num='8'}></a></p>
		</div>
	</li>
	<!--<{/foreach}>-->
</ul>
<{include file='user/page.tpl'}>
<!--<{elseif $act eq 'follow_article'}>-->
<!--我关注的攻略-->
<ul class="btn-group haveline">
	<!--<{foreach from=$list item=item key=key}>-->
	<li>
		<label class="btn infoicon checkboxs ">
			<input type="checkbox" autocomplete="off" value="<{$item.absId}>">
		</label>
		<div class="glsc-detail">
			<h2><a href="/raiders/info/<{$item.absId}>" target="_blank"><{$item.abstitle}></a></h2>
			<span><{$item.updateTime}></span>
		</div>
	</li>
	<!--<{/foreach}>-->
</ul>
<{include file='user/page.tpl'}>
<!--<{elseif $act eq 'question'}>-->
<!--我发表的问题-->
<ul class="haveline">
	<!--<{foreach from=$list item=item key=key}>-->
	<li>
		<div class="ask-detail">
			<h2><a href="/question/info/<{$item.absId}>" target="_blank"><{if $item.status eq 1}>[已关闭]<{/if}><{substr_forecast str=$item.abstitle num='200' dot='...'}></a></h2>
			<div class="divbottom">
				<span>有<{$item.answerCount|default:0}>个回答</span>
				<span class="txt-align-r"><{$item.updateTime}></span>
			</div>
		</div>
	</li>
	<!--<{/foreach}>-->
</ul>
<{include file='user/page.tpl'}>
<!--<{elseif $act eq 'answers'}>-->
<!--我发表的答案-->
<ul class="haveline">
	<!--<{foreach from=$list item=item key=key}>-->
	<li>
		<div class="answer-detail">
			<h2 class="oneline"><a href="/question/info/<{$item.questionInfo.absId}>" target="_blank"><{if $item.questionInfo.status eq 1}>[已关闭]<{/if}><{substr_forecast str=$item.questionInfo.abstitle num='200' dot='...'}></h2>
			<div class="cont-detail clear">
				<span class="da">答</span>
				<div class="answer-cont">
					<p><a href="/answer/info/<{$item.absId}>" target="_blank"><{if $item.status eq 1}>[已关闭]<{/if}>“<{substr_forecast str=$item.abstitle num='280' dot='...'}>”</a></p>
					<span><{$item.updateTime}></span>
				</div>
			</div>

		</div>
	</li>
	<!--<{/foreach}>-->
</ul>
<{include file='user/page.tpl'}>
<!--<{elseif $act eq 'follow_question'}>-->
<!--我关注的问题-->
<ul class="btn-group haveline">
	<!--<{foreach from=$list item=item key=key}>-->
	<li>
		<label class="btn infoicon checkboxs">
			<input type="checkbox" autocomplete="off" value="<{$item.absId}>">
		</label>
		<div class="ask-detail">
			<h2><a href="/question/info/<{$item.absId}>" target="_blank"><{if $item.status eq 1}>[已关闭]<{/if}><{substr_forecast str=$item.abstitle num='200' dot='...'}></a></h2>
			<div class="divbottom">
				<span>有<{$item.answerCount}>个回答</span>
				<span class="txt-align-r"><{$item.updateTime}></span>
			</div>
		</div>
	</li>
	<!--<{/foreach}>-->
</ul>
<{include file='user/page.tpl'}>
<!--<{elseif $act eq 'follow_answers'}>-->
<!--我收藏的答案-->
<ul class="btn-group haveline">
	<!--<{foreach from=$list item=item key=key}>-->
	<li>
		<label class="btn infoicon checkboxs ">
			<input type="checkbox" autocomplete="off" value="<{$item.absId}>">
		</label>
		<div class="answer-detail">
			<h2 class="oneline"><a href="/question/info/<{$item.questionInfo.absId}>" target="_blank"><{if $item.questionInfo.status eq 1}>[已关闭]<{/if}><{substr_forecast str=$item.questionInfo.abstitle num='200' dot='...'}></a></h2>
			<div class="cont-detail clear">
				<span class="da">答</span>
				<div class="answer-cont">
					<p><a href="/answer/info/<{$item.absId}>" target="_blank"><{if $item.status eq 1}>[已关闭]<{/if}>“<{substr_forecast str=$item.abstitle num='280' dot='...'}>”</a></p>
					<span><{$item.updateTime}></span>
				</div>
			</div>

		</div>
	</li>
	<!--<{/foreach}>-->
</ul>
<{include file='user/page.tpl'}>
<!--<{elseif $act eq "get_message"}>-->
<!--我的通知-->
<ul class="btn-group">
	<{foreach from=$returns item=item key=key}>
	<li>
		<label class="btn infoicon checkboxs">
			<input type="checkbox" autocomplete="off" value="<{$item.absId}>">
		</label>
		<div class="mymsg-detail clear">
			<{if $item.flag eq 1}>
				<span class="msgda">答</span>
			<{elseif $item.flag eq 2}>
				<span class="ping">评</span>
			<{elseif $item.flag eq 3}>
				<span class="zan">赞</span>
			<{else}>
				<span class="infoicon system"></span>
			<{/if}>
			<div class="msg-cont">
				<{if $item.flag > 0}>
				<p><{$item.title}><span style="padding-left:30px;"><{$item.updateTime}></span></p>
				<{else}>
				<p>系统通知<span style="padding-left:30px;"><{$item.updateTime}></span></p>
				<{/if}>
				<span>
					<{if $item.url}>
						<a href="<{$item.url}>" target="_blank">“<{substr_forecast str=$item.subtitle num='150' dot='...'}>”</a>
					<{else}>
						”<{substr_forecast str=$item.subtitle num='150' dot='...'}>“
					<{/if}>
				</span>
			</div>
		</div>
	</li>
	<{/foreach}>
</ul>
<{include file='user/page.tpl'}>
<!--<{elseif $act eq 'show_empty'}>-->
<!--无符合条件内容-->
<div class="cont-empty" style="display:block;">
	<div><i><img src="/gl/static/images/v1/empty.png"></i><p>这里什么都没有哦~</p></div>
</div>
<!--<{/if}>-->
<script type="text/javascript">
$(document).ready(function(){
	//pc 分页处理
	$(".pagination li a").on("click", function(e){
		var page = $(this).attr("data-page");
		var data_api =  $(this).parent().parent().find("input").val();
		var data_id = $(".active-info .active a").attr('data-id');
		
		if(page == 0){
			return false;
		}
		
		// 获取列表信息
		$.ajax({
			type : 'POST',
			dataType : 'json',
			cache : false,
			url : '/user/'+data_api,
			data : {is_ajax: 1, page: page},
			success:function(res){
				if (res.result == '200') {
					//隐藏删除操作
					$(".pcbtn-dele").hide();
					if(data_id=='asklistPC' || data_id=='answerlistPC')
					{
						$(".edit-btn-pc .pcdelete").hide();
					}
					else
					{
						$(".edit-btn-pc .pcdelete").show();
					}
					
					$("#"+data_id).html(res.data);
				}
			}
		})
	});
	/* 删除选项操作 */
	$(".btn-group li label").on('click',function(e){
		var inputbox = $(this).find("input[type=checkbox]");
		if($(this).hasClass("active")){
			$(this).removeClass("active");
			inputbox.prop("checked", false);
			inputbox.removeAttr("checked");
		}else{
			$(this).addClass("active");
			inputbox.prop("checked", true);
            inputbox.attr("checked","true");
		}
		return false;
	});
	
	/* 删除关注游戏选项操作 */
	$(".game-dtl .imgbox").on('click', function(){
		var can = $(".pcbtn-dele").css("display");
		var inputbox = $(this).find("input[type=checkbox]"), checki = $(this).find("i.cimg");
		var id = inputbox.val();
		if(inputbox.css("display") != "none" && can != "none"){
			if(inputbox.attr("checked") == "checked"){
				checki.removeClass("checked");
                checki.addClass("checkbox1");
				inputbox.prop("checked", false);
				inputbox.removeAttr("checked");
			}else{
				checki.addClass("checked");
                checki.removeClass("checkbox1");
				inputbox.prop("checked", true);
				inputbox.attr("checked","true");
			}
		}else{
			window.open("/zq/juhe_page/"+id);
		}
		return false;
	});
});
</script>