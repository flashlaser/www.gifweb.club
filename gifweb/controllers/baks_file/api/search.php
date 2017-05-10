<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * API-用户操作
 *                 
 * @author haibo8, <haibo8@staff.sina.com.cn>
 * @version   $Id: user.php 2015-07-20 14:52:27 haibo8 $
 * @copyright (c) 2015 Sina Game Team.
 */
class Search extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Search_Model', 'Search');
		$this->load->model('Common_model','Comm');
		$this->load->driver('cache');
	}

	/**
	 * APP搜索
	 *
	 * @param int $type $_GET
	 * @param string $keyword $_GET
	 * @param string $relatedGame $_GET
	 * @param int $page $_GET
	 * @param int $count $_GET
	 * @param string $max_id $_GET
	 * @return json
	 */
	public function index()
	{

		$this->load->library ( 'SSOServer/SSOServer','ssosever' );

        $type	  = intval( $this->input->get_post('type',true) );
		$keyword  = trim( $this->input->get_post('keyword',true) );
		$related_game = intval( $this->input->get_post('relatedGame',true) );
		$page = intval( $this->input->get_post('page',true) );
		$page_size = intval( $this->input->get_post('count',true) );
		$max_id = trim( $this->input->get_post('max_id',true) );
		$device_id = trim( $this->input->get('deviceId',true) );
		
		$page = $page < 1 ? 1 : $page;
		$page_size = ($page_size < 1 || $page_size > 50) ? 10 : $page_size;
		
		$result = array('result'=>200,'message'=>'success', 'data'=>'');

		// 防止翻页数据重复问题，记录用户请求第一页时间戳
		$mc_key = sha1('glapp_search_list_' . serialize($this->platform . $device_id . $keyword . $type));
		if($page == 1){
			$this->cache->redis->save($mc_key, time(), 180);
		}
		//获取时间戳
		$node_time = 0;
		$rs_time = $this->cache->redis->get( $mc_key );
		if($rs_time != false && intval($rs_time) > 0 && strlen($rs_time) == 10 && $page > 1){
			$node_time = intval($rs_time);
		}

		switch ($type){
			case 0:
				$result['data']['game'] = $this->Search->searchGame( $keyword, $page, $page_size, $this->platform );
				$result['data']['raiders']	=	array('count'=>0, 'resultList'=>array());
				$result['data']['question']	=	array('count'=>0, 'resultList'=>array());
				$result['data']['users'] 	= array('count'=>0, 'resultList'=>array());
				break;
			case 1:
				$result['data']['game']		= array('count'=>0, 'resultList'=>array());
				$result['data']['raiders']	=	$this->Search->searchNews( $keyword, $related_game, $page, $page_size, $this->platform, $node_time );
				$result['data']['question']	=	array('count'=>0, 'resultList'=>array());
				$result['data']['users'] 	= array('count'=>0, 'resultList'=>array());
				break;
			case 2:
				$result['data']['game']		=	array('count'=>0, 'resultList'=>array());
				$result['data']['raiders']	=	array('count'=>0, 'resultList'=>array());
				$result['data']['question']	=	$this->Search->searchQuestions( $keyword, $related_game, $page, $page_size, $this->platform, $node_time);
				$result['data']['users'] 	= array('count'=>0, 'resultList'=>array());
				break;
			case 3:
				$result['data']['game']		=	array('count'=>0, 'resultList'=>array());
				$result['data']['raiders']	=	$this->Search->searchNews( $keyword, $related_game, 1, 10, $this->platform, $node_time );
				$result['data']['question']	=	$this->Search->searchQuestions( $keyword, $related_game, 1, 10, $this->platform, $node_time );
				$result['data']['users'] 	= array('count'=>0, 'resultList'=>array());
				break;
			case 4:
				$result['data']['game']		=	$this->Search->searchGame( $keyword, $page, $page_size, $this->platform );
				$result['data']['raiders']	=	$this->Search->searchNews( $keyword, $related_game, $page, $page_size, $this->platform, $node_time );
				$result['data']['question']	=	array('count'=>0, 'resultList'=>array());
				$result['data']['users'] 	= array('count'=>0, 'resultList'=>array());
				break;
			case 5:
				$result['data']['game']		=	$this->Search->searchGame( $keyword, $page, $page_size, $this->platform );
				$result['data']['raiders']	=	$this->Search->searchNews( $keyword, $related_game, $page, $page_size, $this->platform, $node_time );
				$result['data']['question']	=	$this->Search->searchQuestions( $keyword, $related_game, $page, $page_size, $this->platform, $node_time);
				$result['data']['users'] 	=  array('count'=>0, 'resultList'=>array());
				break;
			case 6:
				$result['data']['game']		= array('count'=>0, 'resultList'=>array());
				$result['data']['raiders']	=	array('count'=>0, 'resultList'=>array());
				$result['data']['question']	=	array('count'=>0, 'resultList'=>array());
				$result['data']['users'] =$this->Search->searchUsers( $keyword, $page, $page_size, $this->platform );
				break;
			default:
				$result['result'] = 1001;
				$result['message'] = 'type类型错误';
		}


		Util::echo_format_return($result['result'], $result['data'], $result['message'], true);

	}
	
	
	// ================================================== review remap =====================================================//
	/**
	 * APP搜索
	 *
	 * @param int $type $_GET
	 * @param string $keyword $_GET
	 * @param string $relatedGame $_GET
	 * @param int $page $_GET
	 * @param int $count $_GET
	 * @param string $max_id $_GET
	 * @return json
	 */
	public function index_review_remap()
	{
		
		$this->load->library ( 'SSOServer/SSOServer','ssosever' );

        $type	  = intval( $this->input->get_post('type',true) );
		$keyword  = trim( $this->input->get_post('keyword',true) );
		$related_game = intval( $this->input->get_post('relatedGame',true) );
		$page = intval( $this->input->get_post('page',true) );
		$page_size = intval( $this->input->get_post('count',true) );
		$max_id = trim( $this->input->get_post('max_id',true) );
		$device_id = trim( $this->input->get('deviceId',true) );
		
		$page = $page < 1 ? 1 : $page;
		$page_size = ($page_size < 1 || $page_size > 50) ? 10 : $page_size;
		
		$limit = 20;
		
		$result = array('result'=>200,'message'=>'success', 'data'=>'');

		// 防止翻页数据重复问题，记录用户请求第一页时间戳
		$mc_key = sha1('glapp_search_list_review' . ENVIRONMENT . serialize($this->platform . $device_id . $keyword . $type));
		if($page == 1){
			$this->cache->redis->save($mc_key, time(), 180);
		}
		//获取时间戳
		$node_time = 0;
		$rs_time = $this->cache->redis->get( $mc_key );
		if($rs_time != false && intval($rs_time) > 0 && strlen($rs_time) == 10 && $page > 1){
			$node_time = intval($rs_time);
		}

		switch ($type){
			case 0:
				$result['data']['game'] = $this->Search->searchGame( $keyword, $page, $limit, $this->platform );
				$result['data']['raiders']	=	array('count'=>0, 'resultList'=>array());
				$result['data']['question']	=	array('count'=>0, 'resultList'=>array());
				$result['data']['users'] 	= array('count'=>0, 'resultList'=>array());
				break;
			case 1:
				$result['data']['game']		= array('count'=>0, 'resultList'=>array());
				$result['data']['raiders']	=	$this->Search->searchNews( $keyword, $related_game, $page, $limit, $this->platform, $node_time );
				$result['data']['question']	=	array('count'=>0, 'resultList'=>array());
				$result['data']['users'] 	= array('count'=>0, 'resultList'=>array());
				break;
			case 2:
				$result['data']['game']		=	array('count'=>0, 'resultList'=>array());
				$result['data']['raiders']	=	array('count'=>0, 'resultList'=>array());
				$result['data']['question']	=	$this->Search->searchQuestions( $keyword, $related_game, $page, $limit, $this->platform, $node_time);
				break;
			case 3:
				$result['data']['game']		=	array('count'=>0, 'resultList'=>array());
				$result['data']['raiders']	=	$this->Search->searchNews( $keyword, $related_game, 1, 10, $this->platform, $node_time );
				$result['data']['question']	=	$this->Search->searchQuestions( $keyword, $related_game, 1, 10, $this->platform, $node_time );
				$result['data']['users'] 	= array('count'=>0, 'resultList'=>array());
				break;
			case 4:
				$result['data']['game']		=	$this->Search->searchGame( $keyword, $page, $limit, $this->platform );
				$result['data']['raiders']	=	$this->Search->searchNews( $keyword, $related_game, $page, $limit, $this->platform, $node_time );
				$result['data']['question']	=	array('count'=>0, 'resultList'=>array());
				$result['data']['users'] 	= array('count'=>0, 'resultList'=>array());
				break;
			case 5:
				$result['data']['game']		=	$this->Search->searchGame( $keyword, $page, $limit, $this->platform );
				$result['data']['raiders']	=	$this->Search->searchNews( $keyword, $related_game, $page, $limit, $this->platform, $node_time );
				$result['data']['question']	=	$this->Search->searchQuestions( $keyword, $related_game, $page, $limit, $this->platform, $node_time);
				$result['data']['users'] 	=  array('count'=>0, 'resultList'=>array());
				break;
			case 6:
				$result['data']['game']		= array('count'=>0, 'resultList'=>array());
				$result['data']['raiders']	=	array('count'=>0, 'resultList'=>array());
				$result['data']['question']	=	array('count'=>0, 'resultList'=>array());
				$result['data']['users'] =$this->Search->searchUsers( $keyword, $page, $page_size, $this->platform );
			default:
				$result['result'] = 1001;
				$result['message'] = 'type类型错误';
		}
		
		
		
		
		$this->load->model('question_model');
		$this->load->model('question_content_model');
		$this->load->model('answer_model');
		$this->load->model('answer_content_model');
		$this->load->model('game_model');
		
		if ($result['data']['game']['resultList']) {
			$data = array();
			foreach ($result['data']['game']['resultList'] as $k => $v) {
				if ($this->_ban($v['abstitle'])) {
					continue;
				}
				$data[] = $v;
			}
			count($data) > $page_size - 1 && $data = array_slice($data, 0 , $page_size - 1);
			$result['data']['game']['resultList'] = $data;
			$result['data']['game']['count'] = count($data);
		}
		
		if ($result['data']['raiders']['resultList']) {
			$data = array();
			foreach ($result['data']['raiders']['resultList'] as $k => $v) {
				if ($this->_ban($v['abstitle'])) {
					continue;
				}
				$info = $this->game_model->get_cms_info($v['absId']);
				if ($this->_ban($info[0]['content'][0]['content'])) {
					continue;
				}
				
				$data[] = $v;
			}
			
			count($data) > $page_size - 1 && $data = array_slice($data, 0 , $page_size - 1);
			$result['data']['raiders']['resultList'] = $data;
			$result['data']['raiders']['count'] = count($data);
		}
		
		if ($result['data']['question']['resultList']) {
			$data = array();
			foreach ($result['data']['question']['resultList'] as $k => $v) {
				if ($this->_ban($v['abstitle'])) {
					continue;
				}
				// 问题内容
				//$v['absId'] = 1;
				$str = $this->question_content_model->get_content($v['absId']);
				if ($this->_ban($str)) {
					continue;
				}
				
				$list = $this->answer_model->get_list($v['absId'], 0, 999);
				$flag = true;
				foreach ($list as $lv) {
					$str = $this->answer_content_model->get_content($lv['aid']);
					if ($this->_ban($str)) {
						$flag = false;
						break;
					}
				}
				
				if (!$flag) {
					continue;
				}
				
				$data[] = $v;
			}
			count($data) > $page_size - 1 && $data = array_slice($data, 0 , $page_size - 1);
			$result['data']['question']['resultList'] = $data;
			$result['data']['question']['count'] = count($data);
		}
		

		Util::echo_format_return($result['result'], $result['data'], $result['message'], true);

	}
	
	private function _ban($content) {
		// 过滤
		static $patterns = array(
			'360',
			'应用宝',
			'百度',
			'豌豆荚',
			'安智',
			'小米',
			'木蚂蚁',
			'机锋',
			'OPPO',
			'华为',
			'联想',
			'安卓',
			'android',
			'礼包',
			'新手包',
			'道具包',
			'领号',
			'福利',
			'福袋',
			'特权',
			'好礼',
			'礼品',
			'新手卡',
			'激活码',
			'测试码',
			'封测码',
			'内测码',
			'道具卡',
			'首测码',
			'专属卡',
			'媒体卡',
			'特典卡',
			'内测卡',
			'渠道',
			'金卡',
			'银卡',
			'白金卡',
			'钻石卡',
		);
		$flag = false;
		foreach ($patterns as $p) {
			$patt = '/' . preg_quote($p ) . '/uis';
			if (preg_match($patt, $content )) {
				$flag = true;
				break;
			}
		}
		
		return $flag;
	}
}

/* End of file user.php */
/* Location: ./application/controllers/api/user.php */
