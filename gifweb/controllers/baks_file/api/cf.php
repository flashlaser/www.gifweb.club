<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
/**
 *
 * @name Config
 * @desc null
 *
 * @author	 liule1
 * @date 2015年9月2日
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 */
class Cf extends MY_Controller {
	protected $sys_type_arr = array(1=>'ios',2=>'android');
	public function __construct() {
		parent::__construct ();
	}


	public function version() {
		try {
			if (empty($this->platform) ) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}
			//$this->load->config('app_config');
			//$config = $this->config->item('app_config');
			$config = $this->echo_config_arr();
			$data = $config['version'][$this->platform][$this->partner_id];

			if (empty($data)) {
				throw new Exception('platform error', _PARAMS_ERROR_);
			}

			Util::echo_format_return(_SUCCESS_, $data);
			return 1;
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}

	public function swift() {
		$version = $this->input->get_post('version');
		$cid = (int)$this->input->get_post('cid');
		try {
			//$this->load->config('app_config');
			//$config = $this->config->item('app_config');
			$config = $this->echo_config_arr();
			$data = $config['swift'][$this->platform][$this->partner_id][$cid][$version];

			if (empty($data)) {
				$data = array ("download" => 1,"review" => 1,"rateMe" => 1, 'recommend' => 1, 'gift' => 1);
			}else{
				//$data['gift'] = $config['swift'][$this->platform][$this->partner_id]['package_switch'][$cid][$version]['package_show'] ? $config['swift'][$this->platform][$this->partner_id]['package_switch'][$cid][$version]['package_show'] : 0;
			}

			if($data['download'] == 1 && $data['review'] == 1 && $data['rateMe'] == 1 && $data['recommend'] == 1){
				$data['gift'] = 1;
			}else{
				$data['gift'] = 0;
			}

			Util::echo_format_return(_SUCCESS_, $data);
			return 1;
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}

	public function setting() {
		$data = array(
			'withdrawcash' => USER_DRAWCASH,
			'cashnote' => USER_DRAWCASH_NOTE ,
			'ranMinDonate' => REWARD_RAND_MIN_MONEY / 100,
			'ranMaxDonate' => REWARD_RAND_MAX_MONEY / 100,
			'maxdonate' => REWARD_MAX_MONEY / 100,
			'donateOverRange' => REWARD_MIN_MONEY_NEED_GESTURE / 100,
			'minwithdraw' => DRAW_MIN_MONEY / 100,
			'maxwithdraw' => DRAW_MAX_MONEY / 100,
			'serviceQQ' => '3064337745',
			'serviceQQgroup' => '560619698',
			// 'tuhaoUrl' => 'http://www.wan68.com/areward/areward_list/1',
			'tuhaoUrl' => 'http://www.wan68.com/hd/jtlq3',
			'qCodeIntroUrl' => 'http://www.wan68.com/areward/qrcode',
			'rewardIntroduction' => 'http://www.wan68.com/areward/introduce',
			'donateRankImgUrl' => 'http://n.sinaimg.cn/games/glapp/PaiXingBang@3x.png',
		);
		Util::echo_format_return(_SUCCESS_, $data);
	}

	public function boot_ad() {
		$game_id = (int) $this->input->get_post('gameId');
		$width = (int) $this->input->get_post('width');
		$height = (int) $this->input->get_post('height');

		try {
			if (empty($width) || empty($height)) {
				throw new Exception('params error', _PARAMS_ERROR_);
			}

			$this->load->model('starting_up_ad_model');
			$ad_data = $this->starting_up_ad_model->get_ad($game_id, $width / $height);

			$data = array(
				'open' => 0,
				'adtitle'  => '',
				'imgUrl' => '',
				'showtime' => 0,
				'begintime' => date('Y-m-d H:i:s', SYS_TIME),
				'endtime' => date('Y-m-d H:i:s', SYS_TIME),
				'fullScreen' => 1,
				'actionUrl' => '',
			);

			if ($ad_data['image']) {
				$data['open'] = 1;
				$data['adtitle'] = $ad_data['title'];
				$data['imgUrl'] = gl_img_url($ad_data['image']['img_url']);
				$data['showtime'] = (int)$ad_data['expose_time'];
				$data['begintime'] = date('Y-m-d H:i:s', $ad_data['start_put_time']);
				$data['endtime'] = date('Y-m-d H:i:s', $ad_data['end_put_time']);
				$data['actionUrl'] = $ad_data['jump_url'];
			}

			Util::echo_format_return(_SUCCESS_, array($data));
			return 1;
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}

	private function echo_config_arr()
	{
		//
		$config ['app_config'] = array();
		$sql = "SELECT * FROM `gl_version_swift_config` ";
		$res_data = $this->common_model->get_data_by_sql($sql);

		if($res_data)
		{
			foreach($res_data as $k=>$v)
			{
				$config ['app_config']['version'][$this->sys_type_arr[$v['sys_type']]][$v['partner_id']] = array(
						'version_id' => $v['version_id'],
						'version' => $v['version'],
						'url' => $v['url'],
						'version_info' => $v['version_info']);
				$res_json_decode_cfg = json_decode($v['cfg_jsoncode'],true);
				$config ['app_config']['swift'][$this->sys_type_arr[$v['sys_type']]][$v['partner_id']] =$res_json_decode_cfg;
				/*
				$res_package_switch = json_decode($v['package_switch'],true);
				$config ['app_config']['swift'][$this->sys_type_arr[$v['sys_type']]][$v['partner_id']]['package_switch'] = $res_package_switch['package_key'];
				*/
			}
		}

		return $config['app_config'];

		//echo json_encode($config);
		//print_r($config);
	}
	public function print_config_test()
	{
		$test = $this->echo_config_arr();
		print_r($test);
	}


	// ==== 审核中 ===== //\
	public function boot_ad_review_remap() {
		$game_id = (int) $this->input->get_post('gameId');
		$width = (int) $this->input->get_post('width');
		$height = (int) $this->input->get_post('height');

		try {
			if (empty($width) || empty($height)) {
				throw new Exception('params error', _PARAMS_ERROR_);
			}
			Util::echo_format_return(_SUCCESS_, array());
			return 1;
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}
}
