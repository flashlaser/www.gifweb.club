<!--<{if $page_data.pages > 1}>-->
<!--分页控件-->
<nav id="nav">
	<ul class="pagination">
		<input type="hidden" value="<{$act}>" />
		<!--头部分-->
		<{if $page_data.curr_page > 0 }>
			<li><a href="javascript:;" <{if $page_data.curr_page eq 1}>style="cursor:default;"<{/if}> aria-label="Previous" data-page="<{$page_data.curr_page - 1}>"><span aria-hidden="true">上一页</span></a></li>
			<{if $page_data.curr_page eq 1}>
				<li class="active"><a href="javascript:;" data-page="1">1</a></li>
			<{elseif $page_data.curr_page > 4 && $page_data.more}>
				<li ><a href="javascript:;" data-page="1" >1</a><!--...--></li>
			<{else}>
				<li ><a href="javascript:;" data-page="1">1</a></li>
			<{/if}>
		<{/if}>
		
		<!--中间部分-->
		<{foreach from=$page_data.middle_page item=item key=key}>
			<li <{if $item eq 1}>class="active"<{/if}>><a href="javascript:;" data-page="<{$key}>"><{$key}></a></li>
		<{/foreach}>
		
		<!--尾部分-->
		<{if $page_data.curr_page < $page_data.pages}>
			<{if $page_data.curr_page > ($page_data.pages-3) && $page_data.more}>
				<li ><!--...--><a href="javascript:;" data-page="<{$page_data.pages}>"><{$page_data.pages}></a></li>
				<li><a style="cursor:pointer;" aria-label="Next" data-page="<{$page_data.curr_page + 1}>"><span aria-hidden="true">下一页</span></a></li>
			<{else}>
				<li ><a href="javascript:;" data-page="<{$page_data.pages}>"><{$page_data.pages}></a></li>
				<li><a style="cursor:pointer;" aria-label="Next" data-page="<{$page_data.curr_page + 1}>"><span aria-hidden="true">下一页</span></a></li>
			<{/if}>
		<{elseif $page_data.curr_page eq $page_data.pages}>
			<li class="active"><a href="javascript:;" data-page="<{$page_data.pages}>"><{$page_data.pages}></a></li>
			<li><a aria-label="Next" data-page="0" style="cursor:default;" ><span aria-hidden="true">下一页</span></a></li>
		<{else}>
			<li ><a href="javascript:;" data-page="<{$page_data.pages}>"><{$page_data.pages}></a></li>
			<li><a  aria-label="Next" style="cursor:pointer;" data-page="<{$page_data.curr_page + 1}>"><span aria-hidden="true">下一页</span></a></li>
		<{/if}>
	</ul>
</nav>
<!--<{/if}>-->