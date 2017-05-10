<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * API-ajax异步调用接口
 *
 * @author haibo8, <haibo8@staff.sina.com.cn>
 * @version   $Id: user.php 2015-12-10 14:52:27 haibo8 $
 * @copyright (c) 2015
 */
class Ajax_fun extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		/*No Cache*/
		$this->output->set_header("Cache-Control: no-cache, must-revalidate");
		$this->output->set_header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
		$this->output->set_header("Pragma: no-cache");

		$this->load->model('Common_model','Comm');
	}

	/**
	 * 获取用户登录验证码
	 *
	 * @param int $uid $_GET
	 * @param int $token $_GET
	 * @return json
	 */
	public function getVcode(){
		$phone	= trim( $this->input->post('phone' , TRUE) );
		$result = array('result'=>1001, 'message'=>'参数有误');
		$ip = $this->global_func->get_remote_ip();

		/*域名限制*/
		if( !isset( $_SERVER['HTTP_REFERER'] ) || (
			!preg_match( "/^http:\/\/www.wan68.com\//" , $_SERVER['HTTP_REFERER'] ) &&
			!preg_match( "/^http:\/\/gl.games.sina.com.cn\//" , $_SERVER['HTTP_REFERER'] ) &&
			!preg_match( "/^http:\/\/wan68.com\//" , $_SERVER['HTTP_REFERER'] )
		) ){
			$result['message'] = '无效域名请求';
			die( json_encode($result) );
		}

		// 手机号码验证
		if(!preg_match("/1[34578]{1}\d{9}$/",$phone)){
			$result['message'] = '手机号格式有误';
			Util::echo_format_return($result['result'], $result['data'], $result['message'], true);
		}

		// mc缓存key
		$mKey = "glapp:users:msg:web_send_".$phone;
		$forbidKey = "glapp:users:msg:web_forbidden_".$phone;
		$succKey = sha1("web_sendDownloadMsg_success_".$phone);

		// 判断是否频繁发送
		$is_forbid = intval( $this->cache->redis->get($forbidKey) );
		if($is_forbid == 1){
			$result['message'] = '操作太频繁，两小时后再来试试吧';
			Util::echo_format_return($result['result'], $result['data'], $result['message'], true);
		}

		// 限制同一电话号码发送频率
		$tNum = $this->cache->redis->get($mKey);
		if(intval($tNum) < 3){
			if($this->cache->redis->exists($mKey)){
				$this->cache->redis->incr($mKey);
			}else{
				$this->cache->redis->incr($mKey);
				$this->cache->redis->expire($mKey, 300);
			}
		}else{
			// 加入限制,2小时候才可以再发送
			$this->cache->redis->save($forbidKey, 1, 7200);
			$result['message'] = '操作太频繁，两小时后再来试试吧';
			Util::echo_format_return($result['result'], $result['data'], $result['message'], true);
		}

		// 判断是否已发送成功，成功发送后60秒内不得重发
		$is_succ = intval( $this->cache->redis->get($succKey) );
		if($is_succ == 1){
			$result['message'] = '60秒后再重发哦';
			Util::echo_format_return($result['result'], $result['data'], $result['message'], true);
		}

		// 生成随机验证码
		$vcode = $this->Comm->get_code(4,1);
		// 记录验证码,300秒有效期
		$redisKey = 'glapp:users:login_code_'.$phone;
		$this->cache->redis->save($redisKey, $vcode, 300);
		// 短信内容
		$msg	= '验证码'.$vcode.',请在5分钟内使用!';

		// 发送短信
		$res = $this->Comm->sendPhoneMsg( $phone,$msg);
		if($res == true){
			$result['result']  = '200';
			$result['data'] = array('validtime'=>'60');
			$result['message'] = '短信发送成功';
			// 发送成功,加入60秒缓存
			$this->cache->redis->save($succKey, 1, 60);
		}else{
			$result['message'] = '验证码发送失败';
			$this->cache->redis->delete( $redisKey );
		}

		Util::echo_format_return($result['result'], $result['data'], $result['message'], true);
	}

	/**
	 * 获取攻略列表
	 *
	 * @param int $ClassId $_GET
	 * @param int $page $_GET
	 * @param int $count $_GET
	 * @param int $max_id $_GET
	 * @return json
	 */
	public function get_list_info_api($ClassId, $max_id, $page = 1, $count = 10){
		$res = $this->global_func->inject_check($ClassId);
		if($res){
			exit('分类参数含有非法字符');
		}
		$ClassId = trim($ClassId);
		$page = $this->global_func->filter_int($page);
		$count = $this->global_func->filter_int($count);
		$max_id = $this->global_func->filter_int($max_id);

        if($ClassId == '0_0_0_0_0_0_0_0'){
            $ClassId = 'a918';
        }

		// mc缓存
		$mcKey = sha1('get_list_info_' . ENVIRONMENT .$ClassId.'_'.$page.'_'.$count.'_'.$max_id);
 		$data = $this->cache->redis->get ( $mcKey );
		$data && $data = json_decode($data, true);

		if($data == false || empty($data)){
			$data = array();
		}else{
			Util::echo_format_return(_SUCCESS_, $data ? $data : array());
			die();
		}

		//载入必要model类
		$this->load->model('game_model');
		$this->load->model('gl_model');
		$this->load->model('article_model');

		try{
			if (empty($ClassId) ) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}

			//分解分类
			$b = explode('_',$ClassId);
			$gameId = str_replace('a','',$b[0]); //替换掉a，获取当前游戏id

			//通过分类ID获取数据
			$article_info = $this->game_model->get_cms_info_by_category($ClassId,$page,$count);

			//-->处理 如果max_id出现相同的 那么从下一页抓取最新的补齐   start --------//
			$flag = 100;
			foreach ($article_info as $_k => $_v) {
				if ($_v['_id'] == $max_id) {
					$flag = $_k;
				}
			}
			$returns = array();
			$returnss = array();
			if ($flag != 100) {

				foreach ($article_info as $_k => $_v) {
					if ($_k > $flag ) {
						array_push($returns, $_v);
					}
				}
				$article_infos = $this->game_model->get_cms_info_by_category($ClassId,$page,$count+1+$flag);
				foreach ($article_infos as $_k1 => $_v) {
					if ($_k1 > $flag) {
						array_push($returnss, $_v);
					}
				}
				$returns = $returnss;
			} else {
				$returns = $article_info;
			}
			//-->处理 如果max_id出现相同的 那么从下一页抓取最新的补齐   end --------//
			$data = array();
			foreach ($returns as $k=>$v){
				/*预留 暂时不要图片
				$images = $this->gl_model->getPicSize($v['_id']);
				$thumbnail[$k] = array();
				foreach ($images as $k_1=>$v_1){
					if($v_1['width'] > '380' && $v_1['height'] > '286' ){
						$thumbnail[$k][] = $v_1['url'];
					}
				}
				if(count($thumbnail[$k]) >=3){
					$thumbnails[$k][] = $thumbnail[$k][0];
					$thumbnails[$k][] = $thumbnail[$k][1];
					$thumbnails[$k][] = $thumbnail[$k][2];
				}*/

				$article[$k] = $this->article_model->findArticleData($v['_id']);
				//$article[$k] = $this->article_model->findArticleData('fxfxrav2052545');

				if(empty($article[$k]['id'])){
					$this->article_model->addRedis($v['_id']);
					continue;
				}

				$_arr = array();
				$_arr['absId'] = $v['_id'];
				$_arr['abstitle'] = $v['title'];
				$_arr['cTime'] = Util::from_time(strtotime($v['mTime']));
				$_arr['absImage'] = $v['pics'][0]['imgurl'] ? $v['pics'][0]['imgurl'] : '';
				$_arr['scanCount'] = $article[$k]['browse_count'] ? (int) $article[$k]['browse_count'] : 0;
				$_arr['praiseCount'] =$article[$k]['mark_up_count'] ? (int)$article[$k]['mark_up_count'] : 0;
				$_arr['thumbnail'] =$thumbnails[$k] ? $thumbnails[$k] : array();
				$_arr['type'] =$v['mdType'] ? 1 : 0;

				$data[] = $_arr;
			}
			$enoughflag = empty($data) ? 1 : 2;

			$returndata = array(
					'data' => $data,
					'enoughflag' => $enoughflag
				);

			$this->cache->redis->save ( $mcKey, json_encode($returndata), 60 * 5 );

			//返回数据
			Util::echo_format_return(_SUCCESS_, $returndata?$returndata:array());
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}

	/**
	 * 获取专区问答列表
	 *
	 * @param int $gameId $_GET
	 * @param int $offsize $_GET
	 * @param int $page_size $_GET
	 * @return json
	 */
	public function getzq_qa_list_api($gameId, $offsize = 2, $page_size = 10){
		$gameId = $this->global_func->filter_int($gameId);
		$offsize = $this->global_func->filter_int($offsize);
		$page_size = $this->global_func->filter_int($page_size);

		//载入必要model类
		$this->load->model('game_model');
		$this->load->model('gl_model');
		$this->load->model('article_model');
		$this->load->model('qa_model');

		try{
			//判断游戏ID
			if (empty($gameId)) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}

			//获取游戏信息
			$cms_info = $this->game_model->get_cms_game_info($gameId);
			$cms_info = $cms_info[0];

			//判断是否有游戏信息
			if (empty($cms_info)) {
				throw new Exception('没有这个游戏', _PARAMS_ERROR_);
			}

			//获取攻略分类集合
			$info_a = $this->gl_model->get_category_row($gameId);

			$data['id'] 	=$info_a['id'] ? $info_a['id'] : '';
			$data['abstitle'] 	=$info_a['abstitle'] ? $info_a['abstitle'] : '';

			$offsize < 2 && $offsize = 2;
			$offsize = ($offsize - 1) * $page_size;

			$data['data'] = $this->qa_model->get_question_list($gameId, $offsize, $page_size);

			$enoughflag = empty($data['data']['newList']) ? 1 : 2;

			$returndata = array(
					'data' => $data,
					'enoughflag' => $enoughflag
				);

			Util::echo_format_return(_SUCCESS_, $returndata?$returndata:array());
			return 1;
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}

	/**
	 * 获取首页最新问答列表
	 *
	 * @param int $offsize $_GET
	 * @param int $page_size $_GET
	 * @return json
	 */
	public function get_home_qa_list_api( $offsize = 2, $page_size = 5){
	    $offsize = $this->global_func->filter_int($offsize);
	    $page_size = $this->global_func->filter_int($page_size);

	    //载入必要model类
	    $this->load->model('question_model');
	    $this->load->model('question_content_model');
	    $this->load->model('user_model');
	    $this->load->model('answer_model');
	    $this->load->model('answer_content_model');
	    $this->load->model('qa_image_model');
	    $this->load->model('qa_model');
	    $this->load->model('game_model');

	    try{

	        //获取用户关注的游戏
	        $infoss = $this->follow_model->get_follow_info($this->user_id,3,-1,-1);
	        $infoss || $infoss = array();
	        foreach ($infoss as $v) {
	            $game_id_arr[] = $v['mark'];
	        }
	        if(!empty($game_id_arr)){
	            $qa_list = $this->question_model->get_list($game_id_arr, intval($offsize), intval($page_size));

	            foreach ($qa_list as $k => $v) {
	                $question_content_info = $this->question_content_model->get_content($v['qid']);

	                $u_info = $this->user_model->getUserInfoById($v['uid']);
	                $questionList =array();
	                $questionList['absId'] 			= (string)$v['qid'];
	                $questionList['uImg'] 	        = $u_info['avatar'];
	                $questionList['abstitle'] 		= $question_content_info ? $this->qa_model->convert_content_to_frontend($question_content_info, false, true) : '';

	                // 获取赞数最多的
	                $answer_list = $this->answer_model->get_secondary_hot_list($v['qid']);
	                $answer_content_info = $this->answer_content_model->get_content($answer_list[0]['aid']);
	                $answer_img_count = $this->qa_image_model->get_list_count(2,$answer_list[0]['aid']);

	                //游戏信息
	                $game_info = $this->game_model->get_cms_game_info($v['gid'],2);
	                $game_info = $game_info[0];
	                if($v['gid'] == 2031){
	                    $questionList['gameInfo']['absId'] 		= '2031';
	                    $questionList['gameInfo']['abstitle'] 	= $v['gname'];
	                    $questionList['gameInfo']['absImage'] 	= '';
	                }else{
	                    $questionList['gameInfo']['absId'] 		= (string)$v['gid'];
	                    if(empty($game_info['title'])){
	                        $questionList['gameInfo']['abstitle'] 	= $v['gname'];
	                    }else{
	                        $questionList['gameInfo']['abstitle'] 	= $game_info['title'] ? (string)$game_info['title'] : '';
	                    }
	                    $questionList['gameInfo']['absImage'] 	= $game_info['logo'] ? (string)$game_info['logo'] : '';
	                }

	                $questionList['answerList'][0]['absId'] = (string)$answer_list[0]['aid'];
	                $questionList['answerList'][0]['imageCount'] 	= $answer_img_count;
	                $questionList['answerList'][0]['abstitle'] = $answer_content_info ? $this->qa_model->convert_content_to_frontend($answer_content_info, false, true) : '';
	                $questionList['answerList'][0]['agreeCount'] = $answer_list[0]['mark_up_rank_0_count'] + $answer_list[0]['mark_up_rank_1_count'] + $answer_list[0]['mark_up_virtual_count'];

	                $new_qa_list[] = $questionList;
	            }
	        }

	        $data['data'] = $new_qa_list;
	        $enoughflag = empty($data) ? 1 : 2;

	        $returndata = array(
	            'data' => $data,
	            'enoughflag' => $enoughflag
	        );

	        Util::echo_format_return(_SUCCESS_, $returndata?$returndata:array());
	        return 1;
	    }catch (Exception $e) {
	        Util::echo_format_return($e->getCode(), array(), $e->getMessage());
	        return 0;
	    }
	}

	/**
	 * 获取答案列表
	 *
	 * @param int $absId $_GET
	 * @param int $page $_GET
	 * @param int $count $_GET
	 * @return json
	 */
	public function answer_list($absId, $page = 1, $count = 10){
		$res = $this->global_func->inject_check($absId);
		if($res){
			exit('分类参数含有非法字符');
		}
		$qid = trim($absId);
		$page = $this->global_func->filter_int($page);
		$page_size = $this->global_func->filter_int($count);

		//载入必要model类
		$this->load->model('qa_model');
		$this->load->model('like_model');

		try{
			if (empty($page) || empty($page_size) ) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}

			$page < 1 && $page = 1;
			$offsize = ($page - 1) * $page_size;
			$data = $this->qa_model->get_answer_list($qid, $offsize, $page_size, $last_id);

			//根据页数判断
			if($page > 1){
				unset($data['hotList']);
			}


			Util::echo_format_return(_SUCCESS_, $data);
			return 1;
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}

	/**
	 * 对攻略赞操作
	 *
	 * @param int $newsid $_GET
	 * @param int $type $_GET
	 * @return json
	 */
	public function raiders_praise_operate($newsid, $type = 0){
		$res = $this->global_func->inject_check($newsid);
		if($res){
			exit('分类参数含有非法字符');
		}
		$newsid = trim($newsid);
		$type = $this->global_func->filter_int($type);
		$guid = $this->user_id;

		//载入必要model类
		$this->load->model('user_model');
		$this->load->model('like_model');
		$this->load->model('answer_model');
		$this->load->model('article_model');

		//执行点赞操作
		try{
			if(!$this->user_id) {
				throw new Exception('用户未登录', _PARAMS_ERROR_);
				exit;
			}

			if (empty($newsid)  ||  $type <0) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
				exit;
			}
			if (false && $this->common_model->is_ban_user()) {
				throw new Exception(_BANNED_MSG_, _USER_BANNED_);
				exit;
			}

			//验证是否重复点赞
			$like_info = $this->like_model->get_info($guid,$newsid,1);
			$like_info2 = $this->like_model->get_info($guid,$newsid,2);
			$user_info = $this->user_model->getUserInfoById($guid);

			$weight_level = $user_info['rank'] ? 1 : 0;

			if($like_info['id']) {//如果对攻略赞过
				if ($type == 1 && $like_info['status'] == 1) { //
					throw new Exception('操作成功', _SUCCESS_);
					Util::echo_format_return(_SUCCESS_, '','操作成功');
					exit;
				} elseif($type != 1 && $like_info['status'] != 1) {
					throw new Exception('操作成功', _SUCCESS_);
					Util::echo_format_return(_SUCCESS_, '','操作成功');
					exit;
				} elseif($type == 1 && $like_info2['status'] == 1) {
					//已经踩过了，点赞数＋1   反对数－1   取消踩操作
					$this->article_model->updateArticleMarkDownCount($newsid,-1);//反对数－1
				}
				$this->like_model->updateLikeData($like_info['id'], $type);
				$this->like_model->updateLikeData($like_info2['id'], 0);
			}else{ //没有对攻略赞过
				if($type == 0){//没有赞过,不能取消点赞
					throw new Exception('操作成功', _SUCCESS_);
					Util::echo_format_return(_SUCCESS_, '','操作成功');
					exit;
				}
				if($type == 1 && $like_info2['status'] == 1) {
					//已经踩过了，点赞数＋1   反对数－1  取消踩操作
					$this->article_model->updateArticleMarkDownCount($newsid,-1);//反对数－1
					$this->like_model->updateLikeData($like_info2['id'], 0);
				}
				//插入该用户点赞情况
				$datas['mark'] = $newsid;
				$datas['user_id'] = $guid;
				$datas['type'] = '1';//攻略赞
				$datas['status'] = '1';
				$datas['weight_level'] = $weight_level;
				$datas['partner_id'] = $partner_id;
				$datas['create_time'] = time();
				$datas['update_time'] = time();
				$this->like_model->insertLikeData($datas);
			}
			//累加点赞数
			$count = $type == 1 ?  1 : -1;
			if($type == 1){
				$msg='操作成功';
			}else{
				$msg='已撤销';
			}
			$this->article_model->updateArticleMarkUpCount($newsid,$count);
			Util::echo_format_return(_SUCCESS_, '',$msg);

		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}

	/**
	 * 对攻略踩操作
	 *
	 * @param int $newsid $_GET
	 * @param int $type $_GET
	 * @return json
	 * @return json
	 */
	public function raiders_cai_operate($newsid, $type = 0){
		$res = $this->global_func->inject_check($newsid);
		if($res){
			exit('分类参数含有非法字符');
		}
		$newsid = trim($newsid);
		$type = $this->global_func->filter_int($type);
		$guid = $this->user_id;

		//载入必要model类
		$this->load->model('qa_model');
		$this->load->model('like_model');
		$this->load->model('article_model');

		try{
			if(!$this->user_id) {
				throw new Exception('用户未登录', _PARAMS_ERROR_);
				exit;
			}
			if (empty($newsid)  ||  $type <0) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
				exit;
			}
			if (false && $this->common_model->is_ban_user()) {
				throw new Exception(_BANNED_MSG_, _USER_BANNED_);
				exit;
			}
			//验证是否重复踩
			$like_info1 = $this->like_model->get_info($guid,$newsid,1);
			$like_info = $this->like_model->get_info($guid,$newsid,2);
			$user_info = $this->user_model->getUserInfoById($guid);
			$weight_level = $user_info['rank'] ? 1 : 0;
			if($like_info['id']) { //曾经踩
				if ($type == 1 && $like_info['status'] == 1) {
					throw new Exception('操作成功', _SUCCESS_);
					Util::echo_format_return(_SUCCESS_, '','操作成功');
					exit;
				} elseif($type != 1 && $like_info['status'] != 1) {
					throw new Exception('操作成功', _SUCCESS_);
					Util::echo_format_return(_SUCCESS_, '','操作成功');
					exit;
				} elseif($type == 1 && $like_info1['status'] == 1) {
					//已经赞过了，点赞数－1   反对数＋1   取消踩操作
					$this->article_model->updateArticleMarkUpCount($newsid,-1);//反对数－1
				}
				$this->like_model->updateLikeData($like_info['id'], $type);
				$this->like_model->updateLikeData($like_info1['id'], 0);
			}else{ //没有踩过
				if($type == 0){
					throw new Exception('操作成功', _SUCCESS_);
					Util::echo_format_return(_SUCCESS_, '','操作成功');
					exit;
				}

				//判断是否曾经赞过
				if($type == 1 && $like_info1['status'] == 1) {
					//已经赞过了，点赞数－1   反对数＋1   取消踩操作
					$this->article_model->updateArticleMarkUpCount($newsid,-1);//反对数－1
					$this->like_model->updateLikeData($like_info1['id'], 0);
				}
				//插入该用户点赞情况
				$datas['mark'] = $newsid;
				$datas['user_id'] = $guid;
				$datas['type'] = '2';//攻略踩
				$datas['status'] = '1';
				$datas['weight_level'] = $weight_level;
				$datas['partner_id'] = $partner_id;
				$datas['create_time'] = time();
				$datas['update_time'] = time();
				$this->like_model->insertLikeData($datas);
			}
			if($type == 1){
				$msg='操作成功';
			}else{
				$msg='已撤销';
			}
			//累加点赞数
			$count = $type == 1 ?  1 : -1;
			$this->article_model->updateArticleMarkDownCount($newsid,$count);
			Util::echo_format_return(_SUCCESS_, '',$msg);
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}

	/**
	 * 对答案赞操作
	 *
	 * @param int $absId $_GET
	 * @param int $type $_GET
	 * @return json
	 */
	public function answer_praise_operate($newsid, $type = 0){
		$res = $this->global_func->inject_check($newsid);
		if($res){
			exit('分类参数含有非法字符');
		}
		$newsid = trim($newsid);
		$type = $this->global_func->filter_int($type);
		$guid = $this->user_id;

		//载入必要model类
		$this->load->model('qa_model');
		$this->load->model('like_model');
		$this->load->model('article_model');

		try{
			if(!$this->user_id) {
				throw new Exception('用户未登录', _PARAMS_ERROR_);
				Util::echo_format_return(_SUCCESS_, '');
				exit;
			}
			if (empty($newsid)  ||  $type <0) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
				Util::echo_format_return(_SUCCESS_, '');
				exit;
			}
			// if ($this->common_model->is_ban_user()) {
			// 	throw new Exception(_BANNED_MSG_, _USER_BANNED_);
			// 	exit;
			// }

			//验证是否重复点赞
			$like_info = $this->like_model->get_info($guid,$newsid,3);
			$like_info4 = $this->like_model->get_info($guid,$newsid,4);
			$user_info = $this->user_model->getUserInfoById($guid);
			$weight_level = $user_info['rank'] ? 1 : 0;

			if($like_info['id']) { //已经赞过
				if ($type == 1 && $like_info['status'] == 1) {
					//重复点赞
					Util::echo_format_return(_SUCCESS_, $this->get_answer_info($like_info['mark']));
					exit;
				} elseif($type != 1 && $like_info['status'] != 1) {
					//重复取消点赞
					Util::echo_format_return(_SUCCESS_, $this->get_answer_info($like_info['mark']));
					exit;
				} elseif($type == 1 && $like_info4['status'] == 1) {
					//已经踩过了，点赞数＋1   反对数－1
					$this->answer_model->add_mark_down_count($newsid,$guid,-1);//反对数－1
					$this->like_model->updateLikeData($like_info4['id'], 0);
				}
				$this->like_model->updateLikeData($like_info['id'], $type);
			}else{ //未曾赞过
				if($type == 0){
					Util::echo_format_return(_SUCCESS_, $this->get_answer_info($like_info['mark']));
					exit;
				}
				if($type == 1 && $like_info4['status'] == 1) {
					//已经踩过了，点赞数＋1   反对数－1
					$this->answer_model->add_mark_down_count($newsid,$guid,-1);//反对数－1
					$this->like_model->updateLikeData($like_info4['id'], 0);
				}


				//插入该用户点赞情况
				$datas['mark'] = $newsid;
				$datas['user_id'] = $guid;
				$datas['type'] = '3';//答案赞
				$datas['status'] = '1';
				$datas['weight_level'] = $weight_level;
				$datas['partner_id'] = $partner_id;
				$datas['create_time'] = time();
				$datas['update_time'] = time();
				$this->like_model->insertLikeData($datas);
				//清缓存
//				$this->answer_model->_clear_info($newsid);
//				$answer_info = $this->answer_model->get_info($newsid);
//				$this->answer_model->_clear_hot_list($answer_info['qid']);
//				$this->answer_model->_clear_list($answer_info['qid']);
                //获取答案的用户
				$answer_info = $this->answer_model->get_info($newsid);
			    $user_infos = $this->user_model->getUserInfoById($answer_info['uid']);
				// 经验
				$this->load->model('exp_model');
				if($user_info['rank'] == 1){
					$add_exp = $this->exp_model->add_exp($user_infos['uid'], 5, $newsid);// 大神
					if ($add_exp) {
						// 增加经验通知
						$this->load->model('push_message_model');
						$this->push_message_model->push(2, 4, $newsid, 1, 1,  $add_exp);
					}
				}else{
					$add_exp = $this->exp_model->add_exp($user_infos['uid'], 6, $newsid);// 一般用户
					if ($add_exp) {
						// 增加经验通知
						$this->load->model('push_message_model');
						$this->push_message_model->push(2, 4, $newsid, 1, 2, $add_exp);
					}
				}

			}
			//累加点赞数
			if($type == 1){
				$count = 1;
			}else{
				$count = -1;
			}
			$this->answer_model->add_mark_up_count($newsid,$guid,$count);

			// 消息模块
			$this->load->model('push_message_model');
			$_push_type = 2;	// 答案
			$_push_flag = 3;	// 赞
			$_push_mark = $newsid;
			$this->push_message_model->push($_push_type, $_push_flag, $_push_mark, $count);

			$like_infoss = $this->like_model->get_info($guid,$newsid,3);
			Util::echo_format_return(_SUCCESS_, $this->get_answer_info($like_infoss['mark']),'操作成功');
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}


	/**
	 * 对答案踩操作
	 *
	 * @param int $absId $_GET
	 * @param int $type $_GET
	 * @return json
	 */
	public function answer_cai_operate($newsid, $type = 0){
		$res = $this->global_func->inject_check($newsid);
		if($res){
			exit('分类参数含有非法字符');
		}
		$newsid = trim($newsid);
		$type = $this->global_func->filter_int($type);
		$guid = $this->user_id;

		//载入必要model类
		$this->load->model('qa_model');
		$this->load->model('like_model');
		$this->load->model('article_model');

		try{
			if(!$this->user_id) {
				throw new Exception('用户未登录', _PARAMS_ERROR_);
			}
			if (empty($newsid)  ||  $type <0) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}
			// if ($this->common_model->is_ban_user()) {
			// 	throw new Exception(_BANNED_MSG_, _USER_BANNED_);
			// 	exit;
			// }
			//验证是否重复点赞
			$like_info = $this->like_model->get_info($guid,$newsid,4);
			$like_info3 = $this->like_model->get_info($guid,$newsid,3);
			$user_info = $this->user_model->getUserInfoById($guid);
			$weight_level = $user_info['rank'] ? 1 : 0;
			if($like_info['id']) {
				if ($type == 1 && $like_info['status'] == 1) {
					//重复点踩
					Util::echo_format_return(_SUCCESS_, $this->get_answer_info($like_info['mark']));
					exit;
				} elseif($type != 1 && $like_info['status'] != 1) {
					//重复取消点踩
					Util::echo_format_return(_SUCCESS_, $this->get_answer_info($like_info['mark']));
					exit;
				} elseif($type == 1 && $like_info3['status'] == 1) {
					//已经赞过了，踩数＋1   赞数－1
					$this->answer_model->add_mark_up_count($newsid,$guid,-1);//点赞数－1
					$this->like_model->updateLikeData($like_info3['id'], 0);


					// 消息模块
					$this->load->model('push_message_model');
					$_push_type = 2;	// 答案
					$_push_flag = 3;	// 赞
					$_push_mark = $newsid;
					$this->push_message_model->push($_push_type, $_push_flag, $_push_mark, -1);
				}
				$this->like_model->updateLikeData($like_info['id'], $type);

			}else{
				if($type == 0){
					//没有踩过,不能取消点踩
					Util::echo_format_return(_SUCCESS_, $this->get_answer_info($like_info['mark']));
					exit;
				}
				if($type == 1 && $like_info3['status'] == 1) {
					//已经赞过了，点赞数-1   反对数+1
					$this->answer_model->add_mark_up_count($newsid,$guid,-1);//赞数－1
					$this->like_model->updateLikeData($like_info3['id'], 0);


					// 消息模块
					$this->load->model('push_message_model');
					$_push_type = 2;	// 答案
					$_push_flag = 3;	// 赞
					$_push_mark = $newsid;
					$this->push_message_model->push($_push_type, $_push_flag, $_push_mark, -1);
				}
				//插入该用户点赞情况
				$datas['mark'] = $newsid;
				$datas['user_id'] = $guid;
				$datas['type'] = '4';//答案踩／答案反对
				$datas['status'] = '1';
				$datas['weight_level'] = $weight_level;
				$datas['partner_id'] = $partner_id;
				$datas['create_time'] = time();
				$datas['update_time'] = time();
				$this->like_model->insertLikeData($datas);

			}
			//累加点赞数
			if($type == 1){
				$count = 1;
			}else{
				$count = -1;
			}
			$this->answer_model->add_mark_down_count($newsid,$guid,$count);

			$like_infoss = $this->like_model->get_info($guid,$newsid,4);

			Util::echo_format_return(_SUCCESS_, $this->get_answer_info($like_infoss['mark']),'操作成功');
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}

	//获取答案状态
	private function get_answer_info($id){
		$this->answer_model->_clear_info($id);
		$answer_info = $this->answer_model->get_info($id);
		$data = array();
		$data['agreeCount'] = $answer_info['mark_up_rank_0_count'] + $answer_info['mark_up_rank_1_count'] + $answer_info['mark_up_virtual_count'];
		$data['combatCount'] = $answer_info['mark_down_rank_0_count'] + $answer_info['mark_down_rank_1_count'];

		return $data;
	}

	//问题或答案举报
	public function complaint_add($mark,$target,$type)
	{
		$res = $this->global_func->inject_check($mark);
		if($res){
			exit('分类参数含有非法字符');
		}
		$mark = trim($mark);
		$target = $this->global_func->filter_int($target);
		$type = $this->global_func->filter_int($type);

		try {
			if($mark == '') {
				throw new Exception('ID不能为空', _PARAMS_ERROR_,'举报失败');
			}

			//引入必要model类
			$this->load->model('complaint_model');
			$this->load->model('answer_model');
			$this->load->model('question_model');

			$data['mark'] = trim($mark);
			$data['type'] = $target ? 2 : 1;
			$data['content_type'] = $type ? $type : 5;
			$data['create_time'] = time();
			$this->complaint_model->insertComplaintData($data);
			if($target == 1){
				//累加举报的答案数
				$this->answer_model->add_complaint_count($mark);
			}else{
				//累加举报的问题数
				$this->question_model->add_complaint_count($mark);
			}

			Util::echo_format_return(_SUCCESS_, '','举报成功');
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}

	//获取游戏列表接口
	public function getgame_list_api(){
		$name = (string)trim( $this->input->get('inname',true) );

		$res = $this->global_func->inject_check($name);
		if($res){
			throw new Exception('参数错误', _PARAMS_ERROR_);
		}

	    try{
			//引入搜索类
			$this->load->model('Search_Model', 'Search');
			$data = $this->Search->searchGame( $name, 1, 5);

			// if(empty($data) || !is_array($data)){
			// 	throw new Exception('没有数据', _PARAMS_ERROR_);
			// }

	        Util::echo_format_return(_SUCCESS_, $data?$data:array());
	        return 1;
	    }catch (Exception $e) {
	        Util::echo_format_return($e->getCode(), array(), $e->getMessage());
	        return 0;
	    }
	}


	/**
	 * 获取问题详情中答案列表
	 *
	 * @param int $offsize $_GET
	 * @param int $page_size $_GET
	 * @return json
	 */
	public function get_qa_list_api( $qid, $offsize = 2, $page_size = 10){
	    $offsize = $this->global_func->filter_int($offsize);
	    $page_size = $this->global_func->filter_int($page_size);

	    $offsize = ($offsize - 1) * $page_size;
	    //载入必要model类
	    $this->load->model('user_model');
	    $this->load->model('answer_model');
	    $this->load->model('answer_content_model');
	    $this->load->model('qa_image_model');
	    $this->load->model('qa_model');
	    $this->load->model('follow_model');
	    $this->load->model('like_model');

	    try{

	        $answer_info = $this->answer_model->get_list($qid, $offsize, $page_size);
	        foreach ($answer_info as $k => $v) {
	            //用户信息
	            $answer_info[$k]['u_info'] = $this->user_model->getUserInfoById($v['uid']);
	            //答案内容
	            $answer_info[$k]['a_content'] = $this->answer_content_model->get_content($v['aid']);
	            $answer_info[$k]['content'] = $this->answer_content_model->get_content($v['aid']);

	            if( (strlen($answer_info[$k]['u_info']['nickname'])/3) > 10){
	                $answer_info[$k]['u_info']['nickname'] = $this->qa_model->convert_content_to_frontend($answer_info[$k]['u_info']['nickname'], 10, 1)."...";
	            }else{
	                $answer_info[$k]['u_info']['nickname'] = $this->qa_model->convert_content_to_frontend($answer_info[$k]['u_info']['nickname'], 0, 1);
	            }
	            if( (strlen($answer_info[$k]['content'])/3) > 100){
	                $answer_info[$k]['content']= $this->qa_model->convert_content_to_frontend($answer_info[$k]['content'], 100, 1)."......";//展开前数据：替换特殊符号、截取100个字符
	                $answer_info[$k]['more_content']=1;
	            }else{
	                $answer_info[$k]['content']= $this->qa_model->convert_content_to_frontend($answer_info[$k]['content'], 0, 1);//展开前数据：替换特殊符号
	            }

	            $answer_info[$k]['a_content'] = $this->qa_image_model->changeImgStr($answer_info[$k]['a_content']);//展开后数据

	            $answer_info[$k]['updateType'] = $v['update_time'] == $v['create_time'] ? 0 : 1;//0发布；1编辑
	            $answer_info[$k]['a_img_count'] = $this->qa_image_model->get_list_count(2, $v['aid'], 1);
	            $answer_info[$k]['hasCollect'] = (boolean) $this->follow_model->is_follow($this->user_id, 2, $v['aid']);//是否收藏
	            $answer_info[$k]['hasAgree'] = (boolean)$this->like_model->is_like($v['aid'], 3);
	            $answer_info[$k]['hasCombat'] = (boolean)$this->like_model->is_like($v['aid'], 4);

	            $answer_info[$k]['agreeCount'] = $v['mark_up_rank_0_count'] + $v['mark_up_rank_1_count'] + $v['mark_up_virtual_count'];//点赞数
	            $answer_info[$k]['ctime'] = Util::from_time($v['update_time']);
	            $image_list = $this->qa_image_model->get_list(2, $v['aid']);
	            // $answer_info[$k]['attribute']['images'] = $image_list['0']['url'] ? IMAGE_URL_PRE .$image_list['0']['url'] : '';
	            $answer_info[$k]['attribute']['images'] = gl_img_url($image_list['0']['url']);
	        }

	        $data['data'] = $answer_info;
	        $enoughflag = empty($data['data'][0]['content']) ? 1 : 2;

	        $returndata = array(
	            'data' => $data,
	            'enoughflag' => $enoughflag
	        );
	        Util::echo_format_return(_SUCCESS_, $returndata?$returndata:array());
	        return 1;
	    }catch (Exception $e) {
	        Util::echo_format_return($e->getCode(), array(), $e->getMessage());
	        return 0;
	    }
	}

	/**
	 * 搜索页分页列表
	 *
	 * @param int $offsize $_GET
	 * @param int $page_size $_GET
	 * @return json
	 */
	public function get_search_list_api( $search_keyword,$type, $page = 2, $page_size = 10){
	    $page = $this->global_func->filter_int($page);
	    $page_size = $this->global_func->filter_int($page_size);

	    $page = $page < 1 ? 1 : $page;
	    $page_size = ($page_size < 1 || $page_size > 50) ? 10 : $page_size;

	    //载入必要model类
	    $this->load->model('Search_Model');

	    try{
	        if(!empty($search_keyword)){
	            switch ($type){
	                case 4:
	                    $result['data']['raiders']	=	$this->Search_Model->searchNews( $search_keyword, $related_game, $page, $page_size, $this->platform, $node_time );
	                    break;
	                case 6:
	                    $result['data']['question']	=	$this->Search_Model->searchQuestions( $search_keyword, $related_game, $page, $page_size, $this->platform, $node_time);
	                    break;
	                default:
	                    $result['data']['raiders']	=	$this->Search_Model->searchNews( $search_keyword, $related_game, $page, $page_size, $this->platform, $node_time );
	            }
	        }
	        if($result['data']['question']['resultList'][0]['absId']){
	            foreach ($result['data']['question']['resultList'] as $k => $v){
	                $result['data']['question']['resultList'][$k]['answerCount'] = $v['answerCount'] ? (int)$v['answerCount'] : 0;
	            }
	        }
	        if($type == 4){
	           $data['data'] = $result['data']['raiders']['resultList'];
	        }elseif($type == 6){
	           $data['data'] = $result['data']['question']['resultList'];
	        }

	        $enoughflag = empty($data['data']) ? 1 : 2;

	        $returndata = array(
	            'data' => $data['data'],
	            'enoughflag' => $enoughflag
	        );
	        Util::echo_format_return(_SUCCESS_, $returndata?$returndata:array());
	        return 1;
	    }catch (Exception $e) {
	        Util::echo_format_return($e->getCode(), array(), $e->getMessage());
	        return 0;
	    }
	}


	/**
	 * 添加游戏
	 * @return json
	 */
	public function add_game( $gameName = ''){
    	$gameName = $gameName?$gameName:$this->input->get ( 'gameName', true );

	    $this->load->model('game_other_model');

    	$otherGameInfo = $this->game_other_model->get_info($gameName);
    	if($otherGameInfo['id']){
    	    //update
    	    $this->game_other_model->updateData($otherGameInfo['id'],1);
    	}else{
    	    //insert
    	    $data = array();
    	    $data['game_name'] = $gameName;
    	    $data['add_num'] = 1;
    	    $data['create_time'] = time();

    	    $this->game_other_model->insertData($data);
    	}
        $enoughflag = 2;
        $returndata = array(
            'data' => '1',
            'enoughflag' => $enoughflag
        );
	    Util::echo_format_return(_SUCCESS_, $returndata?$returndata:array());
	}
	/**
	 * 清空单个搜索记录
	 * @return json
	 */
	public function clearSearchOne($key){
	    $key = $this->global_func->filter_int($key);
	    $cookie_search_array = json_decode($_COOKIE['search_history_keywords'],true);
	    unset($cookie_search_array[$key]);

        setcookie('search_history_keywords',json_encode($cookie_search_array),time()+'31536000','/',$this->config->item('domain'));

	}

	/**
	 * 清空全部搜索记录
	 * @return json
	 */
	public function clearSearchAll(){
	    setcookie('search_history_keywords','','-1','/',$this->config->item('domain'));
	}

	//删除答案方法
	public function answer_del($aid){
	    $aid = $this->global_func->filter_int($aid);
	    $uid = $this->user_id;

        if (empty($uid) || empty($aid) || !is_numeric($aid)) {
            $returndata = array(
                'data' => 'error',
                'enoughflag' => 1
            );
           Util::echo_format_return(1001, $returndata?$returndata:array());
           exit;
        }
        $this->load->model('common_model');
        $this->load->model('qa_model');

        $this->qa_model->answer_del($aid, $uid);

        $returndata = array(
            'data' => 'ok',
            'enoughflag' => 2
        );
       Util::echo_format_return(_SUCCESS_, $returndata?$returndata:array());
       exit;

	}

	/**
	 * 获取搜搜历史记录
	 *
	 */
	public function get_search_history_api($type){

        $cookie_search_array = json_decode($_COOKIE['search_history_keywords'],true);
        if(!empty($cookie_search_array)){
            krsort($cookie_search_array);
            $cookie_search_array = array_unique($cookie_search_array);
        }
        $html = '';
        if(!empty($cookie_search_array)){
            $html .='<div class="search_clear">';
            $html .='<i>搜索历史</i>';
            $html .='<a href="#" class="clear_butAll" onclick="doPop();" style="color: #ff0000">清空记录</a>';
            $html .='</div>';
            foreach($cookie_search_array as $k => $v){
                $html .='<div id="search_w'.$k.'" class="search_clear">';
                $html .='<a href="/search/index?search_keyword='.$v.'&type='.$type.'" class="seach_w">'.$v.'</a>';
                $html .='<a href="#" class="clear_but" onclick="clearSearch('.$k.')">x</a>';
                $html .='</div>';
            }
            $enoughflag = 2;
        }else{
            $enoughflag=1;
        }
        $returndata = array(
            'data' => $html,
            'enoughflag' => $enoughflag
        );
        Util::echo_format_return(_SUCCESS_, $returndata?$returndata:array());
	}
}

/* End of file user.php */
/* Location: ./application/controllers/api/user.php */
