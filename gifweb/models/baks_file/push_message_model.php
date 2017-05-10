<?php
/**
 * 此类负责写入redis缓存，之后会有crontab每隔一段时间（目前为一分钟）跑一次，插入user_message表中
 * @name Push_message_model
 * @desc null
 *
 * @author	 liule1
 * @date 2015年8月25日
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 */
class Push_message_model extends CI_Model {
	private $_cache_expire = 6000;
	private $_cache_key_pre = '';

	function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":push_message:";
	}

	private function _get_hask_key($type, $flag, $mark) {
		$type = (int) $type;
		$flag = (int) $flag;
		return "{$type}_{$flag}_{$mark}";
	}
	private function _get_time_set_cache_key ($type, $flag, $mark) {
		$hash_key = "{$type}_{$flag}_{$mark}";
		$cache_key = $this->_cache_key_pre . "time_set:$hash_key";
		return $cache_key;
	}
	private function _get_user_timeline_cache_key($uid, $type, $flag, $mark) {
		return $this->_cache_key_pre . "uid_timeline:{$uid}_{$type}_{$flag}_{$mark}";
	}
	/**
	 *
	 * @param $type:	跳转类型
	 *						0：web类型    【跳转web页面】
	 *						1：问题类型    【跳转问题详情】
	 *						2：答案类型    【跳转答案详情】
	 *						3：游戏类型    【跳转游戏详情】
	 *						4：攻略类型    【跳转攻略详情】
	 *						5：打赏类型    【跳转打上列表】
	 *						6：提现类型    【跳转提现明细】
	 *
	 * @param $flag		客户端显示ICON
	 *						0：系统类型
	 *						1：回答类型
	 *						2：评论类型
	 *						3：赞类型
	 *						4：经验

	 * @param $mark
	 * @param $add, 增量，可为负
	 * @param $sub_flag, 脚本逻辑， 目前有 0一般   
	 *                   当flag为4时： 1大神赞答案，所增加经验			2一般用户赞答案所增加经验
	 * @param $sub_memo 其他数据
	 *        			当sub_flag为5时：	保存邀请人的UID
	 *        			当sub_flag 为6时： array(
	 *        								'from_uid'=> 1, // 打赏人的uid
	 *        								'amount' => 123	// 打赏金额，分
	 *        							)
	 */
	public function push($type, $flag, $mark, $add = 1, $sub_flag = 0, $sub_memo = array()) {
		if (empty($mark)) {
			return false;
		}
		$cache_key = $this->_cache_key_pre . "data";
		$hash_key = $this->_get_hask_key($type, $flag, $mark);

		$this->cache->redis->hSet($cache_key, $hash_key, json_encode(array('sub_flag' => $sub_flag, 'sub_memo' => $sub_memo)));	// 哈希表里的 key 可以找出 time set

		$time_set_cache_key = $this->_get_time_set_cache_key($type, $flag, $mark);
		$_data = array(
				'time' => time(),
				'add' => $add
		);
		$this->cache->redis->sAdd($time_set_cache_key, json_encode($_data));	// list 里保存 push 时间

		// 更新过期时间，设的时间很长，只是防止项目不用该REDIS时候，自动清除
		$this->cache->redis->expire($cache_key, $this->_cache_expire);
		$this->cache->redis->expire($time_set_cache_key, $this->_cache_expire);

		return 1;
	}
	public function push_now() {
		
	}
	
	public function user_timeline($uid, $type, $flag, $mark) {
		if (empty($uid) || empty($mark)) {
			return false;
		}
		$cache_key = $this->_get_user_timeline_cache_key($uid, $type, $flag, $mark);
		$this->cache->redis->set($cache_key, time(), $this->_cache_expire);
		return 1;
	}
}
