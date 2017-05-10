<?PHP
/**
 * glapp sso server
 *
 * @package SSOServer
 * @author haibo8 <haibo8@staff.sina.com.cn>
 * @copyright Copyright (c) 2015 SINA Game Centre
 * @version $Id: $
 */


!defined('SER_SERVER_ROOT') && define('SER_SERVER_ROOT', dirname(__FILE__));
include_once (SYSDIR."/libraries/SSOServer/SERBase.php");
include_once (SYSDIR."/libraries/SSOServer/SERConfig.php");

@header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');

if (!class_exists('SSOCookie', false)) {
	include_once (SYSDIR."/libraries/SSOServer/SERCookie.php");
}

class SSOServer extends SERBase {
	const E_SYSTEM			= 9999;					// 系统错误，用户不需要知道错误的原因

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

	//private $_serviceId		= SSOConfig::SERVICE;	//应用产品标识, 一般和下面entry相同
	//private $_entry			= SSOConfig::ENTRY;		//应用产品标识, 获取用户详细信息使用，由统一注册颁发的
	//private $_pin			= SSOConfig::PIN; 		//应用产品pin, 获取用户详细信息使用，由统一注册颁发的

	public function __construct() {
		if (!class_exists('SERCookie', false)) {
			include_once SER_SERVER_ROOT . '/SERCookie.php';
		}

		$this->_arrCookie	= $_COOKIE;
		//$this->_cookie		= new SSOCookie();
		$sso_config = new SERConfig();
		$this->_serviceId = $sso_config->getService();
		$this->_entry = $sso_config->getEntry();
		$this->_pin = $sso_config->getPin();
		$this->_domain = $sso_config->getDomain();
		$this->_ticket = $sso_config->getTicket();
		$this->_rsa_sign = $sso_config->getRsaSign();
		$this->_cookie_key_file = $sso_config->getCookieKeyFile();

		$this->_cookie		= new SERCookie($this->_cookie_key_file);
		$this->_cookie->set_apc_expiration_time($this->_arrConfig['cookie_config_apc_expiration_time']);


	}

	/**
	 * 生成APP登录加密token
	 * @return string
	 */
	public function rsaApp($userinfo) {
		// 拼接加密串
		$endtime = time() + 60 * 60 * 24 * 365;
		//$str = $userinfo['uid'].$userinfo['nickname'].$userinfo['create_time'].$endtime;
		$str = $userinfo['uid'].$userinfo['create_time'].$endtime;

		// md5签名
		$md5_sign = $this->_cookie->signMd5($str);

		// rsa加密
		$rsa_sign = $this->_cookie->signRsa($md5_sign);

		$arr['deadline'] = $endtime;
		$arr['gtoken']	= $rsa_sign;
		$arr['guid']	= $userinfo['uid'];

		return $arr;
	}

	/**
	 * 生成web登录加密cookie串
	 * @return string
	 */
	public function rsaWeb($userinfo) {
		// 拼接加密串
		$s_time = time();
		$e_time = time() + 60 * 60 * 24 * 365;
		// 去除头像后随即参数
		$img = explode("?", $userinfo['avatar']);
		$str = "cv=1.0&bt=".$s_time."&et=".$e_time."&uid=".$userinfo['uid']."&nickname=".$userinfo['nickname']."&avatar=".$img[0]."&sex=".$userinfo['gender'] ;

		// md5签名
		$md5_sign = $this->_cookie->signMd5($str);

		// rsa加密
		$rsa_sign = $this->_cookie->signRsa($md5_sign);
		$rsa_sign = $this->base_encode($rsa_sign);

		$cookie_arr['GSUP'] = "cv=1.0&bt=".$s_time."&et=".$e_time."&uid=".$userinfo['uid']."&nickname=".urlencode($userinfo['nickname'])."&avatar=".$img[0]."&sex=".$userinfo['gender'];
		$cookie_arr['GSUE'] = "ges=".$this->_cookie->signMd5($str."v1.0")."&gev=v1.0&grs0=".urlencode($rsa_sign);

		return $cookie_arr;
	}

	/**
	 * 种cookie（header输出cookie）
	 */
	public function setCustomerCookies($str_cookie) {
		if (!$this->_cookie->headerCookie($str_cookie)) {
			$this->_setError('set cookie error', '1001');
			return false;
		}
		return true;
	}

	/**
	 * 对rsa加密串进行替换
	 */
	private function base_encode($str) {
        $src  = array("/","+","=");
        $dist = array("_a_","_b_","_c_");
        $new  = str_replace($src,$dist,$str);
        return $new;
	}

}
