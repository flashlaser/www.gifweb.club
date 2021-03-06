<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="Cache-Control" content="no-cache" >
        <meta id="viewport" name="viewport" content="width=device-width, initial-scale=1.0,minimum-scale=1.0, maximum-scale=1.0">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link rel="apple-touch-icon" href="http://u1.sinaimg.cn/3g/image/upload/0/110/176/19509/64477c90.png" />
        <title>全民手游攻略帮助</title>
        <link rel="stylesheet" href="/gl/static/css/help_app.css?v=1.0"/>
        <script src="/gl/static/js/zepto.js"></script>
    </head>
    <body>
    <!--header-->
    <{if $u_infos}>
        <div class="helphead">您的经验值：<{$exp}></div>
    <{/if}>


    <section class="help-cnt">
        <div class="art">
            <h2>1、等级如何划分？</h2>
            <ul class="ullist">
                <li class="title">
                    <span>等级</span><span>经验值</span>
                </li>
                <li><span>1</span><span>50</span></li>
                <li><span>2</span><span>100</span></li>
                <li><span>3</span><span>200</span></li>
                <li><span>4</span><span>400</span></li>
                <li><span>5</span><span>800</span></li>
                <li><span>6</span><span>1500</span></li>
                <li><span>7</span><span>3000</span></li>
                <li><span>8</span><span>6000</span></li>
                <li><span>9</span><span>15000</span></li>
                <li><span>10</span><span>30000</span></li>
            </ul>
        </div>
        <div class="art">
            <h2>2、如何获得经验值？</h2>
            <div class="t2-cont">
                <table cellpadding="0" cellspacing="1" width="100%" border="1" class="jytable">
                    <tr class="title"><td>操作</td><td>经验值</td><td>上限</td></tr>
                    <tr><td>首次提问</td><td>+10</td><td>10</td></tr>
                    <tr><td>回答</td><td>+1</td><td>5次/日，同一问题首次</td></tr>
                    <tr><td>问题成为热门</td><td>+5</td><td>无</td></tr>
                    <tr><td>答案成为热门</td><td>+20</td><td>无</td></tr>
                    <tr><td>回答被大神赞</td><td>+2</td><td>40/日</td></tr>
                    <tr><td>回答非大神赞</td><td>+1</td><td>20/日</td></tr>
                </table>
            </div>

        </div>
        <div class="art">
            <h2>3、什么是大神？</h2>
            <div>
                <p>大神是指在游戏中造诣颇深的游戏玩家，俗称高手、大腿，他们技术好，玩得精，经验多，能够给新手玩家提供很多建设性的指导意见。</p>
            </div>
        </div>
    </section>
    </body>
</html>
