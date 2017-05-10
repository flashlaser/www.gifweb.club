<?php
/**
 *
 * @name Pay_model
 * @desc 仅为支付model使用
 *
 * @author	 liule1
 * @date 2016.01.27
 *
 * @copyright (c) 2016 SINA Inc. All rights reserved.
 */
class Pay_model extends MY_Model {
	
	private $pay_conf = array();
	var $_cache_key_pre = '';
	var $_cache_expire = 600;
	function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":pay_model:";
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
    
	//=====================================================================
	/**
	 * 当月提现次数
	 * @param  [type] $from_uid [description]
	 * @return [type]           [description]
	 */
	public function get_cur_month_withdraw_count($from_uid) {
		$from_uid = $this->global_func->filter_int($from_uid);
		$time1 = strtotime(date('Y-m-01 00:00:00', SYS_TIME));
		$time2 = strtotime(date('Y-m-01 00:00:00', $this->global_func->add_months_to_time(1, SYS_TIME))) -1;
		
		$sql = "SELECT count(*) as c from {$this->_get_order_finish_table()} WHERE from_uid='$from_uid' AND type='" . ORDER_DRAW . "' AND order_time between $time1 and $time2";
		
		$row = $this->db->query_read($sql);
		$row = $row ? $row->row_array() : array();
		$return = empty($row['c']) ? 0 : $row['c'];
		
		return (int) $return;
	}
	
	// ===============================================================
	private function _strict_data($order_info) {
		if ($order_info) {
			$order_info['status'] = (int) $order_info['status'];
			$order_info['pay_status'] = (int) $order_info['pay_status'];
			$order_info['type'] = (int) $order_info['type'];
			$order_info['amount'] = (int) $order_info['amount'];
		}
		return $order_info;
	}
	// 支付MODEL，仅查主库表
    public function get_info($order_sn) {
		$data = $this->_get_info_from_db($order_sn);
		$data = $this->_strict_data($data);
		return $data;
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
        $rs = $this->db->query_write($sql);
        $row = $rs ? $rs -> row_array() : array();
		return $row;
	}
	
	// =================================================================
	
	
	/**
	 * 生成新订单
	 * @param  [int] $from_uid [源UID]
	 * @param  [int] $to_uid   [目标UID]
	 * @param  [int] $order_type     [10打赏；20提现；30退款]
	 * @param  [int] $amount   [金额， 单位分！！]
	 * @param  [int] $pay_type [0余额；   1支付宝]
	 * @return [string]           [订单SN]
	 */
    public function new_order($from_uid, $to_uid, $order_type, $amount, $pay_type,$related_type=0, $related_id=0) {
        $from_uid = $this->global_func->filter_int($from_uid);
        $to_uid = $this->global_func->filter_int($to_uid);
        $type = $this->global_func->filter_int($type);
        $amount = $this->global_func->filter_int($amount);
        $pay_type = $this->global_func->filter_int($pay_type);
        
        if (!$from_uid || !$to_uid || !$amount) {
            throw new Exception("bad order params", _PARAMS_ERROR_);
        }
		
        $order_sn = $this->_generate_order_sn($from_uid,$order_type);
        $insert_data = array(
            'from_uid' => $from_uid, 
            'to_uid' => $to_uid,
            'type' => $order_type,
            'amount' => $amount,
            'pay_type' => $pay_type,
			'pay_name' => $this->pay_conf[$pay_type],
			'related_type' => intval($related_type),
			'related_id' => intval($related_id),
            'order_time' => SYS_TIME,
            'order_sn' => $order_sn,
            'pay_status' => ORDER_PAY_STATUS_UNPAID,
			'status' => ORDER_PAY_STATUS_UNPAID,
        );
		
        $this->db->insert($this->_get_order_table($from_uid), $insert_data);

        $this->order_model->_clear_list($from_uid, ORDER_REWARD, REWARD_FROM);
        $this->order_model->_clear_list($to_uid, ORDER_REWARD, REWARD_TO);
		$this->order_model->_clear_count('', 1);
		$this->order_model->_clear_count('', 2);
		$this->order_model->_clear_count(1, $related_id);
		return $order_sn;
	}

	/**
	 * 得到新订单号
	 * @return  string
	 */
	 /**
     *    生成订单号
     *
     *    @author    Garbin
     *    @return    string
     */
    public function _generate_order_sn($uid, $order_type){
        /* 选择一个随机的方案 */
		$day_time = strtotime(date("Y-m-d 23:59:59"));
        mt_srand((double) microtime() * 1000000);
        $order_sn = date("YmdHis") . 
		$order_sn .= str_pad($order_type, 2, '0', STR_PAD_LEFT);	// 订单类型
		$order_sn .= str_pad(abs($uid%100), 2, '0', STR_PAD_LEFT);	// 用户uid取余
		
		$rKey = $this->_cache_key_pre ."order_sn";

		//判断订单号是否已存在
		$flag = 1;
		$succ = false;
		do{
			$randNum = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT); // 5位随机数
			if(!$this->cache->redis->hExists($rKey, $order_sn.$randNum)){
				$order_sn  = $order_sn.$randNum;
				$this->cache->redis->hSet($rKey, $order_sn, time());
				$this->cache->redis->expire($rKey, $day_time - time());
				$succ = true;
				break;
			}
			
			$flag++;

		}while($flag < 10 && !$succ);

        return $order_sn;
    }
	
	
	// ======================================================================================================= //
	// 获取总打赏金额,使用finish表，直接查库
	public function get_total_reward($from_uid) {
		$from_uid = $this->global_func->filter_int($from_uid);
		$data = $this->_get_total_reward_from_db($from_uid);
		return $data;
	}
	
	private function _get_total_reward_from_db($from_uid) {
		$from_uid = $this->global_func->filter_int($from_uid);
		
		$conditions = array(
			'field' => 'sum(`amount`) as s',
			'table' => $this->_get_order_finish_table(),
			'where' => array(
				'from_uid' => $from_uid
			),
			'start' => 0,
            'limit' => 1
		);
        $sql = $this->find($conditions);
        $rs = $this->db->query_read($sql);
        $row = $rs ? $rs -> row_array() : array();
		
		return (int)$row['s'];
	}
	
    // ============================================================================================================//
    /**
     * 完成订单， 插入到完成订单表， 更改用户余额
     * @param  $order_sn [description]
     * @return int  是否成功
     */
    public function finish_order($order_sn, $order_type, $pay_type, $pay_account = '', $pay_name = '', $pay_memo = '') {
		
		$status = 0;
		$pay_status = ORDER_PAY_STATUS_PAID;
		if ($order_type == ORDER_REWARD) {
			$status = ORDER_STATUS_REWARD_SUCCESS;
		} elseif ($order_type == ORDER_DRAW) {
			$status = ORDER_STATUS_REWARD_WAIT;
		}
		
		if ($pay_type == PAY_BY_BALANCE) {
			$pay_status = ORDER_PAY_STATUS_FINISH;
		} 
		
		
        $update_data = array(
			'pay_status' => $pay_status,
			'finish_time' => SYS_TIME,
			'pay_type' => $pay_type,
			'pay_account' => $pay_account,
			'pay_name' => $pay_name,
			'status' => $status,
		);
		$where = array(
			'order_sn' => $order_sn,
			'pay_status' => ORDER_PAY_STATUS_UNPAID
		);
		$this->order_memo($order_sn, 'row set to finish');
		$this->db->update($this->_get_order_table_by_order_sn($order_sn), $update_data, $where, 1);
		
		if (!mysql_affected_rows($this->db->conn_write)) {
			return 0;
		}
		$this->order_memo($order_sn, 'row set to finish success');
		
		$order_info = $this->get_info($order_sn);
		$insert_data = array(
			'order_sn' => $order_sn,
			'from_uid' => $order_info['from_uid'],
			'to_uid' => $order_info['to_uid'],
			'type' => $order_info['type'],
			'amount' => $order_info['amount'],
			'pay_status' => $pay_status,
			'status' => $status,
			'pay_type' => $pay_type,
			'pay_account' => $pay_account,
			'pay_name' => $pay_name,
			'related_type' => $order_info['related_type'],
			'related_id' => $order_info['related_id'],
			'order_time' => $order_info['order_time'],
			'finish_time' => SYS_TIME,
			
		);
		
		$this->db->insert($this->_get_order_finish_table(), $insert_data);
		
		$this->order_memo($order_sn, 'insert to finish table, data:' . json_encode($insert_data));
		$this->order_memo($order_sn, 'finish memo, data:' . is_array($pay_memo) ? json_encode($pay_memo) : $pay_memo);
		return 1;
    }
	
	
	// ===========================================================================================================
	/**
	 * 打赏订单失败
	 * @param  [type] $order_sn [description]
	 * @return [type]           [description]
	 */
	public function reward_order_fail($order_sn) {
		$update_data = array(
			'pay_status' => ORDER_PAY_STATUS_FAIL,
			'status' => ORDER_STATUS_REWARD_FAIL,
			'finish_time' => SYS_TIME,
		);
		$where = array(
			'order_sn' => $order_sn,
		);
		$this->db->update($this->_get_order_table_by_order_sn($order_sn), $update_data, $where, 1);
		$this->db->update($this->_get_order_finish_table(), $update_data, $where, 1);
	}

}
