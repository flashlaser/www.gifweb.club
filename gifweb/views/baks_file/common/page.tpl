<{if $page_data.total_rows > $page_data.page_size }>
<ul class="pagination">
    <li>
    	<{if $page_data.page <= 1}>
        <a class="disable">
        <span aria-hidden="true">上一页</span>
        </a>
        <{else}>
        <a href="<{if $page_data.page <= 1}>javascript:void(0)<{else}><{$page_data.url_prefix}><{($page_data.page - 1)}><{if $page_from =='search'}>#content_info<{/if}><{/if}>" aria-label="Previous">
            <span aria-hidden="true">上一页</span>
        </a>
        <{/if}>
    </li>
    
   
    <{assign var="loop" value="<{$page_data.page_end}>"}>
　　
	<{section name="loop" start=$page_data.page_begin-1 loop=$loop}>
	<li class="<{if ($smarty.section.loop.index+1)  == $page_data.page }>active<{/if}>">
		<a href="<{if ($smarty.section.loop.index+1)  == $page_data.page }>javascript:void(0);<{else}><{$page_data.url_prefix}><{$smarty.section.loop.index+1}><{if $page_from =='search'}>#content_info<{/if}><{/if}>"><{$smarty.section.loop.index+1}></a>
	</li>
　　<{/section}>
    
    <li>
    	<{if $page_data.page >= $page_data.total_pages}>
        <a class="disable" >
            <span aria-hidden="true">下一页</span>
        </a>
        <{else}>
    	<a href="<{if $page_data.page >= $page_data.total_pages}>javascript:void(0)<{else}><{$page_data.url_prefix}><{($page_data.page+1)}><{if $page_from =='search'}>#content_info<{/if}><{/if}>" aria-label="Next">
        <span aria-hidden="true">下一页</span>
        </a>
        <{/if}>
    </li>
</ul>
<{/if}>