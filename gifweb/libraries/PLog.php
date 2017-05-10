<?php
/**
 * Copyright (c) 2010, 新浪网支付中心
 * All rights reserved.
 *
 * 文件名称：  class.PLog.php
 * 摘    要：  基础日志
 * 作    者：  yifan@staff.sina.com.cn
 * 版    本：  1.0
 * 修改日期:   2010.3.28(代码整理)
 *
 */

/**
 * 现在正在使用的日志类
 *
 * 调用方式：
 * PLog::w_DebugLog($incsowjd, '',);
 * PLog::w_WarnLog($incsowjd, '');
 * PLog::w_DebugLog($incsowjd, '');
 */
class PLog
{
    /**
     * 递归建目录函数
     *
     * @param string $dir 目录
     * @param string $mode
     * @return boolean
     */
    static private function m_MakeDirs ($dir, $mode = 0777)
    {
        if (! is_dir($dir))
        {
            self::m_MakeDirs(dirname($dir), $mode);
            $result = mkdir($dir, $mode);
            $result = chown($dir, "www");
            $result = chgrp($dir, "www");
            return $result;
        }
        return true;
    }

    /**
     * 获取LOG文件名称（带路径）
     *
     * @param string $file 文件名
     * @param string $module 日志类型
     * @return string LOG文件名称（带路径）
     */
    static private function m_GetLogFileName ($file, $module)
    {
        //取module
        if (! isset($module) || is_null($module) || $module == "")
        {
            $m = 'OTHER';
        }
        else
        {
            $module = strtolower($module);
            switch ($module)
            {
                case 'd':
                    {
                        $m = 'DEBUG';
                        break;
                    }
                case 'w':
                    {
                        $m = 'WARING';
                        break;
                    }
                case 'e':
                    {
                        $m = 'ERROR';
                        break;
                    }
                case 'c':
                    {
                        $m = 'CUSTOM';
                        break;
                    }
                default:
                    {
                        $m = 'OTHER';
                        break;
                    }
            }
        }

        //获取时间
        $datestr = date("Ymd");

        //获取IP
        $ipstr = str_replace(".", "_", $_SERVER["SERVER_ADDR"]);
        if (is_null($ipstr) || $ipstr == "")
        {
            $ipstr = "0_0_0_0";
        }
        $logpath = "";
        if (! isset($file) || is_null($file) || $file === "")
        {
            $basename = "unknow_" . $ipstr . "_" . $datestr;
            $logpath = "";
        }
        else
        {
            //获取文件名
            $basename = basename($file, ".php");
            $basename = str_replace(".", "_", $basename);
            if (is_null($basename) || $basename == "")
            {
                $basename = "unknow";
            }
            //$basename = strtolower($basename) . "_" . $m . "_" . $ipstr . "_" . $datestr;//动态平台在做实时抓取日志功能时志刚让我去掉的 
            $basename = strtolower($basename) . "_" . $m .  "_" . $datestr;
            //获取路径
            $path = dirname($file);
            $dirs = explode("/", $path);
            $lcount = count($dirs);
            if ($lcount == 0)
            {
                $logpath = "";
            }
            for ($i = $lcount - 1; $i >= 0; $i --)
            {
                if ($dirs[$i] == "libs")
                {
                    //库
                    $logpath = "libs/" . $logpath;
                    break;
                }
                elseif (strstr($dirs[$i], "sina.com"))
                {
                    //遇到域名
                    $logpath = "www/" . $logpath;
                    break;
                }
                else
                {
//                     $logpath = str_replace(".", "_", $dirs[$i]) . "_" . $logpath;
                	$logpath = "www/" . $logpath;
                	break;
                }
            }
        }

        $logfile = $_SERVER['SINASRV_APPLOGS_DIR'] . $datestr . "/" . $m . "/" . $logpath . $basename . ".log";
        return $logfile;
    }

    /**
     * 获取每一行的Log字符串
     * 每行的分隔符是：\t\t\n
     * Head的分隔符是：空格
     * Head和Log之间的分隔符是：\t
     * Log1和Log2之间的分隔符是：" \t "(注意前后的空格)
     *
     * 如果Log中有回车，回记录两次，其中一次是替换回车的(用于grep)，另外一次是记录的原始信息
     */
    private function m_getLogMemo ($file, $lineNum, $value1, $value2 = '', $func = 'MAIN')
    {
        list ($usec, $sec) = explode(" ", microtime());
        $tstr = date("H:i:s", $sec) . substr($usec, 1, 6);
        $logMemo = $tstr . "\t[$func][LN:$lineNum]";

        $logMemo .= "\t" . session_id();
        
        if (is_string($value1))
        {
            $value1 = trim($value1);
            if (strpos($value1, "\n") !== false) // 包含 \n
            {
                $oldlog1 = $value1;
                $logMemo .= "\t" . strtr($value1, "\n", "\t"); // 替换回车
            }
            else
            {
                $logMemo .= "\t" . $value1;
            }
        }
        else
        {
            $logMemo .= "\t" . serialize($value1);
        }

        if ($value2 != '')
        {
            if (is_string($value2))
            {
                $value2 = trim($value2);
                if (strpos($value2, "\n") !== false) // 包含 \n
                {
                    $oldlog2 = $value2;
                    $logMemo .= " \t " . strtr($value2, "\n", "\t"); // 替换回车
                }
                else
                {
                    $logMemo .= " \t " . $value2;
                }
            }
            else
            {
                $logMemo .= " \t " . serialize($value2);
            }
        }

        $logMemo .= "\t\t"; // 分隔标识


        if (! empty($oldlog1))
        {
            $logMemo .= "\n" . $oldlog1;
        }
        if (! empty($oldlog2))
        {
            $logMemo .= "\n" . $oldlog2;
        }

        return $logMemo;
    }

    /**
     * 写LOG的函数
     *
     * @param string $module 日志类型
     * @param string $file 文件名
     * @param string $lineNum 行
     * @param string $value1
     * @param string $value2
     * @param string $func 函数名
     */
    static private function m_sLog ($module, $file, $lineNum, $value1, $value2 = '', $func = 'MAIN')
    {

    	/*
        if (! empty($GLOBALS['G_NO_LOG'])) // 不记录Log，主要是针对loader.php
        {
            return;
        }

        $ConfLevel = strtolower($GLOBALS['LOG']['LOGLEVEL']);
        if (($ConfLevel == 'w') && ($module == 'd'))
        {
            return;
        }
        elseif (($ConfLevel == 'e') && (($module == 'd') || ($module == 'w')))
        {
            return;
        }
*/
        $logFile = self::m_GetLogFileName($file, $module);
        $logDir = dirname($logFile);

        if (! is_dir($logDir))
        {
            self::m_MakeDirs($logDir);
        }

        //$logMemo = date("Y-m-d H:i:s") . "\t[$func][LN:$lineNum]\t[PID:" . getmypid() . "][" .round(memory_get_usage() / 1024 / 1024, 2) . "M]\t";


        $logMemo = self::m_getLogMemo($file, $lineNum, $value1, $value2, $func);

        PFile::Append($logFile, $logMemo . "\n");
    }

    /**
     * 返回 文件名、行号和函数名
     * @param $skipLevel
     */
    private function getLogInfo ($skipLevel = 1)
    {
        $trace_arr = debug_backtrace();
        for ($i = 0; $i < $skipLevel; $i ++)
        {
            array_shift($trace_arr);
        }

        $tmp_arr1 = array_shift($trace_arr);

        if (! empty($trace_arr))
        {
            $tmp_arr2 = array_shift($trace_arr);
        }
        else
        {
            $tmp_arr2 = array(
                'function' => "MAIN" //主流程 __MAIN__
            );
        }

        if (isset($tmp_arr2['class'])) // 类的方法
        {
            $func = $tmp_arr2['class'] . $tmp_arr2['type'] . $tmp_arr2['function'];
        }
        else
        {
            $func = $tmp_arr2['function'];
        }

        //if ($func == 'require_once') $func = '__INCLUDE__';


        return array(
            'line' => $tmp_arr1['line'] ,
            'file' => $tmp_arr1['file'] ,
            'func' => $func
        );
    }

    /**
     * 替代WarnLog。旧函数参数太多，使用不方便
     *
     * @param string $value1
     * @param string $value2
     */
    static function w_WarnLog ($value1, $value2 = '')
    {
        $arr = self::getLogInfo();
        // var_dump($arr);
        self::w_AllLog($arr, $value1, $value2);
        //self::WarnLog($arr['file'], $arr['line'], $value1, $value2, $arr['func']);
        self::m_sLog('w', $arr['file'], $arr['line'], $value1, $value2, $arr['func']);
    }

    /**
     * 调试日志
     *
     * @param string $value1
     * @param string $value2
     */
    static function w_DebugLog ($value1, $value2 = '')
    {
        $arr = self::getLogInfo();
        self::w_AllLog($arr, $value1, $value2);
        //self::DebugLog($arr['file'], $arr['line'], $value1, $value2, $arr['func']);
        self::m_sLog('d', $arr['file'], $arr['line'], $value1, $value2, $arr['func']);
    }

    /**
     * 错误日志
     *
     * @param string $value1
     * @param string $value2
     */
    static function w_ErrorLog ($value1, $value2 = '')
    {
        $arr = self::getLogInfo();
        self::w_AllLog($arr, $value1, $value2);
        //self::ErrorLog($arr['file'], $arr['line'], $value1, $value2, $arr['func']);
        self::m_sLog('e', $arr['file'], $arr['line'], $value1, $value2, $arr['func']);
    }

    /**
     * 日常日志
     *
     * @param string $value1 日志内容1
     * @param string $value2 日志内容2
     * @param string $logFileName 文件名
     */
    static function w_CustomLog ($value1, $value2 = '', $logFileName = '')
    {
        $arr = self::getLogInfo();
        self::w_AllLog($arr, $value1, $value2);
        self::m_sLog('c', $arr['file'], $arr['line'], $value1, $value2, $arr['func'], $logFileName);
    }

    /**
     * 把所有的Log记录到 /tmp/debug.log中，便于调试程序
     *
     * @param array $logInfo 日志信息数组
     */
    static function w_AllLog ($logInfo)
    {
        static $script_flag = true;

        if (! empty($GLOBALS['G_NO_LOG'])) // 不记录Log，主要是针对loader.php
        {
            return;
        }

        //if (SINAPAY_ROLE === 'SINAPAY_WEB_CODER')
        if ((ENVIRONMENT !== 'production') || (@$_SESSION['write_debug'] == true))
        {

            $func = $logInfo['func'];
            if (strtolower(substr($func, 0, 6)) == 'plog::')
            {
                return;
            }

            $file = $logInfo['file'];
            $dirs = explode("/", $file);
            $path = implode('/', array_slice($dirs, - 3));

            //$str = '------------ ' . $func . "    " . $logInfo['line'] . "    " . $path . " ----------\n";
            $str = '-- ' . $func . "    " . $logInfo['line'] . "    " . $path . " \n";
            $arg_list = func_get_args();
            $num = func_num_args();
            for ($i = 1; $i < $num; $i ++)
            {
                $v = $arg_list[$i];
                $str .= '  ';
                if (is_array($v))
                {
                    $str .= var_export($v, true) . "\n";
                }
                elseif (is_string($v) || is_numeric($v))
                {
                    $str .= $v . "\t";
                }
            }

            $ip = getClientIp();
            $logfile = $_SERVER['SINASRV_APPLOGS_DIR']."/phpdebug_{$ip}.log"; //按IP保存文件，便于调试查看
            if (file_exists($logfile) && (filemtime($logfile) < time() - 4 * 86400)) // 每天删除一次
            {
                unlink($logfile);
            }

            $fp = @fopen($logfile, "a+");
            if ($fp)
            {
                if ($script_flag)
                {

                    list ($usec, $sec) = explode(" ", microtime());
                    $start_str = "=========================== SCRTIPT_START =================================\n ";
                    $start_str .= empty($_SERVER['REQUEST_URI']) ? $_SERVER['SCRIPT_NAME'] : $_SERVER['REQUEST_URI'] . "\n ";
                    $start_str .= date("Y-m-d H:i:s") . "." . substr($usec, 2, 4) . "\n";
                    $start_str .= "===========================================================================";
                    @fwrite($fp, $start_str . "\n\n");
                    $script_flag = false;
                }
                @fwrite($fp, $str . "\n\n");
                @fclose($fp);
            }
        }
    }
}
// 初始化的Log
function getClientIp ()
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
	$ips = explode (", ", $realip);
	if( $ips[0] == "unknown"){
		$ips[0] = "0.0.0.0";
	}
	return $ips[0];
}

