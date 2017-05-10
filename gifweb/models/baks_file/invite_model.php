<?php
if (! defined ( 'BASEPATH' )) exit ( 'No direct script access allowed' );

/**
 * @Name	Invite_model.php
 */
class Invite_model extends MY_Model {
	
	private $_cache_key_pre = '';
	private $_cache_expire = 600 ;
	protected  $_table = 'gl_invite';

	public function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":invite:";
	}
	
    //插入数据
	public function insertInviteData($data)
	{
		$sql = $this->insert($data);
		$rs  = $this->db->query_write($sql);
		return $rs;
	}
	
	//是否邀请 【【截止当天23:59:59】同一个人对一个用户且同一个问题只能邀请1次】
	public function is_invite($uid,$guid,$mark,$type = 1) {
	    if (!$uid || !$guid || !$mark) return false;
	    
		$today = date('Y-m-d',time());
		$todayStart = strtotime($today.' 00:00:00');
		$todayEnd = strtotime($today.' 23:59:59');
		
		$conditons['fields']= " count(*) as inviteCount ";
		$conditons['where']['uid']= array('eq',intval($guid));
		$conditons['where']['invite_uid']= array('eq',intval($uid));
		$conditons['where']['type']= array('eq',intval($type));
		$conditons['where']['mark']= array('eq',$mark);
		$conditons['where']['create_time'] = array(
		    	array('egt', $todayStart),
		    	array('elt', $todayEnd),
		    	'and'
		    );
		$sql = $this->find($conditons);
		$data = $this->db->query_read($sql);
		$data = $data->row_array();
		return $data && $data['inviteCount'] >0 ? 1 : 0;
	}
}