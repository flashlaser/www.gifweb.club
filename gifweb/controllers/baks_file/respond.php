<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 * @author haibo8, <haibo8@staff.sina.com.cn>
 * @version   $Id: respond.php 2015-12-10 14:52:27 haibo8 $
 * @copyright (c) 2015 Sina Game Team.
 */
class Respond extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		/*No Cache*/
		$this->output->set_header("Cache-Control: no-cache, must-revalidate");
		$this->output->set_header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
		$this->output->set_header("Pragma: no-cache");

		$this->load->model('Common_model','Comm');
		$this->load->library ( 'SSOServer/SSOServer','ssosever' );
	}

	/**
	 * 响应首页
	 *
	 */
	public function index(){
		die('error');
	}

	/**
	 * 微信登录
	 *
	 */
	public function wxdo(){
		echo urlencode(base_url() . "respond/wxdo");
		print_r($_GET);exit;
	}

	/**
	 * 微博登录
	 *
	 */
	public function wbdo($backUrl64 = ''){
		$backUrl = !$backUrl64 ? base_url() : hex2bin($backUrl64);

		$code = trim($this->input->get("code",true));
		if(empty($code)){
			$result['message'] = '微博授权失败';
			$this->showMessage('fail', $result);
		}


		// 初始化
		require_once(APPPATH . 'libraries/wbApi/saetv2.ex.class.php' );
		//$wc = new SaeTClientV2( '691988791', '9576e25bb26d857522510a1d019d06d9', '');
		$wc = new SaeTOAuthV2( '691988791', '9576e25bb26d857522510a1d019d06d9');
		// 获取token
		$token = $wc->getAccessToken('code',array("code"=>$code,"redirect_uri"=>"http://www.wan68.com/respond/wbdo"));

		// 初始化
		$wb = new SaeTClientV2('691988791', '9576e25bb26d857522510a1d019d06d9' , $token['access_token'] );
		$uid_arr = $wb->get_uid();
		$wb_uid = $uid_arr['uid'];
		$wb_user = $wb->show_user_by_id( $wb_uid );//根据ID获取用户等基本信息
		if($wb_user['error'] || $wb_user['id'] != $wb_uid){
			$result['message'] = '微博登录授权失败';
			$this->showMessage('fail', $result);
		}

		/* 获取微博用户对应网站用户信息 */
		$uid = $this->User->getUidByOpenid($wb_uid, 2);

		if($uid > 0){
			$userinfo = $this->User->getUserInfoById( $uid );
			// 用户已被删除，清理缓存信息
			if(intval($userinfo['uid']) < 1){
				$this->User->clearUserCache($uid, $wb_uid, 2);
				$result['message'] = '用户登录失败';
				$this->showMessage('fail', $result);
			}

			//更新用户登录信息
			$data['login_time'] = time();
			$data['login_ip']	= $this->global_func->get_remote_ip();
			$this->User->update_user($userinfo['uid'], $data);
		}else{
			//检查第三方用户昵称是否已存在
			$is_exist = $this->User->_check_nickname($wb_user['screen_name']);
			if($is_exist){
				$name = $this->global_func->strcut($wb_user['screen_name'], 18, '');
				$name .= $this->Comm->get_code(4,1);
			}else{
				$name = $wb_user['screen_name'];
			}

			//注册新用户基本信息
			$data['create_time'] = time();
			$data['login_time'] = time();
			$data['nickname']	= $name;
			$data['avatar']		= $wb_user['profile_image_url'];
			$data['birthday']	= empty($birthday) ? 0 : strtotime($birthday);
			$data['gender']		= $wb_user['gender'] == 'f' ? 2 : 1;
			$data['login_ip']	= $this->global_func->get_remote_ip();

			// 用户登录来源信息
			$thirdData['ch'] = 2;
			$thirdData['open_id'] = $wb_uid;
			$thirdData['token']	  = $token['access_token'];
			//$thirdData['unionid'] = $rs['unionid'];

			$userinfo = $this->User->add_user($data, $thirdData);
		}

		if(!$userinfo || $userinfo['uid'] < 1){
			$result['message'] = '注册用户失败';
			$this->showMessage('fail', $result);
		}

		// 获取登录cookie
		$rs = $this->ssoserver->rsaWeb($userinfo);
		if($rs){
			$this->User->setUserCookies($rs);
			header("Location: " . $backUrl);
		}else{
			$result['message'] = '登录失败';
			$this->showMessage('fail', $result);
		}
	}

	/**
	 * QQ登录
	 *
	 */
	public function qqdo($backUrl64 = ''){
		$backUrl = !$backUrl64 ? base_url() : hex2bin($backUrl64);

		// 初始化
		require_once(APPPATH . 'libraries/webQQ/qqConnectAPI.php' );
		// 获取token
		$qq = new QC();
		$token = $qq->qq_callback();
		$openid = $qq->get_openid();
		//获取用户信息
		$qc = new QC($token, $openid);
		$qq_user = $qc->get_user_info();

		if($qq_user['ret'] != 0){
			$result['message'] = 'QQ登录授权失败';
			$this->showMessage('fail', $result);
		}

		/* 获取qq用户对应网站用户信息 */
		$uid = $this->User->getUidByOpenid($openid, 4);

		if($uid > 0){
			$userinfo = $this->User->getUserInfoById( $uid );
			// 用户已被删除，清理缓存信息
			if(intval($userinfo['uid']) < 1){
				$this->User->clearUserCache($uid, $openid, 4);
				$result['message'] = '用户登录失败';
				$this->showMessage('fail', $result);
			}

			//更新用户登录信息
			$data['login_time'] = time();
			$data['login_ip']	= $this->global_func->get_remote_ip();
			$this->User->update_user($userinfo['uid'], $data);
		}else{
			//检查第三方用户昵称是否已存在
			$is_exist = $this->User->_check_nickname($qq_user['nickname']);
			if($is_exist){
				$name = $this->global_func->strcut($qq_user['nickname'], 18, '');
				$name .= $this->Comm->get_code(4,1);
			}else{
				$name = $qq_user['nickname'];
			}

			//注册新用户基本信息
			$data['create_time'] = time();
			$data['login_time'] = time();
			$data['nickname']	= $name;
			$data['avatar']		= $qq_user['figureurl_1'];
			$data['birthday']	= empty($birthday) ? 0 : strtotime($birthday);
			$data['gender']		= $qq_user['gender'] == '女' ? 2 : 1;
			$data['login_ip']	= $this->global_func->get_remote_ip();

			// 用户登录来源信息
			$thirdData['ch'] = 4;
			$thirdData['open_id'] = $openid;
			$thirdData['token']	  = $token;
			//$thirdData['unionid'] = $rs['unionid'];

			$userinfo = $this->User->add_user($data, $thirdData);
		}

		if(!$userinfo || $userinfo['uid'] < 1){
			$result['message'] = '注册用户失败';
			$this->showMessage('fail', $result);
		}

		// 获取登录cookie
		$rs = $this->ssoserver->rsaWeb($userinfo);
		if($rs){
			$this->User->setUserCookies($rs);
			header("Location: " . $backUrl);
		}else{
			$result['message'] = '登录失败';
			$this->showMessage('fail', $result);
		}
	}

}

/* End of file user.php */
/* Location: ./application/controllers/api/user.php */
