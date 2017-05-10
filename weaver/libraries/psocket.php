<?php

/**
 * 加载库公用配置
 */
/**
 * 网络操作类
 */
class PSocket
{

    /**
     * httpget请求
     *
     * @param string $url 访问地址
     * @param int $timeout 超时
     * @return string $pageMemo
     */
    public static function httpGet ($url, $header = array(),$timeout = 30, $host = '')
    {
        $time_begin = time();

        if ($host)
        {
            $header[] = 'Host: ' . $host;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        if (! empty($header))
        {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        //curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727; .NET CLR 1.1.4322)");
        $pageMemo = curl_exec($ch);
        $info = curl_getinfo($ch);
        /**
         * $info 得到的http头信息
         * Array
			 (
			     [url] => http://serial.sina.com.cn/cgi-bin/GetSerial.cgi
			     [content_type] => text/html
			     [http_code] => 200
			     [header_size] => 169
			     [request_size] => 78
			     [filetime] => -1
			     [ssl_verify_result] => 0
			     [redirect_count] => 0
			     [total_time] => 0.01856
			     [namelookup_time] => 0.004189
			     [connect_time] => 0.004897
			     [pretransfer_time] => 0.004944
			     [size_upload] => 0
			     [size_download] => 30
			     [speed_download] => 1616
			     [speed_upload] => 0
			     [download_content_length] => 0
			     [upload_content_length] => 0
			     [starttransfer_time] => 0.018502
			     [redirect_time] => 0
			 )
         *
         */
        if (curl_errno($ch))
        {
        	curl_close ($ch);
            return false;
        }
        if ($info['http_code'] >= 400)
        {
			curl_close ($ch);
            return false;
        }
        curl_close ($ch);

        return $pageMemo;
    }

    /**
     * httpsslget请求
     *
     * @param string $url 访问地址
     * @param int $timeout 超时
     * @return $pageMemo
     */
    public static function httpSSLGet ($url, $timeout = 30)
    {
        $time_begin = time();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727; .NET CLR 1.1.4322)");
        $pageMemo = curl_exec($ch);
        $info = curl_getinfo($ch);
        /**
         * $info 得到的http头信息
         * Array
			 (
			     [url] => http://serial.sina.com.cn/cgi-bin/GetSerial.cgi
			     [content_type] => text/html
			     [http_code] => 200
			     [header_size] => 169
			     [request_size] => 78
			     [filetime] => -1
			     [ssl_verify_result] => 0
			     [redirect_count] => 0
			     [total_time] => 0.01856
			     [namelookup_time] => 0.004189
			     [connect_time] => 0.004897
			     [pretransfer_time] => 0.004944
			     [size_upload] => 0
			     [size_download] => 30
			     [speed_download] => 1616
			     [speed_upload] => 0
			     [download_content_length] => 0
			     [upload_content_length] => 0
			     [starttransfer_time] => 0.018502
			     [redirect_time] => 0
			 )
         *
         */
        if (curl_errno($ch))
        {
            curl_close($ch);
            return false;
        }
        if ($info['http_code'] >= 400)
        {
            curl_close($ch);
            return false;
        }
        curl_close($ch);

        return $pageMemo;
    }

/**
     * get 方式的 socket 连接
     *
     * @param string $url 访问地址
     * @param int $timeout 超时时间
     * @param string &$reason
     * @param string &$status
     * @return string $content for succ, bool false for fail
     */
    public static function sockGet ($url, $timeout, &$reason, &$status)
    {
        $time_begin = time();

        $info = parse_url($url);
        if (! $info["port"])
        {
            $info["port"] = "80";
        }
        /// socket 连接: 3 秒超时,连接上以后的交互才使用 $timeout
        $fp = fsockopen($info["host"], $info["port"], $errno, $errstr, 3);
        if (! $fp)
        {
            return false;
        }
        if (strlen($info["query"]))
        {
            $head = "GET " . $info['path'] . "?" . $info["query"] . " HTTP/1.1\r\n";
        }
        else
        {
            $head = "GET " . $info['path'] . " HTTP/1.0\r\n";
        }

        $head .= "Host: " . $info['host'] . "\r\n";
        $head .= "\r\n";
        $write = fwrite($fp, $head);
        if ($write != strlen($head))
        {
            return false;
        }

        stream_set_timeout($fp, $timeout);
        $result = "";
        $i = 0;

        while (! feof($fp))
        {
            unset($line);
            $line = fread($fp, 4096);
            if ($i == 0)
            {
                list ($protocol, $status, $statuswork) = explode(" ", $line, 3);
                if ($status >= 300)
                {
                    fclose($fp);
                    return false;
                }
                $i = 1;
            }
            $result .= $line;
            $metadata = stream_get_meta_data($fp);
            if ($metadata["timed_out"])
            {
                fclose($fp);
                return false;
            }
        }
        fclose($fp);
        $result = explode("\r\n\r\n", $result, 2);
        $result = $result[1];
        return $result;
    }

    /**
     * post 方式的 socket 连接
     *
     * @param string $base_url 访问地址
     * @param string $query
     * @param int $timeout
     * @param string &$fail_reason
     * @param string &$http_status
     * @return string $content for succ, bool false for fail
     */
    public static function sockPost ($url, $query, $timeout, &$reason, &$status, $cookies = array())
    {
        $time_begin = time();

        $info = parse_url($url);
        $info["port"] = isset($info["port"]) ? $info["port"] : '';
        if (! $info["port"])
        {
            $info["port"] = "80";
        }
        //var_dump($info);
        //exit(0);
        //$info["host"] = "www.chinamobile.com";
        /// socket 连接: 3 秒超时,连接上以后的交互才使用 $timeout
        $fp = fsockopen($info["host"], $info["port"], $errno, $errstr, 13);
        if (! $fp)
        {
            return false;
        }

        $info["query"] = isset($info["query"]) ? $info["query"] : '';
        if (strlen($info["query"]))
        {
            $head = "POST " . $info['path'] . "?" . $info["query"] . " HTTP/1.0\r\n";
        }
        else
        {
            $head = "POST " . $info['path'] . " HTTP/1.0\r\n";
        }
        $head .= "Host: " . $info['host'] . "\r\n";
        $head .= "Referer: http://" . $info['host'] . $info['path'] . "\r\n";
        $head .= "Content-type: application/x-www-form-urlencoded\r\n";
        $head .= "Content-Length: " . strlen(trim($query)) . "\r\n";
        $head .= "\r\n";
        $head .= trim($query);
        $write = fputs($fp, $head);
        if ($write != strlen($head))
        {
            return false;
        }
        stream_set_timeout($fp, $timeout);
        $metadata = stream_get_meta_data($fp);

        $result = "";
        $i = 0;
        while (! feof($fp) && (!$metadata['timed_out']))
        {
            $line = fread($fp, 4096);
            if ($i == 0)
            {
                list ($protocol, $status, $statuswork) = explode(" ", $line, 3);
                if ($status >= 300)
                {
                    fclose($fp);
                    return false;
                }
                $i = 1;
            }
            $result .= $line;
        }
        fclose($fp);
        if ($metadata["timed_out"])
        {
            fclose($fp);
            return false;
         }
         else 
         {
        
	        $result = explode("\r\n\r\n", $result, 2);
	        $result = $result[1];
	
	        return $result;
         }
    }

	
	/**
	 *  socket https 调用接口
	 *  @author guotao1@staff.sina.com.cn
	 */
	
	public function socketSSLPost($url,$query,$timeout,&$reason,&$status)
	{
	    $time_begin = time();
        $info = parse_url($url);        	
        $info["port"] = "443";      
        if ($info['scheme'] != 'https')
        {
        	return false;
        }
        
        $sslUrl = 'ssl://'.$info['host'];
        //var_dump($info);
        //exit(0);
        //$info["host"] = "www.chinamobile.com";
        /// socket 连接: 3 秒超时,连接上以后的交互才使用 $timeout
        $fp = fsockopen($sslUrl, $info["port"], $errno, $errstr, 13);
        if (! $fp)
        {
            return false;
        }

        $info["query"] = isset($info["query"]) ? $info["query"] : '';
        if (strlen($info["query"]))
        {
            $head = "POST " . $info['path'] . "?" . $info["query"] . " HTTP/1.0\r\n";
        }
        else
        {
            $head = "POST " . $info['path'] . " HTTP/1.0\r\n";
        }
        $head .= "Host: " . $info['host'] . "\r\n";
        //$head .= "Referer: http://" . $info['host'] . $info['path'] . "\r\n";
        $head .= "Content-type: application/x-www-form-urlencoded\r\n";
        $head .= "Content-Length: " . strlen(trim($query)) . "\r\n";
        $head .= "\r\n";
        $head .= trim($query);
        $write = fputs($fp, $head);
        if ($write != strlen($head))
        {
            return false;
        }
        stream_set_timeout($fp, $timeout);
        $metadata = stream_get_meta_data($fp);

        $result = "";
        $i = 0;
        while (! feof($fp) && (!$metadata['timed_out']))
        {
            $line = fread($fp, 4096);
            if ($i == 0)
            {
                list ($protocol, $status, $statuswork) = explode(" ", $line, 3);
                if ($status >= 300)
                {
                    fclose($fp);
                    return false;
                }
                $i = 1;
            }
            $result .= $line;
        }
        fclose($fp);
        if ($metadata["timed_out"])
        {
            fclose($fp);
            return false;
         }
         else 
         {
        
	        $result = explode("\r\n\r\n", $result, 2);
	        $result = $result[1];

	        return $result;
         }	
	
	
	}

    /**
     * 获取客户端真实 ip, 考虑 F5, 代理 等情况
     *
     * @return $realip 用户真实ip
     */
    public static function getClientIp ()
    {
        if (isset($_SERVER))
        {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            {
                $realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
            elseif (isset($_SERVER['HTTP_CLIENT_IP']))
            {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            }
            else
            {
                $realip = @$_SERVER['REMOTE_ADDR'];
            }
        }
        else
        {
            if (getenv("HTTP_X_FORWARDED_FOR"))
            {
                $realip = getenv("HTTP_X_FORWARDED_FOR");
            }
            elseif (getenv("HTTP_CLIENT_IP"))
            {
                $realip = getenv("HTTP_CLIENT_IP");
            }
            else
            {
                $realip = getenv("REMOTE_ADDR");
            }
        }
        return $realip;
    }
}