<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**;
 * API-攻略信息操作
 */
class Gl_add extends MY_Controller
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
	 * 攻略详情信息
	 *
	 */
	public function detail_info()
	{
		$newsid		= trim ( $this->input->get('newsid',true) );//一级分类id
		$guid	  	= intval ( $this->input->get('guid',true) );

		if($guid < 1){
			$expire_time = 60 * 100;
		}else{
			$expire_time = 60 * 10;
		}

		// mc缓存
		$mcKey = sha1('detail_info_gl_add_' . ENVIRONMENT .$newsid.'_'.$guid);
		$newsInfo = $this->cache->redis->get ( $mcKey );
		$newsInfo && $newsInfo = json_decode($newsInfo, true);
		//$newsInfo = false;

		if($newsInfo == false || empty($newsInfo)){
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
// 			$newsInfo["shareUrl"] 	= $URLs ? $URLs : '';
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

			//对攻略内容进行处理
			$this->load->model('waptext_model');
			$_content[0]['content'] = $this->waptext_model->clear_qr_code($_content[0]['content']);

			$contents=array();
			foreach($_content as $k => $v){
				//$contents[0]['content'].= $v['content'];
				$contents[0]['content'].= $this->waptext_model->clear_qr_code($v['content']);
			}
			$grepContentArray = $this->gl_model->pregContent($contents, $newsid);

			$newsInfo["content"] = trim(trim($grepContentArray["content"]," "),"t");
			if($grepContentArray["attribute"]){
				$grepContentArray["attribute"]['images'] = $this->waptext_model->clear_qr_code_in_attr($grepContentArray["attribute"]['images']);
				$newsInfo["attribute"] = $grepContentArray["attribute"];
			}

			//更新浏览数，30秒算一次
			$device_id = trim( $this->input->get('deviceId',true) );
			$mcKey1 = sha1('forbidden_user_pv_'.$device_id."-".$newsid);
			$is_up = $this->cache->redis->get( $mcKey1 );
			if($is_up === false){
				$this->article_model->updateArticleBrowseCount($newsid);
				$this->cache->redis->save( $mcKey1, 1, 30 );
			}

			$this->cache->redis->save ( $mcKey, json_encode($newsInfo), $expire_time );
			Util::echo_format_return(_SUCCESS_, $newsInfo);
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}

	/**
	 * 单体游戏列表
	 *  by wangbo8 2016-9-6
	 */
	public function singel_game_list()
	{
		$guid	  		= intval ( $this->input->get('guid',true) );
        $platform	  	= $this->platform;
		$expire_time = 60 * 30;
		
		
		// mc缓存
		$cache_normal_list_key = sha1('single_game_list1_normal2_' . ENVIRONMENT .$platform);
		
		$cache_normal_list = $this->cache->redis->get ( $cache_normal_list_key );
		$cache_normal_list && $cache_normal_list = json_decode($cache_normal_list, true);
		
		$cache_recommend_list_key = sha1('single_game_list1_recommend2_'  . ENVIRONMENT .$platform);
		$cache_recommend_list = $this->cache->redis->get ( $cache_recommend_list_key );
		$cache_recommend_list && $cache_recommend_list = json_decode($cache_recommend_list, true);
		
		$cache_attentioned_list_key = sha1('game_list1_attentioned_' . ENVIRONMENT .$guid.'_'.$platform);
		$cache_attentioned_list = $this->cache->redis->get ( $cache_attentioned_list_key );
		$cache_attentioned_list && $cache_attentioned_list = json_decode($cache_attentioned_list, true);

		$single_game_tmp = $this->game_model->get_single_game_id_list();
		$single_game_id_list = $single_game_tmp['id_list'];

		try {
			//游戏列表［区分平台］
			$game_id_arr = array();

			if ($cache_normal_list === false) {
				$info = $single_game_tmp['game_info_list'];

				foreach ($info as $v) {
					$game_id_arr[] = $v['id'];
				}
			}

			if ($cache_recommend_list === false) {
				$game_recommend = $this->recommend_model->get_recommend_list(1);//推荐游戏
				foreach ($game_recommend as $v) {
					$game_id_arr[] = $v['gid'];
				}
			}

			if ($cache_attentioned_list === false) {
				$infoss = $this->follow_model->get_follow_info($guid,3,-1,-1);
				$infoss || $infoss = array();
				foreach ($infoss as $v) {
					$game_id_arr[] = $v['mark'];
				}
			}

			if ($game_id_arr) {
				// 缓存中没数据
				$game_id_arr = array_unique($game_id_arr);
				$cms_game_format_info = $this->game_model->get_cms_game_list_info($game_id_arr);
			}

			if ($cache_normal_list === false) {
				$normalList = array();
				foreach ($info as $k => $v){
					//只保留单体游戏
					if(!in_array($v['id'], $single_game_id_list)){
						continue;
					}

					$cms_game_info = $cms_game_format_info[$v['id']];
					if(empty($cms_game_info['logo'])){
						continue;
					}
					$_arr = array();
					$_arr['absId'] = (string) $v['id'];
					$_arr['abstitle'] = (string) $v['abstitle'];
					$_arr['initialsEng'] = $cms_game_info['proLetters'][0] ? (string) $cms_game_info['proLetters'][0] : '';
					$_arr['absImage'] =$cms_game_info['logo'] ? $cms_game_info['logo'] : '';
					$_arr['attentionCount'] = (int) $info[$k]['attentionCount'];
					$_arr['packageURL'] =$cms_game_info['packageURL'] ? array_filter(explode("\r\n",$cms_game_info['packageURL'])) : array();//用于检测是否安装

					$normalList[] = $_arr;
				}
			} else {
				$normalList = $cache_normal_list;
			}

			if ($cache_recommend_list === false) {
				$recommend = array();
				foreach($game_recommend as $k2 => $v2){
					//只保留单体游戏
					if(!in_array($v2['gid'], $single_game_id_list)){
						continue;
					}

					$games[$k2] = $this->game_model->get_game_row($v2['gid'], $platform);
					$cms_game_info = $cms_game_format_info[$v2['gid']];
					if(empty($cms_game_info['logo'])){
						continue;
					}
					$_arr2 = array();
					$_arr2['absId'] = (string) $v2['gid'];
					$_arr2['abstitle'] = $games[$k2]['abstitle'] ? $games[$k2]['abstitle'] : '';
					$_arr2['initialsEng'] = $cms_game_info['proLetters'][0] ? (string) $cms_game_info['proLetters'][0] : '';
					$_arr2['absImage'] = $cms_game_info['logo'] ? $cms_game_info['logo'] : '';
					$_arr2['attentionCount'] = (int)$games[$k2]['attentionCount'];
					$_arr2['packageURL'] = $cms_game_info['packageURL'] ? array_filter(explode("\r\n", $cms_game_info['packageURL'])) : array();//用于检测是否安装

					$recommend[] = $_arr2;
				}
			} else {
				$recommend = $cache_recommend_list;
			}

			if ($cache_attentioned_list === false) {
				$attentionedList = array();
				foreach($infoss as $k1 => $v1){
					//只保留单体游戏
					if(!in_array($v1['mark'], $single_game_id_list)){
						continue;
					}

					$infoss[$k1] = $this->game_model->get_game_row($v1['mark'],$platform);
					$cms_game_info = $cms_game_format_info[$v1['mark']];
					if(empty($cms_game_info['logo'])){
						continue;
					}
					$_arr1 = array();
					$_arr1['absId'] = (string) $v1['mark'];
					$_arr1['abstitle'] = (string) $infoss[$k1]['abstitle'];
					$_arr1['initialsEng'] = $cms_game_info['proLetters'][0] ? (string) $cms_game_info['proLetters'][0] : '';
					$_arr1['absImage'] =$cms_game_info['logo'] ? $cms_game_info['logo'] : '';
					$_arr1['attentionCount'] = (int) $infoss[$k1]['attentionCount'];
					$_arr1['packageURL'] =$cms_game_info['packageURL'] ? array_filter(explode("\r\n",$cms_game_info['packageURL'])) : array();//用于检测是否安装－－－暂无

					$attentionedList[] = $_arr1;
				}
			} else {
				$attentionedList = $cache_attentioned_list;
			}

			$data['normalList'] =$normalList ? $normalList : array();
			$data['recommendList'] =$recommend ? $recommend : array();
			$data['attentionedList'] =$attentionedList ? $attentionedList : array();

			if ($cache_normal_list === false) {
				$this->cache->redis->save ( $cache_normal_list_key, json_encode($data['normalList']), $expire_time );
			}
			if ($cache_recommend_list === false) {
				$this->cache->redis->save ( $cache_recommend_list_key, json_encode($data['recommendList']), $expire_time );
			}
			if ($cache_attentioned_list === false) {
				$this->cache->redis->save ( $cache_attentioned_list_key, json_encode($data['attentionedList']), $expire_time );
			}
			
			Util::echo_format_return(_SUCCESS_, $data);
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}


	
}

/* End of file gl.php */
/* Location: ./application/controllers/api/gl.php */
