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
		$type = 1; //写死只能搜攻略
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
		$mc_key = sha1('glapp_search_list_qihu' . serialize($this->platform . $device_id . $keyword . $type));
		if($page == 1){
			$this->cache->redis->set($mc_key, time(), 180);
		}
		//获取时间戳
		$node_time = 0;
		$rs_time = $this->cache->redis->get($mc_key);
		if($rs_time != false && intval($rs_time) > 0 && strlen($rs_time) == 10 && $page > 1){
			$node_time = intval($rs_time);
		}

		switch ($type){
			case 0:exit;
				$result['data']['game'] = $this->Search->searchGame( $keyword, $page, $page_size, $this->platform );
				$result['data']['raiders']	=	array('count'=>0, 'resultList'=>array());
				$result['data']['question']	=	array('count'=>0, 'resultList'=>array());
				$result['data']['users'] 	= array('count'=>0, 'resultList'=>array());
				break;
			case 1:
				//$result['data']['game']		= array('count'=>0, 'resultList'=>array());
				$result['data']['raiders']	=	$this->Search->searchNews_for_xyd( $keyword, $related_game, $page, $page_size, $this->platform, $node_time );
				//$result['data']['question']	=	array('count'=>0, 'resultList'=>array());
				//$result['data']['users'] 	= array('count'=>0, 'resultList'=>array());
				break;
			case 2:exit;
				$result['data']['game']		=	array('count'=>0, 'resultList'=>array());
				$result['data']['raiders']	=	array('count'=>0, 'resultList'=>array());
				$result['data']['question']	=	$this->Search->searchQuestions( $keyword, $related_game, $page, $page_size, $this->platform, $node_time);
				$result['data']['users'] 	= array('count'=>0, 'resultList'=>array());
				break;
			case 3:exit;
				$result['data']['game']		=	array('count'=>0, 'resultList'=>array());
				$result['data']['raiders']	=	$this->Search->searchNews( $keyword, $related_game, 1, 10, $this->platform, $node_time );
				$result['data']['question']	=	$this->Search->searchQuestions( $keyword, $related_game, 1, 10, $this->platform, $node_time );
				$result['data']['users'] 	= array('count'=>0, 'resultList'=>array());
				break;
			case 4:exit;
				$result['data']['game']		=	$this->Search->searchGame( $keyword, $page, $page_size, $this->platform );
				$result['data']['raiders']	=	$this->Search->searchNews( $keyword, $related_game, $page, $page_size, $this->platform, $node_time );
				$result['data']['question']	=	array('count'=>0, 'resultList'=>array());
				$result['data']['users'] 	= array('count'=>0, 'resultList'=>array());
				break;
			case 5:exit;
				$result['data']['game']		=	$this->Search->searchGame( $keyword, $page, $page_size, $this->platform );
				$result['data']['raiders']	=	$this->Search->searchNews( $keyword, $related_game, $page, $page_size, $this->platform, $node_time );
				$result['data']['question']	=	$this->Search->searchQuestions( $keyword, $related_game, $page, $page_size, $this->platform, $node_time);
				$result['data']['users'] 	=  array('count'=>0, 'resultList'=>array());
				break;
			case 6:exit;
				$result['data']['game']		= array('count'=>0, 'resultList'=>array());
				$result['data']['raiders']	=	array('count'=>0, 'resultList'=>array());
				$result['data']['question']	=	array('count'=>0, 'resultList'=>array());
				$result['data']['users'] =$this->Search->searchUsers( $keyword, $page, $page_size, $this->platform );
				break;
			default:exit;
				$result['result'] = 1001;
				$result['message'] = 'type类型错误';
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
