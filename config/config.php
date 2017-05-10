<?php
	if ( ! defined('WEAVER')) exit('No direct script access allowed');

	$siteconfig = array();
	$siteconfig[$_SERVER['SERVER_ADDR']] = 'gifweb';
	$siteconfig['www.gifweb.club'] = "gifweb";
	$siteconfig['gifweb.club'] = "gifweb";
	//$siteconfig['47.93.101.242'] = "gifweb";

	/**
	 * 
	 * 如果想保留旧的请求方式如：http://host/xxx/xx.php的请求方式
	 * 请在数组中添加配置
	 * eg. $filterconfig = array('/','test.php','test1.php');
	 * @array filterconfig
	 */	
	$filterconfig = array();
	
