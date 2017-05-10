<?php
/**
 * SSOCookie class file.
 *
 * @package SSOClinent
 * @author lijunjie <junjie2@staff.sina.com.cn>
 * @author liuzhiyu <zhiyu@staff.sina.com.cn>
 * @copyright Copyright (c) 2011 SINA R&D Centre
 * @version $Id: $
 */

include_once (SYSDIR."/libraries/SSOClient/SSOBase.php");
include_once (SYSDIR."/libraries/SSOClient/ssoapc.php");
/**
 * set & get cookie for sina.com.cn
 */

class SSOCookie extends SSOBase {
	const COOKIE_SUE		= 'GSUE';   //sina user encrypt info
	const COOKIE_SUP		= 'GSUP';   //sina user plain info
	const COOKIE_SUS		= 'GSUS';   //session id
	const COOKIE_ALF 		= 'GALF';   //alf
	const COOKIE_PATH		= '/';
	const COOKIE_DOMAIN 	= '.wan68.com';
	const COOKIE_KEY_FILE	= '';

	const COOKIE_CHECK_DOMAIN = 1;
	const COOKIE_CHECK_IP     = 2;

	/**
	 * cookie conf中定义方式如下
	 *		rv=1
	 *		rv1=xxxxx
	 *		rv2=yyyyyy
	 * rv为当前使用的版本号，rv[n]为该版本号的base64_encode(公钥)
	 */
	const COOKIE_SIGN_VERSION_NAME	= 'grv';
	/**
	 * cookie的SUE中rs[n]即为不同版本的签名
	 */
	const COOKIE_SIGN_VALUE_NAME	= 'grs';

	/**
	 * 配置信息缓存在apc中所用的key的前缀
	 */
	const APC_CACHE_PREFIX = 'glapp_apc_client_';
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

	private $_arrConf; // the infomation in cookie.conf
	private $_arrKeyMap		= array(
		'cv'	=> 'cookieversion',
		'bt'	=> 'begintime',
		'et'	=> 'expiredtime',
		'uid'	=> 'uniqueid',
		'nickname'	=> 'nickname',
		'sex'	=> 'gender',
		'avatar'	=> 'avatar'
	);
	private $_arrCookie;
	private $_cookieCheckLevel = 0; // 1: domain, 2: ip, 3: domain and ip
	private $_cookieIp = '';

	/**
	 * apc缓存中保存配置信息的过期时间
	 * @var unknown_type
	 */
	private $apc_expiration_time = 0;

	public function __construct($config = self::COOKIE_KEY_FILE) {
		if(!$this->_parseConfigFile($config)){
			throw new Exception($this->getError());
		}
		$this->_arrCookie = $_COOKIE;
	}

	public function setCookieDomain($domain){
		self::$COOKIE_DOMAIN = $domain;
	}
	public function setCookieIp($ip){
		$this->_cookieIp = $ip;
	}
	public function setCookieCheckLevel($level) {
		$this->_cookieCheckLevel = $level;
	}

	public function getCookie(&$arrUserInfo, $use_rsa_sign=false) {
		$sup = $this->_arrCookie[self::$COOKIE_SUP];
		if(!$sup) return false;

		parse_str($sup,$arrSUP);
		$cookieVersion = $arrSUP['cv'];
		switch($cookieVersion) {
			case '1.0':
				return $this->_validateCookieV1($arrUserInfo, $use_rsa_sign);
				break;
			default:
				return false;
		}
	}

	/**
	 * delete cookie
	 */
	public function delCookie() {
		// 产品可以在这里删除自己的cookie
		if (isset($_COOKIE[self::$COOKIE_SUE]) || isset($_COOKIE[self::$COOKIE_SUP])) {
			$this->_delete_cookie(self::$COOKIE_SUE,self::$COOKIE_PATH,self::$COOKIE_DOMAIN);
			$this->_delete_cookie(self::$COOKIE_SUP,self::$COOKIE_PATH,self::$COOKIE_DOMAIN);
			unset($_COOKIE[self::$COOKIE_SUE]);
			unset($_COOKIE[self::$COOKIE_SUP]);
		}
		return true;
	}

	public function setCustomCookie($arrCookie) {
		if (!is_array($arrCookie)) {
			$this->_setError('custom cookie is not array');
			return false;
		}
		$this->_arrCookie = $arrCookie;
		return true;
	}

	/**
	 * 通过header()函数输出cookie
	 * @param string $param
	 */
	public function headerCookie($cookie) {
		$cookie = trim($cookie);
		if (!$cookie) {
			return false;
		}

		$header = explode("\n", $cookie);
		foreach($header as $line) {
			header($line, false);
		}
		return true;
	}

	/**
	 * 从header可直接输出的set cookie字符串中匹配出SUE和SUP，并设置到$_COOKIE中
	 * @param string $cookie
	 */
	public function setCookieFromHeaderCookie($cookie) {
		$sue = $sup = array();
		preg_match('|SUE=(.*);|U', $cookie, $sue);
		preg_match('|SUP=(.*);|U', $cookie, $sup);

		if (!$sue || !$sup) {
			return false;
		}

		$_COOKIE[SSOCookie::$COOKIE_SUE] = rawurldecode($sue[1]);
		$_COOKIE[SSOCookie::$COOKIE_SUP] = rawurldecode($sup[1]);
		return true;
	}

	/**
	 * 验证app登录token
	 *
	 * @param array $str
	 * @param string $rsa_sign
	 * @return bool
	 */
	public function _validateAppV1($str, $rsa_sign) {

		if(empty($str) || empty($rsa_sign)){
			return false;
		}

		//	选择性验证，设置或传递使用RSA方式验证参数
		$crypted	= base64_decode($rsa_sign);
		$public_key	= $this->_getPublicKey();

		if (empty($crypted)) {
			$this->_setError($rskey . ' cookie not exist');
			return false;
		}
		if (empty($public_key)) {
			$this->_setError('public key not exist');
			return false;
		}

		// 检查rsa sign
		if (!$this->_validateRsaCookie($this->_signSUP($str), $crypted, $public_key)) {
			$this->_setError('rsa sign string error');
			return false;
		}

		return true;
	}

	/**
	 * 验证cookie
	 *
	 * @param array $arrUserInfo
	 * @param bool $use_rsa_sign
	 * @return bool
	 */
	private function _validateCookieV1(&$arrUserInfo, $use_rsa_sign=false) {
		// 不存在密文cookie或明文cookie视为无效
		if( !isset($this->_arrCookie[self::$COOKIE_SUE]) || !isset($this->_arrCookie[self::$COOKIE_SUP])) {
			$this->_setError('not all cookie are exists ');
			return false;
		}
		parse_str($this->_arrCookie[self::$COOKIE_SUE],$arrSUE);
		parse_str($this->_arrCookie[self::$COOKIE_SUP],$arrSUP);

		foreach( $arrSUP as $key=>$val) {
			if (isset($this->_arrKeyMap[$key])) $key = $this->_arrKeyMap[$key];
			$arrUserInfo[$key] = iconv('UTF-8','GBk',$val);
		}

		// 判断是否超时
		if($arrUserInfo['expiredtime'] < time()) {
			$this->_setError('cookie is timeout {cookie_expire:'.$arrUserInfo['expiredtime'].';now:'.time().'}');
			return false;
		}

		//	set rsa version
		$this->_setRsaVersion($arrSUE);

		// get rsa cookie name
		$rskey	= $this->_getRsaCookieName();

		//	解决php5.3版本中rawurlencode不转义~问题
		$rawsup = urldecode($_COOKIE[self::$COOKIE_SUP]);

		// 解决手机app浏览器丢失加号问题
		if(strpos($arrSUE[$rskey], "+") === false){
			preg_match_all ( '/'.$rskey.'=(.*)/', $this->_arrCookie[self::$COOKIE_SUE], $match );
			$arrSUE[$rskey] = $match[1][0];
		}

		//	选择性验证，设置或传递使用RSA方式验证参数
		if ($use_rsa_sign) {
			$crypted	= base64_decode($this->base_decode($arrSUE[$rskey]));
			$public_key	= $this->_getPublicKey();

			if (empty($crypted)) {
				$this->_setError($rskey . ' cookie not exist');
				return false;
			}
			if (empty($public_key)) {
				$this->_setError('public key not exist');
				return false;
			}

			// 检查rsa sign
			if (!$this->_validateRsaCookie($this->_signSUP($rawsup), $crypted, $public_key)) {
				$this->_setError('rsa sign string error');
				return false;
			}
		} else {
			// 检查加密cookie
			if ($arrSUE['ges'] != md5($rawsup . $this->_arrConf[$arrSUE['gev']])) {
				$this->_setError('encrypt string error');
				return false;
			}
		}

		return true;
	}
	/**
	 * 对rsa加密串进行替换
	 *
	 * @return string
	 */
	private function base_decode($str) {
        $src = array("_a_","_b_","_c_");
        $dist  = array("/","+","=");
        $new  = str_replace($src,$dist,$str);
        return $new;
	}

	private function _checkDomain($cookieInfo) {
		if (!$cookieInfo["d"]) return true;
		$digest = md5(self::$COOKIE_DOMAIN);
		if (strpos($digest, $cookieInfo["d"]) === 0) return true;
		return false;
	}

	private function _checkIp($cookieInfo) {
		if (!$cookieInfo["i"]) return true;
		$ip = $this->_cookieIp?$this->_cookieIp:self::getIp();
		$digest = md5($ip);
		if (strpos($digest, $cookieInfo["i"]) === 0) return true;
		return false;
	}

	/**
	 * parse cookie config file.
	 * @param $config: cookie config file
	 */
	private function _parseConfigFile($config) {
		//首先试图中apc缓存中得到配置
		$apc_key = self::APC_CACHE_PREFIX . $config;
		$arrConf = SSOApc::getInstance()->get($apc_key);

		if ($arrConf === false) {
			$arrConf = @file_get_contents($config);
		}

		if(!$arrConf) {
			$this->_setError('parse file '.$config . ' error');
			return false;
		}
		$this->_publicKey = $arrConf;
		SSOApc::getInstance()->set($apc_key, $arrConf, $this->apc_expiration_time);
		return true;
	}

	/**
	 * 获取当前rsa算法在cookie中的名字rs[n]（内容为密钥生成的签名）
	 *
	 * @return string
	 */
	private function _getRsaCookieName() {
		return self::COOKIE_SIGN_VALUE_NAME . $this->_getRsaVersion();
	}

	/**
	 * 获取当前rsa算法所使用的公钥的名字rv[n]
	 * 其内容为base64_encode(公钥)，在配置文件中
	 *
	 * @return string
	 */
	private function _getPublicKeyName() {
		return self::COOKIE_SIGN_VERSION_NAME . $this->_getRsaVersion();
	}

	/**
	 * 设置当前所使用的RSA版本
	 *
	 * @return int
	 */
	private function _setRsaVersion($sue) {
		return $this->_rsa_version = 0;
	}

	/**
	 * 当前所使用的RSA版本
	 *
	 * @return int
	 */
	private function _getRsaVersion() {
		return $this->_rsa_version;
	}

	/**
	 * 对sup做签名
	 *
	 * @param string $sup
	 * @return string
	 */
	private function _signSUP($sup) {
		return md5($sup);
	}

	/**
	 * 从conf中获取密钥，base64解密
	 * conf中COOKIE_SIGN_VERSION_NAME值为当前正在使用的版本
	 *
	 * @return string
	 */
	private function _getPublicKey() {
		$public_key = $this->_publicKey;
		return $public_key;
	}


	/**
	 * 验证
	 *
	 * @param string $sign				正确的签名数据
	 * @param string $crypted			签名数据
	 * @param string $public_key		公钥
	 * @return bool
	 */
	private function _validateRsaCookie($sign, $crypted, $public_key) {
		if (!openssl_public_decrypt($crypted, $decrypted, openssl_pkey_get_public($public_key))) {
			$this->_setError('decrypt error : ' . openssl_error_string());
			return false;
		}

		return $sign === $decrypted;
	}

	private function _delete_cookie($name,$path="",$domain="") {
		setcookie($name,"deleted",1,$path,$domain);
		return true;
	}

	/**
	 * 获得客户端的ip
	 */
	public static function getIp() {
		$remoteAddr = getenv("REMOTE_ADDR");
		$xForward = getenv("HTTP_X_FORWARDED_FOR");
		if ($xForward) {
			$arr = explode(",",$xForward);
			$cnt = count($arr);
			$xForward = $cnt==0?"":trim($arr[$cnt-1]);
		}
		if (self::isPrivateIp($remoteAddr) && $xForward) {
			return $xForward;
		}
		return $remoteAddr;
	}

	public static function isPrivateIp($ip) {
		$i = explode('.', $ip);
		if ($i[0] == 10) return true;
		if ($i[0] == 172 && $i[1] > 15 && $i[1] < 32) return true;
		if ($i[0] == 192 && $i[1] == 168) return true;
		return false;
	}

	/**
	 * 设置apc缓存中保存配置信息的过期时间
	 * @param	int	$time
	 */
	public function set_apc_expiration_time($time) {
		$this->apc_expiration_time = $time;
	}

	/**
	 * 取得apc缓存中保存配置信息的过期时间
	 */
	public function get_apc_expiration_time() {
		return $this->apc_expiration_time;
	}
}


?>
