<?php

// 工具类
class Util {

	/**
	 * 能自动去除空元素explode
	 *
	 * @return Array
	 */
	public static function explode($seperator, $str) {
		if (! $str)
			return array ();
		else {
			$result = explode ( $seperator, $str );
			if (! $result)
				return $result;
			$temp = array ();
			for($i = 0; $i < count ( $result ); $i ++) {
				if ($result [$i] == '')
					continue;
				$temp [] = $result [$i];
			}
			return $temp;
		}
	}
	//CURL请求
	public static function curl_get_contents($destURL, $paramStr = '', $flag = 'get') {
		if (!extension_loaded('curl'))
			exit('没有安装curl扩展');
		$curl = curl_init();
		if ($flag == 'post') {//post
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $paramStr);
		}
		curl_setopt($curl, CURLOPT_URL, $destURL);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$str = curl_exec($curl);
		curl_close($curl);
		return $str;
	}
	public static function getpost() {
		if (isset ( $_GET )) {
			foreach ( $_GET as $key => $value ) {
				$getrow [$key] = $value;
			}
		}
		if (isset ( $_POST )) {
			foreach ( $_POST as $key => $value ) {
				$getrow [$key] = $value;
			}
		}
		return $getrow;
		/*
		 * if (getenv('REQUEST_METHOD') == 'GET' || getenv('REQUEST_METHOD') == 'POST') {
		 * $getrow = array();
		 * if (getenv('REQUEST_METHOD') == 'GET') {
		 * $getrow = $_GET;
		 * }
		 * if (getenv('REQUEST_METHOD') == 'POST') {
		 * $getrow = $_POST;
		 * }
		 * return $getrow;
		 * }
		 */
	}

	// 得到当前用户Ip地址
	public static function getRealIp() {
		$pattern = '/(\d{1,3}\.){3}\d{1,3}/';
		if (isset ( $_SERVER ["HTTP_X_FORWARDED_FOR"] ) && preg_match_all ( $pattern, $_SERVER ['HTTP_X_FORWARDED_FOR'], $mat )) {
			foreach ( $mat [0] as $ip ) {
				// 得到第一个非内网的IP地址
				if ((0 != strpos ( $ip, '192.168.' )) && (0 != strpos ( $ip, '10.' )) && (0 != strpos ( $ip, '172.16.' ))) {
					return $ip;
				}
			}
			return $ip;
		} else {
			if (isset ( $_SERVER ["HTTP_CLIENT_IP"] ) && preg_match ( $pattern, $_SERVER ["HTTP_CLIENT_IP"] )) {
				return $_SERVER ["HTTP_CLIENT_IP"];
			} else {
				return $_SERVER ['REMOTE_ADDR'];
			}
		}
	}

	// 得到无符号整数表示的ip地址
	public static function getIntIp() {
		return sprintf ( '%u', ip2long ( self::getRealIp () ) );
	}

	// 文本入库前的过滤工作
	public static function getSafeText($textString, $htmlspecialchars = true) {
		return $htmlspecialchars ? htmlspecialchars ( trim ( strip_tags ( self::qj2bj ( $textString ) ) ) ) : trim ( strip_tags ( self::qj2bj ( $textString ) ) );
	}
	public static function outputExpireHeader($lifetime = 300) {
		header ( "Expires: " . gmdate ( "D, d M Y H:i:s", time () + $lifetime ) . " GMT" );
		header ( "Last-Modified: " . gmdate ( "D, d M Y H:i:s" ) . " GMT" );
		header ( "Cache-Control: max-age=$lifetime" );
	}

	// 格式化秒
	public static function getTimeHISByS($s, $type = 1) {
		if ($type == 0) {
			$i = ( int ) ($s / 60);
			$s = $s % 60;
			if ($i < 10) {
				$i = "0" . $i;
			}
			if ($s < 10) {
				$s = "0" . $s;
			}
			return $i . '分' . $s . '秒';
		} else {
			if ($s == '' || $s <= 0) {
				return 0;
			}
			$i = ( int ) ($s / 60);
			if ($i == 0) {
				return $s . "秒";
			}
			$s = $s % 60;
			$h = ( int ) ($i / 60);
			if ($h == 0) {
				return $i . '分' . $s . "秒";
			}
			$i = $i % 60;
			$d = ( int ) ($h / 24);
			if ($d == 0) {
				return $h . "小时" . $i . "分钟" . $s . "秒";
			}
			$h = $h % 24;
			return $d . "天" . $h . "小时" . $i . "分钟" . $s . "秒";
		}
	}

	// 全角 => 半角
	public static function qj2bj($string) {
		$convert_table = Array (
				'０' => '0',
				'１' => '1',
				'２' => '2',
				'３' => '3',
				'４' => '4',
				'５' => '5',
				'６' => '6',
				'７' => '7',
				'８' => '8',
				'９' => '9',
				'Ａ' => 'A',
				'Ｂ' => 'B',
				'Ｃ' => 'C',
				'Ｄ' => 'D',
				'Ｅ' => 'E',
				'Ｆ' => 'F',
				'Ｇ' => 'G',
				'Ｈ' => 'H',
				'Ｉ' => 'I',
				'Ｊ' => 'J',
				'Ｋ' => 'K',
				'Ｌ' => 'L',
				'Ｍ' => 'M',
				'Ｎ' => 'N',
				'Ｏ' => 'O',
				'Ｐ' => 'P',
				'Ｑ' => 'Q',
				'Ｒ' => 'R',
				'Ｓ' => 'S',
				'Ｔ' => 'T',
				'Ｕ' => 'U',
				'Ｖ' => 'V',
				'Ｗ' => 'W',
				'Ｘ' => 'X',
				'Ｙ' => 'Y',
				'Ｚ' => 'Z',
				'ａ' => 'a',
				'ｂ' => 'b',
				'ｃ' => 'c',
				'ｄ' => 'd',
				'ｅ' => 'e',
				'ｆ' => 'f',
				'ｇ' => 'g',
				'ｈ' => 'h',
				'ｉ' => 'i',
				'ｊ' => 'j',
				'ｋ' => 'k',
				'ｌ' => 'l',
				'ｍ' => 'm',
				'ｎ' => 'n',
				'ｏ' => 'o',
				'ｐ' => 'p',
				'ｑ' => 'q',
				'ｒ' => 'r',
				'ｓ' => 's',
				'ｔ' => 't',
				'ｕ' => 'u',
				'ｖ' => 'v',
				'ｗ' => 'w',
				'ｘ' => 'x',
				'ｙ' => 'y',
				'ｚ' => 'z',
				'　' => ' ',
				'：' => ':',
				'。' => '.',
				'？' => '?',
				'，' => ',',
				'／' => '/',
				'；' => ';',
				'［' => '[',
				'］' => ']',
				'｜' => '|',
				'＃' => '#'
		);
		return strtr ( $string, $convert_table );
	}

	// 时间显示
	public static function getTimeOver($timeLast, $timeNext = 0) {
		if (! $timeNext) {
			$timeNext = time ();
		}
		if ($timeLast === false || $timeNext === false || $timeLast > $timeNext) {
			return "时间异常";
		}

		$iAll = ( int ) (($timeNext - $timeLast) / 60);

		if ($iAll < 60) {
			$iAll = $iAll == 0 ? 1 : $iAll;
			return "{$iAll} 分钟前";
		}
		$hAll = ( int ) ($iAll / 60);
		if ($hAll < 24) {
			return "{$hAll} 小时前";
		}
		$dAll = ( int ) ($hAll / 24);
		if ($dAll < 30) {
			return "{$dAll} 天前";
		}
		if ($dAll < 365) {
			$m = ( int ) ($dAll / 30);
			return "{$m} 月前";
		}
		return date ( 'Y-m-d', $timeLast );
	}


	/**
	 * 输入一个秒数，返回时分秒格式的字符串
	 *
	 * @param int $secs
	 * @return string
	 */
	public static function secToTime($secs) {
		if ($secs < 3600) {
			return sprintf ( "%02d:%02d", floor ( $secs / 60 ), $secs % 60 );
		}
		$h = floor ( $secs / 3600 );
		$m = floor ( ($secs % 3600) / 60 );
		$s = $secs % 60;
		return sprintf ( "%02d:%02d:%02d", $h, $m, $s );
	}


	public static function myGetImageSize($url, $type = 'curl', $isGetFilesize = false) {
		// 若需要获取图片体积大小则默认使用 fread 方式
		$type = $isGetFilesize ? 'fread' : $type;

		if ($type == 'fread') {
			// 或者使用 socket 二进制方式读取, 需要获取图片体积大小最好使用此方法
			$handle = fopen ( $url, 'rb' );

			if (! $handle)
				return false;

				// 只取头部固定长度168字节数据
			$dataBlock = fread ( $handle, 1000 );
		} else {
			// 据说 CURL 能缓存DNS 效率比 socket 高
			$ch = curl_init ( $url );
			// 超时设置
			curl_setopt ( $ch, CURLOPT_TIMEOUT, 5 );
			// 取前面 168 个字符 通过四张测试图读取宽高结果都没有问题,若获取不到数据可适当加大数值
			curl_setopt ( $ch, CURLOPT_RANGE, '0-167' );
			// 跟踪301跳转
			curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
			// 返回结果
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );

			$dataBlock = curl_exec ( $ch );

			curl_close ( $ch );

			if (! $dataBlock)
				return false;
		}

		// 将读取的图片信息转化为图片路径并获取图片信息,经测试,这里的转化设置 jpeg 对获取png,gif的信息没有影响,无须分别设置
		// 有些图片虽然可以在浏览器查看但实际已被损坏可能无法解析信息
		// $size = getimagesize('data://image/jpeg;base64,' . base64_encode($dataBlock));
		// max yang 暂时先如此处理
		$size = getimagesize ( $url );
		if (empty ( $size )) {
			return false;
		}

		$result ['width'] = $size [0];
		$result ['height'] = $size [1];
		return $result;
	}
	public static function getHeadUrlFromConfig($config) {
		$prent = "/.+wb_profile_img\=(.+)&{0,1}/i";
		preg_match_all ( $prent, $config, $_tmpArr );
		return $_tmpArr [1] [0];
	}

	// 返回接口 success
	public static function echo_format_return($code = _SUCCESS_, $array = array(), $message = '请求成功', $exit = 0) {
		$re = array (
				'result' => $code,
				'data' => $array,
				'message' => $message,
				'time_offset' => 0,
				'sys_time' => SYS_TIME * 1000, 	// 毫秒
		);


		//add by wangbo8
		if (class_exists('CI_Controller')) {
			//获取数据
			$CI = get_instance();
			if ($CI instanceof CI_Controller) {
				$CI->load->library('seed');
				$ci_vars = get_object_vars($CI);
				if (!empty($ci_vars['partner_id'])) {
					if ($code == _SUCCESS_ || $code == _ERROR_BUT_NEED_NEW_) {
						$seed_code = $CI->seed->generate_new_seed();
					} else {
						$seed_code = $CI->seed->get_old_seed();
					}

					is_array($re['data']) || $re['data'] = array();
					$seed_code && $re['data']['seed'] = $seed_code;
				}
			}
		}

		// add by liule1, 1,29 2016
		if (!empty($_REQUEST['timestamp'])) {
			$timestamp = is_numeric($_REQUEST['timestamp']) ? $_REQUEST['timestamp'] : 0;
			$re['time_offset'] = SYS_TIME * 1000 - $timestamp;
		}

		// xhprof begin
		if (!empty($GLOBALS['xhprof'])) {
			$data = xhprof_disable();   //返回运行数据

			// xhprof_lib在下载的包里存在这个目录,记得将目录包含到运行的php代码中
			include_once "xhprof_lib/utils/xhprof_lib.php";
			include_once "xhprof_lib/utils/xhprof_runs.php";

			$objXhprofRun = new XHProfRuns_Default();

			// 第一个参数j是xhprof_disable()函数返回的运行信息
			// 第二个参数是自定义的命名空间字符串(任意字符串),
			// 返回运行ID,用这个ID查看相关的运行结果
			$run_id = $objXhprofRun->save_run($data, "xhprof");
			$re['xhprof_id'] = $run_id;
			unset($GLOBALS['xhprof']);
		}
		if (defined('JSON_UNESCAPED_UNICODE')) {
			echo json_encode ( $re, JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_UNICODE );
		} else {
			echo json_encode ( $re );
		}
		if ($exit) exit;
	}

	// 去掉html实体
	public static function html5ObjClean($str) {
		$str = preg_replace ( "/&[^;]*;/", "", $str );
		// $str=preg_replace("/<!--[^\>]*--\>/", "", $str);
		$str = preg_replace ( "/\t|\r|\n/", "", $str );
		return $str;
	}

	// 去除数组中空项
	public static function cleanArray($arr) {
		if (! is_array ( $arr )) {
			return $arr;
		}
		$arrtmp = array ();
		foreach ( $arr as $k => $v ) {
			if ($v != "") {
				array_push ( $arrtmp, trim ( $v ) );
			}
		}
		return $arrtmp;
	}

	// 去除标签方法
	public static function cleanTags($_content, $tag) {
		$pant = "/<" . $tag . "[^>]*>/";
		$_content = preg_replace ( $pant, "", $_content );
		$pant = "/<\/" . $tag . ">/";
		$_content = preg_replace ( $pant, "", $_content );
		return $_content;
	}

	// 远程加载一个图片
	function GrabImage($url, $filename = "") {
		if ($url == "")
			return false;

		if ($filename == "") {
			$ext = strrchr ( $url, "." );
			if ($ext != ".gif" && $ext != ".jpg" && $ext != ".png")
				return false;
				// $filename="tmpImg/".date("Y")."/".date("m")."/".date("d")."/".date("H")."/".md5($url).$ext;
			$filename = md5 ( $url ) . $ext;
		}
		ob_start ();
		readfile ( $url );
		$img = ob_get_contents ();
		ob_end_clean ();
		$size = strlen ( $img );

		$fp2 = @fopen ( $filename, "a" );
		fwrite ( $fp2, $img );
		fclose ( $fp2 );

		return $filename;
	}


	/**
	 * 安全IP检测，支持IP段检测
	 * @param string $ip 要检测的IP
	 * @param string|array $ips 白名单IP或者黑名单IP
	 * @return boolean true 在白名单或者黑名单中，否则不在
	 */
	public function is_safe_ip($ip="",$ips=""){
		if(!$ip) $ip = $this->get_client_ip(); //获取客户端IP
		if($ips){
			if(is_string($ips)){ //ip用"," 例如白名单IP：192.168.1.13,123.23.23.44,193.134.*.*
				$ips = explode(",", $ips);
			}
		}
		if(in_array($ip, $ips)){
			return true;
		}
		$ipregexp = implode('|', str_replace( array('*','.'), array('\d+','\.') ,$ips));
		$rs = preg_match("/^(".$ipregexp.")$/", $ip);
		if($rs) return true;
		return ;
	}
	/**
	 * 获取客户端IP地址
	 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
	 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
	 * @return mixed
	 */
	public function get_client_ip($type = 0,$adv=false) {
		$type = $type ? 1 : 0;
		static $ip = NULL;
		if ($ip !== NULL) return $ip[$type];
		if($adv){
			if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
				$pos = array_search('unknown',$arr);
				if(false !== $pos) unset($arr[$pos]);
				$ip = trim($arr[0]);
			}elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			}elseif (isset($_SERVER['REMOTE_ADDR'])) {
				$ip = $_SERVER['REMOTE_ADDR'];
			}
		}elseif (isset($_SERVER['REMOTE_ADDR'])) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
// IP地址合法验证
		$long = sprintf("%u",ip2long($ip));
		$ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
		return $ip[$type];
	}
	public static  function GetHttpStatusCode($url){
		$curl = curl_init();
		curl_setopt($curl,CURLOPT_URL,$url);//获取内容url
		curl_setopt($curl,CURLOPT_HEADER,1);//获取http头信息
		curl_setopt($curl,CURLOPT_NOBODY,1);//不返回html的body信息
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);//返回数据流，不直接输出
		curl_setopt($curl,CURLOPT_TIMEOUT,30); //超时时长，单位秒
		curl_exec($curl);
		$rtn= curl_getinfo($curl,CURLINFO_HTTP_CODE);
		curl_close($curl);
		return  $rtn;
	}

	//获取请求来源
	public static function getBrowse()
	{
	    global $_SERVER;
	    $Agent = $_SERVER['HTTP_USER_AGENT'];
	    $browseinfo='';
	    if(preg_match('/Mozill/', $Agent) && !preg_match('/MSIE/', $Agent)){
	        $browseinfo = 'Netscape Navigator';
	    }
	    if(preg_match('/Opera/', $Agent)) {
	        $browseinfo = 'Opera';
	    }
	    if(preg_match('/Mozilla/', $Agent) && preg_match('/MSIE/', $Agent)){

	        $browseinfo = 'Internet Explorer';
	    }
	    if(preg_match('/Chrome/', $Agent)){
	        $browseinfo="Chrome";
	    }
	    if(preg_match('/Safari/', $Agent)){
	        $browseinfo="Safari";
	    }
	    if(preg_match('/Firefox/', $Agent)){
	        $browseinfo="Firefox";
	    }
	    if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
	        $browseinfo= 'ios';
	    }else if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android')){
	        $browseinfo='android';
	    }
	    return $browseinfo;
	}

	/*
	 * 精确时间间隔函数
	 * $time 发布时间 如 1356973323
	 * $str 输出格式 如 Y-m-d H:i:s
	 * 半年的秒数为15552000，1年为31104000，此处用半年的时间
	 */
	public static function from_time($time,$str='m-d'){
	    isset($str)?$str:$str='m-d';
        $year = date('Y',$time);
        if($year < date('Y')){
            $str = 'Y-m-d';
        }
	    $way = time() - $time;
	    $r = '';
	    if($way < 60){
	        $r = '刚刚';
	    }elseif($way >= 60 && $way <3600){
	        $r = floor($way/60).'分钟前';
	    }elseif($way >=3600 && $way <86400){
	        $r = floor($way/3600).'小时前';
	    }elseif($way >=86400 && $way <2592000){
	        $r = date($str,$time);
	    }elseif($way >=2592000 && $way <15552000){
	        $r = date($str,$time);
	    }else{
	        $r = date('Y-m-d',$time);
	    }
	    return $r;
	}
}

?>
