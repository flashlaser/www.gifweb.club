<?php
if (! defined ( 'BASEPATH' )) exit ( 'No direct script access allowed' );

/**
 * @Name	Gl_Model.php
 */
class Complaint_model extends MY_Model {
	
	private $_cache_key_pre = '';
	private $_cache_expire = 600 ;
	protected  $_table = 'gl_complaint';

	public function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":complaint:";
	}

	public function insertComplaintData($data)
	{
		$sql = $this->insert($data);
		$rs  = $this->db->query_write($sql);
		return $rs;
	}
}