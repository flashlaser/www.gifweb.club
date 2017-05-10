<?php

/*
 * 全部升级微博V4 内部接口
 * 2012.10.22  by Josephmao
 */

class WeiboConfig {

	const API_DIRECT_MESSAGE = "http://i2.api.weibo.com/2/direct_messages/new" ; //发送一条私信接口 
    const API_FRIENDSHIPS_FRIENDS = "http://i2.api.weibo.com/2/friendships/friends"; //获取用户关注列表及每个关注用户的最新一条微博
    const API_FRIENDSHIPS_FOLLOWS = "https://api.weibo.com/2/friendships/followers";    //获取用户粉丝列表
    const API_FRIENDSHIPS_FOLLOWS_ACTIVE = "http://api.weibo.com/2/friendships/followers/active";    //获取用户优质粉丝列表
    const API_FRIENDSHIPS_FOLLOWS_BILATERAL = "http://i2.api.weibo.com/2/friendships/friends/bilateral";    //获取互粉列表
	const API_FRIENDSHIPS_FOLLOWS_BILATERAL_IDS = "http://i2.api.weibo.com/2/friendships/friends/bilateral/ids";	//获取互粉用户ID列表
    const API_STATUSES_REPOST = "http://i.api.weibo.com/2/statuses/repost";  //转发一条微博
    //const API_STATUSES_UPDATE = "https://api.weibo.com/2/statuses/update";  //发一条微博
    const API_STATUSES_UPDATE = "http://i2.api.weibo.com/2/statuses/update";
    const API_STATUSES_UPDATE_WITH_PIC = "http://i2.api.weibo.com/2/statuses/upload_url_text"; //发一条带图片的微博
    const API_STATUSES_UPLOAD_WITH_PIC = "http://i2.api.weibo.com/2/statuses/upload";
    const API_STATUS_USER_TIMELINE = "https://api.weibo.com/2/statuses/user_timeline";  //获取用户最新发表的微博列表
    const API_USERS_SHOW = "http://i2.api.weibo.com/2/users/show";    //获取用户信息
    const API_USERS_SHOW_INNER = "http://i.api.weibo.com/2/users/show";
    const API_STATUSES_SHOWBATCH = "http://i2.api.weibo.com/2/statuses/show_batch";
    const API_STATUSES_SHOWBATCH_MASTER = "http://i.api.weibo.com/2/statuses/show_batch";
    const API_WEIBO_MINI_URL = "http://i2.api.weibo.com/2/short_url/shorten"; //取短链
    const API_STATUS_SHOW = "http://i2.api.weibo.com/2/statuses/show";  //根据微博id获取微博信息
    
    const API_STATUSES_SHOW = "http://i2.api.weibo.com/2/statuses/user_timeline";    //获取用户发布的微博
    
    const API_FRIENDSHIP_CREATE = "http://i2.api.weibo.com/2/friendships/create";        //关注一个用户
    
    const API_FRIENDSHIP_DESTROY = "http://i2.api.weibo.com/2/friendships/destroy";      //取消对某用户的关注
    const API_FRIENDSHIP_SHOW = "http://i2.api.weibo.com/2/friendships/show";         //返回两个用户关系的详细情况V4
    const API_STATUSES_COMMENTS = "http://i2.api.weibo.com/2/comments/show";          //根据微博ID返回某条微博的评论列表
    const API_STATUSES_COMMENTS_BY_ME = "http://i2.api.weibo.com/2/comments/by_me";          //获取当前用户发出的评论
    const API_STATUSES_COMMENTS_TO_ME = "http://i2.api.weibo.com/2/comments/to_me";          //获取当前用户收到的评论
    const API_STATUSES_COMMENT = "http://i2.api.weibo.com/2/comments/create"; //对一条微博进行评论
    const API_STATUSES_COMMENT_DESTORY = "http://i2.api.weibo.com/2/comments/destroy"; //删除评论
    const API_STATUSES_FRIENDSHIP_SHOW = "http://i2.api.weibo.com/2/friendships/show";    //获得两人的关系V4
    const API_STATUSES_REPLY = "http://i2.api.weibo.com/2/comments/reply";  //回复评论
    const API_FRIENDSHIP_BATCH = "http://i2.api.weibo.com/2/friendships/batch_exists";   //判断当前登录用户与批量提供的用户的相互关系
    
    
    /*
     * V3接口 暂未升级
     */
    const API_STATUSES_UNREAD = "http://api.t.sina.com.cn/statuses/unread";    //获取微博未读消息数
    const API_STATUSES_RESET_COUNT = "http://api.t.sina.com.cn/statuses/reset_count";  // 将当前登录用户的某种新消息的未读数为0。可以清零的计数类别有：1. 评论数，2. @me数，3. 私信数，4. 关注数 
    const API_POIS_ROUND = "http://api.t.sina.com.cn/location/pois/round";   //根据关键字和（或）分类，在中心点附近搜索，返回相关的poi点信息
    const API_ACCOUNT_VERIFY_CREDENTIALS = "http://api.t.sina.com.cn/account/verify_credentials"; //验证用户，并获取个人信息
    const API_BADGES_FORMAT = "http://api.t.sina.com.cn/badges"; //根据中心点生成静态地图的图片
    //const API_MAP_IMAGE = "http://api.t.sina.com.cn/location/base/get_map_image";	//根据中心点生成静态地图的图片
    const API_STATUSES_COUNTS = "http://api.t.sina.com.cn/statuses/counts";   //批量获得一组微博的评论数与转发数
    const API_USERS_BATCH_SHOW = "http://i2.api.weibo.com/2/users/show_batch"; //获取批量用户信息
    
    
    
    
    
    const API_MAP_IMAGE = "http://intra.map.sina.com.cn/base/get_map_image"; //根据中心点生成静态地图的图片
    
    const API_STATUSES_SEARCH = "http://i.api.weibo.com/2/search/statuses";  //搜索某一话题下微博接口
    const API_QUERY_MID = "http://i2.api.weibo.com/2/statuses/querymid"; //更加id 获得mid

    //勋章接口
    const API_BADGES_SHOW = "http://i2.api.weibo.com/2/proxy/badges/show"; //获取某个勋章的详细信息【uid:用户id,bid:勋章id】
    const API_BADGES_COUNTS = "http://i2.api.weibo.com/2/proxy/badges/counts"; //获取某个勋章的计数数量 【code:需要查询的勋章code】
    const API_BADGES_BADGE = "http://i2.api.weibo.com/2/proxy/badges/badge"; //获取某个用户所获得的勋章列表【uid:用户id】
    
    const API_GET_BADGE = "http://api.weibo.com/2/proxy/badges/issue";  //获取勋章接口 【uids:颁发的用户uid,badge_id:勋章code】
    const API_REMOVE_BADGE = "http://i2.api.weibo.com/2/proxy/badges/remove"; //向指定的一个或多个用户收回一枚第三方应用勋章 【uids:颁发的用户uid,badge_id:勋章code】

	/**
	 *  add by guotao1@staff.sina.com.cn
	 */
    
    const API_ACCOUNT_BASIC = "http://i.api.weibo.com/2/account/profile/basic"; //获取用户基本信息 
    
    /**
     * 短链接
     */
    const API_SHORT_URL_CLICKS = "http://i.api.weibo.com/2/short_url/clicks"; //取得短链接的总点击数 
	const API_SHORT_URL_SHARE_COUNTS = "http://i.api.weibo.com/2/short_url/share/counts"; //取得短链接在微博上的微博分享数（包含原创和转发的微博） 
	const API_SHORT_URL_COMMENT_COUNTS = 'http://i.api.weibo.com/2/short_url/comment/counts';//取得短链接在微博上的微博评论数 
	const API_SHORT_URL_LOCATION = "http://i.api.weibo.com/2/short_url/locations"; //取得一个短链接点击的地区来源和数量
	
	/**
	 *  微博相关
	 */
	const API_STATUS_QUERYID = "http://i.api.weibo.com/2/statuses/queryid"; //通过mid获取id
    
	/**
	 * 评论相关
	 */
	const API_RECOMMENT_SHOW = "http://i.api.weibo.com/2/comments/show"; //根据微博ID返回某条微博的评论列表 

	/**
	 * 获取官方表情
	 */
	const API_GET_EMOTIONS = "http://i.api.weibo.com/2/emotions";
	
	/**
	 * 添加收藏接口
	 */
    const API_ADD_FAVORITES = "http://i.api.weibo.com/2/favorites/create";
    
    /**
	 * 删除收藏接口
	 */
    const API_DEL_FAVORITES = "http://i.api.weibo.com/2/favorites/destroy";
    
    /**
     *  优质粉丝搜索接口
     */
    const API_SEARCH_AT_USERS = "https://api.weibo.com/2/search/suggestions/at_users";  //优质粉丝搜索接口
    /**
     *  获取会员状态
     */
    const API_CHECK_MEMBERS = "http://i2.api.weibo.com/2/members/show";//获取会员状态
    
    /**
     * 批量获取短链接的富内容信息
     */
    const API_SHORT_URL_INFO = "http://i.api.weibo.com/2/short_url/info";//批量获取短链接的富内容信息
    
    /**
     * 根据ID批量获取对象的总喜欢数
     */
    const API_LIKE_COUNT = "http://i.api.weibo.com/2/likes/counts";//
    
    /**
     * 根据ID获取某个对象喜欢过的人及其喜欢动态列表 
     */
    const API_LIKE_LIST = "http://i2.api.weibo.com/2/likes/list";//
    
    /**
     * 喜欢某个对象
     */
    const API_LIKE_UPDATE = "http://i2.api.weibo.com/2/likes/update";
    
    /**
     * 增加对象接口
     */
    const API_ADD_OBJECT = 'http://i2.api.weibo.com/2/object/add';
    
    /**
     * 检查对象是否存在
     */
    const API_CHECK_OBJECT = "http://i.api.weibo.com/2/object/exist";
    
    /**
     * 修改对象
     */
    const API_MODIFY_OBJECT = "http://i.api.weibo.com/2/object/modify";
    
    
    /**
     * 我发出的喜欢列表
     */
    const API_LIKE_BY_ME = "http://i2.api.weibo.com/2/likes/by_me";
    
    /**
     * 我发出的喜欢列表ids 
     */
    const API_LIKE_BY_ME_IDS = "http://i2.api.weibo.com/2/likes/by_me/ids";
    
    /**
     * 判断某个人是否喜欢过某个对象
     */
    const API_LIKE_EXIST = "http://i2.api.weibo.com/2/likes/exist";
    
    /**
     * 批量判断某个人是否喜欢过某个对象 
     */
    const API_LIKE_EXISTS = "http://i2.api.weibo.com/2/likes/exists";
    
    /**
     * 取消喜欢某个对象 
     */
    const API_LIKE_DESTORY = "http://i2.api.weibo.com/2/likes/destroy";
    
    /**
     * 给一个或多个用户发送一条新的状态通知
     */
    const API_NOTIFICATION_SEND = "http://i2.api.weibo.com/2/notification/send";
    
    /**
     * 将对象注册激活为一个专页帐号
     */
    const API_ACTIVATE_PAGE = "http://i.api.weibo.com/2/object/activate_page";
    
   	/**
   	 * 批量获取指定的一批用户timeline。根据传递进来的用户ids参数，获取指定用户的微博timeline
   	 */
    const API_TIMELINE_BATCH = "http://i2.api.weibo.com/2/statuses/timeline_batch";
    
    
    /**
     * 用户关注的一组用户的ID列表
     */
    const API_FRIENDSHIPS_FRIENDS_IDS = "http://i2.api.weibo.com/2/friendships/friends/ids";
    
   	/**
   	 * 根据ID判断单个对象信息是否存在
   	 */
    const API_OBJECT_EXISTS = 'http://i2.api.weibo.com/2/object/exist';

    /**
     * 获取用户微博粉丝数量
     */
    const API_GET_WEIBO_FANS_COUNT = 'https://api.weibo.com/2/users/counts';
}
