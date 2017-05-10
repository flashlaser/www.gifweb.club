<{include file="../common/header.tpl"}>
<div id="main" class="cont-width">
    <div class="row">
        <div class="leftPart">
            <div class="content">
                <div class="singleTitle">
                    <div class="box-title">
                        <div class="phone-area"><span class="line"></span>"<{$data['abstitle']}>"问答</div>
                    </div>
                </div>
				
				<{if count($data.data.hotList) > 0}>
                <div class="c-box1">
                    <div class="pcComn-tle single-pcCome-tle"><p>热门问题</p></div>
                    <div class="hot">热门问题</div>

					<{foreach from=$data.data.hotList item=v key=key name=$data.data.hotList}>
						<div class="detail">
							<div class="game">
								<a class="gamephoto"><img src="<{$v['headUrl']}>"/><span></span></a>
								<div class="h-box">
									<a href="/question/info/<{$v['absId']}>" ><p><{$v['abstitle']}><{if $v['imageCount']}><i class="icon"></i><{/if}></p></a>
									<a href="/question/info/<{$v['absId']}>" class="goNext"></a>
								</div>
							</div>
							<div class="line"><div class="ltop"></div><div class="lbottom"></div></div>
							<div class="answer">
								<div class="answer-tip"><i>答</i><span><{if $v['answerList'][0]['agreeCount']<1000}><{$v['answerList'][0]['agreeCount']}><{else}>999+<{/if}></span></div>

								<{if $v['answerList']['0']['abstitle']}>
									
										<div class="a-message"><p><a href="/answer/info/<{$v['answerList']['0']['absId']}>"><{$v['answerList']['0']['abstitle']}></a></p></div>
									
								<{else}>
								<div class="a-message">
									<a href="/question/answer/<{$v['absId']}>">快来成为第一个答主吧~</a>
								</div>
								<{/if}>
							</div>
						</div>
					<{/foreach}>
                </div>
				<{/if}>
				
                <div class="c-box1" id="newanswer">
					<{if count($data.data.newList) > 0}>
                    <div class="pcComn-tle single-pcCome-tle"><p>最新问题</p></div>
                    <div class="hot firstnew">最新问题</div>
					
					<{foreach from=$data.data.newList item=vv key=key name=$data.data.newList}>
						<div class="detail">
							<div class="game">
								<a class="gamephoto"><img src="<{$vv['headUrl']}>"/><span></span></a>
								<div class="h-box">
									<a href="/question/info/<{$vv['absId']}>"><p><{$vv['abstitle']}><{if $vv['imageCount']}><i class="icon"></i><{/if}></p></a>
									<a href="/question/info/<{$vv['absId']}>" class="goNext"></a>
								</div>
							</div>
							<div class="line"><div class="ltop"></div><div class="lbottom"></div></div>
							<div class="answer">
								<div class="answer-tip"><i>答</i><span><{if $vv['answerList']['0']['agreeCount']<1000}><{if $vv['answerList']['0']['agreeCount']}><{$vv['answerList']['0']['agreeCount']}><{else}>0<{/if}><{else}>999+<{/if}></span></div>
									<div class="a-message">
										<p>
										<{if $vv['answerList']['0']['abstitle']}>
											<a href="/answer/info/<{$vv['answerList']['0']['absId']}>"><{$vv['answerList']['0']['abstitle']}></a>
										<{else}>
											<a href="/question/answer/<{$vv['absId']}>">快来成为第一个答主吧~</a>
										<{/if}>
										</p>
									</div>
							</div>
						</div>
					<{/foreach}>
					<{/if}>
					
					<{if count($data.data.newList) > 0}>
						<{if count($data.data.newList) > 10}>
							<div class="addmore"><i class="icon"></i>更多</div>
						<{/if}>
					<{else}>
						<div style='color: #777777;font-size: 15px;line-height: 45px;text-align: center;margin: 100px 0;'>一大波大神伸着大腿等你来抱，腿毛速来...</div>
					<{/if}>
                </div>

            </div>
        </div>
		<!--pc右侧公共-->
		<{include file="../common/moudle_pc_right.tpl"}>
    </div>
</div>
<div class="goTop"><i class="icon"></i></div>
<{include file="../common/moudle_footer.tpl"}>

<script src="/gl/static/js/jquery-1.11.3.min.js"></script>

<script>
	var offset = 2; //默认分页位置
	var firstflag = true; //第一次载入的时候，防止第一个分类没有数据
	var loading_tips = $('.addmore'); //加载更多
	var in_loading = false; //初始话载入状态
	var openflag = false;
	
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
	function load_info() {
		//判断是否载入
		if(!in_loading) {
			//无刷新获取攻略列表
			$.ajax({
				url:"/ajax_fun/getzq_qa_list_api/<{$data.id}>/" + offset + '/',
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
							data = r.data.data.data.newList;
							//console.log(data);
							html = "";
							for(var i in data) {
								html += '<div class="detail"><div class="game">';
								html += '<a class="gamephoto"><img src="' + data[i].headUrl + '"/><span></span></a>';
								html += '<div class="h-box">';
								html += '<a href="/question/info/' + data[i].absId + '/"><p>' + data[i].abstitle + '';
								if(data[i].imageCount){
									html += '<i class="icon"></i>';
								}
								html += '</p></a>';
								html += '<a href="' + data[i].absId + '" class="goNext"></a></div></div>';
								html += '<div class="line"><div class="ltop"></div><div class="lbottom"></div></div><div class="answer">';
								html += '<div class="answer-tip"><i>答</i><span>';
								if(data[i].answerCount < 1000){
									html += '' + data[i].answerCount + '</span></div>';
								}else{
									html += '999+</span></div>';
								}
								html += '<div class="a-message"><p>';
								if(data[i].answerList.length > 0){
									html += '<a href="/answer/info/">'+data[i]['answerList']['0']['abstitle']+'</a>';
								}else{
									html += '<a href="/question/answer/' + data[i].absId + '">快来成为第一个答主吧~</a>';
								}
								html += '</p></div></div></div>';
							}
							
							$('.addmore').before(html);
							offset += 1;

							if (data.length < 10) {
								loading_tips.hide();
							} else {
								loading_tips.show();
							}
						}else{
							if(openflag){
								//alert('没有更多攻略了');
								myPop('没有更多问题了');
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
	//显示更多信息
	$('.addmore').on('click', function () {
		openflag = true;
		load_info();
	});
	
	$(window).resize();
});
</script>
<script src="/gl/static/js/bootstrap.min.js"></script>
<{include file="../common/footer.tpl"}>