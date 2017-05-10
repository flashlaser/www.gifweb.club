/**
 * Created by jiantao5 on 2015/12/22.
 */
(function($) {
    var $editname = $("#editname"),
       $actionA = $(".phoneAction li a"), $deletebtn = $(".makeEdit .delete");
    var a_href = "";
    var userinfo = {
        init: function() {
            this.inithref();
            this.editname();
            this.actionTab();
            this.deleteAction();
        },
        inithref:function(){
            var val = this.getUrlParam('act');
            if(val){
                switch (val){
                    case "follow_game":
                        a_href = "#gameslist";
                        break;
                    case "follow_article":
                        a_href = "#glsclist";
                         break;
                    case "question":
                        a_href = "#asklist";
                         break;
                     case "answers":
                        a_href = "#answerlist";
                         break;
                     case "follow_question":
                        a_href = "#gzwtlist";
                         break;
                     case "follow_answers":
                        a_href = "#dasclist";
                         break;
                     case "my_message":
                        a_href = "#mymsglist";
                        break;
                }
            }
        },
        //修改名字
        editname: function() {
            $editname.on("click", function() {
                $(".myname .namep").addClass("hidden");
                $(".myname .nameinput").removeClass("hidden");
                $(".myname .nameinput").val($(".myname .namep").html());
                $(".myname .nameinput").get(0).focus();
            });
            $(".myname .nameinput").on("blur", function () {
                //ajax 修改名字
                var nicknames = $(".myname .nameinput").val();
                var old_name = $('#old_name').val();
                if(nicknames=='')
                {
                	myPop('昵称不能空');return false;
                }
                if(nicknames.length>14)
                {
                	myPop('昵称太长啦！'); return false;
                }
                if(nicknames.length<2)
                {
                	myPop('昵称太短啦！'); return false;
                }
                if(nicknames == old_name)
                {
                	 $(".myname .nameinput").addClass("hidden");
               		 $(".myname .namep").removeClass("hidden");
                	myPop('昵称没有做任何修改'); return false;
                }
                
                var pregs = /^[a-zA-Z0-9_\-\u4E00-\u9FFF]+$/;
                if(!pregs.test(nicknames))
                {
                	myPop('包含非法字符'); return false;
                	return false;
                }
                
                $.ajax({
					type : 'POST',
					dataType : 'json',
					cache : false,
					url : '/user/edit_user',
					data : {action: 2,nickname:nicknames},
					success:function(res){
						if (res.result == '200') {
                             $('#old_name').val(nicknames)
							 $(".myname .namep").html(nicknames);
                             myPop(res.message); 
						}else
						{
							myPop(res.message); 
						}
					}
				})
                 $(".myname .nameinput").addClass("hidden");
                $(".myname .namep").removeClass("hidden");
               
                
            });
        },
        //个人信息tab,展开收起
        actionTab: function() {
            //wap
            $actionA.on("click",function(){
                event.preventDefault();
                $(".phoneAction  li.active").removeAttr("style");
                var href = $(this).attr("href");
                if($(this).parent("li").hasClass("active")){
                    clearActive($(this));
                }else{
                    clearActive($(this));
                    $(this).parent("li").addClass("active");
                    $(href).addClass('active');
                    $(this).next(".makeEdit").show();
                    a_href = href;
                    aft = $(this).offset().top;
                }
            })
            function clearActive(obj_t){
                $(".phoneAction li").removeClass("active");
                $(".cont .contlist").removeClass("active");
                $(".makeEdit").hide();

                if(obj_t.attr("is_show_data")=='1')
                {
                    $deletebtn.show();    
                }
                else
                {
                    $deletebtn.hide();
                }
                
                $(".edit-btn").hide(); 
                $(".btn-group li label").hide();
                $(".btn-group li label").removeClass("active");
                $('.btn-group li div').removeClass('marginlft');
                a_href = '';
                $(".mymsg-detail>span").css("left","0");
            }
            //pc  tab切换，编辑和删除按钮的显示引藏处理
             $(".pcAction .active-info li a").on("click", function(e){
                e.preventDefault();
                //获取属性值
                var data_id = $(this).attr("data-id");
                var data_api= $(this).attr("data-api");
                //切换
                $('.pcAction ul li').removeClass("active");
                $(this).parent("li").addClass('active');
                $(".tab-content .tab-pane").removeClass("active");
                $("#"+data_id).addClass('active');
                if(data_id.indexOf('asklistPC')<0 && data_id.indexOf('answerlistPC')<0){
                    if(data_id.indexOf('gameslistPC')>-1){
                        $('.gamelist input[type=checkbox]').hide();
                        $('.gamelist i.cimg').hide();
                    }else{
                        $(".contlist  .btn-group .checkboxs").hide();
                        $(".contlist  .btn-group .checkboxs").removeClass("active");
                        $('.contlist  .btn-group div').removeClass('marginlft');
                    }
                    $('.edit-btn-pc .pcdelete').show();
                    $('.edit-btn-pc .pcbtn-dele').hide();
                }else{
                    $('.edit-btn-pc .pcdelete').hide();
                    $('.edit-btn-pc .pcbtn-dele').hide();
                }
				$(".pcAction .active-info li a").css("border-color","#e1e1e1");
                $(this).css("border-color",'#5677fc');
				
				// 获取列表信息
				get_list();
				
            });
            $(".pcAction .active-info li a").hover(function(){
                $(this).css("border-color",'#5677fc');
            },function(){
                if(!$(this).parent("li").hasClass('active')){
                    $(this).css("border-color",'#e1e1e1');
                }

            });
           
        },
        deleteAction:function(){
            //点击删除图标
            $deletebtn.on('click',function(e){
                /*var _a = $(this).parent().prev();
                var href = _a.attr('href');*/
               // $(href+" li .checkboxs").css("display","inline-block");
			   
                if(a_href && a_href != ""){
                    if("#mymsglist" == a_href){
                        $(".mymsg-detail>span").css("left","44px");
                    }
                    $(this).hide();
                    $('.makeEdit .edit-btn').show();
                    $(a_href+" li .checkboxs").show();
                    $('.btn-group li div').addClass('marginlft');
                    $(a_href+" li .hidden_input").val(1);
                }
                e.stopPropagation();
                return false;
            });
            //删除
            $(".edit-btn .deleted").on('click',function(){
                if(a_href && a_href != ""){
                    var delist = $(a_href + " li label.active");
                    var delstr = "";
                    var type_datas = '';
                    var actions = 1;
                    for(var i=0;i<delist.length;i++){
                    	var del = $(delist[i]);
                    	delstr += del.attr('data-del')+",";
                    	//关注游戏
                    	if($(this).attr('id')=='follow_game')
                    	{
                    		$('#follow_game_li'+del.attr('data-del')).hide(500);
                    		type_datas = 'game_attention';
                    	}//消息
                    	else if($(this).attr('id')=='get_message')
                    	{
                    		$('#get_message_li'+del.attr('data-del')).hide(500);
                    		type_datas = 'del_message';
                    		actions = 0;
                    	}//问题收藏
                    	else if($(this).attr('id')=='follow_question')
                    	{
                    		$('#follow_question_li'+del.attr('data-del')).hide(500);
                    		type_datas = 'question_attention';
                    	}//答案收藏
                    	else if($(this).attr('id')=='follow_answer')
                    	{
                    		$('#follow_answer_li'+del.attr('data-del')).hide(500);
                    		type_datas = 'answer_collect';
                    	}
                    	else if($(this).attr('id')=='follow_gl')
                    	{
                    		$('#follow_gl_li'+del.attr('data-del')).hide(500);
                    		type_datas = 'gl_collect';
                    	}
                        del.removeClass('active');
                    	
                    }
                    delstr=delstr.substring(0,delstr.length-1)
                    if(delstr!='')
                    {
                        un_follow(delstr,type_datas,actions);
                        if(a_href && a_href != ""){
                            if("#mymsglist" == a_href){
                                $(".mymsg-detail>span").css("left","0");
                            }
                            $(".btn-group li label").removeClass('active');
                            $(".edit-btn").hide();
                            $deletebtn.show();
                            $(a_href+" li .checkboxs").hide();
                            $('.btn-group li div').removeClass('marginlft');
                            $(a_href+" li .hidden_input").val(0);
                        }
                        e.stopPropagation();
                        return false;
                    }
                    else
                    {
                        myPop('请选择要删除对象');return false;
                    }
                    
                }
            });
            //取消删除
            $('.edit-btn .cancel').on('click',function(e){
                if(a_href && a_href != ""){
                    if("#mymsglist" == a_href){
                        $(".mymsg-detail>span").css("left","0");
                    }
                    $(".btn-group li label").removeClass('active');
                    $(".edit-btn").hide();
                    $deletebtn.show();
                    $(a_href+" li .checkboxs").hide();
                    $('.btn-group li div').removeClass('marginlft');
                    $(a_href+" li .hidden_input").val(0);
                }
                e.stopPropagation();
                return false;
            });
            //-----------pc---------

			
            //点击编辑图标
            $(".edit-btn-pc .pcdelete").on('click',function(e){
                var _id = $('.contpc .active').attr("id");
                $(this).hide();
                $('.pcbtn-dele').show();
                if(_id == "gameslistPC"){
                    $('.gamelist input[type=checkbox]').show();
                    $('.gamelist i.cimg').show();
                }else{
                    $("#"+_id+" .btn-group .checkboxs").show();
					//$("#"+_id+' .btn-group .checkboxs').addClass('active');
                    $("#"+_id+' .btn-group div').addClass('marginlft');
                }
                e.stopPropagation();
                return false;
            });
            //取消
            $('.pcbtn-dele .cancel').on('click',function(e){
                if($(".contpc>div.active").attr("id").indexOf("gameslistPC")>-1){
                    $('.gamelist input[type=checkbox]').hide();
                    $('.gamelist i.cimg').hide();
					$('.gamelist input[type=checkbox]').prop("checked", false);
					$('.gamelist input[type=checkbox]').removeAttr("checked");
					$('.gamelist i.cimg').removeClass("checked");
					$('.gamelist i.cimg').addClass("checkbox1");
                }else{
                    $(".btn-group li label").removeClass('active');
                    $(".btn-group li label").hide();
                    $('.btn-group li>div').removeClass('marginlft');
					$(".btn-group li label").find("input[type=checkbox]").prop("checked", false);
					$(".btn-group li label").find("input[type=checkbox]").removeAttr("checked");
                }
                $('.edit-btn-pc .pcdelete').show();
                $('.edit-btn-pc .pcbtn-dele').hide();

                e.stopPropagation();
                return false;
            });
			
			
            //删除
            $('.pcbtn-dele .deleted').on('click',function(e){
				var actSel = $(".active-info .active a");
				var data_id = actSel.attr('data-id');
				var data_api = actSel.attr('data-api');
				var valstr = "";
				var actions = 1;
				var nowCnt = actSel.find("span").html();
				var delCnt = 0;
				var msg = "请选择要删除对象";
				
				// 获取操作ID
				$("#"+data_id+" input:checked").each(function(){
					if(data_id == 'gameslistPC'){
						$(this).parent().parent().parent().hide();
						$(this).prop("checked", false);
						$(this).removeAttr("checked");
					}else{
						$(this).parent().parent().hide();	
						$(this).prop("checked", false);
						$(this).removeAttr("checked");
					}
					var chb = $(this).val();
					valstr += chb + ",";
					delCnt++;
				});
				
				// 删除操作接口
				//关注游戏
				var re_url = "/user";
				if(data_id=='gameslistPC'){
					re_url = '/user/?act=follow_game';
					type_datas = 'game_attention';
					msg = "没有选择任何游戏 ";
				}//消息
				else if(data_id=='mymsglistPC'){
					re_url = '/user/?act=get_message';
					type_datas = 'del_message';
					actions = 0;
					msg = "没有选择任何通知  ";
				}//问题收藏
				else if(data_id=='gzwtlistPC'){
					re_url = '/user/?act=follow_question';
					type_datas = 'question_attention';
					msg = "没有选择任何问题 ";
				}//答案收藏
				else if(data_id=='dasclistPC'){
					re_url = '/user/?act=follow_answers';
					type_datas = 'answer_collect';
					msg = "没有选择任何答案";
				}
				else if(data_id=='glsclistPC'){
					re_url = '/user/?act=follow_article';
					type_datas = 'gl_collect';
					msg = "没有选择任何攻略";
				}
				
				if(valstr == ''){
					myPop(msg);
					return false;	
				};
				valstr=valstr.substring(0,valstr.length-1);
                un_follow(valstr,type_datas,actions);
				
				//隐藏删除操作
				$(".pcbtn-dele").hide();
				$(".edit-btn-pc .pcdelete").show();
				if(data_id == "gameslistPC"){
                    $('.gamelist input[type=checkbox]').hide();
                    $('.gamelist i.cimg').hide();
                }else{
                    $('.btn-group input[type=checkbox]').parent().hide();
                    $("#"+data_id+' .btn-group div').removeClass('marginlft');
                }
				
				// 如数据存在分页，删除后刷新数据
				actSel.find("span").html(nowCnt-delCnt);
				var hasPage = $("#"+data_id+" #nav").find("input").val();
				if(hasPage != "undefined" && hasPage != undefined ){
					setTimeout('get_list()', 400);
				}else if(nowCnt == delCnt){
					var html = "<div class='cont-empty' style='display:block;'><div><i><img src='/gl/static/images/v1/empty.png'></i><p>这里什么都没有哦~</p></div></div>";
					$(".edit-btn-pc .pcdelete").hide();
					$("#"+data_id).html(html);
				}
				
                e.stopPropagation();
                return false;
            });
        },

        getUrlParam:function (name){
            var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
            var r = window.location.search.substr(1).match(reg);
            if(r!=null)return  unescape(r[2]); return null;
        }
    };
    $(function() {
        userinfo.init();
    });
})(jQuery);
var aft = 0;
if($(".active-info li.active").length>0){
    var aft = $(".active-info li.active").offset().top;
}
$(window).scroll(function(){
        var stop = $(window).scrollTop();
        var headertop = $("header").height()+$(".nav .maintop").height();
        var activeft = $(".active-info li.active").offset();
        if(stop>=aft){
            var lih = $(".active-info li").height();
            $(".phoneAction li.active").css({"height":lih,"position":"fixed", "top":"-8px","background-color":"#fff","z-index":"9999"});
        }else{
            $(".phoneAction li.active").removeAttr("style");
        }
    });
////////////////////////////////////////////////个人中心各个table加载更多///////////////////////////////////////////////// 
//关注游戏
$('#more_follow_game').on('click',function(){
    var wap_data_api= $(this).attr("wap-data-api");
    var page = $(this).attr("page-data");
    var html_content = "";
    
    // 获取列表信息
    page = Number(page)+1;
	$.ajax({
		type : 'POST',
		dataType : 'json',
		cache : false,
		url : '/user/'+wap_data_api,
		data : {is_ajax: 1, page: page},
		success:function(res){
			if (res.result == '200') {
				pages = res.data.page_data;
				var data_list = res.data.list;
				var values_hidden = 0;//判断是否显示复选框
				if(data_list.length>0)
				{
					if($('#gamelist_ul .hidden_input').val()=='1')
					{
						values_hidden = 1;
					}
					for(var i=0;i<data_list.length;i++)
					{
						html_content+="<li id='follow_game_li"+data_list[i].absId+"'>"+
				        "<label class='btn infoicon checkboxs' data-del='"+data_list[i].absId+"'>"+
					            "<input type='checkbox' autocomplete='off' checked>"+
					            "<input type='hidden' class='hidden_input' value='"+values_hidden+"' />"+
					        "</label>"+
					        "<div class='game-detail'>"+
					            "<div class='clear' onclick='togameUrls("+data_list[i].absId+",1);'>"+
					                "<div class='fl gameimg'><img src='"+data_list[i].absImage+"'/></div>"+
					                "<div class='fl gmname'>"+
					                    "<a href='http:\/\/www.wan68.com\/zq\/juhe_page\/"+data_list[i].absId+"'><p>"+data_list[i].abstitle+"</p></a>"+
					                "</div>"+
					            "</div>"+
					        "</div>"+
					    "</li>";
					}
					$('#more_follow_game').attr("page-data",pages.curr_page);
					$('#gamelist_ul').append(html_content).css("opacity","0").animate({opacity:1},500);
					if($('#gamelist_ul .hidden_input').val()=='1')
					{
						$('#gamelist_ul li label').css('display','block');
						$('.btn-group li div').addClass('marginlft');
					}
                    //判断当前数据个数是否==page_size
                    if(pages.curr_page==pages.pages)
                    {
                        $('#more_follow_game').html(" ");
                        $('#more_follow_game').attr("id",'null_follow_game');
                    }
					
				}
				else
				{
					$('#more_follow_game').html(" ");
					$('#more_follow_game').attr("id",'null_follow_game');
				}
			}
		}
	})
})

//我的提问
$('#more_question').on('click',function(){
    var wap_data_api= $(this).attr("wap-data-api");
    var page = $(this).attr("page-data");
    var html_content = "";
    
    // 获取列表信息
    page = Number(page)+1;
	$.ajax({
		type : 'POST',
		dataType : 'json',
		cache : false,
		url : '/user/'+wap_data_api,
		data : {is_ajax: 1, page: page},
		success:function(res){
			if (res.result == '200') {
				pages = res.data.page_data;
				var data_list = res.data.list;
				var  is_close = '';
				if(data_list.length>0)
				{
					for(var i=0;i<data_list.length;i++)
					{
						if(data_list[i].status=='1')
						{
							is_close = '[已关闭]';
						}
						html_content+="<li>"+
	                    "<div class='ask-detail' onclick='togameUrls("+data_list[i].absId+",3);'>"+
	                        "<h2>"+is_close+data_list[i].abstitle+"</h2>"+
	                        "<div class='divbottom'>"+
	                            "<span>有"+data_list[i].answerCount+"个回答</span>"+
	                            "<span class='txt-align-r'>"+data_list[i].updateTime+"</span>"+
	                        "</div>"+
	                   	 "</div>"+
	                	"</li>";
					
					}
					$('#more_question').attr("page-data",pages.curr_page);
					$('#questionlist_ul').append(html_content).show(500);
                    //判断当前数据个数是否==page_size
                    if(pages.curr_page==pages.pages)
                    {
                        $('#more_question').html(" ");
                        $('#more_question').attr("id",'null_question');
                    }
				}
				else
				{
					$('#more_question').html(" ");
					$('#more_question').attr("id",'null_question');
				}
			}
		}
	})
})


//我的回答
$('#more_answers').on('click',function(){
    var wap_data_api= $(this).attr("wap-data-api");
    var page = $(this).attr("page-data");
    var html_content = "";
    
    // 获取列表信息
    page = Number(page)+1;
	$.ajax({
		type : 'POST',
		dataType : 'json',
		cache : false,
		url : '/user/'+wap_data_api,
		data : {is_ajax: 1, page: page},
		success:function(res){
			if (res.result == '200') {
				pages = res.data.page_data;
				var data_list = res.data.list;
				var  is_close = '';
                var answer_is_close = '';
				if(data_list !=null)
				{
					for(var i=0;i<data_list.length;i++)
					{
						if(data_list[i].questionInfo.status=='1')
						{
							is_close = '[已关闭]';
						}
                        if(data_list[i].status=='1')
                        {
                            answer_is_close = '[已关闭]';
                        }
						html_content+="<li>"+
						    "<div class='answer-detail' >"+
						        "<h2 class='oneline' onclick='togameUrls("+data_list[i].questionInfo.absId+",3);'>"+is_close+data_list[i]['questionInfo']['abstitle']+"</h2>"+
						       " <div class='cont-detail clear'>"+
						            "<span class='fl da'>答</span>"+
						            "<div class='fl answer-cont'>"+
						                "<p onclick='togameUrls("+data_list[i].absId+",4);'>"+answer_is_close+"“"+data_list[i].abstitle+"”</p>"+
						                "<span>"+data_list[i].updateTime+"</span>"+
						            "</div>"+
						        "</div>"+
						    "</div>"+
						"</li>";

					
					}
					$('#more_answers').attr("page-data",pages.curr_page);
					$('#answerslist_ul').append(html_content).show(500);
                    //判断当前数据个数是否==page_size
                    if(pages.curr_page==pages.pages)
                    {
                        $('#more_answers').html(" ");
                        $('#more_answers').attr("id",'null_answers');
                    }
				}
				else
				{
					$('#more_answers').html(" ");
					$('#more_answers').attr("id",'null_answers');
				}
			}
		}
	})
})

//攻略收藏
$('#more_follow_article').on('click',function(){
    var wap_data_api= $(this).attr("wap-data-api");
    var page = $(this).attr("page-data");
    var html_content = "";
    
    // 获取列表信息
    page = Number(page)+1;
	$.ajax({
		type : 'POST',
		dataType : 'json',
		cache : false,
		url : '/user/'+wap_data_api,
		data : {is_ajax: 1, page: page},
		success:function(res){
			if (res.result == '200') {
				pages = res.data.page_data;
				var data_list = res.data.list;
				var values_hidden = 0;//判断是否显示复选框
				if(data_list !=null)
				{
					if($('#followarticlelist_ul .hidden_input').val()=='1')
					{
						values_hidden = 1;
					}
					for(var i=0;i<data_list.length;i++)
					{
						html_content+="<li id='follow_gl_li"+data_list[i].absId+"'>"+
                                        "<label class='btn infoicon checkboxs' data-del='"+data_list[i].absId+"'>"+
                                "<input type='checkbox' autocomplete='off' checked>"+
                                "<input type='hidden' class='hidden_input' value='"+values_hidden+"' />"+
                           " </label>"+
                            "<div class='glsc-detail' onclick='togameUrls("+'"'+data_list[i].absId+'"'+",2);'>"+
                                "<h2>"+data_list[i].abstitle+"</h2>"+
                                "<span>"+data_list[i].updateTime+"</span>"+
                            "</div>"+
                        "</li>";

					
					}
					$('#more_follow_article').attr("page-data",pages.curr_page);
					$('#followarticlelist_ul').append(html_content).show(500);
					if($('#followarticlelist_ul .hidden_input').val()=='1')
					{
						$('#followarticlelist_ul li label').css('display','block');
						$('.btn-group li div').addClass('marginlft');
					}
                     //判断当前数据个数是否==page_size
                    if(pages.curr_page==pages.pages)
                    {
                        $('#more_follow_article').html(" ");
                        $('#more_follow_article').attr("id",'null_follow_article');
                    }
				}
				else
				{
					$('#more_follow_article').html(" ");
					$('#more_follow_article').attr("id",'null_follow_article');
				}
			}
		}
	})
})

//关注的问题
$('#more_follow_question').on('click',function(){
    var wap_data_api= $(this).attr("wap-data-api");
    var page = $(this).attr("page-data");
    var html_content = "";
    
    // 获取列表信息
    page = Number(page)+1;
	$.ajax({
		type : 'POST',
		dataType : 'json',
		cache : false,
		url : '/user/'+wap_data_api,
		data : {is_ajax: 1, page: page},
		success:function(res){
			if (res.result == '200') {
				pages = res.data.page_data;
				var data_list = res.data.list;
				var values_hidden = 0;//判断是否显示复选框
				var is_close = '';
				if(data_list !=null)
				{
					if($('#followquestionlist_ul .hidden_input').val()=='1')
					{
						values_hidden = 1;
					}
					for(var i=0;i<data_list.length;i++)
					{
						if(data_list[i].status=='1')
						{
							is_close = '[已关闭]';
						}
						html_content+="<li id='follow_question_li"+data_list[i].absId+"'>"+
                                        "<label class='btn infoicon checkboxs' data-del='"+data_list[i].absId+"'>"+
                                            "<input type='checkbox' autocomplete='off' checked>"+
                                             "<input type='hidden' class='hidden_input' value='"+values_hidden+"' />"+
                                        "</label>"+
                                        "<div class='ask-detail' onclick='togameUrls("+data_list[i].absId+",5);'>"+
                                            "<h2>"+is_close+data_list[i].abstitle+"</h2>"+
                                            "<div class='divbottom'>"+
                                                "<span>有"+data_list[i].answerCount+"个回答</span>"+
                                                "<span class='txt-align-r'>"+data_list[i].updateTime+"</span>"+
                                            "</div>"+
                                        "</div>"+
                                    "</li>";

					
					}
					$('#more_follow_question').attr("page-data",pages.curr_page);
					$('#followquestionlist_ul').append(html_content).show(500);
					if($('#followquestionlist_ul .hidden_input').val()=='1')
					{
						$('#followquestionlist_ul li label').css('display','block');
						$('.btn-group li div').addClass('marginlft');
					}
                     //判断当前数据个数是否==page_size
                    if(pages.curr_page==pages.pages)
                    {
                        $('#more_follow_question').html(" ");
                        $('#more_follow_question').attr("id",'null_follow_question');
                    }
				}
				else
				{
					$('#more_follow_question').html(" ");
					$('#more_follow_question').attr("id",'null_follow_question');
				}
			}
		}
	})
})

//关注的答案
$('#more_follow_answers').on('click',function(){
    var wap_data_api= $(this).attr("wap-data-api");
    var page = $(this).attr("page-data");
    var html_content = "";
    
    // 获取列表信息
    page = Number(page)+1;
	$.ajax({
		type : 'POST',
		dataType : 'json',
		cache : false,
		url : '/user/'+wap_data_api,
		data : {is_ajax: 1, page: page},
		success:function(res){
			if (res.result == '200') {
				pages = res.data.page_data;
				var data_list = res.data.list;
				var values_hidden = 0;//判断是否显示复选框
				var is_close = '';
                var answer_is_close = '';
				if(data_list !=null)
				{
					if($('#followanswerlist_ul .hidden_input').val()=='1')
					{
						values_hidden = 1;
					}
					for(var i=0;i<data_list.length;i++)
					{
						if(data_list[i].questionInfo.status=='1')
						{
							is_close = '[已关闭]';
						}
                         if(data_list[i].status=='1')
                        {
                            answer_is_close = '[已关闭]';
                        }
						html_content+="<li id='follow_answer_li"+data_list[i].absId+"'>"+
                                        "<label class='btn infoicon checkboxs' data-del='"+data_list[i].absId+"'>"+
                                            "<input type='checkbox' autocomplete='off' checked>"+
                                            "<input type='hidden' class='hidden_input' value='"+values_hidden+"' />"+
                                       "</label>"+
                                        "<div class='answer-detail' >"+
                                            "<h2 class='oneline' onclick='togameUrls("+data_list[i].questionInfo.absId+",3;'>"+is_close+data_list[i].questionInfo.abstitle+"</h2>"+
                                            "<div class='cont-detail clear'>"+
                                                "<span class='da'>答</span>"+
                                                "<div class='answer-cont'>"+
                                                    "<p onclick='togameUrls("+data_list[i].absId+",6);'>"+answer_is_close+"“"+data_list[i].abstitle+"”</p>"+
                                                    "<span>"+data_list[i].updateTime+"</span>"+
                                                "</div>"+
                                            "</div>"+
                                        "</div>"+
                                    "</li>";
					}
					$('#more_follow_answers').attr("page-data",pages.curr_page);
					$('#followanswerlist_ul').append(html_content).show(500);
					 if($('#followanswerlist_ul .hidden_input').val()=='1')
					{
						$('#followanswerlist_ul li label').css('display','block');
						$('.btn-group li div').addClass('marginlft');
					}
                     //判断当前数据个数是否==page_size
                    if(pages.curr_page==pages.pages)
                    {
                        $('#more_follow_answers').html(" ");
                        $('#more_follow_answers').attr("id",'null_follow_answers');
                    }
				}
				else
				{
					$('#more_follow_answers').html(" ");
					$('#more_follow_answers').attr("id",'null_follow_answers');
				}
			}
		}
	})
})

//pop弹出函数
function myPop(){
	var msg = arguments[0] ? arguments[0] : '操作成功'; //第一个参数
	var timelen = arguments[1] ? arguments[1] : 1; //第二个参数
	
	$('.popTip').html('<p>' + msg + '</p>').fadeIn("slow");
	setTimeout(function(){$('.popTip').fadeOut('slow');},timelen * 1000);
}

function un_follow(id,type_name,actions)
{
	var urls = 'follow';
	if(type_name=='del_message')
	{
		urls = 'user';
	}
	
	$.ajax({
	 	type: "POST",
	 	data: {action:actions,mark:id},
        dataType: "json",
	    url: '/'+urls+'/'+type_name,
	    dataType:'json',
	    success: function(res){
	    	if (res.result == '200') {
				 myPop(res.message); 
			}else
			{
				myPop(res.message); 
			}
	    }
	  });
	
}
/*个人中心获取信息列表*/
function get_list(){
	var actSel = $(".active-info .active a");
	var data_id = actSel.attr('data-id');
	var data_api = actSel.attr('data-api');
	
	$.ajax({
		type : 'POST',
		dataType : 'json',
		cache : false,
		url : '/user/'+data_api,
		data : {is_ajax: 1},
		success:function(res){
			if (res.result == '200') {
				$("#"+data_id).html(res.data);
			}
		}
	})	
}

    
function togameUrls(id,type)
{
	/*
	 type
	 1 = 关注游戏
	 2 = 攻略收藏
	 3 = 我的提问
	 4 = 我的回答
	 5 = 关注的问题
	 6 = 关注的答案
	 7 = 通知
	 * */
	var urls = '';
	if(type==1)
	{
		urls = 'http://www.wan68.com/zq/juhe_page/'+id;
	}else if(type==2)
	{
		urls = 'http://www.wan68.com/raiders/info/'+id;
	}
	else if(type==3)
	{
		urls = 'http://www.wan68.com/question/info/'+id;
	}
	else if(type==4)
	{
		urls = 'http://www.wan68.com/answer/info/'+id;
	}
	else if(type==5)
	{
		urls = 'http://www.wan68.com/question/info/'+id;
	}
	else if(type==6)
	{
		urls = 'http://www.wan68.com/answer/info/'+id;
	}
	else if(type==7)
	{
		urls = ''+id;
	}
	location.href=urls;
}
