<?php


// xhprof begin
if (!empty($_REQUEST['ll_xhprof'])) {
	if (function_exists('xhprof_enable')) {
		$GLOBALS['xhprof'] = 1;
		xhprof_enable(XHPROF_FLAGS_MEMORY + XHPROF_FLAGS_CPU);
	}
}
//


function ll_dump($var) {
	header("Content-type:text/html;charset=utf-8");
	echo '<pre>';
	var_dump($var);
	echo '</pre>';
}


/**
* 设置为中国时区
*/
date_default_timezone_set('PRC');

define("WEAVER", "v1.0");

require_once('config/config.php');

if (count($filterconfig) > 0 )
{
	//$uri =  $_SERVER['REQUEST_URI'];
	$pathinfo = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');
	$basename = basename($pathinfo);
	if ($pathinfo == '/')
	{
		//原来INDEX.PHP内容
		
	}
	elseif (in_array($basename, $filterconfig))
	{
		switch ($basename)
		{
			case 'xx':
				require_once ($pathinfo);
				break;
			default:
				require_once ($pathinfo);
				break;
		}
		exit(0);		
	}
}


/*
 *---------------------------------------------------------------
 * APPLICATION ENVIRONMENT
 *---------------------------------------------------------------
 *
 * You can load different configurations depending on your
 * current environment. Setting the environment also influences
 * things like logging and error reporting.
 *
 * This can be set to anything, but default usage is:
 *
 *     开发环境 development
 *     测试环境 testing
 *     生产环境 production
 *
 * NOTE: If you change these, also change the error_reporting() code below
 *
 */
	if (array_key_exists('MY_ROLE', $_SERVER))
	{
		define('ENVIRONMENT', $_SERVER['MY_ROLE']);
	}
	else 
	{
		define('ENVIRONMENT', 'testing');
	}
/*
 *---------------------------------------------------------------
 * ERROR REPORTING
 *---------------------------------------------------------------
 *
 * Different environments will require different levels of error reporting.
 * By default development will show errors but testing and live will hide them.
 */
//echo ENVIRONMENT;
if (defined('ENVIRONMENT'))
{
	switch (ENVIRONMENT)
	{
		case 'development':
			ini_set('display_errors', 'on');
			error_reporting(E_ERROR);			
		break;	
		case 'testing':
			ini_set('display_errors', 'on');
			error_reporting(E_ALL ^ E_NOTICE);
			break;
		case 'production':
			error_reporting(0);
		break;

		default:
			error_reporting(0);
		break;
	}
}
 //ini_set('display_errors', 'on');
 //error_reporting(E_ALL);
/*
 *---------------------------------------------------------------
 * SYSTEM FOLDER NAME
 *---------------------------------------------------------------
 *
 * This variable must contain the name of your "weaver" folder.
 * Include the path if the folder is not in the same  directory
 * as this file.
 *
 */
	$system_path = 'weaver';

/*
 *---------------------------------------------------------------
 * 应用文件夹名字
 *---------------------------------------------------------------
 *
 * If you want this front controller to use a different "application"
 * folder then the default one you can set its name here. The folder
 * can also be renamed or relocated anywhere on your server.  If
 * you do, use a full server path. For more info please see the user guide:
 * http://codeigniter.com/user_guide/general/managing_apps.html
 *
 * NO TRAILING SLASH!
 * 如果$_SERVER不存在，说明是CLI模式访问。
 */

	

	if (php_sapi_name()=== "cli")
	{
		//cli 请求方式. 请求方法如：php index.php admin main  index/
		$path = array_slice($_SERVER['argv'], 1,1);
		$application_folder = $path[0];
	}
	else
	{
		$hostname = $_SERVER['HTTP_HOST'];
		if (isset($siteconfig[$hostname]))
		{
			$application_folder = $siteconfig[$hostname];
		}
		else
		{
			$multi_dir = true;
			$path = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');
			if (trim($path, '/') != '')
			{
				$pathInfo = explode("/", $path);
				$application_folder = $pathInfo[1];
			}
			else
			{
				$path =  (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : @getenv('QUERY_STRING');
				if (trim($path, '/') != '') 
				{
					$pathInfo = explode("/", $path);
					$application_folder = $pathInfo[1];			
				}
				else
				{
					//default app
					$application_folder = "application";				
				}
			}		
		}
	}

/*
 * --------------------------------------------------------------------
 * DEFAULT CONTROLLER
 * --------------------------------------------------------------------
 *
 * Normally you will set your default controller in the routes.php file.
 * You can, however, force a custom routing by hard-coding a
 * specific controller class/function here.  For most applications, you
 * WILL NOT set your routing here, but it's an option for those
 * special instances where you might want to override the standard
 * routing in a specific front controller that shares a common CI installation.
 *
 * IMPORTANT:  If you set the routing here, NO OTHER controller will be
 * callable. In essence, this preference limits your application to ONE
 * specific controller.  Leave the function name blank if you need
 * to call functions dynamically via the URI.
 *
 * Un-comment the $routing array below to use this feature
 *
 */
	// The directory name, relative to the "controllers" folder.  Leave blank
	// if your controller is not in a sub-folder within the "controllers" folder
	// $routing['directory'] = '';

	// The controller class file name.  Example:  Mycontroller
	// $routing['controller'] = '';

	// The controller function you wish to be called.
	// $routing['function']	= '';


/*
 * -------------------------------------------------------------------
 *  CUSTOM CONFIG VALUES
 * -------------------------------------------------------------------
 *
 * The $assign_to_config array below will be passed dynamically to the
 * config class when initialized. This allows you to set custom config
 * items or override any default config values found in the config.php file.
 * This can be handy as it permits you to share one application between
 * multiple front controller files, with each file containing different
 * config values.
 *
 * Un-comment the $assign_to_config array below to use this feature
 *
 */
	// $assign_to_config['name_of_config_item'] = 'value of config item';



// --------------------------------------------------------------------
// END OF USER CONFIGURABLE SETTINGS.  DO NOT EDIT BELOW THIS LINE
// --------------------------------------------------------------------

/*
 * ---------------------------------------------------------------
 *  Resolve the system path for increased reliability
 * ---------------------------------------------------------------
 */

	// Set the current directory correctly for CLI requests
	if (defined('STDIN'))
	{
		chdir(dirname(__FILE__));
	}

	if (realpath($system_path) !== FALSE)
	{
		$system_path = realpath($system_path).'/';
	}

	// ensure there's a trailing slash
	$system_path = rtrim($system_path, '/').'/';

	// Is the system path correct?
	if ( ! is_dir($system_path))
	{
		exit("Your system folder path does not appear to be set correctly. Please open the following file and correct this: ".pathinfo(__FILE__, PATHINFO_BASENAME));
	}

/*
 * -------------------------------------------------------------------
 *  Now that we know the path, set the main path constants
 * -------------------------------------------------------------------
 */
	// The name of THIS file
	define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

	// The PHP file extension
	// this global constant is deprecated.
	define('EXT', '.php');

	// Path to the system folder
	define('BASEPATH', str_replace("\\", "/", $system_path));

	// Path to the front controller (this file)
	define('FCPATH', str_replace(SELF, '', __FILE__));

	// Name of the "system folder"
	define('SYSDIR', trim(strrchr(trim(BASEPATH, '/'), '/'), '/'));


	// The path to the "application" folder
	if (is_dir($application_folder))
	{
		define('APPPATH', $application_folder.'/');
	}
	else
	{
		if ( ! is_dir(BASEPATH.$application_folder.'/'))
		{
			exit("Your application folder path does not appear to be set correctly. Please open the following file and correct this: ".SELF);			
		}

		define('APPPATH', BASEPATH.$application_folder.'/');
	}

/*
 * --------------------------------------------------------------------
 * LOAD THE BOOTSTRAP FILE
 * --------------------------------------------------------------------
 *
 * And away we go...
 *
 */
require_once BASEPATH.'core/CodeIgniter.php';




// xhprof begin
/*if (!empty($GLOBALS['xhprof'])) {
	$data = xhprof_disable();   //返回运行数据

	// xhprof_lib在下载的包里存在这个目录,记得将目录包含到运行的php代码中
	include_once "xhprof_lib/utils/xhprof_lib.php";
	include_once "xhprof_lib/utils/xhprof_runs.php";

	$objXhprofRun = new XHProfRuns_Default();

	// 第一个参数j是xhprof_disable()函数返回的运行信息
	// 第二个参数是自定义的命名空间字符串(任意字符串),
	// 返回运行ID,用这个ID查看相关的运行结果
	$run_id = $objXhprofRun->save_run($data, "xhprof");
	unset($GLOBALS['xhprof']);
	
	echo "<script>console.log('run_id:{$run_id}');</script>";
}*/

