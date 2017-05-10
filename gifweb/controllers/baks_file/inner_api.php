<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

/**
 *
 * @name Index
 * @desc null
 *
 * @author	 liule1
 * @date 2015年9月22日
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 *
 * @property	Image_service	$image_service
 */
class Inner_api extends CI_Controller {
	public function __construct() {
		parent::__construct ();
		$this->load->driver('cache');
		$this->load->library('global_func');
		$this->load->model('common_model');
	}
	public function check_weibo_user($weibo_uid = 0) {
		if (!$weibo_uid) {
			Util::echo_format_return(_PARAMS_ERROR_,'','参数错误', 1);
		}

		$this->load->model('event_weibo_user_device_model');
		if ($this->event_weibo_user_device_model->check_weibo_uid($weibo_uid)) {
			Util::echo_format_return(_SUCCESS_,'','', 1);
		} else {
			Util::echo_format_return(_DATA_ERROR_,'','参数错误', 1);
		}
	}

	public function check_weibo_user_and_follow($weibo_uid = 0) {
		if (!$weibo_uid) {
			Util::echo_format_return(_PARAMS_ERROR_,'','参数错误', 1);
		}

		$this->load->model('event_weibo_user_device_model');
		$uid = empty($data) ? 0 : $data['uid'];
		if ($this->event_weibo_user_device_model->check_weibo_uid($weibo_uid)) {
			$this->load->model('user_model');
			$uid = $this->user_model->getUidByOpenid($weibo_uid, 2);

			// 判断有无关注游戏
			$this->load->model('follow_model');
			if ($this->follow_model->get_one_data($uid, 3)) {
				Util::echo_format_return(_SUCCESS_,'','', 1);
			} else {
				Util::echo_format_return(_DATA_ERROR_,'','数据错误', 1);
			}
		} else {
			Util::echo_format_return(_DATA_ERROR_,'','数据错误', 1);
		}
	}
	
	public function get_zq_url_by_name() {
		$game_name = $this->input->get_post('game_name');
		if (empty($game_name)) {
			Util::echo_format_return(_DATA_ERROR_,'','数据错误', 1);
		}
		$url = base_url() . 'zq';
		$this->load->model('game_model');
		$id = $this->game_model->get_gid_by_name($game_name);
		if ($id) {
			// $url = "http://www.wan68.com/zq/juhe_page/{$id}";
			$url = base_url() . "zq/juhe_page/{$id}";
		}
		Util::echo_format_return(_SUCCESS_,array('url' => $url),'', 1);
	}
	
	//客户端ip与服务端IP比较
	private function check_ip(){
		//获取客户端IP
		$addr_ip = Util::getRealIp();
		
		$tmp_arr = explode('.', $addr_ip);
		$conc = $tmp_arr['0'] . "." . $tmp_arr['1'];

		//获取服务端IP
		$server_ip = gethostbyname($_SERVER["SERVER_NAME"]);
		$tmp_arr2 = explode('.', $server_ip);
		$conc2 = $tmp_arr2['0'] . "." . $tmp_arr2['1'];
		
		if($conc == $conc2){
			return true;
		}
		
		//允许访问的IP数组
		$check_arr = array(
				'127.0',
				'61.135',
				'123.125',
				'180.149',
				'113.108',
		);
		
		if(in_array($conc, $check_arr)){
			$last_result = true;
		}else{
			$last_result = false;
		}
		
		return $last_result;
	}
	
	//获取包名以及安全key
	public function get_package_and_key(){
		$check_ip_res = $this->check_ip();
		$check_ip_res = true; //服务器IP总是波动改变，回头收集齐全做好IP限制
		if(!$check_ip_res){
			//获取客户端IP
			$addr_ip = Util::getRealIp();
			
			$tmp_arr = explode('.', $addr_ip);
			$conc = $tmp_arr['0'] . "." . $tmp_arr['1'];
			
			//获取服务端IP
			$server_ip = gethostbyname($_SERVER["SERVER_NAME"]);
			$tmp_arr2 = explode('.', $server_ip);
			$conc2 = $tmp_arr2['0'] . "." . $tmp_arr2['1'];
			
			Util::echo_format_return( _SIGN_ERROR_, array (), "内部访问接口" , 1);
		}
		
		// 前端都进行了ENCODE，POST会自动 DECODE，这里把GET预先处理下
		foreach ($_GET as $k => $v) {
			$_GET[$k] = urldecode($v);
		}
		// 获取传参fetch_directory
		$getrow = Util::getpost ();
		
		if (! isset ( $getrow ['partner_id'] ) || ! isset ( $getrow ['sign'] )) {
			Util::echo_format_return( _SIGN_ERROR_, array (), '校验失败1' , 1);
		}
		$partner_id = intval ( $getrow ['partner_id'] );
	
		$safe_key = array(
				'10002' => array (
						'package_id' => '-1526612315',
						'key' => '2872978FDE93543326E247CF7A00AF53'
				),	// android 总，安卓默认
		);
	
		$parnter_key = isset ( $safe_key [$partner_id] ) ? $safe_key [$partner_id] : false;
	
		if (! $parnter_key) { // 未通过验证
			Util::echo_format_return( _SIGN_ERROR_, array (), '校验失败2' , 1);
		}
		
		// 验证参数
		$str = "";
		foreach ( $getrow as $k => $v ) {
			if (in_array ( $k, array (
					"partner_id",
					"sign"
			) )) {
				continue;
			}
			$str .= isset($_POST[$k]) ? $_REQUEST[$k] : urldecode($_REQUEST[$k]);
		}
	
		$str .= $parnter_key['package_id'];
		$sign = substr($str, 0, 2) . substr($parnter_key['key'], 2, 20) . substr($str, 2);
		$sign = md5($sign);
		if (! isset ( $getrow ['sign'] ) || $getrow ['sign'] !== $sign) {
			Util::echo_format_return( _SIGN_ERROR_, array (), '校验失败3' , 1);
		}
	
		$config ['app_config'] = array();
		$sql = "SELECT * FROM `gl_version_swift_config` ";
		$res_data = $this->common_model->get_data_by_sql($sql);
	
		if($res_data)
		{
			//初始化返回数据
			$data = array();
				
			foreach($res_data as $k=>$v)
			{
				$data[$v['partner_id']] = array(
						'package_id'	=> $v['safe_package_id'],
						'key'	=> $v['safe_key'],
				);
			}
		}
		
		//输出数据
		Util::echo_format_return(_SUCCESS_, $data);
		return 1;
	}
}
