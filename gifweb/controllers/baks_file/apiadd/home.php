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
		$cache_normal_list_key = sha1('game_list1_normal_xuanyaodang' . ENVIRONMENT .$platform);
		$cache_normal_list = $this->cache->redis->get($cache_normal_list_key);
		$cache_normal_list && $cache_normal_list = json_decode($cache_normal_list, true);

		try {
			//游戏列表［区分平台］
			$game_id_arr = array();
			
			if ($cache_normal_list === false) {
				$info = $this->game_model->get_game_list();
				foreach ($info as $v) {
					$game_id_arr[] = $v['id'];
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

					$normalList[] = $_arr;
				}
			} else {
				$normalList = $cache_normal_list;
			}
			
			$data['normalList'] =$normalList ? $normalList : array();

			if ($cache_normal_list === false) {
				$this->cache->redis->set($cache_normal_list_key, json_encode($data['normalList']), $expire_time);
			}

			Util::echo_format_return(_SUCCESS_, $data);
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}

	//通过游戏cms_id,获取游戏的gl id
	public function get_glid_with_cmsid_list(){
		//$cmsid	  		= intval ( $this->input->get('cmsid',true) );
		$platform	  	= $this->input->get('platform');
		$expire_time    = 60 * 60 * 24;  //memcache 过期时间，定位一天

		// mc缓存
		$get_glid_with_cmsid_list_key = sha1('get_glid_with_cmsid_list' . ENVIRONMENT .$platform);
		$data = $this->cache->redis->get($get_glid_with_cmsid_list_key);
		$data && $data = json_decode($data, true);

		try {
			if(!$cmsid){
				//exit('no cmsid');
			}

			if($data === false){
				$data = $this->game_model->get_game_cms_id_list_all();
				$this->cache->redis->set($get_glid_with_cmsid_list_key, json_encode($data), $expire_time);
			}

			Util::echo_format_return(_SUCCESS_, $data);
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}



}

/* End of file home.php */
/* Location: ./application/controllers/api/home.php */
