<?php
/**
 *
 * @name Cms
 * @desc null
 *
 * @author	 liule1
 * @date 2015年7月22日
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 *
 * @property	common_model	$common_model
 * @property	global_func		$global_func
 */
class Cms extends CI_Controller {
	private $_data = array ();
	/**
	 * 构造函数
	 *
	 * 登陆检验
	 */
	function __construct() {
		parent::__construct ();
		$this->load->model ( 'common_model');
		$this->db = $this->load->database('gl_app', true);
		$this->load->library ( 'global_func' );
		$this->load->driver('cache');
		error_reporting(0);

// 		$safe_key = 'SKXYUN,.JASDL;JHFQW;ELK';
// 		$t = $this->input->get_post('t');
// 		$sign = $this->input->get_post('sign');

// 		$token = md5(md5($t) . $safe_key);
// 		if ($token !== $sign) {
// 			echo 'k';
// 			exit;
// 		}
	}
	public function index() {
			$get_url = "http://wap.97973.com/glapp/get_gl_info.d.html?ids=";
			$data = $this->global_func->curl_get($get_url . 'fxirmqc5072520');
			$data && $data = json_decode($data, true);
			$this->_save_cms_image(1, $data[0]['content']);
	}

	private function _get_game_name($id) {
		if (empty($id)) {
			return '';
		}
		$sql = "SELECT game_name FROM gl_games WHERE id='$id' LIMIT 1";
		$data = $this->common_model->get_one_data_by_sql($sql);
		return empty($data) ? '' : (string)$data['game_name'];
	}
	/**
	 * 同步CMS文档
	 */
	public function sync() {
		$cms_data = json_decode ( $_REQUEST ['data'], 1 );
		$cms_id = isset ( $cms_data ['_id'] ) ? $cms_data ['_id'] : false;
		$game_id = 0;


		$log = array($cms_id);
		PLog::w_DebugLog($log);
		if (empty ( $cms_id )) {
			exit ( 'no cms id' );
		}
		$time = time ();

		$table_cms_accept_key = array (
				'title' => 'title',
				'stitle' => 'stitle',
				'pop_title' => 'popTitle',
				'author' => 'author',
				'source' => 'source',
				'other_media' => 'otherMedia',
				'cid' => 'cID',
				'cids' => 'cIDs',
				'to_somewhere' => 'toSomewhere',
				'content' => 'content',
				'tags' => 'tags',
				'summary' => 'summary',
				'weibo_summary' => 'weiboSummary',
				'rel_tags' => 'relTags',
				'smart_feed_setups' => 'smartfeedsetups',
				'app_summary' => 'appSummary',
				'category' => 'category',
				'online' => 'online',
				'm_time' => 'mTime'
		);




		$table_cms_accept_data = array ();
		foreach ( $table_cms_accept_key as $k => $v ) {
			if (!isset ( $cms_data [$v] )) {
				$table_cms_accept_data [$k] = '';
			} elseif (is_array ( $cms_data [$v] )) {
				$table_cms_accept_data [$k] = $this->decode_unicode ( json_encode ( $cms_data [$v] ) );
			} elseif ($k == 'category') {
				// game_name
				$match = array();
				$pattern = '/a(\d+)/';
				preg_match($pattern, strval($cms_data [$v]), $match);
				$game_id = empty($match[1]) ? 0 : $match[1];
				$table_cms_accept_data ['game_id'] = $game_id;
				$table_cms_accept_data['game_name'] = $this->_get_game_name($game_id);
			} else {
				$table_cms_accept_data [$k] = ( string ) $cms_data [$v];
			}
		}

		$table_accept_key = array (
				'title' => 'title',
				'stitle' => 'stitle',
				'category' => 'category'
		);
		$table_accept_data = array ();
		foreach ( $table_accept_key as $k => $v ) {
			if (!isset ( $cms_data [$v] )) {
				$table_accept_data [$k] = '';
			} elseif (is_array ( $cms_data [$v] )) {
				$table_accept_data [$k] = $this->decode_unicode ( json_encode ( $cms_data [$v] ) );
			} elseif ($k == 'category') {
					// game_name
					$match = array();
					$pattern = '/a(\d+)/';
					preg_match($pattern, strval($cms_data [$v]), $match);
					$game_id = empty($match[1]) ? 0 : $match[1];

					$table_accept_data ['game_id'] = $game_id;
			} else {
				$table_accept_data [$k] = ( string ) $cms_data [$v];
			}
		}

		$table_cms = 'gl_article_cms';
		$table = 'gl_article';
		$sql = "SELECT * FROM $table_cms WHERE cms_id='{$cms_id}' LIMIT 1";
		$old_data = $this->common_model->get_one_data_by_sql ( $sql );

		$table_cms_accept_data += array (
				'cms_id' => $cms_id
		);
		$table_accept_data += array (
				'cms_id' => $cms_id
		);
		if ($old_data) {
			// update
			$table_cms_accept_data += array (
					'update_time' => $time
			);
			$table_accept_data += array (
					'update_time' => $time
			);
			$this->common_model->db->update ( $table_cms, $table_cms_accept_data, array (
					'cms_id' => $cms_id
			) );
			$this->common_model->db->update ( $table, $table_accept_data, array (
					'cms_id' => $cms_id
			) );
		} else {
			// insert
			$table_cms_accept_data += array (
					'update_time' => $time,
					'create_time' => $time
			);
			$table_accept_data += array (
					'update_time' => $time,
					'create_time' => $time
			);
            $randsA = rand('500','3000');
            $randsB = rand('50','300');
			$table_accept_data += array (
					'virtual_mark_up_count' => $randsB,
					'virtual_browse_count' => $randsA
			);
			$this->common_model->db->insert ( $table_cms, $table_cms_accept_data );
			$this->common_model->db->insert ( $table, $table_accept_data );
		}


		// delete cache
		$cache_key = "glapp:" . ENVIRONMENT . ":games:" . $game_id;
		$this->cache->redis->delete($cache_key);
		$cache_key = "glapp:" . ENVIRONMENT . ":gl:get_cms_info_by_category:" . $game_id;
		$this->cache->redis->delete($cache_key);
		$cache_key = "glapp:" . ENVIRONMENT . ":article:findArticleData:" . $cms_id;
		$this->cache->redis->delete($cache_key);

		$this->_save_cms_image($cms_id, $cms_data['content']);


		// 搜索, 只可以放到gl.games.sina.com.cn目录下这样用！！！！
		$this->load->model('search_model');
		$this->search_model->updateEsDataFromDb($cms_id, 'news');

		echo 'success';
	}
	private function _save_cms_image($cms_id, $content) {
		$pant = "/(?:<div [^>]*class=\"img_wrapper[^>]*>.*?<img[^>]*\s+src=[\"\'](.*?)[\"\'][^>]*>(?:<span [^>]*?class=[\"\']img_descr[\"\'][^>]*?>(.*?)<\/span>)?.*?<\/div>)|(?:<p [^>]*>.*?<img[^>]*\s+src=[\"\'](.*?)[\"\'] [^>]*>(?:<span [^>]*?class=[\"\']img_descr[\"\'][^>]*?>(.*?)<\/span>)?.*?<\/p>)|(?:<img[^>]*\s+src=[\"\'](.*?)[\"\'](?:[^>]*\s+alt=[\"\'](.*?)[\"\'])?[^>]*>)/";

		$images = array();

		foreach ($content as $_content) {
			$pregData = array();
			preg_match_all($pant,$_content['content'],$pregData);

			// 取1、3 ； 2、4 有值的
			foreach ($pregData[1] as $k => $v) {
				$pregData[1][$k] = $pregData[1][$k] ? $pregData[1][$k] : ($pregData[3][$k] ? $pregData[3][$k] : $pregData[5][$k]);
			}
			foreach ($pregData[2] as $k => $v) {
				$pregData[2][$k] = $pregData[2][$k] ? $pregData[2][$k] : ($pregData[4][$k] ? $pregData[4][$k] : $pregData[6][$k]);
			}

			if ($pregData[1]) {
				foreach ($pregData[1] as $k => $v) {
					$images[] = array(
							'url' => trim($v),
							'desc' => $pregData[2][$k]
					);
				}
			}
		}

		$succ = 1;
		foreach ($images as $k => $image) {
			$repeat = 3;
			do {
				$info = @getimagesize(trim($image['url']));
			} while (empty($info) && $repeat-- > 0);

			if (empty($info)) {
				$succ = 0;
			}
			$images[$k] += array(
					'width' => $info[0],
					'height' => $info[1],
			);
		}
		if ($succ) {
			// 可能存在脚本之间的循环调用，写入一个debug key用作排除
			$cache_key = 'glapp:' . ENVIRONMENT . ':gl:sync_cms_error';
			$this->cache->redis->hDel($cache_key, $cms_id);
		} else {
			// 可能存在脚本之间的循环调用，写入一个debug key用作排除
			$cache_key = 'glapp:' . ENVIRONMENT . ':gl:sync_cms_error';
			$repeat = $this->cache->redis->hGet($cache_key, $cms_id);
			if ($repeat < 3) {
				// 写入自动同步list, 同步一次
				$cache_key = 'glapp:' . ENVIRONMENT . ':gl:listContinue';
				$this->cache->redis->sAdd($cache_key, $cms_id);
			}
		}


		$sql = "DELETE FROM gl_article_image WHERE cms_id='$cms_id'";
		$this->common_model->execute_by_sql($sql);

		if ($images) {
			$sql = "INSERT INTO gl_article_image(`cms_id`,`url`,`desc`,`height`,`width`) VALUES ";
			$values_str = '';
			foreach ($images as $v) {
				$values_str .= ",('$cms_id', '{$v['url']}', '{$v['desc']}', '{$v['height']}', '{$v['width']}')";
			}
			$sql .= substr($values_str, 1);
			$this->common_model->execute_by_sql($sql);
		}
	}
	private function decode_unicode($str) {
		return preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
				create_function(
						'$matches',
						'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'
				),
				$str);
	}


	/**
	 * CMS使用，获取攻略类型
	 */
	public function get_category()
	{
		$query = $this->input->get('keyword');
		$limit = (int)$this->input->get('limit');
		$limit < 1 && $limit = 100;
		if(isset($query) && !empty($query)){
			$stype = 'cname';
			if(preg_match("/^[a-c0-9_]+$/",$query)){
				$stype = 'id';
			}
			if($stype == 'cname'){
				$condition['where']['cname'] = array('like', "%{$query}%");
			}elseif($stype == 'id'){
				$condition['where']['cids'] = array('eq', "{$query}");
			}
		}
		$condition['table'] = 'gl_games';
		$condition['where']['display'] = array('eq', 1);//有效数据
		$condition['start'] = 0;
		$condition['limit'] = $limit;
		$condition['order'] = ' cname asc ';

		$this->load->model('common_model');
		$sql = $this->common_model->find($condition);
		$result = $this->common_model->get_data_by_sql($sql);


		$return = array();
		foreach ($result as  $_k1 => $_v1){
			$return[$_k1]['name']  = $_v1['cids'];
			$return[$_k1]['label']  = $_v1['cname'];
		}
		$arr = array(
				"status"=>0,
				"msg"=>"success",
				"data"=>array(
						"totalNum" => count($return),
						"items" => $return,
				),
		);

		echo json_encode($arr);

	}
}
