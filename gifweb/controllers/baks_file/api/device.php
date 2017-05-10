<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Device extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('spread_model');
	}

	public function add()
	{
		$idfa	  	= $this->input->get('idfa',true) ? $this->input->get('idfa',true) : '';//独立设备标示

		try {
			if($idfa == '') {
				throw new Exception('idfa不能为空', _PARAMS_ERROR_);
			}

			$shike_info = $this->spread_model->findData($idfa);

			if(empty($shike_info)){//自主
				//插入数据库
				$data =array();
				$data['type'] = 0;
				$data['idfa'] = strtoupper($idfa);
				$data['status'] = 1;
                $data['tmp2'] = $this->input->get('version',true) ? $this->input->get('version',true) : '';//版本号
				$data['create_time'] = time();
				$data['active_time'] = time();
				$this->spread_model->insertData($data);
			}elseif($shike_info['type'] == 1 && $shike_info['status'] == 0 ){//app试客来源

				$this->common_model->trans_begin();
				//更新激活状态
				$status_info = $this->spread_model->updateStatus($shike_info['id'],1);
				if (!$status_info) {
					throw new Exception('失败', _DATA_ERROR_);
				}
				//更新重复数据［非激活状态的数据］
				$tmp1_info = $this->spread_model->updateTmp1Status($shike_info['idfa']);
				if (!$tmp1_info) {
					throw new Exception('失败', _DATA_ERROR_);
				}

				$this->common_model->trans_commit();

				//回调
				//$urls="http://t.appshike.com/itry_cooperate.groovy?idfa=".$shike_info['idfa']."&appid=".$shike_info['appid']."&mac=".$shike_info['macid']."&source=quanminshouyou";
                $urls = $shike_info['callback'];
                $json_data = Util::curl_get_contents($urls);
				$returnInfos = json_decode($json_data, true);
				if($returnInfos['success']){//回调成功后做标记
					$this->spread_model->updateCallbackStatus($shike_info['id'],1);//回调成功
				}else{
					$this->spread_model->updateCallbackStatus($shike_info['id'],2);//回调失败
				}
			}elseif($shike_info['type'] == 3 && $shike_info['status'] == 0 ){//触控广告平台

				$this->common_model->trans_begin();
				//更新激活状态
				$status_info = $this->spread_model->updateStatus($shike_info['id'],1);
				if (!$status_info) {
					throw new Exception('失败', _DATA_ERROR_);
				}
				//更新重复数据［非激活状态的数据］
				$tmp1_info = $this->spread_model->updateTmp1Status($shike_info['idfa']);
				if (!$tmp1_info) {
					throw new Exception('失败', _DATA_ERROR_);
				}

				$this->common_model->trans_commit();

				//回调
				$urls = $shike_info['callback'];
				$json_data = Util::curl_get_contents($urls);
				$returnInfos = json_decode($json_data, true);

				$statusCode = Util::GetHttpStatusCode($urls);
				if($statusCode==200){//回调成功后做标记
					$this->spread_model->updateCallbackStatus($shike_info['id'],1);//回调成功
				}else{
					$this->spread_model->updateCallbackStatus($shike_info['id'],2);//回调失败
				}
			}
			Util::echo_format_return(_SUCCESS_, '','成功');
		}catch (Exception $e) {
			$this->common_model->trans_rollback();
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}

}