<{include file="./common/header.tpl"}>
<style>
	body{background-color: #f5f8fa;}
	
	@media screen and (min-width: 997px) {
		body{background-color: #fff!important;}
	}
</style>
<script type="text/javascript">
	var isPc=false;
    var pageWidth = $(window).width();
    if(pageWidth >= 997){
        isPc=true;
    }
    if(!navigator.userAgent.match(/(iPhone|iPod|Android|ios)/i)){
        isPc=true;
    }
    if(isPc){
    	$("body").css("background-color","#fff");
    }
</script>
<div id="main" class="cont-width">
    <div class="row">
        <div class="leftPart">
            <div class="imgslider">
                <div class="slide" id="box">
                    <ul class="clear" style="display:none;">
	        			<{foreach $Recommend as $k => $v}>
	                        <li>
	                            <a href="<{$v.webUrl}>" target="_blank">
	                                <img src="<{$v.absImage}>"/>
	                            </a>
	                        </li>
	                        
	                    <{/foreach}>
                    </ul>
                    <div class="alcenter">
	        			<{foreach $Recommend as $k => $v}>
                        	<span class="circle"></span>
	                    <{/foreach}>
                    </div>
                </div>
            </div>
            <div class="content">
                <div class="c-box1">
                    
	        		<{foreach $hot_qa_list as $k => $v}>
	                    <div class="detail">
	                        <div class="game">
	                        	
	                            <div class="h-box moveleft">
	                                <a href="/question/info/<{$v.absId}>"><p><{$v.abstitle}></p></a>
	                                <a href="/question/info/<{$v.absId}>" class="goNext"></a>
	                            </div>
	                         
	                        </div>
	                        <div class="line"><div class="ltop"></div><div class="lbottom"></div></div>
	                        <div class="answer">
	                            点赞
	                        </div>
	                    </div>
	                <{/foreach}>
                </div>
                <!--<div class="addloading"><img src="http://n.sinaimg.cn/game/homepage/loading.gif"> 加载中....</div>-->

            </div>
        </div>
		<!--pc右侧公共-->
		<{include file="./common/moudle_pc_right.tpl"}>
    </div>
</div>


<{include file="./common/moudle_footer.tpl"}>
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="/gifweb/static/js/slider.js" charset="utf-8"></script>

<script>

    $(window).resize(function(){
        /*if($(window).width()<980){
            //$("#box ul li").height($(window).width()/800*315)
            $(".slide").height(315);
        }else{*/
        if($('.leftPart').width()>800){
            $(".slide").width(800);
            $(".slide").height(315);
        }else{
            $(".slide").width($('.leftPart').width());
            $(".slide").height(parseInt($('.leftPart').width()/800*315));
            //$("#box ul li").width(800);
        }

        /**/
        $("#box ul li").width($(".slide").width());
    })
    $(document).ready(function(){
    	 /*var pageWidth = $(window).width();
    	 if(pageWidth>=997){
    	 	var dd = $(".detail").width();
			$(".game .h-box p").width(dd-110);
    	 }else{
    	 	$(".game .h-box p").width(pageWidth-110);
    	 }*/
    	$("#box ul").show();
		$(window).resize();
	    var aaa2 = new slide({id:'box',addclass:'anyclass'});
    })
   
</script>

<script>
	var offset = 2; //默认分页位置
	var firstflag = true; //第一次载入的时候，防止第一个分类没有数据
	var loading_tips = $('.addmore'); //加载更多
	var in_loading = false; //初始话载入状态
	var openflag = false;
	
	//载入产品方法
	function load_info() {
	
		//判断是否载入
		if(!in_loading) {
			//无刷新获取攻略列表
			$.ajax({
				url:"/ajax_fun/get_home_qa_list_api/" + offset + '/',
				type:"get",
				dataType:"json",
				cache : false,
				async:false,
				beforeSend : function () {
				
				},
				success:function(r) {
					if(r.result == '200') {
						if(r.data.enoughflag == '2') {
							data = r.data.data.data;
							//console.log(data);
							html = "";
							for(var i in data) {
								html += '<div class="detail"><div class="game">';
								if(data[i].gameInfo.absImage){
									html += '<a href="/zq/juhe_page/'+data[i].gameInfo.absId+'" class="gamephoto gamephoto_index"><img src="' + data[i].gameInfo.absImage + '"/><span></span></a>';
									html += '<div class="h-box">';
									html += '<p><a href="/question/info/' + data[i].absId + '">' + data[i]['abstitle'] + '</a>';
									html += '</p>';
									html += '<a href="/question/info/' + data[i].absId + '" class="goNext"></a></div>';
								}else{
									html += '<div class="h-box moveleft">';
									if(data[i].gameInfo.abstitle){
										html += '<p><a href="/question/info/' + data[i].absId + '">【' + data[i].gameInfo.abstitle +'】'+ data[i]['abstitle'] + '</a>';
									}else{
										html += '<p><a href="/question/info/' + data[i].absId + '">' + data[i]['abstitle'] + '</a>';
									}
									html += '</p>';
									html += '<a href="/question/info/' + data[i].absId + '" class="goNext"></a></div>';
								}
								html += '</div>';
								html += '<div class="line"><div class="ltop"></div><div class="lbottom"></div></div><div class="answer">';
								if(data[i]['answerList']['0']['agreeCount'] >=1000){
									html += '<div class="answer-tip"><i>答</i><span>999+</span></div>';
								}else{
									html += '<div class="answer-tip"><i>答</i><span>' + data[i]['answerList']['0']['agreeCount'] + '</span></div>';
								}
								
								html += '<div class="a-message">';
								if(data[i]['answerList']['0']['abstitle']){
									html += '<a href="/answer/info/' + data[i]['answerList']['0']['absId'] + '"><p>'+data[i]['answerList']['0']['abstitle']+'</p></a>';
								}else{
									html += '快来成为第一个答主吧~';
								}
								html += '</div></div></div>';
							}
							$('.addmore').before(html);
							offset += 1;
							
							if (data.length < 20) {
								loading_tips.hide();
							} else {
								loading_tips.show();
							}
						}else{
							if(openflag){
								alert('没有更多了噢~');
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
<script src="/gifweb/static/js/clamp.min.js" charset="utf-8"></script>
<script>
$(document).ready(function(){
	//回答内容超过四行显示省略号
	 /*var module = $(".a-message p");
    for(var i=0;i<module.length;i++){
        var mlist = module[i];
        $clamp(mlist, {clamp: 4});
    }
    var boxp = $(".h-box p");
    for(var i=0;i<boxp.length;i++){
        var mlist = boxp[i];
        $clamp(mlist, {clamp: 2});
    }*/

	//显示更多信息
	$('.addmore').on('click', function () {
		openflag = true;
		load_info();
	});
	
	$(window).resize();
});
</script>

<{include file="./common/footer.tpl"}>