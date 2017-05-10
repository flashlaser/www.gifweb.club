<?php
// 业务线配置数组
// 最后线路，不能作为第一次线路 所有类名，方法名务必小写
$config = array (
	'reset_safe_phone' => array(
		array(
			'ring' => 'user:getvcode',
			'required' => 0,
		),
		array(
			'ring' => 'user:check_phone_code_match',
			'required' => 1,
		),
		array(
			'ring' => 'user:getvcode',
			'required' => 0,
		),
		array(
			'ring' => 'user:check_phone_code_match',
			'required' => 1,
		),
		array(
			'ring' => 'user:set_phone_gpw',
			'required' => 1,
			'params' => array(
				'action' => 1
			)
		),
	),
	
	'first_set_gpw_phone' => array(	// 第一次设置手机打赏密码线路
		array(
			'ring' => 'user:getvcode',
			'required' => 0,
		),
		array(
			'ring' => 'user:check_phone_code_match',
			'required' => 1,
		),
		array(
			'ring' => 'user:set_phone_gpw',
			'required' => 1,
			'params' => array(
				'action' => 0
			)
		), 
    ),
	
	'reset_gpw' => array(
		array(
			'ring' => 'user:getvcode',
			'required' => 1,
		),
		array(
			'ring' => 'user:set_phone_gpw',
			'required' => 1,
			'params' => array(
				'action' => 0
			)
		), 
    ),

    
)
;
