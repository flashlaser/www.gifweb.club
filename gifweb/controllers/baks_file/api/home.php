<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * API-首页信息操作
 *                 
 */
class Home extends MY_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('follow_model');
		$this->load->model('game_model');
		$this->load->model('recommend_model');
	}

	/**
	 * 首页游戏列表
	 *
	 */
	public function game_list()
	{
		$guid	  		= intval ( $this->input->get('guid',true) );
        $platform	  	= $this->platform;
		$expire_time = 60 * 10;
		
		// mc缓存
		$cache_normal_list_key = sha1('game_list1_normal_' . ENVIRONMENT .$platform);
		$cache_normal_list = $this->cache->redis->get ( $cache_normal_list_key );
		$cache_normal_list && $cache_normal_list = json_decode($cache_normal_list, true);
		
		$cache_recommend_list_key = sha1('game_list1_recommend_'  . ENVIRONMENT .$platform);
		$cache_recommend_list = $this->cache->redis->get ( $cache_recommend_list_key );
		$cache_recommend_list && $cache_recommend_list = json_decode($cache_recommend_list, true);
		
		$cache_attentioned_list_key = sha1('game_list1_attentioned_' . ENVIRONMENT .$guid.'_'.$platform);
		$cache_attentioned_list = $this->cache->redis->get ( $cache_attentioned_list_key );
		$cache_attentioned_list && $cache_attentioned_list = json_decode($cache_attentioned_list, true);

		try {
			//游戏列表［区分平台］
			$game_id_arr = array();
			
			if ($cache_normal_list === false) {
				$info = $this->game_model->get_game_list();
				foreach ($info as $v) {
					$game_id_arr[] = $v['id'];
				}
			}
			
			if ($cache_recommend_list === false) {
				$game_recommend = $this->recommend_model->get_recommend_list(1);//推荐游戏
				foreach ($game_recommend as $v) {
					$game_id_arr[] = $v['gid'];
				}
			}
			
			if ($cache_attentioned_list === false) {
				$infoss = $this->follow_model->get_follow_info($guid,3,-1,-1);
				$infoss || $infoss = array();
				foreach ($infoss as $v) {
					$game_id_arr[] = $v['mark'];
				}
			}
			
			if ($game_id_arr) {
				// 缓存中没数据
				$game_id_arr = array_unique($game_id_arr);
				$cms_game_format_info = $this->game_model->get_cms_game_list_info($game_id_arr);
			}
			
			if ($cache_normal_list === false) {
				$normalList = array();
				foreach ($info as $k => $v){
					$cms_game_info = $cms_game_format_info[$v['id']];
					if(empty($cms_game_info['logo'])){
						continue;
					}
					$_arr = array();
					$_arr['absId'] = (string) $v['id'];
					$_arr['abstitle'] = (string) $v['abstitle'];
					$_arr['initialsEng'] = $cms_game_info['proLetters'][0] ? (string) $cms_game_info['proLetters'][0] : '';
					$_arr['absImage'] =$cms_game_info['logo'] ? $cms_game_info['logo'] : '';
					$_arr['attentionCount'] = (int) $info[$k]['attentionCount'];
					$_arr['packageURL'] =$cms_game_info['packageURL'] ? array_filter(explode("\r\n",$cms_game_info['packageURL'])) : array();//用于检测是否安装
				
					$normalList[] = $_arr;
				}
			} else {
				$normalList = $cache_normal_list;
			}
			
			
			if ($cache_recommend_list === false) {
				$recommend = array();
				foreach($game_recommend as $k2 => $v2){
					$games[$k2] = $this->game_model->get_game_row($v2['gid'], $platform);
					$cms_game_info = $cms_game_format_info[$v2['gid']];
					if(empty($cms_game_info['logo'])){
						continue;
					}
					$_arr2 = array();
					$_arr2['absId'] = (string) $v2['gid'];
					$_arr2['abstitle'] = $games[$k2]['abstitle'] ? $games[$k2]['abstitle'] : '';
					$_arr2['initialsEng'] = $cms_game_info['proLetters'][0] ? (string) $cms_game_info['proLetters'][0] : '';
					$_arr2['absImage'] = $cms_game_info['logo'] ? $cms_game_info['logo'] : '';
					$_arr2['attentionCount'] = (int)$games[$k2]['attentionCount'];
					$_arr2['packageURL'] = $cms_game_info['packageURL'] ? array_filter(explode("\r\n", $cms_game_info['packageURL'])) : array();//用于检测是否安装
				
					$recommend[] = $_arr2;
				}
			} else {
				$recommend = $cache_recommend_list;
			}
			
			
			if ($cache_attentioned_list === false) {
				$attentionedList = array();
				foreach($infoss as $k1 => $v1){
					$infoss[$k1] = $this->game_model->get_game_row($v1['mark'],$platform);
					$cms_game_info = $cms_game_format_info[$v1['mark']];
					if(empty($cms_game_info['logo'])){
						continue;
					}
					$_arr1 = array();
					$_arr1['absId'] = (string) $v1['mark'];
					$_arr1['abstitle'] = (string) $infoss[$k1]['abstitle'];
					$_arr1['initialsEng'] = $cms_game_info['proLetters'][0] ? (string) $cms_game_info['proLetters'][0] : '';
					$_arr1['absImage'] =$cms_game_info['logo'] ? $cms_game_info['logo'] : '';
					$_arr1['attentionCount'] = (int) $infoss[$k1]['attentionCount'];
					$_arr1['packageURL'] =$cms_game_info['packageURL'] ? array_filter(explode("\r\n",$cms_game_info['packageURL'])) : array();//用于检测是否安装－－－暂无
				
					$attentionedList[] = $_arr1;
				}
			} else {
				$attentionedList = $cache_attentioned_list;
			}
			
			
			$data['normalList'] =$normalList ? $normalList : array();
			$data['recommendList'] =$recommend ? $recommend : array();
			$data['attentionedList'] =$attentionedList ? $attentionedList : array();
			
			if ($cache_normal_list === false) {
				$this->cache->redis->save ( $cache_normal_list_key, json_encode($data['normalList']), $expire_time );
			}
			if ($cache_recommend_list === false) {
				$this->cache->redis->save ( $cache_recommend_list_key, json_encode($data['recommendList']), $expire_time );
			}
			if ($cache_attentioned_list === false) {
				$this->cache->redis->save ( $cache_attentioned_list_key, json_encode($data['attentionedList']), $expire_time );
			}
			
			Util::echo_format_return(_SUCCESS_, $data);
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}

}

/* End of file home.php */
/* Location: ./application/controllers/api/home.php */
