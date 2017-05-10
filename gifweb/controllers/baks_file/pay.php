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
class Pay extends CI_Controller {
	public function __construct() {
		parent::__construct ();
		$this->load->model('user_model');
        $this->load->library('global_func');
        $this->load->model('common_model');
		$this->load->model('pay_service');
		
		
	}

	public function test() {
		$data = '{"discount":"0.00","payment_type":"1","subject":"\u8f6c\u8d26","trade_no":"2016021821001004670234801545","buyer_email":"llyunyan@foxmail.com","gmt_create":"2016-02-18 18:53:34","notify_type":"trade_status_sync","quantity":"1","out_trade_no":"20160218185243100171205","seller_id":"2088121657982456","notify_time":"2016-02-18 18:53:36","body":"\u7528\u4e8e\u8f6c\u8d26\u63cf\u8ff0","trade_status":"TRADE_SUCCESS","is_total_fee_adjust":"N","total_fee":"0.01","gmt_payment":"2016-02-18 18:53:35","seller_email":"xycm@973.sinanet.com","price":"0.01","buyer_id":"2088302132188673","notify_id":"a6655c1d8fd84e50baeb9ff04037fa5l66","use_coupon":"N","sign_type":"RSA","sign":"L5CpjmjSZW08l3rtxTb\/SoBYGNcrt8zd9QXbboKL5jA3gThLX318zBH8cy995yfJr8VGBlTbQuo+E12arlS0OaSvl9aXwqQhoJTXd8fo\/v0vDdpj3\/Ac+aeAuIbn03nu78Fd1BgmMNpTxxqddMqNbbxEPamOy1wGZ+seeyVIkts="}';
		
		$data = json_decode($data, 1);
		$_POST = $data;
		$a = $this->alipay_notify();
		var_dump($a);
	}
	public function test1() {
		var_dump($this->pay_service->alipay_get_response('9cb5ff39b99a035ca0ba518f57a7813l64'));
	}
	
	
    /**
     * 支付宝通知
     * @return [type] [description]
     */
	public function alipay_notify() {
		$data = $_POST;

		$this->load->driver('cache');
		
		try {
			
			// security check
			if (!$this->pay_service->alipay_sign_check($data)) {
				throw new Exception("sign check error", 1);
			}
			if ($this->pay_service->alipay_get_response($data['notify_id']) !== 'true') {
				// 测试现象：这个只能成功一次。
				throw new Exception("response false", 2);
			}
			
			$order_sn = $data['out_trade_no'];
			$pay_account = $data['buyer_email'];
			$pay_name = '支付宝';
			$pay_memo = $data;
			
			
			// 记 log
			$this->load->model('pay_model');
			$this->pay_model->order_memo($order_sn, 'alipay notify memo, data:' .  json_encode($pay_memo));
			
			// 判断订单状态
			if ($data['trade_status'] === 'WAIT_BUYER_PAY') {
				// 待支付直接输出 success
				throw new Exception("order WAIT_BUYER_PAY : {$data['trade_status']}" , 200);
			}
			
			// status check 
			if ($data['trade_status'] !== 'TRADE_SUCCESS') {
				throw new Exception("order status error: {$data['trade_status']}" , 4);
			}
			
			// 完成支付
			$this->pay_service->pay($order_sn, PAY_BY_ALIPAY, $pay_account, $pay_name, $pay_memo);
			
			echo 'success';
		} catch (Exception $e) {
			if ($e->getCode() == 200) {
				// WAIT_BUYER_PAY
				echo 'success';
			} else {
				$log = array(
					'message:' . $e->getMessage(),
					'code' . $e->getCode(),
					"post data : " => $data,
				);
				PLog::w_DebugLog($log);
				echo 'error';
				$cache_key = 'gl_app:pay_notify:error';
				$this->cache->redis->lPush($cache_key, json_encode($log));
				$this->cache->redis->expire($cache_key, 86400);
			}
		}
		
		
	}
	

}
