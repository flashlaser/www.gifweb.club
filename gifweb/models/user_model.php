<?php

/**
 * User.php
 * 
 * Copyright (c) 2012 SINA Inc. All rights reserved.
 * 
 * @author	songqinglu <qinglu@staff.sina.com.cn>
 * @date	1:14:18 2017-04-25
 * @version	$Id: User.php 13 2012-12-19 07:10:10Z songqinglu $
 * @desc	This guy is so lazy that he doesn't leave anything.
 */

class User_model extends MY_Model
{

	protected $_table = 'gifweb_user';
	protected $_pk = 'uid';

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
	
	public function updateData($id,$data)
	{
		$sql = $this->update($id,$data,$this->_table);
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
		$result = $rs->result_array();
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
		return $rs;
		
	}
}

?>