<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

/**
 * @Name Waptext_Model.php
 */
class Waptext_Model extends MY_Model {
	private $_cache_key_pre = '';
	private $_cache_expire = 600;
	public function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":games_wap:";
		$this->load->driver ( 'cache' );

	}


	//========================= 通过接口获取攻略正文(wap用)开始 ======================================
	/**
	 * 获取cms文章内容
	 */
	public function get_cms_info($id, $device) {
		$cache_id = $id . $device;
		$returnInfo = $this->_get_cms_info_from_cache ( $cache_id, $device );
		if (! is_array ( $returnInfo )) {
			$returnInfo = $this->_get_cms_info_from_api ( $id, $device );
			$this->_set_cms_info_to_cache ( $cache_id, $returnInfo );
		}
		return $returnInfo;
	}

	private function _get_cms_info_from_cache($cache_id, $device) {
		$cache_key = $this->_cache_key_pre . "get_cms_info:$cache_id";
		$returnInfo = $this->cache->redis->get ( $cache_key );
		$returnInfo && $returnInfo = json_decode ( $returnInfo, 1 );
		return $returnInfo;
	}
	private function _set_cms_info_to_cache($cache_id, $data) {
		$cache_key = $this->_cache_key_pre . "get_cms_info:$cache_id";
		$this->cache->redis->set ( $cache_key, json_encode ( $data ), $this->_cache_expire );
	}

	private function _get_cms_info_from_api($id, $device) {
		$url = "http://wap.97973.com/mggl/get_gl_text_info.d.html?ids=" . $id . "&device=" . $device;
		$repeat = 5;
		$returnInfo = false;
		while ( ! is_array ( $returnInfo ) && $repeat -- > 0 ) {
			$json_data = Util::curl_get_contents ( $url );
			$returnInfo = json_decode ( $json_data, true );
		}

		return $returnInfo;
	}
	//========================= 通过接口获取攻略正文(wap用)结束 ======================================


	//========================= CMS方式处理攻略正文(PC用)开始 ======================================
	public function Cms_TagReplace($contents){
		$contentHtml = $this->sdSlideTagReplace($contents, 1);
		$contentHtml = $this->hdSlideTagReplace($contentHtml, 1);
		$contentHtml = $this->weiboListTagReplace($contentHtml, 1);
		$contentHtml = $this->indexLinkReplace($contentHtml);  //链接中index.shtml、index.html去除
		$contentHtml = $this->indexKeywordAddLink($contentHtml,$product);//文章关键字加链接

		$contentHtml = $this->f_article_video_play($contentHtml);
		$videoNewsListNew = $this->game_pc_article_video_play_new($contentHtml);

		$reg = '/<\!--mce-plugin-videoList2\[(.+?)\]mce-plugin-videoList2-->/s';
		$contents = preg_replace($reg, $videoNewsListNew, $contentHtml);

		return $contents;
	}

	//CMS处理攻略内容方法
	/**
	 * 普清图标签替换
	 */
	private function sdSlideTagReplace($content, $page=1){

	    $sd = '/<\!--(\s*)图集开始(\s*)<div class="fake_multiimages_upload">(.*?)<\/div>(\s*)图集结束(\s*)-->/s';
	    $hd =  '/<\!-- HDSlide (.+?) -->/s';

	    if( preg_match_all($sd, $content, $matchs) ){

	        if( preg_match_all($hd, $content, $matchsHD) ){

	            $content =  preg_replace($sd,"<!-- 已有高清,不显示标清 -->", $content);
	            return $content;

	        }

	        $data = $matchs[3][0];
	        $li = '/<li>(.*?)<\/li>/s';
	        preg_match_all($li, $data, $matchsLi);

	        $liArray = $matchsLi[1];
	        $i = 0;
	        $piclist = "";
	        foreach ($liArray as $l){
	            $picInfo = '/<img[^<>]*?alt=["\']([^"\']+?)["\'][^<>]*?>[^<>]*?<span[^<>]*?>(.*?)<\/span>/s';
	            $smallPicInfo = '/<img[^<>]*?src=["\']([^"\']+?)["\'][^<>]*?>[^<>]*?<span[^<>]*?>(.*?)<\/span>/s';
	            preg_match_all($picInfo, $l, $matchsBigPic);
	            preg_match_all($smallPicInfo, $l, $matchsSmallPic);
	            $bigPic = $matchsBigPic[1][0];

	            $picStr =<<<EOF
	                src="$bigPic"
EOF;
	            $div_style = "";
	            if($i>1){
	                $picStr =<<<EOF
	               srcUrl="$bigPic"
EOF;
	                $div_style = 'style="display: none;"';
	            }
	            $smallPic = $matchsSmallPic[1][0];
	            $title = $matchsSmallPic[2][0];
	            $piclist .=<<<EOF
	            <div $div_style class="img_wrapper" thumbImg="$smallPic"><!-- 更换小图 -->
	              <img $picStr alt="$title">
	              <span class="img_descr">$title</span>
	            </div>
EOF;
	            $i++;

	        }

	        $sdHtml =<<<EOF
	        <!-- 标清图 begin -->
	        <link rel="stylesheet" href="http://news.sina.com.cn/css/87/20120920/style_sdfigure.css" />
	        <div id="sdFigure1_$page" class="sdFigureWrap"></div>
	        <!--thumb为0表示没有底部缩略图,非0为有图-->
	        <div id="dataSource_$page" thumb=1>
	$piclist
	        </div>

	        <script type="text/javascript">
	          //普清图初始化
	          jsLoader(ARTICLE_JSS.jq).jsLoader({url:ARTICLE_JSS.sdfigure,charset:'gbk',callback:function(){
	            var ArticleImgInfo=ArticleCollectData("dataSource_$page","img_wrapper","img_descr");
	            var ArticleSdFigure1=new ArticleSdFigure('sdFigure1_$page',ArticleImgInfo);
	            ArticleSdFigure1.init();
	        });
	        </script>
	              <!-- 标清图 end -->
EOF;
	        $content = preg_replace($sd, $sdHtml, $content);

	    }
	    return $content;
	}

	/**
	 * 高清图标签替换
	 */
	private function hdSlideTagReplace($content, $page=1){
	  $reg = '/<\!-- HDSlide (.+?) -->/s';
	  $hdSlideTemplate = <<<EOF
<!-- Hd begin -->
<div id="HdFigure1_{slideID}" class="hdFigureWrap" style="margin-top:20px;"></div>
<link rel="stylesheet" href="http://news.sina.com.cn/css/87/20121218/style_hdfigure_v2.css" />
<script type="text/javascript">
  //高清图初始化
  jsLoader(ARTICLE_JSS.jq).
  jsLoader(ARTICLE_JSS.sinflash).
  jsLoader({url:ARTICLE_JSS.hdfigure,charset:'GBK',callback:function(){
    \$(document).ready(function(){
    var HdFigure1_{slideID} = new HdFigure('HdFigure1_{slideID}', '{hdSlideLink}');
    HdFigure1_{slideID}.init();
    });
  }});
</script>
<!-- Hd end -->
EOF;

	  if( preg_match_all($reg, $content, $matchs) ){
	    foreach($matchs[0] as $k=>$match){
	      $slideID = $page.'_'.$k;
	      $hdSlideLink = trim($matchs[1][$k]);
	      $hdSlideCode = '';
	      if(!empty($hdSlideLink)){
	        $hdSlideCode = str_replace(
	          array('{slideID}', '{hdSlideLink}'),
	          array($slideID, $hdSlideLink),
	          $hdSlideTemplate
	        );
	      }
	      $content = str_replace($match, $hdSlideCode, $content);
	    }
	  }


	  return $content;
	}

	/**
	 * 微博列表标签替换
	 */
	private function weiboListTagReplace($content, $page=1){
	  $reg = '/<\!-- WeiboList (.+?) -->/s';
	  $weiboListTemplate = <<<EOF
	<link rel="stylesheet" href="http://news.sina.com.cn/css/268/2011/1110/17/weibo-all.css" />
	<style type="text/css">
	.weiboListBox{padding:0 10px 0 15px;border:1px solid #ccc;margin-top:10px;background-color:#fff;}
	.weiboListBox p{font-size:12px;line-height:20px;}
	.weiboListBox label{width:auto;height:auto;margin:0;background:none;float:none;}
	.weibo-list{background-color:transparent;}
	.weibo-list .weibo-list-item{margin-top:-1px;border-bottom:none;border-top:1px dashed #ccc;padding:15px 0 10px;overflow:hidden;}
	.weibo-commentbox .weibo-commentbox-form textarea{width:320px;}
	.weibo-list a:link,.weibo-list a:visited{color:#0082CB;}
	.weibo-list a:hover{color:#c00;}
	.weibo-list .weibo-list-meta a:link,.weibo-list .weibo-list-meta a:visited{color:#666;}
	.weibo-list .weibo-list-meta a:hover{color:#c00;}
	.weiboListBox label{padding-left:3px;}
	.weibo-commentbox .weibo-commentbox-form textarea{width:315px;}
	</style>
	<div class="weiboListBox otherContent_01" id="blk_weiboBox_{k}{page}" style="display:none" data-sudaclick="blk_weiboBox_{k}">
	<ol class="weibo-list" id="weiboList{k}{page}"></ol>
	</div>
	<script type="text/javascript">
	jsLoader(ARTICLE_JSS.sinalib).jsLoader(ARTICLE_JSS.weiboAll,function(){
	  Weibo.encoding = 'gbk'; 
	  var wbList1 = new Weibo.Widgets.List({
	    source: '#weiboList{k}{page}',
	    showUserCard: true,
	    stat_click: true
	  });
	  Weibo._getRequest({
	  url: 'http://topic.t.sina.com.cn/interface/api/html?api=statuses/show_batch',
	  data: {
	    ids: '{weiboMsgID}'
	  },
	  onsuccess: function(data){
	    if(!data.html){return}
	    SINA.query('#blk_weiboBox_{k}{page}')[0].style.display = 'block';
	    wbList1.reset(data);
	  }
	  });
	});
	</script>
	<!-- /微博列表 -->
EOF;

	  if( preg_match_all($reg, $content, $matchs) ){
	    foreach($matchs[1] as $k=>$match){
	      if( !$urlInfo = parse_url($match) ){
	      	continue;
	      }
	      $urlPath = trim($urlInfo['path'], '/');
	      if(strpos($urlPath, '/') === false ){continue;}
	      list($weiboUID, $weiboID) = explode('/', $urlPath);
	      if(empty($weiboID)){continue;}
	      $weiboMsgID = getWeiboMsgId($weiboID);
	      $weiboListCode = '';
	      if(strlen($weiboMsgID)){
	        $weiboListCode = str_replace(
	          array('{k}', '{page}', '{weiboMsgID}'),
	          array($k, $page, $weiboMsgID),
	          $weiboListTemplate
	        );
	      }
	      $content = str_replace($matchs[0][$k], $weiboListCode, $content);
	    }
	  }
	  return $content;
	}

	/**
	 * 根据微博url中的地址获取微博消息ID
	 */
	private function getWeiboMsgId($weiboID){
	  $apiURL = 'http://i.api.weibo.com/2/statuses/queryid.json?source=2739192977&type=1&isBase62=1&mid='.$weiboID;
	  $res = Util_Curl::get($apiURL);
	  if($res = json_decode($res, true)){
	    if(isset($res['id']) && $res['id'] != -1){
	      return $res['id'];
	    }
	  }
	  return '';
	}

	/**
	 * 投票标签替换
	 */
	private function surveyTagReplace($content){
	  $reg = '/<\!--mce-plugin-survey\[(.+?)\]mce-plugin-survey-->/s';
	  if( preg_match_all($reg, $content, $matchs) ){
	    foreach($matchs[0] as $k=>$match){
	      $surveyLink = trim($matchs[1][$k]);
	      $surveyCode = '';
	      if(!empty($surveyLink)){
	        $surveyCode = '<script charset="gbk" src="'.$surveyLink.'"></script>';
	      }
	      $content = str_replace($match, $surveyCode, $content);
	    }
	  }
	  return $content;
	}

	/**
	 * 视频标签替换
	 */
	private function videoTagReplace($content, $api){
	  $reg = '/<\!--mce-plugin-videoList\[(.+?)\]mce-plugin-videoList-->/s';

	  if( preg_match($reg, $content, $matchs) ){
	    $videoList = json_decode($matchs[1], true);
	    if(count($videoList['videos']>0)){
	      $video_play_list = $api->t('97973_pc_doc_article_video', $videoList);
	      $content = str_replace($matchs[0], $video_play_list, $content);
	    }
	  }
	  return $content;
	}

	/**
	 * 正文链接index.shtml、index.html去除
	 */
	private function indexLinkReplace($content){
	  $reg = '/href=(\'|")http:\/\/(.*?)\.sina\.com\.cn\/(.*?)\/index\.s?html(\'|")/';
	  $content = preg_replace($reg, 'href=$1http://$2.sina.com.cn/$3/$1', $content);
	  return $content;
	}

	/**
	 * 正文页中关键词自动橙色添加官网超链
	 */
	private function indexKeywordAddLink($content,$product){
	  //空中网产品库名
	  $kzproarr=array('坦克世界','战舰世界','战机世界','激战2','绝世武神','龙翼编年史','赛车联盟','国战','大决战','龙门客栈','机甲世界','猎灵','坦克世界：将军','坦克风云','龙将','傲视天地','梦幻飞仙','黄金国度','战将Online','范特西篮球经理','口袋战争');
	  //完美世界产品库名
	  $wmproarr=array('无冬之夜2','无冬Online','无冬OL','神魔大陆2','HEX','射雕英雄传OL','诛仙世界','笑傲江湖OL','梦幻诛仙2','诛仙3','武林外传','神鬼世界','神鬼传奇','完美国际2','神雕侠侣','圣斗士星矢');
	  if(in_array($product,$kzproarr)){
	    $vipmarkarr=array(  
		  array('坦克世界','http://wot.kongzhong.com/'),
		  array('战舰世界','http://wows.kongzhong.com/'),
		  array('战机世界','http://wowp.kongzhong.com/'),
		  array('激战2','http://gw2.kongzhong.com/'),
		  array('绝世武神','http://js.kongzhong.com/'),
		  array('龙翼编年史ol','http://ly.kongzhong.com/'),
		  array('龙翼编年史','http://ly.kongzhong.com/'),
		  array('赛车联盟','http://acr.kongzhong.com/'),
		  array('闪电战3','http://b3.kongzhong.com/'),
		  array('新功夫世界','http://wok.kongzhong.com/'),
		  array('龙2','http://l2.kongzhong.com/'),
		  array('国战','http://zhan.kongzhong.com/'),
		  array('大决战','http://djz.kongzhong.com/'),
		  array('龙门客栈','http://lm.kongzhong.com/'),
		  array('机甲世界','http://jj.kongzhong.com/'),
		  array('功夫英雄','http://hero.kongzhong.com/'),
		  array('猎灵','http://sm.kongzhong.com/'),
		  array('龙珠ol','http://www.loong3d.com/'),
		  array('像素骑士团','http://74.kongzhong.com/'),
		  array('坦克世界：将军','http://wotg.kongzhong.com/'),
		  array('坦克风云','http://tkfy.kongzhong.com/'),
		  array('龙将','http://lj.zhulang.com/'),
		  array('傲视天地','http://td.zhulang.com/'),
		  array('梦幻飞仙','http://mhfx.zhulang.com/'),
		  array('航海之王','http://hh.zhulang.com/'),
		  array('逐浪棋牌','http://qp.zhulang.com/'),
		  array('德州扑克','http://pk.zhulang.com/'),
		  array('黄金国度','http://gd.zhulang.com/'),
		  array('战将online','http://zj.zhulang.com/'),
		  array('范特西篮球经理','http://ftx.zhulang.com/'),
		  array('超神战队','http://d11.kongzhong.com/'),
		  array('进击吧！三国','http://jjsg.kongzhong.com/'),
		  array('搞怪三国','http://sg.kongzhong.com/'),
		  array('口袋战争','http://kdzz.kongzhong.com/'));
	  }elseif(in_array($product, $wmproarr)){
	    $vipmarkarr=array(
	      array('无冬之夜ol','http://nw.wanmei.com/'),
	      array('无冬之夜2','http://nw.wanmei.com/'),
	      array('无冬之夜','http://nw.wanmei.com/'),
	      array('无冬online','http://nw.wanmei.com/'),
	      array('无冬ol','http://nw.wanmei.com/'),
	      array('无冬','http://nw.wanmei.com/'),
	      array('神魔大陆2','http://shenmo.wanmei.com/'), 
	      array('神魔大陆','http://shenmo.wanmei.com/'),
	      array('神魔2','http://shenmo.wanmei.com/'), 
	      array('HEX','http://hex.wanmei.com/'),
	      array('射雕英雄传OL','http://sd.wanmei.com/'),
	      array('射雕英雄传','http://sd.wanmei.com/'),
	      array('射雕OL','http://sd.wanmei.com/'),
	      array('诛仙世界','http://zxsj.wanmei.com/'),
	      array('笑傲江湖ol','http://xa.wanmei.com/'),
	      array('笑傲江湖','http://xa.wanmei.com/'),
	      array('诛仙前传','http://zhuxian.wanmei.com/'),
	      array('梦幻诛仙','http://mhzx2.wanmei.com'),
	      array('梦诛','http://mhzx2.wanmei.com'),
	      array('诛仙2','http://zhuxian.wanmei.com/'),
	      array('诛仙3','http://zhuxian.wanmei.com/'),
	      array('诛仙二','http://zhuxian.wanmei.com/'),
	      array('诛仙三','http://zhuxian.wanmei.com/'),
	      array('诛仙','http://zhuxian.wanmei.com/'),
	      array('武林外传','http://wulin2.wanmei.com/'),
	      array('神鬼世界','http://sgsj.wanmei.com/'),
	      array('神鬼传奇','http://sgcq.wanmei.com/'),
	      array('完美国际','http://w2i.wanmei.com/'),
	      array('神雕侠侣OL','http://sdxl.wanmei.com/'),
	      array('神雕侠侣online','http://sdxl.wanmei.com/'),
	      array('神雕侠侣','http://sdxl.wanmei.com/'),
	      array('圣斗士星矢','http://seiya.wanmei.com/')
	      );
	  }else{
	    $vipmarkarr=array();
	  }
	  if($vipmarkarr){
	    $i=0;
	      foreach($vipmarkarr as $vipv){
	        $i++;
	        
	        /*$pattern = '/(<div[^>]*>)([^<>]*)('.$vipv[0].')([^<>]*?)(<\/div>)/';
	        $pattern2 = '/(<p[^>]*>)([^<>]*)('.$vipv[0].')([^<>]*?)(<\/p>)/';
	        $content= preg_replace($pattern,'$1$2<a href="'.$vipv[1].'" target="_blank" class="vipmark">'.$vipv[0].'</a>$4$5',$content);
	        $content= preg_replace($pattern2,'$1$2<a href="'.$vipv[1].'" target="_blank" class="vipmark">'.$vipv[0].'</a>$4$5',$content);*/
	        $pattern = array('/(<p[^>]*>)([^<>]*?)(<\/p>)/','/(<div[^>]*>)([^<>]*?)(<\/div>)/');
	        foreach($pattern as $patternv){
	          //替换p标签内的关键词
	          if(preg_match_all($patternv,$content,$arr)!==false && !empty($arr[0])){

	            foreach($arr[2] as $key =>$val){
	              $replacestr[$key]=$arr[1][$key].str_ireplace($vipv[0], '<a href="'.$vipv[1].'" target="_blank" class="vipmark">'.$vipv[0].'</a>', $val).$arr[3][$key];
	            }
	            foreach($replacestr as $key2 =>$val2){
	              if(isset($arr[0][$key2])){
	                $content=str_replace($arr[0][$key2], $val2, $content);
	              }
	            }
	          }
	        }
	      }
	  }

	return $content;
	}

private function game_pc_article_video_play_new($contentHtml){
	$reg = '/<\!--mce-plugin-videoList2\[(.+?)\]mce-plugin-videoList2-->/s';
	$PID=115;
	$PURL='http://games.sina.com.cn';
	if( preg_match($reg, $contentHtml, $matchs) ){
		$videosData = json_decode($matchs[1], true);

        $thestr .=<<<EOF
<link rel="stylesheet" href="http://ent.sina.com.cn/css/470/20120928/style_videolist.css" charset="GBK" />
<div class="artical-player-wrap" style="display:block;">
    <div class="a-p-hd">
        <div id="J_Article_Player">
            视频加载中，请稍候...
        </div>
        <div class="a-p-info">
            <label class="fl" suda-uatrack="key=videoq&value=autoplay" style="display:none;">
                <input type="checkbox" checked id="J_Video_Autoplay" />
                自动播放
            </label>
            <span id="J_Video_Source"> </span>
        </div>
    </div>
    <div class="a-p-bd a-p-bd-b" id="J_Play_List_Wrap" style="display:none;">
        <div class="a-p-slide">
            <div class="a-p-s-list clearfix" id="J_Play_List">
EOF;


		foreach($videosData['videos'] as $key=>$videoInfo){
			$thestr .=<<<EOF
				<div class="a-p-s-item J_Play_Item" play-data="{$videoInfo['videoid']}-{$PID}-{$videoInfo['ad']}" url-data="{$videoInfo['url']}" title-data="{$videoInfo['title']}" source-data="{$videoInfo['source']}">
					<a href="javascript:;" class="a-p-s-img" hidefocus="true" title="{$videoInfo['title']}" >
						<img width="120" height="90" src="{$videoInfo['img']}" alt="{$videoInfo['title']}" />
						<i class="a-p-s-play">play</i>
						<span class="a-p-s-txt">{$videoInfo['title']}</span>
					</a>	
				</div>
EOF;
		}

        $thestr .=<<<EOF1
			</div>
            <a href="javascript:;" class="a-p-s-prev" id="J_Player_Prev">向前</a>
            <a href="javascript:;" class="a-p-s-next" id="J_Player_Next">向后</a>
        </div>
    </div>
    <script type="text/javascript" charset="GBK" src="http://www.sinaimg.cn/ty/sinaui/scrollpic/scrollpic2012070701.min.js"></script>
    <!--script type="text/javascript" charset="GBK" src="http://ent.sina.com.cn/js/470/20120928/videolist.js"></script-->
	<script type="text/javascript" charset="GBK" src="http://news.sina.com.cn/js/792/2015-03-13/149/videolist-2.js"></script>
    <script type="text/javascript">
        /*自动播放1*/
        var AUTOPLAY = 1;
        /*连播1*/
		var CONTIPLAY = 1;
		/*处理自动播放选项和cookie*/
        (function() {
            var Tool = CommonTool;
            var chk = Tool.byId('J_Video_Autoplay');
            var ua = navigator.userAgent.toLowerCase();
            var isIOS = /\((iPhone|iPad|iPod)/i.test(ua);
            if (isIOS) {
                console.log(chk.parentNode.style.display);
                chk.parentNode.style.display = 'none';
                return;
            }
            chk.parentNode.style.display = '';
            var clickCookie = function() {
                Tool.bindEvent(chk, 'change',
                function() {
                    var chked = chk.checked;
                    Tool.writeCookie('ArtiVAuto', (chked ? 1 : 0), 24 * 365 * 10, '/', '.sina.com.cn');
                });
            }
            var byCookie = function() {
                var coo = Tool.readCookie('ArtiVAuto');
                if (coo) {
                    if (parseInt(coo) == 0) {
                        chk.checked = false;
                        AUTOPLAY = 0;
                    }
                }
            };
            clickCookie();
            byCookie();
        })();


        /*获取第一个视频vid*/
        var firstItem = CommonTool.byClass('J_Play_Item', 'J_Play_List')[0];
        var fInfo = firstItem.getAttribute('play-data').split('-');
        var fVid = fInfo[0];
        var fPid = fInfo[1];

        var sinaBokePlayerConfig_o = {
            container: "J_Article_Player", //Div容器的id
            width: 525,
            height: 430,
            playerWidth: 525, //宽
            playerHeight: 430, //高
            autoLoad: 1, //自动加载
            autoPlay: AUTOPLAY, //自动播放
            as: 1, //广告
            pid: fPid,
            tjAD: 0, //显示擎天柱广告
            tj: 1, //片尾推荐
            continuePlayer: 1, //连续播放
            casualPlay: 1, //任意拖动视频
            head: 0, //播放片头动画
            logo: 0, //显示logo
            share: 0,
			thumbUrl: ""
        };
    </script>


    <!--script src="http://video.sina.com.cn/js/sinaFlashLoad.js" charset="utf-8" type="text/javascript" ></script -->
	<script src="http://sjs2.sinajs.cn/video/sinaplayer/js/page/player.js"></script>

<script type="text/javascript">
        (function() {
            var toggle = function(id, hide) {
                var e = CommonTool.byId(id);
                var par = e.parentNode;
                if (hide) {
                    CommonTool.addClass(par, e.className + '_disabled');
                } else {
                    CommonTool.removeClass(par, e.className + '_disabled');
                }
            }
            var scroll = new ScrollPic();
            scroll.scrollContId = "J_Play_List"; //内容容器ID
            scroll.arrLeftId = "J_Player_Prev"; //左箭头ID
            scroll.arrRightId = "J_Player_Next"; //右箭头ID
            scroll.listEvent = "onclick"; //切换事件
            scroll.frameWidth = 532; //显示框宽度 **显示框宽度必须是翻页宽度的倍数
            scroll.pageWidth = 133 * 3; //翻页宽度
            scroll.upright = false; //垂直滚动
            scroll.speed = 10; //移动速度(单位毫秒，越小越快)
            scroll.space = 15; //每次移动像素(单位px，越大越快)
            scroll.autoPlay = false; //自动播放
            scroll.autoPlayTime = 5; //自动播放间隔时间(秒)
            scroll.circularly = false;
            scroll._move = scroll.move;
            scroll.move = function(num, quick) {
                scroll._move(num, quick);
                toggle(scroll.arrRightId, scroll.eof);
                toggle(scroll.arrLeftId, scroll.bof);
            };
            scroll.initialize(); //初始化
            toggle(scroll.arrLeftId, scroll.bof);
        })();
    </script>

    <script type="text/javascript">
        var VideoList1 = new ArticalVideoList('J_Play_List', {
            index: 0,
            autoPlay: AUTOPLAY,
            contiPlay: CONTIPLAY,
            itemClass: 'J_Play_Item'
        });
        VideoList1.init();
        function playCompleted(tag) {
            VideoList1.playNext();
        };
    </script>

	<script>
	var flashConfig = {
		url: "",	// flash播放器地址,
		container : "J_Article_Player",
		id: "myMovie",
		width: 525,
		height: 430,
			params : {
			 allowNetworking : "all",
			 allowScriptAccess : "always",
			 wmode : "opaque",
			 allowFullScreen : "true",
			 quality : "high"
			},
			attributes: {},
			flashvars: {
				autoPlay: 1,  //是否自动播放
				loop: 0,
				autoLoad: 1,
				thumbUrl: 'http://p1.v.iask.com/0/267/137089255_2.jpg',
				tj: 1,
				as: 1
			},
			h5attr: {
				autoPlay: false,  //是否自动播放
				controls: true, //是否显示控制条
				loop: false,
				poster: 'http://p1.v.iask.com/0/267/137089255_2.jpg', //视频加载前欲加载的图片地址，即播放器一开始显示的截图
				preload: 'auto'
			}
	  };

		var videoList = [
EOF1;

			foreach($videosData['videos'] as $key=>$videoInfo){
				$thestr .= "{";
				$thestr .= "video_id: {$videoInfo['videoid']},";
				$thestr .= "pid:'1',";
				$thestr .= "url: '{$videoInfo['url']}',";
				$thestr .= "title:'{$videoInfo['title']}'";
				$thestr .= "},";
			}
	$thestr .=<<<EOF2

		]
	
	</script>
	
	<script>

	(function($){
	var Play = {
		init: function(flashConfig, videoList){
			this.flashConfig = flashConfig;
			this.videoList = videoList;
			this.playVideo = playVideo;
			this.prev = this.current = 0;
			this.length = this.videoList.length;
			this.contNode = $("#J_Video_Autoplay");
			this.titleNode = $("#J_Video_Source");
			this.playListNode = $("#J_Play_List .J_Play_Item");

			this.initPlayer();
			this.bind();
		},
		bind: function(){
			var _this = this;
			$("#J_Play_List").on("click", ".J_Play_Item a", function(e){
				e.preventDefault();
				_this.playCurrent($(this));
			});
		},

		initPlayer: function(){
			var _this = this;
			this.player = this.playVideo(this.flashConfig);
			this.player.init(this.videoList[this.prev]);
			this.player.on("playCompleted", function(){
				_this.playNext();
			});
			this.playListNode.eq(0).addClass("selected");
			this.titleNode.html(this.videoList[0].title);
		},
EOF2;

$thestr .='
		playCurrent: function($this){
			this.prev = this.current;
			this.current = $this.parents(".J_Play_Item").index();
			this.play(this.prev, this.current);
		},
';

$thestr .=<<<EOF2
		playNext: function(){
			if(!this.contNode[0].checked){
				return;
			}
			this.prev = this.current;
			if(this.current >= this.length - 1){
				return;
			}
			this.current++;
			this.play(this.prev, this.current);
		},
		play: function(prev, current){
			this.player.playVideo(this.videoList[current]);
			this.titleNode.html(this.videoList[current].title);
			this.playListNode.eq(prev).removeClass("selected");
			this.playListNode.eq(current).addClass("selected");
		}
	}
	Play.init(flashConfig, videoList);
})(jQuery);

</script>
</div>
EOF2;

	return $thestr;
	}

	return false;
}

private function f_article_video_play($contentHtml){
	if(!$contentHtml){
		return false;
	}
	$reg = '/<\!--mce-plugin-videoList\[(.+?)\]mce-plugin-videoList-->/s';

	if( preg_match($reg, $contentHtml, $matchs) ){
		$videosData = json_decode($matchs[1], true);

		$PID = 1503;
		$PURL = 'http://fashion.sina.com.cn';

		$thestr .=<<<EOF
			<link rel="stylesheet" href="http://ent.sina.com.cn/css/470/20120928/style_videolist.css" charset="GBK" />
			<div class="artical-player-wrap" style="display:block;">
			    <div class="a-p-hd">
			        <div id="J_Article_Player">
			            视频加载中，请稍候...
			        </div>
			        <div class="a-p-info">
			            <label class="fl" suda-uatrack="key=videoq&value=autoplay" style="display:none;">
			                <input type="checkbox" checked id="J_Video_Autoplay" />
			                自动播放
			            </label>
			            <span id="J_Video_Source"> </span>
			        </div>
			    </div>
			    <div class="a-p-bd a-p-bd-b" id="J_Play_List_Wrap" style="display:none;">
			        <div class="a-p-slide">
			            <div class="a-p-s-list clearfix" id="J_Play_List">
EOF;

				foreach($videosData['videos'] as $key=>$videoInfo){
					if($key == 0){ 
						if(empty($videoInfo['img'])){
							$firstPic = $this->getVideoScreenshot($videoInfo['vid'], 2); 
						}else{
							$firstPic = $videoInfo['img'];	
						}
					}
					if(empty($videoInfo['img'])){
						$videoInfo['img'] = $this->getVideoScreenshot($videoInfo['vid']);
					}else{
						//$videoInfo['img'] = $api->file($videoInfo['img'])->transform(['cropThumbnail'=> ['width'=>120, 'height'=>90]])->url();
					}

$thestr .=<<<EOF
			<div class="a-p-s-item J_Play_Item" play-data="{$videoInfo['vid']}-{$PID}-{$videoInfo['ad']}" url-data="{$videoInfo['url']}" title-data="{$videoInfo['title']}" source-data="{$videoInfo['source']}">
				<a href="javascript:;" class="a-p-s-img" hidefocus="true" title="{$videoInfo['title']}" >
					<img width="120" height="90" src="{$videoInfo['img']}" alt="{$videoInfo['title']}" />
					<i class="a-p-s-play">play</i>
					<span class="a-p-s-txt">{$videoInfo['title']}</span>
				</a>	
			</div>
EOF;
				}

$thestr .=<<<EOF
</div>
            <a href="javascript:;" class="a-p-s-prev" id="J_Player_Prev">向前</a>
            <a href="javascript:;" class="a-p-s-next" id="J_Player_Next">向后</a>
        </div>
    </div>
    <script type="text/javascript" charset="GBK" src="http://www.sinaimg.cn/ty/sinaui/scrollpic/scrollpic2012070701.min.js"></script>
    <script type="text/javascript" charset="GBK" src="http://ent.sina.com.cn/js/470/20120928/videolist.js"></script>
    <script type="text/javascript">
        /*自动播放1*/
        var AUTOPLAY = 1;
        /*连播1*/
		var CONTIPLAY = 
EOF;

$thestr .= $videosData['listPlay'] ? 'true;' : 'false;';

$thestr .=<<<EOF
        /*处理自动播放选项和cookie*/
        (function() {
            var Tool = CommonTool;
            var chk = Tool.byId('J_Video_Autoplay');
            var ua = navigator.userAgent.toLowerCase();
            var isIOS = /\((iPhone|iPad|iPod)/i.test(ua);
            if (isIOS) {
                console.log(chk.parentNode.style.display);
                chk.parentNode.style.display = 'none';
                return;
            }
            chk.parentNode.style.display = '';
            var clickCookie = function() {
                Tool.bindEvent(chk, 'change',
                function() {
                    var chked = chk.checked;
                    Tool.writeCookie('ArtiVAuto', (chked ? 1 : 0), 24 * 365 * 10, '/', '.sina.com.cn');
                });
            }
            var byCookie = function() {
                var coo = Tool.readCookie('ArtiVAuto');
                if (coo) {
                    if (parseInt(coo) == 0) {
                        chk.checked = false;
                        AUTOPLAY = 0;
                    }
                }
            };
            clickCookie();
            byCookie();
        })();

        /*获取第一个视频vid*/
        var firstItem = CommonTool.byClass('J_Play_Item', 'J_Play_List')[0];
        var fInfo = firstItem.getAttribute('play-data').split('-');
        var fVid = fInfo[0];
        var fPid = fInfo[1];

        var sinaBokePlayerConfig_o = {
            container: "J_Article_Player", //Div容器的id
            width: 525,
            height: 430,
            playerWidth: 525, //宽
            playerHeight: 430, //高
            autoLoad: 1, //自动加载
            autoPlay: AUTOPLAY, //自动播放
            as: 1, //广告
            pid: fPid,
            tjAD: 0, //显示擎天柱广告
            tj: 1, //片尾推荐
            continuePlayer: 1, //连续播放
            casualPlay: 1, //任意拖动视频
            head: 0, //播放片头动画
            logo: 0, //显示logo
            share: 0,
			thumbUrl: "{{$firstPic}}"
        };
        window.__onloadFun__ = function() {
            SinaBokePlayer_o.addVars('HTML5Player_controlBar', true);
            SinaBokePlayer_o.addVars('HTML5Player_autoChangeBGColor', false);
            //SinaBokePlayer_o.addVars("vid", fVid);
            //SinaBokePlayer_o.addVars("pid", fPid);
            SinaBokePlayer_o.showFlashPlayer();

        };
    </script>

    <script src="http://video.sina.com.cn/js/sinaFlashLoad.js" charset="utf-8" type="text/javascript">
    </script>


    <script type="text/javascript">
        (function() {
            var toggle = function(id, hide) {
                var e = CommonTool.byId(id);
                var par = e.parentNode;
                if (hide) {
                    CommonTool.addClass(par, e.className + '_disabled');
                } else {
                    CommonTool.removeClass(par, e.className + '_disabled');
                }
            }
            var scroll = new ScrollPic();
            scroll.scrollContId = "J_Play_List"; //内容容器ID
            scroll.arrLeftId = "J_Player_Prev"; //左箭头ID
            scroll.arrRightId = "J_Player_Next"; //右箭头ID
            scroll.listEvent = "onclick"; //切换事件
            scroll.frameWidth = 532; //显示框宽度 **显示框宽度必须是翻页宽度的倍数
            scroll.pageWidth = 133 * 3; //翻页宽度
            scroll.upright = false; //垂直滚动
            scroll.speed = 10; //移动速度(单位毫秒，越小越快)
            scroll.space = 15; //每次移动像素(单位px，越大越快)
            scroll.autoPlay = false; //自动播放
            scroll.autoPlayTime = 5; //自动播放间隔时间(秒)
            scroll.circularly = false;
            scroll._move = scroll.move;
            scroll.move = function(num, quick) {
                scroll._move(num, quick);
                toggle(scroll.arrRightId, scroll.eof);
                toggle(scroll.arrLeftId, scroll.bof);
            };
            scroll.initialize(); //初始化
            toggle(scroll.arrLeftId, scroll.bof);
        })();
    </script>

    <script type="text/javascript">
        var VideoList1 = new ArticalVideoList('J_Play_List', {
            index: 0,
            autoPlay: AUTOPLAY,
            contiPlay: CONTIPLAY,
            itemClass: 'J_Play_Item'
        });
        VideoList1.init();
        function playCompleted(tag) {
            VideoList1.playNext();
        };
    </script>
</div>
EOF;
		$contentHtml = str_replace($matchs[0], $thestr, $contentHtml);
		
	}
	return $contentHtml;
}

	private function getVideoScreenshot($vid, $type=1){
		if($vid >= 6251043){
			$imd5 = md5($vid);
			$apath = $this->twHash(substr($imd5, 0, 16), 1024);
			$bpath = $this->twHash(substr($imd5, 16), 1024);
			$urlPath = 'http://p.v.iask.com/'.$apath.'/'.$bpath.'/';
		}else{
			$pidone = $vid % 10;
			$pid = $vid % 100;
			$urlPath =  'http://www.sinaimg.cn/kusou/v/'.$pidone.'/'.$pid.'/'.$pid.'/';
		}
		return $urlPath.$vid.'_'.$type.'.jpg';
	}

	private function twHash($str, $size) {
		$b = array(0,0,0,0);
		for ( $i=0; $i<strlen($str); $i++ ) {
			$b[$i%4] ^= ord( substr( $str, $i, 1 ) );
		}
		$binstr = '';
		for ( $i = 0 ; $i < 4 ; $i++ ) {
			$tempbin = sprintf( "%b", $b[3-$i] );
			$temp0 = "";
			for ( $j=0 ; $j < 8-strlen($tempbin) ; $j++ ) {
				$temp0 .= "0";
			}
			$tempbin = $temp0.$tempbin;
			$binstr .= $tempbin;
		}
		$n = bindec($binstr);
		return $n%$size;
	}
	//========================= CMS方式处理攻略正文(PC用)结束 ======================================

	//PC/H5 处理图片方法
	/**
	 * 上传图片
	 * @param unknown $uid
	 * @param unknown $files
	 */
	public function upload_img($uid, $files) {
		if (!$uid || empty($files)) {
			return false;
		}
		$this->load->model('qa_image_model');

		//============== 增加图片上传数量限制开始 ==============
		//拼装cache_key
		$forbidKey = 'glapp:users:uploadimg:wap:' . ENVIRONMENT . $uid;

		if($this->cache->redis->exists($forbidKey)){
			$check_num = $this->cache->redis->get($forbidKey);

			//判断
			if($check_num >= 200){
				//上传图片超过200张
				exit('上传图片过多，一天最多上传200张图片');
			}

			$this->cache->redis->incr($forbidKey);
		}else{
			//获取当前时间戳
			$exprietime = strtotime(date('Y-m-d',strtotime('+1 day')));

			//设定
			$this->cache->redis->incr($forbidKey);
			$this->cache->redis->expireAt($forbidKey, $exprietime);
		}
		//============== 增加图片上传数量限制结束 ==============
		foreach ($files as $id => $file) {
			$id = $uid . time();
			$_arr = $this->do_upload_img($id, $file);

			if ($_arr['code']) {
				continue;
			}

			$update_data = array(
					'url' =>  $_arr['data'],
			);

			$_arr = getimagesize($file['tmp_name']);
			$update_data += array(
					'width' => $_arr[0],
					'height' => $_arr[1],
			);
		}

		return $update_data;
	}

	public function do_upload_img($id, $file) {
		$return = array(
				'code' => 0,
				'msg' => '',
				'data' => ''
		);
		try {
			//文件类型
			$uptypes = array(
					'image/jpg' => 'jpg',
					'image/png' => 'png',
					'image/gif' => 'gif',
					'image/jpeg' => 'jpeg',
					'image/bmp' => 'bmp',
					'image/x-png' => 'png', //IE8兼容
					'image/pjpeg' => 'jpg', //IE8兼容
			);

			$max_file_size = 2000000;   //文件大小限制1M
			if( (empty($file) || !is_uploaded_file($file['tmp_name'])) ){
				throw new Exception('没有上传图片', -1);
			}elseif(($file['error'])){
				throw new Exception('图片上传错误，请重新上传', -2);
			}elseif(!($uptypes[$file['type']]) ){
				throw new Exception('上传文件不是图片类型', -3);
			}elseif( (@filesize($file['tmp_name']) > $max_file_size)){
				throw new Exception('上传文件大小超过限制', -4);
			}

			// $this->load->library('storeage');
			$pic_path = 'glapp/qa/' . date('Ym') . '/';
			$content1 = @file_get_contents($file['tmp_name']);
			$content1 = $this->_save_resize($content1,630,630);
			$picfile1 = $pic_path . $id . '.' . $uptypes[$file['type']];
			// $ress1 = $this->storeage->upload( $content1 , $picfile1 , $file['type'] );
			// 
			// if(!$ress1){
			// 	throw new Exception('s1_err', -10);
			// }
			try {
				$CI = get_instance();
				$CI->load->config('oss_config', true);
				$config = $CI->config->item('oss_config');
				$this->load->library('OSS/oss', $config);
	
				$this->oss->putObject($this->oss->getBucketName(), $picfile1, $content1);
				// $this->oss->putObject($this->oss->getBucketName(), $picfile3, $content3);
			} catch (OssException $e) {
				throw new Exception('s1_err', -10);
			}
			

			$return['data'] = NEW_IMG_PREFIX .  $picfile1;

		} catch (Exception $e) {
			header('Content-Type:text/html;charset=utf-8');
			$return['code'] = $e->getCode();
			$return['msg'] = $e->getMessage();
			echo '上传错误，错误原因:<br/>';
			echo $return['msg'];
			exit;
		}
		return $return;
	}

	//图片缩减函数
	private function _save_resize($picfile, $maxwidth='800', $maxheight='800'){
		if( empty($picfile)) return false;
		$def_image = @imagecreatefromstring($picfile);
		$def_width =imagesx($def_image);
		$def_height=imagesy($def_image);

		if($def_width > $maxwidth || $def_height > $maxheight){
	      	//2. 计算压缩后的尺寸
	      	if(($maxwidth/$def_width)<($maxheight/$def_height)){
	            $w=$maxwidth;//新图片的宽度
	            $h=($maxwidth/$def_width)*$def_height;//新图片的高度
	      	}else{
	            $h=$maxheight;//新图片的宽度
	            $w=($maxheight/$def_height)*$def_width;//新图片的高度
	      	}
		}else{
			$w = $def_width;
			$h = $def_height;
		}

		$newimg = imagecreatetruecolor($w,$h);
		imagecopyresized ($newimg, $def_image,0,0,0,0,$w, $h, $def_width, $def_height);
		imagedestroy($def_image);
		ob_start();
		imagejpeg($newimg);
		$content = ob_get_contents();
		ob_end_clean();
		imagedestroy($newimg);
		return $content;
	}

	//转换问题提问格式，而后匹配出来(图片匹配成[!--IMG_id--])
	public function changeImgStr2($str,$dataArrs)
	{
		//设定域名前缀
		// $domin = 'http://store.games.sina.com.cn/';

		//获得问题详情
		$content = $str;

		$content = preg_replace('/\\r\\n/',"",$content);//替换换行
		$content = htmlspecialchars_decode($content);

		//设定匹配
		$pattern = '/<\s*img\s+[^>]*?src\s*=\s*\\\\(\'|\")(.*?)\\\\\\1[^>]*?\/?\s*>/i';

		//匹配内容，放入数组
		preg_match_all($pattern,$content,$result);

		//判断图片数量
		if(count($result[2]) > 10){
			return false;
		}

		//初始化该内容图片id数组
		$img_arr = array();

		//数组遍历
		foreach($result[2] as $k=>$v){ //遍历每一个链接地址
			// $domins = '/http:\/\/store.games.sina.com.cn\//';
			// $whereArr['url'] = preg_replace($domins, '', $v); //将地址替换成空，然后作为搜索条件
			
			$whereArr['uid'] = $this->user_id;
			$whereArr['url'] = $v; 
			
			//搜索当前库中是否有该图片信息，如果有则直接获取ID进行替换
			$res_img_data = $this->findImgData($whereArr);

			//判断库中是否有图片信息
			if($res_img_data['id']){
				//库中有图片信息
				$content = str_replace($result[0][$k], "[!--IMG_".$res_img_data['id']."--]", $content);

				$img_arr[] = $res_img_data['id'];
			}else{
				//库中没有图片信息
				$res_w_h = getimagesize($v); //获取图片宽高

				//调用方法执行添加
				$this->load->model('qa_image_model');

				$res_id = $this->qa_image_model->insert($dataArrs['uid'], $dataArrs['type'], $dataArrs['mark'], $whereArr['url'], intval($res_w_h[0]), intval($res_w_h[1]));

				$img_arr[] = $res_id;

				//将图片数据匹配成固定样式
				$content = str_replace($result[0][$k], "[!--IMG_".$res_id."--]", $content);
			}
		}

		//处理图片(删除的自动干掉)
		if(is_array($img_arr) && count($img_arr) > 0){
			//拼装sql
			$sql_img = "update gl_question_answer_image set status=0 where uid='{$dataArrs['uid']}' and type='{$dataArrs['type']}' and mark='{$dataArrs['mark']}' and id not in (" . implode(',', $img_arr) . ") limit 10";
		}else{
			$sql_img = "update gl_question_answer_image set status=0 where uid='{$dataArrs['uid']}' and type='{$dataArrs['type']}' and mark='{$dataArrs['mark']}' limit 15";
		}
		$this->db->query($sql_img);

		//这里替换编辑器里的标签
		$content_new = str_replace( array('&nbsp;' ), array(), $content );//$content_new = str_replace( array('&nbsp;', ' ' ), array(), $content );
		$content_new = preg_replace('/<br\/>/',"\n",$content_new);//替换换行
		$content_new = preg_replace('/<br \/><\/p>/',"",$content_new);//替换换行
		$content_new = preg_replace('/<\/p>/',"\n",$content_new);//替换换行
		$content_new = strip_tags($content_new); //彻底去除其他所有html标签，防止出现页面布局问题
		//$content_new = preg_replace("/<([a-zA-Z]+[^>]*>/","",$content_new);//前半截标签
		//$content_new = preg_replace('/<\/([a-zA-Z]+[^>]*>/',"",$content_new);//后半截标签

		return array('content'=>$content_new);
	}

	public function findImgData($whereArr)
	{
		$cache_key_pre = "glapp:" . ENVIRONMENT . ":findImgData:wap:";
		$cache_key = sha1($cache_key_pre . json_encode($whereArr));
		$resData = $this->cache->redis->get($cache_key);
		$resData && $resData = json_decode($resData, 1);
		$resData = false; //必须入库查，才有确定性
		if($resData === false){
			$whereStr = '';
			if(is_array($whereArr)){
				foreach($whereArr as $k=>$v)
				{
					$whereStr .=" and ".$k." = '".$v."'";
				}
			}

			$resData = $this->db->query_read("select * "." from gl_question_answer_image where 1" .$whereStr);
			$resData = $resData->row_array();

			//保存
			$this->cache->redis->set($cache_key, json_encode($resData));
		}

		return $resData;
	}

	/**
	 * 内容转成wap前端需要的格式
	 */
	public function convert_content_to_wapfrontend($content, $length = 0, $is_list = 1, $decode = 1) {
		$content = trim($content);
		$pattern = array(
				'/\[!--IMG_\d+--\]/',
				"/\n+/",
				'/<a\s+.*?>(.*?)<\/a>/'
		);
		$replace = array(
				'',
				'<br/>',
				'$1'
		);

		$is_list && $content = preg_replace($pattern, $replace, $content);
		$decode && $content = htmlspecialchars_decode($content);
		$length > 0 && $content = mb_substr($content, 0, $length);
		return $content;
	}

	public function get_question_info($uid, $qid, $status = array(0,1)) {
		$this->load->model('user_model');
		$this->load->model('question_model');
		$this->load->model('answer_model');
		$this->load->model('qa_image_model');
		$this->load->model('qa_model');
		$this->load->model('question_content_model');
		$this->load->model('answer_content_model');
		$this->load->model('game_model');
		$this->load->model('follow_model');
		$this->load->model('push_message_model');
		$this->load->model('exp_model');

		$uid = $this->global_func->filter_int($uid);
		$qid = $this->global_func->filter_int($qid);

		$question_info = $this->question_model->get_info($qid, $status);
		if (empty($question_info)) {
			return array();
		}

		$image_list = $this->qa_image_model->get_list(1, $qid);

		$images_frontend = array();
		foreach ($image_list as $v) {
			$images_frontend[] = array(
					'img_id' => (string)$this->qa_image_model->convert_id_to_frontend($v['id']),
					'url' => gl_img_url($v['url']),
					'width' => (int)$v['width'],
					'height' => (int)$v['height'],
					'desc' => '',
			);
		}


		if ($question_info['gid'] == 2031) {
			$game_info_frontend = array(
					'absId' => '2031',
					'abstitle' => $question_info['gname'],
					'absImage' => '',
			);
		} else {
			$game_info = $this->game_model->get_cms_game_info($question_info['gid']);
			if($game_info[0]['title'] == ''){
				if($this->platform == 'ios'){
					$game_info2 = $this->game_model->get_game_row($question_info['gid'],'android');
					if($game_info2['ios_id']){
						$game_info = $this->game_model->get_cms_info($game_info2['android_id']);
					}
				}
				if($this->platform == 'android'){
					$game_info1 = $this->game_model->get_game_row($question_info['gid'],'ios');
					if($game_info1['ios_id']){
						$game_info = $this->game_model->get_cms_info($game_info1['ios_id']);
					}
				}
			}
			$game_info = $game_info[0];
			$game_info_frontend = array(
					'absId' => $question_info['gid'],
					'abstitle' => $game_info['title'],
					'absImage' => $game_info['logo']
			);
		}

		$user_info = $this->user_model->getUserInfoById($question_info['uid']);
		$content =  $this->question_content_model->get_content($question_info['qid']);
		$return = array(
				'absId' => $question_info['qid'],
				'updateType' => $question_info['update_time'] == $question_info['create_time'] ? 0 : 1,	// 0发布     1编辑
				'updateTime' => date('Y-m-d H:i:s', $question_info['update_time']),
				'createTime' => date('Y-m-d H:i:s', $question_info['create_time']),
				'attentioned' => (boolean)($this->follow_model->is_follow($uid, 4, $question_info['qid'])),
				'content' => $this->qa_model->convert_content_to_frontend($content, 0, 0),
				'original_content' => $this->qa_model->convert_content_to_frontend($content, 0, 0, 0),
				'attribute' => array(
						'images' => $images_frontend,
				),
				'answerCount' => (int)$question_info['normal_answer_count'],
				'attentionCount' => (int)($question_info['follow_count'] + $question_info['virtual_follow_count']),
// 				'shareUrl' => base_url() . '/share/detail?qid=' . $qid,
		        // 'shareUrl' => 'http://www.wan68.com/question/info/' . $qid,
		        'shareUrl' => base_url() . 'question/info/' . $qid,
				'shareContent' => $this->qa_model->convert_content_to_frontend($content),
				'inviteContent' => $this->qa_model->convert_content_to_frontend($content, 50),
				'gameInfo' => $game_info_frontend,
				'author' => array(
						'guid' => (string)$user_info['uid'],
						'nickName' => (string)$user_info['nickname'],
						'headImg' => (string)$user_info['avatar'],
						'uLevel' => (int)$user_info['level'],
						'medalLevel' => (int)$user_info['rank'],
				),
		);

		if ($uid) {
			// 记录用户最后一次看答案评论的时间 for 消息系统
			$this->load->model('push_message_model');
			$_push_type = 1;	// 问题
			$_push_flag = 1;	// 新增答案
			$_push_mark = $qid;
			$this->push_message_model->user_timeline($uid, $_push_type, $_push_flag, $_push_mark);
		}


		return $return;
	}

	//去掉二维码方法
	public function clear_qr_code($content){
		if(!$content){
			return $content;
		}

		$content = $this->filter_sina_flag($content);

		//公共
		$patterns1 = '/<p>.*?97973手游网.*?<\/p>/';
		$patterns5 = '/<p>.*?97973 手游网.*?<\/p>/';
		$patterns2 = '/<p>.*?全民手游攻略.*?<\/p>/';
		$patterns3 = '/<div class="img_wrapper">.*?扫描二维码[\s\S]*?<\/div>/';
		$patterns3 = '/<div class="img_wrapper">.*?扫描二维码(\n|\r\n){0,2}.*?<\/div>/';
		$patterns4 = '/<p>.*?加入群.*?最新消息全在这里.*?<\/p>/';
		$patterns6 = '/<strong><span style="color: #0000ff;">更多攻略尽在.*?二维码.*?193936051<\/strong>/';

		$patt = array(
			$patterns1,
			$patterns2,
			$patterns3,
			$patterns4,
			$patterns5,
			$patterns6,
		);

		$content = preg_replace($patt, '', $content);

		//单体
		$patterns_str = array();
		$patterns_str[] = '<p>　　暗黑血统手游攻略下载：</p>';
		$patterns_str[] = '<div class="img_wrapper"><div class=\'popimg\' ><img src=\'http://n.sinaimg.cn/97973/transform/20160126/umIz-fxnuvxh5185855.png\'></div></div>';
		$patterns_str[] = '<p>　　艾尔战记官方攻略APP下载地址：</p>
<div class="img_wrapper"><div class=\'popimg\' ><img src=\'http://n.sinaimg.cn/97973/transform/20160128/Xf7d-fxnzanm3745749.png\'></div><span class="img_descr">扫码二维码关注艾尔战记APP</span></div>
';

		$patterns_str[] = <<<EOF
<p>　　艾尔战记官方攻略APP下载地址：</p>
<div class="img_wrapper"><div class='popimg' ><img src='http://n.sinaimg.cn/97973/transform/20160128/Xf7d-fxnzanm3745749.png'></div><span class="img_descr">扫码二维码关注艾尔战记APP</span></div>
EOF;


		$patterns_str[] = <<<EOF
<p>　　<strong><span style="color: #0000ff;">更多攻略尽在</span><span style="color: #0000ff;">梦幻西游手游官方攻略：</span><span style="color: #ff0000;"><a href="http://www.wan68.com/download/app_mhxy" target=""><span style="color: #ff0000;">点击下载</span></a></span></strong></p>
<div class="img_wrapper"><div class='popimg' ><img src='http://n.sinaimg.cn/97973/transform/20151221/P6PL-fxmueaa3661246.png'></div></div>
<div class="img_wrapper"><span class="img_descr">梦幻西游手游官方攻略二维码</span></div>
<p>　　<span style="color: #3366ff;"><strong><span style="color: #ff0000;">点击群号·一键加入</span>【97973梦幻官方群】：<a href="http://jq.qq.com/?_wv=1027&amp;k=2FzjSh4" target="">193936051</a></strong></span></p>
EOF;


		$patterns_str[] = <<<EOF
<p>　　<strong>更多攻略尽在诛仙手游官方攻略APP：<span style="color: #ff0000;"><a style="color: #ff0000;" href="http://www.wan68.com/download/app_zx/2">点击下载</a></span></strong></p>
<div class="img_wrapper"><div class='popimg' ><img src='http://n.sinaimg.cn/97973/transform/20160801/qxRr-fxupmws1488291.png'></div><span class="img_descr">诛仙手游官方攻略二维码</span></div>
EOF;

		$patterns_str[] = <<<EOF
<p>　　<strong>更多攻略尽在诛仙手游官方攻略APP：<span style="color: #ff0000;"><a style="color: #ff0000;" href="http://www.wan68.com/download/app_zx/2">点击下载</a></span></strong></p>
<div class="img_wrapper"><img src="http://n.sinaimg.cn/97973/transform/20160801/qxRr-fxupmws1488291.png" alt="诛仙手游

官方攻略二维码" data-link=""><span class="img_descr">诛仙手游官方攻略二维码</span></div>
EOF;

		$patterns_str[] = <<<EOF
<p><strong>　<span style="color: #3366ff;">　<span style="color: #0000ff;">97973京门风月官方讨论群，欢迎大家的加入。群号：</span><span style="color: #ff00ff;">460178521</span>。</span></strong></p>
<p>　　<span style="color: #3366ff;"><strong>京门风月手游专用攻略APP下载地址：</strong></span><strong><a href="http://www.wan68.com/download/app_jmfy/2" target=""><span style="color: #ff0000;"><span style="color: #ff0000;">点击下载</span>&gt;&gt;&gt;</span></a></strong></p>
<div class="img_wrapper"><div class='popimg' ><img src='http://n.sinaimg.cn/97973/transform/20160628/Edmc-fxtniax8184134.png'></div></div>
EOF;

		$patterns_str[] = <<<EOF
<p>　　灵域官方攻略APP下载地址：</p>
<div class="img_wrapper"><div class='popimg' ><img src='http://n.sinaimg.cn/97973/transform/20160602/Zi9L-fxsrkwk3429066.png'></div><span class="img_descr">灵域官方攻略二维码</span></div>
<p>　　同时可以使用微信扫下放二维码，关注灵域手游官方公众号，更多活动敬请期待：</p>
<div class="img_wrapper"><div class='popimg' ><img src='http://n.sinaimg.cn/97973/transform/20160714/P_Ac-fxuapvw1982032.jpg'></div><span class="img_descr">灵域官方公众号二维码</span></div>
EOF;

		$patterns_str[] = <<<EOF
<p>　　<strong><span style="color: #0000ff;"><a href="http://www.wan68.com/download/app_qnyh" target="" title="点击下载：" style="color: #0000ff;">点击下载：</a></span></strong></p>
<div class="img_wrapper"><div class='popimg' ><img src='http://n.sinaimg.cn/97973/transform/20160810/V6yN-fxutfpf1725308.png'></div><span class="img_descr">倩女幽魂</span></div>
<p>　　<span style="color: #0000ff;"><strong>也可以加群：</strong></span>366697806来一起交流。</p>
EOF;

		$patterns_str[] = <<<EOF
<p>　　<a href="http://www.wan68.com/download/app_sdtx" target="" title="点击下载"><span style="color: #ff0000;"><strong>点击下载：</strong></span></a></p>
<div class="img_wrapper"><div class='popimg' ><img src='http://n.sinaimg.cn/97973/transform/20160810/aWzA-fxutfpk5140512.png'></div><span class="img_descr">全民手游攻略for闪电突袭</span></div>
EOF;

		$patterns_str[] = <<<EOF
<div class="img_wrapper"><div class='popimg' ><img src='http://n.sinaimg.cn/97973/transform/20160527/S3pN-fxsrkwk3155402.png'></div><span class="img_descr">我叫MT3二维码</span></div>
EOF;

		$patterns_str[] = <<<EOF
<p>　　<span style="color: #ff0000;"><strong><span style="color: #0000ff;">97973问道手游官方群：</span>514465643&nbsp;</strong></span></p>
<p>　　<span style="color: #3366ff;"><strong>问道手游专用攻略APP下载地址：</strong></span><strong><a href="http://www.wan68.com/download/app_wd/2" target=""><span style="color: #ff0000;"><span style="color: #ff0000;">点击下载</span>&gt;&gt;&gt;</span></a></strong></p>
<div class="img_wrapper"><div class='popimg' ><img src='http://n.sinaimg.cn/97973/transform/20160219/uNpd-fxprucs6256383.png'></div></div>
EOF;

		$patterns_str[] = <<<EOF
<div class="img_wrapper"><div class='popimg' ><img src='http://n.sinaimg.cn/97973/transform/20160529/1aak-fxsqxxu4621288.png'></div><span class="img_descr">剑侠情缘手游攻略APP</span></div>
</div>
EOF;

		$patterns_str[] = <<<EOF
<p>　　列王的纷争全民攻略下载地址：<span style="color: #ff0000;"><a href="http://www.wan68.com/download/app_cok" target=""><span style="color: #ff0000;">点击下载</span></a></span></p>
<div class="img_wrapper"><div class='popimg' ><img src='http://n.sinaimg.cn/97973/transform/20160304/cYtw-fxqaffy3620923.png'></div><span class="img_descr"></span></div>
<p>　　<span style="color: #ff0000;"><strong>关于列王的纷争全民攻略</strong></span></p>
<p>　　<span>列王的纷争全民攻略致力于为广大玩家提供最新、最全、最详尽的游戏攻略，在这里你可以查询到任何你想要了解的内容。问答社区服务为玩家们提供了相互交流并相互解惑的平台，让玩家们的疑问尽快得到解决。</span></p>
EOF;

		$patterns_str[] = <<<EOF
<p>　　<strong><span style="color: #0000ff;">更多攻略尽在</span><span style="color: #0000ff;">梦幻西游手游官方攻略：</span><span style="color: #ff0000;"><a href="http://www.wan68.com/download/app_mhxy" target=""><span style="color: #ff0000;">点击下载</span></a></span></strong></p>
<div class="img_wrapper"><div class='popimg' ><img src='http://n.sinaimg.cn/97973/transform/20151221/P6PL-fxmueaa3661246.png'></div></div>
<div class="img_wrapper"><span class="img_descr">梦幻西游手游官方攻略二维码</span></div>
<p>　　<span style="color: #3366ff;"><strong>97973梦幻官方群：</strong></span><span style="color: #ff0000;"><strong>193936051。</strong></span></p>
EOF;

$patterns_str[] = <<<EOF
<p>　　<strong><span style="color: #0000ff;">更多攻略尽在</span><span style="color: #0000ff;">梦幻西游手游官方攻略：</span><span style="color: #ff0000;"><a href="http://www.wan68.com/download/app_mhxy" target=""><span style="color: #ff0000;">点击下载</span></a></span></strong></p>
<div class="img_wrapper"><div class='popimg' ><img src='http://n.sinaimg.cn/97973/transform/20151221/P6PL-fxmueaa3661246.png'></div></div>
<div class="img_wrapper"><span class="img_descr">梦幻西游手游官方攻略二维码</span></div>
<p>　　<span style="color: #3366ff;"><strong><span style="color: #ff0000;">点击群号·一键加入</span>[97973梦幻官方群]：<a href="http://jq.qq.com/?_wv=1027&amp;k=2FzjSh4" target="">193936051</a></strong></span></p>
EOF;

		$patterns_str[] = <<<EOF
<div class="img_wrapper"><div class='popimg' ><img src='http://n.sinaimg.cn/97973/transform/20160223/mZ41-fxpsfak1712912.png'></div><span class="img_descr">乱斗无双下载二维码</span></div>
<p>　　《乱斗无双》手游更多详细信息，玩家可以加入官方粉丝群。亦可关注乱斗无双手游官方微博微信，除了能随时查看心得攻略、福利活动，更能领取豪华礼包，在游戏中快人一步。</p>
<div class="img_wrapper"><div class='popimg' ><img src='http://n.sinaimg.cn/97973/transform/20160224/rNu6-fxprucv9822830.jpg'></div><span class="img_descr">扫描关注《乱斗无双》手游官方微信</span></div>
<p>　　游戏礼包：<a href="http://ka.sina.com.cn/20203" target="">http://ka.sina.com.cn/20203</a></p>
<p>　　游戏官网：<a href="http://ld.97973.com/" target="">http://ld.97973.com/</a></p>
<p>　　官方微博：<a href="http://weibo.com/p/1006065829212800/" target="">http://weibo.com/p/1006065829212800/</a></p>
<p>　　玩爱粉丝群：306608566</p>
EOF;

		$patterns_str[] = <<<EOF
<p>　　想知道更多DNF手游资讯吗？欢迎大家下载最新DNF手游助手APP了解最新动态，现在下载还送QB。同时也可以加入97973DNF手游官方群298097620（快满）<a target="" href="http://shang.qq.com/wpa/qunwpa?idkey=2e3d11b07782ea0e444c3205f4f7af666cdf6344dcf1747cf6c44bded777ba1c"><div class='popimg' ><img src='http://n.sinaimg.cn/97973/crawl/20151116/xT8--fxkszhk0290135.png'></div></a> 2群188353469<a target="" href="http://shang.qq.com/wpa/qunwpa?idkey=671a1fa7c63b609f153d55b4083aa974ea4084e122421bf06045a423cbc978a5"><div class='popimg' ><img src='http://n.sinaimg.cn/97973/crawl/20151116/xT8--fxkszhk0290135.png'></div></a></p>
<p>　　点此下载DNF手游助手：<span style="color: #ff0000;"><a href="http://www.wan68.com/download/app_dnf" target=""><span style="color: #ff0000;">传送门</span></a></span></p>
<p>　　或扫描下方二维码进行下载</p>
<div class="img_wrapper"><div class='popimg' ><img src='http://n.sinaimg.cn/97973/transform/20160617/cTVM-fxtfrrf0540853.jpg'></div><span class="img_descr"></span></div>
EOF;

		$patterns_str[] = <<<EOF
<p>　　<span style="color: #ff0000;"><strong>王者荣耀全民攻略下载：</strong></span></p>
<div class="img_wrapper"><div class='popimg' ><img src='http://n.sinaimg.cn/97973/transform/20160314/QcZs-fxqhwtu7725697.png'></div><span class="img_descr"></span></div>
EOF;

		$patterns_str[] = <<<EOF
<div class="img_wrapper"><img src="http://n.sinaimg.cn/transform/20151102/1hGP-fxkhqea2948302.jpg" alt="扫

描二维码下载全民手游攻略" data-link="" data-mcesrc="http://n.sinaimg.cn/transform/20151102/3UbY-

fxkhcfk7529375.png" data-mceselected="1"><span class="img_descr">扫描二维码下载全民手游攻略</span></div>
EOF;

		$patterns_str[] = <<<EOF
<p>　　<strong><span style="line-height: 22.96px;">乖离性百万亚瑟王专用攻略下载地址：</span><span style="color: #ff0000;"><a href="http://www.wan68.com/download/app_glxm/2" target=""><span style="color: #ff0000;">点击下载</span></a> &gt;&gt;&gt;</span></strong></p>

<div class="img_wrapper"><div class='popimg' ><img src='http://n.sinaimg.cn/97973/transform/20160811/V7py-fxuxwyx8507801.png'></div></div>
EOF;

		$patterns_str[] = <<<EOF
<p>　　<span style="color: #000000;">或扫描下方二维码进行下载</span></p>
<div class="img_wrapper"><div class='popimg' ><img src='http://n.sinaimg.cn/97973/20160517/U7P8-fxsenvm0509686.jpg'></div><span class="img_descr">扫我下载战舰少女R手游助手</span></div>
EOF;

		//特例使用字符串操作函数来匹配
		$patterns_str_arr = $patterns_str;
		$content = str_replace($patterns_str_arr, '', $content);

		return $content;
	}

	//2016-10-12按规则去掉二维码等信息的方法
//封装过滤973各标识函数 type为过滤类型 qq,download,qrode,common,tags为类型数组
function filter_sina_flag($content, $tags=array('all') , $type = 'all'){
	if(!$content){
		return $content;
	}

	//初始化待拼装数组
	$tags_tmp = array();
	$tags_allows = array('qq-part', 'download-part', 'qrcode-part', 'common-filter-part');

	//根据tags拼装
	foreach($tags as $vo){
		//判断
		if(in_array($vo, $tags_allows)){
			$tags_tmp[] = $vo;
		}elseif($vo == 'all'){
			$tags_tmp = $tags_allows;
			break;
		}
	}

	if(empty($tags_tmp)){
		exit('wrong tags');
	}else{
		$tags_str = implode("|", $tags_tmp);
	}

	//判断过滤标签类型
	switch($type){
		case "p":
			$patterns = '/<p.*?class=[\'\"].*?(' . $tags_str . ').*?[\'\"]>.*?<\/p>/';
			break;
		case "div":
			$patterns = '/<div.*?class=[\'\"].*?(' . $tags_str . ').*?[\'\"]>.*?<\/div>/';
			break;
		case "span":
			$patterns = '/<span.*?class=[\'\"].*?(' . $tags_str . ').*?[\'\"]>.*?<\/span>/';
			break;
		case "all":
			$patterns = '/<(.*?) class=[\'\"].*?(' . $tags_str . ').*?[\'\"]>.*?<\/\\1>/';
			break;
		default:
			return $content;
			break;
	}

	$patt = array(
		$patterns,
	);

	// $num = preg_match_all($patterns, $content, $res);
	// echo '<pre>';
	// var_dump($num, $res);exit;

	$content = preg_replace($patt, '', $content);
	return $content;
}



	//去掉属性中的二维码
	public function clear_qr_code_in_attr($arr){
		if(!is_array($arr) || empty($arr)){
			return $arr;
		}

		foreach($arr as $k=>$vo){
			if(preg_match('/二维码/', $vo['desc'])){
				unset($arr[$k]);
			}
		}

		return array_values($arr);
	}



}
