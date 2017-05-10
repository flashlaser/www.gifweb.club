<?php
if (! defined ( 'BASEPATH' )) exit ( 'No direct script access allowed' );

class Download_model extends MY_Model {

	private $_cache_key_pre = '';
	private $_cache_expire = 600 ;
	protected  $_table = 'gl_download_platform';

	public function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":download:";
	}

	/*
	 * 查询列表
	 * author  huanglong
	 * date    2016-05-24
	 */
	public function get_list($start,$count) {
		$cache_key = $this->_cache_key_pre . 'get_list:';
		$hash_key = "$start:$count";

		$data = $this->cache->redis->hGet($cache_key, $hash_key);
		$data && $data = json_decode($data, 1);
		if (!is_array($data)) {
    	    $conditions = array(
    	        'table' => $this->_table,
    	        'start' => (int)$start,
    	        'limit' => (int)$count
    	    );
    	    $conditions['order'] = ' id asc ';
    	    $conditions['where']['status'] = 0;
    	    $sql = $this->find($conditions);
    	    $rs = $this->db->query_read($sql);
    	    $data = $rs ? $rs -> result_array() : array();

    	    $this->cache->redis->hSet($cache_key, $hash_key,json_encode($data));
    	    $this->cache->redis->expire($cache_key, $this->_cache_expire);
    	}
	    return $data;
	}

	/*
	 * 查询单条数据
	 * author  huanglong
	 * date    2016-05-24
	 */
	public function get_row($app) {
		$cache_key = $this->_cache_key_pre . 'get_row:' . "$app";
		$data = $this->cache->redis->get($cache_key);
		$data && $data = json_decode($data, 1);
		if (!is_array($data)) {
    	    $conditions = array(
    	        'table' => $this->_table,
    	        'limit' => 1
    	    );
    	    $conditions['where']['status'] = 0;
    	    $conditions['where']['mark'] = $app;
    	    $sql = $this->find($conditions);
    	    $rs = $this->db->query_read($sql);
    	    $data = $rs ? $rs -> row_array() : array();
			$this->cache->redis->set($cache_key, json_encode($data), $this->_cache_expire*3);
		}
	    return $data;
	}
	/*
	 * 查询
	 * author  huanglong
	 * date    2016-05-24
	 */
	public function get_all() {
		$cache_key = $this->_cache_key_pre . 'get_all';
		$data = $this->cache->redis->get($cache_key);
		$data && $data = json_decode($data, 1);
		if (!is_array($data)) {
    	    $conditions = array(
    	        'table' => $this->_table
    	    );
    	    $conditions['where']['status'] = 0;
    	    $sql = $this->find($conditions);
    	    $rs = $this->db->query_read($sql);
    	    $data = $rs ? $rs -> result_array() : array();
			$this->cache->redis->set($cache_key, json_encode($data), $this->_cache_expire);
		}
	    return $data;
	}
}