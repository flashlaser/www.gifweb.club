<?php

/**
 * MY_Router Class
 *
 * Parses URIs and determines routing
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @author		ExpressionEngine Dev Team
 * @category	Libraries
 * @link		http://codeigniter.com/user_guide/general/routing.html
 *
 *
 * @property	global_func			$global_func
 * @property	common_model		$common_model
 * @property	user_model		$User
 */
class MY_Controller extends CI_Controller {
	public $gifcontent_type;
	public $userinfo;
	public $data;
	public $user_id = 0;
	public $base_user_info;
	public $partner_id = 0;
	public $from_sys = '';
	public $platform = 'ios';
	public $version = '';
	public $device_id = '';
	public $review_state = 0;

	public $request_log = 1;
	public $request_id = 0;		// 每天HTTP请求的唯一ID
	public function __construct($tyle = NULL) {
		parent::__construct ();
		$this->partner_id = $this->input->get_post ( 'partner_id' );
		$this->load->library ( 'global_func' );
		$this->load->driver ( 'cache' );
		$this->load->model ( 'common_model' );
		$this->load->model ( 'user_Model', 'User' );
		$this->load->model('gifcontent_type_model');
		//$this->checkUserLogin ();-------------------------登录验证
		function substr_forecast($str) {
			require_once (APPPATH . "/libraries/Global_func.php");
			$global_func = new Global_func ();
			return $global_func->strcut ( $str ['str'], $str ['num'], $str['dot'] );
		}
		$this->smarty->registerPlugin ( 'function', 'substr_forecast', 'substr_forecast' );

		$this->smarty->assign ( 'base_url', base_url () );
		$this->smarty->assign ( 'userinfo', $this->userinfo );
		$this->smarty->assign ( 'base_user_info', $this->base_user_info );
		$this->smarty->assign ( 'thisClass', $this->router->fetch_class () );
		$this->smarty->assign ( 'thisMethod', $this->router->fetch_method () );

		$platform = $this->input->get_post ( 'platform', true );
		$this->platform = strtolower ( $platform ) == 'ios' ? 'ios' : 'android';

		// 接口请求系统
		$this->from_sys = strtolower ( $this->input->get_post ( 'platform', true ) );

		$this->version = $this->input->get_post('version');

		//增加手机端还是PC端判断 by wangbo8 2016-1-21
		$isMobile = $this->global_func->isMobile();
		$this->smarty->assign ( 'isMobile', $isMobile);

		$this->device_id = $this->input->get_post('deviceId');
		$this->load->library('seed');

		if ($_REQUEST['sign'] == 'backend' && !empty($_REQUEST['profiler']) ) {
			$this->output->enable_profiler(TRUE);
		}

		//动图网 类型
		$conditons_type['where']['is_show'] = 1;
		$conditons_type['where']['is_show'] = 1;
		$conditons_type['order'] = " sorts desc ";
		$this->gifcontent_type = $this->gifcontent_type_model->findData($conditons_type);
		$this->smarty->assign('gifcontent_type_list',$this->gifcontent_type);
	}

	public function request_log($msg = '') {
		if (!$this->request_log) {
			return;
		}

		if (!$this->request_id) {
			// REQUEST 唯一ID
			$cache_key = "glapp:" . ENVIRONMENT . ":request_id:" . date('d');
			$this->request_id = $this->cache->redis->incr($cache_key);
			$this->cache->redis->expire($cache_key, '86400');
		}

		list($s1, $s2) = explode(' ', microtime());
    	$microtime = (floatval($s1) + floatval($s2)) * 1000;

		if (empty($GLOBALS['request_log_time'])) {
            $GLOBALS['request_log_time'] = $microtime;
            $str = "0";
	    } else {
	        $str = $microtime - $GLOBALS['request_log_time'];
	    }

		PLog::w_DebugLog("request_id:" . $this->request_id . " REQUEST_URI:{$_SERVER['REQUEST_URI']} msg:".$msg.", time:$str, caller:". get_caller_info(2));
	}

	private function checkUserLogin() {
		if ($this->input->get_post ( 'sign' ) === 'backend') {
			$uid = trim ( $this->input->get_post ( 'guid', true ) );
			$result = $this->User->getUserInfoById ( $uid );
			$this->base_user_info = $result;
			$this->user_id = $result ['uid'];
			$this->userinfo = $this->User->getUserInfoById ( $this->user_id );
			return;
		}

		// APP登录验证
		if ($this->input->get_post ( 'deviceId' )) {
			$etime = trim ( $this->input->get_post ( 'deadline', true ) );
			$uid = trim ( $this->input->get_post ( 'guid', true ) );
			$gtoken = trim ( $this->input->get_post ( 'gtoken', true ) );
			$result = $this->User->userAppLogin ( $uid, $gtoken, $etime );

			if ($uid && ! (is_array ( $result ) && intval ( $result ['uniqueid'] ))) {
				$msg = 'login user error';
				if ($result = 2003) {
					$msg = _TOKEN_OVERDUE_MSG_;
				}
				Util::echo_format_return ( $result, array($result), '请重新登录', 1 );
			}
		} else {
			$result = $this->User->userLogin ();
		}

		// 登录验证通过初始化信息
		if (is_array ( $result ) && intval ( $result ['uniqueid'] ) > 0) {
			$this->base_user_info = $result;
			$this->user_id = $result ['uniqueid'];
			$this->userinfo = $this->User->getUserInfoById ( $this->user_id );
		}
	}

	// =============================================================================================================
	/**
	 * 版本接口控制
	 * add by liule1 12.8.2015
	 *
	 * 参考 ：get_message_lt_1_2_0_remap 和 get_message 在版本小于1.2.0时，调用get_message时，自动定位到get_message_lt_1_2_0_remap
	 */
	public function _remap($method, $segment_array) {
		// 登录判断
		$class_name = get_class($this);
		$func_list = get_class_methods($class_name);
		$this->review_state = $review_state = (int)$this->input->get_post('review_state');

		// 有些接口得验证 SEED，用以校验安全性要求高的线性操作
		$seed = $this->input->get_post('seed');
		$this->seed->set_class($class_name);
		$this->seed->set_method($method);
		$this->seed->set_unique_flag($this->device_id);


		if ($this->partner_id) {
			// 客户端
			$version = $this->version;

			// V2.0开始，加入HTTP请求的唯一性验证, BY liule1, 1,29 2016
			if ($version > '2.0') {
				$this->unique_sign_check($class_name, $method);
				// 安全性要求高的，采用线性流的验证码：当前接口验证上一个接口所生成的seed
				if (!$this->seed->check_randcode($seed)) {
					Util::echo_format_return ( _SEED_ERROR_, '', 'seed error', 1 );
				}
			}

			if ($review_state) {
				// 审核中的版本
				$pattern = "/{$method}_review_remap$/";
				foreach ($func_list as $func) {
					if (preg_match($pattern, $func)) {
							$method = $func;
							break;
					}
				}
				$pattern = "/{$method}_(?:((?:lt|gt|eq)_\d+_\d+_\d+)_)+remap$/";	// 末前只做了LT，之后有需求再做吧。
				foreach ($func_list as $func) {
					$matchs = array();
					if (preg_match($pattern, $func, $matchs)) {
						// 对比版本号
						$cond_arr = explode('_', $matchs[1]);
						while($cond_arr) {
							$compare = array_shift($cond_arr);
							$the_version = array();
							$the_version[] = array_shift($cond_arr);
							$the_version[] = array_shift($cond_arr);
							$the_version[] = array_shift($cond_arr);
							$the_version = implode('.', $the_version);

							if ($compare == 'lt' && $version < $the_version) {
								$method = $func;
								break;
							}
						}
					}
				}
			}
		}

		if (!method_exists($this, $method)) {
			show_404();
		}
		//array_pop($segment_array);
		$segment_array || $segment_array = array();
		call_user_func_array(array($this, $method), $segment_array);
	}
	// =============================================================================================================
	public function unique_sign_check($class, $method) {
		// for app that after version V2.0
		$expire = 5 * 60 * 1000;	// 毫秒

		if ($this->partner_id) {
			$class = strtolower($class);
			$method = strtolower($method);
			$this->load->config('unique_sign_whitelist', true);
			$whitelist = $this->config->item('unique_sign_whitelist');
			$sign = $this->input->get_post('sign');
			$userip_sign = Util::getRealIp();

			$timestamp = $this->input->get_post('timestamp');	// 毫秒
			is_numeric($timestamp) || $timestamp = 0;

			if ($sign != 'backend' && (!is_array($whitelist[$class]) || !in_array($method, $whitelist[$class]))) {
				// 后台测试backend， 和白名单里的接口不验证

				if (!$timestamp || SYS_TIME * 1000 - $timestamp >= $expire) {
					// 接口时间超过5分钟，这里的时间戳是经过上次后端公共返回参数时间偏移量(time_offset)来矫正后的
					Util::echo_format_return ( _SIGN_ERROR_, '', 'sign unique error: timestamp expire', 1 );
				}

				$cache_key = "gl_all:" . ENVIRONMENT . ":sign_security:" . $sign . $method . $userip_sign;
				// 唯一性验证
				$add = $this->cache->redis->incr($cache_key, 1);
				$this->cache->redis->expire($cache_key, $expire);

				if ($add > 1) {
					Util::echo_format_return ( _SIGN_ERROR_, '', 'sign unique error: unique', 1 );
				}

			}


		}
	}

	// =============================================================================================================
	/**
	 * 提示页
	 *
	 * 站点提示页展示
	 */
	public function showMessage($type, $data,$back_url=""){
		$this->smarty->assign('message', $data['message']);
		$this->smarty->assign('wait_time', 2000);
		$this->smarty->assign('back_url', $back_url);
		$this->smarty->assign('show_type', $type);
		$this->smarty->display ( 'common/message.tpl');
		die();
	}
}
?>
