<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );


/**
 * @Name Game_Model.php
 */
class Game_model extends MY_Model {
	protected $_table = 'gl_games';
	private $_cache_key_pre = '';
	private $_cache_expire = 600;
	public function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":games:";
		$this->load->driver ( 'cache' );
	}

	/**
	 * 获取游戏
	 */
	public function get_cms_game_info($ids,$return_type = 1) {
		$gameInfo = $this->get_game_row ( $ids, $this->platform );
		if ($this->platform == 'android') {
			$id = $gameInfo ['android_id'];
		} else {
			$id = $gameInfo ['ios_id'];
		}

		if($return_type == 1){
		  $rs = $this->get_cms_info ( $id );
		}else{
		  $rs = $this->get_cms_info_row ( $id );
		}
		return $rs;
	}

	/**
	 * 获取游戏
	 */
	public function get_cms_game_list_info($ids) {
		$game_list_info = $this->get_game_list_row ( $ids, $this->platform );

		$cms_id_arr = array ();
		foreach ( $game_list_info as $v ) {
			if ($this->platform == 'android') {
				$cms_id_arr [$v ['android_id']] = $v ['id'];
			} else {
				$cms_id_arr [$v ['ios_id']] = $v ['id'];
			}
		}

		$rs = $this->get_cms_list_info ( array_keys ( $cms_id_arr ) );
		$return = array ();
		if (! empty ( $rs )) {
			foreach ( $rs as $k => $v ) {
				$return [$cms_id_arr [$k]] = $v;
			}
		}
		return $return;
	}

	/**
	 * 获取游戏
	 */
	public function get_cms_game_list_info_for_wap($ids,$platform = 'ios') {
		$game_list_info = $this->get_game_list_row ( $ids, $platform);

		$cms_id_arr = array ();
		foreach ( $game_list_info as $v ) {
			if (!empty($v['android_id'])) {
				$cms_id_arr [$v ['android_id']] = $v ['id'];
			} else {
				$cms_id_arr [$v ['ios_id']] = $v ['id'];
			}
		}
		$rs = $this->get_cms_list_info ( array_keys ( $cms_id_arr ) );
		$return = array ();
		if (! empty ( $rs )) {
			foreach ( $rs as $k => $v ) {
				$return [$cms_id_arr [$k]] = $v;
			}
		}
		return $return;
	}

	// ===============================================================================//
	/**
	 * 获取cms文章内容
	 */
	public function get_cms_info($id) {
		$returnInfo = $this->_get_cms_info_from_cache ( $id );
		if (! is_array ( $returnInfo )) {
			$returnInfo = $this->_get_cms_info_from_api ( $id );
			$this->_set_cms_info_to_cache ( $id, $returnInfo );
		}
		// 兼容海波的set cache 脚本。。
		if ($returnInfo && $returnInfo['_id']) {
			$returnInfo = array($returnInfo);
		}
		return $returnInfo;
	}
	/**
	 * 获取cms文章内容[单条]
	 */
	public function get_cms_info_row($id) {
		$returnInfo = $this->_get_cms_info_from_cache ( $id );
		if (! is_array ( $returnInfo )) {
			$returnInfo = $this->_get_cms_info_from_api_row ( $id );
			$this->_set_cms_info_to_cache ( $id, $returnInfo );
		}
		if ($returnInfo && $returnInfo[0] && $returnInfo[0]['_id']) {
			$returnInfo = $returnInfo[0];
		}
		return $returnInfo;
	}
	private function _get_cms_info_from_cache($id) {
		$cache_key = $this->_cache_key_pre . "get_cms_info:$id";
		$returnInfo = $this->cache->redis->get ( $cache_key );
		$returnInfo && $returnInfo = json_decode ( $returnInfo, 1 );
		if ($returnInfo && $returnInfo['_id']) {
			$returnInfo = array($returnInfo);
		}
		return $returnInfo;
	}
	private function _set_cms_info_to_cache($id, $data) {
		$cache_key = $this->_cache_key_pre . "get_cms_info:$id";
		$this->cache->redis->set ( $cache_key, json_encode ( $data ), $this->_cache_expire );
	}
	private function _get_cms_info_from_api_row($id) {
		$url = "http://wap.97973.com/glapp/get_gl_info_row.d.html?ids=" . $id;
		$json_data = Util::curl_get_contents ( $url );
		$returnInfo = json_decode ( $json_data, true );
		return $returnInfo;
	}
	private function _get_cms_info_from_api($id) {
		$url = "http://wap.97973.com/glapp/get_gl_info.d.html?ids=" . $id;

		$repeat = 2;
		$returnInfo = false;
		while ( ! is_array ( $returnInfo ) && $repeat -- > 0 ) {
			$json_data = $this->global_func->curl_get ( $url, 5 );
			$returnInfo = json_decode ( $json_data, true );
		}

		return $returnInfo;
	}
	private function _get_cms_info_lesser_from_cache($id) {
		$cache_key = $this->_cache_key_pre . "get_cms_info_lesser:$id";
		$returnInfo = $this->cache->redis->get ( $cache_key );
		$returnInfo && $returnInfo = json_decode ( $returnInfo, 1 );
		return $returnInfo;
	}
	private function _set_cms_info_lesser_to_cache($id, $data) {
		$cache_key = $this->_cache_key_pre . "get_cms_info_lesser:$id";
		$this->cache->redis->set ( $cache_key, json_encode ( $data ), 60 );
	}
	public function get_cms_list_info($id_arr) {
		if (! is_array ( $id_arr )) {
			exit ( 'error :' . __CLASS__ . "." . __FUNCTION__ . ':' . __LINE__ );
		}
		$list_cache = array ();
		$list_db = array ();

		$need = array ();
		foreach ( $id_arr as $v ) {
			if (! preg_match ( '/^[a-zA-Z0-9]+$/', $v )) {
				continue;
			}
			$_data = $this->_get_cms_info_from_cache ( $v );
			if ($_data === false) {
				if ($this->_get_cms_info_lesser_from_cache($v) === false) {
					// 次级缓存也取不到值
					$need [] = $v;
				}
			} elseif (is_array ( $_data ) && $_data) {
				$list_cache [$_data [0] ['_id']] = $_data [0]; // info 取到 LIST。。。。
			} else {
			}
		}
		if ($need) {
			$i = 0;
			do {
				$_need = array_slice ( $need, $i, 50 );
				if (empty ( $_need )) {
					break;
				}
				$_arr = $this->_get_cms_info_from_api ( implode ( ',', $_need ) );

				if ($_arr && is_array ( $_arr )) {
					foreach ( $_arr as $v ) {
						$list_db [$v ['_id']] = $v;
					}
					$loop = true;
					$i += 50;
				} else {
					$loop = false;
				}
			} while ( $loop );

			foreach ( $need as $v ) {
				$_v = empty ( $list_db [$v] ) ? array () : array (
						$list_db [$v]
				);
				if (empty($_v)) {
					$this->_set_cms_info_lesser_to_cache($v, $_v);
				} else {
					$this->_set_cms_info_to_cache ( $v, $_v );
				}
			}
		}

		$return = array ();
		$list_cache || $list_cache = array ();
		$list_db || $list_db = array ();
		foreach ( array_merge ( $list_cache, $list_db ) as $v ) {
			$return [$v ['_id']] = $v;
		}

		return $return;
	}
	// ===============================================================================//

	/**
	 * 获取cms文章内容by category
	 */
	public function get_cms_info_by_category($category, $page, $pageSize) {
		$b = explode ( '_', $category );
		$gameId = str_replace ( 'a', '', $b [0] );
		$cache_key = $this->_cache_key_pre . "get_cms_info_by_category:$gameId";
		$hash_key = "normal:$category:$page:$pageSize";
		$returnInfo = $this->cache->redis->hGet ( $cache_key, $hash_key );
		$returnInfo && $returnInfo = json_decode ( $returnInfo, 1 );
		if (! is_array ( $returnInfo )) {
			$url = "http://wap.97973.com/glapp/get_cms_info_by.d.html?category=" . $category . "&page=" . $page . "&pageSize=" . $pageSize;
			$json_data = Util::curl_get_contents ( $url );
			$returnInfo = json_decode ( $json_data, true );
			$this->cache->redis->hSet ( $cache_key, $hash_key, json_encode ( $returnInfo ) );
			$this->cache->redis->expire ( $cache_key, $this->_cache_expire );
		}

		return $returnInfo;
	}

	/**
	 * 获取cms文章内容by category 视频特供（获取对应游戏的视频文章）
	 * by qinglu
	 */
	public function get_video_cms_info_by_category($gameId, $page, $pageSize) {
		$category = 'a' . $gameId . '_';
		$cache_key = $this->_cache_key_pre . "get_vadio_cms_info_by_category:$gameId";
		$hash_key = "normal:$category:$page:$pageSize";
		$returnInfo = $this->cache->redis->hGet ( $cache_key, $hash_key );
		$returnInfo && $returnInfo = json_decode ( $returnInfo, 1 );
		if (! is_array ( $returnInfo )) {

			$url = "http://wap.97973.com/glapp/get_cms_info_by.d.html?category=" . $category . "&mdType=录像&page=" . $page . "&pageSize=" . $pageSize;
			$json_data = Util::curl_get_contents ( $url );
			$returnInfo = json_decode ( $json_data, true );
			$this->cache->redis->hSet ( $cache_key, $hash_key, json_encode ( $returnInfo ) );
			$this->cache->redis->expire ( $cache_key, $this->_cache_expire );
		}

		return $returnInfo;
	}
	public function _aftermath($id) {
		// delete cache
		$cache_key = $this->_cache_key_pre . "get_category_row:one:$this->platform:$id";
		$this->cache->redis->delete ( $cache_key );
		$cache_key = $this->_cache_key_pre . "get_cms_info_by_category:$id";
		$this->cache->redis->delete ( $cache_key );
		return 1;
	}

	// 首页游戏推荐列表
	public function get_games_recommend() {
		$cache_key = $this->_cache_key_pre . "get_games_recommend";
		$returnInfos = $this->cache->redis->get ( $cache_key );
		$returnInfos && $returnInfos = json_decode ( $returnInfos, 1 );
		if (! is_array ( $returnInfos )) {
			$urls = "http://wap.97973.com/glapp/get_game_recommend.d.html";
			$json_data = Util::curl_get_contents ( $urls );
			$returnInfos = json_decode ( $json_data, true );
			$this->cache->redis->set ( $cache_key, json_encode ( $returnInfos ), $this->_cache_expire );
		}
		return $returnInfos;
	}
	public function get_game_list($sort = '') {
		$platform = $this->platform;
		$cache_key = $this->_cache_key_pre . "get_game_list:$platform:$sort";
		$result = $this->cache->redis->get ( $cache_key );
		$result && $result = json_decode ( $result, 1 );
		if (! is_array ( $result )) {
			$sql_info ['fields'] = 'game_name as abstitle,attention_count as attentionCount ,id ';
			$sql_info ['where'] ['parentid'] = array (
					'eq',
					0
			);
			$sql_info ['where'] ['display'] = array (
					'eq',
					1
			);
			if ($platform == 'android') { // 安卓
				$sql_info ['where'] ['android_id'] = array (
						'neq',
						''
				);
			} else { // ios
				$sql_info ['where'] ['ios_id'] = array (
						'neq',
						''
				);
			}
			if($sort!=''){
				$sql_info['start'] = 0;
				$sql_info['limit'] = 10;
			    $sql_info['order'] = ' id desc';
			}else{
			    $sql_info['order'] = ' listorder desc';
			}

			$sql = $this->find ( $sql_info );
			$rs = $this->db->query_read ( $sql );
			$result = $rs->result_array ();
			$this->cache->redis->set ( $cache_key, json_encode ( $result ), $this->_cache_expire );
		}
		return $result;
	}

	//可以获得不分平台的游戏列表(为保证APP端数据稳定，故另写一个方法 by wangbo8 2016-1-13)
	public function get_game_list_all() {
		$platform = $this->platform;
		$cache_key = $this->_cache_key_pre . "get_game_list_all:wap" . ENVIRONMENT . $platform;
		$result = $this->cache->redis->get ( $cache_key );
		$result && $result = json_decode ( $result, 1 );
		//$result = false;
		if (! is_array ( $result )) {
			$sql_info ['fields'] = 'game_name as abstitle,attention_count as attentionCount ,id ';
			$sql_info ['where'] ['parentid'] = array (
					'eq',
					0
			);
			$sql_info ['where'] ['display'] = array (
					'eq',
					1
			);
			if ($platform == 'android') { // 安卓
				$sql_info ['where'] ['android_id'] = array (
						'neq',
						''
				);
			} else if($platform == 'ios') { // ios
				$sql_info ['where'] ['ios_id'] = array (
						'neq',
						''
				);
			}
			$sql_info ['order'] = ' listorder desc';
			$sql = $this->find ( $sql_info );

			$rs = $this->db->query_read ( $sql );
			$result = $rs->result_array ();
			$this->cache->redis->set ( $cache_key, json_encode ( $result ), $this->_cache_expire );
		}
		return $result;
	}

	//通过字母获得不分平台的游戏列表(by wangbo8 2016-9-1)
	public function get_game_list_by_letter($letter, $start = 8) {
		$letter = strtolower($letter);
		$platform = $this->platform;
		$cache_key = $this->_cache_key_pre . "get_game_list_by_letter:wap" . ENVIRONMENT . $platform . $letter;
		$result = $this->cache->redis->get ( $cache_key );
		$result && $result = json_decode ( $result, 1 );

		//$result = false;

		if (! is_array ( $result )) {
			$sql_info ['fields'] = 'game_name as abstitle,attention_count as attentionCount ,id ';
			$sql_info ['where'] ['parentid'] = array (
					'eq',
					0
			);
			$sql_info ['where'] ['display'] = array (
					'eq',
					1
			);

			$sql_info ['where'] ['initial'] = array (
					'eq',
					$letter
			);

			if ($platform == 'android') { // 安卓
				$sql_info ['where'] ['android_id'] = array (
						'neq',
						''
				);
			} else if($platform == 'ios') { // ios
				$sql_info ['where'] ['ios_id'] = array (
						'neq',
						''
				);
			}
			//前八个不要
			$sql_info ['start'] = $start;
			$sql_info ['limit'] = '1000';

			$sql_info ['order'] = ' listorder desc';
			$sql = $this->find ( $sql_info );
			$rs = $this->db->query_read ( $sql );
			$result = $rs->result_array ();
			$this->cache->redis->set ( $cache_key, json_encode ( $result ), $this->_cache_expire );
		}
		return $result;
	}


	// 获取游戏推荐列表［按照热度降序排最多10条］
	public function get_recommend_game_list($ids) {
		$platform = $this->platform;
		if ($ids) {
			$sql_info ['where'] ['id'] = array (
					'not in',
					$ids
			);
		}
		if ($platform == 'android') { // 安卓
			$sql_info ['where'] ['android_id'] = array (
					'neq',
					''
			);
		} elseif ($platform) { // ios
			$sql_info ['where'] ['ios_id'] = array (
					'neq',
					''
			);
		}
		$sql_info ['where'] ['parentid'] = array (
				'eq',
				0
		);
		$sql_info ['where'] ['display'] = array (
				'eq',
				1
		);
		$sql_info ['order'] = ' weights desc';
		$sql_info ['start'] = ' 0';
		$sql_info ['limit'] = ' 20';
		$sql = $this->find ( $sql_info );
		$rs = $this->db->query_read ( $sql );
		$result = $rs->result_array ();
		return $result;
	}

	// ========================================================================================//
	public function get_game_row($id, $platform) {
		$result = $this->_get_game_row_from_cache ( $id, $platform );
		if (! is_array ( $result )) {
			$cache_key = $this->_cache_key_pre . "get_game_row:$id";
			$hash_key = "normal:$platform:$this->user_id";

			$result = $this->_get_game_row_from_db ( $id, $platform );
			$this->cache->redis->hSet ( $cache_key, $hash_key, json_encode ( $result ) );
			$this->cache->redis->expire ( $cache_key, $this->_cache_expire );
		}
		return $result;
	}
	private function _get_game_row_from_cache($id, $platform) {
		$cache_key = $this->_cache_key_pre . "get_game_row:$id";
		$hash_key = "normal:$platform:$this->user_id";
		$result = $this->cache->redis->hGet ( $cache_key, $hash_key );
		$result && $result = json_decode ( $result, 1 );
		return $result;
	}
	private function _get_game_row_from_db($id, $platform) {
		$sql_info ['fields'] = 'game_name as abstitle,attention_count as attentionCount ,android_id,ios_id,id ';
		$sql_info ['where'] ['parentid'] = array (
				'eq',
				0
		);
		$sql_info ['where'] ['display'] = array (
				'eq',
				1
		);
		if ($platform == 'android') { // 安卓
			$sql_info ['where'] ['android_id'] = array (
					'neq',
					''
			);
		} else { // ios
			$sql_info ['where'] ['ios_id'] = array (
					'neq',
					''
			);
		}
		if ($id) {
			$sql_info ['where'] ['id'] = array (
					'eq',
					intval ( $id )
			);
		}
		$sql_info ['order'] = ' listorder desc';
		$sql = $this->find ( $sql_info );
		$rs = $this->db->query_read ( $sql );
		$result = $rs->row_array ();
		return $result;
	}

	/**
	 * 根据游戏ID数组，从数据库获取对应的CMS ID
	 *
	 * @param unknown $id_arr
	 * @param unknown $platform
	 */
	public function get_game_list_row($id_arr, $platform) {
		$need = $id_arr;
		if (empty($need)) {
			return array();
		}
		$cache_key = $this->_cache_key_pre . "get_game_row:$platform:" . md5 ( implode ( ',', $need ) );
		$data = $this->cache->redis->get ( $cache_key );
		$data && $data = json_decode ( $data, true );

		if ($data === false) {
			$sql_info ['fields'] = 'game_name as abstitle,attention_count as attentionCount ,android_id,ios_id,id,url ';
			$sql_info ['where'] ['parentid'] = array (
					'eq',
					0
			);
			$sql_info ['where'] ['display'] = array (
					'eq',
					1
			);
			$sql_info ['where'] ['id'] = array (
					'in',
					$need
			);
			if ($platform == 'android') { // 安卓
				$sql_info ['where'] ['android_id'] = array (
						'neq',
						''
				);
			} else { // ios
				$sql_info ['where'] ['ios_id'] = array (
						'neq',
						''
				);
			}
			$sql_info ['order'] = ' listorder desc';
			$sql = $this->find ( $sql_info );
			$rs = $this->db->query_read ( $sql );
			$list_db = $rs->result_array ();
			$this->cache->redis->set ( $cache_key, json_encode ( $list_db ), $this->_cache_expire );

		} else {
			$list_db = $data;
		}

		$return = array ();
		$list_db || $list_db = array ();
		foreach ( $list_db as $v ) {
			$return [$v ['id']] = $v;
		}

		return $return;
	}

	// ====================================================================
	public function get_game_id($id, $platform = 'android') {
		$cache_key = $this->_cache_key_pre . "get_game_id:$platform:$id";
		$result = $this->cache->redis->get ( $cache_key );
		$result && $result = json_decode ( $result, 1 );
		if (! is_array ( $result )) {
			$sql_info ['fields'] = ' id ';
			$sql_info ['where'] ['parentid'] = array (
					'eq',
					0
			);
			$sql_info ['where'] ['display'] = array (
					'eq',
					1
			);
			if ($platform == 'android') { // 安卓
				$sql_info ['where'] ['android_id'] = array (
						'eq',
						$id
				);
			} else { // ios
				$sql_info ['where'] ['ios_id'] = array (
						'eq',
						$id
				);
			}
			$sql_info ['order'] = ' listorder desc';
			$sql = $this->find ( $sql_info );
			$rs = $this->db->query_read ( $sql );
			$result = $rs->row_array ();
			$this->cache->redis->set ( $cache_key, json_encode ( $result ), $this->_cache_expire );
		}

		return $result;
	}
	public function getRankInfo($rankId, $platform) {
		$gameInfo = $this->get_game_row ( $rankId, $platform );
		if ($platform == 'android') {
			$id = $gameInfo ['android_id'];
		} else {
			$id = $gameInfo ['ios_id'];
		}
		$returnInfo = $this->getRankInfoFromApi ( $id, $platform );
		return $returnInfo;
	}
	private function getRankInfoFromApi($rankId, $platform) {
		if ($platform == 'android') {
			// 判断是cmsid 还是did
			preg_match_all ( "/[a-z]/", $rankId, $pregData );
			if (! empty ( $pregData [0] )) { // cms id
				$url = "http://api.g.sina.com.cn/pgame/android/app_get?cms_id=" . $rankId;
			} else {
				$url = "http://api.g.sina.com.cn/pgame/android/app_get?did=" . $rankId;
			}
		} else {
			preg_match_all ( "/[a-z]+/", $rankId, $pregData );
			if (! empty ( $pregData [0] )) { // cms id
				$url = "http://api.g.sina.com.cn/pgame/appstore/app_get?cms_id=" . $rankId;
			} else { // did
				$url = "http://api.g.sina.com.cn/pgame/appstore/app_get?did=" . $rankId;
			}
		}
		$url .= "&is_index=empty&is_enable=empty";
		$json_data = Util::curl_get_contents ( $url );
		$data = json_decode ( $json_data, true );
		if ($data ['result'] == 'fail') {
			return array ();
		} else {
			return $data ['data'] [0];
		}
	}

	// =================================================================================
	/**
	 * 根据游戏名称查询攻略游戏ID
	 * @param  [type] $name [description]
	 * @return [type]       [description]
	 */
	public function get_gid_by_name($name) {
		$cache_key = $this->_cache_key_pre . "get_gid_by_name1:$name";
		$data = $this->cache->redis->get ( $cache_key );
		if ($data === false) {
			$data = $this->_get_gid_by_name_from_db($name);
			$this->cache->redis->set($cache_key, $data, $this->_cache_expire);
		}
		return (int)$data;
	}
	private function _get_gid_by_name_from_db($name) {
		$conditions = array(
			'table' => 'gl_games',
			'where' => array(
				'parentid' => 0,
				'display' => 1,
				'game_name' => $name,
			),
			'start' => 0,
			'limit' => 1,
		);
		$sql = $this->find($conditions);
		$row = $this->db->query_read($sql);
		$row = $row ? $row->row_array() : array();

		return empty($row['id']) ? 0 : $row['id'];
	}

	//获取单体游戏id列表
	public function get_single_game_id_list(){
		$cache_key = $this->_cache_key_pre . "get_single_game_id_list1";
		$data = $this->cache->redis->get ( $cache_key );
		$data && $data = json_decode($data, 1);
		//$data = false;
		if ($data === false) {
			$data = $this->_get_single_game_id_list_from_db();
			$this->cache->redis->set($cache_key, json_encode($data), $this->_cache_expire);
		}
		return $data;

	}

	//入库获取单体游戏列表
	private function _get_single_game_id_list_from_db(){
		$conditions = array(
			'table' => 'gl_version_swift_config',
			'fields' => 'game_id',
			/*
			'where' => array(
				'parentid' => 0,
				'display' => 1,
				'game_name' => $name,
			),
			'start' => 0,
			'limit' => 1,
			*/
			'group' => 'game_id',

		);
		$sql = $this->find($conditions);
		$sql = "select game_name as abstitle,attention_count as attentionCount ,id from ({$sql}) as sg left join gl_games as g on sg.game_id=g.id";

		$rs = $this->common_model->get_data_by_sql($sql);

		$returndata = array();
		//执行数据加工
		if(is_array($rs) && count($rs) > 0){
			foreach($rs as $vo){
				if($vo['abstitle'] && $vo['id']){
					$returndata['id_list'][] = $vo['id'];
					$returndata['game_info_list'][] = $vo;
				}
			}
		}

		return $returndata;
	}

	//增加通过获取各游戏CMS_id数据的游戏列表
	public function get_game_cms_id_list_all($platform = 'both') {
		$cache_key = $this->_cache_key_pre . "get_game_cms_id_list_all:xyd" . ENVIRONMENT . $platform;
		$result = $this->cache->redis->get ( $cache_key );
		$result && $result = json_decode ( $result, 1 );
		//$result = false;
		if (! is_array ( $result )) {
			$sql_info ['fields'] = 'id, ios_id, android_id';
			$sql_info ['where'] ['parentid'] = array (
					'eq',
					0
			);
			$sql_info ['where'] ['display'] = array (
					'eq',
					1
			);

			if ($platform == 'android') { // 安卓
				$sql_info ['where'] ['android_id'] = array (
						'neq',
						''
				);
			} else if($platform == 'ios') { // ios
				$sql_info ['where'] ['ios_id'] = array (
						'neq',
						''
				);
			}
			$sql_info ['order'] = ' listorder desc';
			$sql = $this->find ( $sql_info );

			$rs = $this->db->query_read ( $sql );
			$result = $rs->result_array ();
			$this->cache->redis->set ( $cache_key, json_encode ( $result ), $this->_cache_expire );
		}
		return $result;
	}



}
