<?php
/**
 * 
 * @name User_balance_model 攻略app 2.0 用户资金信息model
 * @desc null
 *
 * @author	 wangbo8
 * @date 2016年1月26日
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 */
class User_balance_model extends MY_Model {
	private $_cache_key_pre = '';
	private $_cache_expire = 600;
	protected $_table = 'gl_user_balance';
	
	function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":gl_user_balance2:";
	}

	public function delete_user_cache($uid) {
		$uid = $this->global_func->filter_int($uid);
		$cache_key = $this->_cache_key_pre . "user_balance:$uid";
		return $this->cache->redis->delete($cache_key);
	}
	//检查表中是否有对应数据，如果没有则添加该条数据
	public function checkIn($uid){
		$uid = $this->global_func->filter_int($uid);
		if (!$uid) {
			return false;
		}
		
		$cache_key = $this->_cache_key_pre . "user_balance:$uid";
		$hash_key = "checkin";
		$data = $this->cache->redis->hGet($cache_key,$hash_key, true);

		if($data === false){
			$data = 1;
			$sql = "SELECT uid FROM {$this->_table} WHERE uid='$uid' LIMIT 1";
			$res = $this->common_model->get_one_data_by_sql($sql);

			if(!is_array($res) || empty($res)){
				// insert
				$insert_data = array(
						'uid' => $uid
				);
				$res = $this->db->insert($this->_table, $insert_data);

				//获取默认背景图id
				$big_img_id_default = 0; //将来需要修改，用户需要默认背景图 (需要从后台设定)

				$res = array(
						'uid' => $uid,
						'balance' => 0,
						'total_earning' => 0,
						'alipay_account' => '',
						'alipay_name' => '',
						'alipay_qcode' => '',
						'gestures_password' => '',
						'big_img_id' => 0, 
					);

				$hash_info_key = "info";
				$this->cache->redis->hSet($cache_key, $hash_info_key, json_encode($res));
			}

			$this->cache->redis->hSet($cache_key, $hash_key, $data);
			$this->cache->redis->expire($cache_key, $this->_cache_expire);
		}

		return $data;
	}

	//根据uid获取当前用户背景图片
	public function getInfo($uid) {
		$uid = $this->global_func->filter_int($uid);
		if (!$uid) {
			return false;
		}
		$this->checkIn($uid); 

		$cache_key = $this->_cache_key_pre . "user_balance:$uid";
		$hash_key = "info";
		$data = $this->cache->redis->hGet($cache_key, $hash_key, true);
		$data && $data = json_decode($data, true);

		if ($data === false) {
			$data = $this->_get_userBalance_from_db($uid);
			$this->cache->redis->hSet($cache_key, $hash_key, json_encode($data));
			$this->cache->redis->expire($cache_key, $this->_cache_expire);
		}
		
		if ($data['alipay_qcode']) {
			// 显示原图，解决看不清的问题。
			$data['alipay_qcode'] = str_replace('alipay_qcode', 'alipay_qcode_origin', $data['alipay_qcode'] );
		}
		

		return $data;
	}

	//入库通过uid获取用户资金信息
	private function _get_userBalance_from_db($uid) {
		$conditions = array(
				'table' => $this->_table,
				'where' => array(
						'uid' => $uid
				),
				'limit' => 1,
		);

		$sql = $this->find($conditions);
		$rs = $this->common_model->get_one_data_by_sql($sql);
		return $rs;
	}

	//更新用户背景图信息
	public function save_bgimg($uid, $bgid) {
		if (!$uid || !$bgid) {
			return false;
		}
		$this->checkIn($uid);

		$return = $this->_save_bgimg_to_db($uid, $bgid);
		$this->delete_user_cache($uid);
		return $return;
	}

	//执行保存
	private function _save_bgimg_to_db($uid, $bgimg) {
		if (empty($uid) || empty($bgimg) ) {
			return false;
		}

		// update
		$this->db->from($this->_table);
		$this->db->set('big_img_id', $bgimg);
		$this->db->where('uid', $uid);
		$this->db->limit(1);
		$this->db->update();

		return 1;
	}

	//修改用户其他信息
	public function save_blance_info($uid, $data){
		//生成sql语句
		$sql = $this->update(array('uid' => $uid), $data,'');

		$res = $this->common_model->execute_by_sql($sql);

		//删除缓存
		// if($res){

		// }
		$this->delete_user_cache($uid);
		return $res;
	}

	//=================================================================================
	/**
	 * 打赏
	 * @param  [type] $uid      [description]
	 * @param  [type] $minus    [description]
	 * @return [type]           [description]
	 */
	public function balance_reward($uid, $minus) {
		$uid = $this->global_func->filter_int($uid);
		$minus = $this->global_func->filter_int($minus);
		if ($minus <= 0)  {
			return false;
		}
		
		$this->checkIn($uid);	// 初始化uid, 以免没记录
		
		$sql = "UPDATE $this->_table SET balance = balance-$minus WHERE uid='$uid' AND balance >= $minus LIMIT 1";
		$this->db->query_write($sql);
		if (mysql_affected_rows($this->db->conn_write)) {
			$return = 1;
		} else {
			$return = 0;
		}
		PLog::w_DebugLog("uid:$uid minus balance $minus, state $return" );
		return $return;
	}
	public function user_balance_info_change($uid, $total_earning,$total_paying, $balance_reward_total) {
		$uid = $this->global_func->filter_int($uid);
		$total_earning = $this->global_func->filter_int($total_earning);
		$total_paying = $this->global_func->filter_int($total_paying);
		$balance_reward_total = $this->global_func->filter_int($balance_reward_total);
		
		$set_arr = array();
		if ($total_earning) {
			$set_arr[] = ' total_earning=total_earning+' . $total_earning;
		}
		if ($total_paying) {
			$set_arr[] = ' total_paying=total_paying+' . $total_paying;
		}
		if ($balance_reward_total) {
			$set_arr[] = ' balance_reward_total=balance_reward_total+' . $balance_reward_total;
		}
		
		$return = 0;
		if ($uid && $set_arr) {
			$sql = "UPDATE $this->_table SET " . implode(',', $set_arr) ." WHERE uid='$uid' LIMIT 1";
			$this->db->query_write($sql);
			if (mysql_affected_rows($this->db->conn_write)) {
				$return = 1;
			} else {
				$return = 0;
			}
			PLog::w_DebugLog("uid:$uid change balance info: $sql, state $return" );
		}
		return $return;
	}
	
	// ===================================================================
	public function balance_withdraw($uid, $minus, $pay_account, $pay_name) {
		$uid = $this->global_func->filter_int($uid);
		$minus = $this->global_func->filter_int($minus);
		if ($minus <= 0)  {
			return false;
		}
		
		$this->checkIn($uid);	// 初始化uid, 以免没记录
		
		$sql = "UPDATE $this->_table SET balance = balance-$minus WHERE uid='$uid' AND balance >= $minus LIMIT 1";
		$this->db->query_write($sql);
		if (mysql_affected_rows($this->db->conn_write)) {
			$return = 1;
		} else {
			$return = 0;
		}
		PLog::w_DebugLog("uid:$uid withdraw balance $minus, state $return" );
		return $return;
	}
	
	public function balance_add($uid, $add) {
		$uid = $this->global_func->filter_int($uid);
		$add = $this->global_func->filter_int($add);
		if ($add <= 0)  {
			return false;
		}
		
		$this->checkIn($uid);	// 初始化uid, 以免没记录
		
		$set = '';
		
		
		$sql = "UPDATE $this->_table SET balance = balance+$add $set WHERE uid='$uid' LIMIT 1";
		
		$this->db->query_write($sql);
		if (mysql_affected_rows($this->db->conn_write)) {
			$return = 1;
		} else {
			$return = 0;
		}
		PLog::w_DebugLog("uid:$uid add balance $minus, state $return" );
		return $return;
	}

	//验证用户手势密码
	public function verify_gpw($uid, $newgpw){
		//拼装cachekey
		$forbidKey = $this->_cache_key_pre . "user_balance_newgps_num:{$uid}";

		//验证通过，获取当前用户可用次数
		$nums = $this->cache->redis->get($forbidKey);
		$allow_nums = GPW_TIMES_LIMIT - $nums;
		$allow_nums = $allow_nums > 1 ? $allow_nums : 1;

		//检查参数有效性
		$gpw_times = $this->check_gpw_r($newgpw);
		if(!$gpw_times){
			$this->save_wrong_times($forbidKey);
			$return = array('gestureState'=>0, 'availableTime'=>$allow_nums - 1);
			return $return;
		}

		//获取当前用户手势密码
		$user_info = $this->getInfo($uid);
		$user_gpw = $user_info['gestures_password'];

		//检查是否正确
		if($newgpw != $user_gpw){ //检查不通过
			//设置次数
			$this->save_wrong_times($forbidKey);
			$return = array('gestureState'=>0, 'availableTime'=>$allow_nums - 1);
		}else{
			//验证通过，获取当前用户可用次数
			$this->save_wrong_times($forbidKey, 0);
			$allow_nums = GPW_TIMES_LIMIT;
			$return = array('gestureState'=>1, 'availableTime'=>$allow_nums);
		}

		//返回正确结果
		return $return;
	}

	//错误次数入缓存
	private function save_wrong_times($forbidKey, $add = 1){
		if ($add) {
			if($this->cache->redis->exists($forbidKey)){
				$nums = $this->cache->redis->incr($forbidKey);
			}else{
				//获得明日时间戳
				$exprietime = strtotime(date('Y-m-d',strtotime('+1 day')));
				
				$nums = $this->cache->redis->incr($forbidKey);
				$this->cache->redis->expireAt($forbidKey, $exprietime);
			}
		} else {
			// 删除
			$this->cache->redis->delete($forbidKey);
		}
		

		return 1;
	}

	//检查验证码合理性
	public function check_gpw_r($newgpw, $checktimes = true){
		//常规检验
		$gpw_check_normal = $this->_check_gpw_normal($newgpw);

		if(!$gpw_check_normal){
			return false;
		}

		if($checktimes){
			//检查验证次数
			$gpw_times = $this->get_check_gpw_times();

			if($gpw_times >= GPW_TIMES_LIMIT){ //当前验证次数，已经超过限制
				return 0;
			}
		}

		//返回验证通过
		return true;
	}

	private function _check_gpw_normal($newgpw){
		//有无值，是否数字检查
		if(!$newgpw){
			return false;
		}

		$newgpw_str = (string)$newgpw;
		$newgpw_arr = str_split($newgpw_str);
		$newgpw_len = count($newgpw_arr);

		//检查长度
		if($newgpw_len < 4 || $newgpw_len > 9){
			return false;
		}

		//检查是否有重复
		$maxnum = max(array_count_values($newgpw_arr));
		if($maxnum > 1){
			return false;
		}

		return true;
	}

	//获取当前用户手势验证次数
	public function get_check_gpw_times(){
		$uid = $this->user_id;

		//拼装cachekey
		$cache_key = $this->_cache_key_pre . "user_balance_newgps_num:{$uid}";

		//获取次数
		$data = $this->cache->redis->get($cache_key);

		return $data ? $data : 0;
	}

	//产生随机码方法
	public function get_randcode($class, $method, $uid, $seed_key_str = '', $expire_time = 7200){
		//判断
		if(!$class || !$method || !$uid){
			return false;
		}

		if($seed_key_str){
			$seed_pre_arr = explode('|', $seed_key_str);
			$seed_key_pre = $seed_pre_arr[0] . "-";
		}else{
			$seed_key_pre = '';
		}

		//拼装前置
		$pre = md5($seed_key_pre . $class . "_" . $method);

		//调用方法生成随机码
		$vcode = $this->Comm->get_code(8,6);   
		$pre_seed = $pre . "|" . $vcode;

		//保存随机码key
		$redis_key = "glapp:users:rand_code:chainlist:" . $pre_seed . ':code_' .$uid ;
		$redis_key_pre = "glapp:users:rand_code:chainlist:" . $seed_key_str . ':code_' .$uid ;

		//删除之前的码
		$this->cache->redis->delete($redis_key);
		$this->cache->redis->delete($redis_key_pre);

		//保存新码
		$this->cache->redis->save($redis_key, $vcode, $expire_time);

		//返回
		return $pre_seed;
	}

	//检查随机码方法
	public function check_randcode($class, $method, $uid, $old_seed = ''){
		if(!$class || !$method || empty($uid)){
			return false;
		}

		//随机码防刷
		$rKey = "glapp:users:rand_code:chainlist:num:" . $old_seed . ':checktimes_' .$uid ; //失败次数key
		$forbidKey = "glapp:users:randcode:failed_forbid_" . $old_seed . ':' . $uid; //是否禁止验证key

		//获取当前手机号失败次数
		$fNum = intval($this->cache->redis->get($rKey));

		//判断是否超过次数限制
		if($fNum >= 9){
			//将拒绝标识置为真
			$this->cache->redis->save($forbidKey, 1, 3600);
		}

		//获取随机码
		$redis_key = "glapp:users:rand_code:chainlist:" . $old_seed . ':code_' .$uid ;
		$val = $this->cache->redis->get($redis_key);

		//带入seed检查
		$val_arr = explode('|', $old_seed);
		$seed_chainlist = $val_arr[0];
		$in_vcode = $val_arr[1];

		//检查当前业务线是否合法
		$check_chain = $this->check_chain($seed_chainlist);

		if(!$check_chain){
			return false;
		}

		//比较是否相同
		if($val && $val == $in_vcode){
			//返回真
			//删除验证key
			$this->cache->redis->delete($redis_key);

			return true;
		}else{
			//验证不通过
			//限制每分钟验证次数
			if($this->cache->redis->exists($rKey)){
				//存在，加一
				$this->cache->redis->incr($rKey);
			}else{
				//不存在，定义过期时间
				$this->cache->redis->incr($rKey);
				$this->cache->redis->expire($rKey, 60);
			}
			return false;
		}
	}

	private function check_chain($seed_chainlist){
		//获取合理业务线配置文件
		$this->load->config('check_seed_chainlist', true);
		$check_seed_chainlist = $this->config->item('check_seed_chainlist');
		$check_seed_chainlist = $this->_cov_seed_chainlist_set($check_seed_chainlist); //获取业务线数组

		if(!$check_seed_chainlist || !is_array($check_seed_chainlist)){
			return false;
		}

		//循环遍历当前业务线合法性
		if(!in_array($seed_chainlist, $check_seed_chainlist)){
			return false; //业务线不合法
		}

		//返回真
		return true;
	}

	private function _cov_seed_chainlist_set($check_seed_chainlist){
		if(empty($check_seed_chainlist) || !is_array($check_seed_chainlist)){
			return false;
		}

		//初始化返回数组
		$return = array();
		$list_str = '';

		//遍历各条业务线
		foreach($check_seed_chainlist as $val_arr){
			if(is_array($val_arr) && count($val_arr) > 0){
				foreach($val_arr as $val){
					if(!$list_str){
						$list_str = md5($val);
					} else {
						$list_str = md5($list_str . '-' . $val);
					}
					$return[] = $list_str;
				}
			}

			//清空当前
			$list_str = '';
		}

		//返回
		return !empty($return) ? $return : false;
	}

	//删除随机码方法
	public function clear_randcode($seed, $uid){
		$uid = intval($uid);
		if(empty($uid) || !$seed){
			return false;
		}

		//拼装key
		$redis_key = "glapp:users:rand_code:chainlist:" . $seed . ':code_' .$uid ;
		return $this->cache->redis->delete($redis_key);
	}

	/**
	 * 上传图片
	 * @param unknown $uid
	 * @param unknown $files
	 */
	public function upload_img( $files) {
		if (empty($files)) {
			//头像数据为空，则为删除头像信息
			$data = array(
				'alipay_qcode' => $_arr['data'],
			);

			$this->save_blance_info($this->user_id,$data);
			return false;
		}
		$uid = $this->user_id;
		$this->checkIn($uid); 

		$img = array();
		foreach ($files as $id => $file) {
			$_arr = $this->upload_imgs($file);
			if ($_arr['code']) {
				continue;
			}

			$data = array(
				'alipay_qcode' =>  $_arr['data'],
			);

			if($_arr['data']){
				$this->save_blance_info($this->user_id,$data);
			}
		}

		return $_arr;
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

                    // $this->load->library('storeage');
                    $pic_path = 'glapp/user/avatar/' . ENVIRONMENT  . '/' . ($this->user_id % 100) . "/";
					
                    $tmp_pic = file_get_contents($file['tmp_name']);
					
					$content1 = $this->_save_resize($tmp_pic,'250','250');
					$picfile1 = $pic_path . $this->user_id . '/alipay_qcode.' . $uptypes[$file['type']];
					$content3 = $tmp_pic;
                    $picfile3 = $pic_path . $this->user_id . '/alipay_qcode_origin.' . $uptypes[$file['type']];
					
					// $ress1 = $this->storeage->upload( $content1 , $picfile1 , $file['type'] );
					// $ress3 = $this->storeage->upload( $content3 , $picfile3 , $file['type'] );
					// 
                    // if(!$ress1){
                    //         throw new Exception('s1_err', -10);
                    // }
                    
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


                    // $return['data'] = IMAGE_URL_PRE.$picfile1 . '?' . time();
                    $return['data'] = NEW_IMG_PREFIX . $picfile1 . '?' . time();

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

}
