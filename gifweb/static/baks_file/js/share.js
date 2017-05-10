/**
 * Created by jiantao5 on 2016/1/14.
 */
$(document).ready(function(){
    /* mySwiper = new Swiper('#mobileshare',{
     //direction: 'vertical',//竖着swip
     //noSwipingClass : 'stop-swiping',//不让swip
     onSlideChangeEnd: function(swiper){
     //console.log(mySwiper.activeIndex);
     },
     pagination : '.swiper-pagination',
     loop: false,
     noSwiping : true
     });*/
    $(".popMobileshare").on("click",function(){
        if($(this).hasClass("popMobileshare")){
            $(".popMobileshare").hide("fast");
            $(".adiv").hide();
            $(".qdiv").hide();
        }
    });
    $(".popMobileshare").height("100%");
    $(".popMobileshare").hide();
})

