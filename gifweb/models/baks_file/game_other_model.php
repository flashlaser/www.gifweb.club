<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

/**
 * @Name	Game_other_model.php
 */
class Game_other_model extends MY_Model {

	protected  $_table = 'gl_games_other';
	private $_cache_key_pre = '';
	private $_cache_expire = 10 ;

	public function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":Game_other:";
		$this->load->driver ( 'cache' );
	}
	public function get_info($name) {
		if (!$name) return false;

		$cache_key = $this->_cache_key_pre . 'info:' . "$name";
		$data = $this->cache->redis->get($cache_key);
		$data && $data = json_decode($data, 1);
		if (!is_array($data)) {
			$data = $this->get_info_from_db($name);
			$this->cache->redis->set($cache_key, json_encode($data), $this->_cache_expire);
		}

		return $data;
	}

	public function get_info_from_db($name) {
		$conditions = array(
			'table' => $this->_table,
			'where' => array(
				'game_name' => $name
			),
			'limit' => 1,
		);
		$sql = $this->find($conditions);
		$rs = $this->db->query_read($sql);
		return $rs ? $rs->row_array() : array();
	}

	public function insertData($data)
	{
		$sql = $this->insert($data);
		$rs  = $this->db->query_write($sql);
		return $rs;
	}


	public function updateData($id,$add)
	{
		$sql = "update gl_games_other set update_time = '".time()."', `add_num`= (add_num + ".intval($add).") where id='".intval($id)."'";
		$rs = $this->db->query_write($sql);
		$rss = $this->db->affected_rows_write();
		return $rss ? true : false;
	}

}