<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

/**
 * @name Index
 * @author 庆禄
 * @date 2017-04-24
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 *
 */


class Index extends MY_Controller {
	public function __construct() {
		parent::__construct ();
		$this->load->model('user_model');
		$this->load->model('gifcontent_model');
		$this->load->model('gifcontent_image_model');
		
	}

	public function index() {
		//print_r($_SERVER);
		//获取请求来源；
		$this->platform = Util::getBrowse();
		$page = $this->input->get('page');
		$type = $this->input->get('type');

		$page < 1 && $page = 1;
    	$offsize = ($page - 1) * PAGE_LIST_SIZE;
		$condition['where']['status'] =  1;
		//$condition['where']['type'] = 1;
		$condition['order'] = " create_time desc ";
		if($type!='' && $type!=null){
			$condition['where']['type'] = $type;
		}

		$res = $this->gifcontent_model->pages( $page, PAGE_LIST_SIZE, $condition );

		//$res = $this->gifcontent_model->findData($condition);
		foreach($res  as $k=>$v){
			//$condition_image['where']['type'] = 1;
			$res[$k]['format_date'] = date('m-d',$v['create_time']);
			$res[$k]['create_time'] = date('Y-m-d H点',$v['create_time']);
			$condition_image['where']['mark'] = $v['cid'];
			$condition_image['where']['status'] = 1;
			$res_img = $this->gifcontent_image_model->findData($condition_image);
			$res[$k]['img_url'] = OSS_IMG_PREFIX.$res_img[0]['url'];

			if($v['uid']){
				$res[$k]['user_name'] = '游客';//后期获取用户信息
			}else{
				$res[$k]['user_name'] = '庆禄哥';
			}
		}
		$pages = $this->gifcontent_model->pages;
		$this->smarty->assign('pages',$pages);
        $this->smarty->assign('content_list',$res);

        //print_r($this->gifcontent_type);

	    $this->smarty->view ( 'index.html' );
	}

//详情
	public function info() {
		//获取请求来源；
		$this->platform = Util::getBrowse();
		$id = $this->input->get('id');

		$res = $this->gifcontent_model->loadData($id);

		$res['format_date'] = date('m-d',$res['create_time']);
		$res['create_time'] = date('Y-m-d H点',$res['create_time']);
		$condition_image['where']['mark'] = $res['cid'];
		$condition_image['where']['status'] = 1;
		$res_img = $this->gifcontent_image_model->findData($condition_image);
		$res['img_url'] = OSS_IMG_PREFIX.$res_img[0]['url'];

		if($res['uid']){
			$res['user_name'] = '游客';//后期获取用户信息
		}else{
			$res['user_name'] = '庆禄哥';
		}
        $this->smarty->assign('info',$res);

	    $this->smarty->view ( 'info.html' );
	}


}
