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
 * @property comment_model $comment_model
 * @property	user_model	$user_model
 */
class Comment extends MY_Controller {
	public function __construct() {
		parent::__construct ();
		$this->load->model('comment_model');
		$this->load->model('user_model');
	}
	/**
	 * 获取评论列表
	 * @throws Exception
	 */
	public function get_list() {
		$mark = ( string ) $this->input->get_post ( 'newsid', true );
		$type = ( int ) $this->input->get_post ( 'newstype', true );
		$page = ( int ) $this->input->get_post ( 'page', true );
		$page_size = ( int ) $this->input->get_post ( 'count', true );
		$last_id = ( int ) $this->input->get_post ( 'max_id', true );
		
		$uid = $this->user_id;
		
		try {
			$data = array(
					'count' => 0,
					'hot_list' => array(),
					'normal_list' => array(),
			);
			if (empty($mark) || empty($type) || empty($page) || empty($page_size)) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}
			
			$page < 1 && $page = 1;
			$offsize = ($page - 1) * $page_size;
			
			$data['count'] = $this->comment_model->get_data_count($type, $mark);
			
			// 处理max_id与OFFSET
			$last_id > 0 && $offsize = 0;	// 有最后一条id, 直接取 > max_id 的page_size条数据即可
			$normar_list = $this->comment_model->get_data ( $type, $mark, $offsize, $page_size, $last_id );
			$data['normal_list'] = $this->_convert_list($normar_list);
			
			if ($uid && $type == 2) {
				// 记录用户最后一次看答案评论的时间 for 消息系统
				$this->load->model('push_message_model');
				$_push_type = 2;	// 答案
				$_push_flag = 2;	// 查看评论
				$_push_mark = $mark;
				$this->push_message_model->user_timeline($uid, $_push_type, $_push_flag, $_push_mark);
			}
			
			Util::echo_format_return(_SUCCESS_, $data);
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}
	/**
	 * 转换前端所需要格式
	 * @param unknown $list
	 */
	private function _convert_list($list) {
		is_array($list) || $list = array();
		
		$return = array();
		foreach ($list as $k => $v) {
			$author = $this->user_model->getUserInfoById($v['uid']);
			$area = $this->global_func->get_location($v['ip']);
			$return[] = array(
					'absId' => $v['id'],
					'abstitle' => htmlspecialchars_decode($v['content']),
					'updateTime' => date('Y-m-d H:i:s', $v['create_time']),
					'area' => (string)$area['addr'],
					'author' => array(
							'guid' => $author['uid'],
							'nickName' => $author['nickname'],
							'headImg' => $author['avatar'],
							'uLevel' => (int)$author['level'],
							'medalLevel' => (int)$author['rank'],
					)
			);
		}
		
		return $return;
	}
	
	
	// ----------------------------------------------------------------------------------------------------------
	
	/**
	 * 评论
	 * @throws Exception
	 */
	public function add() {
		$mark = ( string ) $this->input->get_post ( 'newsid', true );
		$type = ( int ) $this->input->get_post ( 'newstype', true );
		$uid = ( int ) $this->input->get_post ( 'guid', true );
		$content = ( string ) $this->input->get_post ( 'content', false, false );
		try {
			if (empty($mark) || empty($type) || empty($uid) || empty($content)) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}
			
			if ($this->common_model->is_ban_user()) {
				throw new Exception(_BANNED_MSG_, _USER_BANNED_);
			}
			$content = $this->common_model->filter_content($content);
			if (!$content) {
				throw new Exception('您可能输入了非法字符，请修改！', _DATA_ERROR_);
			}
				
			$s = $this->comment_model->add_data($uid, $type, $mark, $content);
			if (!$s) {
				throw new Exception('数据错误', _DATA_ERROR_);
			}

			Util::echo_format_return(_SUCCESS_, array(), '评论成功');
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}
	
	
	
	
	
}

