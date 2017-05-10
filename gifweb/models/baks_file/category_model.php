<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );


/**
 * @Name Category_Model.php
 */
class Category_model extends MY_Model {
	protected $_table = 'gl_games';
	private $_cache_key_pre = '';
	private $_cache_expire = 3600;
	public function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":Category_model:";
		$this->load->driver ( 'cache' );
		$this->load->model('article_model');
		$this->load->model('game_model');
	}

	/**
	 * 获取游戏
	 */
	public function get_cate($cms_id, $level = 1) {
		$cache_key = $this->_cache_key_pre . "get_cate:$cms_id:$level";
		$ret = $this->cache->redis->get ( $cache_key );
		$ret && $ret = json_decode ( $ret, 1 );
		if ($ret === false) {
			$ret = array();
			$d = $this->article_model->findArticleData($cms_id);
			$classId = $this->_getFirstCateByString($d['category']);

			if (!$classId) {
				$t = $this->game_model->get_cms_info($cms_id);
				$classId = $this->_getFirstCateByString($t[0]['category']);
			}

			if ($classId) {
				$t = explode('_', $classId);
				$classId = implode('_', array_slice($t, $level));
				$t = (int) substr($t[$level], 1);
				if ($t = $this->game_model->get_game_row($t)) {
					$ret['classId'] = $classId;
					$ret['className'] = $t['cname'];
					$this->cache->redis->set($cache_key, json_encode($ret), $this->_cache_expire);
				}
			}
		}
		return $ret;
	}

	private function _getFirstCateByString($cateString) {
		$ret = '';
		if ($cateString) {
			foreach (explode('|', $cateString) as $v) {
				if ($v) {
					$ret = $v;
					break;
				}
			}
		}
		return $ret;
	}

}
