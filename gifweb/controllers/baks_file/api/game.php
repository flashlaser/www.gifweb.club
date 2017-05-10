<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * API-游戏内容操作
 */
class Game extends MY_Controller
{

	public function __construct()
	{
		parent::__construct();
//		ini_set("dispay_errors",1);
//		error_reporting(E_ALL);

		$this->load->model('game_model');
		$this->load->model('game_other_model');
		$this->load->model('answer_model');
		$this->load->model('question_model');
		$this->load->model('user_model');
		$this->load->model('gl_model');
		$this->load->model('recommend_model');
		$this->load->model('article_model');
	}

	/**
	 * 游戏推荐列表
	 *
	 */
	public function game_recommend_list()
	{
		$mark	  		= trim ( $this->input->get('mark',true) );
		try {
			//游戏列表［区分平台］
			$info = $this->game_model->get_recommend_game_list($mark);
			$infos= array();

			$n = 1;
			foreach ($info as $k => $v){
				if($n == 11){
					break;
				}
				$returnInfo = $this->game_model->get_cms_game_info($v['id']);
				if(empty($returnInfo[0]['logo'])){
					continue;
				}
				$_arr = array();
				$_arr['absId'] = (string) $v['id'];
				$_arr['abstitle'] =$v['game_name'] ? $v['game_name'] : '';
				$_arr['initialsEng'] =$returnInfo[0]['proLetters'][0] ? (string) $returnInfo[0]['proLetters'][0] : '';
				$_arr['absImage'] =$returnInfo[0]['logo'] ? $returnInfo[0]['logo'] : '';
				$_arr['attentionCount'] = (int) $info[$k]['attention_count'];
				$_arr['packageURL'] =$returnInfo[0]['packageURL'] ? array_filter(explode("\r\n",$returnInfo[0]['packageURL'])) : array();//用于检测是否安装
				$infos[] = $_arr;
				$n++;
			}

			Util::echo_format_return(_SUCCESS_, $infos);
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}


	/**
	 * 游戏专区信息 wiki 50
	 *
	 */
	public function game_prefecture_info()
	{
		$gameId	  	= trim ( $this->input->get('gameId',true) );
		$page	  	= $this->input->get('page',true) ? $this->input->get('page',true) : 1;
		$count	  	= $this->input->get('count',true) ? $this->input->get('count',true) : 10;
		$max_id	  	= trim ( $this->input->get('max_id',true) );

		// modify on version 2.5
		if ($this->version >= '2.5') {
			$count = 1;
		}

		$page < 1 && $page = 1;
		$offsize = ($page - 1) * $count;

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
			if (empty($info_a)) {
				throw new Exception('没有这个游戏', _PARAMS_ERROR_);
			}
			//［二级分类］
			$info_b = $this->gl_model->get_category_list($info_a['id']);
			//［三级分类］
			if(!empty($info_b) || $info_b != array()){
				foreach ($info_b as $k => $v ){
					$info_c = $this->gl_model->get_category_list($v['id']);

					if($info_c){
						if(count($info_c) >=2){
							array_unshift($info_c,array('absId'=>$v['absId'],'abstitle'=>'全部'));
						}
						$info_b[$k]['item'] = $info_c;
					}
					unset($info_b[$k]['id']);
				}
			}
			$raidersClassList = $info_b;
			unset($raidersClassList['attentionCount']);
			unset($raidersClassList['web_url']);

			//快捷对象数据
			$kuaijie_recommend = $this->recommend_model->get_recommend_list(3,$gameId);//推荐游戏

			$kuaijieRecommend =array();
			if($kuaijie_recommend){
				foreach ($kuaijie_recommend as $k_1 => $v_1) {
					if ($v_1['type'] == 1) {
						$kuaijieRecommend[$k_1]['type'] = 2;
					} elseif ($v_1['type'] == 2) {
						$kuaijieRecommend[$k_1]['type'] = 0;
					} elseif ($v_1['type'] == 3) {
						$kuaijieRecommend[$k_1]['type'] = 1;
					} elseif ($v_1['type'] == 4) {
						$kuaijieRecommend[$k_1]['type'] = 3;
					} elseif ($v_1['type'] == 5) {
						$kuaijieRecommend[$k_1]['type'] = 4;
					} elseif ($v_1['type'] == 6) {
						$kuaijieRecommend[$k_1]['type'] = 5;
					}
					$kuaijieRecommend[$k_1]['abstitle'] = $v_1['title'];
					if ($v_1['type'] == '4' || $v_1['type'] == '5' || $v_1['type'] == '6' || $v_1['type'] == '7') {
						$kuaijieRecommend[$k_1]['absId'] = $v_1['param'];
					} else {
						$kuaijieRecommend[$k_1]['webUrl'] = $v_1['param'];
					}
					$kuaijieRecommend[$k_1]['absImage'] = gl_img_url($v_1['img']);

				}
			}

			//推荐列表
			$jiaodian_recommend = $this->recommend_model->get_recommend_list(4,$gameId,$offsize,$count,$max_id);
			$jiaodianRecommend =array();
			if($jiaodian_recommend){
				foreach ($jiaodian_recommend as $k => $v) {
					if ($v['type'] == 1) {
						$jiaodianRecommend[$k]['type'] = 2;
					} elseif ($v['type'] == 2) {
						$jiaodianRecommend[$k]['type'] = 0;
					} elseif ($v['type'] == 3) {
						$jiaodianRecommend[$k]['type'] = 1;
					} elseif ($v['type'] == 4) {
						$jiaodianRecommend[$k]['type'] = 3;
					} elseif ($v['type'] == 5) {
						$jiaodianRecommend[$k]['type'] = 4;
					} elseif ($v['type'] == 6) {
						$jiaodianRecommend[$k]['type'] = 5;
					}
					$jiaodianRecommend[$k]['abstitle'] = $v['title'];
					if ($v['type'] == '4' || $v['type'] == '5' || $v['type'] == '6') {
						$jiaodianRecommend[$k]['absId'] = $v['param'];
					} else {
						$jiaodianRecommend[$k]['updateTime'] = date("Y-m-d H:i:s" , $v['create_time']);
						$jiaodianRecommend[$k]['webUrl'] = $v['param'];
					}
					if($jiaodianRecommend[$k]['type'] == 5){
						$cms[$k] = $this->game_model->get_cms_info($v['param']);
						$jiaodianRecommend[$k]['authorName'] = $cms[$k][0]['author'] ? $cms[$k][0]['author'] : '';

						$article_info[$k] = $this->article_model->findArticleData($v['param']);
						$jiaodianRecommend[$k]['count'] = ($article_info[$k]['virtual_browse_count']+$article_info[$k]['virtual_browse_count']) ? (int) ($article_info[$k]['virtual_browse_count']+$article_info[$k]['virtual_browse_count']) : 0;
                        $jiaodianRecommend[$k]['praiseCount'] =($article_info[$k]['mark_up_count']+$article_info[$k]['virtual_mark_up_count']) ? (int) ($article_info[$k]['mark_up_count']+$article_info[$k]['virtual_mark_up_count']) : 0;
					}
					if($jiaodianRecommend[$k]['type'] == 3 ){
						$question_info[$k] = $this->question_model->get_info($v['param'],array(0,1,2,3));
						$user_info[$k] = $this->user_model->getUserInfoById($question_info[$k]['uid']);
						$jiaodianRecommend[$k]['authorName'] = $user_info[$k]['nickname'] ? $user_info[$k]['nickname'] : '';
						$jiaodianRecommend[$k]['count'] = (int)$this->answer_model->get_normal_answer_count($v['param']);
					}
					if($jiaodianRecommend[$k]['type'] == 4 ){
						$answer_info[$k] = $this->answer_model->get_info($v['param'],array(0,1,2,3));
						$question_info[$k] = $this->question_model->get_info($answer_info[$k]['qid'],array(0,1,2,3));
						$user_info[$k] = $this->user_model->getUserInfoById($question_info[$k]['uid']);
						$jiaodianRecommend[$k]['authorName'] = $user_info[$k]['nickname'] ? $user_info[$k]['nickname'] : '';
						$jiaodianRecommend[$k]['count'] = (int)$this->answer_model->get_normal_answer_count($answer_info[$k]['qid']);
					}
					// $jiaodianRecommend[$k]['absImage'] = 'http://store.games.sina.com.cn/'.$v['img'];
					$jiaodianRecommend[$k]['absImage'] = gl_img_url($v['img']);
				}
			}
//             if($gameId == '391' || $gameId == '1103' || $gameId == '6331' || $gameId == '6988' || $gameId == '7230' || $gameId == '6725'){
                //单游戏官方活动
                $activties_info = $this->recommend_model->get_recommend_list(8,$gameId);
                $activties =array();
                if($activties_info){
                    foreach ($activties_info as $k => $v) {
    					if ($v['type'] == 1) {
    						$activties[$k]['type'] = 2;
    					} elseif ($v['type'] == 2) {
    						$activties[$k]['type'] = 0;
    					} elseif ($v['type'] == 3) {
    						$activties[$k]['type'] = 1;
    					} elseif ($v['type'] == 4) {
    						$activties[$k]['type'] = 3;
    					} elseif ($v['type'] == 5) {
    						$activties[$k]['type'] = 4;
    					} elseif ($v['type'] == 6) {
    						$activties[$k]['type'] = 5;
    					}
    					if ($v['type'] == '4' || $v['type'] == '5' || $v['type'] == '6' || $v['type'] == '7') {
    						$activties[$k]['absId'] = $v['param'];
    					} else {
    						$activties[$k]['webUrl'] = $v['param'];
    					}
                        $activties[$k]['abstitle'] = $v['title'];
					    $activties[$k]['absImage'] = gl_img_url($v['img']);
					   // $activties[$k]['webUrl'] = $v['param'];
                    }
                    $data['activitiesList'] =$activties;
                }
                //单游戏视频推荐
                $video_info = $this->recommend_model->get_recommend_list(7,$gameId);
                $videos =array();
                if($video_info){
                    foreach ($video_info as $k => $v) {
    					if ($v['type'] == 1) {
    						$videos[$k]['type'] = 2;
    					} elseif ($v['type'] == 2) {
    						$videos[$k]['type'] = 0;
    					} elseif ($v['type'] == 3) {
    						$videos[$k]['type'] = 1;
    					} elseif ($v['type'] == 4) {
    						$videos[$k]['type'] = 3;
    					} elseif ($v['type'] == 5) {
    						$videos[$k]['type'] = 4;
    					} elseif ($v['type'] == 6) {
    						$videos[$k]['type'] = 5;
    					}
    					if ($v['type'] == '4' || $v['type'] == '5' || $v['type'] == '6' || $v['type'] == '7') {
    						$videos[$k]['absId'] = $v['param'];
    					} else {
    						$videos[$k]['webUrl'] = $v['param'];
    					}
                       $videos[$k]['abstitle'] = $v['title'];
                        // $videos[$k]['absImage'] = 'http://store.games.sina.com.cn/'.$v['img'];
                        $videos[$k]['absImage'] = gl_img_url($v['img']);
//                         $videos[$k]['webUrl'] = $v['param'];
                    }
                    $data['videoList'] =$videos;
                }
//             }

			// modify on version 2.5
			if ($this->version >= '2.5') {
				$kuaijieRecommend = array_slice($kuaijieRecommend, 0, 4);
				(count($kuaijieRecommend) % 2 == 0) || array_pop($kuaijieRecommend);
			}

			$data['gameId'] = $gameId;
			$data['abstitle'] = $info_a['abstitle'];
			$data['raidersClassList'] =$raidersClassList;
			$data['shortcutList'] =$kuaijieRecommend;
			$data['recommendList'] =$jiaodianRecommend;

			Util::echo_format_return(_SUCCESS_, $data);
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}

	/**
	 * 游戏详情
	 *
	 */
	public function detail_info()
	{
		$gameId	  = trim ( $this->input->get('gameId',true) );

        try {
        	if (empty($gameId)) {
        		throw new Exception('参数错误', _PARAMS_ERROR_);
        	}
			$cms_info = $this->game_model->get_cms_game_info($gameId);
			$cms_info = $cms_info[0];
			if($cms_info){
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
// 				$URLs ="";
// 				foreach ($cms_info["URLs"] as $k => $v){
// 					$URLs = $v;
// 				}
				$cms_info['gameIntro'] = preg_replace('/<p>/i', '', $cms_info['gameIntro']);
				$cms_info['gameIntro'] = preg_replace('/<\/p>/i', '', $cms_info['gameIntro']);
				$cms_info['gameIntro'] = preg_replace('/<br\/>/i', '', $cms_info['gameIntro']);
				$data['introduction'] 	= $cms_info['gameIntro'] ? $cms_info['gameIntro'] : '';
				$data['buyAddress'] 	= $cms_info['buyUrl'] ? $cms_info['buyUrl'] : $cms_info['buyUrl'];
				$data['packageURL'] 	= $cms_info['packageURL'] ? array_filter(explode("\r\n",$cms_info['packageURL'])) : array();
				$data['shareUrl'] 		= 'http://www.wan68.com/game/detail_info/'.$gameId;
// 			    $data['shareUrl'] 		= $URLs;
				$data['shareContent'] 	= '我在全民手游攻略给你分享，快来看看吧！';
				Util::echo_format_return(_SUCCESS_, $data);
			}else{
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}

        }catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}

	/**
	 * 游戏详情
	 *
	 */
	public function other_game_save()
	{
		$name	  = trim ( $this->input->get('name',true) );

		try {
			if (empty($name)) {
				throw new Exception('游戏名称不能为空', _PARAMS_ERROR_);
			}
			$otherGameInfo = $this->game_other_model->get_info($name);
			if($otherGameInfo['id']){
				//update
				$this->game_other_model->updateData($otherGameInfo['id'],1);
			}else{
				//insert
				$data = array();
				$data['game_name'] = $name;
				$data['add_num'] = 1;
				$data['create_time'] = time();

				$this->game_other_model->insertData($data);
			}
			Util::echo_format_return(_SUCCESS_, '');

		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}

	/**
	 * 获取iOS Spotlight搜索推荐数据（1.2新增）
	 *
	 */
	public function ios_spotlight_search()
	{

	    $gameId	  = trim ( $this->input->get('gameId',true) );
	    try {
	        $search_recommend = $this->recommend_model->get_recommend_list(9,$gameId);
	        $searchRecommend =array();
	        if($search_recommend){
	            foreach ($search_recommend as $k_1 => $v_1) {
	                if ($v_1['type'] == 1) {
	                    $searchRecommend[$k_1]['type'] = 2;
	                } elseif ($v_1['type'] == 2) {
	                    $searchRecommend[$k_1]['type'] = 0;
	                } elseif ($v_1['type'] == 3) {
	                    $searchRecommend[$k_1]['type'] = 1;
	                } elseif ($v_1['type'] == 4) {
	                    $searchRecommend[$k_1]['type'] = 3;
	                } elseif ($v_1['type'] == 5) {
	                    $searchRecommend[$k_1]['type'] = 4;
	                } elseif ($v_1['type'] == 6) {
	                    $searchRecommend[$k_1]['type'] = 5;
	                } elseif ($v_1['type'] == 7) {
	                    $searchRecommend[$k_1]['type'] = 6;
	                }
	                $searchRecommend[$k_1]['abstitle'] = $v_1['title'];
	                if ($v_1['type'] == '4' || $v_1['type'] == '5' || $v_1['type'] == '6' || $v_1['type'] == '7') {
	                    $searchRecommend[$k_1]['param'] = $v_1['param'];
	                } else {
	                    $searchRecommend[$k_1]['param'] = $v_1['param'];
	                }
	                $searchRecommend[$k_1]['expirationDate'] = $v_1['expiration_time'] ? date('Y-m-d H:i:s' ,$v_1['expiration_time']) : '';
	                $searchRecommend[$k_1]['description'] = $v_1['description'];
	                // if($v_1['img'] && $v_1['img'] !='http://store.games.sina.com.cn/' && $v_1['img'] !='http://store.games.sina.com.cn'){
	                //    $searchRecommend[$k_1]['absImage'] = 'http://store.games.sina.com.cn/'.$v_1['img'];
	                // }
					$searchRecommend[$k_1]['absImage'] = gl_img_url($v_1['img']);
	            }
	        }
	        $data =$searchRecommend;
	        Util::echo_format_return(_SUCCESS_, $data);
	    }catch (Exception $e) {
	        Util::echo_format_return($e->getCode(), array(), $e->getMessage());
	    }
	}




}

/* End of file game.php */
/* Location: ./application/controllers/api/game.php */
