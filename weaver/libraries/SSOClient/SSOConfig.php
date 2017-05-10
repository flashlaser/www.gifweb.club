<?PHP
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Sina sso client config file
 * @package  SSOClient
 * @filename SSOConfig.php
 * @author   lijunjie <junjie2@staff.sina.com.cn>
 * @date 	 2009-11-26
 * @version  1.2
 */

//require_once(SYSDIR."/libraries/sso/SSOCookie.php");

class SSOConfig {
//	const SERVICE 	= "bbsgames"; 	//服务名称，产品名称，应该和entry保持一致
//	const ENTRY 	= "bbsgames";	//应用产品entry 和 pin , 获取用户详细信息使用，由统一注册颁发的
//	const PIN 		= "fcc34cfa0b6bfca74d775c13524267b8";
//	const COOKIE_DOMAIN = ".sina.com.cn";  //domain of cookie, 您域名所在的根域，如“.sina.com.cn”，“.51uc.com”
//	const USE_SERVICE_TICKET = false; // 如果只需要根据sina.com.cn域的cookie就可以信任用户身份的话，可以设置为false，这样不需要验证service ticket，省一次http的调用
	
	private $service = '';
	private $entry = '';
	private $pin = '';
	private $domain = '.sina.com.cn';
	private $use_service_ticket = false;
	private $use_rsa_sign = true;
	private $cookie_key_file = '/usr/local/sinasrv2/lib/php/cookie.conf';
	
	public function __construct()
	{
		$CI = &get_instance();
		$service = $CI->config->item('service');
		if (!isset($service) || empty($service))
		{
			log_message('error', "Unable to find the veriable service value.");
			show_error("Unable to find the veriable service value");
		}
		else 
		{
			$this->service = $service;
		}
		$entry = $CI->config->item('entry');
		if (!isset($entry) || empty($entry))
		{
			log_message('error', "Unable to find the veriable entry value.");
			show_error("Unable to find the veriable entry value");
		}
		else 
		{
			$this->entry = $entry;
		}		
		$pin = $CI->config->item('pin');
		if (!isset($pin) || empty($pin))
		{
			log_message('error', "Unable to find the veriable pin value.");
			show_error("Unable to find the veriable pin value");
		}
		else 
		{
			$this->pin = $pin;
		}	
		$domain = $CI->config->item('domain');
		if (isset($domain) && !empty($domain))
		{
			$this->domain = $domain;
		}		
		$use_service_ticket = $CI->config->item('ticket');
		if (isset($use_service_ticket) && !empty($use_service_ticket))
		{
			$this->use_service_ticket = $use_service_ticket;
		}
		$use_rsa_sign = $CI->config->item('rsa_sign');
		if (isset($use_rsa_sign) && !empty($use_rsa_sign))
		{
			$this->use_rsa_sign = $use_rsa_sign;
		}
		$cookie_key_file = $CI->config->item('public_key_file');
		if (isset($cookie_key_file) && !empty($cookie_key_file))
		{
			$this->cookie_key_file = $cookie_key_file;
		}
		
	}
	
	public function getService()
	{
		return $this->service;
	}
	
	public function getEntry()
	{
		return $this->entry;
	}
	
	public function getPin()
	{
		return $this->pin;
	}
	
	public function getDomain()
	{
		return $this->domain;
	}
	
	public function getTicket()
	{
		return $this->use_service_ticket;
	}
	
	public function getRsaSign()
	{
		return $this->use_rsa_sign;
	}
	
	public function getCookieKeyFile()
	{
		return $this->cookie_key_file;
	}
}
?>
