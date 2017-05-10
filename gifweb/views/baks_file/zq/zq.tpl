<{include file="../common/header.tpl"}>
<!-- <link rel="stylesheet" href="/gl/static/css/swiper.min.css" id="swipercss"> -->
<link rel="stylesheet" href="/gl/static/css/idangerous.swiper.css" id="swipercss">
<link rel="stylesheet" href="/gl/static/css/zq.css?33333">
<script src="/gl/static/js/support.js"></script>
<!-- <script src="/gl/static/js/swiper3.1.0.jquery.min.js" id="swiperjs"></script> -->
<script src="/gl/static/js/idangerous.swiper.js" id="swiperjs"></script>
<script>
	//跳转到登录页
	function gologin(){
		var url = "/user/login?backUrl=" + location.href;
		window.location.href = url;
	}
    //swiper兼容性
    //var version = navigator.userAgent;
    //if(version.indexOf("MSIE 8.0")>-1 || version.indexOf("MSIE 7.0")>-1 || version.indexOf("MSIE 6.0")>-1){
        // document.getElementById("swipercss").href="/gl/static/css/idangerous.swiper.css";
        // document.getElementById("swiperjs").src = "/gl/static/js/idangerous.swiper.min.js";
        // document.getElementById("swiperjs").defer = !0;
    //}
</script>
<div id="main" class="cont-width">
    <div class="row">
        <div class="leftPart">
            <div class="content">
                <div class="zq-cont">
                    <div class="zq-box box1" <{if count($data.attentionedList.list) > 0}>style='display:block;'<{else}>style='display:none;'<{/if}>>
                        <div class="box-title zq-title">
                            <div class="phone-area"><span class="line"></span>我的专区(<em class='attentionnum'><{count($data.attentionedList.list)}></em>)</div>
                            <div class="pcComn-tle clear"><i class="zqpciocn area"></i><div class="hot-msg">我的专区[<em class='attentionnum'><{count($data.attentionedList.list)}></em>]<p>My area</p></div><div class="dotline"></div></div>
                        </div>
                        <div class="swiper-container allList" style="height:105px;">
                            <ul class="clear">
							<{foreach from=$data.attentionedList.list item=v key=key name=$data.attentionedList.list}>
								<li class='myzonepic' zgid="<{$v['absId']}>" ><a href="<{base_url()}>zq/juhe_page/<{$v['absId']}>" title="<{$v.abstitle}>"><div class="imgdiv"><img src="<{$v['absImage']}>"/></div><span><{substr_forecast str=$v.abstitle num='30' dot='...'}></span></a></li>
							<{/foreach}>
                            </ul>
                            <!-- 如果需要导航按钮 -->
                            <div class="swiper-button-prev swiper-btn"><i class="zqpciocn"></i></div>
                            <div class="swiper-button-next swiper-btn"><i class="zqpciocn"></i></div>
                        </div>
                    </div>
					
                    <div class="zq-box box2">
                        <div class="box-title glist-title">
                            <div class="phonegmlist"><span class="line"></span>游戏列表<div class="packup"></div></div>
                            <div class="pcComn-tle clear"><i class="zqpciocn gmlist"></i><div class="hot-msg">游戏列表<p>Game list</p></div><div class="dotline"></div></div>
                        </div>
                        <div class="selectList">
                            <ul class="nav clear" role="tablist" id="tablist">
								<li class="all select"><a href="javascript:;"><strong>全部</strong></a></li>
								<{foreach from=$data.normalList.return_letter_arr item=v key=key name=$data.normalList.return_letter_arr}>
									<li role="presentation"><a href="#tab-<{$v}>" aria-controls="home" role="tab" data-toggle="tab"><strong><{if $v=='ZZZ'}>#<{else}><{$v}><{/if}></strong></a></li>
								<{/foreach}>
                            </ul>
                        </div>
                        <div class="tab-content gameslist">
							<{foreach from=$data.normalList.return_arr item=v key=key name=$data.normalList.return_arr}>
								<div role="tabpanel" class="tab-pane gamebox" id="tab-<{$key}>" style="display:block">
									<h2><{if $key=='ZZZ'}>#<{else}><{$key}><{/if}></h2>
									
									<{if count($v)>8 && in_array($key, $data.normalList.return_more_letters)}>
										<!--<{$v|array_pop}>-->
									<{/if}>
									
									<{foreach from=$v item=vv key=keyv name=$v}>
										<div id="<{$vv['absId']}>" class="game-detail">
											<a href="<{base_url()}>zq/juhe_page/<{$vv['absId']}>" class="clear">
												<div class="fl gameimg"><img src="<{$vv['absImage']}>"/></div>
												<div class="fl gmname">
													<p><{substr_forecast str=$vv.abstitle num='30' dot='...'}></p>
													<p class="p2"><em class="phone">关注</em><em class="pc">关注度：</em> <em><{$vv['attentionCount']}></em></p>
												</div>
											</a>
											<{if $data.guid}>
												<{if in_array($vv.absId, $data.attentionedList.id_list)}>
													<div gid="<{$vv['absId']}>" astatus='1' class="attention active">已关注</div>
												<{else}>
													<div gid="<{$vv['absId']}>" astatus='0' class="attention dofollow"><em class="phone">关注</em><em class="pc">关注游戏</em></div>
												<{/if}>
											<{else}>	
												<div class="attention" onclick='gologin()'><em class="phone">关注</em><em class="pc">关注游戏</em></div>
											<{/if}>
										</div>
									<{/foreach}>
									
									<{if in_array($key, $data.normalList.return_more_letters)}>
										<a href="javascript:;" let='<{$key}>' class='getmore'><div class="more-btn"><em class="phone">查看更多游戏</em><em class="pc">点击查看更多游戏<i></i></em></div></a>
									<{/if}>
								</div>
								<div class="gaps"></div>
							<{/foreach}>
                        </div>
                    </div>
                </div>

				<!--
                <div class="addmore"><i class="icon"></i>更多</div>
				-->
            </div>
        </div>
		<!--pc右侧公共-->
		<{include file="../common/moudle_pc_right.tpl"}>
    </div>
</div>
<{include file="../common/moudle_footer.tpl"}>

<!-- 2016-9-1改版,由获取全部游戏，改为获取部分游戏，字母的再通过ajax获取 by wangbo8 -->

<script>
	$(function(){
		$('.getmore').click(function(){
			var that = $(this);
			var mark = $(this).attr('let');
			that.hide();
			$.ajax({
				'async' : true,// 使用异步的Ajax请求
				'type' : "get",
				'cache':false,
				'url' : "/zq/get_game_list_by_letter/" + mark,
				'dataType' : "json",
				success : function(e){
					if(e.result == 200){
						//循环放入
						var data = e.data.normalList;
						html = '';

						for(var i in data){
							html += '<div id="'+ data[i]['absId'] +'" class="game-detail">';
							html += '<a href="http://www.wan68.com/zq/juhe_page/'+ data[i]['absId'] +'" class="clear">';
							html += '<div class="fl gameimg"><img src="'+ data[i]['absImage'] +'"/></div>';
							html += '<div class="fl gmname">';
							html += '<p>'+ data[i]['abstitle'] +'</p>';
							html += '<p class="p2"><em class="phone">关注</em><em class="pc">关注度：</em> <em>'+ data[i]['attentionCount'] +'</em></p>';
							html += '</div></a>';
							
							if(data[i]['uid_login']){
								//登录状态
								if(data[i]['is_attion']){
									html += '<div gid="'+ data[i]['absId'] +'" astatus="1" class="attention active">已关注</div>';
								}else{
									html += '<div gid="'+ data[i]['absId'] +'" astatus="0" class="attention dofollow"><em class="phone">关注</em><em class="pc">关注游戏</em></div>';
								}
							}else{
								html += '<div class="attention" onclick="gologin()"><em class="phone">关注</em><em class="pc">关注游戏</em></div>';
							}
			
							html += '</div>';
						}
						
						var divname = "#tab-" + mark;
						$(divname).children('.game-detail').remove();
						that.remove();
						$(divname).append(html);
					} else {
						myPop('获取');
					}
				}
			});
		});
	});
	
</script>




<script>
    var version = navigator.userAgent;
    var mySwiper;
    var sBetween = 0;//专区的图标间距
    var lilen = 0;
    var imgdiv = 96;
    var swpNum = 0;

    $(document).ready(function(){
        lilen = $(".allList li").length;
        var pageWidth = $(window).width();
        if(pageWidth >= 997){
            imgdiv = 120;
            $(".allList ul li").width(120);
        }

        $(".selectList .all").click(function(){
            $(this).addClass('select');
            $("#tablist li").removeClass("active");
            $(".gamebox").css("display","block");
        });
        //游戏列表展开收起
        $(".phonegmlist .packup").on("click",function(){
            if($(this).hasClass("hideup")){
                $(this).removeClass("hideup");
            }else{
                $(this).addClass("hideup");
            }
            $(".box2 .selectList").toggle(100);
        })
        $(".allList").removeAttr("style");
        $(window).resize();
    })
    $(window).resize(function(){
        //swiper
        checkList();
        if($(window).width()<=996) {
            //游戏列表tab
            $("#tablist li a").click(function(e){
                var href = $(this).attr("href").substr(1);
                window.location.hash=href;
            });
        }else{
            //游戏列表tab
            $("#tablist li a").click(function(e){
                $(".selectList .all").removeClass('select');
                $(".gameslist .gamebox").removeAttr("style");
                window.location.hash='';
            });
        }
         //创建了swiper，根据屏幕设置。
        //var imgdiv = 120;//$(".allList li .imgdiv").width();
        swpNum = parseInt($('.allList').width()/imgdiv);

        if($(window).width()<=996) {
            if(mySwiper){
                //mySwiper.params.slidesPerView=swpNum;
            }
        }else{
            
            /*if(mySwiper){
                mySwiper.params.slidesPerView=swpNum;
            }*/
        }
    });
    
    //判断是否要加swiper
    function checkList(){
        swpNum = parseInt($('.allList').width()/imgdiv);
        //var zc =  imgdiv*lilen+30*lilen;
        var zc =  imgdiv*lilen;
        //判断li的总宽度是否大于ul的宽度，若大于则创建swiper,小于不创建
        var ulwidth = $('.allList ul').width();
        if(ulwidth>$('.allList').width()){
            ulwidth = $('.allList').width();
        }
        if(ulwidth > 100 && zc > ulwidth){
            //$(".allList li").css("margin","0");
            //$(".allList li").width("100%");
            if(!$('.allList ul').hasClass("swiper-wrapper")){
                $('.allList ul').addClass("swiper-wrapper")
            }
            if(!$('.allList ul li').last().hasClass("swiper-slide")){
                $('.allList ul li').addClass("swiper-slide");
            }
            setSwiper();
        }else{

            $(".allList .swiper-btn").hide();
            $('.allList ul li').removeClass("swiper-slide");
            $('.allList ul').removeClass("swiper-wrapper");
            $('.allList ul').removeAttr('style');
            //$(".allList li").css("margin","0 15px");
            //$(".allList li").width(imgdiv);
            if(mySwiper){
                mySwiper.destroy(false); 
                mySwiper = null;
            }
        }
        //创建了swiper，根据屏幕设置。
        if($(window).width()>997) {
            if($('.allList').width() > 100 && zc > $('.allList').width()){
                $(".allList .swiper-btn").show();
                var ind = $(".allList ul li").index($("li.swiper-slide-active"));
                if(ind == 0){
                    $(".swiper-button-prev").addClass("swiper-button-disabled");
                }else if((ind+swpNum) == $(".allList ul li").length){
                    $(".swiper-button-next").removeClass("swiper-button-disabled");
                }
            }
        }
    };
    //创建swiper
    function setSwiper(){
        mySwiper = new Swiper ('.swiper-container', {
            loop: false,
            grabCursor: true,
            slidesPerView : 'auto',
        });
    };
    
        $(".swiper-button-prev").on("click",function(e){
            if(e.preventDefault){
                e.preventDefault();
            }else{
                event.returnValue = false;
            }
            var ind = $(".allList ul li").index($("li.swiper-slide-active"));
            $(".swiper-button-next").removeClass("swiper-button-disabled");
            if(ind == 1 || ind == 0){
                $(this).addClass("swiper-button-disabled");
            }else{
                $(this).removeClass("swiper-button-disabled");
            }
            if(ind == 0){
                return false;
            };
            mySwiper.swipePrev();
        });
        $(".swiper-button-next").on("click",function(e){
            if(e.preventDefault){
                e.preventDefault();
            }else{
                event.returnValue = false;
            }
            var ind = $(".allList ul li").index($("li.swiper-slide-active"));
             $(".swiper-button-prev").removeClass("swiper-button-disabled");
            if((ind+swpNum+1) == $(".allList ul li").length || (ind+swpNum) == $(".allList ul li").length){
                $(this).addClass("swiper-button-disabled");
            }else{
                $(this).removeClass("swiper-button-disabled");
            };
            if((ind+swpNum) == $(".allList ul li").length){
                return false;
            };

            mySwiper.swipeNext();
        });
</script>

<{if $data.guid}>
<script>
	$(function(){
		$(document).delegate('.attention','click', function(){
			var that = this;
			//异步关注
			var mark = $(this).attr("gid");
			var action = $(this).attr("astatus");

			$.ajax({
				'async' : true,// 使用异步的Ajax请求
				'type' : "get",
				'cache':false,
				'url' : "/follow/game_attention",
				'dataType' : "json",
				'data' : {
					'mark':mark,
					'action':action,
				},
				success : function(e){
					//console.log(e);
                    var type = 0;
                    var html = "";
					if(e.result == 200){
						if(action == "1"){
							myPop('取消成功');
							$(that).removeClass('active').html('<em class="phone">关注</em><em class="pc">关注游戏</em>');
							$(that).attr("astatus",'0');

                            lilen = $(".allList li").length;
                            var zc =  imgdiv*(lilen-1);
							//去除专区部分logo
                            if(mySwiper && $('.allList').width() > 100 && zc > $('.allList').width()){
                                var ind = $('.allList li').index($(".myzonepic[zgid=" + mark + "]"));
                                mySwiper.removeSlide(ind); //移除第ind个slide
                                type = 0
                            }else{
                                $(".myzonepic[zgid=" + mark + "]").remove();
                                type = 1;
                            }
						}else{
							myPop('关注成功');
						
							$(that).addClass('active').text('已关注');
							$(that).attr("astatus",'1');
							
							//增加logo至专题部分
							var img = $("#" + mark + " img").attr("src");
							var ahref = $("#" + mark + " a").attr("href");
							var title = $("#" + mark + " p").first().text();
							
                            if(mySwiper){
                                var newSlide = mySwiper.createSlide('<a href="'+ahref+'"><div class="imgdiv"><img src="'+img+'"/></div><span>'+title+'</span></a>','myzonepic swiper-slide','li');
                                mySwiper.appendSlide(newSlide); //加到Swiper的最后
                                $('.allList ul li').last().attr("zgid",mark);
                                type = 0;
                            }else{
                                $('<li class="myzonepic" zgid="'+mark+'"><a href="'+ahref+'"><div class="imgdiv"><img src="'+img+'"/></div><span>'+title+'</span></a></li>').appendTo(".allList ul");
                                type = 1;
                            }
						}
                         if(type){
                            reset_myzone();
                         }
						
						reset_attentionnum(action);
					} else {
						myPop('操作失败');
						//alert('操作失败');
					}
				}
			});
		});
	});
	
	//重置我的专区内容方法
	function reset_myzone(){
		lilen = $(".allList li").length;
		checkList();
	}
	
	//重置我的专区关注游戏数目
	function reset_attentionnum(flag){
		//获得当前数量
		var anum = $('.attentionnum:first').text();
		anum = parseInt(anum);
		
		//判断是关注还是取消
		if(flag == "1"){ //取消
			anum = anum-1;
			
			if(anum < 1){
				$('.zq-cont .box1').hide();
			}
		}else{
			anum = anum+1;
			
			if(anum > 0){
				$('.zq-cont .box1').show();
			}
		}
		anum = anum > 0 ? anum : 0;
		//console.log(anum);
		$('.attentionnum').text(anum);
	}
</script>
<{/if}>
<{include file="../common/footer.tpl"}>