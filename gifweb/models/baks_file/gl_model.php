<?php
if (! defined ( 'BASEPATH' )) exit ( 'No direct script access allowed' );

/**
 * @Name	Gl_Model.php
 */
class Gl_model extends MY_Model {

	private $_cache_key_pre = '';
	private $_cache_expire = 600 ;
	protected  $_table = 'gl_games';

	public function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":gl:";
	}
	/**
	 *
	 * 根据游戏id取分类信息
	 *
	 */
	public function get_category_row($gameId,$type = 0)
	{
		$cache_key = $this->_cache_key_pre . "get_category_row:one:$this->platform:$gameId";
		$data = $this->cache->redis->get($cache_key);
		$data && $data = json_decode($data, 1);
		if (!is_array($data)) {
			$conditons['fields']= 'id,cids as absId,game_name as abstitle, ios_id,android_id, url as web_url,ios_type,android_type,attention_count as attentionCount';
			$conditons['where']['id']= array('eq',intval($gameId));
			if($type == 0){
				$conditons['where']['display']= array('eq','1');
			}
			$sql = $this->find($conditons);
			$rs = $this->db->query_read($sql);
			$data = $rs->row_array();
			$this->cache->redis->set($cache_key, json_encode($data), $this->_cache_expire * 2 );
		}
		return $data;
	}
	/**
	 *
	 * 根据游戏id取二级分类信息
	 *
	 */
	public function get_category_list($parentid,$counts = 0)
	{
		$cache_key = $this->_cache_key_pre . "get_category_list";
		$hash_key = "normal:$parentid";
		$data = $this->cache->redis->hGet($cache_key, $hash_key);
		$data && $data = json_decode($data, 1);
    	if (!is_array($data) || $counts>0) {
			$conditons['fields']= 'id,cids as absId,game_name as abstitle,url as absImage,article_count';
			$conditons['where']['parentid']= array('eq',intval($parentid));
			if($counts > 0){
			     $conditons['where']['article_count']= array('>','0');
			}
			$conditons['where']['display']= array('eq','1');
			$conditons['order'] = ' listorder desc ';
			$sql = $this->find($conditons);
			$rs = $this->db->query_read($sql);
			$data = $rs->result_array();
			if($counts == 0){
    			 $this->cache->redis->hSet($cache_key, $hash_key, json_encode($data));
    			 $this->cache->redis->expire($cache_key, $this->_cache_expire);
			}
		}
		return $data;
	}

	//首页聚合页推荐列表
	public function get_juhe_recommend(){
		$cache_key = $this->_cache_key_pre . "get_juhe_recommend";
		$data = $this->cache->redis->get($cache_key);
		$data && $data = json_decode($data, 1);
		if (!is_array($data)) {
			$urls="http://wap.97973.com/glapp/juhe_recommend.d.html";
			$json_data = Util::curl_get_contents($urls);
			$data = json_decode($json_data, true);
			$this->cache->redis->set($cache_key, json_encode($data), $this->_cache_expire * 2 );
		}
		return $data;
	}
	//首页聚合页推荐列表
	public function findCmsGlCount($category){
	    if($category==''){
	        return '';
	        exit;
	    }
		$cache_key = $this->_cache_key_pre . "findCmsGlCount";
		$data = $this->cache->redis->get($cache_key);
		$data && $data = json_decode($data, 1);
		if (!is_array($data)) {
        	$urls="http://wap.97973.com/glapp/get_gl_nums.d.html?category=".$category;
        	$json_data = Util::curl_get_contents($urls);
        	$data = json_decode($json_data, true);
			$this->cache->redis->set($cache_key, json_encode($data), $this->_cache_expire  );
		}

		return $data;
	}

	/*
	 * 批量查询攻略数量
	 * author huanglong
	 * date 2016-05-27
	 */
	public function findCmsGlArrCount($category){
	    if($category==''){
	        return '';
	        exit;
	    }
	    $cache_key = $this->_cache_key_pre . "findCmsGlArrCount";
	    $data = $this->cache->redis->get($cache_key);
	    $data && $data = json_decode($data, 1);
	    if (!is_array($data)) {
	        $urls="http://wap.97973.com/glapp/get_gl_nums_array.d.html?categorys=".$category;
	        $json_data = Util::curl_get_contents($urls);
	        $data = json_decode($json_data, true);
	        $this->cache->redis->set($cache_key, json_encode($data), $this->_cache_expire  );
	    }

	    return $data;
	}

	// -------------------------------------- 游戏关注数 GO -----------------------------------------------------//
	/**
	 * 游戏关注数
	 * @param unknown $cms_id
	 * @param unknown $add
	 */
	public function update_attention_count($cms_id, $add) {
		return $this->_update_attention_count_to_db($cms_id, $add);
	}
	private function _update_attention_count_to_db($cms_id, $add) {
		if (!$cms_id && !is_numeric($add)) {
			return false;
		}

		return $this->db->set('attention_count', "attention_count+$add", false)->where('id', $cms_id)->update($this->_table);
	}
	// -------------------------------------- 游戏关注数 END -----------------------------------------------------//


	public function _aftermath($id) {
		// delete cache
		$cache_key = $this->_cache_key_pre . "get_game_row:$id";
		$this->cache->redis->delete($cache_key);

		$cache_key = $this->_cache_key_pre . "get_game_list:".$this->platform;
		$this->cache->redis->delete($cache_key);

		return 1;
	}


	//从cms同步攻略
	public function gl_sync($_newsInfos){
//		$url ="http://admin.games.sina.com.cn/glapp/cms/sync";
		$url = "http://gl.games.sina.com.cn/cms/sync";
		$data = array(
			'data' => json_encode($_newsInfos)
		);
//		$return_info = $this->global_func->curl_post_new($url,$data,20,'10.210.228.41');
		$return_info = $this->global_func->curl_post_new($url,$data,20,'10.13.32.235');
		return $return_info;
	}
	//*************************转换文本剪辑器内容 start***************************//


	public function pregContent($_content, $cms_id){
		if(empty($_content) || empty($cms_id)){
			return array(
				'content' => "",
				'attribute' => array(),
			);
		}

		// mc缓存
		$mcKey = sha1('preg_contents'.$cms_id);
		$returnContents = $this->cache->redis->get ( $mcKey );
		$returnContents && $returnContents = json_decode($returnContents , true);
		//$returnContents = false;
		if($returnContents == false){

			$pregDataImageArray=$this->pregNewsImage($_content, $cms_id); // 匹配图片
			$pregDataNewsVideoArray=$this->pregNewsVideos($pregDataImageArray["content"]);//匹配视频
			$pregDataNewsSlideArray=$this->pregNewsSlides($pregDataNewsVideoArray["content"]);

			$returnContents["content"]= str_replace('　',' ',$pregDataNewsSlideArray["content"]);//编辑经常添中文字符串啊！
			$returnContents["content"]= preg_replace('/<h2>/i','<p><strong>',$returnContents["content"]);
			$returnContents["content"]= preg_replace('/<\/h2>/i','</p></strong>',$returnContents["content"]);
			$returnContents["content"]= preg_replace('/<ul>/i','',$returnContents["content"]);
			$returnContents["content"]= preg_replace('/<\/ul>/i','',$returnContents["content"]);
			$returnContents["content"]= preg_replace('/<li>/i','<p>',$returnContents["content"]);
			$returnContents["content"]= preg_replace('/<\/li>/i','</p>',$returnContents["content"]);
			$returnContents["content"]= preg_replace('/<\/li>/i','</p>',$returnContents["content"]);
			$returnContents["content"]= preg_replace('/<hr \/>/i','',$returnContents["content"]);
			$returnContents["content"]= preg_replace('/<div class=\"img_wrapper\"><span class=\"img_descr\">/i','<div style="margin:20px;"><span>',$returnContents["content"]);
			$returnContents["content"] = $this->deleteFrame($returnContents["content"]);//删除掉所有底部frame标签
			$returnContents["content"]= preg_replace('/<p[^>]*?>\s*/i','<p class="sina_t">',$returnContents["content"]);//给P修改样式

			$returnContents["content"]= preg_replace('/<table/i','<div style="width:88%;margin-left:20px; overflow:scroll;"><table',$returnContents["content"]);
			$returnContents["content"]= preg_replace('/<\/table>/i','</table></div>',$returnContents["content"]);
			$returnContents["attribute"] = array();
			$returnContents["attribute"]["images"]=$pregDataImageArray["images"];
			$returnContents["attribute"]["videos"]=$pregDataNewsVideoArray["videos"];
			$returnContents["attribute"]["errorVideos"] = $pregDataNewsVideoArray["errorVideos"];
			$returnContents["attribute"]["youkuVideos"] = $pregDataNewsVideoArray["youkuVideos"];
			$returnContents["attribute"]["imgGroup"] = $pregDataNewsSlideArray["imgGroup"];

			$this->cache->redis->save ( $mcKey, json_encode($returnContents), 60 * 60 * 3 );
		}

		return $returnContents;
	}
	//替换frame标签
	private function deleteFrame($_content) {
		//-->过滤掉<p><frame>..</iframe></p>
		$f_pant = "/<p[^>]*>[^<]*<iframe.*?>.*?<\/iframe><\/p>/";
		preg_match_all($f_pant, $_content, $pregData);
		foreach ($pregData[0] as $key => $value) {
			$_content = str_replace($value, '', $_content);
		}
		return $_content;
		//<--过滤掉<p><frame>..</iframe></p>
	}
	//处理图集
	private function pregNewsSlides($_content) {
		$returnData['content'] = $_content;
		//-->根据地质获取图集信息方式
		//初始化图集类
		include_once FCPATH.'gl/models/Slider.php';
		$this->_sobj = new Slider();
		//$this->_sobj = $this->getObj('Slider');
		$pant = "/<!-- HDSlide(.*?)-->/is";
		preg_match_all($pant,$_content,$pregData);

		if (empty($pregData[0])) {
			$returnData["imgGroup"]=array();
		} else {
			foreach ($pregData[0] as $key => $value) {
				$returnData["content"]=str_replace($value, "<!--IMGGROUP_".$key."-->", $returnData["content"]);

				//echo $pregData[1][$key];exit;
				$sliderInfo = $this->_sobj->getSliderImagesArrayByUrl(trim($pregData[1][$key]));
				$picInfo = $this->getPicInfoByIdAndSid($sliderInfo['sid'], $sliderInfo['images_id']);

				$tmpArray = array();
				foreach ($picInfo['data']['item'] as $pk => $pv) {
					$_tmpArray = array();
					$_tmpArray['url'] = $pv['img_url'];
					$_tmpArray['desc'] = $picInfo['album']['intro'];
					$_tmpArray['title'] = $picInfo['album']['name'];
// 					if ($pk==0) {
// 						$_tmpTotal = array();
// 						$_tmpTotal[] = $_tmpArray;
// 						$_tmpTotal = $this->getPicSize($_tmpTotal);
// 						$_tmpArray = $_tmpTotal[0];
// 					} else {
// 						$_tmpArray["width"] = 0;
// 						$_tmpArray["height"] = 0;
// 					}
					$_tmpArray["width"] = $pv['width'];
					$_tmpArray["height"] =$pv['height'];
					$tmpArray[] = $_tmpArray;
				}
				$returnData["imgGroup"][]['list'] = $tmpArray;
			}
		}
		//<--根据地质获取图集信息方式
		//过度key
		$n_count = count($pregData[0]);
		//-->html插入多个li标签图集方式
		$pant = "/<!-- 图集开始(.*?)图集结束 -->/is";
		preg_match_all($pant,$_content,$pregData);

		if (!empty($pregData[0])) {
			foreach ($pregData[0] as $_k => $_v) {
				$key = $_k + $n_count;
				$returnData["content"] = str_replace($_v, "<!--IMGGROUP_".$key."-->", $returnData["content"]);

				//$s_pant = "/<img src=\"(.*?)\" alt=\"(.*?)\" \/\><span [^>]*\>(.*?)<\/span\><\/li\>/";
				$s_pant = "/<li><img src=\"(.*?)\" alt=\"(.*?)\" \/\>(<span\>|<span [^>]*\>)(.*?)<\/span\><\/li\>/"; //操！ span标签有时候无属性
				preg_match_all($s_pant,$_content,$s_pregData);
				$tmpArray = array();
				foreach ($s_pregData[2] as $s_k => $s_v) {
					$_tmpArray = array();
					$_tmpArray['url'] = $s_v;
					$_tmpArray['desc'] = "";
					if ($s_k == 0) {
						$_tmpTotal = array();
						$_tmpTotal[] = $_tmpArray;
						$_tmpTotal = $this->getPicSize($_tmpTotal);
						$_tmpArray = $_tmpTotal[0];
					} else {
						$_tmpArray["width"] = 0;
						$_tmpArray["height"] = 0;
					}
					$tmpArray[] = $_tmpArray;
					$returnData["content"] = str_replace($s_pregData[0][$s_k], "", $returnData["content"]);
				}
				$returnData["imgGroup"][]['list'] = $tmpArray;
			}
		}

		//<--html插入多个li标签图集方式
		//-->获取js内嵌的图集 -- add by max yang at 2014/10/27
		$n_count += count($pregData[0]);
		$pant = "/<script>.*?<\/script>/is";
		preg_match_all($pant,$_content,$pregData);

		if (!empty($pregData)) {
			foreach ($pregData[0] as $key => $value) {
				//看是否有合法图集
				$pant = "/slide_url : '(.*?)'/is";
				preg_match($pant,$value,$tmpPregData);

				if (isset($tmpPregData[1])) {
					$sliderInfo = $this->_sobj->getSliderImagesArrayByUrl(trim($tmpPregData[1]));
					if (!isset($sliderInfo['sid']) && !isset($sliderInfo['images_id'])) {
						continue;
					}
					$picInfo = $this->getPicInfoByIdAndSid($sliderInfo['sid'], $sliderInfo['images_id']);
					$tmpArray = array();
					foreach ($picInfo['data']['item'] as $pk => $pv) {
						$_tmpArray = array();
						$_tmpArray['url'] = $pv['img_url'];
						$_tmpArray['desc'] = "";
						if ($pk==0) {
							$_tmpTotal = array();
							$_tmpTotal[] = $_tmpArray;
							$_tmpTotal = $this->getPicSize($_tmpTotal);
							$_tmpArray = $_tmpTotal[0];
						} else {
							$_tmpArray["width"] = 0;
							$_tmpArray["height"] = 0;
						}
						$tmpArray[] = $_tmpArray;
					}

					//对返回值进行处理
					$key = $key+$n_count;
					$returnData["content"]=str_replace($value, "<!--IMGGROUP_".$key."-->", $returnData["content"]);
					$returnData["imgGroup"][]['list'] = $tmpArray;
				}
			}
		}

		return $returnData;
	}

	private function getPicInfoByIdAndSid($sid,$album_id){///11111
		$returnInfo=$this->getPicInfoByIdAndSidFromApi($sid,$album_id);
		return $returnInfo;
	}
	private function getPicInfoByIdAndSidFromApi($sid,$album_id){////111
		$destURL="http://platform.sina.com.cn/slide/image?app_key=1372825881&format=json&sid=".$sid."&album_id=".$album_id."&num=100";
		$json_data=Util::curl_get_contents($destURL);
		$picInfo=json_decode($json_data,true);
		if(!empty($picInfo["data"])){
			return $picInfo;
		}else{
			return array();
		}
	}
	//替换文档中视频
	private function pregNewsVideos($_content){///1111
		include_once FCPATH.'gl/models/Video.php';
		//初始化video类
		$this->_vobj = new Video();
		//设置句柄
		$hand = 0;
		$errorHand = 0;
		//处理单个视频
		$pant="/<!--mce-plugin-videoList\[(.*?)\]mce-plugin-videoList-->/is";

		preg_match_all($pant,$_content,$pregData);
		$returnData["content"] = $_content;
		$returnData["videos"] = array();
		$returnData["errorVideos"] = array();
		$returnData["youkuVideos"] = array();
		if(!empty($pregData[0])){
			//max yang 重构视频获取功能
			foreach ($pregData[0] as $key => $value) {
				$replaceKey = "";
				$videoJson = $pregData[1][$key];
				$infoArr = json_decode($videoJson, true);
				$replaceKeyArr = array();
				foreach ($infoArr['videos'] as $ik => $iv) {
					$videoInfo = $this->_vobj->getListVideoInfoById(intval($iv['vid']));
					//<--获取视频信息

					if ($videoInfo['result'] == 0) { //vms视频做特殊视频处理
						$replaceKeyArr[] = "<!--ERRORVIDEOS_".$errorHand."-->";
						$_tmpArray["desc"] = "";
						$_tmpArray["video_url"] = $iv["url"];
						$_tmpArray["img_url"] = "";
						$_tmpArray["video_source"] = "";
						$_tmpArray["video_playcount"] = 0;
						$_tmpArray["video_playtime"] = 0;
						$returnData["errorVideos"][$errorHand]=$_tmpArray;
						$errorHand++;
					} else {
						$replaceKeyArr[] = "<!--VIDEO_".$hand."-->";
						$_tmpArray["desc"]=$videoInfo["desc"];
						$_tmpArray["video_url"]=$videoInfo["ipad_url"];
						$_tmpArray["img_url"]=$videoInfo["imagelink"];
						$_tmpArray["video_source"]=$videoInfo["nick_name"];
						$_tmpArray["video_playcount"]=$videoInfo["play_times"];
						$_tmpArray["video_playtime"]=$videoInfo["time_length"];
						$returnData["videos"][$hand]=$_tmpArray;
						$hand++;
					}
				}
				$replaceKey = implode("<p class=\"sina_t\">&nbsp;&nbsp;</p>", $replaceKeyArr);
				$returnData["content"]=str_replace($value, $replaceKey, $returnData["content"]);
			}
		}

		//处理新增视频类型 高清视频
		$pant="/<!--mce-plugin-videoList2\[(.*?)\]mce-plugin-videoList2-->/is";
		preg_match_all($pant,$_content,$pregData);
		if(!empty($pregData[0])){
			//max yang 重构视频获取功能
			foreach ($pregData[0] as $key => $value) {
				$replaceKey = "";
				$videoJson = $pregData[1][$key];
				$infoArr = json_decode($videoJson, true);
				$replaceKeyArr = array();
				foreach ($infoArr['videos'] as $ik => $iv) {

					$videoInfo = $this->_vobj->getNewVideoInfoById(intval($iv['videoid']));

					if ($videoInfo['result'] == 0) { //vms视频做特殊视频处理
						$replaceKeyArr[] = "<!--ERRORVIDEOS_".$errorHand."-->";
						$_tmpArray["desc"] = "";
						$_tmpArray["video_url"] = $iv["url"];
						$_tmpArray["img_url"] = "";
						$_tmpArray["video_source"] = "";
						$_tmpArray["video_playcount"] = 0;
						$_tmpArray["video_playtime"] = 0;
						$returnData["errorVideos"][$errorHand]=$_tmpArray;
						$errorHand++;
					} else {
						$replaceKeyArr[] = "<!--VIDEO_".$hand."-->";
						$_tmpArray["desc"]=$videoInfo["desc"];
						$_tmpArray["video_url"]=$videoInfo["ipad_url"];
						$_tmpArray["img_url"]=$videoInfo["imagelink"];
						$_tmpArray["video_source"]=$videoInfo["nick_name"];
						$_tmpArray["video_playcount"]=$videoInfo["play_times"];
						$_tmpArray["video_playtime"]=$videoInfo["time_length"];
						$returnData["videos"][$hand]=$_tmpArray;
						$hand++;
					}
				}
				$replaceKey = implode("<p class=\"sina_t\">&nbsp;&nbsp;</p>", $replaceKeyArr);
				$returnData["content"]=str_replace($value, $replaceKey, $returnData["content"]);
			}
		}

		//flash 按照正常视频处理
		//flash obj
		$pant = "/<object[^>]*?>.*?<embed[^>]*? src=\"(.*?)\"[^>]*?>.*?<\/embed><\/object>/";
		preg_match_all($pant, $returnData["content"], $pregData);
		if (!empty($pregData[0])) {
			foreach ($pregData[0] as $key => $value) {
				$returnData["content"]=str_replace($value, "<!--VIDEO_".$hand."-->", $returnData["content"]);
				$_tmpArray["desc"] = "";
				$_tmpArray["video_url"] = $pregData[1][$key];
				$_tmpArray["img_url"] = "";
				$_tmpArray["video_source"] = "";
				$_tmpArray["video_playcount"] = 0;
				$_tmpArray["video_playtime"] = 0;
				$returnData["videos"][$hand]=$_tmpArray;
				$hand++;
			}
		}
		//-->特殊视频处理方案
		//youku video
		$pant="/<p[^>]*>[^<]*<iframe src=\"(.*?)\"[^>]*?><\/iframe><\/p>/";
		preg_match_all($pant, $returnData["content"], $pregData);
		if (!empty($pregData[0])) {
		  foreach ($pregData[0] as $key => $value) {
				if (stristr($value, "player.youku.com")) { //youku 视屏
                    if (!stristr($value, ".swf")) { //.swf
                       // http://player.youku.com/embed/XMTM2MjE0MzU0MA==

                        $youkuId= preg_replace('/http:\/\/player.youku.com\/embed\//','',$pregData[1][$key]);
                        $youkuUrl = "http://play.youku.com/play/get.json?vid=".$youkuId."&ct=10";
                        $youkuData = Util::curl_get_contents($youkuUrl);
                        $result = json_decode($youkuData,true);

                        $returnData["content"]=str_replace($value, "<!--YOUKUVIDEOS_".$errorHand."-->", $returnData["content"]);
                        $_tmpArray["desc"] = "";
                        $_tmpArray["video_url"] = 'http://v.youku.com/v_show/id_'.$youkuId;
                        $_tmpArray["img_url"] = $result['data']['video']['logo'];
                        $_tmpArray["video_source"] = "";
                        $_tmpArray["video_playcount"] = 0;
                        $_tmpArray["video_playtime"] = 0;
                        $returnData["youkuVideos"][$errorHand]=$_tmpArray;
                        $errorHand++;
                    }else{
                        $Urls = $pregData[1][$key];
                        $pregData[1][$key]= preg_replace('/http:\/\/player.youku.com\/player.php\/sid\//','',$pregData[1][$key]);
                        $pregData[1][$key]= preg_replace('/\/v.swf/i','',$pregData[1][$key]);
                        $youkuUrl = "http://play.youku.com/play/get.json?vid=".$pregData[1][$key]."&ct=10";
                        $youkuData = Util::curl_get_contents($youkuUrl);
                        $result = json_decode($youkuData,true);

                        $returnData["content"]=str_replace($value, "<!--YOUKUVIDEOS_".$errorHand."-->", $returnData["content"]);
                        $_tmpArray["desc"] = "";
                        $_tmpArray["video_url"] ='http://v.youku.com/v_show/id_'.$pregData[1][$key];
                        $_tmpArray["img_url"] =  $result['data']['video']['logo'];
                        $_tmpArray["video_source"] = "";
                        $_tmpArray["video_playcount"] = 0;
                        $_tmpArray["video_playtime"] = 0;
                        $returnData["youkuVideos"][$errorHand]=$_tmpArray;
                        $errorHand++;
                    }
				}
			}
		}
		//youku video
		//flash 按照正常视频处理
		//flash obj
		$pant = "/<p[^>]*?>.*?<embed[^>]*? src=\"(.*?)\"[^>]*?>.*?<\/embed><\/p>/";
		preg_match_all($pant, $returnData["content"], $pregData);
		if (!empty($pregData[0])) {
			foreach ($pregData[0] as $key => $value) {
				if (stristr($value, "player.youku.com")) { //youku 视屏
                    if (!stristr($value, ".swf")) { //.swf
                        $returnData["content"]=str_replace($value, "<!--YOUKUVIDEOS_".$errorHand."-->", $returnData["content"]);
                        $_tmpArray["desc"] = "";
                        $_tmpArray["video_url"] = $pregData[1][$key];
                        $_tmpArray["img_url"] = "";
                        $_tmpArray["video_source"] = "";
                        $_tmpArray["video_playcount"] = 0;
                        $_tmpArray["video_playtime"] = 0;
                        $returnData["youkuVideos"][$errorHand]=$_tmpArray;
                        $errorHand++;
                    }else{
                        $Urls = $pregData[1][$key];
                        $pregData[1][$key]= preg_replace('/http:\/\/player.youku.com\/player.php\/sid\//','',$pregData[1][$key]);
                        $pregData[1][$key]= preg_replace('/\/v.swf/i','',$pregData[1][$key]);
                        $youkuUrl = "http://play.youku.com/play/get.json?vid=".$pregData[1][$key]."&ct=10";
                        $youkuData = Util::curl_get_contents($youkuUrl);
                        $result = json_decode($youkuData,true);

                        $returnData["content"]=str_replace($value, "<!--YOUKUVIDEOS_".$errorHand."-->", $returnData["content"]);
                        $_tmpArray["desc"] = "";
                        $_tmpArray["video_url"] ='http://v.youku.com/v_show/id_'.$pregData[1][$key];
                        $_tmpArray["img_url"] =  $result['data']['video']['logo'];
                        $_tmpArray["video_source"] = "";
                        $_tmpArray["video_playcount"] = 0;
                        $_tmpArray["video_playtime"] = 0;
                        $returnData["youkuVideos"][$errorHand]=$_tmpArray;
                        $errorHand++;
                    }
				}
			}
		}
		/*
		//youku video
		//flash 按照正常视频处理
		//flash obj
		$pant = "/<p[^>]*?>.*?<embed[^>]*? src=\"(.*?)\"[^>]*?>.*?<\/embed><\/p>/";
		preg_match_all($pant, $returnData["content"], $pregData);
		if (!empty($pregData[0])) {
		    foreach ($pregData[0] as $key => $value) {
		        if (stristr($value, "player.youku.com")) { //youku 视屏
		            if (!stristr($value, ".swf")) { //.swf
		                $returnData["content"]=str_replace($value, "<!--ERRORVIDEOS_".$errorHand."-->", $returnData["content"]);
		                $_tmpArray["desc"] = "";
		                $_tmpArray["video_url"] = $pregData[1][$key];
		                $_tmpArray["img_url"] = "";
		                $_tmpArray["video_source"] = "";
		                $_tmpArray["video_playcount"] = 0;
		                $_tmpArray["video_playtime"] = 0;
		                $returnData["errorVideos"][$errorHand]=$_tmpArray;
		                $errorHand++;
		            }else{
		                $returnData["content"]=str_replace($value, "<!---->", $returnData["content"]);
		                $errorHand++;
		            }
		        }
		    }
		}
		*/
		//<--特殊视频处理方案
		return $returnData;
	}
	//替换新闻中图片
	public function pregNewsImage($_content,$cms_id = ''){//111

		//$pant = "/<div [^>]*class=\"img_wrapper[^>]*><img [^>]*src=\"(.*?)\"[^>]*><span [^>]*class=\"img_descr[^>]*>(.*?)<\/span><\/div>/";
		//$pant = "/<div [^>]*class=\"img_wrapper[^>]*><img [^>]*src=[\"\'](.*?)[\"\'][^>]*>(?:<span [^>]*class=\"img_descr[^>]*>(.*?)<\/span>)?<\/div>/";
//		$pant = "/(?:<div [^>]*class=\"img_wrapper[^>]*><img [^>]*src=[\"\'](.*?)[\"\'][^>]*>(?:<span [^>]*>(.*?)<\/span>)?<\/div>)|(?:<p [^>]*>(?:<span class=\"ui-dialog-border\">)?<img [^>]*src=[\"\'](.*?)[\"\'] [^>]*>(?:<\/span>)?.*?<\/p>)/";
//		preg_match_all($pant,$_content[0]['content'],$pregData);
//		if (isset($pregData[1][0]) && isset($pregData[3][0])) {
//			if ($pregData[1][0] == null && $pregData[3][0] != null) {
//				$pregData[1] = $pregData[3];
//			}
//		}

		/*
                        $pant = "/(?:<div [^>]*class=\"img_wrapper[^>]*>.*?<img[^>]*\s+src=[\"\'](.*?)[\"\'][^>]*>(?:<span [^>]*?class=[\"\']img_descr[\"\'][^>]*?>(.*?)<\/span>)?.*?<\/div>)|(?:<p [^>]*>.*?<img[^>]*\s+src=[\"\'](.*?)[\"\'] [^>]*>(?:<span [^>]*?class=[\"\']img_descr[\"\'][^>]*?>(.*?)<\/span>)?.*?<\/p>)/";
                        preg_match_all($pant,$_content[0]['content'],$pregData);

                        // 取1、3 ； 2、4 有值的
                        foreach ($pregData[1] as $k => $v) {
                            $pregData[1][$k] = $pregData[1][$k] ? $pregData[1][$k] : $pregData[3][$k];
                        }
                        foreach ($pregData[2] as $k => $v) {
                            $pregData[2][$k] = $pregData[2][$k] ? $pregData[2][$k] : $pregData[4][$k];
                        }
        */
                $pant = "/(?:<div [^>]*class=\"img_wrapper[^>]*>.*?<img[^>]*\s+src=[\"\'](.*?)[\"\'][^>]*>(?:<span [^>]*?class=[\"\']img_descr[\"\'][^>]*?>(.*?)<\/span>)?.*?<\/div>)|(?:<p [^>]*>.*?<img[^>]*\s+src=[\"\'](.*?)[\"\'] [^>]*>(?:<span [^>]*?class=[\"\']img_descr[\"\'][^>]*?>(.*?)<\/span>)?.*?<\/p>)|(?:<img[^>]*\s+src=[\"\'](.*?)[\"\'](?:[^>]*\s+alt=[\"\'](.*?)[\"\'])?[^>]*>)/";

                $images = array();

				$pregData = array();
				preg_match_all($pant,$_content[0]['content'],$pregData);

				// 取1、3 ； 2、4 有值的
				foreach ($pregData[1] as $k => $v) {
					$pregData[1][$k] = $pregData[1][$k] ? $pregData[1][$k] : ($pregData[3][$k] ? $pregData[3][$k] : $pregData[5][$k]);
				}
				foreach ($pregData[2] as $k => $v) {
					$pregData[2][$k] = $pregData[2][$k] ? $pregData[2][$k] : ($pregData[4][$k] ? $pregData[4][$k] : $pregData[6][$k]);
				}

				if ($pregData[1]) {
					foreach ($pregData[1] as $k => $v) {
						$images[] = array(
							'url' => trim($v),
							'desc' => $pregData[2][$k]
						);
					}
				}


                $returnData["content"]=$_content;

                $hand = 0; // num
                if(empty($pregData[0])){
                    $returnData["images"]=array();
                }else{
                    foreach($pregData[0] as $key => $value){
                        $returnData["content"][0]['content']=str_replace($value, "<!--IMG_".$hand."-->", $returnData["content"][0]['content']);

                        $_tmpArray=array();
                        $_tmpArray["url"]=$pregData[1][$key];
                        $_tmpArray["desc"]=$pregData[2][$key];

                        $returnData["images"][$hand]=$_tmpArray;
                        $hand++;
                    }
                }
                $returnData['content'] = $returnData['content'][0]['content'];
                //-->处理特殊图片  专区图片 <a>标签
                $pant = "/<a[^>]*?>[^<]*?<img[^>]*src=[\"\'](.*?)[\"\'][^>]*?\/>.*?<\/a>/";
                preg_match_all($pant,$returnData['content'],$pregData);
                if(!empty($pregData[0])){
                    foreach ($pregData[0] as $key => $value) {
                        $returnData["content"] = str_replace($value, "<!--IMG_".$hand."-->", $returnData["content"]);
                        $_tmpArray = array();
                        $_tmpArray["url"]=$pregData[1][$key];
                        $_tmpArray["desc"]="";
                        $returnData["images"][$hand]=$_tmpArray;
                        $hand++;
                    }
                }
                // <center>标签
                $pant = "/<center[^>]*?>[^<]*?<img[^>]*src=[\"\'](.*?)[\"\'][^>]*?\/>.*?<\/center>/";
                preg_match_all($pant,$returnData['content'],$pregData);
                if(!empty($pregData[0])){
                    foreach ($pregData[0] as $key => $value) {
                        $returnData["content"] = str_replace($value, "<!--IMG_".$hand."-->", $returnData["content"]);
                        $_tmpArray = array();
                        $_tmpArray["url"]=$pregData[1][$key];
                        $_tmpArray["desc"]="";
                        $returnData["images"][$hand]=$_tmpArray;
                        $hand++;
                    }
                }

                // <p>标签
                $pant = "/<p[^>]*?>[^<]*?<img[^>]*?src=[\"\'](.*?)[\"\'][^>]*?\/>.*?<\/p>/is";
                preg_match_all($pant,$returnData['content'],$pregData);
                if(!empty($pregData[0])){
                    foreach ($pregData[0] as $key => $value) {
                        $returnData["content"] = str_replace($value, "<!--IMG_".$hand."-->", $returnData["content"]);
                        $_tmpArray = array();
                        $_tmpArray["url"]=$pregData[1][$key];
                        $_tmpArray["desc"]="";
                        $returnData["images"][$hand]=$_tmpArray;
                        $hand++;
                    }
                }

                // <div>标签
                $pant = "/<div[^>]*?>[^<]*?<img[^>]*src=[\"\'](.*?)[\"\'][^>]*?\/>.*?<\/div>/is";
                preg_match_all($pant,$returnData['content'],$pregData);
                if(!empty($pregData[0])){
                    foreach ($pregData[0] as $key => $value) {
                        $returnData["content"] = str_replace($value, "<!--IMG_".$hand."-->", $returnData["content"]);
                        $_tmpArray = array();
                        $_tmpArray["url"]=$pregData[1][$key];
                        $_tmpArray["desc"]="";
                        $returnData["images"][$hand]=$_tmpArray;
                        $hand++;
                    }
                }

                //<--
                $returnData["images"] = $this->getPicSize($cms_id);
                return $returnData;
            }
            public function getPicSize($cms_id, $count = 100) {
                //本机设置保存
				$count = (int) $count;
                $mcKey = sha1($this->_cache_key_pre . 'get_pic_size'.$cms_id . ":" . $count);
                $rs = $this->cache->redis->get ( $mcKey );
				$rs && $rs = json_decode($rs , true);
                if($rs == false || empty($rs)){
                    $sql = "SELECT * FROM gl_article_image WHERE cms_id='$cms_id' LIMIT $count";
                    $rs = $this->db->query_read($sql);
                    $rs = $rs ? $rs->result_array() : array();
                    $this->cache->redis->save($mcKey, json_encode($rs), $this->_cache_expire * 10);
                }

                return $rs;
            }
            //*************************转换文本剪辑器内容 end***************************//
	public function deleteCache($gameId){
		$cache_key = $this->_cache_key_pre . "getPicSize:$gameId";
		$this->cache->redis->delete($cache_key);
		return 1;
	}
}
