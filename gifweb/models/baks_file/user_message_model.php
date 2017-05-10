<?php
/**
 * 用户通知
 * @name User_message_model
 * @desc null
 *
 * @author	 liule1
 * @date 2015年8月27日
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 */
class User_message_model extends MY_Model {
	private $_cache_key_pre = '';
	private $_cache_expire = 60;
	protected $_table = '';

	function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":user_message:";
		$this->load->model('user_model');
	}
	public function _get_table_name($uid) {
		return 'gl_user_message_' . str_pad($uid % 100, 2, '0', STR_PAD_LEFT);
	}

	// --------------------------------------------------------------------------- //
	public function get_unread_count($uid, $update_time_min = 0) {
		$uid = $this->global_func->filter_int($uid);
		$update_time_min = $this->global_func->filter_int($update_time_min);
		if (empty($uid)) {
			return 0;
		}
		$cache_key = $this->_cache_key_pre . "count:$uid";
		$hash_key = "$update_time_min";

		$data = $this->cache->redis->hGet($cache_key, $hash_key);
		if ($data === false) {
			$data = $this->_get_unread_count_from_db($uid, $update_time_min);
			$this->cache->redis->set($cache_key, $hash_key, $data);
			$this->cache->redis->expire($cache_key, $this->_cache_expire);
		}
		return $data;

	}
	private function _get_unread_count_from_db($uid, $update_time_min = 0) {
		$conditions = array(
				'table' => $this->_get_table_name($uid),
				'fields' => 'count(*) as c',
				'where' => array(
						'uid' => $uid,
						'status' => array(
								'in', array(0,1,3),
						),
						'count' => array(
								'>', 0,
						),
				)
		);
		if ($update_time_min > 0) {
			$conditions['where']['update_time'] = array(
					'>=', $update_time_min
			);
		}

		$sql = $this->find($conditions);
		$rs = $this->db->query_read($sql);
		$rs = $rs ? $rs -> row_array() : array();
		return empty($rs['c']) ? 0 : (int)$rs['c'];
	}

	// -------------------------------------------------------------------------------//
	public function get_message($uid) {
		$data = $this->get_message_from_db($uid);

		// <{count}>
		$pattern = '<{count}>';
		foreach ($data as $k => $v) {
			$replace = $v['count'];
			$title = str_replace($pattern, $replace, $v['title']);
			if ($title != $data[$k]['title']) {
				if ($v['count'] == 0) {
					// 数量为0
					unset($data[$k]);
					continue;
				}
			}

			$data[$k]['title'] = $title;
		}

		return $data;
	}
	public function get_message_from_db($uid) {
		$uid = $this->global_func->filter_int($uid);

		// status
// 		0 待推送
// 		1 已推送
// 		2 已读
// 		3 不推送
// 		4 用户删除
//		5 已看过最新|取消	脚本检测得到


		$time = strtotime('-1 month');
		$sql = "SELECT * FROM " . $this->_get_table_name($uid) . "
				WHERE uid='$uid' AND update_time > $time AND status not in (4, 5)
				order by update_time desc LIMIT 100
				";
		$rs = $this->db->query_read($sql);

		return $rs ? $rs->result_array() : array();
	}
	/**
	 * 状态修改成2 ， 标志成读
	 */
	public function got_message($uid, $id = null) {
		$uid = $this->global_func->filter_int($uid);
		$id = $this->global_func->filter_int($id);
		$where = array();
		if ($id) {
			$where[] = " id = '$id'";
		}
		$where[] = " uid = '$uid' ";
		$where[] = ' status in (0,1,3)' ;

		$sql = "UPDATE {$this->_get_table_name($uid)} SET status=2 WHERE " . implode(' AND ', $where);
		$this->db->query_write($sql);

		$cache_key = $this->_cache_key_pre . "count:$uid";
		$this->cache->redis->delete($cache_key);
		return ;
	}
	/**
	 * 状态修改成4 ， 标志成用户删除
	 */
	public function del_message($uid, $id = 'all') {
		$update_data = array(
				'status' => 4, 		//已拉取
		);
		$where = array(
				'uid' => $uid,
// 				'id' => $id
		);

		$limit = null;
		if (strtolower($id) !== 'all') {
			$where['id'] = $id;
			$limit = 1;
		}

		return $this->db->update($this->_get_table_name($uid), $update_data, $where, $limit);
	}
	

}
