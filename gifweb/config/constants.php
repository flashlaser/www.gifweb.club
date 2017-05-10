<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ', 							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE', 					'ab');
define('FOPEN_READ_WRITE_CREATE', 				'a+b');
define('FOPEN_WRITE_CREATE_STRICT', 			'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');





// 图片前缀
define('IMAGE_URL_PRE', 'http://sinastorage.com/store.games.sina.com.cn/');

//定义code码

/**
 * 200	操作成功
1001	缺少参数
1002	签名错误
2001	用户id不存在
2002	授权token错误
2003	token过期
3001	用户被禁
 */
//成功code
define("_SUCCESS_", 200);
//参数错误code
define("_PARAMS_ERROR_", 1001);
//校验码错误code
define("_SIGN_ERROR_", 1002);
//数据错误code
define("_DATA_ERROR_", 1003);
//校验码错误code
define("_SEED_ERROR_", 1004);
//错误，但需要新SEED
define("_ERROR_BUT_NEED_NEW_", 1005);
//用户ID不存在
define('_USER_NOT_EXIST_', 2001);
// 授权token错误
define('_USER_TOKEN_ERROR_', 2002);
// TOKEN过期
define('_USER_TOKEN_OVERDUE_', 2003);
// 用户被禁
define('_USER_BANNED_', 3001);

define('DEBUG_MODEL', 1);



// 图片前缀，为了区分老的图片和新图片，此地址最好在保存时使用
define('OSS_IMG_PREFIX', 'http://gifweb.oss-cn-beijing.aliyuncs.com/');
/* End of file constants.php */
/* Location: ./system/application/config/constants.php */
define('PAGE_LIST_SIZE', 10);