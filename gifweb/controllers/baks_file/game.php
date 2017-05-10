<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

/**
 * 
 * @name Game
 * @desc 攻略WAP游戏控制类
 *
 * @author	 wangbo8
 * @date 2015年12月17日
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 */
class Game extends MY_Controller {
	public function __construct() {
		parent::__construct ();

		// $_SERVER['SERVER_NAME'] = 'www.wan68.com';
		// $back_url = $url='http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
		$back_url = $url=base_url().$_SERVER["REQUEST_URI"];
		$this->smarty->assign('back_url', $back_url);

		$this->load->model('follow_model');
		$this->load->model('game_model');
		$this->load->model('recommend_model');
		$this->load->model('gl_model');
		$this->load->model('user_model');
		$this->load->model('article_model');
		$this->load->model('like_model');
	}

	//问题列表
	public function detail_info($gameId){ //首页
		$gameId = $this->global_func->filter_int($gameId);
		$guid = $this->user_id;

		try{
			//判断游戏ID
			if (empty($gameId)) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}

			//获取平台类型
			$this->platform = Util::getBrowse();	

			//获取游戏信息
			$cms_info = $this->game_model->get_cms_game_info($gameId);

			//判断
			if(empty($cms_info)){
				$this->platform = 'android';
				$cms_info = $this->game_model->get_cms_game_info($gameId);

				if(empty($cms_info)){
					$this->platform = 'ios';
					$cms_info = $this->game_model->get_cms_game_info($gameId);
				}
			}

			$cms_info = $cms_info[0];

			//判断是否有游戏信息
			if (empty($cms_info)) {
				throw new Exception('没有这个游戏', _PARAMS_ERROR_);
			}

			$platform = $this->platform;

			//查询游戏评分
			$score = $this->game_model->getRankInfo($gameId,$platform);
			if($score){
				if($platform =='android'){
					$data['score'] 			= $score['pub_score'] ? $score['pub_score']  : '' ;
				}else{
					$data['score'] 			= $score['edit_score'] ? $score['edit_score']  : '' ;
				}
			}
			if($score['pub_merit'] == null){
				$data['advantageList'] = array();
			}else{
				$score['pub_merit'] = preg_replace('/<[^>]*?>/', '||', $score['pub_merit']);
				$score['advantageList'] = explode('||', $score['pub_merit']);
				$data['advantageList'] = Util::cleanArray($score['advantageList']);
			}
			if($score['pub_demerit'] == null){
				$data['disadvantageList'] = array();
			}else{
				$score['pub_demerit'] = preg_replace('/<[^>]*?>/', '||', $score['pub_demerit']);
				$score['disadvantageList'] = explode('||', $score['pub_demerit']);
				$data['disadvantageList'] = Util::cleanArray($score['disadvantageList']);
			}
			$data['abstitle'] 		= $cms_info['title'];
			$data['initialsEng'] 	= $cms_info['proLetters'][0] ? (string) $cms_info['proLetters'][0] : '';
			$data['absImage'] 		= $cms_info['logo'] ? $cms_info['logo'] : '';
			$data['price'] 			= $cms_info['price'] ? $cms_info['price'] : '';
			$data['size'] 			= $cms_info['size'] ? $cms_info['size'] : '';
			if($cms_info['iphone1'] || $cms_info['iphone2'] || $cms_info['iphone3'] || $cms_info['iphone4'] || $cms_info['iphone5']){
				$data['screenshot'] 	= array($cms_info['iphone1'],$cms_info['iphone2'],$cms_info['iphone3'],$cms_info['iphone4'],$cms_info['iphone5']);
			}
			if($cms_info['gameTags2'] || $cms_info['gameTags3']){
				$data['type'] 			= array($cms_info['gameTags2'],$cms_info['gameTags3']);
			}
// 			$URLs ="";
// 			foreach ($cms_info["URLs"] as $k => $v){
// 				$URLs = $v;
// 			}
			$cms_info['gameIntro'] = preg_replace('/<p>/i', '', $cms_info['gameIntro']);
			$cms_info['gameIntro'] = preg_replace('/<\/p>/i', '', $cms_info['gameIntro']);
			$cms_info['gameIntro'] = preg_replace('/<br\/>/i', '', $cms_info['gameIntro']);
			$data['introduction'] 	= $cms_info['gameIntro'] ? $cms_info['gameIntro'] : '';
			$data['buyAddress'] 	= $cms_info['buyUrl'] ? $cms_info['buyUrl'] : $cms_info['buyUrl'];
			$data['packageURL'] 	= $cms_info['packageURL'] ? array_filter(explode("\r\n",$cms_info['packageURL'])) : array();
// 			$data['shareUrl'] 		= $URLs;
			// $data['shareUrl'] 		= 'http://www.wan68.com/game/detail_info/'.$gameId;
			$data['shareUrl'] 		= base_url() . 'game/detail_info/'.$gameId;
			$data['shareContent'] 	= '我在全民手游攻略给你分享，快来看看吧！';

			//判断用户是否已经关注改游戏
			$collected = $this->follow_model->is_follow($guid,3,$gameId);
			$data['attentioned'] =$collected ? true : false ;//当前用户是否已关注该游戏
			$data['absId'] 		= $gameId;
			$data['guid'] = $guid;
			$data['gameId'] = $gameId;
		    $this->smarty->assign('data', $data);

			//拼装seo信息
			$seotitle = $data['abstitle'] . "_" . $data['abstitle'] . '专区_全民手游攻略';
			$seokeywords .= $data['abstitle'];
			$seodescription = $this->global_func->cut_str(strip_tags($data['introduction']),200);
			$seo = array(
					'title' => $seotitle,
					'keywords' => trim($seokeywords, ','),
					'description' => $seodescription
			);
			$this->smarty->assign('seo', $seo);
		    $this->smarty->view ( 'zq/zq-xydetail.tpl' );
			//Util::echo_format_return(_SUCCESS_, $data);
			//exit;
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}

	}
}
