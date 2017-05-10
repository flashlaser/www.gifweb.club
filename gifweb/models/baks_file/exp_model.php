<?php
/**
 *
 * @name Exp_model
 * @desc null
 *
 * @author	 liule1
 * @date 2015年9月6日
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 * @property	user_model		$user_model
 */
class Exp_model extends MY_Model {
	private $_cache_key_pre = '';
	private $_cache_expire = 600;
	protected $_table = 'gl_exp_cond_log';

	/**
	 *	total_1 写入数据库uid,type,mark
	 *	total_2 mark为空字符串，即不区分mark
	 *
	 *  today_1 写入redis，key为 uid . type . mark . date('d')
	 *  today_2 mark为空字符串，即不区分mark
	 */
	private $_config = array(
			1 => array(		// 首次提问
					'action_name' => '首次提问',
					'exp' => null,
					'check_func' => array(
							'_check_total_2' => array(1)
					),
					'success_func' => array(
							'_add_total_1' => array()
					)
			),
			2 => array(		// 每次回答每天5次，同一问题永久
					'action_name' => '回答',
					'exp' => null,
					'check_func' => array(
							'_check_today_2' => array(5),
							'_check_total_1' => array(1),

					),
					'success_func' => array(
							'_add_today_2' => array(),
							'_add_total_1' => array(),
					)
			),
			3 => array(		// 问题首次上升至热门榜
					'action_name' => '问题上升至热门榜',
					'exp' => null,
					'check_func' => array(
							'_check_total_1' => array(1)
					),
					'success_func' => array(
							'_add_total_1' => array()
					)
			),
			4 => array(		// 答案首次上升至热门榜
					'action_name' => '答案上升至热门榜',
					'exp' => null,
					'check_func' => array(
							'_check_total_1' => array(1)
					),
					'success_func' => array(
							'_add_total_1' => array()
					)
			),
			5 => array(		// 我的回答被大神点赞
					'action_name' => '回答被大神赞',
					'exp' => null,
					'check_func' => array(
							'_check_today_2' => array(20)
					),
					'success_func' => array(
							'_add_today_2' => array()
					)
			),
			6 => array(		// 我的回答被普通用户点赞
					'action_name' => '回答被赞',
					'exp' => null,
					'check_func' => array(
							'_check_today_2' => array(20)
					),
					'success_func' => array(
							'_add_today_2' => array()
					)
			),
	);

	function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":add_exp:";
		$this->load->model('user_model');

		$this->_init();
	}

	private function _init() {
		// init config
		$data = $this->_get_config();
		foreach ($this->_config as $k => $v) {
			$this->_config[$k]['exp'] = isset($data[$k]['get_exps']) && is_numeric($data[$k]['get_exps']) ? (int)$data[$k]['get_exps'] : null;
		}
	}

	private function _get_config() {
		$cache_key = $this->_cache_key_pre . "config";
		$data = $this->cache->redis->get($cache_key);
		$data && $data = json_decode($data, 1);
		if (!$data) {
			$sql = "SELECT config FROM gl_config WHERE parent_action='user' AND action='set_exps' LIMIT 1";
			$rs = $this->db->query_read($sql);
			$row = $rs->row_array();
			$data = empty($row['config']) ? array() : json_decode($row['config'], 1);
			is_array($data) || $data = array();
			PLog::w_ErrorLog("cant find set_exps config or its not json string!!!");
			$this->cache->redis->set($cache_key, json_encode($data), 86400);
		}
		return $data;
	}
	// ---------------------------------------------------------------------------------- //
	// ================================== 检查函数  GO ================================================//
	private function _check_today_1($count, $uid, $type, $mark) {
		// check today
		$cache_key = $this->_cache_key_pre . "today_count:$uid:$type:$mark:" . date('d');
		$c = (int)$this->cache->redis->get($cache_key);

		if ($c >= $count) {
			return false;
		} else {
			return true;
		}
	}
	private function _check_today_2($count, $uid, $type) {
		// check today
		return $this->_check_today_1($count, $uid, $type, '');
	}

	// --------------------------------------------------------------------------------------//
	private function _check_total_1($count, $uid, $type, $mark) {
		// 总体验证
		$cache_key = $this->_cache_key_pre . "total_count:$uid:$type:$mark";
		$c = (int)$this->cache->redis->get($cache_key, 1);
		if ($c < $count) {
			$sql = "SELECT count(*) as c FROM {$this->_table} WHERE uid='$uid' AND type='$type' ";
			if ($mark) {
				$sql .= " AND mark='$mark'";
			}
			$rs = $this->db->query_read($sql);
			$row = $rs->row_array();
			$c = (int)$row['c'];
			if ($c < $count) {
				return true;
			} else {
				$this->cache->redis->set($cache_key, $c, 86400 * 30);
				return false;
			}
		} else {
			return false;
		}

	}

	private function _check_total_2($count, $uid, $type) {
		// 总体验证
		return $this->_check_total_1($count, $uid, $type, '');
	}


	// ================================== 检查函数  END ================================================//
	// ================================== 成功函数  GO ================================================//
	private function _add_today_1( $uid, $type, $mark) {
		// check today
		$cache_key = $this->_cache_key_pre . "today_count:$uid:$type:$mark:" . date('d');
		$add = (int)$this->cache->redis->incr($cache_key, 1);
		if ($add == 1) $this->cache->redis->expire($cache_key, 86400);
	}
	private function _add_today_2($uid, $type) {
		// check today
		return $this->_add_today_1($uid, $type, '');
	}

	// --------------------------------------------------------------------------------------//
	private function _add_total_1($uid, $type, $mark) {
		// 总体
		$insert_data = array(
				'uid' => $uid,
				'type' => $type,
				'mark' => $mark,
				'create_time' => time()
		);
		return $this->db->insert($this->_table, $insert_data);
	}

	// ================================== 成功函数  END ================================================//

	private function _get_user_exp_table_name($uid) {
		return "gl_user_exp_" . (int)($uid % 10);
	}
	/**
	 * 判断成功之后，增加用户经验
	 * @param $uid : int
	 * @param $type: int 1-6，详见 self._config
	 * @param $mark: string 标志
	 */
	public function add_exp($uid, $type, $mark) {
		if (empty($uid)) {
			PLog::w_ErrorLog("uid is empty");
			return false;
		}

		$config = $this->_config[$type];
		if (empty($config) || !$config['exp']) {
			PLog::w_ErrorLog("cant find config or exp is 0, type:{$type}");
			return false;
		}

		// 验证
		$params = func_get_args();
		if (is_array($config['check_func'])) {
			foreach ($config['check_func'] as $func => $p) {

				if (!call_user_func_array(array($this, $func), array_merge($p , $params))) {
					return false;
				}
			}
		}

		// 验证通过后，增加数量
		if (is_array($config['success_func'])) {
			foreach ($config['success_func'] as $func => $p) {
				call_user_func_array(array($this, $func), array_merge($p , $params));
			}
		}

		$time = time();
		// 增加用户经验记录表
		$sql = "INSERT INTO {$this->_get_user_exp_table_name($uid)} (`uid`,`action`,`action_name`,`exp`,`create_time`)
		VALUES('$uid','$type','{$config['action_name']}','{$config['exp']}', '$time')
		";
		$this->db->query_write($sql);

		// 增加经验
		$update_data = array(
				'exps' => array("exps+'{$config['exp']}'", FALSE)
		);
		$this->user_model->update_user($uid, $update_data);


		return $config['exp'];
	}

}
