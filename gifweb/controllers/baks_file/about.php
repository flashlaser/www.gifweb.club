<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

/**
 * 
 * @name About
 * @desc null
 *
 * @author	 liule1
 * @date 2015年10月19日
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 */
class About extends CI_Controller {
	public function __construct() {
		parent::__construct ();
	}
	public function index() {
		$data['mark'] = 1;
		$this->smarty->view ( 'about/index.tpl', $data );
	}
	
	
	public function join_us() {
		$data['mark'] = 2;
		$this->smarty->view ( 'about/zhaopin.tpl', $data );
	}
	
	public function test() {
        $data = <<<EOF
{"result":-100,"data":{"ll":{"result":"fail","errorcode":"0103","msg":"invalid parameter or invalid sign","ll":"format=json&version=2.0.1&timestamp=1459491380&partner_id=1000000015&item_id=20572&uid=1251142132&name=\u54bf\u5440\u54bf\u5440\u54e6&ip=61.135.152.194&postInfos={\"action\":\"fetchGift\",\"uid\":\"1251142132\",\"token\":\"2.00oNff3BbPn2YC16e6f860c9W4ELhD\",\"name\":\"\\u54bf\\u5440\\u54bf\\u5440\\u54e6\",\"userIp\":\"10.209.18.89\",\"giftId\":\"20572\",\"code\":\"\",\"timestamp\":\"1459491379\",\"version\":\"5.0.0\",\"platform\":\"ios\",\"sys_version\":\"iPhone OS 8.4\",\"partner_id\":\"10002\",\"sign\":\"21aa89502fe86626659569de30a9ce67\",\"BX-WEB3-G0\":\"195becba76f2678e4eb5884b34ae3774\",\"_ga\":\"GA1.3.1981661210.1459478483\",\"ALM\":\"1459478283\",\"Apache\":\"61.135.152.218_1459479818.738722\",\"SINAGLOBAL\":\"61.135.152.218_1459479818.738717\",\"SUB\":\"_2A257-a-ADeTxGedM7lMQ9CzNyD6IHXVZVNvIrDV_PUJbu9APLRLYkWRLHeuI4Aun6B57yclLZ5dmWMviYsTFIw..\",\"SUBP\":\"0033WrSXqPxfM725Ws9jqgMF55529P9D9W5LuqCxbHJya8x.eX14xwLK5JpX5Kzt\",\"SUE\":\"es=49c23d9a6b4323bde1b5dfd80c0dc1b0&ev=v1&es2=9cfbbc68c36280eba2ba5b9278cdd5ae&rs0=e%2B7Rg6ekalAtS0HueJAtyrLg4ibObAQj8ib0rR9PML2U%2B5uEdukHa2mWPBM1f7uL3OlSRsGm34MaY2PqS8hzYehQRVKP9v2Iq7sbAz5sfZerKzqlrIKpQunB8gxpRYjT9ti6xsTKdowg0er7VXZc1RKzzMhRmcCsZZduDZASy7Y%3D&rv=0\",\"SUP\":\"cv=1&bt=1459478480&et=1459564880&d=40c3&i=c4df&us=1&vf=0&vt=0&ac=2&st=0&lt=2&uid=1251142132&user=zjc_1008&ag=2&name=zjc_1008%40sina.com&nick=%E8%8D%89%E6%88%92%E6%8C%87&sex=2&ps=0&email=zjc_1008%40sina.com&dob=1988-04-05&ln=&os=&fmp=&lcp=2015-06-15%2010%3A28%3A28\",\"SUS\":\"SID-1251142132-1459478480-XD-8k0u9-f9ec28bcbcf68c460f94df863120c4df\",\"U_TRS1\":\"000000da.b3e03860.56fddf0b.29e2528e\",\"U_TRS2\":\"000000da.b3ea3860.56fddf0b.98be3916\"}&sign=948f6c6629748f1be393faac48b4ff7a"}},"message":"invalid parameter or invalid sign"}
EOF;
        $data = json_decode($data , true);
        $json = $data['data']['ll']['ll'];
		
		
		$data['post'] = json_encode($json['postInfos']);
        $md5_code = $this->md5Code($data, $time);
			
        
        $destURL = "http://gl.97973.com/about/test1";
        // $post = $normalParameter."&item_id=".$data['item_id']."&uid=".$data['uid']."&name=".$data['name']."&ip=".$data['ip']."&postInfos=".$data['post']."&sign=".$md5_code;
        echo Util::curl_get_contents($destURL, $post, 'post');
    }
    
    public function test1() {
        var_dump($_POST);
    }
}
