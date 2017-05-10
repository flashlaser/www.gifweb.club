<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

/**
 * @Name	Friend_Model.php
 */
class Friend_Model extends MY_Model {
	
	private $nowtime;
	private $fansKey;
	private $friendKey;

	public function __construct() {
		parent::__construct ();
		$this->load->driver ( 'cache' );
		$this->load->library("global_func");
		
		$this->load->model('User_model','User');
		$this->nowtime = time();
		$this->fansKey	= "glapp:friends:follow_me_";
		$this->friendKey= "glapp:friends:my_follow_";
	}
	
	/**
	 * 添加好友
	 */
	public function add_friend($uid, $f_uid){
		if($uid=='' || $f_uid=='' || $uid==$f_uid)
			return false;

		// 关注状态，
		$my_f = $this->is_friend($uid, $f_uid);
		if($my_f){
			return false;
		}

		// 添加好友操作
		$set_list = array(
			'uid'			=>  intval($uid),
			'f_uid'			=>  intval($f_uid),
			'create_time'	=>  $this->nowtime,
		);
		$this->db->insert('gl_friend',$set_list);
		$this->set_cache_follow($uid, $f_uid);

		// 添加redis缓存对应信息
		$res = $this->set_cache_follow($uid, $f_uid);
		
		return $res;
	}

	/**
	 * 取消好友
	 */
	public function del_friend($uid, $f_uid){
		if($uid=='' || $f_uid=='' || $uid==$f_uid)
			return false;

		// 关注状态，
		$my_f = $this->is_friend($uid, $f_uid);
		if(!$my_f){
			return false;
		}

		// 删除好友关系
		$result = $this->db->delete( 'gl_friend', array( 'uid' => intval($uid), 'f_uid' => intval($f_uid) ) );

		// 删除redis缓存对应关系
		$res = $this->del_cache_follow($uid, $f_uid);

		return $res;
	}

	/**
	 * 判断是否好友(即是否已关注对方)
	 */
	public function is_friend($uid, $f_uid){
		if($uid=='' || $f_uid=='' || $uid==$f_uid)
			return false;
		
		// 判断缓存列表
		$friendKey = $this->friendKey . $uid;
		if( $this->cache->redis->zScore($friendKey, $f_uid) ){
			return true;
		}else{
			return $this->is_follow($uid, $f_uid);
		}
	}
	
	/**
	 * 获取总数
	 * @uid  用户uid
	 */
	public function get_friends_cnt($uid){
		$fansKey = $this->fansKey . $uid;		// 关注我的
		$followKey = $this->friendKey . $uid;	// 我关注的

		$follow_me = $this->cache->redis->zSize( $fansKey, true );
		$my_follow = $this->cache->redis->zSize( $followKey, true );

		return array('follow_me'=>$follow_me, 'my_follow'=>$my_follow);
	}

	/**
	 * 获取好友列表
	 * @type  列表类型,0-我关注的好友，1-关注我的好友
	 * @page  当前页
	 * @page_size  每页数量
	 */
	public function get_friends_list($uid, $page=1, $page_size=10, $type = 0){
		$page = intval($page) ? $page : 1;
		
		if($type == 1){
			$redisKey = $this->fansKey . $uid;		// 关注我的
		}else{
			$redisKey = $this->friendKey . $uid;	// 我关注的
		}

		// 记录总数
		$cnt = $this->cache->redis->zSize( $redisKey );
		$page_count = $cnt > 0 ? ceil($cnt/$page_size) : 1;

		// 获取关注信息
		$start = ($page - 1) * $page_size;
		$end = $page * $page_size - 1;
		$result = $this->cache->redis->zRevRange( $redisKey, $start, $end, true);
		
		$list = array();
		$i = 0;
		if($result){
			foreach($result as $k=>$v){
				$list[$i]['uid'] = $k;
				$list[$i]['add_time']= $v;
				$i++;
			}
		}

		$data['list'] = $list;
		$data['cnt']  = $cnt;
		$data['page_count'] = $page_count;
		$data['page'] = $page;

		return $data;
	}

	/**
	 * 添加redis关注好友列表
	 */
	private function set_cache_follow($uId, $fuId) {
		if (! is_numeric ( $uId ) || ! is_numeric ( $fuId ))
			return false;
		
		$friendKey = $this->friendKey . $uId;
		$fansKey = $this->fansKey . $fuId;
		
		//开启事务
		$this->cache->redis->multi();
		
		// 缓存我关注的好友列表
		$this->cache->redis->zAdd ( $friendKey, $this->nowtime, $fuId );
		
		// 缓存被关注人的关注他的好友列表
		$this->cache->redis->zAdd ( $fansKey, $this->nowtime, $uId );

		//执行
		$this->cache->redis->exec();

		$friend_num = $this->cache->redis->zSize ( $friendKey, true);

		return array('act'=>'add', 'my_follow'=>$friend_num);
	}

	/**
	 * 删除redis关注好友列表
	 */
	private function del_cache_follow($uId, $fuId) {
		if (! is_numeric ( $uId ) || ! is_numeric ( $fuId ))
			return false;
		
		$friendKey = $this->friendKey . $uId;
		$fansKey = $this->fansKey . $fuId;

		//开启事务
		$this->cache->redis->multi();
		
		// 缓存我关注的好友列表
		$this->cache->redis->zDelete ( $friendKey, $fuId );
		
		// 缓存被关注人的关注他的好友列表
		$this->cache->redis->zDelete ( $fansKey, $uId );

		//执行
		$this->cache->redis->exec();

		$friend_num = $this->cache->redis->zSize ( $friendKey, true);

		return array('act'=>'del', 'my_follow'=>$friend_num);
	}

	/**
	 * 判断是否已关注
	 */
	public function is_follow($uid, $fuid){
		if(intval($uid) < 1 || intval($fuid) < 1){
			return false;
		}

		$sql = "SELECT * FROM gl_friend WHERE uid=".intval($uid)." and f_uid=".intval($fuid)." LIMIT 1";
		$query = $this->db->query_read( $sql );
		$info = $query ? $query->row_array() : false;

		return $info && $info['id'];
	}
	
}
