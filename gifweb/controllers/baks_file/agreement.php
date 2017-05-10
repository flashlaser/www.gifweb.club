<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * @name Agreement
 * @desc null
 *
 * @author	long
 * @date	2015-09-02
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 *
 */
class Agreement extends MY_Controller
{
	public function __construct(){
		parent::__construct();
	}

	public function index(){
		$this->smarty->view ( 'agreement/agreement.tpl' );
	}

}

