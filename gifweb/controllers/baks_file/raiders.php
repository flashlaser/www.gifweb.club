<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

/**
 * 
 * @name Raiders
 * @desc 攻略WAP攻略控制类
 *
 * @author	 wangbo8
 * @date 2015年12月18日
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 */
class Raiders extends MY_Controller {
	public function __construct() {
		parent::__construct ();

		// $_SERVER['SERVER_NAME'] = 'www.wan68.com';
		// $back_url = $url='http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
		$back_url = $url=base_url() .$_SERVER["REQUEST_URI"];
		$this->smarty->assign('back_url', $back_url);

		$this->load->model('follow_model');
		$this->load->model('game_model');
		$this->load->model('recommend_model');
		$this->load->model('gl_model');
		$this->load->model('user_model');
		$this->load->model('article_model');
		$this->load->model('like_model');
		$this->load->model('waptext_model');
	}

	//攻略详情信息
	public function info($newsid, $gid = NULL){ //首页
		$res = $this->global_func->inject_check($newsid);
		$gid = $this->global_func->filter_int($gid);
		if($res){
			exit('分类参数含有非法字符');
		}
		$newsid = trim($newsid);
		$guid = $this->user_id;

		if($guid < 1){
			$expire_time = 60 * 10;
		}else{
			$expire_time = 60 * 1;
		}

		// mc缓存
		$mcKey = sha1('detail_info_' . ENVIRONMENT .$newsid.'_'.$guid);
		$newsInfo = $this->cache->redis->get ( $mcKey );
		$newsInfo && $newsInfo = json_decode($newsInfo , true);
		if($newsInfo == false || empty($newsInfo)){
			$data = array();
			try{
				//判断攻略ID
				if (empty($newsid)) {
					throw new Exception('参数错误', _PARAMS_ERROR_);
				}

				//获取攻略信息
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

// 				$URLs ="";
// 				foreach ($_newsInfos["URLs"] as $k => $v){
// 					$URLs = $v;
// 				}

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
// 				$newsInfo["shareUrl"] 	= $URLs ? $URLs : '';
			    // $newsInfo["shareUrl"] 	= 'http://www.wan68.com/raiders/info/'.$newsid;
			    $newsInfo["shareUrl"] 	= base_url() . 'raiders/info/'.$newsid;
				$newsInfo["shareContent"] 	= '我在全民手游攻略给你分享，快来看看吧！';
				$newsInfo["commentCount"] = (int) $artilce['comment_count'];//评论数
				$newsInfo["content"] = $_newsInfos["content"] ? $_newsInfos["content"] : '';

				/*
				//相关新闻处理
				$newsInfo["relateNews"] = array();
				if (!empty($_newsInfos['relNews'])) {
					foreach ($_newsInfos['relNews'] as $_k => $_v) {
						$newsInfo["relateNews"][] = array("absId" => trim($_v['id']), "abstitle" => trim($_v['title']));
					}
				}*/

				//=====================为了图片增加开始=========
				$_content = $_newsInfos["content"];
				$contents=array();
				foreach($_content as $k => $v){
					$contents[0]['content'].=$v['content'];
				}

				$grepContentArray = $this->gl_model->pregContent($contents, $newsid);
				if($grepContentArray["attribute"]){
					$img_attribute = $grepContentArray["attribute"];
				}

				//判断分享图片
				if(!empty($img_attribute['images']) && $img_attribute['images'][0]['url']){
					//得到当前图片
					$share_pic_url = $img_attribute['images'][0]['url'];
				}else{
					// $share_pic_url = 'http://www.wan68.com/gl/static/images/foot_logo.png';
					$share_pic_url = baes_url() . 'gl/static/images/foot_logo.png';
				}
				$newsInfo["share_imgurl"] = urlencode($share_pic_url);
				//=====================为了图片增加结束=========

				if($this->global_func->isMobile()){
					//判断设备
					if(preg_match('/(iPhone|iPad)/i', $_SERVER['HTTP_USER_AGENT'])){
						$device = 'ios';
					}else{
						$device = 'android';
					}

					//调用移动端处理model来获取数据
					$wap_result = $this->waptext_model->get_cms_info($newsid, $device);

					if($wap_result['msg'] == 1){ //获取成功
						$contents = $wap_result['html'];
					}else{ //获取数据失败
						throw new Exception('数据不存在', _PARAMS_ERROR_);
					}
				}else {
					//PC端数据处理办法
					$_content = $_newsInfos["content"];

					$contents=array();
					foreach($_content as $k => $v){
						$contents[0]['content'].=$v['content'];
					}

		   			//增加CMS处理内容正则替换方法
		   			$contents = $contents[0]['content'];
					$contents = $this->waptext_model->Cms_TagReplace($contents);
				}

				$newsInfo["content"] = $contents;
				$newsInfo["newsid"] = $newsid;
				//$newsInfo['askgid'] = $gid;exit;

				//通过cms获取游戏ID
				$mcKey1 = sha1('gameidBycmsId' . ENVIRONMENT .$newsid . ":wap");
				$gameid_arr = $this->cache->redis->get ( $mcKey1 );
				$gameid_arr & $gameid_arr = json_decode($gameid_arr, true);
				if($gameid_arr === false){
					$gameid_arr = $this->article_model->findgameidBycmsId($newsid);
					$this->cache->redis->save ( $mcKey1, json_encode($gameid_arr), $expire_time );
				}

				$newsInfo['gameId'] = $gameid_arr['gameid'];
				$newsInfo['askgid'] = $gameid_arr['gameid'];
				$this->cache->redis->save ( $mcKey, json_encode($newsInfo), $expire_time );
			}catch (Exception $e) {
				Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			}
		}

		//用户对攻略内容操作（单独提出来，防止缓存影响用户体验）
		//该用户是否已赞、踩
		$praised = $this->like_model->is_like($newsid,1);

		//该用户是否已踩
		$treaded = $this->like_model->is_like($newsid,2);

		//该用户是否已收藏
		$collected = $this->follow_model->is_follow($guid,1,$newsid);

		$newsInfo["praised"] = $praised ? true : false;//是否赞
		$newsInfo["treaded"] = $treaded ? true : false;//是否踩
		$newsInfo["collected"] = $collected ? true : false;//是否收藏

		// Util::echo_format_return(_SUCCESS_, $newsInfo);
		// exit;
		$newsInfo['guid'] = $guid;

		//分享地址
		$newsInfo["qshareurl"] = base_url () . "raiders/info/" . $newsid;

		//用户禁止判断
		$res = $this->common_model->is_ban_user();
		if($res){
			$newsInfo['is_ban'] = 1;
		}else{
			$newsInfo['is_ban'] = 0;
		}

		//拼装seo信息
		$seotitle = $newsInfo['abstitle'] . '_全民手游攻略';
		$seokeywords .= $newsInfo['abstitle'] . "攻略";
		$seodescription = $this->global_func->cut_str(strip_tags($newsInfo['content']),200);
		$seo = array(
				'title' => $seotitle,
				'keywords' => trim($seokeywords, ','),
				'description' => $seodescription
		);
		$newsInfo['navflag'] = 'zq';
		$this->smarty->assign('seo', $seo);
		//Util::echo_format_return(_SUCCESS_, $newsInfo);
		//exit;
	    $this->smarty->assign('data', $newsInfo);
	    $this->smarty->view ( 'zq/zq-glzw.tpl' );
	}

	/*
	 * 精确时间间隔函数
	 * $time 发布时间 如 1356973323
	 * $str 输出格式 如 Y-m-d H:i:s
	 * 半年的秒数为15552000，1年为31104000，此处用半年的时间
	 */
	function from_time($time,$str='m-d'){
	    isset($str)?$str:$str='m-d';
	    $way = time() - $time;
	    $r = '';
	    if($way < 60){
	        $r = '刚刚';
	    }elseif($way >= 60 && $way <3600){
	        $r = floor($way/60).'分钟前';
	    }elseif($way >=3600 && $way <86400){
	        $r = floor($way/3600).'小时前';
	    }elseif($way >=86400 && $way <2592000){
	        $r = date($str,$time);
	    }elseif($way >=2592000 && $way <15552000){
	        $r = date($str,$time);
	    }else{
	        $r = date('Y-m-d H:i:s',$time);
	    }
	    return $r;
	}

}
