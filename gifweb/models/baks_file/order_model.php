<?php
/**
 *
 * @name Order_model
 * @desc null
 *
 * @author	 liule1
 * @date 2016.01.13
 *
 * @copyright (c) 2016 SINA Inc. All rights reserved.
 */
class Order_model extends MY_Model {
	
	private $pay_conf = array();
	var $_cache_key_pre = '';
	var $_cache_expire = 600;
	function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":order_model:";
		$this->pay_conf = $this->config->item('pay_conf');
		$this->load->driver('cache');
	}
	
    /**
     * order流水，以from_uid为hash参数
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    private function _get_order_table($uid) {
        if (is_string($uid)) {
            $postfix = $uid[strlen($uid) - 1];
        } else {
            $postfix = $uid % 10;
        }
		
        return 'gl_order_' . abs($postfix) ;
    }
	
	/**
     * order流水，以from_uid为hash参数
     * @param  order_sn:     20160126163032100172103
     * @return int
     */
    private function _get_order_table_by_order_sn($order_sn) {
		$order_sn = (string) $order_sn;
		if (mb_strlen($order_sn) < 18) {
			throw new Exception("error order_sn!", _PARAMS_ERROR_);
		}
        // $postfix = $order_sn[16] * 10 + $order_sn[17];
		$postfix = $order_sn[17];
        return 'gl_order_' . abs($postfix) ;
    }
    
    /**
     * 成功的订单
     * @return [type] [description]
     */
    private function _get_order_finish_table() {
        return 'gl_order_finish';
    }
    /**
     * 订单log
     * @return [type] [description]
     */
    private function _get_order_memo_table() {
        return 'gl_order_memo';
    }
	
	// ===============================================================
	public function order_memo($order_sn, $memo) {
		$inser_data = array(
			'order_sn' => $order_sn,
			'memo' => $memo,
			'create_time' => SYS_TIME
		);
		
		return $this->db->insert('gl_order_memo', $inser_data);
	}
    
	// ===============================================================
    public function get_info($order_sn) {
		$cache_key = $this->_get_info_cache_key($order_sn);
		$data = $this->cache->redis->get($cache_key);
		$data && $data = json_decode($data, true);
		if ($data === false) {
			$data = $this->_get_info_from_db($order_sn);
			$this->cache->redis->set($cache_key, json_encode($data), $this->_cache_expire);
		}
		
		return $data;
	}
	
	private function _get_info_cache_key($order_sn) {
		$cache_key = $this->_cache_key_pre . "order_info:$order_sn";
		return $cache_key;
	}
	private function _get_info_from_db($order_sn) {
		$conditions = array(
			'table' => $this->_get_order_table_by_order_sn($order_sn),
			'where' => array(
				'order_sn' => $order_sn
			),
			'start' => 0,
            'limit' => 1
		);
        $sql = $this->find($conditions);
        $rs = $this->db->query_read($sql);
        $row = $rs ? $rs -> row_array() : array();
		return $row;
	}
	
	public function del_info_to_cache($order_sn) {
		$cache_key = $this->_get_info_cache_key($order_sn);
		return $this->cache->redis->delete($cache_key);
	}

	
	
    
    //=======================打赏 start by hl====================================//
     	
	// 获取打赏次数[$type：类型；  1为打赏 2为被打赏]
	public function getTotalCashCount ($uid ,$type = 1,$related_type='',$related_id='') {
	    $uid = $this->global_func->filter_int($uid);
	    $type = $this->global_func->filter_int($type);
	
	    $cache_key = $this->_get_total_cash_cache_key($uid,$type);
	    $data = $this->cache->redis->get($cache_key);
// 	    	    $data=false;
	    if ($data === false) {
	        $data = $this->_get_total_cash_from_db($uid,$type,$related_type,$related_id);
	        $this->cache->redis->set($cache_key, $data, $this->_cache_expire);
	    }
	    return $data;
	}

	// 获取打赏次数[$type：类型；  1为打赏 2为被打赏]
	public function getTotalCashCounts ($related_type='',$related_id='') {
	    $cache_key = $this->_get_total_cash_cache_keys($related_type,$related_id);
	    $data = $this->cache->redis->get($cache_key);
	    // 	    	    $data=false;
	    if ($data === false) {
	        $data = $this->_get_total_cash_from_dbs($related_type,$related_id);
	        $this->cache->redis->set($cache_key, $data, $this->_cache_expire);
	    }
	    return $data;
	}
	
	private function _get_total_cash_cache_keys($related_type,$related_id) {
	    $cache_key = $this->_cache_key_pre . "total_cashs:$related_type:$related_id";
	    return $cache_key;
	}
	
	private function _get_total_cash_from_dbs($related_type='',$related_id='') {
	    if($related_type){
	        $where['related_type'] = $related_type;
	        $where['related_id'] = $related_id;
	    }
	    $conditions = array(
	        'fields' => ' count(1) as cashCnt ',
	        'table' => $this->_get_order_finish_table(),
	        'where' => $where,
	        'start' => 0,
	        'limit' => 1
	    );
	    $sql = $this->find($conditions);
	    $rs = $this->db->query_read($sql);
	    $row = $rs ? $rs -> row_array() : array();
	
	    return (int)$row['cashCnt'];
	}
	
	private function _get_total_cash_cache_key($uid,$type) {
	    $cache_key = $this->_cache_key_pre . "total_cash:$uid:$type";
	    return $cache_key;
	}
	
	private function _get_total_cash_from_db($uid,$type,$related_type='',$related_id='') {
	    if($uid>0){
    	    if($type ==1){
    	        $where['from_uid'] = $uid;
    	    }elseif($type==2){
    	        $where['to_uid'] = $uid;
    	    }
	    }
	    if($related_type){
	        $where['related_type'] = $related_type;
	        $where['related_id'] = $related_id;
	    }
	    $conditions = array(
	        'fields' => ' count(1) as cashCnt ',
	        'table' => $this->_get_order_finish_table(),
	        'where' => $where,
	        'start' => 0,
	        'limit' => 1
	    );
	    $sql = $this->find($conditions);
	    $rs = $this->db->query_read($sql);
	    $row = $rs ? $rs -> row_array() : array();
	
	    return (int)$row['cashCnt'];
	}
	
	public function _clear_list($uid,$order_type,$user_type){
	    $uid = $this->global_func->filter_int($uid);
	    $user_type = $this->global_func->filter_int($user_type);
	    $cache_key = $this->_cache_key_pre . 'get_list:' . $uid.":".$order_type.":".$user_type;
	    $this->cache->redis->delete($cache_key);
	    $cache_key = $this->_cache_key_pre . 'get_order_list:' . $uid.":".$order_type.":".$user_type;
	    $this->cache->redis->delete($cache_key);
	}
	
	public function _clear_count($uid,$user_type){
	    $uid = $this->global_func->filter_int($uid);
	    $user_type = $this->global_func->filter_int($user_type);
    	$cache_key = $this->_cache_key_pre . "total_cash:$uid:$user_type";
    	$this->cache->redis->delete($cache_key);

	}
	public function _clear_counts($related_type,$related_id){
    	$cache_key = $this->_cache_key_pre . "total_cashs:$related_type:$related_id";
    	$this->cache->redis->delete($cache_key);
	}


	public function get_order_list ($uid='' ,$order_type = 10,$user_type = 1,$start,$count,$sort='',$related_type='',$related_id='',$is_group='') {
	    $uid = $this->global_func->filter_int($uid);
	    $user_type = $this->global_func->filter_int($user_type);
	    $order_type = $this->global_func->filter_int($order_type);
	
	    $cache_key = $this->_cache_key_pre . 'get_order_list:' . "$uid:$order_type:$user_type";
	    $hash_key = "normal:$start:$count:$sort:$related_type:$related_id";
	    $data = $this->cache->redis->hGet($cache_key, $hash_key);
	    $data && $data = json_decode($data, 1);
	    if (!is_array($data)) {
	        $data = $this->_get_order_data_from_db($uid ,$order_type,$user_type,$start,$count,$sort,$related_type,$related_id,$is_group);
	        $this->cache->redis->hSet($cache_key, $hash_key, json_encode($data));
	        $this->cache->redis->expire($cache_key, $this->_cache_expire);
	    }
	    return $data;
	}
	public function get_list ($uid='' ,$order_type = 10,$user_type = 1,$start,$count,$sort='',$related_type='',$related_id='',$is_group='') {
	    $uid = $this->global_func->filter_int($uid);
	    $user_type = $this->global_func->filter_int($user_type);
	    $order_type = $this->global_func->filter_int($order_type);

	    $cache_key = $this->_cache_key_pre . 'get_list:' . "$uid:$order_type:$user_type";
	    $hash_key = "normal:$start:$count:$sort:$related_type:$related_id";
	    $data = $this->cache->redis->hGet($cache_key, $hash_key);
	    $data && $data = json_decode($data, 1);
	    if (!is_array($data)) {
	        $data = $this->_get_data_from_db($uid ,$order_type,$user_type,$start,$count,$sort,$related_type,$related_id,$is_group);
	        $this->cache->redis->hSet($cache_key, $hash_key, json_encode($data));
	        $this->cache->redis->expire($cache_key, $this->_cache_expire);
	   }
	    return $data;
	}
	
	private function _get_data_from_db($uid='' ,$order_type,$user_type,$start,$count,$sort='',$related_type='',$related_id='',$is_group='') {
	    if($user_type ==1){
	        if($uid){
	           $where['from_uid'] = $uid;
	        }
	        $group = ' from_uid ';
	    }elseif($user_type==2){
	        $where['to_uid'] = $uid;
	        $group = ' to_uid ';
	    }else{
	        $group = ' from_uid,to_uid ';
	    }
	    
	    $where['type'] = $order_type;
	    
	    if($related_type){
	        $where['related_type'] = $related_type;
	        $where['related_id'] = $related_id;
	    }
	    $conditions = array(
	        'table' => $this->_get_order_finish_table(),
	        'where' => $where,
	        'start' => $start,
	        'limit' => $count
	    );
	    
	    if($sort){
    	    if($is_group){
	           $conditions['order'] = ' max(finish_time) desc ';
    	       $conditions['group'] = $group;
    	    }else{
	           $conditions['order'] = ' finish_time desc ';
    	    }
	    }else{
	           $conditions['order'] = ' finish_time desc ';
	    }
	    $sql = $this->find($conditions);
	    $rs = $this->db->query_read($sql);
	    $row = $rs ? $rs -> result_array() : array();
	
	    return $row;
	}
	private function _get_order_data_from_db($uid='' ,$order_type,$user_type,$start,$count,$sort='',$related_type='',$related_id='',$is_group='') {
	    if($user_type ==1){
	        if($uid){
	           $where['from_uid'] = $uid;
	        }
	        $group = ' from_uid ';
	    }elseif($user_type==2){
	        $where['to_uid'] = $uid;
	        $group = ' to_uid ';
	    }else{
	        $group = ' from_uid,to_uid ';
	    }
	    
	    $where['type'] = $order_type;
	    
	    if($related_type){
	        $where['related_type'] = $related_type;
	        $where['related_id'] = $related_id;
	    }
	    $conditions = array(
	        'table' => $this->_get_order_table($uid),
	        'where' => $where,
	        'start' => $start,
	        'limit' => $count
	    );
	    
	    if($sort){
    	    if($is_group){
	           $conditions['order'] = ' max(order_time) desc ';
    	       $conditions['group'] = $group;
    	    }else{
	           $conditions['order'] = ' order_time desc ';
    	    }
	    }
	    $sql = $this->find($conditions);
	    $rs = $this->db->query_read($sql);
	    $row = $rs ? $rs -> result_array() : array();
	
	    return $row;
	}
	
    //土豪、包养榜单
	public function get_list_top ($user_type) {
	    $user_type = $this->global_func->filter_int($user_type);
        if($user_type==1){//打赏
            $type = 'from_uid';
        }
        if($user_type==2){//被打赏
            $type = 'to_uid';
        }
        $today = date('Y-m-d');
        $todays = strtotime($today." 00:00:00") - 2592000;//30天前
        $sql = "SELECT sum(amount) as all_amount,".$type." as uid FROM `gl_order_finish` WHERE type=10 and status=11 and order_time >'".$todays."'  group by ".$type." order by all_amount desc limit 0,10";
	 
        $rs = $this->db->query_read($sql);
	    $row = $rs ? $rs -> result_array() : array();
	    return $row;
	}
    //=======================打赏 end by hl====================================//
}
