<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

/**
 * 
 * @name Answer
 * @desc 攻略WAP答案控制类
 *
 * @author	 wangbo8
 * @date 2015年12月18日
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 */
class Answer extends MY_Controller {
	public function __construct() {
		parent::__construct ();

		$back_url = $url='http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
		$this->smarty->assign('back_url', $back_url);

		$this->load->model('follow_model');
		$this->load->model('game_model');
		$this->load->model('recommend_model');
		$this->load->model('gl_model');
		$this->load->model('user_model');
		$this->load->model('article_model');
		$this->load->model('like_model');
		$this->load->model('qa_model');
		$this->load->model('qa_image_model');
		$this->load->model('waptext_model');
	}

	//答案详情页
	public function info($absId,$flag = 0){ //首页
		$uid = $this->user_id;
		$res = $this->global_func->inject_check($absId);
		if($res){
			exit('分类参数含有非法字符');
		}
		$aid = $this->global_func->filter_int($absId);

		try {
			if (empty($aid) ) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}

			$data = $this->qa_model->get_answer_info($uid, $aid);

			if(empty($data) || !is_array($data)){
				$result['message'] = '答案已关闭!';
				$this->showMessage('fail', $result);
				return 1;
			}

			//处理答案内容，增加图片
			$ashare_content = $data['content'];
			$answer_tmp = $this->qa_image_model->changeImgStr($data['content']);
			$data['content'] = $answer_tmp['content'] ;

			//判断分享图片
			if(!empty($data['attribute']['images']) && $data['attribute']['images'][0]['url']){
				//得到当前图片
				$ashare_pic_url = $data['attribute']['images'][0]['url'];
			}else{
				$ashare_pic_url = base_url() . '/gl/static/images/foot_logo.png';
			}
			$data['ashare_pic_url'] = urlencode($ashare_pic_url);

			//处理
			$data['content'] = $this->waptext_model->convert_content_to_wapfrontend($data['content']);

			//获取问题详情内容
			$question = $this->waptext_model->get_question_info($uid, $data['questionInfo']['absId'],array(0,1,2));

			//判断分享图片
			if(!empty($question['attribute']['images']) && $question['attribute']['images'][0]['url']){
				//得到当前图片
				$qshare_pic_url = $question['attribute']['images'][0]['url'];
			}else{
				// $qshare_pic_url = 'http://www.wan68.com/gl/static/images/foot_logo.png';
				$qshare_pic_url = base_url() . 'gl/static/images/foot_logo.png';
			}
			$data['qshare_pic_url'] = urlencode($qshare_pic_url);

			//对问题详情图片进行处理
			$qshare_content = $question['content'];
			$question_content = $this->qa_image_model->changeImgStr($question['content']);
			$question['content'] = $question_content['content'];

			//处理
			$question['content'] = $this->waptext_model->convert_content_to_wapfrontend($question['content']);

			$data['questionInfo']['info'] = $question;
			$data['selfuid'] = $uid;
			if (empty($data)) {
				throw new Exception('答案已关闭或已删除', _DATA_ERROR_);
			}

			//拼接分享地址
			$data['qshareurl'] = base_url () . "question/info/" . $question['absId'];
			$data['ashareurl'] = base_url () . "answer/info/" . $aid;
			$data['ashare_content'] = $this->changeImgStrtonone($ashare_content);
			$data['qshare_content'] = $this->changeImgStrtonone($qshare_content);
			//时间处理
			$data['updateTime'] = $this->from_time(strtotime($data['updateTime']));
		    $data['createTime'] = $data['createTime'];
			$data['questionInfo']['info']['updateTime'] = $this->from_time(strtotime($data['questionInfo']['info']['updateTime']));

			//Util::echo_format_return(_SUCCESS_, $data);
			//exit;
			$data['askgid'] = $data['gameInfo']['absId'];
			$data['guid'] = $uid;

			$timenow =  time();
			if($flag){
				$flag_letter = substr($flag,0,1);
				$flag = substr($flag,1);
				if($flag > $timenow){
					$data['flag'] = $flag_letter == 'a' ? 1 : 2;
				}else{
					$data['flag'] = 3;
				}
			}

			//用户禁止判断
			$res = $this->common_model->is_ban_user();
			if($res){
				$data['is_ban'] = 1;
			}else{
				$data['is_ban'] = 0;
			}

			//拼装seo信息
			$seotitle = $this->global_func->cut_str(strip_tags($data['shareContent']),30);
			$seokeywords = $data['gameInfo']['abstitle'];
			$seodescription = $this->global_func->cut_str(strip_tags($data['shareContent']),100);
			$seo = array(
		            'title' => $seotitle."_".$data['gameInfo']['abstitle']."问答_全民手游攻略",
					'keywords' => trim($seokeywords, ','),
					'description' => $seodescription
			);
			$this->smarty->assign('seo', $seo);

		    $this->smarty->assign('data', $data);
		    $this->smarty->view ( 'answer.tpl' );
			//Util::echo_format_return(_SUCCESS_, $return,  $data);
			return 1;
		} catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}

	//删除答案方法
	public function answer_del($aid){
		$aid = $this->global_func->filter_int($aid);
		$uid = $this->user_id;

		try {
			if (empty($uid) || empty($aid)) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}

			$question_info = $this->qa_model->get_answer_info($uid, $aid);
			$qid = $question_info['questionInfo']['absId'];

			$this->common_model->trans_begin();

			if (!is_numeric($aid)) {
				throw new Exception('aid error', _PARAMS_ERROR_);
			}

			$this->qa_model->answer_del($aid, $uid);
			$this->common_model->trans_commit();
			$return = array(
			);

			//定义调整页面
			$timeflaa = time() + 5;
			$go_url = base_url() . 'question/info/' . $qid . "/b{$timeflaa}/";

			header('Location:' . $go_url);
			//Util::echo_format_return(_SUCCESS_, $return, '删除成功');
			return 1;
		} catch (Exception $e) {
			$this->common_model->trans_rollback();//

			header('Content-Type:text/html;charset=utf-8');
			echo "<script>alert('".  $e->getMessage() ."');window.history.back();</script>";
			//Util::echo_format_return($e->getCode(), array(), $e->getMessage());
			return 0;
		}
	}

	/*
	 * 精确时间间隔函数
	 * $time 发布时间 如 1356973323
	 * $str 输出格式 如 Y-m-d H:i:s
	 * 半年的秒数为15552000，1年为31104000，此处用半年的时间
	 */
	function from_time($time,$str='m-d'){
	    isset($str)?$str:$str='m-d';
	    $way = time() - $time;
	    $r = '';
	    if($way < 60){
	        $r = '刚刚';
	    }elseif($way >= 60 && $way <3600){
	        $r = floor($way/60).'分钟前';
	    }elseif($way >=3600 && $way <86400){
	        $r = floor($way/3600).'小时前';
	    }elseif($way >=86400 && $way <2592000){
	        $r = date($str,$time);
	    }elseif($way >=2592000 && $way <15552000){
	        $r = date($str,$time);
	    }else{
	        $r = date('Y-m-d H:i:s',$time);
	    }
	    return $r;
	}

	/**
	 * 图片去除
	 **/
	public function changeImgStrtonone($str)
	{
		$content=$str;
		$pattern = '/\[!--IMG_(\d+)--\]/';
		$new_str = $str;

		$new_str = preg_replace($pattern, "", $new_str);
		return $new_str;
	}

}
