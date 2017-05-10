<?php

require_once  SYSDIR.'/libraries/weibo/HttpRequest.php';
require_once  SYSDIR.'/libraries/weibo/weiboConfig.php';

/**
 * @desc 微博接口
 * @author gaoyi@staff.sina.com.cn
 * 
 */
class WeiboApi {

    private $mExt;
	private $httpRequest;
	private $ci;
    
    public function __construct($akey = '') {
    	$CI = &get_instance();
    	if ($akey != '')
    	{
    	    if ($akey['akey']) //如果已经配置勋章akey则用勋章akey
	    		{
	    		$wb_akey = $akey['akey'];
	    		}
    		else 
	    		{
	    		$wb_akey = $akey;
	    		}
    	}
    	else 
    	{
    		$wb_akey = $CI->config->item('WB_AKEY');
    	}
    	$this->ci = $CI;
    	//$wb_skey = $CI->config->item('WB_SKEY');
        $this->mExt = "json";
        $this->httpRequest = new HttpRequest();
        $this->httpRequest->init($wb_akey);
    }

    public function setExt($sExt) {
        if (in_array($sExt, array("json", "xml"))) {
            $this->mExt = $sExt;
        } else {
            $this->mExt = "json";
        }
    }

    //获取用户粉丝列表-new
    public function friends_follow($uid, $count = 20, $cursor = 0) {
        $aParam['uid'] = $uid;
        $aParam['count'] = $count;
        $aParam['cursor'] = $cursor;
        return $this->httpRequest->send(WeiboConfig::API_FRIENDSHIPS_FOLLOWS . "." . $this->mExt, 'GET', $aParam);
    }

    //获取用户优质粉丝列表-new
    public function friends_follow_active($uid, $count = 20) {
        $aParam['uid'] = $uid;
        $aParam['count'] = $count;
        return $this->httpRequest->send(WeiboConfig::API_FRIENDSHIPS_FOLLOWS_ACTIVE . "." . $this->mExt, 'GET', $aParam);
    }

    //获取用户关注列表
    public function friendships_friends($uid, $count = 30, $page=1) {
        $aParam["uid"] = $uid;
        $aParam["count"] = $count;
        $aParam["page"] = $page;
        return $this->httpRequest->send(WeiboConfig::API_FRIENDSHIPS_FRIENDS . "." . $this->mExt, 'GET', $aParam);
    }

    public function friends_follow_bilateral($uid, $count = 20, $page = 1) {
        $aParam["uid"] = $uid;
        $aParam["count"] = $count;
        $aParam["page"] = $page;
        return $this->httpRequest->send(WeiboConfig::API_FRIENDSHIPS_FOLLOWS_BILATERAL . "." . $this->mExt, 'GET', $aParam);
    }

   	//获取互粉用户ID列表
   	public function friends_follow_bilateral_ids($uid, $count = 50, $page = 1){
   		$aParam['uid'] = $uid;
   		$aParam['count'] = $count;
   		$aParam['page'] = $page;
   		return $this->httpRequest->send(WeiboConfig::API_FRIENDSHIPS_FOLLOWS_BILATERAL_IDS . "." . $this->mExt, 'GET', $aParam);
   	}
   	
   	//获取用户关注的一组用户的ID列表
   	public function friendships_friends_ids($uid, $count = 50){
   		$aParam['uid'] = $uid;
   		$aParam['count'] = $count;
   		return $this->httpRequest->send(WeiboConfig::API_FRIENDSHIPS_FRIENDS_IDS . "." . $this->mExt, 'GET', $aParam);
   	}
   	
    //转发一条微博-new
    public function statuses_repost($id, $status) {
        $aParam['id'] = $id;
        $aParam['status'] = $status;
        return $this->httpRequest->send(WeiboConfig::API_STATUSES_REPOST . "." . $this->mExt, 'POST', $aParam);
    }

    //获取用户最新发表的微博列表-new
    public function statuses_user_timeline($uid, $count = 20) {
        $aParam['uid'] = $uid;
        $aParam['count'] = $count;
        return $this->httpRequest->send(WeiboConfig::API_STATUS_USER_TIMELINE . "." . $this->mExt, 'GET', $aParam);
    }

    //获取用户信息-new
    public function users_show($uid) {
        $aParam['uid'] = $uid;
        return $this->httpRequest->send(WeiboConfig::API_USERS_SHOW . "." . $this->mExt, 'GET', $aParam);
    }

    public function user_show_inner($uid) {
        $aParam["uid"] = $uid;
        return $this->httpRequest->send(WeiboConfig::API_USERS_SHOW_INNER . "." . $this->mExt, 'GET', $aParam);
    }

    //根据用户id获取最新发表的微博列表
    public function status_show($user_id, $count = 20) {
        $aParam['uid'] = $user_id;
        $aParam['count'] = $count;
        return $this->httpRequest->send(WeiboConfig::API_STATUSES_SHOW . "." . $this->mExt, 'GET', $aParam);
    }

    //关注一个用户
    public function friend_create($iUserId) {
        $aParam["uid"] = $iUserId;
        return $this->httpRequest->send(WeiboConfig::API_FRIENDSHIP_CREATE . "." . $this->mExt, "POST", $aParam);
    }

    //取消关注
    public function friend_destroy($iUserId) {
        $aParam["uid"] = $iUserId;
        return $this->httpRequest->send(WeiboConfig::API_FRIENDSHIP_DESTROY . "." . $this->mExt, "POST", $aParam);
    }

    //获得两人的关系
    public function friendship_show($iTargetId, $iSourceId) {
        $aParam["source_id"] = $iSourceId;
        $aParam["target_id"] = $iTargetId;
        return $this->httpRequest->send(WeiboConfig::API_STATUSES_FRIENDSHIP_SHOW . "." . $this->mExt, "GET", $aParam);
    }

    //获取勋章接口
    public function badge_get($uids, $badge_id,$medal_user='',$medal_pwd='') {
        $aParam["uids"] = $uids;
        $aParam["badge_id"] = $badge_id;
            if ($medal_user) {//判断活动是否配置勋章信息
        	$wb_user = $medal_user;
        	$wb_pass = $medal_pwd;
   
        	
        }else 
        {
        	$wb_user = $this->ci->config->item('WB_USER');
        	$wb_pass = $this->ci->config->item('WB_PASS');
        }
        return $this->httpRequest->send(WeiboConfig::API_GET_BADGE . "." . $this->mExt, "POST", $aParam, $wb_user, $wb_pass,true);
    }

    //发一条微博
    public function update($sStatus) {
        $aParam["status"] = $sStatus;
        return $this->httpRequest->send(WeiboConfig::API_STATUSES_UPDATE . "." . $this->mExt, "POST", $aParam);
    }

    //发一条带图片的微博
    public function update_with_pic($sStatus, $url='',$pic_id='') {
        $aParam["status"] = $sStatus;
        if ($url != '')
        {
        	$aParam["url"] = $url;
        }   
             
        if ($pic_id != '')
        {
        	$aParam["pic_id"] = $pic_id;
        }
        return $this->httpRequest->send(WeiboConfig::API_STATUSES_UPDATE_WITH_PIC . "." . $this->mExt, "POST", $aParam);
    }

    //上传一个带图片的微博
    public function upload_with_pic($aStatus, $imgurl) {
        return $this->httpRequest->upload(WeiboConfig::API_STATUSES_UPLOAD_WITH_PIC . "." . $this->mExt, $aStatus, $imgurl);
    }

    //微博消息ID返回某条微博消息的评论列表
    public function status_comments($iWeiboId, $iCount = 10, $iPage = 1) {
        $aParam['id'] = $iWeiboId;
        $aParam['count'] = $iCount;
        $aParam['page'] = $iPage;
        return $this->httpRequest->send(WeiboConfig::API_STATUSES_COMMENTS . '.' . $this->mExt, "GET", $aParam);
    }

    //获得当前用户发出的评论
    public function status_comment_to_me($iCount = 10, $iPage = 1) {
        $aParam['count'] = $iCount;
        $aParam['page'] = $iPage;
        return $this->httpRequest->send(WeiboConfig::API_STATUSES_COMMENTS_TO_ME . '.' . $this->mExt, "GET", $aParam);
    }

    //获得当前用户收到的评论
    public function status_comment_by_me($iCount, $iPage) {
        $aParam['count'] = $iCount;
        $aParam['page'] = $iPage;
        return $this->httpRequest->send(WeiboConfig::API_STATUSES_COMMENTS_BY_ME . '.' . $this->mExt, "GET", $aParam);
    }

    //对一条微博信息进行评论
    public function status_comment($iWeiboId, $sComment,$comment_ori = 0) {
        $aParam['id'] = $iWeiboId;
        $aParam['comment'] = $sComment;
        $aParam['comment_ori'] = $comment_ori;
        
        return $this->httpRequest->send(WeiboConfig::API_STATUSES_COMMENT . '.' . $this->mExt, "POST", $aParam);
    }

    //删除当前用户微博的评论信息
    public function status_comment_destroy($iWeiboId) {
        $aPrams = array();
        $aPrams["cid"] = $iWeiboId;
        return $this->httpRequest->send(WeiboConfig::API_STATUSES_COMMENT_DESTORY . '.' . $this->mExt, "POST", $aPrams);
    }

    public function status_destroy($mid) {
        $aParam["id"] = $mid;
        return $this->httpRequest->send(WeiboConfig::API_PLACE_STATUSES_DESTROY . "." . $this->mExt, "GET", $aParam);
    }

    //删除当前用户微博的评论信息,上面那个是删除微博信息
    public function comment_destroy($cid) {
        global $meUserinfo;
        $aParam['uid'] = $meUserinfo['id'];
        $aParam['id'] = $cid;
        return $this->httpRequest->send(WeiboConfig::API_PLACE_COMMENT_DESTORY . '.' . $this->mExt, "GET", $aParam);
    }

    //转发微博
    public function status_repost($iWeiboId, $sContent = "", $isComment = 0) {
        $aParam["id"] = $iWeiboId;
        $aParam["status"] = $sContent;
        $aParam["is_comment"] = $isComment;
        return $this->httpRequest->send(WeiboConfig::API_STATUSES_REPOST . "." . $this->mExt, "POST", $aParam);
    }

    //回复评论
    public function status_reply($iCid, $sComment, $iRid) {
        $aParam['cid'] = $iCid;
        $aParam['comment'] = $sComment;
        $aParam['id'] = $iRid;
        return $this->httpRequest->send(WeiboConfig::API_STATUSES_REPLY . "." . $this->mExt, "POST", $aParam);
    }

    //批量获得用户关系
    public function friendship_batch($aUid) {
        if (is_array($aUid)) {
            $aParam["uids"] = implode(",", $aUid);
        } else {
            $aParam["uids"] = $aUid;
        }
        return $this->httpRequest->send(WeiboConfig::API_FRIENDSHIP_BATCH . "." . $this->mExt, "GET", $aParam);
    }

    //批量获取用户信息
    public function users_batch_show($aUid,$is_login=false,$userinfo=array()) {
        if (is_array($aUid)) {
            $aParam["uids"] = implode(",", $aUid);
        } else {
            $aParam["uids"] = $aUid;
        }
        
        if ($is_login == true)
        {
        	return $this->httpRequest->send(WeiboConfig::API_USERS_BATCH_SHOW . "." . $this->mExt, 'GET', $aParam,$aParam,$userinfo['user'],$userinfo['password'],true);
        }
        else 
        {
        	return $this->httpRequest->send(WeiboConfig::API_USERS_BATCH_SHOW . "." . $this->mExt, 'GET', $aParam);
        }
    }

    public function statuses_count($ids = '') {
        $aParam['ids'] = $ids;
        return $this->httpRequest->send(WeiboConfig::API_STATUSES_COUNTS . "." . $this->mExt, 'GET', $aParam);
    }

    public function statuses_search($params) {
        //$aParam["sid"] = SEARCH_SID;
        foreach ($params as $key => $value) {
            $aParam[$key] = $value;
        }
        return $this->httpRequest->send(WeiboConfig::API_STATUSES_SEARCH . "." . $this->mExt, 'GET', $aParam);
    }

    //根据关键字和（或）分类进行搜索，返回相关的poi点信息
    public function pois_search($keyword, $province = '', $city = '', $page = 1, $count = 20, $category = '') {
        if (!empty($city) && is_string($city)) {
            $aParam['city'] = $city;
        }

        if (!empty($province) && is_string($province)) {
            $aParam['province'] = $province;
        }

        if (!empty($category) && is_string($category)) {
            $aParam['category'] = $category;
        }

        if ($province == "100" || $city == "0100") {
            unset($aParam['city'], $aParam['province']);
        }

        $aParam['keyword'] = $keyword;
        $aParam['page'] = $page;
        $aParam['count'] = $count;

        return $this->httpRequest->send(WeiboConfig::API_POIS_SEARCH . "." . $this->mExt, "GET", $aParam);
    }

    //地点纠错
    public function poi_flag($poiid, $error_type, $error) {
        $aParam['poiid'] = $poiid;
        $aParam['error_type'] = $error_type;
        $aParam['error'] = urlencode($error);
        return $this->httpRequest->send(WeiboConfig::API_POIS_FLAG . "." . $this->mExt, "POST", $aParam);
    }

    public function statuses_unread($withNewStatus = 1, $sinceID = null) {
        if ($sinceID != null) {
            $aParam['since_id'] = $sinceID;
        }

        $aParam['with_new_status'] = $withNewStatus;

        return $this->httpRequest->send(WeiboConfig::API_STATUSES_UNREAD . "." . $this->mExt, "GET", $aParam);
    }

    public function statuses_unset_count($type) {
        $aParam['type'] = $type;
        return $this->httpRequest->send(WeiboConfig::API_STATUSES_RESET_COUNT . "." . $this->mExt, "GET", $aParam);
    }

    //根据关键字和（或）分类，在中心点附近搜索，返回相关的poi点信息
    public function pois_round() {
        $aParam['coordinate'] = '116.36993,39.97646';
        $aParam['q'] = '1';
        $aParam['city'] = '0010';
        return $this->httpRequest->send(WeiboConfig::API_POIS_ROUND . "." . $this->mExt, 'GET', $aParam);
    }

    //根据中心点生成静态地图的图片
    public function map_image($lat, $lon, $size, $zoom = 12) {
        $aParam['center_coordinates'] = $lon . ',' . $lat;
        $aParam['zoom'] = $zoom;
        $aParam['img_format'] = 'png';
        $aParam['coordinates'] = $lon . ',' . $lat;
        $aParam['names'] = '';
        $aParam['size'] = $size;
        $aParam['icons'] = '105';
        //$aParam['px'] = '1';
        return $this->httpRequest->send(WeiboConfig::API_MAP_IMAGE . "." . $this->mExt, 'GET', $aParam);
    }

    //获取个人信息
    function verify_credentials() {
        return $this->httpRequest->send(WeiboConfig::API_ACCOUNT_VERIFY_CREDENTIALS . "." . $this->mExt, 'GET');
    }

    //获取用户勋章列表
    function badges($uid = '', $count = 10, $page = 1) {
        $aParam['uid'] = $uid;
        $aParam['count'] = $count;
        $aParam['page'] = $page;
        return $this->httpRequest->send(WeiboConfig::API_BADGES_FORMAT . "." . $this->mExt, 'GET', $aParam);
    }

    //获取用户好友动态
    function friends_timeline($count = 10, $page = 1, $uid = null) {
        $aParam['count'] = $count;
        $aParam['page'] = $page;

        if ($uid) {
            $aParam['uid'] = $uid;
        }

        return $this->httpRequest->send(WeiboConfig::API_FRIENDS_TIMELINE . "." . $this->mExt, 'GET', $aParam);
    }

    public function users_checkins($uid, $page = 1, $count = 20) {
        $aParam['uid'] = $uid;
        $aParam['count'] = $count;
        $aParam['page'] = $page;
        return $this->httpRequest->send(WeiboConfig::API_USERS_CHECKINS . "." . $this->mExt, 'GET', $aParam);
    }

    public function users_unread() {
        return $this->httpRequest->send(WeiboConfig::API_USERS_UNREAD . "." . $this->mExt, 'GET');
    }

    //获取公共动态 
    public function public_timeline($count = 10, $page = 1) {
        $aParam['count'] = $count;
        $aParam['page'] = $page;
        return $this->httpRequest->send(WeiboConfig::API_PUBLIC_TIMELINE . "." . $this->mExt, 'GET', $aParam);
    }

    //获取地点动态
    public function pois_timeline($poiid, $since_id = 0, $max_id = 0, $count = 20, $page = 1, $cuid = 0) {
        $aParam['poiid'] = $poiid;
        $aParam['since_id'] = $since_id;
        $aParam['max_id'] = $max_id;
        $aParam['count'] = $count;
        $aParam['page'] = $page;
        $aParam['debug'] = 1;

        if ($cuid > 0) {
            $aParam['cuid'] = $cuid;
        }

        return $this->httpRequest->send(WeiboConfig::API_POIS_TIMELINE . "." . $this->mExt, 'GET', $aParam);
    }

    //获取地点周边动态
    public function near_timeline($long, $lat, $range = 1, $count = 20, $page = 1) {
        $aParam['long'] = $long;
        $aParam['lat'] = $lat;
        $aParam['range'] = $range;
        $aParam['count'] = $count;
        $aParam['page'] = $page;
        return $this->httpRequest->send(WeiboConfig::API_NEAR_TIMELINE . "." . $this->mExt, 'GET', $aParam);
    }

    //获取POI点的关注动态
    public function poi_follow_timeline($poiid, $count = 20, $page = 1) {
        $aParam['poiid'] = $poiid;
        $aParam['count'] = $count;
        $aParam['page'] = $page;
        global $meUserinfo;
        $aParam['uid'] = $meUserinfo['id'];
        return $this->httpRequest->send(WeiboConfig::API_POIS_FRIENDS_TIMELINE . "." . $this->mExt, 'GET', $aParam);
    }

    //获取POI点的关注的人中来过这里的人
    public function poi_follow($poiid, $count = 20, $page = 1) {
        $aParam['poiid'] = $poiid;
        $aParam['count'] = $count;
        $aParam['page'] = $page;
        global $meUserinfo;
        $aParam['uid'] = $meUserinfo['id'];
        return $this->httpRequest->send(WeiboConfig::API_PLACE_POIS_FOLLOW . "." . $this->mExt, 'GET', $aParam);
    }

    //poi点的位置相册 
    public function pois_photos($poiid, $count = 10, $page = 1, $sort = 0) {
        $aParam['poiid'] = $poiid;
        $aParam['count'] = $count;
        $aParam['page'] = $page;
        return $this->httpRequest->send(WeiboConfig::API_POIS_PHOTOS . "." . $this->mExt, 'GET', $aParam);
    }

    //获取地点信息 
    public function pois_show($poiid) {
        $aParam['poiid'] = $poiid;
        return $this->httpRequest->send(WeiboConfig::API_POIS_SHOW . "." . $this->mExt, 'GET', $aParam);
    }

    //获取附近的人 
    public function near_users($lat, $long, $count = 20, $page = 1, $range = 500, $sort = 0) {
        $aParam['lat'] = $lat;
        $aParam['long'] = $long;
        $aParam['count'] = $count;
        $aParam['page'] = $page;
        $aParam['range'] = $range;
        $aParam['sort'] = $sort;
        return $this->httpRequest->send(WeiboConfig::API_NEAR_USERS . "." . $this->mExt, 'GET', $aParam);
    }

    //获取附近的地点 
    public function near_place($lat, $long, $count = 5, $page = 1, $range = 1000, $sort = 0) {
        $aParam['lat'] = $lat;
        $aParam['long'] = $long;
        $aParam['count'] = $count;
        $aParam['page'] = $page;
        $aParam['range'] = $range;
        $aParam['sort'] = $sort;
        return $this->httpRequest->send(WeiboConfig::API_PLACE_NEARBY_POIS . "." . $this->mExt, 'GET', $aParam);
    }

    //地点签到的人列表 
    public function pois_users($poiid, $count = 20, $page = 1) {
        $aParam['poiid'] = $poiid;
        $aParam['count'] = $count;
        $aParam['page'] = $page;
        $aParam['appid'] = 100;
        //$aParam['base_app'] = 1;
        return $this->httpRequest->send(WeiboConfig::API_POIS_USERS . "." . $this->mExt, 'GET', $aParam);
        //return $this->httpRequest->send("http://place.cn/place/pois/users.".$this->mExt, 'GET', $aParam);
    }

    //获取用户好友动态
    public function place_friends_timeline($uid, $count = 20, $page = 1, $since_id = null) {
        $aParam['count'] = $count;
        $aParam['page'] = $page;
        $aParam['uid'] = $uid;

        if (null != $since_id) {
            $aParam['since_id'] = $since_id;
        }
        return $this->httpRequest->send(WeiboConfig::API_PLACE_FRIENDS_TIMELINE . "." . $this->mExt, 'GET', $aParam);
    }

    //获取地点排名
    public function place_top_rank($city = '', $count = 5, $page = 1) {
        $aParam['city'] = $city;
        $aParam['count'] = $count;
        $aParam['page'] = $page;
        return $this->httpRequest->send(WeiboConfig::API_PLACE_TOP_RANK . "." . $this->mExt, 'GET', $aParam);
    }

    //获取用户评论列表
    public function place_comments($uid, $count = 20, $page = 1) {
        $aParam['uid'] = $uid;
        $aParam['count'] = $count;
        $aParam['page'] = $page;
        return $this->httpRequest->send(WeiboConfig::API_PLACE_COMMENTS . "." . $this->mExt, 'GET', $aParam);
    }

    public function place_user_timeline($uid, $page = 1, $count = 20, $sinceID = "") {
        $aParam['uid'] = $uid;
        $aParam['count'] = $count;
        $aParam['page'] = $page;

        if ($sinceID) {
            $aParam['since_id'] = $sinceID;
        }

        return $this->httpRequest->send(WeiboConfig::API_PLACE_USER_TIMELINE . "." . $this->mExt, 'GET', $aParam);
    }

    //获取单条动态详情
    public function place_statuses_show($mid) {
        $aParam['id'] = $mid;
        return $this->httpRequest->send(WeiboConfig::API_PLACE_STATUSES_SHOW . "." . $this->mExt, 'GET', $aParam);
    }

    public function place_geocodeToAddress($lat, $lon) {
        $aParam['coordinate'] = $lon . ',' . $lat;
        return $this->httpRequest->send(WeiboConfig::API_GEOCODE_TOADDRESS . "." . $this->mExt, 'GET', $aParam);
    }

    //获取LBS用户详情
    public function place_users_show($uid) {
        $aParam['uid'] = $uid;
        return $this->httpRequest->send(WeiboConfig::API_PLACE_USERS_SHOW . "." . $this->mExt, 'GET', $aParam);
    }

    public function users_checkin_top($uid) {
        $aParam['uid'] = $uid;
        return $this->httpRequest->send(WeiboConfig::API_PLACE_USERS_CHECKIN_TOP . "." . $this->mExt, 'GET', $aParam);
    }

    //获取动态评论列表
    function place_comments_list($id, $page = 1, $count = 10) {
        $aParam['id'] = $id;
        $aParam['count'] = $count;
        $aParam['page'] = $page;
        return $this->httpRequest->send(WeiboConfig::API_PLACE_COMMENTS_LIST . "." . $this->mExt, 'GET', $aParam);
    }

    //为动态添加评论
    function place_comment_create($id, $comment, $uid) {
        $aParam['id'] = $id;
        $aParam['comment'] = $comment;
        $aParam['uid'] = $uid;
        return $this->httpRequest->send(WeiboConfig::API_PLACE_COMMENTS_CREATE . "." . $this->mExt, 'GET', $aParam);
    }

    function place_comment_to_me($uid, $page = 1, $count = 20) {
        $aParam['uid'] = $uid;
        $aParam['page'] = $page;
        $aParam['count'] = $count;
        return $this->httpRequest->send(WeiboConfig::API_PLACE_COMMENT_TO_ME . "." . $this->mExt, 'GET', $aParam);
    }

    //获取用户checkin的地点排名
    function place_checkin_top($uid) {
        $aParam['uid'] = $uid;
        return $this->httpRequest->send(WeiboConfig::API_PLACE_CHECKIN_TOP . "." . $this->mExt, 'GET', $aParam);
    }

    function place_checks($uid, $count = 20, $page = 1) {
        $aParam['uid'] = $uid;
        $aParam['count'] = $count;
        $aParam['page'] = $page;
        return $this->httpRequest->send(WeiboConfig::API_PLACE_CHECKINS . "." . $this->mExt, 'GET', $aParam);
    }

    function place_commnet_reply($id, $cid, $comment, $uid) {
        $aParam['id'] = $id;
        $aParam['cid'] = $cid;
        $aParam['comment'] = $comment;
        $aParam['uid'] = $uid;
        return $this->httpRequest->send(WeiboConfig::API_PLACE_COMMENTS_REPLY . "." . $this->mExt, 'POST', $aParam);
    }

    function place_category() {
        $aParam['uid'] = 1748619545;
        return $this->httpRequest->send(WeiboConfig::API_CATEGORY . ".php", 'GET', $aParam);
    }

    //微领地感兴趣的人的推荐
    function user_arearecom($uid, $sex, $areaid, $age) {
        $aParam['uid'] = $uid;
        $aParam['sex'] = $sex;
        $aParam['areaid'] = $areaid;
        $aParam['age'] = $age;
        $aParam['type'] = 'q';
        return $this->httpRequest->send(WeiboConfig::API_USERS_AREARECOM . ".php", 'GET', $aParam);
    }

    //批量获得地点详情
    function place_pois_show($poiid) {
        $aParam['poiid'] = $poiid;
        //$aParam['source'] = 100;
        //$aParam['base_app'] = 1;
        return $this->httpRequest->send(WeiboConfig::API_PLACE_POIS_SHOW . "." . $this->mExt, 'GET', $aParam);
    }

    //根据ip返回地理信息 
    function location_geocode_ip_to_geo($ip) {
        $aParam['ip'] = $ip;
        return $this->httpRequest->send(WeiboConfig::API_LOCATION_GEOCODE_IP_TO_GEO . "." . $this->mExt, 'GET', $aParam);
    }

    //获取用户签到过的地点列表(位置组件专用)  合并poi点之后的数据
    function users_checkin_pois($uid, $page = 1, $count = 20) {
        $aParam['uid'] = $uid;
        $aParam['count'] = $count;
        $aParam['page'] = $page;
        return $this->httpRequest->send(WeiboConfig::API_USERS_CHECKIN_POIS . "." . $this->mExt, 'GET', $aParam);
    }

    //根据id 获得mid 
    function query_mid($id, $type = 1, $is_batch = 0) {
        $aParam["id"] = $id;
        $aParam["type"] = $type;
        $aParam["is_batch"] = $is_batch;
        return $this->httpRequest->send(WeiboConfig::API_QUERY_MID . "." . $this->mExt, 'GET', $aParam);
    }

    //根据微博id获取微博信息
    function show_by_id($id, $is_encoded = 0) {
        $aParam["id"] = $id;
        $aParam["is_encoded"] = $is_encoded;
        return $this->httpRequest->send(WeiboConfig::API_STATUS_SHOW . "." . $this->mExt, 'GET', $aParam);
    }

    //跟据一组微博ID批量取得微博信息
    function show_by_ids($ids, $is_master = false, $is_encoded = 0) {
        if (is_array($ids)) {
            $ids = implode(',', $ids);
        }
        $aParam['ids'] = $ids;
        $aParam['is_encoded'] = $is_encoded;
        if ($is_master) {
            return $this->httpRequest->send(WeiboConfig::API_STATUSES_SHOWBATCH_MASTER . '.' . $this->mExt, 'GET', $aParam);
        } else {
            return $this->httpRequest->send(WeiboConfig::API_STATUSES_SHOWBATCH . '.' . $this->mExt, 'GET', $aParam);
        }
    }

    //取得短链接
    function getMiniUrl($langUrl) {
        $aParam['url_long'] = $langUrl;
        return $this->httpRequest->send(WeiboConfig::API_WEIBO_MINI_URL . '.' . $this->mExt, 'GET', $aParam);
    }

    //根据用户昵称获取uid
    function show_by_nickname($nickname) {
        $aParam["screen_name"] = $nickname;
        return $this->httpRequest->send(WeiboConfig::API_USERS_SHOW_INNER . '.' . $this->mExt, 'GET', $aParam);
    }
    
    //获取账户信息	
    public function get_account_basic($appkey='',$uid='')
    {
    	if ($uid == '')
    	{
    		return $this->httpRequest->send(WeiboConfig::API_ACCOUNT_BASIC. '.' . $this->mExt, 'GET','',$appkey);
    	}
    	else 
    	{
    		$aParam['uid'] = $uid;
    		return $this->httpRequest->send(WeiboConfig::API_ACCOUNT_BASIC. '.' . $this->mExt, 'GET',$aParam);
    	}
    }
    
    //发送私信    
    public function send_private_msg($content,$uid)
    {
    	$aParam = array();
		$aParam['uid'] = $uid;
		$aParam['text'] = $content;
    	return  $this->httpRequest->send(WeiboConfig::API_DIRECT_MESSAGE . "." . $this->mExt, 'POST', $aParam);
    }
    
    //取得短链接的总点击数
    public function get_short_url_clicks($short_url)
    {
    	$aParam = array();
    	$aParam['url_short'] = $short_url;
    	return  $this->httpRequest->send(WeiboConfig::API_SHORT_URL_CLICKS . "." . $this->mExt, 'GET', $aParam);
    }
    
    //取得短链接在微博上的微博分享数（包含原创和转发的微博） 
	public function get_shorturl_share_counts($short_url)
	{
    	$aParam = array();
    	$aParam['url_short'] = $short_url;
    	return  $this->httpRequest->send(WeiboConfig::API_SHORT_URL_SHARE_COUNTS . "." . $this->mExt, 'GET', $aParam);
   	}
    
   	//取得短链接在微博上的微博评论数 
   	public function get_shorturl_comment_counts($short_url)  	
   	{
    	$aParam = array();
    	$aParam['url_short'] = $short_url;
    	return  $this->httpRequest->send(WeiboConfig::API_SHORT_URL_COMMENT_COUNTS . "." . $this->mExt, 'GET', $aParam);
   	}
   	
   	//取得短链地区分布
   	public function get_shorturl_location($short_url)
   	{   		
    	$aParam = array();
    	$aParam['url_short'] = $short_url;
    	return  $this->httpRequest->send(WeiboConfig::API_SHORT_URL_LOCATION . "." . $this->mExt, 'GET', $aParam);
   	}
   	
   	//通过MID取ID
   	public function get_status_queryid($mid,$type=1,$isBase62=0)
   	{
    	$aParam = array();
    	$aParam['mid'] = $mid;
    	$aParam['type'] = $type;
    	$aParam['isBase62'] = $isBase62;
    	return  $this->httpRequest->send(WeiboConfig::API_STATUS_QUERYID . "." . $this->mExt, 'GET', $aParam);
   	}
   	
   	//根据微博ID返回某条微博的评论列表 
   	public function get_recomment_show($wid)
   	{
    	$aParam = array();
    	$aParam['id'] = $wid;
   	    if (isset($_COOKIE['SUP']) && isset($_COOKIE['SUE'])) 
   	    {
            return  $this->httpRequest->send(WeiboConfig::API_RECOMMENT_SHOW . "." . $this->mExt, 'GET', $aParam);
        }
    	else 
    	{
    		$user = $this->ci->config->item('WB_USER');
    		$password = $this->ci->config->item('WB_PASS');
    		return  $this->httpRequest->send(WeiboConfig::API_RECOMMENT_SHOW . "." . $this->mExt, 'GET', $aParam,$user,$password,true);
    	}
    	
   	}
   	
   	//获取官方表情
   	public function get_emotions($type='face')
   	{
   		$aParam = array();
   		$aParam['type'] = $type;
   		return  $this->httpRequest->send(WeiboConfig::API_GET_EMOTIONS . "." . $this->mExt, 'GET', $aParam);
   	}
   	
   	//添加收藏
   	public function add_favorites($id)
   	{
   		$aParam = array();
   		$aParam['id'] = $id;
   		return  $this->httpRequest->send(WeiboConfig::API_ADD_FAVORITES . "." . $this->mExt, 'POST', $aParam);
   	}
   	
   	//删除收藏
   	public function del_favorites($id)
   	{
   		$aParam = array();
   		$aParam['id'] = $id;
   		return  $this->httpRequest->send(WeiboConfig::API_DEL_FAVORITES . "." . $this->mExt, 'POST', $aParam);
   	}
   	
   	//优质粉丝搜索接口
    public function search_at_users($input){
        $aParam["q"] = $input;
        $aParam['type'] = 0;
        return $this->httpRequest->send(WeiboConfig::API_SEARCH_AT_USERS . "." . $this->mExt, 'GET', $aParam);   
    }
   	
    //批量获取短链接的富内容信息
    public function short_url_info($url_short,$mark= '')
    {
    	$aParam['url_short'] = $url_short;
    	if ($mark != '') {
    		$aParam['mark'] = $mark;
    	}
    	return $this->httpRequest->send(WeiboConfig::API_SHORT_URL_INFO . "." . $this->mExt, 'GET', $aParam);   
    }
    
    //根据ID批量获取对象的总喜欢数
    public function like_counts($object_id,$l = false)
    {
    	$aParam['object_ids'] = $object_id;
    	if ($l == true)
    	{    		
    		return $this->httpRequest->send(WeiboConfig::API_LIKE_COUNT . "." . $this->mExt, 'GET', $aParam,$this->ci->config->item('WB_USER'),$this->ci->config->item('WB_PASS'),true);
    	}
    	else 
    	{
    		return $this->httpRequest->send(WeiboConfig::API_LIKE_COUNT . "." . $this->mExt, 'GET', $aParam);
    	}
    }
    
    /**
     * 添加对象
     * Enter description here ...
     * @param unknown_type $arr
     */
    public function add_object($arr)
    {
    	$aParam['object_id'] = $arr['object_id'];
    	$aParam['object'] = json_encode($arr['object']);
    	return $this->httpRequest->send(WeiboConfig::API_ADD_OBJECT . "." . $this->mExt, 'POST', $aParam);
    	
    }
    /**
     * 检查对象是否存在
     */
    public function check_object($object_id)
    {
    	$aParam['object_id'] = $object_id;
    	return $this->httpRequest->send(WeiboConfig::API_CHECK_OBJECT . "." . $this->mExt, 'GET', $aParam);	   	
    }
    
    /**
     * 修改对象信息
     * Enter description here ...
     * @param unknown_type $arr
     */
    public function modify_object($arr)
    {
    	$aParam['object_id'] = $arr['object_id'];
    	$aParam['object'] = json_encode($arr['object']);
    	return $this->httpRequest->send(WeiboConfig::API_MODIFY_OBJECT . "." . $this->mExt, 'POST', $aParam);
    }
    
    public function like_list($object_id,$page = 1,$count = 50,$l = false)
    {
    	$aParam['object_id'] = $object_id;
    	$aParam['page'] = $page;
   		$aParam['count'] = $count; 
   		if ($l == true)
   		{
   			return $this->httpRequest->send(WeiboConfig::API_LIKE_LIST . "." . $this->mExt, 'GET', $aParam,$this->ci->config->item('WB_USER'),$this->ci->config->item('WB_PASS'),true);    	
   		}
   		else 
   		{
    		return $this->httpRequest->send(WeiboConfig::API_LIKE_LIST . "." . $this->mExt, 'GET', $aParam);    	
   		}
   	}
    
    /**
     * 我发出的喜欢列表
     */
    public function like_by_me($arr)
    {
    	$aParam = array();
    	if (isset($arr['uid']) && !empty($arr['uid']))
    	{
    		$aParam['uid'] = $arr['uid'];
    	}
    	//$aParam['uid'] = $arr['uid'];
    	$aParam['object_type'] = $arr['object_type'];
    	if (isset($arr['page']) && !empty($arr['page']))
    	{
    		$aParam['page'] = $arr['page'];
    	}
    	if (isset($arr['count']) && !empty($arr['count']))
    	{
    		$aParam['count'] = $arr['count'];
    	}
    	
    	return $this->httpRequest->send(WeiboConfig::API_LIKE_BY_ME. "." . $this->mExt, 'GET',$aParam);
    }
    
    
    /**
     * 我发出的喜欢列表ids
     */
    public function like_by_me_ids($arr)
    {
    	$aParam = array();
    	if (isset($arr['uid']) && !empty($arr['uid']))
    	{
    		$aParam['uid'] = $arr['uid'];
    	}
    	//$aParam['uid'] = $arr['uid'];
    	$aParam['object_type'] = $arr['object_type'];
    	if (isset($arr['page']) && !empty($arr['page']))
    	{
    		$aParam['page'] = $arr['page'];
    	}
    	if (isset($arr['count']) && !empty($arr['count']))
    	{
    		$aParam['count'] = $arr['count'];
    	}
    	 
    	return $this->httpRequest->send(WeiboConfig::API_LIKE_BY_ME_IDS. "." . $this->mExt, 'GET',$aParam);
    }
    
    /**
     * 给一个或多个用户发送一条新的状态通知
     */
    public function notification_send($arr)
    {
    	$aParam = array();
    	$aParam['uids'] = $arr['uids'];
    	$aParam['tpl_id'] = $arr['tpl_id'];
    	if (isset($arr['objects1']) && !empty($arr['objects1']))
    	{
    		$aParam['objects1'] = $arr['objects1'];
    	}
    	if (isset($arr['objects1_count']) && !empty($arr['objects1_count']))
    	{
    		$aParam['objects1_count'] = $arr['objects1_count'];
    	}
        if (isset($arr['objects2']) && !empty($arr['objects2']))
    	{
    		$aParam['objects2'] = $arr['objects2'];
    	}
    	if (isset($arr['objects2_count']) && !empty($arr['objects2_count']))
    	{
    		$aParam['objects2_count'] = $arr['objects2_count'];
    	}
        if (isset($arr['objects3']) && !empty($arr['objects3']))
    	{
    		$aParam['objects3'] = $arr['objects3'];
    	}
    	if (isset($arr['objects3_count']) && !empty($arr['objects3_count']))
    	{
    		$aParam['objects3_count'] = $arr['objects3_count'];
    	}
        if (isset($arr['action_url']) && !empty($arr['action_url']))
    	{
    		$aParam['action_url'] = $arr['action_url'];
    	}
    	
    	if (isset($arr['aKey']) && !empty($arr['aKey']))
    	{
    		$this->httpRequest->setSource($arr['aKey']);
    	}
    	return $this->httpRequest->send(WeiboConfig::API_NOTIFICATION_SEND. "." . $this->mExt, 'POST',$aParam,$arr['user'],$arr['password'],true);
    
    }
    
    /**
     * 喜欢某个对象
     * Enter description here ...
     * @param unknown_type $arr
     */
    public function like_update($arr)
    {
    	$aParam = array();
    	$aParam['object_id'] = $arr['object_id'];
    	if (!array_key_exists('object_type', $arr))
    	{
    		$aParam['object_type'] = 'game';
    	}
    	else 
    	{
    		$aParam['object_type'] = $arr['object_type'];
    	}
    	if (array_key_exists('with_activitie', $arr))
    	{
    		$aParam['with_activitie'] = $arr['with_activitie'];
    	}
    	return $this->httpRequest->send(WeiboConfig::API_LIKE_UPDATE. "." . $this->mExt, 'POST',$aParam);
    }
    
    /**
     *  判断某个人是否喜欢过某个对象 
     * Enter description here ...
     * @param unknown_type $arr
     */
    public function like_exist($arr)
    {
    	$aParam = array();
    	$aParam['object_id'] = $arr['object_id'];
    	if (array_key_exists('uid', $arr))
    	{
    		$aParam['uid'] = $arr['uid'];
    	}

    	return $this->httpRequest->send(WeiboConfig::API_LIKE_EXIST. "." . $this->mExt, 'GET',$aParam);
    }
    
    /**
     *  批量判断某个人是否喜欢过某个对象  
     * Enter description here ...
     * @param unknown_type $arr
     */
    public function like_exists($arr)
    {
    	$aParam = array();
    	$aParam['object_ids'] = $arr['object_ids'];
    	if (array_key_exists('uid', $arr))
    	{
    		$aParam['uid'] = $arr['uid'];
    	}

    	return $this->httpRequest->send(WeiboConfig::API_LIKE_EXISTS. "." . $this->mExt, 'GET',$aParam);
    }
    /**
     * 取消喜欢某个对象
     * Enter description here ...
     * @param unknown_type $arr
     */
    public function like_destory($arr)
    {
    	$aParam = array();
    	$aParam['object_id'] = $arr['object_id'];
    	return $this->httpRequest->send(WeiboConfig::API_LIKE_DESTORY. "." . $this->mExt, 'POST',$aParam);
    }
    
    
    /**
     * 将对象注册激活为一个专页帐号
     * Enter description here ...
     * @param unknown_type $arr
     */
    public function activate_page($arr,$is_login =false)
    {
    	$aParam = array();
    	$aParam['nickname'] = $arr['nickname'];
    	$aParam['remark_name'] = $arr['remark_name'];
    	$aParam['ptype'] = $arr['ptype'];
    	$aParam['owner_uid'] = $arr['owner_uid'];
    	$aParam['object_id'] = $arr['object_id'];
    	if (!isset($arr['category1']))
    	{
    		$aParam['category1'] = 0;
    	}
        if (!isset($arr['category2']))
    	{
    		$aParam['category2'] = 0;
    	}
    	if (!isset($arr['category3']))
    	{
    		$aParam['category3'] = 0;
    	}
    	if (!isset($arr['category4']))
    	{
    		$aParam['category4'] = 0;
    	}
    	if (!isset($arr['category5']))
    	{
    		$aParam['category5'] = 0;
    	}
    	
    	if ($is_login == true)
    	{
    		return $this->httpRequest->send(WeiboConfig::API_ACTIVATE_PAGE. "." . $this->mExt, 'POST',$aParam,$arr['user'],$arr['password'],true);
    	}
    	else 
    	{
//    		var_dump($aParam);
    		return $this->httpRequest->send(WeiboConfig::API_ACTIVATE_PAGE. "." . $this->mExt, 'POST',$aParam);
    	}
    }
    
    
    /**
     * 批量获取指定的一批用户timeline。根据传递进来的用户ids参数，获取指定用户的微博timeline
     * Enter description here ...
     * @param array $arr
     */
    public function timeline_batch($arr,$is_login= false)
    {
    	$aParam = array();
    	if (isset($arr['count']) && !empty($arr['count']))
    	{
    		$aParam['count'] = $arr['count'];
    	}
    	
    	if (isset($arr['page']) && !empty($arr['page']))
    	{
    		$aParam['page'] = $arr['page']; 
    	}
    	
    	if (isset($arr['feature']) && !empty($arr['feature']))
    	{
    		$aParam['feature'] = $arr['feature'];
    	}
    	
    	if (isset($arr['uids']) && !empty($arr['uids']))
    	{
    		$aParam['uids'] = $arr['uids'];
    	}

    	if ($is_login == true)
    	{
    		return $this->httpRequest->send(WeiboConfig::API_TIMELINE_BATCH. "." . $this->mExt, 'GET',$aParam,$arr['user'],$arr['password'],true);
    	}
    	else 
    	{
    		return $this->httpRequest->send(WeiboConfig::API_TIMELINE_BATCH. "." . $this->mExt, 'GET',$aParam); 
    	}
    	
    }
    
    /**
     * 根据ID判断单个对象信息是否存在
     */
    public function object_exist($object_id){
    	$aparam = array();
    	$aParam['object_id'] = $object_id;
    	return $this->httpRequest->send(WeiboConfig::API_OBJECT_EXISTS . "." . $this->mExt, 'GET', $aParam);
    }

    /**
     * 获取用户微博粉丝数量
     */
    public function get_user_weibo_fans($uid){
        $aparam = array();
        $aParam['uids'] = $uid;
        return $this->httpRequest->send(WeiboConfig::API_GET_WEIBO_FANS_COUNT . "." . $this->mExt, 'GET', $aParam);
    }

    
}

