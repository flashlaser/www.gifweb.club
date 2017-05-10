(function($){
    $.fn.respondslide = function(options){
        var self = $(this);
        var defaults = {
            movebox:"#Scroll",
            single:"li",
            ratio:4/2,
            dotwp:"#ScrollThumb",
            // trans:'trans',
            time:5000
        }
        var len;
        var cur = 0;
        var options = $.extend(defaults, options);
        var movobj = self.find(options.movebox);
        var li;
        var width;


        function resize(){
            //alert(0);
            width = self.width();
            li.css('width',width);
            self.height(418);
            //console.log('resize complete');
            to(cur);
        }


        $(window).resize(resize);
        var init = function(){
            len = movobj.find(options.single).size();
            //console.log(len);
            /*for(var i=0;i<len;i++){
                //console.log(i);
                var dotclass = i==0?'selected':'dotItem';
                self.find(options.dotwp).append('<span class="'+dotclass+'" title="'+i+'"></span>');
            }*/

            movobj.append(movobj.html());
            li = self.find(options.single);
            //console.log(li);
            resize();
        };

        init();

        function to(n){
            li.stop().animate({left:-n*width});
            cur = n;
            if(n==len) n-=len;
            //console.log(n);
            self.find(options.dotwp+' span').eq(n).addClass('selected').removeClass('dotItem').siblings('span').removeClass('selected');
        }

        function preto(n){
            li.css('left',-n*width);
        }

        /*self.find(options.dotwp+' span').click(function(){
            var n = $(this).index('span');
            $(this).addClass('selected').removeClass('dotItem').siblings('span').removeClass('selected');
            to(n);
        });*/
        $(".slidbtn .pre").click(function(){
            if(cur-1<0){
                preto(len);
                cur =len;
            }
            cur--;
            to(cur);
        });
        $(".slidbtn .next").click(function(){
            if(cur==len){
                preto(0);
                cur =0;
            }
            cur++;
            to(cur);
        });
       /* var x;
        self.find('img').on({
            'touchstart':function(ev){
                // console.log(ev);
                x = ev.originalEvent.changedTouches[0].clientX;
            },
            'touchmove':function(e){
                e.preventDefault();
            },
            'touchend':function(ev){
                if(ev.originalEvent.changedTouches[0].clientX-x>20){
                    //ev.preventDefault();
                    //console.log('swr');
                    if(cur-1<0){
                        preto(len);
                        cur =len;
                    }
                    cur--;
                    to(cur);
                }else if(ev.originalEvent.changedTouches[0].clientX-x<-20){
                    // console.log('swl');
                    if(cur==len){
                        preto(0);
                        cur =0;
                    }
                    cur++;
                    to(cur);
                }
            }
        });*/
        /*setInterval(function(){
            if(cur==len){
                preto(0);
                cur =0;
            }
            cur++;
            to(cur);
        },options.time);*/

    };
})(jQuery);