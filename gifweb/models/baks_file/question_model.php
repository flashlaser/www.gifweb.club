<?php
/**
 *
 * @name follow_model
 * @desc null
 *
 * @author	 liule1
 * @date 2015年7月27日
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 *
 * @property	Global_func		$global_func
 * @property	user_model		$user_model
 */
class Question_model extends MY_Model {
	private $_cache_key_pre = '';
	private $_cache_expire = 600;
	protected $_table = 'gl_question';

	function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":question8:";
		$this->load->model('user_model');
		$this->load->model('answer_model');
	}
	// ---------------------------------------------------------------------------------- //
	public function get_info($qid, $status = array(0,1)) {
		if (!$qid) return false;
		if (empty($status) || !is_array($status)) return false;

		$cache_key = $this->_cache_key_pre . 'info:' . "$qid";
		$hash_key = implode('_', $status);

		$data = $this->cache->redis->hGet($cache_key, $hash_key);
		$data && $data = json_decode($data, 1);
		if (!is_array($data)) {
			$data = $this->get_info_from_db($qid, $status);
			// 自动修正 normal_answer_count，数据库count老是对不上
			if (!empty($data)) {
				$_c = $this->answer_model->get_normal_answer_count($qid);
				if ($data['normal_answer_count'] != $_c) {
					$this->set_normal_answer_count($qid, $_c);
					$data['normal_answer_count'] = $_c;
				}
			}

			$this->cache->redis->hSet($cache_key, $hash_key,json_encode($data));
			$this->cache->redis->expire($cache_key, $this->_cache_expire);
		}

		return $data;
	}

	public function get_info_from_db($qid, $status=array(0,1)) {
		$conditions = array(
				'table' => $this->_table,
				'where' => array(
						'qid' => $qid,
						'status' => array(
								'in', $status
						)
				),
				'limit' => 1,
		);
		$sql = $this->find($conditions);
		$rs = $this->db->query_read($sql);
		return $rs ? $rs->row_array() : array();
	}
	// ---------------------------------------------------------------------------------- //
	/**
	 * 把gid相关的redis key ，put 进 redis set， 有改动时，能删除相关key
	 * @param unknown $gids
	 */
	private function _put_set_cache_key($gids) {
		if (empty($gids)) {
			return 0;
		}
		if (!is_array($gids)) {
			return false;
		}

		$gids = array_unique($gids);
		foreach ($gids as $k => $v) {
			$cache_key = $this->_cache_key_pre . "list_affect_keys:$v";
			$this->cache->redis->sAdd($cache_key, $this->_get_list_cache_key($gids));
			$this->cache->redis->expire($cache_key, $this->_get_list_cache_expire());
		}

		return 1;
	}
	public function delete_list_cache($gid) {
		$cache_key = $this->_cache_key_pre . "list_affect_keys:$gid";
		$members = $this->cache->redis->sMembers($cache_key);
		if (!empty($members)) {
			foreach ($members as $v) {
				$this->cache->redis->delete($v);
			}
			$this->cache->redis->delete($cache_key);
		}
		$this->cache->redis->delete($this->_get_list_cache_key(''));	// 总体
		return 1;
	}

	// ------------------------------------------------------------------------------------------//

	private function _get_list_cache_key($gids = array()) {
		if (empty($gids)) {
			$gids_str = 'all';
		} else {
			$gids = array_unique($gids);
			sort($gids);
			$gids_str = md5(json_encode($gids));
		}
		$cache_key = $this->_cache_key_pre . 'list:' . "$gids_str";
		return $cache_key;
	}
	private function _get_list_cache_expire() {
		return $this->_cache_expire;
	}
	// -------------------------------------------------------------------------------------//


	private function _aftermath($uid) {
		// delete cache

		$cache_key = $this->_cache_key_pre . 'get_q_list_by_uid:' . $uid;

		$this->cache->redis->delete($cache_key);

		return 1;
	}

	public function get_list($gids, $offset, $limit, $review_state = 'all') {
		if (!empty($gids) && !is_array($gids)) {
			return false;
		}

		$cache_key = $this->_get_list_cache_key($gids);
		$hash_key = "normal:$offset:$limit:$review_state";
		$data = $this->cache->redis->hGet($cache_key, $hash_key);
		$data && $data = json_decode($data, 1);
		if (!is_array($data)) {
			$data = $this->get_list_from_db($gids, $offset, $limit, 0, 'sort_time desc', array(0, 1), $review_state);
			$this->cache->redis->hSet($cache_key, $hash_key, json_encode($data));
			$this->cache->redis->expire($cache_key, $this->_get_list_cache_expire());
			$this->_put_set_cache_key($gids);
		}

		return $data;
	}

	public function get_lists_id_by_uid($uid ,$offset, $limit,$is_other='') {
		$cache_key = $this->_cache_key_pre . 'get_q_list_by_uid:' . $uid;
		$hash_key = "normal:$offset:$limit";
		$data = $this->cache->redis->hGet($cache_key, $hash_key);
		$data && $data = json_decode($data, 1);
		if (!is_array($data)) {
		    if($is_other){
		        $status = array(0, 1);
		    }else{
		        $status = array(0, 1,2);
		    }
			$arr = $this->get_list_from_db('', intval($offset), intval($limit), $uid, 'update_time desc' ,$status);
			$data = array();
			foreach ($arr as $v) {
				$data[] = $v['qid'];
			}
			$this->cache->redis->hSet($cache_key, $hash_key, json_encode($data));
			$this->cache->redis->expire($cache_key, $this->_cache_expire);
		}

		return $data;
	}

	public function get_list_from_db($gids, $offset, $limit, $uid = 0, $order = 'sort_time desc',$status = array(0, 1), $review_state = 'all') {
		$conditions = array(
				'table' => $this->_table,
				'where' => array(
				),
				'start' => $offset,
				'limit' => $limit,
				'order' => $order,
		);

		$uid &&  $conditions['where'] += array(
				'uid' => $uid,
		);
		$gids && is_array($gids) && $conditions['where'] += array(
				'gid' => array(
						'in', $gids
				),
		);
		$conditions['where'] += array(
			'status' => array(
				'in', $status
			)
		);
		if ($review_state !== 'all') {
			$conditions['where']['review_state'] = (int) $review_state;
		}

		$sql = $this->find($conditions);
		$rs = $this->db->query_read($sql);
		return $rs ? $rs->result_array() : array();
	}
	
	//uid 获取问题数
	public function get_count_by_uid_from_db($uid = 0,$is_my_question = '')
	{		
		$where = ' 1=1 ';
		if($uid>0)
		{
			$where .=' and uid='.$uid;
		}
		if($is_my_question)
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


	// --------------------------------------------------------------------------------------------//

	public function get_hot_list($gid=array(), $num = 5) {
		if(is_array($gid) && count($gid) > 0){
			sort($gid);
			$gid_str = implode(',', $gid);
		}else{
			$gid_str = '';
		}

		$cache_key = $this->_cache_key_pre . "hot_list:" . md5($gid_str) . ":$num";
		$data = $this->cache->redis->get($cache_key);
		$data && $data = json_decode($data, true);
		if ($data === false) {
			$data = $this->_get_hot_list_from_db($gid, $num);
			// 经验
			$this->load->model('exp_model');
			foreach ($data as $k => $v) {
				if ($add_exp = $this->exp_model->add_exp($v['uid'], 3, $v['qid'])) {
					// 增加经验通知
					$this->load->model('push_message_model');
					$this->push_message_model->push(1, 4, $v['qid'],1 , 0, $add_exp);
				}
			}
			$this->cache->redis->set($cache_key, json_encode($data), $this->_cache_expire);
		}

		return $data;
	}

	private function _get_hot_list_from_db($gid=array(), $num = 5) {
		if(is_array($gid) && count($gid) > 0){
			sort($gid);
			$gid_str = implode(',', $gid);
		}else{
			$gid_str = '';
		}

		// 当前时间
		$now_time = time();
		$week_time= $now_time - 3600 * 24 * 8;
		$num = ($num>50 || $num<1) ? 5 : $num;

		// 获取后台设置热门
		if($gid_str){
			$sql = " select * from gl_question where status in (0,1) AND weight>0 and gid in (".$gid_str.") and lose_time>'".$now_time."' order by weight desc limit ".$num;
		}else{
			$sql = " select * from gl_question where status in (0,1) AND weight>0 and lose_time>'".$now_time."' order by weight desc limit ".$num;
		}
		$query = $this->db->query_read($sql);
		$data   = $query ? $query->result_array() : array();

		$total = count($data);

		$qid_arr = array();
		// 获取自动计算热门
		while($total < $num){
			// 过滤重复
			foreach ($data as $v) {
				$qid_arr[] = $v['qid'];
			}

			if ($gid_str) {
				$sql = " select qid from gl_weight_log where addtime>'".$week_time."' and game_id in (".$gid_str.") ";
			} else {
				$sql = " select qid from gl_weight_log where addtime>'".$week_time."' ";
			}

			if ($qid_arr) {
				$sql .= " AND qid not in (" . implode(',', $qid_arr) . ") ";
			}
			$sql .= ' group by qid order by sum(weights) desc LIMIT ' . $num ;

			$result = $this->db->query_read($sql);
			$rs = $result ? $result->result_array() : array();

			$data1 = array();
			if(count($rs)>0){
				foreach($rs as $k=>$v){
					if ($total >= $num) {
						break;
					}
					$qid_arr[] = $v['qid'];
					$_arr = $this->get_info($v['qid']);
					if (empty($_arr)) {
						continue;	// 已关闭或删除
					}
					$data1[] = $_arr;
					$total++;
				}
			} else {
				break;	// 找不到合适的了，直接退出
			}
			$data = array_merge($data, $data1);
			$total = count($data);
		}

		return $data;
	}


	// ---------------------------------------------------------------------------------- //
	public function insert($uid, $gid, $game_name = '') {
		if (empty($uid) ) {
			return false;
		}

		$return = $this->_insert_to_db($uid, $gid, $game_name);

		$this->delete_list_cache($gid);
		$cache_key = $this->_cache_key_pre . 'info:' . "{$return}";
		$this->cache->redis->delete($cache_key);
		$this->_aftermath($uid);

		//更新提问数
		$update_data = array(
		    'ask_num' => array("ask_num+1", FALSE)
		);
		$this->user_model->update_user($uid, $update_data);
		return $return;
	}
	private function _insert_to_db($uid, $gid, $game_name = '') {
		if (empty($uid) ) {
			return false;
		}
		$gid = (int)$gid;
		$time = time();
		$status = 0;
		$insert_data = array(
				'uid' => $uid,
				'gid' => $gid,
				'gname' => $game_name,
				'follow_count' => 0,
				'normal_answer_count' => 0,
				'hot_answer_count' => 0,
				'pv' => 0,
				'complaint_count' => 0,
				'weight' => 0,
				'status' => $status,
				'sort_time' => $time,
				'update_time' => $time,
				'create_time' => $time,
		);

		$this->db->insert($this->_table, $insert_data);
		return $this->db->insert_id_write();
	}


	// ========================================= 更新 GO ===========================================================//
	// -------------------------------------------------------------------------------------------------------//
	/**
	 *
	 * @param unknown $update_data
	 * @param unknown $where
	 * @param number $limit
	 * @param number $clean_cache_type : 1 删除info缓存
	 * 									2 删除用户“我的提问”缓存
	 * 									3 删除gid列表缓存
	 */
	private function _update($update_data, $where, $limit = 1, $clean_cache_type = 1) {
		if (empty($update_data) || empty($where) ) {
			return false;
		}
		$return = $this->_update_to_db($update_data, $where);


		if ($return) {
			is_array($clean_cache_type) || $clean_cache_type = array($clean_cache_type);
			foreach ($clean_cache_type as $clean_type) {
				// 删除缓存
				if ($clean_type == 1) {
					if (isset($where['qid'])) {
						$cache_key = $this->_cache_key_pre . 'info:' . "{$where['qid']}";
						$this->cache->redis->delete($cache_key);
					}
				}

				if ($clean_type == 2) {
					if (isset($where['uid'])) {
						$this->_aftermath($where['uid']);
					}
				}

				if ($clean_type == 3) {
					if (empty($where['gid'])) {
						$question_info = empty($where['qid']) ? array() : $this->get_info($where['qid'], array(0,1,2));
						$gid = empty($question_info['gid']) ? 0 : $question_info['gid'];
					} else {
						$gid = $where['gid'];
					}

					if ($gid) {
						$this->delete_list_cache($gid);
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
	public function update_content($uid, $qid, $gid, $game_name = '') {
		$time = time();
		$update_data = array(
				'status' => 0,
				'update_time' => $time,
				'gid' => $gid,
				'gname' => $game_name,
		);
		$where = array(
				'qid' => $qid,
				'uid' => $uid,
		);
		return $this->_update($update_data, $where, 1, array(1,2));
	}
	public function add_normal_answer_count($qid, $add = 1) {
		$update_data = array(
				'normal_answer_count' => array('normal_answer_count + ' . intval($add), FALSE),
		);
		$where = array(
				'qid' => $qid,
		);

		if ($add < 0) {
			$where['normal_answer_count >='] = abs($add);
		}

		return $this->_update($update_data, $where, 1, 1);
	}

	public function set_normal_answer_count($qid, $count) {
		$update_data = array(
				'normal_answer_count' =>  intval($count),
		);
		$where = array(
				'qid' => $qid,
		);

		return $this->_update($update_data, $where, 1, 1);
	}

	/**
	 * 踩数量
	 * @param unknown $aid
	 * @param unknown $uid
	 * @param number $add
	 */
	public function add_follow_count($qid, $add = 1) {
		if (empty($qid) || empty($add)) {
			return false;
		}


		$update_data = array();
		$update_data['follow_count'] = array('follow_count + ' . intval($add), FALSE);

		$where = array(
				'qid' => $qid,
		);

		if ($add < 0) {
			$where['follow_count >='] = abs($add);
		}

		return $this->_update($update_data, $where, 1, 1);
	}

	/**
	 * 投诉
	 * @param unknown $qid
	 * @param number $add
	 */
	public function add_complaint_count($qid, $add = 1) {
		if (empty($qid) || empty($add)) {
			return false;
		}


		$update_data = array();
		$update_data['complaint_count'] = array('complaint_count + ' . intval($add), FALSE);

		$where = array(
				'qid' => $qid,
		);
		if ($add < 0) {
			$where['complaint_count >='] = abs($add);
		}

		return $this->_update($update_data, $where, 1, 0);
	}

	/**
	 * PV
	 * @param unknown $qid
	 * @param number $add
	 */
	public function add_pv_count($qid, $add = 1) {
		if (empty($qid) || empty($add)) {
			return false;
		}


		$update_data = array();
		$update_data['pv'] = array('pv + ' . intval($add), FALSE);

		$where = array(
				'qid' => $qid,
		);
		if ($add < 0) {
			$where['pv >='] = abs($add);
		}

		return $this->_update($update_data, $where, 1, 0);
	}

	/**
	 * 更新排序时间
	 * @param unknown $qid
	 */
	public function update_sort_time($qid) {
		$qid = $this->global_func->filter_int($qid);
		if (empty($qid)) {
			return false;
		}

		$update_data = array();
		$update_data['sort_time'] = time();

		$where = array(
				'qid' => $qid,
		);

		return $this->_update($update_data, $where, 1);
	}
	// ========================================= 更新 END ===========================================================//


	public function check_ownership($uid, $mark) {
		if (empty($uid) || empty($mark)) {
			return false;
		}
		$data = $this->get_info($mark);

		return (!empty($data) && $data['uid'] == $uid) ? true : false;
	}

	// ---------------------------------------- content --------------------------------------//


	public function get_hot_list_wap($gid=array()) {
	    if(is_array($gid) && count($gid) > 0){
	        sort($gid);
	        $gid_str = implode(',', $gid);
	    }else{
	        $gid_str = '';
	    }
	    $cache_key = $this->_cache_key_pre . "hot_list_wap:" . md5($gid_str) ;
	    $data = $this->cache->redis->get($cache_key);
	    $data && $data = json_decode($data, true);
	    $data =false;
	    if ($data === false) {
	        $data = $this->_get_hot_list_from_db_wap($gid);
	        // 经验
	        $this->load->model('exp_model');
	        foreach ($data as $k => $v) {
	            $this->exp_model->add_exp($v['uid'], 3, $v['qid']);
	        }
	        $this->cache->redis->set($cache_key, json_encode($data), $this->_cache_expire);
	    }

	    return $data;
	}

	private function _get_hot_list_from_db_wap($gid=array()) {
	    if(is_array($gid) && count($gid) > 0){
	        sort($gid);
	        $gid_str = implode(',', $gid);
	    }else{
	        $gid_str = '';
	    }

	    // 当前时间
	    $now_time = time();
	    $week_time= $now_time - 3600 * 24 * 8;

	    // 获取后台设置热门
	    if($gid_str){
	        $sql = " select * from gl_question where status in (0,1) AND weight>0 and gid in (".$gid_str.") and lose_time>'".$now_time."' order by weight desc ";
	    }else{
	        $sql = " select * from gl_question where status in (0,1) AND weight>0  order by weight desc ";
	    }
	    $query = $this->db->query_read($sql);
	    $data   = $query ? $query->result_array() : array();
	    return $data;
	}
}
