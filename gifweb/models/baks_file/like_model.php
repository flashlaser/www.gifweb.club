<?php
if (! defined ( 'BASEPATH' )) exit ( 'No direct script access allowed' );

/**
 * @Name	Article_Model.php
 *
 */
class Like_model extends MY_Model {

	private $_cache_key_pre = '';
	private $_cache_expire = 600 ;
	protected  $_table = 'gl_like';

	public function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":like:";
	}

	public function get_info($userid, $mark, $type)
	{
		$cache_key = $this->_cache_key_pre . 'get_info:' . $userid;
		$hash_key = "normal:$mark:$type";
		$data = $this->cache->redis->hGet($cache_key, $hash_key);
		$data && $data = json_decode($data, 1);
		if (!is_array($data)) {
			$conditons['where']['user_id']= array('eq',intval($userid));
			$conditons['where']['mark']= array('eq',$mark);
			$conditons['where']['type']= array('eq',intval($type));
			$sql = $this->find($conditons);
			$data = $this->db->query_read($sql);
			$data = $data->row_array();
			$this->cache->redis->hSet($cache_key, $hash_key, json_encode($data));
			$this->cache->redis->expire($cache_key, $this->_cache_expire);
		}
		return $data;
	}

	/**
	 * 是否赞成
	 * @param unknown $mark
	 * @type	1攻略赞 	2攻略踩 	3答案赞 	4答案反对／答案反对
	 * @return	1赞成 	0没赞成或取消赞成
	 */
	public function is_like( $mark, $type)
	{
		$uid = $this->user_id;
		if(intval($uid) < 1){
			return 0;
		}

		$cache_key = $this->_cache_key_pre . 'is_like:' . $uid;
		$hash_key = "normal:$mark:$type";
		$data = $this->cache->redis->hGet($cache_key, $hash_key);
		$data && $data = json_decode($data, 1);
		if (!is_array($data)) {
			$conditons['where']['user_id']= array('eq',intval($uid));
			$conditons['where']['mark']= array('eq',$mark);
			$conditons['where']['type']= array('eq',intval($type));
			$sql = $this->find($conditons);
			$data = $this->db->query_read($sql);
			$data = $data->row_array();
			$this->cache->redis->hSet($cache_key, $hash_key, json_encode($data));
			$this->cache->redis->expire($cache_key, $this->_cache_expire);
		}
		return $data && $data['status'] > 0 ? 1 : 0;
	}

	public function insertLikeData($data)
	{
		$sql = $this->insert($data);
		$rs  = $this->db->query_write($sql);
		return $this->_aftermath($this->user_id);
	}

	public function updateLikeData($id,$status)
	{
		$sql = "UPDATE {$this->_table} SET status='{$status}',update_time ='".time()."' WHERE id='{$id}' ";
		$rs = $this->db->query_write($sql);

		return $this->_aftermath($this->user_id);
	}

	private function _aftermath($uid) {
		// delete cache
		$cache_key = $this->_cache_key_pre . "get_info:$uid";
		$this->cache->redis->delete($cache_key);

		$cache_key = $this->_cache_key_pre . "is_like:$uid";
		$this->cache->redis->delete($cache_key);

		return 1;
	}
}