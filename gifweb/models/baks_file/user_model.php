<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

/**
 * @Name	User_Model.php
 */
class User_Model extends MY_Model {

	/* 登录渠道 */
	private $ch_info = array();

	public function __construct() {
		parent::__construct ();
		$this->ch_info = $this->config->item('login_ch');
		$this->fld_cacheKey = 'glapp:' . ENVIRONMENT . ':users:';
		$this->load->driver ( 'cache' );
		$this->load->model('Common_model','Comm');
		$this->load->library("global_func");
		$this->load->library('HttpRequestCommon',null,'http');
	}

	/**
	 * 用户PC、H5登录
	 *
	 */
	public function userLogin(){
		$this->load->library ( 'SSOClient/SSOClient','ssoclient' );
		$res = $this->ssoclient->isLogined();

		// 该功能为区分测试环境还是正式环境所加入
		// 客户端内部的登录，是客户端种植的COOKIE？ 没法修改后端代码直接加入其他COOKIE？
		if($res && (!$_COOKIE['GENV'] || $_COOKIE['GENV'] == ENVIRONMENT)){
			$userinfo = $this->ssoclient->getUserInfo();
			return $userinfo;
		}else{
			return false;
		}
	}

	/**
	 * 用户app登录
	 *
	 */
	public function userAppLogin($uid=0, $token='', $etime=''){
		$this->load->library ( 'SSOClient/SSOClient','ssoclient' );
		if(!is_numeric($uid) || intval($uid) < 1 || empty($token) || empty($etime)){
			return _PARAMS_ERROR_;
		}

		// 获取用户信息
		$user = $this->User->getUserInfoById( $uid );
		if(empty($user) || !is_array($user)){
			return _USER_NOT_EXIST_;
		}

		// 判断是否超时
		if($etime < time()){
			return _USER_TOKEN_OVERDUE_;
		}

		// 加密原始数据串
		$token = $this->global_func->urlsafe_b64decode( $token );
		$str = $user['uid'].$user['create_time'].$etime;

		$res = $this->ssoclient->isAppLogin($str, $token);
		if($res){
			$user['uniqueid'] = $user['uid'];
			return $user;
		}else{
			return _USER_TOKEN_ERROR_;
		}
	}

	/**
	 * 登录cookie
	 *
	 */
	public function setUserCookies($arr, $expires=0){
		if(empty($arr) || !is_array($arr)){
			return false;
		}

		$domain = $this->config->item('domain');
		$expires = $expires==0 ? (time() + 3600 * 24 * 365) : $expires;

		setcookie('GSUP',$arr['GSUP'],$expires,'/',$domain);
		setcookie('GSUE',$arr['GSUE'],$expires,'/',$domain);
		setcookie('GENV',ENVIRONMENT,$expires,'/',$domain);

	}

	/**
	 * 删除cookie
	 *
	 */
	public function delUserCookies(){
		$domain = $this->config->item('domain');

		setcookie('GSUP','',-1,'/',$domain);
		setcookie('GSUE','',-1,'/',$domain);
		setcookie('GENV','',-1,'/',$domain);
	}

	/**
	 * 获取用户信息
	 *
	 */
	public function getUserInfoById($uid){
		if(intval($uid) < 1 || !is_numeric($uid)){
			return false;
		}

		//读取redis
		$info = $this->get_redis_user_info($uid);
		//$info = false;
		if(!$info || !is_array($info)){
			$res = $this->_user_info($uid);
			$info= $res[0];
			if($info['uid'] > 0){
				// 加入redis缓存
				$this->cache_user_info($uid, $info);
			}
		}

		// 获取用户等级
		$info['level'] = $this->getLevel($info['exps'], $info['virtual_exps']);

		return $info;
	}



	/**
	 * 根据用户第三平台openid获取对应用户uid
	 *
	 */
	public function getUidByOpenid($open_id, $ch, $wx_unionid = ''){
		$ch_s = $this->ch_info[$ch];
		if(empty($ch_s) || empty($open_id)){
			return false;
		}

		// 读取redis
		$rk = $this->fld_cacheKey . $ch_s."_".$open_id;
		$uid = intval( $this->cache->redis->get( $rk ) );

		if(intval($uid) < 1){
			// 数据库获取
			$row = $this->_user_channel($open_id, $ch, $wx_unionid);
			$uid = $row['uid'] ? $row['uid'] : 0;
			if($uid > 0){
				$this->cache->redis->save( $rk, $uid, 60 * 3 );
			}
		}

		return $uid;
	}

	/**
	 * 如用户已被删除,清理缓存信息
	 *
	 */
	public function clearUserCache($uid, $open_id, $ch){
		$rk = $this->fld_cacheKey . $this->ch_info[$ch] . "_".$open_id;
		$this->cache->redis->delete( $rk );
	}

	/**
	 * 验证用户登录信息是否合法
	 *
	 */
	public function verUser($open_id, $token, $ch, $wx_unionid = ''){
		$flag = false;

		if(empty($token) || empty($open_id) || empty($this->ch_info[$ch])){
			return $flag;
		}

		switch ($ch){
			case 1:	// 短信
				$flag = $this->checkMsVcode($open_id, $token);
				break;
			case 2: // 微博
				$rs = $this->getWbInfo($open_id, $token);
				$flag = $rs ? true : false;
				break;
			case 3: // 微信
				$rs = $this->getWxInfo($open_id, $token, $wx_unionid);
				$flag = $rs ? $rs : false;
				break;
			case 4: // QQ
				$rs = $this->getQqInfo($open_id, $token);
				$flag = $rs ? true : false;
				break;
			case 5: // 小米
			    $rs = $this->getXiaoMiInfo($open_id,$token);
			    $flag = $rs ? true : false;
			    break;
		}

		return $flag;
	}

	/**
	 * 获取用户xiaomi信息,验证token
	 *
	 */
	public function getXiaoMiInfo($open_id,$token){
	    /*
	    require_once(APPPATH . 'libraries/xiaomiApi/php-sdk/xiaomi.inc.php' );
	    // 创建api client
	    $xmApiClient = new XMApiClient($clientId, $token);
	    $tokenId = $token;
	    // 获取nonce  随机数:分钟
	    $nonce = XMUtil::getNonce();

	    $path = $userProfilePath;
	    $method = "GET";
	    $params = array('token' => $tokenId, "clientId" => $clientId);

	    // 计算签名
	    $sign = XMUtil::buildSignature($nonce, $method,  $xmApiClient->getApiHost(), $path, $params, '');

	    // 构建header
	    $head =XMUtil::buildMacRequestHead($tokenId, $nonce, $sign);
	    // 访问api
	    $result = $xmApiClient->callApi($userProfilePath, $params, false, $head);
	    // 返回json
	    if($result['data']['userId']){
	        return $result['data'];
	    }else{
	        return false;
	    }
// 	    print '<br><br>';
// 	    var_dump($result);
// 	    print '<br><br>';
// 	    $result = $xmApiClient->callApiSelfSign($userProfilePath, array(), '');
// 	    // 返回json
// 	    var_dump($result);
 * */
	return true;

	}
	/**
	 * 获取用户weibo信息,验证token
	 *
	 */
	public function getWbInfo($open_id, $token){
		require_once(APPPATH . 'libraries/wbApi/saetv2.ex.class.php' );
		// 初始化
		$wc = new SaeTClientV2( G_WB_AKEY , G_WB_SKEY , $token );
		$uid_arr = $wc->get_uid();
		$wb_uid = $uid_arr['uid'];
		$wb_user = $wc->show_user_by_id( $wb_uid);//根据ID获取用户等基本信息

		$res = ($wb_uid && $wb_user['id']) ? $wb_user : false;

		return $res;
	}

	/**
	 * 获取用户qq信息,验证token
	 *
	 */
	public function getQqInfo($open_id, $token){
		require_once(APPPATH . 'libraries/qqApi/API/qqConnectAPI.php' );
		// 初始化
		$qc = new QC($token,$open_id);
		$arr = $qc->get_user_info();

		$res = $arr['ret'] == 0 ? $arr : false;

		return $res;
	}

	/**
	 * 校验手机登录验证码
	 *
	 */
	public function checkMsVcode($phone, $vcode){
		$vcode = intval($vcode);
		if(empty($phone) || empty($vcode)){
			return false;
		}

		// 开发人员的白名单，上线之后得关闭
		if (DEBUG_MODEL && (string)$vcode == '9999') {
			$this->load->config('phone_whitelist', true);
			$phone_whitelist = $this->config->item('phone_whitelist');
			if (in_array($phone, $phone_whitelist)) {
				return true;
			}
		}

		$rKey = "glapp:users:msg:failed_".$phone;
		$forbidKey = "glapp:users:msg:failed_forbid_".$phone;
		$fNum = intval( $this->cache->redis->get($rKey) );
		if($fNum >= 9){
			// 加入限制,1小时候才可以再发送
			$this->cache->redis->save($forbidKey, 1, 3600);
		}

		// redis取验证码信息
		$redisKey = 'glapp:users:login_code_'.$phone;
		$val = $this->cache->redis->get($redisKey);
		if($val && $vcode == $val){
			return true;
		}else{
			// 短信错误次数
			if($this->cache->redis->exists($rKey)){
				$this->cache->redis->incr($rKey);
			}else{
				$this->cache->redis->incr($rKey);
				$this->cache->redis->expire($rKey, 60);
			}
			return false;
		}

	}

	/**
	 * 获取用户微信信息
	 *
	 */
	public function getWxInfo($open_id, $token, $wx_unionid){
		if(empty($open_id) || empty($token)){
			return false;
		}

		// 通过access_token获取用户信息
		$this->http->setRequest ( 'https://api.weixin.qq.com/sns/userinfo?access_token='.$token.'&openid='.$open_id, 'GET' );
		$result = $this->http->send ();

		PLog::w_DebugLog("get_userrecode_by_uid return " . serialize($result));
		$res = null;
		(is_array($result) && $result['body']) && $res = json_decode($result['body'], true );

		if($res['errcode'] || empty($res['openid'])){
			$res = false;
		}

		return $res;
	}

	/**
	 * 缓存用户信息到Redis
	 *
	 * @param unknown_type $userInfo
	 * @return string
	 */
	public function cache_user_info($uId, $userInfo) {
		if (! $userInfo || ! is_numeric ( $uId ))
			return false;
		$cacheKey = $this->fld_cacheKey . $uId;
		// 删除memcache缓存
		// $mc_key = sha1('glapp_get_userinfo_redis_' . ENVIRONMENT . serialize ( $uId ));
		// $this->cache->memcached->delete ( $mc_key );

		// 设置redis缓存及过期时间
		$rs = $this->cache->redis->hMSet ( $cacheKey, $userInfo );
		$this->cache->redis->expire($cacheKey, 3600 * 24 * 30);

		return $rs;
	}

	/**
	 * 获取用户redis信息
	 *
	 * @param unknown_type $uId
	 * @param unknown_type $field
	 */
	public function get_redis_user_info($uId) {
		if (! is_numeric ( $uId ))
			return false;
		$cacheKey = $this->fld_cacheKey . $uId;
		$result = $this->cache->redis->hGetAll ( $cacheKey );

		return $result;
	}

	/**
	 * 随机生成用户昵称
	 *
	 */
	public function getRandNick(){

		$num = $this->Comm->get_code(3,1);
		return $nick= "U".$num.time();
	}

	/**
	 * 记录用户设备登录APP时间戳
	 *
	 */
	public function recordLoginTime($device_id, $uid){
		// 当前时间
		$now = time();
		$expried_time = $now + LOGIN_EXPIRED_TIME;
		$rKey = $this->fld_cacheKey . "login_expired_" . $uid;
		$arr[$device_id] = $expried_time;

		$rs = $this->cache->redis->hMSet ( $rKey, $arr );
		$this->cache->redis->expireAt($rKey, $now + LOGIN_EXPIRED_TIME + 3600 * 24 * 3);

		return $rs;
	}

	/**
	 * 验证设备登录时间是否过期
	 *
	 */
	public function checkLoginTime($device_id, $uid){
		// 当前时间
		$flag = true;
		$now = time();
		$rKey = $this->fld_cacheKey . "login_expired_" . $uid;
		$login_time = $this->cache->redis->hGet( $rKey, $device_id );

		if($login_time == false){
			// 记录登录时间
			$this->recordLoginTime($device_id, $uid);
		}else{
			if( ($now - $login_time) > LOGIN_EXPIRED_TIME ){
				$flag = false;
			}else{
				// 更新登录时间
				$this->recordLoginTime($device_id, $uid);
			}
		}

		return $flag;
	}

	/**
	 * 获取配置信息
	 *
	 * @param unknown_type $userInfo
	 * @return string
	 */
	public function getCfg($flag,$parent=''){
		$rKey = "glapp:config_set:".$flag.":".$parent;
		$info = $this->cache->redis->get($rKey);
		if($info){
			$rs = json_decode($info, true);
			return $rs;
		}else{
		    $where ="";
		    if($parent){
		        $where .= " and parent_action='".$parent."' LIMIT 1 ";
		    }
			$sql = " select config from gl_config where action='".$flag."' ".$where;
			$query = $this->db->query_read($sql);
			$row  = $query ? $query->row_array () : array();
			if($row['config']){
				// 设置redis缓存
				$this->cache->redis->set($rKey, $row['config'],86400);
				return json_decode($row['config'], true);
			}else{
				return false;
			}
		}
	}

	/**
	 * 添加新用户
	 *
	 */
	public function add_user($data, $thirdData=array()){
		if(!is_array($data)){
			return false;
		}
		// 写入数据库
		$this->db->trans_start();//启动事务
		$rs = $this->db->insert('gl_user',$data);
		$uid= $this->Comm->insert_id();
		$this->db->trans_complete();//同时成功后才提交，否则回滚

		//写入登录渠道信息
		$insertData = $this->getChData($thirdData);
		if($insertData){
			$insertData['uid'] = $uid;
			$insertData['dateline'] = time();
			$rs = $this->db->insert('gl_user_channel',$insertData);
		}

		$data['uid'] = $uid;

		// 设置缓存
		$this->cache_user_info($uid, $data);

		return $data;
	}

	/**
	 * 整理渠道信息
	 *
	 */
	public function getChData($data){
		$arr = false;
		if($data['ch'] == 1){
			$arr['mobile'] = $data['open_id'];
		}elseif($data['ch'] == 2){
			$arr['wb'] = $data['open_id'];
			$arr['wb_token'] = $data['token'];
		}elseif($data['ch'] == 3){
			$arr['wx'] = $data['open_id'];
			$arr['wx_token'] = $data['token'];
			$arr['wx_unionid'] = $data['unionid'];
		}elseif($data['ch'] == 4){
			$arr['qq'] = $data['open_id'];
			$arr['qq_token'] = $data['token'];
		}elseif($data['ch'] == 5){
			$arr['xm'] = $data['open_id'];
			$arr['xm_token'] = $data['token'];
		}


		return $arr;
	}

	/**
	 * 根据用户经验值获取用户等级
	 *
	 */
	public function getLevel($exps, $v_exps = 0){
		$level = 0;
		$exps = intval($exps + $v_exps);
		if($exps < 50){
			$level = 0;
		}elseif($exps >= 50 && $exps < 100){
			$level = 1;
		}elseif($exps >= 100 && $exps < 200){
			$level = 2;
		}elseif($exps >= 200 && $exps < 400){
			$level = 3;
		}elseif($exps >= 400 && $exps < 800){
			$level = 4;
		}elseif($exps >= 800 && $exps < 1500){
			$level = 5;
		}elseif($exps >= 1500 && $exps < 3000){
			$level = 6;
		}elseif($exps >= 3000 && $exps < 6000){
			$level = 7;
		}elseif($exps >= 6000 && $exps < 15000){
			$level = 8;
		}elseif($exps >= 15000 && $exps < 30000){
			$level = 9;
		}elseif($exps >= 30000){
			$level = 10;
		}

		return $level;
	}

	/**
	 * 根据用户等级获取用户下一等级经验值
	 *
	 */
	public function getNLevelExps($level){
		$level = intval($level);
		if($level == 0){
			$nLevelExps['min'] = 0;
			$nLevelExps['max'] = 50;
		}elseif($level == 1){
			$nLevelExps['min'] = 50;
			$nLevelExps['max'] = 100;
		}elseif($level == 2){
			$nLevelExps['min'] = 100;
			$nLevelExps['max'] = 200;
		}elseif($level == 3){
			$nLevelExps['min'] = 200;
			$nLevelExps['max'] = 400;
		}elseif($level == 4){
			$nLevelExps['min'] = 400;
			$nLevelExps['max'] = 800;
		}elseif($level == 5){
			$nLevelExps['min'] = 800;
			$nLevelExps['max'] = 1500;
		}elseif($level == 6){
			$nLevelExps['min'] = 1500;
			$nLevelExps['max'] = 3000;
		}elseif($level == 7){
			$nLevelExps['min'] = 3000;
			$nLevelExps['max'] = 6000;
		}elseif($level == 8){
			$nLevelExps['min'] = 6000;
			$nLevelExps['max'] = 15000;
		}elseif($level == 9){
			$nLevelExps['min'] = 15000;
			$nLevelExps['max'] = 30000;
		}

		return $nLevelExps;
	}
	/**
	 * 更新用户
	 *
	 */
	public function update_user($uid, $data){
		if(!is_numeric($uid) || !is_array($data)){
			return false;
		}

// 		$rs = $this->db->update('gl_user', $data, array( 'uid' => $uid ) );

		foreach ($data as $k => $v) {
			if (is_array($v)) {
				$this->db->set($k, $v[0], $v[1]);
			} else {
				$this->db->set($k, $v);
			}
		}
		$rs = $this->db->where(array( 'uid' => $uid ))->from('gl_user')->limit(1)->update();

		// 删除缓存
		$this->cache->redis->delete( $this->fld_cacheKey . $uid );

		return $rs;
	}

	/**
	 * 上传图片
	 * @param unknown $uid
	 * @param unknown $files
	 */
	public function upload_img( $files) {
		if (empty($files)) {
			return false;
		}

		$img = array();
		foreach ($files as $id => $file) {
			$_arr = $this->upload_imgs($file);
			if ($_arr['code']) {
				continue;
			}

			$data = array(
				'avatar' => gl_img_url($_arr['data']),
			);

			if($_arr['data']){
				$this->update_user($this->user_id,$data);
			}
		}

		return $_arr;//由返回$img改成$_arr  by 宋庆禄
	}
	public function upload_imgs($file) {
            $return = array(
                    'code' => 0,
                    'msg' => '',
                    'data' => ''
            );
            try {
                    //文件类型
                    $uptypes = array(
                            'image/jpg' => 'jpg',
                            'image/png' => 'png',
                            'image/gif' => 'gif',
                            'image/jpeg' => 'jpeg',
                            'image/bmp' => 'bmp',
							'image/x-png' => 'png', //IE8兼容
							'image/pjpeg' => 'jpg', //IE8兼容
                    );
                    $max_file_size = 5000000;   //文件大小限制1M
                    if( (empty($file) || !is_uploaded_file($file['tmp_name'])) ){

                            throw new Exception('not_img', -1);
                    }elseif(($file['error'])){

                            throw new Exception('img_err', -2);
                    }elseif(!($uptypes[$file['type']]) ){

                            throw new Exception('type_err', -3);
                    }elseif( (@filesize($file['tmp_name']) > $max_file_size)){

                            throw new Exception('max_size', -4);
                    }


                    $pic_path = 'glapp/user/avatar/' . ENVIRONMENT  . '/' . ($this->user_id % 100) . "/";

                    $tmp_pic = file_get_contents($file['tmp_name']);

					$content1 = $this->_save_resize($tmp_pic,'100','100');
					$picfile1 = $pic_path . $this->user_id . '/avatar.' . $uptypes[$file['type']];
					$content3 = $tmp_pic;
                    $picfile3 = $pic_path . $this->user_id . '/avatar_origin.' . $uptypes[$file['type']];

					/*$this->load->library('storeage');
					$ress1 = $this->storeage->upload( $content1 , $picfile1 , $file['type'] );
					$ress3 = $this->storeage->upload( $content3 , $picfile3 , $file['type'] );
					if(!$ress1){
                            throw new Exception('s1_err', -10);
                    }*/
					//$return['data'] = $picfile1 . '?' . time();

					 try {
					 	$CI = get_instance();
						$CI->load->config('oss_config', true);
				        $config = $CI->config->item('oss_config');
						$this->load->library('OSS/oss', $config);

						$this->oss->putObject($this->oss->getBucketName(), $picfile1, $content1);
						$this->oss->putObject($this->oss->getBucketName(), $picfile3, $content3);
					} catch (OssException $e) {
						throw new Exception('s1_err', -10);
					}


                    $return['data'] = NEW_IMG_PREFIX . $picfile1 . '?' . time();


            } catch (Exception $e) {
                    $return['code'] = $e->getCode();
                    $return['msg'] = $e->getMessage();
            }

            return $return;
    }

	/**
	 * 处理H5图片上传
	 *
	 */
	public function h5_upload_imgs($img_info, $img_type) {
			$return = array(
                    'code' => 0,
                    'msg' => '',
                    'data' => ''
            );

            try {
                    //文件类型
                    $uptypes = array('jpg','png','gif','jpeg','bmp','png','jpg');
                    $max_file_size = 5000000;   //文件大小限制1M

                    if( empty($img_info) || empty($img_type) ){
                            throw new Exception('not_img', -1);
                    }elseif(!in_array($img_type, $uptypes)){
                            throw new Exception('type_err', -3);
                    }


                    $pic_path = 'glapp/user/avatar/' . ENVIRONMENT  . '/' . ($this->user_id % 100) . "/";

                    $tmp_pic = $img_info;

					$content1 = $this->_save_resize($tmp_pic,'100','100');
					$picfile1 = $pic_path . $this->user_id . '/avatar.' . $img_type;
					$content3 = $tmp_pic;
                    $picfile3 = $pic_path . $this->user_id . '/avatar_origin.' . $img_type;

					//$this->load->library('storeage');
					//$ress1 = $this->storeage->upload( $content1 , $picfile1 , $file['type'] );
					//$ress3 = $this->storeage->upload( $content3 , $picfile3 , $file['type'] );

                    //if(!$ress1){
                    //        throw new Exception('s1_err', -10);
                    //}
					//echo 123;exit;
					//$return['data'] = $picfile1 . '?' . time();

					 try {
					 	$CI = get_instance();
					 	$CI->load->config('oss_config', true);
				         $config = $CI->config->item('oss_config');
					 	$this->load->library('OSS/oss', $config);

					 	$this->oss->putObject($this->oss->getBucketName(), $picfile1, $content1);
					 	$this->oss->putObject($this->oss->getBucketName(), $picfile3, $content3);
					 } catch (OssException $e) {
					 	throw new Exception('s1_err', -10);
					 }

                    //
                     $return['data'] = NEW_IMG_PREFIX . $picfile1 . '?' . time();

					// 更新用户头像
					$this->update_user( $this->user_id, array('avatar'=>$return['data']) );

            } catch (Exception $e) {
                    $return['code'] = $e->getCode();
                    $return['msg'] = $e->getMessage();
            }

            return $return;
    }

	//图片缩减函数
	private function _save_resize($picfile, $maxwidth='800', $maxheight='800'){
		if( empty($picfile)) return false;
		$def_image = @imagecreatefromstring($picfile);
		$def_width =imagesx($def_image);
		$def_height=imagesy($def_image);

      	//2. 计算压缩后的尺寸
      	if(($maxwidth/$def_width)<($maxheight/$def_height)){
            $w=$maxwidth;//新图片的宽度
            $h=($maxwidth/$def_width)*$def_height;//新图片的高度
      	}else{
            $h=$maxheight;//新图片的宽度
            $w=($maxheight/$def_height)*$def_width;//新图片的高度
      	}

		$newimg = imagecreatetruecolor($w,$h);
		imagecopyresized ($newimg, $def_image,0,0,0,0,$w, $h, $def_width, $def_height);
		imagedestroy($def_image);
		ob_start();
		imagejpeg($newimg);
		$content = ob_get_contents();
		ob_end_clean();
		imagedestroy($newimg);
		return $content;
	}
	/**
	 * 获取用户数据库信息
	 *
	 */
	private function _user_info($uid){
		$where = is_array ( $uid ) ? "WHERE `uid` in(" . implode ( ",", $uid ) . ")" : "WHERE `uid` = {$uid}";
		$sql = "SELECT * FROM `gl_user` {$where}";
		$query = $this->db->query_read ( $sql );
		$info = $query ? $query->result_array () : array ();
		return $info;
	}


	/**
	 * 验证用户昵称
	 *
	 */
	public function _check_nickname($nickname){
		$sql = "SELECT uid FROM `gl_user` where nickname = '".$nickname."' limit 1  ";
		$query = $this->db->query_read ( $sql );
		$info = $query ? $query->row_array () : array ();
		return $info['uid'] ? 1 :0;
	}

	/**
	 * 获取用户登录渠道数据库信息
	 *
	 */
	private function _user_channel($key, $ch, $wx_unionid){
		PLog::w_DebugLog('get_user_channel:' . $key." ".$ch." ".$wx_unionid);
		if(empty($this->ch_info[$ch])){
			return false;
		}
		$filed = $this->ch_info[$ch];
		if($filed == 'ms'){
			$filed = 'mobile';
		}elseif($filed == 'wx' && !empty($wx_unionid)){
			$key = $wx_unionid;
			$filed = 'wx_unionid';
		}

		if ($info === false || empty($info)) {
			$where = " where ". $filed . " = '$key' and ".$filed." != '' limit 1";
			$sql = "SELECT * FROM `gl_user_channel` {$where}";
			$query = $this->db->query_read ( $sql );
			$info = $query ? $query->row_array () : array ();
			PLog::w_DebugLog('glapp_user_channel_ SQL RESULT:' . serialize($info));
		}
		return $info;
	}

	public function get_channel_info_by_uid($uid) {
		$uid = $this->global_func->filter_int($uid);
		if (empty($uid)) {
			return false;
		}
		$cache_key = sha1("glapp_user_channel_info_" . $uid);
		$info = $this->cache->redis->get ( $cache_key );
		$info && $info = json_decode($info, true);
		if ($info === false) {
			$sql = "select * from gl_user_channel WHERE uid='$uid' LIMIT 1";
			$res = $this->db->query_read($sql);
			$info = $res ? $res->row_array() : array();
			$this->cache->redis->save ( $cache_key, $info, 600 );
		}
		return $info;
	}

    //用户列表
	public function get_user_list($start,$count) {
		$cache_key = sha1("get_user_list_" . $start . "_" . $count);
		$info = $this->cache->redis->get ( $cache_key );
		$info && $info = json_decode($info, true);
		if ($info == false) {
			$sql = "select * from gl_user WHERE status = 0 and user_type = 0 and uid <> '".$this->user_id."'  order by rank desc LIMIT ".intval($start).",".intval($count);
			$res = $this->db->query_read($sql);
			$info = $res ? $res->result_array() : array();

			$this->cache->redis->save ( $cache_key, $info, 60 );
		}

	    return $info;
	}
}
