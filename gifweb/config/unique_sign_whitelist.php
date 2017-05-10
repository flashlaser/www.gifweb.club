<?php
// 一些不验证SIGN重复性的接口， 不区分大小写
// class name : game_list
$config = array (
	'home' => array(
        'game_list',
    ),
	'device' => array(
		'add',
	),
	'cf' => array(
		'version',
		'setting',
		'swift',
		
	),
	'gl' => array(
		'juhe_page',
		'list_info',
		'detail_info',
		'video_cms_list',
		
	),
	'comment' => array(
		'get_list',
	),
	'user' => array(
		'islogin',
		'getuserinfo',
		'collect_answers',
		'my_answer',
		'my_question',
		'attention_questions', 
		'get_message',
		'question_recommend_list',
		'read_message',
		'other_question',
		'other_user_info',
		'other_answer',
		'other_attention_questions',
		'other_collect_answers',
		'other_collect_gl',
		'bg_img_list',
		'my_attention_user',
		'other_attention_user',
		'my_fans',
		'other_fans',
		'get_safe_phone',
	),
	'game' => array(
		'game_prefecture_info',
		
	),
	'qa' => array(
		'question_info',
		'answer_info',
	),
	'order' => array(
		'income_list',
		'pay_list',
		'withdraw_list',
		'areward_list_by_answer',
		'get_order_pay_status',
	)
	
)
;
