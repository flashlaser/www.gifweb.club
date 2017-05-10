<{include file="./common/header.tpl"}>
<link rel="stylesheet" href="/gl/static/css/ask.css">
<script type="text/javascript" src="/gl/static/js/kindeditor/kindeditor-all-min.js"></script>
<script type="text/javascript" src="/gl/static/js/kindeditor/lang/zh_CN.js"></script>  
<{if !$data.ismobile}>
<script>
	KindEditor.ready(function(K) {
		window.editor = K.create('#editor_id',{
			resizeType : 1,
			height: 300,
			width: '100%',
			allowPreviewEmoticons : false,
			allowImageUpload : false,
			items : [
					'undo', 'redo', '|', 'image',
				],
			readonlyMode : false,
			filterMode : false,
			allowImageUpload:true,
			allowImageRemote : false,
			uploadJson : '/question/q_upload_img/',
			filePostName : 'avatar',
			fontSizeTable : 14,
			afterUpload:function(){
				//获取当前图片数量
				var imgnum = $('#imgnum').val();
				imgnum = parseInt(imgnum) + 1;
				$('#imgnum').val(imgnum);
				
				if(imgnum > 10){
					myPop('只能上传十张图片哦');
				}
				
				editor.blur();
				editor.focus();
			},
			afterChange: function(){this.sync();}  
		});
	});
</script>
<{/if}>
<div id="main" class="cont-width">
    <div class="row">
        <div class="leftPart">
            <div class="content">
                <div class="ask-content">
                    <div class="phonenav">
                        <i class="icon"></i><span>提问</span>
                    </div>
                    <form action="/question/question_save/" method='post'>
                        <div class="box-title">
                            <div><span class="line"></span>游戏名称<div class="packup"></div></div>
                        </div>
                        <div class="gamename"><input class="form-control" autocomplete="off" onchange="limitLength(value,38,'游戏名称','searchgamename')" name='searchgamename' id='searchgamename'  type="text" placeholder="输入您想要提问的游戏" value='<{$data.game_info.title}>'>
                            <input type='hidden' id='absId' name='absId' value='<{$data.question.absId}>' />
                            <input type='hidden' id='gameid' name='gameid' value='<{$data.gid}>' />
							<input type='hidden' id='imgnum' name='imgnum' value='<{if $data.imgnum}><{$data.imgnum}><{else}>0<{/if}>' />
							<div class="listbox" style='display:none;'>
                                <ul class="asklist">
									<!-- <li class='choosegame' style='display:none;'>默认</li> -->
                                </ul>
                            </div>
                        </div>
                        <div class="box-title">
                            <div><span class="line"></span>问题描述<div class="packup"></div></div>
                        </div>
                        <div class="question">
                            <textarea id="editor_id" name='content' class="form-control" rows="4" placeholder="请填写下你的问题"><{$data.question.content}></textarea>
                            <!--
							<div class="selectpic"><i class="icon"></i>插入图片<input type="file" id="inputFile" accept="image/*"></div>
							-->
                        </div>
                        <div class="form-group">
                            <button type="button" class="btn btn-default cancle">取消</button>
                            <button type="submit" onclick='return changesta()' class="btn btn-default ok">提交</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
		
		<!--pc右侧公共-->
		<{include file="./common/moudle_pc_right.tpl"}>
    </div>
</div>
<{include file="./common/moudle_footer.tpl"}>
<script>
    $(window).resize(function(){

    })
    $(window).resize();
    var pageWidth = $(window).width();
    if(pageWidth >= 980 ){
        $(".ask a").click(function(){
            $(".askQuestion").toggle();
        })
    }
	
	var formsta = 1;
	function changesta(){
		formsta = 2;
		
		if(<{$data.is_ban}>){
			confirm_ban();
			return false;
		}
		
		return true;
	}
	
	window.onbeforeunload = function(event) { 
		var addcontent = $('#editor_id').val();
		if(addcontent && addcontent.length>0 && formsta  == 1){
			(event || window.event).returnValue = "您编辑的内容尚未提交，离开会使内容丢失。"; 
		}
	}
</script>
<script>
	$(function(){
		//点击显示
		$('#searchgamename').click(function(){
			showsearch();
			return false;
		}).blur(function(){
		}).keyup(function(){
			var inname = $(this).val();
			inname = $.trim(inname);
			//$(this).val(inname);
			$("#gameid").val('');
			
			if(inname && inname.length){
				getnamelist_api(inname);
			}else{
				return false;
			}
			
			limitLength(inname,40,'游戏名称','searchgamename');
		});
		
		$('body').click(function(){
			hidesearch();
		});
		
		$('.asklist').delegate('.choosegame', 'click', function(){
			//获得当前游戏内容与ID
			var choosegname = $(this).attr('data-content');
			var choosegid = $(this).attr('data-id');
			
			//放置
			$('#searchgamename').val(choosegname);
			$('#gameid').val(choosegid);
			
			hidesearch();
			return false;
		});
		
		$('.form-group .cancle').click(function(){
			//获取输入内容
			var addcontent = $('#editor_id').val();
			if(addcontent.length > 0){ //确定是否输入内容
				confirm_out();
			}else{
				go_back();
			}
		});
	});
	
	//返回上一页
	function go_back(){
		var go_url = document.referrer;
		if(go_url == window.location.href){
			window.location.href = '/';
		}else{
			window.location.href = go_url;
		}
	}
	
	//显示提示区域
	function showsearch(){
		if($('.asklist li').length>0){
			$('.listbox').show('fast');
		}
	}
	
	//隐藏提示区域
	function hidesearch(){
		$('.listbox').hide('fast');
	}
	
	function limitLength(value, byteLength, title, attribute) { 
		var newvalue = value.replace(/[^\x00-\xff]/g, "**"); 
		var length = newvalue.length; 

		//当填写的字节数小于设置的字节数 
		if (length * 1 <= byteLength * 1){ 
			return; 
		} 
		var limitDate = newvalue.substr(0, byteLength); 
		var count = 0; 
		var limitvalue = ""; 
		
		for (var i = 0; i < limitDate.length; i++) { 
			var flat = limitDate.substr(i, 1); 
			if (flat == "*") { 
				count++; 
			} 
		} 
		var size = 0; 
		var istar = newvalue.substr(byteLength * 1 - 1, 1);//校验点是否为“×” 

		//if 基点是×; 判断在基点内有×为偶数还是奇数  
		if (count % 2 == 0) { 
			//当为偶数时 
			size = count / 2 + (byteLength * 1 - count); 
			limitvalue = value.substr(0, size); 
		} else { 
			//当为奇数时 
			size = (count - 1) / 2 + (byteLength * 1 - count); 
			limitvalue = value.substr(0, size); 
		} 
		//alert(title + "最大输入" + byteLength + "个字节（相当于"+byteLength /2+"个汉字）！"); 
		document.getElementById(attribute).value = limitvalue; 
		return; 
	}
	
	//放置数据
	function putdata(data){
		if(data.length < 1){
			return false;
		}
		/*
		//如果结果正好存在，则将对应数值直接放入
		if(data.length == 1){
			var choosegname = data[0].abstitle;
			var choosegid = data[0].absId;
			
			//放置
			$('#searchgamename').val(choosegname);
			$('#gameid').val(choosegid);
		}*/
		
		$('.asklist').html(''); //清空之前搜索
		
		var html = '';
		//拼装数据
		for(var i in data) {
			html += '<li class="choosegame" data-id=' + data[i].absId + ' data-content="' + data[i].abstitle + '"><p>' + data[i].abstitle + '</p></li>';
			//html += '<li onclick="alert(1);document.getElementById(\'searchgamename\').value=\'' + data[i].abstitle + '\';document.getElementById(\'gameid\').value=\'' + data[i].absId + '\';alert(11);hidesearch(1);"><p>' + data[i].abstitle + '</p></li>';
		}
		
		//放置内容
		$('.asklist').html(html); 
		showsearch();
	}
	
	//异步获取游戏名称数据
	function getnamelist_api(inname){
		$.ajax({
			'async' : true,// 使用异步的Ajax请求
			'type' : "get",
			'cache':false,
			'url' : "/ajax_fun/getgame_list_api",
			'dataType' : "json",
			'data' : {
				'inname':inname
			},
			success : function(e){
				//console.log(e);
				if(e.result == 200){
					if(e.data.count > 0){
						//放置数据
						putdata(e.data.resultList);
					}else{
						hidesearch();
					}
				} else {
					alert('数据获取异常');
				}
			}
		});
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
	function confirm_out(){
		var del_message = "放弃已编辑内容？";
		$('.modal-body').text(del_message);
		
		$('#myModal .modal-footer .btn-default').text('取消');
		$('#myModal .modal-footer .btn-primary').text('确定');
		
		$('#myModal .modal-footer .btn-primary').click(function(){
			$("#myModal").modal('hide');
			formsta = 2;
			go_back();
			
		});
		
		$("#myModal").modal('show');
	}

	function confirm_ban(){
		var del_message = "您的帐号已被管理员严禁发言，有问题请在意见反馈中提交，您还可以加客服QQ：2271250263或客服Q群：460025819进行咨询";
		$('.modal-body').text(del_message);
		
		$('#myModal .modal-footer .btn-default').text('取消').hide();
		$('#myModal .modal-footer .btn-primary').text('我知道了');
		
		$('#myModal .modal-footer .btn-primary').click(function(){
			$("#myModal").modal('hide');
			formsta = 2;
			go_back();

		});
		
		$("#myModal").modal('show');
	}
</script>
<script src="/gl/static/js/bootstrap.min.js"></script>
<{include file="./common/footer.tpl"}>