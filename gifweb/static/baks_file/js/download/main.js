var _base = window._base = {
	getKey: function(obj){
		var keys = [];
		for(var i in obj){
			keys.push(i);
		}
		return keys;
	}
}

function eventProxy(Self, el, type, match, level) {
	if(!level) {
		level = 3;
	}
	$(el).on(type, function(e) {
		// debugger;
		var result;
		var el = e.target;
		// 尝试次数
		var count = 0;
		while(true) {
			result = el.className.match(match);
			if(result && Self.events[result[1]]) {
				Self.events[result[1]].call(Self,el);
				break;
			}
			el = el.parentNode;
			if(el == document.body || !el || count >= level) {
				break;
			}
			count++;
		}
	});
}

function Page(){
	this.body = $('body');
	var Self = this;
	//this.vm = {
	//	parent: this,
	//	ptype:  ko.observableArray(this.getPosition()),
	//	plist: ko.observableArray(this.getData('全部')),
	//	changeType: function(data,e){
	//		$(e.target || e.srcElement).parent().addClass('active').siblings().removeClass('active');
	//		this.plist(this.parent.getData(data));
	//	}
	//}
	this.init();
}

Page.prototype = {
	init: function(){
		this.bindEvent();
		this.checkDown();
		this.initHash(this);
	},
	viewBox: $('.content-view'),
	positions: window.positions,
	views: {
		//home: $('#home').html(),
		//intro: $('#intro').html(),
		//employ: $('#employ').html(),
		// download: $('#download').html(),
//		home: $('#wap').html(),
		wap: $('#wap').html(),
		//about: $('#about').html()
	},
	events: {
		download: function(){
			var Self = this;
			$(".btn-download").click(function(){
				if(Self.isWechat()){
					Self.events.wechatDownload.call(Self);
				}else{
					if(Self.getUA() == "ios"){
						window.open(Self.downLink.ios);
					}
					if(Self.getUA() == "android"){
						window.open(Self.downLink.android);
					}
				}

			})

			if(this.getDevice() == "web"){
				$('.share').show();
			$(".ios-down,.wap-down-ios").hover(function(){
				$("#iosma").show();
			},function(){
				$("#iosma").hide();
			})

			$(".android-down,.wap-down-android").hover(function(){
				$("#abdroidma").show();
			},function(){
				$("#abdroidma").hide();
			})
			}
			if(this.getDevice() == "wap"){
				$('.share').hide();
			$(".ios-down,.wap-down-ios").click(function(){
				if(Self.getUA() == "android"){
//					alert("您的系统是android,请选择android下载");
					return;
				}
				if(Self.isWechat()){
//					Self.events.wechatDownload.call(Self);
				}else{
//					window.open(Self.downLink.ios);
				}
			})
			$(".android-down,.wap-down-android").click(function(){
				if(Self.getUA() == "ios"){
//					alert("您的系统是ios,请选择ios下载");
					return;
				}
				if(Self.isWechat()){
//					Self.events.wechatDownload.call(Self);
				}else{
//					window.open(Self.downLink.android);
				}
			})
			}
		},
		wechatDownload: function(){
			if(this.getUA() == 'ios'){
				$('.Guide-modal').find(".android-guide").hide();
			}else{
				$('.Guide-modal').find(".ios-guide").hide();
			}
			$('.Guide-modal').show(0,function(){
				var that = this;
				setTimeout(function(){
					$(that).hide();
				},3000)
			})
		}
	},
	downLink: {
		ios: "http://a.app.qq.com/o/simple.jsp?pkgname=com.caishi.cronus",
		// ios: "http://fusion.qq.com/cgi-bin/qzapps/unified_jump?appid=12218691&from=wx&isTimeline=false&actionFlag=0&params=pname=com.caishi.cronus&versioncode=2&channelid=&actionflag=0",
		android: "http://7xlpcb.dl1.z0.glb.clouddn.com/pkg/01/01/0.9.0/wuli_caishi_v0.9.0.apk"
	},

	bindEvent: function(){
		var Self = this;
		window.onhashchange = function(){
			var hashStr = location.hash.replace("#","");
			if( typeof(Self.views[hashStr]) != "undefined" ) {
		    	Self.viewBox.html(Self.views[hashStr]);
		    	Self.render(hashStr);
		    }
		}
		$(".menu-list li a").click(function(){
			$(this).parent().addClass('cur').siblings().removeClass('cur');
		})

		var match = /clk-([^\s]*)(?:\s+|$)/;
		eventProxy(this,this.body,'click',match,3);
	},
	bindScroll: function(){
		var card = $(".wap-content .wap-card");
		var cardImg = $(".wap-content .wap-card").find("img");
		var headerBg = $(".wap-header-bg");
		var wapPage = $(".wap-page");
		var innerHeight = window.innerHeight;
		var innerWidth = window.innerWidth;
		var imgHeight =  innerWidth * 1.5;
		headerBg.height(innerHeight);
		if(innerHeight > imgHeight){
			cardImg.css({"margin-top":(innerHeight-imgHeight)/2});
		}

		var pageHeight = innerHeight;
		$(".swiper-container").height(pageHeight);

		var mySwiper = new Swiper('.swiper-container',{
			direction : 'vertical'
		})
	},
	initHash: function(Self){
		var hashStr = location.hash.replace("#","");
		var deviceWidth = window.innerWidth;
		var viewsKey = _base.getKey(Self.views);
		if( typeof Self.views[hashStr] != "undefined" && viewsKey.indexOf(hashStr) > -1) {
	    	Self.viewBox.html(Self.views[hashStr]);
		    Self.render(hashStr);
	    }else{
	    	if(this.getDevice() == "wap"){
	    		window.location.href = '#wap';
	    	}else if(hashStr == "download"){
	    		window.location.href = '/liuda';
	    	}else{
				window.location.href = '#home';
	    	}
	    	
	    	window.location.href = '#wap';
	    }
	},
	render: function(hashStr){
		$(".menu-list li[data-hash = "+hashStr+"]").addClass('cur');
		if(hashStr == 'employ'){
			this.vm.plist(this.getData('全部'));
			ko.applyBindings(this.vm,$('.employ-container')[0]);
		}
		if(hashStr == 'home'){
			//this.initFlash();
		}
		if(hashStr == "wap"){
			this.bindScroll();
		}
		this.events.download.call(this);
	},
	checkDown: function(){
		if(this.isWechat() && this.getUrlParam().isFromWechat){
			this.events.wechatDownload.call(this);
			//android wechat bug fix
			if(this.getUA() == "android"){
				$(".Guide-android-modal").show();
			}
		}
		if(this.getUrlParam().isFromWechat){
			if(this.getUA() == "ios"){
				location.href = this.downLink.ios;
			}else if(this.getUA() == "android"){
				location.href = this.downLink.android;
			}else{
				return;
			}
		}
	},
	getData: function(val){
		for(var i in this.positions){
			if(this.positions[i].type == val){
				return this.positions[i].value;
			}
		}
	},
	getDevice: function(){
		if (/iphone|nokia|sony|ericsson|mot|samsung|sgh|lg|philips|panasonic|alcatel|lenovo|cldc|midp|wap|android|iPod|iPad/i.test(navigator.userAgent.toLowerCase())) {
			return "wap";
		}else{
			return "web";
		}
	},
	getUA: function(){
		if(navigator.userAgent.match(/iPhone|iPad|iPod/i)){
			return "ios";
		}
		if(navigator.userAgent.match(/Android/i)){
			return "android";
		}
		return null;
	},
	isWechat: function(){
		if(navigator.userAgent.match(/MicroMessenger/i)){
			return true;
		}else{
			return false;
		}
	},
	getPosition: function(){
		var parr = [];
		for(var i in this.positions){
			parr.push(this.positions[i].type);
		}
		return parr;
	},
	/**
	 * 获取url中的参数
	 * @returns {{}} 键值对的形式
	 */
	getUrlParam: function() {
	    var search = window.location.search.substr(1);
	    var mappers = search.split("&");
	    var hash = {};
	    for(var i in mappers){
	        var index = mappers[i].indexOf("=");
	        hash[mappers[i].substring(0,index)] = mappers[i].substring(index+1)
	    }
	    return hash;
	}

}
var page = new Page();