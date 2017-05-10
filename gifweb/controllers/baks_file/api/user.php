<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * API-用户操作
 *
 * @author haibo8, <haibo8@staff.sina.com.cn>
 * @version   $Id: user.php 2015-07-20 14:52:27 haibo8 $
 * @copyright (c) 2015 Sina Game Team.
 */
class User extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		/*No Cache*/
		$this->output->set_header("Cache-Control: no-cache, must-revalidate");
		$this->output->set_header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
		$this->output->set_header("Pragma: no-cache");
		$this->load->model('User_Model', 'User');
		$this->load->model('Common_model','Comm');
		$this->load->model('Follow_model','Follow');
		$this->load->model('Article_model','Article');
		$this->load->model('Question_model','Question');
		$this->load->model('Question_content_model','Question_content');
		$this->load->model('Answer_model','Answer');
		$this->load->model('Answer_content_model','Answer_content');
		$this->load->model('Qa_model','Qa');
		$this->load->model('Bg_img_model');
		$this->load->model('User_balance_model');
		$this->load->driver('cache');
	}

	/**
	 * APP用户登录
	 *
	 * @param int $uid $_GET
	 * @param int $token $_GET
	 * @return json
	 */
	public function appLogin(){
		$this->load->library ( 'SSOServer/SSOServer','ssosever' );

        $type	  = intval( $this->input->get('type',true) );
		$user_key  = trim( $this->input->get('userid',true) );	// 用户第三方唯一标识
		$third_token = trim( $this->input->get('accesstoken',true) );
		$wx_unionid  = trim( $this->input->get('unionid',true) );	// 微信unionid
		$name = trim( $this->input->get('name',true) );
		$head_url = trim( $this->input->get('headUrl',true) );
		$birthday = trim( $this->input->get('birthday',true) );
		$gender	  = intval( $this->input->get('sex',true) );
		$sys_plat = trim( $this->input->get('platform',true) );
		$device_id = trim( $this->input->get('deviceId',true) );
		$result = array('result'=>200,'message'=>'登录成功', 'data'=>'');

		// 短信验证码发送错误次数超过10次，此号码禁止1小时
		if($type == 1){
			$forbid_failed = $this->cache->redis->get("glapp:users:msg:failed_forbid1_".$user_key);
			if(intval($forbid_failed) == 1){
				Util::echo_format_return(_USER_TOKEN_ERROR_, '', '您因频繁提交错误验证码被禁1小时', true);
				exit;
			}
		}


		/*验证token信息*/
		$rs = $this->User->verUser($user_key, $third_token, $type, $wx_unionid);

		// 短信测试
		// 苹果审核用
		if($type == 1 && $third_token == '9999' && $user_key == '15930453210'){
			$rs = true;
		}
		
		if($rs == false){
			$result['message']= $type == 1 ? '验证码错误' : '登录失败';
			Util::echo_format_return(_USER_TOKEN_ERROR_, '', $result['message'], true);
			exit;
		}
		/* 获取用户信息 */
		$uid = $this->User->getUidByOpenid($user_key, $type, $wx_unionid);

		if($uid > 0){
			$userinfo = $this->User->getUserInfoById( $uid );
			// 用户已被删除，清理缓存信息
			if(intval($userinfo['uid']) < 1){
				$this->User->clearUserCache($uid, $user_key, $type);
				Util::echo_format_return(_USER_NOT_EXIST_, '', '用户不存在', true);
				exit;
			}

			//更新用户登录信息
			$data['login_time'] = time();
			$data['sys_plat']	= $sys_plat;
			$data['device_id']	= $device_id;
			$data['login_ip']	= $this->global_func->get_remote_ip();
			$this->User->update_user($userinfo['uid'], $data);
		}else{
			//检查第三方用户昵称是否已存在
			if(!empty($name) && $name != NULL){
				$is_exist = $this->User->_check_nickname($name);
				if($is_exist){
					$name = $this->global_func->strcut($name, 18, '');
					$name .= $this->Comm->get_code(4,1);
				}
			}else{
				$name = $this->User->getRandNick();
			}

			//注册新用户基本信息
			$data['create_time'] = time();
			$data['login_time'] = time();
			if($type == 5){
			     $data['nickname']	= 'mi'.$user_key;
			}else{
			     $data['nickname']	= $name;
			}
			$data['avatar']		= empty($head_url) ? "http://tp1.sinaimg.cn/5659179515/50/0/1" : $head_url;
			$data['birthday']	= empty($birthday) ? 0 : strtotime($birthday);
			$data['gender']		= $gender;
			$data['sys_plat']	= $sys_plat;
			$data['device_id']	= $device_id;
			$data['login_ip']	= $this->global_func->get_remote_ip();
			if($type == 1){
				$data['mobile'] = $user_key;
			}

			// 用户登录来源信息
			$thirdData['ch'] = $type;
			$thirdData['open_id'] = $user_key;
			$thirdData['token']	  = $third_token;
			$thirdData['unionid'] = $rs['unionid'];

			$userinfo = $this->User->add_user($data, $thirdData);
		}

		if(!$userinfo || $userinfo['uid'] < 1){
			Util::echo_format_return(_USER_NOT_EXIST_, '', '用户id不存在', true);
			exit;
		}

		// 生成用户登录token信息
		$res = $this->ssoserver->rsaApp($userinfo);

		// 登录成功
		$appdata['type'] = $type;
		$appdata['guid'] = "".$userinfo['uid']."";
		$appdata['gtoken'] = $this->global_func->urlsafe_b64encode( $res['gtoken'] );
		$appdata['deadline'] = "".$res['deadline']."";
		$appdata['name'] = $userinfo['nickname'];
		$appdata['headUrl'] = $userinfo['avatar'];
		$appdata['birthday'] = date('Y-m-d H:i:s',intval($userinfo['birthday']));
		$appdata['sex'] = intval($userinfo['gender']);
		$appdata['uLevel']	=	$this->User->getLevel($userinfo['exps'], $userinfo['virtual_exps']);
		$appdata['medaLevel']=	intval($userinfo['rank']);

		$result['data'] = $appdata;

		// 记录该设备登录时间
		$this->User->recordLoginTime($device_id, $userinfo['uid']);

		// 短信验证码登录成功,去除短信60秒发送限制
		if($type == 1){
			$succKey = sha1("sendDownloadMsg_success_".$user_key);
			$this->cache->redis->delete($succKey);
			$this->cache->redis->delete('glapp:users:login_code_'.$user_key);
		}

		if (intval($userinfo['status']) == 1) {
			Util::echo_format_return(_USER_BANNED_, $result['data'], _BANNED_MSG_, true);
			die();
		}

		Util::echo_format_return(_SUCCESS_, $result['data'], $result['message'], true);
		exit;

	}

	/**
	 * 判断用户APP登录
	 *
	 * @param int $uid $_GET
	 * @param int $token $_GET
	 * @return json
	 */
	public function isLogin(){
		$result = array('result'=>1001, 'message'=>'参数有误');
		$uid = trim( $this->input->get('guid',true) );
		$device_id = trim( $this->input->get('deviceId',true) );

		// 验证上一次登录时间
		$last = $this->User->checkLoginTime($device_id, $uid);
		if($last == false){
			$result['message'] = '登录过期,请重新登录';
			Util::echo_format_return($result['result'], $result['data'], $result['message'], true);
		}

		if($this->user_id){
			$result['data']	  = $this->userinfo;
			$result['result'] = $this->common_model->is_ban_user() ? _USER_BANNED_ : _SUCCESS_;
			$result['message'] = $this->common_model->is_ban_user() ? _BANNED_MSG_ : '登录成功';
		}

		Util::echo_format_return($result['result'], $result['data'], $result['message'], true);
	}

	/**
	 * 获取用户web登录cookie信息
	 *
	 * @return json
	 */
	public function getWepCookie(){
		$this->load->library ( 'SSOServer/SSOServer','ssosever' );
		$result = array('result'=>1001,'message'=>'未登录', 'data'=>'');
		$expires = time() + 3600 * 24 * 365;
		if($this->user_id){
			$rs = $this->ssoserver->rsaWeb($this->userinfo);
			if($rs['GSUP'] && $rs['GSUE']){
				$data['.wan68.com'][0] = array('name'=>'GSUP', 'value'=>$rs['GSUP'], 'expires'=>(string)$expires);
				$data['.wan68.com'][1] = array('name'=>'GSUE', 'value'=>$rs['GSUE'], 'expires'=>(string)$expires);

				$data['.sina.com.cn'][0] = array('name'=>'GSUP', 'value'=>$rs['GSUP'], 'expires'=>(string)$expires);
				$data['.sina.com.cn'][1] = array('name'=>'GSUE', 'value'=>$rs['GSUE'], 'expires'=>(string)$expires);

				$result['message'] = '操作成功';
				$result['result'] = 200;
				$result['data'] = $data;
				Util::echo_format_return($result['result'], $result['data'], $result['message'], true);
			}else{
				$result['message'] = '获取cookie信息失败';
				Util::echo_format_return(_SUCCESS_, $result['data'], $result['message'], true);
			}
		}

		Util::echo_format_return(_SUCCESS_, $result['data'], $result['message'], true);
	}

	/**
	 * 获取用户登录验证码
	 *
	 * @param int $uid $_GET
	 * @param int $token $_GET
	 * @return json
	 */
	public function getVcode(){
		$phone	= trim( $this->input->get('phone' , TRUE) );
		$result = array('result'=>1001, 'message'=>'参数有误');
		$ip = $this->global_func->get_remote_ip();
		
		/*
		if( !isset( $_SERVER['HTTP_REFERER'] ) || (
			!preg_match( "/^http:\/\/www.wan68.com\//" , $_SERVER['HTTP_REFERER'] ) &&
			!preg_match( "/^http:\/\/gl.games.sina.com.cn\//" , $_SERVER['HTTP_REFERER'] )
		) ){
			$result['message'] = '无效域名请求';
			die( json_encode($result) );
		}
		*/

		//本地测试停发验证码，直接通过
// 		Util::echo_format_return(200, 9999, '验证成功', true);
// 		exit;


		// 手机号码验证
		if(!preg_match("/1[34578]{1}\d{9}$/",$phone)){
			$result['message'] = '手机号格式有误';
			Util::echo_format_return(_DATA_ERROR_, $result['data'], $result['message'], true);
		}

		// mc缓存key
		$mKey = "glapp:users:msg:send_".$phone;
		$forbidKey = "glapp:users:msg:forbidden_".$phone;
		$succKey = sha1("sendDownloadMsg_success_".$phone);

		// 判断是否频繁发送
		$is_forbid = intval( $this->cache->redis->get($forbidKey) );
		if($is_forbid == 1){
			$result['message'] = '操作太频繁，两小时后再来试试吧';
			Util::echo_format_return(_DATA_ERROR_, $result['data'], $result['message'], true);
		}

		// 限制同一电话号码发送频率
		$tNum = $this->cache->redis->get($mKey);
		if(intval($tNum) < 9){
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
			Util::echo_format_return(_DATA_ERROR_, $result['data'], $result['message'], true);
		}

		// 判断是否已发送成功，成功发送后60秒内不得重发
		$is_succ = intval( $this->cache->redis->get($succKey) );
		if($is_succ == 1){
			$result['message'] = '60秒后再重发哦';
			Util::echo_format_return(_DATA_ERROR_, $result['data'], $result['message'], true);
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
		$res = true;
		if($res == true){
			$result['result']  = '200';
			$result['data'] = array('validtime'=>'60');
			$result['message'] = '短信发送成功';
			// 发送成功,加入60秒缓存
			$this->cache->redis->save($succKey, 1, 60);
		}else{
			$result['result']  = _DATA_ERROR_;
			$result['message'] = '验证码发送失败';
			$this->cache->redis->delete( $redisKey );
		}

		Util::echo_format_return($result['result'], $result['data'] , $result['message'], true);
	}

	/**
	 * 获取用户信息
	 *
	 */
	public function getUserInfo()
	{
		$types	  = $this->input->get('type',true);
		try {
			if(!$this->user_id) {
				throw new Exception('用户未登录', _PARAMS_ERROR_);
			}

			if(!$types && $types != 0){
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}

			$user_info = $this->userinfo;

			$level = $this->User->getLevel($user_info['exps'],$user_info['virtual_exps']);
			$uInfo = array();

			$types = explode(',',$types);

			//循环处理
			foreach($types as $type){
				$type = (int)$type;

				if($type == 0 || $type == 1){
					$uInfo['name'] = $user_info['nickname'] ? $user_info['nickname'] : '';
					$uInfo['headUrl'] = $user_info['avatar'] ? $user_info['avatar'] : '';
					$uInfo['birthday'] = $user_info['birthday'] ? date('Y-m-d H:i:s', $user_info['birthday']) : '';
					$uInfo['sex'] = (int)$user_info['gender'];

					//增加用户背景图信息for app2.0 by wangbo8
					$user_balance_info = $this->User_balance_model->getInfo($this->user_id);
					$bgid = $user_balance_info['big_img_id'];

					//再获取用户背景图url
					$bg_img_info = $this->Bg_img_model->get_bgimg($bgid);
					$uInfo['bgImg'] = $bg_img_info['img_url'] ? $bg_img_info['img_url'] : 'http://n.sinaimg.cn/973/app/bg/info_bg@2x.png';
				}
				if($type == 0 || $type == 2){
					$levelExps = $this->User->getNLevelExps($level);
					$uInfo['uLevel'] = $level;
					$uInfo['medalLevel'] = (int) $user_info['rank'];
					$uInfo['totalExperience'] = (int) ($user_info['exps'] + $user_info['virtual_exps']);
					$uInfo['nextLevelExperience'] = (int) $levelExps['max'];
					$uInfo['currentLevelExperience'] = (int) $levelExps['min'];
				}

				if($type == 0 || $type == 3){
					$this->load->model('user_message_model');
					$uInfo['newMsgCount'] = (int)$this->user_message_model->get_unread_count($this->user_id, $user_info['get_message_time']);
				}

				if ($type == 0 || $type == 1 || $type == 2 || $type == 3 || $type == 4) {
					// 推送开关
					$this->load->model('push_model');
					$push_info = $this->push_model->get_info_by_uid($this->user_id);
					$uInfo['pushState'] = $push_info ? $push_info['switch'] : '11001';
				}

				if ($type == 0 || $type == 1 || $type == 2 || $type == 3 || $type == 5) { //关注其他用户个数 for app2.0 by wangbo8
					$this->load->model('friend_model');

					//获取用户关注其他用户个数
					$friends_cnt_info = $this->friend_model->get_friends_cnt($this->user_id);
					$uInfo['totalAttenCount'] = (int)$friends_cnt_info['my_follow'];
				}

				if ($type == 0 || $type == 1 || $type == 2 || $type == 3 || $type == 6) { //用户的粉丝个数 for app2.0 by wangbo8
					$this->load->model('friend_model');

					//获取用户关注其他用户个数
					$friends_cnt_info = $this->friend_model->get_friends_cnt($this->user_id);
					$uInfo['totalFansCount'] = (int)$friends_cnt_info['follow_me'];
				}

				if ($type == 0 || $type == 1 || $type == 2 || $type == 3 || $type == 7) { //打赏金额信息 for app2.0 by wangbo8
					//打赏金额信息for app2.0 by wangbo8
					$user_balance_info = $this->User_balance_model->getInfo($this->user_id);
					$uInfo['currentCash'] = $user_balance_info['balance'] ? $user_balance_info['balance'] / 100 : 0;
					$uInfo['income'] = $user_balance_info['total_earning'] ? $user_balance_info['total_earning'] / 100 : 0;
					$uInfo['expenditure'] = $user_balance_info['total_paying'] ? $user_balance_info['total_paying'] / 100 : 0;
					$uInfo['expenditurebycash'] = $user_balance_info['balance_reward_total'] ? $user_balance_info['balance_reward_total'] / 100 : 0; //by long
					
				}

				if ($type == 0 || $type == 1 || $type == 2 || $type == 3 || $type == 8) { //支付宝账户信息 for app2.0 by wangbo8
					//打赏金额信息for app2.0 by wangbo8
					$user_balance_info = $this->User_balance_model->getInfo($this->user_id);
					$uInfo['payAccount'] = $user_balance_info['alipay_account'] ? $user_balance_info['alipay_account'] : '';
					$uInfo['payRealName'] = $user_balance_info['alipay_name'] ? $user_balance_info['alipay_name'] : '';
					$uInfo['payQrcode'] = $user_balance_info['alipay_qcode'] ? $user_balance_info['alipay_qcode'] : '';
				}
				
				if ($type == 0 || $type == 1 || $type == 2 || $type == 3 || $type == 9) { //当前被打赏总数  by long
					$this->load->model('order_model');
					$newRewardCount = $this->order_model->getTotalCashCount($this->user_id ,2);
					$uInfo['newRewardCount'] = $newRewardCount ? $newRewardCount : 0;
				}
			}

			// 给予商务活动的支持， 2015.11.3，预计持续一个月
			$uid = $this->user_id;
			$user_channel = $this->User->get_channel_info_by_uid($uid);
			if ($user_channel['wb']) {
				$this->load->model('event_weibo_user_device_model');
				$device_id = $this->input->get_post('deviceId');
				$weibo_uid = $user_channel['wb'];
				$this->event_weibo_user_device_model->add($weibo_uid, $device_id);
			}

			$uInfo = $uInfo ? $uInfo : array();
			Util::echo_format_return(_SUCCESS_, $uInfo);
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}

	/*
	 * 已收藏攻略列表
	 */
	public function collect_gl()
	{
		$page	  	= $this->input->get('page',true) ? $this->input->get('page',true) : 1;
		$count	  	= $this->input->get('count',true) ? $this->input->get('count',true) : 10;
		$max_id	  	= trim ( $this->input->get('max_id',true) );
		$start 		= ($page==1) ? 0 : ($page-1) * $count;
		try {
			if(!$this->user_id) {
				throw new Exception('用户未登录', _PARAMS_ERROR_);
			}

			$returns = $this->getFollowInfo(1,$start,$count,$max_id);
			foreach ($returns as $k=>$v){
				$article[$k] = $this->Article->findArticleData($v['mark']);
				$data[$k]['absId'] = (string) $v['mark'];
				$data[$k]['abstitle'] = trim($article[$k]['title'])?trim($article[$k]['title']):'';
				$data[$k]['updateTime'] = $v['update_time'] ? date('Y-m-d H:i:s',$v['update_time']) : '';
			}
			$data = $data ? $data : array();
			Util::echo_format_return(_SUCCESS_, $data);
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}

	/*
	 * 获取其他用户收藏的攻略列表     2.0版本新增
	 */
	public function other_collect_gl()
	{
	    $page	  	= $this->input->get('page',true) ? $this->input->get('page',true) : 1;
	    $count	  	= $this->input->get('count',true) ? $this->input->get('count',true) : 10;
	    $uid	  	= $this->input->get('uid',true) ? $this->input->get('uid',true) : '';
	    $max_id	  	= trim ( $this->input->get('max_id',true) );
	    $start 		= ($page==1) ? 0 : ($page-1) * $count;
	    try {
	        if($uid =='') {
	            throw new Exception('用户id不能为空', _PARAMS_ERROR_);
	        }
	
	        $returns = $this->getFollowInfo(1,$start,$count,$max_id,$uid);
	        foreach ($returns as $k=>$v){
	            $article[$k] = $this->Article->findArticleData($v['mark']);
	            $data[$k]['absId'] = (string) $v['mark'];
	            $data[$k]['abstitle'] = trim($article[$k]['title'])?trim($article[$k]['title']):'';
	            $data[$k]['updateTime'] = $v['update_time'] ? date('Y-m-d H:i:s',$v['update_time']) : '';
	        }
	        $data = $data ? $data : array();
	        Util::echo_format_return(_SUCCESS_, $data);
	    }catch (Exception $e) {
	        Util::echo_format_return($e->getCode(), array(), $e->getMessage());
	    }
	}
	
	/*
	 * 我关注的问题列表
	 */
	public function attention_questions()
	{
		$page	  	= $this->input->get('page',true) ? $this->input->get('page',true) : 1;
		$count	  	= $this->input->get('count',true) ? $this->input->get('count',true) : 10;
		$max_id	  	= trim ( $this->input->get('max_id',true) );
		$start 		= ($page==1) ? 0 : ($page-1) * $count;
		try {
			if(!$this->user_id) {
				throw new Exception('用户未登录', _PARAMS_ERROR_);
			}

			$returns = $this->getFollowInfo(4,$start,$count,$max_id);

			foreach ($returns as $k=>$v){
				$question[$k] = $this->Question->get_info($v['mark'], array(0,1,2));
				if (empty($question[$k]) || ($question[$k]['uid'] != $this->user_id && $question[$k]['status'] == 2 )) {
					continue;
				}

				$question_content[$k] = $this->Question_content->get_content($v['mark']);
				$arr = array();
				$arr['absId'] = (string) $v['mark'];
				$arr['abstitle'] = trim($question_content[$k])? $this->Qa->convert_content_to_frontend(trim($question_content[$k]),100) : '';
				$arr['answerCount'] = (int) $question[$k]['normal_answer_count'];
				$arr['status'] = $question[$k]['status'] >= 2 ? 1 : 0;
				$arr['updateTime'] = $v['update_time'] ? date('Y-m-d H:i:s',$v['update_time']) : '';

				$data[] = $arr;
			}

			$data = $data ? $data : array();
			Util::echo_format_return(_SUCCESS_, $data);
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}

	/*
	 * 获取其他用户关注的问题列表     2.0版本新增
	 */
	public function other_attention_questions()
	{
	    $page	  	= $this->input->get('page',true) ? $this->input->get('page',true) : 1;
	    $count	  	= $this->input->get('count',true) ? $this->input->get('count',true) : 10;
	    $uid	  	= $this->input->get('uid',true) ? $this->input->get('uid',true) : '';
	    $max_id	  	= trim ( $this->input->get('max_id',true) );
	    $start 		= ($page==1) ? 0 : ($page-1) * $count;
	    try {
	        if($uid =='') {
	            throw new Exception('用户id不能为空', _PARAMS_ERROR_);
	        }
	
	        $returns = $this->getFollowInfo(4,$start,$count,$max_id,$uid);
	
	        foreach ($returns as $k=>$v){
	            $question[$k] = $this->Question->get_info($v['mark'], array(0,1));
	            if (empty($question[$k]) || ($question[$k]['uid'] != $uid && $question[$k]['status'] == 2 )) {
	                continue;
	            }
	
	            $question_content[$k] = $this->Question_content->get_content($v['mark']);
	            $arr = array();
	            $arr['absId'] = (string) $v['mark'];
	            $arr['abstitle'] = trim($question_content[$k])? $this->Qa->convert_content_to_frontend(trim($question_content[$k]),100) : '';
	            $arr['answerCount'] = (int) $question[$k]['normal_answer_count'];
	            $arr['status'] = $question[$k]['status'] >= 2 ? 1 : 0;
	            $arr['updateTime'] = $v['update_time'] ? date('Y-m-d H:i:s',$v['update_time']) : '';
	
	            $data[] = $arr;
	        }
	
	        $data = $data ? $data : array();
	        Util::echo_format_return(_SUCCESS_, $data);
	    }catch (Exception $e) {
	        Util::echo_format_return($e->getCode(), array(), $e->getMessage());
	    }
	}
	/*
	 * 我收藏的答案列表
	 */
	public function collect_answers()
	{
		$page	  	= $this->input->get('page',true) ? $this->input->get('page',true) : 1;
		$count	  	= $this->input->get('count',true) ? $this->input->get('count',true) : 10;
		$max_id	  	= trim ( $this->input->get('max_id',true) );
		$start 		= ($page==1) ? 0 : ($page-1) * $count;
		try {
			if(!$this->user_id) {
				throw new Exception('用户未登录', _PARAMS_ERROR_);
			}
			$returns = $this->getFollowInfo(2,$start,$count,$max_id);
			foreach ($returns as $k=>$v){
				$content_info[$k] = $this->Answer_content->get_content($v['mark']);
				$content[$k] = $this->Answer->get_info($v['mark']);
				$content_q[$k] = $this->Question->get_info($content[$k]['qid'],array(0,1,2));
				$content_qc[$k] = $this->Question_content->get_content($content[$k]['qid']);
				$questionInfo[$k]['absId'] = (string) $content_q[$k]['qid'];
				$questionInfo[$k]['status'] = (string) $content_q[$k]['status'] >= 2 ? 1 : 0;
				$questionInfo[$k]['abstitle'] = trim($content_qc[$k]) ? $this->Qa->convert_content_to_frontend(trim($content_qc[$k]),200) : '';
				$questionInfo[$k]['answerCount'] = (int)$content_q[$k]['normal_answer_count'];
				$data[$k]['absId'] = (string) $v['mark'];
				$data[$k]['abstitle'] = trim($content_info[$k]) ? $this->Qa->convert_content_to_frontend(trim($content_info[$k]),200) : '';
				$data[$k]['updateTime'] = $v['update_time'] ? date('Y-m-d H:i:s',$v['update_time']) : '';
				$data[$k]['status'] = $content[$k]['status'] >= 2 ? 1 : 0;
				$data[$k]['questionInfo'] = $questionInfo[$k];
			}
			$data = $data ? $data : array();
			Util::echo_format_return(_SUCCESS_, $data);
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}

	/*
	 * 获取其他用户收藏的答案列表      2.0版本新增
	 */
	public function other_collect_answers()
	{
	    $page	  	= $this->input->get('page',true) ? $this->input->get('page',true) : 1;
	    $count	  	= $this->input->get('count',true) ? $this->input->get('count',true) : 10;
	    $uid	  	= $this->input->get('uid',true) ? $this->input->get('uid',true) : '';
	    $max_id	  	= trim ( $this->input->get('max_id',true) );
	    $start 		= ($page==1) ? 0 : ($page-1) * $count;
	    try {
	        if( $uid =='') {
	            throw new Exception('uid不能为空', _PARAMS_ERROR_);
	        }
	        $returns = $this->getFollowInfo(2,$start,$count,$max_id,$uid);
	        foreach ($returns as $k=>$v){
	            $content_info[$k] = $this->Answer_content->get_content($v['mark']);
	            $content[$k] = $this->Answer->get_info($v['mark']);
	            $content_q[$k] = $this->Question->get_info($content[$k]['qid'],array(0,1));
	            $content_qc[$k] = $this->Question_content->get_content($content[$k]['qid']);
	            $questionInfo[$k]['absId'] = (string) $content_q[$k]['qid'];
	            $questionInfo[$k]['status'] = (string) $content_q[$k]['status'] >= 2 ? 1 : 0;
	            $questionInfo[$k]['abstitle'] = trim($content_qc[$k]) ? $this->Qa->convert_content_to_frontend(trim($content_qc[$k]),200) : '';
	            $questionInfo[$k]['answerCount'] = (int)$content_q[$k]['normal_answer_count'];
	            $data[$k]['absId'] = (string) $v['mark'];
	            $data[$k]['abstitle'] = trim($content_info[$k]) ? $this->Qa->convert_content_to_frontend(trim($content_info[$k]),200) : '';
	            $data[$k]['updateTime'] = $v['update_time'] ? date('Y-m-d H:i:s',$v['update_time']) : '';
	            $data[$k]['status'] = $content[$k]['status'] >= 2 ? 1 : 0;
	            $data[$k]['questionInfo'] = $questionInfo[$k];
	        }
	        $data = $data ? $data : array();
	        Util::echo_format_return(_SUCCESS_, $data);
	    }catch (Exception $e) {
	        Util::echo_format_return($e->getCode(), array(), $e->getMessage());
	    }
	}
	/*
	 * 我的提问
	 */
	public function my_question()
	{
		$page	  	= $this->input->get('page',true) ? $this->input->get('page',true) : 1;
		$count	  	= $this->input->get('count',true) ? $this->input->get('count',true) : 10;
		$max_id	  	= trim ( $this->input->get('max_id',true) );
		$start 		= ($page==1) ? 0 : ($page-1) * $count;
		try {
			if(!$this->user_id) {
				throw new Exception('用户未登录', _PARAMS_ERROR_);
			}
			$id_list = $this->question_model->get_lists_id_by_uid($this->user_id,$start,$count * 2);

			$data = array();
			$c = 0;
			foreach ($id_list as $id) {
				if (++$c > $count) {
					break;
				}
				if ($id == $max_id) {
					$data = array();	// 抛弃之前的
					$c = 0;
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
				$_arr['updateTime'] = $info['update_time'] ? date('Y-m-d H:i:s',$info['update_time']) : '';
				$_arr['status'] = $info['status'] >= 2 ? 1 : 0;
				$return[] = $_arr;
			}
			Util::echo_format_return(_SUCCESS_, $return);
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}

	/*
	 * 获取其他用户的提问列表    2.0版本新增
	 */
	public function other_question()
	{
	    $page	  	= $this->input->get('page',true) ? $this->input->get('page',true) : 1;
	    $count	  	= $this->input->get('count',true) ? $this->input->get('count',true) : 10;
	    $uid	  	= $this->input->get('uid',true) ? $this->input->get('uid',true) : '';
	    $max_id	  	= trim ( $this->input->get('max_id',true) );
	    $start 		= ($page==1) ? 0 : ($page-1) * $count;
	    try {
	        if($uid =='') {
	            throw new Exception('查询的用户id不能为空', _PARAMS_ERROR_);
	        }
	        $id_list = $this->question_model->get_lists_id_by_uid($uid,$start,$count * 2,1);
	
	        $data = array();
	        $c = 0;
	        foreach ($id_list as $id) {
	            if (++$c > $count) {
	                break;
	            }
	            if ($id == $max_id) {
	                $data = array();	// 抛弃之前的
	                $c = 0;
	            }
	            $data[] = $id;
	        }
	
	
	        $return = array();
	        foreach ($data as $v){
	            $info = $this->question_model->get_info($v,array(0,1));
	            $content_info = $this->Question_content->get_content($v);
	            $_arr = array();
	            $_arr['absId'] = (string) $info['qid'];
	            $_arr['answerCount'] = (int) $info['normal_answer_count'];
	            $_arr['abstitle'] = (string)$this->Qa->convert_content_to_frontend(trim($content_info),100);
	            $_arr['updateTime'] = $info['update_time'] ? date('Y-m-d H:i:s',$info['update_time']) : '';
	            $_arr['status'] = $info['status'] >= 2 ? 1 : 0;
	            $return[] = $_arr;
	        }
	        Util::echo_format_return(_SUCCESS_, $return);
	    }catch (Exception $e) {
	        Util::echo_format_return($e->getCode(), array(), $e->getMessage());
	    }
	}
	/*
	 * 我的回答列表
	 */
	public function my_answer()
	{
		$page	  	= $this->input->get('page',true) ? $this->input->get('page',true) : 1;
		$count	  	= $this->input->get('count',true) ? $this->input->get('count',true) : 10;
		$max_id	  	= trim ( $this->input->get('max_id',true) );
		$start 		= ($page==1) ? 0 : ($page-1) * $count;

		try {
			if(!$this->user_id) {
				throw new Exception('用户未登录', _PARAMS_ERROR_);
			}
			$info = $this->Answer->get_a_list_by_uid($start,$count,$max_id,$this->user_id);

			foreach ($info as $k=>$v){
				$content_info[$k] = $this->Answer_content->get_content($v['aid']);
				$data[$k]['absId'] = (string) $v['aid'];
				$data[$k]['abstitle'] = trim($content_info[$k]) ? $this->Qa->convert_content_to_frontend(trim($content_info[$k]),200) : '';
				$data[$k]['updateTime'] = $v['update_time'] ? date('Y-m-d H:i:s',$v['update_time']) : '';
				$data[$k]['status'] = $v['status'] >= 2 ? 1 : 0;

				$content_info_q[$k] = $this->Question_content->get_content($v['qid']);
				$content_info_q_row[$k] = $this->Question->get_info($v['qid'],array(0,1,2));
				if($content_info_q[$k]){
					$questionInfo[$k]['absId'] = (string) $v['qid'];
					$questionInfo[$k]['status'] = $content_info_q_row[$k]['status'] >= 2 ? 1 : 0;
					$questionInfo[$k]['abstitle'] = $content_info_q[$k] ? $this->Qa->convert_content_to_frontend($content_info_q[$k],200) : '';
					$questionInfo[$k]['answerCount'] = (int) $content_info_q_row[$k]['normal_answer_count'];
					$data[$k]['questionInfo'] = $questionInfo[$k];
				}
			}
			$data = $data ? $data : array();
			Util::echo_format_return(_SUCCESS_, $data);
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}

	/*
	 * 获取其他用户的回答列表    2.0版本新增
	 */
	public function other_answer()
	{
	    $page	  	= $this->input->get('page',true) ? $this->input->get('page',true) : 1;
	    $count	  	= $this->input->get('count',true) ? $this->input->get('count',true) : 10;
	    $uid	  	= $this->input->get('uid',true) ? $this->input->get('uid',true) : '';
	    $max_id	  	= trim ( $this->input->get('max_id',true) );
	    $start 		= ($page==1) ? 0 : ($page-1) * $count;
	
	    try {
	        if( $uid =='') {
	            throw new Exception('查询的用户id不能为空', _PARAMS_ERROR_);
	        }
	        $info = $this->Answer->get_a_list_by_uid($start,$count,$max_id,$uid,2);
	
	        foreach ($info as $k=>$v){
	            $content_info[$k] = $this->Answer_content->get_content($v['aid']);
	            $data[$k]['absId'] = (string) $v['aid'];
	            $data[$k]['abstitle'] = trim($content_info[$k]) ? $this->Qa->convert_content_to_frontend(trim($content_info[$k]),200) : '';
	            $data[$k]['updateTime'] = $v['update_time'] ? date('Y-m-d H:i:s',$v['update_time']) : '';
	            $data[$k]['status'] = $v['status'] >= 2 ? 1 : 0;
	
	            $content_info_q[$k] = $this->Question_content->get_content($v['qid']);
	            $content_info_q_row[$k] = $this->Question->get_info($v['qid'],array(0,1));
	            if($content_info_q[$k]){
	                $questionInfo[$k]['absId'] = (string) $v['qid'];
	                $questionInfo[$k]['status'] = $content_info_q_row[$k]['status'] >= 2 ? 1 : 0;
	                $questionInfo[$k]['abstitle'] = $content_info_q[$k] ? $this->Qa->convert_content_to_frontend($content_info_q[$k],200) : '';
	                $questionInfo[$k]['answerCount'] = (int) $content_info_q_row[$k]['normal_answer_count'];
	                $data[$k]['questionInfo'] = $questionInfo[$k];
	            }
	        }
	        $data = $data ? $data : array();
	        Util::echo_format_return(_SUCCESS_, $data);
	    }catch (Exception $e) {
	        Util::echo_format_return($e->getCode(), array(), $e->getMessage());
	    }
	}
	
	/*
	 * 编辑用户信息
	 */
	public function edit_user()
	{
		$action	  	= $this->input->get('action',true) ? $this->input->get('action',true) : 0;
		$nickname	  	= $this->input->get_post('nickname');
		$bgid	  	= $this->input->get_post('bgid');
		$bgid 		= $this->global_func->filter_int($bgid);

		$check_nickname = $this->User->_check_nickname($nickname);

		try {
			if(!$this->user_id) {
				throw new Exception('用户未登录', _PARAMS_ERROR_);
				exit;
			}
			if($action <= 0) {
				throw new Exception('操作类型无效', _PARAMS_ERROR_);
				exit;
			}
			if($action == 1){
				//头像
				$this->User->upload_img($_FILES);
			}
			if($action == 2){
				//昵称
				$data['nickname'] = trim($nickname);
				if(strlen($data['nickname']) < 4 && strlen($data['nickname']) >30){
					throw new Exception('用户昵称必须在4-30字符内', _PARAMS_ERROR_);
					//Util::echo_format_return(_SUCCESS_, '','用户昵称必须在4-30字符内');
					exit;
				}
				if($check_nickname == 1){
					throw new Exception('用户昵称已经存在', _PARAMS_ERROR_);
					//Util::echo_format_return(_SUCCESS_, '','用户昵称已经存在');
					exit;
				}
				$this->load->config('ban_word_pattern', true);
				$pattern = $this->config->item('ban_word_pattern');
				foreach($pattern as $k => $v)
				{
					$error_uname = '';
					preg_match($v,$data['nickname'],$error_uname);
					if($error_uname){
						throw new Exception('内容不能包含敏感词汇', _PARAMS_ERROR_);
						//Util::echo_format_return(_SUCCESS_, '','内容不能包含敏感词汇');
						exit;
					}
				}

				$this->User->update_user($this->user_id,$data);
			}

			if($action == 3){ //设置背景图片
				if(!is_numeric($bgid) || !$bgid){
					throw new Exception('图片ID错误', _PARAMS_ERROR_);
					exit;
				}

				//判断bgid是否可选
				if(!$this->Bg_img_model->checkBgimg($bgid)){
					throw new Exception('图片ID不合法', _PARAMS_ERROR_);
					exit;
				}

				//拼装修改数据
				$this->User_balance_model->save_bgimg($this->user_id, $bgid);
			}
			$data = $data ? $data : array();
			Util::echo_format_return(_SUCCESS_, '', '修改成功');
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}

	//获取用户可替换背景图列表
	public function bg_img_list(){
		try {
			$return_list = array();

			//调用方法获取数据
			$bg_img_res = $this->Bg_img_model->get_bgimg_list();

			if(is_array($bg_img_res) && count($bg_img_res) > 0){
				foreach($bg_img_res as $k=>$v){
					$return_list[$k]['absId'] = $v['id'];
					$return_list[$k]['abstitle'] = $v['img_title'];
					$return_list[$k]['absImage'] = $v['img_url'];
				}
			}

			$data = $return_list;
			Util::echo_format_return(_SUCCESS_, $data);
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}

	private function getFollowInfo($type,$start,$count,$max_id = 0,$uid=0)
	{
	    if($uid != '0'){
		  $uids = $uid;
	    }else{
		  $uids = $this->user_id;
	    }
		$info = $this->Follow->get_follow_info($uids,$type,$start,$count);
//			echo "<pre>";print_r($article_info);exit;
		//-->处理 如果max_id出现相同的 那么从下一页抓取最新的补齐   start --------//
		$flag = 100;
		foreach ($info as $_k => $_v) {
			if ($_v['mark'] == $max_id) {
				$flag = $_k;
			}
		}
		$returns = array();
		$returnss = array();
		if ($flag != 100) {

			foreach ($info as $_k => $_v) {
				if ($_k > $flag ) {
					array_push($returns, $_v);
				}
			}
			$infos = $this->Follow->get_follow_info($uids,$type,$start,$count+1+$flag);
			foreach ($infos as $_k1 => $_v) {
				if ($_k1 > $flag) {
					array_push($returnss, $_v);
				}
			}
			$returns = $returnss;
		} else {
			$returns = $info;
		}
		//-->处理 如果max_id出现相同的 那么从下一页抓取最新的补齐   end --------//
		return $returns;
	}

	//用户关注操作（关注其他用户和取消关注其他用户）    2.0版本新增
	public function user_attention() {
	    $guid = $this->user_id;//已登录用户id 
		$uid = $this->input->get('uid', true);//被关注的用户id
		$action = $this->input->get('action', true) ? 1 : 0 ;//类型 0为添加关注；  1为取消关注
        
		try {
		    if (empty($guid) || empty($uid)) {
		       throw new Exception('用户id不能为空', _PARAMS_ERROR_);
		    }
		    
    		$this->load->model('friend_model');
    		
		    if($action != 1){//关注
    		    $this->friend_model->add_friend($guid, $uid);
    		    $is_attention = $this->friend_model->is_friend($uid, $guid);
    		    if($is_attention){
    		      $data['relationship'] = 2;//互相关注
    		    }else{
    		      $data['relationship'] = 1;
    		    }
		    }else{//取消关注
    		    $this->friend_model->del_friend($guid, $uid);
    		    $data['relationship'] = 0;
		    }
		    
	        $data = $data ? $data : array();
	        Util::echo_format_return(_SUCCESS_, $data, '操作成功');
		
		    return 1;
		} catch (Exception $e) {
		    Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		    return 0;
		}
	}
	//64 我关注的用户列表     2.0版本新增
	public function my_attention_user() {
	    $guid = $this->user_id;//已登录用户id 
		$page	  	= $this->input->get('page',true) ? $this->input->get('page',true) : 1;
		$count	  	= $this->input->get('count',true) ? $this->input->get('count',true) : 10;
// 		$max_id	  	= trim ( $this->input->get('max_id',true) );
		$start 		= ($page==1) ? 0 : ($page-1) * $count;
        
		try {
		    if (empty($guid)) {
		       throw new Exception('用户id不能为空', _PARAMS_ERROR_);
		    }
		    
    		$this->load->model('friend_model');
    		$attention_list = $this->friend_model->get_friends_list($guid, $start,$count,0);
    		$data =array();
    		foreach ($attention_list['list'] as $k => $v){
    		    $dataInfo = $this->User->getUserInfoById($v['uid']);
	            $data[$k]['guid'] = (string)$dataInfo['uid'];
	            $data[$k]['nickName'] = (string)$dataInfo['nickname'];
	            $data[$k]['headImg'] = (string)$dataInfo['avatar'] ? (string)$dataInfo['avatar']:'';
	            $data[$k]['uLevel'] = (int)$dataInfo['level'];
	            $data[$k]['medalLevel'] = (int)$dataInfo['rank'] == 1 ? 1 : 0;

    		    $is_attention = $this->friend_model->is_friend($guid, $dataInfo['uid']);
    		    if($is_attention){
    		      $is_attentions = $this->friend_model->is_friend( $dataInfo['uid'],$guid);
    		      if($is_attentions){
    		          $data[$k]['relationship'] = 2;//互相关注
    		      }else{
    		          $data[$k]['relationship'] = 1;
    		      }
    		    }else{
    		      $data[$k]['relationship'] = 0;
    		    }
    		}
	        $data = $data ? $data : array();
	        Util::echo_format_return(_SUCCESS_, $data);
		
		    return 1;
		} catch (Exception $e) {
		    Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		    return 0;
		}
	}
    
	//65 其他用户关注的用户列表     2.0版本新增
	public function other_attention_user() {
	    $guid = $this->user_id;//已登录用户id
	    $uid	  	= $this->input->get('uid',true);
	    $page	  	= $this->input->get('page',true) ? $this->input->get('page',true) : 1;
	    $count	  	= $this->input->get('count',true) ? $this->input->get('count',true) : 10;
	    // 		$max_id	  	= trim ( $this->input->get('max_id',true) );
	    $start 		= ($page==1) ? 0 : ($page-1) * $count;
	
	    try {
	        if (empty($uid)) {
	            throw new Exception('用户id不能为空', _PARAMS_ERROR_);
	        }
	
	        $this->load->model('friend_model');
	        $attention_list = $this->friend_model->get_friends_list($uid, $start,$count,0);
	        $data =array();
	        foreach ($attention_list['list'] as $k => $v){
	            $dataInfo = $this->User->getUserInfoById($v['uid']);
	            $data[$k]['guid'] = (string)$dataInfo['uid'];
	            $data[$k]['nickName'] = (string)$dataInfo['nickname'];
	            $data[$k]['headImg'] = (string)$dataInfo['avatar'] ? (string)$dataInfo['avatar']:'';
	            $data[$k]['uLevel'] = (int)$dataInfo['level'];
	            $data[$k]['medalLevel'] = (int)$dataInfo['rank'] == 1 ? 1 : 0;
	            //是否关注对方
	            $is_attention = $this->friend_model->is_friend($guid, $dataInfo['uid']);
	            if($is_attention){
	                //查询是否互相关注
	                $is_attention_other = $this->friend_model->is_friend($dataInfo['uid'],$guid);
	                if($is_attention_other){
	                   $data[$k]['relationship'] = 2;
	                }else{
	                   $data[$k]['relationship'] = 1;
	                }
	            }else{
	                $data[$k]['relationship'] = 0;
	            }
	        }
	        $data = $data ? $data : array();
	        Util::echo_format_return(_SUCCESS_, $data);
	
	        return 1;
	    } catch (Exception $e) {
	        Util::echo_format_return($e->getCode(), array(), $e->getMessage());
	        return 0;
	    }
	}
	//66 我的粉丝列表     2.0版本新增
	public function my_fans() {
	    $guid = $this->user_id;//已登录用户id
	    $page	  	= $this->input->get('page',true) ? $this->input->get('page',true) : 1;
	    $count	  	= $this->input->get('count',true) ? $this->input->get('count',true) : 10;
	    // 		$max_id	  	= trim ( $this->input->get('max_id',true) );
	    $start 		= ($page==1) ? 0 : ($page-1) * $count;
	
	    try {
	        if (empty($guid)) {
	            throw new Exception('用户id不能为空', _PARAMS_ERROR_);
	        }
	
	        $this->load->model('friend_model');
	        $attention_list = $this->friend_model->get_friends_list($guid, $start,$count,1);//关注我的
	        $data =array();
	        foreach ($attention_list['list'] as $k => $v){
	            $dataInfo[$k] = $this->User->getUserInfoById($v['uid']);
	            $data[$k]['guid'] = (string)$dataInfo[$k]['uid'];
	            $data[$k]['nickName'] = (string)$dataInfo[$k]['nickname'];
	            $data[$k]['headImg'] = (string)$dataInfo[$k]['avatar'] ? (string)$dataInfo[$k]['avatar']:'';
	            $data[$k]['uLevel'] = (int)$dataInfo[$k]['level'];
	            $data[$k]['medalLevel'] = (int)$dataInfo[$k]['rank'] == 1 ? 1 : 0;
	            //是否关注对方
	            $is_attention = $this->friend_model->is_friend($guid, $dataInfo[$k]['uid']);
	            if($is_attention){
	                $data[$k]['relationship'] = 2;
	            }else{
	                $data[$k]['relationship'] = 0;
	            }
	        }
	        $data = $data ? $data : array();
	        Util::echo_format_return(_SUCCESS_, $data);
	
	        return 1;
	    } catch (Exception $e) {
	        Util::echo_format_return($e->getCode(), array(), $e->getMessage());
	        return 0;
	    }
	}
	
	//67 其他用户的粉丝列表     2.0版本新增
	public function other_fans() {
	    $guid = $this->user_id;//已登录用户id
	    $uid	  	= $this->input->get('uid',true);
	    $page	  	= $this->input->get('page',true) ? $this->input->get('page',true) : 1;
	    $count	  	= $this->input->get('count',true) ? $this->input->get('count',true) : 10;
	    // 		$max_id	  	= trim ( $this->input->get('max_id',true) );
	    $start 		= ($page==1) ? 0 : ($page-1) * $count;
	
	    try {
	        if (empty($uid)) {
	            throw new Exception('用户id不能为空', _PARAMS_ERROR_);
	        }
	
	        $this->load->model('friend_model');
	        $attention_list = $this->friend_model->get_friends_list($uid, $start,$count,1);//关注该用户的
	        $data =array();
	        foreach ($attention_list['list'] as $k => $v){
	            $dataInfo = $this->User->getUserInfoById($v['uid']);
	            $data[$k]['guid'] = (string)$dataInfo['uid'];
	            $data[$k]['nickName'] = (string)$dataInfo['nickname'];
	            $data[$k]['headImg'] = (string)$dataInfo['avatar'] ? (string)$dataInfo['avatar']:'';
	            $data[$k]['uLevel'] = (int)$dataInfo['level'];
	            $data[$k]['medalLevel'] = (int)$dataInfo['rank'] == 1 ? 1 : 0;
	            //是否关注对方
	            $is_attention = $this->friend_model->is_friend($guid, $dataInfo['uid']);
	            if($is_attention){
	                //查询是否互相关注
	                $is_attention_other = $this->friend_model->is_friend($dataInfo['uid'],$guid);
	                if($is_attention_other){
	                   $data[$k]['relationship'] = 2;
	                }else{
	                   $data[$k]['relationship'] = 1;
	                }
	            }else{
	                $data[$k]['relationship'] = 0;
	            }
	        }
	        $data = $data ? $data : array();
	        Util::echo_format_return(_SUCCESS_, $data);
	
	        return 1;
	    } catch (Exception $e) {
	        Util::echo_format_return($e->getCode(), array(), $e->getMessage());
	        return 0;
	    }
	}

	/**
	 * 56 获取其他用户信息    2.0版本新增
	 *
	 */
	public function other_user_info()
	{	
		$uid	  = (int) ( $this->input->get('uid',true) );
		try {
			
			$user_info = $this->User->getUserInfoById($uid);

			$b_info = $this->User_balance_model->getInfo($uid);
			$bg_info = $this->Bg_img_model->get_bgimg($b_info['big_img_id']);

			$data = array();
		    $data['name'] = (string)$user_info['nickname'];
		    $data['headUrl'] = (string)$user_info['avatar'] ? (string)$user_info['avatar']:'';
		    $data['bgImg'] = (string)$bg_info['img_url'] ? (string)$bg_info['img_url']:'http://n.sinaimg.cn/973/app/bg/info_bg@2x.png';
		    $data['uLevel'] = (int)$user_info['level'];
		    $data['medalLevel'] = (int)$user_info['rank'] == 1 ? 1 : 0;
		    
		    $level = $this->User->getLevel($user_info['exps'],$user_info['virtual_exps']);
	        $levelExps = $this->User->getNLevelExps($level);
	        
	        $data['currentLevelExperience'] = (int) $levelExps['min'];
	        $data['totalExperience'] = (int) ($user_info['exps'] + $user_info['virtual_exps']);
	        $data['nextLevelExperience'] = (int) $levelExps['max'];
	        $user_info['ask_num'] = $this->Question->get_count_by_uid_from_db($uid);
	        $data['questionCount'] = (int) $user_info['ask_num'];
	        
	        $user_info['answer_num'] = $this->Answer->get_count_by_uid_from_db($uid);
	        $data['answerCount'] = (int) $user_info['answer_num'];

	        $this->load->model('friend_model');
	        //是否关注对方
	        $is_attention = $this->friend_model->is_friend($this->user_id, $uid);
	        if($is_attention){
	            //查询是否互相关注
	            $is_attention_other = $this->friend_model->is_friend($uid,$this->user_id);
	            if($is_attention_other){
	                $data['relationship'] = 2;
	            }else{
	                $data['relationship'] = 1;
	            }
	        }else{
	            $data['relationship'] = 0;
	        }
	        $friends_cnt = $this->friend_model->get_friends_cnt($uid);

	        $data['totalAttenCount'] = (int)$friends_cnt['my_follow'] ? (int)$friends_cnt['my_follow'] : 0;
	        $data['totalFansCount'] =  (int)$friends_cnt['follow_me'] ? (int)$friends_cnt['follow_me'] : 0;

	        $this->load->model('order_model');
	        $totalCashCount = $this->order_model->getTotalCashCount($uid,2);
	        $data['totalCashCount'] = (int)$totalCashCount;

	        $raiders = $this->Follow->get_follow_info($uid,1,0,0,1);
	        $data['raidersCount'] = (int)$raiders['counts'] ? (int)$raiders['counts'] : 0;

	        $attention_question = $this->Follow->get_follow_info($uid,4,0,0,1);
	        $attention_questions = $this->Follow->get_follow_info($uid,4,0,1000);
            $attention_counts=0;
	        foreach ($attention_questions as $k=>$v){
	            $question[$k] = $this->Question->get_info($v['mark'], array(2));
	            if ($question[$k]['qid']) {
	                $attention_counts++;
	            }
	        }
	        $data['attentionCount'] = $attention_question['counts']-$attention_counts;
 
	        $collect_answer = $this->Follow->get_follow_info($uid,2,0,0,1);
	        $data['collectCount'] = (int)$collect_answer['counts'] ? (int)$collect_answer['counts'] : 0;

	        $attGames = $this->follow_model->get_follow_info($uid,3,-1,-1);
	        $attGames || $attGames = array();
	        foreach ($attGames as $v) {
	            $game_id_arr[] = $v['mark'];
	        }

	        $this->load->model('game_model');

	        //判断是否有关注游戏
	        if(is_array($game_id_arr) && count($game_id_arr) > 0){
				$game_info = $this->game_model->get_game_list_row($game_id_arr,$this->platform);
				sort($game_info);
				foreach ($game_info as $k=>$v){
				    $data['attGameList'][$k]['absId'] = (int)$v['id'];
				    $data['attGameList'][$k]['abstitle'] = (string)$v['abstitle'] ?(string)$v['abstitle']:'';
				    $data['attGameList'][$k]['absImage'] = (string)$v['url']?(string)$v['url']:'';
				}
	        }else{
	        	$data['attGameList'] = array();
	        }

			$data = $data ? $data : array();
			Util::echo_format_return(_SUCCESS_, $data);
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}

	// ============================================ 消息 GO ===========================================================//
	public function save_device_token() {
		$uid = $this->user_id;
		$device_token = ( string ) $this->input->get_post ( 'deviceToken', true );
		$push_on = $this->input->get_post('push_on', true);
		$token = $this->input->get_post('deviceToken', true);
		$version = $this->input->get_post('version', true);
		$platform = $this->platform;
		$partner_id = $this->partner_id;
		try {
			if (empty($device_token) ) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}

			$this->load->model('push_model');
			$this->push_model->save_token($uid, $token, null, $version, $platform, $partner_id);

			Util::echo_format_return(_SUCCESS_);
			return 1;
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}

	// --------------------------- get message go ----------------------------------------------//
	/// 1.2.0之前修改状态
	// public function get_message_lt_1_2_0_remap () {
	public function get_message () {
		$uid = $this->user_id;
		try {
			if (empty($uid) ) {
				throw new Exception('uid empty', _PARAMS_ERROR_);
			}

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
				if ($v['type'] == 1 && $v['count'] > 1) {
					$content = $this->question_content_model->get_content($v['mark']);
					$content && $v['content'] = $this->qa_model->convert_content_to_frontend($content, 50);
				} elseif ($v['type'] == 2) {
					$content = $this->answer_content_model->get_content($v['mark']);
					$content && $v['content'] = $this->qa_model->convert_content_to_frontend($content, 50);
				}

				$is_new = false;
				if ($v['update_time'] > $last_get_message_time && $v['status'] != 2) {
					// 时间大于最后一次请求该接口的时间   && status != 已读
					$is_new = true;
				}

				$return[] = array(
						'absId' => (string)$v['id'],
						'isNew' => (boolean)($is_new),
						'title' => (string)$v['title'],
						'subtitle' => (string)$v['content'],
						'updateTime' => (string)date('Y-m-d H:i:s', $v['update_time']),
						'type' => (int)$v['type'],
						'flag' => (int)$v['flag'],
						'param' => (string) (empty($v['mark']) ? '' : $v['mark'] ),
				);
			}

			Util::echo_format_return(_SUCCESS_, $return);
			return 1;
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}



	// --------------------------- get message go ----------------------------------------------//

	public function read_message() {
		$mark = $this->input->get_post('id', true);
		$uid = $this->user_id;
		$action = $this->input->get_post('action', true);
		try {
			$mark_arr = explode('_', $mark);
			if (empty($uid) || empty($mark_arr) || count($mark_arr) != 2 || ($uid % 100 != $mark_arr[0])) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}

			$this->load->model('user_message_model');
			$this->user_message_model->got_message($uid, $mark_arr[1]);

			Util::echo_format_return(_SUCCESS_);
			return 1;
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}

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

	public function push_setting() {
		$switch = $this->input->get_post('type', true);
		$token = $this->input->get_post('deviceToken', true);
		$uid = $this->user_id;
		$version = $this->input->get_post('version', true);
		$platform = $this->input->get_post('platform', true);
		$partner_id = $this->partner_id;
		try {
			if (strlen($switch) != 5 ) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}

			$this->load->model('push_model');
			$this->push_model->save_token($uid, $token, $switch, $version, $platform, $partner_id);

			Util::echo_format_return(_SUCCESS_);
			return 1;
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}

	public function set_phone_gpw(){
		$action	  = (int) ( $this->input->get('action',true) );
		$phone	  = ( $this->input->get('phone',true) );
		$code	  = (int) ( $this->input->get('code',true) );
		$seed	  = (string) ( $this->input->get('seed',true) );
		$oldgpw	  = (string) ( $this->input->get('oldgpw',true) );
		$newgpw	  = (string) ( $this->input->get('newgpw',true) );

		$uid = $this->user_id;

		try{
			if($action < 0){
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}

			if($action == 0 || $action == 1){
				$forbid_failed = $this->cache->redis->get("glapp:users:msg:failed_forbid1_".$this->user_id);
				if(intval($forbid_failed) == 1){
					Util::echo_format_return(_USER_TOKEN_ERROR_, '', '您因频繁提交错误验证码被禁1小时', true);
					exit;
				}
			}

			//判断用户是否设置过手势密码(没有为新增，有则为修改)
			$user_info = $this->User_balance_model->getInfo($this->user_id);

			//判断操作类型
			switch($action){
				case 0: //0：设置手机号和手势密码（对于第一次设置，需要phone,code,newgpw三个参数；对于重置密码，需要phone,code,newgpw三个参数）
						if(!$user_info['gestures_password']){ //没有手势密码，本次操作为新增
							if(!$phone || !$code || !$newgpw){
								throw new Exception('参数错误', _PARAMS_ERROR_);
							}

							//验证验证码是否合法
							$rs = $this->User->verUser($phone, $code, 1);

							if($code == 9999){
								// $rs = true;
							}

							//判断是否合法
							if(!$rs){
								$result['message']= '验证码错误' ;
								Util::echo_format_return(_USER_TOKEN_ERROR_, '', $result['message'], true);
								exit;
							}else{
								//清空缓存
								$this->cache->redis->delete('glapp:users:login_code_'.$this->user_id);
							}

							//继续执行入库操作
							// 手机号码验证
							if(!preg_match("/1[34578]{1}\d{9}$/",$phone)){
								$result['message'] = '手机号格式有误';
								Util::echo_format_return(_PARAMS_ERROR_, _PARAMS_ERROR_, $result['message'], true);
							}

							//验证码规则检查
							$res = $this->User_balance_model->check_gpw_r($newgpw, false);

							if(!$res){
								$result['message'] = '手势密码格式有误';
								Util::echo_format_return(_PARAMS_ERROR_, _PARAMS_ERROR_, $result['message'], true);
							}

							//执行数据保存
							$data = array(
									'safe_phone' => $phone,
									'gestures_password' => $newgpw,
								);

							$res = $this->User_balance_model->save_blance_info($uid, $data);
						}else{ //本次操作为重置
							if(!$phone || !$newgpw){
								throw new Exception('参数错误', _PARAMS_ERROR_);
							}

							//验证码规则检查
							$res = $this->User_balance_model->verify_gpw($this->user_id, $newgpw);

							if(!$res){
								$result['message'] = '手势密码有误';
								Util::echo_format_return(_PARAMS_ERROR_, _PARAMS_ERROR_, $result['message'], true);
							}

							//执行数据保存
							$data = array(
									'gestures_password' => $newgpw,
								);

							$res = $this->User_balance_model->save_blance_info($uid, $data);
						}
					break;
				case 1: //1：设置手机号（需要phone,code）
					//判断参数
					if(!$phone || !$code){
						throw new Exception('参数错误', _PARAMS_ERROR_);
					}

					//判断code合法性
					//验证验证码是否合法
					$rs = $this->User->verUser($phone, $code, 1);

					if($code == 9999){
						// $rs = true;
					}

					//判断是否合法
					if(!$rs){
						$result['message']= '验证码错误' ;
						Util::echo_format_return(_USER_TOKEN_ERROR_, '', $result['message'], true);
						exit;
					}else{
						//清空缓存
						$this->cache->redis->delete('glapp:users:login_code_'.$this->user_id);
					}

					// 手机号码验证
					if(!preg_match("/1[34578]{1}\d{9}$/",$phone)){
						$result['message'] = '手机号格式有误';
						Util::echo_format_return(_PARAMS_ERROR_, _PARAMS_ERROR_, $result['message'], true);
					}

					//执行数据保存
					$data = array(
							'safe_phone' => $phone
						);

					$res = $this->User_balance_model->save_blance_info($uid, $data);

					break;
				case 2: //2：设置手势密码（需要oldgpw和newgpw）
					//判断参数
					if(!$oldgpw || !$newgpw){
						throw new Exception('参数错误', _PARAMS_ERROR_);
					}

					//获取当前用户信息,判断旧手势密码是否一致
					if($user_info['gestures_password'] != $oldgpw){
						throw new Exception('旧手势密码错误', _PARAMS_ERROR_);
					}

					//验证码规则检查
					$res = $this->User_balance_model->check_gpw_r($newgpw, false);

					if(!$res){
						$result['message'] = '手势密码格式有误';
						Util::echo_format_return(_PARAMS_ERROR_, _PARAMS_ERROR_, $result['message'], true);
					}

					//执行数据保存
					$data = array(
							'gestures_password' => $newgpw,
						);

					$res = $this->User_balance_model->save_blance_info($uid, $data);

					break;
				default: //标识错误
					throw new Exception('参数错误', _PARAMS_ERROR_);
					break;
			}

			$result['message'] = '设置成功';
			Util::echo_format_return(_SUCCESS_,array(), $result['message'], true);
			return 1;

		//获取密码
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}

	}

	//提现账号设置方法
	public function set_blance_payaccount(){
		$action	  = (int) ( $this->input->get('action',true) );
		$payAccount	  = (string)( $this->input->get('payAccount',true) );
		$payRealName	  =  (string)( $this->input->get('payRealName',true) );

		$uid = $this->user_id;

		try{
			if(!$this->user_id) {
				throw new Exception('用户未登录', _PARAMS_ERROR_,'用户未登录');
				exit;
			}
			if($action < 0) {
				throw new Exception('操作类型无效', _PARAMS_ERROR_,'操作类型无效');
				exit;
			}

			if($action == 0 || $action == 2){
				//其中含有对图片的操作
				$this->User_balance_model->upload_img($_FILES);
			}

			if($action == 0 || $action == 1){
				//需要修改账号跟姓名
				$data = array(
						'alipay_account' => $payAccount,
						'alipay_name' => $payRealName,
					);

				$this->User_balance_model->save_blance_info($uid, $data);
			}

			$data = $data ? $data : array();
			Util::echo_format_return(_SUCCESS_, '', '修改成功');
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}

	//检查用户是否设置过手势密码
	public function check_own_gpw(){
		try{
			//获取用户信息
			$user_info = $this->User_balance_model->getInfo($this->user_id);

			$data['gestured'] = $user_info['gestures_password'] ? 1 : 0;

			Util::echo_format_return(_SUCCESS_, $data);
			return 1;

		//获取密码
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}

	//获取绑定手机号
	public function get_safe_phone(){
		try{
			//获取用户信息
			$user_info = $this->User_balance_model->getInfo($this->user_id);

			$data['phone'] = $user_info['safe_phone'] ? $user_info['safe_phone'] : "";

			Util::echo_format_return(_SUCCESS_, $data);
			return 1;

		//获取密码
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}

	public function check_gpw(){
		$newgpw = $this->input->get_post('newgpw', true);

		try{
			//检查手势密码是否正确
			if(!$newgpw){
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}

			//获取用户uid
			$uid = $this->user_id;

			//查询获取结果
			$return = $this->User_balance_model->verify_gpw($uid, $newgpw);
			Util::echo_format_return(_SUCCESS_, $return);
			return 1;
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}

	public function check_phone_code_match(){
		$phone = $this->input->get_post('phone', true);
		$code = $this->input->get_post('code', true);
		$type = 1; //默认短信验证

		try{
			//检查手势密码是否正确
			if(!$phone || !$code){
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}

			//验证码发送错误次数
			$forbid_failed = $this->cache->redis->get("glapp:users:msg:failed_forbid1_".$this->user_id);
			if(intval($forbid_failed) == 1){
				Util::echo_format_return(_USER_TOKEN_ERROR_, '', '您因频繁提交错误验证码被禁1小时', true);
				exit;
			}

			$rs = $this->User->verUser($phone, $code, $type);

			// 短信测试
			if($type == 1 && $code == '9999'){
				// $rs = true;
			}

			if($rs == false){
				$result['code'] = _DATA_ERROR_;
				$message = '验证码错误';
				$result['checkResult'] = 0;
			}else{
				//删除生成的验证码缓存
				//清空缓存
				$result['code'] = _SUCCESS_;
				$result['checkResult'] = 1;
				$message = '成功';
				$this->cache->redis->delete('glapp:users:login_code_'.$this->user_id);
			}

			Util::echo_format_return($result['code'], $result, $message);
			return 1;
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}

	}


	//临时清除用户数据接口
	public function clear_user_balance(){
		$uid	  = (int) ( $this->input->get('uid',true) );

		$alipay_account	  = (int) ( $this->input->get('alipay_account',true) );
		$alipay_name	  = (int) ( $this->input->get('alipay_name',true) );
		$alipay_qcode	  = (int) ( $this->input->get('alipay_qcode',true) );
		$gestures_password	  = (int) ( $this->input->get('gestures_password',true) );
		$big_img_id	  = (int) ( $this->input->get('big_img_id',true) );
		$balance_reward_total	  = (int) ( $this->input->get('balance_reward_total',true) );
		$safe_phone	  = (int) ( $this->input->get('safe_phone',true) );

		$data = array();

		if($alipay_account){
			$data += array('alipay_account' => '');
		}

		if($alipay_name){
			$data += array('alipay_name' => '');
		}

		if($alipay_qcode){
			$data += array('alipay_qcode' => '');
		}

		if($gestures_password && $safe_phone){
			$data += array('gestures_password' => '',
							'safe_phone' => ''
				);
		}

		if($big_img_id){
			$data += array('big_img_id' => 0);
		}

		if($balance_reward_total){
			$data += array('balance_reward_total' => 0);
		}

		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":gl_user_balance:";
		//拼装cachekey
		$ccccache_key = $this->_cache_key_pre . "user_balance_newgps_num:{$uid}";
		//获取次数
		$this->cache->redis->delete($ccccache_key);

		if(!empty($data)){
			$res = $this->User_balance_model->save_blance_info($uid, $data);
			echo 'bobo say: success! on ' . date('Y-m-d H:i:s');
		}else{
			echo 'bobo say: there is nothing,idiot!';
		}

	}




	// ============================================ 消息 END ===========================================================//
}

/* End of file user.php */
/* Location: ./application/controllers/api/user.php */
