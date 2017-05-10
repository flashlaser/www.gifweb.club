<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

/**
 *
 * @name About
 * @desc null
 *
 * @author	 liule1
 * @date 2015年10月19日
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 */
class Download extends CI_Controller {
	private $_h5_config = array(
			'pc' => array(
					'url' => 'http://wan68.com/download',
					'img' => '',
					'img_pos' => 'center',
			),
			'weichat' => array(
					'android_download' => 'http://a.app.qq.com/o/simple.jsp?pkgname=com.sina.sinaraider',
					'ios_download' => 'http://a.app.qq.com/o/simple.jsp?pkgname=com.sina.sinaraider',
					'android_img' => '',
					'ios_img' => '',
					'img_pos' => 'center',
			),
			'other' => array(
					'android_download' => 'http://mg.games.sina.com.cn/97973/glapp/QMGL-channel-0.apk',
					'ios_download' => 'https://itunes.apple.com/app/id1048841352?mt=8',
					'android_img' => '',
					'ios_img' => '',
					'img_pos' => 'center',
			),
			'jump_img' => 'http://www.wan68.com/gl/static/images/clear.png',
			'jump_img_pos' => 'center',
	);
	public function __construct() {
		parent::__construct ();
		$this->load->library('global_func');
		$this->load->driver ( 'cache' );
	}

	public function index() {
		if($this->global_func->isMobile()){
			$this->_index_h5();
		}
		else {
			$this->_index_pc();
		}
	}
	private function _index_pc() {
		$this->smarty->view ( 'download/download.html' );
	}
	private function _index_h5($config = null) {
		$this->smarty->view ( 'download/download_h5.html' );
	}
	// 众多单游戏下载
	public function platform() {
		$this->smarty->view ( 'download/platform.html' );
	}

	/*
	 * 众多单游戏下载
	 * author  huanglong
	 * date    2016-05-24
	 */
	public function platform_data($page) {
	    $this->load->model('Download_model','Download');
		$page < 1 && $page = 1;
		$offsize = ($page - 1) * 9;
	    $data = $this->Download->get_list($offsize,9);
	    $data_info = $this->Download->get_all();
	    
	    $ret =array();
	    foreach ($data as $k=>$v){
	        $ret[$k]['frontPart']['src1'] = gl_img_url($v['bak_img']);
	        $ret[$k]['frontPart']['src2'] = gl_img_url($v['logo']);
	        if($v['mark']=='cqyh'){
	            $ret[$k]['frontPart']['p1'] = '全民攻略For';
	        }else{
	            $ret[$k]['frontPart']['p1'] = '全民手游攻略For';
	        }
	        $ret[$k]['frontPart']['p2'] = $v['name'];
	        $ret[$k]['backPart']['src1'] = gl_img_url($v['qcode_img']);
	        $ret[$k]['backPart']['a1'] = base_url().'download/app_'.$v['mark'].'/1';
	        $ret[$k]['backPart']['a2'] = base_url().'download/app_'.$v['mark'].'/2';
	        $ret[$k]['backPart']['all_num'] = count($data_info);
	    }
	    echo json_encode($ret);exit;
	}
	
	public function app($config = array()) {
		$config = is_array($config) ? $config + $this->_h5_config : $this->_h5_config;
		$this->smarty->assign($config);
		$this->smarty->view ( 'download/download_app.html' );
	}

	public function app_bd() {
		$this->_h5_config['other']['android_download'] = 'http://n.sinaimg.cn/c6abfe21/glapp/SinaRaider-channel-10501-bdtb.apk';
		$this->app();
	}

	/*
	 * 下载中转页
	 * author  huanglong
	 * date    2016-05-24
	 */
	public function app_common($app,$type= 0) {
	    if (in_array("app_$app", get_class_methods(__CLASS__))) {
	        $this->{"app_$app"}($type);
	        return;
	    }
        if($app){
    	    $this->load->model('Download_model','Download');
    	    $data = $this->Download->get_row($app);
            
    	    if($data){
        		$this->_h5_config['weichat']['android_download'] = '';
        		$this->_h5_config['weichat']['ios_download'] = '';
        		$this->_h5_config['weichat']['ios_img'] = '';
        		$this->_h5_config['weichat']['img_pos'] = 'center';
        
        		$this->_h5_config['other']['android_download'] = $data['android_url'];
        		$this->_h5_config['other']['ios_download'] = $data['ios_url'];
        		$this->_h5_config['other']['ios_img'] = '';
        		$this->_h5_config['other']['img_pos'] = 'center';
        
        		$this->_h5_config['pc']['url'] = $this->_h5_config['other']['android_download'];
        		$this->_h5_config['jump_img'] = '/gl/static/images/clear.png';
        		$this->_h5_config['jump_img_pos'] = 'center';
            	if ( $type == 1) {
        			// 定位到ios
        			$this->_h5_config['pc']['url'] = $this->_h5_config['other']['ios_download'];
        			$this->_h5_config['pc']['img'] = $this->_h5_config['other']['img_pos'];
        			$this->_h5_config['pc']['img_pos'] = 'center';
        		} elseif ($type == 2) {
        			// 定位到安卓，直接下载
        			header('Location:' . $this->_h5_config['other']['android_download']);
        			return;
        		}
    	    }  
    		$this->app();
        }
	}

	/**
	 * 下载页
	 *
	 * by huanglong
	 */
	public function down_page($gameId) {
		// 定制 add by liule1 20160718 GO
		if ($gameId == '11683') {
			$this->load->library('global_func');
			if (!$this->global_func->isMobileClient()) {
				header('Location:http://www.97973.com/zt/jmfy.shtml');
				return;
			}
		}
		if ($gameId == '16094') {
			$this->load->library('global_func');
			if (!$this->global_func->isMobileClient()) {
				header('Location:http://www.97973.com/zt/sdgd.shtml');
				return;
			}
		}
		if ($gameId == '15708') {
			$this->load->library('global_func');
			if (!$this->global_func->isMobileClient()) {
				header('Location:http://www.97973.com/zt/sdtx.shtml');
				return;
			}
		}
		// 定制 add by liule1 20160718 END
		
	    $this->load->model('user_model');
	    $this->load->model('game_model');
	    $data = $this->user_model->getCfg($gameId,'gl_app_game_active_img');
	    if($data['status'] !=1){
	        $result['message'] = '页面不存在';
	        $back_url= 'http://www.wan68.com/download/down_page/'.$gameId;
	        $this->showMessage('fail', $result,$back_url);
	    }
	    $data['game_name'] = urldecode($data['game_name']);

	    $data['gl_pc_desc'] = urldecode($data['gl_pc_desc']);

		$data['gl_pc_ios'] = urldecode($data['gl_pc_ios']);

		$data['gl_pc_android'] = urldecode($data['gl_pc_android']);
		foreach($data['gl_pc_about'] as $k=>$v){
			$data['gl_pc_about'][$k]['gl_pc_about_text'] = urldecode($v['gl_pc_about_text']);
			$data['gl_pc_about'][$k]['gl_pc_about_url'] = urldecode($v['gl_pc_about_url']);
		}

		$cms_game_desc = file_get_contents("http://wap.97973.com/glapp/get_game_desc_info.d.html?ids=".$data['ios_cms_id']);
		if(!$cms_game_desc[0]['gameIntro']){
			$cms_game_desc = file_get_contents("http://wap.97973.com/glapp/get_game_desc_info.d.html?ids=".$data['android_cms_id']);
		}
		$cms_game_desc = json_decode($cms_game_desc,true);
		$data['game_desc'] = $cms_game_desc[0]['gameIntro'];

	    $this->smarty->assign('data', $data);
	    //加入PC端 加一判断
	    if($this->global_func->isMobile()){
			$this->smarty->view ( 'download/download_page.tpl' );
		}
		else {
			//var_dump($data);

			//$this->smarty->view ( 'download/download_page.tpl' );
			$this->smarty->view ( 'download_pc/download_page_pc.html' );
		}
	    
	}
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
