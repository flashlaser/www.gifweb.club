if(typeof(gp_comment_util)=='undefined') {
	var gp_comment_util = {};
	gp_comment_util.load_script = function (url, fsuccess) {
		$.ajax({
			url: url,
			dataType: 'script',
			cache: true,
			success: fsuccess
		});
	};

	gp_comment_util.load_iframe = function () {
		try{
			if(typeof(comment_params)=='undefined') {
				comment_params = {};
			}
			var load_times = 0;
			$(".comment_area").each(function(){
				if(load_times >= 2) {
					return false;
				}
				load_times += 1;
				var params = {};
				params.title = $.trim($(this).attr("data-title"));
				if(!params.title) {
					params.title = comment_params.title ? comment_params.title : document.title;
				}
				params.style = $.trim($(this).attr("data-style"));
				if(!params.style) {
					params.style = comment_params.style ? comment_params.style : '';
				}
				params.game = $.trim($(this).attr("data-game"));
				if(!params.game) {
					params.game = comment_params.game ? comment_params.game : 0;
				}
				params.url = $.trim($(this).attr("data-url"));
				if(!params.url) {
					params.url = comment_params.url ? comment_params.url : location.href;
				}
				params.channel = comment_params.channel ? comment_params.channel : 0;
				params.domain = location.host;
				if(params.url.indexOf('?') >= 0) {
					params.url = params.url.substring(0, params.url.indexOf('?'));
				}
				if(params.url.indexOf('#') >= 0) {
					params.url = params.url.substring(0, params.url.indexOf('#'));
				}
				params.random = Math.floor(Math.random()*1000000);
				if(params.url.indexOf('http://')==0) {
					params.token = $.md5(params.url);
					var domain = 'ptbus.com';
					var tdomain = 'cmt.ptbus.com';
					document.domain = domain;
					var param = '';
					var delimiter = '?';
					for (key in params) {
						param += delimiter + key + '=' + encodeURIComponent(params[key]);
						delimiter = '&';
					}
					var src = 'http://'+tdomain+'/comment/'+param;
					$(this).attr('src', src);
					$(this).attr('data-token', params.token);
				}
			});
			
			
		}catch(e){}
	};
}
if(typeof(comment_params)!='undefined') {
	if(!comment_params.url) {
		comment_params.url = '';
	}
	if(!comment_params.title) {
		comment_params.title = '';
	}
	if(!comment_params.style) {
		comment_params.style = '';
	}
	if(!comment_params.game) {
		comment_params.game = 0;
	}
	document.write('<span><iframe allowTransparency="true" class="comment_area" data-url="'+comment_params.url+'" data-title="'+comment_params.title+'" data-style="'+comment_params.style+'" data-game="'+comment_params.game+'" data-token="" src="" width="100%" scrolling="no" frameborder="0" height="0"></iframe></span>');
}
else {
	document.write('<span><iframe allowTransparency="true" class="comment_area" data-url="" data-title="" data-style="" data-game="" data-token="" src="" width="100%" scrolling="no" frameborder="0" height="0"></iframe></span>');
}
var gp_iframe_loaded = false;
$(document).ready(function() {
	if(!gp_iframe_loaded) {
		gp_comment_util.load_script("http://static.comment.stargame.com/js/jquery.md5.js", gp_comment_util.load_iframe);
		gp_iframe_loaded = true;
	}
});