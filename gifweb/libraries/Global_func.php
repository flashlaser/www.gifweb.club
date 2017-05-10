<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Global_func{

	public function __construct()
	{
		$this->_CI  = & get_instance();
	}

	//下面是一些防注入函数
	/*
	函数名称：inject_check()
	函数作用：检测提交的值是不是含有SQL注射的字符，防止注射，保护服务器安全
	参　　数：$sql_str: 提交的变量
	返 回 值：返回检测结果，ture or false
	*/
	function inject_check($sql_str) {
		return preg_match('/select|insert|update|delete|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile/', $sql_str); // 进行过滤
	}
	
	/**
	 * 过滤除字母数字和下划线以外的字符
	 * @param string $str
	 * @return mixed
	 */
	function alnum_(&$str) {
		$str = preg_replace('/[^a-zA-Z0-9_]/','',$str);
		return true;
	}

	function is_email($email){
		return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-]+(\.\w+)+$/",$email);
	}

	/*
	函数名称：verify_id()
	函数作用：校验提交的ID类值是否合法
	参　　数：$id: 提交的ID值
	返 回 值：返回处理后的ID
	*/
	function verify_id($id=null)
	{
		if (!$id) { exit('没有提交参数！'); } // 是否为空判断
		elseif ($this->inject_check($id)) { exit('提交的参数非法！'); } // 注射判断
		elseif (!is_numeric($id)) { exit('提交的参数非法！'); } // 数字判断
		$id = intval($id); // 整型化
		return $id;
	}

	/*
	函数名称：str_check()
	函数作用：对提交的字符串进行过滤
	参　　数：$var: 要处理的字符串
	返 回 值：返回过滤后的字符串
	*/
	function str_check( $str ) {
		if (!get_magic_quotes_gpc()) { // 判断magic_quotes_gpc是否打开
		$str = addslashes($str); // 进行过滤
		}
		$str = str_replace("_", "\_", $str); // 把 '_'过滤掉
		$str = str_replace("%", "\%", $str); // 把 '%'过滤掉

		return $str;
	}

	//是否UTF8编码
	function isUTF8($src){
		if(is_array($src)){
			while(list(, $value) = each($src)){
				if(!$this->isUTF8($value))
					return false;
			}
			return true;
		}else{
			return ($src === mb_convert_encoding(mb_convert_encoding($src, "UTF-32", "UTF-8"), "UTF-8", "UTF-32"));
		}
	}

	//转换编码(适用键名数组)
	function GBKtoUTF8($src, $encodeKey = false){
		if(is_array($src)){
			$keys = array_keys($src);
			for($i = 0; $i < count($keys); $i ++){
				if($encodeKey)
					$newkey = $this->GBKtoUTF8($keys[$i], $encodeKey);
				else
					$newkey = $keys[$i];
				$src[$newkey] = $this->GBKtoUTF8($src[$keys[$i]], $encodeKey);
				if($newkey != $keys[$i])
					unset($src[$keys[$i]]);
			}
		}else
			$src = iconv('GBK', 'UTF-8', $src);
		return $src;
	}

	//转换编码(适用键名数组)
	function UTF8toGBK($src, $encodeKey = false){
		if(is_array($src)){
			$keys = array_keys($src);
			for($i = 0; $i < count($keys); $i ++){
				if($encodeKey)
					$newkey = $this->UTF8toGBK($keys[$i], $encodeKey);
				else
					$newkey = $keys[$i];
				$src[$newkey] = $this->UTF8toGBK($src[$keys[$i]], $encodeKey);
				if($newkey != $keys[$i])
					unset($src[$keys[$i]]);
			}
		}else
			$src = iconv('UTF-8', 'GBK', $src);
		return $src;
	}
	
	/**
	 * 整理序列化的字符串，解决编码长度不正确时，无法反序列化的情况
	 * @param string $serial_str
	 * @return true
	 */
	function mb_unserialize(&$serial_str) {
		$serial_str= preg_replace( '!s:(\d+):"(.*?)";!se', '"s:".strlen("$2").":\"$2\";"', $serial_str );
		$serial_str = unserialize($serial_str);
		return true;
	}

	//得到系统毫秒
	function getmicrotime(){
		list($usec, $sec) = explode(" ",microtime());
		return ((float)$usec + (float)$sec);
	}

	//计算时间跨度
	function formattime($sec){
		$d = $h = $m = $s = 0;
		if($sec>=24*3600){
			$d = floor($sec / (24*3600));
			$sec = $sec % (24*3600);
		}
		if($sec){
			if($sec>=3600){
				$h = floor($sec / 3600);
				$sec = $sec % 3600;
			}
			if($sec){
				$m = floor($sec / 60);
				$s = intval($sec - $m * 60);
			}
		}
		$s_time="";
		if($d) $s_time.=$d."天";
		if($h) $s_time.=$h."时";
		if($m) $s_time.=$m."分";
		if($s) $s_time.=$s."秒";
		return $s_time;
	}

	//输出JS代码
	function putScript($js){
		print "<script type=\"text/javascript\">$js</script>";
	}

	/*
		SOCKET发送HTTP服务GET|POST请求
		$host		主机IP或域名
		$port		端口号
		$method	请求方法 get|post
		$query		请求主机位置
		$timeout	超时(秒)
		$hostname	主机域名
		$postdata	仅POST需要 $arr = array($key=>$value)形式

		返回：array()
		$result['status'] 状态
		$result['message'] 描述
		$result['head']  返回头
		$result['body']  正文
	*/

	function fsopen($host, $port, $method, $query, $timeout = 10, $hostname = '', $postdata = '')
	{
		$result = null;
		$bufsize = 4096;
		$errno = 0;
		$errstr = "";

		$fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
		if ($fp){
			switch($method):
				case "get":
					$head  = '';
					$head .= "GET $query HTTP/1.0\r\n";
					$head .= "Host: $hostname\r\n";
					$head .= "\r\n";
					break;
				case "post":
					$url_data = http_build_query($postdata);
					$length = strlen($url_data);
					$head ="POST $query HTTP/1.1\r\n";
					$head.="Host: $hostname\r\n";
					$head.="User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30; InfoPath.1; .NET CLR 3.0.04506.648)\r\n";
					$head.="Content-Type: application/x-www-form-urlencoded\r\n";
					$head.="Content-Length: $length\r\n";
					$head.="Connection: Close\r\n\r\n";
					$head.=$url_data;
					break;
				default:
					$result = array(
						'status' => -1,
						'message' => 'method error',
					);
					return $result;
			endswitch;

			fwrite($fp, $head);
			if(!stream_set_timeout($fp, $timeout)){
			//设置超时失败
			$result = array(
				'status' => 0,
				'message' => 'set timeout failed'
			);

		}else{

			$body = '';
			$buff = null;
			while($buff = fread($fp, $bufsize))
			$body .= $buff;
			$info = stream_get_meta_data($fp);
			fclose($fp);

			if (!$info['eof'])
			{
			//读取超时 没有到末尾
				$result = array(
					'status' => 0,
					'message' => 'read timeout',
				);
			}
			else
			{
				//读取完成分离头信息和正文
				preg_match("/^(.*?)\r\n\r\n/s", $body, $matches);
				$head = $matches[1];
				$body = substr($body, strlen($matches[0]));//取得正文
				$result = array(
					'status' => 1,
					'message' => 'succeed',
					'head' => $head,
					'body' => $body,
				);
			}
		}
		}else{
			//连接失败或超时
			$result = array(
				'status' => $errno,
				'message' => $errstr,
			);
		}

		return $result;
	}

	//抓取url位置
	function fsoget($url, $timeout = 30){
		$server = parse_url($url);
		if(empty($server['host']))
			return null;
		$server['scheme'] = empty($server['scheme']) ? 'http' : $server['scheme'];
		$server['port'] = empty($server['port']) ? '80' : $server['port'];
		$server['path'] = empty($server['path']) ? '/' : $server['path'];
		$server['query'] = empty($server['query']) ? $server['path'] : $server['path'] . '?' . $server['query'];

		$fso = $this->fsopen($server['host'], $server['port'], 'get', $server['query'], $timeout, $server['host']);
		return $fso;
	}

	/*
	 * 字符截断
	*/
	function strcut($string, $length, $dot = '...', $charset = 'utf-8') {
		if(strlen($string) <= $length) {
			return $string;
		}
		$string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array('&', '"', '<', '>'), $string);
		$strcut = '';
		if(strtolower($charset) == 'utf-8') {
			$n = $tn = $noc = 0;
			while($n < strlen($string)) {

				$t = ord($string[$n]);
				if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
					$tn = 1; $n++; $noc++;
				} elseif(194 <= $t && $t <= 223) {
					$tn = 2; $n += 2; $noc += 2;
				} elseif(224 <= $t && $t < 239) {
					$tn = 3; $n += 3; $noc += 2;
				} elseif(240 <= $t && $t <= 247) {
					$tn = 4; $n += 4; $noc += 2;
				} elseif(248 <= $t && $t <= 251) {
					$tn = 5; $n += 5; $noc += 2;
				} elseif($t == 252 || $t == 253) {
					$tn = 6; $n += 6; $noc += 2;
				} else {
					$n++;
				}
				if($noc >= $length) {
					break;
				}
			}
			if($noc > $length) {
				$n -= $tn;
			}
			$strcut = substr($string, 0, $n);
		} else {
			for($i = 0; $i < $length; $i++) {
				$strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
			}
		}
		$strcut = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);
		return $strcut.$dot;
	}

	//得到访问者IP
	function get_remote_ip(){
		$ip=false;
		if(!empty($_SERVER["HTTP_CLIENT_IP"]))
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
			if ($ip){
				array_unshift($ips, $ip);
				$ip = FALSE;
			}
			for ($i = 0; $i < count($ips); $i++) {
				if (!preg_match ("/^(10|172\.16|192\.168)+.*$/i", $ips[$i])) {
					$ip = $ips[$i];
					break;
				}
			}
		}
		return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
	}

	//获取host
	function getHost($url,$ip)
	{
		$fp=fsockopen($ip,80,$errno,$errstr,5);
		$res='';
		if(!$fp){
			echo "$errstr ($errno)<br/>\n";
		}else{
			$out = "GET $url HTTP/1.1\r\n";
			$out.= "HOST: i.v1pai.jiaju.com\r\n";
			$out.= "Connection:Close\r\n\r\n";
			fwrite($fp, $out);
			while (!feof($fp)){
				$res .= fgets($fp,256);
			}
			fclose($fp);
		}
		$res = substr($res, strpos($res, "\r\n\r\n") + 4);
		return $res;
	}

	// 根据ip地址的获取所属地和通信商
	function get_location($ip){
		$r = array('addr' => '', 'comm' => '');
		$ip = trim($ip);
		$url = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php';
		if($ip && $this->is_ip($ip)){
			$url .= '?ip=' . $ip;
			$result = trim(file_get_contents($url));
			$result = iconv('gbk', 'utf-8', $result);

			if($result){
				$result = explode("\t", $result);
				if(count($result)){
					$r['comm'] = array_pop($result);
					$result = array_slice($result, 3 , 3);
					$r['addr'] = implode('-',$result);
				}
			}
		}
		return ($r['addr'] || $r['comm']) ? $r : false;
	}

	//设置多个cookie
	function setCookies($cookies, $expire = NULL, $path = NULL, $domain = NULL){
		foreach ($cookies as $key => $value)
			setcookie($key, $value, $expire, $path, $domain);
	}

	//替换文本里的{xxx}标签
	function replaceLable($text, $lables){
		preg_match_all("/{(.+?)}/", $text, $matches);
		list($lable, $name) = $matches;
		for($i = 0; $i < count($lable); $i ++)
			if(array_key_exists($name[$i], $lables))
				$text = str_replace($lable[$i], $lables[$name[$i]], $text);
		return $text;
	}

	//替换html标签
	function myspecialchars($str){
		$str = htmlspecialchars($str,ENT_QUOTES);
		$str = str_replace('&amp;#','&#',$str);
		return $str;
	}

	//
	function common_myspecialchars($arg)
	{
		if(is_array($arg)){
			foreach($arg as $k=>$v){
				$arg[$k] = $this->common_myspecialchars($v);
			}
			return $arg ;
		}else if(is_string($arg)){
			$arg = htmlspecialchars(trim($arg),ENT_QUOTES);
			$arg = str_replace('&amp;','&',$arg);
			return $arg;
		}
		else if(is_int($arg)) return $arg ;
		return false ;
	}

	/* 按字符转换url编码 */
	function str_tourlwords( $old ){
		$tab_text = str_split( $old );
		$output = '';
		foreach ($tab_text as $k=>$v){
			$hex = dechex(ord($v));
			if($hex=='a')$hex='0D%0A';
			$output .= '%' . $hex;
		}
		return strtoupper($output);
	}

	// curl get 
	public function curl_get($get_str,$ttl=20) {
		$curl = curl_init($get_str);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, $ttl);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}

	// curl post 
	public function curl_post($url,$data,$ttl=20){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_TIMEOUT, $ttl);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}


	public function curl_post_new($url,$data,$ttl=20, $ip=null) {
		$curl = curl_init();

		if ($ip) {
			$pattern = "/(http[s]?\:\/\/)?([^\/]+)(.+)/";
			$c =array();
			preg_match($pattern, $url, $c);
			if (empty($c[2])) {
				return false;
			}
			$host = $c[2];
			$url = $c[1] . $ip . $c[3];

			preg_replace('//', $ip, $url);
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Host: ' . $host));
		} else {
			curl_setopt($curl, CURLOPT_URL, $url);
		}

		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_TIMEOUT, $ttl);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;

	}
	/**
	 * 二维数据排序方法（冒泡方式）
	 * 本函数仅限于对二维数组中的数字字段进行排序
	 * @param array 需要排序的array $a
	 * @param string 需要排序的字段 $sort
	 * @param string 排序方式 默认为升序 $d=d为降 $d
	 * @return array
	 */
	function array2sort($a,$sort,$d='') {
		$num=count($a);
		if(!$d){
			for($i=0;$i<$num;$i++){
				for($j=0;$j<$num-1;$j++){
					if($a[$j][$sort] > $a[$j+1][$sort]){
						foreach ($a[$j] as $key=>$temp){
							$t=$a[$j+1][$key];
							$a[$j+1][$key]=$a[$j][$key];
							$a[$j][$key]=$t;
						}
					}
				}
			}
		}
		else{
			for($i=0;$i<$num;$i++){
				for($j=0;$j<$num-1;$j++){
					if($a[$j][$sort] < $a[$j+1][$sort]){
						foreach ($a[$j] as $key=>$temp){
							$t=$a[$j+1][$key];
							$a[$j+1][$key]=$a[$j][$key];
							$a[$j][$key]=$t;
						}
					}
				}
			}
		}
		return $a;
	}

	/**
	 * 
	 * 根据指定的键对数组进行排序
	 * @param array $array
	 * @param string $keyname
	 * @param string $dir
	 */
	public function sortByCol( $array , $keyname , $dir = SORT_DESC )
	{
		return $this->sortByMultiCols( $array , array( $keyname => $dir ) );
	}

	/**
	* 将一个二维数组按照多个列进行排序，类似 SQL 语句中的 ORDER BY
	*
	* 用法：
	* @code php
	* $rows = Helper_Array::sortByMultiCols($rows, array(
	*           'parent' => SORT_ASC, 
	*           'name' => SORT_DESC,
	* ));
	* @endcode
	*
	* @param array $rowset 要排序的数组
	* @param array $args 排序的键
	*
	* @return array 排序后的数组
	*/
	public function sortByMultiCols( $rowset, $args )
	{
		$sortArray = array();
		$sortRule = '';
		foreach ( $args as $sortField => $sortDir ) 
		{
			foreach ( $rowset as $offset => $row ) 
			{
				$sortArray[$sortField][$offset] = $row[$sortField];
			}
			
			$sortRule .= '$sortArray[\'' . $sortField . '\'], ' . $sortDir . ', ';
		}
		
		if ( empty($sortArray) || empty($sortRule ) )
			return $rowset;
			
		eval( 'array_multisort(' . $sortRule . '$rowset);' );
		return $rowset;
	}

	/**
	 * 随机输出数组内容
	 */
	function getRecInfo($arr,$need_num,$now_list_arr2)
	{
		$tmp_num = count($now_list_arr2);
		if($tmp_num==0) {
			return $arr;
		}else{
			if($tmp_num<$need_num){
				$need_num = $tmp_num;
			}
			$key = array_rand($now_list_arr2,$need_num);
			if(!is_array($key)){
				$key = array($key);
			}
			foreach($key as $k){
				array_push($arr,$now_list_arr2[$k]);
			}
			unset($key);
			return $arr;
		}
	}

	/**
	* 时间格式转换函数
	* unix时间戳转换成指定格式。$new=1:2013-05-13 12:25:00
	* @param $new:1秒前,1分钟前,1小时前,3天前,比现在时间大于4天的显示:2013-05-13 12:25:00
	* @param string $yang=1 , 2 , 3 种格式
	* @return string 时间
	*/
	public static function time_new($time,$new=0,$yang=1,$suffix_str = '前')
	{
		if(empty($time))
		{
			return 0;
		}

		$time_now=time();
		if($new==1)
		{
			$time=date('Y-m-d H:i:s',$time);
		}
		else
		{
			$cha=$time_now-$time;
			if($cha <= 10 && $cha > 0)
			{
				$time = '10秒' . $suffix_str;
			}
			elseif ($cha <= 0)
			{
				$time = '5秒' . $suffix_str;
			}
			elseif($cha > 10 && $cha < 60)
			{
				$time=$cha."秒" . $suffix_str;
			}
			elseif($cha >= 60 && $cha < 3600)
			{
				$cha=floor($cha/60);
				$time=$cha."分钟" . $suffix_str;
			}elseif($cha >= 3600 && $cha < 86400)
			{
				$cha=floor($cha/3600);
				$time=$cha."小时" . $suffix_str;
			}elseif($cha >= 86400 && $cha < 345600)
			{
				$cha=floor($cha/86400);
				$time=$cha."天" . $suffix_str;
			}elseif($yang==1){
				$time=date("m月d日 H:i",$time);
			}elseif($yang==2) {
				$time=date("m月d日",$time);
			}elseif($yang==3) {
				$time=date("Y-m-d H:i",$time);
				$time = substr($time,2,14);
			}else {
				$time=date("Y-m-d",$time);
			}
		}
		return $time;
	}

	/**
	* 时间差格式函数  秒数转换 时，分，天　秒
	* @param string $sec 时间差 = 结束时间 - 开始时间
	* @return string 时间
	*/
	function FormatSeconds($sec){
		$d=0;
		$h=0;
		$m=0;
		$s=0;
		if($sec>=24*3600){
			$d = floor($sec / (24*3600));
			$sec = $sec % (24*3600);
		}
		if($sec){
			if($sec>=3600){
				$h = floor($sec / 3600);
				$sec = $sec % 3600;
			}
			if($sec){
				$m = floor($sec / 60);
				$s = intval($sec - $m * 60);
			}
		}
		$s_time="";
		if($d) $s_time.=$d."天";
		if($h) $s_time.=$h."时";
		if($m) $s_time.=$m."分";
		if($s) $s_time.=$s."秒";
		return $s_time;
	}

	// 原 show_nav_bar
	function show_pager($index,$count,$pagesize,$link_url){

		if(!isset($next_name)) $next_name = '下一页';
		if(!isset($prev_name)) $prev_name = '上一页';

		$count = intval($count);
		$pagesize = intval($pagesize);
		$index = (empty($index) || intval($index)==FALSE) ? 1 : intval($index);

		$pagecount = (int)($count /$pagesize ) ;
		if($count % $pagesize) $pagecount++ ;

		if( $pagecount < 2){ return '';	}

		if(!isset($max_showpage)) $max_showpage = 5 ;

		$start = max(1, $index - intval($max_showpage/2));
		$end = min($start + $max_showpage - 1, $pagecount);
		$start = max(1, $end - $max_showpage + 1);

		if($index < $start) $index = $start;
		if($index > $end) $index = $end ;

		$html = '<div class="pages">';

		if($index  > 1){
			$html .= ' <a class="nextprev" href="'.$link_url.($index-1).'">'.$prev_name.'</a>';
		}

		if($start > 1){
			$icount = 0 ;
			for($i=1;$i<$start;$i++){
				if($icount > 1)
					break ;
				$html .= ' <a href="'.$link_url.$i.'">'.$i.'</a>'."";
				$icount ++ ;
			}
			if($start > 3){
				$html .= ' <a href="'.$link_url.max($index-$max_showpage,1).'">...</a>' ;
			}
		}

		for($i=$start;$i<$end+1;$i++){
			if($i==$index){
				$html .= ' <span class="this">'.$i.'</span>'."";
				continue ;
			}
			$html .= ' <a href="'.$link_url.$i.'">'.$i.'</a>'."";
		}

		if($end < $pagecount){
			if($pagecount - $end > 2){
				$html .= ' <a href="'.$link_url.min($index+$max_showpage,$pagecount).'">...</a>' ;
			}
			$icount = 0 ;
			$i= $pagecount - $end > 2 ? $pagecount-1 : $end+1 ;
			for(;$i<=$pagecount;$i++){
				if($icount > 1)
					break ;
				$html .= ' <a href="'.$link_url.$i.'">'.$i.'</a>'."";
				$icount ++ ;
			}
		}

		if($index < $pagecount){
			//$html .= ' <span class="nextprev">'.$next_name.'</span>';
		//}else{
			$html .= ' <a class="nextprev" href="'.$link_url.($index+1).'">'.$next_name.'</a>';
		}

		$html .= '</div>';

		return $html;
	}
	/**
	 * 个人中心关注动态分页
	 * @param $index
	 * @param $count
	 * @param $pagesize
	 * @param $link_url
	 */
	function show_follow_pager($index,$count,$pagesize,$link_url){
		$count = intval($count);
		$pagesize = intval($pagesize);
		$index = (empty($index) || intval($index)==FALSE) ? 1 : intval($index);
		$pagecount = (int)($count /$pagesize ) ;
		if($count % $pagesize) $pagecount++ ;
		if( $pagecount < 2){ return '';	}
		if(!isset($max_showpage)) $max_showpage = 5 ;
		$start = max(1, $index - intval($max_showpage/2));
		$end = min($start + $max_showpage - 1, $pagecount);
		$start = max(1, $end - $max_showpage + 1);
		if($index < $start) $index = $start;
		if($index > $end) $index = $end ;
		$html = '<div >';
		if($index > 1)
			$html .= '<a class="next" href="' .  $link_url . ($index - 1) . '/">上一页</a>';
		else
			$html .= '<span class="current prev">上一页</span>';
		if($start > 1){
			$icount = 0 ;
			for($i=1;$i<$start;$i++){
				if($icount > 1)
					break ;
				$html .= ' <a href="'.$link_url.$i.'/">' . $i . '</a>' . "";
				$icount ++ ;
			}
			if($start > 3)
				$html .= '<span>...</span>';
		}
		for($i=$start;$i<$end+1;$i++){
			if($i==$index){
				$html .= '<span class="current">' . $i . '</span>';
				continue ;
			}
			$html .= ' <a href="' . $link_url . $i . '/">' . $i . '</a>' . '';
		}
		if($end < $pagecount){
			if($pagecount - $end > 2){
				$html .= ' <span>...</span>' ;
			}
			$icount = 0 ;
			$i= $pagecount - $end > 2 ? $pagecount-1 : $end+1 ;
			for(;$i<=$pagecount;$i++){
				if($icount > 1)
					break ;
				$html .= ' <a href="'.$link_url.$i.'/">'.$i.'</a>'."";
				$icount ++ ;
			}
		}
		if($index < $pagecount)
			$html .= '<a class="next" href="' . $link_url . ($index + 1) . '/' . '">下一页</a>';
		else
			$html .= '<span class="current prev">下一页</span>';	
		$html .= '</div>';
		return $html;
	}
	/**
	 * 个人中心关注动态分页H5
	 * @param $index
	 * @param $count
	 * @param $pagesize
	 * @param $link_url
	 */
	function show_follow_pager_h5($index,$count,$pagesize,$link_url){
		$count = intval($count);
		$pagesize = intval($pagesize);
		$index = (empty($index) || intval($index)==FALSE) ? 1 : intval($index);
		$pagecount = (int)($count /$pagesize ) ;
		if($count % $pagesize) $pagecount++ ;
		if( $pagecount < 2){ return '';	}
		if(!isset($max_showpage)) $max_showpage = 5 ;
		$start = max(1, $index - intval($max_showpage/2));
		$end = min($start + $max_showpage - 1, $pagecount);
		$start = max(1, $end - $max_showpage + 1);
		if($index < $start) $index = $start;
		if($index > $end) $index = $end ;
		$html = '<div class="pagination1">';
		if($index > 1)
			$html .= '<a class="next" href="' .  $link_url . ($index - 1) . '/">上一页</a>';
		else
			$html .= '<span class="current prev">上一页</span>';
		if($index < $pagecount)
			$html .= '<a class="next" href="' . $link_url . ($index + 1) . '/' . '">下一页</a>';
		else
			$html .= '<span class="current prev">下一页</span>';
		$html .= '</div>';
		return $html;
	}
	/**
	 * 搜索分页 + ajax 配合 post关键字 执行翻页
	 * @param $index
	 * @param $count
	 * @param $pagesize
	 * @param $link_url
	 */
	function search_page_ajax($index,$count,$pagesize,$type=1)
	{
		$prev_name = '上一页';
		$next_name = '下一页';
		$count = intval($count);
		$pagesize = intval($pagesize);
		$index = (empty($index) || intval($index)==FALSE) ? 1 : intval($index);
		$pagecount = (int)($count /$pagesize ) ;
		if($count % $pagesize) $pagecount++ ;
		if( $pagecount < 2){ return '';	}
		if(!isset($max_showpage)) $max_showpage = 5 ;
		$start = max(1, $index - intval($max_showpage/2));
		$end = min($start + $max_showpage - 1, $pagecount);
		$start = max(1, $end - $max_showpage + 1);
		if($index < $start) $index = $start;
		if($index > $end) $index = $end ;
		$html = '<div class="pagination1">';
		$css_top = $css_end = $btn_top =  $btn_end = $btn_top2 =  $btn_end2 = '';
		if($index > 1)
			$btn_top = 'onclick="search_gopage(\''.($index-1).'\',\''.$type.'\')"';
		if($index > 1)
			$html .= '<a class="next" href="javascript:;" ' . $btn_top . '>上一页</a>';
		else
			$html .= '<span class="current prev">上一页</span>';
		if($start > 1){
			$icount = 0 ;
			for($i=1;$i<$start;$i++){
				if($icount > 1)
					break ;
				$html .= ' <a href="javascript:;" onclick="search_gopage(\''.$i.'\',\''.$type.'\')">'.$i.'</a>'."";
				$icount ++ ;
			}
			if($start > 3){
			//$html .= ' <a href="javascript:;" onclick="search_gopage(\''.max($index-$max_showpage,1).'\',\''.$type.'\')">...</a>' ;
				$html .= '<span>...</span>';
			}
		}
		for($i=$start;$i<$end+1;$i++){
			if($i==$index){
				$html .= '<span class="current">' . $i . '</span>';
				continue ;
			}
			$html .= ' <a onclick="search_gopage(\''.$i.'\',\''.$type.'\')">'.$i.'</a>'."";
		}

		if($end < $pagecount){
			if($pagecount - $end > 2){
				$html .= ' <span>...</span>' ;
			}
			$icount = 0 ;
			$i= $pagecount - $end > 2 ? $pagecount-1 : $end+1 ;
			for(;$i<=$pagecount;$i++){
				if($icount > 1)
					break ;
				$html .= ' <a onclick="search_gopage(\''.$i.'\',\''.$type.'\')">'.$i.'</a>'."";
				$icount ++ ;
			}
		}

		if($index < $pagecount)
			$btn_end = 'onclick="search_gopage(\''.($index+1).'\',\''.$type.'\')"';
		if($index < $pagecount)
			$html .= '<a class="next" href="javascript:;" ' . $btn_end . '>下一页</a>';
		else
			$html .= '<span class="current prev">下一页</span>';
		
		$html .= '</div>';
		return $html;
	}

	/**
	 * 获取当前时间的微秒时间如：2011-07-06 09:37:37.96875
	 */
	public static function microsecondsDate()
	{
		list ($usec, $sec) = explode(" ", microtime());
		$tstr = date("Y-m-d H:i:s", $sec) . substr($usec, 1, 6);
		return $tstr;
	}

	/**
	 * 获取ajax分页
	 * @param unknown_type $index
	 * @param unknown_type $count
	 * @param unknown_type $pagesize
	 * @param unknown_type $link_url
	 * @return string
	 */
	function ajax_pager ($index,$count,$pagesize,$link_url){
		$index = intval($index);
		$index = $index ? $index : 1;

		$count = intval($count);
		if (! $count)
			return false;
		$pagesize = intval($pagesize);

		$pagecount = ($count /$pagesize );
		$pagecount = intval($pagecount);

		if($count % $pagesize) 
			$pagecount++ ;

		if( $pagecount < 2)
			return false;

		$max_showpage = 5 ;
		$start = max(1, $index - intval($max_showpage/2));

		$end = min($start + $max_showpage - 1, $pagecount);

		$start = max(1, $end - $max_showpage + 1);

		if($index < $start) 
			$index = $start;

		if($index > $end) 
			$index = $end ;

		$html = '<div class="pagination1">';
		
$html .= $index - 1 ? '<a class="next" href="javascript:;" data-page="' .($index - 1). '">上一页</a>' : '<span class="current prev">上一页</span>';
	
		if($start > 1){
			
$icount = 0 ;
			for($i=1;$i<$start;$i++){
				if($icount > 1)

					break ;
	
				$html .= ' <a href="javascript:;" data-page="' . $i . '">' . $i . '</a>';

				$icount ++ ;
	
			}

			if($start > 3)
	
				$html .= '<span>...</span>';
		}
		for($i=$start;$i<$end+1;$i++){
			if($i==$index){
		
		$html .= '<span class="current">' . $i . '</span>';
	
				continue ;

			}

			$html .= ' <a href="javascript:;" data-page="' . $i . '">' . $i . '</a>';
		}

		if($end < $pagecount){
			if($pagecount - $end > 2)
	
				$html .= ' <span>...</span>' ;

			$icount = 0 ;

			$i= $pagecount - $end > 2 ? $pagecount-1 : $end+1 ;

			for(;$i<=$pagecount;$i++){

				if($icount > 1)

					break ;
	
				$html .= ' <a href="javascript:;" data-page="' . $i . '">'.$i.'</a>';

				$icount ++ ;
	
			}

		}

		$html .= $index < $pagecount ? '<a class="next" href="javascript:;" data-page="' . ($index + 1) . '">下一页</a>' : '<span class="current prev">下一页</span>';

		$html .= '</div>';

		return $html;

	}


	//判断内容是否为IP
	function is_ip($gonten){
		$ip = explode('.',$gonten);
		for($i=0;$i<count($ip);$i++)
		{
			if($ip[$i]>255){
				return (0);
			}
		}
		return preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/",$gonten);  
	} 
	//判断日期间隔
	function datediff($part, $begin, $end)
	{
// 		$diff = strtotime($end) - strtotime($begin);
		$diff = $end-$begin;
		switch($part)
		{
			case "y": $retval = bcdiv($diff, (60 * 60 * 24 * 365)); break;
			case "m": $retval = bcdiv($diff, (60 * 60 * 24 * 30)); break;
			case "w": $retval = bcdiv($diff, (60 * 60 * 24 * 7)); break;
			case "d": $retval = bcdiv($diff, (60 * 60 * 24)); break;
			case "h": $retval = bcdiv($diff, (60 * 60)); break;
			case "n": $retval = bcdiv($diff, 60); break;
			case "s": $retval = $diff; break;
		}
		return $retval;
	}

    /**
     *  将一个字串中含有全角的数字字符、字母、空格或'%+-()'字符转换为相应半角字符
     *
     * @access  public
     * @param   string　$str　待转换字串
     * @return  string
     */
    function make_semiangle($str)
    {
        $arr = array('０' => '0', '１' => '1', '２' => '2', '３' => '3', '４' => '4',
            '５' => '5', '６' => '6', '７' => '7', '８' => '8', '９' => '9',
            'Ａ' => 'A', 'Ｂ' => 'B', 'Ｃ' => 'C', 'Ｄ' => 'D', 'Ｅ' => 'E',
            'Ｆ' => 'F', 'Ｇ' => 'G', 'Ｈ' => 'H', 'Ｉ' => 'I', 'Ｊ' => 'J',
            'Ｋ' => 'K', 'Ｌ' => 'L', 'Ｍ' => 'M', 'Ｎ' => 'N', 'Ｏ' => 'O',
            'Ｐ' => 'P', 'Ｑ' => 'Q', 'Ｒ' => 'R', 'Ｓ' => 'S', 'Ｔ' => 'T',
            'Ｕ' => 'U', 'Ｖ' => 'V', 'Ｗ' => 'W', 'Ｘ' => 'X', 'Ｙ' => 'Y',
            'Ｚ' => 'Z', 'ａ' => 'a', 'ｂ' => 'b', 'ｃ' => 'c', 'ｄ' => 'd',
            'ｅ' => 'e', 'ｆ' => 'f', 'ｇ' => 'g', 'ｈ' => 'h', 'ｉ' => 'i',
            'ｊ' => 'j', 'ｋ' => 'k', 'ｌ' => 'l', 'ｍ' => 'm', 'ｎ' => 'n',
            'ｏ' => 'o', 'ｐ' => 'p', 'ｑ' => 'q', 'ｒ' => 'r', 'ｓ' => 's',
            'ｔ' => 't', 'ｕ' => 'u', 'ｖ' => 'v', 'ｗ' => 'w', 'ｘ' => 'x',
            'ｙ' => 'y', 'ｚ' => 'z',
            '（' => '(', '）' => ')', '〔' => '[', '〕' => ']', '【' => '[',
            '】' => ']', '〖' => '[', '〗' => ']', '“' => '[', '”' => ']',
            '‘' => '[', '’' => ']', '｛' => '{', '｝' => '}', '《' => '<',
            '》' => '>',
            '％' => '%', '＋' => '+', '—' => '-', '－' => '-', '～' => '-',
            '：' => ':', '。' => '.', '、' => ',', '，' => '.', '、' => '.',
            '；' => ',', '？' => '?', '！' => '!', '…' => '-', '‖' => '|',
            '”' => '"', '’' => '`', '‘' => '`', '｜' => '|', '〃' => '"',
            '　' => ' ');

        return strtr($str, $arr);
    }
	/**
     * 判断是否为手机访问
     */
    function isMobile() {
		// 判断手机端强制访问电脑版
		if(isset($_COOKIE['wapparam']) && trim($_COOKIE['wapparam']) == 'wap2web'){
			return false;
		}
		// 判断是否为手机客户端
		if( $this->isMobileClient() ){
			$this->setCookies(array('wapparam'=>'web2wap'), 0, '/');
			return true;
		}else{
			return false;
		}
		
		return false;
	}

    /**
     * 判断是否为手机客户端
     */
    function isMobileClient() {
		
    	// 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    	if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])){
    		return true;
    	}
    	//如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    	if (isset ($_SERVER['HTTP_VIA'])) {
    		//找不到为flase,否则为true
    		return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    	}
    	//判断手机发送的客户端标志,兼容性有待提高
    	if (isset ($_SERVER['HTTP_USER_AGENT'])) {
    		$clientkeywords = array (
    				'nokia',
    				'sony',
    				'ericsson',
    				'mot',
    				'samsung',
    				'htc',
    				'sgh',
    				'lg',
    				'sharp',
    				'sie-',
    				'philips',
    				'panasonic',
    				'alcatel',
    				'lenovo',
    				'iphone',
    				'ipod',
    				'blackberry',
    				'meizu',
    				'android',
    				'netfront',
    				'symbian',
    				'ucweb',
    				'windowsce',
    				'palm',
    				'operamini',
    				'operamobi',
    				'openwave',
    				'nexusone',
    				'cldc',
    				'midp',
    				'wap',
    				'mobile'
    		);
    		// 从HTTP_USER_AGENT中查找手机浏览器的关键字
    		if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
    			return true;
    		}
    	}
    	//协议法，因为有可能不准确，放到最后判断
    	if (isset ($_SERVER['HTTP_ACCEPT'])) {
    		// 如果只支持wml并且不支持html那一定是移动设备
    		// 如果支持wml和html但是wml在html之前则是移动设备
    		if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
    			return true;
    		}
    	}
    	return false;
    }
    //stdClass to Array
	function object2array($object) {  
	    if (is_object($object)) {  
	        foreach ($object as $key => $value) {  
	            $array[$key] = $value;  
	        }  
	    }  
	    else {  
	        $array = $object;  
	    }  
	    return $array;  
	}
	
	/**
	 * Hash 将字符串转换成小于10位的数字
	 * @access public
	 * @param $str	转换的字符串
	 * @return num
	 */
	function ELFHash($str) {
		$hash = $x = 0;
		$n = strlen($str);
		for ($i = 0; $i <$n; $i++){
			$hash = ($hash <<4) + ord($str[$i]);
			if(($x = $hash & 0xf0000000) != 0){
				$hash ^= ($x>> 24);
				$hash &= ~$x;
			}
		}
		return $hash ;
	}
	
	// 取url头信息，验证url是否存在
	public function get_url_header( $url, $fsock_timeout=3 )
	{
		if( empty($url) || substr($url,0,7)!='http://' )
			return false;
		
		$url2 = parse_url($url);
		if(!isset($url2['path']))$url2['path']='';
		$url2["path"] = ($url2["path"] == "" ? "/" : $url2["path"]);
		$url2["port"] = (isset($url2["port"])?$url2["port"]:80);
		$host_ip = @gethostbyname($url2["host"]);

		// 连接错误返回 false;
		if(!($fsock = fsockopen($host_ip, $url2["port"], $errno, $errstr, $fsock_timeout))){
			return false;
		}
		$request =  $url2["path"] .(isset($url2["query"]) ? "?".$url2["query"] : "");

		$in  = "GET " . $request . " HTTP/1.0\r\n";
		$in .= "Accept: */*\r\n";
		$in .= "User-Agent: Payb-Agent\r\n";
		$in .= "Host: " . $url2["host"] . "\r\n";
		$in .= "Connection: Close\r\n\r\n";

		stream_set_timeout( $fsock , $fsock_timeout ) ;  
		// 提交信息错误关闭连接,返回 false;
		if(!@fwrite($fsock, $in, strlen($in))){
			fclose($fsock);
			return false;
		}
		$status = stream_get_meta_data( $fsock ) ;
		//发送数据超时;
		if($status['timed_out'] ){
			fclose( $fsock );
			return false;
		}
		$out = null;
		while($buff = @fgets($fsock, 2048)){
			$out .= $buff;
			//只读取头部信息;
			if(false!==strpos($out, "\r\n\r\n"))break;
		}
		$status = stream_get_meta_data( $fsock ) ;
		//读取数据超时;
		if( $status['timed_out'] ){
			fclose( $fsock );
			return false;
		}
		fclose($fsock);
		$pos = strpos($out, "\r\n\r\n");
		$head = substr($out, 0, $pos);
		return $head;
		
	}
		
	// 检测referer
	public function check_refer() {
		$ret = false;
		if (! isset ( $_SERVER ['HTTP_REFERER'] ) || empty ( $_SERVER ['HTTP_REFERER'] )) {
			return false;
		}
		
		$rule = <<<EOF
		#此配置文件为检查http referer的配置文件
			#允许的url和域名都可以在此配置
			#以及有安全风险的合法referer也可以在此禁止
			#井号为注释符
			
			[allow]
			#此处为允许的url或domain，会自动加上
			{$_SERVER['HTTP_HOST']}

			[ban]
			#以下为禁止的关键字的正则，尽量不要盲目修改
			<
			>
			\\\\
			document\. 
			(.)?([a-zA-Z]+)?(Element)+(.*)?(\()+(.)*(\))+
			(<script)+[\s]?(.)*(>)+
			src[\s]?(=)+(.)*(>)+
			[\s]+on[a-zA-Z]+[\s]?(=)+(.)*
			new[\s]+XMLHttp[a-zA-Z]+
			\@import[\s]+(\")?(\')?(http\:\/\/)?(url)?(\()?(javascript:)?
EOF;
		$ini_array = array ();
		$inikey = '';
		foreach ( explode ( PHP_EOL, $rule ) as $line ) {
			$line = trim ( $line );
			if (empty ( $line ))
				continue;
			
			if (strpos ( $line, '#' ) === 0)
				continue;
			
			if (preg_match ( "/^\[(.+)\]$/i", $line, $matches )) {
				$inikey = $matches [1];
				continue;
			}
			
			if (! empty ( $inikey )) {
				$ini_array [$inikey] [] = trim ( $line );
			}
		}
		
		if (count ( $ini_array ) == 0)
			return true;
		
		$ref = urldecode ( $_SERVER ['HTTP_REFERER'] );
		
		if (strpos ( $ref, 'http://' ) !== 0 && strpos ( $ref, 'https://' ) !== 0) {
			$ref = 'http://' . $ref;
		}
		
		$refArr = parse_url ( $ref );
		$refhost = $refArr ['host'];
		
		foreach ( $ini_array ['allow'] as $url ) {
			if (strpos ( $url, 'http://' ) !== 0 && strpos ( $url, 'https://' ) !== 0) {
				$url = 'http://' . $url;
			}
			
			$urlhostArr = parse_url ( $url );
			$urlhost = $urlhostArr ['host'];
			
			if (preg_match ( "/^" . preg_quote ( $urlhost, '/' ) . "$/i", $refhost )) {
				$ret = true;
			}
			
			if ($ret) {
				$urlcomp = $urlhostArr ['scheme'] . '://' . $urlhostArr ['host'] . $urlhostArr ['path'];
				$refcomp = $refArr ['scheme'] . '://' . $refArr ['host'] . $refArr ['path'];
				$urlreg = "/^" . preg_quote ( $urlcomp, '/' ) . "/i";
				if (preg_match ( $urlreg, $refcomp )) {
					if (strlen ( $refcomp ) != strlen ( $urlcomp ) && substr ( $refcomp, strlen ( $urlcomp ), 1 ) != '/') {
						$ret = false;
					} else {
						break;
					}
				} else {
					$ret = false;
				}
			}
		}
		
		if ($ret) {
			if (isset ( $ini_array ['ban'] )) {
				foreach ( $ini_array ['ban'] as $reg ) {
					if (preg_match ( "/" . $reg . "/", $ref )) {
						$ret = false;
						break;
					}
				}
			}
		}
		
		return $ret;
	}
	
	//字符串截取函数
	function cut_str($string, $sublen, $start = 0, $code = 'UTF-8') {
		if($code == 'UTF-8') {
			$pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
			preg_match_all($pa, $string, $t_string);
			if(count($t_string[0]) - $start > $sublen) return join('', array_slice($t_string[0], $start, $sublen))."...";
			return join('', array_slice($t_string[0], $start, $sublen));
		} else {
			$start = $start*2;
			$sublen = $sublen*2;
			$strlen = strlen($string);
			$tmpstr = '';
			for($i=0; $i< $strlen; $i++) {
				if($i>=$start && $i< ($start+$sublen)) {
					if(ord(substr($string, $i, 1))>129) {
						$tmpstr.= substr($string, $i, 2);
					} else {
						$tmpstr.= substr($string, $i, 1);
					}
				}
				if(ord(substr($string, $i, 1))>129) $i++;
			}
			if(strlen($tmpstr)< $strlen ) $tmpstr.= "...";
			return $tmpstr;
		}
	}
	
	
	
	// --------------------------------------------------------------------------------------------------------//
	/**
	 * 转成 int 型
	 * @param unknown $numeric
	 */
	public function filter_int($numeric) {
		return is_numeric($numeric) ? $numeric : (int) $numeric;
	}
	
	
	public function array_random_assoc($arr, $num = 1) {
		$keys = array_keys($arr);
		shuffle($keys);
		 
		$r = array();
		for ($i = 0; $i < $num; $i++) {
			$r[$keys[$i]] = $arr[$keys[$i]];
		}
		return $r;
	}
	
	
	
	// ------------------------------------------------------------------------------------------------------------//
	public function urlsafe_b64encode($string) {
		$data = base64_encode($string);
		$data = str_replace(array('+','/','='),array('-','_',''),$data);
		return $data;
	}
	
	function urlsafe_b64decode($string) {
		$data = str_replace(array('-','_'),array('+','/'),$string);
		$mod4 = strlen($data) % 4;
		if ($mod4) {
			$data .= substr('====', $mod4);
		}
		return base64_decode($data);
	}
	
	/**
	 * 获取图片的宽高，内置的太慢。
	 * @param unknown $url
	 * @param string $type
	 * @param string $isGetFilesize
	 */
	public function get_image_size($url, $type = 'curl', $isGetFilesize = false)
	{
		// 若需要获取图片体积大小则默认使用 fread 方式
		$type = $isGetFilesize ? 'fread' : $type;
	
		if ($type == 'fread') {
			// 或者使用 socket 二进制方式读取, 需要获取图片体积大小最好使用此方法
			$handle = fopen($url, 'rb');
	
			if (! $handle) return false;
	
			// 只取头部固定长度168字节数据
			$dataBlock = fread($handle, 168);
		}
		else {
			// 据说 CURL 能缓存DNS 效率比 socket 高
			$ch = curl_init($url);
			// 超时设置
			curl_setopt($ch, CURLOPT_TIMEOUT, 2);
			// 取前面 168 个字符 通过四张测试图读取宽高结果都没有问题,若获取不到数据可适当加大数值
			curl_setopt($ch, CURLOPT_RANGE, '0-256');
			// 跟踪301跳转
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			// 返回结果
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
			$dataBlock = curl_exec($ch);
	
			curl_close($ch);
	
			if (! $dataBlock) return false;
		}
	
		// 将读取的图片信息转化为图片路径并获取图片信息,经测试,这里的转化设置 jpeg 对获取png,gif的信息没有影响,无须分别设置
		// 有些图片虽然可以在浏览器查看但实际已被损坏可能无法解析信息
		$size = getimagesize('data://image/jpeg;base64,'. base64_encode($dataBlock));
		if (empty($size)) {
			return false;
		}
	
		$result['width'] = $size[0];
		$result['height'] = $size[1];
	
		// 是否获取图片体积大小
		if ($isGetFilesize) {
			// 获取文件数据流信息
			$meta = stream_get_meta_data($handle);
			// nginx 的信息保存在 headers 里，apache 则直接在 wrapper_data
			$dataInfo = isset($meta['wrapper_data']['headers']) ? $meta['wrapper_data']['headers'] : $meta['wrapper_data'];
	
			foreach ($dataInfo as $va) {
				if ( preg_match('/length/iU', $va)) {
					$ts = explode(':', $va);
					$result['size'] = trim(array_pop($ts));
					break;
				}
			}
		}
	
		if ($type == 'fread') fclose($handle);
	
		return $result;
	}
	
	public function multiple_curl($res, $options="") {
		if (!is_array($res) || count ( $res ) <= 0)
			return False;
		
		$handles = array ();
		
		if (! $options) // add default options
			$options = array (
					CURLOPT_HEADER => 0,
					CURLOPT_RETURNTRANSFER => 1 
			);
			
			// add curl options to each handle
		foreach ( $res as $k => $row ) {
			$ch {$k} = curl_init ();
			$options [CURLOPT_URL] = $row;
			$opt = curl_setopt_array ( $ch {$k}, $options );
// 			var_dump ( $opt );
			$handles [$k] = $ch {$k};
		}
		
		$mh = curl_multi_init ();
		
		// add handles
		foreach ( $handles as $k => $handle ) {
			$err = curl_multi_add_handle ( $mh, $handle );
		}
		
		$running_handles = null;
		
		do {
			curl_multi_exec ( $mh, $running_handles );
			curl_multi_select ( $mh );
		} while ( $running_handles > 0 );
		
		$return = array();
		foreach ( $res as $k => $row ) {
			$return [$k] ['url'] = $row;
			$return [$k] ['error'] = curl_error ( $handles [$k] );
			if (! empty ( $return [$k] ['error'] ))
				$return [$k] ['data'] = '';
			else
				$return [$k] ['data'] = curl_multi_getcontent ( $handles [$k] ); // get results
					                                                           
			// close current handler
			curl_multi_remove_handle ( $mh, $handles [$k] );
		}
		curl_multi_close ( $mh );

		return $return; // return response
	}
	
	/**
	 * 代替 strtotime (+1 month)
	 * @param integer $numMonths [description]
	 * @param [type]  $timeStamp [description]
	 */
	function add_months_to_time($numMonths = 1, $timeStamp = null){
		$timeStamp === null and $timeStamp = time();//Default to the present
		$newMonthNumDays =  date('d',strtotime('last day of '.$numMonths.' months', $timeStamp));//Number of days in the new month
		$currentDayOfMonth = date('d',$timeStamp);
		if($currentDayOfMonth > $newMonthNumDays){
			$newTimeStamp = strtotime('-'.($currentDayOfMonth - $newMonthNumDays).' days '.$numMonths.' months', $timeStamp);
		} else {
			$newTimeStamp = strtotime($numMonths.' months', $timeStamp);
		}
		return $newTimeStamp;
	}
}
?>
