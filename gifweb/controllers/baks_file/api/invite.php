<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Invite extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('invite_model');
		$this->load->model('user_model');
	}

	/*
	 * 75 用户邀请操作（邀请其他用户参与回答）    2.0版本新增
	 */
	public function invite_save()
	{
		$uid	  	= $this->input->get('uid',true) ;
		$guid	  	= $this->input->get('guid',true) ;
		$mark	  	= $this->input->get('mark',true) ? $this->input->get('mark',true) : '';
		$type	  	= $this->input->get('type',true) ? $this->input->get('type',true) : 1;

		try {
			if(empty($uid) || empty($guid) ) {
		        throw new Exception('用户id不能为空', _PARAMS_ERROR_);
			}
			if(empty($mark)) {
		        throw new Exception('事件id不能为空（2.0版本为问题）', _PARAMS_ERROR_);
			}
			if ($this->common_model->is_ban_user()) {
				throw new Exception(_BANNED_MSG_, _USER_BANNED_);
			}

			$today = date('Y-m-d',time());
			$todayEnd = strtotime($today.' 23:59:59');
			$cache_time =$todayEnd - time();

			$cache_key= "glapp:" . ENVIRONMENT . ":invite:$guid:$uid:$mark";
			//判断是否有过邀请 （【截止当天23:59:59】同一个人对一个用户且同一个问题只能邀请1次）
		    $data = $this->cache->redis->get($cache_key);
		    if($data){
			     Util::echo_format_return(_SUCCESS_, array(),'邀请成功');
			     exit;
			}
// 			$is_invite = $this->invite_model->is_invite($uid,$guid,$mark,$type);
// 			if($is_invite){
// 			     Util::echo_format_return(_SUCCESS_, '','邀请成功');
// 			     exit;
// 			}
			
			//插入数据库操作
			$this->cache->redis->set($cache_key, json_encode(1), $cache_time );
// 			$data = array();
// 			$data['uid'] = $guid;//邀请人
// 			$data['invite_uid'] = $uid;//被邀请人
// 			$data['type'] = $type;
// 			$data['mark'] = $mark;
// 			$data['create_time'] = time();
// 			$data = $this->invite_model->insertInviteData($data);

			//成功后.发送消息给被邀请人
		    $this->load->model('push_message_model');
		    $a = $this->push_message_model->push(1,0,$mark, 1, 5, $uid);//第三个参数为问题ID，第六个参数为邀请人的UID
		    Util::echo_format_return(_SUCCESS_, '','邀请成功');
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}
	
	/*
	 * 75 用户邀请操作（邀请其他用户参与回答）    2.0版本新增
	 */
	public function invite_list()
	{
	    $page	  	= $this->input->get('page',true) ? $this->input->get('page',true) : 1;
	    $count	  	= $this->input->get('count',true) ? $this->input->get('count',true) : 10;
	    $start 		= ($page==1) ? 0 : ($page-1) * $count;
		try {
		    $uList = $this->user_model->get_user_list($start,$count);
            
		    $data =array();
		    foreach ($uList as $k => $v){
		        $data[$k]['guid'] = (string)$v['uid'];
		        $data[$k]['nickName'] = (string)$v['nickname'];
		        $data[$k]['headImg'] = (string)$v['avatar'] ? (string)$v['avatar']:'';
		        $data[$k]['uLevel'] = (int)$v['level'];
		        $data[$k]['medalLevel'] = (int)$v['rank'] == 1 ? 1 : 0;
		    }
			Util::echo_format_return(_SUCCESS_, $data,'成功');
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}

}

/* End of file invite_model.php */
/* Location: ./application/controllers/api/invite_model.php */
