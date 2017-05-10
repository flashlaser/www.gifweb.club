<?php
/**
 * @version gift_model.php
 * @author wangbo8
 * @date 2016年6月1日
 * 
 * 礼包信息处理model
 */
class Gift_model extends MY_Model {
	private $_cache_key_pre = ''; //缓存前缀
	private $_cache_expire = 600; //缓存时间
	protected  $_table = 'gl_user_gift'; //当前操作表

	private $_partner_id_ios = '1000000015'; //partner id
	private $_partner_id_android = '1000000016'; //partner id
	private $_partner_key_ios = '7fa789d45035940d0867c85ff50f85db'; //partner key
	private $_partner_key_android = 'a70d985f9b519790637497c37ca0e5ec'; //partner key
	private $_format = 'json';
	private $_version = '2.0.1';

	private $_access_token = "";

	private $_ka_outtype = '';
	private $_ka_version = '';
	private $_ka_timestamp = '';
	private $_ka_partner_id = '';
	private $_ka_sign = '';
	private $_ka_partner_key = '';

	//构造函数
	function __construct() {
		//引用父类构造
		parent::__construct ();
		$this->_cache_key_pre = "gl_app:" . ENVIRONMENT . ":gift:";
		define("_SINA_APP_KEY_",1372825881);
		$this->load->driver ( 'cache' );
		$this->get_signlist();
	}

	//新手卡数字签名使用方法
	private function get_signlist(){
		//定义各参数
		$this->_ka_outtype = "json";
		$this->_ka_version = "v1.0.0.0";
		$this->_ka_timestamp = time();
		$this->_ka_partner_id = "1000000024";
		$this->_ka_partner_key = "5848dff7b6207871b72bf05663182730";
	}

	//领卡限制总方法
	public function check_get_gift_limit($giftId, $origintype, $guid, $ip, $name, $_newcardInfo){
		//调用方法限制发卡
		//Util::echo_format_return(-100, array('ll' =>'123123'), "领取失败，请稍后再试");

		//初始化返回结果数组
		$return_arr = array(
				'res' => true,
				'code' => '0'
			);

		//当前礼包小时限制
		$check_limit_per_hour = $this->check_limit_per_hour($_newcardInfo);

		if(!$check_limit_per_hour){
			$return_arr[res] = false;
			$return_arr[code] = '1';

			return $return_arr;
		}

		//当前礼包时间限制
		$check_time_limit = $this->check_time_limit($_newcardInfo);

		if(!$check_time_limit){
			$return_arr[res] = false;
			$return_arr[code] = '2';

			return $return_arr;
		}

		return $return_arr;
	}

	//时间限制
	private function check_time_limit($_newcardInfo){
		//获取当前时间
		$now_time = time();

		$start = strtotime($_newcardInfo['valid_date']['start']);
		$end = strtotime($_newcardInfo['valid_date']['end']);

		//获取定义卡的开始时间与结束时间
		if($start > 0){
			if($now_time < $start){
				return false;
			}
		}

		if($end > 0){
			if($now_time > $end){
				return false;
			}
		}

		return true;
	}

	//按小时限制
	private function check_limit_per_hour($_newcardInfo){
		//判断当前是否不为零，为0不限制
		if($_newcardInfo['perhour_max'] > 0){
			//获取当前小时作为key
			$nowdate = date('YmdH');

			//当前有限制，拼装key
			$cache_key = $this->_cache_key_pre . "perhour_max_limit:" . $nowdate;

			//拼装哈希
			$hash_key = "giftId" . $_newcardInfo['item_id'];

			//获取缓存中数据
			$data = $this->cache->redis->hGet($cache_key, $hash_key);
			$data = $data ? $data : 0;

			//判断
			if($data >= $_newcardInfo['perhour_max']){
				return false;
			}

			//当前数+1
			$data++;

			$expire_time = mktime(date('H')+1,0,0);
			$this->cache->redis->hSet($cache_key, $hash_key, $data);
			$this->cache->redis->expireAt($cache_key, $expire_time);
		}

		return true;
	}


	public function getNewCardListByGameId($gameId, $page, $count, $uid, $max_id){
		//拼装缓存key
		$cache_key = $this->_cache_key_pre . "gl:NewCardList:" . $gameId;

		//拼装哈希key
		$hash_key = "feature:$page:$count:$max_id";

		//获取数据
		$data = $this->cache->redis->hGet($cache_key, $hash_key);
		$data && $data = json_decode($data, true);

		$data = false; // 跟老接口一致，保持数据实时性

		//判断是否有数据
	    if (!is_array($data)) {
	    	$data = $this->getNewCardList_from_api($gameId, $page, $count, $uid);

	    	//循环遍历放置id
	        if ($max_id) {
	            // 发现 max_id 则舍去之前的
	            $t = -1;
	            foreach ($data as $k => $v) {
	                if ($v['id'] == $max_id) {
	                    $t = $k;
	                    break;
	                }
	            }
	            if ($t > -1) {
	                $data = array_slice($data, $t + 1);
	            }
	        }

	    	if(is_array($data) && count($data) > 0){
		    	//返回指定数据
		    	$data = array_slice($data, 0, $count);
	    	}

	    	//将串行化后的数据保存入缓存
	    	$this->cache->redis->hSet($cache_key, $hash_key, json_encode($data));

	    	//定义保存时间
	    	$this->cache->redis->expire($cache_key, $this->_cache_expire);
	    }

	    return $data;
	}

	//通过api获取
	public function getNewCardList_from_api($gameId, $page, $count, $uid){
		//定义最大重复请求次数
		$repeat = 3;

		//初始化原始数据
		$data = array();

		//获取
		while(!$return_data && $repeat-- > 0){

			//拼装加密后url
			$post_url_arr = array();
			$post_url_arr['gid'] = $gameId;
			$post_url_arr['page'] = $page;
			$post_url_arr['pagesize'] = $count;

			$post_url_arr['outtype'] = $this->_ka_outtype;
			$post_url_arr['version'] = $this->_ka_version;
			$post_url_arr['timestamp'] = $this->_ka_timestamp;
			$post_url_arr['partner_id'] = $this->_ka_partner_id;
			//$post_url_arr['partner_key'] = $this->_ka_partner_key;

			ksort($post_url_arr);
			$sign = implode('', $post_url_arr) . $this->_ka_partner_key;
			$sign = md5($sign);
			$strtoken = "&outtype={$this->_ka_outtype}&version={$this->_ka_version}&timestamp={$this->_ka_timestamp}&partner_id={$this->_ka_partner_id}&sign={$sign}";

			$destURL = "http://ka.sina.com.cn/newapi/glappapi/getGiftList?gid=".$gameId."&page=".$page."&pagesize=".$count . "$strtoken";

	    	//获取数据
	    	$json_data = Util::curl_get_contents($destURL);

	    	//反json
	    	$json_data = json_decode($json_data, true);
	    	$return_data = $json_data['data'];
		}

		//返回结果
		if(is_array($return_data) && !empty($return_data)){
			return $return_data;
		}else{
			return array();
		}
	}

	//检查当前用户是否领卡
	/*
	 *@TO DO
	*/
	public function check_get_gift($uid, $item_id){
		//拼装
		$cache_key = $this->_cache_key_pre . "check_get_gift:" . $uid;
		//拼装哈希key
		$hash_key = "item_id:$item_id";

		//获取数据
		$data = $this->cache->redis->hGet($cache_key, $hash_key);
		$data && $data = json_decode($data, true);

		if($data === false){
			//入库查询
			$sql = "select * from gl_user_gift where uid='$uid' and item_id='$item_id'";
			//执行获取
			$rs = $this->db->query_read($sql);
			$data = $rs->row_array();

	    	//将串行化后的数据保存入缓存
	    	$this->cache->redis->hSet($cache_key, $hash_key, json_encode($data));

	    	//定义保存时间
	    	$this->cache->redis->expire($cache_key, $this->_cache_expire);
		}

		return empty($data) ? false : true;
	}

	/*
	//通过ip限制规定时间内不得淘号
	public function check_ip_time_limit($ip){
		//通过ip拼装key获取数据，

	}*/

	public function getNewCardDetailInfo($giftId, $origintype = 0) {
		//拼装
		$cache_key = $this->_cache_key_pre . "NewCardDetail:" . $giftId . ":origintype:" . $origintype;

		//获取数据
		$data = $this->cache->redis->get($cache_key);

		$data && $data = json_decode($data, true);
		$data = false;    //跟新浪游戏一直，禁用缓存
		if($data === false){
			$data=$this->getNewCardDetailInfoFromApi($giftId, $origintype);

			//数据入缓存
			$this->cache->redis->set($cache_key, json_encode($data), $this->_cache_expire);
			//echo 'from api';exit;
		}else{
			//echo 'from cache';exit;
		}

	  	return $data;
	}

	//从接口获取游戏详情
    private function getNewCardDetailInfoFromApi($giftId, $origintype=0) {
		//拼装加密后url
		$post_url_arr = array();
		$post_url_arr['itemId'] = $giftId;

		$post_url_arr['outtype'] = $this->_ka_outtype;
		$post_url_arr['version'] = $this->_ka_version;
		$post_url_arr['timestamp'] = $this->_ka_timestamp;
		$post_url_arr['partner_id'] = $this->_ka_partner_id;

		ksort($post_url_arr);
		$sign = implode('', $post_url_arr) . $this->_ka_partner_key;
		$sign = md5($sign);
		$strtoken = "&outtype={$this->_ka_outtype}&version={$this->_ka_version}&timestamp={$this->_ka_timestamp}&partner_id={$this->_ka_partner_id}&sign={$sign}";

		$destURL = "http://ka.sina.com.cn/newapi/glappapi/getGiftInfoById?itemId=".$giftId . "$strtoken";
        $json_data=Util::curl_get_contents($destURL);
        $returnData=json_decode($json_data,true);
        return $returnData['data'];
    }

	//获取当前礼包的卡总量与剩余量
	public function get_card_count($giftId){
		//拼装
		$cache_key = $this->_cache_key_pre . "get_card_count:" . $giftId;

		//获取
		$data = $this->cache->redis->get($cache_key);
		$data && $data = json_decode($data, true);

		if($data === false){
			//接口获取数量
			$card_info = $this->getNewCardDetailInfoFromApi($giftId, $origintype);

			if(!empty($card_info)){
				$data = array(
						'card_amount' => $card_info['card_amount'],
						'card_left' => $card_info['card_left'],
					);
			}else{
				$data = array(
						'card_amount' => 0,
						'card_left' => 0,
					);
			}

			//数据入缓存
			$this->cache->redis->set($cache_key, json_encode($data), $this->_cache_expire);
		}

		return $data;
	}

	//设置缓存中礼包数量信息
	public function set_card_count_in_cache($giftId, $card_amount, $card_left){
		//封装当前数据
		$data = array(
				'card_amount' => $card_amount,
				'card_left' => $card_left,
			);

		//拼装
		$cache_key = $this->_cache_key_pre . "get_card_count:" . $giftId;

		//数据入缓存
		$this->cache->redis->set($cache_key, json_encode($data), $this->_cache_expire);
	}


	//获取用户礼包兑换码信息
	public function getNewCardCodeInfo($giftId, $uid) {
		//拼装
		$cache_key = $this->_cache_key_pre . "getNewCardCodeInfo:" . $giftId;

		//拼装哈希key
		$hash_key = "uid:$uid";

		//获取数据
		$data = $this->cache->redis->hGet($cache_key, $hash_key);
		$data && $data = json_decode($data, true);

		if($data === false){
			//入库获取信息
			$data = $this->getNewCardCodeInfoFromDb($giftId, $uid);

	    	//将串行化后的数据保存入缓存
	    	$this->cache->redis->hSet($cache_key, $hash_key, json_encode($data));

	    	//定义保存时间
	    	$this->cache->redis->expire($cache_key, $this->_cache_expire);
		}

		return $data;
	}

	private function getNewCardCodeInfoFromDb($giftId, $uid) {
		$sql = "select * from gl_user_gift where item_id='$giftId' and uid='$uid'";
		$rs  = $this->db->query_read($sql);
		$res = $rs->row_array();
		return $res;
	}

	//获取旧卡列表，用来淘号
	public function getOldcard($giftId, $origintype = 0){
		$returnInfo = $this->getOldCardFromApi($giftId, $origintype);
		return $returnInfo;
	}

	//入库随机获取号码
	private function getOldCardFromApi($giftId, $origintype = 0){
		//拼装加密后url
		$post_url_arr = array();
		$post_url_arr['itemId'] = $giftId;

		$post_url_arr['outtype'] = $this->_ka_outtype;
		$post_url_arr['version'] = $this->_ka_version;
		$post_url_arr['timestamp'] = $this->_ka_timestamp;
		$post_url_arr['partner_id'] = $this->_ka_partner_id;

		ksort($post_url_arr);
		$sign = implode('', $post_url_arr) . $this->_ka_partner_key;
		$sign = md5($sign);
		$strtoken = "&outtype={$this->_ka_outtype}&version={$this->_ka_version}&timestamp={$this->_ka_timestamp}&partner_id={$this->_ka_partner_id}&sign={$sign}";

		$destURL = "http://ka.sina.com.cn/newapi/glappapi/taohao?itemId=".$giftId. "$strtoken";
		$json_data=Util::curl_get_contents($destURL);
		$returnData=json_decode($json_data,true);
		if ($returnData['result'] == 'fail') {
			return array();
		} else {
			return $returnData['data'];
		}
	}

	//领卡接口调用
  	//领卡 post
  	public function fetchNewCard($giftId, $uid, $name) {
		//拼装加密后url
		$post_url_arr = array();
		$post_url_arr['itemId'] = $giftId;

		$post_url_arr['outtype'] = $this->_ka_outtype;
		$post_url_arr['version'] = $this->_ka_version;
		$post_url_arr['timestamp'] = $this->_ka_timestamp;
		$post_url_arr['partner_id'] = $this->_ka_partner_id;

		ksort($post_url_arr);
		$sign = implode('', $post_url_arr) . $this->_ka_partner_key;
		$sign = md5($sign);
		$strtoken = "&outtype={$this->_ka_outtype}&version={$this->_ka_version}&timestamp={$this->_ka_timestamp}&partner_id={$this->_ka_partner_id}&sign={$sign}";

		$destURL = "http://ka.sina.com.cn/newapi/glappapi/getCard?itemId=" . $giftId . "$strtoken";
		$json_data=Util::curl_get_contents($destURL, $post, 'post');
		$returnData=json_decode($json_data,true);

		$this->_clear_user_cache($uid, $giftId);
		return $returnData ;
	}

  	//删除卡包数据
  	public function delCard($uid, $card_id, $item_id){
  		if(!$uid || !$card_id || !$item_id){
  			return false;
  		}

  		//拼装sql语句
  		$sql = "delete from gl_user_gift where uid='$uid' and id='$card_id' and item_id='$item_id'";
		$rs  = $this->db->query_write($sql);

		//删除缓存
		$this->_clear_user_cache($uid);

		return mysql_affected_rows($this->db->conn_write);;
  	}

  	public function _clear_user_cache($uid, $giftId = ""){
  		//领卡后删除用户卡箱缓存
  		$cache_key = $this->_cache_key_pre . "gl:getUserCard:" . $uid;
  		$this->cache->redis->delete($cache_key);

  		//领卡后删除用户是否领卡信息缓存
  		$cache_key = $this->_cache_key_pre . "check_get_gift:" . $uid;
  		$this->cache->redis->delete($cache_key);

		//领卡后删除礼包信息中对应用户卡信息
  		if($giftId){
			$cache_key = $this->_cache_key_pre . "getNewCardCodeInfo:" . $giftId;
			$hash_key = "uid:$uid";

			$this->cache->redis->hDel($cache_key, $hash_key);
  		}
  	}


	//领卡数据入库
	public function insert_getcard_info($data){
		//拼装数据,产生sql语句
		$sql = $this->insert($data);
		$rs  = $this->db->query_write($sql);
		return @mysql_insert_id ( $this->db->conn_write );

		//$res = $this->commol_model->execute_by_sql($sql);
		//return $this->commol_model->insert_id();
	}

  	//通过游戏id获取当前游戏相关礼包总数
  	public function getGiftCardNum_bygameId($gameId){
  		//拼装缓存key
  		$cache_key = $this->_cache_key_pre . "CardListByGameId:gameId:$gameId";
		$data = $this->cache->redis->get ( $cache_key );
		$data && $data = json_decode($data, 1);

		$data = false;
		if ($data === false) {
			//调用接口获取数据
  			$cardList = array();
  			$page = 1;
  			$count = 50; //获取数量设置为50
  			$flag = 1;
  			while($flag) {
				$_tmpCardList = $this->getNewCardListByGameId($gameId, $page, $count, 0,0);
				$_tmpCardList = $_tmpCardList['list'];
				if (!empty($_tmpCardList)) {
								$cardList = array_merge($cardList, $_tmpCardList);
								$page++;
								if (count($_tmpCardList) < 50) {
											$flag = 0;
								}
				} else {
								$flag = 0;
				}
  			}

  			$data = $cardList;
			$this->cache->redis->set($cache_key, json_encode($data), $this->_cache_expire);
		}
		return $data;
  	}

  	//调取老接口获取游戏数据
	private function getNewCardListByGameIdFromApi($gid, $page, $count, $uid) {
		$time = time();
		$data = array();
		$data['gid'] = $gid;
		$data['page'] = $page;
		$data['size'] = $count;
		$data['uid'] = $uid;
		$md5_code = $this->md5Code($data, $time);
		$normalParameter = $this->implodeParameter($time);

		$destURL = "http://api.g.sina.com.cn/ka/api/item/list_item?".$normalParameter."&gid=".$data['gid']."&page=".$data['page']."&size=".$data['size']."&uid=".$data['uid']."&sign=".$md5_code;
		//echo $destURL;exit;
		$json_data=Util::curl_get_contents($destURL);
		$returnData=json_decode($json_data,true);
		if ($returnData['result'] == 'fail') {
			return array();
		} else {
			return $returnData['data'];
		}
	}

  	//获取当前用户领取的礼包
  	public function getUserCard($uid, $page, $count){
		//拼装缓存key
		$cache_key = $this->_cache_key_pre . "gl:getUserCard:" . $uid;

		//拼装哈希key
		$hash_key = "feature:$page:$count";

		//获取数据
		$data = $this->cache->redis->hGet($cache_key, $hash_key);
		$data && $data = json_decode($data, true);

		if($data === false){
			//暂时直接从数据库中读取
			$data = $this->getUserCardFromDb($uid, $page, $count);

	    	//将串行化后的数据保存入缓存
	    	$this->cache->redis->hSet($cache_key, $hash_key, json_encode($data));

	    	//定义保存时间
	    	$this->cache->redis->expire($cache_key, $this->_cache_expire);
		}


		return $data;
  	}

	private function getUserCardFromDb($uid, $page, $count){
		//拼装sql
		$limit = ($page - 1) * $count . "," . $count;
		$sql = "select * from gl_user_gift where uid='$uid' order by id desc limit $limit";
		$rs  = $this->db->query_read($sql);
		$res = $rs->result_array();
		return $res;
	}

	public function pregContent($_content){
        if(empty($_content)){
                  return array(
                      'content' => "",
                      'attribute' => array(),
                  );
        }
        //print_r($_content);exit;
        $pregDataImageArray=$this->pregNewsImage($_content); // 匹配图片
        
        $pregDataNewsVideoArray=$this->pregNewsVideos($pregDataImageArray["content"]);//匹配视频
        $pregDataNewsSlideArray=$this->pregNewsSlides($pregDataNewsVideoArray["content"]);
        $returnContents["content"]= str_replace('　',' ',$pregDataNewsSlideArray["content"]);//编辑经常添中文字符串啊！
        $returnContents["content"] = $this->deleteFrame($returnContents["content"]);//删除掉所有底部frame标签
        // --> add by liule1 20160401 
        $returnContents["content"]= preg_replace('/<[\/]?ul[^>]*?>\s*/i','',$returnContents["content"]);//删除UL
        $returnContents["content"]= preg_replace('/<li[^>]*?>/is','<p>',$returnContents["content"]);// li 换成 p
        $returnContents["content"]= preg_replace('/<\/li[^>]*?>/is','</p>',$returnContents["content"]);// /li 换成 /p
        $returnContents["content"]= preg_replace('/<div[^>]*?>/is','<p>',$returnContents["content"]);// li 换成 p
        $returnContents["content"]= preg_replace('/<\/div[^>]*?>/is','</p>',$returnContents["content"]);// /li 换成 /p
        
        
        // <--
        $returnContents["content"]= preg_replace('/<p[^>]*?>\s*/i','<p class="sina_t">',$returnContents["content"]);//给P修改样式
        $returnContents["content"]= preg_replace('/<h1>/i','<p class="sina_t">',$returnContents["content"]);//给P修改样式
        $returnContents["content"]= preg_replace('/<\/h1>/i','</p>',$returnContents["content"]);//给P修改样式
        
        $returnContents["content"]= preg_replace('/<div><br\/><\/div>\s*/i','<p class="sina_t"></p>',$returnContents["content"]);//删除UL
        
        //所有外层均套用p
        $returnContents["content"] = '<p class="sina_t">' . $returnContents["content"] . '</p>';
        
        //最后检查所有的未套用文字，均套用
        $pant = '/(>)([^<])/is';
        $returnContents["content"] = preg_replace($pant, '$1<p class="sina_t">$2', $returnContents["content"]);
        $pant = '/([^>])(<)/is';
        $returnContents["content"] = preg_replace($pant, '$1</p>$2', $returnContents["content"]);
        
        $returnContents["attribute"] = array();
        $returnContents["attribute"]["images"]=$pregDataImageArray["images"];
        $returnContents["attribute"]["videos"]=$pregDataNewsVideoArray["videos"];
        $returnContents["attribute"]["errorVideos"] = $pregDataNewsVideoArray["errorVideos"];
        $returnContents["attribute"]["imgGroup"] = $pregDataNewsSlideArray["imgGroup"];
        return $returnContents;
	}

	//替换frame标签
	public function deleteFrame($_content) {
		//-->过滤掉<p><frame>..</iframe></p>
		$f_pant = "/<p[^>]*>[^<]*<iframe.*?>.*?<\/iframe><\/p>/";
		preg_match_all($f_pant, $_content, $pregData);
		foreach ($pregData[0] as $key => $value) {
		  $_content = str_replace($value, '', $_content);
		}
		return $_content;
		//<--过滤掉<p><frame>..</iframe></p>
	}
          
	//替换新闻中图片
	public function pregNewsImage($_content){
		//$pant = "/<div [^>]*class=\"img_wrapper[^>]*><img [^>]*src=\"(.*?)\"[^>]*><span [^>]*class=\"img_descr[^>]*>(.*?)<\/span><\/div>/";
		//$pant = "/<div [^>]*class=\"img_wrapper[^>]*><img [^>]*src=[\"\'](.*?)[\"\'][^>]*>(?:<span [^>]*class=\"img_descr[^>]*>(.*?)<\/span>)?<\/div>/";
		$pant = "/(?:<div [^>]*class=\"img_wrapper[^>]*><img [^>]*src=[\"\'](.*?)[\"\'][^>]*>(?:<span [^>]*>(.*?)<\/span>)?<\/div>)|(?:<p [^>]*>(?:<span class=\"ui-dialog-border\">)?<img [^>]*src=[\"\'](.*?)[\"\'] [^>]*>(?:<\/span>)?.*?<\/p>)/";
		preg_match_all($pant,$_content,$pregData);
		if (isset($pregData[1][0]) && isset($pregData[3][0])) {
				if ($pregData[1][0] == null && $pregData[3][0] != null) {
					$pregData[1] = $pregData[3];
				}
		}
		$returnData["content"]=$_content;
		$hand = 0; // num

		if(empty($pregData[0])){
		          $returnData["images"]=array();
		}else{
			foreach($pregData[0] as $key => $value){
			    $returnData["content"]=str_replace($value, "<!--IMG_".$hand."-->", $returnData["content"]);
			    $_tmpArray=array();
			    $_tmpArray["url"]=$pregData[1][$key];
			    $_tmpArray["desc"]=$pregData[2][$key];

			    //$filesize=array();
			    //$filesize=Util::myGetImageSize($pregData[1][$key],'fread',true);
			    //$_tmpArray["width"]=isset($filesize["width"])?$filesize["width"]:'';
			    //$_tmpArray["height"]=isset($filesize["height"])?$filesize["height"]:'';
			    $returnData["images"][$hand]=$_tmpArray;
			    $hand++;
			}
    	}

		//-->处理特殊图片  专区图片 <a>标签
		$pant = "/<a[^>]*?>[^<]*?<img[^>]*src=[\"\'](.*?)[\"\'][^>]*?\/>.*?<\/a>/";
		preg_match_all($pant,$returnData['content'],$pregData);
		if(!empty($pregData[0])){
		  foreach ($pregData[0] as $key => $value) {
		      $returnData["content"] = str_replace($value, "<!--IMG_".$hand."-->", $returnData["content"]);
		      $_tmpArray = array();
		      $_tmpArray["url"]=$pregData[1][$key];
		      $_tmpArray["desc"]="";
		      $returnData["images"][$hand]=$_tmpArray;
		      $hand++;
		  }
		}

		// <center>标签
		$pant = "/<center[^>]*?>[^<]*?<img[^>]*src=[\"\'](.*?)[\"\'][^>]*?\/>.*?<\/center>/";
		preg_match_all($pant,$returnData['content'],$pregData);
		if(!empty($pregData[0])){
		  foreach ($pregData[0] as $key => $value) {
		      $returnData["content"] = str_replace($value, "<!--IMG_".$hand."-->", $returnData["content"]);
		      $_tmpArray = array();
		      $_tmpArray["url"]=$pregData[1][$key];
		      $_tmpArray["desc"]="";
		      $returnData["images"][$hand]=$_tmpArray;
		      $hand++;
		  }
		}

		// <p>标签
		$pant = "/<p[^>]*?>[^<]*?<img[^>]*?src=[\"\'](.*?)[\"\'][^>]*?\/>.*?<\/p>/is";
		preg_match_all($pant,$returnData['content'],$pregData);
		if(!empty($pregData[0])){
		  foreach ($pregData[0] as $key => $value) {
		      $returnData["content"] = str_replace($value, "<!--IMG_".$hand."-->", $returnData["content"]);
		      $_tmpArray = array();
		      $_tmpArray["url"]=$pregData[1][$key];
		      $_tmpArray["desc"]="";
		      $returnData["images"][$hand]=$_tmpArray;
		      $hand++;
		  }
		}

		// <div>标签
		$pant = "/<div[^>]*?>[^<]*?<img[^>]*src=[\"\'](.*?)[\"\'][^>]*?\/>.*?<\/div>/is";
		preg_match_all($pant,$returnData['content'],$pregData);
		if(!empty($pregData[0])){
		  foreach ($pregData[0] as $key => $value) {
		      $returnData["content"] = str_replace($value, "<!--IMG_".$hand."-->", $returnData["content"]);
		      $_tmpArray = array();
		      $_tmpArray["url"]=$pregData[1][$key];
		      $_tmpArray["desc"]="";
		      $returnData["images"][$hand]=$_tmpArray;
		      $hand++;
		  }
		}

		//<span>标签
		$pant = "/<span[^>]*?>[^<]*?<img[^>]*src=[\"\'](.*?)[\"\'][^>]*?\/>.*?<\/span>/is";
		preg_match_all($pant,$returnData['content'],$pregData);
		if(!empty($pregData[0])){
			foreach ($pregData[0] as $key => $value) {
				$returnData["content"] = str_replace($value, "<!--IMG_".$hand."-->", $returnData["content"]);
				$_tmpArray = array();
				$_tmpArray["url"]=$pregData[1][$key];
				$_tmpArray["desc"]="";
				$returnData["images"][$hand]=$_tmpArray;
				$hand++;
			}
		}

		//<span>标签
		$pant = "/<img[^>]*src=[\"\'](.*?)[\"\'][^>]*?[\/]>.*?/is";
		preg_match_all($pant,$returnData['content'],$pregData);
		if(!empty($pregData[0])){
			foreach ($pregData[0] as $key => $value) {
				$returnData["content"] = str_replace($value, "<!--IMG_".$hand."-->", $returnData["content"]);
				$_tmpArray = array();
				$_tmpArray["url"]=$pregData[1][$key];
				$_tmpArray["desc"]="";
				$returnData["images"][$hand]=$_tmpArray;
				$hand++;
			}
		}

		//<span>标签
		$pant = "/<img[^>]*src=[\"\'](.*?)[\"\'][^>]*?>.*?/is";
		preg_match_all($pant,$returnData['content'],$pregData);
		if(!empty($pregData[0])){
			foreach ($pregData[0] as $key => $value) {
				$returnData["content"] = str_replace($value, "<!--IMG_".$hand."-->", $returnData["content"]);
				$_tmpArray = array();
				$_tmpArray["url"]=$pregData[1][$key];
				$_tmpArray["desc"]="";
				$returnData["images"][$hand]=$_tmpArray;
				$hand++;
			}
		}

	    //<--
	    $returnData["images"] = $this->getPicSize($returnData["images"]);

	    return $returnData;
	}

	private function getPicSize($arr) {
		//$cache_prefix = $this->_mcPrefix . 'Option:imageSizez:';
		$cache_prefix = $this->_cache_key_pre . "gl_gift:Option:imageSizez:";

		$return = array();
		ksort($arr);
		$needs = array(); // 缓存中获取不到的，从接口获取
		$needs2 = array();    // 接口获取不到，直接getimagesize
		foreach ($arr as $_k => $_v) {
		  //增加图片属性的缓存处理
		  $return[$_k] = array(
		      'url' => trim($_v['url']),
		      'desc' => trim($_v['desc']),
		      'width' => 0,
		      'height' => 0,
		  );

		  $cacheId = $cache_prefix.md5(trim($_v['url']));
		  //获取数据
		  $info = $this->cache->redis->get($cacheId);

		  if (false !== $info) {
		      $info = json_decode($info, true);
		  }
		  //$info = false;
		  if (empty($info)) {
		      $needs[$_k] = $_v;
		      continue;
		  }

		  $return[$_k]['width'] = $info[0];
		  $return[$_k]['height'] = $info[1];
		}

		// 从接口获取宽高
		if ($needs) {
		  $imgs = array();

		  foreach ($needs as $k => $v) {
		      $imgs[] = $v['url'];
		  }
		  $api = "http://interface.sina.cn/games/api/get_img_info.shtml?imgs=" . implode(',', $imgs);
		  $dataJson=Util::curl_get_contents($api);
		  $dataArray=json_decode($dataJson,true);
		  if ($dataArray) {
		      foreach ($needs as $k => $v) {
		          if ($dataArray[$v['url']]) {
		              $info = array($dataArray[$v['url']]['w'], $dataArray[$v['url']]['h']);
		              $return[$k]['width'] = $info[0];
		              $return[$k]['height'] = $info[1];

		              $cacheId = $cache_prefix.md5(trim($v['url']));
		              $this->cache->redis->set($cacheId,json_encode($info), $this->_cache_expire);
		              //$this->_mc->set($cacheId,json_encode($info), $this->_alivetime_48h);
		          } else {
		              $needs2[$k] = $v;
		          }
		      }
		  } else {
		      $needs2 = $needs;
		  }
		}
		if ($needs2) {
		  foreach ($needs2 as $k => $v) {
		      $info = getimagesize(trim($_v['url']));
		      $cacheId = $cache_prefix.md5(trim($v['url']));
		      $this->cache->redis->set($cacheId,json_encode($info), $this->_cache_expire);
		     // $this->_mc->set($cacheId,json_encode($info), $this->_alivetime_48h);
		      if ($info) {
		          $return[$k]['width'] = $info[0];
		          $return[$k]['height'] = $info[1];
		      } else {
		          unset($return[$k]);
		      }
		  }
		}


		return $return;
	}

    public function getListVideoInfoById($id) {
        $videoInfo=$this->getVideoInfoById($id);

        $returnData=array();
        if(empty($videoInfo)){
            return array('result'=>0);
        } else {
            $returnData['result'] = 1;
            $returnData['desc'] = "";
            $returnData['ipad_url'] = $videoInfo['stream'];
            $returnData['imagelink'] = $videoInfo['image'];
            $returnData['nick_name'] = "";
            $returnData['play_times'] = 0;
            $returnData['time_length'] = 0;
            return $returnData;
        }
    }

    public function getVideoInfoById($id) {
		//拼装
		$cache_key = $this->_cache_key_pre . "getVideoInfoById:" . $id;

		//获取数据
		$data = $this->cache->redis->get($cache_key);

		$data && $data = json_decode($data, true);

		if($data === false){
			$videoInfo=$this->getVideoInfoByIdFromApi($id);
			$data = $videoInfo['data'];

			//数据入缓存
			$this->cache->redis->set($cache_key, json_encode($data), $this->_cache_expire);
		}

		return !empty($data) ? $data : array();
    }

    public function getVideoInfoByIdFromApi($id) {
        try {
            $getFromUrl='http://vms.video.sina.com.cn/interface/get_videos_by_flvid.php?flvids='.$id;
            $retJsonData=Util::curl_get_contents($getFromUrl);
            $retData=json_decode($retJsonData,true);
            return $retData;
        } catch (Exception $e) {
            ;
        }
        return array();
    }

	//替换文档中视频
	public function pregNewsVideos($_content){
			//初始化video类
			//$this->_vobj = $this->getObj('Video');

	        //设置句柄
	        $hand = 0;
	        $errorHand = 0;
				//处理单个视频
	        $pant="/<!--mce-plugin-videoList\[(.*?)\]mce-plugin-videoList-->/is";
	        preg_match_all($pant,$_content,$pregData);
	        $returnData["content"] = $_content;
	        $returnData["videos"] = array();
	        $returnData["errorVideos"] = array();
	        if(!empty($pregData[0])){
	              //max yang 重构视频获取功能
	              foreach ($pregData[0] as $key => $value) {
	                        $replaceKey = "";
	                        $videoJson = $pregData[1][$key];
	                        $infoArr = json_decode($videoJson, true);
	                        $replaceKeyArr = array();
	                        foreach ($infoArr['videos'] as $ik => $iv) {
	                            //-->获取视频信息
	                            //preg_match_all("/http:\/\//", $iv['url'], $_match);//如果是url 则调用url获取
	                            //if (!empty($_match[0])) {
	                            //    $videoInfo = $this->_vobj->getVideoInfoByUrl($iv['url']);
	                            //} else {
	                            //    $videoInfo = $this->_vobj->getListVideoInfoById($iv['url']);
	                            //}
	                            $videoInfo = $this->getListVideoInfoById(intval($iv['vid']));
	                            //<--获取视频信息

	                                if ($videoInfo['result'] == 0) { //vms视频做特殊视频处理
	                                    $replaceKeyArr[] = "<!--ERRORVIDEOS_".$errorHand."-->";
	                                    $_tmpArray["desc"] = "";
	                                    $_tmpArray["video_url"] = $iv["url"];
	                                    $_tmpArray["img_url"] = "";
	                                    $_tmpArray["video_source"] = "";
	                                    $_tmpArray["video_playcount"] = 0;
	                                    $_tmpArray["video_playtime"] = 0;
	                                    $returnData["errorVideos"][$errorHand]=$_tmpArray;
	                                    $errorHand++;
	                                } else {
	                                    $replaceKeyArr[] = "<!--VIDEO_".$hand."-->";
	                                    $_tmpArray["desc"]=$videoInfo["desc"];
	                                    $_tmpArray["video_url"]=$videoInfo["ipad_url"];
	                                    $_tmpArray["img_url"]=$videoInfo["imagelink"];
	                                    $_tmpArray["video_source"]=$videoInfo["nick_name"];
	                                    $_tmpArray["video_playcount"]=$videoInfo["play_times"];
	                                    $_tmpArray["video_playtime"]=$videoInfo["time_length"];
	                                    $returnData["videos"][$hand]=$_tmpArray;
	                                    $hand++;
	                                }
	                        }
	                        $replaceKey = implode("<p class=\"sina_t\">&nbsp;&nbsp;</p>", $replaceKeyArr);
	                        $returnData["content"]=str_replace($value, $replaceKey, $returnData["content"]);
	              }
	        }

	  //处理新增视频类型 高清视频
	  $pant="/<!--mce-plugin-videoList2\[(.*?)\]mce-plugin-videoList2-->/is";
	  preg_match_all($pant,$_content,$pregData);
	  if(!empty($pregData[0])){
	      //max yang 重构视频获取功能
	      foreach ($pregData[0] as $key => $value) {
	          $replaceKey = "";
	          $videoJson = $pregData[1][$key];
	          $infoArr = json_decode($videoJson, true);
	          $replaceKeyArr = array();
	          foreach ($infoArr['videos'] as $ik => $iv) {
	              $videoInfo = $this->_vobj->getNewVideoInfoById(intval($iv['videoid']));
	              if ($videoInfo['result'] == 0) { //vms视频做特殊视频处理
	                  $replaceKeyArr[] = "<!--ERRORVIDEOS_".$errorHand."-->";
	                  $_tmpArray["desc"] = "";
	                  $_tmpArray["video_url"] = $iv["url"];
	                  $_tmpArray["img_url"] = "";
	                  $_tmpArray["video_source"] = "";
	                  $_tmpArray["video_playcount"] = 0;
	                  $_tmpArray["video_playtime"] = 0;
	                  $returnData["errorVideos"][$errorHand]=$_tmpArray;
	                  $errorHand++;
	              } else {
	                  $replaceKeyArr[] = "<!--VIDEO_".$hand."-->";
	                  $_tmpArray["desc"]=$videoInfo["desc"];
	                  $_tmpArray["video_url"]=$videoInfo["ipad_url"];
	                  $_tmpArray["img_url"]=$videoInfo["imagelink"];
	                  $_tmpArray["video_source"]=$videoInfo["nick_name"];
	                  $_tmpArray["video_playcount"]=$videoInfo["play_times"];
	                  $_tmpArray["video_playtime"]=$videoInfo["time_length"];
	                  $returnData["videos"][$hand]=$_tmpArray;
	                  $hand++;
	              }
	          }
	          $replaceKey = implode("<p class=\"sina_t\">&nbsp;&nbsp;</p>", $replaceKeyArr);
	          $returnData["content"]=str_replace($value, $replaceKey, $returnData["content"]);
	      }
	  }

	  //flash 按照正常视频处理
	          //flash obj
	          $pant = "/<object[^>]*?>.*?<embed[^>]*? src=\"(.*?)\"[^>]*?>.*?<\/embed><\/object>/";
	          preg_match_all($pant, $returnData["content"], $pregData);
	          if (!empty($pregData[0])) {
	              foreach ($pregData[0] as $key => $value) {
	                  $returnData["content"]=str_replace($value, "<!--VIDEO_".$hand."-->", $returnData["content"]);
	                  $_tmpArray["desc"] = "";
	                  $_tmpArray["video_url"] = $pregData[1][$key];
	                  $_tmpArray["img_url"] = "";
	                  $_tmpArray["video_source"] = "";
	                  $_tmpArray["video_playcount"] = 0;
	                  $_tmpArray["video_playtime"] = 0;
	                  $returnData["videos"][$hand]=$_tmpArray;
	                  $hand++;
	              }
	          }
	        //-->特殊视频处理方案
	        //youku video
	        $pant="/<p[^>]*>[^<]*<iframe src=\"(.*?)\"[^>]*?><\/iframe><\/p>/";
	        preg_match_all($pant, $returnData["content"], $pregData);
	        if (!empty($pregData[0])) {
	            foreach ($pregData[0] as $key => $value) {
	                if (stristr($value, "player.youku.com")) { //youku 视屏
	                    $returnData["content"]=str_replace($value, "<!--ERRORVIDEOS_".$errorHand."-->", $returnData["content"]);
	                    $_tmpArray["desc"] = "";
	                    $_tmpArray["video_url"] = $pregData[1][$key];
	                    $_tmpArray["img_url"] = "";
	                    $_tmpArray["video_source"] = "";
	                    $_tmpArray["video_playcount"] = 0;
	                    $_tmpArray["video_playtime"] = 0;
	                    $returnData["errorVideos"][$errorHand]=$_tmpArray;
	                    $errorHand++;
	                }
	            }
	        }
	        
	       $pant = '/<iframe _se_type=\".*?\" [^>]*? src=\"(.*?)\"[^>]*?><\/iframe>/';
	        preg_match_all($pant, $returnData["content"], $pregData);
	        if (!empty($pregData[0])) {
	            foreach ($pregData[0] as $key => $value) {
	                if (stristr($value, "player.youku.com")) { //youku 视屏
	                    $returnData["content"]=str_replace($value, "<!--ERRORVIDEOS_".$errorHand."-->", $returnData["content"]);
	                    $_tmpArray["desc"] = "";
	                    $_tmpArray["video_url"] = $pregData[1][$key];
	                    $_tmpArray["img_url"] = "http://m1.sinaimg.cn/maxwidth.540/m1.sinaimg.cn/8a1ce3e3d488237dcc3b6846d8e43948_909_506.jpg";
	                    $_tmpArray["video_source"] = "";
	                    $_tmpArray["video_playcount"] = 0;
	                    $_tmpArray["video_playtime"] = 0;
	                    $returnData["errorVideos"][$errorHand]=$_tmpArray;
	                    $errorHand++;
	                }
	            }
	        }
	        
	        //flash obj
	        $pant = "/<object[^>]*? mp4=\"(.*?)\" [^>]*?>.*?<\/object>/";
	        preg_match_all($pant, $returnData["content"], $pregData);
	        if (!empty($pregData[0])) {
	        	foreach ($pregData[0] as $key => $value) {
	        		$returnData["content"]=str_replace($value, "<!--VIDEO_".$hand."-->", $returnData["content"]);
	        		$_tmpArray["desc"] = "";
	        		$_tmpArray["video_url"] = $pregData[1][$key];
	        		$_tmpArray["img_url"] = "";
	        		$_tmpArray["video_source"] = "";
	        		$_tmpArray["video_playcount"] = 0;
	        		$_tmpArray["video_playtime"] = 0;
	        		$returnData["videos"][$hand]=$_tmpArray;
	        		$hand++;
	        	}
	        }
	        
	        //<--特殊视频处理方案
	        return $returnData;
	}

    public function getSliderImagesArrayByUrl($sliderUrl)
    {
        $arrTmp = explode('#', $sliderUrl);
        $sliderInfo = $this->getSliderInfoByUrl($arrTmp[0]);
        
        $returnData = array();
        if (empty($sliderInfo)) {
            $returnData['image_count'] = 0;
            $returnData['thumbnail_urls'] = array();
        } else {
            $returnData['image_count'] = $sliderInfo['total'];
            $returnData['sid'] = $sliderInfo['album']['sid'];
            $returnData['images_id'] = $sliderInfo['album']['id'];
            foreach ($sliderInfo['data']['item'] as $key => $imgInfo) {
                $returnData['thumbnail_urls'][] = $imgInfo['thumb_url'];
            }
        }

        return $returnData;
    }
    public function getSliderInfoByUrl($sliderUrl)
    {
        $cache_name = $this->_cache_key_pre."slider:$sliderUrl";
        $data = $this->cache->redis->get($cache_name);
        $data && $data = json_decode($data, true);
        if ($data === false) {
            $data = $this->getSliderInfoFromApiByUrl($sliderUrl);
            $this->cache->redis->save($cache_name, json_encode($data), $this->_cache_expire);
        }

        return $data;
    }

          public function getPicInfoByIdAndSid($sid,$album_id){
			        $cache_name = $this->_cache_key_pre . ":pic:id:" . $album_id .":sid:".$sid;
			        $data = $this->cache->redis->get($cache_name);
			        $data && $data = json_decode($data, true);

			        if ($data === false) {
			            $data = $this->getPicInfoByIdAndSidFromApi($sliderUrl);
			            $this->cache->redis->save($cache_name, json_encode($data), $this->_cache_expire);
			        }
                    
                    return $data;
          }
          
          public function getPicInfoByIdAndSidFromApi($sid,$album_id){
                    
                    $destURL="http://platform.sina.com.cn/slide/image?app_key="._SINA_APP_KEY_."&format=json&sid=".$sid."&album_id=".$album_id."&num=100";
                    $json_data=Util::curl_get_contents($destURL);
                    $picInfo=json_decode($json_data,true);
                    if(!empty($picInfo["data"])){
                              return $picInfo["data"]["item"];
                    }else{
                              return array();
                    }
          }

	//处理图集
	private function pregNewsSlides($_content) {
		$returnData['content'] = $_content;
		//-->根据地质获取图集信息方式
		//初始化图集类
		//$this->_sobj = $this->getObj('Slider');
		$pant = "/<!-- HDSlide(.*?)-->/is";
		preg_match_all($pant,$_content,$pregData);
		 
		if (empty($pregData[0])) {
			$returnData["imgGroup"]=array();
		} else {
	  		foreach ($pregData[0] as $key => $value) {
	  			$returnData["content"]=str_replace($value, "<!--IMGGROUP_".$key."-->", $returnData["content"]);
	  			 
	  			//echo $pregData[1][$key];exit;
	  			$sliderInfo = $this->getSliderImagesArrayByUrl(trim($pregData[1][$key]));
	  			$picInfo = $this->getPicInfoByIdAndSid($sliderInfo['sid'], $sliderInfo['images_id']);
	  			$tmpArray = array();
	  			foreach ($picInfo as $pk => $pv) {
	  				$_tmpArray = array();
	  				$_tmpArray['url'] = $pv['img_url'];
	  				$_tmpArray['desc'] = "";
	  				if ($pk==0) {
	  					//$filesize=array();
	  					//$filesize=Util::myGetImageSize($pv['img_url'],'fread',true);
	  					//$_tmpArray["width"]=isset($filesize["width"])?$filesize["width"]:'';
	  					//$_tmpArray["height"]=isset($filesize["height"])?$filesize["height"]:'';
	  					$_tmpTotal = array();
	  					$_tmpTotal[] = $_tmpArray;
	  					$_tmpTotal = $this->getPicSize($_tmpTotal);
	  					$_tmpArray = $_tmpTotal[0];
	  				} else {
	  					$_tmpArray["width"] = 0;
	  					$_tmpArray["height"] = 0;
	  				}
	  				$tmpArray[] = $_tmpArray;
	  			}
	  			$returnData["imgGroup"][]['list'] = $tmpArray;
	  		}
		}
		//<--根据地质获取图集信息方式
		//过度key
		$n_count = count($pregData[0]);
		//-->html插入多个li标签图集方式
		$pant = "/<!-- 图集开始(.*?)图集结束 -->/is";
		preg_match_all($pant,$_content,$pregData);

		if (!empty($pregData[0])) {
	      	foreach ($pregData[0] as $_k => $_v) {
	      		$key = $_k + $n_count;
	      		$returnData["content"] = str_replace($_v, "<!--IMGGROUP_".$key."-->", $returnData["content"]);
	      		
	  			//$s_pant = "/<img src=\"(.*?)\" alt=\"(.*?)\" \/\><span [^>]*\>(.*?)<\/span\><\/li\>/";
	            $s_pant = "/<li><img src=\"(.*?)\" alt=\"(.*?)\" \/\>(<span\>|<span [^>]*\>)(.*?)<\/span\><\/li\>/"; //操！ span标签有时候无属性
	  			preg_match_all($s_pant,$_content,$s_pregData);
	  			$tmpArray = array();
	  			foreach ($s_pregData[2] as $s_k => $s_v) {
	  						$_tmpArray = array();
	  						$_tmpArray['url'] = $s_v;
	  						$_tmpArray['desc'] = "";
	  						if ($s_k == 0) {
	          							//$filesize=array();
	          							//$filesize=Util::myGetImageSize($s_v,'fread',true);
	          							//$_tmpArray["width"]=isset($filesize["width"])?$filesize["width"]:'';
	          							//$_tmpArray["height"]=isset($filesize["height"])?$filesize["height"]:'';
	          							$_tmpTotal = array();
	          							$_tmpTotal[] = $_tmpArray;
	          							$_tmpTotal = $this->getPicSize($_tmpTotal);
	          							$_tmpArray = $_tmpTotal[0];
	  						} else {
	  									$_tmpArray["width"] = 0;
	  									$_tmpArray["height"] = 0;
	  						}
	  						$tmpArray[] = $_tmpArray;
	                        $returnData["content"] = str_replace($s_pregData[0][$s_k], "", $returnData["content"]);
	  			}
	      		$returnData["imgGroup"][]['list'] = $tmpArray;
	      	}
		}

		//<--html插入多个li标签图集方式
	//-->获取js内嵌的图集 -- add by max yang at 2014/10/27
	$n_count += count($pregData[0]);
	$pant = "/<script>.*?<\/script>/is";
	preg_match_all($pant,$_content,$pregData);

	if (!empty($pregData)) {
	    foreach ($pregData[0] as $key => $value) {
	        //看是否有合法图集
	        $pant = "/slide_url : '(.*?)'/is";
	        preg_match($pant,$value,$tmpPregData);

	        if (isset($tmpPregData[1])) {
	            //echo $pregData[1][$key];exit;
	            $sliderInfo = $this->_sobj->getSliderImagesArrayByUrl(trim($tmpPregData[1]));
	            if (!isset($sliderInfo['sid']) && !isset($sliderInfo['images_id'])) {
	                continue;
	            }
	            $picInfo = $this->getPicInfoByIdAndSid($sliderInfo['sid'], $sliderInfo['images_id']);
	            $tmpArray = array();
	            foreach ($picInfo as $pk => $pv) {
	                $_tmpArray = array();
	                $_tmpArray['url'] = $pv['img_url'];
	                $_tmpArray['desc'] = "";
	                if ($pk==0) {
	                    //$filesize=array();
	                    //$filesize=Util::myGetImageSize($pv['img_url'],'fread',true);
	                    //$_tmpArray["width"]=isset($filesize["width"])?$filesize["width"]:'';
	                    //$_tmpArray["height"]=isset($filesize["height"])?$filesize["height"]:'';
	                    $_tmpTotal = array();
	                    $_tmpTotal[] = $_tmpArray;
	                    $_tmpTotal = $this->getPicSize($_tmpTotal);
	                    $_tmpArray = $_tmpTotal[0];
	                } else {
	                    $_tmpArray["width"] = 0;
	                    $_tmpArray["height"] = 0;
	                }
	                $tmpArray[] = $_tmpArray;
	            }

	            //对返回值进行处理
	            $key = $key+$n_count;
	            $returnData["content"]=str_replace($value, "<!--IMGGROUP_".$key."-->", $returnData["content"]);
	            $returnData["imgGroup"][]['list'] = $tmpArray;
	        }
	    }
	}
	//<--
		return $returnData;
	}

	//获取游戏详情接口（备用）
	public function getGameDetailInfo($id) {
		//拼装
		$cache_key = $this->_cache_key_pre . "gl_newcard_gameinfo:" . $id;

		//获取数据
		$data = $this->cache->redis->get($cache_key);
		$data && $data = json_decode($data, true);
		//$data = false;
		if($data === false){
			$data=$this->getGameDetailInfoFromApi($id);

			//数据入缓存
			$this->cache->redis->set($cache_key, json_encode($data), $this->_cache_expire);
		}

	  	return $data;
	}

	private function getGameDetailInfoFromApi($id) {
		//定义最大重复请求次数
		$repeat = 3;

		//初始化原始数据
		$data = array();
		
		//数据拼装
		$time = time();
		$data = array();
		$data['id'] = $id;
		$md5_code = $this->md5Code($data, $time);
        $normalParameter = $this->implodeParameter($time);

		//获取
		while(!$return_data && $repeat-- > 0){
	    	//设定cms接口地址
      		$destURL = "http://api.g.sina.com.cn/ka/api/game/info?".$normalParameter."&id=".$data['id']."&sign=".$md5_code;

	    	//获取数据
	    	$json_data = Util::curl_get_contents($destURL);

	    	//反json
	    	$json_data = json_decode($json_data, true);
	    	$return_data = $json_data['data'];
		}

		//返回结果
		if(is_array($return_data) && !empty($return_data)){
			return $return_data;
		}else{
			return array();
		}
	}

	// md5 code 
	private function md5Code($data, $time) {
  	//ios android check partner id
		if (isset($_REQUEST['platform'])) {
          	if ($_REQUEST['platform'] == 'android') {
          		$partner_id = $this->_partner_id_android;
          	} else {
          		$partner_id = $this->_partner_id_ios;
          	}
		} else {
				$partner_id = $this->_partner_id_ios;
		}
		$key = '';
		$key .= $this->_format; // format
		$key .= $this->_version; // version
		$key .= $time; // timestamp
		$key .= $partner_id; // partner id

		if (!empty($data)) {
			foreach ($data as $k => $v) {
				$key .= $v;
			}	
		}
		//ios android check partner key
		if (isset($_REQUEST['platform'])) {
			if ($_REQUEST['platform'] == 'android') {
				$key .= $this->_partner_key_android;
			} else {
				$key .= $this->_partner_key_ios;
			}
		} else {
			$key .= $this->_partner_key_ios;
		}

		//echo $key;exit;
		return md5($key);
	}

	private function implodeParameter($time) {
		//ios android check
		if (isset($_REQUEST['platform'])) {
  			if ($_REQUEST['platform'] == 'android') {
  					$partner_id = $this->_partner_id_android;
  			} else {
  					$partner_id = $this->_partner_id_ios;
  			}
		} else {
				$partner_id = $this->_partner_id_ios;
		}
		$parameter = "format=".$this->_format."&version=".$this->_version."&timestamp=".$time."&partner_id=".$partner_id;
		return $parameter;
	}

	/**
	 * 根据攻略游戏ID查询对应新浪新手卡游戏ID
	 * @param  [type] $gl_gid [description]
	 * @return [type]       [description]
	 */
	public function get_newcard_gid_by_glgid($glgid, $platform) {
		$cache_key = $this->_cache_key_pre . "get_newcard_gid_by_glgid:{$glgid}:{$platform}";
		$data = $this->cache->redis->get ( $cache_key );
		if ($data === false) {
			$data = $this->_get_newcard_gid_by_glgid_from_db($glgid, $platform);
			$this->cache->redis->set($cache_key, $data, $this->_cache_expire);
		}
		return (int)$data;
	}
	private function _get_newcard_gid_by_glgid_from_db($glgid, $platform) {
		$conditions = array(
			'table' => 'gl_games',
			'where' => array(
				'id' => $glgid,
				'parentid' => 0
			),
			'start' => 0,
			'limit' => 1,
		);
		$sql = $this->find($conditions);
		$row = $this->db->query_read($sql);
		$row = $row ? $row->row_array() : array();
		$return = 0; //初始化返回数据

		//通过平台判断
		if($platform == 'ios'){
			$return = $row['newcard_gid_ios'];
		}else{
			$return = $row['newcard_gid_android'];
		}

		return $return ? $return : 0;
	}
}
