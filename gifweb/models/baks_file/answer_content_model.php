<?php
/**
 * 
 * @name Answer_content_model
 * @desc null
 *
 * @author	 liule1
 * @date 2015年8月16日
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 */
class Answer_content_model extends MY_Model {
	private $_cache_key_pre = '';
	private $_cache_expire = 600;
	protected $_table = 'gl_answer_content';
	
	function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":answer_content:";
	}
	// ---------------------------------------------------------------------------------- //
	public function get_content($aid) {
		$cache_key = $this->_cache_key_pre . "content:$aid";
		$data = $this->cache->redis->get($cache_key);
		if ($data === false) {
			$data = $this->_get_content_from_db($aid);
			$this->cache->redis->set($cache_key, $data, $this->_cache_expire);
		}
		
		return $data;
	}
	public function _get_content_from_db($aid) {
		$conditions = array(
				'fields' => 'content',
				'table' => $this->_table,
				'where' => array(
						'aid' => $aid
				),
				'limit' => 1,
		);
		
		$sql = $this->find($conditions);
		$rs = $this->db->query_read($sql);
		$rs = $rs ? $rs->row_array() : array();
		return empty($rs['content']) ? '' : $rs['content'];
	}
	// -----------------------------------------------------------------------------------//
	public function save_content($aid, $content) {
		if (empty($aid)) {
			return false;
		}
	
		$return = $this->_save_content_to_db($aid, $content);
		
		
		$cache_key = $this->_cache_key_pre . "content:$aid";
		$this->cache->redis->delete($cache_key);
		
		return $return;
	}
	
	private function _save_content_to_db($aid, $content) {
		if (empty($aid) || empty($content) ) {
			return false;
		}
		$time = time();
		$status = 0;
	
		$content = htmlspecialchars($content, null, null, false);
		
		$sql = "SELECT aid FROM {$this->_table} WHERE aid='$aid' LIMIT 1";
		$query = $this->db->query_read($sql);
		if ($query->row_array()) {
			// update
			$this->db->from($this->_table);
			$this->db->set('content', $content);
			$this->db->where('aid', $aid);
			$this->db->limit(1);
			$this->db->update();
		} else {
			// insert
			$insert_data = array(
					'aid' => $aid,
					'content' => $content
			);
				
			$this->db->insert($this->_table, $insert_data);
		}
	
		return 1;
	}
	
	// ---------------------------------------- content --------------------------------------//
}