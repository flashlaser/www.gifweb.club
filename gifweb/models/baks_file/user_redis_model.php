<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
/**
 * 用户redis相关操作
 *
 * @author Haibo8
 */
class User_Redis_Model extends MY_Model {
	private $fld_cacheKey;
	private $total_day_pv;
	public function __construct() {
		parent::__construct ();
		
		$this->load->model ( 'User_Model', 'User' );
		$this->load->model ( 'Answer_Model', 'Answer' );
		$this->load->model('question_model', 'Question');
		$this->fld_cacheKey = 'glapp:users:';
		$this->load->driver ( 'cache' );
	}
	
	/**
	 * 缓存用户被回答次数、大神回答次数到Redis,达到配置阀值更新用户是否大神
	 *
	 * @param unknown_type $userInfo        	
	 * @return string
	 */
	public function cache_answer_num($qid, $answer_uid) {
		if (intval($qid) < 1 || intval($answer_uid) < 1)
			return false;

		//获取问题所有者uid
		$q_info = $this->Question->get_info($qid);
		$uid = $q_info['uid'];
		if($uid < 1){
			return false;
		}
		
		//获取用户信息
		$user = $this->User->getUserInfoById($uid);
		$a_user = $this->User->getUserInfoById($answer_uid);

		// 记录问题被回答权重
		$this->recordQuestionHot($q_info, 3, $a_user['rank']);

		// 获取配置信息
		$cfg = $this->User->getCfg('god_cfg');
		if($cfg['god_cfg']['n_num'] < 1 || $cfg['god_cfg']['g_num'] < 1){
			return false;
		}
		
		if($user['rank'] > 0){	// 已是大神不在记录
			return false;
		}

		// 获取配置信息
		$nKey = $this->fld_cacheKey . "answers:normal_".$uid;
		$gKey = $this->fld_cacheKey . "answers:god_".$uid;
		
		// 保存
		if( $a_user['rank'] == 1 ){
			$g_num = $this->cache->redis->incr($gKey, 1);
		}else{
			$n_num = $this->cache->redis->incr($nKey, 1);
		}
		
		// 如果已达到大神级别，更新数据库
		if($n_num >= $cfg['god_cfg']['n_num'] || $g_num >= $cfg['god_cfg']['g_num']){
			$upData['rank'] = 1;
			$this->User->update_user($uid, $upData);
			$this->cache->redis->delete( $gKey );
			$this->cache->redis->delete( $nKey );
		}
	}

	/**
	 * 问题每天热度记录
	 * @param q_info 问题内容或问题qid
	 * @param type 1-浏览，2-关注, 3-回答
	 * @param rank 0-普通用户，1-大神用户
	 * @return string
	 */
	public function recordQuestionHot($q_info, $type, $rank=0){

		if(empty($q_info) || empty($type)){
			return false;
		}
		if(is_array($q_info)){
			$id = $q_info['id'];
			$game_id = $q_info['gid'];
		}else{
			//获取问题所有者uid
			$q_info = intval($q_info);
			$rs = $this->Question->get_info($q_info);
			$id = $rs['qid'];
			$game_id = $rs['gid'];
		}
		
		if($id < 1){
			return false;
		}
		
		// 获取配置信息
		$rs = $this->User->getCfg('question_hot');
		$g_f_num = isset($rs['question_hot']['g_f_num']) ? $rs['question_hot']['g_f_num'] : 1;
		$n_f_num = isset($rs['question_hot']['n_f_num']) ? $rs['question_hot']['n_f_num'] : 1;
		$g_a_num = isset($rs['question_hot']['g_a_num']) ? $rs['question_hot']['g_a_num'] : 1;
		$n_a_num = isset($rs['question_hot']['n_a_num']) ? $rs['question_hot']['n_a_num'] : 1;
		$pv_num = isset($rs['question_hot']['pv_num']) ? $rs['question_hot']['pv_num'] : 1;
		
		// 计算权重值
		if($type == 1){
			$w = $pv_num * 1;
		}elseif($type == 2){
			$n = $rank == 1 ? $g_f_num : $n_f_num;
			$w = $n * 1;
		}elseif($type == 3){
			$n = $rank == 1 ? $g_a_num : $n_a_num;
			$w = $n * 1;
		}else{
			return false;
		}
		
		// 记录
		$day = date("Ymd");
		$redisKey = "glapp:hot_list:question_".$day."_".$game_id;
		$this->cache->redis->zIncrBy($redisKey, $w, $id);
		$this->cache->redis->expireAt($redisKey, time() + 86400 * 3);
	}

	/**
	 * 计算答案权重值
	 *
	 * @param $aid 答案ID
	 * @param $op_rank 操作用户对应rank等级
	 * @param $op_rank 操作用户对应操作类型 1-赞成， 2-反对， 3-收藏
	 */
	public function countAnswerWeights($aid, $op_rank=0, $type){
		
		// 获取答案所属用户信息
		$a_info = $this->Answer->get_info($aid);
		if($a_info['uid']){
			$a_user_info = $this->User->getUserInfoById($a_info['uid']);
		}else{
			return false;
		}
		
		//获取配置信息
		if($a_user_info['rank'] == 1){
			$rs = $this->User->getCfg('answer_g_hot');
			$cfg = $rs['answer_g_hot'];
		}else{
			$rs = $this->User->getCfg('answer_n_hot');
			$cfg = $rs['answer_n_hot'];
		}
		
		$g_up_num = intval($cfg['g_up_num']);
		$g_d_num =	intval($cfg['g_d_num']);
		$g_c_num =	intval($cfg['g_c_num']);
		$n_up_num =	intval($cfg['n_up_num']);
		$n_d_num =	intval($cfg['n_d_num']);
		$n_c_num =	intval($cfg['n_c_num']);
		
		// 计算权重
		$w = false;
		if(intval($op_rank) == 1){
			if($type == 1){
				$w = 1* $g_up_num;
			}elseif($type == 2){
				$w = 1 * $g_d_num;
			}elseif($type == 3){
				$w = 1 * $g_c_num;
			}
		}else{
			if($type == 1){
				$w = 1 * $n_up_num;
			}elseif($type == 2){
				$w = 1 * $n_d_num;
			}elseif($type == 3){
				$w = 1 * $n_c_num;
			}
		}
		
		return $w;
	}

}

?>