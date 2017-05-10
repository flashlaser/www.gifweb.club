<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

/**
 *
 * @name Qa
 * @desc null
 *
 * @author	 liule1
 * @date 2015年8月4日
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 *
 * @property	global_func		$global_func
 * @property	qa_model		$qa_model
 * @property	question_model	$question_model
 * @property	answer_model	$answer_model
 * @property	user_model		$user_model
 */
class Qa extends MY_Controller {
	public function __construct() {
		parent::__construct ();
		$this->load->model('user_model');
		$this->load->model('qa_model');
		$this->load->model('article_model');
		$this->load->model('like_model');
		$this->load->model('answer_model');
		$this->load->model('answer_content_model');
		$this->load->model('recommend_model');
		$this->load->model('question_model');
		$this->load->model('question_content_model');
		$this->load->model('qa_image_model');
		$this->load->model('game_model');
		$this->load->model('User_redis_model', 'uredis');
	}

	public function question_list() {
		$gids = ( string ) $this->input->get_post ( 'gids', true );
		$page = ( int ) $this->input->get_post ( 'page', true );
		$page_size = ( int ) $this->input->get_post ( 'count', true );

		try {
			if (empty($page) || empty($page_size) ) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}

			$page < 1 && $page = 1;
			$offsize = ($page - 1) * $page_size;

			$data = $this->qa_model->get_question_list($gids, $offsize, $page_size);

			//
			Util::echo_format_return(_SUCCESS_, $data);
			return 1;
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}


	public function question_recommend_list() {
		$page = ( int ) $this->input->get_post ( 'page', true );
		$page_size = ( int ) $this->input->get_post ( 'count', true );
		$max_id = ( int ) $this->input->get_post ( 'max_id', true );

		$expire_time = 60 * 10;

		// mc缓存
		// $mcKey = sha1('question_recommend_list' . ENVIRONMENT .$page.'_'.$page_size);
		// $data = $this->cache->memcached->get ( $mcKey );
		// if($data == false || empty($data)){
		//     $data = array();
		// }else{
		//     Util::echo_format_return(_SUCCESS_, $data);
		//     die();
		// }
		try {
			if (empty($page) || empty($page_size) ) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}

			$page < 1 && $page = 1;
			$offsize = ($page - 1) * $page_size;

			//问答列表
			$qa_list = $this->recommend_model->get_recommend_list( 6,0,$offsize, $page_size,$max_id);

			foreach ($qa_list as $k => $v) {
				$answer_info = $this->answer_model->get_info($v['param'],array(0,1));
				if(empty($answer_info['qid'])){
					continue;
				}
				$question_info = $this->question_model->get_info($answer_info['qid'],array(0,1));
				if(empty($question_info['qid'])){
					continue;
				}
				//问答详情
				$answer_content_info = $this->answer_content_model->get_content($answer_info['aid']);
				$question_content_info = $this->question_content_model->get_content($answer_info['qid']);
				$question_img_count = $this->qa_image_model->get_list_count(1,$answer_info['qid']);

				//游戏信息
				$game_info = $this->game_model->get_cms_game_info($question_info['gid'],2);
				$game_info = $game_info[0];
				//用户信息
				$answer_user_info = $this->user_model->getUserInfoById($answer_info['uid']);
				$questionList =array();
				$questionList['absId'] 			= (string)$question_info['qid'];
				$questionList['abstitle'] 		= $question_content_info ? $this->qa_model->convert_content_to_frontend($question_content_info, false, true) : '';
				$questionList['imageCount'] 	= $question_img_count;
				$questionList['answerCount'] 	= $question_info['normal_answer_count'] + $question_info['hot_answer_count'];
				if($question_info['gid'] == 2031){
					$questionList['gameInfo']['absId'] 		= '2031';
					$questionList['gameInfo']['abstitle'] 	= $question_info['gname'];
					$questionList['gameInfo']['absImage'] 	= '';
				}else{
					$questionList['gameInfo']['absId'] 		= (string)$question_info['gid'];
					$questionList['gameInfo']['abstitle'] 	= $game_info['title'] ? (string)$game_info['title'] : '';
					$questionList['gameInfo']['absImage'] 	= $game_info['logo'] ? (string)$game_info['logo'] : '';
				}


				$questionList['answerList'][0]['absId'] = (string)$answer_info['aid'];
				$questionList['answerList'][0]['abstitle'] = $answer_content_info ? $this->qa_model->convert_content_to_frontend($answer_content_info, false, true) : '';
				$questionList['answerList'][0]['agreeCount'] = $answer_info['mark_up_rank_0_count'] + $answer_info['mark_up_rank_1_count'] + $answer_info['mark_up_virtual_count'];
				$questionList['answerList'][0]['author']['guid'] = (string)$answer_info['uid'];
				$questionList['answerList'][0]['author']['nickName'] = (string)$answer_user_info['nickname'];
				$questionList['answerList'][0]['author']['headImg'] = (string)$answer_user_info['avatar'];
				$q_infos[] = $questionList;
			}

			//焦点对象数据［区分平台］
			$jiaodian_recommend = $this->recommend_model->get_recommend_list(5);

			$jiaodianRecommend =array();
			foreach ($jiaodian_recommend as $k_1 => $v_1) {
				if ($v_1['type'] == 1) {
					$jiaodianRecommend[$k_1]['type'] = 2;
				} elseif ($v_1['type'] == 2) {
					$jiaodianRecommend[$k_1]['type'] = 0;
				} elseif ($v_1['type'] == 3) {
					$jiaodianRecommend[$k_1]['type'] = 1;
				} elseif ($v_1['type'] == 4) {
					$jiaodianRecommend[$k_1]['type'] = 3;
				} elseif ($v_1['type'] == 5) {
					$jiaodianRecommend[$k_1]['type'] = 4;
				} elseif ($v_1['type'] == 6) {
					$jiaodianRecommend[$k_1]['type'] = 5;
				} elseif ($v_1['type'] == 7) {
					$jiaodianRecommend[$k_1]['type'] = 6;
				}
				$jiaodianRecommend[$k_1]['abstitle'] = $v_1['title'];
				if ($v_1['type'] == '4' || $v_1['type'] == '5' || $v_1['type'] == '6' || $v_1['type'] == '7') {
					$jiaodianRecommend[$k_1]['absId'] = $v_1['param'];
				} else {
					$jiaodianRecommend[$k_1]['webUrl'] = $v_1['param'];
				}
				if($v_1['type'] == '7'){
					$game_infos = $this->game_model->get_cms_game_info($v_1['param']);
					$game_infos = $game_infos[0];

					$jiaodianRecommend[$k_1]['abstitle'] = $game_infos['title'];
				}
				// $jiaodianRecommend[$k_1]['absImage'] = 'http://store.games.sina.com.cn/'.$v_1['img'];
				$jiaodianRecommend[$k_1]['absImage'] = gl_img_url($v_1['img']);

			}

			$data['focusList'] =$jiaodianRecommend ? $jiaodianRecommend : array();

			$data['newList'] =$q_infos ? $q_infos : array();

			// $this->cache->memcached->save ( $mcKey, $data, $expire_time );
			Util::echo_format_return(_SUCCESS_, $data);
			return 1;
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}

	public function question_info() {
		$uid = $this->user_id;
		$qid = ( int ) $this->input->get_post ( 'absId', true );

		try {
			if (empty($qid) ) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}


			$data = $this->qa_model->get_question_info($uid, $qid);
			if (empty($data)) {
				throw new Exception('问题已关闭', _DATA_ERROR_);
			}


			// 同一设备同一问题30秒算一次pv
			$device_id = $this->input->get_post('deviceId');
			$mc_cache_key = sha1("glapp_question_pv_{$device_id}_{$qid}");
			if (!$this->cache->redis->get($mc_cache_key)) {
				$this->question_model->add_pv_count($qid,1);
				$this->cache->redis->save($mc_cache_key, 1, 30);
				// 记录浏览问题权重计算
				$this->uredis->recordQuestionHot($qid, 1, $data['author']['medalLevel']);
			}

			Util::echo_format_return(_SUCCESS_, $data);
			return 1;
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}
	public function question_save() {
		$uid = $this->user_id;
// 		$action = ( int ) $this->input->get_post ( 'action', true );
		$qid = ( int ) $this->input->get_post ( 'absId', true );
		$gid = ( int ) $this->input->get_post ( 'gameId', true );
		$game_name = ( string ) $this->input->get_post ( 'gameName', true );
		$content = ( string ) $this->input->get_post ( 'content', false, true );

		try {
// 			if (empty($uid) || ($action && !$qid) || empty($content) || empty($gid) || empty($game_name)) {
			if (empty($uid) || empty($content) || empty($gid) || empty($game_name)) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}

			if ($this->common_model->is_ban_user()) {
				throw new Exception(_BANNED_MSG_, _USER_BANNED_);
			}

			$content = $this->common_model->filter_content($content);
			if (!$this->qa_model->convert_content_to_frontend($content, 0, 1) ) {
				throw new Exception('您可能输入了非法字符，请修改！', _PARAMS_ERROR_);
			}

			$msg = $qid ? '编辑成功' : '发布成功';

			$this->common_model->trans_begin();
			$save_info = $this->qa_model->question_save($uid, $qid, $gid, $game_name);
			$qid = (!empty($save_info) && $save_info['qid']) ? $save_info['qid'] : 0;
			if (!$qid) {
				throw new Exception('失败', _DATA_ERROR_);
			}

			$frontend_imgages_id = $this->qa_model->question_content_save($uid, $qid, $content);
			if ($frontend_imgages_id === false) {
				throw new Exception('失败', _DATA_ERROR_);
			}

			$this->common_model->trans_commit();

			// 默认关注
			if ($action == 0) $this->follow_model->follow($uid, 4, $qid, 1);	// 新增问题自动关注


			// 搜索
			$this->load->model('search_model');
			$this->search_model->updateEsDataFromDb($qid, 'question');

			$return = array(
					'absId' => (string)$qid,
					'image' => $frontend_imgages_id,
					'score' => $save_info['add_exp'],
			);
			Util::echo_format_return(_SUCCESS_, $return, $msg);
			return 1;
		} catch (Exception $e) {
			$this->common_model->trans_rollback();
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}


	// ------------------------------------------------------------------------------------------------------------------------//
	public function answer_list() {
		$qid = ( int ) $this->input->get_post ( 'absId', true );
		$page = ( int ) $this->input->get_post ( 'page', true );
		$page_size = ( int ) $this->input->get_post ( 'count', true );
		$last_id = ( int ) $this->input->get_post ( 'max_id', true );

		try {
			if (empty($page) || empty($page_size) ) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}

			$page < 1 && $page = 1;
			$offsize = ($page - 1) * $page_size;

			$data = $this->qa_model->get_answer_list($qid, $offsize, $page_size, $last_id);

			//
			Util::echo_format_return(_SUCCESS_, $data);
			return 1;
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}
	public function answer_info() {
		$uid = $this->user_id;
		$aid = ( int ) $this->input->get_post ( 'absId', true );

		try {
			if (empty($aid) ) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}


			$data = $this->qa_model->get_answer_info($uid, $aid);

			if (empty($data)) {
				throw new Exception('答案已关闭或已删除', _DATA_ERROR_);
			}

			Util::echo_format_return(_SUCCESS_, $data);
			return 1;
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}
	public function answer_save() {
		$uid = $this->user_id;
// 		$action = ( int ) $this->input->get_post ( 'action', true );
		$aid = ( int ) $this->input->get_post ( 'absId', true );
		$qid = ( int ) $this->input->get_post ( 'questionId', true );
		$content = ( string ) $this->input->get_post ( 'content', false, true );

		try {
// 			if (empty($uid) || empty($qid) || ($action && !$aid) || empty($content) ) {
			if (empty($uid) || empty($qid) || empty($content) ) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}

			if ($this->common_model->is_ban_user()) {
				throw new Exception(_BANNED_MSG_, _USER_BANNED_);
			}


			$content = $this->common_model->filter_content($content);
			if (!$this->qa_model->convert_content_to_frontend($content, 0, 1) ) {
				throw new Exception('您可能输入了非法字符，请修改！', _PARAMS_ERROR_);
			}

			$msg = $aid ? '编辑成功' : '发布成功';
			$this->common_model->trans_begin();


			$save_info = $this->qa_model->answer_save($uid, $aid, $qid);
			$aid = (!empty($save_info) && $save_info['aid']) ? $save_info['aid'] : 0;
			if (!$aid) {
				throw new Exception('失败', _DATA_ERROR_);
			}
			$frontend_imgages_id = $this->qa_model->answer_content_save($uid, $aid, $content);
			if ($frontend_imgages_id === false) {
				throw new Exception('失败', _DATA_ERROR_);
			}

			$return = array(
					'absId' => $aid,
					'image' => $frontend_imgages_id,
					'score' => $save_info['add_exp'],
			);

			// 记录被回答次数，更改用户是否为大神
			$this->uredis->cache_answer_num($qid, $uid);

			$this->common_model->trans_commit();
			Util::echo_format_return(_SUCCESS_, $return,  $msg);
			return 1;
		} catch (Exception $e) {
			$this->common_model->trans_rollback();
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}

	public function answer_del() {
		$uid = $this->user_id;
		$aids = ( string ) $this->input->get_post ( 'mark', true );

		try {
			if (empty($uid) || empty($aids)) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}
			$this->common_model->trans_begin();

			$aid_arr = explode(',', $aids);
			foreach ($aid_arr as $aid) {
				if (!is_numeric($aid)) {
					throw new Exception('aid error', _PARAMS_ERROR_);
				}
				$this->qa_model->answer_del($aid, $uid);
			}
			$this->common_model->trans_commit();

			$return = array(
			);
			Util::echo_format_return(_SUCCESS_, $return, '删除成功');
			return 1;
		} catch (Exception $e) {
			$this->common_model->trans_rollback();
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}

	/**
	 * 对答案赞操作
	 *
	 */
	public function praise_operate()
	{
		$newsid	  	= trim ( $this->input->get('newsid',true) );
		$type		= (intval ( $this->input->get('type',true) )) ? intval ( $this->input->get('type',true) ) : 0 ;
		$guid		= trim ( $this->input->get('guid',true) );
		$partner_id	  	= trim ( $this->input->get('partner_id',true) );
		try {
			if(!$this->user_id) {
				throw new Exception('用户未登录', _PARAMS_ERROR_);
				Util::echo_format_return(_SUCCESS_, '');
				exit;
			}
			if (empty($newsid)  ||  $type <0) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
				Util::echo_format_return(_SUCCESS_, '');
				exit;
			}
			if ($this->common_model->is_ban_user()) {
				throw new Exception(_BANNED_MSG_, _USER_BANNED_);
				exit;
			}
			//验证是否重复点赞
			$like_info = $this->like_model->get_info($guid,$newsid,3);
			$like_info4 = $this->like_model->get_info($guid,$newsid,4);
			$user_info = $this->user_model->getUserInfoById($guid);
			$weight_level = $user_info['rank'] ? 1 : 0;

			if($like_info['id']) {
				if ($type == 1 && $like_info['status'] == 1) {
					//重复点赞
					Util::echo_format_return(_SUCCESS_, $this->get_answer_info($like_info['mark']));
					exit;
				} elseif($type != 1 && $like_info['status'] != 1) {
					//重复取消点赞
					Util::echo_format_return(_SUCCESS_, $this->get_answer_info($like_info['mark']));
					exit;
				} elseif($type == 1 && $like_info4['status'] == 1) {
					//已经踩过了，点赞数＋1   反对数－1
					$this->answer_model->add_mark_down_count($newsid,$guid,-1);//反对数－1
					$this->like_model->updateLikeData($like_info4['id'], 0);
				}
				$this->like_model->updateLikeData($like_info['id'], $type);
			}else{
				if($type == 0){
					Util::echo_format_return(_SUCCESS_, $this->get_answer_info($like_info['mark']));
					exit;
				}
				if($type == 1 && $like_info4['status'] == 1) {
					//已经踩过了，点赞数＋1   反对数－1
					$this->answer_model->add_mark_down_count($newsid,$guid,-1);//反对数－1
					$this->like_model->updateLikeData($like_info4['id'], 0);
				}


				//插入该用户点赞情况
				$datas['mark'] = $newsid;
				$datas['user_id'] = $guid;
				$datas['type'] = '3';//答案赞
				$datas['status'] = '1';
				$datas['weight_level'] = $weight_level;
				$datas['partner_id'] = $partner_id;
				$datas['create_time'] = time();
				$datas['update_time'] = time();
				$this->like_model->insertLikeData($datas);
				//清缓存
//				$this->answer_model->_clear_info($newsid);
//				$answer_info = $this->answer_model->get_info($newsid);
//				$this->answer_model->_clear_hot_list($answer_info['qid']);
//				$this->answer_model->_clear_list($answer_info['qid']);
                //获取答案的用户
				$answer_info = $this->answer_model->get_info($newsid);
			    $user_infos = $this->user_model->getUserInfoById($answer_info['uid']);
				// 经验
				$this->load->model('exp_model');
				if($user_info['rank'] == 1){
					$add_exp = $this->exp_model->add_exp($user_infos['uid'], 5, $newsid);// 大神
					if ($add_exp) {
						// 增加经验通知
						$this->load->model('push_message_model');
						$this->push_message_model->push(2, 4, $newsid, 1, 1,  $add_exp);
					}
				}else{
					$add_exp = $this->exp_model->add_exp($user_infos['uid'], 6, $newsid);// 一般用户
					if ($add_exp) {
						// 增加经验通知
						$this->load->model('push_message_model');
						$this->push_message_model->push(2, 4, $newsid, 1, 2, $add_exp);
					}
				}

			}
			//累加点赞数
			if($type == 1){
				$count = 1;
			}else{
				$count = -1;
			}
			$this->answer_model->add_mark_up_count($newsid,$guid,$count);

			// 消息模块
			$this->load->model('push_message_model');
			$_push_type = 2;	// 答案
			$_push_flag = 3;	// 赞
			$_push_mark = $newsid;
			$this->push_message_model->push($_push_type, $_push_flag, $_push_mark, $count);

			$like_infoss = $this->like_model->get_info($guid,$newsid,3);
			Util::echo_format_return(_SUCCESS_, $this->get_answer_info($like_infoss['mark']),'操作成功');
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}

	/**
	 * 对答案踩／答案反对操作
	 *
	 */
	public function cai_operate()
	{
		$newsid	  	= trim ( $this->input->get('newsid',true) );
		$type		= (intval ( $this->input->get('type',true) )) ? intval ( $this->input->get('type',true) ) : 0 ;
		$guid		= trim ( $this->input->get('guid',true) );
		$partner_id	  	= trim ( $this->input->get('partner_id',true) );

		try {
			if(!$this->user_id) {
				throw new Exception('用户未登录', _PARAMS_ERROR_);
			}
			if (empty($newsid)  ||  $type <0) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}
			if ($this->common_model->is_ban_user()) {
				throw new Exception(_BANNED_MSG_, _USER_BANNED_);
				exit;
			}
			//验证是否重复点赞
			$like_info = $this->like_model->get_info($guid,$newsid,4);
			$like_info3 = $this->like_model->get_info($guid,$newsid,3);
			$user_info = $this->user_model->getUserInfoById($guid);
			$weight_level = $user_info['rank'] ? 1 : 0;
			if($like_info['id']) {
				if ($type == 1 && $like_info['status'] == 1) {
					//重复点踩
					Util::echo_format_return(_SUCCESS_, $this->get_answer_info($like_info['mark']));
					exit;
				} elseif($type != 1 && $like_info['status'] != 1) {
					//重复取消点踩
					Util::echo_format_return(_SUCCESS_, $this->get_answer_info($like_info['mark']));
					exit;
				} elseif($type == 1 && $like_info3['status'] == 1) {
					//已经赞过了，踩数＋1   赞数－1
					$this->answer_model->add_mark_up_count($newsid,$guid,-1);//点赞数－1
					$this->like_model->updateLikeData($like_info3['id'], 0);


					// 消息模块
					$this->load->model('push_message_model');
					$_push_type = 2;	// 答案
					$_push_flag = 3;	// 赞
					$_push_mark = $newsid;
					$this->push_message_model->push($_push_type, $_push_flag, $_push_mark, -1);
				}
				$this->like_model->updateLikeData($like_info['id'], $type);

			}else{
				if($type == 0){
					//没有踩过,不能取消点踩
					Util::echo_format_return(_SUCCESS_, $this->get_answer_info($like_info['mark']));
					exit;
				}
				if($type == 1 && $like_info3['status'] == 1) {
					//已经赞过了，点赞数-1   反对数+1
					$this->answer_model->add_mark_up_count($newsid,$guid,-1);//赞数－1
					$this->like_model->updateLikeData($like_info3['id'], 0);


					// 消息模块
					$this->load->model('push_message_model');
					$_push_type = 2;	// 答案
					$_push_flag = 3;	// 赞
					$_push_mark = $newsid;
					$this->push_message_model->push($_push_type, $_push_flag, $_push_mark, -1);
				}
				//插入该用户点赞情况
				$datas['mark'] = $newsid;
				$datas['user_id'] = $guid;
				$datas['type'] = '4';//答案踩／答案反对
				$datas['status'] = '1';
				$datas['weight_level'] = $weight_level;
				$datas['partner_id'] = $partner_id;
				$datas['create_time'] = time();
				$datas['update_time'] = time();
				$this->like_model->insertLikeData($datas);

			}
			//累加点赞数
			if($type == 1){
				$count = 1;
			}else{
				$count = -1;
			}
			$this->answer_model->add_mark_down_count($newsid,$guid,$count);

			$like_infoss = $this->like_model->get_info($guid,$newsid,4);

			Util::echo_format_return(_SUCCESS_, $this->get_answer_info($like_infoss['mark']),'操作成功');
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}

	private function get_answer_info($id){

		$this->answer_model->_clear_info($id);
		$answer_info = $this->answer_model->get_info($id);
		$data = array();
		$data['agreeCount'] = $answer_info['mark_up_rank_0_count'] + $answer_info['mark_up_rank_1_count'] + $answer_info['mark_up_virtual_count'];
		$data['combatCount'] = $answer_info['mark_down_rank_0_count'] + $answer_info['mark_down_rank_1_count'];

		return $data;
	}

	// ------------------------------------------------------------------------------------------------------------------------//
	public function upload_img() {
		$uid = $this->user_id;
		$action = ( int ) $this->input->get_post ( 'action', true );

		try {
			if (empty($uid) || !in_array($action, array(1,2)) ) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}

			$this->qa_model->upload_img($uid, $_FILES);
			Util::echo_format_return(_SUCCESS_);
			return 1;
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}



// =========================================== 审核中接口 =============================================================================//
	public function question_recommend_list_review_remap() {
		$page = ( int ) $this->input->get_post ( 'page', true );
		$page_size = ( int ) $this->input->get_post ( 'count', true );
		$max_id = ( int ) $this->input->get_post ( 'max_id', true );

		$expire_time = 60 * 10;

		
		try {
			if (empty($page) || empty($page_size) ) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}

			$page < 1 && $page = 1;
			$offsize = ($page - 1) * $page_size;

			//问答列表
			$qa_list = $this->recommend_model->get_recommend_list( 6,0,$offsize, $page_size,$max_id, 1);

			foreach ($qa_list as $k => $v) {
				$answer_info = $this->answer_model->get_info($v['param'],array(0,1));
				if(empty($answer_info['qid'])){
					continue;
				}
				$question_info = $this->question_model->get_info($answer_info['qid'],array(0,1));
				if(empty($question_info['qid'])){
					continue;
				}
				//问答详情
				$answer_content_info = $this->answer_content_model->get_content($answer_info['aid']);
				$question_content_info = $this->question_content_model->get_content($answer_info['qid']);
				$question_img_count = $this->qa_image_model->get_list_count(1,$answer_info['qid']);

				//游戏信息
				$game_info = $this->game_model->get_cms_game_info($question_info['gid'],2);
				$game_info = $game_info[0];
				//用户信息
				$answer_user_info = $this->user_model->getUserInfoById($answer_info['uid']);
				$questionList =array();
				$questionList['absId'] 			= (string)$question_info['qid'];
				$questionList['abstitle'] 		= $question_content_info ? $this->qa_model->convert_content_to_frontend($question_content_info, false, true) : '';
				$questionList['imageCount'] 	= $question_img_count;
				$questionList['answerCount'] 	= $question_info['normal_answer_count'] + $question_info['hot_answer_count'];
				if($question_info['gid'] == 2031){
					$questionList['gameInfo']['absId'] 		= '2031';
					$questionList['gameInfo']['abstitle'] 	= $question_info['gname'];
					$questionList['gameInfo']['absImage'] 	= '';
				}else{
					$questionList['gameInfo']['absId'] 		= (string)$question_info['gid'];
					$questionList['gameInfo']['abstitle'] 	= $game_info['title'] ? (string)$game_info['title'] : '';
					$questionList['gameInfo']['absImage'] 	= $game_info['logo'] ? (string)$game_info['logo'] : '';
				}


				$questionList['answerList'][0]['absId'] = (string)$answer_info['aid'];
				$questionList['answerList'][0]['abstitle'] = $answer_content_info ? $this->qa_model->convert_content_to_frontend($answer_content_info, false, true) : '';
				$questionList['answerList'][0]['agreeCount'] = $answer_info['mark_up_rank_0_count'] + $answer_info['mark_up_rank_1_count'] + $answer_info['mark_up_virtual_count'];
				$questionList['answerList'][0]['author']['guid'] = (string)$answer_info['uid'];
				$questionList['answerList'][0]['author']['nickName'] = (string)$answer_user_info['nickname'];
				$questionList['answerList'][0]['author']['headImg'] = (string)$answer_user_info['avatar'];
				$q_infos[] = $questionList;
			}

			//焦点对象数据［区分平台］
			$jiaodian_recommend = $this->recommend_model->get_recommend_list(5,0,0,0,0,1);

			$jiaodianRecommend =array();
			foreach ($jiaodian_recommend as $k_1 => $v_1) {
				if ($v_1['type'] == 1) {
					$jiaodianRecommend[$k_1]['type'] = 2;
				} elseif ($v_1['type'] == 2) {
					$jiaodianRecommend[$k_1]['type'] = 0;
				} elseif ($v_1['type'] == 3) {
					$jiaodianRecommend[$k_1]['type'] = 1;
				} elseif ($v_1['type'] == 4) {
					$jiaodianRecommend[$k_1]['type'] = 3;
				} elseif ($v_1['type'] == 5) {
					$jiaodianRecommend[$k_1]['type'] = 4;
				} elseif ($v_1['type'] == 6) {
					$jiaodianRecommend[$k_1]['type'] = 5;
				} elseif ($v_1['type'] == 7) {
					$jiaodianRecommend[$k_1]['type'] = 6;
				}
				$jiaodianRecommend[$k_1]['abstitle'] = $v_1['title'];
				if ($v_1['type'] == '4' || $v_1['type'] == '5' || $v_1['type'] == '6' || $v_1['type'] == '7') {
					$jiaodianRecommend[$k_1]['absId'] = $v_1['param'];
				} else {
					$jiaodianRecommend[$k_1]['webUrl'] = $v_1['param'];
				}
				if($v_1['type'] == '7'){
					$game_infos = $this->game_model->get_cms_game_info($v_1['param']);
					$game_infos = $game_infos[0];

					$jiaodianRecommend[$k_1]['abstitle'] = $game_infos['title'];
				}
				// $jiaodianRecommend[$k_1]['absImage'] = 'http://store.games.sina.com.cn/'.$v_1['img'];
				$jiaodianRecommend[$k_1]['absImage'] = gl_img_url($v_1['img']);

			}

			$data['focusList'] =$jiaodianRecommend ? $jiaodianRecommend : array();

			$data['newList'] =$q_infos ? $q_infos : array();

			Util::echo_format_return(_SUCCESS_, $data);
			return 1;
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}

	public function question_list_review_remap() {
		$gids = ( string ) $this->input->get_post ( 'gids', true );
		$page = ( int ) $this->input->get_post ( 'page', true );
		$page_size = ( int ) $this->input->get_post ( 'count', true );

		try {
			if (empty($page) || empty($page_size) ) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}

			$page < 1 && $page = 1;
			$offsize = ($page - 1) * $page_size;

			$data = $this->qa_model->get_question_list($gids, $offsize, $page_size, 1);

			//
			Util::echo_format_return(_SUCCESS_, $data);
			return 1;
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}

	public function answer_list_review_remap() {
		$qid = ( int ) $this->input->get_post ( 'absId', true );
		$page = ( int ) $this->input->get_post ( 'page', true );
		$page_size = ( int ) $this->input->get_post ( 'count', true );
		$last_id = ( int ) $this->input->get_post ( 'max_id', true );

		try {
			if (empty($page) || empty($page_size) ) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}

			$page < 1 && $page = 1;
			$offsize = ($page - 1) * $page_size;

			$data = $this->qa_model->get_answer_list($qid, $offsize, $page_size, $last_id, 1);

			//
			Util::echo_format_return(_SUCCESS_, $data);
			return 1;
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}

}
