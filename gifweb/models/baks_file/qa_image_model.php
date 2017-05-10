<?php
/**
 * 
 * @name Qa_image_model
 * @desc null
 *
 * @author	 liule1
 * @date 2015年8月6日
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 */
class Qa_image_model extends MY_Model {
	private $_cache_key_pre = '';
	private $_cache_expire = 600;
	protected $_table = 'gl_question_answer_image';
	
	private $_new_frontend_ids = array();	// 新图片的id
	
	function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":qa_image:";
		$this->load->model('user_model');
	}
	// ---------------------------------------------------------------------------------- //
	// ---------------------------------------------------------------------------------- //
	public function get_list($type, $mark, $status = 1) {
		if (empty($type) || empty($mark)) {
			return false;
		}
		
		$cache_key = $this->_cache_key_pre . "$type:$mark";
		$hash_key = "list:$status";
		$data = $this->cache->redis->hGet($cache_key, $hash_key);
		$data && $data = json_decode($data, 1);
		if (!is_array($data)) {
			$data = $this->get_list_from_db($type, $mark, $status);
			$this->cache->redis->hSet($cache_key, $hash_key, json_encode($data));
			$this->cache->redis->expire($cache_key, $this->_cache_expire);
		}
		
		return $data;
	}
	public function get_list_from_db($type, $mark, $status = 1) {
		$conditions = array(
				'where' => array(
						'type' => $type, 
						'mark' => $mark,
						'status' => $status,
				)
		);
		$sql = $this->find($conditions);
		$rs = $this->db->query_read($sql);
		return $rs ? $rs->result_array() : array();
	}
	
	
	// ---------------------------------------------------------------------------------- //
	public function get_list_count($type, $mark, $status = 1) {
		if (empty($type) || empty($mark)) {
			return false;
		}
		
		$cache_key = $this->_cache_key_pre . "$type:$mark";
		$hash_key = "count:$status";

		$data = $this->cache->redis->hGet($cache_key, $hash_key);
		if (!is_array($data)) {
			$data = $this->get_list_count_from_db($type, $mark, $status);
			$this->cache->redis->hSet($cache_key, $hash_key, $data);
			$this->cache->redis->expire($cache_key, $this->_cache_expire);
		}
		
		return (int)$data;
	}
	
	public function get_list_count_from_db($type, $mark, $status = 1) {
		$sql = "SELECT count(*) AS c FROM {$this->_table} WHERE type='$type' AND mark='$mark' AND status='$status'";
		$rs = $this->db->query_read($sql);
		$rs = $rs ? $rs->row_array() : array();
		return empty($rs) ? 0 : $rs['c'];
	}
	
	// ------------------------------------------------------------------------------------//
	
	public function convert_id_to_frontend($id) {
		$id = (int) $id;
		return "[!--IMG_{$id}--]";
	}
	public function convert_id_to_backend($id) {
		$pattern = '/\[!--IMG_(\d+)--\]/';
		
		
		
	}
	/**
	 * 初始化 content, 如发现有图的，则修改content
	 * @param unknown $uid
	 * @param unknown $type
	 * @param unknown $mark
	 * @param unknown $content
	 * 
	 * @return	array : images id
	 */
	public function init_content_image($uid, $type, $mark, &$content) {
		// 查找出新图片的数量
		$match = $this->_detect_content_new_image($content);
		$return = array(); 
		$count = count(array_keys($match, 0));
		if ($count) {
			$frontend_ids = array();
			for ($i = 0; $i < $count; $i++) {
				$insert_id = $this->insert($uid, $type, $mark);
				$return[] = (string)$insert_id;
				$frontend_ids[] = $this->convert_id_to_frontend($insert_id);
			}
			// 替换内容中的 [!--IMG_0--]
			$content = $this->_replace_content_image_mark($content, $frontend_ids);
		}
		// 给其他不用的image status 赋值0
		$not_in = array_merge(array_unique($match) , $return);
		$this->_offline_image($type, $mark, $not_in);
		
		return $return;
	}
	
	private function _detect_content_new_image($content) {
		$pattern = '/\[!--IMG_(\d)+--\]/';
		preg_match_all($pattern, $content, $match);
		return empty($match[1]) ? array() : $match[1];
	}
	private function _replace_content_image_mark($content, $frontend_ids) {
		$this->_new_frontend_ids = $frontend_ids;
		$pattern = '/\[!--IMG_0--\]/';
		return preg_replace_callback($pattern, 
				array($this, '_replace_content_image_mark_callback')
				, $content);
	}
	private function _replace_content_image_mark_callback() {
		$id = array_shift($this->_new_frontend_ids);
		return $id;
	}
	
	// ---------------------------------------------------------------------------------------------------//
	/**
	 * 不用的image的status更新成0
	 * @param unknown $type
	 * @param unknown $mark
	 * @param unknown $not_in
	 */
	private function _offline_image($type, $mark, $not_in) {
		$this->_offline_image_to_db($type, $mark, $not_in);
		$cache_key = $this->_cache_key_pre . "$type:$mark";
		$this->cache->redis->delete($cache_key);
		return 1;
	}
	private function _offline_image_to_db($type, $mark, $not_in) {
		$where = "";
		if (!empty($not_in)) {
			$where = " AND id NOT IN (" . implode(',', $not_in) . ")";
		}
		
		$sql = "UPDATE {$this->_table} SET status=0 WHERE type='$type' AND mark='$mark' $where";
		$this->db->query_write($sql);
		return 1;
	}
	// ---------------------------------------------------------------------------------------------------//
	public function insert($uid, $type, $mark, $url = '', $width = '', $height = '') {
		if (empty($uid) || empty($type) || !is_numeric($mark) ) {
			return false;
		}
		
		return $this->_insert_to_db($uid, $type, $mark, $url, $width, $height);
	}
	private function _insert_to_db($uid, $type, $mark, $url = '', $width = '', $height = '') {
		if (empty($uid) || empty($type) || !is_numeric($mark) ) {
			return false;
		}
		
		$time = time();
		$insert_data = array(
				'uid' => $uid,
				'type' => $type, 
				'mark' => $mark,
				'url' => $url,
				'width' => $width,
				'height' => $height,
				'update_time' => $time,
				'create_time' => $time,
				'status' => 1,
		);
		
		$this->db->insert($this->_table, $insert_data);
		
		return $this->db->insert_id_write();
	}
	
	// ---------------------------------------------------------------------------------------------------//
	public function update($uid, $id, $update_data) {
		if (empty($uid) || empty($id) || empty($update_data) ) {
			return false;
		}
	
		return $this->_update_to_db($uid, $id, $update_data);
	}
	
	private function _update_to_db($uid, $id, $update_data) {
		if (empty($uid) || empty($id) || empty($update_data) ) {
			return false;
		}
	
		$time = time();
		$update_data = array(
				'update_time' => $time,
				'status' => 1,
		) + $update_data;
		
		$this->db->update($this->_table, $update_data, array('id' => $id, 'uid' => $uid));
	
		return 1;
	}
	// ---------------------------------------------------------------------------------------------------//
	
	public function upload_img($id, $file) {
		$return = array(
				'code' => 0,
				'msg' => '',
				'data' => ''
		);
		try {
			//文件类型
			$uptypes = array(
					'image/jpg' => 'jpg',
					'image/png' => 'png',
					'image/gif' => 'gif',
					'image/jpeg' => 'jpeg',
					'image/bmp' => 'bmp',
			);
			$max_file_size = 5000000;   //文件大小限制1M
			if( (empty($file) || !is_uploaded_file($file['tmp_name'])) ){
				throw new Exception('not_img', -1);
			}elseif(($file['error'])){
				throw new Exception('img_err', -2);
			}elseif(!($uptypes[$file['type']]) ){
				throw new Exception('type_err', -3);
			}elseif( (@filesize($file['tmp_name']) > $max_file_size)){
				throw new Exception('max_size', -4);
			}
			
			// $this->load->library('storeage');
			$pic_path = 'glapp/qa/' . date('Ym') . '/';
			$content1 = @file_get_contents($file['tmp_name']);
			$picfile1 = $pic_path . $id . '.' . $uptypes[$file['type']];
			// $ress1 = $this->storeage->upload( $content1 , $picfile1 , $file['type'] );
			// if(!$ress1){
			// 	throw new Exception('s1_err', -10);
			// }
			 
			try {
				$CI = get_instance();
				$CI->load->config('oss_config', true);
				$config = $CI->config->item('oss_config');
				$this->load->library('OSS/oss', $config);
	
				$this->oss->putObject($this->oss->getBucketName(), $picfile1, $content1);
			} catch (OssException $e) {
				throw new Exception('s1_err', -10);
			}
			
			$return['data'] = NEW_IMG_PREFIX . $picfile1;
			
		} catch (Exception $e) {
			$return['code'] = $e->getCode();
			$return['msg'] = $e->getMessage();
		}
		return $return;
	}
	// ---------------------------------------------------------------------------------------------------//
	public function check_onwership($uid, $ids) {
		if (empty($uid) || empty($ids)) {
			return false;
		}
		is_array($ids) || $ids = array($ids);
		$conditions = array(
				'table' => $this->_table,
				'fields' => 'count(*) as c',
				'where' => array(
						'uid' => $uid,
						'id' => array(
								'in' , $ids
						)
				)
		);
		
		$sql = $this->find($conditions);
		$rs = $this->db->query_read($sql);
		$rs = $rs->row_array();
		
		return (int)$rs['c'] === count($ids) ? true : false; 
	}
	// ---------------------------------------------------------------------------------- //

	public function get_content($id) {

		$data = $this->_get_content_from_db($id);

		return $data;
	}
	public function _get_content_from_db($id) {
		$conditions = array(
			'table' => $this->_table,
			'where' => array(
				'id' => intval($id),
				'status' => 1
			),
			'limit' => 1,
		);

		$sql = $this->find($conditions);
		$rs = $this->db->query_read($sql);
		$rs = $rs ? $rs->row_array() : array();
		return empty($rs) ? '' : $rs;
	}

	/**
	 * 转换成<img src='' />格式
	 **/
	public function changeImgStr($str)
	{
		$content=$str;
		$pattern = '/\[!--IMG_(\d+)--\]/';
		preg_match_all($pattern,$content,$result);
		$new_str = '';
		$new_str = $str;
		foreach($result[0] as $k=>$v)
		{
			$whereArr['id'] = $result[1][$k];
			$patterns = '/\[!--IMG_('.$whereArr['id'].')--\]/';//每条匹配
			
			$res_img_data = $this->get_content($whereArr['id']);//返回图片数据
			
			if ($res_img_data['url']) {
				// $res_w_h = getimagesize($domin.$res_img_data['url']);
				$res_w_h = @getimagesize(gl_img_url($res_img_data['url']));
				if($res_w_h[0]>290)//如果宽度大于默认宽度 显示时压缩宽度比例
				{
					$w = 290;
				}
				else
				{
					$w = $res_w_h[0];
				}
				// $new_str = preg_replace($patterns, "<br/><img width='".$w."px'  src='".$domin.$res_img_data['url']."' /><br/>", $new_str);
				$new_str = preg_replace($patterns, "<br/><img width='".$w."px'  src='".gl_img_url($res_img_data['url'])."' /><br/>", $new_str);
			} else {
				$new_str = preg_replace($patterns, "", $new_str);
			}
			
		}
		return array('content'=>$new_str);
	}

}
