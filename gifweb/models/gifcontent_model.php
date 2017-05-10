<?php

/**
 * Gifwebcontent_model.php
 * 
 * Copyright (c) 2012 SINA Inc. All rights reserved.
 * 
 * @author	songqinglu <qinglu@staff.sina.com.cn>
 * @date	1:14:18 2017-04-25
 * @version	$Id: User.php 13 2012-12-19 07:10:10Z songqinglu $
 * @desc	This guy is so lazy that he doesn't leave anything.
 */

class Gifcontent_model extends MY_Model
{

	protected $_table = 'gifweb_content';
	protected $_pk = 'cid';

	public function __construct()
	{
		parent::__construct ();
		$this->ch_info = $this->config->item('login_ch');
		$this->fld_cacheKey = 'gifweb:' . ENVIRONMENT . ':users:';
		$this->load->driver ( 'cache' );
		$this->load->model('Common_model','Comm');
		$this->load->library("global_func");
		$this->load->library('HttpRequestCommon',null,'http');
	}
	

	/**
	 * 返回表名儿
	 * @return String
	 */
	public function getTable()
	{
		return $this->_table;
	}
	
	/**
	 * 
	 * 根据条件查询数据
	 * @param array
	 * @return Array
	 */
	public function findData($conditons)
	{
		$sql = $this->find($conditons);
		$rs = $this->db->query_read($sql);
		$result = $rs->result_array();
		return $result;		
	}

	/**
	 *  更新数据信息
	 *  @param $id 
	 *  @param $data
	 *  @return true/false
	 */
	
	public function updateData($uid,$data)
	{
		$sql = $this->update($uid,$data,$this->_table);
		$rs = $this->db->query_write($sql);
		return $rs;
	}
	
	/**
	 * 
	 * 查询信息数据
	 * @param unknown_type $id
	 * @param unknown_type $col
	 */
	public function loadData($id, $col = null)
	{
		$sql = $this->load($id,$col);
		$rs = $this->db->query_read($sql);
		$result = $rs->row_array();
		return  $result;
	}
	
	public function deleteData($id, $col = null)
	{
		$sql = $this->delete($id,$col);
		$rs = $this->db->query_write($sql);
		return $rs;
	}
	
	public function insertData($data, $table = null)
	{
		$sql = $this->insert($data, $table = null);
		$rs  = $this->db->query_write($sql);
		if ($this->db->conn_write) {
			return mysql_insert_id ( $this->db->conn_write );
		} else {
			return mysql_insert_id ( $this->db->conn_id );
		}
		return $rs;
		
	}
	
	/**
	 * Count result
	 *
	 * @param string $where
	 * @param string $table
	 * @return int
	 */
	public function count($where, $table = null)
	{
		if (null == $table) $table = $this->_table;
	
		try {
			$sql = "select count(1) as cnt from $table where $where";
			$rs = $this->db->query_read($sql);
			$result = $rs->result_array();
			$result = $result[0];
			return empty($result['cnt']) ? 0 : $result['cnt'];
		} catch (Exception $e) {
			$this->error(array('code' => '4000', 'msg' => $e->getMessage()));
			return false;
		}
	}


			/**
	 * find data by limit
	 * 
	 * @param string $page
	 * @param string $pagesize
	 * @param array $condition
	 * @return array
	 */
	public function pages($page, $pagesize = 10, array $condition = array(), $urlrule = '', $array = array())
	{
		$where = isset($condition['where']) ? $this->where($condition['where']) : 1;
		$table = isset($condition['table']) ? $condition['table'] : $this->_table;
		$this->number = $this->count($where, $table);
		($pagesize = intval($pagesize)) or $pagesize = PAGE_LIST_SIZE;
		$page = max(intval($page), 1);
		$page = min(ceil($this->number/$pagesize), $page);
		$offset = $pagesize * ($page-1);
		$this->pages = Page::pages($this->number, $page, $pagesize, $urlrule, $array);
		if ($this->number > 0) {
			$condition['start'] = $offset;
			$condition['limit'] = $pagesize;
			return $this->findData($condition);
		} else {
			return array();
		}
	}
	
	
}

?>