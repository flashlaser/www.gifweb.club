<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

/**
 * @Name	Elastic_Model.php
 */
class Elastic_Model extends MY_Model {

	/* es服务地址,采用负载均衡lvs */
	private $es_url = "http://10.24.192.87:9200";

	/* mysql数字自动更新至ES时间,默认一天 */
	private $es_time= 86400;

	/* es接口地址 */
	private $es_api = '';

	/* es请求方式 */
	private $method = 'POST';

	/* es返回source值 */
	private $_source = '';

	public function __construct($method = 'POST') {
		parent::__construct ();
		if (ENVIRONMENT == 'testing') {
			$this->es_url = 'http://101.201.40.71:9200';
		}

		$this->load->library("global_func");
		$this->load->library('HttpRequestCommon',null,'http');

		$this->es_api = $this->es_url;
		$this->method = $method;


	}

	/* 设置更新时间 */
	public function setEsTime($uptime){
		$this->es_time = $uptime;
	}

	/* 设置es接口地址 */
	public function setEsApiUrl($url=''){
		if(empty($url)){
			$this->es_api = $this->es_url[0];
		}else{
			$this->es_api = $url;
		}
	}

	/**
	 * 根据某一列的关键字，去指定的索引和类型中查询
	 * @param string $index 索引
	 * @param string $type  类型
	 * @param string $column 指定列
	 * @param string $keyword  查询关键字   如果关键字是空，则返回指定索引（或类型）的所有的记录
	 * @param int $s_type  查询返回结果集设置   0:  返回包含所有关键字(即关键字拆分)的结果集 ;   1: 返回包含该短语的结果集;
	 * @return unknown  返回信息
	 *
	 * 说明： 这个方法，返回的关键字，包含分散的结果集
	 * eg keyword：“中国”  result: 中华人民共和国
	 * 如果keyword 是“中    国” result 则返回 包含 “中” 和 “国” 任一个值存在的结果接
 	 */
	public function searchByColumn($es_index, $es_type, $column, $keyword, $page=1,$pageSize=10, $s_type=0){
		if(empty($es_index)) return false;

		if(empty($es_type)) return false;

		if(empty($column)) return false;

		$data['index']	=	$es_index;
		$data['type']	=	$es_type;
		$data['column']	=	$column;
		$data['keyword']=	$keyword;
		$data['filter']	=	array('display'=>1);
		$data['s_type']	=	$s_type;
		$data['page']	=	$page;
		$data['pageSize']	=	$pageSize;

		$res = $this->sendEsRequest( $data, 'search' );

		if(!$res || $res['error']){
			return false;
		}else{
			return $res;
		}
	}

	/**
	 * 根据多列的关键字，去指定的索引和类型中查询
	 * @param string $index 索引
	 * @param string $type  类型
	 * @param array $condition 指定多列及对应查询关键字
	 * @param int $flag  0=>should:有一个成立就行; 1=>must:同时存在; 2=>must_not：2个都不成立的;
	 * @param int $s_type  查询返回结果集设置   1:  返回包含所有关键字(即关键字拆分)的结果集 ;   2: 返回包含该短语的结果集;
	 * @return unknown  返回信息
	 *
 	 */
	public function searchByColumns($es_index, $es_type, $condition, $flag, $page=1,$pageSize=10, $s_type=0 ){
		if(empty($es_index)) return false;

		if(empty($es_type)) return false;

		$data['index']		=	$es_index;
		$data['type']		=	$es_type;
		$data['column']		=	$condition['like'];
		$data['filter']		=	$condition['where'];
		$data['range']		=	$condition['range'];
		$data['sort']		=	$condition['sort'];
		$data['flag']		=	$flag;
		$data['s_type']		=	$s_type;
		$data['page']		=	$page;
		$data['pageSize']	=	$pageSize;

		$res = $this->sendEsRequest( $data, 'search' );

		if(!$res || $res['error']){
			return false;
		}else{
			return $res;
		}
	}

	/**
	 * 拼接ES请求地址
	 *
	 */
	public function createRequestUrl($data, $s_flag){
		$url = $this->es_api."/".$data['index']."/".$data['type'];
		if($s_flag == 'search'){
			$url .= "/_search?" . $this->_source;
		}
		if($s_flag == 'mapping'){
			$url .= "/_meta";
		}
		if($s_flag == 'map'){
			$url .= "/_mapping";
		}
		if($s_flag == 'up' && !empty($data['_id'])){
			$url .= "/".$data['_id'];
		}

		return $url;
	}

	/**
	 * 设置ES返回source值
	 *
	 */
	public function setEsSource($arr){
		if(is_array($arr) && !empty($arr)){
			$str = implode(",", $arr);
			$this->_source = "_source=".$str;
		}
	}

	/* 格式化ES请求参数 */
	public function createRequestParams($arr){

		// 多列查询
		if(is_array($arr['column'])){

			foreach($arr['column'] as $key=>$val){
				if($arr['s_type'] == 1){
					$match_data[] = array("match_phrase"=>array("$key"=>"$val"));
				}elseif($arr['s_type'] == 2){ //这个是利用match_phrase_prefix前缀匹配
					$match_data[] = array("match_phrase_prefix"=>array("$key"=>"$val"));
				}else{
					$match_data[] = array("match"=>array("$key"=>"$val"));
				}
			}

			if($arr['flag'] == 1){
				$data=array("query"=>array("bool"=>array("must"=>$match_data)));
			}elseif($arr['flag'] == 2){
				$data=array("query"=>array("bool"=>array("must_not"=>$match_data)));
			}else{
				$data=array("query"=>array("bool"=>array("should"=>$match_data)));
			}

		}else{	// 单列查询
			if($arr['s_type'] == 1){ //这个是利用 match_phrase 匹配 ，返回包含该短语的结果集
				$data["query"]=array("match_phrase"=>array($arr['column']=>$arr["keyword"]));
			}elseif($arr['s_type'] == 2){ //这个是利用match_phrase_prefix前缀匹配
				$data["query"]=array("match_phrase_prefix"=>array($arr['column']=>$arr["keyword"]));
			}else{   //这个是利用match 匹配,返回包含所有关键字的结果集
				$data["query"]=array("match"=>array($arr['column']=>$arr["keyword"]));
			}

			if(empty($arr['keyword']) || $arr['keyword'] == NULL){ //如果关键字是空，则 返回所有记录
				$data["query"]=array("match_all"=>array());
			}
		}
		// 指定过滤查询
		if($arr['range']){
			$fdata = array();
			$fdata['query']['filtered']['query'] = $data["query"];
			/*
			if($arr['filter']){
				$fdata['query']['filtered']['filter']['and']['term'] = $arr['filter'];
				$fdata['query']['filtered']['filter']['and']['range'] = $arr['range'];
			}else{
				$fdata['query']['filtered']['filter']['range'] = $arr['range'];
			}
			*/
			$fdata['query']['filtered']['filter']['range'] = $arr['range'];
			$data = $fdata;
		}

		if($arr['filter']){
			$data['filter']['term'] = $arr['filter'];
		}



		// 指定排序
		if($arr['sort']){
			$data['sort'] = $arr['sort'];
		}

		$data["size"] =$arr["pageSize"];
		$data["from"]=($arr["page"]-1) * $arr["pageSize"];

		$data = json_encode($data);
		return $data;
	}

	/**
	 * 创建 es索引、类型、更新数据
	 * @param flag 可以设置es唯一ID或请求方式
	 */
	public function createEsIndex($index, $type='', $flag='', $info = array()){
		if(empty($index)){
			return false;
		}

		$s_flag = '';
		$data['index'] = $index;
		$data['type']  = $type;
		$data['data']  = $info;
		$data['_id']   = $info['_id'];
		if($flag != 'map'){
			$s_flag = 'up';
		}else{
			$s_flag = 'map';
		}

		// 设置请求方式
		$this->method = 'PUT';
		$rs = $this->sendEsRequest($data, $s_flag);

		if(!empty($rs['error'])){
			return false;
		}else{
			return true;
		}
	}

	/**
	 * 创建 数据库 与 es的 数据同步
	 * 将数据库作为 es 的 river（数据源）
	 * 每隔 指定时间内，更新一下数据
	 * @param
	 */
	public function createSyncMysqlToEs($arr, $dbconfig,$uptime=86400){
		$res = array('err'=>1, 'msg'=>'参数错误');
		if(empty($arr['river_type'])){
			$res['msg'] = 'mysql同步es数据类型错误';
			return $res;
		}

		if(empty($arr['es_index']) || empty($arr['es_type'])){
			$res['msg'] = 'ES数据索引or类型错误';
			return $res;
		}

		if(empty($arr['sql'])){
			$res['msg'] = 'sql错误';
			return $res;
		}

		if(empty($dbconfig["hostname"]) || empty($dbconfig["database"]) || empty($dbconfig["username"]) || !isset($dbconfig["password"])){
			$res['msg'] = '数据库配置错误';
			return $res;
		}

		// 数据库同步更新时间，为减轻数据库及ES服务器压力，最短为一天
		if($uptime < 86400){
			$res['msg'] = '更新时间错误，最短为一天';
			return $res;
		}

		$data["index"] = '_river';
		$data["type"] = $arr['river_type'];
		$data["data"] =  array(
				"type"=>"jdbc",
				"jdbc"=>array(
						"driver"=>"com.mysql.jdbc.Driver",
						"url"=>"jdbc:mysql://".$dbconfig["hostname"]."/".$dbconfig["database"],
						"user" => $dbconfig["username"],    //链接账号
						"password" =>$dbconfig["password"], // 链接密码
						"sql" =>$arr['sql'],
						"index" => $arr['es_index'], // 数据库中的数据同步到 es中对应的 索引名字建议格式:库名_index
						"type" => $arr['es_type'],// 数据库中的数据同步到 es中对应的 索引下面的类型名字:建议格式:库名_表名_type
						"interval" => $uptime
				)
		);

		// 设置请求方式
		$this->method = 'PUT';
		$rs = $this->sendEsRequest($data, 'mapping');

		return $rs['created'];
	}

	/**
	 * 删除索引，类型
	 * curl -X DELETE http://localhost:9200/{INDEX}/
	 */
	public function dropEsType($index, $type, $id=''){
		if(empty($index) || empty($type)){
			return false;
		}

		$data['index']	=	$index;
		$data['type']	=	$type;
		$data['_id']	=	$id;

		// 设置请求方式
		$this->method = 'DELETE';
		$rs = $this->sendEsRequest($data, 'up');

		if($rs['error']){
			return false;
		}else{
			return true;
		}
	}


	/**
	 * 发送ES搜索请求
	 *
	 */
	private function sendEsRequest( $data, $s_flag = '' ){
		// PLog::w_DebugLog("get_sendEsRequest_data " . serialize($data));

		$url = $this->createRequestUrl($data, $s_flag);

		$this->http->setRequest ( $url, $this->method );
		$this->http->setPort(9200);
		if($this->method == 'POST'){
			$postData= $this->createRequestParams($data);
			$this->http->setPostData ( $postData );
		}elseif($this->method == 'DELETE'){
			$this->http->setMethod('DELETE');
		}elseif($this->method == 'PUT'){
			$this->http->setPostData ( json_encode($data['data']) );
			$this->http->setMethod('PUT');
		}
		$result = $this->http->send ();

		// PLog::w_DebugLog("get_sendEsRequest_result return " . serialize($result));
		$res = null;
		(is_array($result) && $result['body']) && $res = json_decode($result['body'], true );

		return $res;
	}
}
