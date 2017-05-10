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
		'host_w' => '10.210.228.41',
		'password_w' => NULL,
		'port_w' => '7000',
		'timeout_w' => 1,
		'host_r' => '10.210.228.41',
		'password_r' => NULL,
		'port_r' => '7000',
		'timeout_r' => 1
);
/* End of file redis.php */
/* Location: ./application/config/redis.php */
