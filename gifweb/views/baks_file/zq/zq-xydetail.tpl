<{include file="../common/header.tpl"}>
<link rel="stylesheet" href="/gl/static/css/zqxydetail.css">
<div id="main" class="cont-width">
    <div class="row">
        <div class="leftPart">
            <div class="content ">
                <div class="xydetail">
                    <div class="boxtop">
                        <div class="gamepictitle">
                            <div class="beijing"><img src="<{$data.screenshot['0']}>"/><i class="yueya"></i></div>
                            <div class="gameimg"><img src="<{$data.absImage}>"/></div>
                        </div>
                        <div class="game-detail">
                            <a>
                                <div class="gmname">
                                    <div class="abstitle"><p><{$data.abstitle}></p></div>
                                    <div class="p2">
										<{foreach $data.type as $vo}>
											<em><{$vo}></em>
										<{/foreach}>
                                        <div class="vericlline"></div>
                                        <span><span><{$data.size}></span></span>
                                        <div class="vericlline line2"></div>
                                       <!-- <span class="pcgintro">这里再放些东西随便放一点什么吧大概我设定的这个字数</span>-->
                                    </div>
                                </div>
                            </a>
							
							<{if $data.guid}>
								<{if $data.attentioned}>
									<div gid="<{$data['absId']}>" astatus='1' class="attention active">已关注</div>
								<{else}>
									<div gid="<{$data['absId']}>" astatus='0' class="attention">立即关注</div>
								<{/if}>
							<{else}>	
							<div gid="<{$data['absId']}>" astatus='0' class="attention" onclick='gologin()'>立即关注</div>
							<{/if}>
                        </div>
                        <hr class="seprLine">
                    </div>
                    <div class="xbjp">
                        <div class="box-title">
                            <div><span class="line"></span>小编简评</div>
                        </div>
                        <hr class="seprLine">
                        <div class="row yqd">
                            <div class="col-md-6 good clear">
                                <div class="fl tb"><i class=" yqdicon"></i><span>优点</span></div>
								<div class="fl ct-list">
									<{if count($data.advantageList)> 0}>
										<{foreach $data.advantageList as $vo}>
											<p><{$vo}></p>
										<{/foreach}>
									<{else}>
										<p>小编好懒，暂无点评哦~</p>
									<{/if}>
								</div>
								<div class="vericlline" style="height: 80px;margin-top:-40px; position: absolute; right: 0; top: 50%; "></div>
                            </div>
                            <div class="col-md-6 bad clear">
                                <div class="fl tb"><i class=" yqdicon"></i><span>缺点</span></div>
								<div class="fl ct-list">
									<{if count($data.disadvantageList)> 0}>
									<{foreach $data.disadvantageList as $vo}>
										<p><{$vo}></p>
									<{/foreach}>
									<{else}>
										<p>小编好懒，暂无点评哦~</p>
									<{/if}>
								</div>
                            </div>
                        </div>

                    </div>
                    <hr class="seprLine marginT0">
                    <div class="yxje">
                        <div class="box-title">
                            <div><span class="line"></span>游戏简介&评测<div class="packup"></div></div>
                        </div>
                        <hr class="seprLine">
                        <div class="detail-cont">
                            <p><{$data.introduction}></p>
                        </div>
                        <hr class="seprLine">
                    </div>
                </div>
                <!--<div class="addmore"><i class="icon"></i>更多</div>-->
            </div>
        </div>
		<!--pc右侧公共-->
		<{include file="../common/moudle_pc_right.tpl"}>
    </div>
</div>
<div class="goTop"><a href="#top"><i class="icon"></i></a></div>
<{include file="../common/moudle_footer.tpl"}>
<script src="/gl/static/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function(){
        $("#tablist li a").click(function(){

        });
        $(window).resize();
    });
    $(window).resize(function(){
        var pageWidth = $(window).width();
        if(pageWidth >= 997){
            $(".ask a").click(function(){
                $(".askQuestion").toggle();
            })
        }
    });
	
	//跳转到登录页
	function gologin(){
		var url = "/user/login?backUrl=" + location.href;
		window.location.href = url;
	}
</script>
<{if $data.guid}>
<script>
	$(function(){
		$('.attention').click(function(){
			var that = this;
			//异步关注
			var mark = $(this).attr("gid");
			var action = $(this).attr("astatus");
			
			/*
			if(!SOL.isLogin()){
				login();
				return;
			};
			*/
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
					if(e.result == 200){
						if(action == "1"){
							$(that).removeClass('active').html('立即关注');
							$(that).attr("astatus",'0');
							myPop('取消成功');
						}else{
							$(that).addClass('active').text('已关注');
							$(that).attr("astatus",'1');
							myPop('关注成功');
						}
					} else {
						myPop('操作失败');
						//alert('操作失败');
					}
				}
			});
		});
	});
</script>
<{/if}>
<{include file="../common/footer.tpl"}>