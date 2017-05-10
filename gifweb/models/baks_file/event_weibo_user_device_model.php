<?php
/**
 * 给商务活动做的支持，没什么其他用。
 * @name Event_weibo_user_device_model
 * @desc null
 *
 * @author	 liule1
 * @date 2015年10月29日
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 */
class Event_weibo_user_device_model extends MY_Model {
	private $_cache_key_pre = '';
	private $_cache_expire = 600;
	protected $_table = 'event_weibo_user_device';

	function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":event_w_u_d_m:";
	}
	// ---------------------------------------------------------------------------------- //

	public function check_weibo_uid($weibo_uid) {
		$weibo_uid = $this->global_func->filter_int($weibo_uid);
		if (empty($weibo_uid)) {
			return false;
		}
		$cache_key = $this->_cache_key_pre . "check_uid:" . $weibo_uid;
		$data = $this->cache->redis->get($cache_key);
		if ($data === false) {
			$sql = "select * from {$this->_table} WHERE weibo_uid='$weibo_uid' LIMIT 1";
			$res = $this->db->query_read($sql);
			$res = $res ? $res->row_array() : array();
			$data = $res ? '1' : '0';
			$this->cache->redis->save($cache_key, $data, $this->_cache_expire);
		}
		return $data;
	}
	public function check_device_id($device_id) {
		if (empty($device_id)) {
			return false;
		}
		$cache_key = $this->_cache_key_pre . "check_device_id:" . $device_id;
		$data = $this->cache->redis->get($cache_key);
		if ($data === false) {
			$sql = "select * from {$this->_table} WHERE device_id='$device_id' LIMIT 1";
			$res = $this->db->query_read($sql);
			$res = $res ? $res->row_array() : array();
			$data = $res ? '1' : '0';
			$this->cache->redis->save($cache_key, $data, $this->_cache_expire);
		}
		return $data;
	}

	public function add($weibo_uid, $device_id) {
		if ($this->check_device_id($device_id) !== '0' || $this->check_weibo_uid($weibo_uid) !== '0') {
			// 已存在
			return false;
		}
		$insert_data = array(
				'weibo_uid' => $weibo_uid,
				'device_id' => $device_id
		);
		$return = $this->db->insert($this->_table, $insert_data);


		$cache_key = $this->_cache_key_pre . "check_uid:" . $weibo_uid;
		$this->cache->redis->delete($cache_key);

		$cache_key = $this->_cache_key_pre . "check_device_id:" . $device_id;
		$this->cache->redis->delete($cache_key);

	}

}
