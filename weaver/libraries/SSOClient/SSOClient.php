<?PHP
/**
 * 973app sso client
 *
 * @package SSOClient
 * @author haibo8 <haibo8@staff.sina.com.cn>
 * @copyright Copyright (c) 2015 SINA Game Centre
 * @version $Id: $
 */


!defined('SSO_CLIENT_ROOT') && define('SSO_CLIENT_ROOT', dirname(__FILE__));
include_once (SYSDIR."/libraries/SSOClient/SSOConfig.php");

@header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');

if (!class_exists('SSOCookie', false)) {
	include_once (SYSDIR."/libraries/SSOClient/SSOCookie.php");
}

class SSOClient extends SSOBase {
	const E_SYSTEM			= 9999;					// 系统错误，用户不需要知道错误的原因
	
	const GETSSO_URL		= 'http://gl.97973/api/getsso.php';	//获取用户详细信息接口

	//配置信息
	private $_arrConfig = array(
								//如果session距离过期时间小于这个阈值，那么就发送cookie给session server，尝试续时
								'cookie_renew_threshold' => 3600, 
								//如果连续这个次数以上发生因session server故障而验证失败，那么在一定时间以内总返回验证成功
								'session_server_fail_limit' => 10, 
								//apc中存储连续因session server或网络原因发生验证失败的次数的变量名称
								'session_server_fail_limit_name' => 'sso_ss_ssfln',
								//apc中存储连续因session server或网络原因发生验证失败的时间窗的变量名称
								'session_server_fail_time_name' => 'sso_ss_ssftn',
								//向session server发送请求的超时时间
								'session_server_time_out' => 300,
								//是否验证session
								'use_session' => false,
								'userinfo_cache_expire' => 5,
								//cookie的配置信息在apc缓存中的过期时间
								'cookie_config_apc_expiration_time' => 300,
	);

	private $_cookie		= null;
	private $_loginType		= '';
	private $_returnType	= 'META';
	private $_uid			= '';
	private $_userInfo		= array();
	private $_arrCookie;
	private $_arrLoginQuery = array();
	
	private $_serviceId	= '';		// 应用产品ID
	private $_entry	= '';	// 应用产品entry 和 pin , 获取用户详细信息使用，由统一注册颁发的
	private $_pin	= '';
	private $_domain = '';
	private $_ticket = false;
	private $_rsa_sign = true;
	private $_cookieCheckLevel = 0; // cookie 验证级别

	private $_arrUserInfoCache = array();

	private static $_allowReEntrantIsLogined = false; // 默认判断登录状态的函数是不能重入的，第二次调用时直接返回第一次调用的结果
	private static $_arrStatic = array(
		'checkResult'=>array(
			'checked'=>false,
			'result'=>false
	),
		'instance'=>array(
			'_cookie'=>'',
			'_loginType'=>'',
			'_uid'=>'',
			'_userInfo'=>array(),
			'_arrCookie'=>array(),
			'_arrUserInfoCache'=>array(),
			'error'=>'',
			'errno'=>0,
	)
	);

	//private $_serviceId		= SSOConfig::SERVICE;	//应用产品标识, 一般和下面entry相同
	//private $_entry			= SSOConfig::ENTRY;		//应用产品标识, 获取用户详细信息使用，由统一注册颁发的
	//private $_pin			= SSOConfig::PIN; 		//应用产品pin, 获取用户详细信息使用，由统一注册颁发的

	private $_need_validate_session	= false; 		//是否需要验证session

	public function __construct() {

		if (!class_exists('SSOCookie', false)) {
			include_once SSO_CLIENT_ROOT . '/SSOCookie.php';
		}

		$this->_arrCookie	= $_COOKIE;
		$sso_config = new SSOConfig();
		$this->_serviceId = $sso_config->getService();
		$this->_entry = $sso_config->getEntry();
		$this->_pin = $sso_config->getPin();
		$this->_domain = $sso_config->getDomain();
		$this->_ticket = $sso_config->getTicket();
		$this->_rsa_sign = $sso_config->getRsaSign();
		$this->_cookie_key_file = $sso_config->getCookieKeyFile();
		
		$this->_cookie		= new SSOCookie($this->_cookie_key_file);
		$this->_cookie->set_apc_expiration_time($this->_arrConfig['cookie_config_apc_expiration_time']);
		//$this->_cookie->setCookieDomain($this->_domain);
		//$this->_cookie->setCookieCheckLevel($this->_cookieCheckLevel);
		
		
	}

	/**
	 * 退出，清除cookie
	 * @return void
	 */
	public function logout() {
		$this->_cookie->delCookie();	//实际上暂时不会做任何操作
		// 下面这两个删除cookie主要是针对非sina.com.cn域写的，对sina.com.cn域没有影响
		setcookie('GSSOLoginState', 'deleted', 1, '/', SSOCookie::$COOKIE_DOMAIN);
		setcookie('ALFG', 'deleted', 1, '/', SSOCookie::$COOKIE_DOMAIN);
		unset($_COOKIE['SSOLoginState']);
		unset($_COOKIE['ALF']);
	}

	/**
	 * 设置cookie，用于无法在header中取到cookie的情况
	 *
	 * @param array $arrCookie
	 * @return bool
	 */
	public function setCustomCookie($arrCookie) {
		if (!$this->_cookie->setCustomCookie($arrCookie)) {
			$this->_setError($this->_cookie->getError(), $this->_cookie->getErrno());
			return false;
		}
		$this->_arrCookie = array_merge($this->_arrCookie, $arrCookie);
		return true;
	}

	/**
	 * 检查用户是否登录
	 *
	 * @param bool $noRedirect		是否允许在需要的时候访问sso，js判断用户登录状态时，
	 *								该参数可以设置为true，然后js自己去访问sso，避免使用iframe时浏览器兼容问题
	 * @return bool
	 */
	public function isLogined($noRedirect = false) {
		// 防止方法重入, 如果已经验证过了，就直接返回结果
		if (self::$_arrStatic['checkResult']['checked']) {
			$this->_restoreInstance();
			return self::$_arrStatic['checkResult']['result'];
		}

		$arrQuery = $this->_getQueryArray();

		if( isset($this->_arrCookie[SSOCookie::$COOKIE_SUE])) {
			//	使用cookie登录
			$use_rsa_sign = $this->_rsa_sign;
			if ($this->_cookie->getCookie($userinfo, $use_rsa_sign)) {
				$this->_userInfo	= $userinfo;
				$this->_uid			= $this->_userInfo['uniqueid'];
				$this->_loginType	= 'cookie';
				
				return $this->_checkResult(true);
			}

			$this->_setError($this->_cookie->getError(), $this->_cookie->getErrno());
			// 无效的cookie试图删除
			$this->_cookie->delCookie();
		}

		if (@$arrQuery["retcode"] != 0) { // 这个必须写在检查SSOLoginState、ALF之前，否则就死循环了
			$this->_setError(@$arrQuery['reason'], $arrQuery['retcode']);
			$this->logout();
			return $this->_checkResult(false);
		}
		

		// 对于外域才参考retcode
		$this->_setError(@$arrQuery['reason'], @$arrQuery['retcode']);
		$this->logout();  // 对于外域也一定要logout
		return $this->_checkResult(false);
	}
	
	/**
	 * 检查app用户是否登录
	 *
	 * @return bool
	 */
	public function isAppLogin($str, $token){
		return $this->_cookie->_validateAppV1($str, $token);
	}

	/**
	 * 获取用户详细信息,必须保证用户已登录或指定$uid 参数
	 */
	public function getUserInfoByUniqueid($uid) {
		$query = array(
			'user'	=> $uid,
			'ag'	=> 0,
			'entry'	=> $this->_entry,
			'm'		=> md5($uid . 0 .$this->_pin)
		);
		$url = self::GETSSO_URL;
		$ret = $this->_query($url, $query);

		if($ret === false){
			$this->_setError('call '.$url.' error', self::E_SYSTEM);
			return false;
		}
		parse_str($ret,$arr);
		if ($arr['result'] != 'succ') {
			$this->_setError('call ' .$url ." error \n".$ret."\n".$arr['reason'], self::E_SYSTEM);
			return false;
		}
		return $arr;
	}
	/**
	 * 获取用户信息
	 */
	public function getUserInfo() {
		return $this->_userInfo;
	}
	/**
	 * 获取登录方式
	 */
	public function getLoginType() {
		return $this->_loginType;
	}
	/**
	 * 获取用户唯一ID
	 */
	public function getUniqueid() {
		return $this->_uid;
	}
	

	private function _parseCookieStr($str_cookie, &$arr_cookie) {
		$sue = $sup = array();
		preg_match('|SUE=(.*);|U', $str_cookie, $sue);
		preg_match('|SUP=(.*);|U', $str_cookie, $sup);

		if (!$sue || !$sup) {
			return false;
		}

		$arr_cookie[SSOCookie::$COOKIE_SUE] = rawurldecode($sue[1]);
		$arr_cookie[SSOCookie::$COOKIE_SUP] = rawurldecode($sup[1]);
		return true;
	}
	/**
	 * 种cookie（header输出cookie）
	 */
	private function _setCookie($str_cookie) {
		if (!$this->_cookie->headerCookie($str_cookie)) {
			$this->_setError($this->_cookie->getError(), $this->_cookie->getErrno());
			return false;
		}
		return true;
	}
	

	/**
	 * 获取登录后的返回信息
	 *
	 * @return array
	 */
	private function _getQueryArray() {
		$arrQuery = array();
		//为了避免rewrite丢掉url的参数，这里从$_SERVER['REQUEST_URI'] 中分析参数
		if (preg_match('/\?(.*)$/', $_SERVER['REQUEST_URI'], $matches)) {
			parse_str($matches[1], $arrQuery);
		}
		return array_merge((array)$arrQuery, $_POST);
	}

	/**
	 * 获取跳转地址
	 *
	 * @return string
	 */
	private function _getReturnUrl() {
		//redirect to sso server ,then user will send a new request with ST
		$scheme	= isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
		$host	= $_SERVER['HTTP_HOST'];

		//由于7层做了内容转发，导致此处取到的HTTP_HOST可能与用户访问的地址不同，所以设置了一个修正机制
		if (property_exists('SSOConfig', 'HOST_MAPPING') && !empty(SSOConfig::$HOST_MAPPING) && isset(SSOConfig::$HOST_MAPPING[$host])) {
			$host = SSOConfig::$HOST_MAPPING[$host];
		}

		return $scheme . '://' . $host . $_SERVER['REQUEST_URI'];
	}


	/**
	 * 带版本号的发送请求
	 *
	 * @param string $url host
	 * @param array $param		请求参数
	 * @return mixed
	 */
	private function _query($url, $param) {
		$param['_version_'] = self::VERSION;
		$query = http_build_query($param);
		return @file_get_contents($url.'?'.$query);
	}


	/**
	 * 设置是否需要验证session。若session需要续时，则不受此限制。
	 * @param	bool	$need 若为true，则需要验证
	 *                        若为false， 则不需要验证
	 */
	public function need_validate_session($need) {
		$this->_need_validate_session = $need ? true : false;
	}

	/**
	 * 该函数为了避免isLogined函数重入
	 * @param type $bool
	 * @return type
	 */
	private function _checkResult($bool) {
		if (self::$_allowReEntrantIsLogined) return $bool;
		self::$_arrStatic['checkResult'] = array(
				'checked' =>  true,
				'result' =>  $bool,
		);
		$arr = &self::$_arrStatic['instance'];
		foreach($arr as $key=>$val) {
			if ($key == "error" || $key == "errno") continue;
			$arr[$key] = $this->$key;
		}
		$arr["error"] = $this->getError();
		$arr["errno"] = $this->getErrno();
		return $bool;
	}
	private function _restoreInstance() {
		$arr = self::$_arrStatic['instance'];
		foreach($arr as $key=>$val) {
			if ($key == "error" || $key == "errno") continue;
			$this->$key = $val;
		}
		if ($arr["error"]) {
			$this->_setError($arr["error"], $arr["errno"]);
		}
	}

	/**
	 * 设置配置信息
	 */
	public function setConfig($name, $value) {
		if(!array_key_exists($name, $this->_arrConfig))
		return false;
		$this->_arrConfig[$name] = $value;
	}
}
