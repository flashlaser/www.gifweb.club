<{include file='common/header.tpl'}>

<script src="/gl/static/js/user.js"></script>
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="/gl/static/js/slider.js" charset="utf-8"></script>
<script>
function un_game_follow(id)
{
	
	alert(id);
	$.ajax({
	 	type: "POST",
	 	data: {action:1,mark:id},
        dataType: "json",
	    url: '/follow/game_attention',
	    dataType:'json',
	    success: function(data){
	     alert(data.message);
	    }
	  });
	
}

function un_answer_follow(id)
{
	
	alert(id);
	$.ajax({
	 	type: "POST",
	 	data: {action:1,mark:id},
        dataType: "json",
	    url: '/follow/answer_collect',
	    dataType:'json',
	    success: function(data){
	     alert(data.message);
	    }
	  });
	
}
</script>

<div>
	<a href="?act=follow_game_list">关注游戏</a>
</div>
<div>
	<a href="?act=question_list">问题列表</a>
</div>
<div>
	<a href="?act=answer_list">答案列表</a>
</div>
<div>
	<a href="?act=collect_answers">关注答案</a>
</div>
<div>
	<a href="?act=collect_gl">攻略收藏</a>
</div>

<hr>

<{if $act=='follow_game_list'}>
<div>
	<{foreach from=$data_list item=item}>
	<div id="game_div_<{$item.absId}>">
	<img width="60px" height="60px" src="<{$item.absImage}>" />
	<a href="<{$item.absId}>"><{$item.abstitle}></a>&nbsp;<a href="javascript:;" onclick="un_game_follow(<{$item.absId}>)">取消</a>
	</div>
	<{/foreach}>
</div>


<{else if $act=='question_list'}>
<div>
	<{foreach from=$data_list item=item}>
	<a href="<{$item.absId}>"><{$item.abstitle}></a><{$item.answerCount}><br>
	<{/foreach}>
</div>
<{else if $act=='answer_list'}>
<div>
	<{foreach from=$data_list item=item}>
	<a href="<{$item.absId}>"><{$item.abstitle}></a><{$item.answerCount}><br>
	<{/foreach}>
</div>
<{else if $act=='collect_answers'}>
<div>
	<{foreach from=$data_list item=item}>
	<a href="<{$item.absId}>"><{$item.abstitle}></a><{$item.answerCount}>&nbsp;<a href="javascript:;" onclick="un_answer_follow(<{$item.absId}>)">取消收藏</a><br>
	<{/foreach}>
</div>
<{else if $act=='collect_gl'}>
<div>
	<{foreach from=$data_list item=item}>
	<a href="<{$item.absId}>"><{$item.abstitle}></a><br>
	<{/foreach}>
</div>
<{/if}>
	
<div>
	
	<!-- 分页显示 -->
	<{include file='common/page.tpl'}>
	<!-- /分页显示 -->
	
</div>
<{include file='common/footer.tpl'}>