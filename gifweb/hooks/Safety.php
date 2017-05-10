<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
/**
 *
 * @name Safety
 * @author liule1
 *         @date 2015年7月8日
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 */
class Safety {
	private $_safe_key;
	private $_folder_api;
	public function __construct() {
		require_once FCPATH . APPPATH . "/libraries/Util.php";
		// safe.inc.php 中的
		$this->_safe_key = $config ['safe_key'];
	}
	private function _is_app_api() {
		$RTR =& load_class('Router', 'core');

		//初始化需要检验的目录
		$folders_need_check = array(
				'api/', //攻略项目
				'apiadd/', //炫耀党项目
				'apiqihu/', //360项目接口
		);

		//获得当前接口申请目录
		$folder_api = $this->_folder_api = $RTR->fetch_directory();
		$res = in_array($folder_api, $folders_need_check);

		return $res ? 1 : 0;

		//return $RTR->fetch_directory() === 'api/' ? 1 : 0;
	}
	public function verify() {
		if (!$this->_is_app_api()) {
			return;
		}

		//处理safe_key数组
		$this->format_safe_key();

		// 获取传参fetch_directory
		$getrow = Util::getpost ();

		// 前端都进行了ENCODE，POST会自动 DECODE，这里把GET预先处理下
		foreach ($_GET as $k => $v) {
			$_GET[$k] = urldecode($v);
		}

		// 获取传参fetch_directory
		$getrow = Util::getpost ();

		// 测试
		if (isset ( $getrow ['sign'] ) && $getrow ['sign'] === 'backend') {
			if (ENVIRONMENT == 'testing' || (ENVIRONMENT == 'production' && ($_SERVER['SERVER_ADDR'] == '10.13.32.235' || $_SERVER['SERVER_ADDR'] == '10.13.32.237'))) {
				return;
			}
		}

		if (! isset ( $getrow ['partner_id'] ) || ! isset ( $getrow ['sign'] )) {
			Util::echo_format_return( _SIGN_ERROR_, array (), '校验失败1' , 1);
		}
		$partner_id = intval ( $getrow ['partner_id'] );

		// 获取验证参数
		$safe_key = $this->_safe_key;
		$parnter_key = isset ( $safe_key [$partner_id] ) ? $safe_key [$partner_id] : false;

		// 安卓默认
		if (!$parnter_key) {
			if (strtolower($getrow['platform'] ) == 'android') {
				$parnter_key = isset ( $safe_key ['10002'] ) ? $safe_key ['10002'] : false;
			}
		}
		if (! $parnter_key) { // 未通过验证
			Util::echo_format_return( _SIGN_ERROR_, array (), '校验失败2' , 1);
		}
		/*
		// 验证参数
		$str = "";
		foreach ( $getrow as $k => $v ) {
			if (in_array ( $k, array (
					"partner_id",
					"sign"
			) )) {
				continue;
			}
			$str .= isset($_POST[$k]) ? $_REQUEST[$k] : urldecode($_REQUEST[$k]);
		}

		$str .= $parnter_key['package_id'];
		$sign = substr($str, 0, 2) . substr($parnter_key['key'], 2, 20) . substr($str, 2);
		$sign = md5($sign);
		*/

		//不同申请源，不同规则sign
		$sign = $this->create_sign_by_api($getrow, $parnter_key);
		
		if (! isset ( $getrow ['sign'] ) || $getrow ['sign'] !== $sign) {
			Util::echo_format_return( _SIGN_ERROR_, array (), '校验失败3' , 1);
		}
	}

	//根据不同平台走不同的加密规则，生成sign
	private function create_sign_by_api($getrow, $parnter_key){
		//通过api接口文件目录，判断加密规则
		switch($this->_folder_api){
			case "api/":
			case "apiadd/": //攻略跟炫耀党用的是同一套加密规则(兄弟单位嘛，不用那么防)
				$str = "";
				foreach ( $getrow as $k => $v ) {
					if (in_array ( $k, array (
							"partner_id",
							"sign"
					) )) {
						continue;
					}
					$str .= isset($_POST[$k]) ? $_REQUEST[$k] : urldecode($_REQUEST[$k]);
				}

				$str .= $parnter_key['package_id'];
				$sign = substr($str, 0, 2) . substr($parnter_key['key'], 2, 20) . substr($str, 2);
				$sign = md5($sign);
				break;
			case "apiqihu/": //给360单独开一套加密规则
				$str = "";
				foreach ( $getrow as $k => $v ) {
					if (in_array ( $k, array (
							"partner_id",
							"sign"
					) )) {
						continue;
					}
					$str .= isset($_POST[$k]) ? $_REQUEST[$k] : urldecode($_REQUEST[$k]);
				}

				$str .= $parnter_key['package_id'];
				$sign = md5($str) . $parnter_key['key'];
				$sign = md5($sign);
				break;
			default:
				$sign = false;
				break;
		}

		//返回sign
		return $sign;
	}
	
	private function format_safe_key(){
		//调用方法获取接口数组
		$package_key_arr = $this->get_package_key_arr();
		$safe_arr_old = $this->_safe_key;
	
		//遍历，判断，替换
		foreach($package_key_arr as $k=>$v){
			//两个参不为空
			if($v['package_id'] && $v['key']){
				//执行替换 或者 增加
				$safe_arr_old[$k] = $v;
			}
		}
		
		$this->_safe_key = $safe_arr_old;
	}
	
	//获取缓存中的安全配置
	private function get_package_key_arr(){
		$cache_dir = $_SERVER['SINASRV_CACHE_DIR'] ? $_SERVER['SINASRV_CACHE_DIR'] . 'config/PackageKey/' : '/tmp/config/PackageKey/';  // 缓存目录
		$cache_file = $cache_dir . 'PackageKey.cache'; //定义缓存文件名称

		$cache_expire = 60 * 30; //缓存文件保持30分钟

		//判断是否有缓存目录，如果没有则生成
		if(!is_dir($cache_dir)){
			$this->m_MakeDirs($cache_dir, 0777); //权限777
		}

		if(!is_file($cache_file) || !file_exists($cache_file)){
			file_put_contents($cache_file, '');
		}

		require_once FCPATH . APPPATH . "/libraries/pagecache.php";
		$ll_cache_obj = new pagecache($cache_file, $cache_expire);

		//尝试从缓存中获取信息
		$data = $ll_cache_obj->get(false);
		$data && $data = json_decode($data, true);
		$repeat = 3;
		
		//判断
		if($data === false || $data === ""){
			//缓存没有数据，从接口重复拉取
			while($repeat-- > 0 && ($data === false || $data === "")){
				//远程抓取数据
				$data = $this->get_package_key_arr_from_api();
			}

			if($data){
				//数据入缓存
				$ll_cache_obj->set(json_encode($data));
			}
		}

		//返回 
		return $data;
	}
	
	private function m_MakeDirs ($dir, $mode = 0777){
		if (! is_dir($dir))
		{
			$this->m_MakeDirs(dirname($dir), $mode);
			$result = mkdir($dir, $mode);
			$result = chown($dir, "www");
			$result = chgrp($dir, "www");
			return $result;
		}
		return true;
	}
	
	private function get_package_key_arr_from_api(){
		//拼装安全机制
		list($tmp1, $tmp2) = explode(' ', microtime());
		$timestamp =  (float)sprintf('%.0f', (floatval($tmp1) + floatval($tmp2)) * 1000);

		$version = "1.0.0";
		$platform = "android";
		$partner_id = 10002;
		
		$package_id = '-1526612315';
		$key = '2872978FDE93543326E247CF7A00AF53';
		
		//开始拼装
		$str = $timestamp . $version . $platform . $package_id;
		
		//执行处理
		$sign = substr($str, 0, 2) . substr($key, 2, 20) . substr($str, 2);
		$sign = md5($sign);
		$domain = "www.wan68.com";
		$host_arr = array('gl.games.sina.com.cn','www.wan68.com','wan68.com');
		$domain = in_array($_SERVER['HTTP_HOST'], $host_arr) ? $_SERVER['HTTP_HOST'] : "gl.games.sina.com.cn";
		$url = "http://{$domain}/inner_api/get_package_and_key?timestamp={$timestamp}&version={$version}&platform={$platform}&partner_id={$partner_id}&sign={$sign}";
		$data = file_get_contents($url);
		$data && $data = json_decode($data, true);
		
		$data = $data['data'];
		
		//返回结果
		return $data;
	}
	
}
