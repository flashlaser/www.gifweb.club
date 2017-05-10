<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * @name Areward
 * @desc null
 *
 * @author	long
 * @date	2016-02-17
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 *
 */
class Areward extends MY_Controller
{
	public function __construct(){
		parent::__construct();
		$this->load->model('order_model','order');
		$this->load->model('user_model','User');
	}

	public function areward_list($action){
	    if($action !=1 && $action !=2){
	        echo "error";
	        exit;
	    }
	    $datas = $this->order->get_list_top($action);
	    $data=array();
	    foreach ($datas as $k=>$v){
	        $k++;
	        $dataInfo[$k] = $this->User->getUserInfoById($v['uid']);
	        $data[$k]['guid']          = (string)$v['uid'];
	        $data[$k]['nickName']      = (string)$dataInfo[$k]['nickname'];
	        $data[$k]['headImg']       = (string)$dataInfo[$k]['avatar'] ? (string)$dataInfo[$k]['avatar'] : '';
	        $data[$k]['uLevel']        = (int)$dataInfo[$k]['level'];
	        $data[$k]['medalLevel']    = (int)$dataInfo[$k]['rank'] == 1 ? 1 : 0;
	        $data[$k]['moeny']         = $v['all_amount'] ? sprintf("%.2f",substr(sprintf("%.4f", $v['all_amount'] / 100), 0, -2)) : 0;
	    }
        
	    
	    $this->smarty->assign('action', $action);
	    $this->smarty->assign('data', $data);
		$this->smarty->view ( 'areward/areward_list.tpl' );
	}

	public function introduce(){
		$this->smarty->view ( 'areward/introduce.tpl' );
	}
	
}

