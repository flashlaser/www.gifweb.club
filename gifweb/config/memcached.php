<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| MEMCACHED CONFIG
| -------------------------------------------------------------------
|
*/

$mc_servers = explode(' ',$_SERVER['SINASRV_MEMCACHED_SERVERS']);

$config = array();
foreach($mc_servers as $key => $value)
{
	$conf = explode(':',$value);
	$config['server_' . $key] = array(
			'hostname'	=> $conf[0],
			'port'		=> $conf[1],
			'weight'	=> 1
	);
}

// $config['server_1'] = array(
// 			'hostname'	=> '127.0.0.1',
// 			'port'		=> 7600,
// 			'weight'	=> 1
// 			);
// $config['server_2'] = array(
// 			'hostname'	=> '10.73.48.23',
// 			'port'		=> 7601,
// 			'weight'	=> 1
// 			);
// $config['server_3'] = array(
// 			'hostname'	=> '10.73.48.24',
// 			'port'		=> 7601,
// 			'weight'	=> 1
// 			);
// $config['server_4'] = array(
// 			'hostname'	=> '10.73.48.25',
// 			'port'		=> 7601,
// 			'weight'	=> 1
// 			);
// $config['server_5'] = array(
// 			'hostname'	=> '10.73.48.26',
// 			'port'		=> 7601,
// 			'weight'	=> 1
// 			);
// $config['server_6'] = array(
// 			'hostname'	=> '10.73.48.27',
// 			'port'		=> 7601,
// 			'weight'	=> 1
// 			);
// $config['server_7'] = array(
// 			'hostname'	=> '10.73.48.28',
// 			'port'		=> 7601,
// 			'weight'	=> 1
// 			);
// $config['server_8'] = array(
// 			'hostname'	=> '10.73.48.29',
// 			'port'		=> 7601,
// 			'weight'	=> 1
// 			);



/* End of file memcached.php */
/* Location: ./api/config/memcached.php */