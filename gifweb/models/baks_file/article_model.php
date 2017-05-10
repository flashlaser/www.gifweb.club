<?php
if (! defined ( 'BASEPATH' )) exit ( 'No direct script access allowed' );

/**
 * @Name	Article_Model.php
 *
 */
class Article_model extends MY_Model {

	private $_cache_article_key_pre = '';
	private $_cache_list_key_pre = '';
	private $_cache_expire = 600 ;
	protected  $_table = 'gl_article';

	public function __construct() {
		parent::__construct ();
		$this->_cache_article_key_pre = "glapp:" . ENVIRONMENT . ":article3:";
		$this->_cache_list_key_pre = 'glapp:'.ENVIRONMENT.':gl:listContinue';
	}

	public function addRedis($id){
		$this->cache->redis->sAdd($this->_cache_list_key_pre, $id);
	}
	//查询攻略文档信息
	public function findArticleData($newsid,$delCache = 0)
	{
		if($delCache == 1){
			$this->_aftermath($newsid);
		}

		$cache_key = $this->_cache_article_key_pre . "findArticleData:$newsid";
		$data = $this->cache->redis->get($cache_key);
		$data && $data = json_decode($data, 1);
		if (!is_array($data)) {
			$conditons['where']['cms_id']= array('eq',$newsid);
			$sql = $this->find($conditons);
			$rs = $this->db->query_read($sql);
			$data = $rs->row_array();
			$this->cache->redis->set($cache_key, json_encode($data), $this->_cache_expire );
		}
		return $data;
	}


	public function findArticleCountByGameId($gameid)
	{
	    $sql = "select count(*) as article_nums from gl_article where game_id='".$gameid."'";
	    $rs = $this->db->query_read($sql);
	    $data = $rs->row_array();
	    return $data;
	}
	
	
	public function updateArticleMarkDownCount($id,$add)
	{
		$sql = "update gl_article set `mark_down_count`= (mark_down_count  + ".intval($add).") where cms_id='".$id."'";
		$rs = $this->db->query_write($sql);
		return $this->_aftermath($id);
	}

	public function updateArticleMarkUpCount($id,$add)
	{
		$sql = "update gl_article set `mark_up_count`= (mark_up_count  + ".intval($add).") where cms_id='".$id."'";
		$rs = $this->db->query_write($sql);
		return $this->_aftermath($id);
	}
	public function updateArticleVirtualMarkUpCount($id)
	{
    	$randsA = rand('10','30');
		$sql = "update gl_article set `virtual_mark_up_count`= (virtual_mark_up_count  + ".intval($randsA).") where cms_id='".$id."'";
		$rs = $this->db->query_write($sql);
		return $this->_aftermath($id);
	}

	public function updateArticleBrowseCount($id)
	{
    	$randsA = rand('50','100');
		$sql = "update gl_article set `browse_count`= (browse_count + 1),`virtual_browse_count`= (virtual_browse_count + ".$randsA.") where cms_id='".$id."'";
		$rs = $this->db->query_write($sql);
		return $this->_aftermath($id);
	}

	public function updateArticleCommentCount($id,$add)
	{
		$sql = "update gl_article set `comment_count`= (comment_count + ".intval($add).") where cms_id='".$id."'";
		$rs = $this->db->query_write($sql);
		$rss = $this->db->affected_rows_write();
		$this->_aftermath($id);
		return $rss ? true : false;
	}

	private function _aftermath($cms_id) {
		// delete cache
		$cache_key = $this->_cache_article_key_pre . "findArticleData:$cms_id";
		$this->cache->redis->delete($cache_key);

		return 1;
	}
	
	public function findgameidBycmsId($cmsId)
	{
		$sql = "select game_id as gameid from gl_article_cms where cms_id='".$cmsId."'";
		$rs = $this->db->query_read($sql);
		$data = $rs->row_array();
		return $data;
	}

}
