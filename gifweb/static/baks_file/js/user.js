/*
* 用户中心JS
*/
$(document).ready(function(){
   
   var count = 60; //间隔函数，1秒执行
   var curCount;//当前剩余秒数
   
   /*检测验证码输入*/
   $(".login-container").click(function(){
		var mobile = $.trim($("#phone").val());
		var password = $("#password").val();
		var patternMobile = /^13\d{9}$|^14\d{9}$|^15\d{9}$|^17\d{9}$|^18\d{9}$/;
		if(mobile != '' && patternMobile.test(mobile)){
			$("#errormsg").html('');
			$(".errormsg").css("display","none");
			return true;
		}						 
   });
   
   /*发送手机验证码*/
   $(".password span").click(function(){
		var mobile = $.trim($("#phone").val());
		var has_send = $("#phone").attr("data-has");
		var patternMobile = /^13\d{9}$|^14\d{9}$|^15\d{9}$|^17\d{9}$|^18\d{9}$/;
		
		if(has_send == 1){
			return false;	
		}
		
		// 倒计时
		curCount = count;
		
		if(mobile == ''){
			$("#errormsg").html('手机号码不能为空');
			$(".errormsg").css("display","");
			return false;	
		}
		
		if (!patternMobile.test(mobile)){
			//$(".loginbtn").removeClass("complete");
			$("#errormsg").html('请正确输入手机号码');
			$(".errormsg").css("display","");
			return false;
		}
		$(this).removeClass("color47");
   		var serStr = '';
   		serStr = decodeURIComponent($('#myform').serialize());
		$.ajax({
			type : 'POST',
			dataType : 'json',
			cache : false,
			url : '/ajax_fun/getVcode',
			data : {phone: mobile},
			success:function(res){
				if (res.result == '200') {
					$(".errormsg").css("display","none");
					//$(".loginbtn").addClass("complete");
					$("#phone").attr("data-has", 1);
					$(".password span").html('重新发送(60)');
					InterValObj = window.setInterval(SetRemainTime, 1000); //启动计时器，1秒执行一次
				}else{
					//$(".loginbtn").removeClass("complete");
					$("#errormsg").html(res.message);
					$(".errormsg").css("display","");
				}
			}
		})
   });
   
   /*登录表单提交*/
   $(".loginbtn").click(function(){
		var mobile = $.trim($("#phone").val());
		var password = $("#password").val();
		var patternMobile = /^13\d{9}$|^14\d{9}$|^15\d{9}$|^17\d{9}$|^18\d{9}$/;
		if(mobile == '' || !patternMobile.test(mobile)){
			$("#errormsg").html('请正确输入手机号码!');
			$(".errormsg").css("display","");
			return false;
		}
		if(password == ''){
			$("#errormsg").html('请输入验证码!');
			$(".errormsg").css("display","");
			return false;
		}
		$("#loginForm").submit();							 
   });
   
   /* 回车监控 */
   $(".login-container").keydown(function(e){
		if(e.keyCode==13){
			var mobile = $.trim($("#phone").val());
			var password = $("#password").val();
			var patternMobile = /^13\d{9}$|^14\d{9}$|^15\d{9}$|^17\d{9}$|^18\d{9}$/;
			if(mobile == '' || !patternMobile.test(mobile)){
				$("#errormsg").html('请正确输入手机号码!');
				$(".errormsg").css("display","");
				return false;
			}
			if(password == ''){
				$("#errormsg").html('请输入验证码!');
				$(".errormsg").css("display","");
				return false;
			}
			$("#loginForm").submit();
		}
   });
   
   /*倒计时*/
   function SetRemainTime() {
        if (curCount == 0) {
            window.clearInterval(InterValObj);//停止计时器
            $(this).addClass("color47");
            $("#phone").attr("data-has", 0);//启用按钮
            $(".password span").html("重新发送");
        }
        else {
            curCount--;
            $(".password span").html( "重新发送("+curCount + ")");
        }
    }
   
});