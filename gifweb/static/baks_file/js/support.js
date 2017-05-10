/**
 * Created by jiantao5 on 2016/1/7.
 */

//对indexOf的支持
if (!Array.prototype.indexOf){
    Array.prototype.indexOf = function(elt /*, from*/){
        var len = this.length >>> 0;
        var from = Number(arguments[1]) || 0;
        from = (from < 0)
            ? Math.ceil(from)
            : Math.floor(from);
        if (from < 0)
            from += len;
        for (; from < len; from++)
        {
            if (from in this &&
                this[from] === elt)
                return from;
        }
        return -1;
    };
}
//对placeholder的支持
if( !('placeholder' in document.createElement('input')) ){

    $('input[placeholder],textarea[placeholder]').each(function(){
        var that = $(this),
            text= that.attr('placeholder');
        if(that.val()===""){
            that.val(text).addClass('placeholder');
        }
        that.focus(function(){
            if(that.val()===text){
                that.val("").removeClass('placeholder');
            }
        })
            .blur(function(){
                if(that.val()===""){
                    that.val(text).addClass('placeholder');
                }
            })
            .closest('form').submit(function(){
                if(that.val() === text){
                    that.val('');
                }
            });
    });
}

if (!document.addEventListener) {
(function(){
//为window对象添加
addEventListener=function(n,f){
if("on"+n in this.constructor.prototype)
this.attachEvent("on"+n,f);
else {
var o=this.customEvents=this.customEvents||{};
n in o?o[n].push(f):(o[n]=[f]);
};
};
removeEventListener=function(n,f){
if("on"+n in this.constructor.prototype)
this.detachEvent("on"+n,f);
else {
var s=this.customEvents&&this.customEvents[n];
if(s)for(var i=0;i<s.length;i++)
if(s[i]==f)return void s.splice(i,1);
};
};
dispatchEvent=function(e){
if("on"+e.type in this.constructor.prototype)
this.fireEvent("on"+e.type,e);
else {
var s=this.customEvents&&this.customEvents[e.type];
if(s)for(var s=s.slice(0),i=0;i<s.length;i++)
s[i].call(this,e);
}
};
//为document对象添加
HTMLDocument.prototype.addEventListener=addEventListener;
HTMLDocument.prototype.removeEventListener=removeEventListener;
HTMLDocument.prototype.dispatchEvent=dispatchEvent;
HTMLDocument.prototype.createEvent=function(){
var e=document.createEventObject();
e.initMouseEvent=function(en){this.type=en;};
e.initEvent=function(en){this.type=en;};
return e;
};
//为全元素添加
var tags=[
"Unknown","UList","Title","TextArea","TableSection","TableRow",
"Table","TableCol","TableCell","TableCaption","Style","Span",
"Select","Script","Param","Paragraph","Option","Object","OList",
"Meta","Marquee","Map","Link","Legend","Label","LI","Input",
"Image","IFrame","Html","Heading","Head","HR","FrameSet",
"Frame","Form","Font","FieldSet","Embed","Div","DList",
"Button","Body","Base","BR","Area","Anchor"
],html5tags=[
"abbr","article","aside","audio","canvas","datalist","details",
"dialog","eventsource","figure","footer","header","hgroup","mark",
"menu","meter","nav","output","progress","section","time","video"
],properties={
addEventListener:{value:addEventListener},
removeEventListener:{value:removeEventListener},
dispatchEvent:{value:dispatchEvent}
};
for(var o,n,i=0;o=window["HTML"+tags[i]+"Element"];i++)
tags[i]=o.prototype;
for(i=0;i<html5tags.length;i++)
tags.push(document.createElement(html5tags[i]).constructor.prototype);
for(i=0;o=tags[i];i++)
for(n in properties)Object.defineProperty(o,n,properties[n]);
})();}