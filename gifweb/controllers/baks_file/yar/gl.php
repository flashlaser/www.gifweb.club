<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * yar
 *
 * @author	 liule1
 * @date 2016年9月08日
 *
 * @copyright (c) 2016 SINA Inc. All rights reserved.
 */
class Gl extends Yar
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('common_model');
        $this->load->library('global_func');
        $this->load->driver('cache');
    }

    /**
    *   啊  啊啊
    */
    public function list_info_detail ($v, $sort) {
        $this->load->model('gl_model');
        $this->load->model('article_model');

        $images = $this->gl_model->getPicSize($v['_id'],10);
        $thumbnail[$k] = array();
        foreach ($images as $k_1=>$v_1){
            if($v_1['width'] > '380' && $v_1['height'] > '286' ){
                $thumbnail[$k][] = $v_1['url'];
            }
        }
        if(count($thumbnail[$k]) >=3){
            $thumbnails[$k][] = $thumbnail[$k][0];
            $thumbnails[$k][] = $thumbnail[$k][1];
            $thumbnails[$k][] = $thumbnail[$k][2];
        }

        $article[$k] = $this->article_model->findArticleData($v['_id']);
        if(empty($article[$k]['id'])){
            $this->article_model->addRedis($v['_id']);
            continue;
        }
        $_arr = array();
        $_arr['absId'] = $v['_id'];
        $_arr['abstitle'] = $v['title'];
        $_arr['absImage'] = $v['pics'][0]['imgurl'] ? $v['pics'][0]['imgurl'] : '';
        $_arr['scanCount'] = $article[$k]['browse_count'] ? (int) $article[$k]['browse_count'] : 0;
        $_arr['praiseCount'] =$article[$k]['mark_up_count'] ? (int)$article[$k]['mark_up_count'] : 0;
        $_arr['thumbnail'] =$thumbnails[$k] ? $thumbnails[$k] : array();
        $_arr['type'] =$v['mdType'] ? 1 : 0;
        return array('sort' => $sort, 'data' => $_arr);
    }

    public function list_info($ClassId,$page,$count) {
        $this->load->model('gl_model');
		$this->load->model('user_model');
		$this->load->model('game_model');
		$this->load->model('follow_model');
		$this->load->model('article_model');
		$this->load->model('like_model');
		$this->load->model('recommend_model');


        $article_info = $this->game_model->get_cms_info_by_category($ClassId,$page,$count);
        //-->处理 如果max_id出现相同的 那么从下一页抓取最新的补齐   start --------//
        $returns = $article_info;

        //-->处理 如果max_id出现相同的 那么从下一页抓取最新的补齐   end --------//
//            echo "<pre>";print_r($returns);exit;
        $data = array();
        foreach ($returns as $k=>$v){
            $images = $this->gl_model->getPicSize($v['_id']);
            $thumbnail[$k] = array();
            foreach ($images as $k_1=>$v_1){
                if($v_1['width'] > '380' && $v_1['height'] > '286' ){
                    $thumbnail[$k][] = $v_1['url'];
                }
            }
            if(count($thumbnail[$k]) >=3){
                $thumbnails[$k][] = $thumbnail[$k][0];
                $thumbnails[$k][] = $thumbnail[$k][1];
                $thumbnails[$k][] = $thumbnail[$k][2];
            }

            $article[$k] = $this->article_model->findArticleData($v['_id']);
            if(empty($article[$k]['id'])){
                $this->article_model->addRedis($v['_id']);
                continue;
            }
            $_arr = array();
            $_arr['absId'] = $v['_id'];
            $_arr['abstitle'] = $v['title'];
            $_arr['absImage'] = $v['pics'][0]['imgurl'] ? $v['pics'][0]['imgurl'] : '';
            $_arr['scanCount'] = $article[$k]['browse_count'] ? (int) $article[$k]['browse_count'] : 0;
            $_arr['praiseCount'] =$article[$k]['mark_up_count'] ? (int)$article[$k]['mark_up_count'] : 0;
            $_arr['thumbnail'] =$thumbnails[$k] ? $thumbnails[$k] : array();
            $_arr['type'] =$v['mdType'] ? 1 : 0;

            $data[] = $_arr;
        }

        return $data;
    }

}
