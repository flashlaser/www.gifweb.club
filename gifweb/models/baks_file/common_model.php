<?php
/**
 * 商城所用一般MODEL
 * @author liule1
 *
 * @property	cache_redis		$cache_redis
 */
class Common_model extends MY_Model {
	private $_cache_key_pre = '';
	private $_cache_expire = 600;
	private $_trans_flag = false;

	function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:";
		$this->load->library("global_func");
	}
	public function get_data_by_sql($sql) {
		$res = $this->db->query_read ( $sql );

		if (empty ( $res )) {
			return array ();
		} else {
			return $res->result_array ();
		}
	}
	public function get_one_data_by_sql($sql) {
		$res = $this->db->query_read ( $sql );
		if (empty ( $res )) {
			return array ();
		} else {
			return $res->row_array ();
		}
	}
	public function execute_by_sql($sql) {
		$return = $this->db->query_write ( $sql );
		if (stripos ( $sql, 'insert' ) !== false) {
			$return = $this->db->insert_id ();
		}
		return $return;
	}
	public function insert_id() {
		if ($this->db->conn_write) {
			return @mysql_insert_id ( $this->db->conn_write );
		} else {
			return @mysql_insert_id ( $this->db->conn_id );
		}
	}

	/**
	 * 返回上条SQL的影响行数
	 */
	public function get_affected_row_count() {
		// 		$sql = "SELECT ROW_COUNT() as c";
		// 		if ($this->db->conn_write) {
		// 			$rs = $this->db->query_write($sql);
		// 		} else {
		// 			$rs = $this->db->query_read($sql);
		// 		}
		// P.S. PHP5.5.0之前可以使用!!
		if ($this->db->conn_write) {
			$r = mysql_affected_rows($this->db->conn_write);
		} else {
			$r = mysql_affected_rows($this->db->conn_id);
		}

		return $r;
	}
	public function trans_begin () {
		if ($this->_trans_flag) {
			$this->trans_commit();
		}

		$sql = "start transaction";
		$this->execute_by_sql($sql);
		$sql = "SET autocommit=0";
		$this->execute_by_sql($sql);

		$this->_trans_flag = true;
	}
	public function trans_commit() {
		if (!$this->_trans_flag) {
			return;
		}
		$sql = "commit";
		$this->execute_by_sql($sql);
		$sql = "SET autocommit=1";
		$this->execute_by_sql($sql);
		$this->_trans_flag = false;
	}
	public function trans_rollback() {
		if (!$this->_trans_flag) {
			return;
		}
		$sql = "rollback";
		$this->execute_by_sql($sql);
		$sql = "SET autocommit=1";
		$this->execute_by_sql($sql);
		$this->_trans_flag = false;
	}

	// ----------------------------------------------------------------------------------------------------------//

		/**
     * 发送短信接口
	 * 新浪通道
     */
    public function sendPhoneMsg($phone, $msg){
    	// 手机号码验证
		if(!preg_match("/1[34578]{1}\d{9}$/",$phone)){
			return false;
		}

		// 短信内容
		if(empty($msg)){
			return false;
		}

		// 使用渠道
		$sms_cfg = intval( $this->cache->redis->get("glapp:config_set:sms_cfg") );
		if($sms_cfg == 1){
			$flag = $this->send_msg_chuanglan($phone, $msg);
		}else{
			$flag = $this->send_msg_sina($phone, $msg);
		}

		return $flag;
    }

	/**
     * 发送短信接口
	 * 新浪通道
     */
    private function send_msg_sina($phone, $msg){
    	// 手机号码验证
		if(!preg_match("/1[34578]{1}\d{9}$/",$phone)){
			return false;
		}

		// 短信内容
		if(empty($msg)){
			return false;
		}
    	PLog::w_DebugLog("sendPhoneMsg Sina Request:".$phone." ".$msg." ".$this->global_func->get_remote_ip());
    	$this->load->library('HttpRequestCommon',null,'http');
		$this->http->setRequest ( 'http://qxt.intra.mobile.sina.cn/cgi-bin/qxt/sendSMS.cgi', 'POST' );
    	$params = array(
    			'msg' => mb_convert_encoding($msg, 'gb2312', 'utf-8'),
    			'usernumber' => $phone,
    			'count' => 1,
    			'from' => '90423',
    			'longnum' => '1065750241300048',
    			'ext' => -1,
    	);
    	$this->http->setPostData ( $params );
		$result = $this->http->send ();
		$res = null;
		(is_array($result)&&$result['body'])&& $res = $result['body'];
    	PLog::w_DebugLog("sendPhoneMsg Sina Response:".serialize($result));

		return $res > 0 ? true : false;
    }

	/**
     * 发送短信接口
	 *
	 * 创蓝科技通道
     */
    private function send_msg_chuanglan($phone, $msg){
		$this->load->library("ChuanglanSmsApi", "sms");
    	// 手机号码验证
		if(!preg_match("/1[34578]{1}\d{9}$/",$phone)){
			return false;
		}

		// 短信内容
		if(empty($msg)){
			return false;
		}

    	PLog::w_DebugLog("sendPhoneMsg Chuanglan Request:".$phone." ".$msg." ".$this->global_func->get_remote_ip());
		//require_once APPPATH . 'libraries/sms/ChuanglanSmsApi.php';
		$this->sms  = new ChuanglanSmsApi();
		$result = $this->sms->sendSMS($phone, $msg,'true');

		$result = $this->sms->execResult($result);
		if($result[1]==0){
			$flag = true;
		}else{
			$flag = false;
		}

    	PLog::w_DebugLog("sendPhoneMsg Chuanglan Response:".serialize($result));

		return $flag;
    }

	/**
     *随机数生成
     */
	public function get_code($length=32,$mode=0)//获取随机验证码函数
	{
			switch ($mode)
			{
					case '1':
							$str='0123456789';
							break;
					case '2':
							$str='abcdefghijklmnopqrstuvwxyz';
							break;
					case '3':
							$str='ABCDEFGHIJKLMNOPQRSTUVWXYZ';
							break;
					case '4':
							$str='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
							break;
					case '5':
							$str='ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
							break;
					case '6':
							$str='abcdefghijklmnopqrstuvwxyz1234567890';
							break;
					default:
							$str='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
							break;
			}
			$checkstr='';
			$len=strlen($str)-1;
			for ($i=0;$i<$length;$i++)
			{
					//$num=rand(0,$len);//产生一个0到$len之间的随机数
					$num=mt_rand(0,$len);//产生一个0到$len之间的随机数
					$checkstr.=$str[$num];


			}

			return $checkstr;
	}

	// ------------------------------------------------------------------------------------------------------------//
	/**
	 * 过滤内容
	 * 	1 剔除手机表情
	 * 	2 HTMLSPECIALCHARS
	 * 	3 非法反动字符用*代替
	 */
	public function filter_content($content) {
		$content = preg_replace('/[\xf0-\xf7].{3}/', '', $content);	// 剔除手机表情
		$content = htmlspecialchars($content);

		$api = "http://10.24.192.87:9200/gl_app_index/_analyze?analyzer=ik&pretty=true";
		if (ENVIRONMENT == 'testing') {
			$api = "http://101.201.40.71:9200/gl_app_index/_analyze?analyzer=ik&pretty=true";
		}
		$d = $this->global_func->curl_post($api, $content);

		$d && $d = json_decode($d, true);
		if (!empty($d['tokens'])) {
			$this->load->config('ban_word', true);
			$ban_word = $this->config->item('ban_word');
			foreach ($d['tokens'] as $v) {
				foreach ($ban_word as $bv) {
					if ($v['token'] == $bv) {
						// 敏感词
						$l = $v['end_offset'] - $v['start_offset'];
						$content = mb_substr($content, 0, $v['start_offset']) . str_pad('', $l, '*') . mb_substr($content, $v['end_offset']);
					}
				}
			}
		} else {
			$this->load->config('ban_word_pattern', true);
			$pattern = $this->config->item('ban_word_pattern');
			$content = preg_replace_callback($pattern, array($this, '_filter_content_replace'), $content);
		}

		return $content;
	}
	private function _filter_banword($val) {
		var_dump($val);
		var_dump(preg_quote($val));
		exit;
		return preg_quote($val);
	}
	private function _filter_content_replace($match) {
		$return = '';
		$l = mb_strlen($match[0]);
		for ($i = 0; $i < $l; $i++) {
			$return .= "*";
		}

		return $return;
	}
	// -------------------------------------------------------------------------------------------------------------------//
	public function is_ban_user() {
		if (!$this->userinfo) {
			return -1;
		}
		return $this->userinfo['status'] ? 1 : 0;
	}


	// ---------------------------------------------- REDIS 防刷  GO -------------------------------------------------//
	// 开始，不正常直接跳转
	public function defence_repeat_hanle_go($uid, $key, $expire_time = 1) {
		$r = $this->defence_repeat_check_go($uid, $key, $expire_time);
		if (!$r) {
			show_404();
			exit();
		}
		return $r;
	}
	// 结束，不正常直接跳转
	public function defence_repeat_hanle_end($uid, $key, $expire_time = 1) {
		$r = $this->defence_repeat_check_end($uid, $key, $expire_time);
		if (!$r) {
			show_404();
			exit();
		}
		return $r;
	}


	// 开始,
	public function defence_repeat_check_go($uid, $key, $expire_time = 1) {
		$step = 1;
		return $this->defence_repeat_check_step($uid, $key, $step, $expire_time);
	}
	// 结束，返回1正常， 0不正常
	public function defence_repeat_check_end($uid, $key, $expire_time = 1) {
		$step = 2;
		return $this->defence_repeat_check_step($uid, $key, $step, $expire_time);
	}


	// 结束，不正常直接跳转
	public function defence_repeat_hanle_step($uid, $key, $step, $expire_time = 1) {
		$r = $this->defence_repeat_check_step($uid, $key, $step, $expire_time);
		if (!$r) {
			show_404();
			exit();
		}
		return $r;
	}
	public function defence_repeat_check_step($uid, $key, $step, $expire_time = 1) {
		$r = $this->_defence_repeat_incr($uid, $key, $expire_time);
		return $r > $step ? 0 : 1;
	}
	private function _defence_repeat_incr($uid, $key, $expire_time) {
		$cache_key = $this->_cache_key_pre . "repeat:$uid:$key";
		$return = $this->cache->redis->incr($cache_key);
		$this->cache->redis->expire($cache_key, $expire_time);
		return $return;
	}

	// ---------------------------------------------- REDIS 防刷  END -------------------------------------------------//

	//获取分页数据
	public function get_page_data($data)
	{
		$data['show_pages'] = empty($data['show_pages']) ? 5 : $data['show_pages'];	// 最多展示多少页
		$data['url_prefix'] = empty($data['url_prefix']) ? $data['controllers_name'] : $data['url_prefix'];	// URL前缀 . page
		$data['page'] = (int)$data['page'] < 1 ? 1 : (int)$data['page']; // 当前页数
		$data['total_rows'] =  (int)$data['total_rows'];	// 总记录数
		$data['page_size'] = (int) $data['page_size'] < 1 ? 10 : (int)$data['page_size'];	// 每页多少

		// ------------------------------------------------------------------//
		$data['total_pages'] = ceil($data['total_rows'] / $data['page_size']);

		if ($data['total_pages'] >= 1)
		{
			$data['page_begin'] = $data['page'] - floor($data['show_pages'] / 2);
			$data['r_offset'] = $data['page_begin'] >= 1 ? 0 : -$data['page_begin'] + 1;

			$data['page_end'] = $data['page'] + floor($data['show_pages'] / 2);
			$data['l_offset'] = $data['page_end'] > $data['total_pages'] ? $data['page_end'] - $data['total_pages'] : 0;

			$data['page_begin'] -= $data['l_offset'];
			$data['page_begin'] < 1 && $data['page_begin'] = 1;

			$data['page_end'] += $data['r_offset'];
			$data['page_end'] > $data['total_pages'] && $data['page_end'] = $data['total_pages'];
		}

		return $data;
	}

	/**
	 * 分页函数
	 *
	 * @param $num 信息总数
	 * @param $curr_page 当前分页
	 * @param $perpage 每页显示数
	 * @param $urlrule URL规则
	 * @param $array 需要传递的数组，用于增加额外的方法
	 * @return 分页
	 */
	public static function pages($num, $curr_page, $perpage = 20, $setpages = 6, $urlrule = '', $array = array())
	{
		$data = '';
		if ($num > $perpage) {
			$page = $setpages + 1;
			$offset = ceil($setpages / 2 - 1);
			$pages = ceil($num / $perpage);

			$from = $curr_page - $offset;
			$to = $curr_page + $offset;
			$more = 0;
			if ($page >= $pages) {
				$from = 2;
				$to = $pages - 1;
			} else {
				if ($from <= 1) {
					$to = $page - 1;
					$from = 2;
				} elseif ($to >= $pages) {
					$from = $pages - ($page - 2);
					$to = $pages - 1;
				}
				$more = 1;
			}
			$data['pages']	= $pages;
			$data['from']	= $from;
			$data['to']		= $to;
			$data['more']	= $more;
			$data['curr_page']	=	$curr_page;
			$data['pagesize'] = $perpage;
			$data['total_nums'] = $num;

			// 构造分页中间显示部分
			for ($i = $from; $i <= $to; $i++) {
				if ($i != $curr_page) {
					$arr[$i] = 0;
				} else {
					$arr[$i] = 1;
				}

			}
			$data['middle_page'] = $arr;
		}else{
            $data = array('total_nums'=>$num);
        }
		return $data;
	}

}
