<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

/**
 * @version gift.php
 * @author wangbo8
 * @date 2016年7月5日
 */
//礼包信息控制for sinagame gl
class Gift extends MY_Controller {
	public function __construct() {
		parent::__construct ();
		$this->load->model('gift_model');
		$this->load->model('common_model');
	}

	public function getbobo(){
		echo $res = Util::getRealIp();
	}

	//获取游戏礼包列表
	public function gameGiftList(){
		$gameId = ( int ) $this->input->get ( 'gameId', true );
		//$type = ( int ) $this->input->get ( 'type', true ) ? ( int ) $this->input->get ( 'type', true ) : 0 ;
		$guid = $this->input->get ( 'guid', true );
	    $page = ( int ) $this->input->get ( 'page', true ) ? ( int ) $this->input->get ( 'page', true ) : 0;
	    $count = ( int ) $this->input->get ( 'count', true ) ? ( int ) $this->input->get ( 'count', true ) : 10;
		$max_id = ( int ) $this->input->get ( 'max_id', true );

		try {
	        if (empty($gameId)) {
	            throw new Exception('参数错误', _PARAMS_ERROR_);
	        }

	        //攻略游戏id转化为新手卡id
	        $gameId = $this->gift_model->get_newcard_gid_by_glgid($gameId, $this->platform);
	        if (!$gameId) {
	            throw new Exception('游戏id错误', _PARAMS_ERROR_);
	        }

			$page < 1 && $page = 1;
			$offsize = ($page - 1) * $count;

			//获取礼包数据
			$_newcardList = $this->gift_model->getNewCardListByGameId($gameId, $page, $count, $guid, $max_id);
			$_newcardList = $_newcardList['list'];
			//获取游戏数据
			//$_gameInfo = $this->gift_model->getGameDetailInfo($gameId);

			//初始化返回数组
		 	$newcardList = array();
		 	foreach ($_newcardList as $_k => $_v) {
 				$tmpNewcardInfo = array(); //临时卡数组初始化
 				$tmpNewcardInfo['origintype'] = (int)$_v['origintype']; // 礼包类型（0为新手卡， 1为973卡） 
 				$tmpNewcardInfo['absId'] = $_v['item_id']; //礼包ID
 				$tmpNewcardInfo['abstitle'] = $_v['item_name']; //礼包名称
 				$tmpNewcardInfo['absImage'] = $_v['photo']; //礼包缩略图

				//从缓存中获取卡数量信息
				$card_count_cache = $this->gift_model->get_card_count($_v['item_id']);

				$tmpNewcardInfo['totalCardNum'] = $card_count_cache['card_amount']; //礼包下的卡的总量
				$tmpNewcardInfo['remainCardNum'] = $card_count_cache['card_left']; //礼包下的卡剩余量

				//百分比处理
 				if($tmpNewcardInfo['totalCardNum'] > 0){
 					$left_tmp = $tmpNewcardInfo['remainCardNum']/$tmpNewcardInfo['totalCardNum'];
					$left_tmp = $left_tmp * 100;
	 				if($left_tmp > 99 && $left_tmp < 100){
	 					$tmpNewcardInfo['left'] = 99;
	 				}else if($left_tmp > 0 && $left_tmp < 1){
	 					$tmpNewcardInfo['left'] = 1;
	 				}else if($left_tmp == 0){
	 					$tmpNewcardInfo['left'] = 0;
	 				}else{
	 					$tmpNewcardInfo['left'] = ceil($left_tmp);
	 				}
 				}else{
 					$tmpNewcardInfo['left'] = 0;
 				}

 				$tmpNewcardInfo['left'] = "" . $tmpNewcardInfo['left'];
 				$tmpNewcardInfo['updateTime'] = $_v['updateTime']; //礼包更新时间
 				if($guid){
	 				$tmpNewcardInfo['fetch'] = $this->gift_model->check_get_gift($guid, $_v['item_id']); //是否已经领取
	 				$tmpNewcardInfo['fetch'] = $tmpNewcardInfo['fetch'] ? 1 : 0;
 				}else{
 					$tmpNewcardInfo['fetch'] = 0;
 				}

 				$tmpNewcardInfo['attentioned'] = 1;  //是否已关注	(客户端定为1)
 				$tmpNewcardInfo['pauseLing'] = $_v['pauseLing']; //是否暂停领号
				$tmpNewcardInfo['prohibitTao'] = $_v['prohibitTao']; //是否禁止淘号
				$tmpNewcardInfo['attention'] = ""; //关注度

				//分享
				$tmpNewcardInfo['shareUrl'] = "http://ka.sina.com.cn/".$_v['item_id']; //分享地址
				$tmpNewcardInfo['shareContent'] = "我在 全民攻略for{$_v['gname']} 抢{$_v['gname']}礼包，巨多惊喜，快来一起抢~！"; //分享描述

 				$newcardList[] = $tmpNewcardInfo;
 				unset($tmpNewcardInfo);
		 	}

		 	$returnData = empty($newcardList) ? array('list'=>array()) : array('list'=>$newcardList);
	        Util::echo_format_return(_SUCCESS_, $returnData);
	        return 1;
	    } catch (Exception $e) {
	        Util::echo_format_return($e->getCode(), array(), $e->getMessage());
	        return 0;
	    }
	}

	//获取礼包详情
	public function giftDetail(){
		$giftId = ( int ) $this->input->get ( 'giftId', true );
		$origintype = ( int ) $this->input->get ( 'origintype', true ) ? ( int ) $this->input->get ( 'origintype', true ) : 0;
		$guid = $this->input->get ( 'guid', true );

		try {
	        if (empty($giftId)) {
	            throw new Exception('参数错误', _PARAMS_ERROR_);
	        }

			//获取礼包数据
			$newcardInfo = $this->gift_model->getNewCardDetailInfo($giftId, $origintype);

			//获取游戏详情
			$_gameInfo = $this->gift_model->getGameDetailInfo($newcardInfo['gid']);

			//初始化返回数组
			$newcardInfo_tmp = array();
			$newcardInfo_tmp['origintype'] = (int)$origintype;
			$newcardInfo_tmp['absId'] = $newcardInfo['item_id'];
			$newcardInfo_tmp['abstitle'] = $newcardInfo['item_name'];
			$newcardInfo_tmp['absImage'] = $newcardInfo['photo'];
			$newcardInfo_tmp['state'] = $newcardInfo['state'];

			//从缓存中获取卡数量信息
			$card_count_cache = $this->gift_model->get_card_count($giftId);
			$newcardInfo_tmp['totalCardNum'] = $card_count_cache['card_amount']; //礼包下的卡的总量
			$newcardInfo_tmp['remainCardNum'] = $card_count_cache['card_left']; //礼包下的卡剩余量

			//百分比处理
			if($newcardInfo_tmp['totalCardNum'] > 0){
				$left_tmp = $newcardInfo_tmp['remainCardNum']/$newcardInfo_tmp['totalCardNum'];
				$left_tmp = $left_tmp * 100;
				if($left_tmp > 99 && $left_tmp < 100){
					$newcardInfo_tmp['left'] = 99;
				}else if($left_tmp > 0 && $left_tmp < 1){
					$newcardInfo_tmp['left'] = 1;
				}else if($left_tmp == 0){
					$newcardInfo_tmp['left'] = 0;
				}else{
					$newcardInfo_tmp['left'] = ceil($left_tmp);
				}
			}else{
				$newcardInfo_tmp['left'] = 0;
			}
			$newcardInfo_tmp['left'] = "" . $newcardInfo_tmp['left'];

			$newcardInfo_tmp['updateTime'] = $newcardInfo['updateTime']; 
			if($guid){
				$newcardInfo_tmp['fetch'] = $this->gift_model->check_get_gift($guid, $giftId); 
				$newcardInfo_tmp['fetch'] = $newcardInfo_tmp['fetch'] ? 1 : 0;
			}else{
				$newcardInfo_tmp['fetch'] = 0;
			}

			$newcardInfo_tmp['pauseLing'] = $newcardInfo['pauseLing']; 
			$newcardInfo_tmp['prohibitTao'] = $newcardInfo['prohibitTao']; 
			//$newcardInfo_tmp['nextFetchTime'] = $newcardInfo['nextFetchTime'];
			//$newcardInfo_tmp['attention'] = 111;
			$newcardInfo_tmp['shareUrl'] = "http://ka.sina.com.cn/".$giftId;
			$newcardInfo_tmp['shareContent'] = "我在 全民攻略for{$newcardInfo['gname']} 抢{$newcardInfo['gname']}礼包，巨多惊喜，快来一起抢~！"; //分享描述

            $time = time();
            $nexttime = date("Y-m-d H:00:00", $time+3600);
            $newcardInfo_tmp['nextFetchTime'] = $nexttime;
            $newcardInfo_tmp['now'] = date("Y-m-d H:i:s",$time);

            if ($newcardInfo['show_status'] == 1) {
                $newcardInfo_tmp['state'] = 1;
            } else {
                $newcardInfo_tmp['state'] = 0;
            }

			//content 特殊处理
			$newcardInfo['award_detail'] = Util::html5ObjClean($newcardInfo['award_detail']);
			preg_match('<br />', $newcardInfo['award_detail'], $match);
			if (!empty($match)) {
 					$tmpCheck = explode('<br />', $newcardInfo['award_detail']);
			} else {
					$tmpCheck = explode('<div>', $newcardInfo['award_detail']);
			}
 			foreach ($tmpCheck as $tk => $tv) {
 					$tmpCheck[$tk] = trim(strip_tags($tmpCheck[$tk]));
 			}
 			$newcardInfo_tmp['content'] = $tmpCheck;
 			if (is_array($newcardInfo_tmp['content'])) {
 					$newcardInfo_tmp['content'] = Util::cleanArray($newcardInfo_tmp['content']);
 			}

 			//激活说明  http://api.g.sina.com.cn/ka/api/item/info
 			$temp = $this->gift_model->pregContent($newcardInfo['act_detail']);   //该参数需要单独处理

            if (!empty($temp)) {
                $newcardInfo_tmp['activateDescription'] = array();
                $newcardInfo_tmp['activateDescription']['content'] = $temp['content'];
                $newcardInfo_tmp['activateDescription']['images'] = $temp['attribute']['images'];
                $temp = $this->findUrl($newcardInfo_tmp['activateDescription']['content']);
                $newcardInfo_tmp['activateDescription']['content'] = $temp['content'];
                $newcardInfo_tmp['activateDescription']['links'] = $temp['links'];
            } else {
                $newcardInfo_tmp['activateDescription'] = $this->findUrl($newcardInfo['act_detail']);
            }

            /*  @TO DO 跟跟庆录确定时间 是否是valid_date
 			if ($newcardInfo['time_setting'] == null) {
 					$newcardInfo_tmp['redeemBegin'] = $this->exchange_date($newcardInfo['testdate']);
 					$newcardInfo_tmp['redeemEnd'] = date('Y-m-d H:i', strtotime('+6 month', strtotime($_newcardInfo['testdate'])));
 			} else {
 					$_tmpTimeSetting = json_decode($_newcardInfo['time_setting'], true);
 					if (isset($_tmpTimeSetting['validstart']) && isset($_tmpTimeSetting['validend'])) {
 							$newcardInfo_tmp['redeemBegin'] = $this->exchange_date($_tmpTimeSetting['validstart']);
 							$newcardInfo_tmp['redeemEnd'] = $this->exchange_date($_tmpTimeSetting['validend']);
 					} else {
	 						$newcardInfo_tmp['redeemBegin'] = $this->exchange_date($_newcardInfo['testdate']);
	 						$newcardInfo_tmp['redeemEnd'] = date('Y-m-d H:i', strtotime('+6 month', strtotime($_newcardInfo['testdate'])));
 					}
 			}
			*/

			$start = strtotime($newcardInfo['valid_date']['start']);
			$end = strtotime($newcardInfo['valid_date']['end']);

			//判断
			if($start > 0 && $end > 0){
				$newcardInfo_tmp['redeemBegin'] = $this->exchange_date($newcardInfo['valid_date']['start']);
				$newcardInfo_tmp['redeemEnd'] = $this->exchange_date($newcardInfo['valid_date']['end']);
			}else{
				$newcardInfo_tmp['redeemBegin'] = $this->exchange_date($newcardInfo['testdate']);
				$newcardInfo_tmp['redeemEnd'] = date('Y-m-d H:i', strtotime('+2 years', strtotime($newcardInfo['testdate'])));
			}

 			//通过游戏类型来确定平台
            if ($_gameInfo['gtype'] == 'Android') {
                $newcardInfo_tmp['platform'] = 2;
            } elseif ($_gameInfo['gtype'] == 'IOS') {
                $newcardInfo_tmp['platform'] = 1;
            } elseif ($_gameInfo['gtype'] == '网络游戏') {
                $newcardInfo_tmp['platform'] = 3;
            } elseif ($_gameInfo['gtype'] == '网页游戏') {
                $newcardInfo_tmp['platform'] = 4;
            } else { //各平台通用
                $newcardInfo_tmp['platform'] = 0;
            }

            if($guid){
	            //获取兑换码信息
	            $tmpNewCardCodeInfo = $this->gift_model->getNewCardCodeInfo($giftId, $guid);
            }else{
            	$tmpNewCardCodeInfo = array();
            }

            //初始化结果
            $newcardInfo_tmp['redeemCodeInfo'] = array();
 			if (!empty($tmpNewCardCodeInfo)) {
 					$activeCodeArray = explode(",", $tmpNewCardCodeInfo['redeem_code']);

 					$newcardInfo_tmp['redeemCodeInfo']['cardId'] = $tmpNewCardCodeInfo['id'];
 					if (count($activeCodeArray) == 1) {
 							$newcardInfo_tmp['redeemCodeInfo']['redeemCode'] = $activeCodeArray[0];
 					} elseif (count($activeCodeArray) == 2) {
 							$newcardInfo_tmp['redeemCodeInfo']['redeemCode'] = $activeCodeArray[0];
 							$newcardInfo_tmp['redeemCodeInfo']['password'] = $activeCodeArray[1];
 					} else {
 							$newcardInfo_tmp['redeemCodeInfo']['redeemCode'] = $activeCodeArray[0];
 							$newcardInfo_tmp['redeemCodeInfo']['password'] = $activeCodeArray[1];
 							$newcardInfo_tmp['redeemCodeInfo']['area'] = $activeCodeArray[2];
 					}
 					if (in_array($_gameInfo['gtype'], array('Android', 'IOS', 'Windows', '各平台通用'))) {
 							$newcardInfo_tmp['redeemCodeInfo']['activateUrl'] = $newcardInfo['acturl'];
 					}
 					$newcardInfo_tmp['redeemCodeInfo']['invalidtime'] = "兑换期间内";
 			} else {
 				$newcardInfo_tmp['redeemCodeInfo'] = null;
 			}

	        Util::echo_format_return(_SUCCESS_, $newcardInfo_tmp ? $newcardInfo_tmp: array());
	        return 1;
	    } catch (Exception $e) {
	        Util::echo_format_return($e->getCode(), array(), $e->getMessage());
	        return 0;
	    }

	}

	//礼包领取接口
	public function fetchGift(){
		$giftId = ( int ) $this->input->get ( 'giftId', true );
		$origintype = ( int ) $this->input->get ( 'origintype', true );
		$guid = $this->input->get ( 'guid', true );
		$ip = $this->input->get ( 'ip', true );
		$name = $this->input->get ( 'name', true );

		try {
	        if (empty($giftId)) {
	            throw new Exception('参数错误', _PARAMS_ERROR_);
	        }

	        //判断是否领取过礼包
	        $check_get_gift = $this->gift_model->check_get_gift($guid, $giftId);

	        if($check_get_gift){
	            throw new Exception('已经领取过礼包', _PARAMS_ERROR_);
	        }

	        //获取礼包信息
            $_newcardInfo = $this->gift_model->getNewCardDetailInfo($giftId, $origintype);

	        if(empty($_newcardInfo)){
	            throw new Exception('礼包信息不存在', _PARAMS_ERROR_);
	        }

	        //防刷开始
			$this->common_model->defence_repeat_hanle_go($guid, 'fetchGift', 2);

	        //领卡总限制
	        $check_get_gift_limit_res = $this->gift_model->check_get_gift_limit($giftId, $origintype, $guid, $ip, $name, $_newcardInfo);

	        //判断是否已经受限
	        if(!$check_get_gift_limit_res['res']){
		        //判断结果
		        switch($check_get_gift_limit_res['code']){
		        	case "1":
		        		throw new Exception('超过当前小时限制领取数量', _PARAMS_ERROR_);
		        		break;
		        	case "2":
		        		throw new Exception('领卡时间未到或已结束', _PARAMS_ERROR_);
		        		break;
		        	default:
		        		break;
		        }
	        }

	        /* 关于验证码与微博合法性的开发，需要跟刘乐确定是否保留，先完成领卡需求
            //验证码验证
             $code = $this->getCode($uid, $giftId);
             if (isset($_REQUEST['platform']) && $_REQUEST['platform'] == 'ios') {
                 if (isset($_REQUEST['version']) && in_array($_REQUEST['version'], array('4.1.3'))) {
                     if ($_newcardInfo['verify_type_ios'] == 1) { //普通验证码
                         if (!isset($_REQUEST['code']) || empty($_REQUEST['code'])) {
                             $return = array();
                             $param = "?g=".$giftId."&u=".$uid."&t=".$time."&k=".md5($giftId.$uid.$time."sinagameapp");
                             $return['codeImg'] = APP_HOST . "/game_api/code.php".$param;
                             Util::returnArray(1100, $return, '请求成功');
                         }
                         if ($code != $_REQUEST['code']) {
                             Util::returnArray(-100, array("a"=>$code), '验证码错误');
                         }
                     } else {

                     }
                 }
             } elseif (isset($_REQUEST['platform']) && $_REQUEST['platform'] == 'android') {
                 if (isset($_REQUEST['version']) && in_array($_REQUEST['version'], array('4.1.3'))) {
                     if ($_newcardInfo['verify_type_android'] == 1) { //普通验证码
                          if (!isset($_REQUEST['code']) || empty($_REQUEST['code'])) {
                             $return = array();
                             $param = "?g=".$giftId."&u=".$uid."&t=".$time."&k=".md5($giftId.$uid.$time."sinagameapp");
                             $return['codeImg'] = APP_HOST . "/game_api/code.php".$param;
                             Util::returnArray(1100, $return, '请求成功');
                         }
                         if ($code != $_REQUEST['code']) {
                             Util::returnArray(-100, array("a"=>$code), '验证码错误');
                         }
                     } elseif ($_newcardInfo['verify_type_android'] == 2) { //极验验证
                         if (!isset($_REQUEST['code']) || empty($_REQUEST['code'])) {
                             Util::returnArray(1101, array(), '请求成功');
                         }
                         $gee = new geelib();
                         $codeArr = json_decode($_REQUEST['code'], true);
                         if (!$gee->geetest_validate($codeArr['geetest_challenge'], $codeArr['geetest_validate'], $codeArr['geetest_seccode'])) {
                             Util::returnArray(-100, array("a"=>$code), '验证码错误');
                         }
                     } else {
                     }
                 } else {
                     //Util::returnArray(-100, array(), "领取失败，请更新版本！\n更新方法：发现->设置->检测新版本");
                 }
             } else {

             }
                    //-->验证微博用户合法性
                    $weibo = new Weibo();
                    $tokenInfo = $weibo->get_token_info($token);
                    if ( !isset($tokenInfo['expire_in']) || $tokenInfo['expire_in']<=0 || $tokenInfo['uid']!=$uid ) {
                        Util::returnArray(1030, array(), '微博账号授权过期');
                    }
                    //<--验证微博用户合法性
			*/

 			//有效时间指的是进入淘号库时间
 			$_gameInfo = $this->gift_model->getGameDetailInfo($_newcardInfo['gid']);

 			//防刷结束
 			$this->common_model->defence_repeat_hanle_end($guid, 'fetchGift', 2);
 			$re = $this->gift_model->fetchNewCard($giftId, $guid, $name); //调用接口领卡
			if ($re['result'] == 'fail' || !$re['data']['active_code']) {
                if ($re['errorcode'] == '0226') {  //手机限制领取卡
                    Util::echo_format_return(-100, array(), "此卡在客户端发放完毕  请到网页上领取。领取地址:ka.sina.com.cn");
                } elseif ($re['codeno'] == '0205') {  //领取失败
                    Util::echo_format_return(-100, array(), "领取失败，请稍后再试");
                } elseif ($re['codeno'] == '1023') {  //领取失败
                    Util::echo_format_return(-100, array(), "礼包已领完");
                } elseif ($re['msg']) { 
                    Util::echo_format_return(-100, array('ll' => $re), $re['msg']);
                } else {//其他原因
                    Util::echo_format_return(-100, array('ll' => $re), "领取失败，请稍后再试");
                }
 			} else {
 				//领卡成功，领卡信息入库
 				//获取开始结束时间
				$start = strtotime($_newcardInfo['valid_date']['start']);
				$end = strtotime($_newcardInfo['valid_date']['end']);

				//判断
				if($start > 0 && $end > 0){
					$starttime = $this->exchange_date($_newcardInfo['valid_date']['start']);
					$endtime = $this->exchange_date($_newcardInfo['valid_date']['end']);
				}else{
					$starttime = $this->exchange_date($_newcardInfo['testdate']);
					$endtime = date('Y-m-d H:i', strtotime('+2 years', strtotime($_newcardInfo['testdate'])));
				}

 				$data = array(
 						'uid' => $guid,
 						'item_id' => $giftId,
 						'game_id' => $_newcardInfo['gid'],
 						//'origintype' => $_newcardInfo['origintype'],
 						//'card_id' => $giftId, //跟刘乐确定
 						'card_id' => $re['data']['card_id'],
 						'redeem_code' => $re['data']['active_code'],
 						'status' => 1, //入库定为1
 						'starttime' => $starttime,
 						'endtime' => $endtime,
 						'create_time' => date('Y-m-d H:i:s'),
 						'update_time' => date('Y-m-d H:i:s'),
 					);

 				//调用方法入库
 				$insert_res = $this->gift_model->insert_getcard_info($data);

 				//更新缓存中卡的总数跟剩余数
 				$this->gift_model->set_card_count_in_cache($giftId, $re['data']['card_amount'], $re['data']['card_left']);

 				$_tmpData = array();
 				$_tmpData['cardId'] = $insert_res; //卡号
 				$_tmpCode = explode(',', $re['data']['active_code']);
 				if (count($_tmpCode) == 1) {
 						$_tmpData['redeemCode'] = $_tmpCode[0];
 				} elseif (count($_tmpCode) == 2) {
 						$_tmpData['redeemCode'] = $_tmpCode[0];
 						$_tmpData['password'] = $_tmpCode[1];
 				} else {
 						$_tmpData['redeemCode'] = $_tmpCode[0];
 						$_tmpData['password'] = $_tmpCode[1];
 						$_tmpData['area'] = $_tmpCode[2];
 				}

 				//$_newcardInfo['valid_date']['end']
 				if ($_newcardInfo['nextFetchTime'] == 0) {
 						$_tmpData['invalidtime'] = "0";
 				} else {
 						$_tmpData['invalidtime'] = $_newcardInfo['nextFetchTime'];
 				}

 				if (in_array($_gameInfo['gtype'], array('Android', 'IOS', 'Windows', '各平台通用'))) {
 						$_tmpData['activateUrl'] = $_newcardInfo['acturl'];
 				}

		        Util::echo_format_return(_SUCCESS_, $_tmpData ? $_tmpData: array());
		        return 1;
 			}
	    } catch (Exception $e) {
	        Util::echo_format_return($e->getCode(), array(), $e->getMessage());
	        return 0;
	    }
	}


	//获取游戏相关礼包数量接口
	public function getRelationGiftNum(){
		$gameId = ( int ) $this->input->get ( 'gameId', true );

		try {
	        if (empty($gameId)) {
	            throw new Exception('参数错误', _PARAMS_ERROR_);
	        }

	        //攻略游戏id转化为新手卡id
	        $gameId = $this->gift_model->get_newcard_gid_by_glgid($gameId, $this->platform);

	        if (!$gameId) {
	            throw new Exception('游戏id错误', _PARAMS_ERROR_);
	        }

	        //调用接口获取总数
	        $_relateCardList = $this->gift_model->getGiftCardNum_bygameId($gameId);
	        $totalCardNum = count($_relateCardList);

	        Util::echo_format_return(_SUCCESS_, $totalCardNum ? array('total'=>$totalCardNum): 0);
	        return 1;
	    } catch (Exception $e) {
	        Util::echo_format_return($e->getCode(), array(), $e->getMessage());
	        return 0;
	    }
	}

	//已领取礼包列表接口
	public function getUserCardlist(){
		$guid = ( float ) $this->input->get ( 'guid', true );
	    $page = ( int ) $this->input->get ( 'page', true ) ? ( int ) $this->input->get ( 'page', true ) : 0;
	    $count = ( int ) $this->input->get ( 'count', true ) ? ( int ) $this->input->get ( 'count', true ) : 10;
		$max_id = ( int ) $this->input->get ( 'max_id', true );
		$time = time();

		try {
	        if (empty($guid)) {
	            throw new Exception('参数错误', _PARAMS_ERROR_);
	        }
			$page < 1 && $page = 1;
			$offsize = ($page - 1) * $count;

			//调用方法获取当前用户礼包
			$gameList = $this->gift_model->getUserCard($guid, $page, $count);

			//初始化结果数组
			$myCardList = array();

			//循环遍历处理
			foreach ($gameList as $k => $v) {
				$_tmpMyCard = array();

				//获取礼包信息
				$_tmpCardInfo = $this->gift_model->getNewCardDetailInfo($v['item_id']);



				$_tmpMyCard['origintype'] = (int)$v['origintype'];
				$_tmpMyCard['absId'] = $v['item_id'];
				$_tmpMyCard['abstitle'] = $_tmpCardInfo['gname'] . $_tmpCardInfo['item_name'];
				$_tmpMyCard['absImage'] = $_tmpCardInfo['photo'];
				//$_tmpMyCard['timeout'] = ""; //需要判断

				$start = strtotime($v['starttime']);
				$end = strtotime($v['endtime']);

				//判断
				if($start > 0 && $end > 0){
					$_tmpMyCard['redeemBegin'] = $this->exchange_date($v['starttime']);
					$_tmpMyCard['redeemEnd'] = $this->exchange_date($v['endtime']);
				}else{
					//$_tmpMyCard['redeemBegin'] = $this->exchange_date($newcardInfo['testdate']);
					//$_tmpMyCard['redeemEnd'] = date('Y-m-d H:i', strtotime('+6 month', strtotime($newcardInfo['testdate'])));
				}

				/*
				if ($_tmpCardInfo['time_setting'] == null) {
 					$_tmpMyCard['redeemBegin'] = $this->exchange_date($_tmpCardInfo['testdate']);
 					$_tmpMyCard['redeemEnd'] = date('Y-m-d H:i', strtotime('+6 month', strtotime($_tmpCardInfo['testdate'])));
				} else {
						$_tmpTimeSetting = json_decode($_tmpCardInfo['time_setting'], true);
						if (isset($_tmpTimeSetting['validstart']) && isset($_tmpTimeSetting['validend'])) {
								$_tmpMyCard['redeemBegin'] = $this->exchange_date($_tmpTimeSetting['validstart']);
								$_tmpMyCard['redeemEnd'] = $this->exchange_date($_tmpTimeSetting['validend']);
						} else {
								$_tmpMyCard['redeemBegin'] = $this->exchange_date($_tmpCardInfo['testdate']);
								$_tmpMyCard['redeemEnd'] = date('Y-m-d H:i', strtotime('+6 month', strtotime($_tmpCardInfo['testdate'])));
						}
				}
				*/

				//兑换码初始化
				$_tmpMyCard['redeemCodeInfo'] = array();
				$_tmpMyCard['redeemCodeInfo']['cardId'] = $v['id'];

				$_tmpCode = explode(',', $v['redeem_code']);
				if (count($_tmpCode) == 1) {
					$_tmpMyCard['redeemCodeInfo']['redeemCode'] = $_tmpCode[0];
				} elseif (count($_tmpCode) == 2) {
					$_tmpMyCard['redeemCodeInfo']['redeemCode'] = $_tmpCode[0];
					$_tmpMyCard['redeemCodeInfo']['password'] = $_tmpCode[1];
				} else {
					$_tmpMyCard['redeemCodeInfo']['redeemCode'] = $_tmpCode[0];
					$_tmpMyCard['redeemCodeInfo']['password'] = $_tmpCode[1];
					$_tmpMyCard['redeemCodeInfo']['area'] = $_tmpCode[2];
				}
				$_tmpMyCard['redeemCodeInfo']['invalidtime'] = "兑换期间内";

				if($end > 0){
					//判断是否过期
					if ($time >$end) {
						$_tmpMyCard['timeout'] = 1;
					}else{
						$_tmpMyCard['timeout'] = 0;	
					}
				}else{
					$_tmpMyCard['timeout'] = 0;	
				}

				$myCardList[] = $_tmpMyCard;
			}

	        Util::echo_format_return(_SUCCESS_, $myCardList ? array('list' => $myCardList): array('list' =>array()));
	        return 1;
	    } catch (Exception $e) {
	        Util::echo_format_return($e->getCode(), array(), $e->getMessage());
	        return 0;
	    }
	}

	//用户淘号接口
	public function findGift(){
		$giftId = ( int ) $this->input->get ( 'giftId', true );
		$origintype = ( int ) $this->input->get ( 'origintype', true );
		$guid = $this->input->get ( 'guid', true );
		$ip = $this->input->get ( 'ip', true );

		try {
	        if (empty($giftId)) {
	            throw new Exception('参数错误', _PARAMS_ERROR_);
	        }

	        //获取礼包信息
            $_newcardInfo = $this->gift_model->getNewCardDetailInfo($giftId, $origintype);

 			//有效时间指的是进入淘号库时间
 			$_gameInfo = $this->gift_model->getGameDetailInfo($_newcardInfo['gid']);

            //获取旧卡列表
            $oldcardList = $this->gift_model->getOldcard($giftId);
            $oldcardList = $oldcardList['cards'];

		 	if (empty($oldcardList)) {
		        Util::echo_format_return(-100, array(), '暂时无法淘号');
		        return 1;
		 	} else {
 				//只取一条
                if (count($oldcardList) == 1) {
                    $active_code = $oldcardList[0];
                    $starttime = $_newcardInfo['valid_date']['start'];
                    $endtime = $_newcardInfo['valid_date']['end'];
                } else {
                	$bignum = count($oldcardList);
                	$randnum = rand(0,$bignum-1);
                    $active_code = $oldcardList[$randnum];
                    $starttime = $_newcardInfo['valid_date']['start'];
                    $endtime = $_newcardInfo['valid_date']['end'];
                }

 				$returnData = array();
		 		$_tmpCode = explode(',', $active_code);
		 		if (count($_tmpCode) == 1) {
		 			$returnData['redeemCode'] = $_tmpCode[0];
		 		} elseif (count($_tmpCode) == 2) {
		 			$returnData['redeemCode'] = $_tmpCode[0];
		 			$returnData['password'] = $_tmpCode[1];
		 		} else {
		 			$returnData['redeemCode'] = $_tmpCode[0];
		 			$returnData['password'] = $_tmpCode[1];
		 			$returnData['area'] = $_tmpCode[2];
		 		}

		 		if (in_array($_gameInfo['gtype'], array('Android', 'IOS', 'Windows', '各平台通用'))) {
		 			$returnData['activateUrl'] = $_newcardInfo['acturl'];
		 		}

 				//$_newcardInfo['valid_date']['end']
 				if ($_newcardInfo['nextFetchTime'] == 0) {
 						$returnData['invalidtime'] = "0";
 				} else {
 						$returnData['invalidtime'] = $_newcardInfo['nextFetchTime'];
 				}

		 		/*  淘宝不限次数  但不会入库
 				//淘号成功，领卡信息入库
 				//拼装数据
 				$data = array(
 						'uid' => $guid,
 						'item_id' => $giftId,
 						'game_id' => $_newcardInfo['gid'],
 						//'origintype' => $_newcardInfo['origintype'],
 						//'card_id' => $giftId, //跟刘乐确定
 						'card_id' => 12345, //先写死
 						'redeem_code' => $active_code,
 						'status' => 1, //入库定为1
 						'starttime' => $starttime,
 						'endtime' => $endtime,
 						'create_time' => date('Y-m-d H:i:s'),
 						'update_time' => date('Y-m-d H:i:s'),
 					);

 				//调用方法入库
 				$insert_res = $this->gift_model->insert_getcard_info($data);
				*/

		        Util::echo_format_return(_SUCCESS_, $returnData ? $returnData: array());
		        return 1;
		 	}
	    } catch (Exception $e) {
	        Util::echo_format_return($e->getCode(), array(), $e->getMessage());
	        return 0;
	    }
	}

	//删除已经领取的礼包接口
	public function deleteCard(){
		$giftInfo = $this->input->get ( 'giftInfo', true );
		$guid = ( int ) $this->input->get ( 'guid', true );

		//获取要删除礼包信息
		$giftInfo_arr = json_encode($giftInfo, 1);

		try {
	        if (!$guid || empty($giftInfo_arr)) {
	            throw new Exception('参数错误', _PARAMS_ERROR_);
	        }

	        //循环遍历调用方法删除对应卡包
	        foreach($giftInfo_arr as $v){
	        	$this->gift_model->delCard($guid, $card_id, $item_id);
	        }

	        Util::echo_format_return(_SUCCESS_, array(), '删除成功');
	        return 1;
	    } catch (Exception $e) {
	        Util::echo_format_return($e->getCode(), array(), $e->getMessage());
	        return 0;
	    }
	}

    //过滤内容中的a标签
    private function findUrl($str) {
        $returnData = array();
        $returnData['content'] = $str;
        $returnData["content"] = strtr($returnData["content"], array('<br>'=>'[[br]]','<br />'=>'[[br /]]', '<div>'=>'[[div]]', '</div>'=>'[[/div]]', '<p>'=>'[[p]]', '</p>'=>'[[/p]]'));
        $pant = "/<a href=\"(.*?)\"[^>]*>.*?<span [^>]*>(.*?)<\/span>.*?<\/a>/";
        preg_match_all($pant,$str,$pregData);
        //-->去掉连接
        //$pregData[0] = array();
        //<--
        if (empty($pregData[0])) {
            $returnData['links'] = array();
        } else {
            foreach ($pregData[0] as $_k => $_v) {
                $returnData["content"]=str_replace($_v, "{{URL_".$_k."}}", $returnData["content"]);
                $_tmpArray=array();
                $_tmpArray["url"]=$pregData[1][$_k];
                $_tmpArray["desc"]=Util::html5ObjClean(strip_tags($pregData[2][$_k]));
                $returnData['links'][] = $_tmpArray;
            }
        }

        //2次处理
        $returnData["content"] = trim(Util::html5ObjClean($returnData["content"]));
        $returnData["content"] = strtr($returnData["content"], array('{{'=>'<!--','}}'=>'-->', '[['=>'<', ']]'=>'>'));
        return $returnData;
    }

	public function exchange_date($date) {
		return date('Y-m-d H:i', strtotime($date));
	}
}
