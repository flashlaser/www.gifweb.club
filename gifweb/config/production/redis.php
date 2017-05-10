<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| REDIS CONFIG
| -------------------------------------------------------------------
|
*/
/*
$config['master'] = array(
			'host'	=> $_SERVER['SINASRV_REDIS_HOST'],
			'port'		=> $_SERVER['SINASRV_REDIS_PORT'],
			'timeout'	=> 1
			);

$config['slave'] = array(
			'host'	=> $_SERVER['SINASRV_REDIS_HOST_R'],
			'port'		=> $_SERVER['SINASRV_REDIS_PORT_R'],
			'timeout'	=> 1
			);
*/

$config ['redis'] = array (
		'host_w' => '7497a77db2fb45a1.m.cnbja.kvstore.aliyuncs.com',
		'port_w' => '6379',
		'password_w' => 'glgame123QWE',
		'timeout_w' => 1,
		'host_r' => '7497a77db2fb45a1.m.cnbja.kvstore.aliyuncs.com',
		'port_r' => '6379',
		'password_r' => 'glgame123QWE',
		'timeout_r' => 1
);

/* End of file redis.php */
/* Location: ./application/config/redis.php */
