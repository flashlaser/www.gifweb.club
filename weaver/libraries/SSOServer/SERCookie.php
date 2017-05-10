<?php
/**
 * SSOCookie class file.
 *
 * @package SSOCookie
 * @author haibo8 <haibo8@staff.sina.com.cn>
 * @copyright Copyright (c) 2015 SINA R&D Centre
 * @version $Id: $
 */

include_once ("SERBase.php");
include_once ("serapc.php");

/**
 * set & get cookie for 97973.com
 */
class SERCookie extends SERBase {
	const COOKIE_SUE		= 'GSUE';   //sina user encrypt info
	const COOKIE_SUP		= 'GSUP';   //sina user plain info
	const COOKIE_SUS		= 'GSUS';   //session id
	const COOKIE_ALF 		= 'GALF';   //alf
	const COOKIE_PATH		= '/';
	const COOKIE_DOMAIN 	= '.97973.com';
	const COOKIE_KEY_FILE	= 'key/private_cookie.key';

	const COOKIE_CHECK_DOMAIN = 1;
	const COOKIE_CHECK_IP     = 2;

	/**
	 * cookie conf中定义方式如下
	 *		rv=1
	 *		rv1=xxxxx
	 *		rv2=yyyyyy
	 * rv为当前使用的版本号，rv[n]为该版本号的base64_encode(公钥)
	 */
	const COOKIE_SIGN_VERSION_NAME	= 'rv';
	/**
	 * cookie的SUE中rs[n]即为不同版本的签名
	 */
	const COOKIE_SIGN_VALUE_NAME	= 'rs';
	
	/**
	 * 配置信息缓存在apc中所用的key的前缀
	 */
	const APC_CACHE_PREFIX = 'sso_apc_973_';
	/**
	 * 为了类的通用性，不再使用const常量，但会将其作为默认值保留
	 */
	public static $COOKIE_SUE = self::COOKIE_SUE;
	public static $COOKIE_SUP = self::COOKIE_SUP;
	public static $COOKIE_SUS = self::COOKIE_SUS;
	public static $COOKIE_ALF = self::COOKIE_ALF;

	public static $COOKIE_PATH     = self::COOKIE_PATH;
	public static $COOKIE_DOMAIN   = self::COOKIE_DOMAIN;

	/**
	 * rsa version
	 * @var int
	 */
	private $_rsa_version			= 0;
	
	private $_priateKey; // the infomation in cookie.conf
	private $_arrCookie;
	private $_cookieCheckLevel = 0; // 1: domain, 2: ip, 3: domain and ip 
	private $_cookieIp = '';
	
	/**
	 * apc缓存中保存配置信息的过期时间
	 * @var unknown_type
	 */
	private $apc_expiration_time = 300;

	public function __construct($config = self::COOKIE_KEY_FILE) {
		if(!$this->_parseConfigFile($config)){
			throw new Exception($this->getError());
		}
	}
	
	/**
	 * 对md5信息做rsa加密
	 * 
	 * @param string $sup
	 * @return string 
	 */
	public function signRsa($data){
		if(empty($data)){
			return false;
		}
		// RSA加密
		openssl_private_encrypt($data,$encrypted,$this->_priateKey);//私钥加密  
		$encrypted = base64_encode($encrypted);//加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
		
		return $encrypted;
	} 

	/**
	 * 对supg做签名
	 * 
	 * @param string $sup
	 * @return string 
	 */
	public function signMd5($str) {
		return md5($str);
	}

	/**
	 * 从conf中获取私钥
	 * 
	 * @return string
	 */
	private function _getPriateKey($key_file) {
		$private_key = file_get_contents($key_file);
		return $private_key;
	}

	/**
	 * parse cookie config file.
	 * @param $config: cookie config file
	 */
	private function _parseConfigFile($config) {
		//首先试图中apc缓存中得到配置
		$apc_key = self::APC_CACHE_PREFIX . $config;
		$arrConf = SERApc::getInstance()->get($apc_key);
		if ($arrConf === false) {
			$arrConf = $this->_getPriateKey($config);
		}
	
		if(!$arrConf) {
			$this->_setError('parse file '.$config . ' error');
			return false;
		}
		$this->_priateKey = $arrConf;
		SERApc::getInstance()->set($apc_key, $arrConf, $this->apc_expiration_time);
		return true;
	}

	/**
	 * 通过header()函数输出cookie
	 * @param string $param 
	 */
	public function headerCookie($cookie) {
		if(is_array($cookie)){
			$header = $cookie;
		}else{
			$cookie = trim($cookie);
			if (!$cookie) {
				return false;
			}
			$header = explode("\n", $cookie);
		}
		
		foreach($header as $line) {
			header($line, false);
		}
		return true;
	}

	/**
	 * 设置apc缓存中保存配置信息的过期时间
	 * @param	int	$time
	 */
	public function set_apc_expiration_time($time) {
		$this->apc_expiration_time = $time;
	}
}


?>
