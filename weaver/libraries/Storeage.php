<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* @author guotao1, <guotao1@staff.sina.com.cn>
* @version : Storeage.php 2013-7-18 ����05:16:41 guotao1 
* @copyright  (c) 2012 Sina PAY Team.
* @since		Version 1.0
* @filesource
*/

require_once SYSDIR.'/libraries/SinaStorageService/SinaStorageService.php';


class CI_Storeage {
	
	private $project = 'store.games.sina.com.cn';
	private $access_key = 'SINA000000000000GAME';
	private $secret_key = 'sBunk/Em2phUERNfeFysA34tTP3C/ywfdnSfEziT';
	
	/**
	 * 上传文件到S3
	 * Enter description here ...
	 * @param string $content 文件的流信息
	 * @param string $img_name 图片的相对路径+名字,xxx/xxx.jpeg,xxx.jpg,xxx.gif...
	 * @param string $mine_type 内容的类型
	 * @return true on success, false on failure.
	 */
	public function upload($content,$img_name,$mine_type = 'image/jpg')
	{
		$file_length = strlen($content);
		$file_sha1 = sha1($content);
		$o = SinaStorageService::getInstance($this->project, $this->access_key, $this->secret_key);
		$o->setAuth(true);		
		$rs = $o->uploadFile($img_name,$content, $file_length, $mine_type,$result);
		return $rs;
	}
	
	public function delete($dest_name,&$result)
	{
		$o = SinaStorageService::getInstance($this->project, $this->access_key, $this->secret_key);
		$ret = $o->deleteFile($dest_name,$result);
		return $ret;
	}
	
}

?>