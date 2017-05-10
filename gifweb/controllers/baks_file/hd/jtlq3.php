<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

/**
 *
 * null
 *
 * @name Comment
 * @author liule1
 *         @date 2016-11-29
 *
 * @copyright (c) 2016 SINA Inc. All rights reserved.
 *
 * @property comment_model $comment_model
 * @property	user_model	$user_model
 */
class Jtlq3 extends MY_Controller {
	private $_allowMark = array();
	private $_questionId0 = '33065';
	private $_questionId1 = '33066';
	private $_themeId = '5';
	private $_itemInfo0 = array(
			'id' => '111',
			'vote' => 999,
	);
	private $_itemInfo1 = array(
			'id' => '112',
			'vote' => 999,
	);

	public function __construct() {
		parent::__construct ();
		// header("Location: /" );

		$this->load->model('comment_model');
		$this->load->model('user_model');

		$this->_allowMark[] = $this->_questionId0;
		$this->_allowMark[] = $this->_questionId1;

		$this->smarty->assign('isLogin', $this->user_id ? 1 : 0);
		$this->smarty->assign('uid', $this->user_id);
	}

	// ----------------------------------------------------------------------------------------------------------
	public function index() {
		// init vote info
		$url = "http://gameapi.g.sina.com.cn/app/vote/api/open/get_item_list_by_themeid?themeid={$this->_themeId}&page=1&count=2";
		$res = $this->global_func->curl_get($url);
		$res && $res = json_decode($res , true);
		if ($res && $res['result'] == 200 && $res['data']['theme_item_list']) {
			foreach ($res['data']['theme_item_list'] as $v) {
				if ($v['id'] == $this->_itemInfo0['id']) {
					$this->_itemInfo0 = $v;
				}
				if ($v['id'] == $this->_itemInfo1['id']) {
					$this->_itemInfo1 = $v;
				}
			}

			$this->smarty->assign('itemInfo0', $this->_itemInfo0);
			$this->smarty->assign('itemInfo1', $this->_itemInfo1);
		}

		$seo = array(
			'title' => '情怀or竞技|街头篮球我选择我要的游戏_活动_全民手游攻略',
			'keywords' => '攻略 街头篮球 投票',
			'description' => '万众期待的街头篮球手游三测正式到来，等了这么久究竟能否符合玩家要求？你是因为情怀还是竞技选择这款游戏呢？说出你的观点只要有人挺你，你就有Q币！！',
		);
		$this->smarty->assign('seo', $seo);
		$this->smarty->assign('ua', $_SERVER['HTTP_USER_AGENT']);

		if ($this->global_func->isMobile()) {
			$this->index_h5();
		} else {
			$this->index_pc();
		}
	}
	private function index_pc() {
		$this->smarty->assign('link_url', 1);
		$this->smarty->view ( 'hd/jtlq3/pc/index.html' );
	}
	private function index_h5() {
		$this->smarty->view ( 'hd/jtlq3/h5/index.html' );
	}

	public function ajax_answer_list($idx, $page) {
		$idx = (int) $idx;
		$page = (int) $page;
		$page = max(1, $page);

		$count = 9;
		$offset = ($page - 1) * $count;

		$qid = $this->{'_questionId' . $idx};
		$this->load->model('qa_model');
		$res = $this->qa_model->get_answer_list($qid, $offset, $count);

		$data = $res['newList'];
		Util::echo_format_return(_SUCCESS_, $data);
	}
	public function ajax_vote($idx) {
		$idx = (int) $idx;
		try {
			$uid = $this->user_id;
			$nickname = $this->userinfo['nickname'];
			$ip = $_SERVER['REMOTE_ADDR'];
			if (!$ip) {
				throw new Exception("请登录再投票", 1002);
			}
			$uid || $uid = '-1';
			$nickname || $nickname = '未登录';

			$voteApi = "http://gameapi.g.sina.com.cn/app/vote/api/open/voteBySign";
			$postData = array(
				'ip' => $ip,
				'uid' => $uid,
				'nickname' => $nickname,
				'itemId' => $this->{'_itemInfo' . $idx}['id'],
			);

			// 生成sign
			$signKey = 'lasjlIAS:ILDUQWEIOUN*&*( HSAHDAS)(D^A)(HHKLK:L))';
			$signParams = array();
            $getrow = $postData;
    		foreach ( $getrow as $k => $v ) {
    			if (in_array($k, array("sign"))) {
    				continue;
    			}
    			$signParams[] = "$k=$v";
    		}
    		$sign = md5(implode('&', $signParams) . $signKey);

			$postData['sign'] = $sign;

			$res = $this->global_func->curl_post($voteApi, $postData);

			if ($res && json_decode($res , true)) {
				echo $res;
			}
			// Util::echo_format_return(_SUCCESS_, $data);
			return 1;
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}

	public function answer_save($idx) {
		$uid = $this->user_id;
		$idx = (int) $idx;

		$content = ( string ) $this->input->get_post ( 'content', false, true );
		$qid = $this->{'_questionId' . $idx};

		$aid = ( int ) 0;
		try {
			if (empty($uid) || empty($qid) || empty($content) ) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}

			if ($this->common_model->is_ban_user()) {
				throw new Exception(_BANNED_MSG_, _USER_BANNED_);
			}

			$this->load->model('qa_model');

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


	/**
	 * 对答案赞操作
	 *
	 */
	public function praise_operate()
	{
		$newsid	  	= trim ( $this->input->get('newsid',true) );
		$type		= (intval ( $this->input->get('type',true) )) ? intval ( $this->input->get('type',true) ) : 0 ;
		$guid		= $this->user_id;
		$partner_id	  	= '10001';
		try {
			if(!$this->user_id) {
				throw new Exception('用户未登录', _PARAMS_ERROR_);
				Util::echo_format_return(_SUCCESS_, '');
				exit;
			}
			if (empty($newsid)  ||  $type <0 ) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
				Util::echo_format_return(_SUCCESS_, '');
				exit;
			}
			if ($this->common_model->is_ban_user()) {
				throw new Exception(_BANNED_MSG_, _USER_BANNED_);
				exit;
			}
			$this->load->model('like_model');
			$this->load->model('qa_model');
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
			Util::echo_format_return(_SUCCESS_, $this->get_answer_info($like_info['mark']),'操作成功');
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
}
