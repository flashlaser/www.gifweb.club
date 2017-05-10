<?php
/**
 *
 * @name Answer_model
 * @desc null
 *
 * @author	 liule1
 * @date 2015年8月4日
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 */
class Answer_model extends MY_Model {
	private $_cache_key_pre = '';
	private $_cache_expire = 600;
	protected $_table = 'gl_answer';

	function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":answer:";
		$this->load->model('user_model');
	}
	// ---------------------------------------------------------------------------------- //
	// ---------------------------------------------------------------------------------- //
	public function get_info($aid, $status = array(0,1,2)) {
		if (!$aid) return false;

		$cache_key = $this->_cache_key_pre . 'info:' . "$aid";
		$hash_key = implode('_', $status);

		$data = $this->cache->redis->hGet($cache_key,$hash_key);
		$data && $data = json_decode($data, 1);
		if (!is_array($data)) {
			$data = $this->get_info_from_db($aid, $status);
			$this->cache->redis->hSet($cache_key, $hash_key, json_encode($data));
			$this->cache->redis->expire($cache_key, $this->_cache_expire);
		}

		return $data;
	}

	public function get_info_from_db($aid, $status=array(0,1)) {
		$conditions = array(
				'table' => $this->_table,
				'where' => array(
						'aid' => $aid,
						'status' => array(
								'in',$status
						)
				),
				'limit' => 1,
		);
		$sql = $this->find($conditions);
		$rs = $this->db->query_read($sql);
		return $rs ? $rs->row_array() : array();
	}
	// ---------------------------------------------------------------------------------- //
	// ---------------------------------------------------------------------------------- //
	public function get_list($qid, $offset, $limit, $last_id = 0, $review_state = 'all') {
		if ($last_id) $offset = 0;
		$qid = (int)$qid;

		$cache_key = $this->_cache_key_pre . 'list:' . "$qid";
		$hash_key = "normal:$offset:$limit:$last_id:$review_state";
		$data = $this->cache->redis->hGet($cache_key, $hash_key);
		$data && $data = json_decode($data, 1);

		if (!is_array($data)) {
			$data = $this->get_list_from_db($qid, $offset, $limit, $last_id, 0, 0, $review_state);
			$this->cache->redis->hSet($cache_key, $hash_key, json_encode($data));
			$this->cache->redis->expire($cache_key, $this->_cache_expire);
		}

		return $data;
	}

	public function get_a_list_by_uid($offset, $limit, $last_id = 0, $uid = 0,$is_my_answer=1) {
		if ($last_id) $offset = 0;

		$cache_key = $this->_cache_key_pre . 'get_a_list_by_uid:' . "$uid";
		$hash_key = "normal:$offset:$limit:$last_id";
		$data = $this->cache->redis->hGet($cache_key, $hash_key);
		$data && $data = json_decode($data, 1);

		if (!is_array($data)) {
			$data = $this->get_list_from_db('', intval($offset), intval($limit),$last_id, $uid,$is_my_answer);
			$this->cache->redis->hSet($cache_key, $hash_key, json_encode($data));
			$this->cache->redis->expire($cache_key, $this->_cache_expire);
		}
		return $data;
	}

	public function get_list_from_db($qid, $offset, $limit, $last_id = 0, $uid = 0,$is_my_answer='', $review_state = 'all') {
		$conditions = array(
				'table' => $this->_table,
				'where' => array(
				),
				'start' => intval($offset),
				'limit' => intval($limit),
				'order' => 'update_time desc',
		);

		$uid && $conditions['where'] += array(
				'uid' => $uid,
		);
		$qid && $conditions['where'] += array(
				'qid' => $qid,
		);
		$last_id > 0 && $conditions['where'] += array(
			'aid' => array(
					'<', $last_id
			)
		);
		if($is_my_answer == 1){
			$conditions['where'] += array(
				'status' => array(
					'in', array(0,1,2)
				)
			);
		}else{
			$conditions['where'] += array(
				'status' => array(
					'in', array(0,1)
				)
			);
		}
		if ($review_state !== 'all') {
			$conditions['where']['review_state'] = (int)$review_state;
		}


		$sql = $this->find($conditions);
// 		echo $sql;exit;
		$rs = $this->db->query_read($sql);
		return $rs ? $rs->result_array() : array();
	}

	//uid 获取答案总数
	public function get_count_by_uid_from_db($uid = 0,$is_my_answer = '')
	{
		$where = ' 1=1 ';
		if($uid>0)
		{
			$where .=' and uid='.$uid;
		}
		if($is_my_answer)
		{
			$where .=' and status in(0,1,2) ';
		}
		else
		{
			$where .=' and status in(0,1) ';
		}
		$sql = "select count(*) as counts from ".$this->_table." where ".$where;
		$rs = $this->db->query_read($sql);
		$res_data_count = $rs->row_array();
		return $res_data_count['counts'];
	}
	//qid 获取答案总数
	public function get_count_by_qid_from_db($qid)
	{
		$where = ' qid= '.$qid;
		$where .=' and status in(0,1) ';
		$sql = "select count(*) as counts from ".$this->_table." where ".$where;
		$rs = $this->db->query_read($sql);
		$res_data_count = $rs->row_array();
		return $res_data_count['counts'];
	}


	public function is_hot($qid, $aid) {
		$cache_key = $this->_cache_key_pre . "is_hot:$qid";
		$hash_key = $aid;
		$r = $this->cache->redis->hGet($cache_key, $hash_key);

		return $r ? true : false;
	}

	/**
	 * @param unknown $type
	 * @param unknown $mark
	 */
	public function get_hot_list($qid) {
		$qid = (int) $qid;

		$cache_key = $this->_cache_key_pre . "hot_list:$qid";
		$data = $this->cache->redis->get($cache_key);
		$data && $data = json_decode($data, 1);
		if (!is_array($data)) {
			$data = $this->get_hot_list_from_db($qid);
			$this->cache->redis->set($cache_key, json_encode($data), $this->_cache_expire);

			// 设置热门标识
			$cache_key = $this->_cache_key_pre . "is_hot:$qid";
			$this->cache->redis->delete($cache_key);
			$this->cache->redis->hSet($cache_key, 'flag', 1);
			$this->cache->redis->expire($cache_key, 86400 * 30);
			foreach ($data as $v) {
				$hash_key = $v['aid'];
				$this->cache->redis->hSet($cache_key, $hash_key, 1);
			}


			// 经验
			$this->load->model('exp_model');
			foreach ($data as $k => $v) {
				if ($add_exp = $this->exp_model->add_exp($v['uid'], 4, $v['aid'])) {
					// 增加经验通知
					$this->load->model('push_message_model');
					$this->push_message_model->push(2, 4, $v['aid'], 1, 0, $add_exp);
				}
			}
		}

		return $data;
	}
	private function get_hot_list_from_db($qid) {
		$hot_con = $this->User->getCfg('anwser_hot_con');
		if (isset($hot_con['anwser_hot_con']['g_num']) && isset($hot_con['anwser_hot_con']['n_num'])) {
			$con_str = " AND (mark_up_rank_0_count > {$hot_con['anwser_hot_con']['n_num']} OR mark_up_rank_1_count > {$hot_con['anwser_hot_con']['g_num']})";
		}
		$qid = (int) $qid;
		$limit = 3;
		$sql = "SELECT * FROM gl_answer WHERE qid='$qid' and status in (0, 1) AND ( weight > 0 OR (auto_weight > 0 $con_str)) ORDER BY weight DESC, auto_weight DESC LIMIT $limit";
		$rs = $this->db->query_read($sql);

		$data = $rs ? $rs->result_array() : array();
		return $data;
	}


	public function get_secondary_hot_list($qid) {
		$qid = (int) $qid;

		$cache_key = $this->_cache_key_pre . "secondary_hot_list:$qid";

		$data = $this->cache->redis->get($cache_key);
		$data && $data = json_decode($data, 1);
		if (!is_array($data)) {
			$data = $this->get_secondary_hot_list_from_db($qid);
			$this->cache->redis->set($cache_key, json_encode($data), 86400);
		}

		return $data;
	}
	private function get_secondary_hot_list_from_db($qid) {
		$qid = (int) $qid;
		$limit = 1;
		$sql = "SELECT * FROM gl_answer WHERE qid='$qid' and status in (0, 1) ORDER BY mark_up_rank_0_count + mark_up_rank_1_count + mark_up_virtual_count DESC, auto_weight,update_time DESC LIMIT $limit";
		$rs = $this->db->query_read($sql);

		$data = $rs ? $rs->result_array() : array();
		return $data;
	}
	// ------------------------------------------------------------------------------------//
	public function get_normal_answer_count($qid) {
		$qid = (int)$qid;
		$sql = "SELECT COUNT(*) as c FROM {$this->_table} WHERE qid='$qid' AND status in (0,1)";
		$rs = $this->db->query_write($sql);
		$rs = $rs->row_array();
		return $rs ? $rs['c'] : 0;
	}

	// ---------------------------------------------------------------------------------- //
	public function insert($uid, $qid) {
		if (empty($uid) || empty($qid) ) {
			return false;
		}

		$return = $this->_insert_to_db($uid, $qid);

		$this->_clear_hot_list($qid);
		$this->_clear_info($return);
		$this->_clear_answer_list();

		
		$cache_key = $this->_cache_key_pre . 'list:' . "$qid";
		$this->cache->redis->delete($cache_key);

		//更新提问数
		$update_data = array(
		    'answer_num' => array("answer_num+1", FALSE)
		);
		$this->user_model->update_user($uid, $update_data);
		
		return $return;
	}
	private function _insert_to_db($uid, $qid) {
		if (empty($uid) || empty($qid) ) {
			return false;
		}
		$time = time();
		$status = 0;
		$insert_data = array(
				'uid' => $uid,
				'qid' => $qid,
				'follow_rank_0_count' => 0,
				'follow_rank_1_count' => 0,
				'comment_count' => 0,
				'complaint_count' => 0,
				'mark_up_rank_0_count' => 0,
				'mark_up_rank_1_count' => 0,
				'mark_down_rank_0_count' => 0,
				'mark_down_rank_1_count' => 0,
				'weight' => 0,
				'status' => $status,
				'update_time' => $time,
				'create_time' => $time,
		);

		$this->db->insert($this->_table, $insert_data);

		return $this->db->insert_id_write();
	}

	// ========================================= 更新 GO ===========================================================//
	public function _clear_info($aid) {
		$cache_key = $this->_cache_key_pre . 'info:' . "{$aid}";
		$this->cache->redis->delete($cache_key);
	}
	private function _clear_hot_list($qid) {
		$cache_key = $this->_cache_key_pre . 'hot_list:' . "{$qid}";
		$this->cache->redis->delete($cache_key);
		$cache_key = $this->_cache_key_pre . 'secondary_hot_list:' . "{$qid}";
		$this->cache->redis->delete($cache_key);
	}
	private function _clear_hot_list_after_check($qid, $aid) {
		if ($this->is_hot($qid, $aid)) {
			$this->_clear_hot_list($qid);
		}
	}
	private function _clear_list($qid) {
		$cache_key = $this->_cache_key_pre . 'list:' . "{$qid}";
		$this->cache->redis->delete($cache_key);
	}
	public function _clear_answer_list() {
		$cache_key = $this->_cache_key_pre . 'get_a_list_by_uid:' . $this->user_id;
		$this->cache->redis->delete($cache_key);
	}
	// -------------------------------------------------------------------------------------------------------//
	private function _update($update_data, $where, $limit = 1, $clean_cache_type = 1) {
		if (empty($update_data) || empty($where) ) {
			return false;
		}
		$return = $this->_update_to_db($update_data, $where);


		if ($return) {
			// 删除缓存
			is_array($clean_cache_type) || $clean_cache_type = array($clean_cache_type);
			foreach ($clean_cache_type as $c_type) {
				if ($c_type == 1) {
					// 清除详情
					if (isset($where['aid'])) {
						$this->_clear_info($where['aid']);
					}
				}

				if ($c_type == 2) {
					// 清除问题的热门答案
					if (isset($where['aid'])) {
						empty($answer_info) && $answer_info = $this->get_info($where['aid'], array(0,1,2,3));
						$this->_clear_hot_list($answer_info['qid']);
					}
				}

				if ($c_type == 3) {
					// 清除问题的列表
					if (isset($where['aid'])) {
						empty($answer_info) && $answer_info = $this->get_info($where['aid'], array(0,1,2,3));
						$this->_clear_list($answer_info['qid']);
					}
				}

				if ($c_type == 4) {
					// 清除我的回答列表
					$this->_clear_answer_list();
				}


				if ($c_type == 5) {
					// 有选择的清除问题的热门答案
					if (isset($where['aid'])) {
						empty($answer_info) && $answer_info = $this->get_info($where['aid'], array(0,1,2,3));
						$this->_clear_hot_list_after_check($answer_info['qid'], $where['aid']);
					}
				}
			}
		}


		return $return;
	}
	private function _update_to_db($update_data, $where, $limit = 1) {
		if (empty($update_data) || empty($where) ) {
			return false;
		}

		foreach ($update_data as $k => $v) {
			if (is_array($v)) {
				$this->db->set($k, $v[0], $v[1]);
			} else {
				$this->db->set($k, $v);
			}
		}
		$this->db->where($where)->from($this->_table)->limit($limit)->update();

		// 		$this->db->update($this->_table, $update_data, $where, 1);
		return $this->db->affected_rows_write();
	}
	// ---------------------------------------------------------------------------------- //
	public function update_content($uid, $aid) {
		$time = time();
		$update_data = array(
				'status' => 0,
				'update_time' => $time,
		);
		$where = array(
				'aid' => $aid,
				'uid' => $uid,
		);
		return $this->_update($update_data, $where, 1, array(1,2,3,4));
	}
	public function add_comment_count($aid, $add = 1) {
		$update_data = array(
				'comment_count' => array('comment_count + ' . intval($add), FALSE),
		);
		$where = array(
				'aid' => $aid,
		);
		if ($add < 0) {
			$where['comment_count >='] = abs($add);
		}

		return $this->_update($update_data, $where, 1, 1);
	}


	/**
	 * 赞数量
	 * @param unknown $aid
	 * @param unknown $uid
	 * @param number $add
	 */
	public function add_mark_up_count($aid, $uid, $add = 1) {
		if (empty($uid) || empty($aid) || empty($add)) {
			return false;
		}

		$user_info = $this->user_model->getUserInfoById($uid);
		if (empty($user_info)) {
			return false;
		}

		$where = array(
				'aid' => $aid,
		);

		$update_data = array();
		if ($user_info['rank'] == 0) {
			$update_data['mark_up_rank_0_count'] = array('mark_up_rank_0_count + ' . intval($add), FALSE);
			if ($add < 0) {
				$where['mark_up_rank_0_count >='] = abs($add);
			}
		} elseif ($user_info['rank'] == 1) {
			$update_data['mark_up_rank_1_count'] = array('mark_up_rank_1_count + ' . intval($add), FALSE);
			if ($add < 0) {
				$where['mark_up_rank_1_count >='] = abs($add);
			}
		} else {
			return false;
		}

		// 计算权重，取消时不操作
		$this->load->model('User_redis_model', 'uredis');
		$w = $this->uredis->countAnswerWeights($aid, $user_info['rank'], 1);
		if($w !== false){
			$w = $add > 0 ? $w : -$w;
			$update_data['auto_weight'] = array('auto_weight + ' . intval($w), FALSE);
		}

		return $this->_update($update_data, $where, 1, array(1,2,5,3));
	}

	/**
	 * 踩数量
	 * @param unknown $aid
	 * @param unknown $uid
	 * @param number $add
	 */
	public function add_mark_down_count($aid, $uid, $add = 1) {
		if (empty($uid) || empty($aid) || empty($add)) {
			return false;
		}

		$user_info = $this->user_model->getUserInfoById($uid);
		if (empty($user_info)) {
			return false;
		}

		$where = array(
				'aid' => $aid,
		);
		$update_data = array();
		if ($user_info['rank'] == 0) {
			$update_data['mark_down_rank_0_count'] = array('mark_down_rank_0_count + ' . intval($add), FALSE);
			if ($add < 0) {
				$where['mark_down_rank_0_count >='] = abs($add);
			}
		} elseif ($user_info['rank'] == 1) {
			$update_data['mark_down_rank_1_count'] = array('mark_down_rank_1_count + ' . intval($add), FALSE);
			if ($add < 0) {
				$where['mark_down_rank_1_count >='] = abs($add);
			}
		} else {
			return false;
		}

		// 计算权重，取消时不操作
		$this->load->model('User_redis_model', 'uredis');
		$w = $this->uredis->countAnswerWeights($aid, $user_info['rank'], 2);
		if($w !== false){
			$w = $add > 0 ? $w : -$w;
			$update_data['auto_weight'] = array('auto_weight + ' . intval($w), FALSE);
		}

		return $this->_update($update_data, $where, 1, 1);
	}


	/**
	 * 关注数量
	 * @param unknown $aid
	 * @param unknown $uid
	 * @param number $add
	 */
	public function add_follow_count($aid, $uid, $add = 1) {
		if (empty($uid) || empty($aid) || empty($add)) {
			return false;
		}

		$user_info = $this->user_model->getUserInfoById($uid);
		if (empty($user_info)) {
			return false;
		}

		$where = array(
				'aid' => $aid,
		);
		$update_data = array();
		if ($user_info['rank'] == 0) {
			$update_data['follow_rank_0_count'] = array('follow_rank_0_count + ' . intval($add), FALSE);
			if ($add < 0) {
				$where['follow_rank_0_count >='] = abs($add);
			}
		} elseif ($user_info['rank'] == 1) {
			$update_data['follow_rank_1_count'] = array('follow_rank_1_count + ' . intval($add), FALSE);
			if ($add < 0) {
				$where['follow_rank_1_count >='] = abs($add);
			}
		} else {
			return false;
		}

		// 计算权重，取消时不操作
		$this->load->model('User_redis_model', 'uredis');
		$w = $this->uredis->countAnswerWeights($aid, $user_info['rank'], 3);
		if($w !== false){
			$w = $add > 0 ? $w : -$w;
			$update_data['auto_weight'] = array('auto_weight + ' . intval($w), FALSE);
		}

		return $this->_update($update_data, $where, 1, 1);
	}

	/**
	 * 投诉
	 * @param unknown $qid
	 * @param number $add
	 */
	public function add_complaint_count($aid, $add = 1) {
		if (empty($aid) || empty($add)) {
			return false;
		}


		$update_data = array();
		$update_data['complaint_count'] = array('complaint_count + ' . intval($add), FALSE);

		$where = array(
				'aid' => $aid,
		);

		if ($add < 0) {
			$where['complaint_count >='] = abs($add);
		}

		return $this->_update($update_data, $where, 1, 0);
	}


	/**
	 * 置状态为3：删除
	 * @param unknown $aid
	 */
	public function update_status_to_3($aid, $uid) {
		if (empty($aid)) {
			return false;
		}

		$update_data = array();
		$update_data['status'] = 3;

		$where = array(
				'aid' => $aid,
				'uid' => $uid,
		);

		//取消收藏的答案
		$this->load->model('Follow_model', 'follow');
		$this->follow->follow($uid,2,$aid,-1);

		return $this->_update($update_data, $where, 1, array(1, 2, 3, 4, 5));
	}


	// ========================================= 更新 END ===========================================================//

	public function check_ownership($uid, $mark) {
		if (empty($uid) || empty($mark)) {
			return false;
		}
		$data = $this->get_info($mark);

		return (!empty($data) && $data['uid'] == $uid) ? true : false;
	}

}
