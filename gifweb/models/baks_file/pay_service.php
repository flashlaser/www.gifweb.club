<?php
/**
 *
 * @name Pay_service
 * @desc null
 *
 * @author	 liule1
 * @date 2016.01.06
 *
 * @copyright (c) 2016 SINA Inc. All rights reserved.
 */
class Pay_service extends CI_Model {
	var $alipay_notify = null;
	
	function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":alipay:";
		
		/// 支付宝
		$this->load->config('alipay', true);
		$alipay_config = $this->config->item('alipay');
		$this->load->library('pay/alipay/AlipayNotify');
		$this->alipaynotify->set_config($alipay_config);
		$this->load->model('common_model');
		$this->load->model('pay_model');
		$this->load->model('order_model');
		$this->load->model('user_balance_model');
		$this->load->model('push_message_model');
	}
	
	
	public function alipay_sign_check($params) {
		return $this->alipaynotify->getSignVeryfy($params, $params['sign']);
	}
	
	public function alipay_get_response($notify_id) {
		return $this->alipaynotify->getResponse($notify_id);
	}
	
	// ==============================================================================================
	/**
	 * 付款
	 * @param  [type] $order_sn    [description]
	 * @param  [type] $payment     [description]
	 * @param  string $pay_account [description]
	 * @param  string $pay_name    [description]
	 * @param  string $memo        [description]
	 * @return [type]              [description]
	 */
	public function pay($order_sn, $payment, $pay_account = '', $pay_name = '', $memo = '') {
		try {
			$log = array(
				"order:$order_sn pay:"
			) + func_get_args();
			PLog::w_DebugLog($log);
			
			$order_info = $this->pay_model->get_info($order_sn);
			if (empty($order_info) ) {
				throw new Exception('参数错误:' . $order_sn . ', ' . $this->pay_model->db->last_query(), _PARAMS_ERROR_);
			}
			
			if ((int)$order_info['type'] != ORDER_REWARD ) {
				// 订单类型错误
				throw new Exception('order type error', _PARAMS_ERROR_);
			}
			if ((int)$order_info['pay_status'] != ORDER_PAY_STATUS_UNPAID && (int)$order_info['pay_status'] != ORDER_PAY_STATUS_FAIL) {
				// 订单状态不是待支付
				throw new Exception('order pay status error:' . $order_info['pay_status'], _PARAMS_ERROR_);
			}
			
			$log = array(
				"order:$order_sn trans begin"
			);
			PLog::w_DebugLog($log);
			
			
			$this->common_model->trans_begin();
			
			$change_balance_reward_total = 0;
			
			// 资金变动
			if ($payment === PAY_BY_BALANCE) {
				if ($this->common_model->is_ban_user()) {
					throw new Exception(_BANNED_MSG_, _USER_BANNED_);
				}
				
				$from_user_balance_info = $this->user_balance_model->getInfo($order_info['from_uid']);
				if ($from_user_balance_info['balance'] < $order_info['amount']) {
					throw new Exception('余额不足', _DATA_ERROR_);
				}
				
				if (!$this->user_balance_model->balance_reward($order_info['from_uid'], $order_info['amount'], PAY_BY_BALANCE)) {
					throw new Exception('minus fail', _DATA_ERROR_);
				}
				if (!$this->user_balance_model->balance_add($order_info['to_uid'], $order_info['amount'])) {
					throw new Exception('add fail', _DATA_ERROR_);
				}
				
				$change_balance_reward_total = $order_info['amount'];
				
			} elseif ($payment === PAY_BY_ALIPAY) {
				if (!$this->user_balance_model->balance_add($order_info['to_uid'], $order_info['amount'])) {
					throw new Exception('add fail', _DATA_ERROR_);
				}
			} else {
				throw new Exception('payment error', _PARAMS_ERROR_);
			}
			// 完成订单
			$this->pay_model->finish_order($order_sn, $order_info['type'], $payment, $pay_account , $pay_name , $memo );
			
			$this->user_balance_model->user_balance_info_change($order_info['from_uid'], 0, $order_info['amount'], $change_balance_reward_total);
			$this->user_balance_model->user_balance_info_change($order_info['to_uid'], $order_info['amount'], 0, 0);
			
			$this->common_model->trans_commit();
			
			$log = array(
				"order:$order_sn trans commit"
			);
			PLog::w_DebugLog($log);
			
			
			// 发送通知
			if ($order_info['related_type'] == 1) {
				
				$this->push_message_model->push(5, 0, $order_info['related_id'],1 , 6, array('from_uid' => $order_info['from_uid'], 'amount' => $order_info['amount']));
			}
			
			// 相关缓存删除下
			$this->user_balance_model->delete_user_cache($order_info['from_uid']);
			$this->user_balance_model->delete_user_cache($order_info['to_uid']);
			$this->order_model->_clear_list($order_info['from_uid'], ORDER_REWARD, REWARD_FROM);
			$this->order_model->_clear_list($order_info['to_uid'], ORDER_REWARD, REWARD_TO);
			$this->order_model->_clear_list('', ORDER_REWARD, REWARD_FROM);
			$this->order_model->_clear_list('', ORDER_REWARD, REWARD_TO);
			$this->order_model->_clear_list('', ORDER_REWARD, '');
			$this->order_model->del_info_to_cache($order_sn);
			$this->order_model->_clear_count('', 2);
			$this->order_model->_clear_count($order_info['to_uid'], 2);
			$this->order_model->_clear_counts(1,$order_info['related_id']);
			
			
			return 1;
		} catch (Exception $e) {
			$this->common_model->trans_rollback();
			
			if ($payment === PAY_BY_BALANCE) {
				// 余额支付失败，则订单失败
				$this->pay_model->reward_order_fail($order_sn);
			}
			
			// 相关缓存删除下
			$this->user_balance_model->delete_user_cache($order_info['from_uid']);
			$this->user_balance_model->delete_user_cache($order_info['to_uid']);
			$this->order_model->del_info_to_cache($order_sn);

			$this->order_model->_clear_list($order_info['from_uid'], ORDER_REWARD, REWARD_FROM);
			$this->order_model->_clear_list($order_info['to_uid'], ORDER_REWARD, REWARD_TO);
			$this->order_model->_clear_list('', ORDER_REWARD, REWARD_FROM);
			$this->order_model->_clear_list('', ORDER_REWARD, REWARD_TO);
			$this->order_model->_clear_count('', 2);
			$this->order_model->_clear_count($order_info['to_uid'], 2);
			$this->order_model->_clear_counts(1,$order_info['related_id']);
			
			// 记录LOG
			$log = array(
				"order:$order_sn trans rollback",
				'message:' . $e->getMessage(),
				'code:' . $e->getCode()
			);
			PLog::w_DebugLog($log);
			
			throw new Exception($e->getMessage(), $e->getCode());
		}
	}
	/**
	 * 提现
	 * @param  [type] $from_uid    [description]
	 * @param  [type] $money       [description]
	 * @param  [type] $pay_account [description]
	 * @param  [type] $pay_name    [description]
	 * @return [type]              [description]
	 */
	public function withdraw($from_uid, $money, $pay_account, $pay_name) {
		
		
		try {
			$log = array(
				"withdraw:$order_sn pay:"
			) + func_get_args();
			PLog::w_DebugLog($log);
			
			$from_uid = $this->global_func->filter_int($from_uid);
			$money = $this->global_func->filter_int($money);
			
			if (empty($from_uid) ||  $money <= 0) {
				throw new Exception('参数错误1', _PARAMS_ERROR_);
			}
			if (!USER_DRAWCASH) {
				// 开关没开
				throw new Exception(USER_DRAWCASH_NOTE, _PARAMS_ERROR_);
			}
			
			if (DRAW_MIN_MONEY > $money || DRAW_MAX_MONEY < $money) {
				// 金额错误
				throw new Exception('money domain error', _PARAMS_ERROR_);
			}
			
			if ($this->common_model->is_ban_user()) {
				throw new Exception(_BANNED_MSG_, _USER_BANNED_);
			}
			
			// 次数
			if (DRAW_COUNT_PER_MONTH <= $this->pay_model->get_cur_month_withdraw_count($from_uid)) {
				throw new Exception("当前每月提现次数仅限" . DRAW_COUNT_PER_MONTH . "次", _PARAMS_ERROR_);
			}
			
			
			$from_user_balance_info = $this->user_balance_model->getInfo($from_uid);
			if ($from_user_balance_info['alipay_account'] != $pay_account || $from_user_balance_info['alipay_name'] != $pay_name) {
				throw new Exception('帐号错误', _DATA_ERROR_);
			}
			if ($from_user_balance_info['balance'] < $money) {
				throw new Exception('余额不足', _DATA_ERROR_);
			}
			
			
			$log = array(
				"withdraw:$order_sn trans begin"
			);
			PLog::w_DebugLog($log);
			
			// 完成订单
			$this->common_model->trans_begin();
			
			$order_sn = $this->pay_model->new_order($from_uid, -1, ORDER_DRAW, $money, PAY_BY_ALIPAY, 0, 0);
			
			// 资金变动
			if (!$this->user_balance_model->balance_withdraw($from_uid, $money, $pay_account, $pay_name)) {
				throw new Exception('minus fail', _DATA_ERROR_);
			}
			
			// 已经完成资金表懂，完成订单
			$this->pay_model->finish_order($order_sn, ORDER_DRAW, PAY_BY_BALANCE, $pay_account , $pay_name  );
			
			$this->common_model->trans_commit();
			
			$log = array(
				"withdraw:$order_sn trans commit"
			);
			PLog::w_DebugLog($log);
			
			// 相关缓存删除下
			$this->user_balance_model->delete_user_cache($from_uid);
			$this->order_model->_clear_list($from_uid, ORDER_DRAW, REWARD_FROM);
			
			return 1;
		} catch (Exception $e) {
			$this->common_model->trans_rollback();
			
			// 相关缓存删除下
			$this->user_balance_model->delete_user_cache($from_uid);
			
			// 记录LOG
			$log = array(
				"withdraw:$order_sn trans rollback",
				'message:' . $e->getMessage(),
				'code:' . $e->getCode()
			);
			PLog::w_DebugLog($log);
			
			throw new Exception($e->getMessage(), $e->getCode());
		}
	}
	


}
