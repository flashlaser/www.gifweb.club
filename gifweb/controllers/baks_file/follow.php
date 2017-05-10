<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

/**
 *
 * @name Follow
 * @desc 攻略WAP关注关系控制类
 *
 * @author	 wangbo8
 * @date 2015年12月16日
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 *
 * @property	global_func		$global_func
 * @property	follow_model	$follow_model
 */
class Follow extends MY_Controller {
	public function __construct() {
		parent::__construct ();
		$this->load->model('follow_model');
		$this->load->model('user_model');
	}

	private function _follow($type, $mark_func = 'intval', $success_msg = '关注') {
		$marks = $this->input->get_post ( 'mark', true );

		$uid = $this->user_id;
		$action = (int) $this->input->get_post('action', true);

		try {
			if ($action != 2 && empty($marks) || empty($uid) ) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}

			if ($action == 0 || $action == 1) {
				$status = $action ? -1 : 1;
				$success_msg = $status > 0 ? $success_msg . '成功' : "取消" . $success_msg;

				foreach (explode(',', $marks) as $mark) {
					if (function_exists($mark_func)) {
						$mark = call_user_func($mark_func, $mark);
					}
					if (empty($mark)) continue;
					$s = $this->follow_model->follow($uid, $type, $mark, $status);
				}
			} elseif ($action == 2) {
				$success_msg = "取消全部" . $success_msg;
				// 清空
				$this->follow_model->follow_clean($uid, $type);
			}

			Util::echo_format_return(_SUCCESS_,array(),$success_msg);
			return 1;
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}

	/**
	 * 攻略
	 */
	public function gl_collect() {
		$this->_follow(1, 'strval', '收藏');
	}
	/**
	 * 答案
	 */
	public function answer_collect() {
		$this->_follow(2, 'intval', '收藏');
	}

	/**
	 * 游戏
	 */
	public function game_attention() {
		$this->_follow(3);

		//新增游戏关注缓存清除
		$guid = $this->user_id;
		//分平台处理
		$platform = Util::getBrowse();
		if($platform != 'ios' && $platform != 'android'){
			$platform = 'pc';
		}
		$this->platform = $platform;

		$cache_attentioned_list_key = sha1('game_list1_attentioned_'. ENVIRONMENT .$guid.'_'.$platform.":wap");
		$cache_attentioned_list = $this->cache->redis->delete ( $cache_attentioned_list_key );

		$cache_attentioned_list_key = sha1('game_list1_attentioned_'. ENVIRONMENT .$guid."_ios:wap");
		$cache_attentioned_list = $this->cache->redis->delete ( $cache_attentioned_list_key );
		$cache_attentioned_list_key = sha1('game_list1_attentioned_'. ENVIRONMENT .$guid."_android:wap");
		$cache_attentioned_list = $this->cache->redis->delete ( $cache_attentioned_list_key );
		$cache_attentioned_list_key = sha1('game_list1_attentioned_'. ENVIRONMENT .$guid."_pc:wap");
		$cache_attentioned_list = $this->cache->redis->delete ( $cache_attentioned_list_key );

		$cache_attentioned_list_key = sha1('game_list1_attentioned_'. ENVIRONMENT .$guid."_ios");
		$cache_attentioned_list = $this->cache->redis->delete ( $cache_attentioned_list_key );
		$cache_attentioned_list_key = sha1('game_list1_attentioned_'. ENVIRONMENT .$guid."_android");
		$cache_attentioned_list = $this->cache->redis->delete ( $cache_attentioned_list_key );
	}

	/**
	 * 问题
	 */
	public function question_attention() {
		$this->_follow(4);
	}

	/**
	 * 同步一次游戏关注
	 */
	public function sync_game_attention() {
		$type = 3;
		$uid = $this->user_id;

		try {
			if (empty($uid) ) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}
			$a = $this->follow_model->get_one_data($uid, $type, 0);
			if (!$a) {
				$_GET['action'] = 0;
				$this->_follow($type);
			} else {
				Util::echo_format_return(_SUCCESS_);
			}
			return 1;
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}

}
