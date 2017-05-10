<?php
/**
 * @file HttpRequest.php
 * @author zhenyu5@staff
 * @description
 *    HttpRequest 是一个模拟HTTP请求的助手类
 *    可模拟浏览器连贯的访问网页：自动种cookie、自动加引用页
 */
class HttpRequest{

	//访问的$url的地址
	private $url ;

	//post 的数据 array 数据不用urlencode
	private $postData ;

	//请求的方法 GET | POST
	private $method ;

	//超时时间
	private $timeout;

	 //请求头 array
	private $header ;

	 //cookie array
	private $cookies ;

	//结果(上次send的结果)
	private $result ;

	/**
	 * 构造函数，必须先传入一个地址及方法
	 */
	public function __construct($url='',$method='POST'){
		$this->postData = '';
		$this->timeout = 10 ;
		$this->headers = array();
		$this->cookies = array();
		if(!empty($url)){
			$this->setRequest($url,$method);
		}
		$head = array();
		$head['User-Agent'] = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3 (.NET CLR 3.5.30729)';
		$this->setHeaders($head);
	}

	/**
	 * 设置请求
	 * @param $url URL地址
	 * @param $method 'POST' | 'GET'
	 */
	public function setRequest($url,$method=null){
		$this->setUrl($url);
		if(!empty($method))
			$this->setMethod($method) ;
	}

	/**
	 * 设置 URL 地址
	 * @param $url URL地址
	 */
	public function setUrl($url){
		if(!empty($this->url)){
			$this->addHeaders(array('Referer'=>$this->url));
		}
		$this->url = $url ;
	}

	/**
	 * 设置方法
	 * @param $method 'POST' | 'GET'
	 */
	public function setMethod($method){
		$method = strtoupper($method) ;
		if(!in_array($method,array('POST','GET'))) return false;
		$this->method = $method ;
		if($method == 'GET'){
			if(isset($this->headers['Content-Length'])) unset($this->headers['Content-Length']);
			if(isset($this->headers['Content-Type'])) unset($this->headers['Content-Type']);
		}else{
			$this->addHeaders(array('Content-Type'=>'application/x-www-form-urlencoded'));
		}
	}

	/**
	 * 设置POST数据
	 * @param $postData array
	 *    不用urlencode
	 */
	public function setPostData($postData){
		if($this->method == 'POST'){
			if(is_array($postData))
				$this->postData = http_build_query($postData) ;
			else
				$this->postData = $postData ;

			$this->addHeaders(array('Content-Type'=>'application/x-www-form-urlencoded'));
			$this->addHeaders(array('Content-Length'=>strlen($this->postData)));
		}else{
			unset($this->headers['Content-Length']);
		}
	}

	/**
	 * 设置请求超时
	 * @param $postData int
	 */
	public function setTimeout($timeout){
		$this->timeout = $timeout ;
	}

	/**
	 * 设置请求头
	 * @param $headers array
	 *    请求头的集合
	 */
	public function setHeaders($headers){
		$this->headers = $headers ;
	}

	/**
	 * 增加(设置)请求头
	 * @param $headers array
	 *    请求头的集合
	 */
	public function addHeaders($headers){
		if(!is_array($headers)) return false;
		foreach($headers as $header_name=>$header_value){
			$this->headers[$header_name] = $header_value ;
		}
	}

	/**
	 * 增加(设置)cookie
	 * @param $cookies array
	 *    要增加的COOKIE
	 */
	public function addCookies($cookies){
		if(!is_array($cookies)) return false;
		foreach($cookies as $cookie_name=>$cookie_value){
			$this->cookies[$cookie_name] = $cookie_value ;
		}
	}

	/**
	 * 获得之前访问所有地址的种下的所有cookie
	 * 获得之前访问所有地址的种下的某个cookie
	 * @param $name
	 *    要取得的单个COOKIE值
	 * @return array | string
	 *    当不传入参数时候返回数组
	 */
	public function getCookies($name=0){
		if($name){
			if(isset($this->cookies[$name])) return $this->cookies[$name];
			return false ;
		}
		return $this->cookies ;
	}

	/**
	 * 种上次访问之后的cookie
	 */
	public function setResponseCookie(){
		preg_match_all('/Set-Cookie:([^=]*)=([^;]*);/i',$this->result['head'],$mat);
		$cookie = array();
		for($i=0;$i<count($mat[1]);$i++){
			$cookie_name = $mat[1][$i] ;
			$cookie_value = $mat[2][$i] ;
			$cookie[trim($cookie_name)] = trim($cookie_value);
		}
		$this->addCookies($cookie);
	}

	/**
	 * 发送请求
	 * 当一切设置好(cookie , post data , url) 之后,使用send方法发送请求
	 */
	public function send($hostname=""){
		$errno = 0 ;
		$errstr = '';
		$url_info = parse_url($this->url);
		if(empty($url_info['scheme'])) return false ;

		$host = $url_info['host'] ;
		$query = isset($url_info['path']) ? $url_info['path'] : '/' ;

		$port = 80 ;
		$fs_url = 'tcp://'.$host ;
		if($url_info['scheme'] == 'https'){
			$port = 443 ;
			$fs_url = 'ssl://'.$host ;
			if($this->method == 'GET'){
				$this->method  = 'POST' ;
				$this->setPostData('') ;
			}
		}
		if(isset($url_info['query'])) $query .= '?'. $url_info['query'] ;
		$fp = fsockopen($fs_url, intval($port), $errno, $errstr, $this->timeout);

		if(!$fp){
			$this->result = array(
				'status' => $errno,
				'message' => $errstr,
			 );
			return $this->result ;
		}
		$head = array();
		$head[] = "$this->method $query HTTP/1.1";
		//加入了$hostname参数
		if($hostname) $head[] = "Host: $hostname";
		else $head[] = "Host: $host";
		//---------------
		foreach($this->headers as $head_name=>$head_value){
			$head[] = "$head_name: $head_value";
		}
		if(count($this->cookies) > 0){
			$cookie = array();
			foreach($this->cookies as $cookie_name=>$cookie_value){
				$cookie[] = "$cookie_name=$cookie_value";
			}
			$cookie = join('; ',$cookie);
			$head[] = "Cookie: $cookie";
		}
		$head = join("\r\n",$head) ;
		$head .= "\r\nConnection: Close\r\n\r\n";
		if($this->method == 'POST'){
			$head .= $this->postData . "\r\n\r\n";
		}
		fwrite($fp, $head);
		$body = '';
		$buff = null;
		$bufsize = 4096 ;
		while($buff = fread($fp, $bufsize)){
			$body .= $buff;
		}
		preg_match("/^(.*?)\r\n\r\n/s", $body, $matches);
		$head = $matches[1];
		$body = substr($body, strlen($matches[0]));
		$this->result = array(
			'status' => 1,
			'message' => 'succeed',
			'head' => $head,
			'body' => $body,
		);
		$this->setResponseCookie();
		return $this->result ;
	}
}