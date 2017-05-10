<?php
/**
 * 
 * @name Bg_img_model 攻略app 2.0 背景图片操作model
 * @desc null
 *
 * @author	 wangbo8
 * @date 2016年1月26日
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 */
class Bg_img_model extends MY_Model {
	private $_cache_key_pre = '';
	private $_cache_expire = 600;
	protected $_table = 'gl_bg_img';
	
	function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":gl_bg_img:";
	}

	//根据id获取当前背景图片
	public function get_bgimg($id) {
		$cache_key = $this->_cache_key_pre . "bgimg:$id";
		$data = $this->cache->redis->get($cache_key);
		$data && $data = json_decode($data, true);

		if ($data === false) {
			$data = $this->_get_bgimg_from_db($id);
			$this->cache->redis->set($cache_key, json_encode($data), $this->_cache_expire);
		}
		return $data;
	}

	//入库通过id获取背景图信息
	private function _get_bgimg_from_db($id) {
		$conditions = array(
				'fields' => 'id,img_title,img_url',
				'table' => $this->_table,
				'where' => array(
						'id' => $id
				),
				'limit' => 1,
		);

		$sql = $this->find($conditions);
		$rs = $this->common_model->get_one_data_by_sql($sql);
		return $rs;
	}

	//获取可替换背景图列表
	public function get_bgimg_list(){
		//拼装缓存key
		$cache_key = $this->_cache_key_pre . 'bgimglist';

		//从缓存中获取数据
		$data = $this->cache->redis->get($cache_key);
		$data && $data = json_decode($data, true);

		//判断
		if($data === false){
			//入库获取背景图列表
			$data = $this->_get_bgimglist_from_db();

			//数据入缓存
			$this->cache->redis->set($cache_key, json_encode($data), $this->_cache_expire);
		}

		//返回数据
		return $data;
	}

	//入库获取可替换背景图列表
	private function _get_bgimglist_from_db(){
		//拼装搜索条件
		$conditions = array(
				'table' => $this->_table,
				'where' => array(
						'uid' => '-1'
					)
				//'limit' => 1000,
			);

		//生成sql
		$sql = $this->find($conditions);
		$rs = $this->common_model->get_data_by_sql($sql);

		//返回结果
		return $rs;
	}

	//查询当前图片id是否可用
	public function checkBgimg($bgid){
		//获取当前用户可用图片列表
		$res_list = $this->get_bgimg_list();

		//初始化结果数组
		$return_list = array();

		//循环遍历
		if(count($res_list) > 0 && is_array($res_list)){
			foreach($res_list as $v){
				$return_list[] = $v['id'];
			}
		}

		return in_array($bgid, $return_list);
	}
	/*
	//保存用户背景图信息
	public function save_bgimg($uid, $bgimg) {
		if (empty($uid)) {
			return false;
		}

		$return = $this->_save_bgimg_to_db($uid, $bgimg);
		$cache_key = $this->_cache_key_pre . "bgimg:$uid";
		$this->cache->redis->delete($cache_key);
		return $return;
	}

	//执行保存
	private function _save_bgimg_to_db($uid, $bgimg) {
		if (empty($uid) || empty($bgimg) ) {
			return false;
		}
		$time = time();
		$status = 0;

		//$content = htmlspecialchars($content, null, null, false);

		$sql = "SELECT uid FROM {$this->_table} WHERE uid='$uid' LIMIT 1";
		$query = $this->db->query_read($sql);
		if ($query->row_array()) {
			// update
			$this->db->from($this->_table);
			$this->db->set('img_url', $bgimg);
			$this->db->where('uid', $uid);
			$this->db->limit(1);
			$this->db->update();
		} else {
			// insert
			$insert_data = array(
					'uid' => $uid,
					'img_url' => $bgimg
			);
			$this->db->insert($this->_table, $insert_data);
		}

		return 1;
	}
	*/

}