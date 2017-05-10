(function($){
    $.fn.extend({
        "setFocus":function(value){
            value=$.extend({
                "text":""
            },value);
            var dthis = $(this)[0];
            if(document.selection){
                $(dthis).focus();
                var fus = document.selection.createRange();
                fus.text = value.text;
                $(dthis).focus();
            }
            else if(dthis.selectionStart || dthis.selectionStart == "0"){
                var start = dthis.selectionStart; 
                var end = dthis.selectionEnd;
                var top = dthis.scrollTop;
                dthis.value = dthis.value.substring(0, start) + value.text + dthis.value.substring(end, dthis.value.length);
            }
            else{
                this.value += value.text;
            };
            this.focus();
            return $(this);
        }
    })
})(jQuery)

var Util = {};
Util.trim = function(s) {
    return (s != null ? new String(s).replace(/(^\s*)|(\s*$)/g, "") : "");
};
Util.setCookie = function(cookie) {
    var expires = "";
    var date = new Date();
    if (cookie.maxAge != null && typeof (cookie.maxAge) == "number") {
        date.setTime(date.getTime() + cookie.maxAge * 1000);
        expires = ";expires=" + date.toGMTString();
    };
    if (Util.trim(expires) == "") {
        date.setTime(date.getTime() + 24 * 60 * 60 * 100 * 1000);
        expires = ";expires=" + date.toGMTString();
    }
    ;
    var path = ";path=" + (cookie.path ? (cookie.path) : "/");
    var domain = ";domain=" + (cookie.domain ? (cookie.domain) : "");
    document.cookie = [ cookie.name, "=", cookie.value, expires, path, domain ]
            .join("");
};
Util.getDomain = function() {
    var i = 0;
    var domain = window.location.host;
    var domainArr = domain.split(".");
    var len = domainArr.length;
    if ((i = domain.indexOf(".")) > -1) {
        if (len <= 2) {
            domain = "." + domain;
        } else {
            domain = "." + domainArr[len-2] + "." + domainArr[len-1];
        }
    }
    ;
    return domain;
};

var org_content = '';
function checkLength(inp,maxChars,o) { 
    if (inp.val().length > maxChars){
        inp.val(inp.val().substring(0,maxChars))
    }
    var l = inp.val().length;
    o.html(l.toString());   
    return l;
}

function fix_height() {
    if (window != window.top) {
        var height = $(document.body).height();
        var tmpiframe = $(parent.document).find(".comment_area");
        if(tmpiframe.size()>0) {
        	var flag = false;
        	tmpiframe.each(function(){
        		var token = $.trim($(this).attr("data-token"));
        		token = token.toLowerCase();
        		if(typeof(comment_token)!='undefined' && token == comment_token) {
        			$(this).attr('height', height+10);
        			flag = true;
        		}
        	});
        	if(!flag) {
        		tmpiframe.attr('height', height+10);
        	}
        }
        else {
        	$(parent.document).find("#comment_area").attr('height', height+10);
        }
        window.clearTimeout(window.timeout);
    }
}

function auto_height() {
	window.timeout = window.setTimeout('fix_height()', 100);
}

function scrollto_comment(comment_id) {
	var target = document.getElementById("comment_a_"+comment_id);
	if(target) {
		target.scrollIntoView(true);
	}
}

function showReplayInp(o){
	$("#smilies-tooltip").hide();
	if($(o).parents(".comment-item").next().is(".replyModule")) {
		$(".replyModule").remove();
		return;
	}
	var isFeedback = $(o).parents(".feedback-wrap").length > 0 ? true : false;
    var feedbackClass = isFeedback ? "feedbackfloor":"lastfloor";
    var uname = $(o).attr('data-uname');
    var relayToU_html = uname ? "回复 #"+uname+"#: " : "";
    org_content = relayToU_html;
    
    var initInp = "<div class=\"replyModule "+feedbackClass+"\">";
    initInp += "<div class=\"txt-wrap\"><textarea name=\"\" id=\"\"rows=\"3\" ></textarea></div><div class=\"posInfo\"><p class=\"fr text-muted\"><span class=\"num\">"+relayToU_html.length+"</span> / "+maxLen+"<a href=\"javascript:;\" class=\"btn reply_btn\" >提交</a></p><span class=\"face-ico\"></span><span class=\"tips\"></span></div></div>";
    var $insetNode = $(o).parents(".comment-item");
    $insetNode.after(initInp);
    var $oInitInp = $(".replyModule");
    if($oInitInp.length>0) {
    	var $txtInp = $oInitInp.find("textarea");
        $txtInp.focus();
        $txtInp.val(relayToU_html).setFocus(relayToU_html);
    }
    auto_height();
}

function do_post(o) {
    var post_area = o.parents(".comment-txtBox");
	var tip = post_area.find(".tips");
	tip.text('');
	if( ! is_login && ! allow_guest) {
		tip.text('请登录');
		return;
	}
	var content = post_area.find("textarea").val();
	if(!content){
		tip.text('内容不能为空');
		return;
	}
	if(content.length>maxLen){
		tip.text('内容过长');
		return;
	}
	var formhash = $.trim($("#formhash").val());
	var postdata = {
		resource_id:resource_id,
		content:content,
		formhash:formhash
	};
	tip.text('正在提交...');
	$.post('/comment/post',postdata,function(data){
		tip.text(data.msg);
		if(data.result){
			post_area.find("textarea").val('');
			$("#comments").prepend(data.html);
			if(data.total_count) {
				update_comment_count(data.total_count);
				$(".totleNum").text(data.total_count + '条点评');
			}
			auto_height();
		}    		
	},'json');
}

function do_reply(o){
	var replyModule = o.parents(".replyModule");
	var tip = replyModule.find(".tips");
	tip.text('');
	if( ! is_login && ! allow_guest) {
		tip.text('请登录');
		return;
	}
	var content = replyModule.find("textarea").val();
	if(!content || content == org_content){
		tip.text('内容不能为空');
		return;
	}
	if(content.length>maxLen){
		tip.text('内容过长');
		return;
	}
	var dataObj = replyModule.prev().find(".replay").parent();
	var cid = parseInt(dataObj.attr("data-cid"));
	var rid = parseInt(dataObj.attr("data-rid"));
	if(isNaN(cid) || cid < 1 || isNaN(rid)) {
		tip.text('参数错误');
		return;
	}
	var formhash = $.trim($("#formhash").val());
	var postdata = {
		resource_id:resource_id,
		comment_id:cid,
		reply_id:rid,
		content:content,
		formhash:formhash
	};
	tip.text('正在提交...');
	$.post('/comment/reply',postdata,function(data){
		tip.text(data.msg);
		if(data.result){
			var reply_list = $("#replys_"+cid);
			if(reply_list && reply_list.length>0) {
				reply_list.find(".feedback-list").prepend(data.html);
			}
			else {
				var html = '<div id="replys_'+cid+'"><div class="feedback-wrap"><div class="feedback-list">';
				html += data.html;
				html += '</div></div></div>';
				$("#comment_"+cid).append(html);
			}
			if(data.total_count) {
				update_comment_count(data.total_count);
				$(".totleNum").text(data.total_count + '条点评');
			}
			replyModule.remove();
			auto_height();
		}    		
	},'json');
}

function do_support(o) {
	if(o.parent().hasClass('supported')) {
		alert('您已经赞过该内容');
		return;
	}
	var dataObj = o.parent().parent();
	var cid = parseInt(dataObj.attr("data-cid"));
	if(isNaN(cid)) {
		alert('参数错误');
		return;
	}
	var formhash = $.trim($("#formhash").val());
	var postdata = {
		resource_id:resource_id,
		comment_id:cid,
		formhash:formhash
	};
	var rid = parseInt(dataObj.attr("data-rid"));
	if( ! isNaN(rid) && rid>0) {
		postdata.reply_id = rid;
	}
	$.post('/comment/support',postdata,function(data){
		if(data.result){
			var support_b = o.find("b");
			support_b.animate({
                top : -15,
                opacity : 1
            },250,function(){
            	support_b.animate({
                    top : -30,
                    opacity : 0
                },500,function(){
                	o.addClass('active');
        			if(data.support) {
        				o.html('(<span class="num">'+data.support+'</span>) <b>+1</b>');
        			}
                })
                
            })
		}
		else {
			alert(data.msg);
		}
	},'json');
}

function do_del(o) {
	if(!confirm('是否确认删除该条评论')) {
		return;
	}
	var dataObj = o.parent().parent();
	var cid = parseInt(dataObj.attr("data-cid"));
	var rid = parseInt(dataObj.attr("data-rid"));
	if(isNaN(cid) || isNaN(rid)) {
		alert('参数错误');
		return;
	}
	var formhash = $.trim($("#formhash").val());
	var postdata = {
		resource_id:resource_id,
		comment_id:cid,
		reply_id:rid,
		formhash:formhash
	};
	$.post('/comment/del',postdata,function(data){
		if(data.result){
			dataObj.parent().find('.support').remove();
			dataObj.parent().find('.del').remove();
			dataObj.parent().find('.replay').remove();
		}
		alert(data.msg);
	},'json');
}

function do_login() {
	var flag = false;
	if(site_name && login_dialog) {
		try{
			flag = eval('do_login_'+site_name+'()');
		}
		catch(e){flag = false;}
	}
	if(!flag && login_url) {
		window.parent.location.href = login_url.replace('#client_url#', encodeURIComponent(window.parent.location.href));
	}
}

function do_register() {
	if(register_url) {
		window.parent.location.href = register_url.replace('#client_url#', encodeURIComponent(window.parent.location.href));
	}
}

function do_quit() {
	var flag = false;
	if(site_name && login_dialog) {
		try{
			flag = eval('do_quit_'+site_name+'()');
		}
		catch(e){flag = false;}
	}
	if(!flag && quit_url) {
		window.parent.location.href = quit_url.replace('#client_url#', encodeURIComponent(window.parent.location.href));
	}
}

function set_href_to_cookie(backurl_key, maxAge) {
	var cookie = {};
    cookie.domain = Util.getDomain();
    cookie.path = "/";
    cookie.name = backurl_key;
    cookie.value = window.parent.location.href;
    if(maxAge) {
    	cookie.maxAge = maxAge;
    }
    Util.setCookie(cookie);
}

function update_comment_count(count) {
	
}

$(function(){
	auto_height();
	function show_smileys($target) {
		var offset  = $target.offset(),l = offset.left,t = offset.top,$smilies = $("#smilies-tooltip");
        var vpx, vpy;
		if (self.innerHeight) {
		  vpx = self.innerWidth;
		  vpy = self.innerHeight;
		} else if (document.documentElement && document.documentElement.clientHeight) {
		  vpx = document.documentElement.clientWidth;
		  vpy = document.documentElement.clientHeight;
		} else if (document.body) {
		  vpx = document.body.clientWidth;
		  vpy = document.body.clientHeight;
		}
		var tox=0;
		var toy=0;
		var h = $smilies.height();
		if (t + $target.height() + 5 + h > vpy ) {
			toy = vpy - h - 50;
		}
		else {
			toy = t + $target.height() + 5;
		}
        $smilies.css({
            left:l,
            top:toy,
            display : 'block'
        });
	}
	$(".comment-content").click(function(ev){
        var ev = ev || window.event
        var target = ev.target || ev.srcElement;
        var flag = false;
        if(target.parentNode.nodeName.toLowerCase() == "li"){
        	if(target.parentNode.className.match(/replay/)) {
        		flag = true;
        		showReplayInp($(target));
        	}
        	else{
        		if(target.parentNode.className.match(/del/)) {
        			flag = true;
        			do_del($(target));
        		}
        		else {
        			if(target.parentNode.className.match(/support/)) {
        				flag = true;
	        			do_support($(target));
	        		}
        		}
        	}
        }
        else {
        	if(target.nodeName.toLowerCase() == "b" && target.parentNode.parentNode.nodeName.toLowerCase() == "li") {
        		if(target.parentNode.parentNode.className.match(/support/)) {
        			flag = true;
        			do_support($(target).parent());
        		}
        	}
        	if(target.className.match(/reply_btn/)){
 	        	do_reply($(target));
 	        	flag = true;
 	        }
        }
        if(flag || $(target).parents(".replyModule").is(".replyModule")) {
        	if(ev.stopPropagation) { 
                ev.stopPropagation();  
            } else {  
                ev.cancelBubble = true; 
            }
        }
        if($(target).parents(".packUp").is(".packUp")){
            var o = $(target).parents(".packUp");
            var n = target.innerHTML.match(/[0-9]+/);
            var $container = $(target).parents(".comment-item").siblings().find(".feedback-wrap");
            $container.data("onOff",true);
            if($container.data("onOff")){
                $container.data("onOff",false);
                $container.slideUp(500,function(){
                    o.removeClass("packUp").addClass("exp").html("<a href=\"javascript:;\">展开(" + n + ")</a>");
                    $container.data("onOff",true);
                    auto_height();
                })
            }
        }
		if($(target).parents(".exp").is(".exp")){
            var o = $(target).parents(".exp");
            var n = target.innerHTML.match(/[0-9]+/);
            var $container = $(target).parents(".comment-item").siblings().find(".feedback-wrap");
            if($container.data("onOff")){
                $container.data("onOff",false);
                $container.stop().slideDown(500,function(){
                    o.removeClass("exp").addClass("packUp").html("<a href=\"javascript:;\">收起(" + n + ")</a>")
                    $container.data("onOff",true);
                    auto_height();
                })
            }
        }
		//表情
        if($(target).is(".face-ico")){
        	var $target = $(target);
            show_smileys($target);
            curTextArea = $target.parents(".replyModule").find("textarea")
        }
        else {
        	$("#smilies-tooltip").hide();
        }
    });
    
    $(".comment-content").keyup(function(ev){
        var ev = ev || window.event
        var target = ev.target || ev.srcElement;
        if($(target).is("textarea")){
            $oNum = $(target).parents(".feedbackfloor").find(".num");
            checkLength($(target),maxLen,$oNum);
        }
        $("#smilies-tooltip").hide();
    });

    $(document).click(function(){
    	if(!$(".replyModule").find("textarea").val()){
            $(".replyModule").remove()  //点击页面空白处 回复框消失
        }
        $("#smilies-tooltip").hide();
    })
    
    $("#smilies-tooltip .close").click(function(){
        $("#smilies-tooltip").hide();
        
    });
    $("#smilies-tooltip").click(function(){
        return false;
    });

    //表情传值
    $("#smilies-tooltip img").each(function(i,n){
        $(this).click(function(){
            var v = $.trim($(this).attr("title"));
            $oNum = curTextArea.parents(".replyModule").find(".num");
            if(curTextArea.parents("#textareabox").length>0){
                $oNum = curTextArea.parents(".comment-txtBox").find(".num");
            }
            curTextArea.val(curTextArea.val() + v).setFocus();
            checkLength(curTextArea,maxLen,$oNum);
        });
    });
    
    $("#textareabox textarea").keyup(function(ev){
    	$oNum = $(this).parents(".inner").find(".num");
    	checkLength($(this),maxLen,$oNum) ;  //检查字数
    });
    
    $("#post_comment").click(function(){
    	do_post($(this));
    });

    //默认评论框表情
    $("#comment-face-btn").click(function(){
    	show_smileys($(this));
        curTextArea = $("#textareabox").find("textarea");
        curTextArea.focus();
        return false;
    });

    var page = 1;
    $(".loadNext").click(function(){
    	if(isNaN(page) || page < 1)return false;
    	var postdata = {
    		resource_id:resource_id,
    		order:order,
    		page:page+1,
            perpage:10
    	};
    	$.post('/comment/fetch_comments',postdata, function(data){
            if(page + 1 >= data.data.total_page) {
                $(".loadNext").hide();
            }
            if($.trim(data.data.html)) {
                page += 1;
                $("#comments").append(data.data.html);
                auto_height();
                bind_loadAll_click();
            }
            else {
                $(".loadNext").hide();
            }
        },'json');
    });
    
    function bind_loadAll_click() {
    	$(".loadAll").unbind('click');
    	$(".loadAll").click(function(){
        	var comment_id = parseInt($(this).attr('data-cid'));
        	if(isNaN(comment_id) || comment_id < 1)return false;
        	var postdata = {
        		resource_id:resource_id,
        		comment_id:comment_id,
        		order:order
        	};
        	var obj = $(this);
        	var replys = $("#replys_"+comment_id);
        	if(replys) {
        		$.post('/comment/fetch_replys',postdata, function(data){
            		if($.trim(data)) {
            			replys.html(data);
            			auto_height();
            		}
            	},'html');
        	}
        });
    }
    bind_loadAll_click();
    update_comment_count(total_count);
});

