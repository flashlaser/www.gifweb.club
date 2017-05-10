<{include file="../common/header.tpl"}>
<link rel="stylesheet" href="/gl/static/css/zqjh.css">
<div id="main" class="cont-width">
    <div class="row">
        <div class="leftPart">
            <div class="content zqjhcontent">
                <div class="zqjh-cont">
                    <div class="boxtop">
                        <div class="game-detail">
                            <div class="clear">
                                <div class="fl gameimg"><a href="/game/detail_info/<{$data['absId']}>" ><img src="<{$data['absImage']}>" /></a></div>
                                <div class="fl gmname">
                                    <a href="/game/detail_info/<{$data['absId']}>" ><p><{$data['abstitle']}></p></a>
                                    <div class="p2">
										<{foreach $data.type as $vo}>
											<em><{$vo}></em>
										<{/foreach}>
										<div class="vericlline"></div>
                                        <p><{$data.wapadd.size}></p>
                                    </div>
                                </div>
                            </div>
							<{if $data.guid}>
								<{if $data.attentioned}>
									<div gid="<{$data['absId']}>" astatus='1' class="attention active">已关注</div>
								<{else}>
									<div gid="<{$data['absId']}>" astatus='0' class="attention"><em>立即</em>关注</div>
								<{/if}>
							<{else}>	
								<div gid="<{$data['absId']}>" onclick='gologin()' class="attention"><em>立即</em>关注</div>
							<{/if}>
                        </div>
                        <div class="row oneGameGo">
                            <a href="/question/qlist/<{$data['absId']}>" class="col-xs-4 col-md-4 wdgc">
                                <i class="zqjhicon"></i>
                                <span>问答广场</span>
                            </a>
							
							<{if $data.is_mobile == 1}>
							<div class="col-xs-4 col-md-4 vericlline"></div>
							<a href="<{if $data.buyAddress}><{$data.buyAddress}><{else}>javascript:myPop('暂无下载地址')<{/if}>" target='_blank' class="col-xs-4 col-md-4 download">
								<i class="zqjhicon"></i>
								<span>下载游戏</span>
							</a>
							<{/if}>
                        </div>
                    </div>
					<{if count($data.shortcutList)>0 }>
                    <div class="kjl">
                        <div class="box-title">
                            <div><span class="line"></span>快捷栏</div>
                        </div>
                        <hr class="seprLine">
                        <div class="imgList">
                           <ul class="row clear">
								<{foreach from=$data.shortcutList item=v key=key name=$data.shortcutList}>
									<li class="col-xs-6 col-sm-6 col-md-3"><a href="<{$v['webUrl']}>"><img src="<{$v['absImage']}>"/></a></li>
								<{/foreach}>
                           </ul>
                        </div>
                    </div>
					<{/if}>
                    <div class="gldq">
                        <div class="box-title">
                            <div><span class="line"></span>攻略大全<div class="packup"></div></div>
                        </div>
                        <hr class="seprLine">
                        <div class="hottopic">
                            <ul class="nav clear" role="tablist" id="tablist">
								<{foreach from=$data.raidersClassList item=v key=key name=$data.raidersClassList}>
									<li role="presentation" <{if $v@first}><{$firstraidersid = $v['absId']}>class="active"<{/if}>><a href="#<{$v['absId']}>" bid="<{$v['absId']}>" aria-controls="home" role="tab" data-toggle="tab"><strong><{$v['abstitle']}></strong></a></li>
								<{/foreach}>
                            </ul>
                            <hr class="seprLine">
                        </div>
                        <div class="tab-content questionlist">
								<{foreach from=$data.raidersClassList item=v key=key name=$data.raidersClassList}>
									<div id="<{$v['absId']}>" role="tabpanel" class="tab-pane <{if $v@first}>active<{/if}> questionbox">

									</div>
								<{/foreach}>
                            <!-- </ul> -->
						
                        </div>

                    </div>
                </div>
                <div class="addmore"><i class="icon"></i>更多</div>
				<div id='nomessage' style='display:none;background-color: #f5f5f5;border: medium none;color: #777777;font-size: 15px;line-height: 45px;text-align: center;'><i class="icon"></i>这里什么都没有哦~</div>
            </div>
        </div>
		<!--pc右侧公共-->
		<{include file="../common/moudle_pc_right.tpl"}>
    </div>
</div>
<{include file="../common/moudle_footer.tpl"}>

<script>
	var cat = "<{$firstraidersid}>"; //默认分类id
	var offset = 1; //默认分页位置
	var firstflag = true; //第一次载入的时候，防止第一个分类没有数据
	//var loading_img = $('#loading_img');
	var loading_tips = $('.addmore'); //加载更多
	var nomessage_tips = $('#nomessage'); //没有更多
	var in_loading = false; //初始话载入状态
	var next = 1; //初始化下一个值
	var max_id = 0; //最后一条数据初始化
	
	//格式化字串
	function CommaFormatted(nStr) {
		nStr += '';
		var x = nStr.split('.');
		var x1 = x[0];
		var x2 = x.length > 1 ? '.' + x[1] : '';
		var rgx = new RegExp(/(\d+)(\d{3})/g);
		 
		while (rgx.test(x1)) {
			x1 = x1.replace(rgx, '$1' + ',' + '$2');
		}
		 
		return x1 + x2;	
	}

	//载入产品方法
	function load_info(cat_id,flag) {
		var pagew = $(window).width();
		//判断是否载入
		if(!in_loading) {
			//当前分类与请求分类不一致
			if(cat != cat_id) {
				//$('#titconarea_ul').html(''); //列表清空
				offset = 1; //位置偏移量重置为1
				cat = cat_id; //将请求分类id赋值给当前cat
				loading_tips.show();
			} else if (!next) { //判断同一类中，是否有下一页内容
					return;
			}

			//无刷新获取攻略列表
			$.ajax({
				url:"/ajax_fun/get_list_info_api/" + cat_id + "/" + 1 + '/' + offset + '/',
				type:"get",
				dataType:"json",
				cache : false,
				async:false,
				beforeSend : function () {
					//in_loading = true;
					//loading_tips.hide();
					//loading_img.show();
				},
				success:function(r) {
					if(r.result == '200') {
						if(r.data.enoughflag == '2') {
							var target = "";
							if(pagew>980){
								target = "_blank";
							}
							data = r.data.data;
							html = "";
							for(var i in data) {
								html += '<div class="q-detail">';
								html += '<h4><a href="/raiders/info/' + data[i].absId + '" target="'+target+'">' + data[i].abstitle + '</a></h4>';
								//html += '<span>浏览:' + data[i].scanCount + ' &nbsp; 受用: ' + data[i].praiseCount + '</span>';
								html += '<span>' + data[i].cTime + '</span>';
								html += '</div><hr class="f0line">';		
								max_id = data[i].absId;
							}
							$('#'+cat_id+'').append(html);
							offset += 1;
							
							nomessage_tips.hide();
							if (data.length < 10) {
								loading_tips.hide();
								//nomessage_tips.show();
							} else {
								loading_tips.show();
							}
						}else{
							if(flag == "2"){
								myPop('没有更多攻略了');
							}else{
								nomessage_tips.show();
							}

							loading_tips.hide();
						}
					
					}else{
						myPop('获取数据失败');
						//alert('获取数据失败');
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
  		//窗口尺寸改变
        $(window).resize();
        
		//绑定tab事件
        $("#tablist li a").click(function(){
			var load_id = $(this).attr('bid');
			max_id = 0;
			load_info(load_id,'1');
        });
		
		//载入信息
		load_info(cat , '1');

		firstflag = false;
		//显示更多信息
		$('.addmore').on('click', function () {
			load_info(cat,'2');
		});
    });
	//跳转到登录页
	function gologin(){
		var url = "/user/login?backUrl=" + location.href;
		window.location.href = url;
		return false;
	}
	$(window).resize(function(){
		var img1w = $($(".imgList ul li img")[0]).width();
  		var imgh = parseInt(img1w/308*180);
  		$(".imgList ul li img").height(imgh);
	})
</script>
<{if $data.guid}>
<script>
	$(function(){
		$('.attention').click(function(){
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
					if(e.result == 200){
						if(action == "1"){
							$(that).removeClass('active').text('立即关注');
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