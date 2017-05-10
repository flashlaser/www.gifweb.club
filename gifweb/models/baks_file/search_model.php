<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

/**
 * @Name	Search_Model.php
 */
class Search_Model extends MY_Model {

	/* 登录渠道 */
	private $ch_info = array();

	public function __construct() {
		parent::__construct ();
		$this->fld_cacheKey = 'glapp:users:';
		$this->load->driver ( 'cache' );
		$this->load->model('Game_model','Game');
		$this->load->model('Elastic_model', 'Es');
		$this->load->model('Qa_model', 'Qa');
		$this->load->model('Article_model', 'At');
		$this->load->model('Question_model', 'Qu');
		$this->load->library("global_func");

	}

	/**
	 * 游戏搜索
	 *
	 */
	public function searchGame( $keyword, $page=1, $pageSize=10, $sys = 'ios'){
		$es_index = "gl_app_index";
		$es_type  = "glapp_gl_game_type";
		// 单字段查询
		//$rs = $this->Es->searchByColumn( $es_index, $es_type, 'game_name', $keyword, $page, $pageSize, 1 );

		// 多字段查询
		$condition['like'] = array('game_name'=>$keyword,'en_game'=>$keyword);
		$condition['where'] = array('display' => 1);
		$rs = $this->Es->searchByColumns( $es_index, $es_type, $condition, 0, $page,$pageSize, 2 );
		$list = array();
		$count= 0;
		$i = 0;
		// 格式化数据
		if($rs && $rs['hits']['total'] > 0){
			foreach($rs['hits']['hits'] as $k=>$v){
				if($sys == 'ios'){
					$game_info = $this->Game->get_cms_info($v['_source']['ios_id']);
				}elseif($sys == 'android'){
					$game_info = $this->Game->get_cms_info($v['_source']['android_id']);
				}else{
				   $game_info = $this->Game->get_cms_info($v['_source']['android']);
				   if(!$game_info){
						$game_info = $this->Game->get_cms_info($v['_source']['ios_id']);
				   }
				}

				if(empty($game_info[0]['logo'])){
					continue;
				}
				$list[$i]['absId'] = $v['_id'];
				$list[$i]['abstitle'] = $v['_source']['game_name'];
				$list[$i]['absImage'] = empty($game_info[0]['logo']) ? '' : $game_info[0]['logo'];
				$list[$i]['attentionCount'] = $v['_source']['attention_count'];
				$i++;
			}

			$count = intval( $rs['hits']['total'] );
		}

		$jsonData['count'] = $count;
		$jsonData['resultList'] = $list;

		return $jsonData;
	}

	/**
	 * 攻略搜索
	 *
	 */
	public function searchNews( $keyword, $game_id, $page=1, $pageSize=10, $sys = 'ios', $node_time = 0 ){
		$es_index = "gl_app_index";
		$es_type  = "glapp_gl_article_cms_type";
		if(empty($keyword)){
			$condition = '';
		}else{
			$condition['like'] = array('title'=>$keyword,'game_name'=>$keyword,'tags'=>$keyword);
		}
		// 设置过滤条件
		if($game_id > 0){
			$condition['where'] = array('game_id'=>$game_id);
		}

		if($node_time > 0){
			$condition['range'] = array('update_time'=>array('lte'=>$node_time));
		}

		// 排序
		$condition['sort'] = array(array('_score'=>'desc'),array('browse_count'=>'desc'));

		// 设置es返回字段
		$this->Es->setEsSource(array('id','title','game_name','cms_id','mark_up_count','browse_count','update_time'));
		$rs = $this->Es->searchByColumns( $es_index, $es_type, $condition, 0, $page,$pageSize );

		//因英文匹配问题,如未匹配到在进行一次匹配
		if(preg_match("/^[a-zA-Z]+$/",$keyword) && intval($rs['hits']['total']) == 0){
			$rs = $this->Es->searchByColumns( $es_index, $es_type, $condition, 0, $page,$pageSize, 2 );
		}

		$list = array();
		$count= 0;
		// 格式化数据
		if($rs && $rs['hits']['total'] > 0){
			foreach($rs['hits']['hits'] as $k=>$v){
				// 关注、浏览详情
				$cms_info = $this->At->findArticleData($v['_source']['cms_id']);

				$list[$k]['absId'] = $v['_source']['cms_id'];
				$list[$k]['abstitle'] = $v['_source']['title'];
				//$list[$k]['absImage'] = 'http://s.img.mix.sina.com.cn/auto/resize?img='.$cms_info[0]['mainPic'].'&size=100_100';
				$list[$k]['scanCount'] = intval($cms_info['browse_count']);
				$list[$k]['praiseCount'] = intval($cms_info['mark_up_count']);
				$list[$k]['gameTitle'] = $v['_source']['game_name'];
				//$list[$k]['create_time'] = date('Y-m-d H:i:s', $v['_source']['update_time']);
			}

			$count = intval( $rs['hits']['total'] );
		}

		$jsonData['count'] = $count;
		$jsonData['resultList'] = $list;

		return $jsonData;
	}

	 /**
	 * 问题搜索
	 *
	 */
	public function searchQuestions( $keyword, $game_id, $page=1, $pageSize=10, $sys = 'ios', $node_time = 0 ){
		$es_index = "gl_app_index";
		$es_type  = "glapp_gl_question_type";
		if(empty($keyword)){
			$condition = '';
		}else{
			$condition['like'] = array('content'=>$keyword, 'game_name'=>$keyword);
		}
		// 设置过滤条件
		if($game_id > 0){
			$condition['where'] = array('game_id'=>$game_id);
		}

		if($node_time > 0){
			$condition['range'] = array('update_time'=>array('lte'=>$node_time));
		}

		// 排序
		$condition['sort'] = array('_score'=>'desc','pv'=>'desc');

		// 设置es返回字段
		$this->Es->setEsSource(array('qid','content','game_name','follow_count','virtual_follow_count','normal_answer_count','hot_answer_count','pv'));
		$rs = $this->Es->searchByColumns( $es_index, $es_type, $condition, 0, $page,$pageSize );

		//因英文匹配问题,如未匹配到在进行一次匹配
		if(preg_match("/^[a-zA-Z]+$/",$keyword) && intval($rs['hits']['total']) == 0){
			$rs = $this->Es->searchByColumns( $es_index, $es_type, $condition, 0, $page,$pageSize, 2 );
		}

		$list = array();
		$count= 0;
		// 格式化数据
		if($rs && $rs['hits']['total'] > 0){
			foreach($rs['hits']['hits'] as $k=>$v){
				// 关注、浏览详情
				$q_info = $this->Qu->get_info($v['_source']['qid']);
				$list[$k]['absId'] = (string)$v['_source']['qid'];
				$list[$k]['abstitle'] = $this->Qa->convert_content_to_frontend($v['_source']['content'], 50);
				$list[$k]['attentionCount'] = $q_info['follow_count'] + $q_info['virtual_follow_count'];
				$list[$k]['answerCount'] = $q_info['normal_answer_count'];
				$list[$k]['gameTitle'] = $v['_source']['game_name'];
			}

			$count = intval( $rs['hits']['total'] );
		}

		$jsonData['count'] = $count;
		$jsonData['resultList'] = $list;

		return $jsonData;
	}

	 /**
	 * 用户搜索 by wangbo8 2016-1-26    		2.0版本新增
	 *
	 */
	 public function searchUsers($keyword, $page = 1, $pageSize = 10, $sys = 'ios'){
	 	//es索引与类型
	 	$es_index = 'gl_app_index'; //索引
	 	$es_type = 'glapp_gl_users_type'; //类型

	 	//多字段查询条件
		$condition['like'] = array('nickname'=>$keyword);
		//$condition['where'] = array('display' => 1);
		$rs = $this->Es->searchByColumns( $es_index, $es_type, $condition, 0, $page,$pageSize, 2 );

		$list = array();
		$count= 0;
		$i = 0;

		// 格式化数据
		if($rs && $rs['hits']['total'] > 0){
			$count = intval( $rs['hits']['total'] );

			foreach($rs['hits']['hits'] as $k=>$v){
				$list[$i]['guid'] = $v['_source']['uid'];
				$list[$i]['nickName'] = $v['_source']['nickname'];
				$list[$i]['headImg'] = $v['_source']['avatar'];

				$i++; //计数器自增
			}

		}

		$jsonData['count'] = $count;
		$jsonData['resultList'] = $list;

		return $jsonData;
	 }



	/**
	 * 更新ES单条记录
	 *
	 * @param $type	 更新索引 type=question更新问题，news更新攻略
	 * @param $id	 唯一id
	 * @return true/false
	 */
	 public function updateEsDataFromDb($id, $type){
		if(empty($type) || empty($id)){
			return false;
		}
		/*
		* 因数据延迟问题，修改同步ES方式
		* 需要更新同步ES数据ID暂时记录到redis，然后定时5分钟执行一次同步
		*/
		//$rKey = 'glapp:question:wait_rsync';
		//$this->cache->redis->zAdd($rKey, time(), $id);
		//return true;

		// 查询对应数据记录(查库)
		if($type == 'question'){
			$index = 'gl_app_index';
			$type  = 'glapp_gl_question_type';

			// 搜索同步sql
			$condition['where']['es_data_type'] = $type;
			$condition['fields']= ' * ';
			$info = $this->findOne($condition, 'gl_es_task');

			// 搜索更新内容
			$sql = $info['es_sql'] . " and a.qid = ".intval($id);
			$query = $this->db->query_write($sql);
			$row = $query ? $query->row_array() : false;
			if(!$row){
				return false;
			}
			$res = $this->Es->createEsIndex($index, $type, $row['qid'], $row);

			return $res;
		}
		// 更新攻略(查库)
		elseif($type == 'news'){
			$index = 'gl_app_index';
			$type  = 'glapp_gl_article_cms_type';

			// 搜索同步sql
			$condition['where']['es_data_type'] = $type;
			$condition['fields']= ' * ';
			$info = $this->findOne($condition,'gl_es_task');

			// 搜索更新内容
			$sql = $info['es_sql'] . " and c.cms_id = '".trim($id)."' ";
			$query = $this->db->query_write($sql);
			$row = $query ? $query->row_array() : false;

			if(!$row){
				$return = false;
			} else {
				$return = $this->Es->createEsIndex($index, $type, $row['cms_id'], $row);
			}

			// ==================== LOG =======================================//
			// $log = array(
			// 		'sql' => $sql,
			// 		'row' => $row,
			// 		'return' => $return
			// );
			// PLog::w_DebugLog($log);
			// ==================== LOG =======================================//

			return $return;
		}
	 }

	/**
	 * 更新ES单条记录
	 *
	 * @param $data	 更新内容数组
	 * @param $id	 唯一id
	 * @param $type	 索引类型
	 * @return true/false
	 */
	 public function updateEsData($id, $data=array(), $type){
		if(empty($type) || empty($id) || empty($data)){
			return false;
		}

		if($type == 'question'){
			$index = 'gl_app_index';
			$type  = 'glapp_gl_question_type';

			$data['_id'] = $id;

			$res = $this->Es->createEsIndex($index, $type, $id, $data);

			return $res;
		}elseif($type == 'news'){
			$index = 'gl_app_index';
			$type  = 'glapp_gl_article_cms_type';

			$data['_id'] = $id;

			$res = $this->Es->createEsIndex($index, $type, $id, $data);
			return $res;
		}
	 }

	 /**
	 *
	 *
	 * 根据条件查询一条数据
	 *
	 * @param array
	 * @return Array
	 */
	public function findOne($conditons = array(), $table=null) {
		if(null != $table && !empty($table)){
			$this->_table = $table;
		}else{
			return false;
		}

		$sql = $this->find($conditons);
		$rs = $this->db->query_read($sql);
		$result = $rs ? $rs->row_array() : array();
		return $result;
	}

	/**
	 * 攻略搜索
	 *
	 */
	public function searchNews_for_xyd( $keyword, $game_id, $page=1, $pageSize=10, $sys = 'ios', $node_time = 0 ){
		$es_index = "gl_app_index";
		$es_type  = "glapp_gl_article_cms_type";
		if(empty($keyword)){
			$condition = '';
		}else{
			$condition['like'] = array('title'=>$keyword,'game_name'=>$keyword,'tags'=>$keyword);
		}
		// 设置过滤条件
		if($game_id > 0){
			$condition['where'] = array('game_id'=>$game_id);
		}

		if($node_time > 0){
			$condition['range'] = array('update_time'=>array('lte'=>$node_time));
		}

		// 排序
		$condition['sort'] = array(array('_score'=>'desc'),array('browse_count'=>'desc'));

		// 设置es返回字段
		$this->Es->setEsSource(array('id','title','game_id','game_name','cms_id','mark_up_count','browse_count','update_time'));
		$rs = $this->Es->searchByColumns( $es_index, $es_type, $condition, 0, $page,$pageSize );

		//因英文匹配问题,如未匹配到在进行一次匹配
		if(preg_match("/^[a-zA-Z]+$/",$keyword) && intval($rs['hits']['total']) == 0){
			$rs = $this->Es->searchByColumns( $es_index, $es_type, $condition, 0, $page,$pageSize, 2 );
		}

		$list = array();
		$count= 0;
		// 格式化数据
		if($rs && $rs['hits']['total'] > 0){
			foreach($rs['hits']['hits'] as $k=>$v){
				// 关注、浏览详情
				$cms_info = $this->At->findArticleData($v['_source']['cms_id']);

				$list[$k]['absId'] = $v['_source']['cms_id'];
				$list[$k]['abstitle'] = $v['_source']['title'];
				//$list[$k]['absImage'] = 'http://s.img.mix.sina.com.cn/auto/resize?img='.$cms_info[0]['mainPic'].'&size=100_100';
				$list[$k]['scanCount'] = intval($cms_info['browse_count']);
				$list[$k]['praiseCount'] = intval($cms_info['mark_up_count']);
				$list[$k]['gameTitle'] = $v['_source']['game_name'];
				//$list[$k]['create_time'] = date('Y-m-d H:i:s', $v['_source']['update_time']);
			}

			$count = intval( $rs['hits']['total'] );
		}

		$jsonData['count'] = $count;
		$jsonData['resultList'] = $list;

		return $jsonData;
	}


}
