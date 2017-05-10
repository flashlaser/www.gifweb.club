<?php
/**
 * 
 * @name Question_content_model
 * @desc null
 *
 * @author	 liule1
 * @date 2015年8月6日
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 */
class Question_content_model extends MY_Model {
	private $_cache_key_pre = '';
	private $_cache_expire = 600;
	protected $_table = 'gl_question_content';
	
	function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":question_content:";
	}
	// ---------------------------------------------------------------------------------- //
	public function get_content($qid) {
		$cache_key = $this->_cache_key_pre . "content:$qid";
		$data = $this->cache->redis->get($cache_key);
		if ($data === false) {
			$data = $this->_get_content_from_db($qid);
			$this->cache->redis->set($cache_key, $data, $this->_cache_expire);
		}
		
		return $data;
	}
	public function _get_content_from_db($qid) {
		$conditions = array(
				'fields' => 'content',
				'table' => $this->_table,
				'where' => array(
						'qid' => $qid
				),
				'limit' => 1,
		);
		
		$sql = $this->find($conditions);
		$rs = $this->db->query_read($sql);
		$rs = $rs ? $rs->row_array() : array();
		return empty($rs['content']) ? '' : $rs['content'];
	}
	// -----------------------------------------------------------------------------------//
	public function save_content($qid, $content) {
		if (empty($qid)) {
			return false;
		}
	
		$return = $this->_save_content_to_db($qid, $content);
		
		
		$cache_key = $this->_cache_key_pre . "content:$qid";
		$this->cache->redis->delete($cache_key);
		
		return $return;
	}
	
	private function _save_content_to_db($qid, $content) {
		if (empty($qid) || empty($content) ) {
			return false;
		}
		$time = time();
		$status = 0;
	
		$sql = "SELECT qid FROM {$this->_table} WHERE qid='$qid' LIMIT 1";
		$query = $this->db->query_read($sql);
		if ($query->row_array()) {
			// update
			$update_data = array(
					'content' => $content
			);
			$where = array(
					'qid' => $qid,
			);
			$this->db->update($this->_table, $update_data, $where, 1);
		} else {
			// insert
			$insert_data = array(
					'qid' => $qid,
					'content' => $content
			);
				
			$this->db->insert($this->_table, $insert_data);
		}
	
		return 1;
	}
	
	// ---------------------------------------- content --------------------------------------//
}