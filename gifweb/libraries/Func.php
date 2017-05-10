<?php

/**
 * Func.php
 *
 * Copyright (c) 2012 SINA Inc. All rights reserved.
 *
 * @author	ligangzong <gangzong@staff.sina.com.cn>
 * @date	17:01:00 2012-12-10
 * @version	$Id: Func.php 47 2012-12-25 07:36:02Z gangzong $
 * @desc	This guy is so lazy that he doesn't leave anything.
 */
class Func
{

	/**
	 * standard output
	 * @param mixed $var
	 * @param boolean $echo
	 * @param string $label
	 * @param boolean $strict
	 * @return null|string
	 */
	public static function dump($var, $echo = true, $label = null, $strict = true)
	{
		$label = ($label === null) ? '' : rtrim($label) . ' ';
		if (!$strict) {
			if (ini_get('html_errors')) {
				$output = print_r($var, true);
				$output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
			} else {
				$output = $label . print_r($var, true);
			}
		} else {
			ob_start();
			var_dump($var);
			$output = ob_get_clean();
			if (!extension_loaded('xdebug')) {
				$output = preg_replace("/\]\=\>\n(\s+)/m", '] => ', $output);
				$output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
			}
		}
		if ($echo) {
			echo($output);
			return null;
		} else {
			return $output;
		}
	}

	/**
	 * 安全过滤函数
	 *
	 * @param $string
	 * @return string
	 */
	public static function safeReplace($string)
	{
		$string = str_replace('%20', '', $string);
		$string = str_replace('%27', '', $string);
		$string = str_replace('%2527', '', $string);
		$string = str_replace('*', '', $string);
		$string = str_replace('"', '&quot;', $string);
		$string = str_replace("'", '', $string);
		$string = str_replace('"', '', $string);
		$string = str_replace(';', '', $string);
		$string = str_replace('<', '&lt;', $string);
		$string = str_replace('>', '&gt;', $string);
		$string = str_replace("{", '', $string);
		$string = str_replace('}', '', $string);
		$string = str_replace('\\', '', $string);
		return $string;
	}

	/**
	 * 生成随机字符串
	 * @param string $lenth 长度
	 * @return string 字符串
	 */
	public static function randomStr($lenth = 6)
	{
		return self::random($lenth, '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ');
	}

	/**
	 * 产生随机字符串
	 *
	 * @param    int        $length  输出长度
	 * @param    string     $chars   可选的 ，默认为 0123456789
	 * @return   string     字符串
	 */
	public static function random($length, $chars = '0123456789')
	{
		$hash = '';
		$max = strlen($chars) - 1;
		for ($i = 0; $i < $length; $i++) {
			$hash .= $chars[mt_rand(0, $max)];
		}
		return $hash;
	}

	/**
	 * 获取请求ip
	 *
	 * @return ip地址
	 */
	public static function ip()
	{
		if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
			$ip = getenv('HTTP_CLIENT_IP');
		} elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
			$ip = getenv('REMOTE_ADDR');
		} elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return preg_match('/[\d\.]{7,15}/', $ip, $matches) ? $matches [0] : '';
	}

	public static function formatEmpty($data)
	{
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$data[$key] = self::formatEmpty($value);
			}
		} else {
			if (empty($data)) {
				return '--';
			} else {
				return $data;
			}
		}
		return $data;
	}

	/**
	 * 获取当前页面完整URL地址
	 */
	public static function getUrl()
	{
		$sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
		$php_self = $_SERVER['PHP_SELF'] ? self::safeReplace($_SERVER['PHP_SELF']) : self::safeReplace($_SERVER['SCRIPT_NAME']);
		$path_info = isset($_SERVER['PATH_INFO']) ? self::safeReplace($_SERVER['PATH_INFO']) : '';
		$relate_url = isset($_SERVER['REQUEST_URI']) ? self::safeReplace($_SERVER['REQUEST_URI']) : $php_self . (isset($_SERVER['QUERY_STRING']) ? '?' . self::safeReplace($_SERVER['QUERY_STRING']) : $path_info);
		return $sys_protocal . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . $relate_url;
	}

}

?>