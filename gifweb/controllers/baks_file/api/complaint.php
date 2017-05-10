<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Complaint extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('complaint_model');
		$this->load->model('answer_model');
		$this->load->model('question_model');
	}

	/*
	 * 举报操作
	 */
	public function complaint_add()
	{
		$mark	  	= $this->input->get('mark',true) ? $this->input->get('mark',true) : '';
		$target	  	= $this->input->get('target',true) ? $this->input->get('target',true) : 0;
		$type	  	= $this->input->get('type',true) ? $this->input->get('type',true) : 0;

		try {
			if($mark == '') {
				throw new Exception('ID不能为空', _PARAMS_ERROR_,'举报失败');
			}
			$data['mark'] = trim($mark);
			$data['type'] = $target ? 2 : 1;
			$data['content_type'] = $type ? $type : 5;
			$data['create_time'] = time();
			$this->complaint_model->insertComplaintData($data);
			if($target == 1){
				//累加举报的答案数
				$this->answer_model->add_complaint_count($mark);
			}else{
				//累加举报的问题数
				$this->question_model->add_complaint_count($mark);
			}

			Util::echo_format_return(_SUCCESS_, '','举报成功');
		}catch (Exception $e) {
			Util::echo_format_return($e->getCode(), array(), $e->getMessage());
		}
	}

}

/* End of file complaint_model.php */
/* Location: ./application/controllers/api/complaint_model.php */