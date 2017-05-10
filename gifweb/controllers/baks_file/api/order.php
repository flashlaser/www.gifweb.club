<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

/**
 *
 * @name Order
 * @desc null
 *
 * @author	 liule1
 * @date 2016.01.07
 *
 * @copyright (c) 206 SINA Inc. All rights reserved.
 *
 * @property	global_func		$global_func
 */
class Order extends MY_Controller {
	public function __construct() {
		parent::__construct ();
		$this->load->model('pay_model');
		$this->load->model('answer_model');
		$this->load->model('question_model');
		$this->load->model('order_model');
		$this->load->model('pay_service');
		$this->load->model('qa_model');
	}

	public function new_order() {
		$related_type = ( int ) $this->input->get_post ( 'type', true );
		$related_id = ( int ) $this->input->get_post ( 'mark', true );
		$money = intval($this->input->get_post ( 'money', true ));	// 客户端传过来单位为“分”
		$pay_way = (int) $this->input->get_post('payWay');

		$from_uid = $this->user_id;
		$to_uid = 0;
		
		try {
			if (!$from_uid || empty($related_type) || empty($related_id) || $money < 1 ) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}
			
			if ($related_type == 1) {
				// 答案
				$mark_info = $this->answer_model->get_info($related_id);
				$to_uid = $mark_info ? $mark_info['uid'] : 0;
			} elseif ( $related_type == 2) {
				// 问题
				$mark_info = $this->question_model->get_info($related_id);
				$to_uid = $mark_info ? $mark_info['uid'] : 0;
			} else {
				throw new Exception('type error', _PARAMS_ERROR_);
			}

			if (!$to_uid) {
				throw new Exception('mark error', _PARAMS_ERROR_);
			}
			
			// 金额限制
			if ($money > REWARD_MAX_MONEY) {
				throw new Exception('max money', _PARAMS_ERROR_);
			}
			
			$payment = $pay_way == 0 ? PAY_BY_ALIPAY : PAY_BY_BALANCE;
			
			// 默认余额支付
			$order_sn = $this->pay_model->new_order($from_uid, $to_uid, ORDER_REWARD, $money, $payment, $related_type, $related_id);
			
			$data = array(
				'orderNo' => $order_sn
			);
			Util::echo_format_return(_SUCCESS_, $data);
			return 1;
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}


	public function pay_by_balance() {
		$order_sn = $this->input->get_post ( 'orderNo', true );
		$money = intval($this->input->get_post ( 'money', true ));	// 客户端传过来单位为“分”
		$from_uid = $this->user_id;
		$to_uid = $this->input->get_post ( 'uid', true );
		$gesture_code = $this->input->get_post('gestureCode');
		
		try {
			if (!$from_uid || empty($order_sn) || empty($to_uid) || $money < 1 ) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}
			
			$order_info = $this->pay_model->get_info($order_sn);
			
			if (empty($order_info) || $order_info['amount'] != $money || $order_info['to_uid'] != $to_uid) {
				throw new Exception('参数错误1', _PARAMS_ERROR_);
			}
			// 判断是本人
			if ($order_info['from_uid'] != $from_uid) {
				throw new Exception('user error', _PARAMS_ERROR_);
			}
			
			// 支付
			$user_info = $this->user_balance_model->getInfo($from_uid);
			if ($user_info['balance_reward_total'] + $money > REWARD_MIN_MONEY_NEED_GESTURE) {
				// 需要手势密码，验证
				$check = $this->user_balance_model->verify_gpw($from_uid, $gesture_code);
				if (!$check || !is_array($check) || !$check['gestureState']) {
					throw new Exception('gestureState error', _PARAMS_ERROR_);
				}
			}
			
			$return = $this->pay_service->pay($order_sn, PAY_BY_BALANCE);
			
			$data = array(
				'payResult' => 1,
				'timestamp' => date('Y-m-d H:i:s', SYS_TIME),
			);
			Util::echo_format_return(_SUCCESS_, $data);
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}
	
	// 查询支付结果
	public function get_order_pay_status() {
		$order_sn = $this->input->get_post('orderNo');
		
		try {
			if (!$order_sn ) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}
			
			$repeat = 3;
			while($repeat-- > 0) {
				$order_info = $this->pay_model->get_info($order_sn);
				if ($order_info['pay_status']) {
					break;
				} else {
					sleep(1);
				}
			}
			if (empty($order_info)) {
				throw new Exception('参数错误1', _PARAMS_ERROR_);
			}
			
			// 判断是本人
			if ($order_info['from_uid'] != $this->user_id) {
				throw new Exception('user error', _PARAMS_ERROR_);
			}
			
			$data = array(
				'payResult' => $order_info['pay_status'],
				'timestamp' => $order_info['finish_time'] ? date('Y-m-d H:i:s', $order_info['finish_time'] ) : '',
			);
			Util::echo_format_return(_SUCCESS_, $data);
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}
	public function withdraw() {
		$pay_account = $this->input->get_post('payAccount');
		$pay_name = $this->input->get_post('payRealName');
		$money = intval($this->input->get_post ( 'payValue', true ));	// 客户端传过来单位为“分”

		$from_uid = $this->user_id;
		
		try {
			if (!$from_uid || empty($pay_account) || empty($pay_name) || $money < 1 ) {
				throw new Exception('参数错误1', _PARAMS_ERROR_);
			}
			
			if (!USER_DRAWCASH) {
				throw new Exception(USER_DRAWCASH_NOTE, _DATA_ERROR_);
			}
			
			// 金额限制
			if ($money < DRAW_MIN_MONEY || $money > DRAW_MAX_MONEY) {
				throw new Exception('money domain error', _PARAMS_ERROR_);
			}
			
			$this->pay_service->withdraw($from_uid, $money, $pay_account, $pay_name);
			
			$data = array(
				'timestamp' => date('Y-m-d H:i:s', SYS_TIME),
			);
			Util::echo_format_return(_SUCCESS_, $data);
			return 1;
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}
	
	// =================================================================================================

	//70 打赏记录-打赏收入列表     2.0版本新增
	public function income_list() {
	    $guid = $this->user_id;
	    $page	  	= $this->input->get('page',true) ? $this->input->get('page',true) : 1;
	    $count	  	= $this->input->get('count',true) ? $this->input->get('count',true) : 10;
	    $start 		= ($page==1) ? 0 : ($page-1) * $count;
	    
	    try {

	        if (empty($guid)) {
	            throw new Exception('用户id不能为空', _PARAMS_ERROR_);
	        }
	        
	        $income = $this->order_model->get_list($guid ,ORDER_REWARD,REWARD_TO,$start,$count,1);
	        $data = array();
	        foreach ($income as $k=>$v){
	            $dataInfo[$k] = $this->User->getUserInfoById($v['from_uid']);
	            
	            if($v['related_type'] == 1){//针对答案的打赏
	               $this->load->model('answer_content_model');
	               $comment[$k] = $this->qa_model->convert_content_to_frontend($this->answer_content_model->get_content($v['related_id']));
	            }else{
	               $this->load->model('question_content_model');
	               $comment[$k] = $this->qa_model->convert_content_to_frontend($this->question_content_model->get_content($v['related_id']));
	            }
	            $data[$k]['absid']         = (string)$v['order_sn'];
	            $data[$k]['guid']          = (string)$v['from_uid'];
	            $data[$k]['nickName']      = (string)$dataInfo[$k]['nickname'];
	            $data[$k]['headImg']       = (string)$dataInfo[$k]['avatar'] ? (string)$dataInfo[$k]['avatar'] : '';
	            $data[$k]['uLevel']        = (int)$dataInfo[$k]['level'];
	            $data[$k]['medalLevel']    = (int)$dataInfo[$k]['rank'] == 1 ? 1 : 0;
	            $data[$k]['updateTime']    = date('Y-m-d H:i:s' ,$v['order_time']);
	            $data[$k]['comment']       = (string)$comment[$k] ? (string)$comment[$k] : '';
	            $data[$k]['money']         = $v['amount']/100;
	            $data[$k]['status']        = 1;
	        }
	        Util::echo_format_return(_SUCCESS_, $data);
	    } catch (Exception $e) {
	        Util::echo_format_return($e->getCode(), array(), $e->getMessage());
	    }
	}

	//71 打赏记录-给他人打赏列表     2.0版本新增
	public function pay_list() {
	    $guid = $this->user_id;
	    $page	  	= $this->input->get('page',true) ? $this->input->get('page',true) : 1;
	    $count	  	= $this->input->get('count',true) ? $this->input->get('count',true) : 10;
	    $start 		= ($page==1) ? 0 : ($page-1) * $count;
	     
	    try {
	
	        if (empty($guid)) {
	            throw new Exception('用户id不能为空', _PARAMS_ERROR_);
	        }
	         
	        $income = $this->order_model->get_order_list($guid ,ORDER_REWARD,REWARD_FROM,$start,$count,1);
	        $data = array();
	        foreach ($income as $k=>$v){
	            $dataInfo[$k] = $this->User->getUserInfoById($v['to_uid']);

	            if($v['related_type'] == 1){//针对答案的打赏
	                $this->load->model('answer_content_model');
	                $comment[$k] = $this->qa_model->convert_content_to_frontend($this->answer_content_model->get_content($v['related_id']));
	            }else{
	                $this->load->model('question_content_model');
	                $comment[$k] = $this->qa_model->convert_content_to_frontend($this->question_content_model->get_content($v['related_id']));
	            }
	            
	            $data[$k]['absId']         = (string)$v['order_sn'];
	            $data[$k]['updateTime']    = date('Y-m-d H:i:s' ,$v['order_time']);
	            $data[$k]['comment']       = (string)$comment[$k] ? (string)$comment[$k] : '';
	            $data[$k]['uid']           = (string)$v['to_uid'];
	            $data[$k]['nickName']      = (string)$dataInfo[$k]['nickname'];
	            $data[$k]['payWay']        = $v['pay_type'] == 1 ? 0 : 1;
	            $data[$k]['money']         = $v['amount']/100;
	            $data[$k]['type']          = 1;
	            $data[$k]['mark']          = $v['related_id'];
	            if($v['status'] == 0){
	               $data[$k]['payResult']  = 0;
	            }
	            if($v['status'] == ORDER_STATUS_REWARD_FAIL){
	               $data[$k]['payResult']  = 2;
	            }
	            if($v['status'] == ORDER_STATUS_REWARD_SUCCESS){
	               $data[$k]['payResult']  = 1;
	            }
	        }
	        Util::echo_format_return(_SUCCESS_, $data);
	    } catch (Exception $e) {
	        Util::echo_format_return($e->getCode(), array(), $e->getMessage());
	    }
	}

	//72 提现明细列表     2.0版本新增
	public function withdraw_list() {
	    $guid = $this->user_id;
	    $page	  	= $this->input->get('page',true) ? $this->input->get('page',true) : 1;
	    $count	  	= $this->input->get('count',true) ? $this->input->get('count',true) : 10;
	    $start 		= ($page==1) ? 0 : ($page-1) * $count;
	
	    try {
	
	        if (empty($guid)) {
	            throw new Exception('用户id不能为空', _PARAMS_ERROR_);
	        }
	
	        $withdraw = $this->order_model->get_list($guid ,ORDER_DRAW,REWARD_FROM,$start,$count);
	        $data = array();
	        foreach ($withdraw as $k=>$v){
	            $data[$k]['absid']         = (string)$v['order_sn'];
	            $data[$k]['describe']      = '提取现金';
	            $data[$k]['money']         = $v['amount']/100;
	            $data[$k]['updateTime']    = date('Y-m-d H:i:s' ,$v['finish_time']);
	            if($v['status'] == 21){
	                $data[$k]['status'] = 1;
	            }
	            if($v['status'] == 0){
	                $data[$k]['status'] = 2;
	            }
	            if($v['status'] == 22){
	                $data[$k]['status'] = 3;
	            }
	        }
	        Util::echo_format_return(_SUCCESS_, $data);
	    } catch (Exception $e) {
	        Util::echo_format_return($e->getCode(), array(), $e->getMessage());
	    }
	}

	//73 某回答的打赏人列表      2.0版本新增
	public function areward_list_by_answer() {
	    $guid = $this->user_id;
	    $absId	  	= $this->input->get('absId',true) ? $this->input->get('absId',true) : '';
	    $page	  	= $this->input->get('page',true) ? $this->input->get('page',true) : 1;
	    $count	  	= $this->input->get('count',true) ? $this->input->get('count',true) : 10;
	    $start 		= ($page==1) ? 0 : ($page-1) * $count;
	
	    try {
	
	        if (empty($absId)) {
	            throw new Exception('absId不能为空', _PARAMS_ERROR_);
	        }
	        
	        $areward = $this->order_model->get_list('' ,ORDER_REWARD,'',$start,$count,1,1,$absId,1);
	        $data = array();
	        $this->load->model('friend_model');
	        foreach ($areward as $k=>$v){
	            $dataInfo[$k] = $this->User->getUserInfoById($v['from_uid']);
	            $data[$k]['guid']          = (string)$v['from_uid'];
	            $data[$k]['nickName']      = (string)$dataInfo[$k]['nickname'];
	            $data[$k]['headImg']       = (string)$dataInfo[$k]['avatar'] ? (string)$dataInfo[$k]['avatar'] : '';
	            $data[$k]['uLevel']        = (int)$dataInfo[$k]['level'];
	            $data[$k]['medalLevel']    = (int)$dataInfo[$k]['rank'] == 1 ? 1 : 0;

	            if($guid){
	                //是否关注对方
	                $is_attention = $this->friend_model->is_friend($guid, $v['from_uid']);
	                if($is_attention){
	                    //查询是否互相关注
	                    $is_attention_other = $this->friend_model->is_friend($v['from_uid'],$guid);
	                    if($is_attention_other){
	                        $data[$k]['relationship'] = 2;
	                    }else{
	                        $data[$k]['relationship'] = 1;
	                    }
	                }else{
	                    $data[$k]['relationship'] = 0;
	                }
	            }else{
	                $data[$k]['relationship'] = 0;
	            }
	        }
	        Util::echo_format_return(_SUCCESS_, $data);
	    } catch (Exception $e) {
	        Util::echo_format_return($e->getCode(), array(), $e->getMessage());
	    }
	}
}
