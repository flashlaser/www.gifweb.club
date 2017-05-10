<?php
if (! defined ( 'BASEPATH' )) exit ( 'No direct script access allowed' );

/**
 * @Name	Recommend_Model.php
 */
class Recommend_model extends MY_Model {

	private $_cache_key_pre = '';
	private $_cache_expire = 600 ;
	protected  $_table = 'gl_recommend';

	public function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":recommend:";
	}

	public function get_recommend_list($recommend_type,$gameId = 0,$offsize=0,$page_size=0 ,$last_id =0){

		$cache_key = $this->_cache_key_pre . "get_recommend_list:$recommend_type:$this->platform:$gameId:$offsize:$page_size:$this->review_state";
		$result = $this->cache->redis->get($cache_key);
		$result && $result = json_decode($result, 1);
		if (!is_array($result)) {
			$sql_info['where']['is_show']= array('eq',1);
			$sql_info['where']['recommend_type']= array('eq',intval($recommend_type));
			if($recommend_type !=10){
    			if($this->platform == 'android') {//安卓
    				$sql_info['where']['platform'] = array('IN', '2,3');
    			}else{//ios
    				$sql_info['where']['platform'] = array('IN', '1,3');
    			}
			}else{
    			$sql_info['where']['platform'] =array('eq', '4');
			}
			if($gameId > 0) {
				$sql_info['where']['gid'] = array('eq', intval($gameId));
			}
			if ($this->review_state) {
				$sql_info['where']['review_state'] = array('eq', intval($this->review_state));
			}
			$last_id > 0 && $sql_info['where'] += array(
				'id' => array(
					'<', $last_id
				)
			);
			$sql_info['order'] = ' weight desc ';
			if($recommend_type == 1){
				$sql_info['start'] = 0;
				$sql_info['limit'] = 50;
			}
			if($recommend_type == '5'){
				$sql_info['start'] = 0;
				$sql_info['limit'] = 5;
			}
			if($recommend_type == 6){
				$sql_info['start'] = intval($offsize);
				$sql_info['limit'] = intval($page_size);
			}
			if($recommend_type == 4 && $page_size>0 && $offsize >=0){
				$sql_info['start'] = intval($offsize);
				$sql_info['limit'] = intval($page_size);
			}
			if($recommend_type == '10'){
				$sql_info['start'] = 0;
				$sql_info['limit'] = 1;
			}

			$sql = $this->find($sql_info);
			$rs = $this->db->query_read($sql);
			$result = $rs->result_array();
			$this->cache->redis->set($cache_key, json_encode($result), $this->_cache_expire );
		}
		return $result;
	}

}
