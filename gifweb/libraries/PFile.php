<?php
/**
 * Copyright (c) 2010, 新浪网支付中心
 * All rights reserved.
 *
 * 文件名称：  class.PFile.php
 * 摘    要：  文件操作类
 * 作    者：  yifan@staff.sina.com.cn
 * 版    本：  1.0
 * 修改日期:   2010.3.28(代码整理)
 *
 */
/**
 * 文件操作类
 */
class PFile
{
    /**
     * 读取文件
     *
     * @param string $fname 文件地址指针
     * @return string $str  文件内容的一个字符串
     */
    public function Read ($fname)
    {
        $str = file_get_contents($fname);
        if (false === $str)
        {
            trigger_error("file_get_contents 错误 $fname", E_USER_WARNING);
        }
        return $str;
    }

    /**
     * 以覆盖的方式写数据到文件
     * @param	string	$fname		文件名以及文件所在目录的指针
     * @param	string	$content	需要写入的内容
     * @return  参考函数 m_write_file
     */
    public function Write ($fname, $content)
    {
        return self::m_write_file($fname, $content, 'w');
    }

    /**
     * 以追加的方式写数据到文件
     * @param	string	$fname		文件名以及文件所在目录的指针
     * @param	string	$content	需要写入的内容
     * @return  参考函数 m_write_file
     */
    static public function Append ($fname, $content)
    {
        return self::m_write_file($fname, $content, 'a');
    }

    /**
     * 写数据到文件
     * @param	string	$fname		文件名以及文件所在目录的指针
     * @param	string	$content	需要写入的内容
     * @param	string	$mod		写入文件的方式
     * @return  成功 返回写入的字符数 或false
     */
    static private function m_write_file ($fname, $content, $mod)
    {
        $fp = fopen($fname, $mod);
        if ($fp)
        {
            $r = fwrite($fp, $content);
            fclose($fp);
            return $r;
        }
        else
        {
            //				PError::SetErrorCode(6142, 'fopen 错误');
            trigger_error("fopen 错误 $fname", E_USER_WARNING);
            return false;
        }
    }
}

?>
