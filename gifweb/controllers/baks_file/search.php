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
		$this->load->model('Qa_model','qa_model');
		$this->load->model('Game_model','game_model');
		$this->load->driver('cache');
	}
	
	/**
	 * 搜索
	 *
	 * @param int $type $_GET
	 * @param string $keyword $_GET
	 * @param string $relatedGame $_GET
	 * @param int $page $_GET
	 * @param int $count $_GET
	 * @return json
	 */
	public function index()
	{
		$type = intval(( int ) $this->input->get ( 'type', true )) ? intval(( int ) $this->input->get ( 'type', true )) : 4;
		$keyword = trim(( string ) $this->input->get ( 'search_keyword', true ));
		$page = ( int ) $this->input->get ( 'page', true );
		$gameId = ( int ) $this->input->get ( 'gameId', true );
        $this->platform = Util::getBrowse();
		if($gameId >0){
		    $data['gameId'] = $gameId;
	        $this->smarty->assign('data', $data);
	        $gameInfo = $this->game_model->get_game_row($gameId,$this->platform);

	        $this->smarty->assign('gameTitle', $gameInfo['abstitle']);
		}
        //搜索历史 相关
        if(!empty($keyword)){
            if(empty($_COOKIE['search_history_keywords']) || $_COOKIE['search_history_keywords']=='[]'){
        		$search_history_arr = array($keyword);
                $search_history = json_encode($search_history_arr);
                // setcookie('search_history_keywords',$search_history,time()+'31536000','/','.wan68.com');
                setcookie('search_history_keywords',$search_history,time()+'31536000','/',$this->config->item('domain'));
            }else{
                $search_history_arr = array($keyword);
                $search_history = json_encode($search_history_arr);

        		$cookie_search_array = json_decode($_COOKIE['search_history_keywords'],true);

                foreach ($cookie_search_array as $k=>$v){
                    if($v == $keyword){
                        unset($cookie_search_array[$k]);
                    }
                }
//         		$cookie_search_array = array_unique($cookie_search_array);
        		$cookie_search_arrays=array();
        		if(count($cookie_search_array) <10){
        		    if($cookie_search_array && $search_history_arr){
        		      $search_history_arr  = array_merge($cookie_search_array,$search_history_arr);
        		    }else{
        		        $search_history_arr  =  $cookie_search_array;
        		    }
        		    $search_history = json_encode($search_history_arr);
                    // setcookie('search_history_keywords',$search_history,time()+'31536000','/','.wan68.com');
                    setcookie('search_history_keywords',$search_history,time()+'31536000','/',$this->config->item('domain'));
        		}else{
                    //删除数组中首个元素
        		    array_shift($cookie_search_array);
        		    //入栈
        		    array_push($cookie_search_array,$keyword);
        		    $search_history = json_encode($cookie_search_array);
        		    // setcookie('search_history_keywords',$search_history,time()+'31536000','/','.wan68.com');
        		    setcookie('search_history_keywords',$search_history,time()+'31536000','/',$this->config->item('domain'));
        		}
            }
        }
//     	$cookie_search_array = json_decode($_COOKIE['search_history_keywords'],true);
// // 		$cookie_search_array = array_unique($cookie_search_array);
//     	if($cookie_search_array){
//     	   krsort($cookie_search_array);
//     	}
//     	$this->smarty->assign('search_history_info', $cookie_search_array);
    	
		$page = $page < 1 ? 1 : $page;
		$page_size = ($page_size < 1 || $page_size > 50) ? 10 : $page_size;
        if(!empty($keyword)){
    		switch ($type){
    			case 4:
    				$result['data']['game']		=	$this->Search->searchGame( $keyword, 1, 50, $this->platform );
    				$result['data']['raiders']	=	$this->Search->searchNews( $keyword, $gameId, $page, $page_size, $this->platform, $node_time );
    				$result['data']['question']	=	array('count'=>0, 'resultList'=>array());
    				break;
    			case 5:
    				$result['data']['game']		=	$this->Search->searchGame( $keyword, 1, 50, $this->platform );
    				$result['data']['raiders']	=	$this->Search->searchNews( $keyword, $gameId, $page, $page_size, $this->platform, $node_time );
    				$result['data']['question']	=	$this->Search->searchQuestions( $keyword, $gameId, $page, $page_size, $this->platform, $node_time);
    				break;
    			case 6:
    				$result['data']['game']		=	$this->Search->searchGame( $keyword, 1, 50, $this->platform );
    				$result['data']['raiders']	=	array('count'=>0, 'resultList'=>array());
    				$result['data']['question']	=	$this->Search->searchQuestions( $keyword, $gameId, $page, $page_size, $this->platform, $node_time);
    				break;
    			default:
    				$result['data']['game']		=	$this->Search->searchGame( $keyword, 1, 50, $this->platform );
    				$result['data']['raiders']	=	$this->Search->searchNews( $keyword, $gameId, $page, $page_size, $this->platform, $node_time );
    				$result['data']['question']	=	$this->Search->searchQuestions( $keyword, $gameId, $page, $page_size, $this->platform, $node_time);
    		}
    		if($type == 4){
    		  $p_data['total_rows'] = $result['data']['raiders']['count'];
    		}elseif($type == 6){
    		  $p_data['total_rows'] = $result['data']['question']['count'];
    		}
    		$p_data['page'] = $page;
    		$p_data['page_size'] = $page_size;
    		$pages = "#content_info";
    		$p_data['url_prefix'] = base_url () . 'search/index?type='.$type.'&search_keyword='.$keyword.'&gameId='.$gameId.'&page=';
    		$p_data['controllers_name'] = '/search/';
    		$p_data = $this->common_model->get_page_data($p_data);
        }
        if($result['data']['game']['resultList'][0]['absId']){
            foreach ($result['data']['game']['resultList'] as $k => $v){
                $result['data']['game']['resultList'][$k]['abstitles'] = $this->qa_model->convert_content_to_frontend($v['abstitle'],4, 1);
            }
        }
        if($result['data']['question']['resultList'][0]['absId']){
            foreach ($result['data']['question']['resultList'] as $k => $v){
                $result['data']['question']['resultList'][$k]['answerCount'] = $v['answerCount'] ? (int)$v['answerCount'] : 0;
            }
        }
//         echo "<pre>";print_r($result);exit;
	    $this->smarty->assign('page_data', $p_data);
	    $this->smarty->assign('result', $result);
	    $this->smarty->assign('keyword', $keyword);
	    $this->smarty->assign('encode_keyword', urlencode($keyword));
    	$this->smarty->assign('type', $type);
    	$this->smarty->assign('page_from', 'search');
	    $this->smarty->view ( 'search.tpl' );

	}
}

/* End of file user.php */
/* Location: ./application/controllers/api/user.php */
