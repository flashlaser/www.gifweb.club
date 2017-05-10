<?php
if (! defined ( 'BASEPATH' )) exit ( 'No direct script access allowed' );

/**
 * @Name	Spread_model.php 广告推广用
 *
 */
class Spread_model extends MY_Model {

	protected  $_table = 'gl_ad_spread';

	public function __construct() {
		parent::__construct ();
	}
	//查询攻略文档信息
	public function findData($idfa,$appid='',$type=array(0,1,2))
	{
		$conditions = array(
			'table' => $this->_table,
			'where' => array(
				'idfa' => $idfa,
				'type' => array(
					'in',$type
				),
			),
			'order' => 'create_time asc',
			'limit' => '1'
		);
		if($appid) {
			$conditions['where'] += array(
				'appid' => $appid
			);
		}
		$sql = $this->find($conditions);
		$rs = $this->db->query_read($sql);
		$data = $rs->row_array();
		return $data;
	}

	//查询攻略文档信息
	public function checkStatus($idfa,$appid = '',$type=array(0,1,2),$limit=1000)
	{
		$conditions = array(
			'table' => $this->_table,
			'where' => array(
				'idfa' => array(
					'in',$idfa
				),
				'type' => array(
					'in',$type
				),
			),
			'fields' => 'idfa,status',
			'order' => 'create_time asc',
			'limit' => $limit
		);
		if($appid) {
			$conditions['where'] += array(
				'appid' => $appid
			);
		}
		$sql = $this->find($conditions);
		$rs = $this->db->query_read($sql);
		$data = $rs->result_array();
		return $data;
	}

	public function insertData($data)
	{
		$sql = $this->insert($data);
		$rs  = $this->db->query_write($sql);
		return $rs;
	}

	public function updateStatus($id,$status)
	{
		$sql = "update gl_ad_spread set `status`= '".$status."',active_time='".time()."' where id='".$id."'";
		$rs = $this->db->query_write($sql);
		return $rs;
	}

	public function updateCallbackStatus($id,$status)
	{
		$sql = "update gl_ad_spread set `callback_status`= '".$status."' where id='".$id."'";
		$rs = $this->db->query_write($sql);
		return $rs;
	}

	public function updateTmp1Status($id)
	{
		$sql = "update gl_ad_spread set `tmp1`= '1' where status = 0 and active_time = 0 and idfa ='".$id."'";
		$rs = $this->db->query_write($sql);
		return $rs;
	}
}