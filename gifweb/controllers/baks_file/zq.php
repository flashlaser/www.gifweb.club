<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

/**
 * 
 * @name Zq
 * @desc 攻略WAP专区控制类
 *
 * @author	 wangbo8
 * @date 2015年12月15日
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 */
class Zq extends MY_Controller {
	public function __construct() {
		parent::__construct ();

		// $_SERVER['SERVER_NAME'] = 'www.wan68.com';
		// $back_url = $url='http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
		$back_url = $url=base_url().$_SERVER["REQUEST_URI"];
		$this->smarty->assign('back_url', $back_url);

		$this->load->model('follow_model');
		$this->load->model('game_model');
		$this->load->model('recommend_model');
		$this->load->model('gl_model');
		$this->load->model('user_model');
		$this->load->model('article_model');
		$this->load->model('like_model');
	}


	public function index() {
		//获取用户UID
		$guid = $this->user_id;
		$expire_time = 60 * 10;

		//分平台处理
		$platform = Util::getBrowse();

		if($platform != 'ios' && $platform != 'android'){
			$platform = 'pc';
		}
		$this->platform = $platform;

		//memcache缓存获取
		//1 获取正常数据信息列表
		$cache_normal_list_key = sha1('game_list1_normal_'. ENVIRONMENT . $platform . ":wap:new");
		$cache_normal_list = $this->cache->redis->get ( $cache_normal_list_key );
		$cache_normal_list && $cache_normal_list = json_decode($cache_normal_list, true);

		//调试使用
		//$cache_normal_list = false;

		if($guid){
			//2 获取关注信息列表
			$cache_attentioned_list_key = sha1('game_list1_attentioned_'. ENVIRONMENT .$guid.'_'.$platform . ":wap");
			$cache_attentioned_list = $this->cache->redis->get ( $cache_attentioned_list_key );
			$cache_attentioned_list && $cache_attentioned_list = json_decode($cache_attentioned_list, true);
		}

		try{
			//游戏列表［区分平台］
			$game_id_arr = array(); //初始化三类游戏ID数组

			//判断正常游戏列表是否有数据
			if ($cache_normal_list === false) {

				//入库获取游戏列表
				$info = $this->game_model->get_game_list_all();

				//循环保存游戏ID
				foreach ($info as $v) {
					$game_id_arr[] = $v['id'];
				}
			}

			if($guid){
				//获取当前用户关注游戏列表
				if ($cache_attentioned_list === false) {
					$infoss = $this->follow_model->get_follow_info($guid,3,-1,-1);
					$infoss || $infoss = array();
					foreach ($infoss as $v) {
						$game_id_arr[] = $v['mark'];
					}
				}
			}

			//游戏ID列表有数据，则到CMS抓取更详细信息
			if ($game_id_arr) {
				// 缓存中没数据
				$game_id_arr = array_unique($game_id_arr);
				$cms_game_format_info = $this->game_model->get_cms_game_list_info($game_id_arr);
			}

			//开始重新拼装各类别游戏列表
			//1 正常游戏列表
			if ($cache_normal_list === false) {
				$normalList = array();
				foreach ($info as $k => $v){
					$cms_game_info = $cms_game_format_info[$v['id']];
					if(empty($cms_game_info['logo'])){
						continue;
					}
					$_arr = array();
					$_arr['absId'] = (string) $v['id'];
					$_arr['abstitle'] = (string) $v['abstitle'];
					$_arr['initialsEng'] = $cms_game_info['proLetters'][0] ? (string) $cms_game_info['proLetters'][0] : '';
					$_arr['absImage'] =$cms_game_info['logo'] ? $cms_game_info['logo'] : '';
					$_arr['attentionCount'] = (int) $info[$k]['attentionCount'];
					$_arr['packageURL'] =$cms_game_info['packageURL'] ? array_filter(explode("\r\n",$cms_game_info['packageURL'])) : array();//用于检测是否安装

					$normalList[] = $_arr;
				}

				//常规数据按字母顺序重新拼装
				$normalList = $this->_convert_normallist_arr($normalList);
			} else {
				$normalList = $cache_normal_list;
			}

			if($guid){
				//3 关注游戏列表
				if ($cache_attentioned_list === false) {
					$attentionedList = array();
					foreach($infoss as $k1 => $v1){
						$infoss[$k1] = $this->game_model->get_game_row($v1['mark'],$platform);
						$cms_game_info = $cms_game_format_info[$v1['mark']];
						if(empty($cms_game_info['logo'])){
							continue;
						}

						$_arr1 = array();
						$_arr1['absId'] = (string) $v1['mark'];
						$_arr1['abstitle'] = $infoss[$k1]['abstitle'] ? $infoss[$k1]['abstitle'] : $cms_game_info['title'];
						$_arr1['initialsEng'] = $cms_game_info['proLetters'][0] ? (string) $cms_game_info['proLetters'][0] : '';
						$_arr1['absImage'] =$cms_game_info['logo'] ? $cms_game_info['logo'] : '';
						$_arr1['attentionCount'] = (int) $infoss[$k1]['attentionCount'];
						$_arr1['packageURL'] =$cms_game_info['packageURL'] ? array_filter(explode("\r\n",$cms_game_info['packageURL'])) : array();//用于检测是否安装－－－暂无

						$attentionedList[] = $_arr1;
					}

					$attentionedList_tmp = array();
					$attentionedList_tmp['list'] = $attentionedList;
					if(is_array($attentionedList) && count($attentionedList) > 0){
						$attentionedList_id = array();

						//获取关注ID数组
						foreach($attentionedList as $vo){
							$attentionedList_id[] = $vo['absId'];
						}
					}

					$attentionedList_tmp['id_list'] = empty($attentionedList_id) ? array() : $attentionedList_id;
					$attentionedList = $attentionedList_tmp;
				} else {
					$attentionedList = $cache_attentioned_list;
				}
			}

			//数据判断保存
			$data['normalList'] =$normalList ? $normalList : array();
			//$data['recommendList'] =$recommend ? $recommend : array();
			$data['attentionedList'] =$attentionedList ? $attentionedList : array('id_list'=>array());

			//数据入MC缓存
			if ($cache_normal_list === false) {
				$this->cache->redis->save ( $cache_normal_list_key, json_encode($data['normalList']), $expire_time );
			}

			if($guid){
				if ($cache_attentioned_list === false) {
					$this->cache->redis->save ( $cache_attentioned_list_key, json_encode($data['attentionedList']), $expire_time );
				}
			}

			$data['guid'] = $guid;
			$data['navflag'] = 'zq';

			//拼装seo信息
			$seo = array(
					'title' => '游戏专区_全民手游攻略',
					'keywords' => '手游攻略，单机攻略，网游攻略，游戏专区，全民手游攻略',
					'description' => '全民手游攻略为玩家量身打造的一款最全的手游攻略。这里有梦幻西游攻略，全民飞机大战攻略，大话西游攻略，全民突击攻略，火影忍者攻略，王者荣耀攻略，热血传奇攻略，全民无双攻略，天天爱消除攻略，神武2攻略等。'
			);
			$this->smarty->assign('seo', $seo);

		    $this->smarty->assign('data', $data);
		    $this->smarty->view ( 'zq/zq.tpl' );

			//Util::echo_format_return(_SUCCESS_, $data);
			//exit;
		}catch(Exception $e){
			//var_dump($e);
			exit('数据获取错误');
		}
	}


	//通过获取的字母，获取对应游戏列表信息
	public function get_game_list_by_letter($letter) {
		//获取用户UID
		$guid = $this->user_id;
		$expire_time = 60 * 60;

		//分平台处理
		$platform = Util::getBrowse();

		if($platform != 'ios' && $platform != 'android'){
			$platform = 'pc';
		}
		$this->platform = $platform;

		//memcache缓存获取
		//1 获取正常数据信息列表
		$cache_normal_list_key = sha1('game_list_normal_by_letter_'. ENVIRONMENT . $platform . ":wap:" . $letter);
		$cache_normal_list = $this->cache->redis->get ( $cache_normal_list_key );
		$cache_normal_list && $cache_normal_list = json_decode($cache_normal_list, true);

		//调试使用
		//$cache_normal_list = false;

		try{
			$str = ord($letter);

			if($str<=64 || $str>=91){
			   exit('字母有误');
			}

			//游戏列表［区分平台］
			$game_id_arr = array(); //初始化三类游戏ID数组

			//判断正常游戏列表是否有数据
			if ($cache_normal_list === false) {
				//入库获取游戏列表
				//$info = $this->game_model->get_game_list_by_letter($letter, 0);
				$info = $this->game_model->get_game_list_all();
				//循环保存游戏ID
				foreach ($info as $v) {
					$game_id_arr[] = $v['id'];
				}
			}

			if($guid){
				//初始化当前用户关注列表
				$attentionedList_id = array();

				//获取当前用户关注游戏列表
				$infoss = $this->follow_model->get_follow_info($guid,3,-1,-1);
				$infoss || $infoss = array();
				foreach ($infoss as $v) {
					$game_id_arr[] = $v['mark'];
					$attentionedList_id[] = $v['mark'];
				}

			}

			//游戏ID列表有数据，则到CMS抓取更详细信息
			if ($game_id_arr) {
				// 缓存中没数据
				$game_id_arr = array_unique($game_id_arr);
				$cms_game_format_info = $this->game_model->get_cms_game_list_info($game_id_arr);
			}

			//开始重新拼装各类别游戏列表
			//1 正常游戏列表
			if ($cache_normal_list === false) {
				$normalList = array();
				foreach ($info as $k => $v){
					$cms_game_info = $cms_game_format_info[$v['id']];
					if(empty($cms_game_info['logo'])){
						continue;
					}
					$_arr = array();
					$_arr['absId'] = (string) $v['id'];
					$_arr['abstitle'] = (string) substr_forecast(array('str'=>$v['abstitle'],'num'=>30,'dot'=>'...' ));
					$_arr['initialsEng'] = $cms_game_info['proLetters'][0] ? (string) $cms_game_info['proLetters'][0] : '';
					/*
					if(!$_arr['initialsEng'] ){
						continue;
					}
					*/
					$_arr['absImage'] =$cms_game_info['logo'] ? $cms_game_info['logo'] : '';
					$_arr['attentionCount'] = (int) $info[$k]['attentionCount'];
					$_arr['packageURL'] =$cms_game_info['packageURL'] ? array_filter(explode("\r\n",$cms_game_info['packageURL'])) : array();//用于检测是否安装

					//增加是否关注的标识
					//$_arr['is_attion'] = in_array($v[id], $attentionedList_id) ? true : false;
					//$_arr['uid_login'] = $guid ? true : false;
					$normalList[] = $_arr;
				}

				//常规数据按字母顺序重新拼装
				$normalList = $this->_convert_normallist_arr($normalList, false);

				$let_up = strtoupper($letter);
				$normalList = $normalList['return_arr'][$let_up];
			} else {
				$normalList = $cache_normal_list;
			}

			//数据判断保存
			$data['normalList'] =$normalList ? $normalList : array();

			//数据入MC缓存
			if ($cache_normal_list === false) {
				$this->cache->redis->save ( $cache_normal_list_key, json_encode($data['normalList']), $expire_time );
			}

			//数据加工
			if(is_array($data['normalList']) && count($data['normalList']) > 0){
				foreach($data['normalList'] as &$vo){
					$vo['is_attion'] = in_array($vo['absId'], $attentionedList_id) ? true : false;
					$vo['uid_login'] = $guid ? true : false;
				}
			}

			Util::echo_format_return(_SUCCESS_, $data);
			exit;
		}catch(Exception $e){
			//var_dump($e);
			exit('数据获取错误');
		}
	}

	//专区聚合页展示
	public function juhe_page($gameId){
		$gameId = $this->global_func->filter_int($gameId);
		$guid = $this->user_id; //获取用户ID
		//$fromZone	  = $this->input->get('fromZone',true) ;
		$fromZone = 1;

		//执行
		try{
			//判断游戏ID
			if (empty($gameId)) {
				throw new Exception('参数错误', _PARAMS_ERROR_);
			}

			//获取平台类型
			$this->platform = Util::getBrowse();	

			//获取游戏信息
			$cms_info = $this->game_model->get_cms_game_info($gameId);
			$cms_info = $cms_info[0];

			//判断是否有游戏信息
			if (empty($cms_info)) {
				throw new Exception('没有这个游戏', _PARAMS_ERROR_);
			}

			//获取攻略分类集合
			$info_a = $this->gl_model->get_category_row($gameId);

			//不同来源处理
			if($this->platform == 'android'){//android来源
				$hidden_type = $info_a['android_type'];
			}else{//ios来源
				$hidden_type = $info_a['ios_type'];
			}
			if($hidden_type == 1){
				$is_hidden = false;
			}else{
				$is_hidden = true;
			}

			//获取游戏具体信息
			$info_a['absImage'] = $cms_info['logo'];
			if (empty($info_a)) {
				throw new Exception('没有这个游戏', _PARAMS_ERROR_);
			}

			//获取当前游戏的二级分类
			$info_b = $this->gl_model->get_category_list($info_a['id']);

			//循环遍历二级分类，获得三级分类信息
			if(!empty($info_b) || $info_b != array()){
			    /*批量查询raiderCount*/
			    $categorys = "";
				foreach ($info_b as $k => $v ){
				    if($categorys){
				        $categorys .= ",".$v['absId'];
				    }else{
				        $categorys = $v['absId'];
				    }
				}
				$articleData = $this->gl_model->findCmsGlArrCount($categorys);
				
				foreach ($info_b as $k => $v ){
					$info_c = $this->gl_model->get_category_list($v['id']);

					$info_b[$k]['raiderCount'] =  $articleData[$k][$v['absId']];
					if($info_c){
						if(count($info_c) >=2){
							array_unshift($info_c,array('absId'=>$v['absId'],'abstitle'=>'全部'));
						}
						$info_b[$k]['item'] = $info_c;
					}
					unset($info_b[$k]['id']);
				}
			}

			//判断用户是否已经关注改游戏
			$collected = $this->follow_model->is_follow($guid,3,$gameId);

			/*预留
			//焦点对象数据［区分平台］
			$jiaodian_recommend = $this->recommend_model->get_recommend_list(4,$gameId);//推荐游戏

			$jiaodianRecommend =array();
			foreach ($jiaodian_recommend as $k_1 => $v_1) {
				if ($v_1['type'] == 1) {
					$jiaodianRecommend[$k_1]['type'] = 2;
				} elseif ($v_1['type'] == 2) {
					$jiaodianRecommend[$k_1]['type'] = 0;
				} elseif ($v_1['type'] == 3) {
					$jiaodianRecommend[$k_1]['type'] = 1;
				} elseif ($v_1['type'] == 4) {
					$jiaodianRecommend[$k_1]['type'] = 3;
				} elseif ($v_1['type'] == 5) {
					$jiaodianRecommend[$k_1]['type'] = 4;
				} elseif ($v_1['type'] == 6) {
					$jiaodianRecommend[$k_1]['type'] = 5;
				}
				$jiaodianRecommend[$k_1]['abstitle'] = $v_1['title'];
				if ($v_1['type'] == '4' || $v_1['type'] == '5' || $v_1['type'] == '6') {
					$jiaodianRecommend[$k_1]['absId'] = $v_1['param'];
				} else {
					$jiaodianRecommend[$k_1]['webUrl'] = $v_1['param'];
				}
				$jiaodianRecommend[$k_1]['absImage'] = 'http://store.games.sina.com.cn/'.$v_1['img'];
			}
			*/

			//快捷对象数据［区分平台］
			$kuaijie_recommend = $this->recommend_model->get_recommend_list(3,$gameId);//推荐游戏

			$kuaijieRecommend =array();
			foreach ($kuaijie_recommend as $k_1 => $v_1) {
				if ($v_1['type'] == 1) {
					$kuaijieRecommend[$k_1]['type'] = 2;
				} elseif ($v_1['type'] == 2) {
					$kuaijieRecommend[$k_1]['type'] = 0;
				} elseif ($v_1['type'] == 3) {
					$kuaijieRecommend[$k_1]['type'] = 1;
				} elseif ($v_1['type'] == 4) {
					$kuaijieRecommend[$k_1]['type'] = 3;
				} elseif ($v_1['type'] == 5) {
					$kuaijieRecommend[$k_1]['type'] = 4;
				} elseif ($v_1['type'] == 6) {
					$kuaijieRecommend[$k_1]['type'] = 5;
				}
				$kuaijieRecommend[$k_1]['abstitle'] = $v_1['title'];
				if ($v_1['type'] == '4' || $v_1['type'] == '5' || $v_1['type'] == '6') {
					$kuaijieRecommend[$k_1]['absId'] = $v_1['param'];

					switch($v_1['type']){
						case 4:
							$kuaijieRecommend[$k_1]['webUrl'] = "/question/info/" . $v_1['param'];
							break;
						case 5:
							$kuaijieRecommend[$k_1]['webUrl'] = "/answer/info/" . $v_1['param'];
							break;
						case 6:
							$kuaijieRecommend[$k_1]['webUrl'] = "/raiders/info/" . $v_1['param'];
							break;
					}
				} else {
					$kuaijieRecommend[$k_1]['webUrl'] = $v_1['param'];
				}
				// $kuaijieRecommend[$k_1]['absImage'] = 'http://store.games.sina.com.cn/'.$v_1['img'];
				$kuaijieRecommend[$k_1]['absImage'] = gl_img_url($v_1['img']);
			}

			/*预留
			//编辑推荐数据［区分平台］
			$juhe_recommend = $this->recommend_model->get_recommend_list(2,$gameId);//推荐游戏
			$juheRecommends = array();
			foreach ($juhe_recommend as $k_1 => $v_1) {
				if($v_1['area']) {
					$juheRecommend =array();
					if ($v_1['type'] == 1) {
						$juheRecommend['type'] = 2;
					} elseif ($v_1['type'] == 2) {
						$juheRecommend['type'] = 0;
					} elseif ($v_1['type'] == 3) {
						$juheRecommend['type'] = 1;
					} elseif ($v_1['type'] == 4) {
						$juheRecommend['type'] = 3;
					} elseif ($v_1['type'] == 5) {
						$juheRecommend['type'] = 4;
					} elseif ($v_1['type'] == 6) {
						$juheRecommend['type'] = 5;
					}
					$juheRecommend['abstitle'] = $v_1['title'];
					if ($v_1['type'] == '4' || $v_1['type'] == '5' || $v_1['type'] == '6') {
						$juheRecommend['absId'] = $v_1['param'];
					} else {
						$juheRecommend['webUrl'] = $v_1['param'];
					}
					$juheRecommends[] =$juheRecommend;
				}
			}
			*/

			unset($info_a['ios_id']);
			unset($info_a['android_id']);
			unset($info_a['android_type']);
			unset($info_a['ios_type']);
			$raidersClassList = $info_b;
			unset($info_a['web_url']);
			unset($raidersClassList['attentionCount']);
			unset($raidersClassList['web_url']);
			$data  = $info_a;
			unset($data['id']);
			$data['absImage'] =$data['absImage'] ? $data['absImage'] : '';
			$data['absId'] 	=$info_a['id'] ? $info_a['id'] : '';
			$data['packageURL'] =$cms_info['packageURL'] ? array_filter(explode("\r\n",$cms_info['packageURL'])) : array();
 			//$data['packageURL'] = array();
			$data['initialsEng'] =$cms_info['proLetters'][0] ? (string) $cms_info['proLetters'][0] : '';
			$data['buyAddress'] =$cms_info['buyUrl'] ? $cms_info['buyUrl'] : '';//  cms 购买地址
			$data['hidenAction'] =$is_hidden;// 后台IOS可否下载
			$data['attentionCount'] =(int)$data['attentionCount'];//该游戏的关注数－－－暂无   后台添加   关注数基数 、 正式关注数［不可改］
			$data['attentioned'] =$collected ? true : false ;//当前用户是否已关注该游戏\

			//$data['focusList'] =$fromZone ? $jiaodianRecommend : array();
 			//$data['focusList'] =array();
			$data['shortcutList'] =$fromZone ? $kuaijieRecommend : array();//快捷入口数据

			//$data['recommendList'] =$juheRecommends;//编辑推荐
			$data['raidersClassList'] =$raidersClassList;//攻略分类集合

			//=====较前者APP增加的数据部分开始====
			$data['wapadd']['size'] = $cms_info['size'];//游戏大小

			if($cms_info['gameTags2'] || $cms_info['gameTags3']){
				$data['type'] 			= array($cms_info['gameTags2'],$cms_info['gameTags3']);
			}
			$data['askgid'] = $gameId;
			$data['navflag'] = 'zq';
			$data['guid'] = $guid;

			//获得是手机还是PC
			$is_mobile = $this->global_func->isMobile();
			$data['is_mobile'] = $is_mobile ? 1 : 2;
			$data['gameId'] = $gameId;
			//=====较前者APP增加的数据部分结束====

			//拼装seo信息
			$seotitle = $data['abstitle'] . '攻略| ' . $data['abstitle'] . '手游攻略_全民手游攻略';
			foreach($data['raidersClassList'] as $vo){
				$seokeywords .= $data['abstitle'] . $vo['abstitle'] . ",";
			}
			$seodescription = $data['abstitle'] . '攻略频道为您提供最全最新的' . $data['abstitle'] . '攻略，这里有你想要的答题器，模拟器，并且有大神级玩家的攻略帮你升级、通关。';

			$seo = array(
					'title' => $seotitle,
					'keywords' => trim($seokeywords, ','),
					'description' => $seodescription
			);
			$this->smarty->assign('seo', $seo);
		    $this->smarty->assign('data', $data);
		    $this->smarty->view ( 'zq/zqjh.tpl' );

			//Util::echo_format_return(_SUCCESS_, $data);
			//exit;
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}

	//处理游戏列表按字母排序问题
	private function _convert_normallist_arr($arr, $flag = true) {
		if (empty($arr)) return $arr;

		//数据初始化
		$return_letter_arr = array(); //初始化字母数组
		$return_arr = array(); //初始化返回数组
		$return_more_letters = array(); //初始化应该显示更多的数组

		foreach ($arr as $k => $v) {
			//获取当前数据
			$letter = strtoupper($v['initialsEng']);
			$str = ord($letter);

			if($str<=64 || $str>=91){
			    $letter = "ZZZ";
			}

			$return_letter_arr[] = $letter;

			if($flag){
				//增加判断逻辑，每个字母只要8个游戏，超过8个则不放入
				if(count($return_arr[$letter]) >= 9){
					$return_more_letters[] = $letter;
					//array_pop($return_arr[$letter]);
				}else{
					$return_arr[$letter][] = $v;
				}
			}else{
				$return_arr[$letter][] = $v;
			}
		}

		//处理结果
		$return_letter_arr = array_unique($return_letter_arr);
		$return_more_letters = array_unique($return_more_letters);
		ksort($return_arr);
		sort($return_letter_arr);
		sort($return_more_letters);

		return array('return_letter_arr' => $return_letter_arr, 'return_arr' => $return_arr, 'return_more_letters' => $return_more_letters);
	}

}
