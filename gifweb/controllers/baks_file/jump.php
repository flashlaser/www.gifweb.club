<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * @name Jump
 * @desc null
 *
 * @copyright (c) 2016 SINA Inc. All rights reserved.
 *
 */
class Jump extends MY_Controller
{
	public function __construct(){
		parent::__construct();
	}

	// IOS 跳转到对应的ituns地址
	public function index($postfix = ''){
		$postfix == 'qmgl' && $postfix = '';

		$this->load->model('version_swift_model');
		$info = $this->version_swift_model->getInfoByPostfix($postfix);
		$href = '';
		if ($info && $info['url']) {
			$href = $info['url'];
		} else {
			$href = base_url();
		}
		header('Location: ' . $href);
	}

}
