<?php
/**
 * 
 * @name Comment_model
 * @desc null
 *
 * @author	 liule1
 * @date 2015年7月27日
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 * 
 * @property	Global_func		$global_func
 */
class Comment_model extends MY_Model {
	private $_cache_key_pre = '';
	private $_cache_expire = 600;
// 	protected  $_table = 'gl_comments';	
	
	function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":comment:";
	}
	
	private function _get_table_name ($mark) {
		return 'gl_comments_' . (ord(substr($mark, -1)) % 10);
	}
	// ---------------------------------------------------------------------------------- //
	public function get_data($type, $mark, $offset, $limit, $last_id = 0) {
		if ($last_id) $offset = 0;
		
		$cache_key = $this->_cache_key_pre . 'list:' . "$type:$mark";
		$hash_key = "normal:$offset:$limit:$last_id";
		$data = $this->cache->redis->hGet($cache_key, $hash_key);
		$data && $data = json_decode($data, 1);
		if (!is_array($data)) {
			$data = $this->get_data_from_db($type, $mark, $offset, $limit, $last_id);
			$this->cache->redis->hSet($cache_key, $hash_key, json_encode($data));
			$this->cache->redis->expire($cache_key, $this->_cache_expire);
		}
		
		return $data;
	}
	public function get_data_from_db($type, $mark, $offset, $limit, $last_id = 0) {
		$conditions = array(
				'table' => $this->_get_table_name($mark),
				'where' => array(
						'mark' => $mark,
						'type' => $type,
						'status'=> array('in','0,1'),
				),
				'start' => $offset,
				'limit' => $limit,
				'order' => 'id desc',
		);
		$last_id > 0 && $conditions['where'] += array(
			'id' => array(
					'<', $last_id
			)	
		);
		$sql = $this->find($conditions);
		$rs = $this->db->query_read($sql);
		return $rs ? $rs->result_array() : array();
	}
	
	public function get_data_count($type, $mark) {
		$cache_key = $this->_cache_key_pre . 'list:' . "$type:$mark";
		$hash_key = "count";
		
		$data = $this->cache->redis->hGet($cache_key, $hash_key);
		if ($data === false) {
			$data = $this->get_data_count_from_db($type, $mark);
			$this->cache->redis->hSet($cache_key, $hash_key, $data);
			$this->cache->redis->expire($cache_key, $this->_cache_expire);
		}
		
		return $data;
	}
	public function get_data_count_from_db($type, $mark) {
		$conditions = array(
				'table' => $this->_get_table_name($mark),
				'fields' => 'count(*) as c',
				'where' => array(
						'mark' => $mark,
						'type' => $type,
						'status'=> array('in','0,1'),
				),
				
		);
		$sql = $this->find($conditions);
		$rs = $this->db->query_read($sql);
		$rs = $rs ? $rs->row_array() : array();
		return empty($rs['c']) ? 0 : $rs['c'];
	}
	// ---------------------------------------------------------------------------------- //
	public function add_data($uid, $type, $mark, $content) {
		if (empty($uid) || empty($type) || empty($mark) || empty($content)) {
			return false;
		}
		// TODO 其他限制条件，比如屏蔽字
		
		
		$affect = 1;
		if ($type == 1) {
			// 攻略
			$this->load->model('article_model');
			$affect = $this->article_model->updateArticleCommentCount($mark,1);
		} elseif ($type == 2) {
			// 答案
			$this->load->model('answer_model');
			$affect = $this->answer_model->add_comment_count($mark, 1);
		}
		
		// 没有生效
		if (!$affect) {
			return false;
		}
		
		$this->_insert_data_to_db($uid, $type, $mark, $content);
		
		// delete cache 
		$cache_key = $this->_cache_key_pre . 'list:' . "$type:$mark";
		$this->cache->redis->delete($cache_key);
		
		
		if ($type  == 2) {
			// 答案			
			// 推送
			$this->load->model('push_message_model');
			$_push_type = 2;	// 答案
			$_push_flag = 2;	// 新增评论
			$_push_mark = $mark;
			$this->push_message_model->push($_push_type, $_push_flag, $_push_mark);
		}
		
		return 1;
	}
	private function _insert_data_to_db($uid, $type, $mark, $content) {
		$ip = $this->global_func->get_remote_ip();
		$insert_data = array(
				'uid' => $uid,
				'mark' => $mark,
				'type' => $type,
				'content' => $content,
				'ip' => $ip,
				'create_time' => time(),
		);
		$this->db->insert($this->_get_table_name($mark), $insert_data);
		$res_id = mysql_insert_id();
		//添加总表评论用于后台管理
		$insert_data['table_id'] = $res_id;
		$this->db->insert('gl_comments_all', $insert_data);
		unset($insert_data['table_id']);
	}
	// ---------------------------------------------------------------------------------- //
	/**
	 * @param unknown $type
	 * @param unknown $mark
	 */
	public function get_hot_data($type, $mark) {
		$cache_key = $this->_cache_key_pre . 'hot_list';
		$hash_key = "$type:$mark";
	
		$data = $this->cache->redis->hGet($cache_key, $hash_key);
		$data && $data = json_decode($data, 1);
		if (!is_array($data)) {
			$data = $this->get_hot_data_from_db($type, $mark);
			$this->cache->redis->hSet($cache_key, $hash_key, json_encode($data));
			$this->cache->redis->expire($cache_key, 86400);
		}
	
		return $data;
	}
	public function get_hot_data_from_db($type, $mark) {
		$limit = 5;
		$conditions = array(
				'table' => $this->_get_table_name($mark),
				'where' => array(
						'mark' => $mark,
						'type' => $type,
						'status'=> array('in','0,1'),
						'weight' => array(
								'>', 0
						)
				),
				'order' => 'weight desc',
				'limit' => $limit,
		);
		$sql = $this->find($conditions);
		$rs = $this->db->query_read($sql);
		return $rs ? $rs->result_array() : array();
	}
	
	
	// ========================================= 更新 GO ===========================================================//
	// -------------------------------------------------------------------------------------------------------//
	private function _update($update_data, $where, $limit = 1) {
		if (empty($update_data) || empty($where) ) {
			return false;
		}
		$return = $this->_update_to_db($update_data, $where);
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
	public function add_mark_up_count($id, $add = 1) {
		$update_data = array(
				'mark_up' => array('mark_up + 1', FALSE),
		);
		$where = array(
				'id' => $id,
		);
	
		return $this->_update($update_data, $where, 1);
	}
	// ========================================= 更新 END ===========================================================//
}