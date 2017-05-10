<?php
/**
 * 
 * @name Push_model
 * @desc null
 *
 * @author	 liule1
 * @date 2015年8月28日
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 */
class Push_model extends MY_Model {
	private $_cache_key_pre = '';
	private $_cache_expire = 86400;
	protected $_table = 'gl_push';
	
	function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":push:";
		$this->load->model('user_model');
	}
	// ---------------------------------------------------------------------------------- //
	// ---------------------------------------------------------------------------------- //
	public function get_info($token) {
		if (!$token) return false;
		$cache_key = $this->_cache_key_pre . "info:$token";
		$data = $this->cache->redis->get($cache_key) ;
		$data && $data = json_decode($data, true);
		if (empty($data)) {	// 特殊情况，只需要判断empty
			$data = $this->get_info_from_db($token);
			$this->cache->redis->set($cache_key, json_encode($data), $this->_cache_expire);
		}
		return $data;
	}
	
	public function get_info_from_db($token) {
		$conditions = array(
				'table' => $this->_table,
				'where' => array(
						'token' => $token,
				),
				'limit' => 1,
		);
		$sql = $this->find($conditions);
		$rs = $this->db->query_read($sql);
		return $rs ? $rs->row_array() : array();
	}
	
	public function get_info_by_uid($uid) {
		if (!$uid) return false;
		$cache_key = $this->_cache_key_pre . "info_by_uid:$uid";
		$data = $this->cache->redis->get($cache_key) ;
		$data && $data = json_decode($data, true);
		if (empty($data)) {	// 特殊情况，只需要判断empty
			$data = $this->get_info_by_uid_from_db($uid);
			$this->cache->redis->set($cache_key, json_encode($data), $this->_cache_expire);
		}
		return $data;
	}
	
	public function get_info_by_uid_from_db($uid) {
		$conditions = array(
				'table' => $this->_table,
				'where' => array(
						'uid' => $uid,
				),
				'limit' => 1,
		);
		$sql = $this->find($conditions);
		$rs = $this->db->query_read($sql);
		return $rs ? $rs->row_array() : array();
	}
	// ---------------------------------------------------------------------------------- //
	
	// ========================================= 更新 GO ===========================================================//
	// -------------------------------------------------------------------------------------------------------//
	public function save_token($uid, $token, $switch, $version, $platform, $partner_id) {
		$version = strtolower($version);
		is_numeric($uid) || $uid = (int) $uid;
		
		$time = time();
		if ($uid) {
			// 清除原有 uid 的 token 
			$update_data = array(
					'uid' => 0,
					'update_time' => $time 
			);
			$where = array(
					'uid' => $uid,
			);
			$this->db->update($this->_table, $update_data, $where, 8);
		}
		$push_info = $this->get_info($token);
		if ($push_info) {
			// update
			$update_data = array(
					'uid' => $uid,
					'token' => $token,
					'version' => $version,
					'platform' => $platform,
					'partner_id' => $partner_id,
					'update_time' => $time
			);
			empty($switch) || $update_data['switch'] = $switch;
			
			
			$where = array(
					'token' => $token
			);
			$this->db->update($this->_table, $update_data, $where, 1);
		} else {
			$switch === null && $switch = '11001';
			// insert
			$insert_data = array(
					'uid' => $uid,
					'token' => $token,
					'switch' => $switch ,
					'version' => $version,
					'platform' => $platform,
					'partner_id' => $partner_id,
					'update_time' => $time,
					'create_time' => $time
			);
			
			$this->db->insert($this->_table, $insert_data);
		}
		
		
		// 删除CACHE
		$cache_key = $this->_cache_key_pre . "info:$token";
		$this->cache->redis->delete($cache_key);
		
		if ($uid) {
			$cache_key = $this->_cache_key_pre . "info_by_uid:$uid";
			$this->cache->redis->delete($cache_key);
		}
		if ($push_info) {
			$cache_key = $this->_cache_key_pre . "info_by_uid:{$push_info['uid']}";
			$this->cache->redis->delete($cache_key);
		}
		
		$return = 1;
		return $return;
	}
	
	// ========================================= 更新 END ===========================================================//
}