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
			width:'100%',
			allowPreviewEmoticons : false,
			allowImageUpload : false,
			items : [
					'undo', 'redo', '|', 'image',
				],
			readonlyMode : false,
			allowImageUpload:true,
			allowImageRemote : false,
			uploadJson : '/question/q_upload_img/',
			filePostName : 'avatar',
			afterUpload:function(data){
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
                        <i class="infoicon"></i><span>回答</span>
                    </div>
                    <form action="/question/answer_save/" method='post'>
					    <input type='hidden' id='aid' name='aid' value='<{$data.absId}>' />
                        <input type='hidden' id='qid' name='qid' value='<{$data.qid}>' />
						<input type='hidden' id='imgnum' name='imgnum' value='<{if $data.imgnum}><{$data.imgnum}><{else}>0<{/if}>' />
                        <div class="box-title">
                            <div><span class="line"></span>我的回答<div class="packup"></div></div>
                        </div>
                        <div class="question">
                            <textarea id="editor_id" name='content' class="form-control" rows="9" placeholder="填写回答内容，有经验值奖励哦！"><{$data.content}></textarea>
                            
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
	
	$(function(){
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
		//history.go(-1);
		var go_url = document.referrer;
		if(go_url == window.location.href){
			window.location.href = '/';
		}else{
			window.location.href = go_url;
		}
	}
	
	
	function confirm_out(){
		var del_message = "放弃已编辑内容？";
		$('.modal-body').text(del_message);
		
		$('#myModal .modal-footer .btn-default').text('取消').show();
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
<{include file="./common/footer.tpl"}>