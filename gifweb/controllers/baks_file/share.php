<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * @name Share
 * @desc null
 *
 * @author	haibo8
 * @date	2015-09-02
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 *
 */
class Share extends MY_Controller
{
	public function __construct(){
		parent::__construct();
		header("Content-type: text/html; charset=utf8");
		date_default_timezone_set("Asia/Shanghai");   //设置时区
		$this->load->model('Question_model','Question');
		$this->load->model('Question_content_model','Question_content');
		$this->load->model('Answer_model','Answer');
		$this->load->model('Answer_content_model','Answer_content');
		$this->load->model('User_model','User');
		$this->load->model('Qa_image_model','Qa_image');
		$this->load->model('Gl_model','Gl');
		$this->load->model('Qa_model','Qa');
	}

	public function index(){
		$aid		= (intval ( $this->input->get('aid',true) )) ? intval ( $this->input->get('aid',true) ) : 0 ;

		//答案详情get_category_row
		$answer_info = $this->Answer->get_info($aid);

		$question_info = $this->Question->get_info($answer_info['qid'],array(0,1,2));

		if(empty($answer_info) ||  $answer_info['status'] == 2 || empty($question_info) || $question_info['status'] == 2 ){
			$this->smarty->assign('answer_info_empty', 1);
		}else{
			$answer_info['update_time_u'] = date('m-d',$answer_info['update_time']);
			$answer_info['create_time_c'] = date('m-d',$answer_info['create_time']);
			$answer_content_info = $this->Answer_content->get_content($aid);
			$question_content_info = $this->Question_content->get_content($answer_info['qid']);

			$q_content = $this->Qa_image->changeImgStr($question_content_info);
			$a_content = $this->Qa_image->changeImgStr($answer_content_info);
			$answer_info['u_info'] = $this->User->getUserInfoById($answer_info['uid']);
			//答案内容
			$answer_info['a_content'] = $this->Answer_content->get_content($answer_info['aid']);
			$answer_info['a_content'] = str_replace(chr(10),"<br>",$answer_info['a_content']);

			$answer_info['a_img_count'] = $this->Qa_image->get_list_count(2, $answer_info['aid'], 1);

			$answer_info['ctime'] = $this->from_time($answer_info['update_time']);

			$game_info = $this->Gl->get_category_row($question_info['gid'],1);
			$game_names = $game_info['abstitle'];
			$game_name = $this->Qa->convert_content_to_frontend(trim($game_names),6);

			if(mb_strlen($game_names,'utf-8') > 6 ){
				$game_name = $game_name."...";
			}

			$this->smarty->assign('a_info', $answer_info);
			$this->smarty->assign('ac_info', $answer_content_info);
			$this->smarty->assign('game_name', $game_name);
			$this->smarty->assign('qid', $answer_info['qid']);

			$this->smarty->assign('q_content', str_replace(chr(10),"<br>",$q_content['content']));
			$this->smarty->assign('a_content', str_replace(chr(10),"<br>",$a_content['content']));
		}
		$this->smarty->view ( 'share/qustion.tpl' );
	}

	public function detail(){
		$qid		= (intval ( $this->input->get('qid',true) )) ? intval ( $this->input->get('qid',true) ) : 0 ;

		//问题详情
		$question_info = $this->Question->get_info($qid,array(0,1,2));
		if(empty($question_info) ||  $question_info['status'] == 2  ){
			$this->smarty->assign('answer_info_empty', 1);
		}else {
			$question_info['update_time_u'] = date('m-d', $question_info['update_time']);
			$question_info['create_time_c'] = date('m-d', $question_info['create_time']);
			$question_content_info = $this->Question_content->get_content($qid);
			//用户信息
			$u_info = $this->User->getUserInfoById($question_info['uid']);

			$q_content = $this->Qa_image->changeImgStr($question_content_info);

			$game_info = $this->Gl->get_category_row($question_info['gid'], 1);
			$game_names = $game_info['abstitle'];
			$game_name = $this->Qa->convert_content_to_frontend(trim($game_names), 6);

			if(mb_strlen($game_names,'utf-8') > 6 ){
				$game_name = $game_name."...";
			}
			//最新答案列表
			//		$answer_info = $this->get_answer_list($qid,1,10,1);

			$answer_info = $this->Answer->get_list($qid, 0, 10);
			foreach ($answer_info as $k => $v) {
				//用户信息
				$answer_info[$k]['u_info'] = $this->User->getUserInfoById($v['uid']);
				//答案内容
				$answer_info[$k]['a_content'] = $this->Answer_content->get_content($v['aid']);

				$answer_info[$k]['a_content'] = str_replace("[", "<", $answer_info[$k]['a_content']);
				$answer_info[$k]['a_content'] = str_replace("]", ">", $answer_info[$k]['a_content']);
				$answer_info[$k]['a_content'] = str_replace(chr(10),"<br>",$answer_info[$k]['a_content']);

				$answer_info[$k]['a_img_count'] = $this->Qa_image->get_list_count(2, $v['aid'], 1);

				$answer_info[$k]['ctime'] = $this->from_time($v['update_time']);
			}
			//热门答案列表
			$answer_hot_info = $this->Answer->get_hot_list($qid);
			foreach ($answer_hot_info as $k_1 => $v_1) {
				//用户信息
				$answer_hot_info[$k_1]['u_info'] = $this->User->getUserInfoById($v_1['uid']);
				//答案内容
				$answer_hot_info[$k_1]['a_content'] = $this->Answer_content->get_content($v_1['aid']);

				$answer_hot_info[$k_1]['a_content'] = str_replace("[", "<", $answer_hot_info[$k_1]['a_content']);
				$answer_hot_info[$k_1]['a_content'] = str_replace("]", ">", $answer_hot_info[$k_1]['a_content']);
				$answer_hot_info[$k_1]['a_content'] = str_replace(chr(10),"<br>",$answer_hot_info[$k_1]['a_content']);

				$answer_hot_info[$k_1]['a_img_count'] = $this->Qa_image->get_list_count(2, $v_1['aid'], 1);
				$answer_hot_info[$k_1]['ctime'] = $this->from_time($v_1['update_time']);
			}
			$q_content['content'] = str_replace(chr(10),"<br>",$q_content['content']);

			//		echo "<pre>";print_r($answer_hot_info);exit;
			$this->smarty->assign('u_info', $u_info);
			$this->smarty->assign('game_name', $game_name);
			$this->smarty->assign('q_info', $question_info);
			$this->smarty->assign('a_info', $answer_info);
			$this->smarty->assign('a_info_empty', $answer_info ? 1 : 2);
			$this->smarty->assign('ah_info', $answer_hot_info);
			$this->smarty->assign('qc_info', $q_content['content']);
		}
		$this->smarty->view ( 'share/detail.tpl' );
	}

	public function get_answer_list()
	{
		$qid = (intval($this->input->get('qid', true))) ? intval($this->input->get('qid', true)) : 0;
		$page = ( int ) $this->input->get_post ( 'page', true );
		$page_size = ( int ) $this->input->get_post ( 'count', true );

		$page < 1 && $page = 1;
		$offsize = ($page - 1) * $page_size;

		//最新答案列表
		$answer_info = $this->Answer->get_list($qid,$offsize,$page_size);
		foreach($answer_info as $k => $v){
			//用户信息
			$answer_info[$k]['u_info'] = $this->User->getUserInfoById($v['uid']);
			//答案内容
			$answer_info[$k]['a_content'] = $this->Answer_content->get_content($v['aid']);
			$answer_info[$k]['a_content'] = str_replace("[", "<", $answer_info[$k]['a_content']);
			$answer_info[$k]['a_content'] = str_replace("]", ">", $answer_info[$k]['a_content']);
			$answer_info[$k]['a_img_count'] = $this->Qa_image->get_list_count(2, $v['aid'], 1);

			$answer_info[$k]['ctime'] = $this->from_time($v['update_time']);
		}
		if($answer_info){
			$a_info['result'] = 1;
			$a_info['message'] = '成功';
		}else{
			$a_info['result'] = 0;
			$a_info['message'] = '没有数据';
		}
		$offsize = ($page) * $page_size;
		$answer_infos = $this->Answer->get_list($qid,$offsize,$page_size);
		if($answer_infos){
			$a_info['type'] = 1;
		}else{
			$a_info['type'] = 0;
		}
		$a_info['data'] = $answer_info;

		echo  json_encode($a_info);
		exit;
	}
	function format_date($time){
		$t=time()-$time;
		$f=array(
			'31536000'=>'年',
			'2592000'=>'个月',
			'604800'=>'星期',
			'86400'=>'天',
			'3600'=>'小时',
			'60'=>'分钟',
			'1'=>'刚刚'
		);
		foreach ($f as $k=>$v)    {
			if (0 !=$c=floor($t/(int)$k)) {
				if($k == 1){
					return $v;
				}else{
					return $c.$v.'前';
				}
			}
		}
	}
	/*
	 * 精确时间间隔函数
	 * $time 发布时间 如 1356973323
	 * $str 输出格式 如 Y-m-d H:i:s
	 * 半年的秒数为15552000，1年为31104000，此处用半年的时间
	 */
	function from_time($time,$str='m-d'){
		isset($str)?$str:$str='m-d';
		$way = time() - $time;
		$r = '';
		if($way < 60){
			$r = '刚刚';
		}elseif($way >= 60 && $way <3600){
			$r = floor($way/60).'分钟前';
		}elseif($way >=3600 && $way <86400){
			$r = floor($way/3600).'小时前';
		}elseif($way >=86400 && $way <2592000){
			$r = date($str,$time);
		}elseif($way >=2592000 && $way <15552000){
			$r = date($str,$time);
		}else{
			$r = date('Y-m-d H:i:s',$time);
		}
		return $r;
	}
}

