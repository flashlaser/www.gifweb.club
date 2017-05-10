<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

/**
 * 
 * @name Question
 * @desc 攻略WAP问题控制类
 *
 * @author	 wangbo8
 * @date 2015年12月17日
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 */
class Question extends MY_Controller {
	public function __construct() {
		parent::__construct ();
		// $_SERVER['SERVER_NAME'] = 'www.wan68.com';
		// $back_url = $url='http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
		$back_url = $url=base_url() .$_SERVER["REQUEST_URI"];
		$this->smarty->assign('back_url', $back_url);

		$this->load->model('follow_model');
		$this->load->model('game_model');
		$this->load->model('recommend_model');
		$this->load->model('gl_model');
		$this->load->model('user_model');
		$this->load->model('article_model');
		$this->load->model('like_model');
		$this->load->model('qa_model');
		$this->load->model('answer_model');
		$this->load->model('answer_content_model');
		$this->load->model('qa_image_model');
		$this->load->model('waptext_model');
	}
	
	//问题列表
	public function qlist($gameId){ //首页
		$gameId = $this->global_func->filter_int($gameId);

		try{
			//判断游戏ID
			if (empty($gameId)) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}

			//获取平台类型
			$this->platform = Util::getBrowse();	

			//获取游戏信息
			$cms_info = $this->game_model->get_cms_game_info($gameId);

			//判断
			if(empty($cms_info)){
				$this->platform = 'android';
				$cms_info = $this->game_model->get_cms_game_info($gameId);

				if(empty($cms_info)){
					$this->platform = 'ios';
					$cms_info = $this->game_model->get_cms_game_info($gameId);
				}
			}
			$cms_info = $cms_info[0];

			//判断是否有游戏信息
			if (empty($cms_info)) {
				throw new Exception('没有这个游戏', _PARAMS_ERROR_);
			}

			//获取攻略分类集合
			$info_a = $this->gl_model->get_category_row($gameId);

			$data['id'] 	=$info_a['id'] ? $info_a['id'] : '';
			$data['abstitle'] 	=$info_a['abstitle'] ? $info_a['abstitle'] : '';

			$page = 1;
			$page_size = 10;

			$page < 1 && $page = 1;
			$offsize = ($page - 1) * $page_size;
			$data['data'] = $this->qa_model->get_question_list($gameId, $offsize, $page_size);

			$data['askgid'] = $gameId;
			$data['gameId'] = $gameId;

			//拼装seo信息
			$seotitle = $data['abstitle'] . '问答_' . $data['abstitle'] . '专区_全民手游攻略';
			$seokeywords .=  $data['abstitle'] .'问答，解决'. $data['abstitle'] .'问题，'. $data['abstitle'] .'攻略'; 
			$seodescription = $data['abstitle'] . '问答广场，帮你解决一切' . $data['abstitle'] . '的相关问题。所有你想知道的，只要问出来，游戏大神都会为你热心解答。大家帮助大家。';

			$seo = array(
					'title' => $seotitle,
					'keywords' => trim($seokeywords, ','),
					'description' => $seodescription
			);
			$this->smarty->assign('seo', $seo);
		    $this->smarty->assign('data', $data);
		    $this->smarty->view ( 'zq/zq-single.tpl' );
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}

	//提问页面展示
	public function ask($gid = 0,$qid = 0){
		$gid = $this->global_func->filter_int($gid);
		$qid = $this->global_func->filter_int($qid);
		$uid = $this->user_id;

		$askcontent = trim(( string ) $this->input->get ( 'search_keyword', true ));
		$askcontent = urldecode($askcontent);

		//判断是否登录，如果没有登录则跳转到登录页
		if(!$uid){
			//获取当前页面URL
			$self_url = current_url();
			header('Location:/user/login?backUrl=' . $self_url);
		}

		//判断是否指定游戏
		if($gid){
			$game_info = $this->game_model->get_cms_game_info($gid);
			$data['game_info'] = $game_info[0];
			$data['gid'] = $gid;
		}

		//判断是否指定问题
		if($qid > 0){
			//获取问题详情内容
			$question = $this->qa_model->get_question_info($uid, $qid);
			
			//判断当前用户是否是提问者
			if($uid != $question['author']['guid']){
				header('Content-Type:text/html;charset=utf-8');
				echo "<script>alert('非该问题的提问者，无法编辑问题');window.history.back();</script>";
				exit;
			}
			
			$data['imgnum'] = count($question['attribute']['images']);

			//对问题详情图片进行处理
			$qshare_content = $question['content'];

			//判断是移动端还是PC
			if($this->global_func->isMobile()){
				//处理
				$question['content'] = $question['content'];
			}else {
				//处理
				$question_content = $this->qa_image_model->changeImgStr($question['content']);
				$question['content'] = $question_content['content'];
				//处理
				$question['content'] = $this->waptext_model->convert_content_to_wapfrontend($question['content']);
			}

			$data['question'] = $question;

			$data['game_info']['title'] = $question['gameInfo']['abstitle'];
			$data['qid'] = $qid;
		}

		if($askcontent){
			$data['question']['content'] = $askcontent;
		}

		//设备判断
		if($this->global_func->isMobile()){
			$data['ismobile'] = 1;
		}else{
			$data['ismobile'] = 0;
		}

		//用户禁止判断
		$res = $this->common_model->is_ban_user();
		if($res){
			$data['is_ban'] = 1;
		}else{
			$data['is_ban'] = 0;
		}

		// Util::echo_format_return(_SUCCESS_, $data);
		// exit;
		$this->smarty->assign('data', $data);
		$this->smarty->view ( 'ask.tpl' );
	}

	//提问操作(新建，编辑共用)
	public function question_save(){
		//获得当前用户uid
		$uid = $this->user_id;

		//获得传递过来的数据
		$qid = ( int ) $this->input->get_post ( 'absId', true ); //问题ID，用来区分是编辑还是新建
		$gid = ( int ) $this->input->get_post ( 'gameid', true );
		$game_name = ( string ) $this->input->get_post ( 'searchgamename', false, true );
		$game_name = trim($game_name);
		$content = ( string ) $this->input->get_post ( 'content', false, true );

		//开始提交
		try{
			/*
			//执行注入检测
			$res = $this->global_func->inject_check($content);
			if($res){
				throw new Exception('输入内容含有非法字符', _PARAMS_ERROR_);
			}

			//执行注入检测
			$res2 = $this->global_func->inject_check($searchgamename);
			if($res2){
				throw new Exception('输入标题含有非法字符', _PARAMS_ERROR_);
			}
			*/

			//检查当前用户与问题关系
			if($qid){
				//获取问题详情内容
				$question = $this->qa_model->get_question_info($uid, $qid);

				//判断当前用户是否是提问者
				if($uid != $question['author']['guid']){
					throw new Exception('非该问题的提问者，无法编辑问题', _PARAMS_ERROR_);
				}
			}

			//判断参数是否正确
			if (empty($uid) || empty($content) || empty($game_name)) {
				throw new Exception('必填项没有添加', _PARAMS_ERROR_);
			}

			if (!$this->global_func->check_refer()) {
				throw new Exception('csrf', _PARAMS_ERROR_);
			}

			//判断当前用户是否已经禁用
			if ($this->common_model->is_ban_user()) {
				throw new Exception(_BANNED_MSG_, _USER_BANNED_);
			}

			if($this->q_inject_check($content)){
				throw new Exception('内容中含有非法字符', _PARAMS_ERROR_);
			}

			if($this->q_inject_check($game_name)){
				throw new Exception('内容中含有非法字符', _PARAMS_ERROR_);
			}

			//提示信息初始化
			$msg = $qid ? '编辑成功' : '发布成功';

			//根据是否有gid来判断
			if(!$gid){ //没有游戏id
				//引入搜索类
				$this->load->model('Search_Model', 'Search');
				$sdata = $this->Search->searchGame( $game_name, 1, 20);

				if(is_array($sdata['resultList']) && $sdata['count'] > 0){
					//对比
					foreach($sdata['resultList'] as $vo){
						if($vo['abstitle'] == $game_name){
							$gid = $vo['absId'];
							break;
						}
					}
				}
			}

			$gid = $gid ? $gid : 2031;

			//事务开启
			$this->common_model->trans_begin();

			//执行保存问题基本信息
			$save_info = $this->qa_model->question_save($uid, $qid, $gid, $game_name);

			//判断是否有积分奖励
			if($save_info['add_exp']){
				$jifen_flag = 1;
			}

			//获取问题id
			$qid = (!empty($save_info) && $save_info['qid']) ? $save_info['qid'] : 0;

			if (!$qid) {
				throw new Exception('失败', _DATA_ERROR_);
			}

			$dataArrs = array(
					'uid' => $uid,
					'type' => 1,
					'mark' => $qid
				);

			//开始分离文本与图片关系
			$res_content_data = $this->waptext_model->changeImgStr2($content,$dataArrs);

			if($res_content_data === false){
				throw new Exception('上传图片数量超过限制', _PARAMS_ERROR_);
			}

			$content = $res_content_data['content'];

			//对关键词敏感信息进行过滤
			$content = $this->common_model->filter_content($content);

			if (!$this->qa_model->convert_content_to_frontend($content, 0, 1) ) {
				throw new Exception('您可能输入了非法字符或者没有文字内容，请修改！', _PARAMS_ERROR_);
			}

			//载入新浪xss富文本处理类
			//$this->load->library('anti_xss');
			//使用富文本类过滤黑名单标签
			//$content = $this->anti_xss->purify($content);

			//执行字数裁剪
			$content = $this->global_func->cut_str($content, 6000);

			//执行问题内容添加
			$this->load->model('question_content_model');
			$this->question_content_model->save_content($qid, $content);

			//操作提交
			$this->common_model->trans_commit();

			// 默认关注
			if ($action == 0){
 				$this->follow_model->follow($uid, 4, $qid, 1);	// 新增问题自动关注

				//新增游戏自动关注
				if($gid != 2031){
					$this->follow_model->follow($uid, 3, $gid, 1);

					//新增游戏关注缓存清除
					$guid = $this->user_id;

					//分平台处理
					$platform = Util::getBrowse();

					if($platform != 'ios' && $platform != 'android'){
						$platform = 'pc';
					}
					$this->platform = $platform;

					$cache_attentioned_list_key = sha1('game_list1_attentioned_'. ENVIRONMENT .$guid.'_'.$platform . ":wap");

					$cache_attentioned_list = $this->cache->redis->delete ( $cache_attentioned_list_key );
				}
			}

			//清空问题图片缓存
			$this->clear_images_cache(1, $qid);

			// es搜索
			$this->load->model('search_model');
			$this->search_model->updateEsDataFromDb($qid, 'question');

			//定义调整页面
			$timeflaa = time() + 5;
			$jifenflag = $jifen_flag ? "/a{$timeflaa}/" : "/b{$timeflaa}/";
			$go_url = base_url() . 'question/info/' .$qid . $jifenflag;

			header('Location:' . $go_url);

			//Util::echo_format_return(_SUCCESS_, $return, $msg);
			return 1;
		} catch (Exception $e) {
			$this->common_model->trans_rollback();
			header('Content-Type:text/html;charset=utf-8');
			echo "<script>alert('".  $e->getMessage() ."');window.history.back();</script>";
			//$this->mypop($e->getMessage());
			//sleep(3);
			//echo "<script>window.history.back();</script>";

			//Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}

	//回答页面展示
	public function answer($qid,$aid = -1){
		$qid = $this->global_func->filter_int($qid); //问题id
		$aid = $this->global_func->filter_int($aid); //答案id
		$uid = $this->user_id;

		//判断是否登录，如果没有登录则跳转到登录页
		if(!$uid){
			//获取当前页面URL
			$self_url = current_url();
			header('Location:/user/login?backUrl=' . $self_url);
		}

		//判断是否有答案id
		if($aid > 0){
			$data = $this->qa_model->get_answer_info($uid, $aid);
			$data['imgnum'] = count($data['attribute']['images']);

			//判断当前回答是否属于用户
			if($uid != $data['author']['guid']){
				header('Content-Type:text/html;charset=utf-8');
				echo "<script>alert('非该答案的提供者，无法编辑答案');window.history.back();</script>";
				exit;
			}

			//处理答案内容，增加图片
			$ashare_content = $data['content'];

			//判断是移动端还是PC
			if($this->global_func->isMobile()){
				//处理
				$data['content'] = $data['content'];
			}else {
				//处理答案内容，增加图片
				$answer_tmp = $this->qa_image_model->changeImgStr($data['content']);
				$data['content'] = $answer_tmp['content'] ;

				$data['content'] = $this->waptext_model->convert_content_to_wapfrontend($data['content']);
			}
		}
		$data['qid'] = $qid;

		//设备判断
		if($this->global_func->isMobile()){
			$data['ismobile'] = 1;
		}else{
			$data['ismobile'] = 0;
		}

		//用户禁止判断
		$res = $this->common_model->is_ban_user();
		if($res){
			$data['is_ban'] = 1;
		}else{
			$data['is_ban'] = 0;
		}
		
		//Util::echo_format_return(_SUCCESS_,$data);
		//exit;
		$this->smarty->assign('data', $data);
		$this->smarty->view ( 'myAnswer.tpl' );
	}

	//回答问题保存
	public function answer_save() {
		header('Content-Type:text/html;charset=utf-8');
		$uid = $this->user_id;
		$aid = ( int ) $this->input->get_post ( 'aid', true );
		$qid = ( int ) $this->input->get_post ( 'qid', true );
		$content = ( string ) $this->input->get_post ( 'content', false, true );

		try {
			if (empty($uid) || empty($qid) || empty($content) ) {
				throw new Exception('必填项没有添加', _PARAMS_ERROR_);
			}

			if ($this->common_model->is_ban_user()) {
				throw new Exception(_BANNED_MSG_, _USER_BANNED_);
			}

			if (!$this->global_func->check_refer()) {
				throw new Exception('csrf', _PARAMS_ERROR_);
			}

			if($this->q_inject_check($content)){
				throw new Exception('内容中含有非法字符', _PARAMS_ERROR_);
			}

			/*
			//执行注入检测
			$res = $this->global_func->inject_check($content);
			if($res){
				throw new Exception('输入内容仅可为文字与图片', _PARAMS_ERROR_);
			}
			*/
			//检查当前用户与问题关系
			if($aid){
				$answerinfo = $this->qa_model->get_answer_info($uid, $aid);

				//判断当前回答是否属于用户
				if($uid != $answerinfo['author']['guid']){
					throw new Exception('非该答案的提供者，无法编辑答案', _PARAMS_ERROR_);
				}
			}

			$msg = $aid ? '编辑成功' : '发布成功';
			$this->common_model->trans_begin();

			//执行答案添加
			$save_info = $this->qa_model->answer_save($uid, $aid, $qid);

			//判断是否有积分奖励
			if($save_info['add_exp']){
				$jifen_flag = 1;
			}

			$aid = (!empty($save_info) && $save_info['aid']) ? $save_info['aid'] : 0;
			if (!$aid) {
				throw new Exception('失败', _DATA_ERROR_);
			}

			$dataArrs = array(
					'uid' => $uid,
					'type' => 2,
					'mark' => $aid
			);

			//判断上传图片是否超过十张
			$res_content_data = $this->waptext_model->changeImgStr2($content,$dataArrs);

			if($res_content_data === false){
				throw new Exception('上传图片数量超过限制', _PARAMS_ERROR_);
			}

			//开始分离文本与图片关系
			//$res_content_data = $this->waptext_model->changeImgStr2($content,$dataArrs);
			$content = $res_content_data['content'];
			$content = $this->common_model->filter_content($content);
			if (!$this->qa_model->convert_content_to_frontend($content, 0, 1) ) {
				throw new Exception('您可能输入了非法字符或没有文字内容，请修改！', _PARAMS_ERROR_);
			}

			//载入新浪xss富文本处理类
			//$this->load->library('anti_xss');
			//使用富文本类过滤黑名单标签
			//$content = $this->anti_xss->purify($content);

			//执行字数裁剪
			$content = $this->global_func->cut_str($content, 6000);

			//执行答案内容添加
			$this->load->model('answer_content_model');
			$this->answer_content_model->save_content($aid, $content);

			// 记录被回答次数，更改用户是否为大神
			$this->uredis->cache_answer_num($qid, $uid);
			$this->common_model->trans_commit();

			//清空回答图片缓存
			$this->clear_images_cache(2, $aid);

			//定义调整页面
			$timeflaa = time() + 5;
			$jifenflag = $jifen_flag ? "/a{$timeflaa}/" : "/b{$timeflaa}/";
			$go_url = base_url() . 'answer/info/' . $aid . $jifenflag;

			header('Location:' . $go_url);
			//Util::echo_format_return(_SUCCESS_, $return,  $msg);
			return 1;
		} catch (Exception $e) {
			$this->common_model->trans_rollback();
			header('Content-Type:text/html;charset=utf-8');
			echo "<script>alert('".  $e->getMessage() ."');window.history.back();</script>";
			//$this->mypop($e->getMessage());
			//sleep(3);
			//echo "<script>window.history.back();</script>";

			//Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}


	//图片上传接口
	public function q_upload_img() {
		$uid = $this->user_id;
		$action = 1;

		try {
			if (empty($uid) || !in_array($action, array(1,2)) ) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}

			$upload_res = $this->waptext_model->upload_img($uid, $_FILES);

			if(!$upload_res['url']){
				throw new Exception('上传失败', _PARAMS_ERROR_);
			}

			// $image_domin = "http://store.games.sina.com.cn/";
			// $return = array('error' => 0, 'url' =>  $image_domin. $upload_res['url']);
			$return = array('error' => 0, 'url' =>  $upload_res['url']);
			echo json_encode($return);
			//Util::echo_format_return(_SUCCESS_);
			return 1;
		} catch (Exception $e) {
			$return = array('error' => 0, 'message' => $e->getMessage());
			echo json_encode($return);
			//Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}

	//问题详情
	public function info($absId,$isPop=0){
		$res = $this->global_func->inject_check($absId);
		//增加时间判断 1弹出成功的窗口 2弹出得分的窗口
		$timenow =  time();
		if($isPop){
		    $flag_letter = substr($isPop,0,1);
		    $isPop = substr($isPop,1);
		    if($isPop > $timenow){
		        $isPop = $flag_letter == 'a' ? 1 : 2;
		    }else{
		        $isPop = 3;
		    }
		}
		if($res){
    	    $result['message'] = '分类参数含有非法字符';
    	    $this->showMessage('fail', $result);
    	    exit;
		}
		$qid = $this->global_func->filter_int($absId);

		//获取问题详情
		$data = $this->qa_model->get_question_info($this->user_id, $qid);
		$data_content= $this->qa_image_model->changeImgStr($data['content']);
		$data['content'] = $data_content['content'];
        if( (strlen($data['author']['nickName'])/3) > 10){
            $data['author']['nickName'] = $this->qa_model->convert_content_to_frontend($data['author']['nickName'], 10, 1)."...";
        }
		$data['updateTime'] = Util::from_time(strtotime($data['updateTime']));
		$data['createTime'] = $data['createTime'];
		$data['share_content'] =  $this->qa_model->convert_content_to_frontend($data['original_content'], 0, 1);
		
		$data['content'] = $this->waptext_model->convert_content_to_wapfrontend($data['content']);
		$data['original_content'] = $this->waptext_model->convert_content_to_wapfrontend($data['original_content'], 0, 1);

		if (empty($data) || $data['absId'] =='') {
			//以后 错误提示页显示
    	    $result['message'] = '问题已关闭\不存在\没有所属游戏信息';
    	    $this->showMessage('fail', $result);
    	    exit;
		}
		//热门答案列表
		$answer_hot_info = $this->answer_model->get_hot_list($qid);
		foreach ($answer_hot_info as $k_1 => $v_1) {
		    //用户信息
		    $answer_hot_info[$k_1]['u_info'] = $this->user_model->getUserInfoById($v_1['uid']);
		    if( (strlen($answer_hot_info[$k_1]['u_info']['nickname'])/3) > 10){
		          $answer_hot_info[$k_1]['u_info']['nickname'] = $this->qa_model->convert_content_to_frontend($answer_hot_info[$k_1]['u_info']['nickname'], 10, 1)."...";
		    }else{
		          $answer_hot_info[$k_1]['u_info']['nickname'] = $this->qa_model->convert_content_to_frontend($answer_hot_info[$k_1]['u_info']['nickname'], 0, 1);
		    }
		    //答案内容
		    $answer_hot_info[$k_1]['a_content'] = $this->answer_content_model->get_content($v_1['aid']);
		    $answer_hot_info[$k_1]['content'] = $this->answer_content_model->get_content($v_1['aid']);
		    
		    if( (strlen($answer_hot_info[$k_1]['content'])/3) > 100){
		      $answer_hot_info[$k_1]['content']= trim($this->qa_model->convert_content_to_frontend($answer_hot_info[$k_1]['content'], 100, 1))."...";//展开前数据：替换特殊符号、截取100个字符
		      $answer_hot_info[$k_1]['more_content']=1;
		    }else{
		      $answer_hot_info[$k_1]['content']= $this->qa_model->convert_content_to_frontend($answer_hot_info[$k_1]['content'], 0, 1);//展开前数据：替换特殊符号、截取100个字符
		    }
            $answer_hot_info[$k_1]['content'] = str_replace(array("\r\n", "\r", "\n"), '', $answer_hot_info[$k_1]['content']);
    
		    $answer_hot_info[$k_1]['a_content'] = $this->qa_image_model->changeImgStr($answer_hot_info[$k_1]['a_content']);//展开后数据
		    $answer_hot_info[$k_1]['hasCollect'] = (boolean) $this->follow_model->is_follow($this->user_id, 2, $v_1['aid']);//是否收藏
		    $answer_hot_info[$k_1]['a_img_count'] = $this->qa_image_model->get_list_count(2, $v_1['aid'], 1);
		    $answer_hot_info[$k_1]['ctime'] = Util::from_time($v_1['update_time']);

		    $answer_hot_info[$k_1]['updateType'] = $v_1['update_time'] == $v_1['create_time'] ? 0 : 1;//0发布；1编辑
		    $answer_hot_info[$k_1]['hasAgree'] = (boolean)$this->like_model->is_like($v_1['aid'], 3);
		    $answer_hot_info[$k_1]['hasCombat'] = (boolean)$this->like_model->is_like($v_1['aid'], 4);
		    $answer_hot_info[$k_1]['agreeCount'] = $v_1['mark_up_rank_0_count'] + $v_1['mark_up_rank_1_count'] + $v_1['mark_up_virtual_count'];//点赞数

		    $image_list = $this->qa_image_model->get_list(2, $v_1['aid']);
	        // $answer_hot_info[$k_1]['attribute']['images'] = $image_list['0']['url'] ? IMAGE_URL_PRE .$image_list['0']['url'] : '';
	        $answer_hot_info[$k_1]['attribute']['images'] = gl_img_url($image_list['0']['url']);
		}
		
		$answer_info = $this->answer_model->get_list($qid, 0, 10);
		foreach ($answer_info as $k => $v) {
		    //用户信息
		    $answer_info[$k]['u_info'] = $this->user_model->getUserInfoById($v['uid']);
		    if( (strlen($answer_info[$k]['u_info']['nickname'])/3) > 10){
		          $answer_info[$k]['u_info']['nickname'] = trim($this->qa_model->convert_content_to_frontend($answer_info[$k]['u_info']['nickname'], 10, 1))."...";
		    }else{
		          $answer_info[$k]['u_info']['nickname'] = $this->qa_model->convert_content_to_frontend($answer_info[$k]['u_info']['nickname'], 0, 1);
		    }
		    //答案内容
		    $answer_info[$k]['a_content'] = $this->answer_content_model->get_content($v['aid']);
		    $answer_info[$k]['content'] = $this->answer_content_model->get_content($v['aid']);

		    if( (strlen($answer_info[$k]['content'])/3) > 100){
		        $answer_info[$k]['content']= trim($this->qa_model->convert_content_to_frontend($answer_info[$k]['content'], 100, 1))."...";//展开前数据：替换特殊符号、截取100个字符
		        $answer_info[$k]['more_content']=1;
		    }else{
		        $answer_info[$k]['content']= $this->qa_model->convert_content_to_frontend($answer_info[$k]['content'], 0, 1);//展开前数据：替换特殊符号
		    }

            $answer_info[$k]['content'] = str_replace(array("\r\n", "\r", "\n"), '', $answer_info[$k]['content']);
		    $answer_info[$k]['a_content'] = $this->qa_image_model->changeImgStr($answer_info[$k]['a_content']);//展开后数据

		    $answer_info[$k]['a_img_count'] = $this->qa_image_model->get_list_count(2, $v['aid'], 1);
		    $answer_info[$k]['hasCollect'] = (boolean) $this->follow_model->is_follow($this->user_id, 2, $v['aid']);//是否收藏
		    $answer_info[$k]['hasAgree'] = (boolean)$this->like_model->is_like($v['aid'], 3);
		    $answer_info[$k]['hasCombat'] = (boolean)$this->like_model->is_like($v['aid'], 4);
		    $answer_info[$k]['updateType'] = $v['update_time'] == $v['create_time'] ? 0 : 1;//0发布；1编辑

		    $answer_info[$k]['agreeCount'] = $v['mark_up_rank_0_count'] + $v['mark_up_rank_1_count'] + $v['mark_up_virtual_count'];//点赞数
		    $answer_info[$k]['ctime'] = Util::from_time($v['update_time']);

		    $image_list = $this->qa_image_model->get_list(2, $v['aid']);
	        // $answer_info[$k]['attribute']['images'] = $image_list['0']['url'] ? IMAGE_URL_PRE .$image_list['0']['url'] : '';
	        $answer_info[$k]['attribute']['images'] = gl_img_url($image_list['0']['url']);
		}
		$answer_info_count = $this->answer_model->get_count_by_qid_from_db($qid);
// 		if($qid =='7661'){
// 		echo "<pre>";print_r($answer_hot_info);exit;
// 		}
		//用户禁止判断
		$res = $this->common_model->is_ban_user();
		if($res){
		    $data['is_ban'] = 1;
		}else{
		    $data['is_ban'] = 0;
		}
		//拼装seo信息
		$seotitle = $this->global_func->cut_str(strip_tags($data['share_content']),30);
		$seokeywords = $data['gameInfo']['abstitle'];
		$seodescription = $this->global_func->cut_str(strip_tags($data['share_content']),100);
		$seo = array(
		    'title' => $seotitle."_".$data['gameInfo']['abstitle']."问答_全民手游攻略",
		    'keywords' => $seokeywords,
		    'description' => $seodescription
		);
		$this->smarty->assign('seo', $seo);
		
		$this->smarty->assign('data', $data);
	    $this->smarty->assign('uid', $this->user_id);
	    $this->smarty->assign('data', $data);
	    $this->smarty->assign('isPop', $isPop);
	    $this->smarty->assign('answer_info', $answer_info);
	    $this->smarty->assign('answer_hot_info', $answer_hot_info);
	    $this->smarty->assign('answer_info_count', $answer_info_count);
	    $this->smarty->assign('answer_hot_info_count', count($answer_hot_info));
	    
	    $this->smarty->view ( 'question_info.tpl' );
	}

	//防注入函数
	public function q_inject_check($sql_str) {
		$getfilter="'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)|SLEEP";
		if (preg_match("/".$getfilter."/is",$sql_str)==1){   
			return true;
		}

		return false;
		//return eregi('select|insert|update|delete|union|into|load_file|outfile|sleep', $sql_str); // 进行过滤
	}

	//清空图集缓存
	public function clear_images_cache($type,$id){
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":qa_image:";
		$cache_key = $this->_cache_key_pre . "{$type}:{$id}";
		$hash_key = "list:1";
		$data = $this->cache->redis->hDel($cache_key, $hash_key);
	}
}
