<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 * @author haibo8, <haibo8@staff.sina.com.cn>
 * @version   $Id: user.php 2015-12-10 14:52:27 haibo8 $
 * @copyright (c) 2015 Sina Game Team.
 */
class User extends MY_Controller
{
	private $_cache_key_pre = '';
	private $limits = 3;
	private $platforms = 'wap';
	public function __construct()
	{
		parent::__construct();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT;
		/*No Cache*/
		$this->output->set_header("Cache-Control: no-cache, must-revalidate");
		$this->output->set_header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
		$this->output->set_header("Pragma: no-cache");
		$this->output->set_header("Content-type: text/html; charset=utf-8");

		$this->load->model('Common_model','Comm');
		$this->load->model('game_model');
		$this->load->model('follow_model','follow');
		$this->load->model('Article_model','Article');
		$this->load->model('Question_model','Question');
		$this->load->model('Question_content_model','Question_content');
		$this->load->model('Answer_model','Answer');
		$this->load->model('Answer_content_model','Answer_content');
		$this->load->model('Qa_model','Qa');
	}

	/**
	 * 登录页
	 *
	 */
	public function login(){
		if($this->user_id){
			header("Location: /");
		}
		$back_url = trim($this->input->get("backUrl",true));
		$this->smarty->assign('back_url',$back_url);

		$this->smarty->assign('back_url_64', bin2hex($back_url));

		$this->smarty->view ( 'user/login.tpl' );
	}

	/**
	 * 退出登录
	 *
	 */
	public function logout(){
		$back_url = trim($this->input->get("backUrl",true));
		$back_url = empty($back_url) ? "/" : $back_url;

		// 删除登录cookies
		$this->User->delUserCookies();
		header("Location: ".$back_url);
	}

	/**
	 * 登录处理
	 *
	 */
	public function signin(){
		if($this->user_id){
			header("Location: /");
		}

		$this->load->library ( 'SSOServer/SSOServer','ssosever' );

		$phone = trim($this->input->post("phone",true));
		$code  = intval($this->input->post("password",true));
		$back_url = trim($this->input->post("back_url",true));
		$back_url = empty($back_url) ? "/" : $back_url;

		// 手机号码验证
		if(!preg_match("/1[34578]{1}\d{9}$/",$phone)){
			$result['message'] = '手机号格式有误';
			$this->showMessage('fail', $result);
		}

		// 判断是否为暴力提交
		$errorKey = "glapp:users:login_error_num:".$phone;
		$errorForbid = "glapp:users:login_error:".$phone;
		if( $this->cache->redis->get($errorForbid) ){
			$result['message'] = '您登录过于频繁,请休息片刻再试';
			$this->showMessage('fail', $result);
		}

		// 验证码验证
		// $redisKey = 'glapp:users:web_login_code_'.$phone;
		// $ori_code = $this->cache->redis->get($redisKey);

		$rs = $this->User->verUser($phone, $code, 1);

		if(!$rs){
				$result['message'] = '验证码有误';
				$this->showMessage('fail', $result);
		}

		/* 获取用户UID */
		$uid = $this->User->getUidByOpenid($phone, 1);

		if($uid > 0){
			$userinfo = $this->User->getUserInfoById( $uid );
			// 用户已被删除，清理缓存信息
			if(intval($userinfo['uid']) < 1){
				$result['message'] = '用户登录失败';
				$this->showMessage('fail', $result);
			}

			//更新用户登录信息
			$data['login_time'] = time();
			$data['login_ip']	= $this->global_func->get_remote_ip();
			$this->User->update_user($userinfo['uid'], $data);
		}else{
			//注册新用户基本信息
			$data['create_time'] = time();
			$data['login_time'] = time();
			$data['nickname'] = $this->User->getRandNick();
			$data['avatar']		= "http://tp1.sinaimg.cn/5659179515/50/0/1";

			$data['login_ip']	= $this->global_func->get_remote_ip();
			$data['mobile'] = $phone;

			// 用户登录来源信息
			$thirdData['ch'] = 1;
			$thirdData['open_id'] = $phone;

			$userinfo = $this->User->add_user($data, $thirdData);
		}

		if(!$userinfo || $userinfo['uid'] < 1){
			$result['message'] = '用户登录失败';
			$this->showMessage('fail', $result);
		}

		// 删除短信记录信息
		$succKey = sha1("web_sendDownloadMsg_success_".$phone);
		$this->cache->redis->delete($succKey);
		$this->cache->redis->delete($redisKey);

		// 获取登录cookie
		$rs = $this->ssoserver->rsaWeb($userinfo);
		if($rs){
			$this->User->setUserCookies($rs);
			header("Location: ".$back_url);
		}else{
			$result['message'] = '登录失败';
			$this->showMessage('fail', $result);
		}

	}

	/**
	 * 注册协议
	 *
	 */
	public function agreement(){
		$this->smarty->view ( 'user/agreement.tpl' );
	}

	/**
	 * 个人中心首页
	 *
	 */
	public function index(){
		if(!$this->user_id){
			header("Location: /user/login");
		}
		$act = $act_wap = trim($this->input->get("act", true));
		$act = empty($act) ? "follow_game" : $act;

		// 默认加载关注游戏
		$data = $this->follow_game();
		$data['navflag']=	'user';
		$data['act']	=	$act;



		if($this->global_func->isMobile()){
			$my_ask_data = $this->question();
			$this->smarty->assign('my_ask_data',$my_ask_data);//我的提问
			$my_answers_data = $this->answers();
			$this->smarty->assign('my_answers_data',$my_answers_data);//我的答案
			$follow_article_data = $this->follow_article();
			$this->smarty->assign('follow_article_data',$follow_article_data);//收藏攻略
			$follow_question_data = $this->follow_question();
			$this->smarty->assign('follow_question_data',$follow_question_data);//关注的问题
			$follow_answer_data = $this->follow_answers();
			$this->smarty->assign('follow_answer_data',$follow_answer_data);//关注的答案

			$get_message_data = $this->get_message();
			$this->smarty->assign('get_message_data',$get_message_data);//我的通知
			$data['act']=$act_wap;
			$this->smarty->view ( 'user/wap_user_center.tpl', $data );

		}else{
			$res_counts = $this->user_center_count();
			$this->smarty->assign('user_center_count',$res_counts);
			$this->smarty->view ( 'user/pc_user_center.tpl', $data );
		}
	}


	/*
	 * 已收藏攻略列表
	*/
	public function follow_article()
	{
		if(!$this->user_id){
			die('error');
		}
		$p_data['page'] = $page = (int)trim( $this->input->get_post('page',true) );
		$is_ajax = intval($this->input->get_post('is_ajax',true));

		$max_id	  	= trim ( $this->input->get('max_id',true) );
		$limit = 10;
		$page = $page<1 ? 1 : $page;
		$start = ($page - 1) * $limit;

		$platform = $this->platforms;//来源
		$res_data = $this->follow->getFollowInfo($this->user_id,1,$start,$limit,0);
		$returns = $res_data['list'];

		$total_rows = $res_data['total_rows'];
		if($returns)
		{
			foreach ($returns as $k=>$v){
				$article[$k] = $this->Article->findArticleData($v['mark']);
				$data[$k]['absId'] = (string) $v['mark'];
				$data[$k]['abstitle'] = trim($article[$k]['title'])?trim($article[$k]['title']):'';
				$data[$k]['updateTime'] = $v['update_time'] ? date('Y-m-d H:i',$v['update_time']) : '';
			}
		}
		$datas['list']	= $data;
		$datas['page_data']	= $this->common_model->pages($total_rows, $page, $limit);
		if($is_ajax == 1){
			$datas['act'] = $total_rows > 0 ? 'follow_article' : 'show_empty';
			if($this->global_func->isMobile()){
				Util::echo_format_return(200, $datas, '请求成功', true);
			}
			else
			{
				$tpl = $this->smarty->view ( 'user/pc_data_box.tpl', $datas, true );
				Util::echo_format_return(200, $tpl, $total_rows, true);
			}
		}else{
			return $datas;
		}
	}

	/**
	 *我关注的提问
	 * by 宋庆禄
	 **/
	public function follow_question()
	{
		if(!$this->user_id){
			die('error');
		}

		$p_data['page'] = $page = (int)trim( $this->input->get_post('page',true) );
		$is_ajax = intval($this->input->get_post('is_ajax',true));

		$limit = 10;
		$page = $page<1 ? 1 : $page;

		$start = ($page - 1) * $limit;
		$platform = $this->platforms;//来源

		$res_data = $this->follow->getFollowInfo($this->user_id,4,$start,$limit);
		$returns = $res_data['list'];
		$total_rows = $res_data['total_rows'];

		foreach ($returns as $k=>$v){
			// 问题信息
			$info = $this->question_model->get_info($v['mark'],array(0,1,2));
			$content_info = $this->Question_content->get_content($v['mark']);
			$content_info = preg_replace('/\[!--IMG_*--\]/', '', $content_info);

			$data[$k]['absId'] = (string) $info['qid'];
			$data[$k]['answerCount'] = (int) $info['normal_answer_count'];
			$data[$k]['abstitle'] = (string)$this->Qa->convert_content_to_frontend(trim($content_info),200);
			$data[$k]['updateTime'] = $v['update_time'] ? date('Y-m-d H:i',$v['update_time']) : '';
			$data[$k]['status'] = $info['status'] >= 2 ? 1 : 0;
		}

		$datas['list']	= $data;
		$datas['page_data']	= $this->common_model->pages($total_rows, $page, $limit);

		if($is_ajax == 1){
			$datas['act'] = $total_rows > 0 ? 'follow_question' : 'show_empty';
			if($this->global_func->isMobile()){
				Util::echo_format_return(200, $datas, '请求成功', true);
			}
			else
			{
				$tpl = $this->smarty->view ( 'user/pc_data_box.tpl', $datas, true );
				Util::echo_format_return(200, $tpl, $total_rows, true);
			}
		}else{
			return $datas;
		}
	}

	/*
	 * 我收藏的答案列表
	*/
	public function follow_answers()
	{
		if(!$this->user_id){
			die('error');
		}

		$p_data['page'] = $page = (int)trim( $this->input->get_post('page',true) );
		$is_ajax = intval($this->input->get_post('is_ajax',true));

		$limit = 10;
		$page = $page<1 ? 1 : $page;

		$start = ($page - 1) * $limit;
		$platform = $this->platforms;//来源

		$res_data = $this->follow->getFollowInfo($this->user_id,2,$start,$limit);
		$returns = $res_data['list'];
		$total_rows = $res_data['total_rows'];
		foreach ($returns as $k=>$v){
			// 答案信息
			$content_info = $this->Answer_content->get_content($v['mark']);
			$content_info = preg_replace('/\[!--IMG_*--\]/', '', $content_info);
			$content = $this->Answer->get_info($v['mark']);
			// 问题信息
			$content_q = $this->Question->get_info($content['qid'],array(0,1,2));
			$content_qc = $this->Question_content->get_content($content['qid']);
			$content_qc = preg_replace('/\[!--IMG_*--\]/', '', $content_qc);
			$questionInfo['absId'] = (string) $content_q['qid'];
			$questionInfo['status'] = (string) $content_q['status'] >= 2 ? 1 : 0;
			$questionInfo['abstitle'] = trim($content_qc) ? $this->Qa->convert_content_to_frontend(trim($content_qc),200) : '';
			$questionInfo['answerCount'] = (int)$content_q['normal_answer_count'];

			$data[$k]['absId'] = (string) $v['mark'];
			$data[$k]['abstitle'] = trim($content_info) ? $this->Qa->convert_content_to_frontend(trim($content_info),200) : '';
			$data[$k]['updateTime'] = $v['update_time'] ? date('Y-m-d H:i:s',$v['update_time']) : '';
			$data[$k]['status'] = $content['status'] >= 2 ? 1 : 0;
			$data[$k]['questionInfo'] = $questionInfo;
		}

		$datas['list']	= $data;
		$datas['page_data']	= $this->common_model->pages($total_rows, $page, $limit);

		if($is_ajax == 1){
			$datas['act'] = $total_rows > 0 ? 'follow_answers' : 'show_empty';
			if($this->global_func->isMobile()){
				Util::echo_format_return(200, $datas, '请求成功', true);
			}
			else
			{
				$tpl = $this->smarty->view ( 'user/pc_data_box.tpl', $datas, true );
				Util::echo_format_return(200, $tpl, $total_rows, true);
			}
		}else{
			return $datas;
		}

	}

	/**
	 *我的提问
	 * by 宋庆禄
	 **/
	public function question()
	{
		if(!$this->user_id){
			die('error');
		}

		$p_data['page'] = $page = (int)trim( $this->input->get_post('page',true) );
		$is_ajax = intval($this->input->get_post('is_ajax',true));

		$limit = 10;
		$page = $page<1 ? 1 : $page;

		$start = ($page - 1) * $limit;
		$platform = $this->platforms;//来源

		$id_list = $this->question_model->get_lists_id_by_uid($this->user_id, $start, $limit);
		$total_rows = $this->question_model->get_count_by_uid_from_db($this->user_id,1);

		$data = array();
		$c = 0;
		foreach ($id_list as $id) {
			if (++$c > $limit) {
				break;
			}
			$data[] = $id;
		}


		$return = array();
		foreach ($data as $v){
			$info = $this->question_model->get_info($v,array(0,1,2));
			$content_info = $this->Question_content->get_content($v);
			$_arr = array();
			$_arr['absId'] = (string) $info['qid'];
			$_arr['answerCount'] = (int) $info['normal_answer_count'];
			$_arr['abstitle'] = (string)$this->Qa->convert_content_to_frontend(trim($content_info),100);
			$_arr['updateTime'] = $info['update_time'] ? date('Y-m-d H:i',$info['update_time']) : '';
			$_arr['status'] = $info['status'] >= 2 ? 1 : 0;
			$return[] = $_arr;
		}

		$datas['list']	= $return;
		$datas['page_data']	= $this->common_model->pages($total_rows, $page, $limit);

		if($is_ajax == 1){
			$datas['act'] = $total_rows > 0 ? 'question' : 'show_empty';
			if($this->global_func->isMobile()){
				Util::echo_format_return(200, $datas, '请求成功', true);
			}
			else
			{
				$tpl = $this->smarty->view ( 'user/pc_data_box.tpl', $datas, true );
				Util::echo_format_return(200, $tpl, $total_rows, true);
			}
		}else{
			return $datas;
		}
	}

	/*
	 * 我的回答
	 * by 宋庆禄
	*/
	public function answers()
	{
		if(!$this->user_id){
			die('error');
		}

		$p_data['page'] = $page = (int)trim( $this->input->get_post('page',true) );
		$is_ajax = intval($this->input->get_post('is_ajax',true));

		$limit = 10;
		$page = $page<1 ? 1 : $page;

		$start = ($page - 1) * $limit;
		$platform = $this->platforms;//来源


		$info = $this->Answer->get_a_list_by_uid($start,$limit,0,$this->user_id);
		$total_rows = $this->Answer->get_count_by_uid_from_db($this->user_id,1);

		foreach ($info as $k=>$v){
			$content_info = $this->Answer_content->get_content($v['aid']);
			$content_info = preg_replace('/\[!--IMG_*--\]/', '', $content_info);
			$data[$k]['absId'] = (string) $v['aid'];
			$data[$k]['abstitle'] = trim($content_info) ? $this->Qa->convert_content_to_frontend(trim($content_info),200) : '';
			$data[$k]['updateTime'] = $v['update_time'] ? date('Y-m-d H:i',$v['update_time']) : '';
			$data[$k]['status'] = $v['status'] >= 2 ? 1 : 0;

			// 答案对应问题信息
			$content_info_q = $this->Question_content->get_content($v['qid']);
			$content_info_q_row = $this->Question->get_info($v['qid'],array(0,1,2));
			$content_info_q = preg_replace('/\[!--IMG_(\d+)--\]/', '', $content_info_q);
			if($content_info_q){
				$questionInfo['absId'] = (string) $v['qid'];
				$questionInfo['status'] = $content_info_q_row['status'] >= 2 ? 1 : 0;
				$questionInfo['abstitle'] = $content_info_q ? $this->Qa->convert_content_to_frontend($content_info_q,200) : '';
				$questionInfo['answerCount'] = (int) $content_info_q_row['normal_answer_count'];
				$data[$k]['questionInfo'] = $questionInfo;
			}
			else
			{
				//为防止ajax调用 找不到questioninfo的值
				$questionInfo['absId'] = '';
				$questionInfo['abstitle'] = '该问题已不存在';
				$questionInfo['answerCount'] = 0;
				$data[$k]['questionInfo'] = $questionInfo;
			}
		}

		$datas['list']	= $data;
		$datas['page_data']	= $this->common_model->pages($total_rows, $page, $limit);

		if($is_ajax == 1){
		$datas['act'] = $total_rows > 0 ? 'answers' : 'show_empty';
			if($this->global_func->isMobile()){
				Util::echo_format_return(200, $datas, '请求成功', true);
			}
			else
			{
				$tpl = $this->smarty->view ( 'user/pc_data_box.tpl', $datas, true );
				Util::echo_format_return(200, $tpl, $total_rows, true);
			}
		}else{
			return $datas;
		}

	}



	/**
	 * 关注游戏
	 * by 宋庆禄
	 * */
	public function follow_game(){
		if(!$this->user_id){
			die('error');
		}

		$data['page'] = $page = (int)trim( $this->input->get_post('page',true) );
		$is_ajax = intval($this->input->get_post('is_ajax',true));

		if($this->global_func->isMobile()){
			$limit = 5;
		}
		else
		{
			$limit = 30;
		}

		$page = $page<1 ? 1 : $page;

		$start = ($page - 1) * $limit;
		$platform = $this->platforms;//来源

		//缓存key 与哈希key
		$cache_list_key = $this->_cache_key_pre.":users:".$this->user_id.":"."follow_game_list";
		$cache_list_hash_key = 'normal:'.$platform.':'.$page.":".$limit;
		$cache_list_hash_count_key = $cache_list_key.":count";
		//获取缓存数据
		$cache_attentioned_list = $this->cache->redis->hGet ( $cache_list_key, $cache_list_hash_key );
		$cache_attentioned_list = json_decode($cache_attentioned_list,1);
		$cache_attentioned_count = $this->cache->redis->get ( $cache_list_hash_count_key );
		if(! is_array ($cache_attentioned_list))
		{
			$res_data = $this->follow->get_follow_info($this->user_id,3,$start,$limit,true);
			$infoss = $res_data['list'];
			$infoss || $infoss = array();
			foreach ($infoss as $v) {
				$game_id_arr[] = $v['mark'];
			}
		}
		$android_cms = array();
		if ($game_id_arr) {
			// 缓存中没数据
			$game_id_arr = array_unique($game_id_arr);
			$cms_game_format_info = $this->game_model->get_cms_game_list_info_for_wap($game_id_arr,'ios');
			$android_cms = $this->game_model->get_cms_game_list_info_for_wap($game_id_arr,'android');
		}

		foreach($android_cms as $sk=>$sv){
			if(empty($cms_game_format_info[$sk])){
				$cms_game_format_info[$sk] = $sv;
			}
		}

		if(! is_array ($cache_attentioned_list))
		{
			$attentionedList = array();
			foreach($infoss as $k1 => $v1)
			{
				$infoss[$k1] = $this->game_model->get_game_row($v1['mark'],$platform);

				if(!$infoss[$k1]){
					$infoss[$k1] = $this->game_model->get_game_row($v1['mark'],'android');
				}
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

			$cache_attentioned_count = $res_data['counts'];
		}
		else
		{
			$attentionedList = $cache_attentioned_list;
		}

		if(! is_array ($cache_attentioned_list))
		{
			//设置缓存并且设置失效时间
			$this->cache->redis->hSet ( $cache_list_key, $cache_list_hash_key, json_encode($attentionedList) );
			$this->cache->redis->expire ( $cache_list_key, $expire_time );
			$this->cache->redis->set($cache_list_hash_count_key,$res_data['counts']);
		}

		// 首字母排序
		if(count($attentionedList) > 0){
			$attentionedList = $this->global_func->array2sort($attentionedList, "initialsEng");
		}else{
			$cache_attentioned_count = 0;
		}

		$datas['list']	= $attentionedList;
		$datas['page_data']	= $this->common_model->pages($cache_attentioned_count, $page, $limit);

		if($is_ajax == 1){
			$datas['act'] = $cache_attentioned_count > 0 ? 'follow_game' : 'show_empty';
			if($this->global_func->isMobile()){
				Util::echo_format_return(200, $datas, '请求成功', true);
			}
			else
			{
				$tpl = $this->smarty->view ( 'user/pc_data_box.tpl', $datas, true );
				Util::echo_format_return(200, $tpl, $cache_attentioned_count, true);
			}

		}else{
			return $datas;
		}
	}


	/*
	 * 编辑用户信息
	*/
	public function edit_user()
	{
		$action	  	= $this->input->post('action',true) ? $this->input->get_post('action',true) : 0;
		$nickname	  	= $this->input->post('nickname');

		// 处理h5上传头像
		if (preg_match('/^(data:\s*image\/(\w+);base64,(.*))/', $_POST['pic'], $result)){
			$img_type = $result[2];
			$img_info = base64_decode($result[3]);

			$data = $this->User->h5_upload_imgs($img_info, $img_type);

			Util::echo_format_return(_SUCCESS_, $data, '修改成功');
			exit;
		}



		//		echo $pattern;exit; 4-30 字符
		try {
			if (!$this->global_func->check_refer()) {
				throw new Exception('csrf', _PARAMS_ERROR_);
			}

			if(!$this->user_id) {
				throw new Exception('用户未登录', _PARAMS_ERROR_);
			}
			if($action <= 0) {
				throw new Exception('操作类型无效', _PARAMS_ERROR_);
			}
			if($action == 1){
				//头像
				//先判断是否图片格式
				$filetypes = array(
						'image/jpg' => 'jpg',
						'image/png' => 'png',
						'image/gif' => 'gif',
						'image/jpeg' => 'jpeg',
						'image/bmp' => 'bmp',
						'image/x-png' => 'png', //IE8兼容
						'image/pjpeg' => 'jpg', //IE8兼容
				);
				if(!($filetypes[$_FILES['upfile']['type']]))
				{
					Util::echo_format_return(80001, $data, '修改失败');exit;
				}
				$data = $this->User->upload_img($_FILES);
			}
			if($action == 2){
				//昵称
				$data['nickname'] = trim($nickname);
				if(mb_strlen($data['nickname']) < 2 && mb_strlen($data['nickname']) >14){
					throw new Exception('用户昵称必须在2-14字符内', _PARAMS_ERROR_);
				}
				// 特殊中文不支持，比如“㶬”
				$pattern = '/^[\x{4e00}-\x{9fa5}A-Za-z0-9]+$/u';
				if (!preg_match($pattern, $data['nickname'])) {
					throw new Exception('包含非法字符', _PARAMS_ERROR_);
				}

				$check_nickname = $this->User->_check_nickname($nickname);
				if($check_nickname == 1){
					throw new Exception('用户昵称已经存在', _PARAMS_ERROR_);
				}
				$this->load->config('ban_word_pattern', true);
				$pattern = $this->config->item('ban_word_pattern');
				foreach($pattern as $k => $v)
				{
					$error_uname = '';
					preg_match($v,$data['nickname'],$error_uname);
					if($error_uname){
						throw new Exception('内容不能包含敏感词汇', _PARAMS_ERROR_);
					}
				}

				$this->User->update_user($this->user_id,$data);
			}
			$data = $data ? $data : array();
			Util::echo_format_return(_SUCCESS_, $data, '修改成功');
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}


	/**
	 * 获取用户经验等级
	 *
	 */
	public function getLevelExp()
	{
		try {
			if(!$this->user_id) {
				throw new Exception('用户未登录', _PARAMS_ERROR_);
			}
			$user_info = $this->userinfo;
			$level = $this->User->getLevel($user_info['exps'],$user_info['virtual_exps']);
			$uInfo = array();

			$uInfo['name'] = $user_info['nickname'] ? $user_info['nickname'] : '';
			$uInfo['headUrl'] = $user_info['avatar'] ? $user_info['avatar'] : '';
			$uInfo['birthday'] = $user_info['birthday'] ? date('Y-m-d H:i:s', $user_info['birthday']) : '';
			$uInfo['sex'] = (int)$user_info['gender'];

			$levelExps = $this->User->getNLevelExps($level);
			$uInfo['uLevel'] = $level;
			$uInfo['medalLevel'] = (int) $user_info['rank'];
			$uInfo['totalExperience'] = (int) ($user_info['exps'] + $user_info['virtual_exps']);
			$uInfo['nextLevelExperience'] = (int) $levelExps['max'];
			$uInfo['currentLevelExperience'] = (int) $levelExps['min'];

			$uInfo['pct'] =sprintf("%.2f", (($uInfo['totalExperience']-$uInfo['currentLevelExperience']) / ($uInfo['nextLevelExperience']-$uInfo['currentLevelExperience']))) * 100 ;
			$uInfo = $uInfo ? $uInfo : array();
			Util::echo_format_return(_SUCCESS_, $uInfo);
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}

	/**
	 * 我的通知
	 * by 宋庆禄
	 * */
	public function get_message () {
		if(!$this->user_id){
			die('error');
		}
		$uid = $this->user_id;
		$is_ajax = intval($this->input->get_post('is_ajax',true));

		try {
			$user_info = $this->User->getUserInfoById($uid);
			$last_get_message_time = $user_info['get_message_time'];
			$update_data = array(
					'get_message_time' => time()
			);
			$this->User->update_user($uid, $update_data);


			$this->load->model('user_message_model');
			$data = $this->user_message_model->get_message($uid);

			$this->user_message_model->got_message($uid);


			$this->load->model('qa_model');
			$this->load->model('question_content_model');
			$this->load->model('answer_content_model');
			$return = array();
			foreach ($data as $v) {
				// 问题或答案，直接取最新的
				if ($v['type'] == 1) {
					$content = $this->question_content_model->get_content($v['mark']);
					$content && $v['content'] = $this->qa_model->convert_content_to_frontend($content, 200);
				} elseif ($v['type'] == 2) {
					$content = $this->answer_content_model->get_content($v['mark']);
					$content && $v['content'] = $this->qa_model->convert_content_to_frontend($content, 200);
				}

				$is_new = false;
				if ($v['update_time'] > $last_get_message_time && $v['status'] != 2) {
					// 时间大于最后一次请求该接口的时间   && status != 已读
					$is_new = true;
				}

				$url = '';
				if($v['type'] == 1 && !empty($v['mark'])){
					$url = "/question/info/".$v['mark'];
				}elseif($v['type'] == 2 && !empty($v['mark'])){
					$url = "/answer/info/".$v['mark'];
				}elseif($v['type'] == 3 && !empty($v['mark'])){
					$url = "/zq/juhe_page/".$v['mark'];
				}elseif($v['type'] == 4 && !empty($v['mark'])){
					$url = "/raiders/info/".$v['mark'];
				}

				$return[] = array(
						'absId' => (string)$v['id'],
						'isNew' => (boolean)($is_new),
						'title' => (string)$v['title'],
						'subtitle' => (string)$v['content'],
						'updateTime' => (string)date('Y-m-d H:i', $v['update_time']),
						'type' => (int)$v['type'],
						'flag' => (int)$v['flag'],
						'count'=> $v['count'],
						'url'=> $url,
						'param' => (string) (empty($v['mark']) ? '' : $v['mark'] ),
				);
			}
			$datas['returns'] = $return;
			$datas['total_nums'] = count($datas['returns']);

			if($is_ajax == 1){
				$datas['act'] = count($datas['returns']) > 0 ? 'get_message' : 'show_empty';
				if($this->global_func->isMobile()){
					Util::echo_format_return(_SUCCESS_, $datas,'请求成功',true);
				}
				else{
					$tpl = $this->smarty->view ( 'user/pc_data_box.tpl', $datas, true );
					Util::echo_format_return(200, $tpl, '请求成功', true);
				}

			}else{
				return $datas;
			}
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}
	//清除message
	public function del_message() {
		$id_str = $this->input->get_post('mark', true);
		$uid = $this->user_id;
		$action = $this->input->get_post('action', true);
		try {
			if (empty($uid) || ($action == 0 && empty($id_str))) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}

			$this->load->model('user_message_model');

			if ($action == 0) {
				$data = $this->user_message_model->get_message($uid);
				// 修改状态/
				$id_arr = explode(',', $id_str);
				foreach ($id_arr as $v) {
					if (!is_numeric($v)) {
						continue;
					}
					$this->user_message_model->del_message($uid, $v);
				}
			} else {
				$this->user_message_model->del_message($uid, 'all');
			}


			Util::echo_format_return(_SUCCESS_);
			return 1;
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}

	//个人中心 count数
	private function user_center_count()
	{
		$counts_arr = array();
		$follow_game_data = $this->follow_game();
		$counts_arr['follow_game_counts'] = $follow_game_data['page_data']['total_nums'] >0 ? $follow_game_data['page_data']['total_nums']:'';
		$my_ask_data = $this->question();
		$counts_arr['my_ask_counts'] = $my_ask_data['page_data']['total_nums'] >0 ? $my_ask_data['page_data']['total_nums']:'';
		$my_answers_data = $this->answers();
		$counts_arr['my_answer_counts'] = $my_answers_data['page_data']['total_nums'] >0 ? $my_answers_data['page_data']['total_nums']:'';
		$follow_article_data = $this->follow_article();
		$counts_arr['follow_article_counts'] = $follow_article_data['page_data']['total_nums'] >0 ? $follow_article_data['page_data']['total_nums']:'';
		$follow_question_data = $this->follow_question();
		$counts_arr['follow_question_counts'] = $follow_question_data['page_data']['total_nums'] >0 ? $follow_question_data['page_data']['total_nums']:'';
		$follow_answer_data = $this->follow_answers();
		$counts_arr['follow_answer_counts'] = $follow_answer_data['page_data']['total_nums'] >0 ? $follow_answer_data['page_data']['total_nums']:'';
		$get_message_data = $this->get_message();
		$counts_arr['my_message'] = $get_message_data['total_nums'] >0 ? $get_message_data['total_nums']:'';
		return $counts_arr;
	}


}

/* End of file user.php */
/* Location: ./application/controllers/api/user.php */
