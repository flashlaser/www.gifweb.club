<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

/**
 *
 * null
 *
 * @name Index
 * @author liule1
 *         @date 2015-1-23
 *        
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 *           
 * @property task_model $task_model
 * @property tag_model $tag_model
 * @property product_model $product_model
 */
class Index extends MY_Controller {
	public function __construct() {
		parent::__construct ();
	}
	public function get_game_list() {
		echo '{
    "result": 200,
    "message": "success",
    "data": {
        "recommendList": [
            {
                "absId": "3456789ngh",
                "abstitle": "测试游戏",
                "absImage": "http://www.sinaimg.cn/dy/slidenews/21_img/2014_26/1197_3430868_762554.jpg",
                "packageURL": ["com.sina.sinagame"]
            }
        ],
        "attentionedList": [
            {
                "absId": "3456789ngh",
                "abstitle": "水果忍者",
                "absImage": "http://www.sinaimg.cn/dy/slidenews/21_img/2014_26/1197_3430868_762554.jpg",
                "packageURL": ["wx4e52b31c06d6c2ce"]
            }
        ],
        "normalList": [
            {
                "absId": "3412789ngh",
                "abstitle": "刀塔传奇",
                "absImage": "http://www.sinaimg.cn/dy/slidenews/21_img/2014_26/1197_3430868_762554.jpg",
                "attentionCount": 122,
                "packageURL": ["com.sina.sinagame"]
            }
        ]
    }
}';
	}
}

