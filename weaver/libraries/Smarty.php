<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
require BASEPATH."libraries/Smarty/Smarty.class.php";
class CI_Smarty extends Smarty
{

    function __construct(){
        parent::__construct();
		$ci =& get_instance();
        $this->left_delimiter = "<{";
        $this->right_delimiter = "}>";

        $smarty_template_dir = $ci->config->item('smarty_template_dir');
        $smarty_compile_dir = $ci->config->item('smarty_compile_dir');
        $smarty_caching = $ci->config->item('smarty_caching');
        $smarty_cache_dir = $ci->config->item('smarty_cache_dir');
		$this->template_dir = (!empty($smarty_template_dir) ? $smarty_template_dir : APPPATH . 'views/');
        $this->compile_dir  = (!empty($smarty_compile_dir) ? $smarty_compile_dir : APPPATH . '/cache/tpl_compile/');
        $this->caching = false;
        $this->cache_dir = !empty($smarty_cache_dir) ? $smarty_cache_dir : $_SERVER['SINASRV_CACHE_DIR'];

        $this->assign( 'APPPATH', APPPATH );
        $this->assign( 'BASEPATH', BASEPATH );
 
        // Assign CodeIgniter object by reference to CI
        if ( method_exists( $this, 'assignByRef') ){            
            $this->assignByRef("ci", $ci);
        }
        log_message('debug', "Smarty Class Initialized");
    }
    
    function view($template, $data = array(), $return = FALSE){
	    foreach ($data as $key => $val){
	      $this->assign($key, $val);
	    }

	    if ($return == FALSE){
	      $CI =& get_instance();
	      $CI->output->final_output = $this->fetch($template);
	      return;
	    }else{
	      return $this->fetch($template);
	    }
	  }
 
}

/* End of file Smarty.php */
/* Location: ./application/libraries/Smarty.php */
