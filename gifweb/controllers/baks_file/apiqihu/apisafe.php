<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * API-首页信息操作
 *                 
 */
class Apisafe extends MY_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('follow_model');
		$this->load->model('game_model');
		$this->load->model('recommend_model');
	}

	public function index(){
		header('Content-Type:text/html;charset=utf-8');
		echo "看到这里，说明已经通过安全监测__奇虎";
	}


}