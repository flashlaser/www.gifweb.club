/**
 * Created by jiantao5 on 2015/11/30.
 */
/*
 target:ֻ��Ϊ��ʹ�ü�........
 Author��guoxun01@126.com
 ���ݣ�IE6+��FF,CHROME,360�����εȵ�....
 ����һ������������
 ��һ�������þͿ��ԡ�
 var demo2 = new slide({id:'slid2'});
 ȫ���Ĳ�������---------------------->
 var demo=new slide({
 id:'slid',//������id,��ѡ��
 addclass:'active',//active״̬��class
 steppx:null,//Ĭ��Ϊ�������һ�㲻���?
 speed:40,//�ٶ�
 time:2000//ʱ����ѡ��
 });
 */

(function(){
    var count = 0;
    //---------------------------------------------TOOL  FUNCTION-----------------------------------
    var $ = function(id){
        return document.getElementById(id);
    };
    var wrap = document.getElementById('slid');
    //console.log(wrap.clientWidth);

    var EventUtil = {
        addHander : function(e, t, hander) {
            if (e.addEventListener) {
                e.addEventListener(t, hander, false);
            } else if (e.attachEvent) {
                e.attachEvent('on' + t, hander);
            } else {
                e['on' + t] = hander;
            }
        },
        removeHander : function(e, t, hander) {
            if (e.addEventListener) {
                e.removeEventListener(t, hander, false);
            } else if (e.attachEvent) {
                e.detachEvent('on' + t, hander);
            } else {
                e['on' + t] = null;
            }
        }
    };

    function getElementsByClassName(className, root, tagName) {
        if (root) {
            root = typeof root == "string" ? document.getElementById(root) : root;
        } else {
            root = document.body;
        }
        tagName = tagName || "*";
        if (document.getElementsByClassName) {
            return root.getElementsByClassName(className);
        } else {
            var tag = root.getElementsByTagName(tagName);
            var tagAll = [];
            for (var i = 0; i < tag.length; i++) {
                for (var j = 0, n = tag[i].className.split(' '); j < n.length; j++) {
                    if (n[j] == className) {
                        tagAll.push(tag[i]);
                        break;
                    }
                }
            }
            return tagAll;
        }
    }
    //a��ȡb
    var forEach = function(a,b){
        for(var p in b){
            a[p] = b[p];
        }
    }

    var setcss = function(obj,css_collection){
        for(var p in css_collection){
            obj.style[p] = css_collection[p];
        }
    }


    //---------------------------------------------TOOL  FUNCTION-----------------------------------

    function slide(para){
        if(typeof(para.id)!=='string'){
            alert('��Ҫ����һ��id���ǣ�');
        }

        //��ʼ����
        var seting = {
            id:'slid',//������id,��ѡ��
            addclass:'active',//�ı��class
            steppx:null,
            speed:40,//�ٶ�
            time:2000
        }

        forEach(seting,para);
        this.seting = seting;

        count++;
        var self = this;
        this.box = $(seting.id).getElementsByTagName('ul')[0];

        //����������ʽ
        setcss($(seting.id),{position:'relative',overflow:'hidden'});
        setcss(this.box,{position:'absolute',top:0,left:0,display:'block',width:'9999px'});

        //to click handle scrool
        this.handles = getElementsByClassName('circle',$(seting.id));
        //console.log(this.handles);
        //number
        this.n = $(seting.id).getElementsByTagName('ul')[0].getElementsByTagName('li').length;
        this.cur_num = 0;
        this.left = 0;
        this.width = seting.steppx||$(seting.id).clientWidth;
        this.addclass = seting.addclass;
        this.speed = seting.speed;
        //double || copy li
        (function(){
            var target = $(seting.id).getElementsByTagName('ul')[0].getElementsByTagName('li');
            var arr = [];
            for(var i=0;i<target.length;i++){
                arr.push(target[i].cloneNode(true));
            }
            for(var i=0;i<arr.length;i++){
                target[0].parentNode.appendChild(arr[i]);
            }
        })();


        this.bind();
        this.auto = function(n){
            var start_num = n||self.cur_num;
            start_num++;
            if(start_num>self.n){
                start_num= 1;
                self.cur_num = 0;
                self.left = 0;
            }

            //console.log(self.cur_num+1);
            self.status(self.cur_num+1);
            self.scrool(start_num);
        }

       this.timer = setInterval(this.auto,seting.time);

    }

    slide.prototype.showverion = function(){
        alert('verion:1.1;  '+ "usedcount: " + count + ';');
    };

    slide.prototype.scrool = function(n){
        var self = this;
        if(n == self.cur_num) return;
        var speed = n-self.cur_num>0?-self.speed:self.speed;
        var speed = speed*Math.abs(n-self.cur_num);
        // console.log('                       speed:               '+speed);
        var mov = function(){
            self.left += speed;
            // console.log('left:'+self.left+'target:'+n*self.width);

            var a = Math.abs(self.left)>=Math.abs(n*self.width)&&speed<=0;
            var b = Math.abs(self.left)<=Math.abs(n*self.width)&&speed>=0;

            if(a||b||self.left>0){
                self.left = -n*self.width;
                self.box.style.left = self.left+'px';
                self.cur_num = n;
                return;
            }else{
                self.box.style.left = self.left+'px';
                // console.log(self.left);
            }
            setTimeout(mov,12);
        };
        mov();
    };

    //�ı��±�״̬
    slide.prototype.status = function(n){
        for(var i=0;i<this.n;i++){
            this.handles[i].className = 'circle';
        }
        if(n==this.n) n=0;
        this.handles[n].className = 'circle '+this.seting.addclass;
    };

    slide.prototype.bind = function(){
        var self = this;
        for(var i=0;i<this.n;i++){
            EventUtil.addHander(this.handles[i],'mouseover',(function(i){
                return function(){
                    self.status(i);
                    clearInterval(self.timer);
                    self.scrool(i);
                };
            })(i));

            EventUtil.addHander(this.handles[i],'mouseout',(function(i){
                return function(){
                   self.timer = setInterval(self.auto,self.seting.time);
                };
            })(i));

        }
    };
    window.slide = slide;
})();