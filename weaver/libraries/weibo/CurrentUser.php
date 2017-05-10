<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * Created on 2012-8-21
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

require_once  SYSDIR.'/libraries/weibo/SSO.php';
require_once  SYSDIR.'/libraries/weibo/WeiboApi.php';

class CurrentUser {

    private $sso;
    private $weibo;
    private $callback;

	function __construct() {
		$this->sso = new SSO();
		$this->weibo = new WeiboApi();
	}

	function status($noRedirect = 0) {
		return $this->sso->isLogined(TRUE);
	}

	function ssoUserInfo() {
		$weiboInfo = $this->weiboUserInfo();
		$ssoInfo = $this->sso->getUserInfo();
		if (isset($weiboInfo["data"]["screen_name"])) {
			$ssoInfo["displayname"] = $weiboInfo["data"]["screen_name"];
			$ssoInfo["profile_image_url"] = $weiboInfo["data"]["profile_image_url"];
			$ssoInfo["weibo_domain"] = $weiboInfo["data"]["domain"];
			$ssoInfo["avatar_large"] = $weiboInfo["data"]["avatar_large"];
		}
		return $ssoInfo;
	}

	function getuid() {
		return $this->sso->getUniqueid();
	}

	function logout($url = ''){
		return $this->sso->login_out($url);
	}

	function weiboUserInfo($wbuserid='') {
		//获取指定微博用户内容
		if ($wbuserid) 
		{
			$weiboUser = $this->weibo->user_show_inner($wbuserid);
			if (!isset($weiboUser) || !$weiboUser || empty($weiboUser) || isset($weiboUser["error"]) || isset($weiboUser["error_code"])) {
				$ret = array();
				$ret["error"] = 110;
				$ret["errorMsg"] = "该用户不是微博用户";
				return $ret;
			} else {
				$ret = array();
				$ret["error"] = 0;
				$ret['followers_count'] = $weiboUser['followers_count']; 
				return $ret;
			}	
		}
		if ($this->status()) {
			$uid = $this->getuid();
			$weiboUser = $this->weibo->user_show_inner($uid);
			if (!isset($weiboUser) || !$weiboUser || empty($weiboUser) || isset($weiboUser["error"]) || isset($weiboUser["error_code"])) {
				$ret = array();
				$ret["error"] = 110;
				$ret["errorMsg"] = "该用户不是微博用户";
				return $ret;
			} else {
				$ret = array();
				$ret["error"] = 0;
				$ret["data"] = $weiboUser;
				return $ret;
			}
		} else {
			$ret = array();
			$ret["error"] = 100;
			$ret["errorMsg"] = "未登录";
			return $ret;
		}
	}

	function friendlist($nums = 20, $page = 1) {
		if ($this->status()) {
			$uid = $this->getuid();
			$page = my_get_post("page",$page);
			$nums = my_get_post("nums",$nums);
			$nums = ($nums < 0 || $nums > 200) ? 30 : $nums;
			$friendsArray = $this->weibo->friends_follow_bilateral($uid, $nums, $page);
			if (!$friendsArray || isset($friendsArray["error"]) || isset($friendsArray["error_code"])) {
				$friendsArray = $this->weibo->friends_follow_active($uid, $nums);
			}
			if (isset($friendsArray["error_code"])) {
				$code = $friendsArray["error_code"];
				$error = errorConfig::$EVENT_GAMES_WEIBO_ERROR["$code"];
				$array = array();
				$array["code"] = 111;
				$array["msg"] = $error;
				output(false, $array, __CLASS__, true, $this->callback);
			}
			$friendsList = $friendsArray["users"];
			if($friendsList == NULL){
				$friendsArray = $this->weibo->friends_follow_active($uid, $nums);
				if (!$friendsArray || isset($friendsArray["error"]) || isset($friendsArray["error_code"])) {
					$friendsArray = $this->weibo->friends_follow_active($uid, $nums);
				}
				if (isset($friendsArray["error_code"])) {
					$code = $friendsArray["error_code"];
					$error = errorConfig::$EVENT_GAMES_WEIBO_ERROR["$code"];
					$array = array();
					$array["code"] = 111;
					$array["msg"] = $error;
					output(false, $array, __CLASS__, true, $this->callback);
				}
				$friendsList = $friendsArray["users"];
			}
			$array = array();
			foreach ($friendsList as $key => $value) {
				$array[$key]["id"] = $value["id"];
				$array[$key]["profile_image_url"] = $value["profile_image_url"];
				$array[$key]["screen_name"] = $value["screen_name"];
			}
			output(true, $array, __CLASS__, true, $this->callback);
		} else {
			$ret = array();
			$ret["error"] = 100;
			$ret["errorMsg"] = "未登录";
			output(false, $ret, __CLASS__, true, $this->callback);
		}
	}

	function findfriends() {
		if ($this->status()) {
			$qs = my_get_post("qs",'');
			$friendsArr = $this->weibo->search_at_users($qs);
			if (!isset($friendsArr)) {
				$array = array();
				$array["code"] = 111;
				$array["msg"] = "未找到用户";
				output(false, $array, __CLASS__, true, $this->callback);
			}
			$array = array();
			foreach ($friendsArr as $key => $value) {
				$friendInfo = $this->weibo->users_show($value["uid"]);
				if(empty($friendInfo["profile_image_url"])){
				  continue;
				}
				$array[$key]["remark"] = $friendInfo["profile_image_url"];
				$array[$key]["uid"] = $value["uid"];
				$array[$key]["nickname"] = $value["nickname"];
			}
			output(true, $array, __CLASS__, true, $this->callback);
		} else {
			$ret = array();
			$ret["error"] = 100;
			$ret["errorMsg"] = "未登录";
			output(false, $ret, __CLASS__, true, $this->callback);
		}
	}

}
?>
