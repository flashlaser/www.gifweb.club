<?php /* Smarty version Smarty-3.1.12, created on 2017-05-09 16:02:04
         compiled from "gifweb/views/info.html" */ ?>
<?php /*%%SmartyHeaderCode:11210050955911777c8d9019-39311827%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'fbef0a41a92415a790152648405d967336305e18' => 
    array (
      0 => 'gifweb/views/info.html',
      1 => 1493965762,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '11210050955911777c8d9019-39311827',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'info' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_5911777c9028b6_28993912',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5911777c9028b6_28993912')) {function content_5911777c9028b6_28993912($_smarty_tpl) {?>﻿<?php echo $_smarty_tpl->getSubTemplate ("./common/header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<div class="w">
        <dv class="main">
            <ul class="list">
                <li>
                    <div class="cola">
                        <span>05-05</span>
                    </div>
                    <div class="colb">
                        <div class="title"><strong><?php echo $_smarty_tpl->tpl_vars['info']->value['title'];?>
</strong></div>
                        <div class="info">
                            <div class="mb10">
                                <span class="g9 mr50">
                                    by <?php echo $_smarty_tpl->tpl_vars['info']->value['user_name'];?>

                                </span>
                                
                                <span class="tag">
                                    动物
                                </span>
                                <span class="g9 ml50"><span id="vvclick">100</span>人浏览</span>
                            </div>
                        </div>
                        <div class="con">
                            <table cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        
                                        <img src="<?php echo $_smarty_tpl->tpl_vars['info']->value['img_url'];?>
"/>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="share">
                            <span class="dc">
                                <span name="newdigg" id="newdigg13736"></span><!--<a href="/fun/13736.html" target="_blank" class="reply">[field:id function='GetReplayCount(@me)'/]</a>-->
                            </span>
                            <div class="bdsharebuttonbox"><a href="#" class="bds_more" data-cmd="more"></a><a href="#" class="bds_sqq" data-cmd="sqq" title="分享到QQ好友"></a><a href="#" class="bds_weixin" data-cmd="weixin" title="分享到微信"></a><a href="#" class="bds_tsina" data-cmd="tsina" title="分享到新浪微博"></a><a href="#" class="bds_qzone" data-cmd="qzone" title="分享到QQ空间"></a><a href="#" class="bds_tieba" data-cmd="tieba" title="分享到百度贴吧"></a><a href="#" class="bds_huaban" data-cmd="huaban" title="分享到花瓣"></a><a href="#" class="bds_copy" data-cmd="copy" title="分享到复制网址"></a></div>
                            <script>    window._bd_share_config={ "common": { "bdSnsKey": {},"bdText": "","bdMini": "2","bdMiniList": false,"bdPic": "","bdStyle": "2","bdSize": "24" },"share": {} };with(document) 0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src='http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion='+ ~(-new Date()/36e5)];</script>
                        </div>
                        
                        <div class="showcom" aid="13736"><span>收起评论</span>(0)</div>
                        <div class="commentbox">
                            <div class="post">
                                <p>发表评论</p>
                                <textarea data-max="300" data-toggle="text-limit" placeholder="给个面子，来说两句~"></textarea>
                                <div class="ovh">
                                    <input type="button" class="btnPost r" aid="13736" value="发表" /><span><em class="n">0</em>/300</span>
                                </div>
                            </div>
                            <div class="commentlist">
                                <p class="ct">全部评论(<span>0</span>)</p>
                                <p>最新评论</p>
                                <ul>
                                    <li>游客：1</li>
									<li>游客：2</li>
                                </ul>
                            </div>
                        </div>
                        <div class="mt20"></div>
                      </div>
                </li>
                <script>
                    getDigg(13736);
                </script>
            </ul>
            <div class="mt20"></div>

        </div>
</div>
<?php echo $_smarty_tpl->getSubTemplate ('./common/right.html', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

    
<?php echo $_smarty_tpl->getSubTemplate ('./common/footer.html', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<script>
$(function(){
showmsg('嘎嘎噶哈哈哈');
});

</script>
	
	


<?php }} ?>