<?php
/**
 * question & answer
 * @name Qa_model
 * @desc null
 *
 * @author	 liule1
 * @date 2015年8月4日
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 *
 *
 * @property	question_model	$question_model
 * @property	answer_model	$answer_model
 * @property	user_model		$user_model
 * @property	qa_image_model	$qa_image_model
 * @property	question_content_model	$question_content_model
 * @property	game_model		$game_model
 * @property	answer_content_model	$answer_content_model
 * @property	follow_model	$follow_model
 * @property	push_message_model	$push_message_model
 * @property	exp_model		$exp_model
 */
class Qa_model extends MY_Model {
	private $_cache_key_pre = '';
	private $_cache_expire = 600;

	function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":qa:";
		$this->load->model('user_model');
		$this->load->model('question_model');
		$this->load->model('answer_model');
		$this->load->model('qa_image_model');
		$this->load->model('question_content_model');
		$this->load->model('answer_content_model');
		$this->load->model('game_model');
		$this->load->model('follow_model');
		$this->load->model('push_message_model');
		$this->load->model('exp_model');
		$this->load->model('like_model');
	}



	// ================================================== 公共 GO =============================================== //
	/**
	 * 内容转成前端需要的格式
	 * @param unknown $content
	 * @param unknown $length
	 */
	public function convert_content_to_frontend($content, $length = 0, $is_list = 1, $decode = 1) {
		$content = trim($content);
		$pattern = array(
				'/\[!--IMG_\d+--\]/',
				"/\n+/",
				'/<a\s+.*?>(.*?)<\/a>/'
		);
		$replace = array(
				'',
				' ',
				'$1'
		);
		$is_list && $content = preg_replace($pattern, $replace, $content);
		$decode && $content = htmlspecialchars_decode($content);
		$content = stripslashes($content);
		$length > 0 && $content = mb_substr($content, 0, $length);
		return $content;
	}

	/**
	 * 上传图片
	 * @param unknown $uid
	 * @param unknown $files
	 */
	public function upload_img($uid, $files) {
		if (!$uid || empty($files)) {
			return false;
		}

		$ids = array_keys($files);
		if (!$this->qa_image_model->check_onwership($uid, $ids)) {
			return false;
		}


		$img = array();
		foreach ($files as $id => $file) {
			$_arr = $this->qa_image_model->upload_img($id, $file);
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

			$this->qa_image_model->update($uid, $id, $update_data);
		}

		return $img;
	}


	// ================================================== 公共 END =============================================== //


	// ================================================== 问题 GO =============================================== //
	// ---------------------------------------------------------------------------------- //
	public function get_question_list($gids, $offset, $limit, $review_state='all') {
		$gids = $gids === '' ? array() : explode(',', $gids);
		foreach ($gids as &$v) {
			$v = (int)$v;
		}
		$list = $this->question_model->get_list($gids, $offset, $limit, $review_state);
		// 审核中不需要热门
		$hot_list = $review_state === 'all' ? $this->question_model->get_hot_list($gids) : array();

		$return = array();
		$return['newList'] = $this->convert_question_list_to_frontend($list, $review_state);
		$return['hotList'] = $this->convert_question_list_to_frontend($hot_list, $review_state);

		return $return;
	}
	public function convert_question_list_to_frontend($question_list, $review_state = 'all') {
		if (!is_array($question_list)) {
			return array();
		}
		$return = array();
		foreach ($question_list as $k => $v) {
			if ($v['gid'] == 2031) {
				$game_info_frontend = array(
						'absId' => (string)$v['gid'],
						'abstitle' => (string)$v['gname'],
						'absImage' => '',
				);
			} else {
				$game_info = $this->game_model->get_cms_game_info($v['gid']);
				$game_info = $game_info[0];
				$game_info_frontend = array(
							'absId' => (string)$v['gid'],
							'abstitle' => (string)$game_info['title'],
							'absImage' => (string)$game_info['logo']
					);
			}

			$answer_list = array();
			if ($review_state !== 'all' && $review_state == 1) {
					// 获取赞数最多的
					$answer_list = array();
			} else {
				$answer_list = $this->answer_model->get_hot_list($v['qid']);
				if (empty($answer_list)) {
					// 获取赞数最多的
					$answer_list = $this->answer_model->get_secondary_hot_list($v['qid']);
				} else {
					$answer_list = array(array_shift($answer_list));
				}
			}

			$answer_list_frontend = array();
			foreach ($answer_list as $av) {
				$answer_user_info = $this->user_model->getUserInfoById($av['uid']);
				$answer_list_frontend[] = array(
						'absId' => $av['aid'],
						'abstitle' => $this->convert_content_to_frontend($this->answer_content_model->get_content($av['aid']), 100),
						'agreeCount' => $av['mark_up_rank_0_count'] + $av['mark_up_rank_1_count'] + $av['mark_up_virtual_count'],
						'author' => array(
								'guid' => $av['uid'],
								'nickName' => $answer_user_info['nickname'],
								'headImg' => $answer_user_info['avatar'],
						)
				);
			}

			$question_user_info = $this->user_model->getUserInfoById($v['uid']);
			$return[$k] = array(
					'absId' => $v['qid'],
					'authorId' => $v['uid'],
					'headUrl' => $question_user_info['avatar'],
					'abstitle' => $this->convert_content_to_frontend($this->question_content_model->get_content($v['qid']), 50),
					'gameInfo' => $game_info_frontend,
					'imageCount' => (int)$this->qa_image_model->get_list_count(1, $v['qid'], 1),
					'answerCount' => (int)$v['normal_answer_count'],
					'answerList' => $answer_list_frontend,

			);
		}
		return $return;
	}

	// ---------------------------------------------------------------------------------------------------------------------//
	public function get_question_info($uid, $qid) {
		$uid = $this->global_func->filter_int($uid);
		$qid = $this->global_func->filter_int($qid);

		$question_info = $this->question_model->get_info($qid);
		if (empty($question_info)) {
			return array();
		}

		$image_list = $this->qa_image_model->get_list(1, $qid);

		$images_frontend = array();
		foreach ($image_list as $v) {
			$images_frontend[] = array(
					'img_id' => (string)$this->qa_image_model->convert_id_to_frontend($v['id']),
					// 'url' => $v['url'] ? (IMAGE_URL_PRE . $v['url']) : '',
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
				'content' => $this->convert_content_to_frontend($content, 0, 0),
				'original_content' => $this->convert_content_to_frontend($content, 0, 0, 0),
				'attribute' => array(
						'images' => $images_frontend,
				),
				'answerCount' => (int)$question_info['normal_answer_count'],
				'attentionCount' => (int)($question_info['follow_count'] + $question_info['virtual_follow_count']),
// 				'shareUrl' => base_url() . '/share/detail?qid=' . $qid,
		        // 'shareUrl' => 'http://www.wan68.com/question/info/' . $qid,
		        'shareUrl' => base_url() . 'question/info/' . $qid,
				'shareContent' => $this->convert_content_to_frontend($content),
				'inviteContent' => $this->convert_content_to_frontend($content, 50),
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

	// ----------------------------------------------------------------------------------------------------------------------//

	public function question_save($uid, $qid, $gid ,$game_name) {
		$qid = (int) $qid;
		$gid = (int) $gid;
		if ($gid == 2031 && empty($game_name)) {
			return false;
		}

		$return = array(
				'qid' => 0,
				'add_exp' => 0,
		);

		if ($qid) {
			$question_info = $this->question_model->get_info($qid);

			// edit
			if (!$this->question_model->update_content($uid, $qid, $gid, $game_name)) {
				// 修改没生效
				$qid = 0;
			} else {
				// 更新 question.sort_time
				$this->question_model->update_sort_time($qid);

				$this->question_model->delete_list_cache($gid);
				if ($gid != $question_info['gid']) {
					$this->question_model->delete_list_cache($question_info['gid']);
				}
			}
		} else {
			// 防刷

			// 一个自然天内一个用户最多提5个问题
			$day_max_cache_key = $this->_cache_key_pre . "defence_repeat2:$uid:" . date('d') . '_' . __FUNCTION__ ;
			if ($this->cache->redis->get($day_max_cache_key) >= 5) {
				throw new Exception('您今天已经提了很多问题啦，明天再来吧！', _DATA_ERROR_);
			} else {
				$this->cache->redis->expire($day_max_cache_key, 86400);
			}

			// 10秒内一个用户只能提1个问题
			$cache_key = $this->_cache_key_pre . "defence_repeat2:$uid:" . date('d') . '_2m' . __FUNCTION__ ;
			if ($this->cache->redis->incr($cache_key) > 1) {
				throw new Exception('您提问太频繁了哦，请稍后再试', _DATA_ERROR_);
			} else {
				$this->cache->redis->expire($cache_key, 10);
			}

			// insert
			$qid = $this->question_model->insert($uid, $gid, $game_name);

			// 经验
			$return['add_exp'] = (int)$this->exp_model->add_exp($uid, 1, $qid);


			$this->cache->redis->incr($day_max_cache_key);

		}


		$return['qid'] = $qid;
		return $return;
	}

	public function question_content_save($uid, $qid ,$content) {
		$type = 1;
		$qid = (int) $qid;
		if (empty($qid) || empty($content)) {
			return false;
		}

		$return = $this->qa_image_model->init_content_image($uid, $type, $qid, $content);
		$this->question_content_model->save_content($qid, $content);

		return $return;
	}


	// ================================================== 问题 END =============================================== //



	// ================================================== 答案 GO =============================================== //
	public function answer_save($uid, $aid, $qid) {
		$aid = (int) $aid;
		$qid = (int) $qid;
		if (empty($qid)) {
			return false;
		}

		$return = array(
				'aid' => 0,
				'add_exp' => 0,
		);
		if ($aid) {
			// edit
			if (!$this->answer_model->update_content($uid, $aid)) {
				// 修改没生效
				$aid = 0;
			}
		} else {
			// 防刷
			// 10秒内同一个账号只能对一个问题回答一次，若两分钟内再次点击回答按钮，提示用户"您回答太频繁了哦，请稍后再试"
			$cache_key = $this->_cache_key_pre . "defence_repeat:$uid:" . '_2m' . __FUNCTION__ . '_2m_' . $qid ;
			if ($this->cache->redis->incr($cache_key) > 1) {
				throw new Exception('您回答太频繁了哦，请稍后再试', _DATA_ERROR_);
			} else {
				$this->cache->redis->expire($cache_key, 10);
			}

			// insert
			if ($this->question_model->add_normal_answer_count($qid, 1)) {
				$aid = $this->answer_model->insert($uid, $qid);

				// 推送
				$_push_type = 1;	// 问题
				$_push_flag = 1;	// 新增回答
				$_push_mark = $qid;
				$this->push_message_model->push($_push_type, $_push_flag, $_push_mark);

				// 经验
				$return['add_exp'] = (int)$this->exp_model->add_exp($uid, 2, $qid);


			} else {
				$aid = 0;
			}
		}

		if ($aid) {
			// 更新 question.sort_time
			$this->question_model->update_sort_time($qid);

		}

		$return['aid'] = $aid;

		return $return;
	}

	public function answer_content_save($uid, $aid ,$content) {
		$type = 2;
		$$aid = (int) $aid;
		if (empty($aid) || empty($content)) {
			return false;
		}

		$return = $this->qa_image_model->init_content_image($uid, $type, $aid, $content);
		$this->answer_content_model->save_content($aid, $content);

		return $return;
	}


	// ---------------------------------------------------------------------------------------------------------------------//
	public function get_answer_info($uid, $aid) {
		$uid = $this->global_func->filter_int($uid);
		$aid = $this->global_func->filter_int($aid);

		$answer_info = $this->answer_model->get_info($aid);

		if (empty($answer_info) || $answer_info['status'] == '2' ) {
			return array();
		}


		$image_list = $this->qa_image_model->get_list(2, $aid);

		$images_frontend = array();
		foreach ($image_list as $v) {
			$images_frontend[] = array(
					'img_id' => (string)$this->qa_image_model->convert_id_to_frontend($v['id']),
					// 'url' => $v['url'] ? (IMAGE_URL_PRE . $v['url']) : '',
					'url' => gl_img_url($v['url']),
					'width' => (int)$v['width'],
					'height' => (int)$v['height'],
					'desc' => '',
			);
		}


		$this->load->model('like_model');

		$user_info = $this->user_model->getUserInfoById($answer_info['uid']);
		$question_info = $this->question_model->get_info($answer_info['qid']);
		$question_content = $this->question_content_model->get_content($answer_info['qid']);

		$game_info = $this->game_model->get_cms_game_info($question_info['gid']);
		if (is_array($game_info)) {
			$game_info = array_pop($game_info);
		} else {
			$game_info = array(
				'title' => $question_info['gname']
			);
		}


		$content = $this->answer_content_model->get_content($answer_info['aid']);

		$this->load->model('order_model');
		$totalCashCount = $this->order_model->getTotalCashCounts(1,$answer_info['aid']);

		$areward = $this->order_model->get_list('' ,10,1,0,10,'1',1,$answer_info['aid'],1);
// 		$totalCashCount = count($areward);
		$data = array();
		$this->load->model('friend_model');
		foreach ($areward as $k=>$v){
		    $dataInfo[$k] = $this->User->getUserInfoById($v['from_uid']);
		    $data[$k]['guid']          = (string)$v['from_uid'];
		    $data[$k]['nickName']      = (string)$dataInfo[$k]['nickname'];
		    $data[$k]['headImg']       = (string)$dataInfo[$k]['avatar'] ? (string)$dataInfo[$k]['avatar'] : '';
		}
		$return = array(
				'absId' => $answer_info['aid'],
				'updateTime' => date('Y-m-d H:i:s', $answer_info['update_time']),
				'createTime' => date('Y-m-d H:i:s', $answer_info['create_time']),
				'content' => $this->convert_content_to_frontend($content, 0, 0),
				'original_content' => $this->convert_content_to_frontend($content, 0, 0, 0),
				'attribute' => array(
						'images' => $images_frontend,
				),
				'agreeCount' => $answer_info['mark_up_rank_0_count'] + $answer_info['mark_up_rank_1_count'] + $answer_info['mark_up_virtual_count'],
				'combatCount' => $answer_info['mark_down_rank_0_count'] + $answer_info['mark_down_rank_1_count'],
				'hasAgree' => (boolean)$this->like_model->is_like($answer_info['aid'], 3),
				'hasCombat' => (boolean)$this->like_model->is_like($answer_info['aid'], 4),
				'hasCollect' => (boolean) $this->follow_model->is_follow($uid, 2, $answer_info['aid']),
				'commentCount' => (int)$answer_info['comment_count'],
				'isHot' => (boolean)$this->answer_model->is_hot($answer_info['qid'], $aid),
// 				'shareUrl' => base_url() . '/share/index?aid=' . $aid,
		        // 'shareUrl' => 'http://www.wan68.com/answer/info/' . $aid,
		        'shareUrl' => base_url() . 'answer/info/' . $aid,
				'shareContent' => $this->convert_content_to_frontend($content),
				'donateCount' => (int)$totalCashCount,
				'author' => array(
						'guid' => (string)$user_info['uid'],
						'nickName' => (string)$user_info['nickname'],
						'headImg' => (string)$user_info['avatar'],
						'uLevel' => (int)$user_info['level'],
						'medalLevel' => (int)$user_info['rank'],
				),
				'questionInfo' => array(
						'absId' => (string) $answer_info['qid'],
						'abstitle' => $this->convert_content_to_frontend($question_content, false, true),
				),
				'gameInfo' => array(
						'absId' => (string) $question_info['gid'],
						'abstitle' => (string) $game_info['title']
				),
				'donateUsers' => $data
		);

		return $return;
	}

	public function get_answer_list($qid, $offset, $limit, $last_id = 0, $review_state = 'all') {
		if (empty($qid)) {
			return false;
		}

		$list = $this->answer_model->get_list($qid, $offset, $limit, $last_id, $review_state);
		$hot_list = $review_state === 'all' ? $this->answer_model->get_hot_list($qid) : array();

		$return = array();
		$return['newList'] = $this->convert_answer_list_to_frontend($list);
		$return['hotList'] = $this->convert_answer_list_to_frontend($hot_list);

		return $return;
	}
	public function convert_answer_list_to_frontend($answer_list) {
		if (!is_array($answer_list)) {
			return array();
		}
		$return = array();
		foreach ($answer_list as $k => $v) {
			$author_info = $this->user_model->getUserInfoById($v['uid']);
			$return[$k] = array(
					'absId' => $v['aid'],
					'abstitle' => $this->convert_content_to_frontend($this->answer_content_model->get_content($v['aid']), 200),
					'updateTime' =>date('Y-m-d H:i:s', $v['update_time']),
					'imageCount' => (int)$this->qa_image_model->get_list_count(2, $v['aid'], 1),
					'agreeCount' => $v['mark_up_rank_0_count'] + $v['mark_up_rank_1_count'] + $v['mark_up_virtual_count'],
					'commentCount' => (int)$v['comment_count'],
					'hasAgree' => $this->like_model->is_like( $v['aid'], 3),
					'hasCombat' => $this->like_model->is_like( $v['aid'], 4),
					'author' => array(
						'guid' => (string)$author_info['uid'],
						'nickName' => (string)$author_info['nickname'],
						'headImg' => (string)$author_info['avatar'],
						'uLevel' => (int)$author_info['level'],
						'medalLevel' => (int)$author_info['rank'],
					),
			);
		}
		return $return;
	}

	public function answer_del($aid, $uid) {
		if (!is_numeric($aid) || empty($uid)) {
			return false;
		}
		$answer_info = $this->answer_model->get_info($aid, array(0,1,3));

		if (empty($answer_info) || $answer_info['uid'] != $uid) {
			return false;
		}

		$return = $this->answer_model->update_status_to_3($aid, $uid);
		if ($return) {
			// 更新问题表中的答案数量
			$this->question_model->add_normal_answer_count($answer_info['qid'], -1);
		}
		return $return;
	}
	// ================================================== 答案 END =============================================== //
}
