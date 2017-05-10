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
class Yar extends CI_Controller
{
    private $mail_arr = array('liule1@staff.sina.com.cn', 'wangbo8@staff.sina.com.cn');
    private $ipWall = array(
        '127.0.0.1',
        '61.135.152.128/29',

        '10.24.200.168', // 测试机
        '10.24.190.186', // web1
        '10.24.201.96', // web2
    );
    // 允许的 model，得慎重
    private $_allow_model = array(

    );

    public function __construct()
    {
        parent::__construct();
        $this->load->model('common_model');
        $this->load->library('global_func');
        $this->load->driver('cache');

        $this->_verify();
    }
    private function _verify() {
        $this->_verifyIp();
    }
    private function _verifyIp() {
        $ip = getenv('REMOTE_ADDR');    // REMOTE_ADDR相对安全， 其貌似不能伪造
        $this->load->library('IP4_filter', $this->ipWall);
        if (!$this->ip4_filter->check($ip)) {
            echo $ip;
            exit('ip error');
        }
        return true;
    }


    // =======================================================================================
    public function model($model) {
        if (in_array($model, $this->_allow_model)) {
            $this->load->model($model);
            $obj = $this->{$model};
        }
        if (is_object($obj)) {
            $service = new Yar_Server ( $obj );
            $service->handle ();
        } else {
            exit('error');
        }
    }
    public function controller($controller) {

        $controllerArr = func_get_args();
        if ($controllerArr[0] != 'yar') {
            exit('error');
        }

        $class = end($controllerArr);
        $file = FCPATH . DIRECTORY_SEPARATOR . APPPATH . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $controllerArr) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }

        $obj = new $class;
        if ($obj) {
            $service = new Yar_Server ( $obj );
            $service->handle ();
        } else {
            exit('error');
        }
    }


}
