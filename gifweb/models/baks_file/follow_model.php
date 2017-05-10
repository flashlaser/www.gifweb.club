<?php
/**
 *
 * @name follow_model
 * @desc null
 *
 * @author	 liule1
 * @date 2015年7月27日
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 *
 * @property	Global_func		$global_func
 * @property	user_model		$user_model
 */
class Follow_model extends MY_Model {
	private $_cache_key_pre = '';
	private $_cache_expire = 600;
	protected  $_table = 'gl_follow';

	function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":follow6:";
		$this->load->model('user_model');
		$this->load->model('User_redis_model', 'uredis');
	}
	// ---------------------------------------------------------------------------------- //
	/**
	 * 获取一条关注数据
	 * @param unknown $uid
	 * @param unknown $type
	 * @param string $mark
	 */
	public function get_one_data($uid, $type, $mark = '0') {
		$cache_key = $this->_cache_key_pre . 'one:' . "$uid:$type";
		$hash_key = "$mark";

		$data = $this->cache->redis->hGet($cache_key, $hash_key);
		$data && $data = json_decode($data, 1);
		if (!is_array($data)) {
			$data = $this->get_one_data_from_db($uid, $type, $mark);
			$this->cache->redis->hSet($cache_key, $hash_key,json_encode($data));
			$this->cache->redis->expire($cache_key, $this->_cache_expire);
		}

		return $data;
	}
	public function get_one_data_from_db($uid, $type, $mark = '') {
		$conditions = array(
				'where' => array(
						'uid' => $uid,
						'type' => $type,
				),
				'limit' => 1,

		);
		$mark && $conditions['where'] += array('mark' => $mark);
		$sql = $this->find($conditions);
		$rs = $this->db->query_read($sql);
		return $rs ? $rs->row_array() : array();
	}
	// ---------------------------------------------------------------------------------- //
	/**
	 * 判断是否关注
	 * @param unknown $uid
	 * @param unknown $type,    1攻略	2答案	3游戏	4问题
	 * @param unknown $mark
	 * @return	1关注 	0没关注或取消关注
	 */
	public function is_follow($uid,$type,$mark) {
		if (empty($uid) || empty($mark)) {
			return false;
		}
		$data = $this->get_one_data($uid, $type, $mark);
		return ($data && $data['status'] > 0) ? 1 : 0;
	}
	// ---------------------------------------------------------------------------------- //

	public function follow_clean($uid, $type) {
		$uid = $this->global_func->filter_int($uid);
		$type = $this->global_func->filter_int($type);

		$user_follows = $this->get_follow_info($uid, $type, 0, -1) ;

		$status = -1;
		$sql = "UPDATE {$this->_table} SET status=-1 WHERE uid='{$uid}' AND type='$type' AND status>0";
		$this->db->query_write($sql);
		$add = $this->db->affected_rows_write();

		foreach ($user_follows as $v) {
			$this->_aftermath($uid, $type, $v['mark'], 1);
		}
	}
	/*
	**********************
	*** 查询关注，返回总数
	**********************
	*/
	public function getFollowInfo($uid, $type,$start,$count,$max_id = 0){
		$res_info_data = $this->get_follow_info($uid,$type,$start,$count,true);
		$info = $res_info_data['list'];

		// 判断获取时是否数据已有更新
		$flag = false;
		if($info)
		{
			foreach ($info as $_k => $sv) {
				if ($sv['mark'] === $max_id) {
					$flag = $_k;

				}
			}
		}

		$returns = array();
		$returnss = array();
		if ($flag) {
			if($info)
			{
				foreach ($info as $_k => $_v) {
					if ($_k > $flag ) {
						array_push($returns, $_v);
					}
				}
			}

			$infos = $this->get_follow_info($uid,$type,$start,$count+1+$flag);
			if($infos['list'])
			{
				foreach ($infos['list'] as $_k1 => $_v) {
					if ($_k1 > $flag) {
						array_push($returnss, $_v);
					}
				}
			}

			$returns = $returnss;
		} else {
			$returns = $info;
		}
		//-->处理 如果max_id出现相同的 那么从下一页抓取最新的补齐   end --------//

		$list['list'] = $returns;
		$list['total_rows'] =  $res_info_data['counts'];

		return $list;
	}



	private function _aftermath($uid, $type, $mark = '', $add = 1) {
		if (!$add) {
			return ;
		}
		// delete cache
		if ($mark) {
			$cache_key = $this->_cache_key_pre . 'one:' . "$uid:$type";
			$this->cache->redis->delete($cache_key);
		}
		$cache_key = $this->_cache_key_pre . 'one:' . "$uid:$type:0";
		$this->cache->redis->delete($cache_key);

		$cache_key = $this->_cache_key_pre . 'get_follow_info:' . $uid;
		$this->cache->redis->delete($cache_key);


		// 关注数、收藏数等操作
		if ($type == 1) {
			//攻略
			$this->load->model('gl_model');
			$this->load->model('game_model');
			$this->gl_model->_aftermath($mark);
			$this->game_model->_aftermath($mark);
		} elseif ($type == 2) {
			// 答案
			$this->load->model('answer_model');
			$this->answer_model->add_follow_count($mark, $uid, $add);
		} elseif ($type == 3) {
			// 游戏
			$this->load->model('gl_model');
			$this->gl_model->update_attention_count($mark, $add);

			//兼容svn控制器
			$this->cache->redis->delete(sha1('game_list1_attentioned_'. ENVIRONMENT.$uid.'_'.$this->platform));

		} elseif ($type == 4) {
			// 问题
			$this->load->model('question_model');
			$this->question_model->add_follow_count($mark, $add);
		}

		return 1;
	}

	/**
	 *
	 * @param unknown $uid
	 * @param unknown $type,    1攻略	2答案	3游戏	4问题
	 * @param unknown $mark
	 * @param string $status  不能为0， 1为关注，-1不关注
	 */
	public function follow($uid, $type, $mark, $status) {
		if (empty($uid) || !is_numeric($type) || empty($mark) ) {
			return false;
		}

		$follow_info = $this->get_one_data($uid, $type, $mark);
		if (!empty($follow_info) && $follow_info['status'] == $status) {
			// 状态无任何改变
			return false;
		}

		$weight_level = 1;	// 关注用户的权重：0一般用户， 1大神
		$user_info = $this->user_model->getUserInfoById($uid);
		if (empty($user_info)) {
			return false;
		}
		$weight_level = $user_info['rank'] ? 1 : 0;
		$affected = $this->_follow_to_db($uid, $type, $mark, $weight_level, $this->platform, $status);

		// 记录关注问题权重计算
		if($type == 4 && $status == 1){
			$this->uredis->recordQuestionHot($mark, 2, $weight_level);
		}

		// 联动影响
		$add = $status > 0 ? 1 : -1;
		$this->_aftermath($uid, $type, $mark, $add);
		return 1;
	}
	private function _follow_to_db($uid, $type, $mark, $weight_level, $platform, $status = null) {
		if (empty($uid) || empty($mark) || empty($status)) {
			return 0;
		}

		$time = time();
		$sql = "INSERT INTO {$this->_table}(uid,mark,type,status,weight_level,partner_id,platform,create_time,update_time) VALUES('$uid','$mark','$type','$status', '$weight_level' , '{$this->partner_id}', '$platform', '$time','$time')
				ON DUPLICATE KEY
				UPDATE weight_level='$weight_level',update_time='$time',status=$status,partner_id='{$this->partner_id}',platform='$platform'
		";
		return $this->db->query_write($sql);
	}
	// ---------------------------------------------------------------------------------- //




	/**
	 *
	 * 首页 查询用户关注游戏列表
	 */
	public function get_follow_info($guid,$type,$start,$limit,$row_counts = false)
	{
		if (empty($guid) || !is_numeric($type) ) {
			return false;
		}
		$cache_key = $this->_cache_key_pre . 'get_follow_info:'.$guid;
		$hash_key = "normal:$type:$start:$limit";
		$data = $this->cache->redis->hGet($cache_key, $hash_key);
		$data && $data = json_decode($data, 1);

		if (!is_array($data) || (count($data) == 0 && $row_counts) ) {
			$sql_game['where']['uid']= array('eq',intval($guid));
			$sql_game['where']['type']= array('eq',intval($type));
			$sql_game['where']['status']= array('eq',1);
			$type == 3 && $sql_game['where']['mark']= array('<>',2031);
			$sql_game['start'] = intval($start);
			$sql_game['limit'] = intval($limit);
			$sql_game['order'] = ' update_time desc ' ;
			$sql = $this->find($sql_game);
			$rs = $this->db->query_read($sql);
			$data = $rs->result_array();
			$this->cache->redis->hSet($cache_key, $hash_key, json_encode($data));
			$this->cache->redis->expire($cache_key, $this->_cache_expire);
		}

		//update by 宋庆禄 pc分页显示 返回总条数
		if($row_counts){
			// 计算总数
			$where = $type == 3 ? " and mark <>2031 " : " ";
			$cnt_sql = "select count(*) as counts from ".$this->_table.
			" where uid='".intval($guid)."' and type=".intval($type)." and status=1 ".$where;
			$rs_count = $this->db->query_read($cnt_sql);
			$res_count_data = $rs_count->row_array();
			$list['counts'] = $res_count_data['counts'];
			$list['list'] = $data;
			return $list;
		}

		return $data;
	}


}
