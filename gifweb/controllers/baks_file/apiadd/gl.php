<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**;
 * API-攻略信息操作
 */
class Gl extends MY_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('gl_model');
		$this->load->model('user_model');
		$this->load->model('game_model');
		$this->load->model('follow_model');
		$this->load->model('article_model');
		$this->load->model('like_model');
		$this->load->model('recommend_model');
	}

	/**
	 * 攻略列表信息
	 *
	 */
	public function list_info()
	{
		$ClassId	= trim ( $this->input->get('ClassId',true) );//一级分类id
		$page	  	= $this->input->get('page',true) ? $this->input->get('page',true) : 1;
		$count	  	= $this->input->get('count',true) ? $this->input->get('count',true) : 10;
		$max_id	  	= trim ( $this->input->get('max_id',true) );
        if($ClassId == '0_0_0_0_0_0_0_0'){
            $ClassId = 'a918';
        }
		// mc缓存
		$mcKey = sha1('get_list_info_xuanyaodang' . ENVIRONMENT .$ClassId.'_'.$page.'_'.$count.'_'.$max_id);
 		$data = $this->cache->redis->get($mcKey);
 		$data && $data = json_decode($data, true);

		if($data === false || empty($data)){
			$data = array();
		}else{
			Util::echo_format_return(_SUCCESS_, $data ? $data : array());
			die();
		}

		try {
			if (empty($ClassId) ) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}
			$b = explode('_',$ClassId);
			$gameId = str_replace('a','',$b[0]);

			$article_info = $this->game_model->get_cms_info_by_category($ClassId,$page,$count);
			//-->处理 如果max_id出现相同的 那么从下一页抓取最新的补齐   start --------//
			$flag = 100;
			foreach ($article_info as $_k => $_v) {
				if ($_v['_id'] == $max_id) {
					$flag = $_k;
				}
			}
			$returns = array();
			$returnss = array();
			if ($flag != 100) {

				foreach ($article_info as $_k => $_v) {
					if ($_k > $flag ) {
						array_push($returns, $_v);
					}
				}
				$article_infos = $this->game_model->get_cms_info_by_category($ClassId,$page,$count+1+$flag);
				foreach ($article_infos as $_k1 => $_v) {
					if ($_k1 > $flag) {
						array_push($returnss, $_v);
					}
				}
				$returns = $returnss;
			} else {
				$returns = $article_info;
			}
			//-->处理 如果max_id出现相同的 那么从下一页抓取最新的补齐   end --------//
//            echo "<pre>";print_r($returns);exit;
			$data = array();
			foreach ($returns as $k=>$v){
				$images = $this->gl_model->getPicSize($v['_id']);
				$thumbnail[$k] = array();
				foreach ($images as $k_1=>$v_1){
					if($v_1['width'] > '380' && $v_1['height'] > '286' ){
						$thumbnail[$k][] = $v_1['url'];
					}
				}
				if(count($thumbnail[$k]) >=3){
					$thumbnails[$k][] = $thumbnail[$k][0];
					$thumbnails[$k][] = $thumbnail[$k][1];
					$thumbnails[$k][] = $thumbnail[$k][2];
				}

				$article[$k] = $this->article_model->findArticleData($v['_id']);
				if(empty($article[$k]['id'])){
					$this->article_model->addRedis($v['_id']);
					continue;
				}
				$_arr = array();
				$_arr['absId'] = $v['_id'];
				$_arr['abstitle'] = $v['title'];
				$_arr['absImage'] = $v['pics'][0]['imgurl'] ? $v['pics'][0]['imgurl'] : '';
				/* 攻略部分有修改，炫耀党同步改by wangbo8 2016/7/21
				$_arr['scanCount'] = $article[$k]['browse_count'] ? (int) $article[$k]['browse_count'] : 0;
				$_arr['praiseCount'] =$article[$k]['mark_up_count'] ? (int)$article[$k]['mark_up_count'] : 0;
				*/
				$_arr['scanCount'] = ($article[$k]['browse_count']+$article[$k]['virtual_browse_count']) ? (int) ($article[$k]['browse_count']+$article[$k]['virtual_browse_count']) : 0;
				$_arr['praiseCount'] =($article[$k]['mark_up_count']+$article[$k]['virtual_mark_up_count']) ? (int)($article[$k]['mark_up_count']+$article[$k]['virtual_mark_up_count']) : 0;
				$_arr['thumbnail'] =$thumbnails[$k] ? $thumbnails[$k] : array();
				$_arr['type'] =$v['mdType'] ? 1 : 0;

				$data[] = $_arr;
			}
			
			$this->cache->redis->save($mcKey, json_encode($data), 60*5);
			Util::echo_format_return(_SUCCESS_, $data?$data:array());
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}

	/**
	 * 攻略详情信息
	 *
	 */
	public function detail_info()
	{
		$newsid		= trim ( $this->input->get('newsid',true) );//一级分类id
		$guid	  	= intval ( $this->input->get('guid',true) );

		if($guid < 1){
			$expire_time = 60 * 10;
		}else{
			$expire_time = 60 * 1;
		}

		// mc缓存
		$mcKey = sha1('detail_info_xuanyaodang' . ENVIRONMENT .$newsid.'_'.$guid);

		$newsInfo = $this->cache->redis->get ( $mcKey );
		$newsInfo && $newsInfo = json_decode($newsInfo, true);

		if($newsInfo === false || empty($newsInfo)){
			$data = array();
		}else{
			Util::echo_format_return(_SUCCESS_, $newsInfo);
			die();
		}

		try {

			if (empty($newsid)) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}
			$_newsInfos = $this->game_model->get_cms_info($newsid);
			$_newsInfos = $_newsInfos[0];

			//查询文章信息
			$artilce = $this->article_model->findArticleData($newsid);

			if (empty($_newsInfos)) {
				throw new Exception('数据不存在', _PARAMS_ERROR_);
			}

			if(empty($artilce)) {
				//同步攻略
				$gl_sync_return = $this->gl_model->gl_sync($_newsInfos);
				//查询文章信息
				$artilce = $this->article_model->findArticleData($newsid, 1);
			}

			if($_newsInfos["source"]){
				$source = $_newsInfos["source"];
			}elseif($_newsInfos['author']){
				$source = $_newsInfos["author"];
			}elseif($_newsInfos['otherMedia']){
				$source = $_newsInfos["otherMedia"];
			}
			$newsInfo = array();
			$newsInfo["abstitle"] 	= $_newsInfos["title"];
			$newsInfo["source"] 	= $source ? trim($source) : '';
			$newsInfo["updateTime"] = $_newsInfos["mTime"];
			$newsInfo["shareUrl"] 	= 'http://www.wan68.com/raiders/info/'.$newsid;
			$newsInfo["shareContent"] 	= '我在全民手游攻略给你分享，快来看看吧！';
			$newsInfo["commentCount"] = (int) $artilce['comment_count'];//评论数
			$newsInfo["content"] = $_newsInfos["content"] ? $_newsInfos["content"] : '';
			$newsInfo["praised"] = $praised ? true : false;//是否赞
			$newsInfo["treaded"] = $treaded ? true : false;//是否踩
			$newsInfo["collected"] = $collected ? true : false;//是否收藏

			//相关新闻处理
			$newsInfo["relateNews"] = array();
			if (!empty($_newsInfos['relNews'])) {
				foreach ($_newsInfos['relNews'] as $_k => $_v) {
					$newsInfo["relateNews"][] = array("absId" => trim($_v['id']), "abstitle" => trim($_v['title']));
				}
			}
			$_content = $_newsInfos["content"];
			$contents=array();
			foreach($_content as $k => $v){
				$contents[0]['content'].=$v['content'];
			}
			$grepContentArray = $this->gl_model->pregContent($contents, $newsid);

			$newsInfo["content"] = trim(trim($grepContentArray["content"]," "),"t");
			if($grepContentArray["attribute"]){
				$newsInfo["attribute"] = $grepContentArray["attribute"];
			}

			//更新浏览数，30秒算一次
			$device_id = trim( $this->input->get('deviceId',true) );
			$mcKey1 = sha1('forbidden_user_pv_'.$device_id."-".$newsid);

			$is_up = $this->cache->redis->get($mcKey1);
			if($is_up === false){
				$this->article_model->updateArticleBrowseCount($newsid);

				$this->cache->redis->set($mcKey1, 1, 30);
			}

			$this->cache->redis->set($mcKey, json_encode($newsInfo), $expire_time);

			//=============炫耀党数据处理开始======================
			unset($newsInfo['shareUrl']);
			unset($newsInfo['shareContent']);
			unset($newsInfo['commentCount']);
			unset($newsInfo['praised']);
			unset($newsInfo['treaded']);
			unset($newsInfo['collected']);

			//=============炫耀党数据处理结束======================

			//
			Util::echo_format_return(_SUCCESS_, $newsInfo);
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}

	/**
	 * 攻略聚合页
	 *
	 */
	public function juhe_page()
	{
		$gameId	  = trim ( $this->input->get('gameId',true) );
		$guid	  = trim ( $this->input->get('guid',true) );
		$fromZone	  = $this->input->get('fromZone',true) ;

		try {
			if (empty($gameId)) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}
			$cms_info = $this->game_model->get_cms_game_info($gameId);
			$cms_info = $cms_info[0];
			if (empty($cms_info)) {
				throw new Exception('没有这个游戏', _PARAMS_ERROR_);
			}
			//攻略分类集合［一级分类］

			$info_a = $this->gl_model->get_category_row($gameId);

			if($this->platform == 'android'){//android来源
				$hidden_type = $info_a['android_type'];
			}else{//ios来源
				$hidden_type = $info_a['ios_type'];
			}
			if($hidden_type == 1){
				$is_hidden = false;
			}else{
				$is_hidden = true;
			}
			$info_a['absImage'] = $cms_info['logo'];
			if (empty($info_a)) {
				throw new Exception('没有这个游戏', _PARAMS_ERROR_);
			}

			//［二级分类］
			$info_b = $this->gl_model->get_category_list($info_a['id']);

			//［三级分类］
			if(!empty($info_b) || $info_b != array()){
			     /*批量查询raiderCount*/
			    $categorys = "";
				foreach ($info_b as $k => $v ){
				    if($categorys){
				        $categorys .= ",".$v['absId'];
				    }else{
				        $categorys = $v['absId'];
				    }
				}
				$articleData = $this->gl_model->findCmsGlArrCount($categorys);
				foreach ($info_b as $k => &$v ){
					$info_c = $this->gl_model->get_category_list($v['id']);

					$info_b[$k]['raiderCount'] =  $articleData[$k][$v['absId']];
					if($info_c){
						if(count($info_c) >=2){
							array_unshift($info_c,array('absId'=>$v['absId'],'abstitle'=>'全部'));
						}
						$info_b[$k]['item'] = $info_c;
					}
					unset($info_b[$k]['id']);
					unset($v['article_count']);
				}
			}
			//编辑推荐数据［区分平台］
			$juhe_recommend = $this->recommend_model->get_recommend_list(2,$gameId);//推荐游戏
			$juheRecommends = array();
			foreach ($juhe_recommend as $k_1 => $v_1) {
				if($v_1['area']) {
					$juheRecommend =array();
					if ($v_1['type'] == 1) {
						$juheRecommend['type'] = 2;
					} elseif ($v_1['type'] == 2) {
						$juheRecommend['type'] = 0;
					} elseif ($v_1['type'] == 3) {
						$juheRecommend['type'] = 1;
					} elseif ($v_1['type'] == 4) {
						$juheRecommend['type'] = 3;
					} elseif ($v_1['type'] == 5) {
						$juheRecommend['type'] = 4;
					} elseif ($v_1['type'] == 6) {
						$juheRecommend['type'] = 5;
					}
					$juheRecommend['abstitle'] = $v_1['title'];
					if ($v_1['type'] == '4' || $v_1['type'] == '5' || $v_1['type'] == '6') {
						$juheRecommend['absId'] = $v_1['param'];
					} else {
						$juheRecommend['webUrl'] = $v_1['param'];
					}
					$juheRecommends[] =$juheRecommend;
				}
			}
			unset($info_a['ios_id']);
			unset($info_a['android_id']);
			unset($info_a['android_type']);
			unset($info_a['ios_type']);
			$raidersClassList = $info_b;
			unset($info_a['web_url']);
			unset($raidersClassList['attentionCount']);
			unset($raidersClassList['web_url']);
			$data  = $info_a;
			unset($data['id']);
			$data['absImage'] =$data['absImage'] ? $data['absImage'] : '';
			$data['absId'] 	=$info_a['id'] ? $info_a['id'] : '';
			$data['packageURL'] =$cms_info['packageURL'] ? array_filter(explode("\r\n",$cms_info['packageURL'])) : array();
			$data['initialsEng'] =$cms_info['proLetters'][0] ? (string) $cms_info['proLetters'][0] : '';
			$data['buyAddress'] =$cms_info['buyUrl'] ? $cms_info['buyUrl'] : '';//  cms 购买地址
			$data['hidenAction'] =$is_hidden;// 后台IOS可否下载
			$data['attentionCount'] =(int)$data['attentionCount'];//该游戏的关注数－－－暂无   后台添加   关注数基数 、 正式关注数［不可改］
			$data['attentioned'] =$collected ? true : false ;//当前用户是否已关注该游戏\
			$data['focusList'] =$fromZone ? $jiaodianRecommend : array();
			$data['shortcutList'] =$fromZone ? $kuaijieRecommend : array();//快捷入口数据
			$data['recommendList'] =$juheRecommends;//编辑推荐
			$data['raidersClassList'] =$raidersClassList;//攻略分类集合

			//====================炫耀党接口数据处理开始=======================
			unset($data['attentionCount']);
			unset($data['absImage']);
			unset($data['initialsEng']);
			unset($data['packageURL']);
			unset($data['buyAddress']);
			unset($data['hidenAction']);
			unset($data['attentioned']);
			unset($data['focusList']);
			unset($data['shortcutList']);
			//unset($data['recommendList']); //编辑推荐

			//====================炫耀党接口数据处理结束=======================

			Util::echo_format_return(_SUCCESS_, $data);
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}



	// ================================================================================================= //
	/**
	 * 攻略详情信息
	 *
	 */
	public function detail_info_review_remap()
	{
		$newsid		= trim ( $this->input->get('newsid',true) );//一级分类id
		$guid	  	= intval ( $this->input->get('guid',true) );

		if($guid < 1){
			$expire_time = 60 * 10;
		}else{
			$expire_time = 60 * 1;
		}

		// mc缓存
		$mcKey = sha1('detail_info_xuanyaodang' . ENVIRONMENT .$newsid.'_'.$guid . '_review1' );
		//$newsInfo = $this->cache->memcached->get ( $mcKey );
		
		$newsInfo = $this->cache->redis->get($mcKey);
		$newsInfo && $newsInfo = json_decode($newsInfo, true);
// 		$newsInfo = false;//临时
		if($newsInfo === false || empty($newsInfo)){
			$data = array();
		}else{
			Util::echo_format_return(_SUCCESS_, $newsInfo);
			die();
		}

		try {

			if (empty($newsid)) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}
			$_newsInfos = $this->game_model->get_cms_info($newsid);
			$_newsInfos = $_newsInfos[0];

			//查询文章信息
			$artilce = $this->article_model->findArticleData($newsid);

			if (empty($_newsInfos)) {
				throw new Exception('数据不存在', _PARAMS_ERROR_);
			}

			if(empty($artilce)) {
				//同步攻略
				$gl_sync_return = $this->gl_model->gl_sync($_newsInfos);
				//查询文章信息
				$artilce = $this->article_model->findArticleData($newsid, 1);
			}
// 			$URLs ="";
// 			foreach ($_newsInfos["URLs"] as $k => $v){
// 				$URLs = $v;
// 			}

			//该用户是否已赞、踩
			$praised = $this->like_model->is_like($newsid,1);

			//该用户是否已踩
			$treaded = $this->like_model->is_like($newsid,2);

			//该用户是否已收藏
			$collected = $this->follow_model->is_follow($guid,1,$newsid);

			if($_newsInfos["source"]){
				$source = $_newsInfos["source"];
				if (is_array($source)) {
					$source = $source['outlook'];
				}
			}elseif($_newsInfos['author']){
				$source = $_newsInfos["author"];
			}elseif($_newsInfos['otherMedia']){
				$source = $_newsInfos["otherMedia"];
			}
			
			$newsInfo = array();
			$newsInfo["abstitle"] 	= $_newsInfos["title"];
			$newsInfo["source"] 	= $source ? trim($source) : '';
			$newsInfo["updateTime"] = $_newsInfos["mTime"];
			$newsInfo["shareUrl"] 	= 'http://www.wan68.com/raiders/info/'.$newsid;
// 			$newsInfo["shareUrl"] 	= $URLs ? $URLs : '';
			$newsInfo["shareContent"] 	= '我在全民手游攻略给你分享，快来看看吧！';
			$newsInfo["commentCount"] = (int) $artilce['comment_count'];//评论数
			$newsInfo["content"] = $_newsInfos["content"] ? $_newsInfos["content"] : '';
			$newsInfo["praised"] = $praised ? true : false;//是否赞
			$newsInfo["treaded"] = $treaded ? true : false;//是否踩
			$newsInfo["collected"] = $collected ? true : false;//是否收藏

			//相关新闻处理
			$newsInfo["relateNews"] = array();
			if (!empty($_newsInfos['relNews'])) {
				foreach ($_newsInfos['relNews'] as $_k => $_v) {
					$newsInfo["relateNews"][] = array("absId" => trim($_v['id']), "abstitle" => trim($_v['title']));
				}
			}
			$_content = $_newsInfos["content"];
			$contents=array();
			foreach($_content as $k => $v){
				$contents[0]['content'].=$v['content'];
			}
			$grepContentArray = $this->gl_model->pregContent($contents, $newsid);

			$newsInfo["content"] = trim(trim($grepContentArray["content"]," "),"t");
			$newsInfo["content"] = str_replace('<p class="sina_t">全民手游攻略下载地址：<span style="color: #ff0000;">点击下载 &gt;&gt;&gt;</span></p>', '', $newsInfo["content"]);
			$newsInfo["content"] = str_replace('<p class="sina_t">全民手游攻略下载地址：<span style="color: #ff0000;"><a href="http://wan68.com/download" target="_blank"><span style="color: #ff0000;">点击下载</span></a> &gt;&gt;&gt;</span></p>', '', $newsInfo["content"]);
			$newsInfo["content"] = str_replace('<p class="sina_t">全民手游攻略下载地址：<span style="color: #ff0000;"><a href="http://wan68.com/download" target="_blank"><span style="color: #ff0000;">点击下载</span></a>&gt;&gt;&gt;</span></p>', '', $newsInfo["content"]);
			$newsInfo["content"] = str_replace('<p class="sina_t"><span style="color: #ff0000;"><strong><span style="color: #0000ff;">圣骑士之歌小米商店下载地址</span>：</strong></span><strong>http://app.mi.com/detail/119124</strong></p>', '', $newsInfo["content"]);
			if($grepContentArray["attribute"]){
				$newsInfo["attribute"] = $grepContentArray["attribute"];
			}

			//更新浏览数，30秒算一次
			$device_id = trim( $this->input->get('deviceId',true) );
			$mcKey1 = sha1('forbidden_user_pv_'.$device_id."-".$newsid);
			//$is_up = $this->cache->memcached->get( $mcKey );
			$is_up = $this->cache->redis->get($mcKey1);
			if($is_up === false){
				$this->article_model->updateArticleBrowseCount($newsid);
				//$this->cache->memcached->save( $mcKey, 1, 30 );
				$this->cache->redis->set($mcKey1, 1, 30);
			}

			//$this->cache->memcached->save ( $mcKey, $newsInfo, $expire_time );
			$this->cache->redis->set($mcKey, json_encode($newsInfo), $expire_time);
			Util::echo_format_return(_SUCCESS_, $newsInfo);
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}
}

/* End of file gl.php */
/* Location: ./application/controllers/api/gl.php */
