<{include file="./common/header.tpl"}>
    <link rel="stylesheet" href="/gl/static/css/idangerous.swiper.css">
    <link rel="stylesheet" href="/gl/static/css/search.css?111">
	<script src="/gl/static/js/idangerous.swiper.min.js"></script>
	<script src="/gl/static/js/bootstrap.min.js"></script>

<div class="searchDiv">
    <div class="container pcheaddiv">
        <div class="container pcserchtxt">
            <div class="icon1"></div>
            <form action="/search" method="get" name="searchForm" id="searchForm">
                <input type="hidden" name="type" value="<{$type}>" >
                <input type="text" name="search_keyword" id="search_keyword" value="<{$keyword}>"  maxlength="40"  placeholder="搜索游戏、攻略、问答" class="form-control" onfocus="show_history(<{$type}>)"  autocomplete="off">
                <!--<span class="iconpc search-btn"></span>-->
                <input  type="submit" id="cc2" value="" class="iconpc search-btn iconborder" >
                <input  type="hidden" name="gameId" value="<{$data.gameId}>" >
                
                <a href="javascript:;" class="mb_clear_but" style="display: none;" onclick="go_back();">取消</a>
                <{if $keyword !=''}>
                <a href="javascript:;" class="pc_clear_but" style="display: inline;" onclick="clear_search_txt()">x</a>
                <{else}>
                <a href="javascript:;" class="pc_clear_but" onclick="clear_search_txt()">x</a>
                <{/if}>
            </form>
            <div class="search_history" style="display: none">
                
            </div>
        </div>
    </div>
    <div class="result container">
        <{if $gameTitle}>
        	<div class="singleTag"><{$gameTitle}></div>
        <{else}>
	        <div class="nothing "<{if !empty($result.data.game.resultList.0.absId) }>style="display: none"<{/if}> id="nothingGame">
	            <p>
	            没有找到游戏“<{$keyword}>”？<a href="javascript:;" onclick="search_showAlert()"><span style="color: #0078bf">告诉小编</span></a>
	            </p>
	        </div>
	        <div class="swiper-container allList">
	            <ul class="">
		        	<{foreach $result.data.game.resultList as $k => $v}>
		                <li class="">
	                        <a href="/zq/juhe_page/<{$v.absId}>" target="_blank" title="<{$v.abstitle}>">
	                            <div class="imgdiv">
	                                <img src="<{$v.absImage}>">
	                            </div>
	                            <span><{$v.abstitles}></span>
	                        </a>
		                </li>
		            <{/foreach}>
	            </ul>
	            <div class="swiper-button-prev swiper-btn"><i class="zqpciocn"></i></div>
	            <div class="swiper-button-next swiper-btn"><i class="zqpciocn"></i></div>
	        </div>
        <{/if}>
    </div>

   <div class="question_but" style="display: none">
        <a href="/question/ask?search_keyword=<{$encode_keyword}>"><i class="icon"></i>我要提问 </a>
   </div>

    <div class="content">
        <a name="content_info"></a>
        <div class="pcnavTabs">
            <a id="search_navTab0" href="/search/index?search_keyword=<{$keyword}>&type=4&gameId=<{$data.gameId}>#content_info" <{if $type == 4}>class="Aactive"<{/if}>>攻略</a>
            <i class="tab_line">|</i>
            <a id="search_navTab1" href="/search/index?search_keyword=<{$keyword}>&type=6&gameId=<{$data.gameId}>#content_info" <{if $type == 6}>class="Aactive"<{/if}>>问题</a>
        </div>
        <div class="tabdiv" >
            <div id="search_tab0">
                <div class="nothing container"<{if $page_data.total_rows !=0}>style="display: none"<{/if}>>
                        <img src="/gl/static/images/v1/search_icon_3.png">
                        <p style="line-height: 25px">呀~没找到与”<span><{$keyword}></span>“相关的内容去提问，让大家齐心协力帮你解决</p>
                        <a href="/question/ask?search_keyword=<{$encode_keyword}>"><i>+</i> 去提问</a>
                </div>
                <div class="contents">
                    <ul>
	                    <{if $type == 6}>
		        			<{foreach $result.data.question.resultList as $k => $v}>
	                            <li>
	                            <{if $k=$k+1 == count($result.data.question.resultList)}>
	                                <div style="border:0px;">
	                            <{else}>
	                                <div>
	                            <{/if}>
	                                    <a href="/question/info/<{$v.absId}>" target="_blank">
	                                        <p>
	                                            <{$v.abstitle}>
	                                        </p>
	                                    </a>
	                                    <span>关注(<{$v.attentionCount}>)</span>
	                                    <span>回答(<{$v.answerCount}>)</span>
	                                    <span style="color: #5677fc"><{$v.gameTitle}></span>
	                                </div>
	                            </li>
	                        <{/foreach}>
							<!-- /分页显示 -->
							<{if $result.data.question.count > 10}>
		                    	<div class="move_but"><i class="icon"></i>更多</div>
		                    <{/if}>
		        		<{/if}>
	                    <{if $type == 4}>
		        			<{foreach $result.data.raiders.resultList as $k => $v}>
	                            <li>
	                            <{if $k=$k+1 == count($result.data.raiders.resultList)}>
	                                <div style="border:0px;">
	                            <{else}>
	                                <div>
	                            <{/if}>
	                                    <a href="/raiders/info/<{$v.absId}>" target="_blank">
	                                        <p>
	                                            <{$v.abstitle}>
	                                        </p>
	                                    </a>
	                                    <span>浏览(<{$v.scanCount}>)</span>
	                                    <span>受用(<{$v.praiseCount}>)</span>
	                                    <span style="color: #5677fc"><{$v.gameTitle}></span>
	                                </div>
	                            </li>
	                            
	                        <{/foreach}>
							<!-- /分页显示 -->
							<{if $result.data.raiders.count > 10}>
		                    	<div class="move_but"><i class="icon"></i>更多</div>
		                    <{/if}>
		        		<{/if}>
                    </ul>
                    <!-- 分页显示 -->
                    <nav id="nav">
						<{include file='common/page.tpl'}>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>




<div id="search_alert" class="modal fade bs-example-modal-sm" tabindex="-1" >
    <div class="modal-dialog modal-sm" >
        <div class="modal-content" style="text-align: center;min-height: 240px;width:300px;margin-top: 150px;">
            <div style="width: 100%;padding: 20px;line-height: 25px;">
                <p style="font-size: 16px">小编我要找游戏“<span id="gameName"><{$keyword}></span>”的攻略，快给我写攻略去</p>
            </div>
            <input type="submit" onclick="add_game();" data-dismiss="modal" class="btn btn-primary btn-lg" style="width: 70%" value="提交"/>
        </div>
    </div>
</div>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog">
        <div class="modal-content confirm" style="width:100%;">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p>确认删除该答案嘛？</p>
            </div>
            <div class="modal-footer">
            	<input type="hidden" id="answer_id" value=""/>
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" >确认删除</button>
            </div>
        </div>
    </div>
</div>
<script>

    //------------------------------------------------------------------------------
    isMobile=false;
    $(document).ready(function(){
        var pageWidth = $(window).width();
        if(pageWidth < 997){
            isMobile=true;
        }
        if(navigator.userAgent.match(/(iPhone|iPod|Android|ios)/i)){
            isMobile=true;
        }
        setClass();
    })
    function setClass(){
        setHeadClass();
        setSerchtxtClass();
        setQuestion_butClass();
        setContentClass();
        setNavTabClass();
        settabdivClass();
        setSearch_Content_nav();
    };

    function  setHeadClass(){
        if(isMobile){
            $("header").hide();
        }else{
            $("header").show();
        }
    }

    function setSerchtxtClass(){
        var a=$(".pcheaddiv");
        var b=$(".pcserchtxt");
        var c=$(".search_history");
        if(isMobile){
            a.removeClass("pcheaddiv");
            b.removeClass("pcserchtxt");
            a.removeClass("container");
            b.removeClass("container");

            a.addClass("mbheaddiv");
            b.addClass("mbserchtxt");

            c.css("padding","0px 0px");
            c.addClass("mbSearch_history");

            $(".content>.tabdiv .contents>ul div span").css("margin-right","20px");
            $(".content>.tabdiv .contents>ul div span:nth-child(4)").css("margin-right","0px");

            $(".clear_but").show();
            $(".mb_clear_but").show();
            $(".pc_clear_but").hide();
            $(".iconborder").css("border","none");
        }else{
            $("#search_keyword").keyup(function(){
                var t=$("#search_keyword").val();
                if(t!=""){
                    $(".pc_clear_but").show();
                }else{
                    $(".pc_clear_but").hide();
                }
            });
        }
    }

    function setQuestion_butClass(){
        if(isMobile){
            $(".question_but").show();
        }
    }

    function setContentClass(){
        if(isMobile){
            $(".content").css("padding-left","0px");
        }
    }

    function setNavTabClass(){
        var t=$(".pcnavTabs");
        if(isMobile){
            t.removeClass("pcnavTabs");
            t.addClass("mbnavTabs");
            $(".tab_line").show();
        }else{
            $(".tab_line").hide();
        }
    }

    function settabdivClass(){
        var t=$(".tabdiv");
        if(isMobile){
            t.css("border","0px");
            t.css("margin-top","0px");
        }
    }

    function setSearch_Content_nav(){
        if(isMobile){
            $("#search_Content_nav").hide();
            $("#nav").hide();
            $(".move_but").show();
        }else{
            $("#nav").show();
            $("#search_Content_nav").show();
            $(".move_but").hide();
        }
    }
    //-------------------------------------------------------------------------------------------------

    //要返回地址
    var go_url = getCookie("go_url"); 
    if(!go_url || go_url == 'null'){
       setCookie("go_url", document.referrer); 
        go_url = getCookie("go_url"); 
    }
    //返回上一页
    function go_back(){
        delCookie("go_url"); 
        //javascript:window.opener=null;window.open('','_self');window.close();
        if(go_url == window.location.href){
            window.location.href = '/';
        }else{
            window.location.href = go_url+"/";
        }
    }

	function doPop(msg){
		if(msg){
			var del_message = msg;
		}else{
			var del_message = "确定要清空记录嘛？";
		}
		$('.modal-body').text(del_message);
		
		$('#myModal .modal-footer .btn-default').text('取消');
		$('#myModal .modal-footer .btn-primary').text('确定');
		
		$('#myModal .modal-footer .btn-primary').click(function(){
			clearAllSearch();
		});
		
		$("#myModal").modal('show');
	}
	
	function add_search_keyword(keyword){
		$("#search_keyword").val(keyword);
	}
    function clear_search_txt(){
        $(".form-control").val("");
        $(".pc_clear_but").hide();
    }
    function search_showAlert(){
        $("#search_alert").modal("toggle");
    }

    function show_history(type){
    	
    	$.ajax({
			url:"/ajax_fun/get_search_history_api/" + type ,
			type:"get",
			dataType:"json",
			cache : false,
			async:false,
			beforeSend : function () {
			},
			success:function(r) {
				if(r.result == '200') {
					if(r.data.enoughflag == '2') {
						$(".search_history").html(r.data.data);
        				showsearchHistory();
        				$("body").bind("click",clickTest);
					}
				}
			}
		});
    }

    function clickTest(e){
        var obj=$(e.target);
        if($(e.target).hasClass("form-control") || $(e.target).hasClass("search_clear")|| $(e.target).hasClass("clear_but")||$(e.target).hasClass("seach_w")){

        }else{
            clearAllfun();
            $("body").unbind("click",clickTest);
        }
    }

    function showsearchHistory(){
        $(".search_history").show();
    }


	//清除所有搜索记录
    function clearAllSearch(){
    	
    	$.ajax({
			url:"/ajax_fun/clearSearchAll/"  ,
			type:"get",
			dataType:"json",
			cache : false,
			async:false,
			beforeSend : function () {
			},
			success:function(r) {
				//if(r.result == '200') {
					//if(r.data.enoughflag == '2') {
					//}
				//}else{
				//	myPop('操作失败');
				//}
			}
		});
		
		$("#myModal").modal('hide');
		myPop('操作成功');
		clearAllfun();
    }
    
    function clearAllfun(){
        $(".search_history").hide();
    }
    //清除单条搜索
    function clearSearch(wNo){
        
    	$.ajax({
			url:"/ajax_fun/clearSearchOne/" + wNo ,
			type:"get",
			dataType:"json",
			cache : false,
			async:false,
			beforeSend : function () {
			},
			success:function(r) {
			}
		});
	    $("#search_w"+wNo).hide();
    }




//--------------------------------------------------------------------------
var mySwiper;
var lilen = 0;
var swpNum = 0;
var imgdiv = 77;
$(document).ready(function(){
    lilen = $(".allList li").length;
    
    $(".selectList .all").click(function(){
        $(this).addClass('select');
        $("#tablist li").removeClass("active");
        $(".gamebox").css("display","block");
    });
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
});
//判断是否要加swiper
function checkList(){
    swpNum = parseInt($('.allList').width()/imgdiv);
    var zc =  imgdiv*lilen;
    
    //判断li的总宽度是否大于ul的宽度，若大于则创建swiper,小于不创建
    if(zc > $('.allList').width()){
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
}
//创建swiper
function setSwiper(){
    mySwiper = new Swiper ('.swiper-container', {
        loop: false,
        // 如果需要前进后退按钮
        nextButton: '.swiper-button-next',
        prevButton: '.swiper-button-prev',
        slidesPerView : 'auto',
    });
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
}
</script>

<script>
function add_game() {
	var gameName= $("#search_keyword").val();
	$.ajax({
		url:"/ajax_fun/add_game/" + gameName + '/',
		type:"post",
		dataType:"json",
		cache : false,
		async:false,
		beforeSend : function () {
		
		},
		success:function(r) {
			if(r.result == '200') {
				if(r.data.enoughflag == '2') {
					$("#nothingGame").html('游戏“<{$keyword}>”<span style="color:#919191;font-weight: bold;"">已告诉小编</span>');
				}
			}else{
				alert('数据失败');
			}
		}
	});
		
}
</script>
<script>
	var offset = 2; //默认分页位置
	var firstflag = true; //第一次载入的时候，防止第一个分类没有数据
	var loading_tips = $('.move_but'); //加载更多
	var in_loading = false; //初始话载入状态
	var openflag = false;
	var keyword = '<{$keyword}>';
	var type = <{$type}>;
	//载入产品方法
	function load_info() {
		//判断是否载入
		if(!in_loading) {
			$.ajax({
				url:"/ajax_fun/get_search_list_api/" + keyword + '/' + type + '/' + offset + '/',
				type:"get",
				dataType:"json",
				cache : false,
				async:false,
				beforeSend : function () {
				
				},
				success:function(r) {
					if(r.result == '200') {
						if(r.data.enoughflag == '2') {
							data = r.data.data;
							//console.log(data);
							html = "";
							for(var i in data) {
								html += '<li>';
								html += '<div>';
								if(type == 4){
									html += '<a href="/raiders/info/'+data[i].absId+'" target="_blank"><p>'+data[i].abstitle+'</p></a>';
									html += '<span>浏览('+data[i].scanCount+')</span>';
									html += '<span>受用('+data[i].praiseCount+')</span>';
								}
								if(type == 6){
									html += '<a href="/qustion/info/'+data[i].absId+'" target="_blank"><p>'+data[i].abstitle+'</p></a>';
									html += '<span>关注('+data[i].attentionCount+')</span>';
									html += '<span>回答('+data[i].answerCount+')</span>';
								}
								html += '<span>'+data[i].gameTitle+'</span>';
								html += '</div>';
								html += '</li>';
							}
							$('.move_but').before(html);
							offset += 1;
							if (data.length < 10 || data =='') {
								loading_tips.hide();
							} else {
								loading_tips.show();
							}
						}else{
							if(openflag){
								myPop('没有更多了噢~');
								//alert('没有更多了噢~');
							}
							loading_tips.hide();
						}
					}else{
						alert('获取数据失败');
					}
				}
			});
		
		}
		else {
		}
	}
</script>
<script>
$(document).ready(function(){
	//显示更多信息
	$('.move_but').on('click', function () {
		openflag = true;
		load_info();
	});
	
	$(window).resize();
});
</script>
<{include file="./common/moudle_footer.tpl"}>
<{include file="./common/footer.tpl"}>
