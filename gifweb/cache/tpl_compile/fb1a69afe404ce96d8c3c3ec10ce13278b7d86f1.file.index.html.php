<?php /* Smarty version Smarty-3.1.12, created on 2017-05-10 09:44:43
         compiled from "gifweb/views/index.html" */ ?>
<?php /*%%SmartyHeaderCode:168083870059116b55c01df2-85141517%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'fb1a69afe404ce96d8c3c3ec10ce13278b7d86f1' => 
    array (
      0 => 'gifweb/views/index.html',
      1 => 1494380559,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '168083870059116b55c01df2-85141517',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_59116b55c3f5d0_79643501',
  'variables' => 
  array (
    'content_list' => 0,
    'item' => 0,
    'pages' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_59116b55c3f5d0_79643501')) {function content_59116b55c3f5d0_79643501($_smarty_tpl) {?>﻿<?php echo $_smarty_tpl->getSubTemplate ("./common/header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<div class="w">
        <div class="main">
            <ul class="list">
			
			
				<?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['item']->_loop = false;
 $_smarty_tpl->tpl_vars['key'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['content_list']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value){
$_smarty_tpl->tpl_vars['item']->_loop = true;
 $_smarty_tpl->tpl_vars['key']->value = $_smarty_tpl->tpl_vars['item']->key;
?>
                <li>
                    <div class="cola">
                        <span><?php echo $_smarty_tpl->tpl_vars['item']->value['format_date'];?>
</span>
                    </div>
                    <div class="colb">
                        <div class="title"><strong><a href="index/info/?id=<?php echo $_smarty_tpl->tpl_vars['item']->value['cid'];?>
" target="_blank"><?php echo $_smarty_tpl->tpl_vars['item']->value['title'];?>
</a></strong><span class='new'>最新</span></div>
                        <div class="info">
                            <div class="mb10">
                                <span class="g9 mr50">
                                    by <?php echo $_smarty_tpl->tpl_vars['item']->value['user_name'];?>

                                </span>
                                <span class="tag">
                                    
                                </span>
                                <span class="g9 ml50">发布时间<?php echo $_smarty_tpl->tpl_vars['item']->value['create_time'];?>
</span>
                            </div>
                        </div>

                        <div class="con">
                            <table cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        <img class='bigimg' src="<?php echo $_smarty_tpl->tpl_vars['item']->value['img_url'];?>
" />
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="share">
                            <span class="dc">
                                <span name="newdigg" id="newdigg13736"></span><a href="/" target="_blank" class="reply">0</a>
                            </span>
                            <span id="bdshare" class="bdshare_b" style="line-height: 12px;float:none;color:#4A7CB3;" data="{
		                    'text':'<?php echo $_smarty_tpl->tpl_vars['item']->value['title'];?>
...',
                            'pic':'<?php echo $_smarty_tpl->tpl_vars['item']->value['img_url'];?>
',
                            'url':'http://www.gifweb.club/index/info?id=<?php echo $_smarty_tpl->tpl_vars['item']->value['cid'];?>
'
                            }">
                                分享
                            </span>
                        </div>
                        <div class="commentlist">
                            <p class="ct">全部评论(<span>0</span>)</p>
                            <ul id="msglist13736">
                                
                            </ul>
                        </div>
                        <div class="showcom" data-id="<?php echo $_smarty_tpl->tpl_vars['item']->value['cid'];?>
"><span>发表评论</span></div>
                        <div class="commentbox" style="display:none;">
                            <div class="post">
                                <p>发表评论</p>
                                <textarea data-max="300" data-toggle="text-limit" placeholder="来来来~唠两句"></textarea>
                                <div class="ovh">
                                    <input type="button" class="btnPost r" aid="13736" value="评论" /><span><em class="n">0</em>/300</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
				<?php } ?>
            </ul>
            <div class="mt20"></div>



            <?php echo $_smarty_tpl->tpl_vars['pages']->value;?>

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